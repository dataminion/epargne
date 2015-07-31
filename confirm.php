<?php
	if($_POST['confirm']||$_POST['edit']||$_POST['delete'] ){
		include "class_lib.php";
		include "classes/page.php";
	}
	include'classes/my_customers.php';
	$List=new client();
	if($_GET['zid']){
		unset($_SESSION['zone']);
		$_SESSION['zone']=$_GET['zid'];
	}
	if($_POST['confirm']){
	for($J=1;$J<=$_POST['nTrans'];$J++){
			if($_POST['check'.$J]){
				$trans[]=$_POST['tran'.$J];
			}
		}
		if(sizeof($trans)==0){
			header("location: index.php?pageload=15");
		}
		$values="";
		for($K=0;$K<=sizeof($trans)-2;$K++){
		$values.='('.$trans[$K].'),';
		}
		$values.='('.$trans[sizeof($trans)-1].')';	
		
		$List->query("CREATE TEMPORARY TABLE edit_values (TRANS_ID INT);");
		$List->query("INSERT INTO edit_values (TRANS_ID)
			VALUES $values;");
		$List->query("UPDATE deposit, edit_values
			SET deposit.VERIFY =1
			WHERE deposit.TRANS_ID=edit_values.TRANS_ID;");
		header("location: index.php?pageload=15");
	}
	if($_POST['edit']){
		$_POST['editer']=1;
		header("location: index.php?pageload=15");
	}
	if($_POST['editer']){
		echo"holy shit that worked";
		$template->set_filenames(array(
			'body' => 'approval_table.html')
		);
		$template->pparse('body');
	}
	if($_POST['delete']){
		for($J=1;$J<=$_POST['nTrans'];$J++){
			if($_POST['check'.$J]){
				$trans[]=$_POST['tran'.$J];
			}
		}
		if(sizeof($trans)==0){
			header("location: index.php?pageload=15");
		}
		$values="";
		for($K=0;$K<=sizeof($trans)-2;$K++){
		$values.='('.$trans[$K].'),';
		}
		$values.='('.$trans[sizeof($trans)-1].')';	
		
		$List->query("CREATE TEMPORARY TABLE edit_values (TRANS_ID INT);");
		$List->query("INSERT INTO edit_values (TRANS_ID)
			VALUES $values;");
		$List->query("UPDATE deposit, edit_values
			SET deposit.AMOUNT =0 , 
			deposit.VERIFY =1
			WHERE deposit.TRANS_ID=edit_values.TRANS_ID;");
			
		header("location: index.php?pageload=15");
	}
	else{
		$template->set_filenames(array(
			'body' => 'approval_table.html')
		);
		$List->query("CREATE TEMPORARY TABLE recent_date_zone SELECT MAX( DATE ) AS max_date, CUST_ID, ZONE
			FROM cust_location
			GROUP BY CUST_ID
			ORDER BY ZONE DESC;");
		$List->query("SET @rank =0,
			@prev_val = 1;");
		$List->query("CREATE TEMPORARY TABLE current_zone 
			SELECT @rank := IF( @prev_val = ZONE , @rank+1, 1 ) AS rank, @prev_val := ZONE AS ZONE, CUST_ID
			FROM recent_date_zone
			ORDER BY max_date, ZONE DESC;");
		$List->query("CREATE TEMPORARY TABLE goal_date
			SELECT MAX( cust_goal.DATE ) AS max_date, cust_goal.CUST_ID, current_zone.rank, current_zone.ZONE
			FROM cust_goal, deposit, current_zone
			WHERE VERIFY=0
			AND cust_goal.CUST_ID=deposit.CUST_ID
			AND cust_goal.CUST_ID=current_zone.CUST_ID
			GROUP BY CUST_ID;");			
		$List->query("SELECT  deposit.TRANS_ID, goal_date.ZONE, goal_date.rank, deposit.CUST_ID, account.DESCR, emp_info.USER_ID, YEAR(deposit.DATE) AS tyear, MONTH(deposit.DATE) AS tmonth, 
			DAY(deposit.DATE) AS tday, deposit.AMOUNT, cust_goal.GOAL, deposit.VERIFY
			FROM deposit,account, goal_date, cust_goal, emp_info
			WHERE cust_goal.DATE=goal_date.max_date
			AND deposit.CUST_ID=goal_date.CUST_ID
			AND cust_goal.CUST_ID=goal_date.CUST_ID
			AND account.CUST_ID=deposit.CUST_ID
			AND deposit.EMP_ID=emp_info.EMP_ID
			AND deposit.VERIFY = 0
			ORDER BY tyear ASC, tmonth ASC, tday ASC, goal_date.ZONE;");
			
		$switch=array();
		$zwitch=array();
		$a=1;
		$form=1;	
		$counter=0;
		while($resultie=mysql_fetch_array($List->result)){
			$counter++;
			if($form==1){
				$template->assign_block_vars('Date',array()	);
				unset($form);
			}
			if (mysql_errno ( )){
				die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
			}
			if(isset($switch[$resultie[tyear].$resultie[tmonth].$resultie[tday]])){
					if ($switch[$resultie[tyear].$resultie[tmonth].$resultie[tday]]==1){
						$line="line";
						$switch[$resultie[tyear].$resultie[tmonth].$resultie[tday]]++;
					}
					else{
						$line="noline";
						$switch[$resultie[tyear].$resultie[tmonth].$resultie[tday]]--;
					}
				
			}
			else{
			$line="noline";
			unset($zwitch);
			$zwitch=array();
			$switch[$resultie[tyear].$resultie[tmonth].$resultie[tday]]=1;
			$template->assign_block_vars('Date.Block',	array(
				'YEAR' => $resultie[tyear],
				'MONTH' => $resultie[tmonth],
				'DAY' => $resultie[tday],)
			);
			
			}
			if(!isset($zwitch[$resultie[ZONE]])){
				$zwitch[$resultie[ZONE]]=1;
				$template->assign_block_vars('Date.Block.Zone',	array(
					'ZONE' => $resultie[ZONE],)
				);
			
			}
			if(isset($resultie[CUST_ID])){
			$template->assign_block_vars('Date.Block.Zone.Data',	array(
				'LINE' => $line,
				'ROW' => $a,
				'HIDDEN' => $resultie[TRANS_ID],
				'USER' => $resultie[USER_ID],
				'ID' => $resultie[rank],
				'CLIENT_ID' => $resultie[CUST_ID],
				'CLIENT_NAME' => $resultie[DESCR],
				'DEPOSIT' => $resultie[AMOUNT],
				'GOAL' => $resultie[GOAL],)
			);
			}
			
			$a++;
		}
		if(mysql_affected_rows()==0){
				$template->assign_block_vars('nodata',	array()	);
				}		
		$template->assign_vars(array(
				'NTRANS' => $counter,
				'ROWS' => $a-1,)
			);
	}
	$template->pparse('body');
?>