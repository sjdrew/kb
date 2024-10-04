<? 
/**
 * listbox Class
 * 
 * File: listbox.php
 * Version: 2.0
 *
 * Author: softperfection.com
 *
 * Sofperfection grants unlimited, unrestrict use of this source code to modify
 * redistribute in any fashion. It is completely in the public domain.
 * No warranties are implied or provided.
 
 * TODO: Errors:    Fatal error: Cannot redeclare class result in D:\Reports\lib\listbox.php on line 45
 */
 
// ie $CONTENT_STYLE = ' BORDER="2" CELLPADDING="4" CELLSPACING="0" style="border-collapse: collapse" bordercolor="#D0D0D0" ';

define("DEFAULT_ITEMS_PER_PAGE",20);

//
// Performs sql query, and displays results using the Fields structure in a list window
//
class ListBox {

	var $title;
	var $AppDB;
	var $db;
	var $q;
	var $Fields;
	var $Sort;
	var $ModifyPage;
	var $subtitle;
	var $sortable;
	var $width;
	var $hlp;
	var $SumFlds;
	var $Style;
	var $CellStyle;
	var $TableStyle;
	var $limit;
	var $PageLinks;
	var $CmdBar;
	var $Frame;
	var $NoFrame;
	var $trSpec;
	var $TotalRows;
	var $Export;
	var $ExportXLS;
	var $XLS_BodyFmt;
	var $XLS_HdrFmt;
	var $ColIdx;
	var $RowIdx;
	var $hideIDCol; // for select boxes that hide prim key
	var $ScrollAfterRows;
	var $RowHeight;
	var $NoTopStats;
	var $NoTitles;
    var $Form;
    var $PageSize;
    var $Sort2;
    var $TableNum;
    var $TableID;
    var $Repost;
    var $UrlParam;
    var $DefaultSaveAs;
    var $PrevPageIndicator;
    var $NextPageIndicator;
    var $printview;
    var $CurPage;
    var $StartRow;
    var $TotalPages;
    var $LogQueryTime;
    var $Height;
    var $workbook;
    var $XLS_BodyFmtA;
    var $XLS_BodyFmtR;
    var $XLS_BodyFmtC;
    var $result;
    var $ListingRowCount;
    var $rowData;
    var $hdrRowData;
	
	function __construct($title,&$AppDB,$q,$Fields,$Sort="",$ModifyPage="",$subtitle="",$sortable=0,$width='90%',
                 $hlp="",$SumFlds=null,$Style="",$CellStyle="list",$limit="") 
	{
		static $TableNum;
   		$this->TableNum =& $TableNum;		
		$this->TableNum++;	
		$this->TableID = "LT" . $this->TableNum;

		// Also as params		
		$this->title = $title;					// Title of Listbox
		$this->AppDB = $AppDB;					// Pointer to ADODB structure
		$this->q = $q;							// Query to run to produce listing
		$this->Fields = $Fields;				// Fields (columns) to display
		$this->Sort = $Sort;					// Column to sort on
		$this->ModifyPage = $ModifyPage;		// URL to hyperlink first column to
		$this->subtitle = $subtitle;			// subtitle (optional)
		$this->sortable = $sortable;			// Set to one if Sort desired
		$this->width = $width;					// Width of listbox
		$this->hlp = $hlp;						// Help box, displayed on left column
		$this->SumFlds = $SumFlds;				// Array of fields to SUM
		$this->Style = $Style;					// CSS Style to use (default = global $CONTENT_STYLE)

		global $CONTENT_STYLE;	
		if ($this->Style == "")
			$this->Style = $CONTENT_STYLE;
			
		$this->CellStyle = $CellStyle;			// CSS Style to use (default = list)
		if ($this->CellStyle == "") $this->CellStyle = "list";
		
		$this->limit = $limit;					// If specified, pagination will be off
		
		$this->NoTopStats = 0;					// Do not display top line info
		$this->NoTitles = 0;					// No titles
		$this->Repost = 1;						// Do not repost
		$this->Form = 0; 						// Add Form Tag (sorting needs this)
		$this->TableStyle = "boxtable";			// Default table style
		$this->NoFrame = 0;						// Do not frame listing
		$this->Export = 0;						// Export to XLS
		$this->PageSize = DEFAULT_ITEMS_PER_PAGE; // Default Page size
		$this->ExportXLS = 1;					// true = XLS, false = CSV format when exporting
		$this->UrlParam = '';
		
		// Private
		$this->RowIdx = 0;
		$this->ColIdx = 0;
		$this->RowHeight = 19;
		$this->TotalRows = 0;

		$this->DefaultSaveAs = "";
		
		$this->PrevPageIndicator = "<img align=\"absbottom\" src=\"images/arrow_prev.gif\" border=0>";
		$this->NextPageIndicator = "<img align=\"absbottom\" src=\"images/arrow_next.gif\" border=0>";
	 	//$this->PrevPageIndicator = "<";
		//$this->NextPageIndicator = ">";			

		global $printview;
		$this->printview = $printview;
		
		if ($this->sortable) {
			$this->Sort = GetVar("Sort_$this->TableID",0,$this->Sort);
		}
		$this->CurPage = GetVar("Page_$this->TableID");	
		
	}

