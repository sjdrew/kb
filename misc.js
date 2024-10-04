var _browser;
if (document.getElementById) {
    // IE 5.5+ and Gecko (and Opera 7+)
	_browser = 'dom';
} else if (document.layers) {
    // Netscape 4
	_browser = 'nn4';
} else if (document.all) { 
     // IE <= 4 (and any browser that mimics document.all)
	_browser = 'old';
} 

function loadimage(path)
{
	var image = new Image();
	image.src = path;
}

function AutoSizeWindow() {

  var displayWidth = window.top.document.body.offsetWidth;
  var actualWidth = document.body.scrollWidth;
  var displayHeight = window.top.document.body.offsetHeight;
  var actualHeight = document.body.scrollHeight;

  displayWidth = actualWidth + 25;
  displayHeight = actualHeight + 50; //for Status bar as is enforced if in internet zone.

  var widthOffset = 6;
  var heightOffset = 6;

  // make sure we do not resize bigger than the screen resolution
  if (displayWidth+widthOffset > window.screen.width)
    displayWidth = window.screen.width;
  if (displayHeight+heightOffset > window.screen.height)
    displayHeight = window.screen.height;

  resizeTo(displayWidth+widthOffset, displayHeight+heightOffset);
}

function changeImage(lblField, textImageURL)
{
	 document.images[lblField].src=textImageURL;
}

function CheckRequired(R,inTab)
{
	if (!R) return true;
	
	if (trim(R.value) == "") {
		alert(R.name + " is a required field");
		if (inTab) EnableTabByChild(R);
		R.focus();
		return false;
	}
 	return true;
}

function ValidateEmail(str)
{
  var reg1 = /(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/; // not valid
  var reg2 = /^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?)$/; // valid
  if (!reg1.test(str) && reg2.test(str)) { // if syntax is valid
    return true;
  }
  return false;
}

function CheckEmail(field,inTab)
{
  var str = field.value; // email string
  if (ValidateEmail(str) == true) return true; 
  alert("\"" + str + "\" is an invalid e-mail! Please enter a valid Email address."); // this is also optional
  if (inTab) EnableTabByChild(field);
  field.focus();
  field.select();
  return false;
}

function CheckDate(d,inTab)
{
	if (!d || d.value == "") 
		return true;
	var td = d.value.split(' ');
	
	var f = td[0].split('-');
	if (f.length == 3) {
		if (f[0] > 1900 && f[1] < 2156) {
			if (f[1] > 0 && f[1] < 13) {
				if (f[2] > 0 && f[2] < 32) {
					return true;
				}
			}
		}
	}	
	if (inTab) EnableTabByChild(d);
	d.focus();		
	alert("Invalid Date format. Please enter in YYYY-MM-DD format");
	return false;
}

function trim(strText) 
{ 
    // this will get rid of leading spaces 
    while (strText.substring(0,1) == ' ') 
        strText = strText.substring(1, strText.length);

    // this will get rid of trailing spaces 
    while (strText.substring(strText.length-1,strText.length) == ' ')
        strText = strText.substring(0, strText.length-1);

   return strText;
} 

function IsValidChars(inputobj,inTab)
{
   var okchars = '.0123456789';
   what = inputobj.value;
   
    for (var i = 0; i < what.length; i++) {
       var chr = what.substring(i,i+1);
       if (okchars.indexOf(chr) == -1) {
 		if (inTab) EnableTabByChild(inputobj);
      	alert("You have entered invalid characters in this field.");
       	inputobj.focus();
          return false;
       }
    }
    return true;	
}

function IsAlphaNumeric(inputobj)
{
   var okchars = '.0123456789abcdefghijklmnopqrstuvwxyz';
   what = inputobj.value;
   
    for (var i = 0; i < what.length; i++) {
       var chr = what.substring(i,i+1);
       
       if (okchars.indexOf(chr.toLowerCase()) == -1) {
       	alert("You have entered invalid characters in this field. Only alpha-numeric characters are allowed.");
       	inputobj.focus();
          return false;
       }
    }
    return true;	
}



