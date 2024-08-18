<?php
session_start();
require 'vendor/autoload.php'; 
require 'connect_db.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_email = trim($_POST['email']);

    // Check Email in Database
    $query = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $query->bind_param("s", $user_email);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        //  New Otp code and expire time
        $otp_code = strtoupper(bin2hex(random_bytes(3))); 
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));

       $update_query = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?");
        $update_query->bind_param("sss", $otp_code, $otp_expiry, $user_email);
        $update_query->execute();

    //  send OTP email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'info.nikesh12@gmail.com'; 
        $mail->Password = 'gfkq blcf wgjx jlto'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('info.nikesh12@gmail.com', 'No Reply');
        $mail->addAddress($user_email);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Hello,\n\nYour OTP code is: $otp_code\n\nPlease use this code within 2 minutes to verify your account.";
        $mail->isHTML(false);

        // Send the email and check 
        if ($mail->send()) {
            header('Location: verify_otp.php?email=' . urlencode($user_email));
            exit();
        } else {
            $error_message = "Error sending OTP email.";
        }
    } else {
        $error_message = "No account found with that email.";
    }

    $query->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/index.css">

</head>

<body>
    <div class="wrapper">
        <h2>Login</h2>
        <?php if (!empty($error_message)): ?>
            <p class="message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit">LOGIN </button>
        </form>
        <a class="register_link" href="register.php">Don't have an account? Register here</a>
    </div>
</body>

</html>