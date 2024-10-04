<? include("config.php"); 
   RequirePriv(PRIV_ADMIN);

	$Table = "Groups";
	$ID = GetVar("ID");

function ProcessSave($ID,$rdonly,&$msg,&$Err,$Multi=0)
{
	global $AppDB;
	global $Table;
		
	if (ParseFields($Table,$msg) != 0)
		return $ID;
		
	if ($ID) {
		$ModFields = $AppDB->modify_form($ID,$Table,0,$Multi);
		if (!$Multi) $msg = "Changes were saved.";
	}
	else {
		$ID = $AppDB->save_form($Table);
		$msg = "New Group Created.";
	}
	return $ID;
}

function ProcessDelete($ID,&$msg)
{
	global $AppDB;
	global $Table;
	
	if ($_POST['Delete'] && $ID) {
		$Rec = $AppDB->GetRecordFromQuery("select GroupID from Groups where ID='$ID'");
		if ($Rec) {
			$Chk = $AppDB->GetRecordFromQuery("select count(*) as N from Articles where STATUS='Active' 
						and GroupID='$Rec->GroupID'");
			if ($Chk) {
				$N = $Chk->N;
				if ($N > 0) {
					$msg = "Cannot delete Group as it contains $N active articles.";
					return;
				}
			}
		}
		$AppDB->sql("delete from $Table where ID = $ID");
		header("location:admin_groups.php?msg=Group deleted.");
		exit;
	}
}

	
	if ($Save) {
		$ID = ProcessSave($ID,$rdonly,$msg,$Err);		
	}
		
	if ($Delete && $ID) {
		ProcessDelete($ID,$msg);
	}	
	
	if ($ID) {
		$F = $AppDB->get_record_assoc($ID,$Table);
		if (!$F) {
			header("location:admin_groups.php?msg=Group not found");
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
		$STATUS="Active";
	}
	

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Groups - Administration</title>
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
<form onSubmit="ParseForm(this)" name=form action="<? echo $_SERVER['PHP_SELF'] ?>" method="post">
<? hidden("ID",$ID); 
?>
<table width="100%" border=0 cellspacing=0 cellpadding=0><tr>
<td width="25%" class="subhdr">
<img src="images/groups.jpg" width="56" height="59"><span><? if (!$ID) echo "New "; ?>
Group</span></td>
<td align="left" width="75%">&nbsp;<? ShowMsgBox($msg); ?></td>
</tr></table>
<div align="center">
       
	    <div class="shadowboxfloat">
          <div class="shadowcontent">

<table width="480" cellspacing="8" cellpadding="0">
  <tr>
    <td width="100%"><table width="100%" <? echo $FORM_STYLE ?> >
        <tr>
          <td width="19%" class="form-hdr">Group ID</td>
          <td width="81%" class="form-data"><? DBField($Table,"GroupID",$GroupID); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Name</td>
          <td class="form-data"><? DBField($Table,"Name",$Name); ?></td>
        </tr>
        <tr>
          <td height="22" class="form-hdr">Status</td>
          <td class="form-data"><? DBField($Table,"STATUS",$STATUS); ?></td>
        </tr>
        <tr>
          <td colspan="2" align="right" class="form-hdr">
		    <input type="submit" name="Save" value="Save"> 
			<? if ($ID) { ?> 
		    <input type="submit" onClick="return confirm('Are you sure?')" name="Delete" value="Delete">  
			<? } ?>
		    <input onClick="window.location='admin_groups.php'" name="Back" type="button" id="Back" value="Back">
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