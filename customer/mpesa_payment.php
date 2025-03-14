<?php

require_once 'include/db_connection.php';
require_once 'MpesaPaymentController.php';

$errorMsg = '';
$successMsg = '';
$showPaymentForm = true;
$showStatusPage = false;
$bookingDetails = null;
$bookingId = 0;
$paymentStatus = '';


if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
    $errorMsg = 'Invalid booking reference.';
    $showPaymentForm = false;
} else {
    $bookingId = intval($_GET['booking_id']);

    $stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $errorMsg = 'Booking not found.';
        $showPaymentForm = false;
    } else {
        $bookingDetails = $result->fetch_assoc();
    }
    $stmt->close();
}


$mpesaPayment = new MpesaPaymentController($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_payment'])) {
    $phoneNumber = $_POST['phone_number'];
    $paymentType = $_POST['payment_type'];
    $amount = 0;

    if (empty($phoneNumber)) {
        $errorMsg = 'Please enter your M-Pesa phone number.';
    } else {
        
        switch ($paymentType) {
            case 'deposit':
                $amount = $bookingDetails['advance_deposit'];
                break;
            case 'full_payment':
                $amount = $bookingDetails['total_fare'];
                break;
            case 'due_payment':
                $amount = $bookingDetails['due_payment'];
                break;
            case 'additional_charges':
                $amount = $bookingDetails['additional_charges'];
                break;
            default:
                $errorMsg = 'Invalid payment type.';
                break;
        }

        if (empty($errorMsg)) {
          
            $response = $mpesaPayment->initiatePayment($bookingId, $phoneNumber, $amount, $paymentType);

            if ($response['success']) {
                $_SESSION['current_payment_id'] = $response['payment_id'];
                $_SESSION['payment_start_time'] = time();
                $_SESSION['payment_type'] = $paymentType;
                $_SESSION['payment_amount'] = $amount;

                $successMsg = $response['message'];
                $showPaymentForm = false;
            } else {
                $errorMsg = $response['message'];
            }
        }
    }
}


if (isset($_SESSION['current_payment_id'])) {
    $paymentId = $_SESSION['current_payment_id'];
    $paymentStatus = $mpesaPayment->checkPaymentStatus($paymentId);
    $currentTime = time();
    $startTime = $_SESSION['payment_start_time'] ?? $currentTime;
    $elapsedTime = $currentTime - $startTime;

    $hasTimedOut = $elapsedTime >30;

    if ($paymentStatus == 'completed') {
        $showStatusPage = true;
        $showPaymentForm = false;
        $successMsg = 'Payment completed successfully!';

        unset($_SESSION['current_payment_id']);
        unset($_SESSION['payment_start_time']);
    } elseif ($paymentStatus == 'failed') {
        $showStatusPage = true;
        $showPaymentForm = false;
        $errorMsg = 'Payment failed. Please try again.';

        unset($_SESSION['current_payment_id']);
        unset($_SESSION['payment_start_time']);
    } elseif ($paymentStatus == 'cancelled') {
        $showStatusPage = true;
        $showPaymentForm = false;
        $errorMsg = 'Payment was canceled.';

        unset($_SESSION['current_payment_id']);
        unset($_SESSION['payment_start_time']);
    } elseif ($hasTimedOut) {
        $showStatusPage = true;
        $showPaymentForm = false;
        $errorMsg = 'Payment verification timed out. Please check your M-Pesa account to see if payment was processed and try refreshing this page.';
    }
}


