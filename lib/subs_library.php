<?php
/**
 * Generic Routines
 * 
 * File: subs_cal.php
 * Version: 1.1
 *
 * Author: softperfection.com
 *
 * SofPerfection grants unlimited, unrestricted use of this source code to modify
 * redistribute in any fashion. It is completely in the public domain.
 * No warranties are implied or provided.
 */

function deep_copy($source,$dest){
	if(!file_exists($source))return FALSE;
	if (basename($dest) == "Thumbs.db") { @unlink($source); @unlink($dest); return TRUE; }
	if(!is_dir($source))return copy($source,$dest);
	else{
		if(strpos(realpath($dest),realpath($source))===0)return FALSE;
		if(!(file_exists($dest)&&is_dir($dest))&&!mkdir($dest))return FALSE;
		$b=TRUE;
		if ($handle = opendir($source)) {
			while (false !== ($file = readdir($handle))){
				if($file!="."&&$file!="..")$b=$b&&deep_copy("$source/$file","$dest/$file");
			}
			closedir($handle); 
		}
		else return FALSE;
		return $b;
	}
} 

function rmdirr($dirName) {
   if(empty($dirName)) {
       return;
   }
   if(file_exists($dirName)) {
       $dir = dir($dirName);
	   if (!$dir) return;
       while($file = $dir->read()) {
           if($file != '.' && $file != '..') {
               if(is_dir($dirName.'/'.$file)) {
                   rmdirr($dirName.'/'.$file);
               } else {
                   @unlink($dirName.'/'.$file); // or die('File '.$dirName.'/'.$file.' couldn\'t be Deleted!');
               }
           }
       }
	   $dir->close();
	   if ($file) $file = "/$file";
	   $path = $dirName.$file;
       @rmdir($path); // or die('Folder '.$path.' couldn\'t be deleted!');
   }
}

function get_url_contents($url,$proxy_name = "", $proxy_port = "")
{

  	$cont = '';
	if ($proxy_name == '') {
		// note: php version 4.4.2 will crash on fopen to url
		$fp = @fopen($url,"r");
		if (!$fp) return false;
		while(!feof($fp)) $cont .= fread($fp,4096);
		fclose($fp);
	}
	else {
	   	$fp = @fsockopen($proxy_name, $proxy_port, $err, $errstr, 10);
   		if (!$fp) {return false;}
	   	fputs($fp, "GET $url HTTP/1.0\r\nHost: $proxy_name\r\n\r\n");
   		while(!feof($fp)) { $cont .= fread($fp,4096); }
	   	fclose($fp);
   		$cont = substr((string)$cont, strpos($cont,"\r\n\r\n")+4);
	}
	return $cont;
} 


//	Perform a stack-crawl and pretty print it.
//	
//	@param printOrArr  Pass in a boolean to indicate print, or an $exception->trace array (assumes that print is true then).
//	@param levels Number of levels to display
//
function get_backtrace($printOrArr=true,$levels=9999)
{
		$s = '';
		if (PHPVERSION() >= 4.3) {
		
			$MAXSTRLEN = 64;
		
			$s = '<pre align=left>';
			
			if (is_array($printOrArr)) $traceArr = $printOrArr;
			else $traceArr = debug_backtrace();
			array_shift($traceArr);
			$tabs = sizeof($traceArr)-1;
			
			foreach ($traceArr as $arr) {
				$levels -= 1;
				if ($levels < 0) break;
				
				$args = array();
				for ($i=0; $i < $tabs; $i++) $s .= ' &nbsp; ';
				$tabs -= 1;
				$s .= '<font face="Courier New,Courier">';
				if (isset($arr['class'])) $s .= $arr['class'].'.';
				if (isset($arr['args']))
				 foreach($arr['args'] as $v) {
					if (is_null($v)) $args[] = 'null';
					else if (is_array($v)) $args[] = 'Array['.sizeof($v).']';
					else if (is_object($v)) $args[] = 'Object:'.get_class($v);
					else if (is_bool($v)) $args[] = $v ? 'true' : 'false';
					else {
						$v = (string) @$v;
						$str = htmlspecialchars((string)substr((string)$v,0,$MAXSTRLEN));
						if (strlen((string)$v) > $MAXSTRLEN) $str .= '...';
						$args[] = $str;
					}
				}
				$s .= $arr['function'].'('.implode(', ',$args).')';
				$s .= @sprintf("</font><font color=#808080 size=-1> %% line %4d, file: <a href=\"file:/%s\">%s</a></font>",
					$arr['line'],$arr['file'],$arr['file']);
				$s .= "\n";
			}	
			$s .= '</pre>';
			if ($printOrArr) print $s;
		}
		return $s;
}

