<?php

session_start();
$DB_HOST = 'localhost';
$DB_USER = 'root';
$UserAgent = $_SERVER['HTTP_USER_AGENT'];
//$IP = $_SERVER['HTTP_CLIENT_IP'] ;
$mysqli = new mysqli($DB_HOST, $DB_USER);
$con = mysqli_connect($DB_HOST, $DB_USER);


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
if (!isset($_POST['username'], $_POST['password']) ) {
    echo "<SCRIPT type='text/javascript'>alert('Please Complete the form!');window.location.replace('index.html');</SCRIPT>";
    die();
}
 // check if feilds empty string 
if (empty($_POST['username']) || empty($_POST['password']) ) {
    echo "<SCRIPT type='text/javascript'>alert('Please Complete the form!');window.location.replace('index.html');</SCRIPT>";
    die();
}


//Check the attempts of user in last 3 minutes 
if ($CheckAttempts = $con->prepare("SELECT Username, Attempts FROM secureappdb.logs WHERE Username =? and TimeStamp > date_sub(now(), interval 3 minute)")) {
    // Bind parameters
	$CheckAttempts->bind_param('s', $_POST['username']);
    $CheckAttempts->execute();
    $CheckAttempts->store_result();
    $CheckAttempts->bind_result($username, $logattempts);
    $CheckAttempts->fetch();

    if ($CheckAttempts->num_rows === 1) {
		echo "<SCRIPT type='text/javascript'>alert('first we've tired before');</SCRIPT>";
        //If user exists check attempts
        if ($logattempts > 4) {
            //Too many failed attempts
            echo "<SCRIPT type='text/javascript'>alert('Too many failed attempts, Please try again in 3 minutes');window.location.replace('index.html');</SCRIPT>";
            die();
        }
		else{
			$username = sanitize($_POST['username']);
			echo "<SCRIPT type='text/javascript'>alert('log attempts".$logattempts."');</SCRIPT>";
			if(tryLogin()){
				$_SESSION['loggedin'] = TRUE;
				$_SESSION['name'] = $username;
				$_SESSION['id'] = uniqid(mt_rand(), true);
				$_SESSION['startTime'] = time();
				echo '<html>
						<head>
						<meta charset="utf-8">
						<title>Logged in</title>
						</head>
						<div class="login-form">
						<h1>Hello ';
				echo $_SESSION['name'];
				echo'!</h1>
						<h1>You are now logged in!</h1>
						<form action="Landing.php" method="post">
						<input type="submit" value="Go to home page">
						</form>
						</div>
						</html>';
				}
			else{
				echo "<SCRIPT type='text/javascript'>alert('Failled login');</SCRIPT>";
			}	
		}
    }//if
	//adding else if as temporary work around to stop brute force bug add back else when update is ready im sure people can wait 3 minutes :)
	else if($CheckAttempts->num_rows === 0){
		$username = sanitize($_POST['username']);
		if(tryLogin()){
			$_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $username;
			$_SESSION['id'] = uniqid(mt_rand(), true);
			$_SESSION['startTime'] = time();
            echo '<html>
        			<head>
        			<meta charset="utf-8">
        			<title>Logged in</title>
        			</head>
        			<div class="login-form">
        			<h1>Hello ';
            echo $_SESSION['name'];
            echo'!</h1>
        			<h1>You are now logged in!</h1>
        			<form action="Landing.php" method="post">
        			<input type="submit" value="Go to home page">
        			</form>
        			</div>
        			</html>';
		}
		else{
			echo "<SCRIPT type='text/javascript'>alert('Failled login');</SCRIPT>";
		}
	}
	$CheckAttempts->close();
}//if 3 minute check
else{
	echo "<SCRIPT type='text/javascript'>alert('failed 1st query ');window.location.replace('index.html');</SCRIPT>";
}


$mysqli->close();



function failureLog() {
	$DB_HOST1 = 'localhost';
	$DB_USER1 = 'root';
	$UserAgent = $_SERVER['HTTP_USER_AGENT'];
	$con1 = mysqli_connect($DB_HOST1, $DB_USER1);
	$username = sanitize($_POST['username']);
	if($PrepSQL1 = $con1->prepare("SELECT ID, Username, Attempts FROM secureappdb.logs WHERE Username =? and TimeStamp > date_sub(now(), interval 3 minute)")){
		$PrepSQL1->bind_param('s', $username);
		$PrepSQL1->execute();
		$PrepSQL1->store_result();
		$PrepSQL1->bind_result($logId,$us_name,$logAtt);
		$PrepSQL1->fetch();
		if($PrepSQL1->num_rows === 0){
			if($PrepSQL2 = $con1->prepare("INSERT INTO secureappdb.logs( `Username`, `UserAgent`, `Success`, `Attempts`) VALUES (?, ?, 0, 1)" )){
				echo "<SCRIPT type='text/javascript'>alert('first time failing the login');</SCRIPT>";
				$PrepSQL2->bind_param('ss', $username, $UserAgent);
				$PrepSQL2->execute();
				$PrepSQL2->close();
				return true ;
			}
			else{//do nothing
				return false ;
			}
		}//if
		else if ($PrepSQL1->num_rows === 1){
			//UPDATE logins.failed_logins SET attempts = ?, attemptTime = ? WHERE Useragent = ?'
			if($PrepSQL2 = $con1->prepare("UPDATE secureappdb.logs SET Attempts = ? ,Success =0 where ID=?" )){
				echo "<SCRIPT type='text/javascript'>alert('im updating the counter');</SCRIPT>";
				$logAtt = $logAtt + 1 ;
				$PrepSQL2->bind_param('ss',$logAtt,$logId);
				$PrepSQL2->execute();
				$PrepSQL2->close();
				return true;
			}//
			else{//do nothing
				return false ;
			}
		}//else
	}
	else{
		$PrepSQL2->close();
		echo "<SCRIPT type='text/javascript'>alert('failed');</SCRIPT>";
	}
	//$PrepSQL2->close();
}//failureLog

