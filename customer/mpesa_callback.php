<?php
require_once 'include/db_connection.php';
require_once 'MpesaPaymentController.php';


$callbackData = file_get_contents('php://input');
file_put_contents('mpesa_callback_log.txt', date('Y-m-d H:i:s') . " - " . $callbackData . PHP_EOL, FILE_APPEND);


$callbackJson = json_decode($callbackData);

if (isset($callbackJson->Body->stkCallback)) {
    $callback = $callbackJson->Body->stkCallback;
    $merchantRequestID = $callback->MerchantRequestID;
    $checkoutRequestID = $callback->CheckoutRequestID;
    $resultCode = $callback->ResultCode;
    $resultDesc = $callback->ResultDesc;
    
    
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        logError("Database connection failed: " . $conn->connect_error);
        exit;
    }
    
    
    $stmt = $conn->prepare("SELECT id, booking_id, payment_type FROM mpesa_payments WHERE checkout_request_id = ? AND status = 'PENDING'");
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
                    if ($item->Name == "MpesaReceiptNumber") {
                        $mpesaReceiptNumber = $item->Value;
                    } elseif ($item->Name == "Amount") {
                        $transactionAmount = $item->Value;
                    } elseif ($item->Name == "PhoneNumber") {
                        $phoneNumber = $item->Value;
                    }
                }
            }
            
            
            $stmt = $conn->prepare("UPDATE mpesa_payments SET 
                status = ?, 
                result_description = ?, 
                mpesa_receipt = ?, 
                transaction_amount = ?, 
                transaction_date = NOW(),
                updated_at = NOW() 
                WHERE id = ?");
                
            $stmt->bind_param("sssdi", $status, $resultDesc, $mpesaReceiptNumber, $transactionAmount, $paymentId);
            
            if (!$stmt->execute()) {
                logError("Failed to update payment record: " . $stmt->error);
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
            
            
            $stmt = $conn->prepare("UPDATE mpesa_payments SET 
                status = ?, 
                result_description = ?, 
                updated_at = NOW() 
                WHERE id = ?");
            $stmt->bind_param("ssi", $status, $resultDesc, $paymentId);
            
            if (!$stmt->execute()) {
                logError("Failed to update payment status: " . $stmt->error);
            }
            $stmt->close();
        }
        
        
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Callback received successfully']);
    } else {
        logError("Payment record not found for CheckoutRequestID: " . $checkoutRequestID);
        echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Payment record not found']);
    }
    
    $conn->close();
} else {
    logError("Invalid callback data received");
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data']);
}

function logError($message) {
    file_put_contents('mpesa_error_log.txt', date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}