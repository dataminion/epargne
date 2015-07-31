<?php 
// Include PEAR::Spreadsheet_Excel_Writer 
require_once "Spreadsheet/Excel/Writer.php"; 
	include "../class_lib.php";
	include "../classes/page.php";
	include'../classes/my_customers.php';
	function multiArraySearch($needle, $haystack){
	    $value = false;
	    $x = 0;
	    foreach($haystack as $temp){
		    $search = array_search($needle, $temp);
			if (strlen($search) > 0 && $search >= 0){
			    $value[0] = $x;
				$value[1] = $search;
		    }
		    $x++;
	    }
		return $value;
	 }
	$data=array();
	$List=new client();
			$List->query("CREATE TEMPORARY TABLE recent_date_zone SELECT MAX( DATE ) AS max_date, CUST_ID, ZONE
				FROM cust_location
				GROUP BY CUST_ID
				ORDER BY ZONE DESC;");
			$List->query("SET @rank =0,
				@prev_val = 1;");
			$List->query("
				SELECT @rank := IF( @prev_val = ZONE , @rank+1, 1 ) AS rank, @prev_val := ZONE AS ZONE, CUST_ID
				FROM recent_date_zone
				ORDER BY max_date, ZONE DESC;");
			while($resultie = mysql_fetch_array($List->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				$data[$resultie[ZONE]][$resultie[rank]]=0;
			}
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
				WHERE cust_goal.CUST_ID=deposit.CUST_ID
				AND cust_goal.CUST_ID=current_zone.CUST_ID
				GROUP BY CUST_ID;");		
			$List->query("SELECT SUM( deposit.AMOUNT ) AS TDEPOSIT, deposit.CUST_ID, goal_date.rank, goal_date.ZONE 
				FROM deposit, goal_date
				WHERE deposit.CUST_ID = goal_date.CUST_ID
				GROUP BY CUST_ID;");
			while($resultie = mysql_fetch_array($List->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				$var = array_key_exists($resultie[rank], $data[$resultie[ZONE]]);
				if($var){
				$data[$resultie[ZONE]][$resultie[rank]] += $resultie[TDEPOSIT];
				}
				else{continue;}
			}		
				
				
// Create an instance 
$xls =& new Spreadsheet_Excel_Writer(); 
$now=date("Y-m-d");
// Send HTTP headers to tell the browser what's coming 
$xls->send("Total du mois $now.xls");
// Add a worksheet to the file, returning an object to add data to 
$sheeta =& $xls->addWorksheet("Zone 1"); 
// Write some numbers 
$i=0;
foreach ($data[1] as $key=>$value) { 
 // Use PHP's decbin() function to convert integer to binary 
 $sheeta->write($i,0,$key); 
 $sheeta->write($i,1,$value);
 $i++;
} 
unset($i);
$sheetb =& $xls->addWorksheet("Zone 2"); 
// Write some numbers 
$i=0;
foreach ($data[2] as $key=>$value) { 
 // Use PHP's decbin() function to convert integer to binary 
 $sheetb->write($i,0,$key); 
 $sheetb->write($i,1,$value);
 $i++;
} 
unset($i);
$sheetc =& $xls->addWorksheet("Zone 3"); 
// Write some numbers 
$i=0;
foreach ($data[3] as $key=>$value) { 
 // Use PHP's decbin() function to convert integer to binary 
 $sheetc->write($i,0,$key); 
 $sheetc->write($i,1,$value);
 $i++;
} 
unset($i);
$sheetd =& $xls->addWorksheet("Zone 4"); 
// Write some numbers 
$i=0;
foreach ($data[4] as $key=>$value) { 
 // Use PHP's decbin() function to convert integer to binary 
 $sheetd->write($i,0,$key); 
 $sheetd->write($i,1,$value);
 $i++;
} 
unset($i);
$sheete =& $xls->addWorksheet("Zone 5"); 
// Write some numbers 
$i=0;
foreach ($data[5] as $key=>$value) { 
 // Use PHP's decbin() function to convert integer to binary 
 $sheete->write($i,0,$key); 
 $sheete->write($i,1,$value);
 $i++;
} 
unset($i);
$sheetf =& $xls->addWorksheet("Zone 6"); 
// Write some numbers 
$i=0;
foreach ($data[6] as $key=>$value) { 
 // Use PHP's decbin() function to convert integer to binary 
 $sheetf->write($i,0,$key); 
 $sheetf->write($i,1,$value);
 $i++;
} 
unset($i);

// Finish the spreadsheet, dumping it to the browser 
$xls->close(); 
?>
