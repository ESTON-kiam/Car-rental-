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


    $status = 'FAILED';
    switch ((string)$resultCode) {
        case '0':
            $status = 'COMPLETED';
            break;
        case '1032':
            $status = 'CANCELLED';
            break;
        case '1037':
            $status = 'TIMEOUT';
            break;
        default:
            $status = 'FAILED';
    }

   
    $mpesaReceiptNumber = null;
    $transactionDate = null;
    $phoneNumber = null;
    $amount = null;

    if (isset($callback->CallbackMetadata) && isset($callback->CallbackMetadata->Item)) {
        foreach ($callback->CallbackMetadata->Item as $item) {
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

   
    $stmt = $conn->prepare("UPDATE mpesa_payments SET 
        result_code = ?,
        result_description = ?, 
        mpesa_receipt_number = ?, 
        transaction_date = ?,
        updated_at = NOW(),
        {$paymentType}_status = ?
        WHERE id = ?");
    
    $stmt->bind_param("sssssi", 
        $resultCode, 
        $resultDesc, 
        $mpesaReceiptNumber, 
        $transactionDate, 
        $status, 
        $paymentId
    );

    if (!$stmt->execute()) {
        logError("Failed to update payment record: " . $stmt->error);
    } else {
        file_put_contents('mpesa_callback_log.txt', date('Y-m-d H:i:s') . " - Payment record updated successfully for PaymentID: $paymentId, PaymentType: $paymentType, Status: $status" . PHP_EOL, FILE_APPEND);
    }
    $stmt->close();

   
    if ($status == 'COMPLETED') {
        $mpesaPayment = new MpesaPaymentController($conn);
        $updateResult = $mpesaPayment->updateBookingStatus($bookingId, $paymentType);
        file_put_contents('mpesa_callback_log.txt', date('Y-m-d H:i:s') . " - Booking status update result: " . ($updateResult ? "Success" : "Failed") . PHP_EOL, FILE_APPEND);
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