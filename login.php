<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
</head>
<body>
    <?php
        if($_SERVER["REQUEST_METHOD"]=="POST"){

          include "database.php";
          $email= $_POST["email"];
          $password= $_POST["psw"];

            // Prepare SQL query to check if the user exists by email
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email); // Bind email to the query
            $stmt->execute();
            $result = $stmt->get_result(); // Execute query and get result

            // Check if a user with this email exists
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc(); // Fetch user data

                // Verify if the entered password matches the hashed password in the database
                if (password_verify($password, $user['psw'])) {
                    
                    $_SESSION['user_email'] = $user['email']; // Store user email in session
                    header("Location: dashboard.php"); // Redirect to dashboard if login is successful
                    exit();
                } else {
                    echo "Invalid password!";
                }
            } else {
                echo "No user found with that email!";
            }

            $stmt->close(); // Close prepared statement
            $conn->close(); // Close database connection
                }
    
    ?>

  <div class="login-container">
    <form action="login.php" method="post">
      <input type="email" placeholder="Email" name="email" required><br>
      <input type="password" placeholder="Password" name="psw" required><br>
      <button type="submit">Login</button>
      <p>Not registered yet ! ?  <a href="registration.php" >Register Here</a> </p> 
    </form>
  </div>

  
</body>
</html>