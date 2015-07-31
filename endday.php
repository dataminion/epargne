<?php
		if(!$_SESSION['islogin'])
		{
			include 'login.php';
			exit();
		}
		include'classes/my_customers.php';
		$List=new client();
		
		if($_SESSION['access']>1){
			$template->set_filenames(array(
				'body' => 'endday.html')
			);
			$template->assign_vars(array(
					'DATE' => date("Y-m-d"),));	
			
			//================== Initial query generates a list of zones and passes them to the customers query
			//=== not sure if the following is useful
			/*$List->query("CREATE TEMPORARY TABLE zones
				SELECT DISTINCT ZONE  
				FROM cust_location
				WHERE CLCAM_ID= 1
				ORDER BY ZONE;");
			$List->query("CREATE TEMPORARY TABLE dates
				SELECT MAX(emp_loc.DATE) AS MXDATE, emp_loc.ZONE  
				FROM emp_loc, zones
				WHERE emp_loc.ZONE = zones.ZONE
				GROUP BY ZONE;");
			$List->query("CREATE TEMPORARY TABLE most_recent_emp
				SELECT emp_loc.DATE, emp_loc.EMP_ID, emp_loc.ZONE
				FROM emp_loc, dates
				WHERE emp_loc.DATE = dates.MXDATE
				AND emp_loc.ZONE = dates.ZONE;");
			$List->query("SELECT ZONE FROM most_recent_emp;");*/
			/************do some code here about zone storage?*****/
			
			//================== Queries sum transactions and total deposits
			$List->query("CREATE TEMPORARY TABLE recent_date_zone 
				SELECT MAX( DATE ) AS max_date, CUST_ID 
				FROM cust_location
				GROUP BY CUST_ID;");
			$List->query("CREATE TEMPORARY TABLE current_zone 
				SELECT cust_location.CUST_ID, cust_location.ZONE
				FROM recent_date_zone, cust_location
				WHERE cust_location.DATE = recent_date_zone.max_date
				AND cust_location.CUST_ID=recent_date_zone.CUST_ID;"); 
			$List->query("SELECT current_zone.ZONE, COUNT(deposit.TRANS_ID) AS TTRANS, SUM(deposit.AMOUNT) AS TDEPOSIT
				FROM deposit, current_zone
				WHERE deposit.DATE > CURDATE( )
				AND deposit.CUST_ID = current_zone.CUST_ID
				GROUP BY current_zone.ZONE;");			
			while($resultie = mysql_fetch_array($List->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				$template->assign_block_vars('deposit',	array(
				'SWITCH' => $switch,
				'ZONE' => $resultie['ZONE'],
				'TRANSACTION' => $resultie['TTRANS'],
				'DEPOSIT' => $resultie['TDEPOSIT'],)
			);
			}	
			
			$List->query("SELECT current_zone.ZONE, COUNT(withdraw.TRANS_ID) AS TTRANS, SUM(withdraw.AMOUNT) AS TWITH
				FROM withdraw, current_zone
				WHERE withdraw.DATE > CURDATE( )
				AND withdraw.CUST_ID = current_zone.CUST_ID
				GROUP BY current_zone.ZONE;	");
			while($resultie = mysql_fetch_array($List->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				$template->assign_block_vars('withdraw',	array(
				'SWITCH' => $switch,
				'ZONE' => $resultie['ZONE'],
				'TRANSACTION' => $resultie['TTRANS'],
				'TWITH' => $resultie['TWITH'],)
			);
			}	
		}
		else{
			$template->set_filenames(array(
				'body' => 'endday_prom.html')
			);
			$template->assign_vars(array(
					'DATE' => date("Y-m-d"),));	
			$EMP=$_SESSION['emp'];
			$List->query("SELECT LAST_NAME, FIRST_NAME
				FROM emp_info
				WHERE EMP_ID = $EMP;");	
			while($resultie = mysql_fetch_array($List->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				$template->assign_vars(array(
				'FIRST' => $resultie['LAST_NAME'],
				'LAST' => $resultie['FIRST_NAME'],));
			}
			
			$List->query("SELECT COUNT(deposit.TRANS_ID) AS TTRANS, SUM(deposit.AMOUNT) AS TDEPOSIT
				FROM deposit
				WHERE deposit.DATE > CURDATE( )
				AND deposit.EMP_ID = $EMP;");			
			while($resultie = mysql_fetch_array($List->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				$template->assign_block_vars('deposit',	array(
				'SWITCH' => $switch,
				'ZONE' => $_SESSION['zone'],
				'TRANSACTION' => $resultie['TTRANS'],
				'DEPOSIT' => $resultie['TDEPOSIT'],)
				);
			}
			
			$List->query("SELECT COUNT(deposit.TRANS_ID) AS TTRANS, SUM(deposit.AMOUNT) AS TDEPOSIT, DATE
				FROM deposit
				WHERE deposit.EMP_ID = $EMP
				AND deposit.DATE > CURDATE( )
				GROUP BY DATE;");	
			$switch="a";
			$first=0;
			while($resultie = mysql_fetch_array($List->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				if($first==0){
					$template->assign_block_vars('deposit_by_page',	array());
					$first++;
				}
				$template->assign_block_vars('deposit_by_page.line',	array(
				'SWITCH' => $switch,
				'TRANSACTION' => $resultie['TTRANS'],
				'DEPOSIT' => $resultie['TDEPOSIT'],)
				);
				if($switch=="a"){$switch="b";}
				else{$switch="a";}
			}
			
			
		}
		$template->pparse('body');
?>
