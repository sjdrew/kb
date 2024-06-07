<?
	include_once("config.php");
	include_once("lib/subs_image.php");	
	
	$ID = GetVar("ID");
?>	
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Upload</title>
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<body class="DialogBody" onload="AutoSizeWindow()">
<?
function get_mime_headers($fd)
{
	while(!feof($fd)) {
		$line = fgets($fd);
		if (chop($line) == "")
			break;
		if ($header_item && $line[0] == ' ' || $line[0] == "\t") { // wrapped headers
			$headers[$header_item] .= chop($line);
			continue;
		}
		if ($pos = strpos($line,":")) {
			$header_item = trim(substr($line,0,$pos));
			$headers[$header_item] = trim(substr($line,$pos+1));
		}
	}
	return $headers;
}

// Input: 
// 		$fd  = open file descriptor we can read from
//		$files_path = location to write extracted files
//
function mht_to_html($fd,$files_path,$url_path,&$ErrMsg)
{
	// Make sure MIME-Version: string
	// Get Content-Type and boundary.
	/*
MIME-Version: 1.0
Content-Type: multipart/related; boundary="----=_NextPart_01C5A8C1.F9EE0F90"

This document is a Single File Web Page, also known as a Web Archive file.  If you are seeing this message, your browser or editor doesn't support Web Archive files.  Please download a browser that supports Web Archive, such as Microsoft Internet Explorer.

------=_NextPart_01C5A8C1.F9EE0F90
Content-Location: file:///C:/D16D3265/swipe.htm
Content-Transfer-Encoding: quoted-printable
Content-Type: text/html; charset="us-ascii"

<html xmlns:v=3D"urn:schemas-microsoft-com:vml"
	*/
	$MimeOK = false;
	for($i = 0; $i < 15; ++$i) {
		$line = fgets($fd);
		if (stristr($line,"MIME-Version")) {
			$MimeOK = true;
			break;
		}
	}
	if (!$MimeOK) {
		$ErrMsg =  "Imported file did not appear to be a valid mht file.";
		return "";
	}
	$done_header = 0;
	$files = array();
	$headers = get_mime_headers($fd);
	
	if (!is_array($headers)) {
		$ErrMsg =  "MIME Headers incomplete. Could not convert file. Make sure its a .mht file.";
		return "";
	}

	if ( ($ct = $headers["Content-Type"]) && stristr($ct,"multipart")) {
		$pos = strpos($ct,"boundary=");
		if (!$pos) return "No MIME boundary specified";
		$boundary = chop(substr($ct,$pos+strlen("boundary="))); 
		if (substr($boundary,0,1) == "\"") $boundary = substr($boundary,1,strlen($boundary)-2); // remove quotes
		if ($boundary == "") {
			$ErrMsg =  "Not a Multipart MIME file as expected.";
			return "";
		}
		$boundary = "--" . $boundary;
		$file_headers = array();
		// untill done file, split files into files array
		while(!feof($fd)) {
			$line = fgets($fd);
			if (chop($line) == $boundary) {
				@mkdir($files_path);
				// Got start of boundary
				// Read headers
				$file_headers = get_mime_headers($fd);
				list($garb,$origname) = explode("file://",$file_headers["Content-Location"]);
				$fname = basename($origname);
				$files[$fname]->orignal_name = $origname;
				$files[$fname]->headers = $file_headers;		
				$file_name = $files_path . "/" . $fname;
				$files[$fname]->file_name = $file_name;
				if ($first_file == "") $first_file = $fname;
				continue;				
			}
			$files[$fname]->contents .= $line;	
		}		
	}
	else { // not multipart
		list($garb,$origname) = explode("file://",$headers["Content-Location"]);
		@mkdir($files_path);
		$fname = basename($origname);
		$files[$fname]->orignal_name = $origname;
		$files[$fname]->headers = $headers;		
		$file_name = $files_path . "/" . $fname;
		$files[$fname]->file_name = $file_name;
		$first_file = $fname;
		while(!feof($fd)) {
			$files[$fname]->contents .= fgets($fd);
		}
	}		

	// Now decode, fixup html and write each file.		
	foreach($files as $fname => $f) {
		switch($f->headers['Content-Transfer-Encoding']) {
			case "quoted-printable":
				$f->contents = str_replace("=3D","=",$f->contents);
				$f->contents = str_replace("=20"," ",$f->contents);
				$f->contents = str_replace("=\r\n","",$f->contents);
				$f->contents = str_replace("=EF=BB=BF","",$f->contents);
				
				if (stristr($f->headers['Content-Type'],"html")) {
					$f->contents = removeEvilTags($f->contents);
					// Fix up image tags by replacing any src references to file name
					// with new path to file name.
					// Note with current ereg previous file path cannot have space
					foreach(array_keys($files) as $newfile) {
						if ($newfile) {
							$f->contents = eregi_replace("(src=\")([a-zA-Z0-9_/-?&%-])*(/$newfile\")", "src=\"" . $url_path . $newfile . "\"", $f->contents); 
						}
					}
				}
				$of = fopen($f->file_name,"w");
				fwrite($of,$f->contents);
				fclose($of);
				if ($fname == $first_file) $contents = $f->contents;
				break;
			case "base64":
				$f->contents = base64_decode($f->contents);
				$of = fopen($f->file_name,"w");
				fwrite($of,$f->contents);
				fclose($of);
				break;
			default:
				break;
		}			
	}
	//
	// Get rid of any unwanted files that were part of the multipart upload
	//
	foreach($files as $fname => $f) {
		$ext = strrchr($f->file_name,'.');
		if ($ext == ".htm" || $ext == ".mso" || $ext == ".html" || $ext == ".xml" || $ext == ".css")
			unlink($f->file_name);
	}	
	return $contents;	
}

