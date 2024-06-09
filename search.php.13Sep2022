<?

/*
 * Search Notes:
 *
 * The query below is complex and when using strict searching a join is formed for each word.
 * The reason for this is SQL 2000 and 2005 when specifying more than one word to find in an and clause
 * against multiple full text columns, only works if all the words are found agains one column. 
 * This means that we would not be able to search for something like "Error ABC-123" if the word error occred
 * in the Article title and the ABC-123 occured in the content the search would fail.
 *
 * ---
 * May need this if PDF indexing does not work:
 	use master 
	go 
	EXEC sp_fulltext_service 'load_os_resources',1 
	go 
	EXEC sp_fulltext_service 'verify_signature', 0 
	go 
	reconfigure with override 
 *
 */
 
 include("config.php"); 

	if (!$GroupID) {
		$GroupID = $CUser->u->GroupID;
	}

	CheckIfKBNumber($Search); // Redirects if so
		
	if ($SType == "") {
		if ($CUser->u->SearchMode == "") $SType = $AppDB->Settings->DefaultSearchMode;
		else if ($CUser->u->SearchMode == "Strict") $SType = "Strict";
	}
	
	$form_action = "search.php";
	
	if ($What == "Remedy HelpDesk") {
		header("location:search_remedy.php?ns=1&Search=$Search&SType=$SType&Advanced=$Advanced");
	}
	if ($What == "Office Products") {
		$Advanced = 0;
		$form_action = "search_office.php";
	}
	
	if ($What == "") $What = "KB";
	if ($Search == '""') $Search = "";
	if ($Search == "''") $Search = "";
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Search results</title>
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
	if (searchform.What.value == "Remedy HelpDesk") {
		searchform.action="search_remedy.php";
	} else if (searchform.What.value == "Remedy Known Error") {
		searchform.action="search_remedy_ke.php";
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
  <? if (!$_GET['ChildList']) { ?>
  <tr valign="middle">
    <td width="8%" valign="top" align="left"><p class="subhdr">Search:</p></td>
    <td width="92%"><p>
      <form id="searchform" name="searchform" method="get" action="<? echo $form_action ?>">
	  	<? hidden("Advanced",$Advanced); ?>
        <input name="Search" type="text" size="70" maxlength="200" value="<? echo htmlspecialchars(GetVar("Search"))?>" >
        <input name="S" type="submit" value="Search"> <?
			$Priv = $CUser->u->Priv;
			if ($Priv >= PRIV_SUPPORT) {		 
				DisplaySearchModes(); 
			}
			?>
            <br>
			
            <span class="small">Search by: 
			<input name="SType" <? if ($SType == "" || $SType == "English") echo checked ?> type="radio" value="English">
			English query,
            <input name="SType" <? if ($SType == "Strict") echo checked ?> type="radio" value="Strict">
			Strict, match all words and quoted phrases
            <input name="SType" <? if ($SType == "Title") echo checked ?> type="radio" value="Title">
			-or- Title only.</span>
			
			<? if ($Advanced) { ?>
			<br>
			 <fieldset style="padding-top:8px; width:300px">
			    <legend> Advanced Filters: </legend>
			<table style="margin:10px">
			 <tr>
				<td align="right" class="form-hdr">Group:</td>
			      <td class="form-hdr"><? GroupDropList($GroupID,'style="width:130px"'); ?>
				  </td>
                  <td align="right" class="form-hdr">Type:</td>
                  <td class="form-data"><? DBField("Articles","Type_S",$Type); ?>
                  </td>
                  <td align="right" class="form-hdr">Product:</td>
                  <td class="form-data"><? DBField("Articles","Product_S",$Product); PopupFieldValues("Articles","searchform","Product"); ?>
                </td>	
			</tr>	
			</table>
			</fieldset>
			<? } else { ?>
				<a class="small" href="search.php?SType=<? echo $SType ?>&Advanced=1<? echo "&Search=$Search&ns=1" ?>" title="Advanced Search filters">Advanced</a>
			<? } ?>
      </form>
    </td>
  </tr>
  <? } ?>
  <tr >
    <td valign="top" colspan="2"><hr>
   <?
	global $Previews;
	$Previews = ($CUser->u->Previews != "No") ? 1 : 0;
	if (isset($_GET['Previews'])) $Previews = $_GET['Previews']; // cmd line overrides profile.

function fmt_Preview($Text,$ID='')
{
	$force=0;
	// Fix for word imported docs
	// If there is a style block in the first section (within 800 bytes returned by search query)
	// then obtain the full article content and set Text preview text to end of Style block
	if ($ID && stristr($Text,"<style")) {
		global $AppDB;
		$KB = $AppDB->GetRecordFromQuery("select Content from Articles where ID=$ID");
		if ($KB) {
			$P = stristr($KB->Content,"</style");
			if ($P) {
				$Text = substr($P,8,1000);
				$force = 1;
			}
		}
	}
	$text = substr(htmltotext($Text,$force),0,300); 
	if ($text) $text .= "...";
	return $text;
}

function fmt_Title($Title,$ID,$R) 
{
	global $Previews;
	$t = "";
	if ($_GET['ChildList']) $target = " target=_KBWin ";
	$Title = TitleFormat($Title,$R['ViewableBy']);
	
//	if ( $R[MatchType] === 0 || $R["AsContent"]) { // matched on base article or is AsContent
	if ( $R[AttachmentID] == 0 || $R["AsContent"]) { // matched on base article or is AsContent
		if ($Previews) {
			$t =  "<p class=RPreview><a href=\"article.php?ID=$ID\" $target>" . $Title . "</a></p>";
			$preview = fmt_Preview($R[Content],$ID);
			if ($preview) {
				$t .= "<p class=RPreview>" . $preview; // . "</p>"; 
				if ($R["AsContent"] == 0 && trim($R["Filename"])) {
					$t .= ' also see attachment: ' . GetAttachmentIcon($R[Filename]) . " <a title=\"View attachment\" $target href=\"show_attachment.php?ID=" . $R[AttachmentID] . "\">" . $R[Filename] . "</a>";
				}
				echo "</p>";			
			}
		}
		else {
			$t =  "<a href=\"article.php?ID=$ID\" $target>" . $Title . "</a>";		
		}
	} else {
		if ($Previews) {
			$t = '<p class=RTitle>' . GetAttachmentIcon($R[Filename]) . " <a title=\"View attachment\" $target href=\"show_attachment.php?ID=" . $R[AttachmentID] . "\">" . $R[Filename] . "</a></p>";
			$t .= "<p class=RPreview>Attachment is part of article: <a title=\"View Article\" $target href=\"article.php?ID=$ID\"><b>$Title</b></a></p>"; //<hr>" ;		
		}
		else {
			$t = GetAttachmentIcon($R[Filename]) . " <a title=\"View attachment\" $target href=\"show_attachment.php?ID=" . $R[AttachmentID] . "\">" . $R[Filename] . "</a>";
			$t .= " <b>From Article:</b> <a title=\"View Article\" $target href=\"article.php?ID=$ID\"><b>" . substr($Title,0,45) . "...</b>";					
		}
	}
	return $t;
}

function fmt_Prod($Prod)
{
	if ($_GET['ChildList']) $target = " target=_KBWin ";
	if ($Prod) {
		$url = "search.php?Advanced=1&Search=&S=Search&What=KB&Product=" . urlencode($Prod);
		return "<a href=\"$url\" $target title=\"View all Articles for this product\">$Prod</a>";
	} else return "&nbsp;";
}

	$Product = trim($Product);
	$Type = trim($Type);
	$GroupID = trim($GroupID);
	if ($GroupID < 1 || $GroupID == 0) $GroupID = "";
		
	if (!$ns && ($MustRead || $Search || $GroupID || $Type || $Product)) { 
		echo "<div align=\"left\" style=\"float:left\"><b>Search Results:</b></div>";
		
			
		$Sort = GetVar("Sort");

		$q = " where Articles.STATUS = 'Active' and (Expires is NULL OR Expires >= GetDate() ) ";
		$q .= PrivFilter();

		if (trim($GroupID)) {
			$q .= " and Articles.GroupID = '$GroupID'";
		}
		if (trim($Product)) {
			$Product = trim($Product);
			$q .= " and Product like '%$Product%'";
		}		
		if (trim($Type)) {
			$q .= " and Type = '$Type'";
		}
		if (trim($MustRead)) {
			$q .= " and MustRead = 'Yes'";
			if ($CUser->u->GroupsMustRead) $q .= " AND Articles.GroupID in (" . $CUser->u->GroupsMustRead . ")";
		}
		if ($SType == "Title" && trim($Search)) {
			$Title = trim($Search);

			if ($Title) {
				$qtxt .= "$and with Title containing '$Title'";
				$twords = search_split_terms($Title);
				foreach($twords as $word) {
					$word = trim(str_replace("'","",$word));
					if ($word) {
						$q .= " and Title like '%$word%'";
					}
				}
			}

		}
		
		if ($SType != "Title" && $Search) {
			$topn = "";
								
			if ($SType == "Strict") {			
				$SMethod = "CONTAINSTABLE";
				$Search_s = $Search;
			}
			else {
				$SMethod = "FREETEXTTABLE";
				$Search_s = str_replace("'","",$Search); //$AppDB->qstr(trim($Search));
			}
			if ($Search_s != "" && $Search_s != "''") {
			   	$query = BuildSearchQuery($Search_s,$q,$SMethod);
	
				if ($Sort == "")
					$Sort="RankAndHits desc";
					
			} else {
				echo "<font color=red><b>&nbsp;You entered an invalid search string</b></font>";			
			}
		}
		else {
			$query = "select Articles.*,Articles.LASTMODIFIED as LModified, Groups.Name as GroupName,'-' as RankAndHits from Articles " 
					 ."left join Groups on Articles.GroupID = Groups.GroupID $q";			
			if ($Sort == "")
				$Sort="Title";
		}
		
							
		if ($ShowQuery) echo $query;
		
		if ($query) { 
		$DBFields["Title"] = "@fmt_Title";
		$DBFields["GroupName:Group"] = "";
		$DBFields["Type"] = "";			
		$DBFields["Product"] = "@fmt_Prod";
		$DBFields["LModified:Modified"] = "";
		$DBFields["RankAndHits:Rank"] = ":align=right";			

		$LB = new ListBoxPref("",$AppDB,$query,$DBFields,$Sort,"","",1,'95%');
		$LB->NoFrame = 1;
		$LB->Form = 1;
		$LB->CellStyle = ($Previews) ? "list-kb" : "list-sm";
		$LB->Style = "border=0 cellpadding=4 cellspacing=0";
		$LB->Display();
	
		if ($LB->TotalRows == 0 && $CUser->u->Priv >= PRIV_SUPPORT) {
			$kbn = (int)$Search;
			$Search = urlencode($Search);
			?>
			
			<b><br><hr>
			</b>
	  <p><b>No articles found.</b></p>
			<ul>
			  <? if ($AppDB->Settings->RemedyARServer) { ?>
			  <li style="font-size: 13px" ><b><a href="search_remedy.php?Search=<? echo "$Search&SType=$SType&What=Remedy&Product=$Product" ?>">Click here</a>
		      to try the search against Remedy Help Desk Tickets.</b> </li>
			  <? } ?>
              <!--
              <li><b><a href="search_office.php?Search=<? echo "$Search&SType=$SType&Product=$Product" ?>"><strong>Click here</strong></a>
		           to search Microsoft Office Online Assitance and Training articles.</b> (if your search is related to Microsoft Office Products) !-->
	        <? 
			if ($kbn > 0 && $kbn < 1000000) {
				echo "<br><br><i>Note: If you meant to find an article by that number, prefix the number with 'KB'</i>";
			}
		}
		
		// Save the query except for Admins if set
		if ($Search && $_GET["Page"] == "" && (!$CUser->IsPriv(PRIV_ADMIN) || !$AppDB->Settings->DontLogAdmin) ) {
			$Search = str_replace('\\\\',"",$Search);
			$Search = $AppDB->qstr($Search);
			// If this search is the same as one of this users searches in the past hour then don't bother saving it
			// This prevents useless records that may occur when hitting refresh on the browser or going back.
			$Dup = $AppDB->GetRecordFromQuery("select Top 1 ID from Searches where CREATEDBY = '" . $CUser->UserID . "' AND " .
						" DATEDIFF(hour,CREATED," . "GetDate()" . ") < 2 " . 
						" AND Search = $Search AND SearchType = '$SType' ");
			if (!$Dup) {
				$Fields["Search"] = $Search; 
				$Fields["SearchType"] = $SType;
				$Fields["Matches"] = $LB->TotalRows;
				$SearchID = $AppDB->insert_record("Searches",$Fields);
				// Randomly clean up the Search History table.
				if (rand(0,10) == 5) {
					$NDays = $AppDB->Settings->SearchHistoryDays;
					if ($NDays == "") $NDays = 30;
					$AppDB->sql("delete from Searches where DATEDIFF(day,CREATED," . "GetDate()" . ") >= $NDays","",0);
 				}
			} else {
				$SearchID = $Dup->ID;
			}
			AuditTrail("Search",array('SearchID' => $SearchID));	// always log in Activity log		
		}
	}
 }
	?>
                  <br>
                </p>
                                                </li>
    </ul></td>
  </tr>
</table>
</body>
</html>
