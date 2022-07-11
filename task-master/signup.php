<?php

//imports login.php with values for the sql connection
require_once 'login.php';

//html code for the signup page
//also has javascript in script tags for client side validation
echo <<<_END
<html lang="en">
<head>
</head>
<body>
<section class="container">
<div class="login">
<h1>Signup Portal</h1>
<script src = "validate.js"></script>
<form method="post" action="index.php" name="form" onSubmit="return validateForm(this);">
<p><input type="email" name="email" value="" placeholder="Email id"></p>
<p><input type="text" name="user" value="" placeholder="username"></p>
<p><input type="password" name="pass" value="" placeholder="Password"></p>
<p class="submit"><input type="submit" value="Signup"></p>
</form>
</div>
</section>
</body>
</html>
_END;

//creates the sql connection using localhost, root and hw5db
$con = new mysqli($hostname, $username, $pass, $dbname);

//prints an error if the connection fails
if(!$con){
  die("connection failed" .mysqli_connect_error());
}

//functon to create a table called account
function createAccountTable($c){

  //creates a query in the form of a string to create a table account
  $createAccount = "CREATE TABLE account (
            email varchar(64) not null,
            username varchar(64) not null,
            password varchar(64) not null
          )";

  //checks if the passed in connection is valid
  if($c){

    //runs the create table query
    $runQuery = $c->query($createAccount);

  } else {
    //prints an error if the sql connection isn't valid
    echo 'not a valid sql connection';
  }
}

//runs the createAccountTable function with the sql connection
createAccountTable($con);

//function to test the createAccountTable function
function test(){
  $test1 = "string";
  createAccountTable($test1);
}

//runs the test
test();

?>