function CheckAmount(d)
{
	if (!d) return true;
	
	d.value = trim(d.value);
	var rexp = /\,/gi;
	var str = new String(d.value.replace(rexp,''));
	rexp = /\$/gi;
	str = new String(str.replace(rexp,''));
	var okchars = '.0123456789';
   
    for (var i = 0; i < str.length; i++) {
       	var chr = str.substring(i,i+1);
       	if (okchars.indexOf(chr) == -1) {
       		alert("You have entered invalid characters in this field.");
       		d.focus();
          	return false;
       	}
    }
	return str;
}


function showhelp(page)
{
	popup(page,'Help','alwaysRaised,resizable,scrollbars,width=450,height=550');
}


function popup(page,title,params)
{
	var is_ie = 1;
	
	if (is_ie) {
		var nw = self.open(page,title,params);
		if (nw) {
			nw.focus();
		}
	}	
	else {
		open(page);
	}
	return;
}



var dlgwins = new Array();

function dialog_window(url,w,h,style,name)
{
	
	var xtop = screen.width/2 - (w/2);
	var ytop = screen.height/2 - (h/2);

	if (style == "") {
		style = 'resizable=yes,scrollbars=no,directories=0,location=0,status=0';
	}
//	if (dlgwin) {
//		dlgwin.close();
//	}
	if (name == '') name = "dialogwindow";
	
	if (dlgwins[name]) {
		dlgwins[name].close();
	}

 	dlgwin = window.open(url, name, 'width=' + w + ',height=' + h +','+style+',left=' + xtop + ',top=' + ytop);
  	if (dlgwin != null) {
		if (dlgwin.opener == null)
    		dlgwin.opener = self;
  	}
	dlgwins[name] = dlgwin;
	
    return false;
}

function delete_attachment(id)
{
	var df = document.forms[0];
	if (confirm("Are you sure you want to delete this attachment?")) {
		df.delete_attachment.value = id;
		df.submit();
	}
}

function DisplayFacility(id)
{
	var df = document.forms[0];
	var fid;
	
	if (id) fid = id;
	else if (df.FacilityID) {
		fid = df.FacilityID.value;
	}
	if (fid) {
		dialog_window('facility_info.php?dialog=1&ID='+fid,480,540,'resizable=yes,scrollbars=yes');
	}
}

function DisplayMessage(mid)
{
	if (mid) {
		dialog_window('message.php?ID='+mid,520,530,'resizable=yes,scrollbars=yes');
	}
}

function isNumberFloat(inputString)
{
  return (!isNaN(parseInt(inputString))) ? true : false;
}

var FakeSelectLast=false;
function OnSelect(EL)
{
     if (FakeSelectLast)
     {
        FakeSelectLast.className="FakeSelHiliteOFF";
     }
     FakeSelectLast=EL;
     FakeSelectLast.className="FakeSelHiliteON"
	 //alert(FakeSelectLast.cells[1].innerHTML);
}

function OnSelDblClick(EL)
{
     if (FakeSelectLast)
     {
        FakeSelectLast.className="FakeSelHiliteOFF";
     }
     FakeSelectLast=EL;
     FakeSelectLast.className="FakeSelHiliteON"
	 UseSelected();
}

function ListSelectAll(table)
{
	var tobj = FindElement(table);
	if (tobj) {
		for(var i = 1; i < tobj.rows.length; ++ i) {
			selRow(tobj.rows[i],"select");
		}
	}
}

function ListSelectNone(table)
{
	var tobj = FindElement(table);
	if (tobj) {
		for(var i = 1; i < tobj.rows.length; ++ i) {
			selRow(tobj.rows[i],"clear");
		}
	}
}

function ListSelectBetween(table)
{	
    var RowIsSelected
	var tobj = FindElement(table);
	var phase = 0;
		
	if (tobj) {
		for(var i = 1; i < tobj.rows.length; ++ i) {
 			RowIsSelected = IDS[IDArray[tobj.rows[i].rowIndex-1]];
			
			if (phase == 0 && RowIsSelected) phase = 1;
			else if (phase == 1 && RowIsSelected) phase = 2;
			if (phase == 2 && !RowIsSelected) phase = 3;			
			if (phase == 1 || phase == 2) {
				selRow(tobj.rows[i],"select");
			}
			else if (phase == 3) {
				selRow(tobj.rows[i],"clear");
			}
		}
	}
}

