<?php

session_start();

$DB_HOST = 'localhost';
$DB_USER = 'root';

$mysqli = new mysqli($DB_HOST, $DB_USER);
$con = mysqli_connect($DB_HOST, $DB_USER);
$salt = uniqid(mt_rand(), true);

if ($mysqli->connect_errno) {
    echo "<SCRIPT type='text/javascript'>alert('Failed to connect!');</SCRIPT>".$mysqli->connect_errno;
}

$createDatabase = "CREATE DATABASE IF NOT EXISTS secureappdb";
if ($con->query($createDatabase) === TRUE) {
}
else {
    echo "<SCRIPT type='text/javascript'>alert('Failed to create Database'); </SCRIPT>".$con->error;
}
//create logs
$createTableLogs = "CREATE TABLE IF NOT EXISTS secureappdb.logs
(
  `ID` INT AUTO_INCREMENT PRIMARY KEY,
  `Username` varchar(255) NOT NULL,
  `TimeStamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `UserAgent` varchar(255) NOT NULL,
  `Success` tinyint(1) NOT NULL,
  `Attempts` int(255) NOT NULL
)
";
if ($con->query($createTableLogs) === TRUE) {

} else {
    echo "<SCRIPT type='text/javascript'>alert('Failed to create Table logs');</SCRIPT>".$con->error;
}

$createTableUsers = "CREATE TABLE IF NOT EXISTS secureappdb.users
(
  `Salt` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Admin` tinyint(1) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Created Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Locked` tinyint(1) NOT NULL
)
";
if ($con->query($createTableUsers) === TRUE) {
	$stmt = $mysqli->prepare("SELECT * FROM secureappdb.users WHERE Username = 'ADMIN'");
	$stmt->execute();
	$stmt->store_result();
    if ($stmt->num_rows > 0) {
		//its not the first time someone inserted into you baby
    }
	else{
		$stmt = $mysqli->prepare("INSERT INTO secureappdb.users (Username, Password, Admin, Salt, Locked) VALUES ('ADMIN', ?, 1, ?, 0)");
		$password1 = hash('sha256', "SAD_2019!" ). $salt;
		$stmt->bind_param('ss',$password1,$salt);
		if($stmt->execute()){// baby we did it
		}
		else{
			echo "Didnt run";
		}
	}
} 
else {
    echo "<SCRIPT type='text/javascript'>alert('Failed to create Table users');</SCRIPT>".$con->error;
}

 // check is feilds are set
if (!isset($_POST['username'], $_POST['password'], $_POST['confirmPassword'])) {
    echo "<SCRIPT type='text/javascript'>alert('Please Complete the form!');window.location.replace('register.html');</SCRIPT>";
    die();
}
 // check if feilds empty string 
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['confirmPassword'])) {
    echo "<SCRIPT type='text/javascript'>alert('Please Complete the form!');window.location.replace('register.html');</SCRIPT>";
    die();
}

// Check to see if password feilds are not the same
if ($_POST['confirmPassword'] !== $_POST['password']) {
    echo "<SCRIPT type='text/javascript'>alert('Passwords are not the same!');window.location.replace('register.html');</SCRIPT>";
    die();
}
// Password must be between 5 and 20 characters long.
if (strlen($_POST['password']) > 255 || strlen($_POST['password']) < 8) {
    echo "<SCRIPT type='text/javascript'>alert('The password must be between 8 and 255 characters!');window.location.replace('register.html');</SCRIPT>";
    die();
}
// Password must be between 5 and 20 characters long.
if (strlen($_POST['username']) > 255 || strlen($_POST['username']) < 1) {
    echo "<SCRIPT type='text/javascript'>alert('The password must be between 1 and 255 characters!');window.location.replace('register.html');</SCRIPT>";
    die();
}

// Username must contain only characters and numbers and spaces.
if (preg_match('/^[_a-zA-Z0-9- ]+$/', $_POST['username']) < 1){
    echo "<SCRIPT type='text/javascript'>alert('Username is not valid!');window.location.replace('register.html');</SCRIPT>";
    die();
}
//Password must contain 1 uppercase
if (preg_match('/[A-Z]+/', $_POST['password']) < 1) {
    echo "<SCRIPT type='text/javascript'>alert('Password must contain an uppercase letter!');window.location.replace('register.html');</SCRIPT>";
    die();
}
//Password must contain 1 lowercase
if (preg_match('/[a-z]+/', $_POST['password']) < 1) {
    echo "<SCRIPT type='text/javascript'>alert('Password must contain a lowercase letter!');window.location.replace('register.html');</SCRIPT>";
    die();
}
//Password must contain 1 number
if (preg_match('/[0-9]+/', $_POST['password']) < 1) {
    echo "<SCRIPT type='text/javascript'>alert('Password must contain a number!');window.location.replace('register.html');</SCRIPT>";
    die();
}
// Password must contain special charcter
$pattern = '/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/';
if (preg_match($pattern, $_POST['password']) < 1) {
    echo "<SCRIPT type='text/javascript'>alert('Password must contain a special char!');window.location.replace('register.html');</SCRIPT>";
    die();
}

if ($stmt = $mysqli->prepare('SELECT * FROM secureappdb.users WHERE username = ?')) {
  	$username = sanitize($_POST['username']);
   	$username = preg_replace("/[^a-zA-Z0-9\ ]+/", "", $username);
    // Bind parameters
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Username already exists
        echo "<SCRIPT type='text/javascript'>alert('Username already exists, please choose another!');window.location.replace('register.html');</SCRIPT>";
		  die();
    } else {
        // Username doesnt exists, insert new account
        if ($stmt = $mysqli->prepare('INSERT INTO secureappdb.users (Username, Password, Salt, Admin,Locked) VALUES (?, ?, ?, 0, 0)')) {
            $password = hash('sha256', $_POST['password']);
			$password = $password.$salt ;
            $stmt->bind_param('sss', $username, $password, $salt);
            if($stmt->execute()){
            	echo "<SCRIPT type='text/javascript'>alert('You have Successfully registered!');window.location.replace('index.html');</SCRIPT>";
            }
            else{
            	echo "Didnt run";
            }   
        }//if 
		else {
            echo "<SCRIPT type='text/javascript'>alert('Error with mysqli!');window.location.replace('register.html');</SCRIPT>";
        }
    }
    $stmt->close();
}//if 
else {
}
function sanitize($input) {
	$input = str_replace("&", "&amp;", $input);
	$input = str_replace("\"", "&quot;", $input);
	$input = str_replace("'", "&#039;", $input);
	$input = str_replace("<", "&lt;", $input);
	$input = str_replace(">", "&gt;", $input);
	//$input = str_replace("(", "%28", $input);
	//$input = str_replace(")", "%29", $input);
	return $input;
}
$mysqli->close();
?>
