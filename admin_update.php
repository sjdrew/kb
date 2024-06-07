<? 	include("config.php"); 
   	RequirePriv(PRIV_ADMIN);
	set_time_limit(180);

	if (!defined("KB_UPDATE_SCRIPT"))
		define("KB_UPDATE_SCRIPT","admin_install_update.php");

	// If we have a uppdate_kits folder use us as the update server
	define("LOCAL_UPDATE_SCRIPT","update_kits/kbupdate.dat");
	if (file_exists(LOCAL_UPDATE_SCRIPT)) {
		$Local = 1;
	}
	
	if ($AppDB->Settings->ProxyHost == "") {
		if (defined("PROXY_HOST")) {
			$AppDB->Settings->ProxyHost = PROXY_HOST;
			$AppDB->Settings->ProxyPort = PROXY_PORT;		
		}
	}
	  
   //
   // Update script that really fetches latest upgrade script from update server.
   //
   // Steps:
   //
   //	1. NTAuthenticate this page
   //	2. mkdir of updates folder
   //	3. check enough NTLM permissions of user by fopening about.php with append mode (as a test)
   //	4. fetch $update_url/kbupdate.dat as /admin_install_update.php
   //	5. Redirect to /admin_install_update.php and let it do the work.
   //
   // 
   //
   //  If http://localhost/kb/update_kits/kbupdate.php exist use local host
   
	$CUser->NTAuthenticate(); // only returns if authenticated.
	
	if ($AppDB->Settings->ProxyHost) {
		$proxy = $AppDB->Settings->ProxyHost;
		$proxy_port = $AppDB->Settings->ProxyPort;
	}
	
   	$msg = "";
	if ($fp = @fopen("about.php","a")) {
		fclose($fp);
		@mkdir("updates");
		if (!file_exists("updates")) {
			$msg = "Unable to create subfolder called updates. Check file permissions.";
		}
		else {
			@unlink(KB_UPDATE_SCRIPT);
			if (file_exists(KB_UPDATE_SCRIPT)) {
				$msg = "Unable to remove previous install script " . KB_UPDATE_SCRIPT . " Please remove this file manually.";		
			}
			else {
				// OK
				$str = '';
				if (!$Local) {
					if ($proxy)
						$str = get_url_contents(KB_UPDATE_URL,$proxy,$proxy_port);
					if (!$str) {
						$str = get_url_contents(KB_UPDATE_URL);
					}
					if (stristr($str,"<TITLE>404")) $str = "";
				}
				else {
					$str = @file_get_contents(LOCAL_UPDATE_SCRIPT);
				}
				if ($str) {
					if ($fp = fopen(KB_UPDATE_SCRIPT,"w")) {
						fwrite($fp,$str);
						fclose($fp);
						header("location: " . KB_UPDATE_SCRIPT);
						exit;
					} else {
						$msg = "Unable to create update script.";
					}
				}
				else $msg = "Unable to contact application update service.";
			}
   		}
	}
	else {
	   $msg = "Unable to Start update process as your Windows account '($CUser->UserID)' does not have enough permissions on the server";
   	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Update</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<? include("header.php"); ?>
<table width="100%" border=0 cellspacing=0 cellpadding=0><tr>
<td valign="top" width="25%" class="subhdr">
<img src="images/updates.gif" width="50" height="50">Update Software</td>
<td align="left" width="75%">&nbsp;<? ShowMsgBox($msg); ?></td>
</tr></table>
</body>
</html>