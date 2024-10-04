<?  
	include("config.php");
	RequirePriv(PRIV_GROUP);

    $Export = GetVar('Export');
    $S = GetVar('S');
    $Search = GetVar('Search');
    $CREATEDBY = GetVar('CREATEDBY');
    $CREATED = GetVar('CREATED');
    $StartDate = GetVar('StartDate');
    $EndDate = GetVar('EndDate');
    $GroupID = GetVar('GroupID');
    $Prompter = GetVar('Prompter');
    $Escalated = GetVar('Escalated');
    $ServiceName = GetVar('ServiceName');
    $EndTime = GetVar('EndTime');
    $StartTime = GetVar('StartTime');
    $msg = GetVar('msg');
    $Back = GetVar('Back');


	$Table = "Messages";
	if ($Export) {   	
		$defaultsaveas = "MUI_Report_".date("YmdHi",time()) . ".csv";
		Header_Excel($defaultsaveas);
	}
	else {
?>
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - MUI Report</title>
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
	if (!CheckDate(form.StartTime)) return false;
	if (!CheckDate(form.EndTime)) return false;
	return true;
}
</script>
<center>
<br>
<form onSubmit="return parse()" method="Get" name="form" Action="<? echo $_SERVER['PHP_SELF'] ?>">
<?  hidden("S",$S);
ShowMsgBox($msg,"center");
}

$S = GetVar("S");

if ($Back) $S = "";

if ($S) {
	$q = MessageQuery("",1);

	if (trim((string)$CREATEDBY)) {
		$q .= " and $Table.CREATEDBY = '$CREATEDBY' ";
		$qtxt = "$and Created by $CREATEDBY ";
	}
	
	if (trim((string)$Search)) {
		$Search = str_replace('"',"",trim((string)$Search));
		$Search = str_replace("'","",$Search);
		if ($Search) {
			$q .= " and (Subject like '%$Search%' or Message like '%$Search%') ";
		}
	}
	
	if (trim((string)$GroupID) && $GroupID > 0) {
		$q .= " and $Table.GroupID = $GroupID";
	}
		
	if (trim((string)$ServiceName)) {
		$q .= " and ServiceName = '$ServiceName'";
	}
	

	$q .= " and $Table.Type like 'MUI-%'";

	
	if (trim((string)$Escalated)) {
		$q .= " and Escalated = '$Escalated'";
	}
	
	if (trim((string)$Prompter)) {
		$q .= "and Prompter = '$Prompter'";
	}
	
	if (trim((string)$StartTime)) {
		$q .= "and StartTime >= '$StartTime'";
	}
	if (trim((string)$EndTime)) {
		$q .= "and StartTime <= '$EndTime'";
	}

	$Sort = GetVar("Sort");
	if ($Sort == "")
		$Sort = "StartTime";
		
function fmt_bb_icon($str,$ID,$R)
{	
	return(MessageIcon($R["Type"]));
}

function fmt_ticket($str)
{   //TODO not working
	return "<b><a target=_blank href=\"Case.php?Case=$str\" title=\"Click to view\">$str</a></b>";
}

	$Fields = array();
//	$Fields[" "] = "@fmt_bb_icon";
	$Fields["ID"] = '';
	$Fields["StartTime:Start Time"] = "@DateStr:nowrap";
	$Fields["TicketNumber:Ticket"] = "@fmt_ticket";
	$Fields["Subject"] = " ";
	$Fields["EndTime:End Time"] = "@DateStr:nowrap";
	$Fields["Duration"] = ":nowrap";
	$Fields["Prompter"] = ":align=center";
	$Fields["ServiceName:Service"] = " ";
	$Fields["Message"] =  ':style="font-size:8pt;" ';
	$LB = new ListBoxPref('MUI Report',$AppDB,$q,$Fields,$Sort,"admin_message.php",'',1);
	$LB->width="90%";
	$LB->CmdBar=0;
	$LB->Export = $Export;
	$LB->PageSize=-1;
	$LB->Display();
	if ($Export) exit;
?> 

<table border="0" cellspacing="0" cellpadding="8" width="90%"><tr><td align="left"><button onClick="window.location='report_mui.php?Back=1&<? echo $_SERVER['QUERY_STRING'] ?>'">Back</button></td></tr></table>
<?
} else {
?>
	 <div class="shadowboxfloat">
          <div class="shadowcontent">
            <table <? echo $FORM_STYLE ?> width="500"  >
                <tr>
                  <td height="30" colspan="3" class="normal"><strong>Multi User Incident Report:</strong></td>
                </tr>				
                <tr>
                  <td rowspan="5" width="14%" align="right"><img src="images/clipboard.gif" width="47" height="48"></td>
                  <td WIDTH="24%" align="right" nowrap class="form-hdr">Search Text:</td>
                  <td WIDTH="62%" class="form-data"><input name="Search" type="text" value="<? echo $Search ?>" size="45"></td>
                </tr>
			    <tr>
			      <td align="right" class="form-hdr">Group:</td>
			      <td class="form-data"><? GroupDropList($M->GroupID,1); ?>&nbsp;</td>
		      </tr>
                <tr>
                  <td align="right" nowrap class="form-hdr">Prompter Up:</td>
                  <td class="form-data"><? DBField($Table,"Prompter",$Prompter,0,1); ?></td>
                </tr>
                <tr>
                  <td align="right" nowrap class="form-hdr">Starting Date: </td>
                  <td class="form-data"><?	DBField($Table,"StartTime",$M->StartTime);?></td>
                </tr>
                <tr>
                  <td align="right" nowrap class="form-hdr">Ending Date: </td>
                  <td class="form-data"><? DBField("$Table","EndTime",$M->EndTime); ?></td>
                </tr>
                <tr>
				<td align="right" colspan=3 class="form-data"><input type="submit" VALUE="Search" NAME="S">
				  <input onClick="Javascript:window.location='admin_reports.php'" type="button" VALUE="Back" NAME="Back">
				  <?  HelpButton()  ?>
                </td>
              </tr>
             </table>
	</div></div>
<?
}
?>
</form> 
</center>
</body>
</html>