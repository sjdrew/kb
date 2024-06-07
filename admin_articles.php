<?  
	include("config.php");
	RequirePriv(PRIV_GROUP);
	if ($_GET[S] == "") {
		RequirePriv(PRIV_APPROVER);
	}
	CheckIfKBNumber($Search,$Loc="admin_article.php");
	if (!$Export) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Admin - Articles</title>
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
<script LANGUAGE="JavaScript" SRC="lib/AnchorPosition.js"></script>
<script LANGUAGE="JavaScript" SRC="lib/PopupWindow.js"></script>
<script LANGUAGE="JavaScript" SRC="lib/date.js"></script>
<script LANGUAGE="JavaScript" SRC="lib/CalendarPopup.js"></script>
<? include("header.php");  ?>

<script language="JavaScript">
function parse()
{
	if (!CheckDate(form.Modified_Since)) return false;
	if (!CheckDate(form.Modified_Before)) return false;
	if (!CheckDate(form.Reviewed_Since)) return false;
	if (!CheckDate(form.Reviewed_Before)) return false;
	if (form.NoteType.value != ' ' && trim(form.Search.value) != '') {
		alert("You cannot search on Article content and Note Type values at the same time. Clear one or the other.");
		return false;
	}
	return true;
}
function ClearSearch(c)
{
	var s = FindElement("Search");
	if (c.checked && s.value != "") {
		alert("Search value will be cleared as cannot apply search value when displaying Read Stats");
		s.value = '';
	}
}

function ListModifySelected()
{
<? if ($CUser->IsPriv(PRIV_ADMIN) || $CUser->IsPriv(PRIV_APPROVER)) { ?>
	DoListModifySelected("admin_article.php");
<? } else { ?>
	alert("You must have Approver Privileges to use the Modify Selected function");
<? } ?>
}

function ListExport() { return DoListExport(); }

</script>
<center>
<br>
<form onSubmit="return parse()" method="Get" name="form" Action="<? echo $PHP_SELF ?>">
<?  hidden("S",$S);
ShowMsgBox($msg,"center");

} // End if !export

