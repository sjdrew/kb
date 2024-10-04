<?php
/*
 * File: admin_article.php
 * Purpose: Creating/Editing of Articles
 */
include("config.php");
header('Content-Type: text/html; charset=utf-8');
RequirePriv(PRIV_GROUP);
$ID = GetVar("ID");

if (substr((string)$ID, 0, 2) == "KB") $ID = (int)substr((string)$ID, 2);
set_time_limit(180);

function ProcessSave($ID, $rdonly, &$msg, &$Err, $ModifyAll = 0)
{
    global $AppDB;
    global $CUser;

    $ArchiveID = 0;


    $OldRec = (object)[];

    if ($ID) $OldRec = $AppDB->GetRecordFromQuery("select * from Articles where ID=$ID");

    // Permissions check again here in case of ModifyAll
    if ($AppDB->Settings->PrivMode == "Group" && $ID) {
        if (
            $CUser->u->Priv != PRIV_ADMIN &&
            $CUser->u->GroupArray[$OldRec->GroupID] != "W" &&
            $CUser->u->GroupArray[$OldRec->GroupID] != "A"
        ) {

            $msg .= "No write access to Article $ID";
            return 0;
        }
        if (
            $ID && isset($CUser->u->GroupArray[$OldRec->GroupID]) && $CUser->u->GroupArray[$OldRec->GroupID] == "W" && !$AppDB->Settings->AllowModifyArticles &&
            $OldRec->STATUS == "Active"
        ) {

            $msg .= "You are not permitted to modify Active Articles.";
            return 0;
        }
    }

    if (GetVar('delete_attachment')) {
        $DAR = $AppDB->GetRecordFromQuery("select ID,Filename from ArticleAttachments where ID=?", [GetVar('delete_attachment')]);
        if ($DAR) {
            // Save everything before we delete this 
            $ArchiveID = CreateArchiveRecord($OldRec);
            $AppDB->sql("delete from ArticleAttachments where ID=?", [GetVar('delete_attachment')]);
            $AFields['ArticleID'] = $ID;
            $AFields['Trail'] = "Attachment " . $DAR->Filename . " deleted";
            AuditTrail("DeleteAttachment", $AFields);
            $msg .= "Attachment deleted";
            unset($AFields);
            $_POST['LASTMODIFIED'] = 1; // Force update
        }
    }

    if (GetVar('RemoveAID')) {
        if (DeleteArticleVersion(GetVar('RemoveAID')))
            $msg .= "Version $_POST[RemoveAID] deleted.";
        return $ID;
    }

    if (GetVar('RestoreAID')) {
        if (RestoreArticleVersion($ID, $OldRec, GetVar('RestoreAID')))
            $msg .= "Version " . GetVar('RestoreAID') . " Restored as Current.";
        return $ID;
    }

    if ($ModifyAll == 0 && ParseFields("Articles", $msg) != 0) {
        return $ID;
    }

    if ($AppDB->Settings->PrivMode == "Group") {
        $OldGroupID = isset($OldRec->GroupID) ? $OldRec->GroupID : null;

        // check that this User has A or W priv in new Group
        if ($OldGroupID != GetVar('GroupID')) {
            if (
                $CUser->u->Priv != PRIV_ADMIN &&
                $CUser->u->GroupArray[GetVar('GroupID')] != "W" &&
                $CUser->u->GroupArray[GetVar('GroupID')] != "A"
            ) {

                $msg = "You do not have write permissions to the specified Group.";
                $Err = 1;
                return $ID;
            }
            //
            // If we moved to another group that we had Write access to but not Approval for
            // then set to pending approval
            //
            if (
                $OldGroupID != "" &&
                GetVar('STATUS') == "Active" &&
                $CUser->u->Priv != PRIV_ADMIN &&
                $CUser->u->GroupArray[GetVar('GroupID')] != "A"
            ) {

                $_POST['STATUS'] = "Pending Technical Review";
                $_POST['ViewableBy'] = PRIV_GROUP;

                $msg = "Warning: Since you do not have Approval Privilage for the selected group, the Status has been " .
                    "set to Pending Technical Review.";
            }
        }

        if (GetVar('STATUS') == "Active" && $OldRec->STATUS != "Active") {
            if (GetVar('LastReviewed') == "" && $CUser->u->Priv != PRIV_ADMIN) {
                $msg = "Article $ID must be Reviewed by pressing the 'Mark Reviewed' button, before being set Active.";
                $Err = 1;
                $_POST['STATUS'] = $OldRec->STATUS;
                return $ID;
            }
            if ($CUser->u->Priv != PRIV_ADMIN && $CUser->u->GroupArray[GetVar('GroupID')] != "A") {
                $msg = "You do not have Approval Privilage and thefore may not move the Status to Active.";
                $Err = 1;
                return $ID;
            }
        }

        //
        // If its not approved you can move it to public. Once Status is active only an approver can
        // change the viewable by from something to public
        //
        if (!$Err) {
            if (
                GetVar('ViewableBy') == PRIV_GUEST && $OldRec->ViewableBy != PRIV_GUEST &&
                GetVar('STATUS') == "Active" &&
                $CUser->u->Priv != PRIV_ADMIN /* && 
				$CUser->u->GroupArray[$_POST[GroupID]] != "A" */
            ) {

                $msg = "Admin Privilage is required to enable Public viewing of this article once it has been " .
                    "set Active.";

                $Err = 1;
                return $ID;
            }
        }
    } else { // In Simple permission mode

        if (
            GetVar('STATUS') == "Active" && $OldRec->STATUS != "Active" &&
            $CUser->u->Priv != PRIV_ADMIN &&
            $CUser->u->Priv != PRIV_APPROVER
        ) {

            $msg = "You do not have Approval Privilage and thefore may not move the Status to Active.";
            $Err = 1;
            return $ID;
        }
        if (
            GetVar('ViewableBy') == PRIV_GUEST && $OldRec->ViewableBy != PRIV_GUEST &&
            GetVar('STATUS') == "Active" &&
            $CUser->u->Priv != PRIV_ADMIN
        ) {

            $msg = "Approval Privilage is required to enable Public viewing of this article once it has been " .
                "set Active.";

            $Err = 1;
            return $ID;
        }
    }

    if ($ID) {
        // 
        // Dont update the LastModified date if LastModifyLock is true
        //
        $LockModifiedDate = $ModifyAll;
        if ($AppDB->Settings->LastModifyLock) $LockModifiedDate = 1;
        $ModFields = $AppDB->modify_form($ID, "Articles", 0, $LockModifiedDate);
        if (is_array($ModFields) && count($ModFields) > 0) {
            if ($msg == "" && $ModifyAll == 0) $msg = "Changes were saved.";
            //sort($ModFields);
            $nChanged = 0;
            $AFields = [];
            $AFields['Trail'] = '';
            foreach ($ModFields as $MField => $OldValue) {
                if (trim((string)$OldValue) === trim((string)$_POST[$MField]))
                    continue;
                if ($MField == "LASTMODIFIED") continue;
                if ($MField == "Content") {
                    if (!$LockModifiedDate) {
                        // See if content is really modified (ignore html formatting, spaces and newlines)
                        $OldValue = str_replace("\n", "", htmltotext(trim((string)$OldValue)));
                        $OldValue = str_replace("\r", "", $OldValue);
                        $OldValue = str_replace(" ", "", $OldValue);
                        $NewValue = str_replace("\n", "", htmltotext(trim((string)$_POST[$MField])));
                        $NewValue = str_replace("\r", "", $NewValue);
                        $NewValue = str_replace(" ", "", $NewValue);
                        if ($OldValue == $NewValue)
                            continue;
                        $AFields['Trail'] .= "Article content modified<br>";
                        // Send notifications of Article content changes, 
                        // but not if via Mark Reviewed button (do below)
                        if (!trim((string)GetVar('Reviewed'))) {
                            if ($OldRec->STATUS != "Obsolete")
                                $msg .= SendNotifications($ID, "NotifyUpdated");
                        }
                        $LMSETS["ContentLastModified"] = "GetDate()";
                        $AppDB->update_record($ID, 'Articles', $LMSETS);
                        if (!$ArchiveID) $ArchiveID = CreateArchiveRecord($OldRec);
                    }
                } else if ($MField == "GroupID") {
                    $OGroup = $AppDB->GetRecordFromQuery("select Name from Groups where GroupID = " . (int)$OldValue);
                    $NGroup = $AppDB->GetRecordFromQuery("select Name from Groups where GroupID = " . (int)GetVar('GroupID'));
                    $AFields['Trail'] .= "Group changed from $OGroup->Name to $NGroup->Name<br>";
                } else if ($MField == "ViewableBy") {
                    $VB = array("Public", "Group Members", "Editors", "Administrators");
                    $OVB = $VB[$OldValue];
                    $NVB = $VB[$_POST[$MField]];
                    $AFields['Trail'] .= "Viewable By changed from $OVB to $NVB<br>";
                } else {
                    $MFieldName = $MField;
                    if ($MField == "Custom1") $MFieldName = $AppDB->Settings->Custom1Label;
                    else if ($MField == "Custom2") $MFieldName = $AppDB->Settings->Custom2Label;
                    $AFields['Trail'] .= "$MFieldName changed from \"$OldValue\" to \"$_POST[$MField]\"<br>\n";
                }
                ++$nChanged;
            }
            if ($nChanged) {
                $AFields['ArticleID'] = $ID;
                AuditTrail("ArticleModified", $AFields);
            }
        }
    } else {
        $_POST["ContentLastModified"] = NowDateTime();
        $ID = $AppDB->save_form("Articles");
        $msg = "New Article Created.";
        $AFields['Trail'] = "Article Created";
        $AFields['ArticleID'] = $ID;
        AuditTrail("ArticleCreated", $AFields);
    }

    if ($ID) {

        if (GetVar('STATUS') == "Active" && $OldRec->STATUS != "Active") {
            $msg .= SendNotifications($ID, "NotifyNew");
        }
        if (GetVar('STATUS') == "Pending Technical Review" && $OldRec->STATUS != "Pending Technical Review") {
            $msg .= SendNotifications($ID, "NotifyTechnicalReview");
        }
        if (GetVar('STATUS') == "Pending Content Review" && $OldRec->STATUS != "Pending Content Review") {
            $msg .= SendNotifications($ID, "NotifyContentReview");
        }
    }
    return $ID;
}

