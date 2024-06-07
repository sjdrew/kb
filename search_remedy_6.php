<?
//
// Search Remedy HelpDesk Table (T85)
//
// Relies on Microsoft 2000 Full Text search catalog being defined on table
//
// The following fields are full texted and rebuilt nightly via SQL 2000
//
/*
Case ID+:1:char:
Submitted By:2:char:
Summary:8:charmenu:
Category:200000003:charmenu:
Type:200000004:charmenu:
Item:200000005:charmenu:
Requester ID+:240000000:char:
Requester Name+:240000001:char:
Group+:240000006:charmenu:
Description:240000007:char:
Work Log:240000008:diary:
Assigned To Individual+:240000015:charmenu:
*/
include("config.php"); 
RequirePriv(PRIV_GROUP,"home.php");
if (!$AppDB->Settings->RemedyARServer) {
	 echo "Remedy Server not defined";
	 exit;
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
	if ($What == "KB") {
		header("location:search.php?ns=1&Search=$Search&SType=$SType&Advanced=$Advanced");
	}
	if ($What == "Office Products") {
		header("location:search.php?ns=1&Search=$Search&SType=$SType&What=Office Products");
	}
	if ($What == "") $What = "Remedy HelpDesk";

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
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<? include("header.php"); ?>
<table width="95%" border="0" align="center" cellpadding="4">
  <tr>
    <td colspan="2">
  <tr valign="middle">
    <td width="23%" align="left" valign="top" nowrap><p class="subhdr">Search Remedy Help Desk:</p></td>
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
			Strict, match all words and quoted phrases.</span>
			<? if ($Advanced) { ?>
			<br>
			 <fieldset style="padding-top:8px; width:300px">
			    <legend> Advanced Filters: </legend>
			<table style="margin:10px">
			 <tr>
				<td align="right" class="form-hdr">Group:</td>
			      <td class="form-hdr"><? 
				  	dropdownlistfromquery("Group",$RemDB,"select Group_Name as Name from Group_x where Group_Type=0 AND Status=0 order by Group_Name",$Group,"(All Groups)",'style="width:130px"',"Name","Name"); ?>
				  </td>
                  <td align="right" class="form-hdr">Product:</td>
                  <td class="form-data"><? DBField("Articles","Product_S",$Product); ?>
                </td>	
			</tr>	
			</table>
			</fieldset>
			<? } else { ?>
				<a class="small" href="search_remedy.php?SType=<? echo $SType ?>&Advanced=1" title="Advanced Search filters">Advanced</a>
			<? } ?>
      </form>
    </td>
  </tr>
  <tr >
    <td valign="top" colspan="2"><hr>
    <?
	global $Previews;
	$Previews = ($CUser->u->Previews != "No") ? 1 : 0;

function fmt_Preview($Text)
{
	return substr(htmltotext($Text),0,300) . "...";
}

function fmt_Summary($Title,$ID,$R) 
{
	global $Previews;
	$t = "";
	
		if ($Previews) {
			$t =  "<p class=RPreview><A title=\"click to view in new window\" target=_blank href=\"http://itshd/UpdateCase.asp?Case=$ID\">" . $Title . "</a></p>";
			$t .= "<p class=RPreview>" . fmt_Preview($R[Description]) . "</p>"; //<hr>";
		}
		else {
			$t =  "<A title=\"click to view in new window\" target=_blank href=\"http://itshd/UpdateCase.asp?Case=$ID\">" . $Title . "</a>";		
		}
	return $t;
}

function fmt_date($d)
{
	return(DateTimeStrFromValue($d,0));
}
	
	$Product = trim($Product);
	$Type = trim($Type);
	$Group = trim($Group);
	if ($Group == "(All Groups)") $Group = "";
		
	if (!$ns && ($Search || $Group || $Type || $Product)) { 
		echo "<div align=\"left\" style=\"float:left\"><b>Search Results:</b></div>";
		$DBFields["Summary"] = "@fmt_Summary";
		$DBFields["Date"] = "@fmt_date";
		$DBFields["Product"] = " ";
		$DBFields["Group_:Group"] = " ";
		$DBFields["Rank"] = ":align=right";			
			
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
		
				$query  = " select HPD.Case_ID_ as ID,HPD.Summary,HPD.Arrival_Time as Date,HPD.Group_,HPD.Type as Product," 
				. " HPD.Description,KEY_TBL.Rank from HPD_HelpDesk as HPD"
				. " inner JOIN $SMethod(T85, *,$Search_s," . MAXROWS . ") as KEY_TBL ON HPD.Case_ID_=KEY_TBL.[KEY]";
				if ($Sort == "")
					$Sort="Rank desc";
				
			} 
		}
		else {
			$query = "select HPD.Case_ID_ as ID,HPD.Summary,HPD.Arrival_Time as Date,HPD.Group_,HPD.Type as Product," 
				. " HPD.Description,' ' as Rank from HPD_HelpDesk as HPD where 1=1 ";
			if ($Sort == "")
				$Sort="Summary";
		}
		if (trim($Group)) {
			$q .= " and Group_ = '$Group'";
		}
		if (trim($Product)) {
			$Product = trim($Product);
			$q .= " and Type like '%$Product%'";
		}		
		$query .= " $q ";
		//echo $query;
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
