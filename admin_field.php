<?  
	// Initialize
	include("config.php");
	RequirePriv(PRIV_ADMIN);
	$Table = "FieldDetails";	
	$ID = GetVar("ID");
	
	if ($ID || file_exists("admin_create_update.php")) // only allow creates on dev system.
		$AllowChanges = 1;
	if (file_exists("admin_create_update.php"))
		$AllowDelete = 1;
	
?>	

<html>

<head>
<title><? echo $AppDB->Settings->AppName ?> - Database - Configure</title>
<link REL="stylesheet" HREF="styles.css"></link>
</head>

<? $SECTION="Section-ADMIN"; 
   include("header.php");  ?>
<body>

<SCRIPT LANGUAGE="JavaScript" SRC="misc.js"></SCRIPT>

<script language="JavaScript">
function parse()
{
	var df = document.forms[0];
	//if (!CheckRequired(df.CompanyName)) return false;	
	return true;
}
</script>

<?
 	if ($ID && $TableName == "") {
		$F = $AppDB->get_record_assoc($ID,$Table);
		$TableName = $F["TableName"];
	}
	if ($TableName == "") {
		echo "Table or Field not defined.";
		exit;
	}

	// Figure out the indexes and also reorder by column name to list all the indexes it is
	// related to. That way we can remove the index before attempting to drop the column
	// as mssql requires the index to be deleted before dropping the column.
	$Indexes = $AppDB->MetaIndexes($TableName);
	$ColumnsIndexed = array();
	
	foreach($Indexes as $IndexName => $Idx) {
		foreach ($Idx['columns'] as $Col) {
			$ColumnsIndexed[$Col][] = $IndexName;
		}
	}
	//echo "<pre>";
	//print_r($ColumnsIndexed);
	//echo "</pre>";	


	// Delete record support
	if ($ID && $_POST[Delete] == "Delete") { // for security
		$AppDB->delete_record($ID,$Table);
		$q = "select ID from $Table where TableName='$TableName' and ColumnName='$ColumnName'";
		if (!$AppDB->GetRecordFromQuery($q)) {
		
			// Remove any indexes before removing column.	
			if (is_array($ColumnsIndexed[$ColumnName])) {
				foreach($ColumnsIndexed[$ColumnName] as $IdxName) {			
					$AppDB->DropIndex($TableName,$IdxName);
				}
			}
			$AppDB->DropColumn($TableName,$ColumnName);
			$msg = "<center><br><br>Field Details and Column Removed from Table $TableName";
		} else $msg = "<center>Field Detail Item Deleted.";
		$msg .= "<br><a href=\"admin_fields.php?TableName=$TableName\">Continue</a></center>";
		ShowMsgBox($msg,"center");		
		exit;
	}

	//
	// Set DataType and Length from actual table schema
	//
 	if ($ID) {
		$F = $AppDB->get_record_assoc($ID,$Table);
		$Cols = $AppDB->MetaColumns($F[TableName]);
		foreach($Cols as $C) {
			if ($C->name == $F[ColumnName]) {
				$DB_DataType = $AppDB->MetaType($C->type);
				$DB_Length = $C->max_length;
				break;
			}
		}
		if (!$_POST) {
			$DataType = $DB_DataType;
			$Length = $DB_Length;
		}
	}
	

	
	if ($Save) {
	
		BusyImage(1);
		$ID = $_POST["ID"];
		if (!$ID) {
			// Make sure we arenot creating another table/field combo that already exists
			$R = $AppDB->GetRecordFromQuery("select ID from $Table where TableName = '$TableName' and FieldName = '$FieldName'"); 
			if ($R) {
				$msg = "A Field Detail entry with that FielName already exists in this Table";
			}
			else {
				// If column does not exist create it
				$q = "select ID from $Table where TableName = '$TableName' and ColumnName = '$ColumnName'"; 			
				$R = $AppDB->GetRecordFromQuery($q);
				if (!$R) {
					if ($DataType == "C" || $DataType == "N")
						$L = "($Length)";
					else $Length = "";
					$AppDB->AddColumn($TableName,$ColumnName,$DataType,$L); 
					// now after adding, get back real size and type and override and save.
					$Cols = $AppDB->MetaColumns($TableName);
					foreach($Cols as $C) {
						if ($C->name == $ColumnName) {
							$DataType = $_POST[DataType] = $AppDB->MetaType($C->type);
							$Length = $_POST[Length] = $C->max_length;
						}
					}
				} 
				$ID = $AppDB->save_form($Table);
				$msg = "Record created";
			}
		}
		else if ($ID) {
			// dont allow changes to columnname
			// allow changes to type and size
			if ($DataType != $DB_DataType || $Length != $DB_Length) {
				if ($DataType == "C" || $DataType == "N")
					$L = "($Length)";
				else $Length = "";
				$q = $AppDB->AlterColumn($TableName,$ColumnName,$DataType,$L);
				if (!$AppDB->sql($q[0],"",0)) {
					$msg = "Error changing column data type: " . $AppDB->ErrorMsg();
					$Err = 1;
				}
				else {
					$SETS["$DataType"] = $DataType;
					$SETS["Length"] = $Length;
					$msg .= "DB Column information altered. ";
				}
			}
			$DB_Index =  (is_array($ColumnsIndexed[$ColumnName])) ? "Yes" : "No";
			
			if ($Index == "Yes" and $DB_Index == "No") {
				$msg .= " Index Added. ";
				$q = $AppDB->CreateIndex($TableName,$ColumnName,"IX_$ColumnName");
				if (!$AppDB->sql($q[0],"",0)) {
					$msg = "Error Creating Index: IX_$ColumnName" . $AppDB->ErrorMsg();
					$Err = 1;
				}
			}
			else if ($Index == "No" && $DB_Index == "Yes") {
				foreach($ColumnsIndexed[$ColumnName] as $IdxName) {			
					$msg .= " Index Removed. ";
					$q = $AppDB->DropIndex($TableName,$IdxName);
					if (!$AppDB->sql($q[0],"",0)) {
						$msg = "Error removing Index: " . $AppDB->ErrorMsg();
						$Err = 1;
					}
				}
			}
			$Changes = $AppDB->modify_form($ID,$Table);
			if (!$Err && $Changes) $msg .= "Changes have been saved";
		}		
		BusyImage(0);
	}
	 
 	if ($ID) {
		$F = $AppDB->get_record_assoc($ID,$Table);
		if ($F) RecordToGlobals($F);
		if (is_array($ColumnsIndexed[$ColumnName])) {
			$Index = "Yes";
		}
		else $Index = "No";
	}
	
	if ($_POST) {
		// keep reposted values, but strip slashes
		repost_stripslashes();
		if ($CopyToNew) {
			$ID = $LASTMODIFIEDBY = $LASTMODIFIED = $CREATED = $CREATEDBY = "";
		}
	}
	// Defaults:
	else if (!$ID) {
		$RWGroups = "Submitter;Administrators";
		$RGroups = "Everyone";
	}

	$Tables = $AppDB->MetaTables("TABLES");
	$Tmp = array();
	foreach($Tables as $T) {
		if ($T != "FieldDetails")
			$Tmp[] = $T;
	}
	$Tables = $Tmp;
		
	$Cols = $AppDB->MetaColumns($TableName);
	foreach($Cols as $C) $Columns[] = $C->name;
	
	ShowMsgBox($msg,"center");	
 ?>

