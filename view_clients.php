<?php
session_start();
		if(!$_SESSION['islogin'])
		{
			include "template.php";
			$template= new template('html');
			$template->set_filenames(array(
				'login' => 'login.html')
			);
			$template->assign_block_vars('switch_login_fails',	array());
			$template->pparse('login');
			$template->set_filenames(array(
				'footer' => 'footer.html')
			);
			$template->pparse('footer');
			exit();
		}
	
		if($_GET['zid']){
		unset($_SESSION['zone']);
		$_SESSION['zone']=$_GET['zid'];
		}
	if(!$_SESSION['zone']){
		if($_SESSION['access']>1){
			include_once "view_all_clients.php";
		}
		else{
			$template->set_filenames(array(
				'body' => 'zone_error.html')
				);

			$template->pparse('body');
		}
	}
	else{
		include'classes/my_customers.php';
		$List=new client();
		
		if($_GET['pageload']==7){
			$template->set_filenames(array(
				'body' => 'view_client.html')
			);
			
			
			if($_GET['cid']||$_POST['cid']||$_POST['scid']){
				include"classes/deposit.php";
				$test= new deposit();
				$cid=$_GET['cid']+$_POST['cid'];
				if($_POST['scid']){
					$cid= $test->is_cust($_POST['scid'],$_POST['szone']);
					if($cid==0){
						$template->assign_vars(array(
						'ERROR' => "Votre donnee n'est pas valide",
						));
						$template->assign_block_vars('switch_cust_search', array());
						if($_SESSION['access']>1){
							$template->assign_block_vars('switch_cust_search.switch_zone', array());
						}
						$template->pparse('body');
						$template->set_filenames(array(
							'footer' => 'footer.html')
						);
						$template->pparse('footer');
						exit();
					}
				}	
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
					AND ZONE = {$_SESSION['zone']};");
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
					'EMP_ID' => $_SESSION['emp'],
				    'CL_ACCT' => $List->display[CL_ACCT],
				    'DESCR' => $List->display[DESCR],
				    'STATUS' => $List->display[STATUS],
				    'SIGNING_AGENT' => $List->display[SIGNING_EMP],
				    'SIGNING_DATE' => $List->display[SIGNING_DATE],
				    'CURRENT_GOAL' => $List->display[GOAL],));
				//================== Finds signatories and displays them
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
				if(!$resultie){$template->assign_block_vars('switch_cust_display.addperson', array());}
				if($_SESSION['access']>3 and $_SESSION['access']<7){
						$template->assign_block_vars('switch_cust_display.goal_edit', array());
					}
				else{
						$template->assign_block_vars('switch_cust_display.goal', array());
					}
				//================== Building the data for a month of deposits
				$template->assign_block_vars('switch_cust_display.a',	array());
				if($_POST['date']){
				$template->assign_vars(array(
					'DATE'=>date("M-Y", $_POST['date']),
					'NoDATE'=>$_POST['date'],));
					$List->display[DATE]=date("M-Y", $_POST['date']);
					$List->date($cid,$caller_file,$_POST['date']);
					$m=date(n,$_POST['date']);
					$y=date(Y,$_POST['date']);
					if($_SESSION['access']>1 and $_SESSION['access']<9){
						$template->assign_block_vars('switch_cust_display.a.pull', array());
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
					'SHOWING_DATE'=>$List->display[DATE],
					'SHOWING_GOAL'=>$List->display[GOAL],
					'NET'=>$List->funds_available+$List->funds_withdrawn-$List->display[GOAL],
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
				$template->assign_block_vars('switch_cust_display.a.drop',	array(
										'DATE_NAME' => $List->worddate[$J],
										'DATE_VALUE' => $List->newdate[$J],)
									);
				}
			}
			else{
				$template->assign_block_vars('switch_cust_search', array());
				if($_SESSION['access']>1){
					$template->assign_block_vars('switch_cust_search.switch_zone', array());
				}
			}
		}
		