function ProcessDelete($ID, &$msg, $Multi = 0)
{
    global $AppDB;
    global $CUser;
    if ($ID) {
        // Must have Admin or Approver priv for current group or be the CREATOR of the Article
        $Rec = $AppDB->GetRecordFromQuery("select * from Articles where ID=$ID");
        if (
            $CUser->u->Priv != PRIV_ADMIN &&
            $CUser->u->GroupArray[$Rec->GroupID] != "A" &&
            !stristr($CUser->UserID, $Rec->CREATEDBY)
        ) {
            if ($msg) $msg .= "<br>";
            $msg .= "You do not have the correct privileges to delete Article $ID";
            return 0;
        }

        $STATUS = $Rec->STATUS;
        if ($STATUS != "Obsolete") {
            $_POST['STATUS'] = "Obsolete";
            $Err = "";
            ProcessSave($ID, 0, $msg, $Err, $Multi);
            if ($msg) $msg .= "<br>";
            $msg .= "Article $ID has been Marked Obsolete. Press Delete again to physically Remove.";
            return 0;
        } else {
            $AFields['ArticleID'] = $ID;
            $AFields['Trail'] = "Article deleted by " . $CUser->u->FirstName . " " . $CUser->u->LastName;
            AuditTrail("ArticleDeleted", $AFields);

            $AppDB->sql("delete from Articles where ID = $ID");
            $AppDB->sql("delete from ArticleAttachments where ArticleID = $ID");
            $AppDB->sql("delete from Related where IDA = $ID OR IDB = $ID");
            return 1;
        }
    }
    return 0;
}


