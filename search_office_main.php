<? include("config.php"); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><? echo $AppDB->Settings->AppName ?> - Search Office Online</title>
</head>
<?
	// Save the query (except for Admins)
	if ($Search && (!$CUser->IsPriv(PRIV_ADMIN) || !$AppDB->Settings->DontLogAdmin) ) {
		$Search = str_replace('\\\\',"",$Search);
		$Fields["Search"] = $Search; 
		$Fields["SearchType"] = "Office";
		$Fields["Matches"] = -1;
		$AppDB->insert_record("Searches",$Fields);
	}
?>
<body onLoad="SearchNow()">
<script language="javascript">
var image = new Image();
image.src = "images/srchanim.gif";
</script>
<center><img src="images/srchanim.gif" width="110" height="95"></center>
</body>
<script language="javascript">
function SearchNow()
{
window.location.replace("http://office.microsoft.com/en-ca/results.aspx?Scope=DC%2CEM%2CES%2CFX%2CHA%2CHP%2CQZ%2CRC%2CTC%2CXT&Query=<? echo $Search ?>");
}
</script>
</html>
