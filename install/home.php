<?
if ($_POST) {
	if ($_SERVER["AUTH_TYPE"] && stristr("Negotiate|NTLM|Basic",$_SERVER["AUTH_TYPE"])) {
		// OK
	} else {
		header('Status: 401 Unauthorized');
		header('HTTP/1.1 401 Unauthorized');
		header('WWW-Authenticate: Negotiate',false);
		header('WWW-Authenticate: NTLM',false);
		exit;
	}
}


$SITE_URL = "http://" . $_SERVER['SERVER_NAME'] . "/" . $DBNAME . "/";
set_time_limit (600);
if ($Next) {
	header("location: " . $SITE_URL . "admin_settings.php");
	exit;
}
$_app_path = substr($_SERVER["SCRIPT_FILENAME"],0,strrpos($_SERVER["SCRIPT_FILENAME"],"\\")+1);
$_app_path = str_replace("\\\\","\\",$_app_path);
DEFINE("INSTALL_FOLDER","$_app_path");

function _scandir($dir = '.') 
{
   $dir_open = @ opendir($dir);
   
   if (! $dir_open)
       return false;
       
   while (($dir_content = readdir($dir_open)) !== false)
           $files[] = $dir_content;
   
   sort($files, SORT_STRING);
   
   return $files;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>KB - Installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="../styles.css"></link>
<style type="text/css">
<!--
.style1 {font-size: 10px}
.style2 {color: #CC0000}
-->
</style>
</head>
<body>
<p><br></p>
<p><br></p>
<script language="JavaScript" src="../lib/misc.js"></script>
<? /*
    * Ask for sa account and password
	* Run osql script to create database
	* Update config.php changing DEFINE("DBNAME","KB"); to actual db name
	* Then rename this file to .done
	*/
?>
<center>
<form action="<? echo $PHP_SELF ?>" method="post" name="form" id="form">
<? 
$Done = 0;


function CheckError() 
{
    if( ($errors = sqlsrv_errors(SQLSRV_ERR_ERRORS) ) != NULL) {
       	foreach( $errors as $error) {
		$ErrorMsg .=  "SQL State: ".$error[ 'SQLSTATE'].", ";
		$ErrorMsg .=  "Error: ".$error[ 'code']."<br>";
       		$ErrorMsg .=  "Message: ".$error[ 'message']."<br>";
	}
	if ($ErrorMsg) echo "<br><div style='margin-left:100px;text-align:left'><font color=red>$ErrorMsg</font></div></br></br>";
    }
    return $ErrorMsg;
}

function InstallScript($lnk)
{
	sqlsrv_configure("WarningsReturnAsErrors", 0);

	if ($_POST['DropDB']) {
		$rs = sqlsrv_query($lnk,"Drop Database " . $_POST['DBNAME']);
		CheckError();
	}

	$rs = sqlsrv_query($lnk,"Create Database " . $_POST['DBNAME']);
	CheckError();
	sqlsrv_query($lnk,"use " . $_POST['DBNAME']);
	CheckError();


	$script = file_get_contents("install.sql");
	$rs = sqlsrv_query($lnk,$script);
	$err = CheckError();

	if (!$err) {
		// Do all the updates
		$files = _scandir();
		foreach($files as $file) {
			
			if (substr($file,0,7) == "update_" && stristr($file,".sql")) {
				$script = file_get_contents($file);
				$rs = sqlsrv_query($lnk,$script);
				if (CheckError()) {
				     echo "While processing: $file<br>";
				}								
			}
		}
		return 1;
	}
}

if ($Submit) {
	echo "<input type=\"hidden\" name=\"DBNAME\" value=\"$DBNAME\">";
	chdir(INSTALL_FOLDER);
	//echo "Working...." . $_SERVER['LOGON_USER'] . "<br>";

	if ($_POST['uid']) {
	    $lnk = sqlsrv_connect($_POST['DBHOST'], array("UID" => $_POST['uid'],"PWD" => $_POST['sapassword']));
	}
	else {
	    $lnk = sqlsrv_connect($_POST['DBHOST']);
	}
	if (!$lnk) {
	     CheckError();	     
	}
	else $Done = InstallScript($lnk);
}
else {
	// Get default DBNAME from INI file
	$fp = fopen(INSTALL_FOLDER . "/Instance.ini","r");
	if ($fp) {
		$line = fgets($fp);
		$line = fgets($fp);
		// expect Instance= NAME
		list($garb,$param) = explode("=",$line);
		$param=trim($param); // DB@SERVER\INSTANCE
		list($DBNAME,$DBHOST) = explode('@',$param);
		if ($DBHOST == "") $DBHOST = "localhost";
		fclose($fp);
	} else $DBNAME = "KB";
} 
if ($Done) { ?>
<table width="400" border="0" cellpadding="4" cellspacing="0" style="border: 2px solid #000">
  <tr bgcolor="#CCCCCC">
    <th height="29" class="list-hdr" scope="row">Knowledge base - Database Initialization</th>
    </tr>
  <tr bgcolor="#CCCCCC">
    <th scope="row"><p><br><? echo $Msg ?><br>
      Operation Complete. Press Next to login and <br>
      configure the application settings.</p>
      <p>&nbsp;</p>
      <p>Important: To Login Use:<br>
        <br>
        <span class="style2">Account = Admin<br>
        Password= Admin</span><br>
        <br>
      (case sensistive) </p></th>
    </tr>
  <tr bgcolor="#CCCCCC">
    <th scope="row"><div align="right">
        <input name="Next" type="submit" id="Next" value="Next">
    </div></th>
    </tr>
</table>
<? } else {
$SITE_URL = "http://" . $_SERVER['SERVER_NAME'] . "/" . $DBNAME . "/";
?>
<form action="" method="post" name="form" id="form">
<table width="400" border="0" cellpadding="4" cellspacing="0" style="border: 2px solid #000">
  <tr bgcolor="#CCCCCC">
    <th height="29" colspan="2" class="list-hdr" scope="row">Knowledge base - Database Initialization (<? echo $SITE_URL ?>)</th>
    </tr>
  <tr bgcolor="#CCCCCC">
    <th colspan="2" scope="row">&nbsp;</th>
  </tr>
  <tr bgcolor="#CCCCCC">
    <th colspan="2" scope="row"><div align="left">
      <p>Provide an SQL account and password (sa account recommended) that has SQL sysadmin role. This will be used to create the required Database. This account is only used for this install process and is not required after installation.</p>
      <p>&nbsp;</p>
      <p>You may leave the SQL Account and password blank if your current windows account has SQL sysadmin permission.</p>
    </div></th>
    </tr>
  
  <tr bgcolor="#CCCCCC">
    <th scope="row"><div align="right">SQL Account</div></th>
    <td>
      <input name="uid" type="text" value="<? echo $uid ?>" size="20" maxlength="100"></td>
  </tr>
   <tr bgcolor="#CCCCCC">
    <th scope="row"><div align="right">SQL  password:</div></th>
    <td>
      <input name="sapassword" type="password" value="<? echo $sapassword ?>" size="20" maxlength="100"></td>
  </tr>
  <tr bgcolor="#CCCCCC">
    <th scope="row"><div align="right">Database Host (or Host\Instance):</div></th>
    <td><input name="DBHOST" type="text" value="<? echo $DBHOST ?>" size="20"></td>
  </tr>
  <tr bgcolor="#CCCCCC">
    <th scope="row"><div align="right">Database Name:</div></th>
    <td><input name="DBNAME" type="text" value="<? echo $DBNAME ?>" size="20"></td>
  </tr>
  <tr bgcolor="#CCCCCC">
    <th scope="row"><div align="right">Remove existing Database: </div></th>
    <td><input name="DropDB" type="checkbox" id="DropDB" value="1"></td>
  </tr>
  <tr bgcolor="#CCCCCC">
    <th scope="row"><span class="style1">(Note: When you press Submit you maybe asked to authenticate to your windows account.)</span></th>
    <td><div align="right">
      <input type="submit" name="Submit" value="Submit">
    </div></td>
  </tr>
</table><? } ?>
</form>
</center>  
</body>
</html>
