<?php
session_start();
require 'token.php' ;
if (Token::check($_GET['nonce'])) {
	if ( $_SESSION['loggedin'] == True) {
		$DB_HOST = 'localhost';
		$DB_USER = 'root';
		$mysqli = new mysqli($DB_HOST, $DB_USER);
		$con = mysqli_connect($DB_HOST, $DB_USER);
		
		if ( mysqli_connect_errno()) {
			 echo "<SCRIPT type='text/javascript'>alert('mysqli error');window.location.replace('PasswordChange.html.php')</SCRIPT>" . mysqli_connect_error();
		}

		//check if the data was submitted
		if (!isset($_GET['oldPass'], $_GET['newPass'], $_GET['newPassConf'])) {
			// Could not get the data that should have been sent.
			echo "<SCRIPT type='text/javascript'>alert('Please Complete the form!');window.location.replace('PasswordChange.html.php');</SCRIPT>";
				  die();
		}

		// Make sure the submitted values are not empty.
		if (empty($_GET['oldPass']) || empty($_GET['newPass']) || empty($_GET['newPassConf'])) {
			echo "<SCRIPT type='text/javascript'>alert('Please Complete the form!');window.location.replace('PasswordChange.html.php');</SCRIPT>";
				  die();
		}
		//Password must contain only characters and numbers
		if (preg_match('/[A-Za-z0-9]+/', $_GET['newPass']) < 1) {
			echo "<SCRIPT type='text/javascript'>alert('Password is not valid!');window.location.replace('PasswordChange.html.php');</SCRIPT>";
				  die();
		}
		//Password must contain 1 uppercase
		if (preg_match('/[A-Z]+/', $_GET['newPass']) < 1) {
			echo "<SCRIPT type='text/javascript'>alert('Password must contain an uppercase letter!');window.location.replace('PasswordChange.html.php');</SCRIPT>";
				  die();
		}
		//Password must contain 1 lowercase
		if (preg_match('/[a-z]+/', $_GET['newPass']) < 1) {
			echo "<SCRIPT type='text/javascript'>alert('Password must contain a lowercase letter!');window.location.replace('PasswordChange.html.php');</SCRIPT>";
				  die();
		}
		//Password must contain 1 number
		if (preg_match('/[0-9]+/', $_GET['newPass']) < 1) {
			echo "<SCRIPT type='text/javascript'>alert('Password must contain a number!');window.location.replace('PasswordChange.html.php');</SCRIPT>";
				  die();
		}

		if (strlen($_GET['newPass']) > 255 || strlen($_GET['newPass']) < 8) {
			echo "<SCRIPT type='text/javascript'>alert('The password must be between 5 and 20 characters!');window.location.replace('PasswordChange.html.php');</SCRIPT>";
				  die();
		}
		$pattern = '/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/';
		if (preg_match($pattern, $_GET['newPass']) < 1) {
			echo "<SCRIPT type='text/javascript'>alert('Password must contain a special char!');window.location.replace('PasswordChange.html.php');</SCRIPT>";
			die();
		}
		
		//The magic
			if ($stmt = $mysqli->prepare('SELECT username, password, salt FROM secureappdb.users WHERE username = ?')){
				$stmt->bind_param('s', $_SESSION['name']);
				$stmt->execute(); 
				$stmt->store_result(); 
				$stmt->bind_result($username, $password, $salt);
				$stmt->fetch();  

						$hashedOldPass = hash('sha256', $_GET['oldPass']). $salt;
						if($hashedOldPass == $password){
							if($_GET['newPass'] == $_GET['newPassConf']){
								if($stmt = $con->prepare("UPDATE secureappdb.users SET password = ? WHERE username = ?")){
									$hashedNewPass = hash('sha256', $_GET['newPass']).$salt;
									$stmt->bind_param('ss', $hashedNewPass,$username);
									$stmt->execute();
									$stmt->store_result();
									$stmt->close();
									echo "<SCRIPT type='text/javascript'>alert('Password successfully changed!');";
									echo 'window.location.replace("index.html") ';
									echo "</SCRIPT>";

									session_destroy();
								}
								else{
									unset($_SESSION['nonce']);
									echo "<SCRIPT type='text/javascript'>alert('Cannot update password');window.location.replace('PasswordChange.html.php');</SCRIPT>";
								}
							}
							else{
								echo "<SCRIPT type='text/javascript'>alert('New Passwords do not match');window.location.replace('PasswordChange.html.php');</SCRIPT>";
							}
						}
						else{
							echo "<SCRIPT type='text/javascript'>alert('Old Passwords do not match');window.location.replace('PasswordChange.html.php');</SCRIPT>";
						}
				}
		} 
	else {
		echo "<SCRIPT type='text/javascript'>alert('You are not logged in');window.location.replace('index.html');</SCRIPT>";
	}
}//nonce
else{
	echo "<SCRIPT type='text/javascript'>alert('Something is up');window.location.replace('PasswordChange.html.php');</SCRIPT>";
}

?>