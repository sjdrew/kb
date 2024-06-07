<?php
/** * 
 * CUser class
 * File: subs_auth.php
 * Version: 1.0
 *
 *
 * Author: softperfection.com
 *
 * SofPerfection grants unlimited, unrestricted use of this source code to modify
 * redistribute in any fashion. It is completely in the public domain.
 * No warranties are implied or provided.
 */
 
 
//
// Authentication Routines
//
// COOKIES TRACKED
// APPID_USERID
// APPID_AUTHID
// Globals $AppDB - database context
//
define("PRIV_ADMIN",8);
define("PRIV_APPROVER",4);
define("PRIV_FINANCE",4);
define("PRIV_USER",2);
define("PRIV_SUPPORT",2);
define("PRIV_GROUP",2);
define("PRIV_GUEST",1);

//To be defined in your config.php file:
//
//define("AUTHENTICATION_MODE","NT"); // NT or LOCAL
//define("ALLOW_GUESTS",1);			// if true then auto create a User account on first access with Guest permissions
									// else provide message that they must have an account. Valid for NT mode only



// Include in pages needing authentication
// CurrentUser object is declared
// On Istantiation sets CurrentUser->userid = from cookie
//

class CurrentUser {

	var $u;
	var $UserID;
	var $LoggedIn=false;
	var $UserHash;
	var $Simulated;
	var $AuthenticationMode; 
	var $AllowGuests = ALLOW_GUESTS;
	var $AutoCreateUserCallBack; // function to call after creating skeleton User record after NT Authentication.
	
	function CurrentUser($SimulateID = "",$CustomCreateUserCallBack = "") 
	{
		global $noauth;
		global $AppDB;
		
		$this->AuthenticationMode = $AppDB->Settings->AuthenticationMode; // NT or Local
		
		// For security, in case php register globals is enabled
		if (GetVar("noauth") || GetVar("auth_in_progress") || GetVar($SimulateID)) {
			echo "Invalid security arguments specified.";
			exit;
		}
		$this->AutoCreateUserCallBack = $CustomCreateUserCallBack;
		
		if ($SimulateID) {
			$this->u = GetUser("",$SimulateID);
			$this->LoggedIn = true;
			$this->UserID = $this->u->Username;
			$this->Simulated = true;
		}
		else {	
			$this->UserID =  $_COOKIE[APPID . "_USERID"];
			$this->UserHash = $_COOKIE[APPID . "_USERHASH"];
			$this->Authenticate();
			if ($this->LoggedIn) {	
				$this->u = GetUser($this->UserID);
			}
			if ($this->u->LastName == "" && $this->u->FirstName == "") 
				$this->u->LastName = $this->UserID;
		}
		if ($AppDB->Settings->PrivMode == "Group") {
		
			$this->u->Priv = "";
			
			$Groups = explode(',',$this->u->Groups);
			$GroupsMustRead = array();
			foreach($Groups as $Grp) {
				$MustRead = "N";
				list($GID,$Mode,$MustRead) = explode(":",$Grp);
				$GroupIDs[] = $GID;
				if ($MustRead == "Y") $GroupsMustRead[] = $GID;
				if ($GID == 1) $this->u->Priv = PRIV_ADMIN;
				if ($Mode == "A") $GroupIDs_A[] = $GID;
				// Set a flag if user has write access to one or more groups.
				if ($Mode == "W" || $this->u->Priv == PRIV_ADMIN || $Mode == "A") $this->PrivWrite = true;
				$GroupArray[$GID] = $Mode;
			}
			$this->u->GroupArray = array();
			if ($GroupArray) {
				$this->u->GroupArray = $GroupArray;
				$this->u->GroupIDs = implode(",",$GroupIDs);
				$this->u->GroupsMustRead = implode(",",$GroupsMustRead);
				if ($GroupIDs_A) $this->u->GroupIDs_A = implode(",",$GroupIDs_A);
			}
			if ($this->u->Priv == "") {
				if ($this->u->GroupIDs_A != "") {
					$this->u->Priv = PRIV_APPROVER;
				}			
				else if ($this->u->Groups != "") {
					$this->u->Priv = PRIV_SUPPORT;
				}
				else {
					$this->u->Priv = PRIV_GUEST;
				}
			}
			if ($this->u->GroupIDs == "") $this->u->GroupIDs = 0;
			//print_r($this->u);
		}			
	}
	
	function IsAppAdmin()
	{	
		return ($this->u->Priv & PRIV_ADMIN);	
	}

	function Priv()
	{
		return($this->u->Priv);
	}

	function IsPriv($Priv)
	{
		return($Priv & $this->u->Priv);
	}

	function UserRecord()
	{
		return($this->u);
	}

	function Logout()
	{
		if ($this->Simulated)
			return;
				
		$GLOBALS[APPID . "_USERID"] = $GLOBALS[APPID . "_USERHASH"] = $GLOBALS["LOGGED_IN"] = "";
    	setcookie(APPID . "_USERID","",0,"/" /*. APP_NAME*/);
    	setcookie(APPID . "_USERHASH","",0,"/" /*. APP_NAME*/);
		$this->UserID = $this->UserHash = "";
		$this->LoggedIn = false;
	}

