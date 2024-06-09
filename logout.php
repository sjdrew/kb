<? include("config.php"); 
	$CUser->Logout();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Logout</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<script language="JavaScript" src="lib/misc.js"></script>
<script language="JavaScript" src="lib/AnchorPosition.js"></script>
<script language="JavaScript" src="lib/PopupWindow.js"></script>
<script language="JavaScript" src="lib/date.js"></script>
<script language="JavaScript" src="lib/CalendarPopup.js"></script>

<? include("header.php"); ?>

<p align=center><strong>You have been logged out.</strong></p>
<p align=center><a href="home.php">Continue to home page.</a></p>
</body>

</html>