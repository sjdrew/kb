<? 	
	$noauth = true; // Bypass login required, needed for cmd line
	$SimulateID = 1; // Pretend we are Admin user
	
	include("config.php");

	global $db_err_routine;
	
// Call back override to print any database 
function print_db_err(&$db,$sql,$errno,$errmsg,$info="")
{
	global $AppDB;
	if ($errno == 7619) return;
	
	echo "Database Error $errno: $errmsg";
		
	exit();
}

    $db_err_routine = 'print_db_err';

	///////////////////////////////////////////////////////////////////////
	//
	// Expect csv file in format:
	// OLD_URL, KBID
	// 
	if ($argc != 2) {
		print "must specify csv file to process\n";
		exit;
	}
	$fp = @fopen($argv[1],"r");
	if (!$fp) {
		print "cannot open file $argv[1]\n";
		exit;
	}

	while($Data = fgetcsv($fp,1000,",")) {
		++$line;
		if ($line == 1) {
			continue;
		}
		if (count($Data) < 2) {
			print("Line $line, skipped no enough data\n");
			continue;
		}
		
		$KBID = $Data[1];
		$OLD_URL = $Data[0];
		print $KBID ;
		if (substr((string)$KBID,0,2) == "KB") {
			$KBID = substr((string)$KBID,2);
		}
		$KBID = (int)$KBID;
		
		if ($KBID == "" || $KBID < 1) {
			print "Line: $line, no KBID specified\n";
			continue;
		}		
		
		// Get Record from Attachments table for KBID that is used as the Content
		$res = $AppDB->sql("select * from ArticleAttachments where ArticleID=$KBID and AsContent = 1 ");	

		if ($res && $Record = $AppDB->sql_fetch_obj($res)) {
			
			print("Processing Article ID: $KBID - ");
				
			// Fix Content
			// Column "Attachment" contains content of attachment
			//
			// NOTE: Tested on WORD doc and beocmes corrupted as WORD has proprietery format that
			// cannot be used with binary search/replace. Tested on TXT file works ok.
			// For Word documents, will need to save on hard drive, run OLE search/replace, then
			// read contents back and save back in database.
			//
			$NewContent = str_replace($OLD_URL,"/KB/Article.php?ID=$KBID",$Record->Attachment);
				
			// Update record
			//		
			if ($AppDB->UpdateBlob("ArticleAttachments","Attachment",$NewContent,"ID=$Record->ID")) {
				print("Update OK\n");
			}
			else {
				print("Update failed\n");
			}
		}
		else {
			print("Line: $line, Attachment for Article $KBID not found\n");
		}
	}
	
?>