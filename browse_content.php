<? include("config.php"); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Browse</title>
<script type="text/javascript" src="mtmtrack.js">
</script>
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<script language="javascript">
function ListModifySelected()
{
<? if ($CUser->IsPriv(PRIV_ADMIN) || $CUser->IsPriv(PRIV_APPROVER)) { ?>
	DoListModifySelected("admin_article.php?nohdr=1");
<? } else { ?>
	alert("You must have Approver Privileges to use the Modify Selected function");
<? } ?>
}
</script>
<br>
<div style="margin: 12px;" >
<?
	$Sort = GetVar("Sort");
		
	if (!$_GET) exit;
    
    $GroupID = GetVar('GroupID');
    $Product = GetVar('Product');
    $Type = GetVar('Type');
    $Sort = GetVar('Sort');
    
	$DBFields["ID:ID"] = "@fmt_kb";
	$DBFields["Title"] = " ";
	$DBFields["Hits"] = " ";
	
	$q = "where 1=1 ";	
	if ($Sort == "")
		$Sort = "Title";
	
	if (trim((string)$GroupID)) {
		$q .= " and Articles.GroupID = '$GroupID'";
		$GR = $AppDB->GetRecordFromQuery("select * from Groups where GroupID= $GroupID");
		$qtxt = " for Group $GR->Name";
		$and = "and";
	}

	if (trim((string)$Product)) {
		if ($Product == "(unspecified)") $q .= " and Product is NULL ";
		else $q .= " and Product = " . $AppDB->qstr(trim((string)$Product));
		$and = "and";
	}
		
	if (trim((string)$Type)) {
		if ($Type == "(unspecified)") $q .= " and Type is NULL ";
		else $q .= " and Type = '$Type'";
		$and = "and";
	}	
	
	$q .= " and STATUS != 'Obsolete' ";
	
	// Account for Privs	
	$q .= PrivFilter();

	$query = "select Articles.* from Articles $q";
	$title = "Articles for $Product / $Type $qtxt";
		
	$LB = new ListBoxPref("$title",$AppDB,$query,$DBFields,$Sort,"article.php?nohdr=1",$subtitle,1,'97%');
	$LB->Form = 1;
	$LB->CmdBar = 1;
	$LB->Display();
	
?>
</div>
</body>
</html>