	function Authenticate()
	{
		global $auth_in_progress;
		global $noauth;
		
		// already authenticating (only set for logon.php)		
		if ($auth_in_progress && $this->AuthenticationMode != "NT") {
			return;
		}
		// check if already logged in
		if ($this->CheckTokens()) {
			// See if first time here this session and if so
			// record login etc.
			// This means cookies where in place already (autologin)
			//
			if ($_COOKIE['C_LoginTime'] == "") $this->Dologin(1);
			return;
		}
		
		// Page does not need authentication
		global $noauth;
		if ($noauth) {
			return;
		}
		if ($this->AuthenticationMode == "NT") {
			$this->NTAuthenticate(); // only returns if authenticated.
			$this->Login($this->UserID,"");
			return;			
		}
		
		// else route to Logon Page 
		$target = urlencode($_SERVER[URL]);
		header ("Location: logon.php?target=$target");
		exit;
	}

	function NTAuthenticate() 
	{
		if ($Agent = $_SERVER['HTTP_USER_AGENT']) {
			// Fail for PowerPC: 'Mozilla/4.0 (compatible; MSIE 5.15; Mac_PowerPC)';
			if (stristr($Agent,'PowerPC')) {
				echo("This Site Requires NT Authentication which does not work properly from your Mac PowerPC Browser.<br>");
				echo("Please connect using a Windows PC with version 5.5 or Higher of Internet Explorer.");
				exit;
			}
		}
 		if ($_SERVER["AUTH_TYPE"] && stristr("Negotiate|NTLM|Basic",$_SERVER["AUTH_TYPE"])) {

			list($domain,$account) = split('\\\\',$_SERVER['LOGON_USER']);
			if ($account == "") $account = $domain;
		
			if ($account == "") {
				echo("Unable to determine your Account information");
				exit;	
			}
			
			// At this point save in Session or cookie	
			$this->UserID = strtolower($account);
			$this->NTDomain = strtolower($domain); // currently not saved in cookie so only avail on first access.
			return($account);	
		}
		header('Status: 401 Unauthorized');
		header('HTTP/1.1 401 Unauthorized');
		header('WWW-Authenticate: Negotiate',false);
		header('WWW-Authenticate: NTLM',false);

		exit;
	}

	//
	// Called from Login Procedure, check userid and password
	// If ok, set the tokens and marked logged in.
	//
	function Login($user,$pw,$TZOffset="300")
	{
		global $AppDB;	
		$this->UserID = $user;
		if (!$this->UserID) {
			return("Please supply your your logon ID.");
		}
		$this->u = getuser($this->UserID);
				
		if ($this->u == "" && $this->AuthenticationMode == "NT") {
			if (!$this->AllowGuests) {
				echo "Your account ($user) does not have a local profile created. Please contact your Administrator.";
				exit;
			}
			// For NTAuthentication Mode, create a user record with Guest priv if one does not
			// exist. That way we can track last logged in date and other stats.
			$ID = $AppDB->sql("insert into " . USERS_TABLE . " (LastName,FirstName,Username,Priv) values ('$user','$user','$user',".PRIV_GUEST.")");
			$this->u = GetUser($this->UserID);
		}
		/* now called on each login to allow callback to update KB user profile from external source (ie remedy)
		 */
		if ($this->u && $this->AutoCreateUserCallBack) {
			$cb = $this->AutoCreateUserCallBack;
			$cb($this->u);
			$this->u = GetUser($this->UserID);
		}
		
		if ($this->u && ($this->AuthenticationMode == "NT" || $this->u->Password == $pw)) {
			$this->DoLogin(0);
			return "OK";
		}
		return("Invalid password, please try again.");
	}
	
	function DoLogin($Auto=0)
	{
		if ($Auto == 0)	$this->SetTokens();
		$this->SetLastLogin();
		$this->LoggedIn = true;
		setcookie('C_LoginTime',NowDateTime(),0,"/");				
	}

	function CheckTokens()
	{
    	//are both cookies present?
    	if ($this->UserID && $this->UserHash) {
        /*
            Create a hash of the user name that was 
            passed in from the cookie as well as the 
            trusted hidden variable

            If this hash matches the cookie hash,
            then all cookie vars must be correct and
            thus trustable
        */
        	$hash=md5(strtolower($this->UserID).AUTHENTICATE_ID);
	        if ($hash == $this->UserHash) {
				$this->LoggedIn = true;
        	    return true;
        	} else {
            	//hash didn't match - must be a hack attempt?
            	$this->LoggedIn = false;
				//echo "HASH fail, $hash, $this->UserHash, $this->UserID, " . AUTHENTICATE_ID . "<br>";
				//exit;
            	return false;
        	}
    	} 
		else {
        	$this->LoggedIn=false;
        	return false;
    	}
	}

