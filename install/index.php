<?php
session_start();
header('Expires: Fri, 25 Dec 1980 00:00:00 GMT'); // time in the past
header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s') . 'GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
if($_SESSION['validinstall']!=1){
	header("location: ../");
}
else{
	include "../template.php";
	$template= new template('../templates/square/html');
	if($_POST['submit']){
	
	}
	else{
		$template->set_filenames(array(
			'body' => 'new.html',)
		);
		$template->pparse('body');
	}

}






?>