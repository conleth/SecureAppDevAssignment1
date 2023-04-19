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
if($_SESSION['admin'] === 1){
	mysqli_connect("localhost", "root") or
    die("Could not connect: " . mysql_error());
	mysqli_select_db($con1,"secureappdb");

	$PrepSQL1 = mysqli_query($con1,"SELECT * from logs WHERE 1")or die (mysql_error());
	Echo " <table border='1'> <tr> <th>ID</th> <th>Name</th> <th>Time Stamp</th> <th>User Agent</th> <th>Success</th> <th>Attempts</th> </tr>";
	while ($row = mysqli_fetch_array($PrepSQL1)){
		printf("<tr> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%d</td> <td>%d</td> </tr>", $row[0], $row[1],$row[2], $row[3],$row[4], $row[5]); 
		 //echo $row[''] . " " . $row['username'] . " " . $row['timestamp'] . " "  . $row['success'] . "<br/>";
	}
	echo "</table>" ;
}//if 
else{
	echo "<SCRIPT type='text/javascript'>alert('You are not a admin');window.location.replace('Landing.php');</SCRIPT>";
}
	// user is logged in
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
//record last action
$_SESSION['last_action'] = time();
?>