if (isset($_GET['cancel']) && isset($_SESSION['current_payment_id'])) {
    $paymentId = $_SESSION['current_payment_id'];
    $response = $mpesaPayment->cancelPayment($paymentId);

    if ($response['success']) {
        $errorMsg = $response['message'];
        $showStatusPage = true;
        $showPaymentForm = false;

        unset($_SESSION['current_payment_id']);
        unset($_SESSION['payment_start_time']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Pesa Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="assets/css/mpesa.css">
</head>
<body>
    <div class="container">
        <?php if ($showStatusPage): ?>
            
            <div class="payment-container">
                <div class="payment-header">
                    <h2>Payment Status</h2>
                </div>
                <div class="payment-body">
                    <div class="status-container">
                        <?php if ($paymentStatus == 'completed'): ?>
                            <div class="status-icon success">
                                <i class="fas fa-check"></i>
                            </div>
                            <h2 class="mb-4">Payment Successful!</h2>
                            <p class="mb-4">Your booking has been confirmed. Thank you for your payment.</p>
                        <?php elseif ($paymentStatus == 'failed'): ?>
                            <div class="status-icon failed">
                                <i class="fas fa-times"></i>
                            </div>
                            <h2 class="mb-4">Payment Failed</h2>
                            <p class="mb-4">We couldn't process your payment. Please try again.</p>
                        <?php elseif ($paymentStatus == 'cancelled'): ?>
                            <div class="status-icon warning">
                                <i class="fas fa-ban"></i>
                            </div>
                            <h2 class="mb-4">Payment Cancelled</h2>
                            <p class="mb-4">Your payment request was cancelled.</p>
                        <?php else: ?>
                            <div class="status-icon warning">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h2 class="mb-4">Payment Status Unknown</h2>
                            <p class="mb-4"><?php echo $errorMsg; ?></p>
                        <?php endif; ?>
                        <div class="payment-actions">
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Go to Dashboard
                            </a>
                            <?php if ($paymentStatus != 'completed'): ?>
                                <a href="mpesa_payment.php?booking_id=<?php echo $bookingId; ?>" class="btn btn-secondary">
                                    <i class="fas fa-redo me-2"></i>Try Again
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
           
            <div class="payment-container">
                <div class="payment-header">
                    <h2>M-Pesa Payment</h2>
                </div>
                <div class="payment-body">
                    <?php if (!empty($errorMsg)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMsg; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($successMsg)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle me-2"></i><?php echo $successMsg; ?>
                        </div>
                        <div class="spinner-container">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="waiting-text">Waiting for payment confirmation...</p>
                            <p class="timer-text">
                                <span id="timer-count">30</span> seconds remaining before timeout
                            </p>
                            <div class="mt-4">
                                <a href="mpesa_payment.php?booking_id=<?php echo $bookingId; ?>&cancel=1" class="btn btn-danger">
                                    <i class="fas fa-times me-2"></i>Cancel Payment
                                </a>
                            </div>
                        </div>
                        <script>
                            var timeLeft = 30;
                            var timer = setInterval(function() {
                                timeLeft--;
                                document.getElementById('timer-count').textContent = timeLeft;
                                if (timeLeft <= 0) {
                                    clearInterval(timer);
                                    location.reload();
                                }
                            }, 1000);
                        </script>
                    <?php endif; ?>
                    <?php if ($showPaymentForm && $bookingDetails): ?>
                        <div class="mpesa-logo">
                            <img src="assets/img/mpesa-logo.png" alt="M-Pesa Logo" height="60">
                        </div>
                        <div class="booking-details">
                            <h5>Booking Details</h5>
                            <div class="booking-item">
                                <span class="booking-label">Booking ID:</span>
                                <span class="booking-value">#<?php echo $bookingDetails['booking_id']; ?></span>
                            </div>
                            <div class="booking-item">
                                <span class="booking-label">Vehicle:</span>
                                <span class="booking-value"><?php echo $bookingDetails['model_name']; ?> (<?php echo $bookingDetails['registration_no']; ?>)</span>
                            </div>
                            <div class="booking-item">
                                <span class="booking-label">Dates:</span>
                                <span class="booking-value"><?php echo date('d M Y', strtotime($bookingDetails['start_date'])); ?> to <?php echo date('d M Y', strtotime($bookingDetails['end_date'])); ?></span>
                            </div>
                            <div class="booking-item">
                                <span class="booking-label">Total Fare:</span>
                                <span class="booking-value">KSh <?php echo number_format($bookingDetails['total_fare'], 2); ?></span>
                            </div>
                            <div class="booking-item">
                                <span class="booking-label">Advance Deposit:</span>
                                <span class="booking-value">KSh <?php echo number_format($bookingDetails['advance_deposit'], 2); ?></span>
                            </div>
                            <?php if ($bookingDetails['due_payment'] > 0): ?>
                                <div class="booking-item">
                                    <span class="booking-label">Due Payment:</span>
                                    <span class="booking-value">KSh <?php echo number_format($bookingDetails['due_payment'], 2); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($bookingDetails['additional_charges'] > 0): ?>
                                <div class="booking-item">
                                    <span class="booking-label">Additional Charges:</span>
                                    <span class="booking-value">KSh <?php echo number_format($bookingDetails['additional_charges'], 2); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form method="post" action="">
                            <div class="mb-4">
                                <label for="phone_number" class="form-label">M-Pesa Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="e.g., 07XXXXXXXX or 254XXXXXXXXX" required>
                                </div>
                                <div class="form-text">Enter the phone number registered with M-Pesa.</div>
                            </div>
                            <div class="mb-4">
                                <label for="payment_type" class="form-label">Payment Type</label>
                                <select class="form-select" id="payment_type" name="payment_type" required onchange="updateSelectedAmount()">
                                    <?php if ($bookingDetails['booking_status'] == 'pending'): ?>
                                        <option value="deposit" data-amount="<?php echo $bookingDetails['advance_deposit']; ?>">Pay Deposit (KSh <?php echo number_format($bookingDetails['advance_deposit'], 2); ?>)</option>
                                        <option value="full_payment" data-amount="<?php echo $bookingDetails['total_fare']; ?>">Pay Full Amount (KSh <?php echo number_format($bookingDetails['total_fare'], 2); ?>)</option>
                                    <?php elseif ($bookingDetails['due_payment'] > 0): ?>
                                        <option value="due_payment" data-amount="<?php echo $bookingDetails['due_payment']; ?>">Pay Due Amount (KSh <?php echo number_format($bookingDetails['due_payment'], 2); ?>)</option>
                                    <?php endif; ?>
                                    <?php if ($bookingDetails['additional_charges'] > 0): ?>
                                        <option value="additional_charges" data-amount="<?php echo $bookingDetails['additional_charges']; ?>">Pay Additional Charges (KSh <?php echo number_format($bookingDetails['additional_charges'], 2); ?>)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="total-amount">
                                <h4 id="selected-amount">
                                    KSh 
                                    <?php 
                                    if ($bookingDetails['booking_status'] == 'pending') {
                                        echo number_format($bookingDetails['advance_deposit'], 2);
                                    } elseif ($bookingDetails['due_payment'] > 0) {
                                        echo number_format($bookingDetails['due_payment'], 2);
                                    } elseif ($bookingDetails['additional_charges'] > 0) {
                                        echo number_format($bookingDetails['additional_charges'], 2);
                                    } else {
                                        echo "0.00";
                                    }
                                    ?>
                                </h4>
                            </div>
                            <div class="payment-actions">
                                <button type="submit" name="submit_payment" class="btn btn-primary">
                                    <i class="fas fa-credit-card me-2"></i>Proceed to Pay
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </a>
                            </div>
                        </form>
                    <?php elseif (!$showStatusPage): ?>
                        <div class="text-center mt-4">
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateSelectedAmount() {
            const select = document.getElementById('payment_type');
            const option = select.options[select.selectedIndex];
            const amount = option.getAttribute('data-amount');
            document.getElementById('selected-amount').textContent = 'KSh ' + 
                parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    </script>
</body>
</html>