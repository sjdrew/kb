<? include("config.php");
   RequirePriv(PRIV_ADMIN);
   $Table = "Activity";

   $Export = GetVar('Export');
   $S = GetVar('S');
   $Search = GetVar('Search');
   $CREATEDBY = GetVar('CREATEDBY');
   $CREATED = GetVar('CREATED');
   $StartDate = GetVar('StartDate');
   $EndDate = GetVar('EndDate');

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
<form onSubmit="return parse()" method="Get" name="form" Action="<? echo $_SERVER['PHP_SELF'] ?>">
<?
} 
if ($S) {

	if (!$Export) hidden("S",$S);
	$Fields["Date"] = ":nowrap";
	$Fields["Name"] = "";
	$Fields["Activity"] = "";
	$Fields["ItemID"] = "";
	$Fields["Details"] = "";
	
	$Sort = GetVar("Sort");
	if ($Sort == "")
		$Sort = "Date desc";
	
	$UserID = GetVar("UserID");
	if ($UserID) {
		$q2 .= " AND " . USERS_TABLE . ".ID=$UserID ";
	}
    $CREATEDBY = GetVar('CreatedBy');
	if (trim((string)$CREATEDBY)) {
		$q2 .= " AND $Table.CREATEDBY = '$CREATEDBY'";
		$str .= " by user Account $CREATEDBY";
	}
    $StartDate = GetVar('StartDate');
	if (trim((string)$StartDate)) {
		$q2 .= " and $Table.CREATED >= '$StartDate'";
		if (!trim((string)$EndDate)) $str .= " Since $StartDate";
	}
    $EndDate = GetVar('EndDate');
	if (trim((string)$EndDate)) {
		$q2 .= " and $Table.CREATED < '$EndDate'";
		if (trim((string)$StartDate)) $str .= " for the Period of $StartDate to $EndDate ";
		else $str .= " before $EndDate";
	}
    $ItemID = GetVar('ItemID');
	if ($ItemID) {
		if (strtoupper(substr((string)$ItemID,0,2)) == "KB") {
			$ItemID = substr((string)$ItemID,2);
		}
		$ItemID = (int)$ItemID;
		if ($ItemID > 0) {
			$q2 = " AND ($Table.Tbl = 'Articles' AND $Table.ItemID = $ItemID) ";
			$str = " for Article " .  fmt_kb($ItemID);
		}
	}	
 	
	$q = "select Activity.CREATED as Date,Activity.CREATEDBY,ItemID,Activity,Tbl,FirstName + ' ' + LastName as Name
		,ISNULL((select Search from Searches where Activity.Tbl = 'Searches' AND ID = Activity.ItemID),
			ISNULL((select Title from Articles where Activity.Tbl = 'Articles' AND ID = Activity.ItemID),
                (select Subject from Messages where Activity.Tbl = 'Messages' AND ID = Activity.ItemID))) as Details 
				  FROM Activity  
				    left join " . USERS_TABLE . " on Activity.CREATEDBY = " . USERS_TABLE . ".Username  
						where 1=1 " . $q2 ;
						
	$LB = new ListBoxPref("Activity Log $str",$AppDB,$q,$Fields,$Sort,"",'',1);
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
                  <td height="30" colspan="3" class="normal"><strong>Activity Log Report:</strong></td>
                </tr>				
                <tr>
                  <td rowspan="5" width="14%" align="right"><img src="images/search.gif" ></td>
                  <td align="right" nowrap class="form-hdr">Starting Date: </td>
                  <td class="form-data"><?	DBField($Table,"StartDate",$StartDate);?></td>
                </tr>
                <tr>
                  <td align="right" nowrap class="form-hdr">Ending Date: </td>
                  <td class="form-data"><? DBField($Table,"EndDate",$EndDate); ?></td>
                </tr>
                <tr>
                  <td WIDTH="24%" align="right" nowrap class="form-hdr"> Article ID:</td>
                  <td WIDTH="62%" class="form-data"><? DBField($Table,"ItemID",$ItemID); ?></td>
                </tr>				
			    <tr>
			      <td align="right" class="form-hdr">Account ID:</td>
			      <td class="form-data"><? DBField($Table,"Account",$CREATEDBY); ?>&nbsp;</td>
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