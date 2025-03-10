<?php
require_once 'include/db_connection.php';
require_once 'MpesaPaymentController.php';

// Get payment ID from URL parameter
$paymentId = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;

// Validate payment ID
if($paymentId <= 0) {
    file_put_contents('mpesa_error.log', date('Y-m-d H:i:s') . ' - Invalid payment ID: ' . $paymentId . PHP_EOL, FILE_APPEND);
    http_response_code(400);
    echo json_encode(['ResponseCode' => '1', 'ResponseDesc' => 'Invalid payment ID']);
    exit;
}

// Get callback data from request body
$callbackData = json_decode(file_get_contents('php://input'), true);

// Log the callback data
file_put_contents('mpesa_callback.log', date('Y-m-d H:i:s') . ' - Payment ID: ' . $paymentId . 
                 ' Data: ' . file_get_contents('php://input') . PHP_EOL, FILE_APPEND);

if(!$callbackData) {
    file_put_contents('mpesa_error.log', date('Y-m-d H:i:s') . ' - Invalid callback data for payment ID: ' . $paymentId . PHP_EOL, FILE_APPEND);
    http_response_code(400);
    echo json_encode(['ResponseCode' => '1', 'ResponseDesc' => 'Invalid callback data']);
    exit;
}

// Process the payment callback
$mpesaPayment = new MpesaPaymentController($conn);
$result = $mpesaPayment->handleCallback($paymentId, $callbackData);

// Return response
if($result) {
    http_response_code(200);
    echo json_encode(['ResponseCode' => '0', 'ResponseDesc' => 'Success']);
} else {
    http_response_code(200); // Still return 200 to Safaricom to acknowledge receipt
    echo json_encode(['ResponseCode' => '1', 'ResponseDesc' => 'Failed']);
}
?>