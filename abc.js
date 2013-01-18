
// ABC - Address Book Continued

// Author:     Mathis Dirksen-Thedens <zephyrsoft@users.sourceforge.net>
// Homepage:   http://www.zephyrsoft.net/
// License:    GPL v2

var size_min = 8;
var size_max = 24;
var size = 14;

var XMLHTTP = null;
if (window.XMLHttpRequest) {
	XMLHTTP = new XMLHttpRequest();
} else if (window.ActiveXObject) {
	try {
		XMLHTTP = new ActiveXObject("Msxml2.XMLHTTP");
	} catch(ex) {
		try {
			XMLHTTP = new ActiveXObject("Microsoft.XMLHTTP");
		} catch(ex) {
		}
	}
}

var insertRow = null;
var insertedRow = null;

function mouseOver(dieses) {
	dieses.bgColor="#dddddd";
}
function mouseOut(dieses) {
	dieses.bgColor="#ffffff";
}

function clearDebugSpace() {
	document.getElementById("debug").innerHTML = "";
}

function debug(text) {
	if (false) {
		document.getElementById("debug").innerHTML = "<br />"+text;
		window.setTimeout("clearDebugSpace()", 3000);
	}
}

function hideRemarks() {
	// change link to "show remarks"
	var link = document.getElementById("remarkslink");
	link.innerHTML = "Show remarks"
	link.setAttribute("onclick", "showRemarks(); return false;");
	// set class="rn" for whole column
	var column = document.getElementsByName("r");
	for (var i = 0; i < column.length; i++) {
		column[i].setAttribute("class", "rn");
	}
}

function showRemarks() {
	// change link to "hide remarks"
	var link = document.getElementById("remarkslink");
	link.innerHTML = "Hide remarks"
	link.setAttribute("onclick", "hideRemarks(); return false;");
	// reset whole column to class="r"
	var column = document.getElementsByName("r");
	for (var i = 0; i < column.length; i++) {
		column[i].setAttribute("class", "r");
	}
}

function bigger() {
	if (size < size_max) {
		size += 1;
		document.getElementById("addresstable").style.fontSize = ""+size+"px";
	}
	debug("text size is now "+size+"px");
	
}

function smaller() {
	if (size > size_min) {
		size -= 1;
		document.getElementById("addresstable").style.fontSize = ""+size+"px";
	}
	debug("text size is now "+size+"px");
}

function stripSpaces(text) {
	if (text == "&nbsp;" || text == "&nbsp; ") {
		return "";
	}
	while (text.substr(text.length - 1, text.length) == " ") {
		text = text.substr(0, text.length - 1);
	}
	while (text.substr(0, 1) == " ") {
		text = text.substr(1, text.length);
	}
	return text;
}

function oneTrailingSpace(text) {
	if (text == "") {
		return "&nbsp; ";
	} else {
		return stripSpaces(text) + " ";
	}
}

function xmlGet(node) {
	if (node.hasChildNodes()) {
		return myDecode(node.firstChild.nodeValue);
	} else {
		return "";
	}
}

function myEncode(text) {
	// + signs have to be encoded separately!
	return escape(text).replace(/\+/g, '%2B');
}

function myDecode(text) {
	return unescape(text);
}