	// Does all the work for a default listbox, header, rows, etc
	function Display() 
	{
		$_SERVER['PHP_SELF'] = $_SERVER['PHP_SELF'];

		if ($this->Export) {
			$this->InitExport();

			$this->PageSize = -1;
			$this->NoFrame = 1;
			$this->CmdBar = 0;
			$this->printview = 1;
			$this->sortable = 0;
		}
		
		flush();
		ob_start();
		
		if ($this->Form)
			echo "<form name=\"F_$this->TableID\" method=GET action=\"".$_SERVER['PHP_SELF']."\">\n";

		if ($this->ScrollAfterRows) $this->PageSize = -1; // turn of if scrolling

		$this->PaginationInit();
		$this->Execute(); 
		$this->Pagination(); 
		$this->DisplayHdr();
		$this->DisplayRows();
		if ($this->NoFrame == 1) {
			if (!$this->Export) echo "</table>\n";
		}
		else {
			$this->DisplayEnd();
		}
			
		if ($this->Form) 
			echo "</form>";	

		ob_end_flush();
		if ($this->Export)
			$this->ExportClose();
	}

	//
	// Called before executing sql
	//
	function PaginationInit()
	{
		if ($this->limit != "" || $this->Export)  { // If limit or export specified dont paginate
			return;
		}		
		if ($this->CurPage == "ALL") { $this->PageSize = -1; return; }
		// CurPage is one based.
		if ($this->CurPage <= 0) $this->CurPage = 1;		
		// StartRow is zero based.
		$this->StartRow = ($this->CurPage - 1) * $this->PageSize;
	}
	
	//
	// Called after sql and we have a $result
	//
	function Pagination()
	{
		if ($this->limit != "" || $this->Export)  { // If limit or export specified dont paginate
			return;
		}
		
		//	Set Total Pages	
		if ($this->PageSize > 0) $this->TotalPages = (int)(($this->TotalRows + ($this->PageSize - 1)) / $this->PageSize);
		
		// If less than two pages Do not page, but still report total items
		if ($this->TotalPages < 2) {
			if (!$this->NoTopStats) 
				$this->PageLinks = "<span class=\"page\">&nbsp;<i>(" . number_format($this->TotalRows) . " Items)</i>&nbsp;</span>";
			return;
		}
		
		
		if ($this->CurPage > $this->TotalPages) $this->CurPage = $this->TotalPages;
	
		// StartRow is zero based.
		$this->StartRow = ($this->CurPage - 1) * $this->PageSize;

		if (!$this->NoTopStats) $PageLinks = "<i>(" . number_format($this->TotalRows) . " Items)</i>&nbsp; ";
		
		$PageLinks .= "Pages: ";
        $comma = '';
		// Show as Page: < 1..12,[14],15..31 >
		for($Page = 1, $n = 0; $n < $this->TotalRows; ) {
			if ($n == 0)  $PageLinks .= $this->page_link($this->CurPage - 1, $this->PrevPageIndicator) . " ";
			else $Page = (int)($n / $this->PageSize) + 1;
			if ($Page == $this->CurPage) {
				$PageLinks .= "$comma<span class=\"page\">[$Page]</span>";
				if ($Page == $this->TotalPages) $PageLinks .= $this->NextPageIndicator;
			}
			else if ($Page < $this->CurPage) {
				if ($Page == 2 && ($this->CurPage - $Page) >= 2) $PageLinks .= $this->page_link(1) . " .. ";
				if (($this->CurPage - $Page) <= 2) {
					$PageLinks .= $comma . $this->page_link($Page);
				}
			}
			else {
				if ($Page == $this->TotalPages) {
					if (($Page - $this->CurPage) > 2) {
						$PageLinks .= " .. " . $this->page_link($Page) . " ";
					}
					else {
						$PageLinks .= $comma . $this->page_link($Page) . " ";
					}
					$PageLinks .= $this->page_link($this->CurPage+1,$this->NextPageIndicator);
				}
				else if (($Page - $this->CurPage) <= 2) {
					$PageLinks .= $comma . $this->page_link($Page);
				}
			}
			$comma = " ";
			$n += $this->PageSize;
		}
		$this->PageLinks = '<div class="page" style="float:right;"><span style="white-space:nowrap;" >' . $PageLinks . "&nbsp;</span></div>\n";
	}

