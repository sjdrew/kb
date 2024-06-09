<? 
if ($_GET['ID']) {
	header('location:article.php?ID=' . $_GET['ID']);
	exit;
}
include("config.php"); 

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Home</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body onLoad="FindElement('Search').focus()">
<script language="JavaScript" src="lib/misc.js"></script>
<script language="JavaScript" src="lib/AnchorPosition.js"></script>
<script language="JavaScript" src="lib/PopupWindow.js"></script>
<script language="JavaScript" src="lib/date.js"></script>
<script language="JavaScript" src="lib/CalendarPopup.js"></script>

<? include("header.php"); ?>
<?

	if (!$GroupID) {
		$GroupID = $CUser->u->GroupID;
	}

	AuditTrail("HomePage",array());

// Init ------------------------------------------

	$ListPadding = "border=0 cellpadding=2 cellspacing=1";
	$DateReadSubQuery = ",(select top 1 CREATED from Hits where Hits.ArticleID=Articles.ID AND Hits.CREATEDBY='$CUser->UserID' order by Hits.CREATED desc) as DateRead "; 
	$PFilter = "1=1 " . PrivFilter();
	$HideExpired = " and (Expires is NULL OR Expires >= GetDate()) ";
	$DaysBack = $AppDB->Settings->HitsHistoryDays - 1;

	if ($CUser->u->GroupsMustRead) $mrGroupFilter = " AND Articles.GroupID in (" . $CUser->u->GroupsMustRead . ")";
	$mrclause = " left join Hits on (Hits.CREATEDBY = '$CUser->UserID' AND Hits.ArticleID = Articles.ID " .
                	"AND Hits.CREATED > Articles.ContentLastModified) " .
	 			"left join Groups on Articles.GroupID = Groups.GroupID " .
	 			"where $PFilter $HideExpired and MustRead = 'Yes' AND Articles.STATUS='Active' AND " .
	 			"DATEDIFF(day,ContentLastModified,getdate()) <= $DaysBack AND Hits.ID is NULL " . 
				$mrGroupFilter;


	if ($SType == "") {
		if ($CUser->u->SearchMode == "") $SType = $AppDB->Settings->DefaultSearchMode;
		else if ($CUser->u->SearchMode == "Strict") $SType = "Strict";
	}
	
