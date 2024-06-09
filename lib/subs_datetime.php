<?
/**
 * Date/Time Routines
 * 
 * File: subs_datetime.php
 * Version: 1.0
 *
 * Author: softperfection.com
 *
 * SofPerfection grants unlimited, unrestricted use of this source code to modify
 * redistribute in any fashion. It is completely in the public domain.
 * No warranties are implied or provided.
 */
 
 
///////////////////////////////////////////////////
// Date routines
//
//
// At some point to support dates > 2038, need to use adodb-time.inc.php to
// replace the php date,mktime functions with ones that support this.
//


$WeekType   = array("first"=>1,"second"=>2,"third"=>3,"fourth"=>4,"last"=>-1);
$DaysOfWeek = array("Sunday"=>0,"Monday"=>1,"Tuesday"=>2,"Wednesday"=>3,"Thursday"=>4,
					"Friday"=>5,"Saturday"=>6);
$MonthNames = array("January"=>1,"Feburary"=>2,"March"=>3,"April"=>4,"May"=>5,"June"=>6,
					"July"=>7,"August"=>8,"September"=>9,"October"=>10,"November"=>11,"December"=>12);


function Now()
{
	return date("Y-m-d");
}

function NowDateTime()
{
	return date("Y-m-d H:i:s");
}

function DateStr($d)
{
	return substr($d,0,10);
}

function DateTimeStrFromValue($d,$FromGMT=0)
{
	if ($FromGMT) {
		$d = gmttolocal($d);
	}
	return date("Y-m-d H:i:s",$d);
}

//
// Input YYYY-MM-DD
// Add Days and return in same format
//
function AddDays($date,$days) 
{
	$dv = mktimefromstr($date);
	$dv += 86400 * $days;
	return DateStr(DateTimeStrFromValue($dv));
}

//
// Return HH:MM from DateTime string
//
function ExtractTime($date)
{
	return substr($date,11,5);
}

//
// Return YYYY-MM-DD plus add months
//
function MonthAdd($date = "", $AddMonths = 0)
{
	if ($date == "") {
		$date = Now();
	}
	list($year,$mon,$day) = split('-',$date);
	//$day = "01";
	$mon += $AddMonths;
	if ($mon > 12) {
  		$year += (int)($mon / 12);
  		$mon = $mon % 12;
 	}
	return sprintf("%d-%02d-%02d",$year,$mon,$day);
}

//
// Expects SQL date format YYYY-MM-DD HH:MM:SS
// If gmt, Converts from GMT to local user time
// return back in same format
//
function DateTimeStr($datestr,$gmt = 0)
{
	if ($gmt) {
		$DVal = str_replace("-","",substr($datestr,0,10));
		return DateTimeStrFromValue(mktimefromstr($datestr,0),1);
	} else {
		return substr($datestr,0,16);
	}
}

// In Text format ie "Sunday"
function DayofWeek($d,$FromGMT=0)
{
	if ($FromGMT) 
		$d = gmttolocal($d);
	return date("l",$d);
}

//
// Return YYYY-MM-DD of this sunday
//
function WeekStartingDate($AddWeeks=0)
{
	$w = date("w");
	$t = mktimefromstr(Now());
	$t -= ($w * 86400);
	$t += ($AddWeeks * (7 * 86400));
	return DateStr(DateTimeStrFromValue($t));
}
	
/*
 * Find the first, second, third, last, second-last etc. weekday of a month
 *
 * args: day 0 = Sunday
 *       which 1 = first
 *             2 = second
 *             3 = third
 *            -1 = last
 *            -2 = second-last
 *			   0 = everyweek
 *
 * Input in YYYY-MM-DD HH:MM:SS
 */
function WeekdayFind($FromDate, $day, $which) {

	$tm = DateFieldsFromStr($FromDate);
	$month = $tm->mon;
	$year = $tm->year;
	
    $ts = mktime(0,0,0,$month+(($which>=0)?0:1),($which>=0)?1:0,$year);    
    $done = false;
    $match = 0;
    $inc = 3600*24;
    while(!$done) {
        if(date('w',$ts)==$day) {
            $match++;
            if ($which == 0) {
            	$wkdays[$wkdayn] = $ts;
            	++$wkdayn;
            }
        }
        if($which != 0 && $match==abs($which)) $done=true;
        else $ts += (($which>=0)?1:-1)*$inc;
        if ($which == 0 && $month != date('m',$ts)) {
        	$done = true;
        }
    }
    if ($which == 0) 
    	return($wkdays);
    else
	    return $ts;
}

//
// Convert a GMT date/time back to local time for current user.
// Have to factor in whether we are in daylight savings now and
// whether the date to be displayed is in it also.
// We are assuming that every user does use Daylight Savings
// TODO: see if browser can tell us if DST is used.
function gmttolocal($t)
{
	global $C_TZOffset;

	$UZ_mins = ($C_TZOffset == "") ? 420 : $C_TZOffset;	
	
	$OffsetNow = date("O");
	$OffsetThen = date("O",$t);
	// ie -0600 and -0700
	// or -0700 and -0600
	// or +0100 and +0200
	str_replace("+","",$OffsetNow);
	str_replace("+","",OffsetThen);
	$diff = (($OffsetThen - $OffsetNow) / 100) * 60;		
	//echo "diff = $diff, $OffsetThen - $OffsetNow, <br>";
	$UZ_mins -= $diff;
	$t  -= (60 * $UZ_mins);
	return $t;
}

//
// Put into gmt, uses gmtoffset in affect for the date passed
// 
function timetogmt($t) 
{
	$gmtoff = date("O" , $t );
	$sign = substr($gmtoff,0,1);
	$hrs = substr($gmtoff,1,2);
	$mins = substr($gmtoff,3,2);
	$val = (60 * mins) + (3600 * $hrs);
	$t = ($sign == '-') ? ($t + $val) : ($t - $val);
	return $t;	
}

function DateFieldsFromStr($datetimestr)
{
	// expecting YYYY-MM-DD HH:MM:SS
	list($datestr, $timestr) = split(' ',$datetimestr);
	list($tm->year,$tm->mon,$tm->day) = split('-',$datestr);
	list($tm->hour,$tm->min,$tm->sec) = split(':',$timestr);

	return $tm;
}

function mktimefromstr($datetimestr,$ConvertToGMT=0)
{
	// expecting YYYY-MM-DD HH:MM:SS
	if (strlen($datetimestr) == 10) {
		$datetimestr .= " 00:00:00";
	}
	if (strlen($datetimestr) == 17) {
		$datetimestr .= ":00";
	}
	$tm = DateFieldsFromStr($datetimestr);
	
	$t = mktime($tm->hour,$tm->min,$tm->sec,$tm->mon,$tm->day,$tm->year);
	if ($ConvertToGMT) {
		$t = timetogmt($t);
	}
	return $t;
}
?>