//
// Convert html to plain text
//
function htmltotext($html,$force=0)
{
	if (($p = strpos($html,'<')) === false) return $html;
	$html = substr((string)$html,$p);
//	if (!$force && !strpos($html,"<")===false) 
//		return $html;
	$text = html_entity_decode($html . ">");
	$pattern="'<[\/\!]*?[^<>]*?>'si";
	$replace="";
	$text = preg_replace ($pattern, $replace, $text);
	return rtrim((string)$text,">");
}

//
// Convert HTML to readable TEXT 
// Formatted as best we can
//
function HTMLToReadableText($html)
{
	// Remove A links but display them using readable http text
	// 1 = http:, 2 = hostname, 3 = filespec, 4 = link name
	$text = preg_replace('/<a.*href="?(.*:\/\/)?([^ \/]*)([^ >"]*)"?[^>]*>(.*)(<\/a>)/', '$4 $1$2$3', $html );

	// Get Rid of Styles
	$text = preg_replace("'<style[^>]*>.*</style>'siU",'',$text);

	// Remove all current text line breaks
	$text = str_replace("\n","",$text);
	$text = str_replace("\r","",$text);

	// Reinsert a double break at the end of any table row
	$text = str_replace("</tr>","</tr>\n\n",$text);
	
	// todo: could do preg_replace to insert space between <\td><td> or <\th><th>
	
	// Now convert html line breaks to text line breaks
	$text = str_replace("<p>","\n\n",$text);
	$text = str_replace("<br>","\n",$text);
	$text = str_replace("<br />","\n",$text);
	
	// Get rid of all remaining tags
	$text = strip_tags($text);
	return $text;	
}



function thisURL()
{
	//$_SERVER['SERVER_PROTOCOL']  == HTTP/1.1
	return "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
}

//
// Expect associated Record array and creates globals.
//
function RecordToGlobals($F)
{
    foreach($F as $key => $val) {
		$GLOBALS[$key] = $val;				
	}
}

function GetVar($name,$PostOnly=0,$Default=NULL)
{
	if ($PostOnly == 0 && array_key_exists($name,$_GET)) return $_GET[$name];
	if (array_key_exists($name,$_POST)) return $_POST[$name];
	return $Default;
}		

//
// When magic quotes are on and we are posting back to a page and display contents
// in a input tag, the slashes must be removed. We want them on for database queries
// but after that is done and they are going to be used to display back in the input field
// then we must strip them.
// Since we are overwriting the global value if that has changed used it instead
//
function repost_stripslashes()
{
	
	if ($_POST) {
		foreach($_POST as $key => $val) {
			$GLOBALS[$key] = $val;		
		}
	}
}

function dropdownlist($name,$texts,$values,$cur,$param="",$blank=0)
{
	if (!is_array($values)) {
		$useindex = 1;
	}
	else if ($blank) {
		if ($blank != 1) { // use text value
			array_unshift($texts,$blank);	
			array_unshift($values," ");				
		}
		else {
			array_unshift($texts," ");	
			array_unshift($values," ");	
		}
	}
	$size = 'SIZE="1"';
	if (stristr($param,"SIZE=")) $size = "";
	
	echo "<SELECT $size NAME=\"$name\" $param >\n";
	
	$found = "";
	$useindex = null;
  	for($i = 0; $i < count($texts); ++$i) {
  		$sel = "";
  		if ($useindex) {
  			if ($cur == $i) {
  				$sel = "selected";
  			}
  		} else {
  			if ($values[$i] == $cur) {
  				$sel = "selected";
  				$found = $cur;
  			}
  		}
		$v = ($useindex == true) ? $i : $values[$i];					
   		echo "<option $sel value=\"" . htmlspecialchars((string)(string)$v) . "\">" . htmlspecialchars((string)(string)$texts[$i]) . "</option>\n";
	}
	//
	// If current is not found in list, add it as a valid choice
	//
	if (trim((string)$cur) != "" && $found == "") {
		$cur = htmlspecialchars((string)$cur);
   		echo "<option selected value=\"$cur\">$cur</option>\n";
	}
	echo "</select>";
	return ($found != "" ? $found : $values[0]);
}

