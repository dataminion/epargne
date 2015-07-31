<?php
/**************************************************************************\
*		*/class deposit extends layout{/*		*
*												*
*												*
\**************************************************************************/
//=============
	function is_cust($ID, $zone = null){
	if($zone == null){
	$zone=$_SESSION['zone'];
	}
		$this->query("CREATE TEMPORARY TABLE recent_date_zone 
			SELECT MAX( DATE ) AS max_date, CUST_ID 
			FROM cust_location
			GROUP BY CUST_ID;");
			
		$this->query("CREATE TEMPORARY TABLE current_zone (
			id INT NOT NULL AUTO_INCREMENT,
			PRIMARY KEY (id))			
			SELECT cust_location.CUST_ID, cust_location.ZONE
			FROM recent_date_zone, cust_location
			WHERE cust_location.DATE = recent_date_zone.max_date
			AND cust_location.CUST_ID=recent_date_zone.CUST_ID
			AND ZONE = $zone
			ORDER BY CUST_ID asc;");
		$this->query("SELECT CUST_ID
			FROM current_zone
			WHERE id = '$ID';");
		if ($_SESSION['access']>1){
			$_SESSION['zone']=$zone;
		}
		while($resultie = mysql_fetch_array($this->result)){$CUST_ID=$resultie[CUST_ID];}
		$this->query("DROP TABLE recent_date_zone, current_zone ;");
		if(!$CUST_ID){return false;}
		else{return $CUST_ID;}
	
		/*$this->query("CREATE TEMPORARY TABLE recent_date_zone 
					SELECT MAX( DATE ) AS max_date, CUST_ID 
					FROM cust_location
					WHERE CUST_ID= '$ID'
					GROUP BY CUST_ID;");
		$this->query("SELECT cust_location.CUST_ID, cust_location.ZONE
					FROM recent_date_zone, cust_location
					WHERE cust_location.DATE = recent_date_zone.max_date
					AND cust_location.CUST_ID =$ID;");
		while($resultie = mysql_fetch_array($this->result)){$ZONE=$resultie[ZONE];}
		$this->query("DROP TABLE recent_date_zone;");
		if($ZONE == $_SESSION['zone']){return true;}
		else{return false;}*/
		
	}
//==============
	function is_goal_round($ID,$DEP){
		$this->query("CREATE TEMPORARY TABLE recent_date_goal
					SELECT MAX( cust_goal.DATE ) AS max_date, CUST_ID
					FROM cust_goal
					WHERE CUST_ID= '$ID'
					GROUP BY CUST_ID;");
		$this->query("SELECT cust_goal.CUST_ID, cust_goal.GOAL
					FROM cust_goal, recent_date_goal
					WHERE cust_goal.DATE=recent_date_goal.max_date
					AND cust_goal.CUST_ID=$ID;");
		while($resultie = mysql_fetch_array($this->result)){$GOAL=$resultie[GOAL];}
		$this->query("DROP TABLE recent_date_goal;");
		if($GOAL == 0){return false;}
		else if(is_int($DEP/$GOAL)){return true;}
		else{return false;}
	
	}
//==============
	function is_double($ID,$row){
		for($j=0;$j<sizeof($row)-1;$j++){
			$field= explode(".",$row[$j]);
			if($ID==$field[0]){
			$n++;
			}
			unset($field);
		}
		if($n==1){return true;}
		else{return false;}
	}
//==============
}
/***********************************************************************/
?>