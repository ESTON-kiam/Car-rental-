<?php


function logIPAddress($conn, $user_id, $session_type) {
    $ip_address = $_SERVER['REMOTE_ADDR'];

    
    $sql = "INSERT INTO login_logs (user_id, session_type, ip_address) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $session_type, $ip_address);
    $stmt->execute();
    $stmt->close();
}
?>