/*
 * TODO: need to remove <!--[if vml *]  to 
 * or replace any <![ if !  
 */


/**
 * Allow these tags
 */
$allowedTags = '<title><strong><p><br><body><style><link><h1><h2><h3><h4><h5><b><i><a><ol><i><ul><li><pre><hr><blockquote><img><table><td><tr><th><div>';

/**
 * Disallow these attributes/prefix within a tag
 */
$stripAttrib = 'javascript:|onclick|ondblclick|onmousedown|onmouseup|onmouseover|'.
               'onmousemove|onmouseout|onkeypress|onkeydown|onkeyup';

/**
 * @return string
 * @param string
 * @desc Strip forbidden tags and delegate tag-source check to removeEvilAttributes()
 */
function removeEvilTags($source)
{
   global $allowedTags;
   /*
    * Since strip_tags removes all comments and <-![if > tags it also removes
	* some styles that are inside comments tags, when word saves as mht file.
	* So replace this pattern to bring the style tags outside of the comments so they do not get
	* removed
	*/
   $source = str_replace("<style>\r\n<!--","<style>\r\n",$source);
   $source = strip_tags($source, $allowedTags);
   return ($source); // for now allow all through seems to be better than block and causing problems.
   //return preg_replace('/<(.*?)>/ie', "'<'.removeEvilAttributes('\\1').'>'", $source);
}

function removeScriptTags($source)
{
	return ereg_replace("~<script[^>]*>.+</script[^>]*>~isU", "", $source); 
}

/**
 * @return string
 * @param string
 * @desc Strip forbidden attributes from a tag
 */
function removeEvilAttributes($tagSource)
{
   global $stripAttrib;
   return stripslashes(preg_replace("/$stripAttrib/i", 'forbidden', $tagSource));
}

