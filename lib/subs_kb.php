<?

/*
 * Very specific to KB functions
 */
//DEFINE("ISDEMO",1);  // If its going to expire.

//
// INITIALIZE
//
$AppDB = new DB(DBHOST,DBTYPE,DBUSER,DBPASS,DBNAME);
if (!$AppDB || $AppDB->failed) {
	exit;
}
$AppDB->Settings = $AppDB->GetRecordFromQuery("select * from Settings where ID=1");
// In Case no settings:
if ($AppDB->Settings->ID == "") {
	$AppDB->Settings->AppName = "Knowledge Base";
	$AppDB->Settings->AuthenticationMode = "Local";
	$AppDB->Settings->PrivMode = "Group";
	$AppDB->Settings->FiltersOnHomePage = false;
}
if ($AppDB->Settings->MaxUploadSize < 1) $AppDB->Settings->MaxUploadSize = 5;

$CUser = new CurrentUser($SimulateID,"AutoCreateUser"); // pages must set global $noauth = 1, for world readable pages.

function AutoCreateUser($u)
{
	//
	// If we can pull more information about the User from external source and save in local Users table on first create.
	//
	global $AppDB;
	if ($u) {
		// Only used when using NT Authentication and ALLOW_GUESTS is true to create a record on the fly.
		
		if (defined("LDAP_GROUP_PREFIX")) {
			AutoCreateFromAD($u);
		}
		
	}
}

/**
 * 
 */
function AutoCreateFromAD($u)
{
	global $AppDB;

	/**
	 * Get Users AD Record
	 */
	
	if (!LDAPGetUser($u->Username,$Rec)) return;
	if (!$Rec) return;
	
	$SETS = array();
	
	if ($u->Email == "") $SETS["Email"] = $Rec['mail'];
	if ($u->Phone == "") $SETS["Phone"] = $Rec['telephonenumber'];
	if ($u->LastName == $u->FirstName) {
		$SETS["LastName"] = $Rec['sn'];
		$SETS["FirstName"] = $Rec['givenname'];
	}
	$SETS["Priv"] = PRIV_GROUP;
	$SETS['Groups'] = $u->Groups;
		
	/**
	 * Get users AD Groups applicable to KB
	 */
	$Perms = array("Write" => "W","Read" => "R", "Approver"=>"A");
	$PermsR = array("W"=>"_Write","R"=>"_Read","A"=>"_Approver");
	
	$ADGroupsPre = LDAPGetUsersGroups($u->Username,LDAP_GROUP_PREFIX);
	
	
	/**
	 * First Check any KB groups in KB, that user is a member of, but is no
	 * longer a member of in AD.
	 */
	$AllADGroups = LDAPGetGroups(LDAP_GROUP_PREFIX . "*",LDAP_GROUP_PREFIX);
	//error_log("AllADGroups = " . print_r($AllADGroups,1));
	$CurrentGroups = GroupStrToArray($SETS['Groups']);
	//error_log("CurrentGroups = " . print_r($CurrentGroups,1));
		
	if (is_array($AllADGroups) && count($AllADGroups) > 0) {
		foreach($CurrentGroups as $GID => $Perm) {
			$GrpRec = $AppDB->GetRecordFromQuery("select * from Groups where GroupID = '$GID'");
			if ($GrpRec) {
				$ADGrpName = $GrpRec->Name . $PermsR[$Perm]; 
				$ADGrpName2 = $GrpRec->Name . ' ' . $PermsR[$Perm]; 
				//error_log("Checking for $ADGrpName,$ADGrpName2");
				if ( (in_array($ADGrpName,$AllADGroups) || in_array($ADGrpName2,$AllADGroups)) 
  				    && (!in_array($ADGrpName,$ADGroupsPre) && !in_array($ADGrpName2,$ADGroupsPre)) ) {
					// no longer in group 
					$DelGrps[] = $GrpRec->GroupID;
					error_log("Removing user $u->Username from Group $GrpRec->Name as no longer in AD Group");
				}
			} else {
				error_log("Warning: Group $GID not found, removing from User $u->Username");
				$DelGrps[] = $GID;
			}
		}
	}
	if ($DelGrps) {
		foreach($DelGrps as $GID) {
			$GroupList = GroupStrToArray($SETS['Groups'],1);
			$SETS['Groups'] = GroupArrayToStr($GroupList,$GID,"",1);				
		}
	}
	
	/**
	 * Resolve Group name and Permission from the ADGroupsPre array
	 */
	$ADGroups = array();
	// $ADGroupsPre = GroupName_Write or GroupName_Read etc.
	foreach($ADGroupsPre as $ADGroup) {	
		$tpos = strrpos($ADGroup,'_');
		$Perm = trim(substr((string)$ADGroup,$tpos+1));
		$GroupName = trim(substr((string)$ADGroup,0,$tpos));
		$CPerm = $ADGroups[$GroupName];
		// Dont overwrite a higher priv with a lower one in the cases
		// when use is in multiple perm groups of same group name.
		if ($CPerm) { // already found this group
			if ($Perm == "Read") continue;
			if ($Perm == "Write" && $CPerm == "Approver") continue;
		}
		$ADGroups[$GroupName] = $Perm;	
	}
	/**
	 * If user was a member of some AD Groups then
	 * make sure they exist in KB profile
	 */
	if (count($ADGroups) > 0) {
		$CurrentGroups = GroupStrToArray($SETS['Groups']);
		if ($SETS['Groups'] != '') $comma = ",";
		foreach($ADGroups as $GroupName => $Perm) {
			$GrpRec = $AppDB->GetRecordFromQuery("select * from Groups where Name = '$GroupName'");
			if (!$GrpRec) {
				/**
				 * AD Group found that does not exist in KB, so trigger ADSync here
				 */
				error_log("Could not find GroupName $GroupName during ADGroup Check for client logon");
				error_log("Starting AD Sync to resolve");
				global $CUser;
				$CUser->UserID = strtolower($u->Username);
				AD_Group_Sync($msg);
				error_log($msg);
				$GrpRec = $AppDB->GetRecordFromQuery("select * from Groups where Name = '$GroupName'");
				if (!$GrpRec) continue;
			}
			$GID = $GrpRec->GroupID;
			$PermVal = $Perms[$Perm];
			
			if ($CurrentGroups[$GID]) continue; //already a member.
			error_log("ADUserSync: Add User " . $u->Username . " to group $GroupName");
			$SETS["Groups"] .= $comma . "$GID" . ":$PermVal:Y";
			$comma = ",";
			$SETS["Priv"] = PRIV_GROUP;
		}
	}	
	$AppDB->update_record($u->ID,USERS_TABLE,$SETS,DB_NOAUDIT_UPDATE);
}

