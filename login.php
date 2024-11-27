<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "database.php";

    $email = $_POST["email"];
    $password = $_POST["psw"];

    // Prepare the SQL query to get the user details
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if the user status is active
        if ($user['status'] == 'active') {
            // Verify password
            if (password_verify($password, $user['psw'])) {
                $_SESSION['user_email'] = $user['email'];
                header("Location: dashboard.php");
                exit();
            } else {
                echo "Invalid password!";
            }
        } else {
            echo "Your account is inactive!";
        }
    } else {
        echo "No user found with that email!";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Login</h1>
    <div class="container">
        
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="psw" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Not registered? <a href="registration.php">Sign Up</a></p>
    </div>
</body>
</html>
