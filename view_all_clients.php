<?php 
	include'classes/my_customers.php';
	$List=new client();
	
	if($_GET['pageload']){
		$template->set_filenames(array(
				'body' => 'zone_choice.html')
			);
	//================== Initial set of queries generates the active zones and the promoters assigned to them
		$List->query("CREATE TEMPORARY TABLE zones
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
		$List->query("SELECT most_recent_emp.EMP_ID, emp_info.FIRST_NAME, emp_info.LAST_NAME, most_recent_emp.ZONE
			FROM most_recent_emp, emp_info
			WHERE emp_info.EMP_ID = most_recent_emp.EMP_ID
			ORDER BY most_recent_emp.ZONE ASC;");
	//===============================================================================
		while($resultie = mysql_fetch_array($List->result)){
			if (mysql_errno ( )){
				die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),				mysql_errno ( )));
			}
			$contenttest=sizeof($resultie);
			$template->assign_block_vars('line',	array(
				'SWITCH' => $switch,
				'CUST_ID' => $resultie[EMP_ID],
				'FNAME' => $resultie[FIRST_NAME],
				'LNAME' => $resultie[LAST_NAME],
				'ZONE' => $resultie[ZONE],)
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
	$template->pparse('body');
?>