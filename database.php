<?php

$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "login_register";

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    echo("Connection failed: ");
} 

?>
