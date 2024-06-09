<? include("config.php");
   RequirePriv(PRIV_APPROVER);
   if (!$Export) { 
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Reports</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>

<? include("header.php"); ?>
<div align="center">
<br><br>
<?
}

function fmt_article($ID)
{
	return "<a href=\"article.php?ID=$ID\" title=\"Click to view\">" . fmt_kb($ID) . "</a>";
}

	$Fields["Username:UserID"] = " ";
	$Fields["Name"] = " ";
	$Fields["Email"] = "";
	$Fields["LastLogin:Last Login"] = " ";	
	$Fields["DateRead"] = ":nowrap";
	
	$Sort = GetVar("Sort");
	if ($Sort == "")
		$Sort = "DateRead desc";
	$ID = GetVar("ID");
	if (!$ID) {
	   	ShowMsgBox("Article ID was not specified.","center");
		exit;
	}
	$pf = PrivFilter();
	$R = $AppDB->GetRecordFromQuery("select ID,GroupID,Title,ContentLastModified from Articles where ID=$ID $pf");
	$GroupID = $R->GroupID;
	if (!$R) {
	   	ShowMsgBox("Article $ID not found. (Or no access to specified article).","center");
		exit;		
	}
	$str = fmt_article($ID) . " " . "<i>($R->Title)</i>";

	$Table = USERS_TABLE;
	$q = "select $Table.ID, LastLogin, Username, Email, FirstName + ' ' + LastName as Name,  " .
      "(select top 1 CREATED from Hits where Hits.ArticleID=$ID AND Hits.CREATEDBY=Username order by Hits.CREATED desc) as DateRead " .
  	  "from $Table where " .
      "(users.Groups like '1:%' OR users.Groups like '%,1:%' OR users.Groups like '$GroupID:%' OR users.Groups like '%,$GroupID:%')";

	$LB = new ListBoxPref("Article Read Status: $str",$AppDB,$q,$Fields,$Sort,"admin_user.php",'',1);
	$LB->width="90%";
	$LB->CmdBar=1;
	$LB->Form = 1;
	$LB->Export = $Export;
	$LB->Display();
?>

<table border="0" cellspacing="0" cellpadding="8" width="90%"><tr><td align="left">
<button onClick="window.location='admin_articles.php'">Back</button>
</td></tr></table>
</div>

</body>

</html>