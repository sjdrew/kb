<?
	include("config.php");
	RequirePriv(PRIV_ADMIN);
?>
<html>

<head>
<title><? echo $AppDB->Settings->AppName ?> - Administration</title>
<link REL="stylesheet" HREF="styles.css"></link>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
</head>
<? $SECTION="Section-ADMIN"; 
   include("header.php"); ?>
<body>

<center>
<br>
<?
	ShowErrorLine($msg);
	
	//
	// Always make sure that all Fields in the current table exist as records in the FieldDetails table
	// If they dont create them. Then we can easily list the FieldDetails table and quickly see which ones
	// need some attention.
	// If the max_length is > 128 assume a text area
	// Required: No
	// Read/Write = Submitter;Administrators
	// Read = Everyone
	// HTML Size = min(50,max_length)
	// HTML MaxLength = max_length
	// Style = "field"

	$Tables = $AppDB->MetaTables("TABLES");
	sort($Tables);
	$Tmp = array();
	foreach($Tables as $T) {
		if ($T != "FieldDetails")
			$Tmp[] = $T;
	}
	$Tables = $Tmp;
	if ($TableName == "") $TableName = $Tables[0];
	
	$Cols = $AppDB->MetaColumns($TableName);
	foreach($Cols as $C) {
		// If a Field def does not exist in FieldDetails then create it.
		$Rec = $AppDB->GetRecordFromQuery("select * from FieldDetails where TableName = '$TableName' and ColumnName = '$C->name'");
		if (!$Rec) {
			$SETS["TableName"] = $TableName;
			$SETS["ColumnName"] = $C->name;
			$SETS["FieldName"] = $C->name;
			$SETS["RWGroups"] = "Submitter;Administrators";
			$SETS["RGroups"] = "Everyone";
			$SETS["Type"] = "TextBox";
			$SETS["HTMLSize"] = min(50,$C->max_length);
			$SETS["MaxLength"] = $C->max_length;
			$SETS["Style"] = "field";
			$SETS["Required"] = "No";
			$AppDB->insert_record("FieldDetails",$SETS);
		}
	}
	// If a Field Def exists but column name no longer exists, then remove it
	$FList = $AppDB->MakeArrayFromQuery("select ID,ColumnName as ITEM from FieldDetails where TableName='$TableName'");
	foreach($FList as $FID => $CName) {
		$Found = 0;
		foreach($Cols as $C) {
			if ($C->name == $CName) {
				$Found = 1;
			}
		}
		if ($Found == 0) {
			$AppDB->delete_record($FID,"FieldDetails");
		}
	}
	
	$Fields["ColumnName"] = " ";
	$Fields["FieldName"] = " ";
	$Fields["Type"] = " ";
	$Fields["MaxLength"] = ":align=right";
	$Fields["Required"] = ":align=center";
	//$Fields["RWGroups"] = " ";
	//$Fields["RGroups"] = " ";
	$Fields["HelpText"] = "";
	$Sort = GetVar("Sort");
	if ($Sort == "")
		$Sort="ColumnName";
	$q = "Select * from FieldDetails where TableName = '$TableName'";
	$subtitle = '(Click on a Field to Modify Details, or <a href="admin_field.php?TableName=' . $TableName . '">Add</a> a new Field)';
	
	ob_start();
	dropdownlist("TableName",$Tables,$Tables,$TableName,"onchange='F_LT1.submit()'");
	$DropHTML = ob_get_contents();
	ob_end_clean();
	
	$title = "Table $Table Field Schema for: $DropHTML";
	$LB = new ListBox("$title",$AppDB,$q,$Fields,$Sort,"admin_field.php",$subtitle,1);
	$LB->Form=1;
	$LB->PageSize=50;
	$LB->Display();
	echo '<table border="0" cellspacing="0" cellpadding="8" width="90%"><tr><td align="left"><button onclick="window.location=\'admin.php\'">Back</button></td></tr></table>';
?>
	
</center>
</body>
</html>