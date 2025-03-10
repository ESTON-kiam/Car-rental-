<?php

require_once 'include/db_connection.php';


class MpesaConfig {
    const CONSUMER_KEY = 'F1tuXfV73l8AUIXUVEdvQsRE7OJsRdg9kz22y67vCEG1TCul';
    const CONSUMER_SECRET = 'agskGrWUs4A9NwazyA6bRhk9fCUm5wDmGfoPA9RQjA5biDaOJckGIAAIkJPFH0uU';
    const PASSKEY = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
    const SHORTCODE = '174379';
    const CALLBACK_URL = 'https://655a-196-250-209-180.ngrok-free.app/callback_url.php';
    const SANDBOX_URL = 'https://sandbox.safaricom.co.ke';
    const PRODUCTION_URL = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
   
    const IS_SANDBOX = true;
    
    public static function getBaseUrl() {
        return self::IS_SANDBOX ? self::SANDBOX_URL : self::PRODUCTION_URL;
    }
}

class MpesaPaymentController {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
  
    private function getAccessToken() {
        $credentials = base64_encode(MpesaConfig::CONSUMER_KEY . ':' . MpesaConfig::CONSUMER_SECRET);
        $url = MpesaConfig::getBaseUrl() . '/oauth/v1/generate?grant_type=client_credentials';
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
       
        if($response === false) {
            error_log('cURL Error: ' . curl_error($curl));
            throw new Exception('Failed to connect to M-Pesa API: ' . curl_error($curl));
        }
        
        curl_close($curl);
        
        $result = json_decode($response);
        
       
        if(json_last_error() !== JSON_ERROR_NONE || !isset($result->access_token)) {
            error_log('M-Pesa API Error: ' . $response);
            throw new Exception('Invalid response from M-Pesa API. Please check your credentials.');
        }
        
        return $result->access_token;
    }
    
   
    public function initiatePayment($bookingId, $phoneNumber, $amount, $paymentType) {
        try {
            // Format phone number
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);
            
            // Amount validation and formatting
            $amount = (int)$amount; // Ensure integer value
            
            // Validate amount range
            if ($amount < 1 || $amount > 150000) {
                throw new Exception('Amount must be between 1 and 150,000 KES');
            }
            
            $timestamp = date('YmdHis');
            
            $password = base64_encode(MpesaConfig::SHORTCODE . MpesaConfig::PASSKEY . $timestamp);
            
            $stmt = $this->conn->prepare("INSERT INTO payments (booking_id, transaction_type, amount, mpesa_phone_number, payment_status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("isds", $bookingId, $paymentType, $amount, $phoneNumber);
            $stmt->execute();
            $paymentId = $stmt->insert_id;
            $stmt->close();
            
            $url = MpesaConfig::getBaseUrl() . '/mpesa/stkpush/v1/processrequest';
            $accessToken = $this->getAccessToken();
            
            $stkPushData = array(
                'BusinessShortCode' => MpesaConfig::SHORTCODE,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => $amount,
                'PartyA' => $phoneNumber,
                'PartyB' => MpesaConfig::SHORTCODE,
                'PhoneNumber' => $phoneNumber,
                'CallBackURL' => MpesaConfig::CALLBACK_URL . '?payment_id=' . $paymentId,
                'AccountReference' => 'Booking #' . $bookingId,
                'TransactionDesc' => 'Payment for car booking'
            );
            
            $data_string = json_encode($stkPushData);
            
            $logStmt = $this->conn->prepare("INSERT INTO payment_logs (payment_id, request_type, request_data) VALUES (?, 'STK_PUSH', ?)");
            $logStmt->bind_param("is", $paymentId, $data_string);
            $logStmt->execute();
            $logId = $logStmt->insert_id;
            $logStmt->close();
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $accessToken));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            if($response === false) {
                $error = curl_error($curl);
                curl_close($curl);
                throw new Exception('Failed to connect to M-Pesa STK API: ' . $error);
            }
            
            curl_close($curl);
            
            $updateLogStmt = $this->conn->prepare("UPDATE payment_logs SET response_data = ? WHERE log_id = ?");
            $updateLogStmt->bind_param("si", $response, $logId);
            $updateLogStmt->execute();
            $updateLogStmt->close();
            
            $result = json_decode($response);
            
            if(json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid response from M-Pesa STK API: ' . $response);
            }
            
