<? include("config.php"); 

function SetContentSection2($SectionName,$Content)
{
	global $AppDB;
	global $CUser;
	$RS = $AppDB->sql("select * from ContentSections where SectionName='$SectionName'");
	if ($RS) $S = $AppDB->sql_fetch_obj($RS);
	if ($S) {
		$AppDB->sql("update ContentSections set LASTMODIFIEDBY='$CUser->UserID',LASTMODIFIED=$AppDB->sysTimeStamp, Content=".$AppDB->qstr($Content)." where SectionName='$SectionName'");
	} else {
		//$AppDB->insert_record("ContentSections",$SETS);
	}
}

   RequirePriv(PRIV_ADMIN);

   if ($Save) {
		SetContentSection2($SectionName,$Content);
   }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Edit Page Section</title>
<link REL="stylesheet" HREF="styles.css"></link>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<? if ($Save) { ?>
<script language="javascript">window.close()</script>
<? exit; } ?>
<body onLoad="AutoSizeWindow()">
<form action="<? echo $_SERVER['PHP_SELF']?>" method="post" name="form">
<? hidden("SectionName",$SectionName); ?>
<table cellspacing="0" cellpadding="0" width="100%" border="0">
	<tr>
	   <td height="20%" id="DialogTitleArea" class="DialogTitle">Edit Section: <? echo $SectionName ?></td>
	</tr>
	<tr>
	   <td class="DialogBody" height="80%" valign="top">
          <table  width="100%"  >
              <tr>
                <td><?
						include_once("lib/fckeditor.php") ;
						$S = $AppDB->GetRecordFromQuery("select * from ContentSections where SectionName='$SectionName'");
						$oFCKeditor = new FCKeditor('Content') ;
						$oFCKeditor->BasePath = "/" . APP_NAME . '/lib/';
						$oFCKeditor->Value = $S->Content;

						$oFCKeditor->Config['AllowUploads' ] = 'true';
						$dir = "/images";

						$oFCKeditor->Config['ImageUploadURL'] = $oFCKeditor->BasePath . 'editor/filemanager/upload/php/upload.php?Type=Image&ServerPath='.$dir."/";
						$oFCKeditor->Config['LinkUploadURL'] =   $oFCKeditor->BasePath . 'editor/filemanager/upload/php/upload.php?Type=Image&ServerPath='.$dir."/";
						$oFCKeditor->Config['FlashUploadURL'] =  $oFCKeditor->BasePath . 'editor/filemanager/upload/php/upload.php?Type=Image&ServerPath='.$dir."/";

						$oFCKeditor->Config['ImageBrowserURL'] = $oFCKeditor->BasePath . 'editor/filemanager/browser/custom/browser.html?Type=File&Connector=connectors/php/connector.php&ServerPath='.$dir ;
						$oFCKeditor->Config['LinkBrowserURL'] = $oFCKeditor->BasePath . 'editor/filemanager/browser/custom/browser.html?Connector=connectors/php/connector.php&ServerPath='.$dir ;
						$oFCKeditor->Config['FlashBrowserURL'] = $oFCKeditor->BasePath . 'editor/filemanager/browser/custom/browser.html?Type=Flash&Connector=connectors/php/connector.php&ServerPath='.$dir ;

						$oFCKeditor->Height	= '350' ;
						$oFCKeditor->Width	= '100%' ;
						$oFCKeditor->Create();	
					?>				
				</td>
              </tr>
           </table>
	   </td>
	</tr>
	<tr>
	   <td align="right" class="DialogFooter">
          <input type="submit" value="Save" name="Save">
          <input type="button" onClick="window.close()" value="Cancel" name="Cancel">
	   </td>
	</tr>
</table>
</form>

</body>
</html>
