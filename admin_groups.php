<? include("config.php"); 
  RequirePriv(PRIV_ADMIN);
   

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

<? include("header.php"); 

function DisplayList($params="")
{	
	$Fields["Name"] = " ";
	$Fields["GroupID"] = " ";
	$Fields["STATUS"] = " ";
    $Sort = GetVar("Sort");
	if ($Sort == "")
		$Sort = "Name";
	$q = 'Select * from Groups';
	global $AppDB;
	$LB = new ListBox('Groups',$AppDB,$q,$Fields,$Sort,"admin_group.php",'Click on a Group to Modify or <b><a style="font-size:11px" href="admin_group.php">Add</a></b> a new Group',1);
	$LB->width="90%";
	$LB->Form = 1;
	$LB->Display();
}
?>

<div align="center">
<br><br>
<? DisplayList(); ?>
<table border="0" cellspacing="0" cellpadding="8" width="90%"><tr><td align="left"><button onclick="window.location='admin.php'">Back</button></td></tr></table>
</div>

</body>

</html>