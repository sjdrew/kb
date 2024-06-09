<? 
	include("config.php");
	$table = "ArticleAttachments";
	if (stristr($Type,"Archive")) $table = "ArchiveArticleAttachments";
	$ID = GetVar("ID");
	if ($ID) {
		$R = $AppDB->get_record_assoc($ID,$table);
 		if ($R) {
 			$defaultsaveas = $R["Filename"];
			$p = strrchr($defaultsaveas,'.');
			if ($p) {
				$ext = substr($p,1);
			}
			if (stristr("jpg,png,jpeg,gif,tif,bmp,jpe",$ext)) {
				header("Content-Type: image/$ext");
			}
			else {
				header("Content-Type: application/$ext");
		  //  	header("Content-type: application/vnd.ms-excel");
			}
			$Mode = GetVar("Mode");
			if ($Mode != "attachment") $Mode = "inline";
					
	        header('Content-Disposition: ' . $Mode . '; filename="' . $defaultsaveas  .  '"');
        	header("Pragma: public");	    
	        echo $R["Attachment"];
		}
	}
?>