<?php
		if(!$_SESSION['islogin'])
		{
			include 'login.php';
			exit();
		}
		include'classes/my_customers.php';
		$List=new client();
		if($_GET['pageload']==8){
			if(isset($_POST['Submit'])){
				$labels = array( "noclcam"=>"Numero du compte CLCAM:",
					"NOM"=>"Nom du compte:",
					"emp"=>"Nom du promoteur:",
					"date"=>"La date au`jourdui:",
					"zone"=>"Zone location du client:",
					"mise"=>"Mise choisi par le client:",);
				foreach($_POST as $field=>$value){
					if(empty($_POST[$field])){
						if($field != "noclcam"){
							$blanks[$field] = "blank";
						}
					}
					else{
						$value = trim($value);
						if($field == "NOM"){
							if(!ereg("^[A-Za-z0-9’ .-]{1,65}$",$value)){
								$formats[$field] = "bad";
							}
						}
						elseif($field != "NOM"){
							if(!ereg("^[0-9]?",$value)){
								$formats[$field] = "bad";
							}
						}
					}
				}
				### if any fields were not okay, display error ###
				### message and redisplay form ###
				if (@sizeof($blanks) > 0 or @sizeof($formats) > 0){
					$template->set_filenames(array(
						'body' => 'new_client.html')
					);

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
					$template->assign_vars(array(
					    'ZONE' => $_SESSION['zone'],
						'MISE' => $_POST['mise'],
						'NOM' => $_POST['NOM'],
						'NOCLCAM' => $_POST['noclcam'],
					    'SIGNING_AGENT' => $_SESSION['emp'],
					    'SIGNING_DATE' => date("Y-m-d h:i:s"),));
				}
				else{
					$CL_ACCT=$_POST['noclcam'];
					$DESCR=$_POST['NOM'];
					$SIGNING_EMP=$_POST['emp'];
					$SIGNING_DATE=$_POST['date'];
					$GOAL=$_POST['mise'];
					$ZONE=$_POST['zone'];
					if(!isset($ZONE)){
					$ZONE= $_SESSION['zone'];
					}
					if(is_int($CL_ACCT)){
						$label ='`CL_ACCT`, ';
						$CL_ACCT.=', ';
					}
					else{
						$label ='';
						$CL_ACCT ='';
					}
					$List->query("INSERT INTO `account` 
								($label`DESCR`, `SIGNING_EMP`, `SIGNING_DATE`, `STATUS`) 
								VALUES
								($CL_ACCT '$DESCR', $SIGNING_EMP, NOW(), 'ACTIVE');");
					$List->query("SELECT LAST_INSERT_ID({$List->conn}) AS CID FROM `account` ORDER BY CID;");
					while($resultie = mysql_fetch_array($List->result)){$cid=$resultie[CID];}
						$List->query("INSERT INTO `cust_goal`
									(`CUST_ID`, `DATE`, `GOAL`)
									VALUES
									($cid, NOW(), $GOAL);");
						$List->query("INSERT INTO `cust_location`
									(`CUST_ID`, `DATE`, `ZONE`, `CLCAM_ID`)
									VALUES
									($cid, NOW(), $ZONE, 1);");
				/******************************************BUILD CLIENT IS DONE NOW DISPLAYING********************************************************/
						$List->query("SELECT ZONE FROM `cust_location` WHERE CUST_ID = $cid ;");
						while($resultie = mysql_fetch_array($List->result)){$test=$resultie[ZONE];}
						echo $test;
						
						$template->set_filenames(array(
							'body' => 'view_client.html')
							);
						$template->assign_block_vars('switch_cust_display', array());
						$List->query("CREATE TEMPORARY TABLE recent_date_zone 
							SELECT MAX( DATE ) AS max_date, CUST_ID 
							FROM cust_location
							WHERE CUST_ID= $cid
							GROUP BY CUST_ID;");
						$List->query("CREATE TEMPORARY TABLE current_zone 
							SELECT cust_location.CUST_ID, cust_location.ZONE
							FROM recent_date_zone, cust_location
							WHERE cust_location.DATE = recent_date_zone.max_date
							AND cust_location.CUST_ID = recent_date_zone.CUST_ID
							AND ZONE = $ZONE ;");
						$List->query("CREATE TEMPORARY TABLE recent_date_goal
							SELECT MAX( cust_goal.DATE ) AS max_date, cust_goal.CUST_ID
							FROM cust_goal, current_zone
							WHERE cust_goal.CUST_ID=current_zone.CUST_ID
							GROUP BY CUST_ID;");
						$List->query("CREATE TEMPORARY TABLE current_goal
							SELECT cust_goal.CUST_ID, cust_goal.GOAL
							FROM cust_goal, recent_date_goal, account
							WHERE cust_goal.DATE=recent_date_goal.max_date
							AND cust_goal.CUST_ID=recent_date_goal.CUST_ID
							AND account.CUST_ID=recent_date_goal.CUST_ID;");
						$List->query("SELECT account.CUST_ID, account.CL_ACCT, account.DESCR, account.SIGNING_EMP,
							account.SIGNING_DATE, account.STATUS, current_goal.GOAL
							FROM account, current_goal
							WHERE account.CUST_ID = current_goal.CUST_ID;");
						$List->dumpTable();
						$template->assign_vars(array(
							'CUST_ID' => $List->display[CUST_ID],
						    'CL_ACCT' => $List->display[CL_ACCT],
						    'DESCR' => $List->display[DESCR],
						    'STATUS' => $List->display[STATUS],
						    'SIGNING_AGENT' => $List->display[SIGNING_EMP],
						    'SIGNING_DATE' => $List->display[SIGNING_DATE],
						    'CURRENT_GOAL' => $List->display[GOAL],));
						$List->query("SELECT cust_info.CUST_ID, cust_info.FIRST_NAME, cust_info.LAST_NAME, cust_info.D_O_B, cust_info.OCCUPATION, cust_info.SEX, cust_info.UNIQUEID,
							current_goal.GOAL, current_goal.CUST_ID
							FROM cust_info, current_goal
							WHERE cust_info.CUST_ID = current_goal.CUST_ID
							;");
						while($resultie = mysql_fetch_array($List->result)){
							if (mysql_errno ( )){
								die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
							}	
							$template->assign_block_vars('switch_cust_display.person',	array(
								'UNIQUE' => $resultie[UNIQUEID],
								'FIRST_NAME' => $resultie[FIRST_NAME],
								'LAST_NAME' => $resultie[LAST_NAME],
								'D_O_B' => $resultie[D_O_B],
								'OCCUPATION' => $resultie[OCCUPATION],
								'SEX' => $resultie[SEX],)
								);				
						}
						if($_SESSION['access']>3 and $_SESSION['access']<7){
								$template->assign_block_vars('switch_cust_display.goal_edit', array());
							}
						else{
								$template->assign_block_vars('switch_cust_display.goal', array());
							}
						//================== Shockingly building the data for a month of deposits turns out to be the shortest query on the page
						$template->assign_block_vars('switch_cust_display.a',	array());
						if($_POST['date']){
						$template->assign_vars(array(
							'DATE'=>date("M-Y", $_POST['date']),
							'NoDATE'=>$_POST['date'],));
							$List->display[DATE]=date("M-Y", $_POST['date']);
							$List->date($cid,$caller_file,$_POST['date']);
							$m=date(n,$_POST['date']);
							$y=date(Y,$_POST['date']);
							if($_SESSION['access']>1 and $_SESSION['access']<5){
								$template->assign_block_vars('switch_cust_display.pull', array());
							}
						}
						else{
						$template->assign_vars(array(
							'DATE'=> date("M-Y"),));
							$List->display[DATE]=date("M-Y");
							$List->date($cid,$caller_file,date(U));
							$m='MONTH(CURDATE( ))';
							$y='YEAR(CURDATE( ))';
						}
					
						if($_POST['date']){
							$List->query("CREATE TEMPORARY TABLE goal_hist
								SELECT DATE , CUST_ID, GOAL
								FROM cust_goal
								WHERE CUST_ID= $cid
								AND DATE <= '$y-$m-01';");
							$List->query("CREATE TEMPORARY TABLE goal_for_date
								SELECT MAX(DATE) AS max_date, CUST_ID
								FROM goal_hist
								GROUP BY CUST_ID;");
							$List->query("SELECT cust_goal.GOAL, cust_goal.CUST_ID
								FROM cust_goal,goal_for_date
								WHERE cust_goal.CUST_ID=goal_for_date.CUST_ID
								AND cust_goal.DATE=goal_for_date.max_date
								GROUP BY CUST_ID;");
							while($resultie = mysql_fetch_array($List->result)){$List->display[GOAL]=$resultie[GOAL];}
							$List->query("SELECT AMOUNT, VERIFY, STATUS 
								FROM deposit
								WHERE MONTH(DATE) = $m
								AND YEAR(DATE) =$y
								AND CUST_ID ='{$List->display[CUST_ID]}';");
							$List->dumpDeposits();
							$List->dumpBooklet($List->display[GOAL]);
						}
						else{
							$List->query("SELECT AMOUNT, VERIFY, STATUS 
								FROM deposit
								WHERE MONTH(DATE) = $m
								AND YEAR(DATE) =$y
								AND CUST_ID ='{$List->display[CUST_ID]}'");
							$List->dumpDeposits();
							$List->dumpBooklet($List->display[GOAL]);	
						}
						$template->assign_vars(array(
							'UNVERIFIED'=> $List->funds_unverified,
							'WITHDRAWN'=>$List->funds_withdrawn,
							'GROSS'=>$List->funds_available,
							'SHOWING_GOAL'=>$List->display[GOAL],
							'NET'=>$List->funds_available-$List->display[GOAL],
							));	
						/*****************************Generates 35 entries for the booklet table with a default empty option**************************/
							for($b=1;$b<=31;$b++){
								if($List->entries_available>0){
										$template->assign_vars(array(
											$b => 'a',)
										);
										$List->entries_available--;
									}
								else{
									if($List->entries_unverified>0){
										$template->assign_vars(array(
											$b => 'b',)
										);
										$List->entries_unverified--;
									}
									else{
										if($List->entries_withdrawn>0){
												$template->assign_vars(array(
													$b => 'c',)
												);
												$List->entries_withdrawn--;
											}
										else{
											$template->assign_vars(array(
												$b => 'd',)
											);
										}
									}
								}
							}
						/*****************************************************************************************************************/
						For($J=0; $J<=sizeof($List->worddate); $J++){
						$template->assign_block_vars('switch_cust_display.drop',	array(
												'DATE_NAME' => $List->worddate[$J],
												'DATE_VALUE' => $List->newdate[$J],)
											);
						
						}
				}
			}
			else{
				$template->set_filenames(array(
					'body' => 'new_client.html')
				);
				$template->assign_vars(array(
					    'ZONE' => $_SESSION['zone'],
						'MISE' => $_POST['mise'],
						'NOM' => $_POST['NOM'],
						'NOCLCAM' => $_POST['noclcam'],
					    'SIGNING_AGENT' => $_SESSION['emp'],
					    'SIGNING_DATE' => date("Y-m-d h:i:s"),));
				if($_SESSION['access']<2){
				$template->assign_block_vars('disabled',	array());
				}
			}
			
			
		}	
			
		$template->pparse('body');
?>