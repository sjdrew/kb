<?  
	include("config.php");
	RequirePriv(PRIV_GROUP);
	$Table = "Messages";
?>
<html>

<head>
<title><? echo $AppDB->Settings->AppName ?> - Bulletins Administration</title>
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

$S = GetVar("S");
$Back = GetVar('Back');

if ($Back) $S = "";

if ($S) {
	$q = MessageQuery("",1);

    $CREATEDBY = GetVar('CREATEDBY');
    $Search = GetVar('Search');
    $GroupID = GetVar('GroupID');
    $Type = GetVar('Type');
    $ServiceName = GetVar('ServiceName');
    $STATUS = GetVar('STATUS');
    $Escalated = GetVar('Escalated');
    $Prompter = GetVar('Prompter');
    $StartTime = GetVar('StartTime');
    $EndTime = GetVar('EndTime');
    $Sort = GetVar('Sort');
    
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
	
	if (trim((string)$Type)) {
		$q .= " and Type = '$Type'";
	}
	
	if (trim((string)$ServiceName)) {
		$q .= " and ServiceName = '$ServiceName'";
	}
	
	if (trim((string)$STATUS)) {
		$q .= " and $Table.STATUS='$STATUS'";
	}
	
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
		$Sort = "Date desc";
		
function fmt_bb_icon($str,$ID,$R)
{	
	return(MessageIcon($R["Type"]));
}

	$Fields = array();
//	$Fields[" "] = "@fmt_bb_icon";
	$Fields["Subject"] = " ";
	$Fields["Type"] = ":nowrap";
	$Fields["ServiceName:Service"] = " ";
	$Fields["STATUS:Status"] = " ";
	$Fields["Date"] = "@DateStr:nowrap";
	$Fields["DisplayUntil"] = "@DateStr:nowrap";
	$Fields["GroupName"] = " ";	
	$Fields["ID"] = " ";	
	
	$LB = new ListBoxPref('Bulletin Board Messages',$AppDB,$q,$Fields,$Sort,"admin_message.php",' (Click on a Message to Modify, or <a href="admin_message.php">Add</a> a new message)',1);
	$LB->width="90%";
	$LB->CmdBar=0;
	$LB->Display();
?> 

<table border="0" cellspacing="0" cellpadding="8" width="90%"><tr><td align="left"><button onClick="window.location='admin_messages.php?Back=1&<? echo $_SERVER['QUERY_STRING'] ?>'">Back</button></td></tr></table>
<?
} else {
?>
	 <div class="shadowboxfloat">
          <div class="shadowcontent">
            <table <? echo $FORM_STYLE ?> width="500"  >
                <tr>
                  <td height="30" colspan="3" class="normal"><strong>Search for
                  Bulletins to manage:</strong></td>
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
                  <td align="right" class="form-hdr">Service Type:</td>
                  <td class="form-data"><? DBField($Table,"Type",$Type,0,1); ?>
                  </td>
                </tr>
                <tr>
                  <td width="24%" align="right" class="form-hdr">Service Name:</td>
                  <td width="62%" class="form-data"><? DBField($Table,"ServiceName",$ServiceName,0,1); PopupFieldValues($Table,"form","ServiceName"); ?>
                  </td>
                </tr>
                <tr>
                  <td width="24%" align="right" nowrap class="form-hdr">Status:</td>
                  <td width="62%" class="form-data"><? DBField($Table,"STATUS",$STATUS,0,1); ?>
                  </td>
                </tr>
                <tr>
                  <td rowspan="2" align="right">&nbsp;</td>
                  <td align="right" nowrap class="form-hdr">Escalated:</td>
                  <td class="form-data"><? DBField($Table,"Escalated",$Escalated,0,1); ?>
                  </td>
                </tr>
                <tr>
                  <td align="right" nowrap class="form-hdr">Prompter Up:</td>
                  <td class="form-data"><? DBField($Table,"Prompter",$Prompter,0,1); ?></td>
                </tr>
                <tr>
                  <td align="right">&nbsp;</td>
                  <td align="right" nowrap class="form-hdr">Starting Date: </td>
                  <td class="form-data"><?	DBField($Table,"StartTime",$M->StartTime);?></td>
                </tr>
                <tr>
                  <td align="right">&nbsp;</td>
                  <td align="right" nowrap class="form-hdr">Ending Date: </td>
                  <td class="form-data"><? DBField("$Table","EndTime",$M->EndTime); ?></td>
                </tr>
                <tr>
                </tr>
                <tr>
				<td align="right" colspan=3 class="form-data"><input type="submit" VALUE="Search" NAME="S">
				  <input onClick="Javascript:window.location='admin_message.php';" type="button" VALUE="Add" NAME="Add">
				  <input onClick="Javascript:window.location='admin.php'" type="button" VALUE="Back" NAME="Back">
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