<?php

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Defuse\Crypto\Exception\BadFormatException;

add_action('admin_init', 'guesty_settings_init');

function setup_guesty_menu()
{
    // Add main menu page
    add_menu_page(
        'Guesty Integration',  // Page title
        'Guesty Integration',  // Menu title
        'manage_options',      // Capability
        'guesty-integration',  // Menu slug
        'guesty_options_page', // Callback function to display the page
        'dashicons-admin-generic', // Icon URL ( Dashicon class )
        200                     // Position in the menu
    );

    // Add 'Settings' submenu
    add_submenu_page(
        'guesty-integration',  // Parent slug
        'Guesty Settings',     // Page title
        'API Settings',            // Menu title
        'manage_options',      // Capability
        'guesty-settings',     // Menu slug
        'guesty_options_page' // Callback function
    );

    // Add 'List' submenu
    add_submenu_page(
        'guesty-integration',  // Parent slug
        'Guesty List',         // Page title
        'Listings',                // Menu title
        'manage_options',      // Capability
        'guesty-list',         // Menu slug
        'get_list_page'     // Callback function
    );

    add_submenu_page(
        'guesty-integration',  // Parent slug
        'Guesty Calendar',     // Page title
        'Availability',            // Menu title
        'manage_options',      // Capability
        'guesty-calendar',     // Menu slug
        'get_calendar_page' // Callback function
    );

    guesty_detail();  // Add this line to add the Detail Listing submenu

    // Remove the duplicate 'Guesty Integration' submenu that automatically gets added
    remove_submenu_page('guesty-integration', 'guesty-integration');
}

add_action('admin_menu', 'setup_guesty_menu');

function guesty_detail()
{
    add_submenu_page(
        'guesty-list',           // Parent slug (Guesty List)
        'Detail Listing',        // Page title
        'Detail Listing',        // Menu title
        'manage_options',        // Capability
        'guesty-detail',         // Menu slug
        'get_detail_list'        // Callback function
    );
}


function guesty_settings_init()
{
    register_setting('guesty_settings', 'guesty_client_id', array('sanitize_callback' => 'guesty_encrypt_callback'));
    register_setting('guesty_settings', 'guesty_client_secret', array('sanitize_callback' => 'guesty_encrypt_callback'));
    register_setting('guesty_settings', 'guesty_environment');

    add_settings_section('guesty_section', __('API Settings', 'guesty'), 'guesty_settings_section_callback', 'guesty_settings');

    add_settings_field('guesty_client_id', __('Site Key', 'guesty'), 'guesty_client_id_render', 'guesty_settings', 'guesty_section');
    add_settings_field('guesty_client_secret', __('Secret Key', 'guesty'), 'guesty_client_secret_render', 'guesty_settings', 'guesty_section');
    add_settings_field('guesty_environment', __('Environment', 'guesty'), 'guesty_environment_render', 'guesty_settings', 'guesty_section');
}

function guesty_client_id_render()
{
    $encrypted_client_id = get_option('guesty_client_id');
    $client_id = $encrypted_client_id ? guesty_decrypt($encrypted_client_id) : '';
    echo "<input type='text' name='guesty_client_id' value='" . esc_attr($client_id) . "'>";
}

function guesty_client_secret_render()
{
    $encrypted_client_secret = get_option('guesty_client_secret');
    $client_secret = $encrypted_client_secret ? guesty_decrypt($encrypted_client_secret) : '';
    echo "<input type='password' name='guesty_client_secret' value='" . esc_attr($client_secret) . "'>";
}

function guesty_environment_render()
{
    $environment = get_option('guesty_environment');
?>
    <select name='guesty_environment'>
        <option value='production' <?php selected($environment, 'production');
                                    ?>>Production</option>
        <option value='sandbox' <?php selected($environment, 'sandbox');
                                ?>>Sandbox</option>
    </select>
<?php
}

function guesty_settings_section_callback()
{
    echo __('Enter your Guesty API credentials and select the environment.', 'guesty');
}

function guesty_loading_css()
{
?>
    <div id="loader-container" class="loader-container" style="display: none">
        <div class="loader">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
<?php
}