	function IsLoggedIn()
	{
		return $this->LoggedIn;
	}
	
 
	function SetTokens() 
	{
    	if (!$this->UserID) {
			echo "error setting tokens";
			exit;
        	return false;
    	}
    	$user_name=strtolower($this->UserID);
    
    	//create a hash of the two variables we know
    	$id_hash= md5($user_name.AUTHENTICATE_ID);
	
		if ($this->AutoLogin) {
			$expire = time() + 60 * 60 * 24 * 1000; // 1000 days
		} else $expire = 0;
			
	    setcookie(APPID . "_USERID",$this->UserID,$expire,"/" /*. APP_NAME*/);
    	setcookie(APPID . "_USERHASH",$id_hash,$expire,"/" /*. APP_NAME*/);		
	}
	
	function FirstName()
	{
		return $this->u->FirstName;	
	}

	function FullName()
	{
		return $this->u->FirstName . " " . $this->u->LastName;
	}

	function SetLastLogin()
	{
		global $AppDB;
	
		if ($AppDB->UseGMT) {
			$SETS .= 'LastLogin=' . $AppDB->OffsetDate(SERVER_GMT_OFFSET,"GetDate()");
		}
		else {
			$SETS .= "LastLogin= GetDate()";
		}
		$q = "update " . USERS_TABLE . " SET $SETS Where Username ='$this->UserID'";
		$AppDB->sql($q);
		global $CUser;
		$CUser = $this;
		if (function_exists("AuditTrail")) AuditTrail("Login",array());
	}
}

//
// Supporting functions
//
function IsPrivOrCreator($Priv,$T,$ID)
{
	global $AppDB;
	global $CUser;
	if ($ID > 0) {
		$R = $AppDB->GetRecordFromQuery("select * from $T where ID=$ID");
		if ($R->CREATEDBY == $CUser->UserID) return 1;
	}
	if ($Priv & $CUser->Priv()) return 1;
	
	return 0;
}


// GetUser and EmailPassword
//
function GetUser($user,$ID="")
{
	global $noauth;
	global $AppDB;
	
	if (!$noauth && $user == "system") {
		echo "system specified with auth mode.\n";
		exit;
	}
	if ($user == "system" && $noauth)  {
		$u->ID = 0;
		$u->FirstName = "System";
		$u->LastName = " ";
		$u->Username = "system";
		$u->Priv = PRIV_ADMIN;
		$u->ReadAgreement = "Y";
		return $u;
	}
	$user = addslashes($user);	
	
	if ($user == "" && $ID == "") {
		ShowErrorLine("Internal Error: User ID not specified.");
		echo get_backtrace();
		exit;
	}
	$q = "select * from " . USERS_TABLE . " WHERE Username = '$user'";
	if ($ID) {
		$q = "select * from " . USERS_TABLE . " WHERE ID=$ID";
	}
	if (!$AppDB) {
		ShowErrorLine("Fatal error no database.");
		echo get_backtrace();
	}	
	$u = $AppDB->GetRecordFromQuery($q);
	return $u;
}

// global for backwards compatibility
function getusername() 
{
	global $CUser;
	return $CUser->UserID;
}

function RequirePriv($P,$RedirectTo="")
{
	global $CUser;
	if ($CUser->u->Priv < $P) {
		if ($RedirectTo == "") $RedirectTo="admin.php";
		$msg = "Sorry, you do not have access to that function.";
		header("location: $RedirectTo?msg=$msg");
		exit;
	}
}

function EmailPassword($UserID, &$err)
	{
		$u = getuser($UserID);
		$pw = $u->Password;
		
	if ($u->ID) {
		$mailmsg = '

You requested us to email you, your  password.

Your current password is '. $pw . '

';
		if (send_mail(array($u->Email),"Account information",'',$mailmsg,$u->Email,$u->Email))
			$err = "The Password has been sent you via email.";
		else 
			$err = "Mail operation failed.";
		}
	else {
		$err = "That account does not exist.";
	}
}

function GroupStrToArray($str,$includeMustRead = false)
{
	$GList = array();
	$Groups = explode(',',$str);
	foreach($Groups as $Grp) {
		@list($ID,$Mode,$MustRead) = explode(":",$Grp);
		if ($ID && $Mode) {
			$GList[$ID] = $Mode;
			if ($includeMustRead) $GList[$ID] .= ":" . $MustRead;
		}
	}
	return $GList;
}

function GroupArrayToStr($Groups,$NewID,$NewMode,$Delete = 0)
{
	$NewGroups = array();
	foreach($Groups as $GroupID => $Mode) {
		if ($GroupID == $NewID) {
			if ($Delete) {
				continue;
			} else {
				$Mode = $NewMode;
				$found = 1;
			}	
		}
		$NewGroups[$GroupID] = $Mode;
	}
	if ($NewID && $Delete == 0 && $found == 0) {
		$NewGroups[$NewID] = $NewMode;
	}
	foreach($NewGroups as $GroupID => $Mode) {
		$NewGroupsStr .= $comma . $GroupID . ":" . $Mode;
		$comma = ",";
	}
	return $NewGroupsStr;
}
?>