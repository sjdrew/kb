<? include("config.php");
RequirePriv(PRIV_GROUP);

set_time_limit(180); // as send email operation to a large list can take some time.

$ID = GetVar("ID");
$Table = "Messages";
$Delete = GetVar('Delete');
$Save = GetVar('Save');
$SendEmail = GetVar('SendEmail');
$msg = GetVar('msg');
$nohdr = GetVar('nohdr');
$M = new stdClass();
$Err = '';

if ($Delete) {
    $q = MessageQuery($ID, 1);
    $M = $AppDB->GetRecordFromQuery($q);
    if ($CUser->IsPriv(PRIV_ADMIN) || strcasecmp($CUser->UserID, $M->CREATEDBY) == 0) {
        $AppDB->delete_record($ID, "$Table");
        if ($nohdr) {
            echo "<html><script>alert('Bulletin Deleted');window.close()</script></html>";
            exit;
        }
        header("location: admin_messages.php?msg=Bulletin+Deleted");
        exit;
    } else {
        $msg = "You must be the author of the Bulletin to delete it.";
    }
}

if ($Save || $SendEmail) {

    // See if we need W permission to create a bulletin...
    if ($AppDB->Settings->PrivMode == "Group" && !$AppDB->Settings->AllowCreateBulletins) {
        // check that this User has A or W priv inGroup
        if (
            $CUser->u->Priv != PRIV_ADMIN &&
            $CUser->u->GroupArray[$_POST['GroupID']] != "W" &&
            $CUser->u->GroupArray[$_POST['GroupID']] != "A"
        ) {

            $msg = "You do not have write permissions to the specified Group.";
            $Err = 1;
        }
        if (!$AppDB->Settings->AllowCreateBulletinsW && $CUser->u->GroupArray[$_POST['GroupID']] == "W") {
            $msg = "Your Administrator has disabled the ability for creating bulletins except by Approvers.";
            $Err = 1;
        }
    }

    if (!$Err) {

        // Normal save path
        if ($ID) {
            $AppDB->modify_form($ID, "$Table");
            $msg = "Changes Saved";
            $AFields['Trail'] = "Bulletin $ID Modified";
            $AFields['BulletinID'] = $ID;
            AuditTrail("ModifyBulletin", $AFields);
        } else {
            if (empty($_POST["StartTime"])) $_POST["StartTime"] = NowDateTime();
            $ID = $AppDB->save_form("$Table");
            $msg = "Bulletin Created.";
            $AFields['Trail'] = "New Bulletin $ID Created";
            $AFields['BulletinID'] = $ID;
            AuditTrail("AddBulletin", $AFields);
        }

        if ($ID && $SendEmail) {
            // Determine all users than can read Bulletin
            // and send email notification.

            $MQ = "select Email from users where Email is Not NULL ";
            $MQ .= " and users.BulletinEmail = 'Yes' ";
            $MailTo = array();
            if ($_POST['GroupID'] > 0) {
                $GroupID = $_POST['GroupID'];
                $MQ .= " AND (users.Groups like '1:%' OR users.Groups like '%,1:%' OR " .
                    "users.Groups like '$GroupID:%' OR users.Groups like '%,$GroupID:%') ";
            }

            $MRes = $AppDB->sql($MQ);
            while ($MRec = $AppDB->sql_fetch_obj($MRes)) {
                $MailTo[] = $MRec->Email;
            }
            $MailTo = array_unique($MailTo);
            if (count($MailTo)) {
                $from = $CUser->u->Email;
                if (trim((string)$GroupID))
                    $G = $AppDB->GetRecordFromQuery("select * from Groups where GroupID=$GroupID");
                $GroupName =  $G->Name;

                $q = MessageQuery($ID, 1);
                $M = $AppDB->GetRecordFromQuery($q);

                $Vars['ID'] = $ID;
                $Vars['GroupName'] = $GroupName;
                $Vars['SITE_URL'] = SITE_URL;
                $Vars['Type'] = $M->Type;
                $Vars['ServiceType'] = $M->ServiceType;
                $Vars['ServiceName'] = $M->ServiceName;
                $Vars['Author'] = $M->Author;
                $Vars['TicketNumber'] = $M->TicketNumber;
                $Vars['StartTime'] = $M->StartTime;
                $Vars['EndTime'] = $M->EndTime;
                $Vars['Escalated'] = $M->Escalated;
                $Vars['Message'] = nl2br($M->Message);
                $Vars['Subject'] = $M->Subject;

                $Template = new template();
                $Template->assign($Vars);
                $HtmlMsg = $Template->render("EmailTemplates/Bulletin.tpl");

                if ($HtmlMsg) {
                    $TextMsg = HTMLToReadableText($HtmlMsg);
                    $Num = send_mail(array(), "Bulletin Notification: $M->Subject", $HtmlMsg, $TextMsg, $from, $MailTo);
                    if ($Num >= 0) $msg = "Email was sent to $Num recipients";
                    else $msg = "Email notification Failed";
                }
            } else {
                $msg = "No Recipients found. No email was sent.";
            }
        }
    }
}

