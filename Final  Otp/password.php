<?php
session_start();
require 'config.php'; // Include your database configuration

$error_message = ''; // Initialize error message

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password']; // Get the password from the form

    // Prepare and execute the database query
    $stmt = $conn->prepare("SELECT id, password FROM users"); // Query to fetch all users
    $stmt->execute();
    $result = $stmt->get_result();

    $authenticated = false; // Flag to track if a match is found

    while ($user = $result->fetch_assoc()) {
        // Check if the password matches any of the users
        if (password_verify($password, $user['password'])) {
            // Password matches, proceed to home.php
            $_SESSION['user_id'] = $user['id']; // Store user ID in session for future use
            $authenticated = true; // Set the flag to true
            break; // Exit the loop once a match is found
        }
    }

    if ($authenticated) {
        // Redirect to home.php if authenticated
        header('Location: home.php');
        exit();
    } else {
        // Set an error message for invalid password
        $error_message = "Invalid password.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Login</title>
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
            margin: 5px 0px 20px 0px;
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
    </style>
</head>

<body>
    <div class="container">
        <h2>Password Login</h2>
        <?php if ($error_message): ?>
            <p class="message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
