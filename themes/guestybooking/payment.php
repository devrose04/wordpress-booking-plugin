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

    .container {
        padding: 5rem 16rem;
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
</style>

<body>
    <form class="container" id="submit-payment">
        <div>
            <h2>Card holder name</h2>
            <div class="display-flex">
                <div>
                    <p>First Name</p>
                    <input type="text" placeholder="First name" name="firstName" id="firstName">
                </div>
                <div>
                    <p>Last Name</p>
                    <input type="text" placeholder="Last name" name="lastName" id="lastName">
                </div>
            </div>
        </div>
        <div>
            <h2>Payment details</h2>
            <div class="display-flex">
                <div>
                    <p>Credit card number</p>
                    <input type="text" placeholder="****-****-****-****" name="creditCard" id="creditCard">
                </div>
                <div class="display-flex">
                    <div>
                        <p>Expiration date</p>
                        <input type="date" placeholder="MM/YY" name="expiration" id="expiration">
                    </div>
                    <div>
                        <p>CVC</p>
                        <input type="text" placeholder="CVC" name="cvc" id="cvc">
                    </div>
                </div>
            </div>
        </div>
        <div>
            <h2>Billing address</h2>
            <div class="display-flex">
                <div>
                    <p>Street</p>
                    <input type="text" placeholder="Street" name="street" id="street">
                </div>
                <div>
                    <p>City</p>
                    <input type="text" placeholder="City" name="city" id="city">
                </div>
            </div>
        </div>
        <div>
            <button type="submit">Pay Now</button>
            <button>Go to Back</button>
        </div>
    </form>
    <?php
    ?>
</body>

</html>