<?php
 /**
  * AuditAction function is called (if it exists) to perfom any custom actions against the
  * current task.
  *
  * This provides great flexibility to do almost anything, for example set of an alarm of email if
  * someone searches for the work "Pirate"
  */
function AuditAction($Action,$AFields)
{
	// $AFields['ArticleID']	= If article admin related
	// $AFields['ID']			= If article read
	// $AFields['BulletinID']	= If Bulletin Added or Modified or Read
	// $AFields['Trail'] 		= Text to describe task	
	global $CUser;
	global $AppDB;
	
	//uncomment to trap to error file
	//error_log("AuditAction: $Action, " . print_r($AFields,1));
		
	// Default Behaviour is to log the Activity to the Activity Table.
	// Logs ArticleID, BulletinID, Action along with person performing the action.
	//
	$LogActivity = true;
	
	$Fields['Activity'] = $Action;
	if ($AFields['ArticleID']) {
		$Fields['ItemID'] = $AFields['ArticleID'];
		$Fields['Tbl'] = "Articles";
	}
	else if ($AFields['ID']) {
		$Fields['ItemID'] = $AFields['ID'];
		$Fields['Tbl'] = "Articles";	
	}
	else if ($AFields['BulletinID']) {
		$Fields['ItemID'] = $AFields['BulletinID'];
		$Fields['Tbl'] = "Messages";
	}
	else if ($AFields['SearchID']) {
		$Fields['ItemID'] = $AFields['SearchID'];
		$Fields['Tbl'] = "Searches";	
	}
		
	switch($Action) {
	
		case "ArticleRead":
			// Do action here:
			break;
		
		case "BulletinRead":			
			// example:
			// echo "Bulletin " . $AFields['BulletinID'] . "just read by " . $CUser->Fullname() . "<br>";
			break;
			
		case "HomePage":
			$LogActivity = false;
			// Do action here:
			break;
			
		case "Search":
			// Do Action here;
			break;
			
		case "Login":
			// Do any desired action here (do not cause any output on this action or login will fail).
			break;
	
		case "ArticleCreated":
			// Do action here:
			break;
		
		case "ArticleModified":
			// Do action here:
			break;
			
		case "ArticleDeleted":
			// Do action here:
			break;
		
		case "DeleteAttachment":
			// Do action here:
			break;
			
		case "ModifyBulletin":
			// Do action here:
			
			break;
		
		case "AddBulletin":
			// Do action here:
			break;
		
		case "AddAttachmentContent":
			// Do action here:
			break;
		
		case "AddAttachment":
			// Do action here:
			break;

		case "AddContent":
			// Do action here:
			break;
		
		case "ArticleRestored":
			// Do action here:
			break;
		
		case "AddNote":
			break;
		
		case "EditNote":
			break;

		case "DeleteNote":
			break;
					
		default:
			// Unknown action
			break;
	}

	if ($LogActivity) {
		$AppDB->insert_record("Activity",$Fields);
		// Occasionally attempt a cleanup of Activity table
		if (rand(1,50) == 5) {
			$NDays = $AppDB->Settings->SearchHistoryDays;  // Activity Retention is same as SearchHistory
			if ($NDays == "") $NDays = 100;
			$AppDB->sql("delete from Activity where DATEDIFF(day,CREATED,GetDate()) >= $NDays","",0);					
		}
	}
}

?>