?>
<script language="javascript">
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
<? ShowMsgBox($msg,"center"); if ($msg) echo "<br>"; ?>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  	<tr><td height="14"> 	    
  	<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td width="22%"> 
      <td width="78%" colspan="2"> 
    <tr> 
      <td colspan="5"> </td>
    </tr>
    <tr valign="middle">
      <td width="185" valign="top" align="left" background="images/vert_bar.gif">
	  <img src="images/spacer.gif" width="185" height="1" border=0>
	  <table width="87%" border="0" cellpadding="4" cellspacing="0">
          <tr><td width="39%" ><img src="images/compglobe.gif" width="53" height="53"></td>
            <td width="61%" align="center" valign="middle" class="hdr1">Knowledge
              Base</td>
          </tr>
          <tr> 
            <td colspan="2" class="dots">...............................................</td>
          </tr>
          <tr>
            <td nowrap colspan="2">
			<div>
			<? $Priv = $CUser->u->Priv;
			
			if ($Priv >= PRIV_SUPPORT) {
			
				// in Compose Status
				$n = $AppDB->count_of("select count(*) from Articles where STATUS='Compose' and CREATEDBY='$CUser->UserID' $HideExpired " . PrivFilter(true));
				if ($n > 0) {
					echo ("<p class=listp><img align=\"middle\" src=\"images/i_compose.gif\"> <a title=\"click to display your created articles which are currently in Compose Status\" href=\"admin_articles.php?MyGroups=1&S=1&STATUS=Compose&CREATEDBY=$CUser->UserID\">$n in Composed status</a></p>");
				}
			
			}
						
			if ($Priv >= PRIV_APPROVER) {

				// Pending Tech Review
				$n = $AppDB->count_of("select count(*) from Articles where STATUS='Pending Technical Review' $HideExpired " . PrivFilter(true));
				if ($n > 0) {
					$s = ($n > 1) ? "s" : "";
					echo ("<p class=listp><img align=\"middle\" src=\"images/i_check.gif\"> <a title=\"click to display these articles which are pending a technical review\" href=\"admin_articles.php?MyGroups=1&S=1&STATUS=Pending+Technical+Review\">$n Pending technical review</a></p>");
				}
				
				// Pending Content Review				
				$n = $AppDB->count_of("select count(*) from Articles where STATUS='Pending Content Review' $HideExpired " . PrivFilter(true));
				if ($n > 0) {
					$s = ($n > 1) ? "s" : "";
					echo ("<p class=listp><img align=\"middle\" src=\"images/i_checkg.gif\"> <a title=\"click to display these articles which are pending a content review before they can be made active\" href=\"admin_articles.php?MyGroups=1&S=1&STATUS=Pending+Content+Review\">$n Pending content review</a></p>");
				}
				
				// High Priority Articles
				$n = $AppDB->count_of("select count(*) from Articles where (STATUS='Pending Content Review' or STATUS='Pending Technical Review') and Priority='High' $HideExpired " . PrivFilter(true));				
				if ($n > 0) {
					$s = ($n > 1) ? "s" : "";
					echo ("<p class=listp><img align=\"middle\" src=\"images/i_checkr.gif\"> <a title=\"click to display these High priority articles which are pending a technical or content review\" href=\"admin_articles.php?MyGroups=1&S=1&STATUS=Pending&Priority=High\">$n High Priority article$s</a></p>");
				}
								

				$n = $AppDB->count_of("select count(*) from Articles where STATUS='Active' $HideExpired AND ReviewBy <= GetDate() " . PrivFilter(true));
				if ($n > 0) {
					echo ("<p class=listp><img align=\"middle\" src=\"images/i_clock.gif\"> <a title=\"Display Articles that are due for a another review\" href=\"admin_articles.php?MyGroups=1&S=1&STATUS=Active&ReviewBy=".Now()."\">$n Due for another review</a></p>");
				}
				
				$n = $AppDB->count_of("select count(*) from Articles inner join ArticleNotes on Articles.ID=ArticleNotes.ArticleID where 1=1 " . 
								" $HideExpired AND NoteType = 'Action Required' " . PrivFilter(true));
				if ($n > 0) {
					$s = ($n > 1) ? "s" : "";
					echo ("<p class=listp><img align=\"middle\" src=\"images/i_note.gif\"> <a title=\"Display Articles that contain 'Action Required' Notes\" href=\"admin_articles.php?MyGroups=1&S=1&NoteType=Action+Required\">$n 'Action Required' Note$s</a></p>");
				}
			}
			
			// Unread article count
			$mr = $AppDB->count_of("select count(*) from Articles where STATUS='Active' and MustRead='Yes' $HideExpired $mrGroupFilter " . PrivFilter());
			if ($mr) { 
				$mr_unread = $AppDB->count_of("select count(*) from Articles " . $mrclause);	
			?>
			<p class=listp><img align="middle" src="images/i_important.gif"> <a title="Articles which are marked as 'Must Read'" href="search.php?MustRead=Yes"><? echo $mr ?> Must Read (<? echo $mr_unread ?> unread)</a></p>
			<? }
			
			
			if ($Priv >= PRIV_SUPPORT && $CUser->PrivWrite) {
				echo "<p class=listp><img align=\"middle\" src=\"images/i_bulb.gif\"> <a title=\"Display any articles that you have submitted or are listed as a contact\" href=\"admin_articles.php?S=1&AuthorOrContact=$CUser->UserID\">Your articles</a></p>"; 
			}
			if (HIDE_BULLETINS != 'Yes') {
			
			if ( ($AppDB->Settings->AllowCreateBulletinsW && $CUser->PrivWrite)	 ||
				 ($AppDB->Settings->AllowCreateBulletins) ||
				 ($Priv > PRIV_SUPPORT) ) {
				echo "<p class=listp><img align=\"middle\" src=\"images/i_bb.jpg\"> <a title=\"Add a Bulletin Board Message which will be listed on the KB home page and viewable by all your Group members\" href=\"admin_message.php?\">Create Bulletin</a></p>"; 
			}
			
			}
			if ($Priv >= PRIV_SUPPORT && $CUser->PrivWrite) { ?>
			<p class=listp><img align="middle" src="images/i_pencil.gif"> <a title="Create a new Article in the KB" href="admin_article.php">Create
			    new article</a></p>
			<?
			} ?>
			<p class=listp><img align="middle" src="images/i_browse.gif"> <a title="Browse all articles by Product or by Type" href="browse.php">Browse Articles</a></p>
			</div>
			<?
			
			?></td>
          </tr>
          <tr> 
            <td colspan="2" class="dots" ><p>..............................................<br>
              </p>
              </td>
          </tr>
          <tr>
            <td colspan="2" >
			<? 
				DisplayContentSection("HomePageLeftColumn");
			?>
			</td>
          </tr>
       </table>
	  </td>
      <td colspan="2" valign="top">
			<? 
				DisplayContentSection("HomePageTopCenter");
			?>	   
	      <table width="95%" border="0" align="center" cellpadding="5">
          <tr valign="middle"> 
            <td style="padding-top:8px"width="11%" valign="top" class="subhdr"><strong>Search: </strong></td>
            <td nowrap width="89%" class="subhdr"><form name="searchform" method="get" action="search.php"><input name="Search" type="text" size="60" maxlength="200">
            <input name="S" type="submit" value="Search"> <?
			if ($Priv >= PRIV_SUPPORT) {
				DisplaySearchModes("onchange='onchangewhat()' style='font-size:10px'");
			}
			?>
            <br>
            <span class="small">Search by:
			<input name="SType" <? if ($SType != "Strict" && $SType != "Title") echo checked ?> type="radio" value="English">
			English query,
            <input name="SType" <? if ($SType == "Strict") echo checked ?> type="radio" value="Strict">
			Strict, match all words and quoted phrases
            <input name="SType" <? if ($SType == "Title") echo checked ?> type="radio" value="Title">
			-or- Title only.</span>

			<? if ($AppDB->Settings->FiltersOnHomePage) { ?>
			<input type="hidden" name="Advanced" value="1">
			<br>
			 <fieldset style="padding-top:8px; width:300px">
			    <legend style="font-size: 12px"> Filters: </legend>
			<table style="margin:4px">
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
			<? } ?>
			
			
			</form></td>
          </tr>
          <tr valign="middle">
            <td align="center" valign="top" class="subhdr"><a href="javascript:showhelp('help/intro.html')"><img src="images/help_circle.gif" alt="Help" width="18" height="18" border="0"></a></td>
            <td class="small">Enter search phrase, for example: <em>how
              do I create a distribution list in outlook, enter article number as KBnnn,<br>
              use the word NEAR to favour words in close proximity. ie error near outlook. <a href="browse.php">
              Browse all articles</a> or use <a href="search.php?Advanced=1">Advanced Search</a>              </em></td>
          </tr>
          <tr valign="middle">
            <td colspan="2" class="subhdr"><hr></td>
            </tr>
          <tr valign="middle">
            <td colspan="2"><p>	