function dropdownlistfromquery($name,&$AppDB,$query,$Current,$Blank=1,$param="",$FieldToUse="",$FieldValueToUse="")
{
	$result = $AppDB->sql($query);
	$texts = array();
	$values = array();
	if ($Blank != 1) { // use text value
		array_unshift($texts,$Blank);	
		array_unshift($values," ");				
	}
	else {
		array_unshift($texts," ");	
		array_unshift($values," ");	
	}

	if (!$FieldToUse) $FieldToUse = $name;
	if (!$FieldValueToUse) $FieldValueToUse = $FieldToUse;
	if ($result) while ($R = $AppDB->sql_fetch_array($result)) {
		$texts[] = $R[$FieldToUse];
		$values[] = $R[$FieldValueToUse];
	}
		
	return(dropdownlist($name,$texts,$values,$Current,$param));
}

// TODO: use showerrbox instead
function ShowErrorLine($msg="",$br=0,$color="red")
{
	if (!$msg)
		return;
	if ($br == 0) {
		$nobr = "<br>";
	}
	echo("<font color=$color><b>$msg</b></font>$nobr");
}

function ttf_size($pointsize)
{
  return ($pointsize / 96) * 72;
}

function hidden($name, $value)
{
	global $printview;
	if ($printview) return;
	echo "<input type=\"hidden\" name=\"$name\" value=\"" . htmlentities((string)$value) . "\">\n";
}

function htmlstr($string)
{
	echo htmlentities($string);
}

function unhtmlstr($string)
{
	$trans_tbl = get_html_translation_table (HTML_ENTITIES);
	$trans_tbl = array_flip ($trans_tbl);
	$s =  strtr ($string, $trans_tbl);
	return(stripslashes($s));
}

function html_encode($s)
{
	$trans = get_html_translation_table(HTML_ENTITIES); 
	$trans[" "] = "&nbsp"; 
	return(strtr($s, $trans));
}

function pq($s)
{
	echo(nl2br(htmlentities($s)));
}

function unamount_str($a)
{
	if (!isset($a)) return;
	$a = str_replace('$','',$a);
	$a = str_replace(',','',$a);
	return $a;
}


function amount_str($a)
{	
	$a = str_replace('$','',$a);
	$a = str_replace(',','',$a);

	return("$" . number_format($a,0));
}

// 
// Returns 2 decimal number
// If a is blank then return ""
//
function amount_str2($a)
{
	$a = str_replace('$','',$a);
	$a = str_replace(',','',$a);
	
	if ($a == "") return "";
	
	return("$" . number_format($a,2));
}

function nocache()
{	
	header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
	header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
}

function BusyImage($onoff, $msg="")
{
	global $printview;
	if (!$printview) {		
		if ($msg) {
			$msg = "<b><font color=red>$msg</font></b><br><br>";
		}
		if ($onoff) {
			echo '<span name="wait" id="wait"><p align="center">' . $msg . '<img boarder="0" src="images/animbar1.gif"></p></span>';
		}
		else {
			echo '<s' . 'cript language="JavaScript">
					if (window._browser && _browser == "dom") { var e = document.getElementById("wait"); if (e) e.style.display="none"; } 
					else if (window.browser && _browser == "nn4") { var e = document.layers["wait"]; if (e) e.visibility="hidden"; } 
					else if (document.all.wait) wait.style.display="none"; 
				  </scr' . 'ipt>' . "\n\n\n";
		}
	}
	flush();
}

