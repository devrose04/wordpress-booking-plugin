    <?php
    get_header();
    require 'vendor/autoload.php'; // Path to Stripe PHP library

    $stripe_key = 'sk_test_51PhpdsKXUqLENGZVLwVQcdxhnuFsiiWTN6D9b9CtG8caokIgjJZDAnmp0Tv5SrAG8chsDc08Ge8woWle2B5nSeJe00HDrWsITe';
    \Stripe\Stripe::setApiKey($stripe_key); // Replace with your Stripe secret key

    // Replace '%20' with a space for clarity
    $amountString = isset($_GET['amount']) ? $_GET['amount'] : null;
    if (isset($_GET['amount'])) {
        $amountString = str_replace('%20', ' ', $amountString);

        // Split the string by the space
        $parts = explode(' ', $amountString);

        // Assigning parts to variables
        $amount = $parts[0];
        $currency = $parts[1];
    }

    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['stripeToken'];
        $stripe = new \Stripe\StripeClient($stripe_key);
        try {
            $charge = $stripe->charges->create([
                'amount' => $amount,
                'currency' => $currency,
                'source' => $token,
                'description' => 'Example charge',
            ]);

            // Payment successful
            $message = 'Payment successful!';
        } catch (\Stripe\Exception\CardException $e) {
            // Payment failed
            $message = 'Payment failed: ' . $e->getError()->message;
        }
    }
    ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
</head>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 200px;
        padding: 0;
        background-color: #f7f7f7;
    }

    .container {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
        font-size: 1.5em;
        margin-bottom: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 1em;
    }

    .form-group input:focus {
        outline: 1px solid #007bff;
    }

    .form-group #card-element {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .form-group .error {
        color: red;
        margin-top: 5px;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        background-color: #007bff;
        color: #fff;
        font-size: 1em;
        cursor: pointer;
    }

    .btn:hover {
        background-color: #0056b3;
    }

    .btn-secondary {
        background-color: #6c757d;
        margin-right: 10px;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    .message {
        display: inline-block;
        margin-left: 20px;
        font-size: 1em;
    }

    .success {
        color: green;
    }

    .error {
        color: red;
    }
</style>

<body>
    <div class="container">
        <h2>Payment Details</h2>
        <form id="payment-form" method="post">
            <div class="form-group">
                <label for="cardholder-name">Cardholder Name</label>
                <input type="text" id="cardholder-name" name="cardholder-name" required>
            </div>
            <div class="form-group">
                <label for="card-element">Credit or Debit Card</label>
                <div id="card-element">
                    <!-- A Stripe Element will be inserted here. -->
                </div>
                <!-- Used to display form errors. -->
                <div id="card-errors" role="alert" class="error"></div>
            </div>
            <button type="submit" id="pay-submit" class="btn">Pay Now</button>
            <button type="button" class="btn btn-secondary" id="btn_back">Back</button>
            <span id="payment-message" class="message <?php if(strpos($message, "success")) echo "success"; else echo "error"; ?>">
                <?php echo $message; ?>
            </span>
        </form>
    </div>

    <script src="https://js.stripe.com/v3/"></script>

    <script>
        var publicKey = 'pk_test_51PhpdsKXUqLENGZVsIfi9Kvei6ebJZJfVMXysrLQJAO0QrByAkGYllrARbGO3LmAs25HeCrEYG9ZsoWzBPpVgIOO00UGcdfA3F';

        var stripe = Stripe(publicKey);
        var elements = stripe.elements();

        var style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        var card = elements.create('card', {
            style: style
        });
        card.mount('#card-element');

        card.on('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    stripeTokenHandler(result.token);
                }
            });
        });

        function stripeTokenHandler(token) {
            var form = document.getElementById('payment-form');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);

            // Add a hidden input for storing the redirect message
            var messageInput = document.createElement('input');
            messageInput.setAttribute('type', 'hidden');
            messageInput.setAttribute('name', 'paymentMessage');
            form.appendChild(messageInput);

            // Submit the form
            form.submit();
        }

        // After form submission, handle the response
        document.addEventListener('DOMContentLoaded', function() {
            var paymentMessage = document.getElementById('payment-message').textContent;
            if (paymentMessage.trim() !== "") {

                setTimeout(function() {
                    window.location.href = '<?php echo home_url(); ?>';
                }, 3000);
            }
        });


        var backward = document.getElementById('btn_back');
        backward.addEventListener('click', function(e) {
            e.preventDefault();
            var homeurl = '<?php echo home_url() . '/listing'; ?>';
            window.location.href = homeurl;
        });
    </script>


</body>

</html>


