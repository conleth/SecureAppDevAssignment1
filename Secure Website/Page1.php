<?php
session_start();
// check to see if the user is logged in
if (isset( $_SESSION['id'])) {	
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
		session_unset();
		session_destroy();
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
	
echo "
<html>
	<head>
		<meta charset='utf-8'>
		<title>Page 1</title>
	</head>
	<body>
		<div class='login-form'>
			<h1>Hello ";
			echo $_SESSION['name'];
			echo "!
			<h1>I am page the other page you can access</h1>
			<form action='Landing.php'>
				<input type='submit' value='Landing'>
			</form>
		</div>
	</body>
</html>";
} 
else {
	// user is not logged in, send the user to the login page
	echo "<SCRIPT type='text/javascript'>alert('You are not logged in');window.location.replace('index.html');</SCRIPT>";
}
$_SESSION['last_action'] = time();//last action for inactive
?>