function guesty_options_page()
{
?>
    <form class="container" action='admin.php?page=guesty-integration' method='post'>
        <h1>Guesty Integration</h1>
        <?php
        settings_fields('guesty_settings');
        do_settings_sections('guesty_settings');
        submit_button();
        ?>
        <div class="display-flex">
            <input type='submit' name='test_connection' value='Test Connection' class='button-secondary'>
        </div>
    </form>
    <?php
    if (isset($_POST['test_connection'])) {
        $guesty_api = new Guesty_API();
        $response = $guesty_api->test_connection();
        echo '<div class="notice notice-success is-dismissible"><p>Connection status: ' . esc_html($response) . '</p></div>';
        // Set the token as a cookie

    } else if (isset($_POST['guesty_client_id'])) {
        $encrypted_client_id = $_POST['guesty_client_id'];
        $encrypted_client_secret = $_POST['guesty_client_secret'];
        $environment = $_POST['guesty_environment'];
        update_option('guesty_client_id', $encrypted_client_id);
        update_option('guesty_client_secret', $encrypted_client_secret);
        update_option('guesty_environment', $environment);
    }
}



// List Management
function get_list_page()
{
    echo '<div class="container">';
    ?>
    <h1>Guesty Listings</h1>
    <?php

    // Fetch the list data
    $guesty_api = new Guesty_API();
    $response = $guesty_api->fetch_guesty_list_data();
    // Check if the response is valid and contains results
    if (!$response || !isset($response['results'])) {
        echo '<p>Failed to retrieve data. Check your connection or API configuration.</p>';
        return;
    }

    $list_data = $response['results'];

    // Collect all unique cities for the filter dropdown
    $cities = array();
    foreach ($list_data as $data) {
        if (!empty($data['address']['city']) && !in_array($data['address']['city'], $cities)) {
            $cities[] = $data['address']['city'];
        }
    }

    // Handle filtering
    $search_term = isset($_POST['search_term']) ? strtolower(trim($_POST['search_term'])) : '';
    $filter_city = isset($_POST['filter_city']) ? strtolower(trim($_POST['filter_city'])) : '';

    ?>

    <form method='post'>
        <input type='text' name='search_term' placeholder='Search by name...' value="<?php echo esc_attr($search_term); ?>" />
        <select name='filter_city'>
            <option value=''>All Cities</option>
            <?php
            foreach ($cities as $city) {
                echo '<option value="' . esc_attr($city) . '"' . selected($filter_city, strtolower($city), false) . '>' . esc_html($city) . '</option>';
            }
            ?>
        </select>
        <input type='submit' value='Filter' />
    </form>

    <?php
    if (empty($list_data)) {
        echo '<p>No data available.</p>';
    } else {
        echo '<table class="widefat fixed" cellspacing="0">';
        echo '<thead><tr><th>ID</th><th>Name</th><th>City</th></thead>';
        echo '<tbody>';

        foreach ($list_data as $data) {
            $item = $data['address'];
            // Apply search and filter here
            $building_name = $data['nickname'] ?? 'No building';
            $city = $item['city'] ?? 'No city';

            if (!empty($search_term) && strpos(strtolower($building_name), $search_term) === false) {
                continue;
            }
            if (!empty($filter_city) && strtolower($city) != $filter_city) {
                continue;
            }

            // $detail_url = add_query_arg(array(
            //     'id' => $data['_id'],
            // ), '/wordpress/wp-admin/admin.php?page=guesty-list');
            $detail_url = add_query_arg(array(
                'page' => 'guesty-detail',  // Use 'page' instead of 'id'
                'id' => $data['_id'],
            ), admin_url('admin.php'));

            echo '<tr>';
            echo '<td><a href="' . esc_url($detail_url) . '">' . esc_html($data['_id']) . '</a></td>';
            echo '<td><a href="' . esc_url($detail_url) . '">' . esc_html($building_name) . '</a></td>';
            echo '<td><a href="' . esc_url($detail_url) . '">' . esc_html($city) . '</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    echo '</div>';
}

// List Detail
function get_detail_list()
{
    // Fetch the detail data
    if (isset($_GET['id'])) {
        $id = sanitize_text_field($_GET['id']);
    }
    $guesty_api = new Guesty_API();
    $response = $guesty_api->fetch_guesty_detail_data($id);


    $buildingName = $response['nickname'] ?? "No name";
    $address = $response['address']['full'] ?? "No location";
    $bedrooms = $response['bedrooms'] ?? "No bedroom";
    $guests = $response['accommodates'] ?? "No guest";
    $bathrooms = $response['bathrooms'] ?? "No bathroom";
    $description = $response['publicDescription'] ?? "No description";
    $amenities = $response['amenities'] ?? "No amenities";
    $pictures = $response['pictures'] ?? "No images";
    $mapInfo = $response['customFields'] ?? "No map";

    echo '<div class="container top-margin"><h1>' . $buildingName . '</h1></div>';
    ?>
    <div class="container display-grid">
        <div>
            <div class="display-flex header-icons">
                <p class="contents"><i class="fas icon-size fa-location-dot"></i><?php echo $address; ?></p>
                <p class="contents"><i class="fas icon-size fa-bed"></i><?php echo $bedrooms; ?> Bedrooms</p>
                <p class="contents"><i class="fas icon-size fa-person"></i><?php echo $guests; ?> Guests</p>
                <p class="contents"><i class="fas icon-size fa-bath"></i><?php echo $bathrooms; ?> Bathrooms</p>
            </div>
            <div class="dsp">
                <h3 class="">Description</h3>
                <p><?php echo $description['summary'] ?? "No description"; ?></p>
                <p><?php echo $description['space'] ?? ""; ?></p>
            </div>
            <div class="dsp-title">
                <h3 class="">Amenities</h3>
                <div class="row">
                    <?php
                    $idx = 0;
                    foreach ($amenities as $item) {
                    ?>
                        <div class="column">
                            <p class="contents"><i class="fas icon-size fa-check"></i><?php echo $item; ?></p>
                        </div>
                    <?php
                        $idx++;
                        if ($idx % 4 == 0 && $idx < count($amenities)) {
                            echo '</div><div class="row">';  // End the current row and start a new one
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <div>
            <!-- <img src="https://res.cloudinary.com/guesty/image/upload/c_fit,h_200/ktcc4vcarvpdgwhscn1w?_a=BAMCcSGi0" width="100%" alt=""> -->
            <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3942.762046628037!2d115.1142549!3d-8.8084117!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd24589e8b5ce2f%3A0x7f444e7d8cc06b82!2sADAYA%20Boutique%20Villas!5e0!3m2!1sen!2sru!4v1721261412461!5m2!1sen!2sru" width="100%" height="90%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
    <div class="container">
        <h1>Gallery</h1>
        <div class="img-container">
            <?php
            foreach ($pictures as $img) {
            ?>
                <img src="<?php echo $img['thumbnail']; ?>" alt="img">
            <?php
            }
            ?>
        </div>
    </div>
<?php
}
add_shortcode("get_detail_list", "get_detail_list");

// Calendar
function get_calendar_page()
{
    $guesty_api = new Guesty_API();
    $res_list = $guesty_api->fetch_guesty_list_data()['results'];
    // $response = $guesty_api->fetch_guesty_calendar_data($id, $startDate, $endDate);
?>
    <div class="wpbc_header wpdvlp-top-tabs container">
        <h1>Booking Calendar</h1>
        <form method='post' id="calendar-form" class="display-flex form-box">
            <div class="display-flex dis-gap">
                <div>
                    <select name="acm">
                        <?php
                        $acms = array();
                        array_push($acms, "All Accommodation Type");
                        foreach ($res_list as $item) {
                            $acm = $item['propertyType'];
                            array_push($acms, $acm);
                        }
                        $acms_list = array_values(array_unique($acms));
                        $status_list = array('All Statues', 'Booked', 'Pending', 'External', 'Blocked');
                        $selectedAcmIdx = $_POST['acm'] ?? "";
                        $selectedStatusIdx = $_POST['status'] ?? "";
                        $startDate = $_POST['start_date'] ?? "";
                        $endDate = $_POST['end_date'] ?? "";

                        foreach ($acms_list as $idx => $acm) {
                            $selected = ($selectedAcmIdx == $idx) ? 'selected' : '';
                            echo '<option value="' . $idx . '" ' . $selected . '>' . $acm . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="display-flex">
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" required>
                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" required>
                </div>
                <div class="display-flex">
                    <select name="status">
                        <?php
                        foreach ($status_list as $idx => $status) {
                            $selected = ($selectedStatusIdx == $idx) ? 'selected' : '';
                            echo '<option value="' . $idx . '" ' . $selected . '>' . $status . '</option>';
                        }
                        ?>
                    </select>
                    <div>
                        <button type="submit" class="btn-search button-secondary" style="margin-left: 12px">Search</button>
                    </div>
                </div>
            </div>
            <div class="display-flex">
                <div class="display-col">
                    <p class="status-box booked-color"></p>
                    Booked
                </div>
                <div class="display-col">
                    <p class="status-box pending-color"></p>
                    Pending
                </div>
                <div class="display-col">
                    <p class="status-box external-color"></p>
                    External
                </div>
                <div class="display-col">
                    <p class="status-box blocked-color"></p>
                    Blocked
                </div>
            </div>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['start_date'] <= $_POST['end_date']) {
            guesty_loading_css();
        ?><script>
                document.getElementById('loader-container').style.display = 'flex';
            </script><?php
                        $acmIdx = $_POST['acm'];
                        $statusIdx = $_POST['status'];
                        $startDate = $_POST['start_date'];
                        $endDate = $_POST['end_date'];
                        $selectedAcm = $acms_list[$acmIdx];
                        $selectedStatus = $status_list[$statusIdx];
                        $res = array();
                        $res_villa = array();
                        $res_apart = array();
                        foreach ($res_list as $item) {
                            if (!$acmIdx) {
                                if ($item['propertyType'] == 'Villa') array_push($res_villa, $item['_id']);
                                if ($item['propertyType'] == 'Apartment') array_push($res_apart, $item['_id']);
                            } else if ($acmIdx && $selectedAcm == $item['propertyType'])
                                array_push($res, $item['_id']);
                        }
                        $calendar_api = new Booking_Guestify_Calendar;
                        $days = $calendar_api->getAllDaysBetween($startDate, $endDate);

                        for ($i = 0; $i < ($acmIdx ? 1 : 2); $i++) {
                        ?>
                <div class="table-wrapper">
                    <table class="sm-container">
                        <thead>
                            <th style="font-size: larger; padding-inline: 10px">Accommodation</th>
                            <?php
                            foreach ($days as $day) {
                            ?>
                                <th class="cal-days">
                                    <p id="day"><?php echo $day['day'] ?></p>
                                    <p id="month"><?php echo $day['month'] ?></p>
                                    <p id="year"><?php echo $day['year'] ?></p>
                                    <p id="week"><?php echo $day['week'] ?></p>
                                </th>
                            <?php
                            }
                            ?>
                        </thead>
                        <tbody>
                            <?php
                            $idx = 1;
                            foreach (($acmIdx ? $res : ($i ? $res_apart : $res_villa)) as $each) {
                            ?>
                                <tr class="">
                                    <?php
                                    foreach ($res_list as $element) {
                                        if ($element['_id'] == $each) {
                                            echo '<td class="acm-size">' . $element['nickname'] . '</td>';
                                            break;
                                        }
                                    }
                                    $idx++;
                                    $daysData = $guesty_api->fetch_guesty_calendar_data($each, $startDate, $endDate);
                                    foreach ($daysData as $dayData) {
                                    ?>
                                        <?php if ($dayData['status'] == 'available') { ?> <td class="day-mark"></td> <?php } ?>
                                        <?php if ($dayData['status'] == 'unavailable') { ?> <td class="day-mark blocked-color"></td> <?php } ?>
                                        <?php if ($dayData['status'] == 'booked') { ?> <td class="day-mark booked-color"></td> <?php } ?>
                                        <?php if ($dayData['status'] == 'pending') { ?> <td class="day-mark pending-color"></td> <?php } ?>
                                        <?php if ($dayData['status'] == 'external') { ?> <td class="day-mark external-color"></td> <?php }
                                                                                                                            } ?>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php
                        }
            ?><script>
                document.getElementById('loader-container').style.display = 'none';
            </script><?php
                    } else if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['start_date'] > $_POST['end_date']) {
                        echo '<div class="notice notice-error is-dismissible"><p>Please input date correctly.</p></div>';
                    }
                        ?>
    </div>
<?php
}

// frontend UI
function guesty_booking_quote_form()
{
    ob_start();
    $res = array();
    $guesty_api = new Guesty_API();
    $response = $guesty_api->fetch_guesty_list_data();
    $list_data = $response['results'];

    // Retain the submitted values
    $range = isset($_POST['daterange']) ? $_POST['daterange'] : "";
    $guests = isset($_POST['accommodates']) ? $_POST['accommodates'] : "";
    $bedrooms = isset($_POST['bedrooms']) ? $_POST['bedrooms'] : "";

?>

    <form class="search-panel" method="post">
        <div class="container booking-search-box">
            <!-- Location Selection -->
            <div class="search-field">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px">
                    <path d="M15 8.25a3 3 0 1 1-6 0 3 3 0 0 1 6 0m1.5 0a4.5 4.5 0 1 0-9 0 4.5 4.5 0 0 0 9 0M12 1.5a6.75 6.75 0 0 1 6.75 6.75c0 2.537-3.537 9.406-6.75 14.25-3.214-4.844-6.75-11.713-6.75-14.25A6.75 6.75 0 0 1 12 1.5M12 0a8.25 8.25 0 0 0-8.25 8.25c0 2.965 3.594 9.945 7 15.08a1.5 1.5 0 0 0 2.5 0c3.406-5.135 7-12.115 7-15.08A8.25 8.25 0 0 0 12 0"></path>
                </svg>
                <select name="location" id="location" required>
                    <option value="" disabled selected>Where are you going?</option>
                    <?php
                    $street = array();
                    foreach ($list_data as $idx => $data) {
                        array_push($street, $data['address']['city']);
                    }
                    $streets = array_unique($street);
                    foreach ($streets as $idx => $data) {
                        if ($data) echo '<option value="' . $idx . '">' . $data . '</option>';
                    }
                    ?>
                </select>
            </div>
            <!-- Date Range Selection -->
            <div class="search-field">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px">
                    <path d="M22.5 13.5v8.25a.75.75 0 0 1-.75.75H2.25a.75.75 0 0 1-.75-.75V5.25a.75.75 0 0 1 .75-.75h19.5a.75.75 0 0 1 .75.75zm1.5 0V5.25A2.25 2.25 0 0 0 21.75 3H2.25A2.25 2.25 0 0 0 0 5.25v16.5A2.25 2.25 0 0 0 2.25 24h19.5A2.25 2.25 0 0 0 24 21.75zm-23.25-3h22.5a.75.75 0 0 0 0-1.5H.75a.75.75 0 0 0 0 1.5M7.5 6V.75a.75.75 0 0 0-1.5 0V6a.75.75 0 0 0 1.5 0M18 6V.75a.75.75 0 0 0-1.5 0V6A.75.75 0 0 0 18 6M5.095 14.03a.75.75 0 1 0 1.06-1.06.75.75 0 0 0-1.06 1.06m.53-1.28a1.125 1.125 0 1 0 0 2.25 1.125 1.125 0 0 0 0-2.25.75.75 0 0 0 0 1.5.375.375 0 1 1 0-.75.375.375 0 0 1 0 .75.75.75 0 0 0 0-1.5m-.53 6.53a.75.75 0 1 0 1.06-1.06.75.75 0 0 0-1.06 1.06m.53-1.28a1.125 1.125 0 1 0 0 2.25 1.125 1.125 0 0 0 0-2.25.75.75 0 0 0 0 1.5.375.375 0 1 1 0-.75.375.375 0 0 1 0 .75.75.75 0 0 0 0-1.5m5.845-3.97a.75.75 0 1 0 1.06-1.06.75.75 0 0 0-1.06 1.06m.53-1.28A1.125 1.125 0 1 0 12 15a1.125 1.125 0 0 0 0-2.25.75.75 0 0 0 0 1.5.375.375 0 1 1 0-.75.375.375 0 0 1 0 .75.75.75 0 0 0 0-1.5m-.53 6.53a.75.75 0 1 0 1.06-1.06.75.75 0 0 0-1.06 1.06M12 18a1.125 1.125 0 1 0 0 2.25A1.125 1.125 0 0 0 12 18a.75.75 0 0 0 0 1.5.375.375 0 1 1 0-.75.375.375 0 0 1 0 .75.75.75 0 0 0 0-1.5m5.845-3.97a.75.75 0 1 0 1.06-1.06.75.75 0 0 0-1.06 1.06m.53-1.28a1.125 1.125 0 1 0 0 2.25 1.125 1.125 0 0 0 0-2.25.75.75 0 0 0 0 1.5.375.375 0 1 1 0-.75.375.375 0 0 1 0 .75.75.75 0 0 0 0-1.5"></path>
                </svg>
                <input type="text" value="<?php echo $range; ?>" placeholder="Check-in - Check-out" name="daterange" id="date-range" required>
            </div>
            <!-- Guests and Bedrooms Selection -->
            <div class="search-field">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px">
                    <path d="M16.5 6a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0M18 6A6 6 0 1 0 6 6a6 6 0 0 0 12 0M3 23.25a9 9 0 1 1 18 0 .75.75 0 0 0 1.5 0c0-5.799-4.701-10.5-10.5-10.5S1.5 17.451 1.5 23.25a.75.75 0 0 0 1.5 0"></path>
                </svg>
                <input type="text" value="<?php echo $guests; ?> Guests | <?php echo $bedrooms; ?> Bedrooms" placeholder="Guests | Bedrooms" name="guests" id="guests" onclick="displayCount()" required>
                <div id="guests-popup" class="guests-popup">
                    <label>
                        <p style="width: 50%;"><?php echo "Guests: " ?></p>
                        <button type="button" class="decrement-btn" onclick="updateValue('accommodates', -1)">-</button>
                        <input type="text" name="accommodates" id="accommodates" min="1" value="1" readonly>
                        <button type="button" class="increment-btn" onclick="updateValue('accommodates', 1)">+</button>
                    </label>
                    <label>
                        <p style="width: 50%;"><?php echo "Bedrooms: " ?></p>
                        <button type="button" class="decrement-btn" onclick="updateValue('bedrooms', -1)">-</button>
                        <input type="text" name="bedrooms" id="bedrooms" min="1" value="1" readonly>
                        <button type="button" class="increment-btn" onclick="updateValue('bedrooms', 1)">+</button>
                    </label>
                    <button type="button" id="guests-popup-close" onclick="popup()">Done</button>
                </div>
            </div>
            <!-- Search Button -->
            <button type="submit" class="search-button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                    <path d="M15.8 14.8c-1.5 1.5-3.5 2.5-5.8 2.5-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8c0 2.3-1 4.3-2.5 5.8l4.5 4.5-1.4 1.4-4.5-4.5zM14 9c0-2.8-2.2-5-5-5S4 6.2 4 9s2.2 5 5 5 5-2.2 5-5z" />
                </svg>
                Search
            </button>
        </div>
    </form>

    <?php
    $checkin = "none";
    $checkout = "none";
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['id'])) {
        // Sanitize and process the form data

        $location = $streets[sanitize_text_field($_POST['location'])];
        $range = sanitize_text_field($_POST['daterange']);
        $guests = sanitize_text_field($_POST['accommodates']);
        $bedrooms = sanitize_text_field($_POST['bedrooms']);
        list($checkin_str, $checkout_str) = explode(' - ', $range);
        $checkin = DateTime::createFromFormat('m/d/Y', $checkin_str)->format('Y-m-d');
        $checkout = DateTime::createFromFormat('m/d/Y', $checkout_str)->format('Y-m-d');

        $guesty_api = new Guesty_API();
        $list_data = $guesty_api->fetch_guesty_list_data()['results'];
        $res = array();
        foreach ($list_data as $data) {
            if (isset($data['address']['city']) && $data['address']['city'] == $location && $data['accommodates'] >= $guests && $data['bedrooms'] >= $bedrooms) {
                $result = $guesty_api->new_booking_data($data['_id'], $checkin, $checkout, $guests);
                if ($result['status'] == 'success') {
                    array_push($res, $data['_id']);
                }
            }
        }
    }

    update_option('res_list', $res);
    update_option('res_checkin', $checkin);
    update_option('res_checkout', $checkout);
    update_option('res_guests', $guests);
    update_option('res_range', $range);
}
add_shortcode('guesty_booking_quote_form', 'guesty_booking_quote_form');

// reservation quote
function guesty_reservation_quote()
{
    ?>
    <div id="failedModal" class="modal">
        <div class="modal-content">
            <div id="modal-message">
                <div class="modal-header modal-red">
                    <span class="close" onclick="failedModal()">&times;</span>
                    <h2>Unavailable</h2>
                </div>
                <div class="modal-main">
                    <p>There isn't available villa or apartment.</p>
                </div>
            </div>
        </div>
    </div>
    <?php
    $list_data = get_option('res_list');
    $checkin = get_option('res_checkin');
    $checkout = get_option('res_checkout');
    $guests = get_option('res_guests');
    $range = get_option('res_range');

    $guesty_api = new Guesty_API;
    if (empty($list_data)) {
        if ($checkin !== 'none') {
    ?><script>
                var modal = document.getElementById("failedModal");
                modal.style.display = "block";

                function failedModal() {
                    modal.style.display = "none";
                }
                <?php } ?>
            </script>
            <div class="no-data-message"><svg height="24px" viewBox="0 0 1792 1792" width="24px" xmlns="http://www.w3.org/2000/svg">
                    <path d="M896 768q237 0 443-43t325-127v170q0 69-103 128t-280 93.5-385 34.5-385-34.5-280-93.5-103-128v-170q119 84 325 127t443 43zm0 768q237 0 443-43t325-127v170q0 69-103 128t-280 93.5-385 34.5-385-34.5-280-93.5-103-128v-170q119 84 325 127t443 43zm0-384q237 0 443-43t325-127v170q0 69-103 128t-280 93.5-385 34.5-385-34.5-280-93.5-103-128v-170q119 84 325 127t443 43zm0-1152q208 0 385 34.5t280 93.5 103 128v128q0 69-103 128t-280 93.5-385 34.5-385-34.5-280-93.5-103-128v-128q0-69 103-128t280-93.5 385-34.5z" />
                </svg> No Data Found</div>
            <?php
        } else {
            echo '<div class="cards-container container">';
            foreach ($list_data as $idx => $id) {
                $data = $guesty_api->fetch_guesty_detail_data($id);
                $result = $guesty_api->new_booking_data($id, $checkin, $checkout, $guests);
                $invoice = $result['data']['rates']['ratePlans'][0]['ratePlan']['money']['invoiceItems'][0];
            ?>
                <div class="card">
                    <input type="hidden" id="<?php echo $idx; ?>" name="<?php echo $idx; ?>" value="<?php echo $id; ?>">
                    <input type="hidden" id="nickname<?php echo $idx; ?>" name="nickname<?php echo $idx; ?>" value="<?php echo $data['nickname']; ?>">
                    <input type="hidden" id="range<?php echo $idx; ?>" name="range<?php echo $idx; ?>" value="<?php echo $range; ?>">
                    <input type="hidden" id="guests<?php echo $idx; ?>" name="guests<?php echo $idx; ?>" value="<?php echo $guests; ?>">
                    <input type="hidden" id="amount<?php echo $idx; ?>" name="amount<?php echo $idx; ?>" value="<?php echo $invoice['amount'] . ' ' . $invoice['currency']; ?>">

                    <img src="<?php echo $data['picture']['thumbnail']; ?>" alt="Denim Jeans" style="width:100%">
                    <div class="card-container card-box">
                        <h2><?php echo $data['nickname']; ?></h2>
                        <div class="contents-container">
                            <p><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px">
                                    <path d="M15 8.25a3 3 0 1 1-6 0 3 3 0 0 1 6 0m1.5 0a4.5 4.5 0 1 0-9 0 4.5 4.5 0 0 0 9 0M12 1.5a6.75 6.75 0 0 1 6.75 6.75c0 2.537-3.537 9.406-6.75 14.25-3.214-4.844-6.75-11.713-6.75-14.25A6.75 6.75 0 0 1 12 1.5M12 0a8.25 8.25 0 0 0-8.25 8.25c0 2.965 3.594 9.945 7 15.08a1.5 1.5 0 0 0 2.5 0c3.406-5.135 7-12.115 7-15.08A8.25 8.25 0 0 0 12 0"></path>
                                </svg><?php echo "  " . $data['address']['street']; ?></p>
                            <p><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px">
                                    <path d="M4 10V7a3 3 0 0 1 3-3h10a3 3 0 0 1 3 3v3h1a1 1 0 0 1 1 1v10h-2v-3H5v3H3V11a1 1 0 0 1 1-1h1zm2-3v3h12V7a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm-2 7h16v-2H4v2z"></path>
                                </svg><?php echo "  " . $data['bedrooms']; ?></p>
                            <p><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px">
                                    <path d="M2 20.914v1.586h1.586l4.442-4.442-1.586-1.586L2 20.914zM23.707 7.707l-2.414-2.414a1 1 0 0 0-1.414 0L7 17.172V21h3.828l12.293-12.293a1 1 0 0 0 0-1.414zM20 4.914l2.086 2.086-1.085 1.086L18.914 6 20 4.914z"></path>
                                </svg><?php echo "  " . $data['prices']['basePrice'] . " " . $data['prices']['currency']; ?></p>
                        </div>
                    </div>
                    <p class="card-container"><button onclick="newBook(<?php echo $idx; ?>)">Book</button></p>
                </div>
        <?php }
        }
        echo "</div>"; ?>
        <script>
            function newBook(idx) {
                var listingid = document.getElementById(idx).value;
                var amount = document.getElementById("amount" + idx).value;
                var nickname = document.getElementById("nickname" + idx).value;
                var range = document.getElementById("range" + idx).value;
                var guests = document.getElementById("guests" + idx).value;

                document.getElementById("modal-amount").textContent = amount;
                document.getElementById("modal-nickname").textContent = nickname;
                document.getElementById("modal-range").textContent = range;
                document.getElementById("modal-guests").textContent = guests;
                document.getElementById("listingid").value = listingid;

                var modal = document.getElementById("successModal");
                modal.style.display = "block";
            }

            function closeModal() {
                var modal = document.getElementById("successModal");
                modal.style.display = "none";
            }
        </script>
        <div id="successModal" class="modal">
            <input type="hidden" id="listingid" value="">
            <div class="modal-content">
                <div id="modal-message">
                    <div class="modal-header">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <h2>Reservation quote</h2>
                    </div>
                    <div class="modal-main">
                        <p>Booked: <strong id="modal-nickname"></strong></p>
                        <p>Range: <strong id="modal-range"></strong></p>
                        <p>Guests: <strong id="modal-guests"></strong></p>
                        <p>Amount: <strong id="modal-amount"></strong></p>
                    </div>
                </div>
                <div id="payment" class="container payment">
                    <form id="pay-check"><button type="submit" class="pay-check">Book now</button></form>
                    <form id="detail-check"><button type="submit" class="pay-check">Detail</button></form>
                    <button class="pay-cancel" onclick="closeModal()">Cancel</button>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            document.getElementById('pay-check').addEventListener('submit', function(e) {
                e.preventDefault();
                var listingid = document.getElementById("listingid").value;
                var amount = document.getElementById("modal-amount").textContent;
                var paymentUrl = '<?php echo home_url(); ?>/payment';
                var redirectUrl = paymentUrl + '?listingid=' + encodeURIComponent(listingid) + '&amount=' + encodeURIComponent(amount);
                window.location.href = redirectUrl;
            });
            document.getElementById('detail-check').addEventListener('submit', function(e) {
                e.preventDefault();
                var listingid = document.getElementById("listingid").value;
                var paymentUrl = '<?php echo home_url(); ?>/list-detail';
                var redirectUrl = paymentUrl + '?id=' + encodeURIComponent(listingid);
                window.location.href = redirectUrl;
            });
        </script>
    <?php
}
add_shortcode('guesty_reservation_quote', 'guesty_reservation_quote');

// css
function guesty_booking_quote_form_styles()
{
    ?>
        <style>
            .payment {
                display: flex;
                margin-bottom: 24px;
                padding: inherit;
                gap: 16px;
                justify-content: center;
            }

            .payment button {
                padding: 8px 24px;
                border: none;
                border-radius: 8px;
                font-size: large;
            }

            .pay-check {
                background-color: #5CB85C;
                color: white;
            }

            .pay-cancel {
                background-color: red !important;
            }

            .pay-cancel:hover {
                cursor: pointer;
                background-color: darkred !important;
            }

            .pay-check:hover {
                cursor: pointer;
                background-color: #3d8b3d !important;
            }

            .pay-cancel {
                background-color: #5CB85C;
                color: white;
            }

            .no-data-message {
                text-align: center;
                font-size: 20px;
                color: red;
                margin: 50px 0;
            }

            .modal {
                display: none;
                position: fixed;
                z-index: 1;
                padding-top: 100px;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0, 0, 0, 0.4);
            }

            .modal-content {
                position: relative;
                background-color: #fefefe;
                margin: auto;
                padding: 0;
                border: 1px solid #888;
                border-radius: 10px;
                width: 36%;
                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                animation: animatetop 0.4s;
            }

            .modal-main {
                width: 60%;
                margin: 16px auto;
                font-size: larger;
                line-height: 32px;
            }

            @keyframes animatetop {
                from {
                    top: -300px;
                    opacity: 0
                }

                to {
                    top: 0;
                    opacity: 1
                }
            }

            .modal-header {
                padding: 16px;
                background-color: #5cb85c;
                color: white;
                border-top-left-radius: 10px;
                border-top-right-radius: 10px;
            }

            .modal-red {
                background-color: red;
            }

            .modal-body {
                padding: 16px;
            }

            .modal-body p {
                margin-bottom: 16px;
            }

            .modal-body p strong {
                display: inline-block;
                width: 100px;
            }

            .close {
                color: white;
                float: right;
                font-size: 28px;
                font-weight: bold;
            }

            .close:hover,
            .close:focus {
                color: #000;
                text-decoration: none;
                cursor: pointer;
            }
        </style>
    <?php
}
add_action('wp_head', 'guesty_booking_quote_form_styles');

// Internal
function guesty_encrypt_callback($value)
{
    return guesty_encrypt($value);
}

function guesty_encrypt($data)
{
    $key = guesty_get_encryption_key();
    return Crypto::encrypt($data, $key);
}

function guesty_decrypt($data)
{
    $key = guesty_get_encryption_key();
    return Crypto::decrypt($data, $key);
}

function guesty_get_encryption_key()
{
    $key = get_option('guesty_encryption_key');
    if (!$key) {
        $key = Key::createNewRandomKey()->saveToAsciiSafeString();
        update_option('guesty_encryption_key', $key);
    }
    try {
        $key = get_option('guesty_encryption_key');
        return Key::loadFromAsciiSafeString($key);
    } catch (BadFormatException $e) {
        var_dump($e);
        error_log('Failed to load encryption key: ' . $e->getMessage());
        return null;
    }
}