	function Reposting()
	{
		if ($this->Export) return;
		
		$NoRepost = "Page,ShiftUp,ShiftDown,SaveColumns,Cancel,SelectedItems,SelectColumns,SelectedColumns,AvailableItems,SaveColumns,SaveCompanyColumns,ResetCompanyColumns,ResetColumns,ResetUserColumns";
			
		foreach ($_GET as $f => $v) {
			if (strstr($NoRepost,$f)) continue;
			if (strstr($f,"Sort_")) continue;	
			if (strstr($f,"Page_")) continue;
			if (is_array($_GET[$f])) {
				foreach ($_GET[$f] as $vv) 
					hidden($f . "[]",$vv);
			} else 	hidden($f,$v);
		}
	}

	function SortSupport()
	{
		if ($this->Export) return;
		
		if ($this->Repost) { // fix Jan 2005
			$this->Reposting();
		}
    	hidden("Sort_" . $this->TableID,$this->Sort);
    
		echo '<script language="JavaScript">
			function sort_' . $this->TableID . '(f) 
		{			
			var sf;
			sf = FindElement("Sort_' . $this->TableID . '");
			if (!sf) return;
			var cs = sf.value;
			var form = sf.form;
			var p;
			var bdir = 0;
			if ((p = cs.indexOf(" desc")) != -1) {
				cs = cs.substr(0,p);
				bdir = 1;
			}
			if (cs == f) {
				if (bdir == 0) {
					f = f + " desc";
				}
			}
			sf.value = f; 
			form.submit(); 
		}
		</scr' . 'ipt>' . "\n";
	}


