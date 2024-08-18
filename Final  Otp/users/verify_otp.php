<?php
require 'vendor/autoload.php';
require 'connect_db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Resend Otp
function resendOTP($conn, $email)
{
    $otp_code = rand(100000, 999999);
    $otp_expiry = date('Y-m-d H:i:s', strtotime('2 minutes'));

    $update_query = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?");
    $update_query->bind_param("sss", $otp_code, $otp_expiry, $email);

    if ($update_query->execute()) {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'info.nikesh12@gmail.com';
        $mail->Password = 'gfkq blcf wgjx jlto';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('info.nikesh12@gmail.com', 'No Reply');
        $mail->addAddress($email);
        $mail->Subject = 'Your New OTP Code';
        $mail->Body = "Hello, Your new OTP code is: $otp_code \n \n Please verify your account within 2 minutes.";
        $mail->isHTML(false);

        if ($mail->send()) {
            return "New OTP has been sent to your email.";
        } else {
            return "Failed to send OTP email: " . $mail->ErrorInfo;
        }
    } else {
        return "Failed to update OTP in the database.";
    }
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['otp_code']) && isset($_POST['email'])) {
        $otp_code = $_POST['otp_code'];
        $email = $_POST['email'];

        if (!empty($otp_code) && !empty($email)) {
            $s_email = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE email = ?");
            $s_email->bind_param("s", $email);
            $s_email->execute();
            $result = $s_email->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                if ($otp_code === $user['otp_code'] && strtotime($user['otp_expiry']) > time()) {
                    header('Location: password.php');
                    exit();
                } else {
                    $error_message = 'Invalid or expired OTP.';
                }
            } else {
                $error_message = 'No user found with this email.';
            }
        } else {
            $error_message = 'Please provide both OTP and email.';
        }
    }

    if (isset($_POST['resend_otp']) && isset($_POST['email'])) {
        $email = $_POST['email'];

        if (!empty($email)) {
            $result = resendOTP($conn, $email);

            if (strpos($result, 'New OTP has been sent') !== false) {
                $success_message = $result;
            } else {
                $error_message = $result;
            }
        } else {
            $error_message = 'Email is required.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="css/verify.css">
</head>

<body>
    <div class="wrapper">
        <h2>Verify OTP</h2>
        <?php if (!empty($error_message)): ?>
            <p class="message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <div class="verify_button">
            <form method="post" action="">
                <input type="text" name="otp_code" placeholder="Enter OTP" required>
                <input type="hidden" name="email" value="<?php echo $_GET['email'] ?? ''; ?>">
                <button type="submit" class="verify-button" id="verify_button">Verify OTP</button>
            </form>
        </div>
        <div class="resend">
            <form method="post" action="">
                <input type="hidden" name="email" value="<?php echo $_GET['email'] ?? ''; ?>">
                <button type="submit" name="resend_otp" class="resend-button" id="resend_button">Resend OTP</button>
            </form>
        </div>

    </div>
</body>

</html>