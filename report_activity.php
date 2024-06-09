<? include("config.php");
   include("graph/jpgraph.php");
   include("graph/jpgraph_bar.php");
   include("graph/jpgraph_line.php");
     
   RequirePriv(PRIV_APPROVER);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $AppDB->Settings->AppName ?> - Reports</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link REL="stylesheet" HREF="styles.css"></link>
</head>
<body>
<SCRIPT LANGUAGE="JavaScript" SRC="lib/misc.js"></SCRIPT>

<? include("header.php"); ?>

<div align="center">
<br><br>

<?
function bar_chart($file,$datax,$data_y1,$data_y2,$title,$xtitle,$ytitle,$targs = "",$alts = "")
{
	DEFINE("GRAPH_FONT",FF_ARIAL);  
	DEFINE("GRAPH_FONT_TITLE",FF_ARIAL);
	
	DEFINE("FONT_SMALL",ttf_size(11));
	DEFINE("FONT_MED",ttf_size(12));
	DEFINE("FONT_MEDL",ttf_size(13)); 
	DEFINE("FONT_LARGE",ttf_size(15)); 

	// Setup the graph. 
	$tsize = 15;  // Font size for the title
	$title_lines = 1;
	if (strlen($title) > 60) $tsize = 14.5;
	if (strlen($title) > 80) $tsize = 14;
	if (strlen($title) > 100) {
		$tsize = 15;
		$title = wordwrap($title,88,"\n");
		$title_lines = substr_count($title,"\n");
	}	
	$graph = new Graph(700,320 + ($title_lines * 12),"auto");
	$graph->img->SetMargin(80,25,18 + ($title_lines * 12),40);
	
	$graph->SetScale("textlin");
	$graph->SetMarginColor("#F0F0F0");
	$graph->SetShadow();

	// Create the bar pot
	$bplot = new BarPlot($data_y1);
	$bplot->SetWidth(0.6);
	$bplot->SetLegend("Article Hits");

	// Create the line plot
	$lplot = new LinePlot($data_y2);
	//$lplot->SetAlign("center");
	$lplot->SetColor("red");
	$lplot->SetLegend("Searches");
	
	$graph->img->SetMargin(40,140,40,80);
	
	$graph->legend->Pos(0.03,0.5,"right","center");
	

	// Create targets for the image maps. One for each column
	if ($targs) {
		$bplot->SetCSIMTargets($targs,$alts);
	}
	
	// Setup color for gradient fill style 
	$bplot->SetFillGradient("navy","lightsteelblue",GRAD_HOR);
	$bplot->SetColor("navy");
	
	$graph->Add($bplot);
	$graph->Add($lplot);

	// Set up the title for the graph
	$graph->title->Set(stripslashes($title));
	$graph->title->SetMargin(8);
	$graph->title->SetColor("black");
	$graph->title->SetFont(GRAPH_FONT,FS_BOLD,ttf_size($tsize));

	// Setup font for y axis
	//$graph->yaxis->SetColor("black",GRAPH_LABEL_COLOR);
	$graph->yaxis->SetFont(GRAPH_FONT,FS_NORMAL,FONT_SMALL);
	$graph->yaxis->title->Set($ytitle);
	//$graph->yaxis->title->SetFont(GRAPH_FONT,FS_BOLD,FONT_SMALL);

	// Setup X-axis title (color & font)
	//$graph->xaxis->SetColor("black",GRAPH_LABEL_COLOR);
	$graph->xaxis->SetFont(GRAPH_FONT,FS_NORMAL,FONT_SMALL);
	$graph->xaxis->title->Set($xtitle);
	//$graph->xaxis->title->SetColor(GRAPH_LABEL_COLOR);
	//$graph->xaxis->title->SetFont(GRAPH_FONT,FS_BOLD,FONT_SMALL);
	
	$graph->xaxis->SetTickLabels($datax);
	if (count($datax) > 25) {
		$graph->xaxis->SetTextLabelInterval(2);
	}
	
	$graph->Stroke(APP_ROOT_DIR . $file);   

	if ($targs) {
		echo $graph->GetHTMLImageMap("imap");
	}
}

$file = graph_file("activity");

if ($month == "") $month = date("m");
if ($year == "") $year = date("Y");

$s_month = $month;
$s_year = $year;

$date_start = $year . "-" . $month . "-01";
$t = mktime(2,0,0,$month,1,$year);
$month_name = date("M",$t);
++$month;
if ($month > 12) { $month = 1; $year++; }
$date_end = $year . "-" . $month . "-01";

$n_month = $p_month = $s_month;
$n_year = $p_year = $s_year;

++$n_month;

if ($n_month > 12) { $n_month = 1; $n_year++; }
$cur_month = date("m");
if ($n_month > $cur_month && $n_year >= date("Y")) $n_month = 0;

--$p_month;
if ($p_month < 1) { $p_month = 12; --$p_year; }

$q = "select count(*) as Count,datepart(day,CREATED) as Day from Hits " .
     "where CREATED >= '". $date_start ."' and CREATED < '" . $date_end . "' group by datepart(day,CREATED) order by Day";

$res = $AppDB->sql($q);
$min_day=28;
while($R = $AppDB->sql_fetch_obj($res)) {
		$data_hits[$R->Day] = $R->Count;
		$max_day = max($max_day,$R->Day);
		$min_day = min($min_day,$R->Day);
}	  

$q = "select count(*) as Count,datepart(day,CREATED) as Day from Searches " .
     "where CREATED >= '". $date_start ."' and CREATED < '" . $date_end . "' group by datepart(day,CREATED) order by Day";

$res = $AppDB->sql($q);
while($R = $AppDB->sql_fetch_obj($res)) {
		$data_searches[$R->Day] = $R->Count;
		$max_day = max($max_day,$R->Day);
		$min_day = min($min_day,$R->Day);
}

$data_y1 = array();
$data_y2 = array();
if ($min_day >= $max_day) $min_day = $max_day - 1;
for($i = $min_day; $i < $max_day + 1; ++$i) {
	$data_y1[] = $data_hits[$i];
	$data_y2[] = $data_searches[$i];
	$datax[] = $i;
}
bar_chart($file,$datax,$data_y1,$data_y2,"KB Activity for $month_name $year","Day","Article Hits");

?>

<img src="<? echo $file ?>" border=0>
<table border="0" cellspacing="0" cellpadding="8" width="90%"><tr><td align="left">
<button onClick="window.location='admin_reports.php'">Back</button>
<button onClick="window.location='<? echo $PHP_SELF . "?month=$p_month&year=$p_year"?>'">&lt;&lt; Previous Month</button>
<button <? if ($n_month == 0) echo "disabled" ?> onClick="window.location='<? echo $PHP_SELF . "?month=$n_month&year=$n_year"?>'">Next Month &gt;&gt;</button>
</td></tr></table>
</div>

</body>

</html>