	function PageSupport()
	{
		if ($this->Export) return;
		if ($this->PageSize <= 0) return;
	
		hidden("Page_$this->TableID",$this->CurPage);
		echo '<scri' . 'pt language="JavaScript">';
		if ($this->TableNum == 1) {
			$np = $this->CurPage + 1;
			$pp = $this->CurPage - 1;
			echo '
			function page_next() { page_'.$this->TableID.'('. $np .'); }
			function page_prev() { page_'.$this->TableID.'('. $pp .'); }

			addKeyHandler(document);
			document.addKeyDown(39,page_next);
			document.addKeyDown(37,page_prev); ';
		}
		echo '
		function page_'.$this->TableID.'(n) { var pf = ';
		if ($this->Form) echo 'FindElement("F_'.$this->TableID.'");'; 
	 	else echo 'document.form;';
		
		echo 'pf.Page_'.$this->TableID.'.value=n; pf.submit(); }
		</scr' . 'ipt>' . "\n";
	}

	// Help Function for setting Page url
	function page_link($n,$sym="")
	{
		if ($sym == "") $sym = $n;
		if ($n <= 0) return $sym;
		return "<a title=\"Go to Page $n\" class=\"page\" href=\"Javascript:page_$this->TableID('$n')\">$sym</a>";
	}

	function DisplayHdr()
	{	
		
		// Need to repost posted fields so that when sort reposts to page we recalc same query
		if ($this->sortable) { 
			$this->SortSupport();
		}
		if ($this->TotalPages > 1) $this->PageSupport();
			
		$hdr_style = $this->CellStyle . "-hdr";
	
		if ($this->NoFrame == 0) {
			if ($this->LogQueryTime)
			    $this->subtitle .= '&nbsp;&nbsp;<span style="font-size:8pt">(' . $this->AppDB->QueryTime . ' secs)</span>';
			$this->Frame = new FrameBox($this->title,$this->width,$this->subtitle,$this->hlp,$this->PageLinks);
			$this->Frame->TableStyle = $this->TableStyle;
			
			if ($this->CmdBar) {
				$this->Frame->Set_TableID($this->TableID);
				$this->Frame->Set_CmdBar();
			}							
			$this->Frame->Display();		
	// below was here
		}
		else if (!$this->Export) echo '<div style="margin-bottom:6px" align="right">' . $this->PageLinks . '</div>';
		
		if (!$this->Export && ($this->ScrollAfterRows) && ($this->TotalRows > $this->ScrollAfterRows)) {
			$this->Height = ($this->RowHeight * $this->ScrollAfterRows) + $this->RowHeight;
			echo "<div style=\"height:$this->Height; overflow:auto\">";
		}	
			
		if (!$this->Export) echo '<table width="100%" ID="' . $this->TableID . '" ' . $this->Style . ">";

		if ($this->NoTitles) return;
		
		$this->StartRow();
					
        foreach($this->Fields as $key => $val) {    
			$F = $this->FieldParams($key,$val);
		
			
			if ($this->sortable && substr((string)$F->formatting,0,6) != "nosort") {
				$data = $this->HdrCelSortable($F->fld,$F->use_title,$this->Sort,$hdr_style);
			} 
			else {
				$data = $this->HdrCel($F->use_title);
			}
			if (trim((string)$F->cwidth)) $w = "width=\"$F->cwidth\""; 
			else $w = "";
			$this->PrintCel($hdr_style, "$w $F->formatting ", $data,1);
		}
		$this->EndRow();	
	} 
	
	function FieldParams($key,$val)
	{
        $F = new stdClass();
        
		$F->formatting = $F->fmt_func = "";
		
		$e = explode(':',$val,2);
		if (count($e) > 0) $F->cwidth = $e[0];
		if (count($e) > 1) $F->formatting = $e[1];
		
		unset($e);
		
		$e = explode('@',$F->cwidth);
		if (count($e) > 0) $F->cwidth = $e[0];
		if (count($e) > 1) $F->fmt_func = $e[1];
		unset($e);
		
		$e = explode(':',$key,2);
		if (count($e) > 0) $F->fld = $e[0];
		if (count($e) > 1) $F->use_title = $e[1]; 
		else $F->use_title = $F->fld;
		
		return $F;	
	}
	
	function HdrCel($s)
	{
		return $s;
	}
	
	function HdrCelSortable($colname,$use_title = "",$Sort = "",$Style="list-hdr")
	{	
		if (!$use_title)
			$use_title = $colname;
	
		$Style .= "-sort";  // now = list-hdr-sort
		$img='';
		if ($Sort) {
			$sn = $Sort;
			$stype = "up";
			if (($p = strpos($Sort," desc"))) {
				$sn = substr((string)$Sort,0,$p);
				$stype = "dn";
			}
			if ($sn == $colname) {
				$img = '&nbsp;&nbsp;<img src="images/icon_sort_' . $stype . '.gif" border="0">';
			}
		}
		return "<a class=\"$Style\" title=\"Click to sort by $use_title\" href=\"Javascript:sort_$this->TableID('$colname')\" >$use_title</a>$img";
	}

	function Set_trSpec($trSpec)
	{
		$this->trSpec = $trSpec;
	}
	
	function Set_NoFrame()
	{
		$this->NoFrame = 1;
	}
	
	function Set_CmdBar()
	{
		$this->CmdBar = 1;
	}

	function PrintCel($class,$formatting,$data,$bHdr = 0)
	{
		if ($this->Export) {
			if ($this->ExportXLS) {
				if ($bHdr) {
                    if (!$this->hdrRowData) $this->hdrRowData = [];
                    $this->hdrRowData[] = [$data,$formatting];
                }
				else {
                    if (!$this->rowData) $this->rowData = [];
                    $this->rowData[] = [$data,$formatting];
               
                    /*
					if (substr((string)$data,0,1) == '$' and strlen((string)$data) < 16) {
						$fmt = $this->XLS_BodyFmtA;
						$num = true;
						$data = substr((string)$data,1);
						$data = str_replace(',','',$data);
					}
					else if (stristr($formatting,"right")) {
						$fmt = $this->XLS_BodyFmtR;
					}
					else if (stristr($formatting,"center")) {
						$fmt = $this->XLS_BodyFmtC;
					}
					else $fmt = $this->XLS_BodyFmt;
					if (stristr($formatting,"number")) $num = true;
					if ($num) 
						$this->worksheet->write_number($this->RowIdx,$this->ColIdx,$data,$fmt);
					else {
						if (strlen((string)$data) > 50)
						  	$this->worksheet->set_column($this->ColIdx, $this->ColIdx, 40);							
						$this->worksheet->write_string($this->RowIdx,$this->ColIdx,$data,$fmt);
				         }
				
                     * 
                     */
                }
				$this->ColIdx++;
			}
			else echo "\"$data\",";
		}
		else echo "<td class=\"$class\" $formatting >$data</td>";
	}
	
	function EndRow()
	{
		if ($this->Export) {
			if ($this->ExportXLS) {
                
                if ($this->hdrRowData) {
                    $data = [];
                    foreach($this->hdrRowData as $d) {
                        $data[$d[0]] ='string';                  
                    }
                    $this->workbook->writeSheetHeader('Export',$data); 
                    $this->hdrRowData = null;
                }
                else {
                    $data = [];
                    foreach($this->rowData as $d) {
                        $data[] = $d[0];                  
                    }
                    $this->workbook->writeSheetRow('Export',$data);
                    $this->rowData = null;
                }
                
				$this->RowIdx++;
				$this->ColIdx = 0;
			}
			else echo "\n";
		}
		else echo "</tr>";
	}

	function StartRow($fmt="")
	{
		if (!$this->Export)
			echo "<tr $fmt >\n";
	}

	function Execute()
	{
		$this->ListingRowCount = 0;
		$query = $this->q;
		
		if ($this->Sort) {
			$query .= " order by $this->Sort";
			if ($this->Sort2) $query .= ",$this->Sort2";
		}
		
		if ($this->limit) {
			if ($this->AppDB->databaseType != "mssql") { // no limit support. TODO: add stored procedure					
				$query .= " $this->limit";
			}
		}		
		else if ($this->AppDB->databaseType == "mysql") {
			if ($this->StartRow == "") $this->StartRow = 0;
			if ($this->StartRow >= 0 && $this->PageSize > 0) {
				$query .= " limit $this->StartRow,$this->PageSize";
			}
		}
   		$this->result = $this->AppDB->sql($query);
		// Execute query for count

		if (!$this->result) return false;	

		// Rows in this Page of the listing		
		$this->ListingRowCount = $this->AppDB->RecordCount($query);		

		if ($this->AppDB->databaseType == "mysql") {
			// Total Rows in unpaginated listing
			$this->TotalRows = $this->AppDB->count_of($this->q);
		}
		if ($this->AppDB->databaseType == "mssql") {
			// Since mssql does not have the nice limit clause like mysql we return all rows
			// and loop thru later.
			$this->TotalRows = $this->ListingRowCount;
		}

	//	if ($this->ListingRowCount > $this->PageSize) $this->ListingRowCount = $this->PageSize;
		if ($this->ListingRowCount < 0) $this->ListingRowCount = 0;	
	
	}

	
	function DisplayRows()
	{ 
		// ModifyPage syntax:
		// 		page.php
		// or	page.php?stuff=abc
		// or 	page.php?stuff=abc:3  (3 = field index for link creation)
		// or 	@urlfmt_function  (formats the href link, gets passed record to work with)
		// last format could also be acheived by setting ModifyPage to blank and using fmt function
		// for the cell data and returning a url href string.
		//
		$ModifyPage = $this->ModifyPage;
		$lnkfld = '';
		$e = explode('@',$ModifyPage);
		$urlfmt = "";
		if (count($e) > 1) $urlfmt = $e[1];
		
		if (!$urlfmt) {  // If modify page is fully spec'd then use it
			if (strstr($ModifyPage,":")) {
				list($ModifyPage, $lnkfld) = explode(":",$ModifyPage,2);
			}
			$elem = "?";
			if (strstr($ModifyPage,"?")) {
				$elem = "&";
			}
		}
		
		$nRec = 0;
		$doTotals = is_array($this->SumFlds);
		$doingTotals = 0;
		
		$trSpec = $this->trSpec;
		
		$IDS="";
		$comma = '';	
	
		if ($this->AppDB->databaseType == "mssql") { // no limit support. TODO: add stored procedure	
			if ($this->limit) { 				
				list($start,$count) = explode(',',substr((string)$this->limit,6));
				$this->AppDB->Move($this->result,$start);			
			}
			else if ($this->StartRow && $this->PageSize > 0) {
				$this->AppDB->Move($this->result,$this->StartRow);
			}
		}
		
        $Totals = [];
		while (1) {
		
			if ($this->PageSize > 0 && $this->AppDB->databaseType == "mssql" && $nRec >= $this->PageSize) {
				$R = "";
			} else {
				$R = $this->AppDB->sql_fetch_array($this->result);
			}
				
			if ($R) {
				$nRec++;
			}
			else if ($nRec > 0 && $doTotals) {
				$doingTotals = 1;
			}
			else {
				break;
			}
		
			$trSpecSel = '';
			if ($trSpec == "" && !empty($R["ID"]) && $doingTotals == 0 && !$this->printview) {
				$trSpecSel = 'onmousedown="selRow(this,\'click\')" '; 
				$IDS .= $comma . "'" . $R["ID"] . "'"; $comma = ',';
			}	
		
			reset($this->Fields);

			$this->StartRow("$trSpec $trSpecSel");
			
			$first = 1;

            foreach($this->Fields as $key => $val) {
								
				$F = $this->FieldParams($key, $val);
				
				if ($doingTotals) {
					$data = $Totals[$F->fld];
				}
				else {
					$data = @$R[$F->fld];
				}
				$class = ($nRec & 1) ? $this->CellStyle . "2" : $this->CellStyle;
				$class = ($doingTotals == 0) ? $class : "list-hdr";
					
				if ($doTotals && !$doingTotals && in_array($F->fld,$this->SumFlds)) {
					$Totals[$F->fld] += $data;
				}
				if ($urlfmt) {
					$urlformatted = $urlfmt($R);
				}
				if ($F->fmt_func) {
					$func = $F->fmt_func;
					$data = $func($data,(isset($R["ID"])) ? $R['ID'] : null,$R);
				}
				if (trim((string)$F->cwidth)) {
					$F->formatting .= " width=\"$F->cwidth\"";
				}
				if ($first) {
					if ($doingTotals) {
						$this->PrintCel($class,$F->formatting . " style='font-weight:bold' ","Total");
					}
					else {
						if ($ModifyPage && !$this->printview) {
					 		if ($lnkfld) 
								$this->PrintCel($class,$F->formatting,"<a $this->UrlParam href=\"$ModifyPage" . "$R[$lnkfld]\">$data</a>");
							else if ($urlfmt) 
								$this->PrintCel($class,$F->formatting,"<a $this->UrlParam href=\"$urlformatted\">$data</a>");
							else				
								$this->PrintCel($class,$F->formatting,"<a $this->UrlParam href=\"$ModifyPage" . $elem . "ID=$R[ID]\">$data</a>");
					    }
						else { 
							if ($this->hideIDCol) {
								$this->PrintCel($class,'style="display:none"',$R[$this->hideIDCol]);
							}
							$this->PrintCel($class,$F->formatting,$data);
						}
					}
					$first = 0;
				} else {
					$this->PrintCel($class,$F->formatting,$data);
				}
			}
			$this->EndRow();
			if ($doingTotals) break;
		}
		// TODO: IDArray is being defined multiple times if listbox used multiple times on single page.
		if ($IDS && !$this->printview) echo "<script language=JavaScript>var IDArray=[$IDS];</script>\n";
	}


	// Mostly Standalone function
	function DisplayCel($data,$url="",$param="",$class="")
	{ 
		static $n;
		
		if ($class == "") $class = $this->CellStyle;
		if ($n & 1) $class .= "2";
		++$n;
	
		if ($url && !$this->printview) {
			echo "<td $param class=\"$class\"><a href=\"$url\">$data</a></td>";
		}
		else {
			echo "<td $param class=\"$class\">$data</td>";
		}
	}

	function DisplayEnd()
	{
    	if (!$this->Export) {
			echo "</table>\n";
			if ($this->Height) echo "</div>";	
		}
		$str = '';
		
		if ($this->ListingRowCount == 0) $this->ListingRowCount = $this->TotalRows;
		
		if ($this->TotalRows > $this->ListingRowCount) {
			$str =  "Displaying $this->ListingRowCount of $this->TotalRows items ";
			
			if ($this->limit == "") {
				$str .= $this->page_link($this->CurPage - 1,"<");
				if ($this->CurPage < $this->TotalPages) {
					$str .= " ";
					$str .= $this->page_link($this->CurPage + 1,">");
				} else $str .= ">";
			}
		} else if ($this->ListingRowCount > 3) {
			$str = "$this->ListingRowCount items";
		}
		
		if ($this->Frame)				
	    	$this->Frame->DisplayEnd($str);
	}
	
	function InitExport()
	{
  		// HTTP headers
		$base = basename($_SERVER['PHP_SELF'],".php");
		
		if ($this->DefaultSaveAs)  
			$defaultsaveas = $this->DefaultSaveAs;
		else 
			$defaultsaveas = "Export_".$base;
	
		if ($this->ExportXLS) $defaultsaveas .= ".xlsx";
		else $defaultsaveas .= ".csv";
		
		Header_Excel($defaultsaveas);

		if ($this->ExportXLS) {
			$fontsize = 9;

		  	require_once('xlsxwriter.class.php');

            //TODO: Formatting.
            
		  	// Creating a workbook
		  	$this->workbook = new XLSXWriter();

            /*
			$this->worksheet->set_margins_TB(.25);
			$this->worksheet->set_margins_LR(.4);
			$this->worksheet->set_landscape();
		 	$this->worksheet->fit_to_pages(1,99);
 */

		}
	}

	function ExportClose()
	{
		if ($this->ExportXLS) {
			$this->workbook->writeToStdOut();
		}
		exit; // end page.	
	}		

}

//
// Extends ListBox class to provide a Selection Box
//
class ListBoxSelection extends ListBox
{
	var $Height;
	
