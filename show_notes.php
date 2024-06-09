<? 
	include("config.php"); 
?>
<html>
	
<head>
<title><? echo $AppDB->Settings->AppName ?> - Article Notes</title>
<link REL="stylesheet" HREF="styles.css"></link>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
</head>
<body class="DialogBody">

<center>
<form name="form" enctype="multipart/form-data" action="<?  echo $PHP_SELF ?>" method="post">
<? hidden("ArticleID",$ArticleID); 
	hidden("DeleteNoteID","");
?>
<table cellspacing="0" cellpadding="0" width="100%" height="100%" border="0">
	<tr>
	   <td height="20%" id="DialogTitleArea" class="DialogTitle">Notes for Article <? echo fmt_kb($ArticleID); ?>
	   </td>
	</tr>
    <tr>
        <td CLASS="DialogBody" align="center" height="80%">
		    <div style="overflow:auto; height:380px">
		    <? ShowNotes("ArticleNotes",$ArticleID,"ArticleID",$printview); ?>
			</div>
      </tr>
       <tr class="DialogFooter">
          <td align="right" height="25">
		 <input type="button" onClick="window.close()" value="Close" name="Close">
	      </td>
       </tr>
  </table>
</form>
</center>
</body>
</html>