function upload_file(&$msg)
{
	global $MAX_FILE_SIZE, $ID;
	global $HTTP_POST_FILES;
	global $AppDB;
	
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
				}
				else {
					$p = strrchr($basename,'.');
					if ($p) $ext = $p; else $ext = ".txt";
					if ($ext == ".mht" || $ext == ".mhtml")	{
						$files_path = APP_ROOT_DIR . FILES_FOLDER . fmt_kb($ID);	
						$url_path = FILES_FOLDER . fmt_kb($ID) . "/";
						$msg = "";
						$contents = mht_to_html($fd,$files_path,$url_path,$msg);			
					}	
					//else $contents = fread($fd, $filesize);
					else $msg = "Only .mht or .mhtml files maybe imported";
					@fclose($fd);
					if ($contents && $msg == "") {
						global $CUser;

						$OldRec = $AppDB->GetRecordFromQuery("select * from Articles where ID=$ID");
						if ($OldRec) CreateArchiveRecord($OldRec);
						$AFields[ArticleID] = $ID;
						$AFields[Trail] = "Imported content from " . $basename . " by " . $CUser->u->FirstName . " " . $CUser->u->LastName;
						AuditTrail("AddContent",$AFields);
						$LMSETS["ContentLastModified"] = "GetDate()";
						$LMSETS["Content"] = $contents;
						$AppDB->update_record($ID,'Articles',$LMSETS);
						$msg = "Content Saved.";
						return(1);
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
	return(0);
}
if ($MAX_FILE_SIZE) { // uploading
	BusyImage(1,"Please wait...");
}
?>
<form enctype="multipart/form-data" action="<?  echo $PHP_SELF ?>" method="post">
<?
	hidden("ID",GetVar("ID"));
	hidden("nohdr",GetVar("nohdr")); // so when we refresh parent we do it right in case in frame.
?>
<table cellspacing="0" cellpadding="0" width="100%" border="0">
	<tr>
	   <td height="20%" class="DialogTitle">
		<img src="images/doc.gif" width="16" height="16"> Import mht file as Article Content
	   </td>
	</tr>
	<tr>
	   <td class="DialogBody" valign="top">
        <table height="350" width="100%" <? echo $FORM_STYLE ?>>
		<?  if ($MAX_FILE_SIZE) {  ?>
            <tr>
            	<td class="form-data" align="center" colspan="2" height="140">
                  <?
					$OK = upload_file($msg);
					BusyImage(0);
					if ($OK) {
						echo "<script language=\"Javascript\">window.opener.location='admin_article.php?ID=$ID&nohdr=$nohdr';</script>";
					}
					echo "<p class=\"MsgBox\" style=\"width:70%\">$msg</p>";
				 ?><p align="center" ><br>
				 <? if (!$OK) { ?>
				 <input type="button" onclick="history.back()" value="Back" name="Back">
				<? } ?>				 
				 <input type="button" onclick="window.close()" value="Close" name="Close"></b></td>
            </tr>		
		<?  } else {  ?>
            <tr>
            	<td align="left" colspan="2" height="56" style="padding-left:20px"><p><span class="form-hdr2"><br>
                </span><span class="MsgBox" style="width:95%">Warning: This will become the new Article content replacing any existing content.</span><span class="subhdr"><br>
                  </span><span class="form-hdr2"><br>
                  <strong>(To save a word document as an .mht file, use File-&gt;Save As and choose mht/mhtml format.)<br>
                  </strong><em><br>
                  <strong>Notes: </strong></em></span></p>
            	  <ol>
            	    <li><span class="form-hdr2"><em> There maybe some slight formatting changes in the document when converted to mht.</em></span></li>
          	        <li><span class="form-hdr2"><em> All imbeded images will automatically upload. </em></span></li>
            	    <li><span class="form-hdr2"><em>Excel files must be single worksheets only (remove other worksheets before saving as mht.)</em></span></li>
           	        <li><span class="form-hdr2"><em>Ensure filename does not contain spaces before converting to mht file type.</em></span></li>
            	  </ol></td>
          </tr>
            <tr>
            	<td CLASS="form-hdr" align="right" height="40">mht File:</td>
            	<td CLASS="form-data" height="40"><input type="hidden" name="MAX_FILE_SIZE" value="<? echo $AppDB->Settings->MaxUploadSize * 1048576 ?>">&nbsp;<input name="attachmentfile" type="file" size="29" accept="text/mht"> </td>
            </tr>
            <tr>
           	  <td colspan="2" CLASS="DialogFooter" align="right" height="25"><input type="submit" value="Import" name="Upload"><input type="button" onclick="window.close()" value="Cancel" name="Cancel"></td>
            </tr>
         <?  }  ?>
  	</table>
	</td>
   </tr>
</table>
</form>
</body>
</html>
