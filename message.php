<?  $nohdr = 1;
	include_once("config.php");
	$ID = GetVar("ID");
	if (!$ID) {
		echo "Message ID was not specified";
		exit;
	}	
	$q = MessageQuery($ID);
	$M = $AppDB->GetRecordFromQuery("$q");

 ?>
<html>

<title><? echo $AppDB->Settings->AppName ?> - Message</title>
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>

<SCRIPT LANGUAGE="JavaScript" SRC="misc.js"></SCRIPT>


<center>
<br><? ShowErrorLine($msg); ?>
<? 
	$HFields['MessageID'] = $ID;
				
	// If we just read it in past 30 minutes then
	// just update the hit record, rather than add a new one.
	// This prevents extra records from users doing browser refreshes.
	$MyLastHit = $AppDB->GetRecordFromQuery("select top 1 * from MessageHits where MessageID=$ID and CREATEDBY='$CUser->UserID' AND " .
					"DATEDIFF(minute,CREATED,getdate()) < 30 order by CREATED desc");

	if ($MyLastHit) {
		$HFields[CREATED] = "GetDate()";
		$AppDB->update_record($MyLastHid->ID,'Hits',$HFields,DB_NOAUDIT_UPDATE);
	}
	else {
		$AppDB->insert_record("MessageHits",$HFields);
	}
				
	// Occssionally (randomly) cleanup MessageHits table
	if (rand(1,20) == 5) {
		$NDays = $AppDB->Settings->HitsHistoryDays;
		if ($NDays == "") $NDays = 400;
		$AppDB->sql("delete from MessageHits where DATEDIFF(day,CREATED,GetDate()) >= $NDays","",0);					
	}
	AuditTrail("BulletinRead",array("BulletinID" => $ID));

	$title = MessageIcon($M->Type) . " " . $M->Type . " Bulletin";
	if ($CUser->Priv() >= PRIV_GROUP) $title .= "<span style='font-size:8pt'> (<a href=admin_message.php?nohdr=1&ID=$ID title=\"Edit Bulletin\">edit</a>)</span>";
	$Frame = new FrameBox("$title", "99%");  
	$Frame->Display();	
	?>    
        
    <table width="100%" <? echo $FORM_STYLE ?> >
        <? if ($M) { ?>
        <tr>
          <td width="24%" class="form-hdr2" style="align:left"> Date:</td>
          <td width="76%" class="form-data">
                <? echo $M->CREATED; ?> 
          </td>
        </tr>
        <tr>
            <td align="left" class="form-hdr2"> From: </td>
            <td class="form-data">
                <? echo $M->Author; ?> 
            </td>
        </tr>
        <tr >
            <td align="left" class="form-hdr2"> To:</td>
            <td class="form-data">
                <? echo $M->GroupName; ?> 
            </td>
        </tr>
        <tr>
            <td align="left" class="form-hdr2"> Subject: </td>
            <td class="form-data">
                <? echo "<b>$M->Subject</b>"; ?> 
            </td>
        </tr>
        <tr>
          <td align="left" nowrap class="form-hdr2">Service Type: </td>
          <td class="form-data"><? echo $M->ServiceType ?></td>
        </tr>
        <tr>
          <td align="left" nowrap class="form-hdr2">Service Name: </td>
          <td class="form-data"><? echo $M->ServiceName ?></td>
        </tr>
        <tr>
          <td align="left" nowrap class="form-hdr2">Ticket Number: </td>
          <td class="form-data"><? 
		  	if ($AppDB->Settings->RemedyARServer) {
				echo "<b><a target=_blank href=\"http://itshd/UpdateCase.asp?Case=$M->TicketNumber\" title=\"Click to view\">$M->TicketNumber</a></b>"; 
			} else echo $M->TicketNumber;
			?>
		  <? if ($M->TicketNumber && $AppDB->Settings->RemedyARServer) { ?>
		  	&nbsp;<a href="OpenTicket.php?Ticket=<? echo $M->TicketNumber . "&Server=" . $AppDB->Settings->RemedyARServer; ?>"><img src="images/artask.gif" title="Open with Remedy User program" border="0"></a>
		 <? 	}
		  ?>
		  </td>
        </tr>
        <tr>
          <td align="left" class="form-hdr2">Escalated:</td>
          <td class="form-data"><? echo $M->Escalated ?></td>
        </tr>
        <tr>
          <td align="left" nowrap class="form-hdr2">Start Date/Time: </td>
          <td class="form-data"><? echo $M->StartTime ?></td>
        </tr>
        <tr>
          <td align="left" nowrap class="form-hdr2">End Date/Time: </td>
          <td class="form-data"><? echo $M->EndTime ?></td>
        </tr>
        <tr>
            <td colspan="2" align="left" CLASS="form-data">
                <table height="230" width="100%" style="border-collapse: collapse" border="1"  bordercolor="#A2A2A2" cellpadding="3">
                    <tr>
                        <td style="font-weight: normal" valign="top" bgcolor="#EAEAEA">
                            <? 
							hyperlink($M->Message);
							echo (nl2br($M->Message)); ?> 
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <? } else { ?>
        <td height="100" valign="center" CLASS="form-hdr" COLSPAN="2" ALIGN="center">
            <b>We are sorry, but that Message could not be located.</b> 
        </td>
        <? } ?>
        <tr>
            <td COLSPAN="2" ALIGN="right" class="form-data">
                <input TYPE="button" VALUE="Close" NAME="Close" onClick="window.close()" >
                <?  HelpButton()  ?>
            </td>
        </tr>
  </table>
<?  $Frame->DisplayEnd()  ?>                     
</center>
<script language="javascript">
if (window.opener) {
	var e = window.opener.document.getElementById('B' + '<? echo $ID ?>');
	if (e) {
		e.className='aread';
	}
}
</script>
</body>
</html>