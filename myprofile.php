<? include("config.php"); 

	if ($Password) { header("location:password.php"); }
	$ID = GetVar("ID");
	$FirstName = GetVar("FirstName");
	$Table = "users";

	$U = $CUser->UserRecord();
	$ID = $U->ID;

	//TODO: Could have generic routing for posting that uses dbfields and sets vars for all checkboxes?
	if ($ID && $_POST) {
		// checkboxes do not post if not set
		if ($_POST[BulletinEmail] == "") $_POST[BulletinEmail] = "";
		if ($_POST[NotifyNew] == "") $_POST[NotifyNew] = "";
		if ($_POST[NotifyUpdated] == "") $_POST[NotifyUpdated] = "";
		if ($_POST[NotifySubmitted] == "") $_POST[NotifySubmitted] = "";
		if ($_POST[NotifyTechnicalReview] == "") $_POST[NotifyTechnicalReview] = "";
		if ($_POST[NotifyContentReview] == "") $_POST[NotifyContentReview] = "";
	}
	
	if ($Save) {
		$ModFields = $AppDB->modify_form($U->ID,"users");
	}
	
	if ($ID) {
		$R = $AppDB->get_record_assoc($ID,$Table);
		RecordToGlobals($R);
	}
	if (!$ID) {
		header("location:home.php?msg=User not found");
		exit;
	}
	if ($SearchMode == "") $SearchMode = $AppDB->Settings->DefaultSearchMode;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Profile</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<? include("header.php"); 
	
?>
<br>
<script language="javascript">
function ParseForm(f)
{
	if (!CheckEmail(f.Email)) return false;
	return true;
}
</script>
<form onSubmit="return ParseForm(this);" action="<? echo $PHP_SELF ?>" method="post" name="form">
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  	<tr><td height="14"> 	    
  	<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td width="22%"> 
      <td width="78%" colspan="2"> 
    <tr> 
      <td colspan="5"> </td>
    </tr>
    <tr>
      <td width="180" valign="top" align="left" background="images/vert_bar.gif">
	  <img src="images/spacer.gif" width="180" height="1" border=0>
	  <table width="87%" border="0" cellpadding="4" cellspacing="0">
          <tr><td ><img src="images/folder1.jpg" width="49" height="45" border="0"></td>
            <td valign="top" class="hdr1"><br>
              PROFILE<br>
			</td>
          </tr>
          <tr> 
            <td colspan="2" class="dots">.....................................</td>
          </tr>
          <tr> 
            <td colspan="2">Review your personal information.</td>
          </tr>
          <tr> 
            <td colspan="2" class="dots" >.....................................</td>
          </tr>
       </table>
	  </td>
      <td colspan="2" valign="top"> <table width="90%" border="0" align="center" cellpadding="4">
          <tr> 
            <td colspan="2"> 
          <tr> 
            <td height="27" colspan="2" valign="top" class="subhdr" >              Profile
              and Preferences</td>
          </tr>
          <tr>
            <td width="30%" class="form-hdr">User ID:</td>
            <td width="70%" class="form-data"><b><? echo $Username ?></b>            </td>
          </tr>
          <tr>
            <td class="form-hdr">Email:</td>
            <td class="form-data"><? DBField($Table,"Email",$Email); ?></td>
          </tr>
          <tr>
            <td width="30%" class="form-hdr">FirstName:</td>
            <td width="70%" class="form-data"><? DBField($Table,"FirstName",$FirstName); ?>            </td>
          </tr>
          <tr>
            <td height="22" class="form-hdr">Last Name:</td>
            <td class="form-data"><? DBField($Table,"LastName",$LastName); ?>            </td>
          </tr>
          <tr>
            <td class="form-hdr">Phone:</td>
            <td class="form-data"><? DBField($Table,"Phone",$Phone); ?>            </td>
          </tr>
		  <? if ($AppDB->Settings->PrivMode == "Simple") { ?>
          <tr>
            <td class="form-hdr">Support:</td>
            <td class="form-data"><? if ($CUser->u->Priv >= PRIV_SUPPORT) { echo "Yes"; } else { echo "No"; }  ?>            </td>
          </tr>
		<? } else { ?>
          <tr>
            <td valign = "top" class="form-hdr">Group Memberships:</td>
            <td style="padding-left:4px;" class="form-data"><?
				$Modes = array("W" => "(Write Access)", "R" => "(Read Access)", "A" => "(Approval Access)");
				foreach($CUser->u->GroupArray as $Grp => $Mode) {
					if ($Grp && $Mode) {
						$G = $AppDB->GetRecordFromQuery("select Name from Groups where GroupID = $Grp");
						echo "<b>$G->Name</b> : " . $Modes[$Mode] . "<br>";
					}
				}
			?></td>
          </tr>
		  <? } ?>
          <tr>
            <td class="form-hdr">Default Search Group: </td>
            <td class="form-data"><? 	
				GroupDropList($GroupID,1);
			?>
		    </td>
          </tr>
          <tr>
            <td class="form-hdr">Default Search Mode: </td>
            <td class="form-data"><? DBField($Table,"SearchMode",$SearchMode); ?></td>
          </tr>
          <tr>
            <td class="form-hdr">Items displayed per page: </td>
            <td class="form-data"><? 
			if ($Pagination == "") $Pagination = DEFAULT_ITEMS_PER_PAGE;
			DBField($Table,"Pagination",$Pagination); ?></td>
          </tr>
          <tr>
            <td class="form-hdr">Display Article Previews:</td>
            <td class="form-data"><? 
			DBField($Table,"Previews",$Previews); ?></td>
          </tr>
          <tr>
            <td class="form-hdr">Email Notifications:</td>
            <td nowrap class="form-data"><p class="medium"><? 
			DBField($Table,"BulletinEmail",$BulletinEmail); ?>
              Receive Bulletin Email notifications.<br><? 
			DBField($Table,"NotifyNew",$NotifyNew); ?>
              Notify me of any newly approved articles for my group(s).<br>
              <? 
			DBField($Table,"NotifyUpdated",$NotifyUpdated); ?>
Notify me when any articles for my groups are updated.              <br>
			<? 	DBField($Table,"NotifySubmitted",$NotifySubmitted); ?>
				Notify me when an article I had submitted am a contact for or reviewed is updated.<br>
			<? 	DBField($Table,"NotifyTechnicalReview",$NotifyTechnicalReview); ?>
				Notify me when an article requires a Technical Review.<br>
			<? 	DBField($Table,"NotifyContentReview",$NotifyContentReview); ?>
				Notify me when an article requires a Content Review.<br>
            </p>              </td>
          </tr>
          <tr valign="middle"> 
            <td colspan="2" > <div align="center"> 
                <input name="Save" type="submit" id="Save" value="Save">
				<? if ($AppDB->Settings->AuthenticationMode != "NT") { ?>				
                <input name="Password" type="submit" id="Password" value="Change Password">
				<? } ?>
		    	<input onClick="window.location='home.php'" name="Back" type="button" id="Back" value="Back">
              </div></td>
          </tr>
          <tr> 
            <td colspan="2">&nbsp;</td>
          </tr>
        </table></td>
    </tr>
  </table>
    </td>
  </tr>
</table>
</form>
</body>

</html>