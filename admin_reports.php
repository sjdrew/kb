<?
include("config.php");
RequirePriv(PRIV_APPROVER);
?>
<html>

<head>
    <title><? echo $AppDB->Settings->AppName ?> - Administration</title>
    <link REL="stylesheet" HREF="styles.css">
    </link>
    <SCRIPT LANGUAGE="JavaScript" SRC="misc.js"></SCRIPT>
</head>
<? $SECTION = "Section-ADMIN";
include("header.php");  ?>

<body>
    <center>
        <? ShowMsgBox($msg); ?>
        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
                <td height="14">
                    <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">

                        <td width="250" valign="top" align="left"> <img src="images/spacer.gif" width="180" height="1" border=0>
                            <table style='background-image:"images/vert_bar.gif";border-right:1px solid navy;' width="87%" border="0" cellpadding="4" cellspacing="0">
                                <tr>
                                    <td align="center" width="36%"><img src="images/reports.jpg" width="32" height="32"></td>
                                    <td width="64%" align="left" valign="middle" class="hdr1">Reports</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="dots">.....................................</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <button onclick="window.location='admin.php'">Back</button>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="padding-left:20px;" colspan="2" valign="top">
                            <p>&nbsp;</p>
                            <ul>
                                <li><a href="report_active_articles.php">Article Read Report </a> - Displays a list of Articles which have been read during a given time period </li>
                                <li><a href="report_searches.php">Search History Report</a> - Displays searches and the resulting numer of matches. (Admin only) </li>
                                <li><a href="admin_articles.php?MustRead=Yes&ReadStats=1&S=Search&Sort=UnRead+desc">Must Read Articles</a> - Displays a list of Must Read Articles including Read/Unread status</li>
                                <li><a href="report_activity.php">Usage Report </a> - A graphical representation of KB Activity per month</li>
                                <li><a href="report_activity_log.php">Activity Log Report</a> - Report all activity for a specific time or user. (Admin only) </li>
                                <li><a href="report_mui.php">MUI Report</a> - Report of Multi User Incident Bulletins </li>
                            </ul>
                        </td>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>

</html>