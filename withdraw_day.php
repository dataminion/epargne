<?php
	if(!$_POST['final']){
	$template->set_filenames(array(
		'body' => 'day_withdraw.html')
	);
	}
	if($complete){
		$template->assign_block_vars('switch_deposit_done', array());
			unset($loop);
	}
	
	if($_POST['cancel']){
		$data=$_POST['input'];
		$template->assign_vars(array(
			'HIDDEN' => $data,));	
		$template->assign_block_vars('switch_post_entries', array());
		$row = explode("|",$data);
		for($j=0;$j<sizeof($row);$j++){
		$field= explode(".",$row[$j]);
		if($field[0]){
			$template->assign_block_vars('switch_post_entries.row', array(
				'CLIENT_ID' => $field[0],
				'GOAL' => $field[1],
				'POSITION' => $j+1, ));
		}
		unset($field);
		}
	}
	else{
		//======We have our first value lets build the hidden and display it
		if($_POST['add'] && !$_POST['input']){
			$template->assign_vars(array(
							'POSITION' => "",
							'HIDDEN' =>$_POST['month1'].'-'.$_POST['day1'].'-'.$_POST['year1'].'.'.$_POST['CLIENT_ID'].'.'.$_POST['CHECK'].'.'.$_POST['month2'].'-'.$_POST['year2'].'.'.$_POST['MONTANT'].'|',));	
			$template->assign_block_vars('switch_post_entries', array());
			$template->assign_block_vars('switch_post_entries.row', array(
							'DATE1' => $_POST['month1'].'-'.$_POST['day1'].'-'.$_POST['year1'],
							'CLIENT_ID' => $_POST['CLIENT_ID'],
							'CHECK' => $_POST['CHECK'],
							'DATE2' => $_POST['month2'].'-'.$_POST['year2'],
							'MONTANT' => $_POST['MONTANT'],
							'POSITION' => "1",));
			$template->assign_block_vars('switch_submit_final', array());
		}
		//======We now have multiple values to break down and assign
		else if($_POST['add']&&$_POST['input']&&!$_POST['position']){
			$data=$_POST['input'];
			
			$data.=$_POST['month1'].'-'.$_POST['day1'].'-'.$_POST['year1'].'.'.$_POST['CLIENT_ID'].'.'.$_POST['CHECK'].'.'.$_POST['month2'].'-'.$_POST['year2'].'.'.$_POST['MONTANT'].'|';
			$template->assign_vars(array(
				'HIDDEN' => $data,));	
			$template->assign_block_vars('switch_post_entries', array());
			$row = explode("|",$data);
			for($j=0;$j<sizeof($row);$j++){
			$field= explode(".",$row[$j]);
			if($field[0]){
				$template->assign_block_vars('switch_post_entries.row', array(
					'LINE' => $switch,
					'DATE1' => $field[0],
					'CLIENT_ID' => $field[1],
					'CHECK' => $field[2],
					'DATE2' => $field[3],
					'MONTANT' => $field[4],
					'POSITION' => $j+1, ));
			}
			unset($field);
				if(isset($switch)){
					unset($switch);
				}
				else{
					$switch='line';
				}
			}
			$template->assign_block_vars('switch_submit_final', array());
		}
		//======Now lets change the value in a row
		else if($_POST['edit']){
		$template->assign_vars(array(
			'HIDDEN' => $_POST['input'],
			'POSITION' => $_POST['row'],));
		$row = explode("|",$_POST['input']);
		$field= explode(".",$row[$_POST['row']-1]);
		$template->assign_block_vars('switch_edit_entry', array(
					'DATE1' => $field[0],
					'CLIENT_ID' => $field[1],
					'CHECK' => $field[2],
					'DATE2' => $field[3],
					'MONTANT' => $field[4],));
		/*
		//Shows the chosen position number and the hidden string
		echo $_POST['row'];
		echo"<br />";
		echo $_POST['input'];
		echo"<br />"	;
		
		*/
		}
		##Some Processing for the value change
		else if($_POST['add']&&$_POST['position']){
			$data=$_POST['input'];
			$row = explode("|",$data);
			$row[$_POST['position']-1]= $_POST[CLIENT_ID].'.'.$_POST[GOAL];
			$template->assign_vars(array(
					'HIDDEN' => implode("|",$row),));
			$template->assign_block_vars('switch_post_entries', array());
			for($j=0;$j<sizeof($row);$j++){
				$field= explode(".",$row[$j]);
				if($field[0]){
					$template->assign_block_vars('switch_post_entries.row', array(
						'LINE' => $switch,
						'DATE1' => $field[0],
						'CLIENT_ID' => $field[1],
						'CHECK' => $field[2],
						'DATE2' => $field[3],
						'MONTANT' => $field[4],
						'POSITION' => $j+1, ));
				}
				unset($field);
				if(isset($switch)){
					unset($switch);
				}
				else{
					$switch='line';
				}
			}
			$template->assign_block_vars('switch_submit_final', array());	
		}
		// Here we delete a selected entry
		else if($_POST['remove']){
			$row = explode("|",$_POST['input']);
			unset($row[$_POST['row']-1]);
			$template->assign_vars(array(
					'HIDDEN' => implode("|",$row),));
			$template->assign_block_vars('switch_post_entries', array());
			for($j=0;$j<sizeof($row);$j++){
				$field= explode(".",$row[$j]);
				if($field[0]){
					$template->assign_block_vars('switch_post_entries.row', array(
						'LINE' => $switch,
						'DATE1' => $field[0],
						'CLIENT_ID' => $field[1],
						'CHECK' => $field[2],
						'DATE2' => $field[3],
						'MONTANT' => $field[4],
						'POSITION' => $j+1, ));
				}	
				unset($field);
				if(isset($switch)){
					unset($switch);
				}
				else{
					$switch='line';
				}
			}
			$template->assign_block_vars('switch_submit_final', array());		
		}
		// Here we have the final logic for corrections
		else if($_POST['final']){
			if($_POST['delete']){
				$row = explode("|",$_POST['fstring']);
				unset($row[$_POST['fposition']]);
				$data= implode("|",$row);
			}
			else if($_POST['replace']){
				$row = explode("|",$_POST['fstring']);
				$field= explode(".",$row[$_POST['fposition']]);
				$field[1]=$_POST['GOAL'];
				$row[$_POST['fposition']]= implode(".",$field);
				$data= implode("|",$row);
			}
			else{
				$data=$_POST['input'];
			}
			$template->set_filenames(array(
				'body' => 'final.html')
			);
			$template->assign_vars(array(
				'HIDDEN' => $data,));
			$row = explode("|",$data);
			if(sizeof($row)>1){
				include"classes/deposit.php";
				$test= new deposit();
				for($j=0;$j<sizeof($row)-1;$j++){
					$field= explode(".",$row[$j]);
				//==Pulls an entry and verifies that the customer is a customer in the zone
					$field[2]= $test->is_cust($field[0]);
				//==Pulls an entries goal out of the db and tests to see if the deposit is round
					$field[3]= $test->is_goal_round($field[0],$field[1]);
				//==Compares each entry to look for double customers
					$field[4]= $test->is_double($field[0],$row);
					if(!$field[2]||!$field[3]||!$field[4]){
					$template->assign_block_vars('error', array(
						'CLIENT_ID' => $field[0],
						'GOAL' => $field[1],
						'POSITION' => $j,));
					}
					if($field[2]!=1){
						$template->assign_block_vars('error.switch_not_cust', array());
					}
					if($field[3]!=1){
						$template->assign_block_vars('error.switch_not_round_goal', array());
					}
					if($field[4]!=1){
					$template->assign_block_vars('error.switch_is_double', array());
					}
					if($field[2]+$field[3]+$field[4]==3){
					$value++;
					}
				$row[$j]=implode(".",$field);
				unset($field);
				}
				if($value==sizeof($row)-1){
					$sql="INSERT INTO deposit (`TRANS_ID`, `CUST_ID`, `EMP_ID`, `DATE`, `AMOUNT`, `VERIFY`, `STATUS`) VALUES";
					$row = explode("|",$data);
					for($j=0;$j<sizeof($row)-1;$j++){
						$field= explode(".",$row[$j]);
						$sql.="(NULL, '{$field[0]}', '{$_SESSION['emp']}', NOW(), '{$field[1]}', '0', '0')";
						if($j<sizeof($row)-2){$sql.=",";}
						else{$sql.=";";}
						unset($field);
					}
					$deposit=$test->query($sql);
					if($deposit==1){
						unset($_POST);
						$template->assign_vars(array(
							'HIDDEN' => "",));
						$complete=true;					
						include('withdraw_day.php');
						$loop=true;
					}
				}
			}
			else{
			$template->assign_block_vars('switch_no_data', array());
			}
		}
	}
	if(!$loop){
		$template->pparse('body');
	}
?>