function db_err($sql,$errno,$errmsg,$info="")
{
	global $AppDB;
	if ($errno == 7619) return;
	
	echo '
	<html>
	<head>
	<title>Error</title>
	<link rel="stylesheet" href="styles.css" type="text/css" >
	</head>
	<body><center>';

	echo("</table></table></table>");
	
	$Frame = new FrameBox("<font color=red>Error</font>","450");
	$Frame->Display();
	echo("<b><br>We are sorry, an unexpected Error has occurred.");
	if ($info)
		echo "<br>($info)";
	echo "</b></br></br><blockquote>$errno: " . nl2br($errmsg) . "</blockquote>";
		
	$Notify = $AppDB->Settings->NotifyEmail;
	
	if ($Notify == "" && defined(NOTIFY_ON_ERROR)) $Notify = NOTIFY_ON_ERROR;
	
	echo('Please contact KB Support at <a href="mailto:' . $Notify . '">' . $Notify . '</a> to report this problem.<br><br>');
	$Frame->DisplayEnd();
	$mailmsg = "$errmsg \n\n<br>";
	if ($sql) $mailmsg .= "<pre>Query: $sql\n<br></pre>";
	if ($Notify) send_error($mailmsg);
		
	exit();
}

function send_error($errmsg)
{
	global $AppDB;
	global $CUser;

	$Notify = $AppDB->Settings->NotifyEmail;
	
	if ($Notify == "") $Notify = NOTIFY_ON_ERROR;
	
	if ($Notify == "") 
		return;
	
	$from = $CUser->u->Email;
	if ($from == "") $from = $Notify;
	
	$mailmsg = $errmsg;
	$mailmsg .= "\n\nBacktrace:\n---------\n\n<pre>";
	$mailmsg .= get_backtrace(0);
	$mailmsg .= print_r($CUser,1);
	$mailmsg .= "</pre>";
	
	if (strtolower($CUser->u->Email) != strtolower($Notify)) {
		$res = send_mail(array($Notify),'KB Error',$mailmsg,$mailmsg,$from);
		if ($res <= 0) {
			echo '</td></tr></table></center><p align="left"><pre>' . $mailmsg .'</pre>';		
		}
	}
	else {
		echo '</td></tr></table></center><p align="left"><pre>' . $mailmsg .'</pre>';		
	}
}

function send_mail($To,$Subject,$HtmlMsg,$TextMsg,$from,$Bcc = "")
{
	global $AppDB;

	
	if ($from == "") $from = $To[0];

	ini_set("sendmail_from","$from");
	$SMTP = $AppDB->Settings->SMTPServer;
	if ($SMTP == "") $SMTP = DEFAULT_SMTP_SERVER;
	ini_set("SMTP",$SMTP);
	ini_set("smtp_port","25");

    $mail = new htmlMimeMail();
	$mail->setSMTPParams($SMTP, 25);
	if ($HtmlMsg) 
		$mail->setHtml($HtmlMsg,$TextMsg);
	else 
		$mail->setText($TextMsg);
		
	$mail->setReturnPath($from);
	$mail->setFrom($from);
	$mail->setSubject($Subject);

	//if (is_array($To) && count($To) == 0) $mail->setheader ... set fake to TODO: ?		
	// All notifications are really sent as Bcc's
	$num = 0;
	if (is_array($Bcc)) {
		 // Fix from Sam...
         foreach($Bcc as $element) {
            $bcc[]=$element;
         }
         $Bcc=$bcc;
		 $num = count($Bcc); 
	}
	$ChunkSize = 40;
	$NumSent = 0;
	
	/*
	 * TODO: Will produce an error as 2nd chunk has no valid recipients
	 *
	$ChunkSize = 2;
	$Bcc = array();
	$Bcc[] = "sdrew@shaw.ca";
	$Bcc[] = "sdrew@softperfection.com";
	$num = 3;
	*/
	
	// If more than chunksize Bcc recips split into seperate emails
	// $num maybe 0 here if we are using the To:
	if ($num >= $ChunkSize) {
		$ToChunk = array();
		for($i = 0; $i < $num; ++$i) {
			$ToChunk[] = $Bcc[$i];
			if (($i + 1) >= $num || (($i + 1) % $ChunkSize) == 0) {
				$mail->setBcc(implode(', ',$ToChunk));

				$res = $mail->send($To,'smtp');
				if (!$res) {
					echo "Warning Notification EMail failed. Server: " . $AppDB->Settings->SMTPServer;
					echo "<br><blockquote>";
					foreach($mail->errors as $err) {
						echo htmlspecialchars((string)$err);
						echo "<br>";
					}
					echo "</blockquote>";
					
					//print_r($mail->errors);
					//print_r($ToChunk);
					//print_r($To);
					//print_r($Bcc);
					//echo "</pre><br>";
				}
				else {
					$NumSent += count($ToChunk);
					$NumSent += count($To);
				}
				$ToChunk = array();
				$To = array();
			}
		}
	} else {
		// either no Bcc's or less than chunksize so send all in one email.
		if (is_array($Bcc)) $mail->setBcc(implode(', ', $Bcc));
		$res = $mail->send($To,'smtp');
		if (!$res) {
			echo "Warning Notification EMail Failed. Server: " . $AppDB->Settings->SMTPServer;
			echo "<br><pre>";
			error_log("Email Failed: " . print_r($mail->errors,1));
			print_r($mail->errors);
			print_r($To);
			print_r($Bcc);
			echo "</pre><br>";
			return -1;
		}
		if (is_array($To)) $NumSent += count($To);
		if (is_array($Bcc)) $NumSent += count($Bcc);
	}	
	return $NumSent;
}

