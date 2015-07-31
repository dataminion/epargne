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

//=============
function call_info($PID, $access){
		if(!$PID){$PID=1;}
		$this->access= $access;
		$this->query("SELECT CATEGORY, TITLE, PAGE_ID, LOCATION, CODE 
					FROM pages 
					WHERE ACCESS <= {$this->access};");
		unset($this->CATEGORY);
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
			$this->tabledata[$this->CATEGORY][sizeof($this->tabledata[$this->CATEGORY])-2][PID] = $this->resultie[PAGE_ID];
			if($this->resultie[PAGE_ID]== $PID){
				$this->display[code]=$this->resultie[CODE];
				$this->display[content]=$this->resultie[LOCATION];
				$this->display[TITLE]=$this->resultie[TITLE];
			}
		}
	}
//=============
}
/***********************************************************************/
?>