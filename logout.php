<?php
	session_start();
	session_unset();
	if($_SESSION['go'] != 1||!$_SESSION['go']){
		session_destroy();
		header("Location: index.php");
		exit();
	}

?>
