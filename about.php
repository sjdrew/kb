<? include("config.php"); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - About</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<script language="JavaScript" src="lib/misc.js"></script>

<? include("header.php"); ?>

<img src="images/spacer.gif" width="180" height="1" border=0>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  	<tr><td height="14"> 	    
  	<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr valign="middle">
      <td width="8%" valign="top" align="left">&nbsp;	  </td>
      <td width="90%" colspan="2" valign="top"><p><img src="images/compglobe.gif" width="53" height="53" align="left"><br> 
	    <span class="hdr1">Service Desk Knowledge Base V<? echo $AppDB->Settings->AppVersion?> <? if(defined("ISDEMO")) { ?>(DEMO)<? } ?></span>
         <br>
        <span style="color:#555">by SoftPerfection.com</span>
        </p>
		<? if ($AppDB->Settings->RemedyARServer) { ?>
        <p><em><strong>with Remedy Integration.</strong></em></p>
		<? } ?>
       
		<? if (!$ShowUpdates) { ?>
        <p><a href="about.php?ShowUpdates=1">Display Update History</a> </p>
        <? } else {
  		$MAXTOSHOW = 20;

		$Ver = $AppDB->Settings->AppVersion;
		
		for($i = 0; $i < $MAXTOSHOW; ++$i) {
		
			$Ver -= .01;
			$Ver = sprintf("%.2f",$Ver);
			
			$file = "updates/$Ver/kbupdate.txt";
			$fp = @fopen($file,"r");
			
			if ($fp) {

				$line = fgets($fp);
				$a = explode('=',$line,2);
				$VersionStr = $a[1];
				if ($VersionStr == "") { 
					fclose ($fp);
					continue;
				}
				$line = fgets($fp);
				$a = explode('=',$line,2);
				$DateStr = $a[1];

				echo '<table width="90%" cellspacing=0 cellpadding=4 border=0><tr><th style="background: #CCCCCC;">Version: ' . $VersionStr . " - $DateStr</th><tr>";
				echo '<tr><td style="border: 1px solid #CCCCCC">';
				fgets($fp);
				fgets($fp);
				fgets($fp);
				while(!feof($fp)) {
					$line = fgets($fp);
					echo nl2br($line);
					
				}
				echo '</td></tr>';
				echo "</table>\n<BR><BR>";
				
				fclose($fp);
			}	
		
		}
  
   } ?>
        </p></td>
    </tr>
  </table>
  </td>
  </tr>
</table>
</body>

</html>