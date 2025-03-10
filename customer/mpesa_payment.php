<?php
require_once 'include/db_connection.php';
require_once 'MpesaPaymentController.php';

// Initialize variables
$errorMsg = '';
$successMsg = '';
$showPaymentForm = true;
$showSuccessPage = false;
$bookingDetails = null;
$bookingId = 0;

// Check if booking ID is provided
if(!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
    $errorMsg = 'Invalid booking reference.';
    $showPaymentForm = false;
} else {
    $bookingId = intval($_GET['booking_id']);
    
    // Get booking details
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 0) {
        $errorMsg = 'Booking not found.';
        $showPaymentForm = false;
    } else {
        $bookingDetails = $result->fetch_assoc();
    }
    
    $stmt->close();
}

// Initialize payment controller
$mpesaPayment = new MpesaPaymentController($conn);

// Handle payment form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_payment'])) {
    $phoneNumber = $_POST['phone_number'];
    $paymentType = $_POST['payment_type'];
    $amount = 0;
 
    if(empty($phoneNumber)) {
        $errorMsg = 'Please enter your M-Pesa phone number.';
    } else {
        // Determine amount based on payment type
        switch($paymentType) {
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
        
        if(empty($errorMsg)) {
            // Initiate payment
            $response = $mpesaPayment->initiatePayment($bookingId, $phoneNumber, $amount, $paymentType);
            
            if($response['success']) {
                // Store payment ID in session
                $_SESSION['current_payment_id'] = $response['payment_id'];
                $successMsg = $response['message'];
                $showPaymentForm = false;
            } else {
                $errorMsg = $response['message'];
            }
        }
    }
}

// Check current payment status
if(isset($_SESSION['current_payment_id'])) {
    $paymentId = $_SESSION['current_payment_id'];
    $paymentStatus = $mpesaPayment->checkPaymentStatus($paymentId);
    
    if($paymentStatus == 'completed') {
        $showSuccessPage = true;
        $showPaymentForm = false;
        $successMsg = 'Payment completed successfully!';
        
        unset($_SESSION['current_payment_id']);
    } elseif($paymentStatus == 'failed') {
        $errorMsg = 'Payment failed. Please try again.';
        
        unset($_SESSION['current_payment_id']);
    } elseif($paymentStatus == 'canceled') {
        $errorMsg = 'Payment was canceled.';
        
        unset($_SESSION['current_payment_id']);
    }
}

