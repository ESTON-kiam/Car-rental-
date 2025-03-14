<?php
require_once 'include/db_connection.php';

class MpesaPaymentController {
    private $conn;
    private $consumerKey = 'F1tuXfV73l8AUIXUVEdvQsRE7OJsRdg9kz22y67vCEG1TCul';
    private $consumerSecret = 'agskGrWUs4A9NwazyA6bRhk9fCUm5wDmGfoPA9RQjA5biDaOJckGIAAIkJPFH0uU';
    private $businessShortCode = '174379';
    private $passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
    private $callbackUrl = 'https://655a-196-250-209-180.ngrok-free.app/callback_url.php';
    private $baseUrl = 'https://sandbox.safaricom.co.ke';


    public function __construct($conn) {
        $this->conn = $conn;
        date_default_timezone_set('Africa/Nairobi');
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
            return [
                'success' => false,
                'message' => 'Payment initiation failed: ' . $e->getMessage()
            ];
        }
    }

    public function checkPaymentStatusFromSafaricom($checkoutRequestId) {
        try {
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

            if (isset($result['ResultCode'])) {
                $resultCode = $result['ResultCode'];
                $resultDesc = isset($result['ResultDesc']) ? $result['ResultDesc'] : '';

                switch ($resultCode) {
                    case '0':
                        return ['status' => 'COMPLETED', 'message' => 'Payment completed successfully'];
                    case '1':
                        return ['status' => 'FAILED', 'message' => 'Insufficient funds'];
                    case '1032':
                        return ['status' => 'CANCELLED', 'message' => 'Transaction cancelled by user'];
                    case '1037':
                        return ['status' => 'TIMEOUT', 'message' => 'Transaction timed out'];
                    default:
                        return ['status' => 'FAILED', 'message' => $resultDesc];
                }
            } else {
                throw new Exception('Invalid response from Safaricom');
            }
        } catch (Exception $e) {
            return ['status' => 'ERROR', 'message' => $e->getMessage()];
        }
    }

    public function checkPaymentStatus($paymentId) {
        $stmt = $this->conn->prepare("SELECT * FROM mpesa_payments WHERE id = ?");
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $payment = $result->fetch_assoc();
            $stmt->close();

            $paymentType = $payment['payment_type'];
            $statusField = $paymentType . '_status';

            if ($payment[$statusField] == 'PENDING') {
                $checkoutRequestId = $payment['checkout_request_id'];
                $apiStatus = $this->checkPaymentStatusFromSafaricom($checkoutRequestId);

                if ($apiStatus['status'] != 'ERROR') {
                    $this->updatePaymentStatus($paymentId, $paymentType, $apiStatus['status'], $apiStatus['message']);

                    if ($apiStatus['status'] == 'COMPLETED') {
                        $this->updateBookingStatus($payment['booking_id'], $payment['payment_type']);
                    }

                    return strtolower($apiStatus['status']);
                }
            }

            return strtolower($payment[$statusField]);
        }

        return 'not_found';
    }

    private function updatePaymentStatus($paymentId, $paymentType, $status, $resultDesc = '') {
        $statusField = $paymentType . '_status';
        $query = "UPDATE mpesa_payments SET $statusField = ?, result_description = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $status, $resultDesc, $paymentId);
        $stmt->execute();
        $stmt->close();
    }

    public function updateBookingStatus($bookingId, $paymentType) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM bookings WHERE booking_id = ?");
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $booking = $result->fetch_assoc();
                $stmt->close();

                $newStatus = $booking['booking_status'];
                $updateFields = [];

                switch ($paymentType) {
                    case 'deposit':
                        $newStatus = 'confirmed';
                        $updateFields[] = "booking_status = 'confirmed'";
                        $updateFields[] = "payment_status = 'deposit_paid'";
                        break;
                    case 'full_payment':
                        $newStatus = 'confirmed';
                        $updateFields[] = "booking_status = 'confirmed'";
                        $updateFields[] = "payment_status = 'fully_paid'";
                        $updateFields[] = "due_payment = 0";
                        break;
                    case 'due_payment':
                        $updateFields[] = "payment_status = 'fully_paid'";
                        $updateFields[] = "due_payment = 0";
                        break;
                    case 'additional_charges':
                        $updateFields[] = "additional_charges_paid = 1";
                        $updateFields[] = "additional_charges = 0";
                        break;
                }

                if (!empty($updateFields)) {
                    $updateQuery = "UPDATE bookings SET " . implode(", ", $updateFields) . ", updated_at = NOW() WHERE booking_id = ?";
                    $stmt = $this->conn->prepare($updateQuery);
                    $stmt->bind_param("i", $bookingId);

                    if (!$stmt->execute()) {
                        throw new Exception("Failed to update booking: " . $stmt->error);
                    }

                    $stmt->close();
                    return true;
                }
            } else {
                throw new Exception("Booking not found with ID: " . $bookingId);
            }

            return false;
        } catch (Exception $e) {
            file_put_contents('mpesa_error_log.txt', date('Y-m-d H:i:s') . " - Error updating booking: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
            return false;
        }
    }

    public function cancelPayment($paymentId) {
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
                $this->updatePaymentStatus($paymentId, $paymentType, 'CANCELLED', 'Cancelled by user');
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
    }
}