function file_ext($f)
{
	$ext = "";
	$dpos = strrpos($f,".");
	if ($dpos) {
		$ext = substr((string)$f,$dpos+1);
	}
	return $ext;
}

function Header_Excel($filename) {
      header("Content-type: application/vnd.ms-excel");
      header("Content-Disposition: attachment; filename=$filename" );
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
      header("Pragma: public");
}

// Tab support
function ShowTabs($TabsList,$ActiveTab,$ClassPrefix="")
{
	global $HideTabs;
	if ($HideTabs) return;

	global $printview;
	if ($printview) {
		return;
	}
	global $_Tab;	
	if ($_Tab != "") $ActiveTab = substr((string)$_Tab,3); // reposted _Tab overrides ActiveTab
	if ($ActiveTab == "") $ActiveTab = $TabsList[0];
	hidden("_Tab", "Tab" . $ActiveTab);
		
	echo '
		 <div><img src="images/spacer.gif" height="1" width="1"></div>	
	     <table width="95%" cellspacing="0" cellpadding="2" border=0>
		 <tr>';
	$tabonclass = $ClassPrefix . "tabon";
	$taboffclass = $ClassPrefix . "taboff";
	$percent = 90;
	foreach ($TabsList as $Tab) {
		$useclass = ($Tab == $ActiveTab) ? $tabonclass : $taboffclass;
		echo '<td id="Tab' . $Tab . '" align="center" onclick="TabEnable(this)" class="' . $useclass . '">' . $Tab. "</td>\n";
		$percent -= 15;
	}
	if ($percent < 10) $percent = 10;
	$tabfillclass = $ClassPrefix . "tabfill";
	echo '<td class="'. $tabfillclass . '" width="'.$percent.'%" id="TabFill">&nbsp;</td>
		 </tr>
		</table>
		<div><img src="images/spacer.gif" height="5" width="1"></div>';
	return $ActiveTab;	
}

// Tab support
function ShowTabs3($TabsList,$ActiveTab,$ClassPrefix="",$bottom_spacer=5,$width="95%")
{
	global $HideTabs;
	static $TabGroup;
	if ($HideTabs) return;

	++$TabGroup;
	$TabGroupStr = "_Tab";
	if ($TabGroup > 1) $TabGroupStr .= "_$TabGroup";
	
	global $printview;
	if ($printview) {
		return;
	}
	$_Tab = GetVar($TabGroupStr);
	
	if ($_Tab != "") $ActiveTab = substr((string)$_Tab,3); // reposted _Tab overrides ActiveTab
	if ($ActiveTab == "") $ActiveTab = $TabsList[0];
	hidden("$TabGroupStr", "Tab" . $ActiveTab);
		
	echo '
	     <table style="width:' . $width . '" cellspacing="0" cellpadding="2" border=0>
		 <tr>';
	$tabonclass = $ClassPrefix . "tabon";
	$taboffclass = $ClassPrefix . "taboff";
	$tabfillclass = $ClassPrefix . "tabfill";
	$tabhoverclass = $ClassPrefix . "hover";
	
	$percent = 90;
	foreach ($TabsList as $Tab) {
		$useclass = ($Tab == $ActiveTab) ? $tabonclass : $taboffclass;
		echo '<td id="Tab' . $Tab . '" align="center" onclick="TabEnable(this,\''. $TabGroupStr .'\')" class="' . $useclass . '">' . 
		     "<a href=\"JavaScript:void(0)\">$Tab<a>"  . "</td>\n";
		$percent -= 10;
	}
	if ($percent < 2) $percent = 2;
	$tabfillclass = $ClassPrefix . "tabfill";
	echo '<td class="'. $tabfillclass . '" width="'.$percent.'%" id="TabFill">&nbsp;</td>';
	echo '	 </tr>
		</table>';
	if ($bottom_spacer > 0) {
		echo '
			<div><img src="images/spacer.gif" height="' . $bottom_spacer . '" width="1"></div>';
	}
	return $ActiveTab;	
}