function addField (form, fieldType, fieldName, fieldValue) 
{
  if (document.getElementById) {
    	var input = document.createElement('INPUT');
      	if (document.all) { // what follows should work 
                          // with NN6 but doesn't in M14
        input.type = fieldType;
        input.name = fieldName;
        input.value = fieldValue;
      }
      else if (document.getElementById) { // so here is the
                                          // NN6 workaround
        input.setAttribute('type', fieldType);
        input.setAttribute('name', fieldName);
        input.setAttribute('value', fieldValue);
      }
    	form.appendChild(input);
  }
}



var ListIDS = new Array;
var IDS = new Array;
var clear_color;
// Example:
// <tr onmousedown="selRow(this,'click')"> 
function selRow(theRow,Action)
{
	var Cells = theRow.cells;
	if (!theRow.cells) return false;	
	var bsel;
	var RowIsSelected = IDS[IDArray[theRow.rowIndex-1]];

	var sel_color = "#B9D5F7";
	var cur_color = (RowIsSelected) ? sel_color : clear_color;	
	if (!RowIsSelected && !clear_color) clear_color = theRow.style.backgroundColor;
	
	switch(Action) {
		case 'over':
			//TODO:
			break;
		case 'out':
			//TODO:
			break;
		case 'click':
		case 'select':
		case 'clear':
			if ( (RowIsSelected && Action != "select") || Action == "clear") {
				newcolor = clear_color;
				bsel = 0;
			} else {
				newcolor = sel_color;
				bsel = 1;
			}
			if (IDArray && IDArray.length >= (theRow.rowIndex-1)) {
				if (bsel) IDS[IDArray[theRow.rowIndex-1]] = bsel;
				else delete IDS[IDArray[theRow.rowIndex-1]];
			}

			break;
		default:
			return false;
	}	
	if (cur_color != newcolor) {
		for(i = 0; i < theRow.cells.length; ++i) {
			Cells[i].style.backgroundColor=newcolor;
		}
	}	
	return true;
}

function MakeSelectedIDList()
{
	var SelectedIDList = '';
	var comma = '';
	if (IDS && IDS.length > 0) {
		for (var i in IDS) {
			SelectedIDList += "" + comma + i;
			comma = ',';
		}
	}
	return SelectedIDList;			
}

function FindElement(e)
{
	if (_browser == "dom") { 
		var obj = document.getElementById(e);
  		//no worky/not needed: if (!obj) obj = document.getElementByName(e);
		return obj;
	}
	else {
		return(document.all(e));
	}
}

function ShowCmdBar() 
{ 
	var b = FindElement("CmdBar"); 
	if (b) {
		if (b.style.display == "none") {
			b.style.display = '';
			changeImage("CmdBarIcon","images/icon_cmdbaroff.gif");
		}
		else {
			b.style.display = "none";
			changeImage("CmdBarIcon","images/icon_cmdbaron.gif");
		}
	} 
}

function DoListExport()
{
	var s = document.URL;
	var pre = "";
	if (s.indexOf('?') == -1) pre = "?zz=1";	
	window.location = document.URL + pre + "&Export=1";
}

function DoListModifySelected(page)
{
	var IDList = MakeSelectedIDList();
	var chk = IDList.split(',');
	if (chk.length < 2) {
		alert("You must select more than 1 item for Modify Selected function");
		return false;
	}
	if (!document.body.appendChild) {
		alert("You browser does not seem to support this function. Please upgrade your browser.");
		return false;
	}
	var newform = document.createElement('FORM');
	if (newform) {
		newform.name="IDLISTForm";
		newform.action=page;
		newform.method="post";
		document.body.appendChild(newform);
		addField (newform, "hidden", "IDLIST", IDList);
		newform.submit();
	} 
}

