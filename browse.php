<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN"
		  "http://www.w3.org/TR/REC-html40/frame.dtd">
<html>
<head>
<title>Browse - Articles</title>
</head>

<frameset frameborder="0" framespacing="0" border="0" cols="*" rows="60,*">
  <frame marginwidth="0" marginheight="0" src="header.php" name="heading" noresize scrolling="no">
  <frameset frameborder="0" framespacing="0" border="0" cols="275,*" rows="*">
    <frameset frameborder="0" framespacing="0" border="0" cols="*" rows="0,*">
      <frame marginwidth="0" marginheight="0" src="browse_code.php?GroupID=<? echo $GroupID . "&Grouping=$Grouping" ?>" name="code" noresize scrolling="no" frameborder="0">
      <frame marginwidth="5" marginheight="5" src="lib/treemenu/menu_empty.html" name="menu" noresize scrolling="auto" frameborder="0">
    </frameset>
    <frame marginwidth="5" marginheight="5" src="browse_content.php" name="text" noresize>
  </frameset>

<noframes>
Frame Support is Required.
</noframes>
</frameset>
</html>
