<?
	include_once("config.php");
	include_once("lib/subs_image.php");	
	set_time_limit(180);
?>
<html>	
<head>
<title><? echo $AppDB->Settings->AppName ?> - Upload</title>
<link REL="stylesheet" HREF="styles.css"></link>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
</head>

<body class="DialogBody">
<center>
<?

ini_set("upload_max_filesize",$AppDB->Settings->MaxUploadSize . "M");

function upload_file()
{
	global $MAX_FILE_SIZE; 
	global $HTTP_POST_FILES;
	global $AppDB;
	
	$ID = GetVar("ID");
	$Type = GetVar("Type");
	$AttID = GetVar("AttID");
	$AsContent = GetVar("AsContent");
	
	if ($Type == "" || $ID == "") {
		echo "Internal Error, Type or ID not specified";
		exit;
	}
	if ($MAX_FILE_SIZE && $ID) {	
		if (is_uploaded_file($HTTP_POST_FILES['attachmentfile']['tmp_name'])) {
			clearstatcache();
			$orig_name = $HTTP_POST_FILES['attachmentfile']['name'];
			$basename = basename($orig_name);			
			$filename = $HTTP_POST_FILES['attachmentfile']['tmp_name'];
			$fd = @fopen($filename, "rb");
			if ($fd) {
				$filesize = @filesize($filename);
				if ($filesize == 0) {
					// Fails here if you have not provided permissions to everyone for temp folder
					$msg = "Unable to determine attachment size, or a empty attachment was provided.";
					fclose($fd);
				}
				else {
					$contents = fread($fd, $filesize);
					@fclose($fd);
					//    $thumb = GenerateThumb($basename,$contents);
					$p = strrchr($basename,'.');
					if ($p) $ext = $p; else $ext = ".txt";				
					$Fields = array();
					$Fields["$Type".ID] = $ID;
					$Fields["Size"] = $filesize;
					$Fields["DocType"] =  $ext;
					$Fields["Filename"] = $basename;
					if ($AsContent) $Fields["AsContent"] = $AsContent;
					//$Fields["Attachment"] = "0x".bin2hex($contents);
					if ($AttID) {
						// Since we are overwriting the Attachment that is the content of the article
						// Archive current version of it first.
						$OldRec = $AppDB->GetRecordFromQuery("select * from Articles where ID=$ID"); 
						CreateArchiveRecord($OldRec);
						$Fields["Attachment"] = '';
						$AppDB->update_record($AttID,$Type."Attachments",$Fields);
					}
					else $AttID = $AppDB->insert_record("$Type" . "Attachments",$Fields);
					
					if (!$AttID) {
    					$msg = "Server Error unable to save file.";
						return $msg;
					}
	    			else {
						if ($AppDB->UpdateBlob("$Type" . "Attachments","Attachment",$contents,"ID=$AttID"))
	  		    			$msg = "$orig_name was successfully attached to this $Type.";
						else
							$msg = "Unable to insert attachment:<br>" . $AppDB->Errorno(). ": ". $AppDB->ErrorMsg() . "<br>";

					}
					
					if ($Type == "Article") {
						global $CUser;
						$AFields[ArticleID] = $ID;
						$AFields[Trail] = "Attachment " . $basename . " added by " . $CUser->u->FirstName . " " . $CUser->u->LastName;
						AuditTrail(($AsContent) ? "AddAttachmentContent" : "AddAttachment", $AFields);
						// Notify Must be called before LastModified is updated.
						SendNotifications($ID,"NotifyUpdated");
						if ($AsContent) $LMSETS["Content"] = "";		
						$LMSETS["ContentLastModified"] = "GetDate()";
						$AppDB->update_record($ID,'Articles',$LMSETS);
					}
				}
  	    	}
  	    	else 
  	    		$msg = "Unable to read uploaded file. ";
  	 
		} else {
			// Attack or too big
			$msg = "Unable to upload file. Ensure it does not exceed allowed size of " . $AppDB->Settings->MaxUploadSize . "MB";
		}
	}
	return $msg;
}
if ($MAX_FILE_SIZE) { // uploading
	BusyImage(1,"Please wait...");
}
?>

<form enctype="multipart/form-data" action="<?  echo $PHP_SELF ?>" method="post">
<?
	hidden("ID",GetVar("ID"));
	hidden("Type",$Type);
	hidden("AsContent",$AsContent);
	hidden("AttID",$AttID);
	//$Frame = new FrameBox(($AsContent) ? "<img src=\"images/doc.gif\" width=\"16\" height=\"16\"> Upload Attachment as Content" : "Upload Attachment", "450");
	//$Frame->Display();	 
?>
<table cellspacing="0" cellpadding="0" width="100%" border="0">
	<tr>
	   <td height="20%" id="DialogTitleArea" class="DialogTitle">
		<?
		 echo ($AsContent) ? "<img src=\"images/doc.gif\" width=\"16\" height=\"16\"> Upload Attachment as Content" : "Upload Attachment";
	    ?>
	   </td>
	</tr>
	<tr>
	   <td class="DialogBody" height="80%" valign="top">
	   <br>
        <table width="100%">
		<?  if ($MAX_FILE_SIZE) {  ?>
            <tr>
            	<td class="form-data" align="right" colspan="2" height="140">
                <p align="center" ><b>
                  <?  
					$msg = upload_file();
					BusyImage(0);
					echo $msg;
				 ?><br><br><input type="button" onClick="window.opener.document.forms[0].submit(); window.close()" value="Close" name="Close"></b></td>
            </tr>		
		<?  } else {  ?>
            <tr>
            	<td CLASS="form-hdr" align="right" colspan="2" height="56">
				<? if ($AsContent == "") { ?>
                <p align="center"><br>
                  You may upload file(s) as an attachment to this <? echo $Type ?>.<br>
                    Each attached file must be less than <? echo $AppDB->Settings->MaxUploadSize . "MB" ?> 
				<? } else { ?>
				<p align="center">Select a document to be uploaded and displayed as the Article content. <br>
				  <em>(This will override any existing article content)</em>
				  <br><br>
				  <? } ?>
			</td>
            </tr>        
			<tr>
            	<td CLASS="form-hdr" align="right" height="40">Attachment File:</td>
            	<td CLASS="form-data" height="40"><input type="hidden" name="MAX_FILE_SIZE" value="<? echo $AppDB->Settings->MaxUploadSize * 1048576 ?>" >&nbsp;<input name="attachmentfile" type="file" size="29"> </td>
            </tr>
			<tr>
            	<td colspan="2" height="40">&nbsp;</td>
            </tr>
			
            <tr>
           	  <td colspan="2" class="DialogFooter" align="right">
			    <input type="submit" value="Upload" name="Upload"><input type="button" onClick="window.close()" value="Cancel" name="Cancel">
			   </td>
            </tr>
         <?  }  ?>
  </table>
  </td>
  </tr>
</table>
    <?  //$Frame->DisplayEnd()  ?> 
</form>
</center>
</body>
</html>