function EnableTabByChild(e)
{
	var p = e.parentNode;
	for(var i = 0; i < 7 && p.tagName != "DIV"; ++i ) {
		p = p.parentNode;
	}
	if (p.tagName == "DIV") {
		var tablediv = p.id; 
		var tab_td = tablediv.substr(0,tablediv.length - 3);
		var tab = FindElement(tab_td);
		if (tab) TabEnable(tab);
	}
}

function TabEnableByName(tabname,tgroup)
{
	var t = FindElement('Tab'+tabname);	
	if (t) EnableTabByChild(t);
	if (t) TabEnable(t,tgroup);
}

function TabEnable(t,tgroup)
{
	var tr = t.parentNode;
	var i;
	var cell;
	var divname;
	var div;
	var s;
	var classprefix;
	
	for(i = 0; i < tr.cells.length - 1; ++i) {
		cell = tr.cells[i];
		if (cell.id == 'TabFill') continue;
		divname = cell.id + "Div";
		div = FindElement(divname);
		if (div) {
			if (cell.id == t.id) {		
				div.style.display='';
			} else {
				div.style.display='none';
			}
		}
		s = cell.className.split('-');
		classprefix = '';
		if (s.length > 1) {
			classprefix = s[0] + "-";
		}
		cell.className= classprefix + 'taboff';
	}
	t.className=classprefix + 'tabon';
	if (!tgroup) tgroup = '_Tab';
	var tg = FindElement(tgroup);
	if (tg) {
		tg.value = t.id;
	} 
}		


// For Dual Selection boxes
// Expects Two Select Boxes, SelectedItems and AvailableItems
function sel_add_it()
{
	var df = document.forms[0];		
	sel_move_item(df.AvailableItems,df.AvailableItems.selectedIndex,df.SelectedItems);		
}

function sel_remove_all_items()
{
	var df = document.forms[0];
	var num = df.SelectedItems.length;
	var i;
	for(i = 0;i < num; ++i) {
		df.SelectedItems.selectedIndex = 0;
		sel_remove_it();
	}
}

function sel_add_all_items()
{
	var df = document.forms[0];
	var i;
	if (df.AvailableItems.length) {
		var n = df.AvailableItems.length;
		for(i = 0; i < n; ++i) {
			df.AvailableItems.selectedIndex = 0;
			sel_add_it();
		}
		df.AvailableItems.selectedIndex = 0;
	}
}

function sel_remove_it()
{
	var df = document.forms[0];
	sel_move_item(df.SelectedItems,df.SelectedItems.selectedIndex,df.AvailableItems);
}

function sel_move_item(FromSelect,index,ToSelect)
{
	if (index < 0) return;	
	var oOption = document.createElement("OPTION");
	ToSelect.options.add(oOption);
	oOption.innerText = FromSelect(index).text;
	oOption.value = FromSelect(index).value;
	
	FromSelect.remove(index);
}

function sel_shift_item(direction)
{
	var df = document.forms[0];
	
	var oSelect = df.SelectedItems;
	index = oSelect.selectedIndex;
	if (index < 0) return;
	if (index + direction < 0) return;
	if (index + direction >= oSelect.length) return;	
	var oOption = oSelect.options(index);
	var oNewOption = document.createElement("OPTION");
	oSelect.remove(index);
	index += direction;
	oSelect.options.add(oNewOption,index);
	oNewOption.innerText = oOption.text
	oNewOption.value = oOption.value;

	oSelect.options(index).selected=1;
}

function sel_get_list(delim)
{
	var list_str = "";
	var i;
	var comma = "";
	var df = document.forms[0];
	var use_delim;
	
	if (delim) use_delim = delim;
	else use_delim = ',';
	
	for (i = 0; i < df.SelectedItems.length; ++i) {
		list_str += comma + df.SelectedItems.options(i).value;
		comma = use_delim;
	}
	return list_str;
}