	// was sel_listing
	function __construct(&$AppDB,$q,$Fields,$Height,$Sort,$sortable=0,$limit="")
	{
		parent::__construct('',$AppDB,$q,$Fields,$Sort,"","",$sortable=0); 
				 				$this->Height = $Height;
		$this->limit = $limit;
		$this->ListingRowCount = 0;
		$this->hideIDCol = "ID";
		global $printview;
		if (!$printview) {
			$this->Set_trSpec('ondblclick="OnSelDblClick(this)" onClick="OnSelect(this)" class="FakeSelHiliteOFF"');
		} else {
			$this->Set_trSpec(' class="FakeSelHiliteOFF" ');
		}
		$this->CellStyle = "FakeSelCelSize";		
	}
	
	function Display()
	{
		$this->Execute();
		$this->DisplayHdr();
		$this->DisplayRows();
		$this->DisplayEnd();		
	}
	
	function DisplayHdr()
	{		
		// Need to repost posted fields so that when sort reposts to page we recalc same query	
		if ($this->sortable) $this->SortSupport();
			 	
		// Add last table wrapper for firefox issues 	
		echo '<table width="100%" border=0 cellspacing=0 cellpadding=0><tr><td align="center">' . "\n";
		echo '<div class="FakeSelBOXt" id="SelBoxt">' . "\n";
		echo '<table width="100%" border=0 cellspacing=0 cellpadding=0>';
		echo "<tr>\n";
        foreach($this->Fields as $key => $val) {
			list ($cwidth, $formatting) = explode(':',$val,2);
			list ($cwidth, $fmt_func) = explode('@',$cwidth);
			list ($fld, $use_title) = explode(':',$key,2);
			if (!$use_title) $use_title = $fld;
					
			if ($this->sortable) {
				$data = $this->HdrCelSortable($fld,$use_title,'');
			} 
			else {
				$data = $this->HdrCel($use_title);
			}
			
			$this->PrintCel("FakeSelHdr","width=\"$cwidth\" $formatting",$data,1);
		}
		// Filler to account for scroll Bar
		echo "<td class=\"FakeSelHdr\" nowrap width=\"4%\" $formatting >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "</tr>\n";
		echo "</table></div>\n";
		echo "<div class=\"FakeSelBOXb\" style=\"height:$this->Height;\" id=\"SelBox\">\n";
		echo '<table id="SelectTable" STYLE="table-layout:fixed;" width="100%" border=0 cellspacing=0 cellpadding=0>';
	}
	
