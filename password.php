<? 
include("config.php"); 
	
	$Table = "users";
	$ID = GetVar("ID");
	$ID = $CUser->u->ID;
	if ($ID == "" ) {
		header("location: home.php");
		exit;
	}

	if ($Save) {
		if ($Password == "") {
			$msg = "Password cannot be blank.";
		}
		else if ($Password != $Password2) {
			$msg = "Password and confirmation password fields do not match. Please re-enter the password.";
		}
		else {
			$AppDB->modify_form($ID,"$Table");
			header("location: myprofile.php?msg=Password has been changed.");
			exit;
		}
	}
	

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Profile - Change Password</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<? include("header.php"); ?>
<br>
<center>

<form action="<? echo $PHP_SELF ?>" method="post" name="form">
<? hidden("ID",$ID); ?>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  	<tr><td height="14"> 	    
  	<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td width="22%"> 
      <td width="78%" colspan="2"> 
    <tr> 
      <td colspan="5"> </td>
    </tr>
    <tr>
      <td width="180" valign="top" align="left" background="images/vert_bar.gif">
	  <img src="images/spacer.gif" width="180" height="1" border=0>
	  <table width="87%" border="0" cellpadding="4" cellspacing="0">
          <tr><td ><img src="images/register.jpg" width="74" height="41" border="0"></td>
            <td valign="top" class="hdr1"><br>
              PASSWORD<br>
			</td>
          </tr>
          <tr> 
            <td colspan="2" class="dots">.....................................</td>
          </tr>
          <tr> 
            <td colspan="2">Change your password</td>
          </tr>
          <tr> 
            <td colspan="2" class="dots" >.....................................</td>
          </tr>
       </table>
	  </td>
      <td colspan="2" valign="top"> <table width="90%" border="0" align="center" cellpadding="4">
          <tr> 
            <td colspan="2"> <? ShowMsgBox($msg); ?>
          <tr> 
            <td height="42" colspan="2" valign="top" class="subhdr" >Password</td>
          </tr>
          <tr>
            <td width="25%" class="form-hdr">Password</td>
            <td width="75%" class="form-data"><? DBField($Table,"Password",$Password); ?></td>
          </tr>
          <tr>
            <td class="form-hdr">Confirm Password</td>
            <td class="form-data"><input name="Password2" type="password" value="<? echo $Password2 ?>" size="30" maxlength="30" ></td>
          </tr>
          <tr valign="middle"> 
            <td colspan="2" > <div align="right"> 
                <input name="Save" type="submit" value="ChangePassword">				
		    	<input onclick="window.location='myprofile.php'" name="Cancel" type="button" id="Cancel" value="Cancel">
              </div></td>
          </tr>
          <tr> 
            <td colspan="2">&nbsp;</td>
          </tr>
        </table></td>
    </tr>
  </table>
    </td>
  </tr>
</table>
</form>
</center>
</body>

</html>