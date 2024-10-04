<?
/**
 * Calendar Routines
 * 
 * File: subs_cal.php
 * Version: 1.0
 *
 * Author: softperfection.com
 *
 * SofPerfection grants unlimited, unrestricted use of this source code to modify
 * redistribute in any fashion. It is completely in the public domain.
 * No warranties are implied or provided.
 */
function CalHeader($name, $editable=1, $MonthNameFormat=0,$x=40,$y=20,$showtime=0,$deftime=0,$windowmode=0)
{ 	
	if (!$editable) {
		return;
	}
	echo('<script language="Javascript" SRC="lib/date.js"></script>');
	echo("<script lanaguage=javascript>\n");
	if ($windowmode) {
		echo('var '.$name.'_Cal = new CalendarPopup();'."\n");
	} else {
		echo('var '.$name.'_Cal = new CalendarPopup("'.$name.'_Div");'."\n");	
	}
	echo(''.$name.'_Cal.setReturnFunction("'.$name.'_Set");'."\n");
	echo(''.$name.'_Cal.offsetX = ' . $x . ';'."\n");
	echo(''.$name.'_Cal.offsetY = ' . $y . ';'."\n");
	
	if ($showtime) {
		echo(''.$name.'_Cal.showTime = true;'."\n");
		if ($deftime) {
			echo(''.$name.'_Cal.defaultTime = "' . $deftime . '";'."\n");
		}
	}
	
	echo('document.write('.$name.'_Cal.getStyles());'."\n");
	echo("\n");
	if ($MonthNameFormat) {
		echo('function '.$name.'_Set(y,m,d,t) { FindElement("'.$name.'").value=""+d+"-"+sMonths[m-1]+"-"+y+t; }' ."</s" . "cript>\n"); 
	}
	else {
		echo('function '.$name.'_Set(y,m,d,t) { FindElement("'.$name.'").value=""+y+"-"+m+"-"+d+t; }' ."</s" . "cript>\n"); 
	}
}

function CalPopup($name, $editable, $value, $style, $size=14)
{
	if (!$editable) {
		echo($value);
		return;
	}
	if (strlen((string)$value) > $size) $value=substr((string)$value,0,$size);
	
	echo("<input type=\"text\" name=\"$name\" class=\"$style\" value=\"$value\" size=\"$size\" >&nbsp;");
	echo('<a href="#" name="'. $name .'_anchor" id="'.$name.'_anchor" onClick="'.$name.'_Cal.select(FindParentForm(this).' . $name .',\''.$name.'_anchor\',\'yyyy-MM-dd\');return false;" ><img border="0" src="images/calendar.gif" style="text-indent: 0; word-spacing: 0; margin: 0" align="top" ></a><DIV ID="'.$name.'_Div" Name="'.$name.'_Div" STYLE="position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></DIV>');
	echo('<input type=hidden name="'.$name."v\" >\n");
}
