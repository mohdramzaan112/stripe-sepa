<?php
//check whether stripe token is not empty
if(isset($_POST["stripeSource"]) && !empty($_POST["stripeSource"])) {
    //get token, card and user info from the form
    $source = $_POST['stripeSource'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    //include Stripe PHP library
    require_once('../stripe-php/init.php');
    //set api key
    $stripe = array(
        "secret_key" => "sk_test_*******",
        "publishable_key" => "pk_test_*******"
    );
    
    \Stripe\Stripe::setApiKey('sk_test_*******');
    
    //add customer to stripe
    $customer = \Stripe\Customer::create(array(
        'email' => $email,
        'source' => $source
    ));
    
    //item information
    $itemName = "Premium Script";

    //charge a credit or a debit card
    $charge = \Stripe\Charge::create(array(
        'customer' => $customer->id,
        'amount' => 1099,
        'currency' => "eur",
        'source' => $source
    ));
    //retrieve charge details
    $chargeJson = $charge->jsonSerialize();

    //check whether the charge is successful
    if($chargeJson['amount_refunded'] == 0 && empty($chargeJson['failure_code'])  && $chargeJson['captured'] ==1 ) {
        //order details
        $amount = $chargeJson['amount'];
        $balance_transaction = $chargeJson['balance_transaction'];
        $currency = $chargeJson['currency'];
        $payment_status = $chargeJson['status'];
        $source_status = $chargeJson['source']['status'];
        $stripe_source = $chargeJson['source']['id'];
        $payment_id = $chargeJson['id'];
        $client_secret = $chargeJson['source']['client_secret'];
        $response = json_encode($chargeJson);
        $date = date("Y-m-d H:i:s");
        
        //include database config file
        include_once '../dbConfig.php';

        //insert transaction data into the database
        $sql = "INSERT INTO sepa_orders(name,email,item_name,currency,amount,payment_status,source_status,stripe_source,payment_id,client_secret,response,created)VALUES('".$name."','".$email."','".$itemName."','".$currency."','".$amount."','".$payment_status."','".$source_status."','".$stripe_source."','".$payment_id."','".$client_secret."','".$response."','".$date."')";

        $insert = $db->query($sql);
        $last_insert_id = $db->insert_id;
        //if order inserted successfully
        if($last_insert_id && $payment_status == 'pending' || $payment_status == 'succeeded') {
            $statusMsg = "The transaction successful.<h4>Order ID: {$last_insert_id}</h4>";
        } else {
            $statusMsg = "Transaction has been failed";
        }
    } else {
        $statusMsg = "Transaction has been failed";
    }
} else {
    $statusMsg = "Form submission error.....";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>charge</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container text-center">
        <h2 class="text-success text-center m-5"><?php echo $statusMsg; ?></h2>
        <a href="index.php" class="btn btn-dark btn-lg">Back</a>
    </div>
</body>
</html>