function TabSectionStart($Tab,$ActiveTab,$Param="")
{
	global $HideTabs;
	if ($HideTabs) return;

	global $printview;
	if ($printview) {
		echo "<b>&nbsp;$Tab</b><hr size=1>";
		return;
	}

	$display = ($Tab == $ActiveTab) ? "inline" : "none";
	echo '<div ID="Tab'.$Tab.'Div" ' . " $Param " . ' style="display:' . $display . '">' . "\n";
}

function TabSectionEnd()
{	
	global $HideTabs;
	if ($HideTabs) return;

	global $printview;
	if ($printview) {
		echo "<br>";
		return;
	}

	echo "</div>\n";
}

function ShowMsgBox($msg,$align="left")
{
	if ($msg) {
		echo '<table width="100%" border="0"><tr><td width="70%" align="' . $align . '">
		      <div class="MsgBox"><ul style="padding-top:0; padding-bottom:0; margin-top:0; margin-bottom:0;" >';
		$lines = explode('<br>',$msg);
		foreach($lines as $line) {
			$line = trim((string)$line);
			if ($line)	echo "<li>$line</li>\n";
		}
		echo '</ul></div>
			  </tr></td></table>';
	}
}


//
// write out html that displays a ... button.
// when clicking on the button it opens a popup window with a list of choices
// clicking on one of the choices sets the FieldName value on FormName to this value
// and closes the popup.
//
function PopupFieldValues($TableName,$FormName,$FieldName) 
{
	echo '<button onclick=\'PopValues("'.$TableName.'","'.$FormName.'","'.$FieldName.'")\'>...</button>';
}

//
// Check each FieldDetails record for Required fields and make sure
// they are provided in posted Data
//
function ParseFields($Table,&$msg) 
{
	global $AppDB;
	$Errors = 0;
	$RList = $AppDB->MakeArrayFromQuery("select ID,ColumnName as ITEM from FieldDetails where TableName='$Table' AND Required='Yes'");

	foreach($RList as $FID => $FName) {
		if (trim((string)$_POST[$FName]) == "") {
			$msg .= "'$FName' cannot be blank.<br>";
			++$Errors;
		}
	}
	return $Errors;
}

