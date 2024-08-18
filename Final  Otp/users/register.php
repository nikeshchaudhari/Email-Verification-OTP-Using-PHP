<?php
session_start();
require 'connect_db.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check password
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Check Password Strong
        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
            $error_message = "Password must be at least 8 characters ";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $otp_code = rand(100000, 999999);
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));

            $mail = $conn->prepare("INSERT INTO users (full_name, email, password, otp_code, otp_expiry) VALUES (?, ?, ?, ?, ?)");
            $mail->bind_param("sssss", $full_name, $email, $hashed_password, $otp_code, $otp_expiry);

            if ($mail->execute()) {
                header('Location: index.php');
                exit();
            } else {
                $error_message = "Registration failed. Email may already be registered.";
            }

            $mail->close();
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
    <link rel="stylesheet" href="css/register.css">

</head>

<body>
    <div class="wrapper">
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
        <a class="login_link" href="index.php">Already have an account ? Login here</a>
    </div>
</body>

</html>