function successLog() {
	//todo add extra select and update incase they've logged in and logout and tried to brute force in the last 3 minutes
$DB_HOST1 = 'localhost';
	$DB_USER1 = 'root';
	$UserAgent = $_SERVER['HTTP_USER_AGENT'];
	$con1 = mysqli_connect($DB_HOST1, $DB_USER1);
	$username = sanitize($_POST['username']);
	if($PrepSQL1 = $con1->prepare("SELECT ID, Username, Attempts FROM secureappdb.logs WHERE Username =? and TimeStamp > date_sub(now(), interval 3 minute)")){
		$PrepSQL1->bind_param('s', $username);
		$PrepSQL1->execute();
		$PrepSQL1->store_result();
		$PrepSQL1->bind_result($logId,$us_name,$logAtt);
		$PrepSQL1->fetch();
		if($PrepSQL1->num_rows === 0){
			if($PrepSQL2 = $con1->prepare("INSERT INTO secureappdb.logs( `Username`, `UserAgent`, `Success`, `Attempts`) VALUES (?, ?, 1, 0)" )){
				echo "<SCRIPT type='text/javascript'>alert('first you got it right');</SCRIPT>";
				$PrepSQL2->bind_param('ss', $username, $UserAgent);
				$PrepSQL2->execute();
				$PrepSQL2->close();
				return true ;
			}
			else{//do nothing
				return false ;
			}
		}//if
		else if ($PrepSQL1->num_rows === 1){
			//UPDATE logins.failed_logins SET attempts = ?, attemptTime = ? WHERE Useragent = ?'
			if($PrepSQL2 = $con1->prepare("UPDATE secureappdb.logs SET Attempts = 0, Success=1 where ID =?" )){
				echo "<SCRIPT type='text/javascript'>alert('im resetting the counter');</SCRIPT>";
				$PrepSQL2->bind_param('s',$logId);
				$PrepSQL2->execute();
				$PrepSQL2->close();
				return true;
			}//
			else{//do nothing
				return false ;
			}
		}//else
	}
	else{
		$PrepSQL2->close();
		echo "<SCRIPT type='text/javascript'>alert('failed');</SCRIPT>";
	}
}//successLog

function tryLogin() {
	$DB_HOST1 = 'localhost';
	$DB_USER1 = 'root';
	$UserAgent = $_SERVER['HTTP_USER_AGENT'];
	$con1 = mysqli_connect($DB_HOST1, $DB_USER1);
	$username = sanitize($_POST['username']);
	
    if ($PrepSQL = $con1->prepare('SELECT Password, Salt, Username FROM secureappdb.users WHERE Username = ?')) {
    $PrepSQL->bind_param('s', $username);
    $PrepSQL->execute();
    $PrepSQL->store_result();
	$PrepSQL->bind_result($safePassword,$safeSalt,$safeUsername);
	$PrepSQL->fetch();
	    if ($PrepSQL->num_rows === 1) {
			$hashedPass = hash('sha256', $_POST['password']);
			$hashedPass= $hashedPass.$safeSalt;
			if($hashedPass == $safePassword){
				//login
				if(successLog()){
					$PrepSQL->close();
					return true ;
				}
			}
			else{
				if(failureLog()){
					$PrepSQL->close();
					//echo "<SCRIPT type='text/javascript'>alert(Username or Password incorrect for user:". echo $safeUsername."');</SCRIPT>";
					echo '<h3>Username or Password incorrect for user: ';
					echo $username;
					echo'! click <a href="index.html">here</a> to return to the login</h3>';
					return false; 
				}
			}
		}
		else{
			failureLog();
			//echo "<SCRIPT type='text/javascript'>alert('Username or Password incorrect for user:". echo $safeUsername."');</SCRIPT>";
			echo '<h3>Username or Password incorrect for user: ';
			echo $username;
			echo'! click <a href="index.html">here</a> to return to the login</h3>';
			return false; 
		}
	$PrepSQL->close();
	}//query if
	else{
		//failed query
	}
	
}//try login
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

?>
