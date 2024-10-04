<?  
	// Initialize
	include("config.php");
	RequirePriv(PRIV_ADMIN);
	$Table = "FieldDetails";	
	$ID = GetVar("ID");
	
    $AllowChanges = 0;
	if (strstr($_SERVER['SERVER_NAME'],'localhost')) // only allow creates on dev system.
		$AllowChanges = 1;

    $AllowDelete = 0;
	if (file_exists("admin_create_update.php"))
		$AllowDelete = 1;

    $msg = GetVar('msg');

    $TableName = GetVar('TableName');
    $ColumnName = GetVar('ColumnName');
    $FieldName = GetVar('FieldName');
    $RWGroups = GetVar('RWGroups');
    $RGroups = GetVar('RGroups');

    $Save = GetVar('Save');
 
	
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
	
	// Delete record support
	if ($ID && GetVar('Delete') == "Delete") { // for security
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
		$Cols = $AppDB->MetaColumns($F['TableName']);
		foreach($Cols as $C) {
			if ($C->name == $F['ColumnName']) {
				$DB_DataType = $AppDB->MetaType($C->type);
				$DB_Length = $C->max_length;
				break;
			}
		}
        if ($_POST) {
            $F = array_merge($F,$_POST);
        }
		else  {
			$F['DataType'] = $DB_DataType;
			$F['Length'] = $DB_Length;
		}
	}
	else {
        $F['Type'] = 'varchar';
        $F['FieldValues'] = '';
        $F['RadioGroup'] = '';
        $F['HTMLSize'] = '';
        $F['MaxLength'] = 100;
        $F['Style'] = '';
        $F['TagParams'] = '';
        $F['Query'] = '';
        $F['QFieldText'] = '';
        $F['QFieldValue'] = '';
        $F['DataType'] = 'varchar';
        $F['Length'] = 100;
        $F['ColumnName'] = '';
        $F['Required'] = '';
        if ($_POST) {
            $F = array_merge($F,$_POST);
        }        
    }

	
	if ($Save) {
	
		BusyImage(1);
		$ID = $_POST["ID"];
		if (!$ID) {
            if (!$ColumnName) {
                $msg = 'Column Name is required';
            }
            else {
                // Make sure we arenot creating another table/field combo that already exists
                $R = $AppDB->GetRecordFromQuery("select ID from $Table where TableName = '$TableName' and FieldName = '$FieldName'"); 
                if ($R) {
                    $msg = "A Field Detail entry with that Field Name already exists in this Table";
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
                                $DataType = $_POST['DataType'] = $AppDB->MetaType($C->type);
                                $Length = $_POST['Length'] = $C->max_length;
                            }
                        }
                    } 
                    $_POST['ID'] = $ID = $AppDB->save_form($Table);
                    $msg = "Record created";
                }
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
					$msg = "Error changing column data type: " . $AppDB->ErrorMsg;
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
					$msg = "Error Creating Index: IX_$ColumnName" . $AppDB->ErrorMsg;
					$Err = 1;
				}
			}
			else if ($Index == "No" && $DB_Index == "Yes") {
				foreach($ColumnsIndexed[$ColumnName] as $IdxName) {			
					$msg .= " Index Removed. ";
					$q = $AppDB->DropIndex($TableName,$IdxName);
					if (!$AppDB->sql($q[0],"",0)) {
						$msg = "Error removing Index: " . $AppDB->ErrorMsg;
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
		if (is_array(!empty($ColumnsIndexed[$F['ColumnName']]))) {
			$F['Index'] = "Yes";
		}
		else $F['Index'] = "No";
	}
	
	if ($_POST) {
		if (isset($_POST['CopyToNew'])) {
			$ID = $F['ID'] = $F['LASTMODIFIEDBY'] = $F['LASTMODIFIED'] = $F['CREATED'] = $F['CREATEDBY'] = $F['ColumnName'] = "";
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
<form onSubmit="return parse()" id="form" name="form" method="post" action="<? echo $_SERVER['PHP_SELF'] ?>">
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
					if ($ID) { echo "<B>{$F['ColumnName']}</B>"; hidden("ColumnName",$F['ColumnName']); } 
				   	else { ?><input TYPE="text" NAME="ColumnName" SIZE="40" VALUE="<? echo $F['ColumnName']  ?>"><? } ?></td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Data Type:</td>
              <td CLASS="form-data">
			   <? 	$DataTypesT = array("varchar","datetime","int","decimal","large text","large binary");
			   		$DataTypesV = array("C","T","I","N","X","B");
			   		dropdownlist("DataType",$DataTypesT,$DataTypesV,$F['DataType']); ?> 
			  Length: <input TYPE="text" NAME="Length" SIZE="6" VALUE="<? echo $F['Length'] ?>"></td>
            </tr>
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">Field Name:</td>
            	<td width="60%" CLASS="form-data">
                <input TYPE="text" NAME="FieldName" SIZE="40" VALUE="<? echo $F['FieldName']  ?>"></td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Required:</td>
              <td CLASS="form-data"><? dropdownlist("Required",array("No","Yes"),array("No","Yes"),$F['Required']);  ?>
				</td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Index:</td>
              <td CLASS="form-data"><? dropdownlist("Index",array("No","Yes"),array("No","Yes"),$F['Index']); ?></td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Field Help Text:</td>
              <td CLASS="form-data"><input NAME="HelpText" TYPE="text" VALUE="<? echo htmlspecialchars((string)$F['HelpText'])  ?>" SIZE="50"></td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">R/W Permission Groups:</td>
              <td CLASS="form-data"><input TYPE="text" NAME="RWGroups" SIZE="50" VALUE="<? echo $F['RWGroups'] ?>"></td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Read Permission Groups:</td>
              <td CLASS="form-data"><input TYPE="text" NAME="RGroups" SIZE="50" VALUE="<? echo $F['RGroups'] ?>"></td>
            </tr>
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">Type.: </td>
            	<td width="60%" CLASS="form-data">
                <? dropdownlist("Type",array("TextBox","TextArea","DropList","CheckBox","Radio","Date",),
				                       array("TextBox","TextArea","DropList","CheckBox","Radio","Date"),$F['Type']); ?></td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Values:</td>
              <td CLASS="form-data"><input NAME="FieldValues" TYPE="text" id="FieldValues" VALUE="<? echo $F['FieldValues'] ?>" SIZE="50"></td>          
         </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Radio Group:</td>
              <td CLASS="form-data"><input TYPE="text" NAME="RadioGroup" SIZE="20" VALUE="<? echo $F['RadioGroup']  ?>"> </td>
            </tr>
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">HTML Size (or cols):</td>
            	<td width="60%" CLASS="form-data">
                <input TYPE="text" NAME="HTMLSize" SIZE="10" VALUE="<? echo $F['HTMLSize'] ?>"></td>
            </tr>
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">Max Length (or rows):</td>
            	<td width="60%" CLASS="form-data">
                <input TYPE="text" NAME="MaxLength" SIZE="10" VALUE="<? echo $F['MaxLength'] ?>"></td>
            </tr>
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">Style:</td>
            	<td width="60%" CLASS="form-data">
                <input TYPE="text" NAME="Style" SIZE="10" VALUE="<? echo $F['Style'] ?>"></td>
				</td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Tag Parameters:</td>
            	<td width="60%" CLASS="form-data">
                <input TYPE="text" NAME="TagParams" SIZE="50" VALUE="<? echo htmlspecialchars((string)$F['TagParams']) ?>"></td>
            </tr>
            <tr>
            	<td width="40%" CLASS="form-hdr" align="right">Query:</td>
           	  <td width="60%" CLASS="form-data">
                <input NAME="Query" TYPE="text" VALUE="<? echo htmlspecialchars((string)$F['Query']) ?>" SIZE="60" maxlength="200">
           	  </td>
            </tr>
            <tr>
              <td CLASS="form-hdr" align="right">Query Fields:</td>
              <td CLASS="form-data"> Text:
              <input TYPE="text" NAME="QFieldText" SIZE="15" VALUE="<? echo $F['QFieldText'] ?>"> 
              Value: 
              <input TYPE="text" NAME="QFieldValue" SIZE="15" VALUE="<? echo $F['QFieldValue'] ?>"></td>
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