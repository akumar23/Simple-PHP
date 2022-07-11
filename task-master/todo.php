<?php

//starts the session
session_start();

//imports login.php with values for the sql connection
require_once 'login.php';

//creates the sql connection using localhost, root and hw5db
$con = new mysqli($hostname, $username, $pass, $dbname);

//creates a function for a query to select data from the account table
function selectQuery($c, $e, $p){

  //creates a query in the from of a string that selects everything from the account table
  //where the email and password are the same as the ones passed in
  $selectQuery = "select * from `account` where `email`='$e' and `password`='$p'";

  //checks if the sql connection is valid
  if($c){

    //runs the query
    $runQuery = $c->query($selectQuery);

    //prints error if the query
    if (!$runQuery) {
      echo $c->error;
    } else {

      //returns the resut of the query running if the query runs correctly
      return $runQuery;
    }
  } else {

    //prints an error if the passed in sql connection isn't valid
    echo "not valid sql connection";
  }
}

//function to create tasks table
function createTasks($c){

  //sql query that creates tasks table
  $createTasks = "CREATE TABLE tasks (
                    email varchar(64) not null,
                    task varchar(64) not null,
                    id int(10) not null
                  )";

  //checks if passed in connection is valid
  if($c){
    //runs query to create tasks table if connection is valid
    $runQuery = $c->query($createTasks);
  } else {
    //echos that there's an error in connection if connection isn't valid
    echo "error in connection";
  }

}

//runs command to create tasks table
createTasks($con);

//function to sanitize sql inputs
function sanitizeMySQL($connection, $s){
  $con = new mysqli($hostname, $username, $pass, $dbname);
  if(get_magic_quotes_gpc()){
    $s = stripslashes($s);
  }
  return $con->real_escape_string($s);
}

//does session security, checks if email is passed in
if(isset($_POST["email"])){

    //sets session ip to $_SERVER['REMOTE_ADDR']
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];

    //sets session variables
    $_SESSION["email"] = $_POST["email"];
    $_SESSION["pass"] = $_POST["pass"];
}

//checks if the session ip is equal to $_SERVER['REMOTE_ADDR']
if ($_SESSION['ip'] != $_SERVER['REMOTE_ADDR']){

  //if it isn't then the session is destroyed and the user is told to login again
  session_destroy();
  echo 'login again';
}

//sets session email
$email = $_SESSION["email"];
$pass = $_SESSION["pass"];

//salts
$salt1 = "qm&h*";
$salt2 = "pg!@";

//hashes the password
$hashPass = hash("ripemd128", "$salt1$pass$salt2");

//create cookie for user and set it to expire in a day
setcookie("user", $email, time()+(86400*30));

//runs selectQuery using the sql connection and the email and password
//and stores in a variable
$rows = selectQuery($con, $email, $hashPass);

