<?
/**
 * Opens the specified Case in ARUser program by streaming a .ARTASK file to the browser.
 *
 * Params are &Server=Server&ID=CaseID&Form=HPD:HelpDesk
 *
 * @author Steve Drew <sdrew@softperfection.com>
 * @version 1.0
 * @package RemedyPortal
 */
/**
 */

	$Form = $_GET['Form'];
	$Server = $_GET['Server'];
	$ID = $_GET['ID'];
		
	  if ($Form == "") $Form = "HPD:Help Desk";
	  	  
      header("Content-type: application/ARTask");
      header("Content-Disposition: attachment; filename=Open" . str_replace(":","",$Form) . "Case.ARTask" );
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
      header("Pragma: public");
	  if (strlen($ID) != 15) {
	  	if (substr($ID,0,3) == "INC") {
			$ID = substr($ID,3);
		}
		$ID = sprintf("INC%012d",$ID);
	  }
	  echo "[Shortcut]\nName = $Form\nType=0\nSJoin = 0\nServer = $Server\nTicket = $ID\n";
?>
