<? include("config.php"); 
   
   $Type = GetVar("Type");
   
   if ($Type != "Custom1" && $Type != "Types") $Type = "";
   if ($Type == "") $Type = "Types";
   
   RequirePriv(PRIV_APPROVER);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Admin Categorization</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>

<? include("header.php"); 

function DisplayList($Table)
{

	$Singular = $Table;
	if (substr($Table,strlen($Table)-1,1) == "s") $Singular = substr($Table,0,strlen($Table) - 1);
	
	$Fields["Name"] = " ";
	$Fields["STATUS"] = " ";
    $Sort = GetVar("Sort");
	if ($Sort == "")
		$Sort = "Name";
	$q = 'Select * from ' . $Table;
	global $AppDB;
	
	ob_start();
	
	$Custom1Label = (trim($AppDB->Settings->Custom1Label) != "") ? $AppDB->Settings->Custom1Label : "Custom1";
	$TablesTxt = array($Custom1Label,"Types");
	$Tables = array("Custom1","Types");
	dropdownlist("Type",$TablesTxt,$Tables,$Table,"onchange='F_LT1.submit()'");
	$DropHTML = ob_get_contents();
	ob_end_clean();
	
	$title = "Category Type: $DropHTML";	
	$LB = new ListBox($title,$AppDB,$q,$Fields,$Sort,"admin_category.php?Type=$Table"," (Click on an $Singular to Modify or <b><a style=\"font-size:11px\" href=\"admin_category.php?Type=$Table\">Add</a></b> a new $Singular)",1);
	$LB->width="90%";
	$LB->Form = 1;
	$LB->Display();
}
?>

<div align="center">
<br><br>
<? DisplayList($Type); ?>
<table border="0" cellspacing="0" cellpadding="8" width="90%"><tr><td align="left"><button onClick="window.location='admin.php'">Back</button></td></tr></table>
</div>

</body>

</html>