if ((!$_POST || $Save || $SendEmail) && $ID) {

    // not posting or just saved then read record
    $q = MessageQuery($ID, 1);
    $M = $AppDB->GetRecordFromQuery($q);
}
?>
<html>

<title><? echo $AppDB->Settings->AppName ?> - Administration - Bulletin Board Message</title>
<link REL="stylesheet" HREF="styles.css">
</link>
<style type="text/css">
    <!--
    .style1 {
        color: #4100B6
    }
    -->
</style>
</head>
<? $SECTION = "Section-ADMIN";
if (!$nohdr) include("header.php");  ?>

<body <? if ($nohdr) echo 'onload="AutoSizeWindow()"'; ?>>

    <SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
    <script LANGUAGE="JavaScript" SRC="lib/AnchorPosition.js"></script>
    <script LANGUAGE="JavaScript" SRC="lib/PopupWindow.js"></script>
    <script LANGUAGE="JavaScript" SRC="lib/date.js"></script>
    <script LANGUAGE="JavaScript" SRC="lib/CalendarPopup.js"></script>

    <script language="JavaScript">
        function parse() {
            var df = document.forms[0];
            if (!CheckRequired(df.Author)) return false;
            if (!CheckRequired(df.Subject)) return false;
            if (!CheckRequired(df.Message)) return false;

            return true;
        }
    </script>


    <table width="100%" border=0 cellspacing=0 cellpadding=0>
        <tr>
            <td nowrap class="subhdr">
                <img src="images/clipboard.gif"><span><? if (!$ID) echo "New "; ?>
                    Bulletin Board Message<br><br></span>
            </td>
            <td align="left" width="75%">&nbsp;<? ShowMsgBox($msg); ?></td>
        </tr>
    </table>

    <div align="center">
        <div class="shadowboxfloat" style="width:680px">
            <div class="shadowcontent">
                <form name=form method="post" action="<? echo $_SERVER['PHP_SELF'] ?>">
                    <? hidden("ID", $ID);
                    hidden("nohdr", $nohdr);
                    $title = ($ID == "") ? "New Bulletin" : "Modify Bulletin";

                    if (!$ID) {
                        $M->CREATED =  '';
                        $M->Subject =  '';
                        $M->STATUS = '';
                        $M->Type = '';
                        $M->ServiceType = '';
                        $M->ServiceName = '';
                        $M->TicketNumber = '';
                        $M->Escalated = '';
                        $M->Prompter = '';
                        $M->EndTime = '';
                        $M->Message = '';
                        $M->DisplayUntil = AddDays(Now(), 1);
                        $M->Author = $CUser->u->FirstName . " " . $CUser->u->LastName;
                        $Grps = explode(",", $CUser->u->GroupIDs);
                        $M->GroupID = $Grps[0];
                        $M->StartTime = NowDateTime();
                    }

                    ?>
                    <table width="640" <? echo $FORM_STYLE ?>>
                        <tr>
                            <td align="right" style="width:180px;" CLASS="form-hdr">
                                Date </td>
                            <td CLASS="form-data">
                                <? echo ($M->CREATED == "") ? NowDateTime() : DateTimeStr($M->CREATED); ?>
                            </td>
                        </tr>
                        <tr>
                            <td CLASS="form-hdr" align="right">
                                From </td>
                            <td CLASS="form-data">
                                <? DBField($Table, "Author", $M->Author); ?>
                            </td>
                        </tr>
                        <tr>
                            <td CLASS="form-hdr" align="right">
                                To </td>
                            <td CLASS="form-data">
                                <?
                                GroupDropList($M->GroupID, 1);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td CLASS="form-hdr" align="right">
                                Subject </td>
                            <td CLASS="form-data">
                                <? DBField($Table, "Subject", $M->Subject);  ?>
                            </td>
                        </tr>
                        <tr>
                            <td align="right" CLASS="form-hdr">
                                Status </td>
                            <td CLASS="form-data">
                                <? DBField($Table, "STATUS", $M->STATUS); ?>
                            </td>
                        </tr>
                        <tr>
                            <td align="right" CLASS="form-hdr">
                                Display Until </td>
                            <td CLASS="form-data">
                                <? DBField($Table, "DisplayUntil", $M->DisplayUntil); ?>
                                &nbsp; <font size="1">(if blank will display until manually removed or hidden)</font>
                            </td>
                        </tr>
                        <tr>
                            <td CLASS="form-hdr" align="right">Bulletin Type </td>
                            <td CLASS="form-data"><? DBField($Table, "Type", $M->Type); ?></td>
                        </tr>
                        <tr>
                            <td CLASS="form-hdr" align="right">Service Type </td>
                            <td CLASS="form-data"><? DBField($Table, "ServiceType", $M->ServiceType); ?></td>
                        </tr>
                        <tr>
                            <td CLASS="form-hdr" align="right">Service Name </td>
                            <td CLASS="form-data"><? DBField($Table, "ServiceName", $M->ServiceName); ?> <font size="1">(if applicable)</font>
                            </td>
                        </tr>
                        <tr>
                            <td CLASS="form-hdr" align="right">Ticket # </td>
                            <td CLASS="form-data"><? DBField($Table, "TicketNumber", $M->TicketNumber); ?></td>
                        </tr>
                        <tr>
                            <td CLASS="form-hdr" align="right">Escalated</td>
                            <td CLASS="form-data"><? DBField($Table, "Escalated", $M->Escalated); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="style1">Prompter Up</span>: <? DBField($Table, "Prompter", $M->Prompter); ?></td>
                        </tr>
                        <tr>
                            <td CLASS="form-hdr" align="right">Start Date/Time </td>
                            <td CLASS="form-data"><? DBField($Table, "StartTime", $M->StartTime); ?> <font size="1"> (if MUI provide date/time that problem first noticed)</font>
                            </td>
                        </tr>
                        <tr>
                            <td CLASS="form-hdr" align="right">End Date/Time </td>
                            <td CLASS="form-data"><? DBField($Table, "EndTime", $M->EndTime); ?> </td>
                        </tr>
                        <tr>
                            <td CLASS="form-hdr" align="right">
                                Message </td>
                            <td CLASS="form-data">
                                <? DBField("$Table", "Message", $M->Message); ?>
                            </td>
                        </tr>
                        <tr>
                            <td width="100%" CLASS="form-data" COLSPAN="2" ALIGN="right">
                                <input onClick="return parse()" TYPE="submit" VALUE="Save" NAME="Save">
                                <? if ($ID) { ?>
                                    <input TYPE="submit" onClick="return confirm('Are you sure you want to send an Email notification to all persons than can read this bulletin?')" VALUE="Email" NAME="SendEmail">
                                    <input TYPE="submit" onClick="return confirm('Are you sure?')" VALUE="Delete" NAME="Delete">
                                <? } ?>
                                <? if ($nohdr) { ?>
                                    <input TYPE="button" VALUE="Close" NAME="Close" onClick="window.close()">
                                <? } else { ?>
                                    <input TYPE="button" VALUE="Back" NAME="Back" onClick="window.location='admin_messages.php';">
                                <? } ?>
                                <? HelpButton()  ?>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
</body>

</html>