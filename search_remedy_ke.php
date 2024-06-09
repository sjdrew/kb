<?
//
// Search Remedy Known Errors 
//
include("config.php"); 
RequirePriv(PRIV_GROUP,"home.php");
if (!$AppDB->Settings->RemedyARServer) {
	 echo "Remedy Server not defined";
	 exit;
}

function BuildRemedySearchQuery($Search,$QSub,$SMethod,$SType='')
{
	global $AppDB;
	$Search = str_replace("'","",$Search);
	// Remove quotes if not balanced
	$qc = substr_count($Search,'"');
	if ($qc % 2 != 0) {
		$Search = str_replace('"','',$Search);		
	}
	
	$KnownErrorTable = "T" . $AppDB->Settings->KnownErrorTable;
	$WorkLogTable = "T" . $AppDB->Settings->KnownErrorWorkLogTable;

	if ($SType == 'Title') $FTColumn = 'C1000000000';
	else $FTColumn = '*';

	$Words = array();
	if ($SMethod == "CONTAINSTABLE") {
		$sa = search_split_terms($Search);
		$Search_s = "";
		foreach($sa as $phrase) {
			$phrase = trim($phrase);
			$phrase = str_replace("'","",$phrase);
			if (strtolower("$phrase") == "and") continue;
			if ($phrase) {
				if (IsNoiseWord($phrase)) continue;
				$Words[] = " \"$phrase\" ";
				$Search_s .= $Clause . " $phrase ";
				$Clause = "and";
			}
		}
		
		if (stristr($Search," near ") && count($Words) > 2) {
			$WordMethod = 0;
		}
		if (count($Words) == 0) {
			echo "<font color=red><b>&nbsp;You entered an invalid search string</b></font>";			
			return "";
		}
		else if (count($Words) < 20) { // dont join more than 20 tables
			$NewMethod = 1;
		}
		else $Search = "\"$Search_s\"";
	}
	
	// SQL 2000 and above using ContainsTAble method only matches if  each word searched for appears in same fulltext indexed column.
	// Only way around that is multiple ContainsTable(... calls and joins
	// This code creates those multiple statements based on number of words/phrases in query.
	// We only do this on the KnownError form not the workInfo table as the WorkInfo table has only one column we
	// search so we do not need the join per word search on that table.
	if ($NewMethod) {
		$i = 1;
		foreach($Words as $Word) {
			$PBMRankT .= " $plus KT$i" . ".Rank ";
			$plus = "+";
    	  	$QPBM2 .= "\n\t\tinner join $SMethod(".$KnownErrorTable.", $FTColumn,' $Word '," . MAXROWS . ") as KT$i ON PBM.Sys_Known_Error_ID=KT$i.[KEY] ";
			++$i;
			$SPhrase = $and . "'$Word'";
			$and = " AND ";
		} 
	}else {
		$PBMRankT = " KT1.Rank ";
        $QPBM2 .= "\n\t\tinner join $SMethod(" . $KnownErrorTable.", $FTColumn,'$Search'," . MAXROWS . ") as KT1 ON  PBM.Sys_Known_Error_ID=KT1.[KEY] ";
	}
	
	$WLRankT = " KT1.Rank ";
    $QWL2 .= "\n\t\tinner join $SMethod(" . $WorkLogTable.", *,'$Search'," . MAXROWS . ") as KT1 ON  WL.Work_Log_ID=KT1.[KEY] ";
		
	$query = '
				
	select  PBM.Sys_Known_Error_ID as ID,
	 		PBM.Known_Error_ID as Known_Error_ID,
			PBM.Description as Summary,
			PBM.First_Reported_On as Date,
			PBM.Assigned_Group as Group_,
			PBM.Product_Name as Product,
			PBM.Detailed_Decription as Notes,
			RANK as Rank
	from
	(
		select Merged.Known_Error_ID,sum(RANK) as RANK 
			from (
				select PBM.Known_Error_ID,
					'. $PBMRankT .' as RANK
					from PBM_Known_Error as PBM
					'. $QPBM2 .'		
	';
	if (!$TitleOnly) {
	$query .= '						
			union all
				select WL.Known_Error_ID,
					'. $WLRankT .' as RANK
					from PBM_Known_Error_WorkLog as WL	
					'. $QWL2 .'
		';
	}
	$query .= '
			) 
			as Merged group by Known_Error_ID

	) as Results

	inner join PBM_Known_Error as PBM on Results.Known_Error_ID = PBM.Known_Error_ID
	
	';	
	
	return $query;
}



 	$ID = "";
 	if (strtoupper(substr($Search,0,2)) == "KB") {
 		$ID = substr($Search,2);
		if ($ID > 0 && $ID < 1000000 && strlen($ID) < 7) {
			header("location:article.php?ID=$ID");
			exit;
		}
	}
	
	if ($SType == "") {
		if ($CUser->u->SearchMode == "Strict") $SType = "Strict";
	}
	if ($What == "Remedy HelpDesk") {
		header("location:search_remedy.php?S=Search&Search=$Search&SType=$SType&Advanced=$Advanced");	
	}
	if ($What == "KB") {
		header("location:search.php?ns=1&Search=$Search&SType=$SType&Advanced=$Advanced");
	}
	if ($What == "Office Products") {
		header("location:search.php?ns=1&Search=$Search&SType=$SType&What=Office Products");
	}
	if ($What == "") $What = "Remedy Known Error";
	$_GET['What'] = $What;
	
	$RemDB = OpenRemedyDB();
	if (!$RemDB) {
		echo "Remedy currently unavailable.";
		exit;
	}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Search Remedy</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css">
