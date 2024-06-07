<? 	
   	//----------------------------------------------------------------------------
	// SRC: admin_install_update.php, Version: 1.2.0 and higher
	//
   	// Actual Update script:
   	//
   	// Steps:
   	//
   	//	1. NTAuthenticate this page
   	//	2. mkdir of updates folder
   	//	3. again, check enough NTLM permissions of user by fopening about.php with append mode
   	//	4. Formulate kit location via current version
   	//	5. Fetch TXT file to check for availability and comments
   	//	6. Present results of TXT file to use
   	//	7. If Users Presses Update then Grab KIT file to local updates folder/version
	//	8. unzip folder to updates/version/kit
	//  9. check for updates/verison/kit/install/update_version.sql file
	// 10. move each file from updates/version/kit... to ./
	//
	include("config.php"); 
	require("lib/dUnZip2.inc.php");
   	RequirePriv(PRIV_ADMIN);
	$CUser->NTAuthenticate(); // only returns if authenticated.
	set_time_limit(600);

	if ($_GET['PreVer'] > 1.20) {
		$CurVer = $_GET['PreVer'];
		unset($_GET);
	} else {
		if ($_GET['CurVer']) $CurVer = $_GET['CurVer'];
		else $CurVer = $AppDB->Settings->AppVersion;
		$PreVer = sprintf("%.2f",$CurVer - .01);
	}

	if ($PreVer == "" && $CurVer == "") $CurVer = "1.10"; // was not there at/before this version
	
	$localpath = "updates/$CurVer";

	if (file_exists("update_kits/$CurVer/kbupdate.txt")) {
		define("KB_UPDATE_TXT", SITE_URL  . "update_kits/$CurVer/kbupdate.txt?A");
		define("KB_UPDATE_KIT", SITE_URL  . "update_kits/$CurVer/kbupdate.zip?B");
		$Local = 1;	
	}
	else {
		define("KB_UPDATE_TXT","http://" . KB_UPDATE_SERVER . "/" . KB_UPDATES_FOLDER . "/" . $CurVer . "/kbupdate.txt?A");
		define("KB_UPDATE_KIT","http://" . KB_UPDATE_SERVER . "/" . KB_UPDATES_FOLDER . "/" . $CurVer . "/kbupdate.zip?B");
	}
	
	if ($AppDB->Settings->ProxyHost == "") {
		if (defined("PROXY_HOST")) {
			$AppDB->Settings->ProxyHost = PROXY_HOST;
			$AppDB->Settings->ProxyPort = PROXY_PORT;		
		}
	}
	
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Update</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
<SCRIPT LANGUAGE="JavaScript" SRC="misc.js"></SCRIPT>
</head>
<body>
<? include("header.php"); ?>
<form name=form action="<? echo $PHP_SELF ?>" method="get">
<table width="100%" border=0 cellspacing=0 cellpadding=0><tr>
<td valign="top" abbr=""width="25%" class="subhdr">
<img src="images/updates.gif" width="50" height="50"><span>Update</span></td>
</tr></table>
<?

if (!$_GET) {
	//
	// Step 1:
	// Get and Display TXT file info
	//
	
	
   	$msg = "";
	if ($fp = @fopen("about.php","a")) {
		fclose($fp);
		@mkdir("updates");
		if (!file_exists("updates")) {
			$msg = "Unable to create subfolder called updates. Check file permissions.";
		}
		else {
			// OK
			// Figure out current version and look for update
			BusyImage(1,"Checking Update Server for any updates to your version, please wait...");
			@mkdir("$localpath");
			$str = "";
			if (!$Local) $str = get_url_contents(KB_UPDATE_TXT,$AppDB->Settings->ProxyHost,$AppDB->Settings->ProxyPort);
			if (!$str) {
				$str = get_url_contents(KB_UPDATE_TXT);
			}
			if ($str) {
				if ($fp = @fopen("$localpath/kbupdate.txt","w")) {
					fwrite($fp,$str);
					fclose($fp);
					$fp = fopen("$localpath/kbupdate.txt","r");
					while(!feof($fp)) {
						$line = fgets($fp);
						if (chop($line) == "") $cflag = 1;
						if (!$cflag) {
							list($key,$value) = explode("=",$line,2);
							$Info["$key"] = chop($value);
						} else {
							$Comments .= $line . "<br>";
						}
					}
					fclose($fp);
					$NewVersion = $Info["VERSION"];
					$Date = $Info["DATE"];
					$phase = 1;
				} else {
					$msg = "Unable to write to $localpath";
				}
			}
			else $msg = "Unable to obtain any information about upgrades to version $CurVer. Try again later.";
			BusyImage(0);
		}
   	}
	else {
	   $msg = "Unable to Start update process as your Windows account '($CUser->UserID)' does not have enough permissions on the server";
   	}
}

