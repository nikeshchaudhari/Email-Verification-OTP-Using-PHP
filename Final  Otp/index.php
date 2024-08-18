<?php
session_start();
require 'vendor/autoload.php'; // Include PHPMailer
require 'config.php'; // Include database configuration

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_email = trim($_POST['email']);

    // Check if the email is in the database
    $query = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $query->bind_param("s", $user_email);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Generate a new OTP code and set its expiry time
        $otp_code = strtoupper(bin2hex(random_bytes(3))); // Random 6-character code
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));

        // Update the OTP code and expiry time in the database
        $update_query = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?");
        $update_query->bind_param("sss", $otp_code, $otp_expiry, $user_email);
        $update_query->execute();

        // Prepare and send OTP email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'info.nikesh12@gmail.com'; // SMTP username
        $mail->Password = 'gfkq blcf wgjx jlto'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('info.nikesh12@gmail.com', 'No Reply');
        $mail->addAddress($user_email);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Hello,\n\nYour OTP code is: $otp_code\n\nPlease use this code within 2 minutes to verify your account.";
        $mail->isHTML(false);

        // Send the email and check for success
        if ($mail->send()) {
            // Redirect to OTP verification page
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
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Jost', sans-serif;
            background: linear-gradient(to bottom, #0f0c29, #302b63, #24243e);
            overflow: hidden;
        }

        .container {
            width: 350px;
            max-width: 400px;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            animation: slide-in 0.8s ease-out;
            transform: scale(0.9);
            animation: scale-in 0.8s ease-out forwards;
        }

        @keyframes slide-in {
            0% {
                transform: translateY(-100%);
            }

            100% {
                transform: translateY(0);
            }
        }

        @keyframes scale-in {
            0% {
                transform: scale(0.9);
            }

            100% {
                transform: scale(1);
            }
        }

        h2 {
            margin: 0 0 20px;
            font-size: 28px;
            color: #333;
            text-align: center;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 5px 0px 20px -10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: rgba(255, 255, 255, 0.9);
        }

        input:focus {
            border-color: #2575fc;
            box-shadow: 0 0 5px rgba(37, 117, 252, 0.5);
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #573b8a;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            outline: none;
        }

        button:hover {
            background-color: #6d44b8;
            transform: translateY(-2px);
        }

        .message {
            color: #e74c3c;
            font-size: 14px;
            margin: 10px 0;
            text-align: center;
        }

        .register-link {
            margin-top: 10px;
            display: block;
            text-align: center;
            color: green;
            text-decoration: none;
            font-size: 14px;
        }

        .register-link:hover {
            color: black;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($error_message)): ?>
            <p class="message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit">LOGIN </button>
        </form>
        <a class="register-link" href="register.php">Don't have an account? Register here</a>
    </div>
</body>

</html>