// Handle payment cancellation
if(isset($_GET['cancel']) && isset($_SESSION['current_payment_id'])) {
    $paymentId = $_SESSION['current_payment_id'];
    $response = $mpesaPayment->cancelPayment($paymentId);
    
    if($response['success']) {
        $errorMsg = $response['message'];
       
        unset($_SESSION['current_payment_id']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Pesa Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #06a54d;
            --secondary-color: #e02b73;
            --accent-color: #fbb03c;
            --text-color: #333;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #6c757d;
            --white: #ffffff;
            --shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .payment-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .payment-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .payment-header h2 {
            margin: 0;
            font-weight: 600;
        }
        
        .payment-body {
            padding: 30px;
        }
        
        .success-container {
            text-align: center;
            padding: 40px 20px;
        }
        
        .success-icon {
            background-color: var(--primary-color);
            color: white;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            margin: 0 auto 30px;
        }
        
        .booking-details {
            background-color: var(--light-gray);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .booking-details h5 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .booking-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        .booking-item:last-child {
            border-bottom: none;
        }
        
        .booking-label {
            font-weight: 500;
        }
        
        .booking-value {
            font-weight: 600;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--medium-gray);
            margin-bottom: 5px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(6, 165, 77, 0.25);
        }
        
        .form-text {
            color: var(--dark-gray);
            font-size: 0.85rem;
        }
        
        .payment-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #058f42;
            border-color: #058f42;
        }
        
        .btn-secondary {
            background-color: var(--dark-gray);
            border-color: var(--dark-gray);
        }
        
        .btn-danger {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-danger:hover {
            background-color: #c02663;
            border-color: #c02663;
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .spinner-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 30px 0;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: var(--primary-color);
        }
        
        .waiting-text {
            margin-top: 15px;
            font-weight: 500;
        }
        
        .total-amount {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        
        .total-amount h4 {
            margin: 0;
            font-weight: 600;
        }
        
        .mpesa-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .payment-body {
                padding: 20px;
            }
            
            .booking-item {
                flex-direction: column;
            }
            
            .payment-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if($showSuccessPage): ?>
            <div class="payment-container">
                <div class="payment-header">
                    <h2>Payment Status</h2>
                </div>
                <div class="payment-body">
                    <div class="success-container">
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <h2 class="mb-4">Payment Successful!</h2>
                        <p class="mb-4">Your booking has been confirmed. Thank you for your payment.</p>
                        <a href="dashboard.php" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="payment-container">
                <div class="payment-header">
                    <h2>M-Pesa Payment</h2>
                </div>
                <div class="payment-body">
                    <?php if(!empty($errorMsg)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMsg; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($successMsg)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle me-2"></i><?php echo $successMsg; ?>
                        </div>
                        <div class="spinner-container">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="waiting-text">Waiting for payment confirmation...</p>
                            <div class="mt-4">
                                <a href="payment.php?booking_id=<?php echo $bookingId; ?>&cancel=1" class="btn btn-danger">
                                    <i class="fas fa-times me-2"></i>Cancel Payment
                                </a>
                            </div>
                        </div>
                        <script>
                            // Reload the page every 5 seconds to check payment status
                            setInterval(function() {
                                location.reload();
                            }, 5000);
                        </script>
                    <?php endif; ?>
                    
                    <?php if($showPaymentForm && $bookingDetails): ?>
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
                            <?php if($bookingDetails['due_payment'] > 0): ?>
                                <div class="booking-item">
                                    <span class="booking-label">Due Payment:</span>
                                    <span class="booking-value">KSh <?php echo number_format($bookingDetails['due_payment'], 2); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if($bookingDetails['additional_charges'] > 0): ?>
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
                                    <?php if($bookingDetails['booking_status'] == 'pending'): ?>
                                        <option value="deposit" data-amount="<?php echo $bookingDetails['advance_deposit']; ?>">Pay Deposit (KSh <?php echo number_format($bookingDetails['advance_deposit'], 2); ?>)</option>
                                        <option value="full_payment" data-amount="<?php echo $bookingDetails['total_fare']; ?>">Pay Full Amount (KSh <?php echo number_format($bookingDetails['total_fare'], 2); ?>)</option>
                                    <?php elseif($bookingDetails['due_payment'] > 0): ?>
                                        <option value="due_payment" data-amount="<?php echo $bookingDetails['due_payment']; ?>">Pay Due Amount (KSh <?php echo number_format($bookingDetails['due_payment'], 2); ?>)</option>
                                    <?php endif; ?>
                                    <?php if($bookingDetails['additional_charges'] > 0): ?>
                                        <option value="additional_charges" data-amount="<?php echo $bookingDetails['additional_charges']; ?>">Pay Additional Charges (KSh <?php echo number_format($bookingDetails['additional_charges'], 2); ?>)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="total-amount">
                                <h4 id="selected-amount">
                                    KSh 
                                    <?php 
                                    // Default to the first available option
                                    if($bookingDetails['booking_status'] == 'pending') {
                                        echo number_format($bookingDetails['advance_deposit'], 2);
                                    } elseif($bookingDetails['due_payment'] > 0) {
                                        echo number_format($bookingDetails['due_payment'], 2);
                                    } elseif($bookingDetails['additional_charges'] > 0) {
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
                    <?php elseif(!$showSuccessPage): ?>
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
    
    <!-- Bootstrap JS -->
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