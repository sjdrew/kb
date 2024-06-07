<? 	include("config.php"); 
   	RequirePriv(PRIV_ADMIN);
	$ID = GetVar("ID");
	$Table = "Settings";

function ProcessSave($ID,$rdonly,&$msg,&$Err,$Multi=0)
{
	global $AppDB;
	global $Table;
		
	if (ParseFields($Table,$msg) != 0)
		return $ID;
	
		
	if ($ID) {
		// checkboxes do not post if not set
		if ($_POST[FullTextBackground] == "") $_POST[FullTextBackground] = 0;
		if ($_POST[RemedyFullTextBackground] == "") $_POST[RemedyFullTextBackground] = 0;
		if ($_POST[LastModifyLock] == "") $_POST[LastModifyLock] = 0;
		if ($_POST[DontLogAdmin] == "") $_POST[DontLogAdmin] = 0;
		if ($_POST[FiltersOnHomePage] == "") $_POST[FiltersOnHomePage] = 0;
		if ($_POST[AllowCreateBulletins] == "") $_POST[AllowCreateBulletins] = 0;
		if ($_POST[AllowCreateBulletinsW] == "") $_POST[AllowCreateBulletinsW] = 0;
		if ($_POST[AllowModifyArticles] == "") $_POST[AllowModifyArticles] = 0;
		if ($_POST[IndicatePrivateArticle] == "") $_POST[IndicatePrivateArticle] = 0;

		if ($_POST['InitCatalog']) {
			InitFullTextCatalog(&$msg);
		}

		$AppDB->Settings->RemedyARServer = $_POST['RemedyARServer'];
		$AppDB->Settings->RemedyDBServer = $_POST['RemedyDBServer'];
		
		if (trim($AppDB->Settings->RemedyDBServer) == "")  {
			$_POST['RemedyDBServer'] = $AppDB->Settings->RemedyDBServer = $AppDB->Settings->RemedyARServer;
		}
		
		if ($AppDB->Settings->RemedyDBServer) {
			$RemDB = OpenRemedyDB();
			
			if ($RemDB) { 
			$Schema = 'HPD:Help Desk';
			if (REMEDY_VERSION == 6) $Schema = "HPD:HelpDesk";
			
			$SRec = $RemDB->GetRecordFromQuery("select * from arschema where name = '$Schema'");
			if (!$SRec) {
				$msg = "Cannot locate Remedy 'HPD:Help Desk' schema. Check Remedy settings in config.php.";
			} else {
				 $_POST['HelpDeskTable'] = $AppDB->Settings->HelpDeskTable = $SRec->schemaId;
			}
			
			if (REMEDY_VERSION != 6) {
				$WRec = $RemDB->GetRecordFromQuery("select * from arschema where name = 'HPD:WorkLog'");
				if (!$WRec) {
					$msg = "Cannot locate Remedy 'HPD:WorkLog' schema. Check Remedy settings in config.php.";
				} else {
					 $_POST['HDWorkLogTable'] = $AppDB->Settings->HDWorkLogTable = $WRec->schemaId;
				}
				

				$WRec = $RemDB->GetRecordFromQuery("select * from arschema where name = 'PBM:Known Error WorkLog'");
				if (!$WRec) {
					$msg = "Cannot locate Remedy 'PBM:Known Error Worklog' schema. Check Remedy settings in config.php.";
				} else {
					 $_POST['KnownErrorWorkLogTable'] = $AppDB->Settings->KnownErrorWorkLogTable = $WRec->schemaId;
				}


				$WRec = $RemDB->GetRecordFromQuery("select * from arschema where name = 'PBM:Known Error'");
				if (!$WRec) {
					$msg = "Cannot locate Remedy 'PBM:Known Error' schema. Check Remedy settings in config.php.";
				} else {
					 $_POST['KnownErrorTable'] = $AppDB->Settings->KnownErrorTable = $WRec->schemaId;
				}



			}
			} else {
				$msg .= "Unable to Connect to Remedy Server";
			}
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
		if ($_POST['InitRemedyCatalog']) {
			if ($RemDB) InitRemedyFullTextCatalog(&$msg);
		}
		if ($msg == "") $msg = "Changes were saved.";
	}
	else {
		$ID = $AppDB->save_form($Table);
	}
	return $ID;
}


function InitRemedyFullTextCatalog(&$msg)
{
	global $AppDB;
	
	$RemDB = OpenRemedyDB();
	$DBNAME = "ARSystem";
		
	if ($AppDB->Settings->HelpDeskTable == "") {
		$msg .= "Cannot enable Full Text Catalog on Remedy Help Desk";
		return;
	}
	
	$HelpDeskTable = $AppDB->Settings->HelpDeskTable;
	
	$FullTextFields = array("Description","Detailed Decription",
			"Assigned Group","Resolution",
			"Last Name","Site","Department",
			"Categorization Tier 1",
			"Categorization Tier 2",
			"Categorization Tier 3",
			"Product Name","Product Model/Version","Incident Number");
	
	$Columns = array();
	
	foreach($FullTextFields as $FieldName) {		
		$CRec = $RemDB->GetRecordFromQuery("select fieldId,fieldName from field 
				where schemaId = $HelpDeskTable and fieldName = '$FieldName'");

		if (!$CRec) {
			$msg .= "Warning: $FieldName not found on form HPD:Help Desk";
			continue;
		}
		$Columns[] = "C" . $CRec->fieldId;
	}
	
	if (count($Columns) == 0) {
		$msg .= "Unable to locate specified Help Desk Table columns for Full Text indexing.";
		return;
	}
	
	$WorkLogTable = "T" . $AppDB->Settings->HDWorkLogTable;
	$HelpDeskTable = "T" . $HelpDeskTable;

	$KnownErrorTable = "T" . $AppDB->Settings->KnownErrorTable;
	$KnownErrorWorkLogTable = "T" . $AppDB->Settings->KnownErrorWorkLogTable;
	
	/* 
	 * Enable Full Text Catalogs, first remove if already there.
	 */
	$RemDB->sql("if (select DATABASEPROPERTY('$DBNAME', N'IsFullTextEnabled')) <> 1 exec sp_fulltext_database N'enable' ");	
			
	/* drop current full text on tables */		
	$RemDB->sql("if OBJECTPROPERTY(object_id('$HelpDeskTable'),'TableHasActiveFulltextIndex') = 1 exec sp_fulltext_table  N'[dbo].[$HelpDeskTable]', N'drop'");

	$RemDB->sql("if OBJECTPROPERTY(object_id('$WorkLogTable'),'TableHasActiveFulltextIndex') = 1 exec sp_fulltext_table  N'[dbo].[$WorkLogTable]', N'drop'");

	$RemDB->sql("if OBJECTPROPERTY(object_id('$KnownErrorTable'),'TableHasActiveFulltextIndex') = 1 exec sp_fulltext_table  N'[dbo].[$KnownErrorTable]', N'drop'");

	$RemDB->sql("if OBJECTPROPERTY(object_id('$KnownErrorWorkLogTable'),'TableHasActiveFulltextIndex') = 1 exec sp_fulltext_table  N'[dbo].[$KnownErrorWorkLogTable]', N'drop'");

	
	/* drop catalog */		
	//$RemDB->sql("if exists (select * from dbo.sysfulltextcatalogs where name = '$DBNAME') exec sp_fulltext_catalog '" . $DBNAME . "', N'drop'");
			
	/* create new catalog */
	$RemDB->sql("if not exists (select * from dbo.sysfulltextcatalogs where name = '$DBNAME') exec sp_fulltext_catalog '" . $DBNAME . "', N'create'");
	
	
	/*
	 * must determin key name for Column C1
	 */
	$PK = $RemDB->GetRecordFromQuery("select k.constraint_name as PKName from information_schema.key_column_usage k,
		information_schema.table_constraints tc 
		where tc.constraint_name = k.constraint_name 
		and tc.constraint_type = 'PRIMARY KEY' and k.table_name = '$HelpDeskTable'
		and column_name = 'C1'");
	$PKNAME = $PK->PKName;
	if ($PKNAME == "") {
		$msg .= "Unable to determine Primary Key name for $HelpDeskTable C1";
		return;
	}
	/* create new fulltext on table */
	$RemDB->sql($a = "exec sp_fulltext_table N'[dbo].[$HelpDeskTable]', N'create', '$DBNAME', N'$PKNAME'");

	/* choose columns to fulltext on */
	
	foreach ($Columns as $Column) {
		$RemDB->sql("exec sp_fulltext_column N'[dbo].[$HelpDeskTable]', N'$Column', N'add', 1033");
	}
		
	/* Activate it */
	$RemDB->sql("exec sp_fulltext_table N'[dbo].[$HelpDeskTable]', N'activate'");


	/**
	 * Similar steps for WorkLogTable (REMEDY 7 and Above)
	 */
	$WorkLogTable = $AppDB->Settings->HDWorkLogTable;
	
	if ($WorkLogTable) {
		
		$FullTextFields = array("Description","Detailed Description");
		$Columns = array();
	
		foreach($FullTextFields as $FieldName) {		
			$CRec = $RemDB->GetRecordFromQuery("select fieldId,fieldName from field 
					where schemaId = $WorkLogTable and fieldName = '$FieldName'");
			if (!$CRec) {
				$msg .= "Warning: $FieldName not found on form HPD:WorkLog";
				continue;
			}
			$Columns[] = "C" . $CRec->fieldId;
		}
			
		if (count($Columns) == 0) {
			$msg .= "Unable to locate specified HPD:WorkLog columns for Full Text indexing.";
			return;
		}

		$WorkLogTable = "T" . $WorkLogTable;
		
		/* drop current full text on table */		
		$RemDB->sql("if OBJECTPROPERTY(object_id('$WorkLogTable'),'TableHasActiveFulltextIndex') = 1 exec sp_fulltext_table  N'[dbo].[$WorkLogTable]', N'drop'");
	
		/*
		 * must determin key name for Column C1
		 */
		$PK = $RemDB->GetRecordFromQuery("select k.constraint_name as PKName from information_schema.key_column_usage k,
			information_schema.table_constraints tc 
			where tc.constraint_name = k.constraint_name 
			and tc.constraint_type = 'PRIMARY KEY' and k.table_name = '$WorkLogTable'
			and column_name = 'C1'");
		$PKNAME = $PK->PKName;
		if ($PKNAME == "") {
			$msg .= "Unable to determine Primary Key name for $WorkLogTable C1";
		}
		/* create new fulltext on table */
		$RemDB->sql($a = "exec sp_fulltext_table N'[dbo].[$WorkLogTable]', N'create', '$DBNAME', N'$PKNAME'");

		/* choose columns to fulltext on */	
		foreach ($Columns as $Column) {
			$RemDB->sql("exec sp_fulltext_column N'[dbo].[$WorkLogTable]', N'$Column', N'add', 1033");
		}
		
		/* Activate it */
		$RemDB->sql("exec sp_fulltext_table N'[dbo].[$WorkLogTable]', N'activate'");	
	}


	/**
	 * Similar steps for KnownError Table (REMEDY 7 and Above)
	 */
	$Table = $AppDB->Settings->KnownErrorTable;
	
	if ($Table) {
		
		$FullTextFields = array("Description","Detailed Decription",
			"Assigned Group","Resolution","Temporary Workaround",
			"Product Name","Product Model/Version","Known Error ID","Generic Categorization Tier 1");
		$Columns = array();
	
		foreach($FullTextFields as $FieldName) {		
			$CRec = $RemDB->GetRecordFromQuery("select fieldId,fieldName from field 
					where schemaId = $Table and fieldName = '$FieldName'");
			if (!$CRec) {
				$msg .= "Warning: $FieldName not found on form PRB:Known Error";
				continue;
			}
			$Columns[] = "C" . $CRec->fieldId;
		}
			
		if (count($Columns) == 0) {
			$msg .= "Unable to locate specified PBM:Known Error columns for Full Text indexing.";
			return;
		}

		$Table = "T" . $Table;
		
		/* drop current full text on table */		
		$RemDB->sql("if OBJECTPROPERTY(object_id('$Table'),'TableHasActiveFulltextIndex') = 1 exec sp_fulltext_table  N'[dbo].[$Table]', N'drop'");
	
		/*
		 * must determin key name for Column C1
		 */
		$PK = $RemDB->GetRecordFromQuery("select k.constraint_name as PKName from information_schema.key_column_usage k,
			information_schema.table_constraints tc 
			where tc.constraint_name = k.constraint_name 
			and tc.constraint_type = 'PRIMARY KEY' and k.table_name = '$Table'
			and column_name = 'C1'");
		$PKNAME = $PK->PKName;
		if ($PKNAME == "") {
			$msg .= "Unable to determine Primary Key name for $Table C1";
		}
		/* create new fulltext on table */
		$RemDB->sql($a = "exec sp_fulltext_table N'[dbo].[$Table]', N'create', '$DBNAME', N'$PKNAME'");

		/* choose columns to fulltext on */	
		foreach ($Columns as $Column) {
			$RemDB->sql("exec sp_fulltext_column N'[dbo].[$Table]', N'$Column', N'add', 1033");
		}
		
		/* Activate it */
		$RemDB->sql("exec sp_fulltext_table N'[dbo].[$Table]', N'activate'");	
	}

	/**
	 * Similar steps for KnownError Worklog Table (REMEDY 7 and Above)
	 */
	$Table = $AppDB->Settings->KnownErrorWorkLogTable;
	
	if ($Table) {
		
		$FullTextFields = array("Description","Detailed Description");
		$Columns = array();
	
		foreach($FullTextFields as $FieldName) {		
			$CRec = $RemDB->GetRecordFromQuery("select fieldId,fieldName from field 
					where schemaId = $Table and fieldName = '$FieldName'");
			if (!$CRec) {
				$msg .= "Warning: $FieldName not found on form PRB:Known Error WorkLog";
				continue;
			}
			$Columns[] = "C" . $CRec->fieldId;
		}
			
		if (count($Columns) == 0) {
			$msg .= "Unable to locate specified PBM:Known Error WorkLog columns for Full Text indexing.";
			return;
		}

		$Table = "T" . $Table;
		
		/* drop current full text on table */		
		$RemDB->sql("if OBJECTPROPERTY(object_id('$Table'),'TableHasActiveFulltextIndex') = 1 exec sp_fulltext_table  N'[dbo].[$Table]', N'drop'");
	
		/*
		 * must determin key name for Column C1
		 */
		$PK = $RemDB->GetRecordFromQuery("select k.constraint_name as PKName from information_schema.key_column_usage k,
			information_schema.table_constraints tc 
			where tc.constraint_name = k.constraint_name 
			and tc.constraint_type = 'PRIMARY KEY' and k.table_name = '$Table'
			and column_name = 'C1'");
		$PKNAME = $PK->PKName;
		if ($PKNAME == "") {
			$msg .= "Unable to determine Primary Key name for $Table C1";
		}
		/* create new fulltext on table */
		$RemDB->sql($a = "exec sp_fulltext_table N'[dbo].[$Table]', N'create', '$DBNAME', N'$PKNAME'");

		/* choose columns to fulltext on */	
		foreach ($Columns as $Column) {
			$RemDB->sql("exec sp_fulltext_column N'[dbo].[$Table]', N'$Column', N'add', 1033");
		}
		
		/* Activate it */
		$RemDB->sql("exec sp_fulltext_table N'[dbo].[$Table]', N'activate'");	
	}


		
	if ($_POST['RemedyFullTextBackground']) {
		$RemDB->sql("Exec sp_fulltext_table '$HelpDeskTable', 'start_change_tracking'");
		$RemDB->sql("Exec sp_fulltext_table '$HelpDeskTable', 'start_background_updateindex'");
		if ($WorkLogTable) {
			$RemDB->sql("Exec sp_fulltext_table '$WorkLogTable', 'start_change_tracking'");
			$RemDB->sql("Exec sp_fulltext_table '$WorkLogTable', 'start_background_updateindex'");		
			$RemDB->sql("Exec sp_fulltext_table '$KnownErrorTable', 'start_background_updateindex'");		
			$RemDB->sql("Exec sp_fulltext_table '$KnownErrorWorkLogTable', 'start_background_updateindex'");		
		}		
		$msg .= "Remedy Full Text Catalog Background Population enabled";
	}
	else {
		$msg = "Warning Remedy Background Full Text Population is not enabled.";
		$RemDB->sql("Exec sp_fulltext_table '$HelpDeskTable', 'stop_change_tracking'");
		$RemDB->sql("EXEC sp_fulltext_table '$HelpDeskTable', 'stop_background_updateindex'");
		$RemDB->sql("EXEC sp_fulltext_table '$HelpDeskTable', 'start_full'");
		$msg .= "Remedy Full Text Catalog Re-Population started";
	}
}


function InitFullTextCatalog(&$msg)
{
	global $AppDB;
	/* 
	 * Enable Full Text Catalogs, first remove if already there.
	 */
	$AppDB->sql("exec sp_tableoption N'Articles', 'text in row', 'ON'");
	$AppDB->sql("exec sp_tableoption N'ArticleAttachments', 'text in row', 'ON'");
	$AppDB->sql("if (select DATABASEPROPERTY('" . DBNAME . "', N'IsFullTextEnabled')) <> 1 exec sp_fulltext_database N'enable' ");	
			
	/* drop current */		
	$AppDB->sql("if OBJECTPROPERTY(object_id('Articles'),'TableHasActiveFulltextIndex') = 1 exec sp_fulltext_table  N'[dbo].[Articles]', N'drop'");
	$AppDB->sql("if OBJECTPROPERTY(object_id('ArticleAttachments'),'TableHasActiveFulltextIndex') = 1 exec sp_fulltext_table  N'[dbo].[ArticleAttachments]', N'drop'");


//failed: exec sp_fulltext_column N'[dbo].[T1000]', N'C1000000984', N'add', 1033

	
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
	
	if ($_POST[InitCatalog] || $_POST['InitRemedyCatalog']) $_POST["Save"] = 1;
	
	if ($_POST["Save"]) {
		$ID = ProcessSave($ID,$rdonly,&$msg,&$Err);		
	}
					
	if ($ID) {
		$F = $AppDB->get_record_assoc($ID,$Table);
		if (!$F) {
			$SETS[ID] = 1;
			$SETS[AuthenticationMode] = "NTLM";
			$SETS[PrivMode] = "Simple";
			$SETS[SMTPServer] = "mail";
			$SETS[AppName] = "Knowledge Base";
			$SETS[SearchHistoryDays] = 30;
			$SETS[HitsHistoryDays] = 400;
			$SETS[DefaultSearchMode] = "English Query";
			$SETS[Custom1Label] = "Custom1";
			$SETS[Custom2Label] = "Custom2";
			$ID = $AppDB->insert_record($Table,$SETS);
			$F = $AppDB->get_record_assoc($ID,$Table);
		}
		RecordToGlobals($F);
	}
	
	if ($_POST) {
		// keep reposted values, but strip slashes
		repost_stripslashes();
		if ($CopyToNew) {
			$ID = $LASTMODIFIEDBY = $LASTMODIFIED = $CREATED = $CREATEDBY = "";
		}
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
<form onSubmit="return ParseForm(this);" name=form action="<? echo $PHP_SELF ?>" method="post">
<? hidden("ID",$ID); 
?>
<table width="100%" border=0 cellspacing=0 cellpadding=0><tr>
<td width="25%" class="subhdr">
<img src="images/settings.gif" width="42" height="48"><span>Settings</span></td>
<td align="left" width="75%">&nbsp;<? ShowMsgBox($msg); ?></td>
</tr></table>
<br>
<?
 	$Tabs = array("General","Options","Remedy Integration");
 	$ActiveTab = ShowTabs3($Tabs,"General",$ClassPrefix="article-",0,"600px");
	$Tabn = 0; 
	TabSectionStart($Tabs[$Tabn++],$ActiveTab);
?>
    <table width="600px" cellpadding="0" cellspacing="0" class="tabtable">
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
    <table width="600px" cellpadding="0"  cellspacing="0" class="tabtable">
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
    <? TabSectionEnd(); 
		TabSectionStart($Tabs[$Tabn++],$ActiveTab);	  
	 ?>
    <table width="600px" cellpadding="0" cellspacing="0" class="tabtable">
        <tr>
          <td class="form-hdr">&nbsp;</td>
          <td class="form-data">&nbsp;</td>
        </tr>
        <tr>
          <td class="form-hdr">Remedy AR Server</td>
          <td class="form-data"><? DBField("$Table","RemedyARServer",$RemedyARServer); ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Remedy Database Server</td>
          <td class="form-data"><? DBField("$Table","RemedyDBServer",$RemedyDBServer); ?></td>
        </tr>			
        <tr>
          <td class="form-hdr">Help Desk Schema ID</td>
          <td class="form-data"><? echo $AppDB->Settings->HelpDeskTable ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Help Desk WorkLog Schema ID</td>
          <td class="form-data"><? echo $AppDB->Settings->HDWorkLogTable ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Known Error  Schema ID</td>
          <td class="form-data"><? echo $AppDB->Settings->KnownErrorTable ?></td>
        </tr>
        <tr>
          <td class="form-hdr">Known Error  WorkLog Schema ID</td>
          <td class="form-data"><? echo $AppDB->Settings->KnownErrorWorkLogTable ?></td>
        </tr>
        <tr>
          <td nowrap class="form-hdr">Background Full Text Population </td>
          <td class="form-data"><? DBField("$Table","RemedyFullTextBackground",$RemedyFullTextBackground); ?>
              <input onClick="return(confirm('This will remove and recreate the Remedy Full Text index. This may take some time to complete. Are you sure?'))" type="submit" name="InitRemedyCatalog" value="Initialize"></td>
        </tr>
        <tr>
          <td class="form-hdr">&nbsp;</td>
          <td class="form-data">&nbsp;</td>
        </tr>
        <tr>
          <td class="form-hdr">&nbsp;</td>
          <td class="form-data">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2" class="form-hdr"><div align="left"><em>Currently only Remedy 6 or Remedy 7  HelpDesk running on Microsoft SQL Server is support</em>ed.</div></td>
        </tr>
        <tr>
          <td class="form-hdr">&nbsp;</td>
          <td class="form-data">&nbsp;</td>
        </tr>
        <tr>
          <td class="form-hdr">&nbsp;</td>
          <td class="form-data">&nbsp;</td>
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