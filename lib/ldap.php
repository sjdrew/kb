<?php
global $ldapConn;

define("LDAP_BASE",'dc=cnrl,dc=com');
define("LDAP_SERVER",'LDAP-CGY.cnrl.com');
define("LDAP_ACCOUNT",'CNRLCGY1\ISPortal_AD_View');
define("LDAP_PASSWORD",'2levelappr');
define("LDAP_GROUP_PREFIX","04_KB_");

if ($_GET['LDAP_TEST']) {

	echo "<pre>";
	echo "All KB Groups:\n";
	print_r(LDAPGetGroups(LDAP_GROUP_PREFIX . "*"));

}

/**
 * Remove any users from KB that no longer exist in AD
 */
function AD_User_Sync(&$msg)
{
	global $AppDB;
	global $ldapConn;
	$Table = USERS_TABLE;
	$Res = $AppDB->sql("select ID,Username from $Table order by Username");
	if (!LDAPConnect()) {
		$msg = "Unable to connect to LDAP. " . ldap_error($ldapConn) . "\n";
		return;
	}
	$msg  = "Starting AD/KB User sync\n";
	while($Res && $Rec = $AppDB->sql_fetch_array($Res)) {
		$LRec = '';
		$Stat = LDAPGetUser($Rec['Username'],$LRec);
		if ($Stat == 0) {
			$msg .= "Aborting due to LDAP Error\n";
			error_log("Aborting due to LDAP Error");
			return;
		}
		if (!$LRec) {
			$msg .= "User " . $Rec['Username'] . " does not exist in Active Directory\n";
			$AppDB->sql("delete from users where ID = '".$Rec['ID'] ."'");
			++$Count;
		}
	}
	if ($Count > 0) {
		$msg .= "$Count user profiles removed.";
	} else {
		$msg .= "In Sync. There are no user profiles to remove.";
	}

}

/**
 * Create any Groups in KB that are Prefix matches in AD
 */
function AD_Group_Sync(&$msg) 
{
	global $AppDB;
	global $ldapConn;
	
	if (!LDAPConnect()) {
		$msg = "Unable to connect to LDAP. " . ldap_error($ldapConn) . "\n";
		return 20;
	}
	
	$ADGroups = LDAPGetGroups(LDAP_GROUP_PREFIX . "*");
	
	$msg = "Active Directory Group Sync:\n";
	
	$Groups = array();
	foreach($ADGroups as $ADGroup) {	
		$tpos = strrpos($ADGroup,'_');
		$Perm = trim(substr($ADGroup,$tpos+1));
		$GroupName = trim(substr($ADGroup,0,$tpos));
		$GroupName = substr($GroupName,strlen(LDAP_GROUP_PREFIX));
		$Groups[] = $GroupName;		
	}
	if (count($Groups) == 0) {
		$msg .= "No matching groups found or LDAP error";
		return 10;
	}
	$ADGroups = array_unique($Groups);
	foreach($ADGroups as $GroupName) {
		$GrpRec = $AppDB->GetRecordFromQuery("select ID from Groups where Name='$GroupName'");
		$msg .= "$GroupName: ";
		if ($GrpRec) $msg .= "[OK]\n";
		else {
			$msg .= "[NOT Found on KB] - Creating...";
			$GenRec = $AppDB->GetRecordFromQuery("select top 1 GroupID from Groups where GroupID < 999999 order by GroupID desc");
			if (!$GenRec) exit;
			$GID = $GenRec->GroupID + 1;
			unset($_POST);
			$_POST['Name'] = $GroupName;
			$_POST['GroupID'] = $GID;
			$_POST['STATUS'] = 'Active';
			$NewID = $AppDB->save_form('Groups');
			if ($NewID) $msg .= "[OK]\n";
			else echo "\n";
		}
	}
	return 0;
}   

function LDAPConnect()
{
	global $ldapConn;
	
	if ($ldapConn) return $ldapConn;
	
	/*
	 * try to connect to the server
	 */
	$ldapConn = ldap_connect(LDAP_SERVER);
	if (!$ldapConn)
	{
		error_log("Cannot connect to LDAP server" . LDAP_SERVER);
		return NULL;
	}

	/*
	* set the ldap options
	*/
	ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

	/*
	 * bind 
	 */
	
	$ldapBind = @ldap_bind($ldapConn,LDAP_ACCOUNT,LDAP_PASSWORD);
	//$ldapBind = ldap_bind($ldapConn);

	if (!$ldapBind)
	{
		error_log("Unable to Bind LDAP Server " . LDAP_SERVER . " " . ldap_error($ldapConn));
		return NULL;
	}

	return $ldapConn;
}

function LDAPGetUser($UserID,&$Rec)
{
	global $ldapConn;
	LDAPConnect();
	
	$fields = array('givenname','sn','telephonenumber','mail','department');
	
	$filter = "(samaccountname=$UserID)";
	$ldapSearch = @ldap_search($ldapConn, LDAP_BASE, $filter,$fields);
	
	if (!$ldapSearch) {
		error_log("LDAP Search error on $filter");
		return 0;
	}

	$info = ldap_get_entries($ldapConn, $ldapSearch);
	
	for ($item = 0; $item < $info['count']; $item++) {
		for ($attribute = 0; $attribute < $info[$item]['count']; $attribute++) {
			$data = $info[$item][$attribute];
			$Rec[$data] = $info[$item][$data][0];
		}
	}
	return 1;
}

function LDAPGetGroups($GroupSearch,$Prefix='')
{
	global $ldapConn;
	
	LDAPConnect();
	
	$Groups = array();
	$filter = "(cn=$GroupSearch)";
	$ldapSearch = @ldap_search($ldapConn, LDAP_BASE, $filter, array('cn'));
	
	if (!$ldapSearch) return $Groups;

	$info = ldap_get_entries($ldapConn, $ldapSearch);
	
	for ($item = 0; $item < $info['count']; $item++) {
		for ($attribute = 0; $attribute < $info[$item]['count']; $attribute++) {
			$data = $info[$item][$attribute];
			$Group = $info[$item][$data][0];
			if ($Prefix) {
				if (substr($Group,0,strlen($Prefix)) != $Prefix) {
					continue;
				}
				$Group = substr($Group,strlen($Prefix));
			}
			$Groups[] = $Group;
		}
	}
	return $Groups;
}

function LDAPGetUsersGroups($UserID,$Prefix='')
{
	global $ldapConn;
	
	LDAPConnect();
	
	$Groups = array();
	/*
	 * search the LDAP server  
	 */
	$filter = "(samaccountname=$UserID)";
	$ldapSearch = ldap_search($ldapConn, LDAP_BASE, $filter, 
		array('memberof','cn','PrimaryGroupID' ));

	$info = ldap_get_entries($ldapConn, $ldapSearch);
	$ii=0;
	for ($i=0; $ii<$info[$i]["count"]; $ii++){
		$data = $info[$i][$ii];
		if ($data == "memberof") {
			$total = count($info[$i][$data]);
			$jj=0;
			for ($jj=0; $jj<$total; $jj++) {
				$GroupCN = $info[$i][$data][$jj];
				$s = explode(',',$GroupCN);
				$Group = str_replace('\\','',substr($s[0],3));
				if ($Prefix) {
					if (substr($Group,0,strlen($Prefix)) != $Prefix) {
						continue;
					}
					$Group = substr($Group,strlen($Prefix));
				}
				$Groups[] = $Group;
			}
		}
	}
	return $Groups;
}
?>