<?

function fmt_btitle($str,$ID,$R)
{
	$ids = "id=\"B$ID\"";
	if ($R['DateRead'] && $R['LASTMODIFIED'] && $R['DateRead'] < $R['LASTMODIFIED']) {
		$span = "<span $ids>";
	}
	else if ($R['DateRead'] != "") {
		$span = "<span $ids class=\"aread\">";
	}
	else  {
		$span = "<span $ids>";
	}
	return("$span <a href='Javascript:DisplayMessage($ID); void(0);' title=\"" . substr(htmlentities($R["Message"]),0,512) . "\">$str</a>\n</span>");
}

function fmt_title($str,$ID,$R)
{
	$str = TitleFormat($str,$R['ViewableBy']);

	if ($R['DateRead'] && $R['DateRead'] < $R['ContentLastModified']) {
		$span = "<span>";
		$param = " title=\"Article has been updated since you last read it.\nUpdated on $R[ContentLastModified]\" ";	
	}
	else if ($R['DateRead'] != "") {
		$span = "<span class=\"aread\">";
		$param = "title=\"You read this article on " . $R['DateRead']  . '" ';
	}
	else  {
		$span = "<span>";
		$param = " title=\"Unread Article\" ";
	}
	return("$span<a href=\"Article.php?ID=$ID\" $param >$str</a></span>");
}