</link>
</head>
<body onLoad="onPageLoad()">
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<script language="javascript">
function onPageLoad()
{
	<? if (!$_GET['ChildList']) { ?> if (FindElement('Search')) FindElement('Search').focus(); <? } ?>
}
function onchangewhat()
{
	if (searchform.What.value == "Remedy Known Error") {
		searchform.action="search_remedy_ke.php";
	} else if (searchform.What.value == "Remedy HelpDesk") {
		searchform.action="search_remedy.php";
	} else if (searchform.What.value == "Office Products") {
		searchform.action="search_office.php";
	} else if (searchform.What.value == "Microsoft KB") {
		searchform.action="search_mskb.php";
	} else {
		searchform.action="search.php";	
	}
}
</script>

<? include("header.php"); ?>
<table width="98%" border="0" align="center" cellpadding="4">
  <tr>
    <td colspan="2">
  <tr valign="middle">
    <td width="23%" align="left" valign="top" nowrap><p class="subhdr">Search Remedy Known Errors:</p>
    <span style="font-size:9px">(Search most fields including Work Info)</span></td>
    <td width="77%"><p>
      <form name="searchform" method="get" action="<? echo $PHP_SELF ?>">
	  	<? hidden("Advanced",$Advanced); ?>
        <input name="Search" type="text" size="70" maxlength="200" value="<? echo htmlspecialchars(GetVar("Search"))?>" >
        <input name="S" type="submit" value="Search"> 	
			<?	DisplaySearchModes();  ?>
            <br>
            <span class="small">Search by: 
			<input name="SType" <? if ($SType == "" || $SType == "English") echo checked ?> type="radio" value="English">
			English query -or-
            <input name="SType" <? if ($SType == "Strict") echo checked ?> type="radio" value="Strict">
			Strict, match all words and quoted phrases.
            <input name="SType" <? if ($SType == "Title") echo checked ?> type="radio" value="Title"> 
            -or- Title only.          
            </span>
			<? if ($Advanced) { ?>
			<br>
			 <fieldset style="padding-top:8px; width:300px">
			    <legend> Advanced Filters: </legend>
			<table style="margin:10px">
			 <tr>
				<td align="right" class="form-hdr">Group:</td>
			      <td class="form-hdr"><? 
				  	dropdownlistfromquery("Group",$RemDB,
					"select distinct Support_Group_Name as Name from CTM_Support_Group where Status = 1 order by Support_Group_Name ",
					$Group,"-All-",'style="width:130px"',"Name","Name"); 
					?>
				  </td>
                  <td align="right" class="form-hdr">Product:</td>
                  <td class="form-data"><? DBField("Articles","Product_S",$Product); ?>
                </td>	
			</tr>	
			</table>
			</fieldset>
			<? } else { ?>
				<a class="small" href="search_remedy_ke.php?What=<? echo $What ?>&SType=<? echo $SType ?>&Advanced=1" title="Advanced Search filters">Advanced</a>
			<? } ?>
      </form>
    </td>
  </tr>
  <tr >
    <td valign="top" colspan="2"><hr>
    <?
	global $Previews;
	$Previews = ($CUser->u->Previews != "No") ? 1 : 0;
	if (isset($_GET['Previews'])) $Previews = $_GET['Previews'];

function fmt_Preview($Text)
{
	return substr(htmltotext($Text),0,300) . "...";
}

function fmt_Summary($Title,$ID,$R) 
{
	global $AppDB;
	global $Previews;
	$t = "";
		$Known_Error_ID = $R['Known_Error_ID'];
		
		if ($Previews) {
			$t =  "<p class=RPreview><a title=\"click to view in Remedy\" href=\"OpenTicket.php?Form=PBM:Known Error&Server=" . 
				$AppDB->Settings->RemedyARServer  . "&ID=$ID\"><img align=\"absmiddle\" src=\"images/aruser_sm.gif\" border=\"0\"></a> ";
				if (defined("REMEDY_SHOW_CASE_URL")) {
					$t .= '<a target=_blank title="click to view details" href="'.REMEDY_SHOW_CASE_URL.'&Server=' . $AppDB->Settings->RemedyARServer . "&ID=$Known_Error_ID\">" . $Title . "</a></p>\n";	
				} else
					$t .= $Title . "</p>";	
				$t .= "<p class=RPreview>" . fmt_Preview($R[Notes]) . "</p>"; //<hr>";
		}
		else {
			$t =  "<a title=\"click to view in Remedy\" href=\"OpenTicket.php?Form=PBM:Known Error&Server=" . 
				$AppDB->Settings->RemedyARServer  . "&ID=$ID\"><img align=\"absmiddle\" src=\"images/aruser_sm.gif\" border=\"0\"></a> ";		
				if (defined("REMEDY_SHOW_CASE_URL")) {
					$t .= '<a target=_blank title="click to view details" href="'.REMEDY_SHOW_CASE_URL.'&Server=' . $AppDB->Settings->RemedyARServer . "&ID=$Known_Error_ID\">" . $Title . "</a></p>\n";	
				} else
					$t .= $Title;	
		}
	return $t;
}

