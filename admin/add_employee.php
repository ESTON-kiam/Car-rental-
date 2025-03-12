<?php
require 'include/db_connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';

require 'vendor/autoload.php';

$employee_id = $name = $gender = $contact_no = $email = $designation = $date_hired = $address = $emergency_contact = $password = "";
$employee_idErr = $nameErr = $genderErr = $contactErr = $emailErr = $designationErr = $dateErr = $addressErr = $emergencyErr = $passwordErr = "";
$success_message = $error_message = "";


function generateEmployeeID($conn) {
    $prefix = "EMP";
    $sql = "SELECT MAX(CAST(SUBSTRING(employee_id, 4) AS UNSIGNED)) as max_id FROM employees WHERE employee_id LIKE 'EMP%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    if ($row['max_id']) {
        $next_id = $row['max_id'] + 1;
    } else {
        $next_id = 1;
    }
    
    return $prefix . str_pad($next_id, 6, '0', STR_PAD_LEFT);
}


function sendEmployeeEmail($employeeName, $employeeEmail, $password) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();                                           
        $mail->Host       = 'smtp.gmail.com';                   
        $mail->SMTPAuth   = true;                                
        $mail->Username   = 'engestonbrandon@gmail.com';            
        $mail->Password   = 'dsth izzm npjl qebi';                    
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     
        $mail->Port       = 587;  
        
       
        $mail->setFrom('noreply@carrentalsystem.com', 'Car Rental Management System');
        $mail->addAddress($employeeEmail, $employeeName);
        
       
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Car Rental Management System';
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
            <h2 style='color: #3366cc;'>Welcome to Car Rental Management System</h2>
            <p>Dear $employeeName,</p>
            <p>Congratulations! Your employee account has been created successfully.</p>
            <p>Please use the following credentials to log into the system:</p>
            <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                <p><strong>Username:</strong> $employeeEmail</p>
                <p><strong>Password:</strong> $password</p>
            </div>
            <p>Please change your password after your first login for security reasons.</p>
            <p>If you have any questions, please contact your administrator.</p>
            <p>Best regards,<br>Car Rental Management Team</p>
        </div>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}


