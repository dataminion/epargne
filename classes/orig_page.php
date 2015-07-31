<?php
/**************************************************************************\
*			*/class layout extends sql{/*				*
*														*
*														*
\**************************************************************************/
	var $tablefield= array();
	var $tableNfield=array();
	var $tabledata= array();
	var $tableNdata=0;
	var $Nentries=0;
	var $CATEGORY="";
	var $switch=1;
	var $access="";
	var $content;
	var $title;
	var $display =array();
	var $code="";
	var $unformatted_code="";
	var $final_code="";
	var $newdate = array();
	var $worddate = array();
//=============
function call_info($PID, $access){
		if(!$PID){$PID=1;}
		$this->access= $access;
		$this->query("SELECT CATEGORY, TITLE, PAGE_ID 
					FROM pages 
					WHERE ACCESS <= {$this->access};");
		unset($this->CATEGORY);
		$J=0;
		while($this->resultie = mysql_fetch_array($this->result)){
		if (mysql_errno ( )){
				die (
					sprintf (
						"Cannot connect to server: %s (%d)\n",
						htmlspecialchars (mysql_error ( )),
						mysql_errno ( )
					)
				);
			}
		$this->debug("","resultie");
		$this->CATEGORY =$this->resultie[CATEGORY];
		$this->tabledata[$this->CATEGORY][sizeof($this->tabledata[$this->CATEGORY])][PAGE] = $this->resultie[PAGE_ID];
		$this->tabledata[$this->CATEGORY][sizeof($this->tabledata[$this->CATEGORY])-1][NAME] = $this->resultie[TITLE];
		$J++;
		}
		$this->query("SELECT TITLE, LOCATION, CODE 
					FROM pages 
					WHERE PAGE_ID = $PID
					AND ACCESS <={$this->access};");
		while($this->resultie = mysql_fetch_array($this->result)){
			$this->display[code]=$this->resultie[CODE];
			$this->display[content]=$this->resultie[LOCATION];
			$this->display[TITLE]=$this->resultie[TITLE];
		}
	}

//=============
	function date($cid,$caller_file,$postdate= null,$pref = null){
		$this->query("SELECT YEAR(DATE), MONTH(DATE) FROM deposit WHERE CUST_ID = $cid;");
		while($this->resultie = mysql_fetch_array($this->result)){
			if (mysql_errno ( )){
				die (
					sprintf (
						"Cannot connect to server: %s (%d)\n",
						htmlspecialchars (mysql_error ( )),
						mysql_errno ( )
					)
				);
			}
			$this->debug("","resultie");
			$this->y=$this->resultie[0];
			$this->tabledata[$this->y][$this->resultie[1]]= 1;
		}
		$J=0;
		foreach($this->tabledata as $yea=>$a){
			foreach($a as $mon=>$b){
				$this->newdate[$J]=mktime(0,0,0,$mon,1,$yea);
				$this->worddate[$J]=date("M-Y",$this->newdate[$J]);
				$J++;
			}
		}
	}
//====================
}
/***********************************************************************/
?>