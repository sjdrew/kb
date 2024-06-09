<?
/*******************************************************************************
*
* File: 	article.php
* Purpose: 	displays article, in page or in frames  
* Version:  1.2   
* Date:     2004-09-01  
* Author:   Steve Drew (sdrew@softperfection.com)
*
*
* This page is quite complex has it handles both standard html viewing of articles
* and 3 frame view of a attachment as content article.
*
***********************/

 include("config.php"); 
 nocache();
 $ID = GetVar("ID");
 if ($ID == "") { header("location:home.php"); exit; }
 
// Initialize
	list($ID,$Version) = explode('.',$ID,2);
	if (substr($ID,0,2) == "KB") $ID = (int)substr($ID,2);
	$ID = (int)$ID;
	$IDstr = sprintf("KB%06d",$ID);
	$IDBase = $ID;
	$Archive = 0;
	$Version = substr($Version,0,4);
	$Vercheck = (int)$Version;
	if ($Vercheck > 0) {
		$ID = "$ID.$Version";
		$Table = "ArchiveArticles";
		$AttTable = "ArchiveArticle";
		$Archive = 1;
		$AttIDColumn = "ArchiveArticleID";
	}
	else {
		$Version = '';
		$Table = "Articles";
		$AttTable = "Article";
		$AttIDColumn = "ArticleID";
	}
 ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Article <? echo $ID ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link REL="stylesheet" HREF="styles.css">
</link>
</head>
<?
	// If No content, but have attachments then act as frameset
	if (!$Frame) {
		$AttR = $AppDB->GetRecordFromQuery("select ID,Filename from $AttTable" . "Attachments where $AttIDColumn='$ID' and AsContent=1");
		if ($AttR) {
			$Frame = 1;
			$AttachmentID=$AttR->ID;
			$AttachmentExt=strtolower(substr($AttR->Filename,-3));
		}
	}

	// Trap article loading into a frame that is not framed.
	if (!$Frame && $frm == "") {
		echo '
<script language="javascript">
	if (parent.document.frames && parent.document.frames.length == 3) {
		var _hdr = "";
		if (window.parent.location.href.indexOf("nohdr=1") != -1) _hdr = "&nohdr=1";
		window.parent.location.href = window.location.href + _hdr;
	}
	else if (parent.document.frames && parent.document.frames.length == 4) {
		if (window.location.href.indexOf("nohdr=1") == -1) {
			window.location.href = window.location.href + "&nohdr=1";
		}
	}		
</script>
';
	}
	if ($Frame && $frm == "") {
		$urlbot = "show_attachment.php?Type=$AttTable&ID=$AttachmentID&frm=b&nohdr=$nohdr&Mode=$Mode";
		$topspace = ($nohdr) ? 60 : 135; 
		// jscript below prevents us from being double framed, ie if you click on a link within
		// a embeded word document that references a kb article link.
		echo '
<script language="javascript">
	if (parent.document.frames && parent.document.frames.length == 3) {
		window.top.location = window.location;
	}
</script>
<frameset rows="'.$topspace.',*" frameborder="NO" border="0" framespacing="0">
  <frame src="'. $PHP_SELF . "?Frame=1&ID=$ID&nohdr=$nohdr" . "&frm=t" .'" name="topFrame" scrolling="No"  noresize >
  <frameset cols="*,200" frameborder="NO" border="0" framespacing="0">
';
    //ensure that articles with attachment as content do not reveal the content unless user is authorized to see it
	$pf = PrivFilter();
	$R = $AppDB->GetRecordFromQuery("select ID from $Table as Articles where ID=$ID $pf"); 
	if(!$R) exit;
	echo '
	  <frame src="'. $urlbot .'" name="leftFrame" >
	  <frame src="'. $PHP_SELF . "?Frame=1&ID=$ID&AttID=$AttachmentID&Ext=$AttachmentExt&nohdr=$nohdr" . "&frm=r" .'"name="rightFrame" scrolling="No"  noresize >
  </frameset>
</frameset>';
		exit;
	} 
	if ($Frame) {
		if ($frm == "t") {
			$ShowTop = 1;
		}
		if ($frm == "b") {
			$ShowBot = 1;
		}
		if ($frm == "r") {
			$ShowRight = 1;
		}
	} 
	else {
		$ShowRight = $ShowTop = $ShowBot = 1;
	}
