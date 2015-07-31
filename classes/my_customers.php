<?php
/**************************************************************************\
*			*/class client extends layout{/*				*
*														*
*														*
\**************************************************************************/
//===== var for dump deposits
var $funds_unverified=0;
var $funds_withdrawn=0;
var $funds_available=0;
//===== var for dump Booklet
var $entries_unverified=0;
var $entries_withdrawn=0;
var $entries_available=0;
//===== var for date
var $newdate = array();
var $worddate = array();
//================
	function dumpDeposits(){
		while($this->resultie = mysql_fetch_array($this->result)){
			if (mysql_errno ( )){die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
			switch($this->resultie[VERIFY]){
				case 0:
					$this->funds_unverified+=$this->resultie[AMOUNT];
					break;
				case 1:
					switch($this->resultie[STATUS]){
						case 0:
							$this->funds_available+=$this->resultie[AMOUNT];
							break;
						case 1:
							$this->funds_withdrawn+=$this->resultie[AMOUNT];
							break;
						default:
							echo "This shouldn't be possible??!";
							break;
					}
					break;
				default:
					echo "This shouldn't be possible?!";
					break;
			}
		}

		$this->display[GROSS]=$this->funds_available;
		$this->display[NET]=$this->display[GROSS]-$this->display[GOAL];
	}
//================
	function dumpBooklet($MISE){
		if ((!$MISE)||($MISE<1)){
			$this->entries_unverified=0;
			$this->entries_withdrawn=0;
			$this->entries_available=0;
		}
		else{
			$this->entries_unverified=$this->funds_unverified/$MISE;
			$this->entries_withdrawn=$this->funds_withdrawn/$MISE;
			$this->entries_available=$this->funds_available/$MISE;
		}
	}
//==============
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
}
/***********************************************************************/
?>