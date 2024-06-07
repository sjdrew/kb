<? include("config.php"); 
  RequirePriv(PRIV_ADMIN);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Select group</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
function onapply()
{
	if (form.Name.value == ' ') {
		alert("Please select a group");
		return false;
	}
	if (window.opener && window.opener.form && window.opener.form.AddGroup) {
		window.opener.form.AddGroup.value="" + form.Name.value + ":" + form.Mode.value + ":";
		window.opener.form.AddGroup.value += (form.MustRead.checked) ? "Y" : "N";
		window.opener.form.submit();
	}
	window.close();
}
</SCRIPT>
<form name=form action="<? echo $PHP_SELF ?>" method="post">
<div align="center">
<? 
	$Modes = array("Read","Write","Approval");
	$ModeV = array("R","W","A"); 
	if ($GroupID == "") $MustRead = "Y";
?>
<br>       
<div class="shadowboxfloat">
<div class="shadowcontent">
<table border=0>
<tr><td colspan=5><b>Select Group and Privilage mode:</b></td></tr>
<tr><td class="form-hdr">Group: </td>
  <td class="form-data"><? DBField("Groups","GroupSelect","$GroupID"); ?>
  </td>
  <td class="form-hdr">Mode: </td>
  <td class="form-data"><? dropdownlist("Mode",$Modes,$ModeV,"$Mode") ?>
  <td nowrap class="form-hdr">Must Read:
    <input type="checkbox" value="Y" <? if ($MustRead == "Y") echo "checked" ?> name="MustRead" >
  </td>
</tr>
<tr>
  <td colspan=5 align=right>&nbsp;</td>
</tr>
<tr><td colspan=5 align=right><input type="button" name="Save" onClick="javascript:onapply()" value="Select">
<input type="button" name="Cancel" value="Cancel" onclick="window.close()"></td>
</tr>
</table>
</div></div>
</div>
</form>
</body>
</html>