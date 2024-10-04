<? 	include("config.php"); 
   	RequirePriv(PRIV_ADMIN);
	$ID = GetVar("ID");
	$Table = "Settings";
    $msg = GetVar('msg');
    $rdonly = GetVar('rdonly');


function ProcessSave($ID,$rdonly,&$msg,&$Err,$Multi=0)
{
	global $AppDB;
	global $Table;
		
	if (ParseFields($Table,$msg) != 0)
		return $ID;
	
		
	if ($ID) {
		// checkboxes do not post if not set
		if (empty($_POST['FullTextBackground'])) $_POST['FullTextBackground'] = 0;
		if (empty($_POST['LastModifyLock'])) $_POST['LastModifyLock'] = 0;
		if (empty($_POST['DontLogAdmin'])) $_POST['DontLogAdmin'] = 0;
		if (empty($_POST['FiltersOnHomePage'])) $_POST['FiltersOnHomePage'] = 0;
		if (empty($_POST['AllowCreateBulletins'])) $_POST['AllowCreateBulletins'] = 0;
		if (empty($_POST['AllowCreateBulletinsW'])) $_POST['AllowCreateBulletinsW'] = 0;
		if (empty($_POST['AllowModifyArticles'])) $_POST['AllowModifyArticles'] = 0;
		if (empty($_POST['IndicatePrivateArticle'])) $_POST['IndicatePrivateArticle'] = 0;

		if (GetVar('InitCatalog')) {
			InitFullTextCatalog($msg);
		}
	
	
		$ModFields = $AppDB->modify_form($ID,$Table,0,$Multi);
		if ($_POST['FullTextBackground']) {
			$AppDB->sql("Exec sp_fulltext_table 'Articles', 'start_change_tracking'");
			$AppDB->sql("EXEC sp_fulltext_table 'Articles', 'start_background_updateindex'");
			$AppDB->sql("Exec sp_fulltext_table 'ArticleAttachments', 'start_change_tracking'");
			$AppDB->sql("EXEC sp_fulltext_table 'ArticleAttachments', 'start_background_updateindex'");
		}
		else if ($_POST['InitCatalog'] == "") {
			$msg = "Warning Background Full Text Population is not enabled.";
			$AppDB->sql("Exec sp_fulltext_table 'Articles', 'stop_change_tracking'");
			$AppDB->sql("EXEC sp_fulltext_table 'Articles', 'stop_background_updateindex'");
			$AppDB->sql("Exec sp_fulltext_table 'ArticleAttachments', 'stop_change_tracking'");
			$AppDB->sql("EXEC sp_fulltext_table 'ArticleAttachments', 'stop_background_updateindex'");		
		}

		if ($msg == "") $msg = "Changes were saved.";
	}
	else {
		$ID = $AppDB->save_form($Table);
	}
	return $ID;
}

