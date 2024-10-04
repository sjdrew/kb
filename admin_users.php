<? include("config.php");
 RequirePriv(PRIV_ADMIN);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - User Administration</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<? include("header.php");  ?>
<br>
<br>
<center>
<?  
ShowMsgBox($msg,"center");
$Table = USERS_TABLE;

if (GetVar('Back')) $Search = "";


function fmt_grouplist($GrpStr) {
	static $GroupNames;
	global $AppDB;
	
	if (!is_array($GroupNames)) {
		$GroupNames = $AppDB->MakeArrayFromQuery("select GroupID as ID,Name as ITEM from Groups");
	}
    $UsersGroups = '';
    $comma = '';
	$List = GroupStrToArray($GrpStr);
	if (is_array($List)) {
		foreach($List as $GroupID => $Perm) {
			$GroupName = $GroupNames[$GroupID];
			if ($GroupName == "") $GroupName = $GroupID;
			if ($Perm) $GroupName .= "[" . $Perm . "]";
			$UsersGroups .= $comma . $GroupName;
			$comma = ", ";
		}
	}
	return $UsersGroups;
}

if ($Search) {
	
	$q = 'Select * from ' . $Table . " where 1=1 ";

	if (trim((string)$Name)) {
		$q .= " and (FirstName + ' ' + LastName) like '%$Name%'";
	}

	if (trim((string)$UserID)) {
		$q .= " and Username like '%$UserID%'";
	}

	if (trim((string)$GroupID) && $GroupID > 0) {
		$q .= " and ($Table.Groups like '$GroupID:%' OR $Table.Groups like '%,$GroupID:%') ";
	}
	
	$Sort = GetVar("Sort");
	if ($Sort == "")
		$Sort = "$LastName";
		
	$Fields = array();
	$Fields["Username"] = " ";
	$Fields["FirstName"] = " ";
	$Fields["LastName"] = " ";
	$Fields["Email"] = " ";	
	$Fields["Groups"] = "@fmt_grouplist";	
	$Fields["Phone"] = " ";	
	$Fields["LastLogin:Last Login"] = " ";	
	$Sort = GetVar("Sort");
	if ($Sort == "")
		$Sort = "Username";
	global $AppDB;
	$LB = new ListBoxPref('Users',$AppDB,$q,$Fields,$Sort,"admin_user.php",' (Click on a User to Modify or <a href="admin_user.php" title="Add User">Add</a> a new user)',1);
	$LB->width="90%";
	$LB->CmdBar=0;
	$LB->Form=1;
	$LB->Display();
?> 
<table border="0" cellspacing="0" cellpadding="8" width="90%"><tr><td align="left"><button onClick="window.location='admin_users.php?Back=1&<? echo $_SERVER['QUERY_STRING'] ?>'">Back</button></td></tr></table>
<?
} else {
?>
<form method="Get" name="form" onsubmit='this.Search.value=1;this.submit();' Action="<? echo $_SERVER['PHP_SELF'] ?>">
	<? hidden("Search",""); ?>
	 <div class="shadowboxfloat">
          <div class="shadowcontent">
            <table <? echo $FORM_STYLE ?> width="500"  >
                <tr>
                  <td height="30" colspan="3" class="normal"><strong>Search for Users:</strong></td>
                </tr>				
                <tr>
                  <td rowspan="4" width="12%" align="right"><img src="images/users.gif" width="56" height="55"></td>
                  <td WIDTH="29%" align="right" nowrap class="form-hdr">Name:</td>
                  <td WIDTH="59%" class="form-data"><input name="Name" type="text" id="Name" value="<? echo $Name ?>" size="45"></td>
                </tr>
			    <tr>
			      <td align="right" class="form-hdr">User ID: </td>
			      <td class="form-data"><input name="UserID" type="text" id="UserID" value="<? echo $UserID ?>" size="45"></td>
		      </tr>
			    <tr>
			      <td align="right" class="form-hdr">Group:</td>
			      <td class="form-data"><? GroupDropList($GroupID); ?>&nbsp;</td>
		      </tr>
                <tr>
                  <td colspan="2" align="right" class="form-hdr">&nbsp; </td>
                </tr>
                <tr>
				<td align="right" colspan=3 class="form-data">
				  <input type="submit" VALUE="Search" NAME="S">
				  <input onClick="Javascript:window.location='admin_user.php';" type="button" VALUE="Add" NAME="Add">
				  <input onClick="Javascript:window.location='admin.php'" type="button" VALUE="Back" NAME="Back">
				  <?  HelpButton()  ?>                </td>
              </tr>
             </table>
	</div></div>
</form> 
<?
}
?>
</center>
</body>
</html>