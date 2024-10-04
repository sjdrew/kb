<?
/**
 * Database shim Routines for MS Sql Server 2005 or better
 * 
 * php_sqlsrv_ts.dll from Microsoft downloads required
 *
 * File: subs_db.php
 * Version: 1.0
 *
 * Author: softperfection.com
 
 * 
 *
 */

$_tzoff = date("O");
$_tzfraction =  (((substr((string)$_tzoff,1,2) * 60)  +  substr((string)$_tzoff,4,2)) /1440);

DEFINE("SERVER_GMT_OFFSET",$_tzfraction);
DEFINE("DB_NOSTOP_ON_ERROR",8);
DEFINE("DB_NOAUDIT_UPDATE",32);

function trap_dberr()
{

}

class DB 
{
	var $lnk;
	var $QueryTime;
	var $sysDate = 'convert(datetime,convert(char,GetDate(),102),102)';
	var $sysTimeStamp = 'GetDate()';
	var $UseGMT = true;
	var $ErrorMsg;
	var $ErrorNo;
	var $databaseType = 'mssql';
    var $failed;
    var $Settings;
    var $databaseName;
    var $ErrMsg;
	
	function __construct($dbhost,$dbtype,$dbuser,$dbpass,$dbname,$abort=1)
	{
		if ($dbname == "") {
			echo "<br>Internal Error open called with no db name specified<br>";
			get_backtrace();
			exit;
		}	
		$this->UseGMT = 0;
					
		$this->lnk = sqlsrv_connect($dbhost, 
            array(
                "UID" => $dbuser,
                "PWD" => $dbpass,
                'Database' => $dbname,
                "Encrypt" => "no",
                "CharacterSet" => "UTF-8",
                "TrustServerCertificate" => "yes"
            ));
		if ($abort && !$this->lnk) {
			$this->GetLastError();
			global $db_err_routine;
			if ($db_err_routine) {
				return($db_err_routine('',$this->ErrorNo,$this->ErrorMsg,"Connection to Database $dbname @ $dbhost Failed"));
			}
	   		echo "Error connecting to database $dbname @ $dbhost<br>" . nl2br($this->ErrorMsg) . "<br>";
			return 0;
		}		
	 	sqlsrv_configure("WarningsReturnAsErrors", 0);
		$this->sql("SET ANSI_WARNINGS OFF");
   		return $this->lnk;
	}
	
	function GetLastError() 
	{
      	if( ($errors = sqlsrv_errors() ) != null) {
            $this->ErrMsg = '';
        	foreach( $errors as $error) {
				if ($this->ErrorNo == "") $this->ErrorNo = $error['code'];
				$this->ErrorMsg .=  "SQLSTATE: ".$error[ 'SQLSTATE']."\n";
				$this->ErrorMsg .=  "code: ".$error[ 'code']."\n";
           		$this->ErrorMsg .=  "message: ".$error[ 'message']."\n";
			}
			return true;
		}
		return false;
	}
	
	function Set_UseGMT($yes = 1)
	{
		$this->UseGMT = $yes;
	}

	function OffsetDate($dayFraction,$date=false)
	{		
		if (!$date) $date = $this->sysDate;
		return  '('.$date.'+'.$dayFraction.')';
	}
	
	function select_db($dbname)
	{
		if ($dbname == "") { return; }
		$this->databaseName = $dbname;
		return($this->sql("use $dbname"));	
	}

	function qstr($s)
	{
		//$s = str_replace('\"','"',$s);
		//$s = str_replace("\\'","'",$s);
		$s = str_replace("'","''",$s);
		return "'$s'";
	}

	function MetaIndexes($table)
	{
		$sql = "SELECT i.name AS ind_name, C.name AS col_name, USER_NAME(O.uid) AS Owner, c.colid, k.Keyno, 
			CASE WHEN I.indid BETWEEN 1 AND 254 AND (I.status & 2048 = 2048 OR I.Status = 16402 AND O.XType = 'V') THEN 1 ELSE 0 END AS IsPK,
			CASE WHEN I.status & 2 = 2 THEN 1 ELSE 0 END AS IsUnique
			FROM dbo.sysobjects o INNER JOIN dbo.sysindexes I ON o.id = i.id 
			INNER JOIN dbo.sysindexkeys K ON I.id = K.id AND I.Indid = K.Indid 
			INNER JOIN dbo.syscolumns c ON K.id = C.id AND K.colid = C.Colid
			WHERE LEFT(i.name, 8) <> '_WA_Sys_' AND o.status >= 0 AND O.Name LIKE '$table'
			ORDER BY O.name, I.Name, K.keyno";

        $rs = $this->sql($sql);
		if (!$rs) {
        	return FALSE;
        }
		$indexes = array();
		while ($row = $this->sql_fetch_array($rs)) {
            $indexes[$row['ind_name']]['unique'] = $row['IsUnique'];
            $indexes[$row['ind_name']]['columns'][] = $row['col_name'];
    	}
        return $indexes;
	}

