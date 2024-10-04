<? 
	include("config.php");
	$table = "ArticleAttachments";
    $Type = GetVar('Type');
    $Mode = GetVar('Mode');
	if (stristr($Type,"Archive")) $table = "ArchiveArticleAttachments";
	$ID = GetVar("ID");
	if ($ID) {
		$R = $AppDB->get_record_assoc($ID,$table);
 		if ($R) {
            $ext = '';
 			$defaultsaveas = $R["Filename"];
			$p = strrchr($defaultsaveas,'.');
			if ($p) {
				$ext = substr((string)$p,1);
                if ($ext == 'mht') {
                    $ext = 'html';
                }
			}

            //  image
			if (stristr("jpg,png,jpeg,gif,tif,bmp,jpe",$ext)) {
				header("Content-Type: image/$ext");
                echo $R['Attachment'];
			}
            // mht file
            else if ($ext == 'html') {
                echo $R['Attachment'];
            }
            // 
			else if ($Mode == 'attachment') {
				header("Content-Type: application/$ext");
                header('Content-Disposition: download; filename="' . $defaultsaveas  .  '"');
                header("Pragma: public");	                    
                echo $R["Attachment"];
			}
            else {
				header("Content-Type: application/$ext");
                header('Content-Disposition: inline; filename="' . $defaultsaveas  .  '"');
                header("Pragma: public");	                    
                echo $R["Attachment"];
            }
		}
	}
?>