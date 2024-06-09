<? include("config.php"); 

$ID = GetVar("ID");

RequirePriv(PRIV_APPROVER);

function ProcessSave($Table,$ID,$rdonly,&$msg,&$Err,$Multi=0)
{
	global $AppDB;
	
	if (ParseFields($Table,$msg) != 0)
		return $ID;
		
	if ($ID) {
		$ModFields = $AppDB->modify_form($ID,$Table,0,$Multi);
		if (!$Multi) $msg = "Changes were saved.";
	}
	else {
		$ID = $AppDB->save_form($Table);
		$msg = "New record Created.";
	}
	return $ID;
}

function ProcessDelete($Table,$ID)
{
	global $AppDB;
	
	if ($_POST[Delete] && $ID) {
		// Delete 
		$AppDB->sql("delete from $Table where ID = $ID");
		header("location:admin_categories.php?msg=record deleted.");
		exit;
	}
}

	$Singular = $Table = GetVar("Type");
	$CategoryName = $Table;
	$Custom1Label = (trim($AppDB->Settings->Custom1Label) != "") ? $AppDB->Settings->Custom1Label : "Custom1";
	if ($Table == "Custom1") $Singular = $CategoryName = $Custom1Label;
	if (substr($CategoryName,strlen($CategoryName)-1,1) == "s") $Singular = substr($CategoryName,0,strlen($CategoryName) - 1);
	
	// Handle, Save, Delete, and Reposting
	if ($Save) {
		$ID = ProcessSave($Table,$ID,$rdonly,&$msg,&$Err);		
	}
		
	if ($Delete && $ID) {
		ProcessDelete($Table,$ID);
	}
	
	if ($ID) {
		$F = $AppDB->get_record_assoc($ID,$Table);
		if (!$F) {
			header("location:admin.php?msg=Item not found");
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
		$STATUS = "Active";
	}
	

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Admin - Category</title>
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
<form onSubmit="ParseForm(this)" name=form action="<? echo $PHP_SELF ?>" method="post">
<? hidden("ID",$ID); 
   hidden("Type",$Type);
?>
<table width="100%" border=0 cellspacing=0 cellpadding=0><tr>
<td width="25%" class="subhdr">
<img src="images/categories.gif" width="48" height="37" align="left"><span>
<? if (!$ID) echo "New "; ?>
<? echo $Singular ?> Category</span></td>
<td align="left" width="75%">&nbsp;<? ShowMsgBox($msg); ?></td>
</tr></table>
<div align="center">
       
	    <div class="shadowboxfloat">
          <div class="shadowcontent">

<table width="480" cellspacing="8" cellpadding="0">
  <tr>
    <td width="100%"><table width="100%" <? echo $FORM_STYLE ?> >
        <tr>
          <td width="19%" class="form-hdr">Value:</td>
          <td width="81%" class="form-data"><? DBField($Table,"Name",$Name); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Status</td>
          <td class="form-data"><? DBField($Table,"STATUS",$STATUS); ?></td>
        </tr>
        <tr>
          <td colspan="2" align="right" class="form-hdr">
		    <input type="submit" name="Save" value="Save"> 
			<? if ($ID) { ?> 
		    <input type="submit" onClick="return confirm('Are you sure?')" name="Delete" value="Delete">  
			<? } ?>
		    <input onClick="window.location='admin_categories.php?Type=<? echo $Type ?>'" name="Back" type="button" id="Back" value="Back">
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