//css for dropdown menu and html to welcome the user and have text box to add tasks and a logout button
echo <<<_END
<html>
<head>
	<title>Task Master</title>
  <style>

  .dropdown {
    position: relative;
    display: inline-block;
  }

  .dropdown-content {
    display: none;
    position: absolute;
    background-color: #f6f6f6;
    box-shadow: 0px 2px 5px 0px;
    padding: 12px 16px;
    z-index: 1;
  }

  .dropdown-content a {
    color: black;
    text-decoration: none;
    display: block;
  }

  .dropdown-content a:hover {background-color: #C0C0C0}

  .dropdown:hover .dropdown-content {
    display: block;
  }
  </style>
</head>

<body>
		<h1>Task Master</h1>
    <head>welcome to your account $email add a task below</head>
    <br><br>
	<form method="post" action="todo.php">
		<input type="text" name="task">
		<button type="submit" name="submit" id="add_btn">Add Task</button>
    <br><br>
	</form>

  <button name="logout" onclick="location.href='index.php'">Logout</button>
</body>
</html>
_END;

//checks if rows with users is over 0
if(mysqli_num_rows($rows)>0){

  //checks if task is clicked
  if (isset($_POST['task'])) {
    //if it is clicked then the value written in the text box for task is stored in variable task
    $task = $_POST['task'];

    //sql command to insert task to tasks table, also has a email value to link each task to the right user
    $sql = "INSERT INTO tasks (email, task, id) VALUES ('$email', '$task', 1)";

    //executes the sql query
    mysqli_query($con, $sql);
    header('location: todo.php');
  }

  //sql query to get the all the tasks linked the the current user
  $taskList = mysqli_query($con, "SELECT * FROM tasks WHERE email = '$email'");

  //html for the title of the page
  echo <<<_END

                        <h2>
                        <b>List of Your Tasks</b>
                        </h2>

                        <body>
  _END;

  //loops for all tasks linked to the user
  while ($row = mysqli_fetch_array($taskList)){

      //echoes each task
      echo $row['task'];

      //checks the id of the task to label it as not started yet, in progress or compelted
      if ($row['id'] == 1){
        echo ": not started yet  |  ";
      } elseif($row['id'] == 2) {
        echo ": in progress  |  ";
      } else {
        echo ": completed  |  ";
      }

  //html to make a text box for editing a task and the drop down menu to change the task to in progress or completed or to delete the task
   echo <<<_END
        <input type="text" name="edit">
        <button type="submit" name="editButton" id="edit_btn">Edit Task</button> |
        <div class="dropdown">
        <button class="dropbtn"> change task status </button>
        <div class="dropdown-content">

        <a href="todo.php"?progress=
   _END;

   //checks if the edit button is clicked
   if (isset($_POST['editButton'])) {

     //if it is then the new task is saved to varaible newTask
     $newTask = $_POST['edit'];

     //sql query to update the task with the new task
     $updateSql = "UPDATE tasks SET task= '$newTask' WHERE email= '$email'";
     mysqli_query($con, $updateSql);
     header('location: todo.php');
   }

   echo $row['task'];

   echo <<<_END
        ">in progress</a>
   _END;

   echo<<<_END
        <a href="todo.php?complete=
        _END;

   echo $row['task'];

   echo <<<_END
        ">completed</a>
   _END;

   echo<<<_END
        <a href="todo.php?delete=
        _END;

   echo $row['task'];

   echo <<<_END
        ">Delete</a>
        </body>
        </div>
        </div>
        <br>
        _END;
  }

  //checks if progress is clicked from the dropdown menu
  if(isset($_GET['progress'])) {

    //if it is clicked then the task is saved to variable taskToProgress
    $taskToProgress = $_GET['progress'];

    //runs query to delete that task from table tasks
    mysqli_query($con, "DELETE FROM tasks WHERE task= '$taskToProgress'");

    //sql query to add task to table tasks with id 2
    $progressSql = "INSERT INTO tasks (email, task, id) VALUES ('$email', '$taskToProgress', 2)";

    //runs that query
    mysqli_query($con, $progressSql);
    header('location: todo.php');
  }

  //checks if complete is cliked from the dropdown menu
  if(isset($_GET['compelte'])) {

    //if it is clicked then the task is saved to variable taskToComplete
    $taskToComplete = $_GET['compelte'];

    //runs query to delete that task from table tasks
	  mysqli_query($con, "DELETE FROM tasks WHERE task= '$taskToComplete'");

    //sql query to add task to table tasks with id 3
    $currentSql = "INSERT INTO tasks (email, task, id) VALUES ('$email', '$taskToComplete', 3)";

    //runs that query
    mysqli_query($con, $currentSql);
    header('location: todo.php');
  }

  //checks if delete is cliekd from the dropdown menu
  if (isset($_GET['delete'])) {

     //if it is clicked then the task is saved to variable taskToDel
	   $taskToDel = $_GET['delete'];

     //runs sql query to delete the task from the table tasks
	   mysqli_query($con, "DELETE FROM tasks WHERE task= '$taskToDel'");
	   header('location: todo.php');
   }

} else {
  //if the user row is below 0 then user isn't logged in with valid creditials so the message is echoed
  echo "invalid credentials";
}

//a function to test selecteQuery and createInfo functions
function test(){
  $test1 = array("number", "letter", "array");
  $e = "a@mail.com";
  $p = "1234";
  selectQuery($test1, $e, $p);
  createTasks($test1);
  $test2 = "";
  selectQuery($test2, $e, $p);
  createTasks($test2);
}

//runs the test
test();

//if logout is clicked then the session is destroyed
if(isset($_GET['logout'])){
  session_destroy();
}

?>
