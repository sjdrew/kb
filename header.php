<?
if (!$nohdr) {
if (!isset($AppDB)) {
include("config.php");  
$hdr_as_page = 1;
echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
';
} 

/*
<!--[if lt IE 7]>
<script defer type="text/javascript" src="lib/pngfix.js"></script>
<![endif]-->
*/
?>
<style type="text/css">
<!--
.hdrtitle {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 18px;
	font-weight: normal;
	color: #EEEEEE;
	padding-left: 15px;
	/* letter-spacing: -.05em; */
}
-->
</style>
<? 
	$SC = $AppDB->GetRecordFromQuery("select Content from ContentSections where SectionName='Header'"); 
	if (strlen($SC->Content) <= 8) { 
		$SC->Content = '
<table cellspacing="0" cellpadding="0" width="100%" border="0">
    <tbody>
        <tr>
            <td width="100%">
            <table cellspacing="0" cellpadding="0" width="100%" border="0">
                <tbody>
                    <tr>
                        <td nowrap="nowrap" width="90%" background="/' . DBNAME . '/images/topbarbg.gif" height="36">
						<span class="hdrtitle"> {AppName}</span></td>
                        <td background="/' . DBNAME . '/images/topbarright.gif"><img height="2" alt="" src="spacer.gif" width="36" border="0" /></td>
                        <td valign="top" nowrap="nowrap" width="20%" background="/' . DBNAME . '/images/topbarright2.gif">
						<span style="FONT-SIZE: 7pt; COLOR: #dddddd">{Welcome}</span></td>
                    </tr>
                </tbody>
            </table>
            </td>
        </tr>
    </tbody>
</table>';
		SetContentSection("Header",$SC->Content);
    }
	if ($CUser->IsLoggedIn()) { 
		$welcome = "Welcome " . $CUser->FirstName();
	}
	DisplayContentSection("Header",array('{Welcome}' => $welcome,'{AppName}' => $AppDB->Settings->AppName));
?>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr>
        <td width="100%" background="images/searchbg.gif">
    <table border="0" cellpadding="4" cellspacing="0" width="100%" background="images/searchbg.gif">
      <tr>
        <td width="68%" nowrap class="small">
        <td width="32%" nowrap>
        <p class="smallbold" align="right">
		
		<font color="#FFFFFF">|</font>&nbsp;
		<a target="_top" class="barurl" href="home.php">Home</a>&nbsp; 

		<? if ($CUser->u->Priv >= PRIV_APPROVER) { ?>
		<font color="#FFFFFF">|</font>&nbsp;
		<a target="_top" class="barurl" href="admin.php">Admin</a>&nbsp; 
		<? } ?>
		
		<font color="#FFFFFF">|</font>&nbsp;
		<a target="_top" class="barurl" href="myprofile.php">My Profile</a>
		
		<? if ($CUser->IsLoggedIn()) {
			 if ($AppDB->Settings->AuthenticationMode != "NT") {
		 ?>		
			<font color="#FFFFFF">&nbsp;|</font>&nbsp; <a target="_top" class="barurl" href="logout.php">Logout</a>
		<? } } else { ?>
			<font color="#FFFFFF">&nbsp;|</font>&nbsp; <a target="_top" class="barurl" href="logon.php">Sign in</a>
		<? } ?>

		<font color="#FFFFFF">|</font>&nbsp;
		<a target="_top" class="barurl" href="javascript:showhelp('help/help-topics.html')">Help</a>&nbsp; 		
		<font color="#FFFFFF">|</font>&nbsp;
		<a target="_top" class="barurl" href="about.php">About</a>&nbsp; 		
		&nbsp;&nbsp;</p></td>
        </tr>
    </table>
</td>
</tr></table>
<? if ($hdr_as_page) {
echo '</body></html>' . "\n";
} else { ?>
<br>
<? } } ?>