//
// Note Blank must be "" for Non blank item, 1 for Blank item or any other value is text of first item with a null value
// 
function DBField($TableName,$FieldName,$Value,$rdonly = 0,$Blank = "", $QueryArg = "")
{
	global $AppDB;
	$F = $AppDB->GetRecordFromQuery("select * from FieldDetails where TableName = '$TableName' and FieldName = '$FieldName'"); 
	if (!$F) {
		echo "$TableName:$FieldName N/A";
		return;
	}
	
	$HTMLFieldName = $F->ColumnName;
	
	if ($rdonly == 1 && $F->Type == "DropList") {	
		if ($F->Query) {
			$Q = $F->Query . $QueryArg;
			$result = $AppDB->sql($Q);
			$texts = array();
			$values = array();
			if (!$F->QFieldText) $F->QFieldText = $HTMLFieldName;
			if (!$F->QFieldValue) $F->QFieldValue = $F->QFieldText;
			if ($result) while ($R = $AppDB->sql_fetch_array($result)) {
				$texts[] = $R[$F->QFieldText];
				$values[] = $R[$F->QFieldValue];
			}
		} else {
			$vlist = explode(",",$F->FieldValues);
			$values = $texts = array();
			foreach($vlist as $v) {
				list($text, $value) = explode(";",$v,2);
				if ($value == "") $value = $text;
				$values[] = $value;
				$texts[] = $text;
			}
		}
		for($i = 0; $i < count($texts); ++$i) {
			if ($values[$i] == $Value) {
				echo $texts[$i];
				return;
			}
		}
	}
	
	if ($rdonly == 1) {
		echo $Value;
		return;
	}
	
    $Dis = '';
	if ($rdonly == 2) {
		$Dis = " disabled ";
	}
	switch($F->Type) {
		case "TextBox":
		case "Text":
			$type = "text";
			if ($HTMLFieldName == "Password") $type = "password";
			echo "<input $Dis type=\"$type\"  value=\"" . htmlspecialchars((string)$Value) . "\" name=\"$HTMLFieldName\" title=\"$F->HelpText\" size=\"$F->HTMLSize\" maxlength=\"$F->MaxLength\" class=\"$F->Style\" $F->TagParams>\n";
			break;
		case "TextArea":
			echo "<textarea $Dis id=\"$HTMLFieldName\" name=\"$HTMLFieldName\" title=\"$F->HelpText\" cols=\"$F->HTMLSize\" rows=\"$F->MaxLength\" class=\"$F->Style\" $F->TagParams>" . htmlspecialchars((string)$Value) . "</textarea>\n";
			break;
		case "DropList":
			if ($F->Query) {
				//TODO: allow for variable substitution but need access to globals...
				//eval("\$Q = \"$F->Query\";");
				//$p = strstr($F->Query,'$');
				//if ($p) {
				//	$varname = substr((string)$p,1,
				
				$Q = $F->Query . $QueryArg;
				dropdownlistfromquery($HTMLFieldName,$AppDB,$Q,$Value,$Blank,$F->TagParams . $Dis,$F->QFieldText,$F->QFieldValue);
			} else {
				// allow for "One,Two,Three"  and "One;1,Two;2,Three;3"  formats
				$vlist = explode(",",$F->FieldValues);
				$values = $texts = array();
				foreach($vlist as $v) {
					@list($text, $value) = explode(";",$v,2);
					if ($value == "") $value = $text;
					$values[] = $value;
					$texts[] = $text;
				}
				dropdownlist($HTMLFieldName,$texts,$values,$Value,$F->TagParams . $Dis,$Blank);
			}
			break;
		case "CheckBox":
			$checked = ($F->FieldValues == $Value) ? "checked" : "";
			echo "<input $Dis type=\"checkbox\" $checked value=\"$F->FieldValues\" name=\"$HTMLFieldName\" title=\"$F->HelpText\" class=\"$F->Style\" $F->TagParams>\n";
			break;
		case "Radio":
			$checked = ($F->FieldValues == $Value) ? "checked" : "";
			echo "<input $Dis type=\"radio\" $checked value=\"$F->FieldValues\" name=\"$F->RadioGroup\" title=\"$F->HelpText\" class=\"$F->Style\" $F->TagParams>\n";
			break;
        case "Date":
			echo "<input $Dis type=\"$F->Type\"  value=\"" . htmlspecialchars((string)$Value) . "\" name=\"$HTMLFieldName\" title=\"$F->HelpText\" class=\"$F->Style\" $F->TagParams>\n";
            break;
		case "old_Date":
			// note: switched to using FieldName for date controls as otherwise cannot have two from
			// same field on same page. (ie start/end)
			CalHeader($FieldName, !$rdonly, 0, 0, 0,($F->HTMLSize > 14),0,1);
			CalPopup($FieldName, !$rdonly, $Value, $F->Style, $F->HTMLSize);
			break;
		default:
			break;
	}
}

function HelpFileName()
{
    clearstatcache();
	
	$filename = basename($_SERVER['PHP_SELF']);
	@list($file,$ext) = explode("\.",$filename);

	$hlpfile = "help/$file" . ".html";
	return $hlpfile;
}

function HelpIcon($echo = 1)
{
	$hlpfile = HelpFileName();
	
	if (file_exists($hlpfile)) {

        $str = '<a title="Help on this Page." href="Javascript:showhelp(\'' . $hlpfile . '\')"  ><img border="0" src="images/icon_help.gif" width="23" height="24" ></a>'; 
        
    	if ($echo) {
    		echo $str;
    	}
    	else {
    		return $str;
    	}		
	}		
}

function hyperlink(&$text)
{
	$text = preg_replace( "/(?<!<a href=\")((http|ftp)+(s)?:\/\/[^<>\s]+)/i", "<a target=_blank href=\"\\0\">\\0</a>", $text ); 
}

function HelpButton()
{
	$hlpfile = HelpFileName();
	
	if (file_exists($hlpfile)) {
		echo "
		<input TYPE=\"button\" VALUE=\"Help\" NAME=\"Help\" onclick=\"showhelp('$hlpfile')\" >";
	}
}

