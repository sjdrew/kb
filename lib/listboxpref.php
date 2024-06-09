<?
/**
 * listbox Class
 * 
 * File: listboxpref.php
 * Version: 1.0
 *
 * Author: softperfection.com
 *
 * SofPerfection grants unlimited, unrestrict use of this source code to modify
 * redistribute in any fashion. It is completely in the public domain.
 * No warranties are implied or provided.
 */

//include_once("listbox.php");
	
class ListBoxPref extends ListBox
{
	function ListBoxPref($title,$db,$q,$Fields,$Sort="",$ModifyPage="",$subtitle="",$sortable=0,$width='90%',
                 $hlp="",$SumFlds="",$Style="",$CellStyle="list",$limit="") 
	{
		parent::ListBox($title,$db,$q,$Fields,$Sort,$ModifyPage,$subtitle,$sortable,$width,
                 $hlp,$SumFlds,$Style,$CellStyle,$limit);
		global $CUser;
		if ($CUser->u->Pagination) $this->PageSize = $CUser->u->Pagination;
	}
}		 		
?>