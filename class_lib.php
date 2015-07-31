<?php
/**************************************************************************\
*						Epagne Planifier 						*
*					     Class Library of Doom					*
*						      ver 1.0        						*
*						 01/29/2008      						*
\**************************************************************************/
/**************************************************************************\
*					*/class index{/*						*
*														*
*														*
\**************************************************************************/
//=============
	function directoryList($dir,$ext){
		if (is_dir($dir)) /*test directory*/ {
			if ($dh = opendir($dir)) /*assign open directory*/{
				unset($this->ListN);
				$this->ListN = 0;
				while (($file = readdir($dh)) !== false){
					$testext=stristr($file,".");
					if($testext==$ext){
						$this->List[$this->ListN]= $file;
						$this->ListN = $this->ListN +1;
					}
				}
				closedir($dh);
			}
		}
	}
//=============
	function fileName(){
		$pos=strpos($this->List[$this->ListN],'.');
		$this->fileName=substr($this->List[$this->ListN],0,$pos);
		return $this->fileName;
	}
//=============
	function timestamp(){
	for($J=1;$J<=sizeof($this->tabledata[TIMESTAMP]);$J++){
					$h=substr($this->tabledata[TIMESTAMP][$J-1],11,2);
					$i=substr($this->tabledata[TIMESTAMP][$J-1],14,2);
					$s=substr($this->tabledata[TIMESTAMP][$J-1],17,2);
					$m=substr($this->tabledata[TIMESTAMP][$J-1],5,2);
					$d=substr($this->tabledata[TIMESTAMP][$J-1],8,2);
					$y=substr($this->tabledata[TIMESTAMP][$J-1],0,4);
					$this->tabledata[TIMESTAMP][$J-1] = mktime($h,$i,$s,$m,$d,$y);
				}
	}
//=============	
	function parse($code){
		$this->code=$code;
		$varrefs = array();
		preg_match_all("/({)(.*)(})/", $code, $varrefs);
			/*echo"<pre>";
			Print_r($varrefs);
			echo"</pre>";*/
		$varcount = sizeof($varrefs[1]);
		for ($i = 0; $i < $varcount; $i++)
		{
			$namespace = $varrefs[2][$i];
			if($this->display[$namespace]){
				$new = $this->display[$namespace];
				$this->code = str_replace($varrefs[0][$i], $new, $this->code);
			}
		}
		$this->final_code=$this->code;
	}
	function clean($input){
		$last_name = trim($_POST['last_name']);
		if ( !ereg("^[0-9]{5}(\.[0-9]{4})?",$last_name)){
		return false;
		}
	
	}
}
/***********************************************************************/
/**************************************************************************\
*				*/class sql extends index{/*				*
*														*
*														*
\**************************************************************************/
//===================
	private $hostname = "localhost";
	private $username = "epargne";
	private $password = "SBrxM8pAcxNqYpd4";
	private $dbName = "epargne";
	/*private $dbName = "new";*/
	/*private $dbName = "boukombe_08_07_2008";*/
	/*private $dbName = "natitingou_09_29_2009";*/
	/*private $dbName = "guinman_02_02_2009";*/
	/*private $dbName = "ep";*/
	private $conn="";
	var $result;
	var $resultie= array();
	var $exported;
	var $tables;
	var $fields;

//=============Opens the connection to the data base
function sql(){
		$this->conn = mysql_connect($this->hostname, $this->username , $this->password);
		mysql_select_db($this->dbName, $this->conn);
}
//=============Sumbits a query formats the result if there is one
	function query($quer){
		unset($this->result);
		unset($this->resultie);
		
		$this->result = mysql_query($quer, $this->conn);
		if(mysql_error()){
			return false;
		}
		if($this->result){
			return true;
		}
	}
//=============Debug Array
function debug($pref = null,$var){
	if ($pref != null){
		echo "<pre>";
		print_r($this->$var);
		echo "</pre>";
	}
}

//=============
	function dumpTable(){
		while($this->resultie = mysql_fetch_array($this->result)){
			if (mysql_errno ( )){die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				
				foreach($this->resultie as $category=>$value){
					$this->display[$category]=$value;
				};
		}
	}
//=============
	function dumpBlockTable(){
		$VAL=0;
		while($this->resultie = mysql_fetch_array($this->result)){
			if (mysql_errno ( )){die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				
				foreach($this->resultie as $category=>$value){
					$this->display[$category][$VAL]=$value;
				};
		}
	}
//=============
function generateSortBy($table,$caller_file) {
	$this->query("DESCRIBE $table");
	while($this->resultie = mysql_fetch_array($this->result)){
		$this->resultie[Field];
	}
}
//=============
function export()
{
	$this->query("SHOW TABLES;");
	while($this->resultie = mysql_fetch_array($this->result))
	{
	$this->tables[] = $this->resultie[0];
	}
	$this->exported .= "-- SQLSaver DB Dump\n-- version 1.0\n--\n-- Host: {$this->server}\n-- Generation Time: ".date("M d, Y \a\\t g:i a")."\n-- Server version: 5.0.67\n-- PHP Version: 5.2.6\n\nSET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n\n--\n-- Database: `{$this->db}`\n--\n\n";
	foreach($this->tables as $t)
	{
		$this->table = $t;
		$header = $this->create_header();
		$data = $this->get_data();
		$this->exported .= "-- --------------------------------------------------------\n\n--\n-- Table structure for table $t\n--\n\n$header--\n-- Dumping data for table $t\n--\n\n" . $data . "\n";
	}
	
	return($this->exported);
}
//=============
	function create_header()
	{
		$h = "CREATE TABLE IF NOT EXISTS `" . $this->table . "` (\n";
		$this->query("DESCRIBE {$this->table}");
		$z=0;
		unset($this->fields);
		while($this->resultie = mysql_fetch_array($this->result))
		{
		if($z!=0){$h .= ",\n";}
		unset($Field,$Type,$Null,$Default,$Extra);
		$Field=$this->resultie['Field'];
		$this->fields[]=$Field;
		$Type=$this->resultie['Type'];
		if($this->resultie['Null']=="NO"){$Null = "NOT NULL";}else $Null = "NULL";
		if($this->resultie['Key']=="PRI"){$Pri = $this->resultie['Field'];}
		if($this->resultie['Default']){$Default="default ".$this->resultie['Default']."";}else $Default="";
		$Extra=$this->resultie['Extra'];		
		$h .= "\t`$Field` $Type $Null $Default $Extra";
		$z++;
		}
		if(!empty($Pri)){
			$pkey = ", \n\t PRIMARY KEY (`$Pri`)";
		}
		$h .= "$pkey\n) ENGINE=MyISAM DEFAULT CHARSET=latin1;\n\n";
		return($h);
	}
//=============
	function get_data()
	{
		$d = null;
		$d .= "INSERT INTO `" . $this->table ."` (";
		for($j=0; $j<sizeof($this->fields); $j++)
			{
				$d .="`{$this->fields[$j]}`";
				if($j<sizeof($this->fields)-1){
				$d .=",";
				}
			}
		
		$this->query("SELECT * FROM `{$this->table}`;");
		$run=0;
		while($this->resultie = mysql_fetch_array($this->result))
		{
			if($run==0){	
				$d .= ")\nVALUES \n";
				$run++;
			}			
			$d .="\t(";
			for($i=0; $i<=sizeof($this->resultie)/2-1; $i++)
			{
				if($this->resultie[$i] == '') {
					$d .= 'NULL,';
				} else {
					$d .= "'{$this->resultie[$i]}',";
				}
			}
			$d = substr($d, 0, strlen($d) - 1);
			$d .= "),\n";
		}
		if($run == 0){return ("-- Empty data set");}
		$d = substr($d, 0,- 2);
		$d .= ";\n";
		return($d);
	}
//=============
}
/***********************************************************************/
/**************************************************************************\
*				*/class session_info{/*					*
*														*
*														*
\**************************************************************************/
	var $islogin;
	var $access;
	var $EMP;
	var $zone;
//=============
	function __construct(){
		$this->islogin=$_SESSION['islogin'];
		$this->access=$_SESSION['access'];
		$this->EMP=$_SESSION['emp'];
		$this->zone=$_SESSION['zone'];
		include "classes/page.php";
}
//=============
	function __destruct(){
	
	}
//=============
}
/***********************************************************************/
?>
