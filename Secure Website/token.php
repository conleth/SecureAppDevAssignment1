<?php
class Token{
	public static function generate(){
		return $_SESSION['token'] = uniqid(mt_rand(), true);
	}
	public static function check($token){
		if(isset($_SESSION['token']) && $token === $_SESSION['token']){
			unset($_SESSION['token']);
			return true ;
		}
		return false; 
	}
}
?>