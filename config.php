<script language="php">

////////////////////////////////////////////////////////////////////////////////////////////////
// Config and Init 
//
//////
DEFINE("APP_ROOT_DIR",str_replace("\\","/",dirname(__FILE__)) . "/"); 

//
// INSTANCE NAME is now Determined from install/Instance.ini file which is created during install
// and defaults to KB if not found
//
$_Instance = "KB"; // In case .ini file not present
$_fp = @fopen(APP_ROOT_DIR . "install/Instance.ini","r");
if ($_fp) {
	while(!feof($_fp)) {
		$_line = fgets($_fp);
		if (stristr($_line,"Instance=")) { 
			list($_keyword,$_Instance) = explode("=",chop($_line),2);
			list($_DBNAME,$_DBHOST) = explode('@',$_Instance);
		    if ($_DBHOST == "") $_DBHOST = "localhost";
			break;
		}
	}
	fclose($_fp);
	unset($_line);
	unset($_fp);
}

DEFINE("SEARCH_MICROSOFT_KB",1);  // 1 = enable, 0 = disable
DEFINE("SEARCH_OFFICE",1);
DEFINE("NOTIFY_ON_ERROR","user@domain.com"); // Used only if cannot get default from Database settings
DEFINE("DEFAULT_SMTP_SERVER","mail.mydomain.com"); // used only if cannot get settings from database


DEFINE("DBNAME","$_DBNAME");
DEFINE("DBHOST","$_DBHOST");
DEFINE("DBUSER","KBApp");
//DEFINE("DBPASS",'kb$zz01'); // PROD SERVER PASSWORD
DEFINE("DBPASS",'bu11et%40'); // TEST SERVER PASSWORD
DEFINE("APP_NAME",DBNAME); // used by editor for virutal root name
DEFINE("FILES_FOLDER","files/"); 
DEFINE("FILES_VPATH", DBNAME . "/" . FILES_FOLDER);
DEFINE("COMPANY"," ");
DEFINE("SITENAME",COMPANY . DBNAME);
DEFINE("APPID",strtoupper(DBNAME));
DEFINE("SITE_URL","http://" . $_SERVER['SERVER_NAME'] . "/" . DBNAME . "/");
DEFINE("AUTHENTICATE_ID",strtoupper(DBNAME)."-Auth");
DEFINE("DBTYPE","mssql");
DEFINE("APP_VERSION", "1.10"); //overridden by database once updated
DEFINE("DBVERSION","1.10");
DEFINE("USERS_TABLE","users");
DEFINE("MAXROWS",5000);  // Searches are limited to this: TODO add to settings page

DEFINE("REMEDY_DBUSER","ARAdmin");
DEFINE("REMEDY_DBPASS","AR#Admin#");
DEFINE("REMEDY_VERSION","7");

define("AUTHENTICATION_MODE","Local"); // NT or LOCAL
define("ALLOW_GUESTS",1); // if true then auto create a User account on first access with Guest permissions
						  // else provide message that they must have an account. Valid for NT mode only

define("KB_UPDATE_SERVER","softperfection.com");
define("KB_UPDATES_FOLDER","kbupdates");
define("KB_UPDATE_URL","http://" . KB_UPDATE_SERVER . "/" . KB_UPDATES_FOLDER . "/kbupdate.dat?A"); // ptr to script

define("REMEDY_SHOW_CASE_URL","http://myis:81/Case.php?");
define("HIDE_BULLETINS","No"); // Yes to hide Bulletin feature

//
// FORM_STYLE Form Style used for Input pages
//
$FORM_STYLE    = 'style="background-color: #eaeaea" border="0" cellpadding="1" cellspacing="0" ';

//
// $CONTENT_STYLE = Content Style used for list or menu choice windows, defined elsewhere but required. 
//
$CONTENT_STYLE = 'style="background-color: #eaeaea; border-collapse: collapse" BORDER="1" CELLPADDING="4" CELLSPACING="0" ';

// 
// GLOBALS
//
global $printview;
global $AppDB;
global $SimulateID;
global $db_err_routine;
$db_err_routine = "db_err";

include_once("lib/ldap.php");
include_once("lib/mail/htmlMimeMail.php");
include_once("lib/CustomActions.php");
include_once("lib/subs_library.php");
include_once("lib/db_sqlsrv.php");
include_once("lib/subs_cal.php");
include_once("lib/subs_auth.php");
include_once("lib/listbox.php");
include_once("lib/listboxpref.php");
include_once("lib/subs_datetime.php");
include_once("lib/subs_kb.php");
include_once("lib/template.php");
//nocache();

</script>