            if($httpCode == 200 && isset($result->ResponseCode) && $result->ResponseCode == "0") {
                return array(
                    'success' => true,
                    'message' => 'STK Push initiated successfully. Please check your phone to complete the payment.',
                    'payment_id' => $paymentId
                );
            } else {
                $this->updatePaymentStatus($paymentId, 'failed');
                
                $errorMessage = isset($result->errorMessage) ? $result->errorMessage : 
                               (isset($result->ResponseDescription) ? $result->ResponseDescription : 'Please try again later.');
                
                return array(
                    'success' => false,
                    'message' => 'Failed to initiate payment. ' . $errorMessage,
                    'payment_id' => $paymentId
                );
            }
        } catch (Exception $e) {
            error_log('Payment initiation error: ' . $e->getMessage());
            
            if(isset($paymentId)) {
                $this->updatePaymentStatus($paymentId, 'failed');
            }
            
            return array(
                'success' => false,
                'message' => 'Failed to initiate payment: ' . $e->getMessage(),
                'payment_id' => $paymentId ?? null
            );
        }
    }
    
    public function handleCallback($paymentId, $callbackData) {
        try {
            $callbackJson = json_encode($callbackData);
            $logStmt = $this->conn->prepare("INSERT INTO payment_logs (payment_id, request_type, request_data) VALUES (?, 'CALLBACK', ?)");
            $logStmt->bind_param("is", $paymentId, $callbackJson);
            $logStmt->execute();
            $logStmt->close();
            
            if(isset($callbackData['Body']['stkCallback']['ResultCode']) && $callbackData['Body']['stkCallback']['ResultCode'] == 0) {
                $metadata = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'];
                $mpesaTransactionId = '';
                
                foreach($metadata as $item) {
                    if($item['Name'] == 'MpesaReceiptNumber') {
                        $mpesaTransactionId = $item['Value'];
                        break;
                    }
                }
                
                $stmt = $this->conn->prepare("UPDATE payments SET payment_status = 'completed', mpesa_transaction_id = ?, callback_metadata = ? WHERE payment_id = ?");
                $stmt->bind_param("ssi", $mpesaTransactionId, $callbackJson, $paymentId);
                $stmt->execute();
                $stmt->close();
                
                $paymentStmt = $this->conn->prepare("SELECT booking_id, transaction_type, amount FROM payments WHERE payment_id = ?");
                $paymentStmt->bind_param("i", $paymentId);
                $paymentStmt->execute();
                $result = $paymentStmt->get_result();
                $payment = $result->fetch_assoc();
                $paymentStmt->close();
                
                if($payment) {
                    $this->updateBookingAfterPayment($payment['booking_id'], $payment['transaction_type'], $payment['amount']);
                }
                
                return true;
            } else {
                $this->updatePaymentStatus($paymentId, 'failed');
                return false;
            }
        } catch (Exception $e) {
            error_log('Payment callback error: ' . $e->getMessage());
            $this->updatePaymentStatus($paymentId, 'failed');
            return false;
        }
    }
    
    public function updatePaymentStatus($paymentId, $status) {
        $stmt = $this->conn->prepare("UPDATE payments SET payment_status = ? WHERE payment_id = ?");
        $stmt->bind_param("si", $status, $paymentId);
        $stmt->execute();
        $stmt->close();
    }
    
    public function cancelPayment($paymentId) {
        $this->updatePaymentStatus($paymentId, 'canceled');
        return array(
            'success' => true,
            'message' => 'Payment has been canceled.'
        );
    }
    
    public function checkPaymentStatus($paymentId) {
        $stmt = $this->conn->prepare("SELECT payment_status FROM payments WHERE payment_id = ?");
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $payment = $result->fetch_assoc();
        $stmt->close();
        
        return $payment ? $payment['payment_status'] : 'unknown';
    }
    
    private function updateBookingAfterPayment($bookingId, $transactionType, $amount) {
        switch($transactionType) {
            case 'deposit':
                $stmt = $this->conn->prepare("UPDATE bookings SET advance_deposit = ?, due_payment = total_fare - ?, booking_status = 'active' WHERE booking_id = ?");
                $stmt->bind_param("ddi", $amount, $amount, $bookingId);
                $stmt->execute();
                $stmt->close();
                break;
                
            case 'full_payment':
                $stmt = $this->conn->prepare("UPDATE bookings SET total_fare = ?, advance_deposit = ?, due_payment = 0, due_payment_status = 'paid', booking_status = 'active' WHERE booking_id = ?");
                $stmt->bind_param("ddi", $amount, $amount, $bookingId);
                $stmt->execute();
                $stmt->close();
                break;
                
            case 'additional_charges':
                $stmt = $this->conn->prepare("UPDATE bookings SET additional_charges = ?, due_payment = due_payment + ? WHERE booking_id = ?");
                $stmt->bind_param("ddi", $amount, $amount, $bookingId);
                $stmt->execute();
                $stmt->close();
                break;
                
            case 'due_payment':
                $stmt = $this->conn->prepare("UPDATE bookings SET due_payment = 0, due_payment_status = 'paid' WHERE booking_id = ?");
                $stmt->bind_param("i", $bookingId);
                $stmt->execute();
                $stmt->close();
                break;
        }
    }
    
    private function formatPhoneNumber($phone) {
        $phone = preg_replace('/\D/', '', $phone);
        
        if(strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
            $phone = '254' . substr($phone, 1);
        } elseif(strlen($phone) == 9) {
            $phone = '254' . $phone;
        }
        
        return $phone;
    }
}
?>