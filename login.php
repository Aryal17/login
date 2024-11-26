<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
</head>
<body>
    <?php

      session_start();
        if($_SERVER["REQUEST_METHOD"]=="POST"){

          include "database.php";
          $email= $_POST["email"];
          $password= $_POST["psw"];

            
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result(); 

            // Check if a user with this email exists
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc(); // Fetch user data

                if($user['status']=='inactive'){
                  $updateStmt = $conn->prepare("UPDATE users SET status = 'active' WHERE email = ?");
                  $updateStmt->bind_param("s", $email);
                  $updateStmt->execute();
                  $updateStmt->close();

                
                if (password_verify($password, $user['psw'])) {
                    
                    $_SESSION['user_email'] = $user['email']; 
                    header("Location: dashboard.php"); 
                    exit();
                } else {
                    echo "Invalid password!";
                }
               } else{
                echo"Your account is inactive";
               }
              } 
               else {
                echo "No user found with that email! please signin";
            }

            $stmt->close(); 
            $conn->close(); 
                
              }
    
    ?>

    <h1>SIGN IN</h1>
    
    <hr>

  <div class="login-container">
    <form action="login.php" method="post">
      <b>Email:</b>
      <input type="email" placeholder="Email" name="email" required><br>
      <b>Password:</b>
      <input type="password" placeholder="Password" name="psw" required><br>
      <button type="submit">Login</button>
      <p>Not registered yet ! ?  <a href="registration.php" >Signup</a> </p> 
    </form>
  </div>

  
</body>
</html>