function fmt_title2($str,$ID,$R)
{
	$str = TitleFormat($str,$R['ViewableBy']);
	$param = " title=\"Must Read Article. Updated on $R[ContentLastModified]\nAfter you have read this article it will be removed from this list.\"";	
	return("<a href=\"Article.php?ID=$ID\" $param >$str</a>");
}

if ($AppDB->count_of(MessageQuery("",0))) {
	
?>
 <fieldset>
    <legend> Bulletins <a style="font-size:8pt" href="admin_messages.php" title="Manage Bulletins">(Manage)</a> </legend>
	<?
	
function xfmt_SubjectStr($str,$ID,$R)
{
}

function fmt_bb_icon($str,$ID,$R)
{	
	return(MessageIcon($R["Type"]));
}
	// ----------------------- Bulletins -----------------------------------
	$BBQuery = MessageQuery("",0,1);
	$Fields = "";	
	$Fields[" "] = "@fmt_bb_icon";
	$Fields["Subject"] = "75%@fmt_btitle";
	$Fields["Type"] = ":nowrap";
	$Fields["ServiceName:Service"] = ":nowrap";
	$Fields["GroupName:Group"] = ":nowrap";	
	//$Fields["Date"] = "75@DateStr:nowrap";
	$LB1 = new ListBox("",$AppDB,$BBQuery,$Fields,"Type desc","",'',1,"90%");
	$LB1->NoFrame = 1;
	$LB1->ScrollAfterRows = 15;
	$LB1->NoTopStats = 1;
	$LB1->CellStyle = "list-sm";
	$LB1->Style = $ListPadding;
	$LB1->Form = 1;
	$LB1->sortable = 1;
	$LB1->Sort2="Messages.ID desc";
	$LB1->Display();

	?>
 </fieldset>
<br>

<? } 

/*
 * Remedy Known Errors
 */
if ($AppDB->Settings->RemedyARServer && $Priv >= PRIV_SUPPORT) {
?>
 <fieldset>
    <legend> Recent Known Errors </legend>
<?
	$KEQuery = "select top 30 Known_Error_ID as ID,Product_Name as Product ,Detailed_Decription as Notes,
			First_Reported_On, Searchable,Category,Assigned_Group,
			Description as Summary 
			from PBM_Known_Error 
			where Searchable = 0 and Known_Error_Status < 4
			";
		
	$RemDB = OpenRemedyDB();
	
	if (!$RemDB) {
		echo "Remedy currently unavailable.";
		exit;
	}
	
	// ----------------------- Bulletins -----------------------------------
	unset($Fields);	
	$Sort = '';
	$Fields["Summary"] = "";
//	$Fields["ID"] = "";
	$Fields["Product"] = "";
	$Fields["Assigned_Group:Group"] = ":nowrap";	
	//$Fields["Date"] = "75@DateStr:nowrap";
	$LB5 = new ListBox("",$RemDB,$KEQuery,$Fields,"","/Reports/Case.php",'',1,"90%");
	$LB5->NoFrame = 1;
	$LB5->ScrollAfterRows = 5;
	$LB5->NoTopStats = 1;
	$LB5->CellStyle = "list-sm";
	$LB5->Style = $ListPadding;
	$LB5->Form = 1;
	$LB5->sortable = 1;
	$LB5->Sort="First_Reported_On desc";
	$LB5->Display();
	?>
 </fieldset>
<br>
<? }  // End Known Errors


	//
	// Must Read Section -----------------------------------------------------------------
	//
	$DBFields = "";
	$DBFields["Title"] = "@fmt_title2";
	$DBFields["Product"] = " ";	
	$DBFields["GroupName:Group"] = ":nowrap";	
	$DBFields["Date"] = ":align=right nowrap";			
	$SelFields = "Articles.ID,Articles.ViewableBy,Articles.Title,Articles.Product,ContentLastModified,Groups.Name as GroupName,left(ContentLastModified,11) as Date";
	$top = 100;
	$lb4_sort = "ContentLastModified desc";
	
	//
	// Select from Hits to see if Current User has Read each MustRead article.
	// We can only select articles going back as far as the HitsHistoryDays setting as data is purged after that
	// date and we would not have info as to read or not read.
	//

	$query = "select top $top $SelFields from Articles " . $mrclause;
