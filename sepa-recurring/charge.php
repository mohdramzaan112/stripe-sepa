<?php 
//include stripe library
require "./stripe-php/init.php";
//set api keys
\Stripe\Stripe::setApiKey('sk_test_mNzN7aUZG9VWciTmKVsoM6xj');
//initialize variables
$name = 'Habeeb';
$email = 'abc@gmail.com';
$iban = 'DE89370400440532013000';
$amount = 31;

//create source object
$source_obj = \Stripe\Source::create([
    'type' => 'sepa_debit',
    'sepa_debit' => ['iban' => $iban],
    'currency' => 'EUR',
    'owner' => [
        'name' => $name
    ]
]);

//create customer
$customer = \Stripe\Customer::create([
    'email' => $email,
    'source' => $source_obj->id
]);
//create charge object
$charge = \Stripe\Charge::create([
    'customer' => $customer->id,
    'amount' => $amount*100,
    'currency' => 'EUR',
    'source' => $source_obj->id
]);

if($charge->status == 'succeeded' || $charge->status == 'pending') { ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>success</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container text-center">
        <h2 class="text-success">Payment Succeeded</h2>
        <a href="index.php" class="btn btn-dark">Back to Home</a>
    </div>
</body>
</html>

<?php
}
?>