$Reviewed = trim((string)GetVar('Reviewed'));
$Now = Now();
$AttachmentAsContent = '';
$nohdr = GetVar('nohdr');
$rdonly = '';
$Ret = GetVar('Ret');
$msg = GetVar('msg');
$ModifyAll = '';


// Move from Compose to Pending Technical Review status
if ($Reviewed == "Submit for Review") {
    $_POST['STATUS'] = "Pending Technical Review";
    if ($AppDB->Settings->ReviewMode == "Content Only") { // vs "Technical and Content"
        $_POST['STATUS'] = "Pending Content Review";
    }
    $_POST['Save'] = "Save";
    $bSendNotifyReview = 1;
} else if ($Reviewed == "Mark Reviewed" && GetVar('STATUS') == "Pending Technical Review") {
    $_POST['STATUS'] = "Pending Content Review";
    $_POST['Save'] = "Save";
    $_POST['LastReviewed'] = $Now;
    $_POST['LastReviewedBy'] = $CUser->UserID;
    $bSendNotifyReview = 1;
} else if ($Reviewed && GetVar('STATUS') == "Pending Content Review") {
    $_POST['STATUS'] = "Active";

    $msg = "Article has been marked reviewed and made Active. <br>Also, check the Review By date and set as appropriate.";
    if ($AppDB->Settings->DefReviewPeriod) {
        $_POST['ReviewBy'] = MonthAdd('', $AppDB->Settings->DefReviewPeriod);
    }

    $bSendNotifyNew = 1;
    $_POST['Save'] = "Save";
    $_POST['LastReviewed'] = $Now;
    $_POST['LastReviewedBy'] = $CUser->UserID;
} else if ($Reviewed && GetVar('STATUS') == "Active") {
    if ($AppDB->Settings->DefReviewPeriod) {
        $_POST['ReviewBy'] = MonthAdd('', $AppDB->Settings->DefReviewPeriod);
        $msg = "Article has been marked reviewed and the next review date set to " . GetVar('ReviewBy');
    }
    $_POST['Save'] = "Save";
    $_POST['LastReviewed'] = $Now;
    $_POST['LastReviewedBy'] = $CUser->UserID;
}

// Since Save flag set, use ProcessSave below to write changes after checking permissions.

if (GetVar('AddRelated') && GetVar('AddRelatedID')) {
    if (strtoupper(substr(GetVar('AddRelatedID'), 0, 2)) == "KB") {
        $AddRelatedID = (int) substr(GetVar('AddRelatedID'), 2);
    }
    if ($AddRelatedID > 0) {
        $Check = $AppDB->GetRecordFromQuery("select ID from Articles where ID=$AddRelatedID");
        if ($Check) {
            $Check2 = $AppDB->GetRecordFromQuery("select ID from Related where IDA=ID and IDB=$AddRelatedID");
            if ($Check2) {
                $msg = "$AddRelatedID is already related to this Article.";
            } else {
                $RFields['IDA'] = $ID;
                $RFields['IDB'] = $AddRelatedID;
                $AppDB->insert_record("Related", $RFields);
                $msg = "Added $AddRelatedID as a related article";
            }
        } else {
            $msg = "Article $AddRelatedID not found.";
        }
    }
}

if (GetVar('RemoveRelatedID') > 0) {
    $AppDB->sql("delete from Related where ID = " . (int)GetVar('RemoveRelatedID'));
    $msg = "Relationship removed.";
}

if (GetVar('delete_attachment') || GetVar('RemoveAID') || GetVar('RestoreAID')) {
    $_POST['Save'] = "Save";
}

$IDLIST = GetVar('IDLIST');

// Handle, Save, Delete, and Reposting	
if (GetVar('Save') && !$IDLIST) {
    $ID = ProcessSave($ID, false, $msg, $Err);
}

if (GetVar('Delete') && GetVar('ID') && !$IDLIST) {
    if (ProcessDelete(GetVar('ID'), $msg)) {
        header("location:admin_articles.php?msg=Article Deleted.");
        exit;
    }
}

