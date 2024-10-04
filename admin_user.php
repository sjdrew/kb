<? include("config.php"); 
   nocache();
   RequirePriv(PRIV_ADMIN);
	$ID = GetVar("ID");
	$FirstName = GetVar("FirstName"); // advoid conflict with itshd cookies
	
	if ($ID && $_POST) {
		// checkboxes do not post if not set
		if ($_POST['BulletinEmail'] == "") $_POST['BulletinEmail'] = "";
		if ($_POST['NotifyNew'] == "") $_POST['NotifyNew'] = "";
		if ($_POST['NotifyUpdated'] == "") $_POST['NotifyUpdated'] = "";
		if ($_POST['NotifySubmitted'] == "") $_POST['NotifySubmitted'] = "";
		if ($_POST['NotifyTechnicalReview'] == "") $_POST['NotifyTechnicalReview'] = "";
		if ($_POST['NotifyContentReview'] == "") $_POST['NotifyContentReview'] = "";
	}

function ProcessSave($ID,$rdonly,&$msg,&$Err,$Multi=0)
{
	global $AppDB;
	$Table = "users";
	
	if (ParseFields($Table,$msg) != 0)
		return $ID;
		
	if ($ID) {
		$ModFields = $AppDB->modify_form($ID,$Table,0,$Multi);
		if (!$Multi) $msg = "Changes were saved.";
	}
	else {
		$CHK = $AppDB->GetRecordFromQuery("Select ID from $Table where Username = '". $_POST['Username']. "'");
		if ($CHK) { 
			$msg = "A User by that User ID already exists";
			return "";
		}
		$ID = $AppDB->save_form($Table);
		$msg = "New User record Created.";
	}
	return $ID;
}

function ProcessDelete($ID)
{
	global $AppDB;
	
	if ($_POST['Delete'] && $ID) {
		// Delete 
		$AppDB->sql("delete from users where ID = $ID");
		header("location:admin.php?msg=User record deleted.");
		exit;
	}
}

	$Table = "users";
	
	// Handle, Save, Delete, and Reposting

	if ($DelGroup) {
		$GroupList = GroupStrToArray($_POST['Groups'],1);
		$_POST['Groups'] = GroupArrayToStr($GroupList,$DelGroup,"",1);				
	}
	if ($AddGroup) {
		$GroupList = GroupStrToArray($_POST['Groups'],1);
		list($AddID,$Mode,$MustRead) = explode(":",$AddGroup);
		if ($AddID && $Mode) {
			if ($AddID == 1) $Mode = "A"; // administrator 
			if ($AddID == 0) $Mode = "R"; 
			$_POST['Groups'] = GroupArrayToStr($GroupList,$AddID,$Mode . ":$MustRead",0);
		}
	}
	if ($Save) {
		$ID = ProcessSave($ID,$rdonly,$msg,$Err);		
	}
		
	if ($Delete && $ID) {
		ProcessDelete($ID);
	}	
	
	if ($ID) {
		$F = $AppDB->get_record_assoc($ID,$Table);
		if (!$F) {
			header("location:admin.php?msg=User not found");
			exit;
		}
		RecordToGlobals($F);
	}
	
	if ($_POST) {
		// keep reposted values, but strip slashes
		repost_stripslashes();
		if ($CopyToNew) {
			$ID = $LASTMODIFIEDBY = $LASTMODIFIED = $CREATED = $CREATEDBY = "";
		}
	} else if (!$ID) {
		// defaults
	}
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - User Administration</title>
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
	if (!CheckEmail(f.Email)) return false;
	return true;
}
function RemoveGroup(id)
{
	if (confirm("Are you sure?")) {
		form.DelGroup.value=id;
		form.submit();
	}
}
function ModGroup(GroupID,Mode,MustRead)
{
	dialog_window('popup_group_add.php?GroupID=' + GroupID + "&Mode=" + Mode + "&MustRead=" + MustRead,560,130);
}

</script>
<? include("header.php"); ?>
<form onSubmit="return ParseForm(this);" name=form action="<? echo $_SERVER['PHP_SELF'] ?>" method="post">
<? hidden("ID",$ID); 
   hidden("DelGroup","");
   hidden("AddGroup","");
   hidden("Groups",$Groups);
?>
<table width="100%" border=0 cellspacing=0 cellpadding=0><tr>
<td width="25%" class="subhdr">
<img src="images/users.jpg" width="56" height="55"><span><? if (!$ID) echo "New "; ?>
User</span></td>
<td align="left" width="75%">&nbsp;<? ShowMsgBox($msg); ?></td>
</tr></table>
<div align="center">
       
	    <div class="shadowboxfloat">
          <div class="shadowcontent">

