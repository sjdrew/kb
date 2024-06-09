<? 	include("config.php"); 
	if (!$GroupID) {
		$GroupID = $CUser->u->GroupID;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Articles</title>

<script type="text/javascript">
// Framebuster script to relocate browser when MSIE bookmarks this
// page instead of the parent frameset.  Set variable relocateURL to
// the index document of your website (relative URLs are ok):
var relocateURL = "/<? echo DBNAME ?>/browse.php";

if(parent.frames.length == 0) {
  if(document.images) {
    location.replace(relocateURL);
  } else {
    location = relocateURL;
  }
}
</script>

<script type="text/javascript" src="lib/treemenu/mtmcode.js">
</script>

<script type="text/javascript">
// Morten's JavaScript Tree Menu
// version 2.3.2-macfriendly, dated 2002-06-10
// http://www.treemenu.com/

// Copyright (c) 2001-2002, Morten Wang & contributors
// All rights reserved.

// This software is released under the BSD License which should accompany
// it in the file "COPYING".  If you do not have this file you can access
// the license through the WWW at http://www.treemenu.com/license.txt

// Nearly all user-configurable options are set to their default values.
// Have a look at the section "Setting options" in the installation guide
// for description of each option and their possible values.
MTMDefaultTarget = "text";

/******************************************************************************
* User-configurable list of icons.                                            *
******************************************************************************/
MTMenuText = "<? echo DBNAME ?> Articles"
var MTMIconList = null;
MTMIconList = new IconList();
MTMIconList.addIcon(new MTMIcon("menu_link_external.gif", "http://", "pre"));
MTMIconList.addIcon(new MTMIcon("menu_link_pdf.gif", ".pdf", "post"));
MTMenuImageDirectory = 'lib/treemenu/menu-images/';
MTMenuCSSize = "74%";
MTMLinkedSS = false;
MTMRootCSSize = "80%";
MTMRootIcon = "kb_small2.gif";
MTMHeader = '<form style="padding:0; margin:0; padding-bottom:2;" target=_top name="MFilter" method="get" action="/<? echo DBNAME ?>/browse.php">' +
            '<table border=0><tr><td style="font-family: arial; font-size: 9pt; margin-left:10px;"><i>Group Filter:</i></td>' +
			'<td><? GroupDropList($GroupID,' style="width: 140px; height: 14px; font-family: arial; font-size:8pt" onchange="MFilter.submit()"',1); ?>' +
			'</td><tr><td style="font-family: arial; font-size: 9pt; margin-left:10px;"><i>Display by:</i></td>' +
			'<td><select name="Grouping" style="width: height: 14px; font-family: arial; font-size:8pt" onchange="MFilter.submit()">' +
			'<option <? if ($Grouping == "Product") echo "Selected" ?> value="Product">Product</option>' +
			'<option <? if ($Grouping == "Type") echo "Selected" ?> value="Type">Type</option></select>' +
			'</td></tr></table></form><hr>';	
</script>
<? 
/*
// 
// SAMPLE
//

// Main menu.
var menu = new MTMenu();

//addItem(text,URL,target,tooltip,icon) make target = _top for out of frame.
menu.addItem("Notes","home.php",null,"Notes");

// Sub Menu
menu.addItem("Nested Sub-menu #1");

	// Submenu
	var number_1 = new MTMenu();
	number_1.addItem("Page #1-1", "documents/page-1-1.html");
	number_1.addItem("Page #1-2", "documents/page-1-2.html");
	number_1.addItem("Submenu #1-1");
		// Sub-Sub Menu
		var number_1_1 = new MTMenu();
		number_1_1.addItem("Page1-1-1","mypage.html");
	number_1.makeLastSubmenu(number_1_1);

// makeLastSubmenu(menu,isExpanded,closeIcon,openIcon)
menu.makeLastSubmenu(number_1);
*/
?>
<script type="text/javascript">
// Main menu.
var menu = new MTMenu();

//addItem(text,URL,target,tooltip,icon) make target = _top for out of frame.


//menu.addItem("Summary","browse_summary.php",null,"Summary of Articles");
</script>
<? 
	ob_start();
	echo '<script type="text/javascript">' . "\n";
	// Select all disctinct Products and Active Types
	$pf = PrivFilter();
	if (trim($GroupID) != "") {
		$gf = "and GroupID=$GroupID ";
	}
	
	if ($Grouping == "") $Grouping = "Product";
		
	if ($Grouping == "Product") {
		$Res1 = $AppDB->sql("select distinct Product,Type,Count(ID) as N from Articles where STATUS='Active' $pf " .
					"$gf group by Product,Type order by Product,Type");
	} else  {
		$Res1 = $AppDB->sql("select distinct Type,Product,Count(ID) as N from Articles where STATUS='Active' $pf " .
					"$gf group by Type,Product order by Type,Product");
	}
	$n = 0;
	$hook = 0;
	$LastProduct = $LastType = "__";
	while($R1 = $AppDB->sql_fetch_obj($Res1)) {
		if ($Grouping == "Type") {
			$Type = $FolderItem = $R1->Type;
			$Product = $Item = $R1->Product;
		} else {
			$Type = $Item = $R1->Type;
			$Product = $FolderItem = $R1->Product;		
		}
		if ($Item == "") $Item = "(unspecified)";
		if ($FolderItem == "") $FolderItem = "(unspecified)";
		
		$FolderItem = str_replace('"','',$FolderItem);
		$Item = str_replace('"','',$Item);
		
		if ($Type == "") $Type = "(unspecified)";
		if ($Product == "") $Product = "(unspecified)";
		
		if ( ($Grouping == "Product" && $R1->Product != $LastProduct) ||
		     ($Grouping == "Type" && $R1->Type != $LastType)) {
			if ($hook) {
				echo 'menu.makeLastSubmenu(__subm_' . $n . ",1);\n";
				$hook = 0;
			}
			$furl = "browse_content.php?";
			$furl .= ($Grouping == "Product") ? "Product=". urlencode($Product) : "Type=". urlencode($Type);
			$furl .= "&GroupID=" . $_GET['GroupID'];
			echo 'menu.addItem("'. $FolderItem . '","' .$furl .'"' . ");\n";
			++$n;
			echo 'var __subm_' . $n . ' = new MTMenu();' . "\n";
		}
		echo '__subm_' . $n . '.addItem("'. $Item . " ($R1->N)\",\"browse_content.php?Product=" . urlencode($Product) . "&Type=" . urlencode($Type) . "&GroupID=" . $_GET['GroupID'] . "\");\n";
		$hook = 1;		
		$LastProduct = $R1->Product;
		$LastType = $R1->Type;
	}
	if ($hook) {
		echo 'menu.makeLastSubmenu(__subm_' . $n . ",1);\n";
	}
	echo '</script>';
	ob_end_flush();
?>
</head>
<body onLoad="MTMStartMenu(true)">
</body>
</html>
