<?php
session_start();
// check to see if the user is logged in
if (isset( $_SESSION['id'])) {	
    $DB_HOST1 = 'localhost';
	$DB_USER1 = 'root';
	$UserAgent = $_SERVER['HTTP_USER_AGENT'];
	$con1 = mysqli_connect($DB_HOST1, $DB_USER1);
	if ($PrepSQL = $con1->prepare('SELECT Admin from secureappdb.users WHERE Username = ?')) {
		$PrepSQL->bind_param('s', $_SESSION['name']);
		$PrepSQL->execute();
		$PrepSQL->store_result();
		$PrepSQL->bind_result($admin);
		$PrepSQL->fetch();
		$_SESSION['admin'] = $admin;
		//echo $PrepSQL->num_rows;
		//echo "<h1>Value</h1>". $_SESSION['admin'] ;
	}
	$expireAfter = 10; 
	//Check to see if our "last action" session
	//variable has been set.
	if(isset($_SESSION['last_action'])){
		//Figure out how many seconds have passed
		//since the user was last active.
		$secondsInactive = time() - $_SESSION['last_action'];
		//Convert our minutes into seconds.
		$expireAfterSeconds = $expireAfter * 60;
		//Check to see if they have been inactive for too long.
		if($secondsInactive >= $expireAfterSeconds){
			//User has been inactive for too long.
			//Kill their session.
			session_unset();
			session_destroy();
			echo "<SCRIPT type='text/javascript'>alert('You are not logged in');window.location.replace('index.html');</SCRIPT>";
		}
	}// timeout inactive
	else{
		//someones been messing with my sessions by golly
		//session_unset();
		//session_destroy();
	}
	if(isset($_SESSION['startTime'])){
		$expireAfter2 = 60; 
		$timeElasped = time() - $_SESSION['startTime'] ;
		$expireAfterSeconds = $expireAfter2 * 60;
		if($timeElasped >= $expireAfterSeconds){
			session_unset();
			session_destroy();
			echo "<SCRIPT type='text/javascript'>alert('You are not logged in');window.location.replace('index.html');</SCRIPT>";
		}
	}
	else{
		//be gone foul creature!
		//session_unset();
		//session_destroy();
	}
	// user is logged in
echo "<html>
		<head>
			<meta charset='utf-8'>
			<title>Home</title>
		</head>
		<body>
			<div class='login-form'>
				<h1>Hello ";
				echo $_SESSION['name'];
				echo "!
				<h1>Here are you options</h1>
				<form action='page1.php'>
					<input type='submit' value='Page 1'>
				</form>
				<form action='logout.php'>
					<input type='submit' value='Log out'>
				</form>
				<form action='PasswordChange.html.php'>
					<input type='submit' value='Change Password'>
				</form>";
				if($_SESSION['admin'] === 1){
					echo"
				<form action='logs.php'>
					<input type='submit' value='logs'>
				</form>";
				}
				echo"
			</div>
		</body>
	</html>";
} 
else {
	// user is not logged in, send the user to the login page
	echo "<SCRIPT type='text/javascript'>alert('You are not logged in');window.location.replace('index.html');</SCRIPT>";
}
//record last action
$_SESSION['last_action'] = time();
?>
