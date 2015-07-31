<?php
		if(!$_SESSION['islogin'])
		{
			include 'login.php';
			exit();
		}
		include'classes/my_customers.php';
		$List=new client();
		if($_GET['pageload']==9){
			if(isset($_POST['Submit'])){
			
				$labels = array( "noclcam"=>"Numero du compte CLCAM:",
					"fname"=>"Prenom de l'utilisateur:",
					"lname"=>"Nom de l'utilisateur:",
					"uname"=>"Login pour le compte:",
					"date"=>"La date au`jourdui:",
					"zone"=>"Zone location de l'utilisateur:",
					"job"=>"Position:",);
				if($_POST['pass1']!=$_POST['pass2']){
				$misspass = "bad";
				}
				foreach($_POST as $field=>$value){
					if(empty($_POST[$field])){
						if($field != "zone" && $field != "pass1" && $field != "pass2" ){
							$blanks[$field] = "blank";
						}
					}
					else{
						$value = trim($value);
						if($field == "uname"&& $field == "pass1"&& $field == "pass2" ){
							if(!ereg("^[A-Za-z0-9]{1,65}$",$value)){
								$formats[$field] = "bad";
							}
						}
						elseif($field == "fname" && $field == "lname"){
							if(!ereg("^[A-Za-z`]{1,65}$",$value)){
								$formats[$field] = "bad";
							}
						}
						elseif($field != "zone"){
							if(!ereg("^[0-9]?",$value)){
								$formats[$field] = "bad";
							}
						}
					}
				}
				### if any fields were not okay, display error ###l
				### message and redisplay form ###
				if (@sizeof($blanks) > 0 or @sizeof($formats) > 0){
					if($_POST['action']=="new"){
					$template->set_filenames(array(
						'body' => 'new_employee.html')
					);}
					elseif($_POST['action']=="update"){
					$template->set_filenames(array(
						'body' => 'view_emp.html')
					);	
					}
					else{
					exit;
					}
					
					if (@sizeof($blanks) > 0){
						$template->assign_block_vars('error_required', array());
						foreach($blanks as $field => $value){
						$template->assign_block_vars('error_required.data',	array(
						'ERROR' => $labels[$field],)
						);
						}
					}
					if (@sizeof($formats) > 0){
						$template->assign_block_vars('error_symbol', array());
						foreach($formats as $field => $value){
						$template->assign_block_vars('error_symbol.data',	array(
						'ERROR' => $labels[$field],)
						);
						}
					}
					if (@sizeof($misspass) > 0){
						$template->assign_block_vars('error_password', array());
						foreach($misspass as $field => $value){
						$template->assign_block_vars('error_password.data',	array(
						'ERROR' => $labels[$field],)
						);
						}
					}
					$template->assign_vars(array(
					    'ZONE' => $_SESSION['zone'],
						'MISE' => $_POST['mise'],
						'NOM' => $_POST['NOM'],
						'NOCLCAM' => $_POST['noclcam'],
					    'SIGNING_AGENT' => $_SESSION['emp'],
					    'SIGNING_DATE' => date("Y-m-d h:i:s"),));
				
				}
				else{
					$LAST_NAME=$_POST['lname'];
					$FIRST_NAME=$_POST['fname'];
					$USER_ID=$_POST['uname'];
					$ACCESS=$_POST['job'];
					$HIRE_DATE=$_POST['date'];
					$ZONE=$_POST['zone'];
					$PASS=md5($_POST['pass1']);
					if(!empty($_POST['pass1'])){
					$PASSV=", '$PASS'";
					$PASSF=',`PASS`'; 
					}
					else{
					$PASSV='';
					$PASSF=''; 
					}
				if(!empty($_POST['uid'])){
					if(!empty($_POST['pass1'])){
						$PASSV=", `PASS`= '$PASS'";
					}
					else{
						$PASSV='';
					}
					$List->query("UPDATE `emp_info` SET `LAST_NAME` = '$LAST_NAME',
									`FIRST_NAME` = '$FIRST_NAME',`USER_ID` = '$USER_ID',
									`HIRE_DATE` = '$HIRE_DATE',`ACCESS` = '$ACCESS' $PASSV
									WHERE EMP_ID = '{$_POST['uid']}';");
					$emp=$_POST['uid'];
				}
				else{
					if(!empty($_POST['pass1'])){
						$PASSV=", '$PASS'";
						$PASSF=',`PASS`'; 
					}
					else{
						$PASSV='';
						$PASSF=''; 
					}
					$List->query("INSERT INTO `emp_info` 
								(`LAST_NAME`, `FIRST_NAME`, `USER_ID`, `HIRE_DATE`, `ACCESS` $PASSF) 
								VALUES
								('$LAST_NAME', '$FIRST_NAME', '$USER_ID', NOW(), $ACCESS $PASSV);");
					$List->query("SELECT LAST_INSERT_ID({$List->conn}) AS EMP FROM `emp_info` ORDER BY EMP;");
					while($resultie = mysql_fetch_array($List->result)){$emp=$resultie[EMP];}
				}
						$List->query("INSERT INTO `emp_loc`
									(`EMP_ID`, `DATE`, `ZONE`, `CLCAM_ID`)
									VALUES
									($emp, NOW(), $ZONE, 1);");
				/******************************************BUILD Employee IS DONE NOW DISPLAYING********************************************************/
				$template->set_filenames(array(
							'body' => 'user_admin.html')
						);
					$template->assign_block_vars('switch_post_entries',	array());
					$List->query("SELECT * FROM `emp_info`
								WHERE ACCESS< {$_SESSION['access']};");	
					while($resultie = mysql_fetch_array($List->result)){				
						$template->assign_block_vars('switch_post_entries.row',	array(
									'EMP_ID' => $resultie['EMP_ID'],
									'LAST' => $resultie['LAST_NAME'],
									'FIRST' => $resultie['FIRST_NAME'],
									'USER_ID' => $resultie['USER_ID'],));
					}	
				}
			}
			else{	
				$jobs = array(		
					0 => "promoteur",
				    1 => "caissier",
					2 => "superviseur",
					3 => "gerant",
					4 => "admin",);			
				if($_POST['add']){
					$template->set_filenames(array(
						'body' => 'new_employee.html')
					);
					for($a=$_SESSION['access']-1; $a>=1; $a--){
						$template->assign_block_vars('select',	array(
									'JOB_TITLE' => $jobs[$a-1],
									'JOB_LVL' => $a,));
						}
					$template->assign_vars(array(
						    'ZONE' => $_SESSION['zone'],
							'MISE' => $_POST['mise'],
							'NOM' => $_POST['NOM'],
							'NOCLCAM' => $_POST['noclcam'],
						    'SIGNING_AGENT' => $_SESSION['emp'],
						    'HIRE_DATE' => date("Y-m-d h:i:s"),));
				}
				else if($_POST['edit']){
				$template->set_filenames(array(
						'body' => 'view_emp.html')
					);					
				$template->assign_block_vars('switch_emp_display',	array());
					$List->query("SELECT * FROM `emp_info`
								WHERE EMP_ID= {$_POST['emp']};");	
					while($resultie = mysql_fetch_array($List->result)){				
						$template->assign_vars(array(
									'EMP_ID' => $resultie['EMP_ID'],
									'LAST_NAME' => $resultie['LAST_NAME'],
									'FIRST_NAME' => $resultie['FIRST_NAME'],
									'HIRE_DATE' => $resultie['HIRE_DATE'],
									'USER_ID' => $resultie['USER_ID'],));
									$viewAccess = $resultie['ACCESS'];
					}	
				for($a=$_SESSION['access']-1; $a>=1; $a--){
						$template->assign_block_vars('switch_emp_display.select',	array(
									'JOB_TITLE' => $jobs[$a-1],
									'JOB_LVL' => $a,));
						if($a==$viewAccess) $template->assign_block_vars('switch_emp_display.select.selected',	array());
						}	
				$List->query("SELECT @max := MAX( DATE ) FROM emp_loc WHERE EMP_ID = {$_POST['emp']};");
				$List->query("SELECT ZONE FROM emp_loc WHERE EMP_ID = {$_POST['emp']} AND DATE = @max;");
				while($resultie = mysql_fetch_array($List->result)){
					$emp_zone =$resultie['ZONE'];
				}	
				if(isset($emp_zone)){
					$template->assign_vars(array(
										'ZONE' => $emp_zone,));
				}
				else{
					if($viewAccess>1) {}
					else{
					$template->assign_block_vars('switch_emp_display.switch_no_goal',	array());
					}
					$template->assign_vars(array(
										'ZONE' => '0',));
				}
				
				}
				else{
					$template->set_filenames(array(
							'body' => 'user_admin.html')
						);
					$template->assign_block_vars('switch_post_entries',	array());
					$List->query("SELECT * FROM `emp_info`
								WHERE ACCESS< {$_SESSION['access']};");	
					while($resultie = mysql_fetch_array($List->result)){				
						$template->assign_block_vars('switch_post_entries.row',	array(
									'EMP_ID' => $resultie['EMP_ID'],
									'LAST' => $resultie['LAST_NAME'],
									'FIRST' => $resultie['FIRST_NAME'],
									'USER_ID' => $resultie['USER_ID'],));
					}	
				}
			}
			
			
		}	
			
		$template->pparse('body');
?>