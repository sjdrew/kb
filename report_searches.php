<? 	include("config.php");
   	RequirePriv(PRIV_ADMIN);
	$Table = "Searches";
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
<script LANGUAGE="JavaScript" SRC="lib/date.js"></script>
<script LANGUAGE="JavaScript" SRC="lib/AnchorPosition.js"></script>
<script LANGUAGE="JavaScript" SRC="lib/PopupWindow.js"></script>
<script LANGUAGE="JavaScript" SRC="lib/CalendarPopup.js"></script>
<? include("header.php");  ?>

<script language="JavaScript">
function parse()
{
	if (!CheckDate(form.StartDate)) return false;
	if (!CheckDate(form.EndDate)) return false;
	return true;
}
function ListExport() { return DoListExport(); }
</script>
<div align="center">
<br>
<form onSubmit="return parse()" method="Get" name="form" Action="<? echo $PHP_SELF ?>">
<? 
}

if ($S) {

	if (!$Export) hidden("S",$S);

	$q = "Select $Table.*,$Table.CREATED as Date, FirstName + ' ' + LastName as Name from $Table left join " . USERS_TABLE . " on $Table.CREATEDBY = " . USERS_TABLE . ".[Username] where 1=1 ";
	
	if (trim($Search)) {
		$Search = str_replace('"',"",trim($Search));
		$Search = str_replace("'","",$Search);
		if ($Search) {
			$q .= " and (Search like '%$Search%') ";
			$str .= " for Search = '$Search' ";
		}
	}

	$UserID = GetVar("UserID");
	if ($UserID) $q .= " and " . USERS_TABLE . ".ID=$UserID";	

	if (trim($CREATEDBY)) {
		$q .= " and $Table.CREATEDBY = '$CREATEDBY'";
		$str .= " by user Account $CREATEDBY";
	}
	if (trim($StartDate)) {
		$q .= " and $Table.CREATED >= '$StartDate'";
		if (!trim($EndDate)) $str .= " Since $StartDate";
	}
	if (trim($EndDate)) {
		$q .= " and $Table.CREATED < '$EndDate'";
		if (trim($StartDate)) $str .= " for the Period of $StartDate to $EndDate ";
		else $str .= " before $EndDate";
	}

	$Sort = GetVar("Sort");
	if ($Sort == "")
		$Sort = "StartTime";

	function fmt_searchstr($str) { return str_replace("+"," ",$str); }
	
	$Fields["Date"] = " ";
	$Fields["Name"] = " ";
	$Fields["Search"] = "@fmt_searchstr";
	$Fields["SearchType:Type"] = " ";
	$Fields["Matches"] = ":align=center";
	
	$Sort = GetVar("Sort");
	if ($Sort == "")
		$Sort = "Date desc";
	$LB = new ListBoxPref("Search History $str",$AppDB,$q,$Fields,$Sort,"report_session.php",'',1);
	$LB->width="90%";
	$LB->CmdBar=1;
	$LB->Export = $Export;
	$LB->Display();

?>
<table border="0" cellspacing="0" cellpadding="8" width="90%"><tr><td align="left">
<? $BackLoc = str_replace("S=Search","S=",$_SERVER['REQUEST_URI']);
   $BackLoc = str_replace("S=1","S=",$BackLoc);
?>
<button onClick="window.location='<? echo $BackLoc ?>'">Back</button>
</td></tr></table>
<? } else { 
	?>
	 <div class="shadowboxfloat">
          <div class="shadowcontent">
            <table <? echo $FORM_STYLE ?> width="500"  >
                <tr>
                  <td height="30" colspan="3" class="normal"><strong>Search History Report:</strong></td>
                </tr>				
                <tr>
                  <td rowspan="5" width="14%" align="right"><img src="images/search.gif" ></td>
                  <td WIDTH="24%" align="right" nowrap class="form-hdr">Search Text:</td>
                  <td WIDTH="62%" class="form-data"><input name="Search" type="text" value="<? echo $Search ?>" size="45"></td>
                </tr>				
			    <tr>
			      <td align="right" class="form-hdr">Account ID:</td>
			      <td class="form-data"><? DBField($Table,"Account",$CREATEDBY); ?>&nbsp;</td>
 		       </tr>				
                <tr>
                  <td align="right" nowrap class="form-hdr">Starting Date: </td>
                  <td class="form-data"><?	DBField($Table,"StartDate",$StartDate);?></td>
                </tr>
                <tr>
                  <td align="right" nowrap class="form-hdr">Ending Date: </td>
                  <td class="form-data"><? DBField($Table,"EndDate",$EndDate); ?></td>
                </tr>
                <tr>
				<td align="right" colspan=3 class="form-data">
				  <input type="submit" VALUE="Search" NAME="S">
				  <input onClick="Javascript:window.location='admin_reports.php'" type="button" VALUE="Back" NAME="Back">
				  <?  HelpButton()  ?>
                </td>
              </tr>
             </table>
	</div></div>
<? } ?>
</form>
</div>
</body>

</html>