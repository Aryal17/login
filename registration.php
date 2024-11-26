
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration</title>
</head>
<body>
    <?php
      if(isset($_POST["submit"])){
        $email = $_POST["email"];
        $psw = $_POST["psw"];
        $pswrepeat = $_POST["pswrepeat"];

    
          //  empty fields
          if(empty($email) || empty($psw) || empty($pswrepeat)){
            echo "All fields are required!";
          }
          //  passwords match
          elseif($psw != $pswrepeat){
            echo "Passwords do not match!";
          }
          else {
            
            $passwordhash = password_hash($psw, PASSWORD_DEFAULT);
            
            
          // Database connection setup 
          include 'database.php';

          $status = 'inactive';

          // Insert into database
          $sql = "INSERT INTO users (email, psw , status) VALUES (?,?,?)";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("sss", $email, $passwordhash , $status);

          if($stmt->execute()){
              echo "Registration successful!";
             
          } else {
              echo "Error";
          }
          
          $stmt->close();
          $conn->close();
            } 
    }
    ?>



<form action="registration.php" method="post">

<h1>Registration</h1>
    <p>Please fill in this form to create an account.</p>
    <hr>

  <div class="container">
    <label for="email"><b>Email</b></label>
    <input type="text" placeholder="Enter Email"  name="email" required><br>

    <label for="psw"><b>Password</b></label>
    <input type="password" placeholder="Enter Password"  name="psw" required><br>

    <label for="pswrepeat"><b>Repeat Password</b></label>
    <input type="password" placeholder="Repeat Password"  name="pswrepeat" required><br>
    <hr>

    <!-- <input type="checkbox" required> Agree to our <a href="#">Terms & Privacy</a> -->

    <button type="submit" name="submit">Submit</button>
    </div>

  <div class="container signin">
    <p>Already have an account? <a href="login.php">Sign in</a>.</p>
  </div>
</form>
</body>
</html>