function edit(id) {
	debug("start editing (ID="+id+")");
	
	// collect all data
	var el = document.getElementById(id);
	var firstname = stripSpaces(el.childNodes[1].innerHTML);
	var lastname = stripSpaces(el.childNodes[2].innerHTML);
	var street = stripSpaces(el.childNodes[3].innerHTML);
	var zipcode = stripSpaces(el.childNodes[4].innerHTML);
	var city = stripSpaces(el.childNodes[5].innerHTML);
	var birthday = stripSpaces(el.childNodes[6].innerHTML);
	var phone1 = stripSpaces(el.childNodes[7].innerHTML);
	var phone2 = stripSpaces(el.childNodes[8].innerHTML);
	var phone3 = stripSpaces(el.childNodes[9].innerHTML);
	var email = stripSpaces(el.childNodes[10].innerHTML);
	var remarks = stripSpaces(el.childNodes[11].innerHTML);
	
	// modify the table row to contain a form
	el.childNodes[1].innerHTML = "<input type=\"text\" size=\"10\" value=\""+firstname+"\" />";
	el.childNodes[2].innerHTML = "<input type=\"text\" size=\"12\" value=\""+lastname+"\" />";
	el.childNodes[3].innerHTML = "<input type=\"text\" size=\"18\" value=\""+street+"\" />";
	el.childNodes[4].innerHTML = "<input type=\"text\" size=\"4\" value=\""+zipcode+"\" />";
	el.childNodes[5].innerHTML = "<input type=\"text\" size=\"14\" value=\""+city+"\" />";
	el.childNodes[6].innerHTML = "<input type=\"text\" size=\"6\" value=\""+birthday+"\" />";
	el.childNodes[7].innerHTML = "<input type=\"text\" size=\"12\" value=\""+phone1+"\" />";
	el.childNodes[8].innerHTML = "<input type=\"text\" size=\"12\" value=\""+phone2+"\" />";
	el.childNodes[9].innerHTML = "<input type=\"text\" size=\"12\" value=\""+phone3+"\" />";
	el.childNodes[10].innerHTML = "<input type=\"text\" size=\"27\" value=\""+email+"\" />";
	el.childNodes[11].innerHTML = "<input type=\"text\" size=\"35\" value=\""+remarks+"\" />";
	
	// correct buttons
	el.childNodes[0].innerHTML = "<a href=\"#\" onclick=\"save('"+id+"'); return false;\"><img src=\"save.png\" alt=\"\" /></a>";
	el.childNodes[12].innerHTML = "";
}

function insert() {
	debug("start inserting");
	
	// collect all data from form
	insertRow = document.getElementById("i-1");
	var firstname = stripSpaces(insertRow.childNodes[1].firstChild.value);
	var lastname = stripSpaces(insertRow.childNodes[2].firstChild.value);
	var street = stripSpaces(insertRow.childNodes[3].firstChild.value);
	var zipcode = stripSpaces(insertRow.childNodes[4].firstChild.value);
	var city = stripSpaces(insertRow.childNodes[5].firstChild.value);
	var birthday = stripSpaces(insertRow.childNodes[6].firstChild.value);
	var phone1 = stripSpaces(insertRow.childNodes[7].firstChild.value);
	var phone2 = stripSpaces(insertRow.childNodes[8].firstChild.value);
	var phone3 = stripSpaces(insertRow.childNodes[9].firstChild.value);
	var email = stripSpaces(insertRow.childNodes[10].firstChild.value);
	var remarks = stripSpaces(insertRow.childNodes[11].firstChild.value);
	
	// generate new row (clone the insert row)
	insertedRow = insertRow.cloneNode(true);
	
	// send request to server which updates the database there
	XMLHTTP.open("POST", document.URL);
	XMLHTTP.onreadystatechange = insert_finish;
	XMLHTTP.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	XMLHTTP.send("abc_action=insert&firstname="+myEncode(firstname)+
		"&lastname="+myEncode(lastname)+"&street="+myEncode(street)+"&zipcode="+myEncode(zipcode)+
		"&city="+myEncode(city)+"&birthday="+myEncode(birthday)+"&phone1="+myEncode(phone1)+
		"&phone2="+myEncode(phone2)+"&phone3="+myEncode(phone3)+"&email="+myEncode(email)+
		"&remarks="+myEncode(remarks));
}