	function sqlerr($sql="",$info="")
	{
		global $db_err_routine;
	
		if($this->GetLastError() && $this->ErrorNo != 7657 && $this->ErrorNo != 8152 && $this->ErrorNo != 9927) { 
			// ignore full text warning errors, and truncation errors
			Logger("SQL Error: " . $this->ErrorMsg . ", Query: $sql",'ERROR');
			if ($db_err_routine) {
				return($db_err_routine($sql,$this->ErrorNo,$this->ErrorMsg,$info));
			}
			if ($sql != "") {
				echo "<br><br>An Error occurred with the following SQL statement:<br><b>$sql</b><br><br>";
			}
   			echo $this->ErrorMsg."<br>";
   		}
		else if ($this->ErrorNo == 8152) {
			error_log("SQL Truncation Error: " . $this->ErrMsg . ", Query: $sql");
		}
	}


	//
	// Generic Save to DB from a Form
	// 
	// Returns new ID created
	//
	function save_form($table)
	{ 	
		$Fields = $this->db_field_types($table);	
	
		if( $_POST ) { 
            foreach($_POST as $key => $value) {
                if (array_key_exists($key,$Fields)) {
                    $SETS[$key] = trim((string)$value);
                    if ($SETS[$key] === "") {
                        $SETS[$key] = null;
                    }
                }
            }
    		return($this->insert_record($table,$SETS));
	 	}
 		return 0;
	}

	//
	// Returns list of Fields that were modified with the contents of the old value returned
	//
	function modify_form($id,$table,$force=0,$ModifyAllFlag=0)
	{
		$ModifiedFields = array();
		$SETS = array(); // Initialize an array to hold the record data to update
	
		if ($id <= 0) {
			die("Internal Error: modify form called with no ID specified.");
		}
	  
	  	$RS = $this->sql("select * from $table where ID=$id");	
	  	$R = $this->sql_fetch_array($RS);		
		$Fields = $this->db_field_types($table);	
		reset($_POST);
	
		if( $_POST ) { 
   
            foreach($_POST as $key => $val) {
				//
				// Skip posted fields that do not exist in DB Table, or have not changed.
				//
				//echo("<br>checking field $key $Fields[$key] <br>");
				//
				// Todo if we are saving a datetime field, we could detect this and convert
				// to GMT before saving (and before comparing)
				//
				$val = trim((string)$val);
				
				if ($ModifyAllFlag && $val == "") continue;
			
				if (array_key_exists($key,$Fields) && $val != $R[$key]) {
					//
					// If datetime field and new value does not include time, then just compare date part
					//
					if ($Fields[$key] == "datetime" && strlen((string)$val) < 12) {
						$R[$key] = substr((string)$R[$key],0,10);
						if ($val == $R[$key]) continue;
					}
					$NewValue = $val; 				
					$SETS[$key] = $NewValue;
					if ($SETS[$key] == "") $SETS[$key] = NULL;
        			$ModifiedFields[$key] = $R[$key];
	      		}
   			}
			if (count($SETS) > 0 || $force) {
				$flag =  ($ModifyAllFlag) ? DB_NOAUDIT_UPDATE : 0;
		    	if ($this->update_record($id,$table,$SETS,$flag)) 
    				return $ModifiedFields;
			}
	 	}
 		return 0;
	}

	function db_field_types($table)
	{
		$Fields = array();
        $olddb = null;
		@list($dbname,$dbo,$acttable) = explode('.',$table); 
		if ($acttable) {
			$olddb = $this->databaseName;
			$this->select_db($dbname);
			$table = $acttable;
		}
		$MetaCols = $this->MetaColumns($table);
		foreach($MetaCols as $F) {
			$Fields[$F->name] = $F->type;
		}
		if ($olddb) $this->select_db($olddb);
  		return $Fields;
	}

