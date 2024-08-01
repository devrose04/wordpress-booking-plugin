<?php get_header(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
</head>

<style>
    /* style.css */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f7f7f7;
    }

    h2 {
        font-size: 1.5em;
        margin-bottom: 10px;
    }

    .btn-container {
        margin: 2rem 16rem;
        background-color: #bfb8b8;
        cursor: pointer;
    }

    .display-flex {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .display-flex>div {
        flex: 1;
        margin-right: 10px;
    }

    .display-flex>div:last-child {
        margin-right: 0;
    }

    div>div {
        margin-bottom: 15px;
    }

    p {
        margin: 5px 0;
    }

    input,
    input[type="date"] {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 1em;
    }

    input::placeholder {
        color: #aaa;
    }

    input:focus {
        outline: 1px solid grey;
    }

    @media (max-width: 768px) {
        .display-flex {
            flex-direction: column;
        }

        .display-flex>div {
            margin-right: 0;
        }
    }

    button {
        padding: 8px 24px;
        border: none;
        border-radius: 8px;
        font-size: large;
    }

    .paid {
        background-color: #5CB85C;
        color: white;
    }

    .canceled {
        background-color: red !important;
    }

    .paid:hover {
        cursor: pointer;
        background-color: #3d8b3d !important;
    }

    .canceled:hover {
        cursor: pointer;
        background-color: darkred !important;
    }
</style>
<?php
$firstName  = isset($_POST['firstName']) ? $_POST['firstName'] : "";
$lastName   = isset($_POST['lastName']) ? $_POST['lastName'] : "";
$creditCard = isset($_POST['creditCard']) ? $_POST['creditCard'] : "";
$expiration = isset($_POST['expiration']) ? $_POST['expiration'] : "";
$cvc        = isset($_POST['cvc']) ? $_POST['cvc'] : "";
$street     = isset($_POST['street']) ? $_POST['street'] : "";
$city       = isset($_POST['city']) ? $_POST['city'] : "";
$country    = isset($_POST['country']) ? $_POST['country'] : "";
$state      = isset($_POST['state']) ? $_POST['state'] : "";
$zipcode    = isset($_POST['zipcode']) ? $_POST['zipcode'] : "";
?>

<body>
    <button class="btn-container" id='btn_back'>Back</button>
    <form method="post" class="container" id="submit-payment" style="margin-bottom: 4rem;">
        <div>
            <h2>Card holder name</h2>
            <div class="display-flex">
                <div>
                    <p>First Name</p>
                    <input required type="text" placeholder="First name" name="firstName" id="firstName" value="<?php echo esc_attr($firstName); ?>">
                </div>
                <div>
                    <p>Last Name</p>
                    <input required type="text" placeholder="Last name" name="lastName" id="lastName" value="<?php echo esc_attr($lastName); ?>">
                </div>
            </div>
        </div>
        <div>
            <h2>Payment details</h2>
            <div class="display-flex">
                <div>
                    <p>Credit card number</p>
                    <input required type="text" placeholder="****-****-****-****" name="creditCard" id="creditCard" value="<?php echo esc_attr($creditCard); ?>" oninput="formatCreditCard(this)">
                </div>
                <div class="display-flex">
                    <div>
                        <p>Expiration date</p>
                        <input required type="date" placeholder="MM/YY" name="expiration" id="expiration" value="<?php echo esc_attr($expiration); ?>">
                    </div>
                    <div>
                        <p>CVC</p>
                        <input required type="text" placeholder="CVC" name="cvc" id="cvc" value="<?php echo esc_attr($cvc); ?>">
                    </div>
                </div>
            </div>
        </div>
        <div>
            <h2>Billing address</h2>
            <div class="display-flex">
                <div>
                    <p>Street</p>
                    <input required type="text" placeholder="Street" name="street" id="street" value="<?php echo esc_attr($street); ?>">
                </div>
                <div>
                    <p>City</p>
                    <input required type="text" placeholder="City" name="city" id="city" value="<?php echo esc_attr($city); ?>">
                </div>
            </div>
            <div class="display-flex">
                <div>
                    <p>Country</p>
                    <input required type="text" placeholder="Country" name="country" id="country" value="<?php echo esc_attr($country); ?>">
                </div>
                <div>
                    <p>State</p>
                    <input required type="text" placeholder="State" name="state" id="state" value="<?php echo esc_attr($state); ?>">
                </div>
                <div>
                    <p>Zip code</p>
                    <input required type="text" placeholder="Zip code" name="zipcode" id="zipcode" value="<?php echo esc_attr($zipcode); ?>">
                </div>
            </div>
        </div>
        <div>
            <button type="submit" class="paid">Pay Now</button>
            <button class="canceled">Go to Back</button>
        </div>
    </form>
    <input type="hidden" id="provider" value="<?php echo $paymentProviderId; ?>">
    <div id="guesty-tokenization-container"></div>
    <?php
    $price = isset($_GET['amount']) ? $_GET['amount'] : null;
    $listingId = isset($_GET['listingid']) ? $_GET['listingid'] : null;

    if (preg_match('/^(\d+)\s*(\w+)$/', $price, $matches)) {
        $amount = $matches[1];   // The numeric part
        $currency = $matches[2]; // The alphabetic part
    }

    $payment = new Guesty_API();
    $paymentProviderId = $payment->pay_provider($listingId)["providerAccountId"];
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $exp = new DateTime($_POST['expiration']);
        $number = preg_replace('/\D/', '', $_POST['creditCard']);
        $exp_month = $exp->format('m');
        $exp_year = $exp->format('Y');
        $cvc = $_POST['cvc'];
        $card = array(
            'number' => $number,
            'exp_month' => $exp_month,
            'exp_year' => $exp_year,
            'cvc' => $cvc
        );

        $name =  $_POST['firstName'] . " " . $_POST['lastName'];
        $city = $_POST['city'];
        $country = $_POST['country'];
        $line1 = $_POST['street'];
        $postal_code = $_POST['zipcode'];
        $billing_details = array(
            'name' => $name,
            'address' => array(
                'city' => $city,
                'country' => $country,
                'line1' => $line1,
                'postal_code' => $postal_code
            )
        );

        $threeDS = array(
            'amount' => $amount,
            'currency' => $currency
        );
        $guestyPay = new Guesty_API();
        $paid = $guestyPay->payment_provider($listingId, $card, $billing_details, $threeDS);
        var_dump($paid);
    }
    ?>
