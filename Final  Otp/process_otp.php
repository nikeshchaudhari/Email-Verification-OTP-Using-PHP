<?php
require 'config.php'; // Include your database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $otp_code = $_POST['otp_code'];

    // Retrieve the OTP and expiry time from the database
    $otp = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE email = ?");
    $otp->bind_param("s", $email);
    $otp->execute();
    $otp->store_result();
    $otp->bind_result($stored_otp, $otp_expiry);

    if ($otp->num_rows > 0) {
        $otp->fetch();

        // Check if the entered OTP matches the stored OTP and is within the expiry time
        if ($otp_code == $stored_otp && strtotime($otp_expiry) > time()) {
            // OTP is valid
            echo "OTP verified successfully!";
            // Perform further actions (e.g., activating the account, redirecting)
        } else {
            // OTP is invalid
            $error_message = "Invalid OTP or OTP has expired.";
            header('Location: verify_otp.php?email=' . urlencode($email) . '&error=' . urlencode($error_message));
            exit();
        }
    } else {
        // Email not found
        $error_message = "Email not found.";
        header('Location: verify_otp.php?email=' . urlencode($email) . '&error=' . urlencode($error_message));
        exit();
    }
}
?>
