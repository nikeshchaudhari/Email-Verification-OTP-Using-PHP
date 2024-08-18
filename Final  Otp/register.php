<?php
session_start(); // Start a session to manage redirection
require 'config.php'; // Include your database configuration file

$error_message = ''; // Initialize error message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Check if password is strong
        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
            $error_message = "Password must be at least 8 characters long, include at least one uppercase letter, one lowercase letter, one number, and one special character.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $otp_code = rand(100000, 999999); // Generate a random OTP code
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+2 minutes')); // Set OTP expiry time

            // Prepare and execute the database insert statement

          
            $stmt_mail = $conn->prepare("INSERT INTO users (full_name, email, password, otp_code, otp_expiry) VALUES (?, ?, ?, ?, ?)");
            $stmt_mail->bind_param("sssss", $full_name, $email, $hashed_password, $otp_code, $otp_expiry);

            if ($stmt_mail->execute()) {
                // Redirect to index.php after successful registration
                header('Location: index.php');
                exit(); // Ensure no further code execution
            } else {
                $error_message = "Registration failed. Email may already be registered.";
            }

            $stmt_mail->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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

        .login-link {
            margin-top: 10px;
            display: block;
            text-align: center;
            color: green;
            text-decoration: none;
            font-size: 14px;
        }

        .login-link:hover {
            color: black;
        }

        .login-button {
            background-color: #28a745;
            color: #fff;
            text-decoration: none;
            padding: 10px;
            border-radius: 4px;
            display: block;
            text-align: center;
        }

        .login-button:hover {
            background-color: green;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Register</h2>
        <?php if (!empty($error_message)): ?>
            <p class="message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <a class="login-link" href="index.php">Already have an account ? Login here</a>
    </div>
</body>

</html>