<?php
require_once 'include/db_connection.php';
require_once 'MpesaPaymentController.php';


$callbackData = file_get_contents('php://input');
file_put_contents('mpesa_callback_log.txt', date('Y-m-d H:i:s') . " - Raw Callback Data: " . $callbackData . PHP_EOL, FILE_APPEND);


$callbackJson = json_decode($callbackData);


if (!isset($callbackJson->Body->stkCallback)) {
    logError("Invalid callback data: Missing stkCallback");
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data: Missing stkCallback']);
    exit;
}

if (!isset($callbackJson->Body->stkCallback->CheckoutRequestID)) {
    logError("Invalid callback data: Missing CheckoutRequestID");
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data: Missing CheckoutRequestID']);
    exit;
}


$callback = $callbackJson->Body->stkCallback;
$merchantRequestID = $callback->MerchantRequestID;
$checkoutRequestID = $callback->CheckoutRequestID;
$resultCode = $callback->ResultCode;
$resultDesc = $callback->ResultDesc;


file_put_contents('mpesa_callback_log.txt', date('Y-m-d H:i:s') . " - Callback Details: MerchantRequestID: $merchantRequestID, CheckoutRequestID: $checkoutRequestID, ResultCode: $resultCode, ResultDesc: $resultDesc" . PHP_EOL, FILE_APPEND);


$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
if ($conn->connect_error) {
    logError("Database connection failed: " . $conn->connect_error);
    exit;
}


storeFullCallback($conn, $callbackJson, $checkoutRequestID, $resultCode, $resultDesc);

$stmt = $conn->prepare("SELECT id, booking_id, payment_type FROM mpesa_payments WHERE checkout_request_id = ?");
$stmt->bind_param("s", $checkoutRequestID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $payment = $result->fetch_assoc();
    $paymentId = $payment['id'];
    $bookingId = $payment['booking_id'];
    $paymentType = $payment['payment_type'];
    $stmt->close();

    $mpesaPayment = new MpesaPaymentController($conn);

    if ($resultCode == 0) {
        $status = 'COMPLETED';
        $mpesaReceiptNumber = '';
        $transactionAmount = 0;
        $phoneNumber = '';

        
        if (isset($callback->CallbackMetadata) && isset($callback->CallbackMetadata->Item)) {
            foreach ($callback->CallbackMetadata->Item as $item) {
                if ($item->Name == "MpesaReceiptNumber" && isset($item->Value)) {
                    $mpesaReceiptNumber = $item->Value;
                } elseif ($item->Name == "Amount" && isset($item->Value)) {
                    $transactionAmount = $item->Value;
                } elseif ($item->Name == "PhoneNumber" && isset($item->Value)) {
                    $phoneNumber = $item->Value;
                }
            }
        } else {
            logError("CallbackMetadata missing or invalid for CheckoutRequestID: $checkoutRequestID");
        }

        
        file_put_contents('mpesa_callback_log.txt', date('Y-m-d H:i:s') . " - Payment Successful: MpesaReceiptNumber: $mpesaReceiptNumber, Amount: $transactionAmount, PhoneNumber: $phoneNumber" . PHP_EOL, FILE_APPEND);

        
        $statusField = $paymentType . '_status';
        $stmt = $conn->prepare("UPDATE mpesa_payments SET 
            $statusField = ?, 
            result_description = ?, 
            mpesa_receipt_number = ?, 
            transaction_date = NOW(),
            updated_at = NOW() 
            WHERE id = ?");
        $stmt->bind_param("sssi", $status, $resultDesc, $mpesaReceiptNumber, $paymentId);

        if (!$stmt->execute()) {
            logError("Failed to update payment record: " . $stmt->error);
        } else {
            file_put_contents('mpesa_callback_log.txt', date('Y-m-d H:i:s') . " - Payment record updated successfully for PaymentID: $paymentId" . PHP_EOL, FILE_APPEND);
        }
        $stmt->close();

        
        $mpesaPayment->updateBookingStatus($bookingId, $paymentType);
    } else {
        
        $status = 'FAILED';
        if ($resultCode == 1032) {
            $status = 'CANCELLED';
        } elseif ($resultCode == 1037) {
            $status = 'TIMEOUT';
        }

        
        file_put_contents('mpesa_callback_log.txt', date('Y-m-d H:i:s') . " - Payment Failed: Status: $status, ResultDesc: $resultDesc" . PHP_EOL, FILE_APPEND);

        
        $statusField = $paymentType . '_status';
        $stmt = $conn->prepare("UPDATE mpesa_payments SET 
            $statusField = ?, 
            result_description = ?, 
            updated_at = NOW() 
            WHERE id = ?");
        $stmt->bind_param("ssi", $status, $resultDesc, $paymentId);

        if (!$stmt->execute()) {
            logError("Failed to update payment status: " . $stmt->error);
        } else {
            file_put_contents('mpesa_callback_log.txt', date('Y-m-d H:i:s') . " - Payment status updated to $status for PaymentID: $paymentId" . PHP_EOL, FILE_APPEND);
        }
        $stmt->close();
    }

    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Callback received successfully']);
} else {
    logError("Payment record not found for CheckoutRequestID: $checkoutRequestID");
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Payment record not found']);
}

$conn->close();


function storeFullCallback($conn, $callbackJson, $checkoutRequestID, $resultCode, $resultDesc) {
    $mpesaReceiptNumber = null;
    $transactionDate = null;
    $phoneNumber = null;
    $amount = null;
    
    
    if (isset($callbackJson->Body->stkCallback->CallbackMetadata) && 
        isset($callbackJson->Body->stkCallback->CallbackMetadata->Item)) {
            
        foreach ($callbackJson->Body->stkCallback->CallbackMetadata->Item as $item) {
            if ($item->Name == "MpesaReceiptNumber" && isset($item->Value)) {
                $mpesaReceiptNumber = $item->Value;
            } elseif ($item->Name == "Amount" && isset($item->Value)) {
                $amount = $item->Value;
            } elseif ($item->Name == "PhoneNumber" && isset($item->Value)) {
                $phoneNumber = $item->Value;
            } elseif ($item->Name == "TransactionDate" && isset($item->Value)) {
                
                $dateStr = (string)$item->Value;
                if (strlen($dateStr) >= 14) {
                    $transactionDate = substr($dateStr, 0, 4) . '-' . 
                                      substr($dateStr, 4, 2) . '-' . 
                                      substr($dateStr, 6, 2) . ' ' . 
                                      substr($dateStr, 8, 2) . ':' . 
                                      substr($dateStr, 10, 2) . ':' . 
                                      substr($dateStr, 12, 2);
                }
            }
        }
    }
    
    
    $fullResponse = json_encode($callbackJson);
    $stmt = $conn->prepare("INSERT INTO mpesa_callbacks 
                           (checkout_request_id, result_code, result_desc, 
                            mpesa_receipt_number, transaction_date, phone_number, 
                            amount, full_response) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssssds", 
                     $checkoutRequestID, 
                     $resultCode, 
                     $resultDesc, 
                     $mpesaReceiptNumber, 
                     $transactionDate, 
                     $phoneNumber, 
                     $amount, 
                     $fullResponse);
    
    if (!$stmt->execute()) {
        logError("Failed to store callback data: " . $stmt->error);
    }
    
    $stmt->close();
}


function logError($message) {
    file_put_contents('mpesa_error_log.txt', date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}