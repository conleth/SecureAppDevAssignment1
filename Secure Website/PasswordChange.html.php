<?php
session_start();
require 'token.php' ;
if ( $_SESSION['loggedin'] == True) 
{
?>
<html>
	<head>
		<meta charset="utf-8">
		<title>Change Password</title>
	</head>
	<body>
		<div class="login-form">
			<h1>Password Change Form</h1>
			<form action="PasswordChange.php" method="get">
				<input type="password" name="oldPass" placeholder="Old Password">
				<input type="password" name="newPass" placeholder="New Password">
				<input type="password" name="newPassConf" placeholder="Confirm New Password">
				<input type="hidden" name="nonce" value=<?php echo Token::generate()?>>
				<input type="submit" value="Change Password">
				<h3>Password must contain the following:</h3>
			  	<p id="letter" class="invalid">A <b>lowercase</b> letter</p>
			  	<p id="capital" class="invalid">A <b>capital (uppercase)</b> letter</p>
			  	<p id="number" class="invalid">A <b>number</b></p>
				<p id="number" class="invalid">A <b>special charcter</b></p>
			  	<p id="length" class="invalid">Minimum <b>8 characters</b></p>
			</form>
		</div>
	</body>
</html>
<?php
}
else {
	// user is not logged in, send the user to the login page
	echo "<SCRIPT type='text/javascript'>alert('You are not logged in');window.location.replace('index.html');</SCRIPT>";
}
?>