//
// Graph file info
//
function graph_file($type,$cache=0)
{
	$base = "images/graphs";
	$base_path = APP_ROOT_DIR . $base;
	$secs = time();
	$purge_time = 60;
	
	if ($dir = @opendir($base_path)) {
		while (($file = readdir($dir)) !== false) {
			if ($file == ".." || $file == ".") {
				continue;
			}
			$filepath = "$base_path/$file";
			$ctime = filectime($filepath);
			if (($secs - $ctime) > $purge_time) {
				@unlink($filepath);
			}
		}  
		closedir($dir);
	}
	$u = explode("@",getusername());
	$fname = sprintf("$base/%s_%s_%s.png",$u[0],$type,substr((string)$secs,6,4));
	return $fname;
}

function DisplayAttachmentsAddButton($Type,$ID,$AllowRW)
{
	if ($AllowRW) {
		$Dis = ($ID) ? "" : " disabled title=\"Press Save first\"";
    	echo '&nbsp;<input ' . $Dis . ' type="button" onclick="JavaScript:dialog_window(\'upload_attachment.php?Type=' . $Type . '&ID=' . $ID .  '\',480,220);"  value="Add" ID="AddAttachment" NAME="AddAttachment" style="font-size: 8pt; width: 28; height: 20">'; 
	}
}


function DisplayAttachmentsAddContentButton($Type,$ID,$AllowRW,$AttID)
{
	if ($AllowRW) {
		$Lbl = ($AttID) ? "Replace" : "Insert";
		$Dis = ($ID) ? "" : " disabled title=\"Press Save first\"";
    	echo '&nbsp;<input ' . $Dis . ' type="button" onclick="JavaScript:dialog_window(\'upload_attachment.php?AsContent=1&Type=' . $Type . '&ID=' . $ID . '&AttID=' . $AttID . '\',480,220);"  value="' . $Lbl . '" ID="AddAttachment" NAME="AddAttachment" style="font-size: 8pt; height: 20">'; 
	}
}

//
// $NonPics =  1 = only display attachments not recognized as pictures
//
function DisplayAttachments($Type,$ID,$AllowRW,$NonPics = 0, $AddButton = 0, $Q = "")
{
	global $AppDB;
	
	//if (!$ID) return;
							
	if ($AllowRW && $AddButton) { 
		if ($AddButton == 1) 
			DisplayAttachmentsAddButton($Type,$ID,$AllowRW);
		else { // 2
		    if ($ID) $CA = $AppDB->GetRecordFromQuery("select * from "  . $Type . "Attachments where " . $Type . "ID=$ID $Q");		
			DisplayAttachmentsAddContentButton($Type,$ID,$AllowRW,isset($CA->ID) ? $CA->ID : '');		
		}
	}
	if (!$ID) return;
	
	$n = 0;
    $result = $AppDB->sql("select * from "  . $Type . "Attachments where " . $Type . "ID=$ID $Q order by CREATED");
    if ($result) {
		$first = 1;
        while($AR = $AppDB->sql_fetch_obj($result)) {  
			if ($NonPics == 0 || !isPicture($AR->Filename)) { 
				++$n;
				if ($first) echo "<p style=\"margin:0; margin-top: 6px;\">\n";
				$first = 0;
            	echo '&nbsp;&nbsp;';
				if ($AllowRW) { 
					echo '<a title="Remove attachment" href="Javascript:delete_attachment(' . $AR->ID . ');"><img border="0" src="images/delete.gif" width="11" height="11"></a>&nbsp;';
				}
				$attachment_icon = GetAttachmentIcon($AR->Filename);
				echo $attachment_icon .
				    '<a target=_blank title="' . number_format($AR->Size) . ' bytes. Uploaded on ' . DateTimeStr($AR->CREATED) . ' by ' . $AR->CREATEDBY . '" href="show_attachment.php?Type=' . $Type . '&ID=' . $AR->ID . '">';
			    echo $AR->Filename . "</a> <font style='font-size=\"10px\"'>(" . number_format(sprintf("%2d",$AR->Size/1024)) . " KB)</font><br> \n";
            } 
		}
    } 
	return $n;
}