<center>
<br>		
<form onSubmit="return parse()" id="form" name="form" method="post" action="<? echo $PHP_SELF ?>">
<? 	$Frame = new FrameBox("Field Details", "80%",'',"");
	$Frame->Display();
	hidden("ID",$ID); ?>
       <table width="100%" <? echo $FORM_STYLE ?> >
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">Table:</td>
            	<td width="60%" CLASS="form-data">
                <? 
					if (!$ID) dropdownlist("TableName",$Tables,$Tables,$TableName,"onchange='form.submit()'"); 
					else { hidden("TableName","$TableName"); echo "<B>$TableName</B>"; } ?></td>
            </tr>
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">Column Name:</td>
            	<td width="60%" CLASS="form-data">
                <? // dropdownlist("ColumnName",$Columns,$Columns,$ColumnName);
					if ($ID) { echo "<B>$ColumnName</B>"; hidden("ColumnName",$ColumnName); } 
				   	else { ?><input TYPE="text" NAME="ColumnName" SIZE="40" VALUE="<? echo $ColumnName  ?>"><? } ?></td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Data Type:</td>
              <td CLASS="form-data">
			   <? 	$DataTypesT = array("varchar","datetime","int","decimal","large text","large binary");
			   		$DataTypesV = array("C","T","I","N","X","B");
			   		dropdownlist("DataType",$DataTypesT,$DataTypesV,$DataType); ?> 
			  Length: <input TYPE="text" NAME="Length" SIZE="6" VALUE="<? echo $Length ?>"></td>
            </tr>
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">Field Name:</td>
            	<td width="60%" CLASS="form-data">
                <input TYPE="text" NAME="FieldName" SIZE="40" VALUE="<? echo $FieldName  ?>"></td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Required:</td>
              <td CLASS="form-data"><? dropdownlist("Required",array("No","Yes"),array("No","Yes"),$Required);  ?>
				</td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Index:</td>
              <td CLASS="form-data"><? dropdownlist("Index",array("No","Yes"),array("No","Yes"),$Index); ?></td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Field Help Text:</td>
              <td CLASS="form-data"><input NAME="HelpText" TYPE="text" VALUE="<? echo htmlspecialchars($HelpText)  ?>" SIZE="50"></td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">R/W Permission Groups:</td>
              <td CLASS="form-data"><input TYPE="text" NAME="RWGroups" SIZE="50" VALUE="<? echo $RWGroups ?>"></td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Read Permission Groups:</td>
              <td CLASS="form-data"><input TYPE="text" NAME="RGroups" SIZE="50" VALUE="<? echo $RGroups ?>"></td>
            </tr>
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">Type.: </td>
            	<td width="60%" CLASS="form-data">
                <? dropdownlist("Type",array("TextBox","TextArea","DropList","CheckBox","Radio","Date",),
				                       array("TextBox","TextArea","DropList","CheckBox","Radio","Date"),$Type); ?></td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Values:</td>
              <td CLASS="form-data"><input NAME="FieldValues" TYPE="text" id="FieldValues" VALUE="<? echo $FieldValues ?>" SIZE="50"></td>          
         </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Radio Group:</td>
              <td CLASS="form-data"><input TYPE="text" NAME="RadioGroup" SIZE="20" VALUE="<? echo $RadioGroup  ?>"> </td>
            </tr>
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">HTML Size (or cols):</td>
            	<td width="60%" CLASS="form-data">
                <input TYPE="text" NAME="HTMLSize" SIZE="10" VALUE="<? echo $HTMLSize ?>"></td>
            </tr>
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">Max Length (or rows):</td>
            	<td width="60%" CLASS="form-data">
                <input TYPE="text" NAME="MaxLength" SIZE="10" VALUE="<? echo $MaxLength ?>"></td>
            </tr>
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">Style:</td>
            	<td width="60%" CLASS="form-data">
                <input TYPE="text" NAME="Style" SIZE="10" VALUE="<? echo $Style ?>"></td>
				</td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Tag Parameters:</td>
            	<td width="60%" CLASS="form-data">
                <input TYPE="text" NAME="TagParams" SIZE="50" VALUE="<? echo htmlspecialchars($TagParams) ?>"></td>
            </tr>
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">Query:</td>
           	  <td width="60%" CLASS="form-data">
                <input NAME="Query" TYPE="text" VALUE="<? echo htmlspecialchars($Query) ?>" SIZE="60" maxlength="200">
           	  </td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Query Fields:</td>
              <td CLASS="form-data"> Text:
              <input TYPE="text" NAME="QFieldText" SIZE="15" VALUE="<? echo $QFieldText ?>"> 
              Value: 
              <input TYPE="text" NAME="QFieldValue" SIZE="15" VALUE="<? echo $QFieldValue ?>"></td>
            </tr>
            <tr>
            	<td CLASS="form-data" COLSPAN="2" ALIGN="right">
              <input <? if (!$AllowChanges) echo "disabled" ?> TYPE="submit" VALUE="Save" NAME="Save"><?  if ($ID) {  ?><input TYPE="submit" VALUE="Copy to New" NAME="CopyToNew"><input <? if (!$AllowDelete) echo "disabled" ?> TYPE="submit" name="Delete" onClick="return confirm('Are you sure?');" value="Delete"> <? } ?><input TYPE="button" VALUE="Back" NAME="Back" onClick="window.location='admin_fields.php?TableName=<? echo $TableName ?>'" ><?  HelpButton()  ?></td>
            </tr>
    </table>
    <?  $Frame->DisplayEnd()  ?> 
</form> 
</center>
</body>
</html>