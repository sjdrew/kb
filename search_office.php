<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN"
		  "http://www.w3.org/TR/REC-html40/frame.dtd">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Search - Microsoft Office Online</title>
</head>

<? $Search = str_replace('"','',$Search); ?>

<frameset rows="60,*" frameborder="NO" border="0" framespacing="0">
  <frame marginwidth="0" marginheight="0" src="header.php" name="heading" noresize scrolling="no">
  <frame src="search_office_main.php?Search=<? echo $Search ?>" name="mainFrame">
</frameset>

<noframes>
Frame Support is Required.
</noframes>
</frameset>
</html>
