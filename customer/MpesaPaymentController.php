<?php
require_once 'include/db_connection.php';

class MpesaPaymentController {
    private $conn;
    private $consumerKey = 'F1tuXfV73l8AUIXUVEdvQsRE7OJsRdg9kz22y67vCEG1TCul';
    private $consumerSecret = 'agskGrWUs4A9NwazyA6bRhk9fCUm5wDmGfoPA9RQjA5biDaOJckGIAAIkJPFH0uU';
    private $businessShortCode = '174379';
    private $passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
    private $callbackUrl = 'https://abcd1234.ngrok.io/customer/mpesa_callback.php'; // Replace with your actual Ngrok URL
    private $baseUrl = 'https://sandbox.safaricom.co.ke';

    public function __construct($conn) {
        $this->conn = $conn;
        date_default_timezone_set('Africa/Nairobi');
    }

    private function logMessage($message, $type = 'INFO') {
        $logFile = 'mpesa_requests.log';
        $timestamp = date('[Y-m-d H:i:s]');
        $logEntry = "$timestamp [$type] $message\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    private function getAccessToken() {
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
        $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new Exception('Curl error: ' . curl_error($curl));
        }

        curl_close($curl);

        $result = json_decode($response);

        if (isset($result->access_token)) {
            return $result->access_token;
        } else {
            throw new Exception('Failed to get access token');
        }
    }

    public function initiatePayment($bookingId, $phoneNumber, $amount, $paymentType) {
        try {
            $phoneNumber = preg_replace('/^0/', '254', $phoneNumber);
            $phoneNumber = preg_replace('/^\+254/', '254', $phoneNumber);

            $timestamp = date('YmdHis');
            $password = base64_encode($this->businessShortCode . $this->passkey . $timestamp);

            $accessToken = $this->getAccessToken();

            $url = $this->baseUrl . '/mpesa/stkpush/v1/processrequest';
            $stkheader = ['Content-Type: application/json', 'Authorization: Bearer ' . $accessToken];

            $postData = [
                'BusinessShortCode' => $this->businessShortCode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => round($amount),
                'PartyA' => $phoneNumber,
                'PartyB' => $this->businessShortCode,
                'PhoneNumber' => $phoneNumber,
                'CallBackURL' => $this->callbackUrl,
                'AccountReference' => 'Booking#' . $bookingId,
                'TransactionDesc' => ucfirst($paymentType) . ' for Booking #' . $bookingId
            ];

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                throw new Exception('Curl error: ' . curl_error($curl));
            }

            curl_close($curl);

            $result = json_decode($response, true);

            if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
                $checkoutRequestId = $result['CheckoutRequestID'];
                $merchantRequestId = $result['MerchantRequestID'];

                $stmt = $this->conn->prepare("INSERT INTO mpesa_payments (booking_id, phone_number, amount, payment_type, checkout_request_id, merchant_request_id, deposit_status, full_payment_status, due_payment_status, additional_charges_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $depositStatus = $paymentType == 'deposit' ? 'PENDING' : 'PENDING';
                $fullPaymentStatus = $paymentType == 'full_payment' ? 'PENDING' : 'PENDING';
                $duePaymentStatus = $paymentType == 'due_payment' ? 'PENDING' : 'PENDING';
                $additionalChargesStatus = $paymentType == 'additional_charges' ? 'PENDING' : 'PENDING';
                $stmt->bind_param("isdsssssss", $bookingId, $phoneNumber, $amount, $paymentType, $checkoutRequestId, $merchantRequestId, $depositStatus, $fullPaymentStatus, $duePaymentStatus, $additionalChargesStatus);

                if (!$stmt->execute()) {
                    throw new Exception('Failed to save payment record: ' . $stmt->error);
                }

                $paymentId = $stmt->insert_id;
                $stmt->close();

                return [
                    'success' => true,
                    'message' => 'Payment request sent to your phone. Please check your phone and enter M-PESA PIN to complete payment.',
                    'payment_id' => $paymentId,
                    'checkout_request_id' => $checkoutRequestId
                ];
            } else {
                $errorMessage = isset($result['errorMessage']) ? $result['errorMessage'] : 'Failed to initiate payment';
                throw new Exception($errorMessage);
            }
        } catch (Exception $e) {
            $this->logMessage("Payment initiation failed: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'message' => 'Payment initiation failed: ' . $e->getMessage()
            ];
        }
    }

    public function queryTransaction($checkoutRequestId) {
        try {
            $this->logMessage("Starting transaction query for CheckoutRequestID: " . $checkoutRequestId);

            $timestamp = date('YmdHis');
            $password = base64_encode($this->businessShortCode . $this->passkey . $timestamp);

            $accessToken = $this->getAccessToken();

            $url = $this->baseUrl . '/mpesa/stkpushquery/v1/query';
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ];

            $postData = [
                'BusinessShortCode' => $this->businessShortCode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId
            ];

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                throw new Exception('Curl error: ' . curl_error($curl));
            }

            curl_close($curl);

            $result = json_decode($response, true);

            if (!$result) {
                throw new Exception('Failed to decode MPesa response');
            }

            $result['ResultMessage'] = match((string)($result['ResultCode'] ?? 'UNKNOWN')) {
                '0' => 'Transaction successful',
                '1' => 'Insufficient balance',
                '1032' => 'Transaction cancelled by user',
                '1037' => 'Transaction timeout',
                default => 'Unknown result code: ' . ($result['ResultCode'] ?? 'N/A')
            };

            $this->logMessage("Parsed MPesa Response: " . json_encode($result), 'DEBUG');

            return $result;

        } catch (Exception $e) {
            $this->logMessage("Transaction query failed: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }

    public function updatePaymentStatus($checkoutRequestId, $status, $resultCode, $resultDesc, $mpesaReceiptNumber = null, $phoneNumber = null) {
        try {
            $this->logMessage("Attempting to update payment status with details: " . json_encode([
                'checkoutRequestId' => $checkoutRequestId,
                'status' => $status,
                'resultCode' => $resultCode,
                'resultDesc' => $resultDesc,
                'mpesaReceiptNumber' => $mpesaReceiptNumber,
                'phoneNumber' => $phoneNumber
            ]), 'DEBUG');

            $updateQuery = "UPDATE mpesa_payments SET 
                            status = ?,
                            result_code = ?,
                            result_desc = ?,
                            mpesa_receipt = COALESCE(?, mpesa_receipt),
                            phone_number = COALESCE(?, phone_number),
                            updated_at = NOW()
                           WHERE checkout_request_id = ?";

            $stmt = $this->conn->prepare($updateQuery);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $this->conn->error);
            }

            $stmt->bind_param("ssssss", 
                $status, 
                $resultCode, 
                $resultDesc, 
                $mpesaReceiptNumber, 
                $phoneNumber, 
                $checkoutRequestId
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to update payment status: " . $stmt->error);
            }

            $this->logMessage("Payment status update - Affected rows: " . $stmt->affected_rows, 'DEBUG');

            if ($stmt->affected_rows === 0) {
                $this->logMessage("No rows updated for CheckoutRequestID: $checkoutRequestId", 'WARNING');
            } else {
                $this->logMessage("Successfully updated payment status for CheckoutRequestID: $checkoutRequestId");
            }

            if ($status === 'COMPLETED') {
                $this->updateBookingStatus($checkoutRequestId, $status);
            }

            return $stmt->affected_rows > 0;

        } catch (Exception $e) {
            $this->logMessage("Database update error: " . $e->getMessage(), 'ERROR');
            throw $e;
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    public function updateBookingStatus($bookingId, $paymentType) {
        try {
            $this->logMessage("Updating booking status for Booking ID: $bookingId and Payment Type: $paymentType");

            $updateFields = [];
            $updateQuery = "UPDATE bookings SET ";

            switch ($paymentType) {
                case 'deposit':
                    $updateFields[] = "payment_status = 'DEPOSIT_PAID'";
                    $updateFields[] = "status = 'CONFIRMED'";
                    break;
                case 'full_payment':
                    $updateFields[] = "payment_status = 'FULLY_PAID'";
                    $updateFields[] = "status = 'CONFIRMED'";
                    break;
                case 'due_payment':
                    $updateFields[] = "payment_status = 'FULLY_PAID'";
                    $updateFields[] = "due_payment = 0";
                    break;
                case 'additional_charges':
                    $updateFields[] = "additional_charges_paid = 1";
                    $updateFields[] = "additional_charges = 0";
                    break;
                default:
                    throw new Exception("Invalid payment type: $paymentType");
            }

            $updateQuery .= implode(", ", $updateFields) . ", updated_at = NOW() WHERE booking_id = ?";

            $stmt = $this->conn->prepare($updateQuery);
            if (!$stmt) {
                throw new Exception("Failed to prepare booking update statement: " . $this->conn->error);
            }

            $stmt->bind_param("i", $bookingId);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update booking status: " . $stmt->error);
            }

            $this->logMessage("Successfully updated booking status for Booking ID: $bookingId");
            return true;

        } catch (Exception $e) {
            $this->logMessage("Booking status update error: " . $e->getMessage(), 'ERROR');
            return false;
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    public function checkPaymentStatus($paymentId) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM mpesa_payments WHERE id = ?");
            $stmt->bind_param("i", $paymentId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $payment = $result->fetch_assoc();
                $stmt->close();

                $checkoutRequestId = $payment['checkout_request_id'];
                $apiStatus = $this->queryTransaction($checkoutRequestId);

                if (isset($apiStatus['ResultCode'])) {
                    $status = match((string)$apiStatus['ResultCode']) {
                        '0' => 'COMPLETED',
                        '1' => 'FAILED',
                        '1032' => 'CANCELLED',
                        '1037' => 'TIMEOUT',
                        default => 'UNKNOWN'
                    };

                    $this->updatePaymentStatus(
                        $checkoutRequestId,
                        $status,
                        (string)$apiStatus['ResultCode'],
                        $apiStatus['ResultDesc'] ?? 'No description provided',
                        $apiStatus['MpesaReceiptNumber'] ?? null,
                        $apiStatus['PhoneNumber'] ?? null
                    );

                    return $status;
                }
            }

            return 'not_found';
        } catch (Exception $e) {
            $this->logMessage("Error checking payment status: " . $e->getMessage(), 'ERROR');
            return 'error';
        }
    }

    public function cancelPayment($paymentId) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM mpesa_payments WHERE id = ?");
            $stmt->bind_param("i", $paymentId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $payment = $result->fetch_assoc();
                $paymentType = $payment['payment_type'];
                $statusField = $paymentType . '_status';

                if ($payment[$statusField] == 'PENDING') {
                    $stmt->close();
                    $this->updatePaymentStatus($payment['checkout_request_id'], 'CANCELLED', '1032', 'Cancelled by user');
                    return [
                        'success' => true,
                        'message' => 'Payment request cancelled.'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Cannot cancel this payment - it is not in pending status.'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Payment not found.'
                ];
            }
        } catch (Exception $e) {
            $this->logMessage("Error cancelling payment: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'message' => 'Error cancelling payment: ' . $e->getMessage()
            ];
        }
    }
}