if ($ID) {
    $F = $AppDB->get_record_assoc($ID, "Articles");
    if (!$F) {
        header("location:admin_articles.php?msg=Article not found");
        exit;
    }

    if ($AppDB->Settings->PrivMode == "Group") {
        if (
            $CUser->u->Priv != PRIV_ADMIN &&
            $CUser->u->GroupArray[$F['GroupID']] != "W" &&
            $CUser->u->GroupArray[$F['GroupID']] != "A"
        ) {

            header("location:admin_articles.php?msg=No write access to Article $ID");
            exit;
        }

        if (
            $ID && $CUser->u->Priv != PRIV_ADMIN && $CUser->u->GroupArray[$F['GroupID']] == "W" && !$AppDB->Settings->AllowModifyArticles &&
            $F['STATUS'] == "Active"
        ) {

            $msg = "You have read only access to this 'Active' Article.";
            $rdonly = 1;
            //		header("location:home.php?msg=No write access to article. You do not have access to modify Active Articles.");
            //		exit;
        }
    }

    if ($F['STATUS'] == "Compose" && $msg == "") {
        $msg = "Press the 'Submit for Review' button when ready to move to the next step.";
    }

    $_POST = $F; // so GetVar works

    // fixup src relative to absolute needed by new editor
    //$Content = str_replace("src=\"files/KB0","src=\"/".DBNAME."/files/KB0",GetVar('Content'));
    // Set flag if this article uses an attachment as its content.
    $AttR = $AppDB->GetRecordFromQuery("select ID,Filename from ArticleAttachments where ArticleID=$ID and AsContent=1");
    $AttachmentAsContent = (isset($AttR->ID)) ? $AttR->ID : null;
} else {

    $Cols = $AppDB->MetaColumns('Articles');
    foreach ($Cols as $Key => $x) {
        $F[$Key] = null;
    }
    $F = array_merge($_POST, $F);
}

if ($_POST && !GetVar('RestoreAID')) {
    // keep reposted values,
    if (GetVar('CopyToNew')) {
        $ID = $_POST['ID'] = $_POST['LASTMODIFIEDBY'] = $_POST['LASTMODIFIED'] = $_POST['CREATED'] = $_POST['CREATEDBY'] = $_POST['LastReviewed'] = $_POST['LastReviewedBy'] = "";
        $_POST['STATUS'] = "Compose";
        $_POST['ReviewBy'] = "";
        //TODO: Need to copy the files folder from old article to this one
        // and correct img src pointers.
        // Which means we must first save the record as well to get ID.
    }
} else if (!$ID) {
    if ($AppDB->Settings->PrivMode != "Simple") {
        foreach ($CUser->u->GroupArray as $GID => $GMode) {
            if ($GID != 1) {
                $F['GroupID'] = $GID;
                break;
            }
        }
        $_POST['ViewableBy'] = PRIV_GROUP;
    } else $_POST['ViewableBy'] = PRIV_SUPPORT;
}

    $tinyKey ='ugrhy4loowottgqj6rt9gova6o34szr1r91pzu0zmyx0cnk3';

?>
<!DOCTYPE html>
<html>

<head>
    <title><? echo $AppDB->Settings->AppName ?> - Article Administration</title>
    <link REL="stylesheet" HREF="styles.css" />
    <style type="text/css">
        <!--
        .style1 {
            color: #333333
        }
        -->
    </style>

    <script src="https://cdn.tiny.cloud/1/<?php echo $tinyKey; ?>/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

</head>

