<?php
session_start();

// Check if the user is logged in
if(!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'database.php';

// Get user email from session
$user_email = $_SESSION['user_email'];

// Check if the user is active
$sql = "SELECT * FROM users WHERE email = ? AND status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    // If the user is not found or their account is inactive
    session_unset(); // Clear the session
    session_destroy(); // Destroy the session
    header("Location: login.php"); // Redirect to login
    exit();
}

$user = $result->fetch_assoc(); // Fetch the user data

// Now you can display the dashboard content
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
</head>
<body>

    <h1>Welcome, <?php echo $_SESSION['user_email']; ?>!</h1>

    <p>Your account is active. Enjoy using the system!</p>

    <a href="logout.php">Logout</a>

</body>
</html>

<?php
if (!isset($_SESSION['user_email'])) {
  // If the user is not logged in, redirect to the login page
  header("Location: login.php");
  exit();
}
// Close the database connection
$stmt->close();
$conn->close();
?>