// Notes support
function DeleteNote(ID,NID)
{
	var df = document.forms[0];

	if (NID == -1) {
		alert("You must be the note Author or Administrator to delete this note.");
		return false;
	}	
	if (confirm("Are you sure you want to delete this note")) {
		df.DeleteNoteID.value = NID;
		df.submit();
	}
}

function EditNote(ID,NID,T,KF)
{
	var u = 'edit_note.php?T=' + T + '&ID=' + ID + '&NID=' + NID + "&KeyField=" + KF;

	if (NID == -1) {
		alert("You must be the note Author or Administrator to edit this note.");
		return false;
	}	
	dialog_window(u,630,420,'','ArticleNote');
}

function OnLoadShowNote()
{
	var e = FindElement("ShowAddNotes");
	if (e && e.value)
		Add_Notes(1);
}

function SetFocusTo(f)
{
	var e;
	if ( (e = FindElement(f)) && (e.type != "hidden")) 
		e.focus();
}


var _KeyHandlerSetup = 0;

function addKeyHandler(element) 
{
	if (_browser != "dom") return;
	
	if (_KeyHandlerSetup == 1) return;

	_KeyHandlerSetup = 1;
		
	element._keyObject = new Array();
	element._keyObject["keydown"] = new Array();
	element._keyObject["keyup"] = new Array();
	element._keyObject["keypress"] = new Array();
	
	element.addKeyDown = function (keyCode, action) {
		element._keyObject["keydown"][keyCode] = action;
	}
	element.removeKeyDown = function (keyCode) {
		element._keyObject["keydown"][keyCode] = null;
	}
	element.addKeyUp = function (keyCode, action) {
		element._keyObject["keyup"][keyCode] = action;
	}
	element.removeKeyUp = function (keyCode) {
		element._keyObject["keyup"][keyCode] = null;
	}
	element.addKeyPress = function (keyCode, action) {
		element._keyObject["keypress"][keyCode] = action;
	}
	element.removeKeyPress = function (keyCode) {
		element._keyObject["keypress"][keyCode] = null;
	}
	
	function handleEvent() {
		var type = window.event.type;
		var code = window.event.keyCode;
		//alert("keycode = " + window.event.keyCode + " type = " + window.event.type);
		if (element._keyObject[type][code] != null) 
			element._keyObject[type][code]();
	}	
	element.onkeypress = handleEvent;
	element.onkeydown = handleEvent;
	element.onkeyup = handleEvent;
	
}

function FindParentForm(oElement)
{
	//var oElement = document.getElementById("some anchor");
	while (oElement && oElement.tagName != "BODY" && oElement.tagName != "FORM") {
		oElement = oElement.parentElement;
	}
	if (oElement.tagName == "FORM") {
		return oElement;
	}
	return false;
}

function PopValues(TableName,FormName,FieldName)
{
	dialog_window('popup_values.php?TableName='+TableName+'&FormName='+FormName+'&FieldName='+FieldName,200,350,
		'resizable=yes,scrollbars=yes',"PopupValues");
}

var XMLobj = function() {}

XMLobj.prototype.GetHttpRequest = function()
{
	if ( window.XMLHttpRequest )		// Gecko
		return new XMLHttpRequest() ;
	else if ( window.ActiveXObject )	// IE
		return new ActiveXObject("MsXml2.XmlHttp") ;
}

XMLobj.prototype.LoadUrl = function( urlToCall, asyncFunctionPointer )
{
	var oXMLobj = this ;

	var bAsync = ( typeof(asyncFunctionPointer) == 'function' ) ;

	var oXmlHttp = this.GetHttpRequest() ;
	oXmlHttp.open( "GET", urlToCall, bAsync ) ;
		
	if ( bAsync )
	{
		oXmlHttp.onreadystatechange = function() 
		{
			if ( oXmlHttp.readyState == 4 )
			{
				oXMLobj.DOMDocument = oXmlHttp.responseXML ;
				asyncFunctionPointer( oXMLobj ) ;
			}
		}
	}
	oXmlHttp.send( null ) ;
	if (!bAsync) {
		this.DOMDocument = oXmlHttp.responseXML;
		return(oXmlHttp.statusText);
	}
	return oXMLobj;
}