	function DisplayEnd()
	{
		echo "</table></div></td></tr></table>\n";	
	}
}

class FrameBox
{
	var $title;
	var $width;
	var $subtitle;
	var $help;
	var $CmdBar;
	var $Style;
	var $CelStyle;
	var $PageLinks;
	var $TableID;
	var $DialogFrame;
    var $TableStyle;
	
	function __construct($title,$width,$subtitle="",$help="",$PageLinks="")
	{	
		$this->Style = "window-frame";
		$this->CelStyle = "window-cel";
		$this->TableStyle = "boxtable";
		$this->title = $title;
		$this->width = $width;
		$this->subtitle = $subtitle;
		$this->help = $help;
		$this->PageLinks = $PageLinks;
	}
	
	function Set_TableID($id)
	{
		$this->TableID = $id;
	}

	function Set_CmdBar()
	{
		$this->CmdBar = 1;
	}
		
	function DisplayHelpBox()
	{
		echo '
			<table cellpadding=0 cellspacing=0 width="100%" >
			<tr>
			<td width="150" align=left valign=top>
				<br>
				<table class="hlp-box"> 
					<tr>
					<td class="hlp-body" ><i>' . $this->help . '</i></td>
					</tr>
				</table>
			</td>
			<td valign="top" width="' . $this->width . '"><div style="width:' . $this->width . '">';		
		$this->width="100%";	
	}
	
