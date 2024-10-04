<?php

////////////////////////////////////////////////////////////////////////////////////////////////
// Config and Init 
//
//////

ini_set('error_log',__DIR__.'/logs/kb.log');
ini_set('display_errors', 'Off');

DEFINE("APP_ROOT_DIR",str_replace("\\","/",dirname(__FILE__)) . "/"); 

include_once('lib/DotEnv.php');
(new DotEnv(__DIR__ . '/env.ini'))->load();

// see env.ini
DEFINE("DBNAME",getenv('DBNAME'));
DEFINE("DBHOST",getenv('DBHOST'));
DEFINE("DBUSER",getenv('DBUSER'));
DEFINE("DBPASS",getenv('DBPASS')); 

DEFINE("SEARCH_MICROSOFT_KB",1);  // 1 = enable, 0 = disable
DEFINE("SEARCH_OFFICE",1);
DEFINE("NOTIFY_ON_ERROR","user@domain.com"); // Used only if cannot get default from Database settings
DEFINE("DEFAULT_SMTP_SERVER","mail.mydomain.com"); // used only if cannot get settings from database

DEFINE("TTF_DIR",APP_ROOT_DIR."graph/fonts/");
DEFINE("APP_NAME",DBNAME); // used by editor for virutal root name
DEFINE("FILES_FOLDER","Files/"); 
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

define("AUTHENTICATION_MODE","Local"); // NT or LOCAL
define("ALLOW_GUESTS",1); // if true then auto create a User account on first access with Guest permissions
						  // else provide message that they must have an account. Valid for NT mode only

define("KB_UPDATE_SERVER","softperfection.com");
define("KB_UPDATES_FOLDER","kbupdates");
define("KB_UPDATE_URL","http://" . KB_UPDATE_SERVER . "/" . KB_UPDATES_FOLDER . "/kbupdate.dat?A"); // ptr to script

define("HIDE_BULLETINS","No"); // Yes to hide Bulletin feature

//
// FORM_STYLE Form Style used for Input pages
//
$FORM_STYLE    = 'style="background-color: #f2f2f2;border-radius:10px"  border="0" cellpadding="4" cellspacing="0" ';

//
// $CONTENT_STYLE = Content Style used for list or menu choice windows, defined elsewhere but required. 
//
$CONTENT_STYLE = 'style="background-color: #eaeaea; border-collapse: collapse" BORDER="1" CELLPADDING="4" CELLSPACING="0" ';

// 
// GLOBALS
//
global $printview;

/**
 * @var \DB
 */
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