<body id="body">
    <a name="top"></a>
    <SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
    <script LANGUAGE="JavaScript" SRC="lib/AnchorPosition.js"></script>
    <script LANGUAGE="JavaScript" SRC="lib/PopupWindow.js"></script>
    <script LANGUAGE="JavaScript" SRC="lib/date.js"></script>
    <script LANGUAGE="JavaScript" SRC="lib/CalendarPopup.js"></script>
    <script language="JavaScript">
        function ParseForm() {
            if (!CheckDate(form.ReviewBy)) return false;
            if (!CheckDate(form.Expires)) return false;

            if (form.IDLIST.value != "") return true;

            return true;
        }

        function RemoveRelated(id) {
            if (confirm("Are you sure you want to remove the relationship?")) {
                form.RemoveRelatedID.value = id;
                form.submit();
                return true;
            }
            return false;
        }

        function RestoreArticle(aid) {
            if (!confirm("Are you sure you want to Restore this version of the Article as the active copy? The Current active version will be archived."))
                return false;
            form.RestoreAID.value = aid;
            form.submit();
            return true;
        }

        function RemoveArticle(aid) {
            if (confirm("Are you sure you want to completely remove this version of the Article?")) {
                form.RemoveAID.value = aid;
                form.submit();
                return true;
            } else return (false);
        }

        function ImportMHT(id, nohdr, asContentFlag) {
            if (!id) {
                alert("You must Press Save first to create the Article ID before importing content");
                return;
            }
            if (asContentFlag) {
                alert("You must remove the 'Attachment as Content' file above, if you wish to import an MHT file as the new article content.");
                return;
            }
            dialog_window('upload_content.php?ID=' + id + '&nohdr=' + nohdr, 525, 200);
        }
    </script>
    <?

    if (!$nohdr) include("header.php");

    // Modify All
    if ($ID == "" && $IDLIST) {
        $ModifyAll = 1;
        set_time_limit(600);
        $IDARRAY = explode(',', $IDLIST);
        $n = 0;
        if ($Delete) {
            BusyImage(1, "Working...");
            foreach ($IDARRAY as $IDREC) {
                $msg = "";
                if (ProcessDelete($IDREC, $msg, $ModifyAll)) ++$n;
                if ($msg) $AllMsg .= $msg . "<br>";
            }
            BusyImage(0);
            $AllMsg .= " $n Articles deleted.";
            if (!$nohdr) $AllMsg .= " <a href='admin_articles.php'>Continue</a>";
            ShowMsgBox($AllMsg, "center");
            exit;
        } else if (GetVar('Save')) {
            BusyImage(1, "Working...");
            $n  = 0;
            foreach ($IDARRAY as $IDREC) {
                $msg = "";
                if (ProcessSave($IDREC, 0, $msg, $Err, $ModifyAll)) ++$n;
                if ($msg) $AllMsg .= $msg . "<br>";
            }
            BusyImage(0);
            $AllMsg .= " $n Articles modified. ";
            if (!$nohdr) $AllMsg .= "<a href='admin_articles.php'>Continue</a>";
            ShowMsgBox($AllMsg, "center");
            exit;
        } else {
            $msg = "You are about to modify " . count($IDARRAY) . " Articles. <br>Set only the field(s) you wish changed in each Article.";
        }
    }

    ?>
    <center>
        <form onSubmit="return ParseForm();" name=form action="<? echo $_SERVER['PHP_SELF'] ?>" method="post">

            <? hidden("ID", $ID);
            hidden("RemoveRelatedID", "");
            hidden("IDLIST", "$IDLIST");
            hidden("nohdr", "$nohdr");
            hidden("DeleteNoteID", "");
            hidden("RemoveAID", "");
            hidden("RestoreAID", "");
            hidden("Ret", $Ret);
            hidden("delete_attachment", ""); ?>
            <table width="100%" border=0 cellspacing=0 cellpadding=0>
                <tr>
                    <td width="25%" class="subhdr"><img align="middle" src="images/newticket.jpg" width="51" height="51"><span>
                            <? if (!$ID && !$IDLIST) echo "New Article"; ?>
                            <? if ($ID) echo "Article " . sprintf("KB%06d", $ID); ?>
                        </span></td>
                    <td align="left" width="75%">&nbsp;
                        <? ShowMsgBox($msg); ?>
                    </td>
                </tr>
            </table>
            <br>
            <?
            $Tabs = array("Details", "Content");
            if ($ID) {
                $Tabs[] = "Notes";
                $Tabs[] = "Versions";
                $Tabs[] = "Audit";
            }
            $ActiveTab = ShowTabs3($Tabs, "Details", $ClassPrefix = "article-", 0);
            $Tabn = 0;
            $dir = '';
            TabSectionStart($Tabs[$Tabn++], $ActiveTab);
            ?>
            <table width="95%" cellpadding="0" height="450px" cellspacing="0" class="tabtable">
                <tr>
                    <td valign="top" align="center" width="100%">
                        <table height="100%" width="100%" <? echo $FORM_STYLE ?>>
                            <tr>
                                <td valign="top">
                                    <table width="100%">
                                        <tr>
                                            <td colspan="2" align="left">
                                                <? $imgidx = array("" => 1, "Compose" => 1, "Pending Technical Review" => 2, "Pending Content Review" => 3, "Active" => 4, "Obsolete" => 5);
                                                $n = $imgidx[$F['STATUS']]; ?>
                                                <img src="images/articlestep<? echo $n ?>.gif" border="0">
                                            </td>
                                            <td rowspan="3" class="form-hdr">
                                                <table cellpadding="2">
                                                    <tr>
                                                        <td align="right"><span style="margin-right:6px">
                                                                <?
                                                                $NotesCount = 0;
                                                                if ($ID > 0) $NotesCount = $AppDB->count_of("select count(*) from ArticleNotes where ArticleID=$ID");
                                                                if ($NotesCount > 0) { ?>
                                                                    <a title="Display Author/Reviewer notes for this Article"
                                                                        onClick='TabEnableByName("Notes","Tab")' href="#"> <img src="images/i_note.gif" width="18" height="18" border="0" align="absbottom">
                                                                        <? if ($NotesCount > 1)  echo "$NotesCount Notes ";
                                                                        else echo "1 Note ";  ?>
                                                                    </a>
                                                                <? }
                                                                ?>
                                                            </span></td>
                                                    </tr>
                                                    <tr>
                                                        <td align="right"><span class="form-data"><span class="form-hdr" align="right">
                                                                    <? if ($ID) { ?>
                                                                        <a href="report_active_articles.php?S=1&ID=<? echo $ID ?>" title="Display hits for this Article"><img src="images/newpage.gif" width="18" height="18" border="0" align="absbottom">
                                                                            <? if ($F['Hits'] == "") $Hits = 0;
                                                                            echo $F['Hits'] ?>
                                                                            Hits</a>
                                                                    <? } ?>
                                                                </span></span></td>
                                                    </tr>
                                                    <tr>
                                                        <td align="right"><span class="form-data"><span class="form-hdr" align="right">
                                                                    <? if ($ID) { ?>
                                                                        <a title="Display list of Previous versions of this Article" onClick='TabEnableByName("Versions","Tab")' href="#"><img src="images/ed_copy.gif" width="18" height="18" border="0" align="absbottom">
                                                                            <? $PCount = $AppDB->count_of("select count(*) from ArchiveArticles where ID >= $ID and ID < ($ID + 1)");
                                                                            if ($PCount == 1) {
                                                                                echo "1 Previous version";
                                                                            } else if ($PCount == 0) {
                                                                                echo "No Previous versions";
                                                                            } else echo "$PCount Previous versions";
                                                                            ?>
                                                                        </a>
                                                                    <? } ?>
                                                                </span></span></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="form-hdr"><strong> Title</strong>:</td>
                                            <td class="form-data"><? DBField("Articles", "Title", GetVar('Title'), $ModifyAll ? 2 : 0); ?></td>
                                        </tr>
                                        <? if ($AppDB->Settings->PrivMode == "Group") { ?>
                                            <tr>
                                                <td class="form-hdr"><strong>Group</strong>:</td>
                                                <td class="form-data"><?
                                                                        //DBField("Articles","GroupID",$GroupID);  
                                                                        GroupDropList($F['GroupID'], '', '', '');
                                                                        ?>&nbsp;</td>
                                            </tr>
                                        <? } else hidden("GroupID", "0"); ?>
                                        <tr>
                                            <td colspan="3">
                                                <table width="100%">
                                                    <tr>
                                                        <td valign="top" style="padding:5px" width="50%">
                                                            <fieldset style="padding: 5px; height:100%;">
                                                                <legend>Classification</legend>
                                                                <table width="100%">
                                                                    <tr>
                                                                        <td class="form-hdr">Viewable By:</td>
                                                                        <td class="form-data"><?
                                                                                                if ($AppDB->Settings->PrivMode == "Group") {
                                                                                                    DBField("Articles", "ViewableByG", GetVar('ViewableBy'), 0, $ModifyAll);
                                                                                                } else {
                                                                                                    DBField("Articles", "ViewableBy", GetVar('ViewableBy'), 0, $ModifyAll);
                                                                                                }

                                                                                                ?>
                                                                            &nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="form-hdr">Type:</td>
                                                                        <td class="form-data"><? DBField("Articles", "Type", GetVar('Type')); ?>
                                                                            &nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="form-hdr">Product:</td>
                                                                        <td class="form-data"><?
                                                                                                DBField("Articles", "Product", GetVar('Product'));
                                                                                                PopupFieldValues("Articles", "form", "Product");
                                                                                                ?>
                                                                            &nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="form-hdr">Must Read: </td>
                                                                        <td class="form-data"><? DBField("Articles", "MustRead", GetVar('MustRead'), ($CUser->u->Priv != PRIV_ADMIN), $ModifyAll); ?></td>
                                                                    </tr>
                                                                    <? if ($AppDB->Settings->Custom1Label) { ?>
                                                                        <tr>
                                                                            <td nowrap valign="top" class="form-hdr"><? echo $AppDB->Settings->Custom1Label ?></td>
                                                                            <td class="form-data"><? DBField("Articles", "Custom1", $F['Custom1'], 0, $ModifyAll); ?></td>
                                                                        </tr>
                                                                    <? } ?>
                                                                    <? if ($AppDB->Settings->Custom2Label) { ?>
                                                                        <tr>
                                                                            <td nowrap valign="top" class="form-hdr"><? echo $AppDB->Settings->Custom2Label ?></td>
                                                                            <td class="form-data"><? DBField("Articles", "Custom2", $F['Custom2'], 0, $ModifyAll); ?></td>
                                                                        </tr>
                                                                    <? } ?>
                                                                    <tr>
                                                                        <td nowrap valign="top" class="form-hdr">
                                                                            <p>Search Keywords:<br>
                                                                                <span class="small"><em>(Specify same keyword more than</em></span>
                                                                            </p>
                                                                            <p class="small"><em> once to increase ranking). </em></p>
                                                                        </td>
                                                                        <td class="form-data"><? DBField("Articles", "Keywords", GetVar('Keywords'), 0, $ModifyAll); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td nowrap valign="top" class="form-hdr">Related Articles:</td>
                                                                        <td class="form-data"><input <? if (!$ID) echo "Disabled" ?> type="submit" name="AddRelated" value="Add">
                                                                            <input type="text" size="8" maxlength="8" name="AddRelatedID">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:3px" colspan="2">

                                                                            <?
                                                                            if ($ID) {
                                                                                $RRes = $AppDB->sql("select Related.*,Articles.Title from Related inner join Articles on Related.IDB=Articles.ID where IDA=$ID order by Related.IDB");
                                                                                $first = 1;
                                                                                while ($RRec = $AppDB->sql_fetch_obj($RRes)) {
                                                                                    if ($first) {
                                                                                        echo '<table border=0>';
                                                                                        $first = 0;
                                                                                    }
                                                                                    echo "<tr><td><p class=\"form-sm\"><a title=\"Remove relationship\" href=\"Javascript:RemoveRelated($RRec->ID)\"><img border=0 src=\"images/delete.gif\"></a></p></td>" .
                                                                                        "<td><p class=\"form-sm\"><a target=_blank href=\"article.php?ID=$RRec->IDB\" title=\"Click to view in new window\">$RRec->IDB</a></p></td><td><p class=\"form-sm\">" . substr((string)$RRec->Title, 0, 54) . "...</p></td></tr>\n";
                                                                                }
                                                                                if (!$first) echo "</table>";
                                                                            }
                                                                            ?> </td>
                                                                    </tr>
                                                                </table>
                                                            </fieldset>
                                                        </td>
                                                        <td style="padding:5px" valign="top" height="250" width="50%">
                                                            <fieldset style="padding: 5px; height:100%;">
                                                                <legend>Status</legend>
                                                                <table width="100%">
                                                                    <tr>
                                                                        <td class="form-hdr">Status:</td>
                                                                        <td class="form-data"><? DBField("Articles", "STATUS", $F['STATUS'], 0, $ModifyAll); ?> </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="form-hdr">Created:</td>
                                                                        <td class="form-data"><? echo GetVar('CREATED');
                                                                                                if (GetVar('CREATEDBY')) echo " (" . GetVar('CREATEDBY') . ")"; ?>&nbsp; </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="form-hdr">Modified:</td>
                                                                        <td class="form-data"><? echo GetVar('LASTMODIFIED');
                                                                                                if (GetVar('LASTMODIFIED')) echo " (" . GetVar('LASTMODIFIEDBY') . ")"; ?>&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td nowrap class="form-hdr">Content Modified: </td>
                                                                        <td class="form-data"><? echo GetVar('ContentLastModified'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="form-hdr">1st Contact:</td>
                                                                        <td class="form-data"><? DBField("Articles", "Contact1", $F['Contact1']) ?><? //PopupFieldValues("Articles","form","Contact1"); 
                                                                                                                                                    ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="form-hdr">2nd Contact:</td>
                                                                        <td class="form-data"><? DBField("Articles", "Contact2", $F['Contact2']) ?><? //PopupFieldValues("Articles","form","Contact2"); 
                                                                                                                                                    ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="form-hdr">Review By:</td>
                                                                        <td class="form-data"><? DBField("Articles", "ReviewBy", $F['ReviewBy']); ?> </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td valign="top" nowrap class="form-hdr">Review Priority: </td>
                                                                        <td class="form-data"><? DBField("Articles", "Priority", $F['Priority'], 0, $ModifyAll); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td valign="top" nowrap class="form-hdr">Last Reviewed:</td>
                                                                        <td class="form-data"><? if (GetVar('LastReviewed')) {
                                                                                                    echo substr($F['LastReviewed'], 0, 10);
                                                                                                    if ($F['LastReviewedBy']) echo " by " . $F['LastReviewedBy'];
                                                                                                } else echo "(never)"; ?>
                                                                            <?
                                                                            $R_Enabled = '';
                                                                            // Set button label
                                                                            if ($F['STATUS'] == "" || $F['STATUS'] == "Compose") {
                                                                                $lbl = "Submit for Review";
                                                                                if (!$ID) $R_Enabled = "disabled";
                                                                            } else {
                                                                                // If current user has Review Priv
                                                                                $R_Enabled =  ($ID && ($CUser->IsPriv(PRIV_APPROVER) || $CUser->ISPriv(PRIV_ADMIN))) ? "" : "disabled";
                                                                                $lbl = "Mark Reviewed";
                                                                            }


                                                                            ?>
                                                                            <input <? echo $R_Enabled ?> type="submit" name="Reviewed" value="<? echo $lbl ?>">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="form-hdr">Expires:</td>
                                                                        <td class="form-data"><? DBField("Articles", "Expires", GetVar('Expires')); ?> </td>
                                                                    </tr>
                                                                </table>
                                                            </fieldset>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td valign="top">
                                    <table width="100%">
                                        <tr>
                                            <td valign="top" class="form-hdr" width="50%" style="padding-left:10px; text-align:left;">
                                                <fieldset style="padding: 5px;">
                                                    <legend>Attachments:</legend>
                                                    <? DisplayAttachments("Article", $ID, !$rdonly, 0, 1, "AND AsContent is NULL"); ?>
                                                </fieldset>
                                            </td>
                                            <td valign="top" class="form-hdr" width="50%" style="padding-left:10px; text-align:left;">
                                                <fieldset style="padding: 5px;">
                                                    <legend>Attachment as Content:</legend>
                                                    <? DisplayAttachments("Article", $ID, !$rdonly, 0, 2, "AND AsContent = 1"); ?>
                                                </fieldset>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table><? TabSectionEnd(); ?>
            <? if (!$IDLIST) { ?>
                <? TabSectionStart($Tabs[$Tabn++], $ActiveTab); ?>
                <table height="450px" width="95%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="center" width="100%">
                            <table width="100%" class="tabtable" <? echo $FORM_STYLE ?>>
                                <tr>
                                    <td colspan="2" style="background-color: white;"><?
                                        if (!$AttachmentAsContent) {
                                                                                                ?>
                                            <textarea style="min-height:600px" name="Content" id="content"><?php echo $F['Content']; ?></textarea>

                                            <script>
                                                var ctab = FindElement('TabContent');
                                                ctab.onmouseup = ShowContent;

                                                function ShowContent() {
                                                    tinymce.init({
                                                        selector: 'textarea#content',
                                                        plugins: [
                                                            // Core editing features
                                                            'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'image', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount'
                                                        ],
                                                        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat'
                                                    });

                                                }
                                            </script>
                                        <?
                                        } else {
                                        ?>
                                            <div style="min-height:600px;text-align:center;padding-top:20px">
                                                <div class="MsgBox" style="width:550px; margin:auto; "><img align="left" src="images/warning.gif"><br>
                                                    The uploaded file '<? echo $AttR->Filename ?>' will be displayed as the content of this article.
                                                    <p align="center" class="form-data style1"><br>
                                                        (
                                                        To update or remove this attached content, see the "Attachment as Content" item on the details Tab.)
                                                    </p>
                                                </div>
                                            </div>
                                        <? } ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table><?
                        TabSectionEnd();
                    }
                    if ($ID) {

                        TabSectionStart($Tabs[$Tabn++], $ActiveTab);
                        ?>
                <a name="Notes"></a>
                <table width="95%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="center" width="100%">
                            <table width="100%" class="tabtable" <? echo $FORM_STYLE ?>>
                                <tr>
                                    <td class="form-hdr" style="text-align:left"><strong>Author/Reviewer Notes: <span class="form-hdr" style="text-align:left">(most recent shown first)</span></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="form-data">
                                        <div style="overflow:auto; height:470px">
                                            <? ShowNotes("ArticleNotes", $ID, "ArticleID", $printview); ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table><?
                        TabSectionEnd();

                        TabSectionStart($Tabs[$Tabn++], $ActiveTab);
                        ?>
                <a name="Archived"></a>
                <table width="95%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="center" width="100%">
                            <table width="100%" class="tabtable" <? echo $FORM_STYLE ?>>
                                <tr>
                                    <td class="form-hdr" style="text-align:left"><strong>Previous Article Versions:</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="form-data">
                                        <div style="overflow:auto; height:470px">
                                            <table cellpadding="4" style="width:100%" cellspacing="1">
                                                <tr bgcolor="#CCCCCC">
                                                    <td class="list-sm-hdr">ID</td>
                                                    <td class="list-sm-hdr">Date</td>
                                                    <td class="list-sm-hdr">Action</td>
                                                    <?
                                                    $aq = "select ID,LASTMODIFIED,LASTMODIFIEDBY from ArchiveArticles where ID >= $ID and ID < ($ID + 1) order by ID desc";
                                                    $archivers = $AppDB->sql($aq);
                                                    while ($AR = $AppDB->sql_fetch_obj($archivers)) {
                                                        echo "<tr><td class=\"list-sm\"><b>$AR->ID</b></td>";
                                                        echo "<td class=\"list-sm\" valign=\"top\">$AR->LASTMODIFIED ($AR->LASTMODIFIEDBY)</td>";
                                                        echo "<td class=\"list-sm\">| <a target=\"_blank\" href=\"article.php?ID=$AR->ID\">View</a> |" .
                                                            " <a href=\"Javascript:RestoreArticle('$AR->ID');void(0);\">Restore as Current</a> |" .
                                                            " <a href=\"JavaScript:RemoveArticle('$AR->ID');void(0);\">Remove</a></td></tr>";
                                                    }
                                                    ?>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table><?
                        TabSectionEnd();
                        TabSectionStart($Tabs[$Tabn++], $ActiveTab);
                        ?>
                <table width="95%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="center" width="100%">
                            <table width="100%" class="tabtable" <? echo $FORM_STYLE ?>>
                                <tr>
                                    <td class="form-hdr" style="text-align:left"><strong>Audit Trail:</strong> (most recent shown first)</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="form-data">
                                        <div style="overflow:auto; height:470px;width:100%">
                                            <table cellpadding="3" cellspacing="1" style="width:100%">
                                                <tr>
                                                    <td class="list-sm-hdr">Date</td>
                                                    <td align=center class="list-sm-hdr">Author</td>
                                                    <td class="list-sm-hdr">Details</td>
                                                </tr>
                                                <?
                                                $ARes = $AppDB->sql("select * from AuditTrail where ArticleID = $ID order by CREATED desc");
                                                while ($ARec = $AppDB->sql_fetch_obj($ARes)) {
                                                    echo "<tr><td valign=\"middle\" class=\"list-sm\" valign=\"top\">$ARec->CREATED</td>";
                                                    echo "<td valign=\"middle\" align=center width=90 class=\"list-sm\">$ARec->CREATEDBY</td>";
                                                    echo "<td class=\"list-sm\">$ARec->Trail</td></tr>";
                                                }
                                                ?>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table><?
                        TabSectionEnd();
                    }
                        ?>
            <table class="footer-buttons" width="95%" cellpadding="0" cellspacing="0">
                <tr>
                    <?
                    $Disabled = '';
                    if ($rdonly) $Disabled = "disabled"; ?>
                    <td height="24" align="right" bgcolor="#3A467A"><input <? echo $Disabled ?> type="submit" name="Save" value="Save">
                        <? if ($ID) {
                        ?>
                            <input onClick="window.open('article.php?Preview=1&ID=<? echo $ID ?>','preview')" type="button" name="Preview" value="Preview">
                        <? } ?>
                        <? if ($ID || $IDLIST) { ?>
                            <input <? echo $Disabled ?> onClick="return confirm('Are you sure')" type="submit" name="Delete" value="Delete">
                        <? } ?>
                        <? if ($ID) { ?>
                            <input type="submit" name="CopyToNew" value="Copy to New">
                        <? } ?>
                        <? if (!$IDLIST) { ?><input onClick="JavaScript:ImportMHT('<? echo $ID ?>','<? echo $nohdr ?>','<? echo $AttachmentAsContent ?>')" type="button" name="ImportContent" <? echo $Disabled ?> value="Import Content">
                        <? ShowNotesBut($ID, "ArticleNotes", "ArticleID");
                        } ?>
                        <? // TODO: set target incase in fram via nohdr 
                        ?>

                        <?
                        $Ret = GetVar('Ret');
                        if (!$Ret) $Ret = "admin_articles.php"; ?>
                        <input onClick="window.location='<? echo $Ret ?>'" name="Back" type="button" id="Back" value="Back">
                    </td>
                </tr>
            </table>
        </form>
    </center>
</body>

</html>