function fmt_date($d)
{
	return(DateTimeStrFromValue($d,0));
}

function fmt_product($p)
{
	if ($p == "") $p = "&nbsp;";
	return $p;
}
	
	$Product = trim($Product);
	$Type = trim($Type);
	$Group = trim($Group);
	if ($Group == "(All Groups)") $Group = "";
		
	if (!$ns && ($Search || $Group || $Type || $Product)) { 
		echo "<div align=\"left\" style=\"float:left\"><b>Search Results:</b></div>";
		$DBFields["Summary"] = "@fmt_Summary";
		$DBFields["Date"] = "@fmt_date:nowrap";
		$DBFields["Product"] = "@fmt_product";
		$DBFields["Group_:Group"] = " ";
		$DBFields["Known_Error_ID:Error ID"] = " ";
		if ($Search) $DBFields["Rank"] = ":align=right";			
			
		$Sort = GetVar("Sort");

		$q = "  ";
	
		if ($Search) {
			$topn = "";
		
			if ($SType == "Strict") {			
				$SMethod = "CONTAINSTABLE";
				$sa = search_split_terms($Search);
				$Search_s = "";
				foreach($sa as $phrase) {
					$phrase = trim($phrase);
					$phrase = str_replace("'","",$phrase);
					if (strtolower("$phrase") == "and") continue;
					if ($phrase) {
						if (IsNoiseWord($phrase)) continue;
						$Search_s .= $Clause . " '$phrase' ";
						$Clause = "and";
					}
				}
				$Search_s = "'$Search_s'";
			}
			else {
				$SMethod = "FREETEXTTABLE";
				$Search_s = $AppDB->qstr($Search);
			}
		
			if ($Search_s) {		
				$query = BuildRemedySearchQuery($Search_s,'',$SMethod,$SType);		
				if ($Sort == "")
					$Sort="Rank desc";
			} 
		}
		else {
			$q = "select 
			PBM.Known_Error_ID as ID,
	 		PBM.Known_Error_ID as Known_Error_ID,
			PBM.Description as Summary,
			PBM.Reported_Date as Date,
			PBM.Assigned_Group as Group_,
			PBM.Product_Name as Product,
			PBM.Detailed_Decription as Notes
			 from PBM_Help_Desk as PBM where 1=1 ";
		}
		if (trim($Group)) {
			$q .= " and Assigned_Group = '$Group'";
		}
		if (trim($Product)) {
			$Product = trim($Product);
			$q .= " and Product_Name like '%$Product%'";
		}		
		$query .= " $q ";
		if ($ShowQuery) echo "<pre>$query</pre>";
		
		$LB = new ListBoxPref("",$RemDB,$query,$DBFields,$Sort,"","",1,'95%');
		$LB->NoFrame = 1;
		$LB->Form = 1;
		$LB->CellStyle = ($Previews) ? "list-kb" : "list-sm";
		$LB->Style = "border=0 cellpadding=3 cellspacing=0";
		$LB->Display();
		
		// Save the query (except for Admins)
		if ($Search && $_GET["Page"] == "" && (!$CUser->IsPriv(PRIV_ADMIN) || !$AppDB->Settings->DontLogAdmin) ) {
			$Search = str_replace('\\\\',"",$Search);
			$Search = $AppDB->qstr($Search);
			// If this search is the same as one of this users searches in the past hour then don't bother saving it
			// This prevents useless records that may occur when hitting refresh on the browser or going back.
			$SType = "R-" . $SType;   // Prefix with R- for Remedy Searches
			$Dup = $AppDB->GetRecordFromQuery("select Top 1 ID from Searches where CREATEDBY = '" . $CUser->UserID . "' AND " .
						" DATEDIFF(hour,CREATED," . "GetDate()" . ") < 2 " . 
						" AND Search = $Search AND SearchType = '$SType' ");
			if (!$Dup) {
				$Fields["Search"] = $Search; 
				$Fields["SearchType"] = $SType;
				$Fields["Matches"] = $LB->TotalRows;
				$AppDB->insert_record("Searches",$Fields);
				// Randomly clean up the Search History table.
				if (rand(0,10) == 5) {
					$NDays = $AppDB->Settings->SearchHistoryDays;
					if ($NDays == "") $NDays = 30;
					$AppDB->sql("delete from Searches where DATEDIFF(day,CREATED," . "GetDate()" . ") >= $NDays","",0);
 				}
			} 
		}
	}
	?>
        <br>
      </p>
    </td>
  </tr>
</table>
</body>
</html>