function insert_finish() {
	if (XMLHTTP.readyState == 4) {
		// read XML data
		var id, firstname, lastname, street, zipcode, city, birthday, phone1, phone2, phone3, email, remarks;
		var data = XMLHTTP.responseXML.getElementsByTagName("data");
		for (var i = 0; i < data.length; i++) {
			for (var j = 0; j < data[i].childNodes.length; j++) {
				var node = data[i].childNodes[j];
				switch (node.nodeName) {
					case "id":
						id = xmlGet(node);
						break;
					case "firstname":
						firstname = xmlGet(node);
						break;
					case "lastname":
						lastname = xmlGet(node);
						break;
					case "street":
						street = xmlGet(node);
						break;
					case "zipcode":
						zipcode = xmlGet(node);
						break;
					case "city":
						city = xmlGet(node);
						break;
					case "birthday":
						birthday = xmlGet(node);
						break;
					case "phone1":
						phone1 = xmlGet(node);
						break;
					case "phone2":
						phone2 = xmlGet(node);
						break;
					case "phone3":
						phone3 = xmlGet(node);
						break;
					case "email":
						email = xmlGet(node);
						break;
					case "remarks":
						remarks = xmlGet(node);
						break;
				}
			}
		}
		
		debug("finish inserting (ID="+id+")");
		
		// clear insert row
		insertRow.childNodes[1].firstChild.value = "";
		insertRow.childNodes[2].firstChild.value = "";
		insertRow.childNodes[3].firstChild.value = "";
		insertRow.childNodes[4].firstChild.value = "";
		insertRow.childNodes[5].firstChild.value = "";
		insertRow.childNodes[6].firstChild.value = "";
		insertRow.childNodes[7].firstChild.value = "";
		insertRow.childNodes[8].firstChild.value = "";
		insertRow.childNodes[9].firstChild.value = "";
		insertRow.childNodes[10].firstChild.value = "";
		insertRow.childNodes[11].firstChild.value = "";
		insertRow.setAttribute("bgColor", "");
		
		// modify the new (cloned) table row to contain the values
		insertedRow.setAttribute("id", id);
		insertedRow.childNodes[1].innerHTML = oneTrailingSpace(firstname);
		insertedRow.childNodes[2].innerHTML = oneTrailingSpace(lastname);
		insertedRow.childNodes[3].innerHTML = oneTrailingSpace(street);
		insertedRow.childNodes[4].innerHTML = oneTrailingSpace(zipcode);
		insertedRow.childNodes[5].innerHTML = oneTrailingSpace(city);
		insertedRow.childNodes[6].innerHTML = oneTrailingSpace(birthday);
		insertedRow.childNodes[7].innerHTML = oneTrailingSpace(phone1);
		insertedRow.childNodes[8].innerHTML = oneTrailingSpace(phone2);
		insertedRow.childNodes[9].innerHTML = oneTrailingSpace(phone3);
		insertedRow.childNodes[10].innerHTML = oneTrailingSpace(email);
		insertedRow.childNodes[11].innerHTML = oneTrailingSpace(remarks);
		insertedRow.removeAttribute("class");
		
		// correct buttons
		insertedRow.childNodes[0].innerHTML = "<a href=\"#\" onclick=\"edit('"+id+"'); return false;\"><img src=\"edit.png\" alt=\"\" /></a>";
		insertedRow.childNodes[12].innerHTML = "<a href=\"#\" onclick=\"del('"+id+"'); return false;\"><img src=\"delete.png\" alt=\"\" /></a>";
		
		// add the previously saved "insert" table row
		document.getElementById("addresstable").appendChild(insertedRow);
	}
}

function save(id) {
	debug("start saving (ID="+id+")");
	
	// collect all data from form
	var el = document.getElementById(id);
	var firstname = stripSpaces(el.childNodes[1].firstChild.value);
	var lastname = stripSpaces(el.childNodes[2].firstChild.value);
	var street = stripSpaces(el.childNodes[3].firstChild.value);
	var zipcode = stripSpaces(el.childNodes[4].firstChild.value);
	var city = stripSpaces(el.childNodes[5].firstChild.value);
	var birthday = stripSpaces(el.childNodes[6].firstChild.value);
	var phone1 = stripSpaces(el.childNodes[7].firstChild.value);
	var phone2 = stripSpaces(el.childNodes[8].firstChild.value);
	var phone3 = stripSpaces(el.childNodes[9].firstChild.value);
	var email = stripSpaces(el.childNodes[10].firstChild.value);
	var remarks = stripSpaces(el.childNodes[11].firstChild.value);
	
	// send request to server which updates the database there
	XMLHTTP.open("POST", document.URL);
	XMLHTTP.onreadystatechange = save_finish;
	XMLHTTP.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	XMLHTTP.send("abc_action=update&id="+myEncode(id)+"&firstname="+myEncode(firstname)+
		"&lastname="+myEncode(lastname)+"&street="+myEncode(street)+"&zipcode="+myEncode(zipcode)+
		"&city="+myEncode(city)+"&birthday="+myEncode(birthday)+"&phone1="+myEncode(phone1)+
		"&phone2="+myEncode(phone2)+"&phone3="+myEncode(phone3)+"&email="+myEncode(email)+
		"&remarks="+myEncode(remarks));
}