if ($_GET["Update"]) {

	BusyImage(1,"Downloading Upgrade KIT, please wait...");

	$str = "";
	if (!$Local) $str = get_url_contents(KB_UPDATE_KIT,$AppDB->Settings->ProxyHost,$AppDB->Settings->ProxyPort);
	if (!$str) {
		$str = get_url_contents(KB_UPDATE_KIT);
	}
	if ($str) {
		BusyImage(0);
		if ($fp = fopen("$localpath/kbupdate.zip","w")) {	
			fwrite($fp,$str);
			fclose($fp);
			
			ShowMsgBox("Kit downloaded and unpacked, Starting upgrade procedure from $CurVer to $NewVersion ...","center");
			flush();
			// PRE Specific version steps
			// For new version 1.21 and old version 1.20, replace editor folder
			//
			if ($CurVer == "1.20") {
				ShowMsgBox("Execute version specific changes.","center");
				rmdirr("lib/editor.old");
				rename("lib/editor","lib/editor.old");	
			}
			//
			// Step 9: Now execute any SQL scripts (if they fail dont go further).
			//
			$zip = new dUnzip2("$localpath/kbupdate.zip");
			$zip->getList();
			$sql_script = $zip->unzip("install/update_$CurVer.sql");
			if ($sql_script) {
				ShowMsgBox("Updating Database...","center");
				flush();
				$transactions = explode("GO\r\n",$sql_script);
				$SQLErrors = 0;
				foreach ($transactions as $t) {
					$AppDB->sql($t,"",0);
					if ($ErronNo = $AppDB->ErrorNo()) {
						ShowMsgBox("SQL Error: " . $ErrorNo . "  " . $AppDB->ErrorMsg(),"center"); 
						++$SQLErrors;
						if ($SQLErrors > 5) {
							$Err = 1;
							$zip->close();
							break;
						}
					}
				}
			}
			//
			// Step 10: unpack files to application folder
			//
			if (!$Err) {
				$zip->unzipAll(".");
				$zip->close();
				if ($zip->Warnings > 0) {						
					ShowMsgBox("The Update has completed, but with warnings.","center");
				} else {
					ShowMsgBox("The Update has completed successfully.","center");
				}
				//
				// Fix user accounts without MustRead flag specified. Set any missing to Y.
				//
				// not harmful to run twice.
				//
				$Res = $AppDB->sql("select * from " . USERS_TABLE);
				while($U = $AppDB->sql_fetch_obj($Res)) {
					if ($U->Groups) {
						$GroupList = GroupStrToArray($U->Groups,1);
						$comma = "";
						$NewGroupStr = "";
						foreach($GroupList as $GroupID => $Mode) {
							list($Mode,$MustRead) = explode(":",$Mode);
							if ($MustRead == "") $MustRead = "Y";
							$NewGroupStr  .= $comma . $GroupID . ":" . $Mode . ":$MustRead";
							$comma = ",";
						}
						if ($U->Groups != $NewGroupStr) {
							$AppDB->sql("update " . USERS_TABLE . " set Groups='$NewGroupStr' where ID=$U->ID");
						}
					}
				}
			}
			$done = 1;
		}
	} else {
		BusyImage(0);
		ShowMsgBox("Unable to download upgrade kit. Try again later.","center");
		$phase = 1;
	}
}

ShowMsgBox($msg,"center");
if ($done) exit;

?>
<? if ($phase == 1) { ?>
<div align="center">
	    <div class="shadowboxfloat">
          <div class="shadowcontent">
<table width="590" cellspacing="8" cellpadding="0">
  <tr>
    <td width="100%">
	   <table width="100%" <? echo $FORM_STYLE ?> >
        <tr>
          <td width="50%" class="form-hdr"><strong>Your Current Version</strong> : </td>
          <td width="50%" class="form-data"><strong><? echo $AppDB->Settings->AppVersion; ?></strong>&nbsp;</td>
        </tr>
        <tr>
          <td class="form-hdr"><strong>Version available</strong>: </td>
          <td nowrap class="form-data"><strong><? if ($NewVersion) echo $NewVersion; else echo "You are running the Latest Version." ?></strong></td>
        </tr>
		<tr>
		  <td colspan=2 align="center">&nbsp;</td>
		  </tr>
		<tr>
		  <td colspan=2 align="center"><strong><em>Update information:</em></strong></td>
		  </tr>
		<tr>
		  <td colspan=2 align="center"><div style="background:white; padding:10px; margin:10px; text-align:left; height:200px; overflow:auto; width:95%; border: solid black 1px;"><? echo $Comments ?></div></td>
		</tr>
		<tr>
          <td colspan=2 align="right" class="form-hdr">
		   <? hidden("NewVersion",$NewVersion);
		   	  hidden("CurVer",$CurVer); ?>
		    <input <? if ($NewVersion <= $CurVer) echo "disabled" ?> type="submit" name="Update" value="Update Now"> 
			<? if ($NewVersion == $CurVer || $NewVersion == "") { ?>
		    <input onClick="window.location='<? echo $PHP_SELF . "?PreVer=$PreVer" ?>'" type="button" name="LastUpdate" value="ReApply Last Update"> 
			<? } ?>
		    <input onClick="window.location='admin.php'" name="Back" type="button" id="Back" value="Back">
            </td>
          </tr>
      </table>	   
      </td>
  </tr>
</table>
</div></div>
</div>
<? } ?>
</form>
</body>
</html>