<table width="480" cellspacing="8" cellpadding="0">
  <tr>
    <td width="100%"><table width="100%" <? echo $FORM_STYLE ?> >
        <tr>
          <td width="20%" class="form-hdr">User ID</td>
          <td width="80%" class="form-data"><? DBField($Table,"Username",$Username); ?></td>
        </tr>
		<? if ($AppDB->Settings->AuthenticationMode != "NT") { ?>
        <tr>
          <td class="form-hdr">Password</td>
          <td class="form-data"><? DBField($Table,"Password",$Password); ?></td>
        </tr>
		<? } ?>
		<? if ($AppDB->Settings->PrivMode == "Simple") { ?>
        <tr>
          <td class="form-hdr">Priv</td>
          <td class="form-data"><? DBField($Table,"Priv",$Priv); ?></td>
        </tr>
		<? } else { ?>
		<tr>
		  <td valign="top" class="form-hdr">&nbsp;</td>
		  <td class="form-data">&nbsp;</td>
		  </tr>
		<tr>
		<td valign="top" class="form-hdr">Groups</td>
		<td colspan=2 class="form-data"><? 
			$GroupList = GroupStrToArray($Groups,1);
			// ie 23:R:Y,34:W:N,1:A
			echo "<table width=\"90%\" border=1 style=\"border-collapse: collapse\"><th>&nbsp</th><th align=\"left\">Group Name</th><th>Mode</th><th>Must Read</th></tr>\n";
			$count = 0;
			foreach($GroupList as $GID => $Mode) {
				$MustRead = "";
				list($Mode,$MustRead) = explode(":",$Mode);
				if ($MustRead == "") $MustRead = "N";
				$G = $AppDB->GetRecordFromQuery("select * from Groups where GroupID = $GID");
				if ($G) {
					echo "<tr><td><a href=\"javascript:RemoveGroup($GID);\"><img border=0 title=\"Remove Group\" src=\"images/delete.gif\"></a></td>\n" .
					     "<td><a title=\"Modify\" href=\"javascript:ModGroup('$GID','$Mode','$MustRead'); void(0);\">$G->Name</a></td><td align=\"center\">$Mode</td><td align=\"center\">$MustRead</td></tr>\n";
					++$count;
				}
			}
			if ($count == 0) {
				echo "<tr><td align=center colspan=4><i>(no groups assigned)</i></td></tr>\n";
			}
			echo "</table>\n";
			?>
			<input type=button name="AddGroupBut" value="Add" onClick="JavaScript:dialog_window('popup_group_add.php',560,130);" > 

			</td>
				</tr>
		<? } ?>
        <tr>
          <td colspan="2" class="form-hdr">&nbsp;</td>
          </tr>
        <tr>
          <td class="form-hdr">FirstName</td>
          <td class="form-data"><? DBField($Table,"FirstName",$FirstName); ?></td>
        </tr>
        <tr>
          <td height="22" class="form-hdr">Last Name</td>
          <td class="form-data"><? DBField($Table,"LastName",$LastName); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Phone</td>
          <td class="form-data"><? DBField($Table,"Phone",$Phone); ?></td>
          </tr>
        <tr>
          <td class="form-hdr">Email</td>
          <td class="form-data"><? DBField($Table,"Email",$Email); ?></td>
          </tr>
        <tr>
          <td class="form-hdr">Notifications:</td>
          <td class="form-data"><p class="medium"><? 
			DBField($Table,"BulletinEmail",$BulletinEmail); ?>
      User will receive Bulletin Email messages.<br><? 
			DBField($Table,"NotifyNew",$NotifyNew); ?>
      Notify user of any new articles for their group(s).<br>
      <? 
			DBField($Table,"NotifyUpdated",$NotifyUpdated); ?>
  Notify user of any  articles modified for their group(s).    
  <br>
  <? 
			DBField($Table,"NotifySubmitted",$NotifySubmitted); ?>
  Notify user when an article they had submitted or reviewed is updated.<br>
  			<? 	DBField($Table,"NotifyTechnicalReview",$NotifyTechnicalReview); ?>
				Notify user when an article requires a Technical Review.<br>
			<? 	DBField($Table,"NotifyContentReview",$NotifyContentReview); ?>
				Notify user when an article requires a Content Review.<br>
          </p>
            </td>
          </tr>
        <tr>
          <td class="form-hdr">&nbsp;</td>
          <td class="form-data">&nbsp;</td>
        </tr>
        <tr>
          <td class="form-hdr">Last Login</td>
          <td class="form-data"><? echo $LastLogin; ?> </td>
        </tr>
		<? if ($ID) { ?>
        <tr>
          <td class="form-hdr">&nbsp;</td>
          <td class="form-data">&nbsp;</td>
        </tr>
        <tr>
          <td valign="middle" class="form-hdr">Activity </td>
          <td class="form-data">
		    <ul>
		      <li><a href="report_searches.php?S=1&UserID=<? echo $ID ?>" title="View Recent searches">View Searches</a>&nbsp;</li>
		      <li>
		        <a href="report_active_articles.php?S=1&UserID=<? echo $ID ?>" title="View Recently viewed articles">View articles read</a>		        </li>
		      <li><a href="report_activity_log.php?CREATEDBY=<? echo $Username ?>&S=1">All Activity </a></li>
		    </ul>		    </td>
        </tr>
		<? } ?>
        <tr>
          <td colspan="2" align="right" class="form-hdr">
		    <input type="submit" name="Save" value="Save"> 
			<? if ($ID) { ?> 
		    <input type="submit" onClick="return confirm('Are you sure?')" name="Delete" value="Delete">  
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
</form>

</body>

</html>