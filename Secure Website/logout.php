<?php
session_start();
if ( $_SESSION['loggedin'] == True) {
//free all session variables
session_unset();
//destroy the session
session_destroy();
	echo "<SCRIPT type='text/javascript'>alert('You are now logged out');window.location.replace('index.html');</SCRIPT>";
}
else{
	echo "<SCRIPT type='text/javascript'>alert('You are not logged in');window.location.replace('index.html');</SCRIPT>";
}
?>