</body>

<?php get_footer(); ?>

</html>

<script>
    var backward = document.getElementById('btn_back');
    backward.addEventListener('click', function(e) {
        e.preventDefault();
        var homeurl = '<?php echo home_url(); ?>';
        window.location.href = homeurl;
    })
</script>

<script type="module">
    import {
        loadScript
    } from "@guestyorg/tokenization-js";

    document.addEventListener("DOMContentLoaded", async function() {
        const containerId = "guesty-tokenization-container";
        const providerId = document.getElementById("provider").value; // Replace with your actual provider ID
        alert(providerId);

        try {
            // Load the Guesty Tokenization SDK
            const guestyTokenization = await loadScript();
            console.log("Guesty Tokenization JS SDK is loaded and ready to use");

            // Render the tokenization form
            await guestyTokenization.render({
                containerId: containerId,
                providerId: providerId,
            });
            console.log("Guesty Tokenization form rendered successfully");

            // Handle form submission
            document
                .getElementById("pay-now")
                .addEventListener("click", async function() {
                    try {
                        const paymentMethod = await guestyTokenization.submit();
                        console.log("Payment method received:", paymentMethod);
                        // Process payment method via Guesty's API
                    } catch (e) {
                        console.error("Failed to submit the Guesty Tokenization form", e);
                    }
                });
        } catch (error) {
            console.error(
                "Failed to load the Guesty Tokenization JS SDK script",
                error
            );
        }
    });

    function formatCreditCard(input) {
        const value = input.value.replace(/\D/g, ''); // Remove all non-digit characters
        const formattedValue = value.match(/.{1,4}/g)?.join('-') ?? value; // Group digits in sets of 4
        input.value = formattedValue;
    }

    function removeHyphens(value) {
        // Remove all hyphens
        return value.replace(/-/g, '');
    }
</script>