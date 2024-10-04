<? 
/**
 * Remove users in KB that no longer exist in AD
 */

/**
 * Detect and allow running from the cmd line, so this can be scheduled
 * as php admin_ad_user_sync.php
 */

$Sync = GetVar('Sync');
$msg = GetVar('msg');

if (!$_SERVER['REQUEST_URI']) {
	$noauth = true;
	include("config.php"); 
	echo "Starting AD User Sync";
	$Stat = AD_User_Sync($msg);
	echo $msg;
	exit ($Stat);
}


include("config.php"); 
  
   RequirePriv(PRIV_ADMIN);

if ($Sync) {
	AD_User_Sync($msg);
	$msg = nl2br($msg);	
}
   
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Users - AD Sync</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<script LANGUAGE="JavaScript" SRC="lib/AnchorPosition.js"></script>
<script LANGUAGE="JavaScript" SRC="lib/PopupWindow.js"></script>
<script LANGUAGE="JavaScript" SRC="lib/CalendarPopup.js"></script>
<script language="JavaScript">
function ParseForm(f)
{
	return true;
}
</script>
<? include("header.php"); ?>
<form onSubmit="ParseForm(this)" name=form action="<? echo $_SERVER['PHP_SELF'] ?>" method="get">
<table width="100%" border=0 cellspacing=0 cellpadding=0><tr>
<td style="vertical-align:top" width="25%" class="subhdr">
<img src="images/users_sync.jpg" width="56" height="59"></td>
<td align="left" width="75%">&nbsp;<? ShowMsgBox($msg); ?></td>
</tr></table>

<? 
	if ($Sync) {
?>

<div align="center">
       
	    <div class="shadowboxfloat">
          <div class="shadowcontent">

<table width="480" cellspacing="8" cellpadding="0">
  <tr>
    <td width="100%"><table width="100%" <? echo $FORM_STYLE ?> >
        <tr>
          <td height="22" class="form-hdr" style="text-align:left">
		  Press Sync below to scan Active Directory and remove any User accounts in KB
		  that no longer exist in AD.
		  </td>
        </tr>
        <tr>
          <td colspan="2" align="right" class="form-hdr">
		    <input type="submit" name="Sync" value="Sync"> 
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