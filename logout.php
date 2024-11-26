<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_email'])) {

    
    include "database.php";

    // Get the logged-in user's email
    $email = $_SESSION['user_email'];

    // Updating to inactive
    $updateStmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE email = ?");
    $updateStmt->bind_param("s", $email);
    $updateStmt->execute();
    $updateStmt->close();

    // Unset & destroy the session
    session_unset(); 
    session_destroy();

    
    header("Location: login.php");
    exit();
}
?>
