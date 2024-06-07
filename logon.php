<?
	if ($NewUser) {
		header("location: register.php");
		exit;
	}
	 
	$auth_in_progress = true;
	include("config.php"); 

	if ($CUser->LoggedIn && $AppDB->Settings->AuthenticationMode == "NT") {
		// no login box required, just go back.
		$backto = $_SERVER["HTTP_REFERER"];
		if ($backto == "") $backto = "index.php";
		header("location: $backto");
		exit;
	}
	
	if ($Login) {
		$CUser->AutoLogin = $AutoLogin;
		$err = $CUser->Login($UserID,$Password,$TZOffset);
		if ($err == "OK") {
			if ($target) {
				header("location: $target");
				exit;
			}
			else {
				header("location: home.php");
				exit;
			}
		}
	}
	else {
		$empw = $_POST["empw"];
		if ($empw && $UserID) {
			EmailPassword($UserID,$err);
		}
	}
 ?>
<html>

<head>
<link REL="stylesheet" HREF="styles.css">
<title><? echo $AppDB->Settings->AppName ?> - Logon</title>
</head>

<body onLoad="init()">

<script language="javascript">
function init()
{
	var D = new Date();
	document.forms[0].TZOffset.value = D.getTimezoneOffset();
	document.forms[0].UserID.focus(); 
}

function EmailPW()
{
	df = document.forms[0];
	
	if (df.UserID.value == "") {
		alert("Please enter field.");
		return;
	}
	df.empw.value=1;
	df.submit();
}
</script>

<table BORDER="0" CELLPADDING="0" CELLSPACING="0" STYLE="border-collapse: collapse" width="100%">
    <tr>
        <td align="left" valign="top"><p>&nbsp;</p>
        <p>&nbsp;</p></td>
    </tr>
    <tr>
        <td WIDTH="100%" ALIGN="center">
        <font SIZE="1">(c) 2007</font></td>
    </tr>
</table>
<br>
<center>
<font color=red><b><?  echo $err ?></b></font>
<form name="form" action="logon.php" method="post">
<? 	hidden("empw","");
	hidden("target",$target);
	hidden("TZOffset",360);					
	
	$Frame = new FrameBox("Sign in", "440");  
	$Frame->Display();	
	?>    

        <table width="100%" BORDER="0" CELLPADDING="4" CELLSPACING="0" style="background-color: #eaeaea; border: solid black 1px;">
            <tr>
                <td HEIGHT="47" COLSPAN="2" CLASS="form-hdr" align="left">
                  <div align="center">Please enter your Login Information
                </div></td>
            </tr>
            <tr>
                <td WIDTH="32%" HEIGHT="21" align="right" CLASS="form-hdr">User
                  ID: </td>
                <td WIDTH="51%" HEIGHT="21" CLASS="form-data"><input TYPE="text" NAME="UserID" SIZE="30"></td>
            </tr>
            <tr>
                <td WIDTH="32%" HEIGHT="21" align="right" CLASS="form-hdr">Password:</td>
                <td WIDTH="51%" HEIGHT="21" CLASS="form-data">
                <input TYPE="password" NAME="Password" SIZE="30"></td>
            </tr>
            <tr>
              <td HEIGHT="21" align="right" CLASS="form-hdr">&nbsp;</td>
              <td HEIGHT="21" CLASS="form-hdr2"><input name="AutoLogin" type="checkbox" class="form-hdr2" value="1">
                Login automatically from this Computer.</td>
            </tr>
            <tr>
              <td HEIGHT="21" colspan="2" align="right" CLASS="form-hdr">&nbsp;</td>
            </tr>
            <tr>
                <td HEIGHT="21" colspan="2" align="right" CLASS="form-hdr">
                  <input TYPE="submit" VALUE="Login" NAME="Login">
                  <input disabled TYPE="submit" VALUE="New User" NAME="NewUser">
                  <input TYPE="button" onClick="window.location='home.php'" VALUE="Cancel" NAME="Cancel">
                </td>
            </tr>
            <tr>
                <td CLASS="form-data" COLSPAN="2" ALIGN="right"><a HREF="javascript:EmailPW()">email
                my password</a></td>
            </tr>
    </table>
<?  $Frame->DisplayEnd()  ?>            
           
</form>
</center>


</body>

</html>