if ($AppDB->count_of($query)) {
?>
 <fieldset>
    <legend> <u>Must Read</u> - unread articles </legend>
    <?			 
	$LB4 = new ListBox("",$AppDB,$query,$DBFields,$lb4_sort,"article.php","",1,'90%');
	$LB4->NoFrame = 1;
	$LB4->NoTopStats = 1;
	$LB4->ScrollAfterRows = 10;
	$LB4->CellStyle = "list-sm";
	$LB4->sortable = 1;
	$LB4->Form = 1;
	$LB4->Style = $ListPadding;
	$LB4->Display();
?>
 </fieldset>
<br>
<? 
} 

	// ------------------- New or Updated --------------------------
?>
 <fieldset>
    <legend> New or updated articles (past 7 days)</legend>
	<?
	$ShowGroups = 1;
	
	$DBFields = "";
	$DBFields["Title"] = "@fmt_title";
	$DBFields["Product"] = " ";	
	if ($ShowGroups) {
		$DBFields["GroupName:Group"] = ":nowrap";	
	}
	$DBFields["Days"] = ":align=right";			
	$SelFields = "Articles.ID,Articles.ViewableBy,Articles.Title,ContentLastModified,Articles.Product,Groups.Name as GroupName $DateReadSubQuery";
	/* this line not needed */ 
	$SelFields .= ",datediff(day,(CASE WHEN ContentLastModified is NULL then Articles.CREATED ELSE ContentLastModified END),getdate()) as Days ";

	$top = $AppDB->Settings->DisplayNewCount ? $AppDB->Settings->DisplayNewCount : 10;
	$lb2_sort = $AppDB->Settings->DisplayNewSort == 2 ? "Title" : "ContentLastModified desc";
	
	$query = "select top $top $SelFields " .
			 "from Articles " .
			 "left join Groups on Articles.GroupID = Groups.GroupID " .
			 "where $PFilter $HideExpired and Articles.STATUS='Active' AND (DATEDIFF(day,ContentLastModified,getdate()) <= 7 or " .
	            " DATEDIFF(day,Articles.CREATED,getdate()) <= 7) "; 
	$LB2 = new ListBox("",$AppDB,$query,$DBFields,$lb2_sort,"","",1,'90%');
	$LB2->NoFrame = 1;
	$LB2->NoTopStats = 1;
	$LB2->ScrollAfterRows = 10;
	$LB2->CellStyle = "list-sm";
	$LB2->sortable = 1;
	$LB2->Form = 1;
	$LB2->Style = $ListPadding;
	$LB2->Display();
	?>
 </fieldset>
<br>	
 <fieldset>
    <legend>Most viewed articles</legend>
	<?
	
	// ----------------- Most Viewed Articles ---------------------------
	
	$DBFields = "";
	$DBFields["Title"] = "@fmt_title";
	$DBFields["Product"] = " ";
	if ($ShowGroups) {
		$DBFields["GroupName:Group"] = ":nowrap";	
	}
	$DBFields["Hits"] = ":align=right";			
	$SelFields = "Articles.ID,Articles.ViewableBy,Articles.Title,ContentLastModified,Articles.Product,Articles.Hits,Groups.Name as GroupName $DateReadSubQuery";
	
	$top = $AppDB->Settings->DisplayViewedCount ? $AppDB->Settings->DisplayViewedCount : 10;
	$lb3_sort = $AppDB->Settings->DisplayViewedSort == 2 ? "Title" : "Hits desc";
	
	$query = "select top $top $SelFields from Articles " .
			 "left join Groups on Articles.GroupID = Groups.GroupID " .	
			 "where $PFilter $HideExpired and Articles.STATUS='Active' and Hits > 0 ";
			 
	$LB3 = new ListBox("",$AppDB,$query,$DBFields,$lb3_sort,"","",0,'90%');
	$LB3->NoFrame = 1;
	$LB3->ScrollAfterRows = 10;
	$LB3->Form = 1;
	$LB3->NoTopStats = 1;
	$LB3->sortable = 0;
	$LB3->CellStyle = "list-sm";
	$LB3->Style = $ListPadding;
	$LB3->Display();
	?>
 </fieldset>
	<br>
            </p>
              </td>
          </tr>
        </table>
        </td>
    </tr>
  </table>
    </td>
  </tr>
</table>
</body>

</html>