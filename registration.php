<?php
// Include database connection
include "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $email = $_POST["email"];
    $password = $_POST["psw"];
    $confirm_password = $_POST["confirm_psw"];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "Passwords do not match!";
        exit();
    }

    // Check if the email already exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "Email already registered!";
        echo '<a href="login.php"> Login </a>';
        $stmt->close();
        $conn->close();
        exit();
    }

    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    
    $status = 'active';

    // Insert new user into the database
    $stmt = $conn->prepare("INSERT INTO users (email, psw, status, last_active) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $email, $hashed_password, $status);
    
    if ($stmt->execute()) {
        echo "Registration successful! ";
    } else {
        echo "Error: " . $stmt->error;
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
    <title>Registration</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<body>
    <h1>Register</h1>
    <p>Please fill in the form to create an account:</p>
    <hr>

    <div class="container">
        <form action="registration.php" method="POST">
            <b>Email:</b>
            <input type="email" placeholder="Email" name="email" required><br>
            <b>Password:</b>
            <input type="password" placeholder="Password" name="psw" required><br>
            <b>Confirm Password:</b>
            <input type="password" placeholder="Confirm Password" name="confirm_psw" required><br>
            <button type="submit">Register</button>
        </form>
    </div>

    <p>Already have an account? <a href="login.php">Login</a></p>
</body>
</html>
