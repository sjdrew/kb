<? 
	include("config.php"); 
	$ID = GetVar("ID");
	
	if ($Save == "Save") {
		$F["$KeyField"] = $ID;
		$F["NoteType"] = $NoteType;
		if (!$NID) {
			$F["Notes"] = $Note;
			$NID = $AppDB->insert_record("$T",$F);
			AuditTrail("AddNote",array('ID' => $ID));
		}
		else {
			$F["Notes"] = $Note;
		    $AppDB->update_record($NID,$T,$F);
			AuditTrail("EditNote",array('ID' => $ID));
		}
		if ($EmailNote && $NID && ID) {
			$msg = SendNoteToContacts($ID,$NID);
		}
	}
	else {
		if ($NID) {
			$NR = $AppDB->GetRecordFromQuery("select * from $T where ID=$NID");
			$Note = $NR->Notes;
			$NoteType = $NR->NoteType;   
			if (AllowEditNote($T,$NR,$KeyField) == -1) {
				$Abort = "1"; // really just bail out and close window with no updates done.
			}
		}
	}
 ?>
<html>
	
<head>
<title><? echo $AppDB->Settings->AppName ?> - Article Notes</title>
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body class="DialogBody" onLoad="init()">

<center>
<script language="JavaScript">
bDone = '<?  echo ($Save == "Save") ? 1 : 0;  ?>';
bAbort = '<?  echo ($Abort == 1) ? 1 : 0;  ?>';

function init()
{
	if (bAbort == 1) {
		alert("You are not permitted to Edit this note.");
		window.close();
		return;
	}
	
	if (bDone == 1 && window.opener) {
		var Msg = '<? echo $msg ?>';
		if (Msg) {
			alert(Msg);
		}
		if (window.opener.document.forms[0]) {
			window.opener.document.forms[0].submit(); 
		} else {
			window.opener.location.reload(true);
		}
		window.close();
	}
}

</script>

<form name="form" enctype="multipart/form-data" action="<?  echo $PHP_SELF ?>" method="post">
<? 
	hidden("ID",$ID);
	hidden("NID",$NID);
	hidden("T",$T);
	hidden("KeyField",$KeyField);
 ?>
<table cellspacing="0" cellpadding="0" width="100%" height="100%" border="0">
	<tr>
	   <td height="20%" id="DialogTitleArea" class="DialogTitle">
		<?
			if ($NID) echo "Edit Note"; else echo "New Note";
	    ?>
	   </td>
	</tr>
	<tr>
		<td CLASS="form-hdr" style="height:30px; text-align:center" >Note Type: <? DBField("ArticleNotes","NoteType",$NoteType,0); ?> 
		<input type="checkbox" checked name="EmailNote" value="1"> 
		Send note by email to Contacts
		</td>
	</tr>
    <tr>
        <td CLASS="form-data" align="center" height="80%">
              <textarea rows="18" name="Note" style="width:100%; height:80%; font-size:10pt;"><?  echo $Note  ?></textarea></td>
      </tr>
       <tr class="form-data">
          <td align="right" height="25">
			  <input type="submit" value="Save" name="Save"><input type="button" onClick="window.close()" value="Cancel" name="Cancel">
	      </td>
       </tr>
  </table>
</form>
</center>
</body>
</html>
