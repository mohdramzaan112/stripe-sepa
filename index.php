<!DOCTYPE html>
<html lang="en">
<head>
    <title>sepa</title>
    <link rel="stylesheet" href="style.css">
    <!-- jQuery is used only for this example; it isn't required to use Stripe -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <style>
        body {background: aliceblue;}
        form {background: white; padding:20px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);}
    </style>
</head>
<body>
<script src="https://js.stripe.com/v3/"></script>
<div class="container">
    <h1 class="text-center m-3">SEPA Direct Debit ( IBAN )</h1>
    <form action="charge.php" method="post" id="payment-form" class="form">
        <label for="name">Name</label>
        <input id="name" name="name" placeholder="Jenny Rosen" required="" class="form-control">
        
        <label for="email">Email </label>
        <input id="email" name="email" type="email" placeholder="jenny.rosen@example.com" required="" class="form-control">
        
        <label for="iban-element">IBAN</label>
        <div id="iban-element" class="StripeElement StripeElement--empty form-control"></div>
        
        <div id="bank-name" class="text-success pt-3"></div><br>

        <button class="btn btn-info">Submit Payment</button><br>

        <!-- Used to display form errors. -->
        <div id="error-message" role="alert" class="text-danger pt-3"></div><br>

        <!-- Display mandate acceptance text. -->
        <div id="mandate-acceptance">
            <p>
            By providing your IBAN and confirming this payment, you are
        authorizing Rocketship Inc. and Stripe, our payment service
        provider, to send instructions to your bank to debit your account and
        your bank to debit your account in accordance with those instructions.
        You are entitled to a refund from your bank under the terms and
        conditions of your agreement with your bank. A refund must be claimed
        within 8 weeks starting from the date on which your account was debited.
            </p>
        </div>
    </form>
</div>
        
        <script>
        $(document).ready(function() { 
            // Create a Stripe client.
        // Note: this merchant has been set up for demo purposes.
        var stripe = Stripe('pk_test');

        // Create an instance of Elements.
        var elements = stripe.elements();

        // Custom styling can be passed to options when creating an Element.
        // (Note that this demo uses a wider set of styles than the guide below.)
        var style = {
        
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a',
            ':-webkit-autofill': {
            color: '#fa755a',
            },
        }
        };

        // Create an instance of the iban Element.
        var iban = elements.create('iban', {
            style: style,
            supportedCountries: ['SEPA'],
        });

        // Add an instance of the iban Element into the `iban-element` <div>.
        iban.mount('#iban-element');

        var errorMessage = document.getElementById('error-message');
        var bankName = document.getElementById('bank-name');

        iban.on('change', function(event) {
        // Handle real-time validation errors from the iban Element.
        if (event.error) {
            errorMessage.textContent = event.error.message;
            errorMessage.classList.add('visible');
        } else {
            errorMessage.classList.remove('visible');
        }

        // Display bank name corresponding to IBAN, if available.
        if (event.bankName) {
            bankName.textContent = event.bankName;
            bankName.classList.add('visible');
        } else {
            bankName.classList.remove('visible');
        }
        });

        // Handle form submission.
        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            //showLoading();

            var sourceData = {
                type: 'sepa_debit',
                currency: 'eur',
                owner: {
                    name: document.querySelector('input[name="name"]').value,
                    email: document.querySelector('input[name="email"]').value,
                },
                mandate: {
                // Automatically send a mandate notification email to your customer
                // once the source is charged.
                notification_method: 'email',
                }
            };

            // Call `stripe.createSource` with the iban Element and additional options.
            stripe.createSource(iban, sourceData).then(function(result) {console.log(result)
                if (result.error) {
                // Inform the customer that there was an error.
                errorMessage.textContent = result.error.message;
                errorMessage.classList.add('visible');
                //stopLoading();
                } else {
                // Send the Source to your server to create a charge.
                errorMessage.classList.remove('visible');
                stripeSourceHandler(result.source);
                }
            });
        });

        function stripeSourceHandler(source) { 
        // Insert the Source ID into the form so it gets submitted to the server.
        var form = document.getElementById('payment-form');
        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'stripeSource');
        hiddenInput.setAttribute('value', source.id);
        form.appendChild(hiddenInput);

        // Submit the form.
        form.submit();
        }
    });
    </script>
<div class="container m-5 text-center">
<pre>
IBAN Numbers for testing:

valid:
GB94BARC10201530093459
GB33BUKB20201555555555
DE89370400440532013000

invalid:
GB94BARC20201530093459
GB96BARC202015300934591
GB02BARC20201530093451
GB68CITI18500483515538
GB12BARC20201530093A59
</pre>
</div>
</body>
</html>