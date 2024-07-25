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
        // var_dump($guesty_api->get_token());
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
            $building_name = $data['propertyType'] ?? 'No building';
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


    $buildingName = $response['propertyType'] ?? "No name";
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
                                    echo '<td class="acm-size">' . ($acmIdx ? $selectedAcm : ($i ? 'Apartment' : 'Villa')) . ' ' . $idx . '</td>';
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
    $guesty_api = new Guesty_API();
    $response = $guesty_api->fetch_guesty_list_data();
    $list_data = $response['results'];

    // Retain the submitted values
    $guests = isset($_POST['guests']) ? $_POST['guests'] : '';
    $listing_id = isset($_POST['id']) ? $_POST['id'] : '';
    $check_in = isset($_POST['checkIn']) ? $_POST['checkIn'] : '';
    $check_out = isset($_POST['checkOut']) ? $_POST['checkOut'] : '';

?>
    <div class="quote-form-container">
        <form id="guesty-booking-quote-form" method="post">
            <div class="form-group">
                <label for="guestsCount">Guests Count:</label>
                <input type="number" id="guestsCount" name="guests" value="<?php echo esc_attr($guests); ?>" required>
            </div>
            <div class="form-group">
                <label for="listingId">Listing ID:</label>
                <select name="id" id="listingId">
                    <?php
                    $idxVilla = 1;
                    $idxApart = 1;
                    foreach ($list_data as $data) {
                        $selected = ($data['_id'] == $listing_id) ? 'selected' : '';
                        if ($data['propertyType'] == "Villa") {
                            echo "<option value='" . esc_attr($data['_id']) . "' $selected>" . esc_html($data['propertyType'] . " " . $idxVilla . ": " . $data['_id']) . "</option>";
                            $idxVilla++;
                        } else {
                            echo "<option value='" . esc_attr($data['_id']) . "' $selected>" . esc_html($data['propertyType'] . " " . $idxApart . ": " . $data['_id']) . "</option>";
                            $idxApart++;
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="checkInDate">Check-in Date:</label>
                <input type="date" id="checkInDate" name="checkIn" value="<?php echo esc_attr($check_in); ?>" required>
            </div>
            <div class="form-group">
                <label for="checkOutDate">Check-out Date:</label>
                <input type="date" id="checkOutDate" name="checkOut" value="<?php echo esc_attr($check_out); ?>" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Get Quote" class="btn-submit">
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('guesty_booking_quote_form', 'guesty_booking_quote_form');

function guesty_booking_handle_quote_form_submission()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['checkIn'] <= $_POST['checkOut'])) {
        // Sanitize and process the form data
        $count = sanitize_text_field($_POST['guests']);
        $id = sanitize_text_field($_POST['id']);
        $checkin = sanitize_text_field($_POST['checkIn']);
        $checkout = sanitize_text_field($_POST['checkOut']);

        $guesty_api = new Guesty_API();
        $result = $guesty_api->new_booking_data($id, $checkin, $checkout, $count);

        if ($result['status'] == 'success') {
            $invoice = $result['data']['rates']['ratePlans'][0]['ratePlan']['money']['invoiceItems'][0];
    ?>
            <div id="myModal" class="modal">
                <div class="modal-content">
                    <div id="modal-message">
                        <div class="modal-header">
                            <span class="close">&times;</span>
                            <h2>Successfully Booked</h2>
                        </div>
                        <div class="modal-main">
                            <p>Booked Villa: <strong><?php echo esc_html($result['data']['unitTypeId']); ?></strong></p>
                            <p>Period: <strong><?php echo esc_html($result['data']['checkInDateLocalized']); ?> ~ <?php echo esc_html($result['data']['checkOutDateLocalized']); ?></strong></p>
                            <p>Currency: <strong><?php echo esc_html($invoice['currency']); ?></strong></p>
                            <p>Amount: <strong><?php echo esc_html($invoice['amount']); ?></strong></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        } else {
        ?>
            <div id="myModal" class="modal">
                <div class="modal-content">
                    <div id="modal-message">
                        <div class="modal-header modal-red">
                            <span class="close">&times;</span>
                            <h2>Unavailable</h2>
                        </div>
                        <div class="modal-main">
                            <p>This villa is already booked.</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['checkIn'] > $_POST['checkOut']) {
        ?>
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div id="modal-message">
                    <p>Please input the dates correctly.</p>
                </div>
            </div>
        </div>
    <?php
    }
}
add_action('template_redirect', 'guesty_booking_handle_quote_form_submission');


// css
function guesty_booking_quote_form_styles()
{
    ?>
    <style>
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
            width: 50%;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            animation: animatetop 0.4s;
        }

        .modal-main {
            padding: 1rem 1rem 1rem 8rem;
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
        .modal-red { background-color: red; }

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

// js
function guesty_booking_quote_form_scripts()
{
?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('myModal');
            var span = document.getElementsByClassName('close')[0];

            span.onclick = function() {
                modal.style.display = 'none';
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }

            if (document.getElementById('modal-message')) {
                modal.style.display = 'block';
            }
        });
    </script>
<?php
}
add_action('wp_footer', 'guesty_booking_quote_form_scripts');


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
