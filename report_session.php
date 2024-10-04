<?  
	include("config.php");
	RequirePriv(PRIV_ADMIN);
	$ID = GetVar("ID");
	if ($ID == "") { header("location: admin_reports.php"); exit; }
    
    $msg = GetVar('msg');
    
 ?>
<html>

<head>
<title><? echo $AppDB->Settings->AppName ?> - Administration</title>
<link REL="stylesheet" HREF="styles.css"></link>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>
</head>
<? include("header.php");  ?>
<body>
<center>
  <?  ShowMsgBox($msg); 
  	$DefRange = "5";
 
  
  // Here passed user id of Search
  $SearchRec = $AppDB->GetRecordFromQuery("Select * from Searches where ID=$ID");
  $UserID = $SearchRec->CREATEDBY;
  $STime = $SearchRec->CREATED;
  $UserRec = $AppDB->GetRecordFromQuery("select * from " . USERS_TABLE . " where Username = '$UserID'");
  
  // Find Time Range to work with.
  $rq = "Select * from Searches where CREATEDBY='$UserID' AND " .  
  				"CREATED > dateadd(minute, -$DefRange, '$STime') AND ".
				"CREATED <= dateadd(minute, $DefRange, '$STime') " .
				"order by CREATED";
				
  $SearchRange = $AppDB->sql($rq);
  $first = 1;
  while ($SRec = $AppDB->sql_fetch_obj($SearchRange)) {
  		if ($first) $SessionStart = $SRec->CREATED;
		$first = 0;
	  	$SessionEnd = $SRec->CREATED;
  }
  
  $nRead = $AppDB->count_of($x = "select ID from Hits where CREATEDBY = '$UserID' AND " .
  				"CREATED > '$SessionStart' AND ".
				"CREATED <= dateadd(minute, 1, '$SessionEnd')");
  ?>
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr align="left">
      <td height="14" colspan="3" class="subhdr">KB User Session Details</td>
    </tr>
    <tr>
      <td width="7%" height="14">&nbsp; <img src="images/users.jpg" width="56" height="55"></td>
      <td nowrap><strong><? echo $UserRec->FirstName . " " . $UserRec->LastName; ?></strong><br>(<? echo $UserID ?>)</td>
      <td width="79%"><table width="100%"  border="0">
        <tr>
          <td width="17%"><strong>Session Start:</strong></td>
          <td width="83%"><? echo $SessionStart ?></td>
        </tr>
        <tr>
          <td><strong>Session End:</strong></td>
          <td><? echo $SessionEnd ?></td>
        </tr>
        <tr>
          <td><strong>Articles Read:</strong></td>
          <td><? echo $nRead ?></td>
        </tr>
      </table><br><br>
      </td>
    </tr>
    <tr>
      <td align="right">&nbsp;</td>
      <td colspan="2" align="left">
	
		<?
			function fmt_kbid($str)
			{
				return sprintf("KB%06d",$str);
			}
			function fmt_kbtitle($str,$ID,$R)
			{
				return("<a target=_blank style=\"font-style:italic\" href=\"article.php?ID=$R[ArticleID]\" title=\"click to view in new window\">" . $str . "</a>");
			}
		
		// Return number of articles read between the > time of this search
		// and before the time of the next search or 1 minute, which ever is less.
		function fmt_ArticlesRead($Str,$ID,$Rec) 
		{

			global $AppDB;
			
			// Find next search i
			$NxtSearch = $AppDB->GetRecordFromQuery("Select * from Searches where CREATEDBY='$Rec[CREATEDBY]' AND " .
									"CREATED > dateadd(second,1,'$Rec[CREATED]') AND " .
									"CREATED <= dateadd(minute,2,'$Rec[CREATED]')");
									
									
			$qs = "Select Hits.*,Articles.Title from Hits left join Articles on Articles.ID = Hits.ArticleID where Hits.CREATEDBY='$Rec[CREATEDBY]' AND " .
							"Hits.CREATED > '$Rec[CREATED]' AND ";
			if ($NxtSearch) {
				$NxtSearchTime = $NxtSearch->CREATED;
				$qs .= "Hits.CREATED <= '$NxtSearchTime' ";
			} else {
				$qs .= "Hits.CREATED <= dateadd(minute,1,'$Rec[CREATED]')";
			}
						
			$CountRead = $AppDB->count_of($qs);
			//echo $qs;
			if ($CountRead > 0) {
				ob_start();
				$Fields["ArticleID:Article"] = "@fmt_kbid";
				$Fields["Title"] = "@fmt_kbtitle";		
				$LB = new ListBox('',$AppDB,$qs,$Fields,"Hits.CREATED","",'',0);
				$LB->width="100%";
				$LB->NoTopStats = 1;
				$LB->NoFrame = 1;
				$LB->NoTitles = 1;
				$LB->Display();
				$buffer = ob_get_contents();
				ob_end_clean();
				return $buffer;
			} else return "(none)";
		}
		
			// Search, Type, Matches, Articles Read
			unset($Fields);
			$Fields["Search"] = "";
			$Fields["SearchType:Type"] = "";
			$Fields["Matches"] = ":align=center";
			$Fields["ArticlesRead:Articles Read"] = "@fmt_ArticlesRead";
		
			$Sort = "CREATED";
			$q = "Select * from Searches " .
				 	"where CREATEDBY = '$UserID' AND " .
	  				"CREATED >= '$SessionStart' AND ".
					"CREATED <= dateadd(second,1,'$SessionEnd') ";	
			$LB = new ListBox('Searches:',$AppDB,$q,$Fields,$Sort,"",'',0);
			$LB->width="100%";
			$LB->Form=1;
			
			$LB->Display();	
		?>	  </td>
    </tr>
    <tr>
      <td align="right">&nbsp;</td>
      <td height="14" colspan="2">
	  </td>
    </tr>
  </table>
</center>


</body>

</html>