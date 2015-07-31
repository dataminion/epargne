<?php 
		if ($_POST['submit']){
			session_start();
			include "class_lib.php";
			include "classes/page.php";
		}
		include'classes/my_customers.php';
		$List=new client();
			if ($_POST['submit']){
				$cid=$_POST['cid'];
				$m=date(n,$_POST['date']);
				$y=date(Y,$_POST['date']);
				$List->query("SELECT TRANS_ID 
					FROM deposit
					WHERE MONTH(DATE) = $m
					AND YEAR(DATE) =$y
					AND CUST_ID =$cid;");
				while($resultie = mysql_fetch_array($List->result)){
					$trans[] = $resultie['TRANS_ID'];
				}
				if(sizeof($trans)==0){
				//ERROR
					header("location: index.php?pageload=15");
				}
				$values="";
				for($K=0;$K<=sizeof($trans)-2;$K++){
					$values.='('.$trans[$K].'),';
				}
				$values.='('.$trans[sizeof($trans)-1].')';	
				$List->query("INSERT INTO withdraw 
					(`CUST_ID`,`AMOUNT`,`PERIOD`,`EMP_ID`,`CLCAM_ID`,`CHECK`)
					VALUES 
					($cid,{$_POST['NET']},'$y-$m-0',{$_SESSION['emp']},1, {$_POST['check']});");
					  
				$List->query("CREATE TEMPORARY TABLE edit_values (TRANS_ID INT);");
				$List->query("INSERT INTO edit_values (TRANS_ID)
					VALUES $values;");
				$List->query("UPDATE deposit, edit_values
					SET deposit.STATUS =1
					WHERE deposit.TRANS_ID=edit_values.TRANS_ID;");
				//Finished
				
				header("location: index.php?pageload=7&cid=$cid");
			}
			else{
				$cid=$_POST['cid'];
				$m=date(n,$_POST['date']);
				$y=date(Y,$_POST['date']);
				$List->query("SELECT AMOUNT, VERIFY, STATUS 
					FROM deposit
					WHERE MONTH(DATE) = $m
					AND YEAR(DATE) =$y
					AND CUST_ID =$cid;");
				$withdrawn = false;
				$unconfirmed =false;
				while($resultie = mysql_fetch_array($List->result)){
					if($resultie['STATUS']==1){
						$withdrawn = true;
					}
					if($resultie['VERIFY']==0){
						$unconfirmed =true;
					}
				}
				if($withdrawn || $unconfirmed){
					$template->set_filenames(array(
						'body' => 'error_withdraw.html')
					);
					if($withdrawn == true){
						$template->assign_block_vars('switch_withdrawn_error',	array());
					}
					if($unconfirmed == true){
						$template->assign_block_vars('switch_verify_error',	array());		
					}
					$template->pparse('body');
				}
				else{
					$template->set_filenames(array(
						'body' => 'print_withdraw.html')
					);
					$template->assign_vars(array(
						'CUST_ID' => $_POST['cid'],
						'NoDATE' => $_POST['date'],
						));
					
					$template->assign_block_vars('a',	array());
					$List->query("CREATE TEMPORARY TABLE goal_hist
						SELECT DATE , CUST_ID, GOAL
						FROM cust_goal
						WHERE CUST_ID= $cid
						AND DATE <= '$y-$m-31';");
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
						AND CUST_ID =$cid;");			
					$List->dumpDeposits();
					$List->dumpBooklet($List->display[GOAL]);
					$template->assign_vars(array(
						'DATE'=>date("M-Y", $_POST['date']),
						'UNVERIFIED'=> $List->funds_unverified,
						'WITHDRAWN'=>$List->funds_withdrawn,
						'GROSS'=>$List->funds_available,
						'SHOWING_GOAL'=>$List->display[GOAL],
						'NET'=>$List->funds_available-$List->display[GOAL],
						'TOTAL'=>($List->funds_available-$List->display[GOAL]),
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
				$template->pparse('body');
				}
			}
?>