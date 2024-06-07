<?
	// Store categorization in Buffer Table when user hits Copy button
	
	// Remedy Workflow added to read from the KB table to pull and paste into current form
	
	// What if logged on as different user on Remedy???

	//?KB="+KB+"&CatType="+CatType+"&CatT1="+CatT1+"&CatT2="+CatT2+"&CatT3="+
	//CatT3+"&CatProd="+CatProd+"&CatOrg="+CatOrg+"&CatGroup="+CatGroup;							

include_once("../config.php");
$_GET['CatType'] = "&nbsp;";
	$REC['KB'] 		= trim(html_entity_decode($_GET['KB']));
	$REC['CatType'] = trim(html_entity_decode($_GET['CatType']));
	$REC['CatT1'] 	= trim(html_entity_decode($_GET['CatT1']));
	$REC['CatT2'] 	= trim(html_entity_decode($_GET['CatT2']));
	$REC['CatT3'] 	= trim(html_entity_decode($_GET['CatT3']));
	$REC['CatProd'] = trim(html_entity_decode($_GET['CatProd']));
	$REC['CatOrg'] 	= trim(html_entity_decode($_GET['CatOrg']));
	$REC['CatGroup'] = trim(html_entity_decode($_GET['CatGroup']));
	
	$RemDB = OpenRemedyDB();
	
	if ($RemDB && $CUser->UserID) {
	
		if ($RS && $RS->RecordCount() > 0) {
			$RemDB->update_record($CUser->UserID,'KBRemedyBuffer',$REC);
		}
		else {
			$RemDB->insert_record("KBRemedyBuffer",$REC);
		}
	} else {
		error_log("Unable to Save Categorization for Remedy");
	}		
?>