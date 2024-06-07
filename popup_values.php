<? include("config.php");
   $TableName = GetVar("TableName");
   
   // For security we validate the table and column we are browsing so as to prevent misuse.
   if (!stristr("Articles,Groups,Products,Types,Messages",$TableName)) {
   		echo "Error: Invalid argument $TableName";
		exit;
   }
   if (!stristr("Product,ServiceName,Type,Contact1,Contact2",$FieldName)) {
   		echo "Error: Invalid columnname $FieldName";
		exit;
	}
   
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Select a value</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
function SelValue(V)
{
	var fld = window.opener.<? echo $FormName; ?>.<? echo $FieldName; ?>;
	
	fld.value = V;
	window.close();
}
</SCRIPT>
<form name=form action="<? echo $PHP_SELF ?>" method="post">
<div align="center">
		 <?
			function fmtField($Data,$ID,$R="")
			{
				return '<a title="Select" href="Javascript:SelValue(\'' . $Data . '\');">'.$Data.'</a>';
			}

			$Fields["$FieldName:Existing $FieldName values"] = "@fmtField";
			$Sort = GetVar("Sort");
			if ($Sort == "") $Sort="$FieldName";
			$q = "Select Distinct $FieldName from $TableName";
			$ListStyle = ' CELLPADDING="2" CELLSPACING="0" border="1" style="border-collapse: collapse" bordercolor="#E6E6E6"';
			$LB = new ListBox("",$AppDB,$q,$Fields,$Sort,"","",0,"","",""); //,$ListStyle,"list-sm"); 
			$LB->Set_NoFrame();
			$LB->PageSize = 0;
			$LB->NoTopStats = 1;
			$LB->Display();
			echo '<button onclick="window.close()">Close</button>'; 
		?>
</div>
</form>
</body>
</html>