function InitFullTextCatalog(&$msg)
{
	global $AppDB;
	/* 
	 * Enable Full Text Catalogs, first remove if already there.
	 */
    //  no longer required with nvar(max)
	//$AppDB->sql("exec sp_tableoption N'Articles', 'text in row', 'ON'");
	//$AppDB->sql("exec sp_tableoption N'ArticleAttachments', 'text in row', 'ON'");

	$AppDB->sql("if (select DATABASEPROPERTY('" . DBNAME . "', N'IsFullTextEnabled')) <> 1 exec sp_fulltext_database N'enable' ");	
			
	/* drop current */		
	$AppDB->sql("if OBJECTPROPERTY(object_id('Articles'),'TableHasActiveFulltextIndex') = 1 exec sp_fulltext_table  N'[dbo].[Articles]', N'drop'");
	$AppDB->sql("if OBJECTPROPERTY(object_id('ArticleAttachments'),'TableHasActiveFulltextIndex') = 1 exec sp_fulltext_table  N'[dbo].[ArticleAttachments]', N'drop'");

	// Never actually remove the Catalog as some SQL installations do no allow recreating the catalog by the same name without
	// first backing up the transaction log
	// TODO: Look for work around (ie truncating log?) see msg below
	//		Msg 1833, Level 16, State 3, Line 1
	//		File 'sysft_KB' cannot be reused until after the next BACKUP LOG operation.
		
	//$AppDB->sql("if exists (select * from dbo.sysfulltextcatalogs where name = '" . DBNAME . "') exec sp_fulltext_catalog '" . DBNAME . "', N'drop'");
			
	/* create new */
	$AppDB->sql("if not exists (select * from dbo.sysfulltextcatalogs where name = '" . DBNAME . "') exec sp_fulltext_catalog '" . DBNAME . "', N'create'");
	$AppDB->sql("exec sp_fulltext_table N'[dbo].[ArticleAttachments]', N'create', '" . DBNAME . "', N'PK_Attachments'");
	$AppDB->sql("exec sp_fulltext_column N'[dbo].[ArticleAttachments]', N'Filename', N'add', 1033  ");
	$AppDB->sql("exec sp_fulltext_column N'[dbo].[ArticleAttachments]', N'Attachment', N'add', 1033, N'DocType'");
	$AppDB->sql("exec sp_fulltext_table N'[dbo].[ArticleAttachments]', N'activate'");
	$AppDB->sql("exec sp_fulltext_table N'[dbo].[Articles]', N'create', '" . DBNAME . "', N'IX_Articles'");
	$AppDB->sql("exec sp_fulltext_column N'[dbo].[Articles]', N'Title', N'add', 1033");
	$AppDB->sql("exec sp_fulltext_column N'[dbo].[Articles]', N'Product', N'add', 1033"); 
	$AppDB->sql("exec sp_fulltext_column N'[dbo].[Articles]', N'Type', N'add', 1033");
	$AppDB->sql("exec sp_fulltext_column N'[dbo].[Articles]', N'Content', N'add', 1033"); 
	$AppDB->sql("exec sp_fulltext_column N'[dbo].[Articles]', N'Keywords', N'add', 1033");
	$AppDB->sql("exec sp_fulltext_table N'[dbo].[Articles]', N'activate'");
		
	if (!$_POST['FullTextBackground']) {
		$AppDB->sql("EXEC sp_fulltext_table 'Articles', 'start_full'");
		$AppDB->sql("Exec sp_fulltext_table 'ArticleAttachments', 'start_full'");			
	}
	$msg = "Full Text Catalog Re-Population started";
}


	$ID = 1;
	
	if (GetVar('InitCatalog')) $_POST["Save"] = 1;
	
	if (GetVar("Save")) {
		$ID = ProcessSave($ID,$rdonly,$msg,$Err);		
	}
					
	if ($ID) {
		$F = $AppDB->get_record_assoc($ID,$Table);
		if (!$F) {
			$SETS['ID'] = 1;
			$SETS['AuthenticationMode'] = "NTLM";
			$SETS['PrivMode'] = "Simple";
			$SETS['SMTPServer'] = "mail";
			$SETS['AppName'] = "Knowledge Base";
			$SETS['SearchHistoryDays'] = 30;
			$SETS['HitsHistoryDays'] = 400;
			$SETS['DefaultSearchMode'] = "English Query";
			$SETS['Custom1Label'] = "Custom1";
			$SETS['Custom2Label'] = "Custom2";
			$ID = $AppDB->insert_record($Table,$SETS);
			$F = $AppDB->get_record_assoc($ID,$Table);
		}
		RecordToGlobals($F);
	}
	
	if ($DisplayNewCount == "") $DisplayNewCount = 10;
	if ($DisplayViewedCount == "") $DisplayViewedCount = 10; 
	if ($MaxUploadSize == "") $MaxUploadSize = 2;
	

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Settings</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<script LANGUAGE="JavaScript" SRC="lib/AnchorPosition.js"></script>
<script LANGUAGE="JavaScript" SRC="lib/PopupWindow.js"></script>
<script LANGUAGE="JavaScript" SRC="lib/CalendarPopup.js"></script>
<script language="JavaScript">
function ParseForm(f)
{
	if (!CheckEmail(f.NotifyEmail)) return false;
	
	return true;
}
</script>
<? include("header.php"); ?>
<center>
<form onSubmit="return ParseForm(this);" name=form action="<? echo $_SERVER['PHP_SELF'] ?>" method="post">
<? hidden("ID",$ID); 
?>
<table width="100%" border=0 cellspacing=0 cellpadding=0><tr>
<td width="25%" class="subhdr">
<img src="images/settings.gif" width="42" height="48"><span>Settings</span></td>
<td align="left" width="75%">&nbsp;<? ShowMsgBox($msg); ?></td>
</tr></table>
<br>
<?
 	$Tabs = array("General","Options");
 	$ActiveTab = ShowTabs3($Tabs,"General",$ClassPrefix="article-",0,"700px");
	$Tabn = 0; 
	TabSectionStart($Tabs[$Tabn++],$ActiveTab);
