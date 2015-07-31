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
	$List->query("CREATE TEMPORARY TABLE recent_date_zone 
				SELECT MAX( DATE ) AS max_date, CUST_ID 
				FROM cust_location
				GROUP BY CUST_ID;");
				
			$List->query("CREATE TEMPORARY TABLE current_zone 
				SELECT cust_location.CUST_ID, cust_location.ZONE
				FROM recent_date_zone, cust_location
				WHERE cust_location.DATE = recent_date_zone.max_date
				AND cust_location.CUST_ID=recent_date_zone.CUST_ID
				AND ZONE = {$_GET['zone']};");
				
			$List->query("CREATE TEMPORARY TABLE recent_date_goal
				SELECT MAX( cust_goal.DATE ) AS max_date, cust_goal.CUST_ID
				FROM cust_goal, current_zone
				WHERE cust_goal.CUST_ID=current_zone.CUST_ID
				GROUP BY CUST_ID;");
			
			$List->query("CREATE TEMPORARY TABLE current_goal
				SELECT cust_goal.CUST_ID, cust_goal.GOAL, account.DESCR
				FROM cust_goal, recent_date_goal, account
				WHERE cust_goal.DATE=recent_date_goal.max_date
				AND cust_goal.CUST_ID=recent_date_goal.CUST_ID
				AND account.CUST_ID=recent_date_goal.CUST_ID;");
			$List->query("SELECT CUST_ID
				FROM current_goal
				ORDER BY CUST_ID ASC;");
			while($resultie = mysql_fetch_array($List->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				$data[$resultie[CUST_ID]]=0;
			
			}	
		
			$List->query("SELECT CUST_ID, AMOUNT
				FROM withdraw
				WHERE DATE > CURDATE( );");
			while($resultie = mysql_fetch_array($List->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				$var = array_key_exists($resultie[CUST_ID], $data);
				if($var){
				$data[$resultie[CUST_ID]] += $resultie[AMOUNT];
				}
				else{continue;}
			}	
				
// Create an instance 
$xls =& new Spreadsheet_Excel_Writer(); 
$now=date("Y-m-d");
// Send HTTP headers to tell the browser what's coming 
$xls->send("Retrait Journal $now.xls"); 

// Add a worksheet to the file, returning an object to add data to 
$sheet =& $xls->addWorksheet("Zone {$_GET['zone']}"); 

// Write some numbers 
$i=0;
foreach ($data as $key=>$value) { 
 // Use PHP's decbin() function to convert integer to binary 
 $sheet->write($i,0,$key); 
 $sheet->write($i,1,$value);
 $i++;
} 
unset($i);
// Finish the spreadsheet, dumping it to the browser 
$xls->close(); 
?>