function DisplayContentSection($Name,$Vars = "")
{
	global $AppDB;
	$CS = $AppDB->GetRecordFromQuery("Select * from ContentSections where SectionName='$Name'");
	if ($CS) {
		if (is_array($Vars)) {
			foreach($Vars as $Key => $Value) {
				$CS->Content = str_replace("$Key",$Value,$CS->Content);
			}
		}
		echo "<span title=\"$CS->SectionName Content updated by $CS->LASTMODIFIEDBY on $CS->LASTMODIFIED\">".$CS->Content."</span>\n";
	}
}

function SetContentSection($SectionName,$Content)
{
	global $AppDB;
	$RS = $AppDB->sql("select * from ContentSections where SectionName='$SectionName'");
	if ($RS) $S = $AppDB->sql_fetch_obj($RS);
    Logger("S = ".print_r($S,1));
	$SETS["Content"] = $Content;
	$SETS["SectionName"] = $SectionName;
	if ($S) {
		$AppDB->update_record($S->ID,'ContentSections',$SETS);
	} else {
		$AppDB->insert_record("ContentSections",$SETS);
	}
}



//
// PrivFilter query arguments when selecting articles
//
function PrivFilter($MyGroupsOnly=false) // added MyGroupsOnly for benefit of home page left col links
{
	global $CUser;
	global $AppDB;
	
    $q = '';
	$Priv = $CUser->Priv();
	if ($Priv == "") 
		$Priv = PRIV_GUEST;

	if ($AppDB->Settings->PrivMode == "Simple") {
		if ($Priv != PRIV_ADMIN) {
			$q = " and (ViewableBy <= $Priv OR Articles.CREATEDBY = '" . $CUser->UserID . "')";
		}
	}
	else {
		// if Non member of Administrators group then filter by group
		if ($Priv != PRIV_ADMIN || $MyGroupsOnly) {
			if ($CUser->u->GroupIDs != "") {
				// If Pulic or 
				//			MyPriv = Support AND In my group and Viewable by <= Support 
				//			MyPriv = Editor  AND In my group and Viewable by <= Editors
				//
				// Care about A and W/R
				$q = " and ( ";
				if (!$MyGroupsOnly) $q .= " ViewableBy=" . PRIV_GUEST .  " OR ";
				$q .= " (Articles.CREATEDBY = '" . $CUser->UserID . "') ";
				if ($CUser->u->GroupIDs_A) 
					// added Articles.
					$q .=  	" OR (Articles.GroupID in (" . $CUser->u->GroupIDs_A . ") AND ViewableBy <= " . PRIV_APPROVER . ")  " ;
				if ($CUser->u->GroupIDs) 
				    $q .= 	" OR (Articles.GroupID in (" . $CUser->u->GroupIDs . ") AND ViewableBy <= " . PRIV_GROUP . ") ";
				$q .= ")";
			}
			else {
				$q = " and ViewableBy=" . PRIV_GUEST . " ";
			}
		}	
	}
	//echo $q;	
	return $q;
}

function fmt_kb($ID) {
	return sprintf("KB%06d",(int)$ID);
}

// Returns HTML selection list for groups that the current user may select
function GroupDropList($Current="",$param="",$JavaTise=0,$AddChoice="(All Groups)")
{
	global $CUser;
	global $AppDB;
	
	if ($AppDB->Settings->PrivMode == "Simple") {
		echo "(All)";
		return;
	}
	ob_start();
	if ($CUser->IsPriv(PRIV_ADMIN)) {
		$q = "select * from Groups where Status = 'Active' order by Name";		
	}
	else if ($CUser->u->GroupIDs) {
		$q = "select * from Groups where Status = 'Active' AND GroupID in (" . $CUser->u->GroupIDs . ") order by Name";
	}
	else $q = "select * from Groups where 1=0";
	
	dropdownlistfromquery("GroupID",$AppDB,$q,$Current,$AddChoice,$param,"Name","GroupID");
	$html = ob_get_contents();
	ob_end_clean();
	if ($JavaTise) {
		$html = str_replace("'",'\'',$html);
		$html = str_replace("\n","' + \n '",$html);
	}
	echo $html;
}

function IsNoiseWord($w)
{
	//cut/pasted from SQLSERVER\FTDATA\Config\noise.enu
 	$NoiseWords = '	
about
after
all
also
an
and
another
any
are
as
at
be
$
because
been
before
being
between
both
but
by
came
can
come
could
did
do
does
each
for
from
get
got
has
had
he
have
her
here
him
himself
his
how
if
in
into
is
it
like
make
many
me
might
more
most
much
must
my
never
now
of
on
only
or
other
our
out
over
said
same
see
should
since
some
still
such
take
than
that
the
their
them
then
there
these
they
this
those
through
to
too
under
up
very
was
way
we
well
were
what
where
which
while
who
with
would
you
your
';
	if (strlen(trim((string)$w)) == 1) return 1;
	
	$w = strtolower($w);
	if (strstr($NoiseWords,"$w\r"))
		return 1;
	return 0;
}

