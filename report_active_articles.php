<? include("config.php");
   RequirePriv(PRIV_APPROVER);
   $Table = "Hits";
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

function fmt_article($ID)
{
	global $Export;
	if ($Export) return fmt_kb($ID);
	return "<a href=\"article.php?ID=$ID\" title=\"Click to view\">" . fmt_kb($ID) . "</a>";
}
	if (!Export) hidden("S",$S);
	$Fields["Date"] = ":nowrap";
	$Fields["Name"] = " ";
	$Fields["KBID:Article"] = "@fmt_article:align=center";
	$Fields["Title"] = " ";
	
	$Sort = GetVar("Sort");
	if ($Sort == "")
		$Sort = "Date desc";
	
	$UserID = GetVar("UserID");
	if ($UserID) {
		$q2 .= " AND " . USERS_TABLE . ".ID=$UserID ";
	}
	if (trim($CREATEDBY)) {
		$q2 .= " AND $Table.CREATEDBY = '$CREATEDBY'";
		$str .= " by user Account $CREATEDBY";
	}
	if (trim($StartDate)) {
		$q2 .= " and $Table.CREATED >= '$StartDate'";
		if (!trim($EndDate)) $str .= " Since $StartDate";
	}
	if (trim($EndDate)) {
		$q2 .= " and $Table.CREATED < '$EndDate'";
		if (trim($StartDate)) $str .= " for the Period of $StartDate to $EndDate ";
		else $str .= " before $EndDate";
	}
	$ID = GetVar("ID");
	if ($ID) {
		if (strtoupper(substr($ID,0,2)) == "KB") {
			$ID = substr($ID,2);
		}
		$ID = (int)$ID;
		if ($ID > 0) {
			$q2 = " AND Articles.ID = $ID ";
			$str = " for Article " .  fmt_kb($ID);
		}
	}	
 
	$q3 = PrivFilter();
	$q = "Select Hits.*,Hits.CREATED as Date,Articles.ID as KBID,Articles.Title,FirstName + ' ' + LastName as Name from Hits left join " . USERS_TABLE . " on Hits.CREATEDBY = " . USERS_TABLE . ".[Username] " .
						" left join Articles on Articles.ID = Hits.ArticleID where 1=1 $q2 $q3";
	$LB = new ListBoxPref("Article Activity $str",$AppDB,$q,$Fields,$Sort,"",'',1);
	$LB->width="90%";
	$LB->CmdBar=1;
	$LB->Export=$Export;
	$LB->Display();
?>

<table border="0" cellspacing="0" cellpadding="8" width="90%"><tr><td align="left">
<? $BackLoc = str_replace("S=Search","S=",$_SERVER['REQUEST_URI']);
   $BackLoc = str_replace("S=1","S=",$BackLoc);
?>
<button onClick="window.location='<? echo $BackLoc ?>'">Back</button>
</td></tr></table>
<? } else { 
	$ID = GetVar("ID");
?>
	 <div class="shadowboxfloat">
          <div class="shadowcontent">
            <table <? echo $FORM_STYLE ?> width="500"  >
                <tr>
                  <td height="30" colspan="3" class="normal"><strong>Article Report:</strong></td>
                </tr>				
                <tr>
                  <td rowspan="5" width="14%" align="right"><img src="images/search.gif" ></td>
                  <td WIDTH="24%" align="right" nowrap class="form-hdr">Article ID:</td>
                  <td WIDTH="62%" class="form-data"><? DBField($Table,"ID",$ID); ?></td>
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