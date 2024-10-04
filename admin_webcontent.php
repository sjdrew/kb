<? include("config.php"); 
   RequirePriv(PRIV_ADMIN);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Customize Page Content</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<script LANGUAGE="JavaScript" SRC="lib/AnchorPosition.js"></script>
<script LANGUAGE="JavaScript" SRC="lib/PopupWindow.js"></script>
<script LANGUAGE="JavaScript" SRC="lib/CalendarPopup.js"></script>
<script language="javascript">
function EditSection(s)
{
	dialog_window('admin_edit_section_popup.php?SectionName=' + s,720,500,'');
}
</script>
<? include("header.php"); ?>
<form name=form action="<? echo $_SERVER['PHP_SELF'] ?>" method="post">
<table width="100%" border=0 cellspacing=0 cellpadding=0><tr>
<td width="25%" nowrap class="subhdr">
<img src="images/page.gif" width="49" height="43">Customize Page Content<br>
<br> </td>
<td align="left" width="75%">&nbsp;<? ShowMsgBox($msg); ?></td>
</tr></table>

<div align="center">
	    <div class="shadowboxfloat">
          <div class="shadowcontent">
<table width="480" cellspacing="8" cellpadding="0">
  <tr>
    <td width="100%">
	   <table width="100%" cellpadding="4" <? echo $FORM_STYLE ?> >
		<tr><td colspan="2" class="form-hdr"><div align="center"><strong>Click on the section name that you wish to add or change content. </strong></div></td></tr>
		<tr>
		  <td colspan="2">&nbsp;</td>
		  </tr>
		<tr class="subhdr">
		  <td nowrap><u>Name</u></td>
		  <td><u>Purpose</u></td>
		  </tr>
		<tr>
		  <td nowrap><a href="Javascript:EditSection('Header');">Header bar</a> </td>
		  <td>Content displayed on the top of each page.</td>
		  </tr>
		<tr>
		  <td nowrap><a href="Javascript:EditSection('Article Footer');">Article Footer</a></td>
		  <td>Content displayed on the bottom of Article page. </td>
		  </tr>
		<tr>
		  <td nowrap><a href="Javascript:EditSection('HomePageTopCenter');">Home Page Top Center</a> </td>
		  <td>If provided, this content displays above the Search panel on the home page. </td>
		  </tr>
		<tr>
		  <td width="34%" nowrap><a href="Javascript:EditSection('HomePageLeftColumn');">Home Page Left Column</a> </td>
		  <td width="66%">Content supplied here is displayed on the left column of the home page </td>
		</tr>
      </table>	   
      </td>
  </tr>
</table>
</div></div>
</div>
</form>
</body>
</html>