function save_finish() {
	if (XMLHTTP.readyState == 4) {
		// read XML data
		var id, firstname, lastname, street, zipcode, city, birthday, phone1, phone2, phone3, email, remarks;
		var data = XMLHTTP.responseXML.getElementsByTagName("data");
		for (var i = 0; i < data.length; i++) {
			for (var j = 0; j < data[i].childNodes.length; j++) {
				var node = data[i].childNodes[j];
				switch (node.nodeName) {
					case "id":
						id = xmlGet(node);
						break;
					case "firstname":
						firstname = xmlGet(node);
						break;
					case "lastname":
						lastname = xmlGet(node);
						break;
					case "street":
						street = xmlGet(node);
						break;
					case "zipcode":
						zipcode = xmlGet(node);
						break;
					case "city":
						city = xmlGet(node);
						break;
					case "birthday":
						birthday = xmlGet(node);
						break;
					case "phone1":
						phone1 = xmlGet(node);
						break;
					case "phone2":
						phone2 = xmlGet(node);
						break;
					case "phone3":
						phone3 = xmlGet(node);
						break;
					case "email":
						email = xmlGet(node);
						break;
					case "remarks":
						remarks = xmlGet(node);
						break;
				}
			}
		}
		
		debug("finish saving (ID="+id+")");
		
		// modify the table row to contain normal text again
		var el = document.getElementById(id);
		el.childNodes[1].innerHTML = oneTrailingSpace(firstname);
		el.childNodes[2].innerHTML = oneTrailingSpace(lastname);
		el.childNodes[3].innerHTML = oneTrailingSpace(street);
		el.childNodes[4].innerHTML = oneTrailingSpace(zipcode);
		el.childNodes[5].innerHTML = oneTrailingSpace(city);
		el.childNodes[6].innerHTML = oneTrailingSpace(birthday);
		el.childNodes[7].innerHTML = oneTrailingSpace(phone1);
		el.childNodes[8].innerHTML = oneTrailingSpace(phone2);
		el.childNodes[9].innerHTML = oneTrailingSpace(phone3);
		el.childNodes[10].innerHTML = oneTrailingSpace(email);
		el.childNodes[11].innerHTML = oneTrailingSpace(remarks);
		
		// correct buttons
		el.childNodes[0].innerHTML = "<a href=\"#\" onclick=\"edit('"+id+"'); return false;\"><img src=\"edit.png\" alt=\"\" /></a>";
		el.childNodes[12].innerHTML = "<a href=\"#\" onclick=\"del('"+id+"'); return false;\"><img src=\"delete.png\" alt=\"\" /></a>";
	}
}

function del(id) {
	debug("start deleting (ID="+id+")");
	
	// send request to server which deletes the row there
	XMLHTTP.open("POST", document.URL);
	XMLHTTP.onreadystatechange = del_finish;
	XMLHTTP.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	XMLHTTP.send("abc_action=delete&id="+myEncode(id));
}

function del_finish() {
	if (XMLHTTP.readyState == 4) {
		var id = null;
		var data = XMLHTTP.responseXML.getElementsByTagName("data");
		for (var i = 0; i < data.length; i++) {
			for (var j = 0; j < data[i].childNodes.length; j++) {
				var node = data[i].childNodes[j];
				if (node.nodeName=="id") {
					id = xmlGet(node);
				}
			}
		}
		debug("finish deleting (ID="+id+")");
		
		// delete the table row
		var el = document.getElementById(id);
		el.parentNode.removeChild(el);
	}
}