?>
    <table width="700" cellpadding="0" cellspacing="0" class="tabtable">
        <tr>
          <td class="form-hdr">&nbsp;</td>
          <td class="form-data">&nbsp;</td>
        </tr>
        <tr><td width="50%" class="form-hdr">Application Name</td>
        <td width="50%" class="form-data"><? DBField("$Table","AppName",$AppName); ?></td>
		</tr>
        <tr>
          <td class="form-hdr">Application Version</td>
          <td class="form-data"><? DBField("$Table","AppVersion",$AppVersion,1); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">DB Version </td>
          <td class="form-data"><? DBField("$Table","DBVersion",$DBVersion,1); ?></td>
        </tr>
        <tr><td class="form-hdr">Authentication Mode</td>
		<td class="form-data"><? DBField("$Table","AuthenticationMode",$AuthenticationMode);?></td>	
        </tr>

        <tr><td class="form-hdr">Permissions Type</td>
		<td class="form-data"><? DBField("$Table","PrivMode",$PrivMode) ;?></td></tr>
        <tr>
          <td nowrap class="form-hdr">Background Full Text Population </td>
          <td class="form-data"><? DBField("$Table","FullTextBackground",$FullTextBackground); ?>
            <input onClick="return(confirm('This will remove and recreate the Full Text index. This may take some time to complete. Are you sure?'))" type="submit" name="InitCatalog" value="Repopulate Now"></td>
        </tr>
        <tr>
          <td class="form-hdr">Search History Days (retention)</td>
          <td class="form-data"><? DBField("$Table","SearchHistoryDays",$SearchHistoryDays); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Article Hits History (retention)</td>
          <td class="form-data"><? DBField("$Table","HitsHistoryDays",$HitsHistoryDays); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Number of Article Versions</td>
          <td class="form-data"><? DBField("$Table","ArticleVersions",$ArticleVersions); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Max Upload Size (in MegaBytes) </td>
          <td class="form-data"><? DBField("$Table","MaxUploadSize",$MaxUploadSize); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Notify on Error </td>
          <td class="form-data"><? DBField("$Table","NotifyEmail",$NotifyEmail); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">SMTP Email Server</td>
          <td class="form-data"><? DBField("$Table","SMTPServer",$SMTPServer); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Article Maintenance Mode<br> 
            (last modifed date lock) </td>
          <td class="form-data"><? DBField("$Table","LastModifyLock",$LastModifyLock); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">&nbsp;</td>
          <td class="form-data">&nbsp;</td>
        </tr>
    </table>
    <? TabSectionEnd(); 
		TabSectionStart($Tabs[$Tabn++],$ActiveTab);	  
	  ?>
    <table width="700" cellpadding="0"  cellspacing="0" class="tabtable">
        <tr>
          <td class="form-hdr">&nbsp;</td>
          <td class="form-data">&nbsp;</td>
        </tr>
        <tr>
          <td class="form-hdr">Display Advanced Filters on Home Page </td>
          <td class="form-data"><? DBField("$Table","FiltersOnHomePage",$FiltersOnHomePage); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Number of new articles to display</td>
          <td class="form-data"><? DBField("$Table","DisplayNewCount",$DisplayNewCount); ?> <span class="form-hdr">sort by <? DBField("$Table","DisplayNewSort",$DisplayNewSort); ?>
          </span></td>
        </tr>
        <tr>
          <td nowrap abbr=""class="form-hdr">Number of most viewed articles to display </td>
          <td class="form-data"><? DBField("$Table","DisplayViewedCount",$DisplayViewedCount); ?>
            <span class="form-hdr">sort by 
            <? DBField("$Table","DisplayViewedSort",$DisplayViewedSort); ?>
</span></td>
        </tr>
        <tr>
          <td class="form-hdr">Default Search Mode</td>
          <td class="form-data"><? DBField("$Table","DefaultSearchMode",$DefaultSearchMode);?></td>
        </tr>
        <tr>
          <td class="form-hdr">Default Review Period (months) </td>
          <td class="form-data"><? DBField("$Table","DefReviewPeriod",$DefReviewPeriod);?></td>
        </tr>
        <tr>
          <td class="form-hdr">Add Lock icon to non Public Articles</td>
          <td class="form-data"><? DBField("$Table","IndicatePrivateArticle",$IndicatePrivateArticle); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Review Type </td>
          <td class="form-data"><? DBField("$Table","ReviewMode",$ReviewMode); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Allow 'W'rite users to modify active articles </td>
          <td class="form-data"><? DBField("$Table","AllowModifyArticles",$AllowModifyArticles); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Allow 'R'ead users to create bulletins </td>
          <td class="form-data"><? DBField("$Table","AllowCreateBulletins",$AllowCreateBulletins); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Allow 'W'rite users to create bulletins</td>
          <td class="form-data"><? DBField("$Table","AllowCreateBulletinsW",$AllowCreateBulletinsW); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Don't Log Hits/Searches for Admins</td>
          <td class="form-data"><? DBField("$Table","DontLogAdmin",$DontLogAdmin); ?></td>
        </tr>
        <tr>
          <td colspan="2" class="form-data"><em>Custom Article fields are are enabled only if you provide a Label: </em></td>
          </tr>
        <tr>
          <td class="form-hdr">Custom Field1 (droplist) Label </td>
          <td class="form-data"><? DBField("$Table","Custom1Label",$Custom1Label); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Custom Field2 (date) Label </td>
          <td class="form-data"><? DBField("$Table","Custom2Label",$Custom2Label); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">&nbsp;</td>
          <td class="form-data">&nbsp;</td>
        </tr>
    </table>
    <? TabSectionEnd(); ?>
		
     <table width="600px" cellpadding="0" cellspacing="0">
		<tr>
          <td align="right" class="form-hdr">
		    <input type="submit" name="Save" value="Save"> 
		    <input onClick="window.location='admin.php'" name="Back" type="button" id="Back" value="Back">          </td>
        </tr>
     </table>	   
</form>
</center>
</body>

</html>