	function CmdBarScript()
	{
		if ($this->CmdBar)
			return '<script language="Javascript">loadimage("images/icon_cmdbaron.gif"); loadimage("images/icon_cmdbaroff.gif");
				function CustomizeColumns(){ var df=document.form; if (df.SelectColumns) { df.SelectColumns.value=1; df.submit(); } else alert("Currently this page does not support Customizing the columns."); } </script>';
	}
	
	function CmdBarIcon()
	{
		if ($this->CmdBar)
			return '<div style="float:right;"><span><img onClick="ShowCmdBar()" style="cursor:hand" alt="Display or Hide the Command Bar that allows you to perform operations against this list."  name="CmdBarIcon"  src="images/icon_cmdbaron.gif"></span></div>';
	}
	
	function CmdBarHTML()
	{
		$CmdBarHTMLStr = "";
		
		if ($this->CmdBar) {
			$CmdBarHTMLStr = '
			<div id="CmdBar" style="display:none" class="cmdbar"><span>
			<a class="cmdbartext" href="JavaScript:ListSelectAll(\''.$this->TableID.'\')" title="Select all entries">Select All<a> |
			<a class="cmdbartext" href="Javascript:ListSelectNone(\''.$this->TableID.'\')" title="Unselect all entries">Select None<a> |
			<a class="cmdbartext" href="Javascript:ListSelectBetween(\''.$this->TableID.'\')" title="Select all entries between the First currently selected Row and the Next currently selected Row">Select Between<a> |
			<script language="JavaScript">
			if (FindElement("Page_'.$this->TableID.'")) {
				if (FindElement("Page_'.$this->TableID.'").value > 0)
				document.write(\'<a class="cmdbartext" href="JavaScript:page_'.$this->TableID.'(\\\'ALL\\\');" title="Unpaginate the listing to display all items on one page.">Display All<a> | \');
				else if (FindElement("Page_'.$this->TableID.'").value == "ALL")
				document.write(\'<a class="cmdbartext" href="JavaScript:page_'.$this->TableID.'(1);" title="Paginate the listing.">Paginate<a> | \');			
			}
			if (this.ListExport) {
				document.write(\'<a class="cmdbartext" href="JavaScript:ListExport(); void(0);" title="Export This List to Excel.">Export List to Excel<a> | \');
			} 
			if (this.ListModifySelected) {
				document.write(\'<a class="cmdbartext" href="JavaScript:ListModifySelected(); void(0);" title="Modify selected entries...">Modify Selected<a> | \');
			} 
			if (this.ListDeleteSelected) {
				document.write(\'<a class="cmdbartext" href="JavaScript:ListDeleteSelected(); void(0);" title="Delete selected entries...">Delete Selected<a> |\');
			} 
			</script>
			
			<a class="cmdbartext" href="JavaScript:CustomizeColumns(); void(0);" title="Customize the columns that are displayed on this page">Customize Columns<a> | 
			<a class="cmdbartext" href="JavaScript:showhelp(\'help/listing_cmd_bar.html\')">Help</a>		
			</span></div>';
		}
		return $CmdBarHTMLStr;	
	}
	
	
	// You can supply CMDBarHTML() function to provide dropdown cmd bar support
	function Display()
	{
		if ($this->help) {
			$this->DisplayHelpBox();
		}		
		
		echo $this->CmdBarScript();
		
	
		if ($this->DialogFrame) {

            echo '
                <table cellspacing="0" cellpadding="0" width="100%" border="0">
                <tr>
                    <td height="20%" id="DialogTitleArea" class="DialogTitle">'.$this->title.'</td>
                    </tr>
                <tr>';
        }
		else {
		
		/* Uses following:
		 * where window-frame is default for $this->Style
		 * class window-frame
		 *			window-frame.bn
		 *			window-frame.boxcontent
		 *				window-frame.boxcontent.boxtitle
		 *				window-frame.pagelinks
		 *				window-frame.boxtable
		 *				window-frame.boxfooter
		 */
		 echo '	 
		<table width="'. $this->width . '" border=0>
		  <tr>
		    <td valign="baseline" width="100%"><div class="window-frame"><b class="b1"></b><b class="b2"></b><b class="b3"></b><b class="b4"></b>
             <div class="boxcontent">
               	<div class="boxtitle" style="display:block; height:18px; "> <span style="float:left">' . $this->title . '</span>';
		if ($this->subtitle) {
			  echo '
            	<div><span style="margin-top:2px; font-weight:normal; white-space:wrap; float:left; font-size: 8pt; margin-left:10px;">';
			echo $this->subtitle;
			echo "</span></div>\n";
		  }	
		  echo $this->CmdBarIcon();
		  echo $this->PageLinks;
		  echo '
		 </div>
		 ';
		echo $this->CmdBarHTML();
		echo '
		  <table class="' .$this->TableStyle . '" CELLPADDING="0" CELLSPACING="0">
		    <tr>
              <td width="100%" CLASS="window-cel" ALIGN="center">
		';		
	   }

	}

	function DisplayEnd($DisplayMsg="")
	{		
		if ($this->DialogFrame) {
			echo '</td></tr></table>';
			return;
		}	
		echo '
			</td>
	    	</tr>
          </table>';
		if ($DisplayMsg) { 
			echo '<div class="boxfooter">' . $DisplayMsg . '</div>';
		}
		echo '</div>
        <b class="b4b"></b><b class="b3b"></b><b class="b2b"></b><b class="b1b"></b></div></td>
    		</tr>
		</table>';

		if ($this->help) {
			echo '
				</div></td>
				</tr>  
			</table>';
		}     	
	} 
}


?>