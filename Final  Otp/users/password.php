<?php
session_start();
require 'connect_db.php'; 

$error_message = ''; 

// Check Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password']; 

    // Database query
    $query = $conn->prepare("SELECT id, password FROM users"); 
    $query->execute();
    $result = $query->get_result();

    $auth = false; 

    while ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; 
            $auth = true; 
            break; 
        }
    }

    if ($auth) {
        header('Location: dashboard.php#home');
        exit();
    } else {
        $error_message = "Invalid password.";
    }

    $query->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Verification</title>
    <link rel="stylesheet" href="css/password.css">
</head>

<body>
    <div class="wrapper">
        <h2>Password Verification</h2>
        <?php if ($error_message): ?>
            <p class="message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
