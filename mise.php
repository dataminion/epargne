<?php
if($_POST['submit']){
include "class_lib.php";
	$db = new sql;
	$db->query("INSERT INTO `cust_goal` (`CUST_ID`, `GOAL`) VALUES ( {$_POST['cid']}, {$_POST['nmise']});");
?>
	<html>
		<head>
		<title>Editer Le Mise</title>
		<link href="formatting/style.css" rel="stylesheet" type="text/css" />		
		<link href="formatting/tables.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="formatting/menu/SpryMenuBar.js"></script>
		</head>
		<body>
			Cliquer ici pour 
			<a title="Return" href="javascript:returnWindow()"> retourner</a>
			sure la page et finir.
		</body>
	</html>
<?php
}


if(!$_POST['submit']){
?>
	<html>
		<head>
		<title>Editer Le Mise</title>
		<link href="formatting/style.css" rel="stylesheet" type="text/css" />		
		<link href="formatting/tables.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="formatting/menu/SpryMenuBar.js"></script>
		</head>
		<body>
			Le Mise de cet client est maintanent:<?php echo $_GET['mi'];?>
			<br />
				<form action="mise.php" method="post"> 
					<th scope="row">Nouveau Mise </th> 
					<td><input name="nmise" type="text" size="11" /></td>
					<input name="cid" type="hidden" value="<?php echo $_GET['cid'];?>" />
					<input name="submit" type="submit" value="Valider" />
				</form>
			<br />	
		</body>
	</html>
<?php
}
?>