	function MetaColumns($Table)
	{
		// prec = precision, scale = number of decimal digits
		$res = $this->sql("select c.name as col,t.name,c.length as max_length,
				(case when c.xusertype=61 then 0 else c.xprec end) as prec,
				(case when c.xusertype=61 then 0 else c.xscale end) as scale 
				from syscolumns c join systypes t on t.xusertype=c.xusertype 
				join sysobjects o on o.id=c.id where o.name='$Table'");
		$Cols = array();
		if ($res) {
			while($Rec = $this->sql_fetch_array($res)) {
                $Cols[$Rec['col']] = (object)[];
				$Cols[$Rec['col']]->name = $Rec['col'];
				$Cols[$Rec['col']]->max_length = $Rec['max_length'];
				$Cols[$Rec['col']]->type = $Rec['name'];
				$Cols[$Rec['col']]->scale = $Rec['scale'];
				$Cols[$Rec['col']]->prec = $Rec['prec'];
			}
		}
		return $Cols;
	}
	
	function MetaTables() 
	{
		$Tables = array();
		$result = $this->sql("select name from sysobjects where xtype = 'U'"); 
		while($R = $this->sql_fetch_array($result)) {
			$Tables[] = $R['name'];
		}
		return $Tables;
	}

	///////////////////////////////////////////////////////////
	// Generic routine to retrieve a Record knowing the ID
	// returns associative array
	//
	function get_record_assoc($id,$table,$and="")
	{
		if (!$id) {
			ShowErrorLine("Internal Error: Tried to get_record_assoc with null ID: $table,$and");
			exit;
		}
		$q = "select * from $table where ID=$id $and";
		$result = $this->sql($q);
		if (!$result) {
			return 0;
		}
		return($this->sql_fetch_array($result));	
	}


	//
	// Expects ID and ITEM columns to be selected
	// Returns Array[ID] = ITEM
	// ie select ID,mycolumn as ITEM from ...
	//
	function MakeArrayFromQuery($q)
	{
		$a = array();
		$result = $this->sql($q);
		if ($result) {
			while($R = $this->sql_fetch_obj($result)) {
				$a[$R->ID] = $R->ITEM;
			}
		}
		return $a;
	}

	function GetRecordFromQuery($q,$params=[])
	{
		$result = $this->sql($q,$params);
		if (!$result) {
			return 0;
		}
		return($this->sql_fetch_obj($result));
	}


	function getmicrotime()
	{ 
    	list($usec, $sec) = explode(" ",microtime()); 
	    return ((float)$usec + (float)$sec); 
	} 


	function sql($q,$params=[],$stoponerr=1)
	{
        if (!$params) $params = [];
		$start = $this->getmicrotime();
		if ($this->lnk == "") {
			echo "sql function called with no Database connection. <br>";
			get_backtrace();
			exit;
		}
		$rs = sqlsrv_query($this->lnk,$q,$params);
		if ($stoponerr)
			$this->sqlerr($q,print_r($params,1));
					
		$end = $this->getmicrotime();
		$this->QueryTime = sprintf("%.3f",$end - $start);
	
		return $rs;
	}


	/**
	 * The Microsoft php_sqlsrv dll returns date fields as datetime objects
	 * Rather than convert lots of code to use the objects vs strings, we convert any datetime
	 * fields here when fetching the record
	 */
	function sql_fetch_obj(&$rs)
	{
		if (!$rs) return;
		$rec = sqlsrv_fetch_object($rs);
        $newrec = null;
        $i = 0;
		if ($rec) {
            $newrec = new stdClass();
			foreach($rec as $k => $v) {
				++$i;
				if (!$k) $k = $i;
				if (gettype($v) == 'object') {
					$newrec->$k = $v->format('Y-m-d H:i:s');
				}
				else $newrec->$k = $v;
			}
		}
		return $newrec;
	}
	
	function _RecordCount()
	{
		$r = $this->sql("select @@rowcount as Num");
		if ($r) {
			$a = $this->sql_fetch_array($r);
			return $a['Num'];
		}
	}

	/**
	 * Redo select query using count(*) to get row count
	 * If contains union, count records instead
	 * TODO: could try two queries and then move to next result to get @@rowcount
	 */
	function count_of($q)
	{
		$count = 0;
		if (stristr($q, "union all")) {
			$res = $this->sql($q,'',0);
			while($res && sqlsrv_fetch($res)) {
				++$count;
			}
			return $count;
		}
		if (stristr($q,"select ")) {
			$From = strripos($q," from ");
			$q = "select count(*) as Num " . substr((string)$q,$From);
			if ($p = strripos($q,'order by')) {
				$q = substr((string)$q,0,$p);
			}
		}
		$res = $this->sql($q,'',0); // ignore errors
		if ($res) {
			$s = $this->sql_fetch_array($res);
			return($s['Num']);
		}
		return;
	}

	function RecordCount($q)
	{
		return $this->count_of($q);
	}

    /**
     * 
     * @return array|null
     */
	function sql_fetch_array(&$rs)
	{
		if (!$rs) return;
        $i = 0;
        $newrec = null;
		$rec = sqlsrv_fetch_array($rs,SQLSRV_FETCH_ASSOC);
		if ($rec) {
			foreach($rec as $k => $v) {
				++$i;
				if (!$k) $k = $i;
				if (gettype($v) == 'object') {
					$newrec[$k] = $v->format('Y-m-d H:i:s');
				}
				else $newrec[$k] = $v;
			}
		}
		return $newrec;
	}
	
	function Move(&$rs,$n)
	{
		while($n > 0) {
			--$n;
			sqlsrv_fetch($rs);
		}
	}

	function AffectedRows(&$rs)
	{
		return sqlsrv_rows_affected($rs);
	}
	
	function sql_execute($filename,$ignore_dups=0,$verbose=0,$continue_on_error=0)
	{
		$ln = 0;
		$fp = @fopen($filename,"r");
		if (!$fp) {
			return("Could not execute $filename.");
		}
		else {
			$q = "";
			while(!feof($fp)) {
				$buffer = trim(fgets($fp));  // 4.3 rewmoved size, auto
				++$ln;
				if (substr((string)$buffer,0,1) == "#") {
					continue;
				}
				if ($buffer == "\n") 
					continue;

				$q = $q . $buffer;
					
				if (substr((string)$buffer,strlen((string)$buffer)-1,1) == ";") {
					$q = str_replace(";\n","",$q);
					$q = str_replace("\n","",$q);
					$result = $this->sql($q,"",0);
					if ($this->GetLastError()) {
						if ($ignore_dups == 0 || ($this->ErrorNo != 1060 && $this->ErrorNo != 1050) && $continue_on_error == 0 ) {
							$this->sqlerr($q,"Line: $ln SQL Execute Error");
						}
					   	if ($verbose) echo "<tr><td>Line: $ln </td><td>" . $this->ErrorNo .": ". $this->ErrorMsg. "</td></tr>";
					}
					else {
					   	if ($verbose) echo "<tr><td>Line: $ln </td><td>affected rows: " . $this->AffectedRows($result) . "</td></tr>";
					}
					
					$q = "";
				}
			}
			fclose($fp);
		}
		return "";
	}


	function update_record($ID,$table,$SETS,$flag=0)
	{
		global $CUser;

		if ($ID == "" || $table == "") {
			echo "Internal Error: update_record without required arguments";
			get_backtrace();
			exit;
		}
		
		if (!($flag & DB_NOAUDIT_UPDATE)) {

			if ($CUser->UserID == "") { 
				echo "Internal Error: No current user context during update";
				get_backtrace();
				exit;
			}

			$SETS["LASTMODIFIEDBY"] = $CUser->UserID;
			if ($this->UseGMT) {
				$SETS["LASTMODIFIED"] = $this->OffsetDate(SERVER_GMT_OFFSET,$this->sysTimeStamp);
			}
			else {
				$SETS["LASTMODIFIED"] = $this->sysTimeStamp;
			}
		}

		if (!is_array($SETS) || count($SETS) == 0) {
			return 0; // nothing done.
		}
		
		$q = "update $table set ";
        
        $comma = '';
        $params = [];
		foreach($SETS as $F => $V) {
			$q .= $comma . " $F = ";
			if (trim((string)$V) == "") {
				$q .= "NULL";
			} else if ($V == "GetDate()") {
				$q .= $V;
			} else {
                $q .= "?";
				$params[] = $V;
			}
			$comma = ',';
		}
        
		$q .= " where ID=$ID";
    	if (!$this->sql($q,$params,($flag & DB_NOSTOP_ON_ERROR))) {
    		return 0; // Caller to check error code if return rather than stop.
		}	
		return(1);   	
	} 
	   	
	function insert_record($table,$Fields,$StripID=1)
	{
		global $CUser;
		if (!is_array($Fields) || count($Fields) == 0) {
			return 0;
		}
		if ($CUser->UserID == "") { 
			global $db_err_routine;
			if ($db_err_routine) {
				$db_err_routine('','Internal Error: User not Authenticated','','');
			} else {
				echo "Internal Error: No current user context during insert";
				get_backtrace();			
			}
			exit;
		}
		
        $stamped = 0;
		foreach($Fields as $F => $V) {
			if ($StripID && $F == "ID") continue;
			if ($F == "CREATEDBY") $stamped = 1;
			$InsertFields[$F] = $V;
		}

		if (!$stamped) {
			$InsertFields["CREATEDBY"] = $CUser->UserID;		
			if ($this->UseGMT) {
				$InsertFields["CREATED"] = $this->OffsetDate(SERVER_GMT_OFFSET,$this->sysTimeStamp);
			}
			else {
				$InsertFields["CREATED"] = $this->sysTimeStamp;
			}
		}
		foreach($InsertFields as $Key => $Value) {
			$KF["[".$Key."]"]=$Value;
		}
		$q = "insert into $table (" . implode(",",array_keys($KF)) . ") ";
		$q .= "VALUES (";
        $comma = '';
        $params = [];
		foreach($InsertFields as $K => $V) {
			if (trim((string)$V) == "") {
				$q .= $comma . "NULL";
			} else if ($V == "GetDate()") {
				$q .= $comma . $V;
			} else {
				$q .= $comma . "?";
                $params[] = $V;
			}
			$comma = ',';
		}
		$q .= ") ";
		$q .= "; SELECT SCOPE_IDENTITY() AS ID";
		
		$res = $this->sql($q,$params);
    	if (!$res) {
	    	return 0;
		}
		return($this->LastID($res));   	
	}    	

	function LastID($res) 
	{
     	sqlsrv_next_result($res);
     	sqlsrv_fetch($res);
     	return sqlsrv_get_field($res, 0);
	}


	function UpdateBlob($table,$column,$val,$where)
	{
		$q = "UPDATE $table SET $column=0x".bin2hex($val)." WHERE $where";
		return $this->sql($q);
	}

	function delete_record($id,$table,$audit=0)
	{
 		$q = "DELETE from $table WHERE ID=$id";
		if ($audit) {
			//	echo("$sql<br>");
		}	
    	if (!$this->sql($q)) {
    		return 0;
		}
		return($id);   	
	}

	//ALTER TABLE doc_exy ALTER COLUMN column_a DECIMAL (5, 2) ;
	function AlterColumn($TableName,$ColumnName,$DataType,$L)
	{
		$type = $this->DataTypeFromMeta($DataType);
		
		$this->sql("ALTER table $TableName ALTER COLUMN $ColumnName $type$L NULL"); 	
	}

	function AddColumn($TableName,$ColumnName,$DataType,$L)
	{
		$type = $this->DataTypeFromMeta($DataType);
		$this->sql("ALTER table $TableName Add $ColumnName $type $L NULL"); 
	}
	
	function CreateIndex($TableName,$ColumnName,$IndexName) 
	{
		$this->sql("CREATE INDEX $IndexName on $TableName ($ColumnName)");
	}
	
	// EXEC sp_rename 'Sales.SalesTerritory.TerritoryID', 'TerrID', 'COLUMN';
	function RenameColumn($Table,$old,$new) {
		$this->sql("EXEC sp_rename '$Table.$old','$new', 'COLUMN'");
	}

	function DropIndex($TableName,$IdxName) {
		$this->sql("drop index $TableName.$IdxName");
	}

	function DropColumn($TableName,$ColName) {
		$this->sql("alter table $TableName drop column $ColName");
	}

	function DataTypeFromMeta($Meta) 
	{
		static $metaMap = array(
			'C' => 'varchar',
			'T' => 'datetime',
			'I'	=>	'int',
			'D' => 'decima',
			'T' => 'large text',
			'B' => 'large binary');
		$d = $metaMap[$Meta];
		if ($d == "") {
			$d = "varchar";
			error_log("Warning DateTypeFromMeta, unknown Meta Type $Meta");
		}
		return $d;
	}
	
	function MetaType($type)
	{
		static $typeMap = array(
			'varchar' 	=> 'C',
			'uniqueidentifier' => 'C',
			'varchar2' 	=> 'C',
			'char' 		=> 'C',
			'datetime'	=> 'T',
			'time'		=> 'T',
			'date'		=> 'T',
			'int'		=> 'I',
			'decimal'	=> 'N',
			'ntext'		=> 'X',
			'lvarchar'	=> 'X',
			'text'		=> 'X',
			'lonchar'	=> 'X',
			'large text' => 'X',
			'float'		=> 'F',
			'real'		=> 'F',
			'bit'		=> 'I1',
			'tinyint'	=> 'I2',
			'smallint'	=> 'I8',
			'bigint'	=> 'I8'
		);
		$t = $typeMap[$type];
		if ($t == "") {
			error_log("Warning MetaType unknown: $type");
			$t = "N";
		}
		return $t;
	}
}