function GetAttachmentIcon($Filename)
{
				
	$ext = substr((string)$Filename,strrpos($Filename,".")+1,3);
	if ($ext == "") $extimage = "txt.gif";
	else {
		if (!stristr("jpg,gif,doc,pdf,txt,xls,rtf,zip",$ext)) $ext = "txt";				
			$extimage = "$ext.gif";
	}								
	return '<img style="margin-right:1px" align="absmiddle" height=16 width=16 border=0 src="images/' .$extimage .'">';
}

function ShowNotesBut($ID,$Table,$KeyField)
{
	global $printview;
	if ($printview) return;
	$disabled = '';
	if ($ID == "") { $disabled = "disabled"; $ID = 0; }
   	echo '<input type="Button" ' . $disabled . ' onclick="javascript:EditNote(' . "$ID,0,'$Table','$KeyField'" . '); void(0);" NAME="NewNoteBut" VALUE="Add Notes">';
}

function MonthName($M)
{
	$Monthstr = strftime("%b",mktime(4,0,0,$M));
	return $Monthstr;
}

function ShowNotes($Table,$ID,$KeyField,$printview,$StylePrefix="")
{
	global $AppDB;
	global $CUser;
	
	if (GetVar("DeleteNoteID") > 0) {
		if (IsPrivOrCreator(PRIV_ADMIN,"$Table",GetVar("DeleteNoteID"))) {
			$AppDB->sql("delete from $Table where ID=" . GetVar("DeleteNoteID"));
			AuditTrail("DeleteNote",array('ID' => $ID));
		}
	}
	
	if ($ID == "") $disabled = "disabled";

	$first = 1;
	// List the notes
	if ($ID > 0) {
        $Order = "desc";                       
        $result = $AppDB->sql("select * from $Table where $KeyField=$ID order by CREATED $Order");
				if ($result) {
        	while($NR = $AppDB->sql_fetch_obj($result)) { 
            	$NID = -1;
				$NID = AllowEditNote($Table,$NR,$KeyField);
				if ($first) {
					// Start the Table holder
					$first = 0;
					echo '<table width="100%" border="0" CELLPADDING="6" CELLSPACING="3" BORDERCOLOR="#CCCCCC" STYLE="border-collapse: collapse">';	
				}
            			echo '
        	<tr>
    	      	<td nowrap class="' . $StylePrefix . 'form-sm" WIDTH="20%" ALIGN="right" VALIGN="top">';
			    if (!$printview) echo '<a title="Delete this note" href="javascript:DeleteNote(' . "$ID,$NID" . '); void(0);"> <img src="images/icon_delete.gif" border="0" width="12" height="12"></a> <a title="Edit Note" href="javascript:EditNote(' . "$ID,$NID,'$Table'" . ",'$KeyField'".'); void(0);"><img src="images/i_pencilt.gif" border="0" width="12" height="12"></a>';
					echo DateTimeStr($NR->CREATED) . '<br>' . $NR->CREATEDBY;
                       	if ($NR->LASTMODIFIED) {
    	               	echo '<br><div title="Last Edited By: ' . $NR->LASTMODIFIEDBY . '">' .DateTimeStr($NR->LASTMODIFIED) . '<br>' .$NR->LASTMODIFIEDBY. ' (edited)</div>';
                      	}
						echo '
          </td>
    	      	<td nowrap class="' . $StylePrefix . 'form-notes-type" VALIGN="top">['.$NR->NoteType.']</td>
    	      	<td class="' . $StylePrefix . 'form-notes" WIDTH="80%" VALIGN="top">';
            			pq($NR->Notes); 
						echo '
		  </td>
          </tr>
		  <tr><td style="font-size:8px; line-height:8px" colspan="3"><hr style="border:none 0; border-top: 1px dashed #888; height: 1px; margin:0px;margin-right:20px;"></td></tr>';
		  
					} 
           		} 
           	}
	if (!$first) echo '</table></div>';
	else echo "</div><br>";
}