?>
<body>
<script language="JavaScript" src="lib/misc.js"></script>
<? if ($ShowTop ) { ?>
<? if (!$nohdr) include("header.php"); ?>
<? } // Show Top ?>
<div class="ArticleDiv">
	<? 
		$pf = PrivFilter();
		$R = $AppDB->GetRecordFromQuery("select * from $Table as Articles where ID=$ID $pf"); 
		
	  	// If in Frame because of AsContent flag, then edit url will take us to top level window to edit
	  	$edit_url = "href=\"admin_article.php?ID=$R->ID";
		if ($Frame) {
			if ($nohdr) $edit_url .= "&nohdr=$nohdr\" target=text ";
			else $edit_url .= "\" target=_top ";
		}
		// Else not framed in article, but maybe via browse so pass nohdr flag
		else {
			$edit_url .= "&nohdr=$nohdr\" ";
		}
		
	   	if ($R) {

			// Dont log hit if Admin and DontLogAdmin is set, except always log if MustRead is Yes
	   		if (!$Archive &&  (!$CUser->IsPriv(PRIV_ADMIN) || !$AppDB->Settings->DontLogAdmin || $R->MustRead == "Yes") ) {	
				$HFields[ArticleID] = $ID;
				
				// If we just read it in past 30 minutes then
				// just update the hit record, rather than add a new one.
				// This prevents extra records from users doing browser refreshes.
				$MyLastHit = $AppDB->GetRecordFromQuery(
					"select top 1 * from Hits where ArticleID=$ID and CREATEDBY='$CUser->UserID' AND " .
					"DATEDIFF(minute,CREATED,getdate()) < 30 order by CREATED desc");

				if ($MyLastHit) {
					$HFields[CREATED] = "GetDate()";
					$AppDB->update_record($MyLastHit->ID,'Hits',$HFields,DB_NOAUDIT_UPDATE);
				}
				else {
					if ($R->Hits == "") $R->Hits = 0;
		   			$AppDB->sql("update Articles set Hits=$R->Hits + 1,LastHitBy='" . $_SERVER["REMOTE_ADDR"] . "' where ID=$ID");
					$AppDB->insert_record("Hits",$HFields);
					$R->Hits++;
				}
				
				// Occssionally (randomly) cleanup Hits table
				if (rand(1,20) == 5) {
					$NDays = $AppDB->Settings->HitsHistoryDays;
					if ($NDays == "") $NDays = 400;
					$AppDB->sql("delete from Hits where DATEDIFF(day,CREATED,GetDate()) >= $NDays","",0);					
				}
			}

			if ($AppDB->Settings->PrivMode == "GROUP") {
				// If not world readable article or we are not Admin then 
				if ($R->ViewableBy != PRIV_GUEST || $CUser->u->Priv != PRIV_ADMIN) {			
					// Make sure we have group membership to this article to view
					if ($CUser->u->GroupArray[$R->GroupID] == "A" ||
						$CUser->u->GroupArray[$R->GroupID] != "" && $R->ViewableBy <= PRIV_SUPPORT) {
							ShowMsgBox("You do not have the correct permission or Group membership <br>required to view article $IDstr " .
								'<input type=button name="Continue" value="Continue" onclick="window.location=\'home.php\'">',"center");
						exit;
					}
				}
			} else {
				if ($R->ViewableBy > $CUser->u->Priv) {
					ShowMsgBox("You do not have the correct permission required to view article $IDstr " .
							'<input type=button name="Continue" value="Continue" onclick="window.location=\'home.php\'">',"center");
					exit;				
				}
			}

	   	} else {
	   		ShowMsgBox("Article $ID not found. (Or no access to specified article).","center");
			exit;		
	   	}
		
	?>
  <? if ($ShowRight) { 
 		AuditTrail("ArticleRead",array('ID' => (int)$ID));

		if ($AttID) $DivScroll = "height: 150px; overflow:auto;";
  		// If AttID is set, then we are displaying Content attachment in frame "AsContent"
  ?>
  <div id="ArticleAttachments" style="padding-bottom:6px; <? echo $DivScroll; ?>">
      <table class="ArticleInfo" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <th colspan="2">Attachments</th>
        </tr>
        <tr>
          <td><? if (DisplayAttachments("$AttTable",$ID,0,0,0,"AND AsContent is null") == 0) echo "<p align=center>(none)</p>"; ?>
          </td>
        </tr>
      </table>
  </div>
  <div id="ArticleDetails"  >
    <table cellpadding="0" class="ArticleInfo" cellspacing="0" border="0">
      <tr>
        <th colspan="2">Article Details</th>
      </tr>
	  <? if ($CUser->u->Priv >= PRIV_APPROVER) { ?>
      <tr>
        <td align="right"> Version:</td>
        <td nowrap><? 
		$V = $Version;
		if ($V == "") {
			echo "Active "; 
			$pID = $ID + 0.9999;
		}
		else {
			$iV = (int)$Version;
			echo "<font color=red>$iV</font> ";
			$pID = $ID;
			$Tmp = $AppDB->GetRecordFromQuery("select top 1 ID from ArchiveArticles where ID > $ID and ID < ($IDBase + 1) order by ID");
			$NextArchivedID = $Tmp->ID;
		}
		$Tmp = $AppDB->GetRecordFromQuery("select top 1 ID from ArchiveArticles where ID < $pID and ID > $IDBase order by ID desc");
		$PrevArchivedID = $Tmp->ID;
		
		if ($Frame) $target = "target=_top";
		if ($nohdr && $Frame) $target = "target=text";
		
		if ($PrevArchivedID)
	  		echo "| <a $target title=\"View previous version of Article\" href=\"article.php?ID=$PrevArchivedID&nohdr=$nohdr\">&lt;&lt;Prev</a> ";
		if ($NextArchivedID)
			echo "| <a $target title=\"View next version of Article\" href=\"article.php?ID=$NextArchivedID&nohdr=$nohdr\">Next&gt&gt</a> ";
		else if ($Version)
			echo "| <a $target title=\"View Active version of this Article\" href=\"article.php?ID=$IDBase&nohdr=$nohdr\">Active&gt&gt</a>";
		?></td>
      </tr>
	  <? } ?>
      <tr>
        <td align="right">Group:</td>
        <td><? 
	  	$G = $AppDB->GetRecordFromQuery("select * from Groups where GroupID=$R->GroupID");
		
	  echo $G->Name?></td>
      </tr>
      <tr>
        <td align="right">Product:</td>
        <td><? echo "<a target=_top href=\"search.php?Advanced=1&S=Search&Product=" .urlencode($R->Product) . "\" title=\"click to view all Articles for this product\" > " .
		      $R->Product . "</a>"; ?></td>
      </tr>
      <tr>
        <td align="right">Type:</td>
        <td><? echo $R->Type ?></td>
      </tr>
      <tr>
        <td align="right">Hits:</td>
        <td><? 
	  	if (!$Archive && $CUser->IsPriv(PRIV_ADMIN)) { // TODO: probably should be PRIV_APPROVER
		  echo "<a target=_top href=\"report_active_articles.php?S=1&ID=$ID&nohdr=$nohdr\" title=\"Click to view Hits\">$R->Hits</a>"; 
		}  else  echo $R->Hits;
	  ?></td>
      </tr>
	<?
	   // TODO, could display this for all users but only link for approvers and higher
	   // but wait and see load factor as this is extensive query.	  
  	  if (!$Archive && $CUser->u->Priv >= PRIV_APPROVER) { ?>
      <tr>
        <td align="right">Read Stat:</td>
        <td nowrap>
	   <? 
	$rqRead = 'select (select count(distinct Username)
	 from users 
     left join Hits on (Hits.ArticleID = Articles.ID and Hits.CREATEDBY = Username and Hits.CREATED > Articles.ContentLastModified)
	    where  
       (users.Groups like \'1:%\' OR users.Groups like \'%,1:%\' OR users.Groups like cast(Articles.GroupID as varchar(10)) + \':%\' OR users.Groups like \'%,\' +  cast(Articles.GroupID as varchar(10)) + \':%\')
		  	and Hits.ArticleID is  not null) as NumRead from Articles where ID='. $ID;
			
	$rqUnRead = 'select (select count(distinct Username)
	 from users 
     left join Hits on (Hits.ArticleID = Articles.ID and Hits.CREATEDBY = Username and Hits.CREATED > Articles.ContentLastModified)
	    where  
       (users.Groups like \'1:%\' OR users.Groups like \'%,1:%\' OR users.Groups like cast(Articles.GroupID as varchar(10)) + \':%\' OR users.Groups like \'%,\' +  cast(Articles.GroupID as varchar(10)) + \':%\')
		  	and Hits.ArticleID is null) as UnRead from Articles where ID='. $ID;
	$ResR = $AppDB->GetRecordFromQuery($rqRead);
	$ResUR = $AppDB->GetRecordFromQuery($rqUnRead);
	$ReadNum = $ResR->NumRead;
	$UnReadNum = $ResUR->UnRead;
	if ($ReadNum > 0) {
		$ReadPercent = (int)(($ReadNum / ($ReadNum + $UnReadNum)) * 100);
		$linep = (int)($ReadPercent * .70);
		$ReadPercent .= "%";
		$rstr = "$ReadNum of " . ($ReadNum + $UnReadNum) . " people have read this article since it was last modified.";
		$ReadPercent = '<span style="line-height:.5; width:'. $linep .'%; background-color: green">&nbsp;</span>&nbsp;' . $ReadPercent;
	} else $ReadPercent = "0%";
	    
		echo "<a target=_top href=\"report_article_read_status.php?ID=$ID&nohdr=$nohdr\" title=\"$rstr Click for details\">$ReadPercent</a>"; 
	  ?></td>
      </tr>
	<? } ?>
	  
      <tr>
        <td align="right">Created:</td>
        <td><? echo substr($R->CREATED,0,10); ?> <? echo "by $R->CREATEDBY" ?></td>
      </tr>
      <tr>
        <td align="right">Updated:</td>
        <td><? echo substr($R->ContentLastModified,0,10) . " by $R->LASTMODIFIEDBY"; ?></td>
      </tr>
      <tr>
        <td align="right">Expires:</td>
        <td><? echo $R->Expires ?></td>
      </tr>
      <tr>
        <td align="right">Reviewed:</td>
        <td><? echo substr($R->LastReviewed,0,10); ?>
        <? if ($R->LastReviewed) echo "by $R->LastReviewedBy" ?></td>
      </tr>
      <tr>
        <td align="right" nowrap>Review By:</td>
        <td><? echo substr($R->ReviewBy,0,10); ?></td>
      </tr>
      <tr>
        <td align="right">Contact:</td>
        <td><? echo $R->Contact1 ?></td>
      </tr>
	  <tr>
	  	<td align="right">Notes:</td>
		<td><?		
		$NC = $AppDB->count_of("select count(*) from ArticleNotes where ArticleID=$ID");
		if ($NC > 0) {
			$s = ($NC == 1) ? "" : "s";
			echo "<a style=\"font-size:11px; font-weight:bold\" href=\"javascript:void(0);\"" .
					" onclick=\"dialog_window('show_notes.php?ArticleID=$ID',640,460,'resizable=yes','shownote');\"" .
					" title=\"Click to view notes\">$NC note$s</a>";
		}
		?></td>
	  </tr>
      <tr>
	  <td colspan=2 align="center" style="border-top: 1px solid #ccc">
     <? if (!$Archive && ($CUser->u->Priv == PRIV_ADMIN ||
		   $CUser->u->GroupArray[$R->GroupID] == "A" ||
		   $CUser->u->GroupArray[$R->GroupID] == "W")) {
	  			
			echo "<a " . $edit_url . ' ><img align="bottom" src=images/i_pencil.gif height="12" width="12" border=0>edit article</a>';
		}
	    echo " <a title=\"Post a note on this Article\" href=\"javascript:void(0);\" onclick=\"javascript:EditNote($ID,0,'ArticleNotes','ArticleID');\">" .
				"<img align=\"top\" border=0 src=\"images/i_notesm.gif\"> Add Note</a>"; ?>
	  </td>
      </tr>
      </table>
	  <? if ($AttID) {
		 // Work around for IE embeding excel file
		 // It cant seem to launch an external excel window and have an imbedded object in IE
		 // So if xls file, close it and reopen in new
		 echo '<div style="font-size:12px; width:180px; margin-top:15px; margin-left:5px;"><img border="0" align="absmiddle" src="images/arrow_out.gif">
		 ';
		 if ($Ext == "xls" || $Ext == "csv")
		  	echo '<a href="#" title="Open Attachment Content in Application" onclick="openInApp()">Open Content in Application</a>';
		 else		
		 	echo "<a href=\"show_attachment.php?Mode=attachment&Type=Article&ID=$AttID\" title=\"Open Attachment Content in Application\">Open Content in Application</a>\n";
		
		 echo "</div>\n";

		 }
		 
 	  ?>
    </div>
	<script language="javascript">
	function openInApp()
	{
		parent.document.frames[1].location="article_doc_opened.php";
		window.location="<? echo "show_attachment.php?Mode=attachment&Type=Article&ID=$AttID" ?>";
	}
	</script>
<? } // show Right ?>
<? if ($ShowTop) { ?>
    <p class="ArticleTitle">Article: <? 
	  echo $IDstr;
	if ($Version != "") {
		echo "  <font color=red>[Archived Version $Version] </font>";
	}
	if ($R->STATUS == "Obsolete") echo " <font color=red><b>(This Article is Obsolete)</b></font> ";
	if ($CUser->u->Priv == PRIV_ADMIN || $CUser->u->GroupArray[$R->GroupID] == "A" || $CUser->u->GroupArray[$R->GroupID] == "W") {
		if (!$Archive)
		  	echo "&nbsp;<a title=\"Edit Article\" $edit_url ><img src=images/i_pencil.gif height=\"12\" width=\"12\" border=0></a>";
	}
	?>
    <hr><p class="ArticleTitle"><? echo $R->Title ?></p>
<? } // ShowTop ?>
<? if ($ShowBot) { ?>
	<script language="javascript" type="text/jscript">var KBIDstr = '<? echo $IDstr; ?>';</script>
	<div class="ArticleBody"><? 	
	echo $R->Content; 
	$RRes = $AppDB->sql("select Related.*,Articles.Title from Related inner join Articles on Related.IDB=Articles.ID where IDA=$ID $pf order by Related.IDB");
	$first = 1;
	 while($RRec = $AppDB->sql_fetch_obj($RRes)) {
		if ($first) {
			echo '<hr><table cellspacing="0" cellpadding="1" border=0><tr><th align=left colspan="2">See Also:<br></th></tr>';
			$first = 0;
		}
		$RKB = sprintf("KB%06d",$RRec->IDB);
		echo "<tr><td align=right>$RKB</td><td style=\"text-align:left\" ><a href=\"article.php?ID=$RRec->IDB&nohdr=$nohdr\" title=\"Click to view article\">$RRec->Title</a></td></tr>\n";
	}
	if (!$first) echo "</table>";
	?><br>
	<? DisplayContentSection("Article Footer"); ?>
	</div>
<? } // ShowBot?>
</div>
</body>
</html>