if ($_GET[S]) {
function fmt_dateonly($str)
{
	return substr($str,0,10);
}

function fmt_rstat($str,$ID)
{
	return "<a title=\"view Read Status\" href=\"report_article_read_status.php?ID=$ID\">$str</a>";
}

function fmt_note($str,$ID) 
{
	if (strlen($str) > 250) $str = substr($str,0,250) . "...";
	return "<span style=\"font-size:8pt\">$str</span>";
}

function fmt_title($str,$ID,$R)
{
	return(TitleFormat($str,$R['ViewableBy']));
}


	$DBFields["RID:ID"] = "@fmt_kb";
	$DBFields["Title"] = '@fmt_title';
	$DBFields["CREATEDBY:Created By"] = '';
	$DBFields["GroupName:Group"] = '';
	$DBFields["Hits"] = " ";
	if (trim($Reviewed_Since) || trim($Reviewed_Before)) {
		$DBFields["LastReviewed:Reviewed"] = "@fmt_dateonly:nowrap";	
	}
	else {
		if (trim($NoteType)) 
			$DBFields["Date"] = "@fmt_dateonly:nowrap";
		else
			$DBFields["Modified"] = "@fmt_dateonly:nowrap";
	}
	
	// Show Note column instead of Product,Type and Status.
	if (trim($NoteType)) {
		$DBFields["Note"] = '@fmt_note';
	}
	else {
		$DBFields["Product"] = '';
		$DBFields["Type"] = '';
		$DBFields["AStatus:Status"] = '';
	}

	if ($ReadStats) {
		$DBFields["IsRead:Read"] = "@fmt_rstat:align=right";
		$DBFields["UnRead"] = "@fmt_rstat:align=right";
	}
	
	$q = "where 1=1 ";	
	$Sort = GetVar("Sort");
	if ($Sort == "")
		if ($Search) $Sort="Rank desc";
		else $Sort = "RID";
	
	
	if (trim($GroupID)) {
		if ($GroupID == "(unassigned)" || $GroupID == "NULL" || $GroupID == 'null') {
			$q .= " and Groups.GroupID is NULL ";
			$qtxt .= " unassigned  ";
			$and = "and";
		} else {
		$q .= " and Articles.GroupID = '$GroupID'";
		$GR = $AppDB->GetRecordFromQuery("select * from Groups where GroupID= $GroupID");
		$qtxt .= " for Group $GR->Name";
		$and = "and";
		}
	}
	
	if (trim($NoteType)) {
		$q .= " and NoteType ='$NoteType'";
		$NoteJoin = 1;
		$qtxt .= " with Action Required Notes";
	}	
	if (trim($Product)) {
		$q .= " and Product like " . $AppDB->qstr("%" . trim($Product). "%");
		$qtxt .= " $and Product = $Product";
		$and = "and";
	}

	if (trim($Type)) {
		$q .= " and Type ='$Type'";
		$qtxt .= " $and Type = $Type";
		$and = "and";
	}	
	
	if ($ViewableBy > 0) {
		$q .= " and ViewableBy = $ViewableBy ";
	}

	if (trim($STATUS)) {
		if ($STATUS == "Pending") { // special case
			$q .= "and (Articles.STATUS='Pending Content Review' or Articles.STATUS='Pending Technical Review') ";
			$qtxt .= " $and Status = $STATUS";
		}
		else {
			$q .= " and Articles.STATUS ='$STATUS'";
			$qtxt .= " $and Status = $STATUS";
		}
		$and = "and";
	}

	if (trim($Priority)) {
		$q .= " and Priority ='$Priority'";
		$qtxt .= " $and Priority = $Priority";
		$and = "and";
	}

	if (trim($MustRead)) {
		$q .= " and MustRead ='$MustRead'";
		$qtxt .= " $and MustRead = $MustRead";
		$and = "and";
	}
	
	if (trim($Custom1)) {
		$q .= " and Custom1 ='$Custom1'";
		$qtxt .= " $and " . $AppDB->Settings->Custom1Label . " = $Custom1";
		$and = "and";
	}

	if (trim($Keywords)) {
		$q .= " and Keywords like'%$Keywords%'";
		$qtxt .= " $and having Keyword $Keywords";
		$and = "and";
	}
	
	if (trim($ReviewBy)) {
		$q .= " and ReviewBy <='$ReviewBy' ";
		$qtxt .= " $and due for review on or before $ReviewBy";
		$and = "and";
	}
	
	if ($Month = $_GET['Month']) { // expect YYYY-MM
		list($y,$m) = split('-',$Month);
		$Created_Since = $Month . "-01";
		++$m;
		if ($m == 13) { ++$y; $m = "01"; }
		$Created_Before = $y . "-" . $m . "-01";
	}
	
	if (trim($Created_Since)) {
		$q .= " and Articles.CREATED >= '$Created_Since'";
		if (!trim($Created_Before))
			$qtxt .= " $and created since $Created_Since";
	}

	if (trim($Created_Before)) {
		$q .= " and Articles.CREATED <= '$Created_Before'";
		if (!trim($Created_Since)) 
			$qtxt .= " $and created on or before $Created_Before";
		else 
			$qtxt .= " $and created between $Created_Since and $Created_Before";
	}

	if (trim($Modified_Since)) {
		$q .= " and Articles.LASTMODIFIED >= '$Modified_Since'";
		if (!trim($Modified_Before))
			$qtxt .= " $and modified since $Modified_Since";
	}

	if (trim($Modified_Before)) {
		$q .= " and Articles.LASTMODIFIED <= '$Modified_Before'";
		if (!trim($Modified_Since)) 
			$qtxt .= " $and modified on or before $Modified_Before";
		else 
			$qtxt .= " $and modified between $Modified_Since and $Modified_Before";
	}

	if (trim($Reviewed_Since)) {
		$q .= " and LastReviewed >= '$Reviewed_Since'";
		$qtxt .= " $and Last Reviewed since $Modified_Since";
	}

	if (trim($Custom2_Before)) {
		$q .= " and Custom2 <= '$Custom2_Before'";
		$qtxt .= " $and  " . $AppDB->Settings->Custom2Label . " on or before $Custom2_Before";
		$and = "and";
	}

	if (trim($Custom2_Since)) {
		$q .= " and Custom2 >= '$Custom2_Since'";
		$qtxt .= " $and  " . $AppDB->Settings->Custom2Label . " since $Custom2_Since";
		$and = "and";
	}

	if (trim($Reviewed_Before)) {
		$q .= " and LastReviewed <= '$Reviewed_Before'";
		$qtxt .= " $and Last Reviewed on or before $Modified_Before";
	}
	
	if (trim($CREATEDBY)) {
		$q .= " and Articles.CREATEDBY = '$CREATEDBY' ";
		$qtxt .= " $and Created by $CREATEDBY ";
		$and = "and";
	}	

	if (trim($Contact1)) {
		$Contact1 = trim($Contact1);
		$q .= " and (Contact1 like '%$Contact1%' OR Contact2 like '%$Contact1%') ";
		$qtxt .= "$and Contact is like $Contact1 ";
	}
	
	if (trim($AuthorOrContact)) {
		$AuthorOrContact = trim($AuthorOrContact);
		$q .= " and (Articles.CREATEDBY = '$AuthorOrContact' OR Contact1 = '$AuthorOrContact' OR Contact2 = '$AuthorOrContact') ";
		$qtxt .= "$and where $AuthorOrContact listed as the Author or Contact ";	
	}	

	if (trim($Title)) {
		$Title = trim($Title);
		$qtxt .= "$and with Title containing '$Title'";
		$twords = search_split_terms($Title);
		foreach($twords as $word) {
			$word = trim(str_replace("'","",$word));
			if ($word) {
				$q .= " and Title like '%$word%'";
			}
		}
	}

	// Account for Privs	
	$q .= PrivFilter($_GET['MyGroups']);
	if ($MustRead) {
		$UserFilter = ':_:Y%';
	}
	else $UserFilter = ':%'; 
	
	if ($ReadStats) $ReadQuery = "	
	(select count(distinct Username)
	 from users 
     left join Hits on (Hits.ArticleID = Articles.ID and Hits.CREATEDBY = Username and Hits.CREATED > Articles.ContentLastModified)
	    where  
       (users.Groups like '1$UserFilter' OR users.Groups like '%,1$UserFilter' OR users.Groups like cast(Articles.GroupID as varchar(10)) + '$UserFilter' OR users.Groups like '%,' +  cast(Articles.GroupID as varchar(10)) + '$UserFilter')
		  	and Hits.ArticleID is  not null) as IsRead,
	(select count(distinct Username)
	 from users 
	 left join Hits on (Hits.ArticleID = Articles.ID and Hits.CREATEDBY = Username and Hits.CREATED > Articles.ContentLastModified)
	   where 
       (users.Groups like '1$UserFilter' OR users.Groups like '%,1$UserFilter' OR users.Groups like cast(Articles.GroupID as varchar(10)) + '$UserFilter' OR users.Groups like '%,' +  cast(Articles.GroupID as varchar(10)) + '$UserFilter')
	   		and Hits.ArticleID is  null) as UnRead,
	";
	//echo $ReadQuery;
	
	if ($SType == "Strict") {			
		$SMethod = "CONTAINSTABLE";
		$Search_s = $Search;
	}
	else {
		$SMethod = "FREETEXTTABLE";
		$Search_s = $AppDB->qstr($Search);
	}

	if ($Search_s != "" && $Search_s != "''") {
		$query = BuildSearchQuery($Search_s,$q,$SMethod,1);
 	} else {
		if ($NoteJoin) $nf = "cast (ArticleNotes.Notes as varchar(255)) as Note, ArticleNotes.CREATED as Date,";
		else $nf = "";
		$query = "select Articles.ID as RID, Articles.ViewableBy, Articles.STATUS as AStatus, Articles.LASTMODIFIED as Modified, $ReadQuery Articles.*, $nf Groups.Name as GroupName from Articles 
		          left join Groups on Articles.GroupID = Groups.GroupID ";
		if ($NoteJoin) 
			$query .= " inner join ArticleNotes on ArticleNotes.ArticleID = Articles.ID ";
		$query .= "$q";
	}
		
	$title = "Articles $qtxt";
	$subtitle = ' (Click on a Article to View/Modify, or <b><a style="font-size:11px" href="admin_article.php">Add</a></b> a new Article)';

function fmt_url($R) {
	$Ret = $_SERVER['REQUEST_URI'];
	$ID = $R['ID'];
	return "admin_article.php?ID=$ID&Ret=" . urlencode($Ret);
}

	if ($query) {
	
	$LB = new ListBoxPref("$title",$AppDB,$query,$DBFields,$Sort,"@fmt_url",$subtitle,1,'90%');
	$LB->CmdBar = 1;
	$LB->Export = $Export;
	$LB->Display();
	
	}
?> 
<table border="0" cellspacing="0" cellpadding="8" width="90%"><tr><td align="left">
<? $BackLoc = str_replace("S=Search","S=",$_SERVER['REQUEST_URI']);
   $BackLoc = str_replace("S=1","S=",$BackLoc);
	if ($CUser->Priv() < PRIV_APPROVER) $BackLoc =  "home.php";

?>
<button onClick="window.location='<? echo $BackLoc ?>'">Back</button>
</td></tr></table>
<?
} else {
?>
	 <div class="shadowboxfloat">
          <div class="shadowcontent">
<?

	if ($SType == "") {
		if ($CUser->u->SearchMode == "") $SType = $AppDB->Settings->DefaultSearchMode;
		else if ($CUser->u->SearchMode == "Strict") $SType = "Strict";
	}

	?>       

            <table <? echo $FORM_STYLE ?> width="550"  >
                <tr>
                  <td height="30" colspan="4" class="normal"><strong>Search for
                  Articles to manage:</strong></td>
                </tr>				
                <tr>
                  <td rowspan="8" width="14%" align="right"><img src="images/article.gif" width="50" height="53"></td>
                  <td colspan="2" align="right" nowrap class="form-hdr">Search:</td>
                  <td width="69%" class="form-data"><input name="Search" type="text" value="<? echo $Search ?>" size="55">				  </td>
                </tr>
                <tr>
                  <td colspan="2" align="right" nowrap class="form-hdr">&nbsp;</td>
                  <td class="form-data">			<span class="small">
			<input name="SType" <? if ($SType == "" || $SType == "English") echo checked ?> type="radio" value="English">
			English query -or-
            <input name="SType" <? if ($SType == "Strict") echo checked ?> type="radio" value="Strict">
			Strict, match all words and quoted phrases.</span>			     </td>
                </tr>

			    <tr>
			      <td colspan="2" align="right" class="form-hdr">Title:</td>
			      <td class="form-data"><? DBField("Articles","Title_Search",$Title); ?></td>
		      </tr>
			    <tr>
			      <td colspan="2" align="right" class="form-hdr">Group:</td>
			      <td class="form-data"><? DBField("Articles","GroupID",$GroupID);  ?>&nbsp;</td>
		      </tr>
				<?  if ($AppDB->Settings->PrivMode == "Group") { ?>
              <tr>
                  <td colspan="2" align="right" class="form-hdr">Viewable By: </td>
                  <td class="form-data"><? 
						DBField("Articles","ViewableByG_S",$ViewableBy,0,$ModifyAll); 
				  ?>
				  </td> 
               </tr>
               <? } ?>
               <tr></td>
                </tr>
                <tr>
                  <td colspan="2" align="right" class="form-hdr">Type:</td>
                  <td class="form-data"><? DBField("Articles","Type",$Type); ?>                  </td>
                </tr>
                <tr>
                  <td colspan="2" align="right" class="form-hdr">Product:</td>
                  <td class="form-data"><? DBField("Articles","Product_S",$Product); PopupFieldValues("Articles","form","Product"); ?>                  </td>
                </tr>
                <tr>
                  <td colspan="3" align="right" nowrap class="form-hdr">Status:</td>
                  <td class="form-data"><? DBField("Articles","STATUS_Search",$STATUS); ?>                  </td>
                </tr>
                <tr>
                  <td align="right">&nbsp;</td>
                  <td colspan="2" align="right" nowrap class="form-hdr">Priority:</td>
                  <td class="form-data"><? DBField("Articles","Priority_Search",$Priority); ?></td>
                </tr>
                <tr>
                  <td colspan="3" align="right" class="form-hdr">Must Read:</td>
                  <td class="form-data"><? DBField("Articles","MustRead_Search",$MustRead); ?></td>
                </tr>
				<? if ($AppDB->Settings->Custom1Label) { ?>
                <tr>
                  <td colspan="3" align="right" class="form-hdr"><? echo $AppDB->Settings->Custom1Label ?></td>
                  <td class="form-data"><? DBField("Articles","Custom1",$Custom1); ?></td>
                </tr>
				<? } ?>
                <tr>
                  <td colspan="3" align="right" class="form-hdr">Display Read Stats:</td>
                  <td class="form-data"><input onClick="ClearSearch(this)" type="checkbox" <? if ($ReadStats) echo "checked"; ?> value="1" name="ReadStats"></td>
                </tr>
                <tr>
                  <td colspan="3" align="right" class="form-hdr">Having Keyword: </td>
                  <td class="form-data"><? DBField("Articles","Keyword_Search",$Keyword); ?></td>
                </tr>
                <tr>
                  <td colspan="3" align="right" class="form-hdr">Containing Notes of type: </td>
                  <td class="form-data"><? DBField("ArticleNotes","NoteType",$NoteType,0,1); ?></td>
                </tr>
                <tr>
                  <td colspan="3" align="right" class="form-hdr">Created by:</td>
                  <td class="form-data"><? DBField("Articles","CREATEDBY",$CREATEDBY); ?> (Account ID)</td>
                </tr>
                <tr>
                  <td colspan="3" align="right" class="form-hdr">Contact:</td>
                  <td class="form-data"><? DBField("Articles","Contact1_S",$Contact1); ?> (Account ID)                </td>
                </tr>
				
                <tr>
                  <td colspan="3" align="right" class="form-hdr">Needing Review By:</td>
                  <td class="form-hdr2"><? DBField("Articles","ReviewBy",$ReviewBy); ?></td>
                </tr>
                <tr>
                  <td colspan="3" align="right" class="form-hdr">Modified Since: </td>
                  <td class="form-hdr2"><? DBField("Articles","Modified_Since",$Modified_Since); ?> On/Before: <? DBField("Articles","Modified_Before",$Modified_Before); ?></td>
                </tr>
				
                <tr>
                  <td colspan="3" align="right" class="form-hdr">Reviewed Since: </td>
                  <td nowrap class="form-hdr2"><? DBField("Articles","Reviewed_Since",$Reviewed_Since); ?> On/Before: 
                  <? DBField("Articles","Reviewed_Before",$Reviewed_Before); ?></td>
                </tr>
				
				<? if ($AppDB->Settings->Custom2Label) { ?>

                <tr>
                  <td colspan="3" align="right" class="form-hdr"><? echo $AppDB->Settings->Custom2Label ?>: </td>
                  <td nowrap class="form-hdr2"><? DBField("Articles","Custom2_Since",$Custom2_Since); ?> On/Before: 
                  <? DBField("Articles","Custom2_Before",$Custom2_Before); ?></td>
                </tr>				
				
				<? } ?>
                <tr>
                  <td colspan="3" align="right" class="form-hdr">&nbsp;</td>
                  <td nowrap class="form-hdr2">&nbsp;</td>
                </tr>
                <tr>
				<td align="right" colspan=4 class="form-data"> <input type="submit" VALUE="Search" NAME="S">
				  <input onClick="Javascript:window.location='admin_article.php';" type="button" VALUE="Add" NAME="Add">
				  <input onClick="Javascript:window.location='admin.php'" type="button" VALUE="Back" NAME="Back">
				  <?  HelpButton()  ?>                </td>
              </tr>
             </table>
	</div></div>
<?
}
?>
</form> 
</center>
</body>
</html>