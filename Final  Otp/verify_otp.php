<?php
require 'vendor/autoload.php'; // Ensure PHPMailer is included
require 'config.php'; // Include your database configuration file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to resend OTP
function resendOTP($conn, $email)
{
    // Generate a new OTP code and expiry time
    $otp_code = rand(100000, 999999);
    $otp_expiry = date('Y-m-d H:i:s', strtotime('2 minutes'));

    // Update the OTP and expiry time in the database
    $update_query = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?");
    $update_query->bind_param("sss", $otp_code, $otp_expiry, $email);

    if ($update_query->execute()) {
        // Prepare to send OTP
        $mail = new PHPMailer(true);
        
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'info.nikesh12@gmail.com';
        $mail->Password = 'gfkq blcf wgjx jlto'; // Use your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email content
        $mail->setFrom('info.nikesh12@gmail.com', 'No Reply');
        $mail->addAddress($email);
        $mail->Subject = 'Your New OTP Code';
        $mail->Body = "Hello, Your new OTP code is: $otp_code \n \n Please verify your account within 2 minutes.";
        $mail->isHTML(false);

        // Send email and handle potential errors
        if ($mail->send()) {
            return "New OTP has been sent to your email.";
        } else {
            return "Failed to send OTP email: " . $mail->ErrorInfo;
        }
    } else {
        return "Failed to update OTP in the database.";
    }
}

// Handle form submission for OTP verification and resending
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['otp_code']) && isset($_POST['email'])) {
        $otp_code = $_POST['otp_code'];
        $email = $_POST['email'];

        if (!empty($otp_code) && !empty($email)) {
            // Verify the OTP
            $stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                if ($otp_code === $user['otp_code'] && strtotime($user['otp_expiry']) > time()) {
                    // OTP is valid and not expired
                    // Redirect to password.php
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
            transition: background-color 0.5s ease;
        }

        /* Container styling */
        .container {
            width: 350px;
            max-width: 400px;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            animation: slide-in 0.8s ease-out, scale-in 0.8s ease-out forwards;
            transform: scale(0.9);
        }

        /* Slide-in animation */
        @keyframes slide-in {
            0% {
                transform: translateY(-100%);
            }
            100% {
                transform: translateY(0);
            }
        }

        /* Scale-in animation */
        @keyframes scale-in {
            0% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Heading styling */
        h2 {
            margin: 0 0 20px;
            font-size: 28px;
            color: #333;
            text-align: center;
        }

        /* Input field styling */
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0px 20px -10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: rgba(255, 255, 255, 0.9);
        }

        /* Input focus styling */
        input[type="text"]:focus {
            border-color: #2575fc;
            box-shadow: 0 0 5px rgba(37, 117, 252, 0.5);
        }

        /* Submit button styling */
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
            margin-bottom: 10px;
        }

        /* Button hover effect */
        button:hover {
            background-color: black;
            transform: translateY(-2px);
        }

        /* Resend button specific styling */
        .resend-button {
            background-color: #888;
        }

        /* Resend button hover effect */
        .resend-button:hover {
            background-color:brown;
        }

        /* Error message styling */
        .message {
            color: #e74c3c;
            font-size: 14px;
            margin: 10px 0;
            text-align: center;
        }

        /* Success message styling */
        .success-message {
            color: #27ae60;
            font-size: 14px;
            margin: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verify OTP</h2>
        <?php if (!empty($error_message)): ?>
            <p class="message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <input type="text" name="otp_code" placeholder="Enter OTP" required>
            <input type="hidden" name="email" value="<?php echo $_GET['email'] ?? ''; ?>">
            <button type="submit">Verify OTP</button>
        </form>
        <form method="post" action="">
            <input type="hidden" name="email" value="<?php echo $_GET['email'] ?? ''; ?>">
            <button type="submit" name="resend_otp" class="resend-button">Resend OTP</button>
        </form>
    </div>
</body>
</html>
