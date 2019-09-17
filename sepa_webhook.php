<?php 
include_once '../dbConfig.php';
//include Stripe PHP library
require_once('./stripe-php/init.php');

// Set your secret key: remember to change this to your live secret key in production
// See your keys here: https://dashboard.stripe.com/account/apikeys
\Stripe\Stripe::setApiKey('sk_test_***');

// You can find your endpoint's secret in your webhook settings
$endpoint_secret = 'whsec_***';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;
//file_put_contents('event_json.txt', $payload);

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    echo "Invalid payload";
    http_response_code(400);
    exit();
} catch(\Stripe\Error\SignatureVerification $e) {
    // Invalid signature
    echo "Invalid signature";
    http_response_code(400);
    exit();
}

$source_id = $event->data->object->source->id;
$balance_transaction = $event->data->object->balance_transaction;

// Handle the event

switch ($event->type) {
    case 'payment_intent.succeeded':
        $paymentIntent = $event->data->object; // contains a StripePaymentIntent
        handlePaymentIntentSucceeded($paymentIntent);
        break;
    case 'charge.succeeded':
        $sql = "UPDATE `sepa_orders` SET payment_status='succeeded', transaction_id='".$balance_transaction."' WHERE stripe_source='".$source_id."';";
        $insert = $db->query($sql);
        break;
    case 'charge.failed':
        $sql = "UPDATE `sepa_orders` SET payment_status='failed' WHERE stripe_source='".$source_id."';";
        $insert = $db->query($sql);
        break;
    case 'source.chargeable':
        $sql = "UPDATE `sepa_orders` SET source_status='chargeable' WHERE stripe_source='".$source_id."';";
        $insert = $db->query($sql);
        break;
    case 'payment_method.attached':
        $paymentMethod = $event->data->object; // contains a StripePaymentMethod
        handlePaymentMethodAttached($paymentMethod);
        break;
    // ... handle other event types
    default:
        // Unexpected event type
        http_response_code(400);
        exit();
}

http_response_code(200);
?>