/******************************GENERATES CLIENT LIST FOR CURRENT ZONE***********************************************************************************/
		
		else if($_GET['pageload']==17){
			$template->set_filenames(array(
						'body' => 'dump_all_clients.html')
					);
			$List->query("CREATE TEMPORARY TABLE recent_date_zone 
				SELECT MAX( DATE ) AS max_date, CUST_ID 
				FROM cust_location
				GROUP BY CUST_ID;");
			$List->query("CREATE TEMPORARY TABLE current_zone 
				SELECT cust_location.CUST_ID, cust_location.ZONE
				FROM recent_date_zone, cust_location
				WHERE cust_location.DATE = recent_date_zone.max_date
				AND cust_location.CUST_ID=recent_date_zone.CUST_ID
				AND ZONE = {$_SESSION['zone']};");
			$List->query("CREATE TEMPORARY TABLE recent_date_goal
				SELECT MAX( cust_goal.DATE ) AS max_date, cust_goal.CUST_ID, cust_goal.GOAL
				FROM cust_goal, current_zone
				WHERE cust_goal.CUST_ID=current_zone.CUST_ID
				GROUP BY CUST_ID;");
			$List->query("CREATE TEMPORARY TABLE cust_lst(
				id INT NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (id))	
				SELECT recent_date_goal.CUST_ID, recent_date_goal.GOAL, account.DESCR
				FROM recent_date_goal, account
				WHERE account.CUST_ID=recent_date_goal.CUST_ID
				ORDER BY account.CUST_ID asc;");
			$List->query("SELECT id, CUST_ID,DESCR, GOAL
				FROM cust_lst
				ORDER BY CUST_ID ASC;");
			while($resultie = mysql_fetch_array($List->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				$contenttest=sizeof($resultie);
				$template->assign_vars(array(
					'ZONE' => $_SESSION['zone'],));					
				$template->assign_block_vars('line',	array(
					'SWITCH' => $switch,
					'ID'=> $resultie[id],
					'CUST_ID' => $resultie[CUST_ID],
					'NAME' => $resultie[DESCR],
					'GOAL' => $resultie[GOAL],)
				);
				if(isset($switch)){
					unset($switch);
				}
				else{
				$switch='line';
				}
				if(!$contenttest){
					$template->assign_block_vars('line',	array(
						'NO_RESULT' => 'No Result')
					);
					}
			}	
				
		}
		else{
		//================== Initial set of queries generates a client list with current goals to match the location of the user 
			$template->set_filenames(array(
					'body' => 'list_client.html')
				);
			$List->query("CREATE TEMPORARY TABLE recent_date_zone 
				SELECT MAX( DATE ) AS max_date, CUST_ID 
				FROM cust_location
				GROUP BY CUST_ID;");
			$List->query("CREATE TEMPORARY TABLE current_zone 
				SELECT cust_location.CUST_ID, cust_location.ZONE
				FROM recent_date_zone, cust_location
				WHERE cust_location.DATE = recent_date_zone.max_date
				AND cust_location.CUST_ID=recent_date_zone.CUST_ID
				AND ZONE = {$_SESSION['zone']};");
			$List->query("CREATE TEMPORARY TABLE recent_date_goal
				SELECT MAX( cust_goal.DATE ) AS max_date, cust_goal.CUST_ID, cust_goal.GOAL
				FROM cust_goal, current_zone
				WHERE cust_goal.CUST_ID=current_zone.CUST_ID
				GROUP BY CUST_ID;");
				
			$List->query("CREATE TEMPORARY TABLE cust_lst(
				id INT NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (id))	
				SELECT recent_date_goal.CUST_ID, recent_date_goal.GOAL, account.DESCR
				FROM recent_date_goal, account
				WHERE account.CUST_ID=recent_date_goal.CUST_ID
				ORDER BY account.CUST_ID asc;");
			//================== Final query handles a basic sort by for the initial customer list
			/*
			**This is more complete now.
			**This could usesome testing to make sure that it continues to work
			**I would like to see some filters added e.g. "select only female clients"
			*/

			//==========Set some data to make a next/prev page navigation
			$List->query("SELECT COUNT(id)
				FROM cust_lst;");
			$nav= array(
			'START'=> (!$_GET['page']? 0 : ($_GET['page']*20)-20),
			'FINISH'=> (!$_GET['page']? 20 : ($_GET['page']*20)),
			'TOTAL_PAGES'=>(!is_int(mysql_result($List->result, 0)/20)? intval(mysql_result($List->result, 0)/20)+1:mysql_result($List->result, 0)/20),
			'CURRENT_PAGE'=> (!$_GET['page']? 1 : $_GET['page']),
			);
			//===========Set final queries for Ordering collums			
			switch($_GET['orderby']){
				case "id":
					$orderby = "ORDER BY id";
					if($_GET['dir']){$orderby.=" ASC";}
					else{$orderby.=" DESC";}
					echo $orderby;
					break;
				case "name":
					$orderby = "ORDER BY DESCR";
					if($_GET['dir']){$orderby.=" ASC";}
					else{$orderby.=" DESC";}
					break;
				case "goal":
					$orderby = "ORDER BY GOAL";
					if($_GET['dir']){$orderby.=" ASC";}
					else{$orderby.=" DESC";}
					break;
				default:
					unset($orderby);
					break;
				}		
				$List->query("SELECT id, CUST_ID,DESCR, GOAL
					FROM cust_lst
					$orderby
					LIMIT {$nav['START']},20;");
			
			//===============================================================================
			while($resultie = mysql_fetch_array($List->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				$contenttest=sizeof($resultie);
				$template->assign_vars(array(
					'ZONE' => $_SESSION['zone'],));					
				$template->assign_block_vars('line',	array(
					'SWITCH' => $switch,
					'ID'=> $resultie[id],
					'CUST_ID' => $resultie[CUST_ID],
					'NAME' => $resultie[DESCR],
					'GOAL' => $resultie[GOAL],)
				);
				if(isset($switch)){
					unset($switch);
				}
				else{
				$switch='line';
				}
				if(!$contenttest){
					$template->assign_block_vars('line',	array(
						'NO_RESULT' => 'No Result')
					);
					}
			}
			//===============================Assign all of the formatting for list links
			if($nav['CURRENT_PAGE']>1){
				$template->assign_block_vars('prev',	array(
						'ORDERBY' => $_GET['orderby'],
						'DIRECTION' => $_GET['dir'],
						'NEW_PAGE' => $nav['CURRENT_PAGE']-1,)
					);
				}
				
			for($j=($nav['CURRENT_PAGE']-3<=0? 1:$nav['CURRENT_PAGE']-3);
				$j<=(($nav['CURRENT_PAGE']+3)>=$nav['TOTAL_PAGES']?$nav['TOTAL_PAGES']:$nav['CURRENT_PAGE']+3);
				$j++){
				$bold= j==$nav['CURRENT_PAGE']? "":"bold";
				$template->assign_block_vars('page',	array(
						'ORDERBY' => $_GET['orderby'],
						'DIRECTION' => $_GET['dir'],
						'BOLD' => $bold,
						'NEW_PAGE' => $j,)
					);
				unset($bold);
				}
			if($nav['CURRENT_PAGE']<$nav['TOTAL_PAGES']){
				$template->assign_block_vars('next',	array(
						'ORDERBY' => $_GET['orderby'],
						'DIRECTION' => $_GET['dir'],
						'NEW_PAGE' => $nav['CURRENT_PAGE']+1,)
					);
				}
			}
			
		$template->pparse('body');
	}
?>