<?  
	include("config.php");
	RequirePriv(PRIV_APPROVER,"home.php");
 ?>
<html>

<head>
<title><? echo $AppDB->Settings->AppName ?> - Administration</title>
<link REL="stylesheet" HREF="styles.css"></link>
<SCRIPT LANGUAGE="JavaScript" SRC="misc.js"></SCRIPT>
</head>
<?  $SECTION="Section-ADMIN"; 
   include("header.php");  ?>
<body>
<center>
  <?  ShowMsgBox($msg,"center"); if ($msg) echo "<br>"; ?>
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td height="14">
        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td width="22%">
            <td width="78%" colspan="2">
          <tr>
            <td colspan="5"> </td>
          </tr>
          <tr valign="middle">
            <td width="180" valign="top" align="left" background="images/vert_bar.gif"> <img src="images/spacer.gif" width="180" height="1" border=0>
              <table width="87%" border="0" cellpadding="4" cellspacing="0">
                <tr>
                  <td align="center" width="36%" ><img src="images/desk.gif" width="32" height="32"></td>
                  <td width="64%" align="center" valign="middle" class="hdr1">Administration</td>
                </tr>
                <tr>
                  <td colspan="2" class="dots">.....................................</td>
                </tr>
                <tr>
                  <td colspan="2"><p><br>
                  Management of the Knowledge Base and Users.
                    <p class="dots">....................................</p>
					<button onclick="window.location='home.php'">Back</button>
                  </td>
                </tr>
              </table>
            </td>
            <td style="padding-left:20px;" colspan="2" valign="top"><table width="100%" cellpadding="6" cellspacing="0"  >
                <tr>
                  <td align="center">
                    <div class="divIcon">
                      <p><a href="admin_articles.php"> <img src="images/article.jpg" width="50" height="53" border="0"><br>
                        Manage Articles</a></p>
                    </div>
                    <div class="divIcon"><p><a href="admin_categories.php"><img src="images/categories.gif" width="48" height="37" border="0"><br>
                      Manage Categorization</a> </p>
                    </div>
                    <div class="divIcon"><p><a href="admin_users.php"> <img src="images/users.jpg" width="56" height="55" border="0"><br>
                      Users</a></p>
                    </div>
                    <div class="divIcon"><p><a href="admin_ad_user_sync.php"> <img src="images/users_sync.jpg" width="56" height="55" border="0"><br>
                      AD User Sync</a></p>
                    </div>
		<? if ($AppDB->Settings->PrivMode != "Simple") { ?>
                    <div class="divIcon"><p><a href="admin_groups.php"> <img src="images/groups.jpg" width="56" height="59" border="0"><br>
                      Groups</a></p>
                    </div>
					<div class="divIcon"><p><a href="admin_ad_group_sync.php"> <img src="images/groups_sync.jpg" width="56" height="59" border="0"><br>
                      AD Group Sync</a></p>
                    </div>
		<? } ?>
                    <div class="divIcon"><p><a href="admin_messages.php"><img src="images/clipboard.gif" width="47" height="48" border="0"><br>
                      Bulletin Board</a></p>
                    </div>                    
                    <div class="divIcon"><p><a href="admin_webcontent.php"><img src="images/page.gif"  border="0"><br>
                      Page Contents</a></p>
                    </div>                    
                    <div class="divIcon"><p><a href="admin_reports.php"><img src="images/reports.jpg"  border="0"><br>
                      Reports</a></p>
                    </div>                    
					<div class="divIcon"><p><a href="admin_settings.php"><img src="images/settings.gif" width="42" height="48" border="0"><br>
                      Settings</a></p>
                    </div>
                    <div class="divIcon"><p><a href="admin_update.php"><img title="You must have Admin rights on the Windows Server" src="images/updates.gif"  border="0"><br>
                      Check for Updates</a></p>
                    </div>
                    <div class="divIcon"><p><a href="admin_fields.php"><img src="images/databases.gif" width="57" height="40" border="0"><br>
                      Manage Database Fields</a></p>
                    </div>
		<? if (file_exists("admin_create_update.php")) { ?>
                    <div class="divIcon"><p><a href="admin_create_update.php"><img src="images/mkupdate.gif" border="0"><br>
                      Create Update Kit</a></p>
                    </div>
		<? } ?>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</center>


</body>

</html>