function TitleFormat($Title,$ViewableBy)
{
	global $AppDB;
	
	$T = $Title;
	if ($AppDB->Settings->IndicatePrivateArticle && $ViewableBy > 1) {
		$T .= " <img src=\"images/lock.gif\" align=\"bottom\" title=\"Not Publicly viewable\" border=\"0\">";
	}
	return $T;
}



function CheckIfKBNumber($Search,$Loc="article.php")
{
 	$ID = "";
	$Search = trim((string)$Search);
 	if (strtoupper(substr((string)$Search,0,2)) == "KB") {
 		$ID = (int)substr((string)$Search,2);
		if ($ID > 0 && $ID < 1000000 && strlen((string)$ID) < 7) {
			header("location:" . $Loc . "?ID=$ID");
			exit;
		}
	}
}

/*
-- If using $ShowOnlyOnce = 1 then...
-- By having the min(_AttachmentID) and the max(_Filename) for records that match
-- we conclude:
--		If MatchType is 0 then matched on article its self.
-- 		If MatchType not 0, then matched on article only
--		If FileName and MatchType not 0 then only matched on attachment.
--	

-- Bahaviour:

	$Merged - Always set to enable the Merging of the hits from the Articles and Attachments. If cleared then you will get 
	two records if Attachment as content record matches and Attachment say via title. 
	So Currently Merging is always on.

	$ShowOnlyOnce, if true then group by Arrticle ID after merging Attachment hits and Article hits to only show 1 match per article
	otherwise, shows 1 Match for the Article and one for each attachment that matches, except the Attachment as Content which is 
	grouped with the Article match by using the pID psuedo field that is a combination of Article ID + Attachment ID (attachment ID is removed
	if the Attachment is As Content making it group with the Article ID and preenting duplicate records.
			

*/
function BuildSearchQuery($Search,$QSub,$SMethod,$ShowOnlyOnce=0)
{
	$Merge = 1;
	// Remove quotes if not balanced
	$qc = substr_count($Search,'"');
	if ($qc % 2 != 0) {
		$Search = str_replace('"','',$Search);		
	}
    $WordMethod = 0;

    $Clause = '';
	$Words = array();
	if ($SMethod == "CONTAINSTABLE") {
		$sa = search_split_terms($Search);
		$Search_s = "";
		foreach($sa as $phrase) {
			$phrase = trim((string)$phrase);
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
		else if (count($Words) == 0) {
			echo "<font color=red><b>&nbsp;You entered an invalid search string</b></font>";			
			return "";
		}
		else if (count($Words) < 20) { // dont join more than 20 tables
			$WordMethod = 1;								
		}
		else $Search = "\"$Search_s\"";

	}
	// SQL 2000 and above using ContainsTAble method only matches if 
	// each word searched for appears in same fulltext indexed column.
	// Only way around that is multiple ContainsTable(... calls and joins
	// This code creates those multiple statements based on number of words/phrases in query.
    $RankT = '';
    $plus = '';
    $QAttach2 = '';
    $QArt2 = '';
	if ($WordMethod) {
		$i = 1;
		foreach($Words as $Word) {
			$RankT .= " $plus KT$i" . ".Rank ";
			$plus = "+";
            $QAttach2 .= "\n\t\tinner join $SMethod(ArticleAttachments, *,'$Word'," . MAXROWS . ") as KT$i ON  ArticleAttachments.ID=KT$i.[KEY] ";
        	$QArt2 .= "\n\t\tinner join $SMethod(Articles, *,'$Word'," . MAXROWS . ") as KT$i ON Articles.ID=KT$i.[KEY] ";
			++$i;
		}
	} else {
		$RankT = " KT1.Rank ";
        $QAttach2 .= "\n\t\tinner join $SMethod(ArticleAttachments, *,'$Search'," . MAXROWS . ") as KT1 ON  ArticleAttachments.ID=KT1.[KEY] ";
        $QArt2 .= "\n\t\tinner join $SMethod(Articles, *,'$Search'," . MAXROWS . ") as KT1 ON Articles.ID=KT1.[KEY] ";
	}
	
	// Can do away with ShowOnylOnce merged query as although it causes the article to only display once
	// articles with many attachments loose as only displays one attachment match, so this method is good
	// for Admin searches when looking for articles to manage vs search results when you want a hit on each
	// article.

	if ($ShowOnlyOnce) {	
		$GroupByField = "ID";

//		$SelectParam = "Merged.ID as ID,
//				 		max(Merged.pID) as ID,
		$SelectParam = "Merged.ID as ID,
					";
	} else {
		$GroupByField = "pID";
		
		$SelectParam = "max(Merged.ID) as ID,
						Merged.pID as pID,
					";
	}

	$query = '

select Results.*,Results.ID as RID,
	A.STATUS as AStatus,
	A.Type,
	A.Product,
	A.Title,
	A.ViewableBy,
	Results.Modified as LModified,
	Results.Rank + (A.Hits / 50) as RankAndHits,
	A.LastReviewed,
	Groups.Name as GroupName,
       cast(cast(A.Content as nvarchar(800)) as nvarchar) as Content 

   from ( ';
   
   
	if ($Merge) { 
	
		$query .= '
    select 
	' . $SelectParam . '
	max(Merged.Modified) as Modified,
	max(Art.Hits) as Hits,
        max(Merged.Filename) as Filename,
	min(Merged.AttachmentID) as MatchType,
	max(Merged.AttachmentID) as AttachmentID,
	max(Merged.Rank) as Rank,
	max(Merged.AsContent) as AsContent

      from ( ';
	 
	}
	
	$query .= '  

       select 
	
		Articles.ID as ID,
       	ArticleAttachments.Filename as Filename,
      	ArticleAttachments.ID as AttachmentID,
		ArticleAttachments.CREATED as Modified,
		cast(Articles.ID as varchar(10)) + \'-\' + cast( (1 - ISNULL(AsContent,0)) * ArticleAttachments.ID as varchar(10)) as pID,
		' . $RankT . '
		
		as Rank,
       	ArticleAttachments.AsContent

        from Articles 
               inner join ArticleAttachments on Articles.ID = ArticleAttachments.ArticleID 
			   ' . $QAttach2 . '
			   ' . $QSub . '
			   
        union all 

	   select 

		Articles.ID as ID,  
		\'\' as Filename,
		0 as AttachmentID,
		Articles.LASTMODIFIED as Modified,
		--(Articles.ID * 1000) as pID,
		cast(Articles.ID as varchar(10)) + \'-\' +  cast(0 as varchar(10)) as pID,
		 
        ' . $RankT . '
		
		as Rank, 
	
		\'\' as AsContent
        
		from Articles 
			   ' . $QArt2 . '				
			   ' . $QSub . '';
			   
	  if ($Merge) {
	  
	  	$query .= '
	
	  ) as Merged

		left join Articles as Art on Merged.ID=Art.ID

    	where 1=1 

		group by Merged.' . $GroupByField . '
		';
	  }
	 
	 $query .= '
	   
    ) as Results

    left join Articles as A on Results.ID=A.ID
    left join Groups on A.GroupID = Groups.GroupID';

	return $query;
}
	
function search_split_terms($terms)
{

	$terms = preg_replace("/\"(.*?)\"/e", "search_transform_term('\$1')", $terms);
	$terms = preg_split("/\s+|,/", $terms);

	$out = array();

	foreach($terms as $term){

		$term = preg_replace("/\{WHITESPACE-([0-9]+)\}/e", "chr(\$1)", $term);
		$term = preg_replace("/\{COMMA\}/", ",", $term);

		$out[] = $term;
	}

	return $out;
}

function search_transform_term($term)
{
	$term = preg_replace("/(\s)/e", "'{WHITESPACE-'.ord('\$1').'}'", $term);
	$term = preg_replace("/,/", "{COMMA}", $term);
	return $term;
}	

//
// Called by subs_library show notes to check for permissions on notes
//
function AllowEditNote($Table,$R,$KeyField)
{
	global $CUser;
	global $AppDB;
	
	if ($Table == "ArticleNotes" && $R->ArticleID > 0) {
		$KB = $AppDB->GetRecordFromQuery("select GroupID from Articles where ID=$R->ArticleID");
		if (!$KB) return -1;
		if ($CUser->u->Priv != PRIV_ADMIN && 
			$CUser->u->GroupArray[$KB->GroupID] != "A" && 
			$CUser->u->GroupArray[$KB->GroupID] != "W" && // New allow Write users to update notes.
			$R->CREATEDBY != getusername()) {
			
			return -1;
			
		}
		else {
			return $R->ID;
		}
	}
	return -1;
}

function IsArticleReadableByUser($KB,$U)
{
	$GroupID = $KB->GroupID;
	$GroupArray = GroupStrToArray($U->Groups);

	if ($GroupArray[1] == "A" || 
	    $GroupArray[$GroupID] == "A" || 
	   ($GroupArray[$GroupID] != "" && $KB->ViewableBy <= PRIV_SUPPORT)) {
	   
	   return true;
	}
	return false;
}

/*
 * Review KB Updated
 * Find all users that are members of this group 
 * Returns number of Receipients that were sent this notification
 */
function SendNotifications($ID,$Type)
{
	global $AppDB;
	$Table = USERS_TABLE;
	global $CUser;
    $To = [];
    
	if (!$ID) return;
	
	$KB = $AppDB->GetRecordFromQuery("select * from Articles where ID=$ID");

	$GroupID = $KB->GroupID;
	
    
	// If  article is modified send notifications if ContentLastModified is at least 4 hours old.
		
	// Find all users for this group with Notification enabled
	//
	// NotifyUpdated,NotifyNew,NotifySubmitted, NotifyTechnicalReview,NotifyContentReview,
	// --------------------------
	if ($KB->STATUS == "Active" || $Type == "NotifyTechnicalReview" || $Type == "NotifyContentReview") {	
	
		if ($KB->STATUS != "Active") $GF = "A"; // need approve priv in group to be notified about the review required
		else $GF = '';
        
		$q = "select * from $Table where Email is not NULL AND $Type = 'Yes'";
		$q .= " and ($Table.Groups like '1:%' OR $Table.Groups like '%,1:%' OR $Table.Groups like '$GroupID:$GF%' OR $Table.Groups like '%,$GroupID:$GF%') ";
		if ($Type == "NotifyUpdated") 
			$q .= " and ('$KB->ContentLastModified' = '' OR DATEDIFF(hour,'$KB->ContentLastModified',getdate()) > 4)";
		$res = $AppDB->sql($q);

		while($R = $AppDB->sql_fetch_obj($res)) {
			// If Article is Readable by user then email notification.
			if (IsArticleReadableByUser($KB,$R)) {
				$To[] = $R->Email;
			}
		}
	}
	
	// For any status, Notify those that have elected to be notified of articles modified that they had Submitted or Reviewed
	// NotifySubmitted (any status, last 4 hours only once)
	$q = "select * from $Table where Email is not NULL AND NotifySubmitted = 'Yes'";
	$q .= " and $Table.Priv >= $KB->ViewableBy ";
	$q .= " and ($Table.Username = '$KB->CREATEDBY' OR $Table.Username = '$KB->LastReviewedBy' OR $Table.Username = '$KB->LASTMODIFIEDBY' OR $Table.Username = '$KB->Contact1' OR $Table.Username = '$KB->Contact2') ";
	$q .= " and ('$KB->ContentLastModified' = '' OR DATEDIFF(hour,'$KB->ContentLastModified',getdate()) > 4)";	
	$res = $AppDB->sql($q);
	while($R = $AppDB->sql_fetch_obj($res)) {
		$To[] = $R->Email;
	}

		
	if (is_array($To) && count($To) > 0) {
		$To = array_unique($To);
		$from = $CUser->u->Email;
		if ($KB->GroupID)
			$G = $AppDB->GetRecordFromQuery("select * from Groups where GroupID=$KB->GroupID");

	  	$GroupName =  $G->Name;
		
		$Subjectstr = "KB Article Updated: ";
		if ($Type == 'NotifyNew') $Subjectstr = "KB Article Added: ";
		else if ($Type == 'NotifyTechnicalReview') $Subjectstr = "KB Technical Review required: ";
		else if ($Type == 'NotifyContentReview') $Subjectstr = "KB Content Review required: ";	
			
			
		foreach($KB as $Key => $Value) {
			$Vars[$Key] = $Value;
		}	
		$Vars['GroupName'] = $GroupName;
		$Vars['ID'] = fmt_kb($ID);
		$Vars['SITE_URL'] = SITE_URL;
		
		$Template = new template();
		$Template->assign($Vars);
		$HtmlMsg = $Template->render("EmailTemplates/$Type.tpl");

		if (!$HtmlMsg) return '';
		
		$TextMsg = HTMLToReadableText($HtmlMsg);
								
		$Num = send_mail(array(),$Subjectstr . "KB". $KB->ID . " - $KB->Title",$HtmlMsg,$TextMsg,$from,$To);
		
		if ($Num > 0) {
			$AFields['Trail'] = "Notification type $Type sent to $Num Recipients";
			$AFields['ArticleID'] = $ID;
			AuditTrail("Notification",$AFields);	
			return("<br>Email Notification sent to $Num Recipients");
		} 
	}
	return '';	
}


function SendNoteToAuthors($ID,$NoteID)
{
	global $AppDB;
	$Table = USERS_TABLE;
	global $CUser;
	$msg = "";
	
	if (!$ID || !$NoteID) return;
	
	$KB = $AppDB->GetRecordFromQuery("select ID,Title,CREATEDBY,LastReviewedBy,LASTMODIFIEDBY from Articles where ID=$ID");
	$NR = $AppDB->GetRecordFromQuery("select * from ArticleNotes where ID=$NoteID");
	
	if (!$NR || !$KB) return;
	
	$q = "select * from $Table where Email is not NULL AND NotifySubmitted = 'Yes'";
	$q .= " and ($Table.Username = '$KB->CREATEDBY' OR $Table.Username = '$KB->LastReviewedBy' OR $Table.Username = '$KB->LASTMODIFIEDBY' OR $Table.Username = '$KB->Contact1' OR $Table.Username = '$KB->Contact2') ";
	$res = $AppDB->sql($q);
	while($R = $AppDB->sql_fetch_obj($res)) {
		$To[] = $R->Email;
	}		
	if (is_array($To) && count($To) > 0) {
		$from = $CUser->u->Email;
		$To = array_unique($To);

		$Vars['ID'] = $KB->ID;
		$Vars['SITE_URL'] = SITE_URL;
		$Vars['NoteType'] = $NR->NoteType;
		$Vars['Notes'] = $NR->Notes;
		$Vars['Title'] = $KB->Title;
		
		$Template = new template();
		$Template->assign($Vars);
		$HtmlMsg = $Template->render("EmailTemplates/NotifyNotes.tpl");
		
		if ($HtmlMsg) {
			$TextMsg = HTMLToReadableText($HtmlMsg);
			$Num = send_mail($To,"Note added to Article: $ID",$HtmlMsg,$TextMsg,$from);	
		}
		if ($Num > 0) {		
			$msg = "Note sent by Email to: " . implode(", ", $To);
			$AFields['Trail'] = $msg;
			$AFields['ArticleID'] = $ID;
			AuditTrail("Notification",$AFields);	
		}
		else {
			$msg = "Email notification Failed";
			error_log("Error sending email note for article $ID");
		}		
	 } else {
		$msg = "No Recipients found (or recipients profiles have this notification type disabled). No email was sent.";
	}
	return $msg;
}

function SendNoteToContacts($ID,$NoteID)
{
	global $AppDB;
	$Table = USERS_TABLE;
	global $CUser;
	$msg = "";
	
	if (!$ID || !$NoteID) return;
	
	$KB = $AppDB->GetRecordFromQuery("select ID,Title,CREATEDBY,LastReviewedBy,LASTMODIFIEDBY,Contact1,Contact2 from Articles where ID=$ID");
	$NR = $AppDB->GetRecordFromQuery("select * from ArticleNotes where ID=$NoteID");
	
	if (!$NR || !$KB) return;
	
	$q = "select * from $Table where Email is not NULL AND NotifySubmitted = 'Yes'";
	$q .= " and ($Table.Username = '$KB->Contact1' OR $Table.Username = '$KB->Contact2') ";
	$res = $AppDB->sql($q);
	while($R = $AppDB->sql_fetch_obj($res)) {
		$To[] = $R->Email;
	}		
	if (is_array($To) && count($To) > 0) {
		$from = $CUser->u->Email;
		$To = array_unique($To);

		$Vars['ID'] = $KB->ID;
		$Vars['SITE_URL'] = SITE_URL;
		$Vars['NoteType'] = $NR->NoteType;
		$Vars['Notes'] = $NR->Notes;
		$Vars['Title'] = $KB->Title;
		
		$Template = new template();
		$Template->assign($Vars);
		$HtmlMsg = $Template->render("EmailTemplates/NotifyNotes.tpl");
		
		if ($HtmlMsg) {
			$TextMsg = HTMLToReadableText($HtmlMsg);
			$Num = send_mail($To,"Note added to Article: $ID",$HtmlMsg,$TextMsg,$from);	
		}
		if ($Num > 0) {		
			$msg = "Note sent by Email to: " . implode(", ", $To);
			$AFields['Trail'] = $msg;
			$AFields['ArticleID'] = $ID;
			AuditTrail("Notification",$AFields);	
		}
		else {
			$msg = "Email notification Failed";
			error_log("Error sending email note for article $ID");
		}		
	 } else {
		$msg = "No Recipients found (or recipients profiles have this notification type disabled). No email was sent.";
	}
	return $msg;
}

/*
  When updating the content of Article or if deleting an attachment:
	- Create/copy to new record in ArchiveArticles
	- Copy Attachments to ArchiveAttachments
	- ID's in ArchiveArticle (float) = n.v and not auto incr
	- ArticleID's in ArchiveAttachments (float) = n.v 
	- Then remove > MaxVersions.
	
	For attachments as they post each alteration:
		- If delete attachment - make archive
		- If adding attachment - do nothing as can always delete it. 
*/
function CreateArchiveRecord($OldRec, $bNoPurge=0)
{
	global $AppDB;
	$Versions = $AppDB->Settings->ArticleVersions;
	if ($Versions == "") $Versions = 5;
	if ($Versions > 999) $Versions = 999;
	
	// Create Archive Record
	foreach($OldRec as $K => $V) {
		if ($K == "Keywords") continue; // does not exist on archive records (prob should though).
		$NR[$K] = $V;
	}

	$ID = $NR["ID"];
	$LR = $AppDB->GetRecordFromQuery("select top 1 ID from ArchiveArticles where ID >= $ID AND ID < ($ID + 1) order by ID desc");
	if (isset($LR->ID)) $AID = $LR->ID + .0001;
	else $AID = .0001 + $ID; 
	$NR["ID"] = $AID;
	@list($garb,$version) = explode('.',$AID,2);
	// Replace src="files/KB000008" with src="files/$AID" so as to point to archived copies
	$OLDPATH = FILES_FOLDER . fmt_kb($ID);
	$NEWPATH = $OLDPATH . sprintf(".%04d",$version);
	$NR["Content"] = str_replace("src=\"$OLDPATH","src=\"$NEWPATH",(string)$NR["Content"]);
	
	$AppDB->insert_record("ArchiveArticles",$NR,0);

	// Copy Attachments
	$AttRS = $AppDB->sql("select * from ArticleAttachments where ArticleID = $ID");
	while($Att = $AppDB->sql_fetch_array($AttRS)) {
		$Att["ArchiveArticleID"] = $AID;
		$Attachment = $Att["Attachment"];
		unset($Att["Attachment"]);
		unset($Att['ArticleID']);
		$AttID = $AppDB->insert_record("ArchiveArticleAttachments",$Att);
		if ($AttID) $AppDB->UpdateBlob("ArchiveArticleAttachments","Attachment",$Attachment,"ID=$AttID");
	}
	// Copy files folder
	deep_copy(APP_ROOT_DIR . "$OLDPATH", APP_ROOT_DIR . "$NEWPATH");
	
	if ($bNoPurge == 0) {
		// Only keep newest n Versions
		$res = $AppDB->sql("select top $Versions ID from ArchiveArticles where ID >= $ID AND ID < ($ID + 1) order by ID desc");
		$LowestVersion = $AID;
		while($R = $AppDB->sql_fetch_obj($res)) {
			$LowestVersion = min($AID,$R->ID);
		}
		$res = $AppDB->sql("select ID from ArchiveArticles where ID >= $ID AND ID < $LowestVersion order by ID desc");
		while($R = $AppDB->sql_fetch_obj($res)) {
			DeleteArticleVersion($R->ID);
		}
	}
	return($AID);
}

/*****
 */
function RestoreArticleVersion($ID,$OldRec,$AID)
{
	global $AppDB;
	// First Save current
	CreateArchiveRecord($OldRec,1);
	
	$res = $AppDB->sql("select * from ArchiveArticles where ID = $AID");
	if ($res) {
		$R = $AppDB->sql_fetch_array($res);
		if ($R) {
			$chk = array_shift($R);
			if ($chk != $AID) {
				echo "Unexpected table format: $chk";
				exit;
			}
			// Replace src="files/$AID" with src="files/KB00008" so as to point to archived copies
			list($ids,$version) = explode('.',$AID,2);	
			// Replace src="files/KB000008" with src="files/$AID" so as to point to archived copies
			$OLDPATH = FILES_FOLDER . fmt_kb($ID);
			$NEWPATH = $OLDPATH . sprintf(".%04d",$version);
			$R["Content"] = str_replace("src=\"$NEWPATH","src=\"$OLDPATH",$R["Content"]);
		
			$AppDB->update_record($ID,'Articles',$R);
			// cleanup current folder and copy version contents to active.
			rmdirr(APP_ROOT_DIR . $OLDPATH);
			deep_copy(APP_ROOT_DIR . $NEWPATH, APP_ROOT_DIR . $OLDPATH);
			
			$AFields['ArticleID'] = $ID;
			$AFields['Trail'] = "Article restored from version $AID";
			AuditTrail("ArticleRestored",$AFields);

			// Copy attachments back
			$AppDB->sql("delete from ArticleAttachments where ArticleID = $ID");
			$AttRS = $AppDB->sql("select * from ArchiveArticleAttachments where ArchiveArticleID = $AID");
			while($Att = $AppDB->sql_fetch_array($AttRS)) {
				$Att["ArticleID"] = $ID;
				$Attachment = $Att["Attachment"];
				$Att["Attachment"] = "null";
				$AttID = $AppDB->insert_record("ArticleAttachments",$Att);
				if ($AttID) $AppDB->UpdateBlob("ArticleAttachments","Attachment",$Attachment,"ID=$AttID");
			}
			
			return 1;
		}
	}
	return 0;
}

function DeleteArticleVersion($AID)
{
	global $AppDB;
	list($ids,$version) = explode('.',$AID,2);	
	if ($ids && $version) {
		$RemovePath = APP_ROOT_DIR . sprintf(FILES_FOLDER . "KB%06d.%04d",$ids,$version);
		rmdirr($RemovePath);
		$AppDB->sql($q = "delete from ArchiveArticles where ID=$AID");
		$AppDB->sql($q = "delete from ArchiveArticleAttachments where ArchiveArticleID=$AID");
		return 1;
	}
	return 0;
}


function SubjectStr($str,$ID)
{
	return("<a href='Javascript:DisplayMessage($ID); void(0);' title=\"Click to view message\">$str</a>\n");
}

function MessageIcon($type="")
{
	$bb_icons = array();
	$bb_icons["MUI-Open"] = "i_bb_red.gif";
	$bb_icons["MUI-Closed"] = "i_bb_green.gif";
	$bb_icons["Advisory"] = "i_bb_yellow.gif";
	$bb_icons["Information"] = "i_bb.jpg";

	return("<img  align=top height=16 src=\"images/" . $bb_icons[$type] ."\" border=0>");

}

//
//  This ensures we always qualify the query even if ID specified for security purposes.
//
function MessageQuery($ID = "",$ShowAll = 0, $bDateReadInfo = 0)
{
	global $CUser;
	global $AppDB;
	$Table = "Messages";
	global $AppDB;
	$q2 = '';
    $DateReadSubQuery = '';
	$duration = " convert(varchar,datediff(day,StartTime,EndTime)) + ' ' + convert(varchar,endTime-StartTime,108) as Duration, ";
	if ($bDateReadInfo) $DateReadSubQuery = "(select top 1 CREATED from MessageHits where MessageHits.MessageID=Messages.ID AND MessageHits.CREATEDBY='$CUser->UserID' order by MessageHits.CREATED desc) as DateRead, "; 

	if ($AppDB->Settings->PrivMode == "Simple") {
		$q = "select Messages.CREATED as CREATED,Messages.STATUS as STATUS, $DateReadSubQuery $duration Messages.ID as ID from $Table where 1=1 ";
	} else { 
		if (!$CUser->IsPriv(PRIV_ADMIN))  {
			$q2  = ' AND (Messages.GroupID is NULL ';
			$q2 .= " OR Messages.GroupID in ( " . $CUser->u->GroupIDs . " ))";
		}			
		$q = "Select Messages.*,Messages.CREATED as Date,Messages.STATUS as STATUS, $DateReadSubQuery $duration Messages.ID as ID,Groups.Name as GroupName from $Table left join Groups on $Table.GroupID = Groups.GroupID where 1=1 $q2 ";
	}
	
	if ($ShowAll == 0) {
		$q .= " AND (DisplayUntil is NULL or GetDate() <= DisplayUntil)  AND $Table.STATUS = 'Visible' ";
	}
	if ($ID) {
		$q .= " AND $Table.ID=$ID";
	}
	return $q;
}

//////////////////////////////////////////////////
// APP specific
//
function print_icons_nonreport($url)
{
	echo '<a target="_blank" href="' . $url . '&printview=1">
	      <img alt="Click to open a printable view of this Page" border="0" src="images/icon_print.gif" width="28" height="24" align="right"></a>';
	echo '<a title="Send This Page by Email" target="_blank" onclick="alert(\'' .
		 'From the new Printable View Window of this Page, select the File->Send->Page By email...\n\n' .
		 'This new window will open after you click OK.\');" href="' . $url . '&printview=1&Email=1"  >' .
	     '<img align="right" border="0" src="images/icon_email.gif" width="26" height="24" ></a>';
}		



function print_icons($purl,$export=1)
{
	$purl = stripslashes($purl);
	echo '
        <a title="Open a Printable view of this report"  target="_blank" href="' . $purl . '"  > 
        <img border="0" src="images/icon_print.gif" width="28" height="24"></a>
        
        <a title="Send Report by Email" target="_blank" onclick="alert(\'From the new Printable View Window of this report, select the File->Send->Page By email...\n\nThis new window will open after you click OK.\');" href="' . $purl . '&Email=1"  >
        <img border="0" src="images/icon_email.gif" width="26" height="24" ></a>
        
   		<a title="Save a copy of the Report to a file" onclick="alert(\'To save the complete report:\n\n1. From the new window that will open choose File->SaveAs and enter a file name\n2. Change the Save-As-Type to Web Archive single file (*.mht) file.\n\nThis format will place all information from this report in a single file. You may also email this report file as you would any other attachment.\');" target="_blank" href="' . $purl . '&Save=1"  >
        <img border="0" src="images/icon_save.gif" width="22" height="24" ></a>';
    
    if ($export)
      echo '
   		<a title="Export the selected Projects to a csv file." href="' . $purl . '&export=1"  >
        <img border="0" src="images/icon_export4.gif" width="26" height="24" ></a>';
    
    HelpIcon();
}				        

// Create entry in Audit Trail and call AuditAction routine to do any other steps 
// AuditAction is optional and contained in lib/CustomActions.php
//
function AuditTrail($Action,$AFields) 
{
	global $AppDB;
	if (!empty($AFields['ArticleID'])) $AppDB->insert_record("AuditTrail",$AFields);
	AuditAction($Action,$AFields);
}

/**
 * 
 * @param string $msg
 * @param string $level
 * @param string $trace
 * @param boolean $includePost
 */
function Logger($msg,$level='LOG',$trace='',$includePost=true)
{

	if ($level != 'LOG' && $level != 'PHP') {
        $ErrMsg = $level.": " . $msg;
        if ($trace) {
            $ErrMsg .= "\n$trace";
        } else {
     		$e = new Exception;
        	$ErrMsg .= "\n" . $e->getTraceAsString();
        }
	}
    else {
		$e = new Exception;
        $a =$e->getTrace();
        $ErrMsg = $level.": " . substr((string)$a[0]['file'],-20).':'.$a[0]['line'] . " " . $msg;
    }
    
    error_log($ErrMsg);

}
