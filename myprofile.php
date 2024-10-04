<? include("config.php");

if ($GetVar('Password')) {
    header("location:password.php");
    exit;
}

$Save = GetVar('Save');
$FirstName = GetVar("FirstName");
$Table = "users";
$SearchMode = GetVar('SearchMode');


$U = $CUser->UserRecord();
$ID = $U->ID;

//TODO: Could have generic routing for posting that uses dbfields and sets vars for all checkboxes?
if ($ID && $_POST) {
    // checkboxes do not post if not set
    if ($_POST['BulletinEmail'] == "") $_POST['BulletinEmail'] = "";
    if ($_POST['NotifyNew'] == "") $_POST['NotifyNew'] = "";
    if ($_POST['NotifyUpdated'] == "") $_POST['NotifyUpdated'] = "";
    if ($_POST['NotifySubmitted'] == "") $_POST['NotifySubmitted'] = "";
    if ($_POST['NotifyTechnicalReview'] == "") $_POST['NotifyTechnicalReview'] = "";
    if ($_POST['NotifyContentReview'] == "") $_POST['NotifyContentReview'] = "";
}

if ($Save) {
    $ModFields = $AppDB->modify_form($U->ID, "users");
}

if ($ID) {
    $R = $AppDB->get_record_assoc($ID, $Table);
}
if (!$ID) {
    header("location:home.php?msg=User not found");
    exit;
}
if ($SearchMode == "") $SearchMode = $AppDB->Settings->DefaultSearchMode;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
    <title><? echo $AppDB->Settings->AppName ?> - Profile</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link REL="stylesheet" HREF="styles.css">
    </link>
</head>

<body>
<center>
    <SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
    <? include("header.php");

    ?>
    <br>
    <script language="javascript">
        function ParseForm(f) {
            if (!CheckEmail(f.Email)) return false;
            return true;
        }
    </script>
    <form onSubmit="return ParseForm(this);" action="<? echo $_SERVER['PHP_SELF'] ?>" method="post" name="form">

        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
                <td height="14">
                    <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">

                        <td width="250" valign="top" align="left"> <img src="images/spacer.gif" width="180" height="1" border=0>
                            <table style='background-image:"images/vert_bar.gif";border-right:1px solid navy;' width="87%" border="0" cellpadding="4" cellspacing="0">
                                <tr>
                                    <td align="center" width="36%"><img src="images/folder1.jpg" width="32" height="32"></td>
                                    <td width="64%" align="left" valign="middle" class="hdr1">Profile</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="dots">.....................................</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <button onclick="window.location='home.php'">Back</button>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <td colspan="2" valign="top">
                            <table width="90%" border="0" align="center" cellpadding="4">
                                <tr>
                                    <td colspan="2">
                                    </td>
                                </tr>
                                <tr>
                                    <td> </td>
                                    <td height="27" valign="top" class="subhdr"> Profile
                                        and Preferences</td>
                                </tr>
                                <tr>
                                    <td width="30%" class="form-hdr">User ID:</td>
                                    <td width="70%" class="form-data"><b><? echo $R['Username'] ?></b> </td>
                                </tr>
                                <tr>
                                    <td class="form-hdr">Email:</td>
                                    <td class="form-data"><? DBField($Table, "Email", $R['Email']); ?></td>
                                </tr>
                                <tr>
                                    <td width="30%" class="form-hdr">FirstName:</td>
                                    <td width="70%" class="form-data"><? DBField($Table, "FirstName", $R['FirstName']); ?> </td>
                                </tr>
                                <tr>
                                    <td height="22" class="form-hdr">Last Name:</td>
                                    <td class="form-data"><? DBField($Table, "LastName", $R['LastName']); ?> </td>
                                </tr>
                                <tr>
                                    <td class="form-hdr">Phone:</td>
                                    <td class="form-data"><? DBField($Table, "Phone", $R['Phone']); ?> </td>
                                </tr>
                                <? if ($AppDB->Settings->PrivMode == "Simple") { ?>
                                    <tr>
                                        <td class="form-hdr">Support:</td>
                                        <td class="form-data"><? if ($CUser->u->Priv >= PRIV_SUPPORT) {
                                                                    echo "Yes";
                                                                } else {
                                                                    echo "No";
                                                                }  ?> </td>
                                    </tr>
                                <? } else { ?>
                                    <tr>
                                        <td valign="top" class="form-hdr">Group Memberships:</td>
                                        <td style="padding-left:4px;" class="form-data"><?
                                                                                        $Modes = array("W" => "(Write Access)", "R" => "(Read Access)", "A" => "(Approval Access)");
                                                                                        foreach ($CUser->u->GroupArray as $Grp => $Mode) {
                                                                                            if ($Grp && $Mode) {
                                                                                                $G = $AppDB->GetRecordFromQuery("select Name from Groups where GroupID = $Grp");
                                                                                                echo "<b>$G->Name</b> : " . $Modes[$Mode] . "<br>";
                                                                                            }
                                                                                        }
                                                                                        ?></td>
                                    </tr>
                                <? } ?>
                                <tr>
                                    <td class="form-hdr">Default Search Group: </td>
                                    <td class="form-data"><?
                                                            GroupDropList($R['GroupID'], 1);
                                                            ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="form-hdr">Default Search Mode: </td>
                                    <td class="form-data"><? DBField($Table, "SearchMode", $R['SearchMode']); ?></td>
                                </tr>
                                <tr>
                                    <td class="form-hdr">Items displayed per page: </td>
                                    <td class="form-data"><?
                                                            if ($R['Pagination'] == "") $R['Pagination'] = DEFAULT_ITEMS_PER_PAGE;
                                                            DBField($Table, "Pagination", $R['Pagination']); ?></td>
                                </tr>
                                <tr>
                                    <td class="form-hdr">Display Article Previews:</td>
                                    <td class="form-data"><?
                                                            DBField($Table, "Previews", $R['Previews']); ?></td>
                                </tr>
                                <tr>
                                    <td class="form-hdr">Email Notifications:</td>
                                    <td nowrap class="form-data">
                                        <p class="medium"><?
                                                            DBField($Table, "BulletinEmail", $R['BulletinEmail']); ?>
                                            Receive Bulletin Email notifications.<br><?
                                                                                        DBField($Table, "NotifyNew", $R['NotifyNew']); ?>
                                            Notify me of any newly approved articles for my group(s).<br>
                                            <?
                                            DBField($Table, "NotifyUpdated", $R['NotifyUpdated']); ?>
                                            Notify me when any articles for my groups are updated. <br>
                                            <? DBField($Table, "NotifySubmitted", $R['NotifySubmitted']); ?>
                                            Notify me when an article I had submitted am a contact for or reviewed is updated.<br>
                                            <? DBField($Table, "NotifyTechnicalReview", $R['NotifyTechnicalReview']); ?>
                                            Notify me when an article requires a Technical Review.<br>
                                            <? DBField($Table, "NotifyContentReview", $R['NotifyContentReview']); ?>
                                            Notify me when an article requires a Content Review.<br>
                                        </p>
                                    </td>
                                </tr>
                                <tr valign="middle">
                                    <td colspan="2">
                                        <div align="center">
                                            <input name="Save" type="submit" id="Save" value="Save">
                                            <? if ($AppDB->Settings->AuthenticationMode != "NT") { ?>
                                                <input name="Password" type="submit" id="Password" value="Change Password">
                                            <? } ?>
                                            <input onClick="window.location='home.php'" name="Back" type="button" id="Back" value="Back">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</center>
</body>

</html>