$employee_id = generateEmployeeID($conn);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    if (empty($_POST["employee_id"])) {
        $employee_idErr = "Employee ID is required";
    } else {
        $employee_id = test_input($_POST["employee_id"]);
        
        $check_id = "SELECT * FROM employees WHERE employee_id = ?";
        $stmt = $conn->prepare($check_id);
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $employee_idErr = "Employee ID already exists";
        }
        $stmt->close();
    }
    
    
    if (empty($_POST["name"])) {
        $nameErr = "Name is required";
    } else {
        $name = test_input($_POST["name"]);
        if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
            $nameErr = "Only letters and white space allowed";
        }
    }
    

    if (empty($_POST["gender"])) {
        $genderErr = "Gender is required";
    } else {
        $gender = test_input($_POST["gender"]);
    }
    
   
    if (empty($_POST["contact_no"])) {
        $contactErr = "Contact number is required";
    } else {
        $contact_no = test_input($_POST["contact_no"]);
        if (!preg_match("/^[0-9]{10,15}$/", $contact_no)) {
            $contactErr = "Invalid contact number format";
        }
    }
    
   
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        } else {
        
            $check_email = "SELECT * FROM employees WHERE email_address = ?";
            $stmt = $conn->prepare($check_email);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $emailErr = "Email already exists";
            }
            $stmt->close();
        }
    }
    
    
    if (empty($_POST["designation"])) {
        $designationErr = "Designation is required";
    } else {
        $designation = test_input($_POST["designation"]);
    }
    
   
    if (empty($_POST["date_hired"])) {
        $dateErr = "Date hired is required";
    } else {
        $date_hired = test_input($_POST["date_hired"]);
    }
    
   
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = test_input($_POST["password"]);
        if (strlen($password) < 8) {
            $passwordErr = "Password must be at least 8 characters";
        }
    }
    
    $address = test_input($_POST["address"] ?? "");
    $emergency_contact = test_input($_POST["emergency_contact"] ?? "");
    
    
    if (empty($employee_idErr) && empty($nameErr) && empty($genderErr) && empty($contactErr) && empty($emailErr) && empty($designationErr) 
        && empty($dateErr) && empty($passwordErr)) {
        
       
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
           
            $sql = "INSERT INTO employees (employee_id, name, gender, contact_no, email_address, designation, date_hired, address, emergency_contact, password) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssss", $employee_id, $name, $gender, $contact_no, $email, $designation, $date_hired, $address, $emergency_contact, $hashed_password);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                
                $email_sent = sendEmployeeEmail($name, $email, $password);
                
                if ($email_sent) {
                    $success_message = "Employee registered successfully! Login credentials have been sent to their email.";
                } else {
                    $success_message = "Employee registered successfully! However, there was a problem sending the email with login credentials.";
                }
                
                
                $employee_id = generateEmployeeID($conn);
                
               
                $name = $gender = $contact_no = $email = $designation = $date_hired = $address = $emergency_contact = $password = "";
            } else {
                $error_message = "Error: Could not register employee.";
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
        
    } else {
        $error_message = "Please fix the errors in the form.";
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <title>Employee Registration - Car Rental Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
   <link href="assets/css/addemplo.css" rel="stylesheet">
</head>
<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>
    <main class="main-content">
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Register New Employee</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success">
                                <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="required-field">Employee ID</label>
                                        <input type="text" class="form-control" name="employee_id" value="<?php echo $employee_id; ?>" readonly>
                                        <small class="text-muted">Auto-generated ID, cannot be modified</small>
                                        <span class="error"><?php echo $employee_idErr; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="required-field">Full Name</label>
                                        <input type="text" class="form-control" name="name" value="<?php echo $name; ?>">
                                        <span class="error"><?php echo $nameErr; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="required-field">Gender</label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender" id="male" value="Male" <?php if($gender == "Male") echo "checked"; ?>>
                                                <label class="form-check-label" for="male">Male</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender" id="female" value="Female" <?php if($gender == "Female") echo "checked"; ?>>
                                                <label class="form-check-label" for="female">Female</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender" id="other" value="Other" <?php if($gender == "Other") echo "checked"; ?>>
                                                <label class="form-check-label" for="other">Other</label>
                                            </div>
                                        </div>
                                        <span class="error"><?php echo $genderErr; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="required-field">Contact Number</label>
                                        <input type="text" class="form-control" name="contact_no" value="<?php echo $contact_no; ?>">
                                        <span class="error"><?php echo $contactErr; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="required-field">Email Address</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo $email; ?>">
                                        <small class="text-muted">This will be used as their login email</small>
                                        <span class="error"><?php echo $emailErr; ?></span>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="required-field">Password</label>
                                        <input type="password" class="form-control" name="password" value="<?php echo $password; ?>">
                                        <small class="text-muted">Minimum 8 characters</small>
                                        <span class="error"><?php echo $passwordErr; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="required-field">Designation</label>
                                        <select class="form-control" name="designation">
                                            <option value="" <?php if(empty($designation)) echo "selected"; ?>>Select Designation</option>
                                            <option value="Car Cleaner" <?php if($designation == "Car Cleaner") echo "selected"; ?>>Car Cleaner</option>
                                            <option value="Maintenance Technician" <?php if($designation == "Maintenance Technician") echo "selected"; ?>>Maintenance Technician</option>
                                            <option value="Office Assistant" <?php if($designation == "Office Assistant") echo "selected"; ?>>Office Assistant</option>
                                        </select>
                                        <span class="error"><?php echo $designationErr; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="required-field">Date Hired</label>
                                        <input type="date" class="form-control" name="date_hired" value="<?php echo $date_hired; ?>">
                                        <span class="error"><?php echo $dateErr; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea class="form-control" name="address" rows="2"><?php echo $address; ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Emergency Contact</label>
                                        <input type="text" class="form-control" name="emergency_contact" value="<?php echo $emergency_contact; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mt-3 text-center">
                                <button type="submit" class="btn btn-primary">Register Employee</button>
                                <a href="employeelist.php" class="btn btn-secondary">View Employee</a>
                                <a href="manage_employees.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>