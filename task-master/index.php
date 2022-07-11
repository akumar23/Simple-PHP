<?php

//starts the session
session_start();

//imports login.php with values for the sql connection
require_once 'login.php';

//html code to make a login interface
echo <<<_END
<html lang="en">
<head>
</head>
<body>
<section class="container">
<div class="login">
<h1>login</h1>
<form method="post" action="todo.php" name="loginForm">
<p><input type="email" name="email" value="" placeholder="Email"></p>
<p><input type="password" name="pass" value="" placeholder="Password"></p>
<p class="submit"><input type="submit" name="login" value="Login"></p>
</form>
<p class="submit"><a href="signup.php"> <input type="button" value="Signup"></a></p>
</section>
</body>
</html>
_END;


//function to sanitize sql inputs
function sanitizeMySQL($s){
  require_once 'login.php';
  $con = new mysqli($hostname, $username, $pass, $dbname);
  if(get_magic_quotes_gpc()){
    $s = stripslashes($s);
  }
  return $con->real_escape_string($s);
}

//function to take a sql connection and run a sql query to insert data
function insertToAccount($c){

  //checks if the connection is valid
  if($c){

    //checks if the user asset is avalible
    if(isset($_POST["user"])){

      //salts
      $salt1 = "qm&h*";
      $salt2 = "pg!@";

      //sets variables for user, email and password that should be passed in from signup
      //and sanitizes the inputs
      $user = sanitizeMySQL($_POST["user"]);
      $email = sanitizeMySQL($_POST["email"]);
      $password = sanitizeMySQL($_POST["pass"]);

      //hashes the password
      $hashPass = hash("ripemd128", "$salt1$password$salt2");

      //creates a query in the form of a string to insert the email, username and password into the account table
      $insertQuery = "INSERT INTO account(email, username, password) VALUES ('$email','$user','$hashPass')";

      //runs the query using the passed in sql connection
      $runQuery = $c->query($insertQuery);

      //prints an error if the query gives an error
      if (!$runQuery){
        die($c->error);
      }
    }
  } else {

      //echos that it isn't a valid sql connection if it isn't
      echo 'not a valid sql connection';
    }
}

//checks if the user asset is avalible
if(isset($_POST["user"])){
  require_once 'login.php';
  //creates the sql connection using localhost, root and hw5db
  $con = new mysqli($hostname, $username, $pass, $dbname);

  //prints an error if the connection fails
  if(!$con){
    die("connection failed" .mysqli_connect_error());
  }

  //runs the insert to account query using the sql connection
  insertToAccount($con);
}

//function to test the insertToAccount function
function test(){
  $test1 = array('hi', 'bye');
  insertToAccount($test1);
}

//runs the tests
test();

session_destroy();
?>
