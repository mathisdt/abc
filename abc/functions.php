<?php

/*
 * ABC - Address Book Continued
 *
 * Author:     Mathis Dirksen-Thedens <zephyrsoft@users.sourceforge.net>
 * Homepage:   http://www.zephyrsoft.net/
 * License:    GPL v2
 */

defined( '_VALID_ABC' ) or die( 'Direct access forbidden!' );

function getErrorPage($errormessage) {
	// make an error message look nice
	return '<html><head><title>Error Report</title></head><body>'.
		'<br /><big><b>The following error occured while processing your request:</b></big>'.
		'<br /><br /><pre>'.$errormessage.' </pre></body></html>';
}

$link = NULL;

function db_connect() {
	global $link, $dbhost, $dbuser, $dbpassword;
	$link = mysql_pconnect($dbhost, $dbuser, $dbpassword) or die(getErrorPage('MySQL connection error: ' . mysql_error()));
}

function db_result($query) {
	// query the database and return the result
	global $dbinstance;
	db_connect();
	mysql_select_db($dbinstance) or die(getErrorPage('MySQL database selection error'));
	$result = mysql_query($query) or die(getErrorPage('MySQL query error: ' . mysql_error()));
	if (!$result) {
		die(getErrorPage('MySQL query error: ' . mysql_error()));
	}
	return $result;
}

function db_rows($query) {
	// query the database and return the number of affected or returned rows
	global $dbinstance;
	db_connect();
	mysql_select_db($dbinstance) or die(getErrorPage('MySQL database selection error'));
	mysql_query($query) or die(getErrorPage('MySQL query error: ' . mysql_error()));
	return mysql_affected_rows();
}

function login($username, $password, $passwordless = FALSE) {
	// login this user
	global $dbusertable;
	$count = 0;
	if ($passwordless) {
		$count = db_rows('select username from '.$dbusertable.' where username="'.my_escape_string($username).'"');
	} else {
		$count = db_rows('select username,password from '.$dbusertable.' where username="'.my_escape_string($username).'" and password="'.
			my_escape_string($password).'"');
	}
	if ($count == 1) {
		db_rows('update '.$dbusertable.' set lastlogin=now() where username="'.my_escape_string($username).'"');
		$_SESSION['user'] = my_escape_string($username);
		return true;
	} else {
		$_SESSION['user'] = '';
		return false;
	}
}

function getLoggedInUser() {
	// is a user logged in? if so, return the username, else return null
	global $sessionmaxtime, $dbusertable;
	if (isset($_SESSION['user']) && $_SESSION['user'] != '') {
		$count = db_rows('select username from '.$dbusertable.' where username="'.my_escape_string($_SESSION['user']).'" '.
			'and addtime(lastlogin, "'.$sessionmaxtime.'")>now()');
		if ($count == 1) {
			return $_SESSION['user'];
		}
	}
	return null;
}

function logout() {
	// logout the user
	global $dbusertable;
	if (getLoggedInUser() != null) {
		db_rows('update '.$dbusertable.' set lastlogin="0000-00-00 00:00:00" where username="'.my_escape_string($_SESSION['user']).'"');
		$_SESSION = array();
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}
		session_destroy();
		return true;
	} else {
		return false;
	}
}

function getURL() {
	return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
}

function getAllRows() {
	// get all rows as HTML for displaying
	global $dbaddresstable, $defaultorder;
	$result = db_result('select id,firstname,lastname,street,zipcode,city,birthday,phone1,phone2,phone3,email,remarks from '.$dbaddresstable.
		' where owner="'.getLoggedInUser().'" order by '.$defaultorder);
	$ret = '<form action="#"><table class="sortable" id="addresstable" cellspacing="0" style="font-family:\'Times New Roman\',Times,serif; font-size: 14px;">'."\n";
	
	$ret .= '<tr>';
	$ret .= '<th class="nosort"> </th>';
	$ret .= '<th class="u">First Name </th>';
	$ret .= '<th class="ul">Last Name </th>';
	$ret .= '<th class="ul">Street </th>';
	$ret .= '<th class="ul">ZIP </th>';
	$ret .= '<th class="ul">City </th>';
	$ret .= '<th class="ul">Birthday </th>';
	$ret .= '<th class="ul">Phone 1 </th>';
	$ret .= '<th class="ul">Phone 2 </th>';
	$ret .= '<th class="ul">Phone 3 </th>';
	$ret .= '<th class="ul">Email </th>';
	$ret .= '<th class="rn" name="r">Remarks </th>';
	$ret .= '<th class="nosort"> </th>';
	$ret .= '</tr>'."\n";

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$ret .= '<tr id="i'.$row['id'].'" onMouseOver="mouseOver(this)" onMouseOut="mouseOut(this)">';
		$ret .= '<td class="np"><a href="#" onclick="edit(\'i'.$row['id'].'\'); return false;"><img src="edit.png" alt="" /></a></td>';
		$ret .= '<td class="u">'.nbspIfEmpty(htmlentities($row['firstname'], ENT_COMPAT, 'ISO-8859-15')).' </td>';
		$ret .= '<td class="ul">'.nbspIfEmpty(htmlentities($row['lastname'], ENT_COMPAT, 'ISO-8859-15')).' </td>';
		$ret .= '<td class="ul">'.nbspIfEmpty(htmlentities($row['street'], ENT_COMPAT, 'ISO-8859-15')).' </td>';
		$ret .= '<td class="ul">'.nbspIfEmpty(htmlentities($row['zipcode'], ENT_COMPAT, 'ISO-8859-15')).' </td>';
		$ret .= '<td class="ul">'.nbspIfEmpty(htmlentities($row['city'], ENT_COMPAT, 'ISO-8859-15')).' </td>';
		$ret .= '<td class="ul">'.nbspIfEmpty(htmlentities($row['birthday'], ENT_COMPAT, 'ISO-8859-15')).' </td>';
		$ret .= '<td class="ul">'.nbspIfEmpty(htmlentities($row['phone1'], ENT_COMPAT, 'ISO-8859-15')).' </td>';
		$ret .= '<td class="ul">'.nbspIfEmpty(htmlentities($row['phone2'], ENT_COMPAT, 'ISO-8859-15')).' </td>';
		$ret .= '<td class="ul">'.nbspIfEmpty(htmlentities($row['phone3'], ENT_COMPAT, 'ISO-8859-15')).' </td>';
		$ret .= '<td class="ul">'.nbspIfEmpty(htmlentities($row['email'], ENT_COMPAT, 'ISO-8859-15')).' </td>';
		$ret .= '<td class="rn" name="r">'.nbspIfEmpty(htmlentities($row['remarks'], ENT_COMPAT, 'ISO-8859-15')).' </td>';
		$ret .= '<td class="np"><a href="#" onclick="del(\'i'.$row['id'].'\'); return false;"><img src="delete.png" alt="" /></a></td>';
		$ret .= '</tr>'."\n";
	}
	$ret .= '</table></form><br />';
	$ret .= '<form action="#"><table cellspacing="0" style="font-family:\'Times New Roman\',Times,serif; font-size: 14px;">'."\n";
	$ret .= '<tr class="np">';
	$ret .= '<th class="nosort"> </th>';
	$ret .= '<th class="u">First Name </th>';
	$ret .= '<th class="ul">Last Name </th>';
	$ret .= '<th class="ul">Street </th>';
	$ret .= '<th class="ul">ZIP </th>';
	$ret .= '<th class="ul">City </th>';
	$ret .= '<th class="ul">Birthday </th>';
	$ret .= '<th class="ul">Phone 1 </th>';
	$ret .= '<th class="ul">Phone 2 </th>';
	$ret .= '<th class="ul">Phone 3 </th>';
	$ret .= '<th class="ul">Email </th>';
	$ret .= '<th class="rn" name="r">Remarks </th>';
	$ret .= '<th class="nosort"> </th>';
	$ret .= '</tr>'."\n";
	$ret .= '<tr class="np" id="i-1" onMouseOver="mouseOver(this)" onMouseOut="mouseOut(this)">';
	$ret .= '<td class="np"><a href="#" onclick="insert(); return false;"><img src="insert.png" alt="" /></a></td>';
	$ret .= '<td class="u"><input type="text" size="10" /></td>';
	$ret .= '<td class="ul"><input type="text" size="12" /></td>';
	$ret .= '<td class="ul"><input type="text" size="18" /></td>';
	$ret .= '<td class="ul"><input type="text" size="4" /></td>';
	$ret .= '<td class="ul"><input type="text" size="14" /></td>';
	$ret .= '<td class="ul"><input type="text" size="6" /></td>';
	$ret .= '<td class="ul"><input type="text" size="12" /></td>';
	$ret .= '<td class="ul"><input type="text" size="12" /></td>';
	$ret .= '<td class="ul"><input type="text" size="12" /></td>';
	$ret .= '<td class="ul"><input type="text" size="27" /></td>';
	$ret .= '<td class="rn" name="r"><input type="text" size="35" /></td>';
	$ret .= '<td class="np"></td>';
	$ret .= '</tr>'."\n";
	$ret .= '</table></form>'."\n";
	return $ret;
}

function getJPilotCSV() {
	// get all rows as CSV for importing it into JPilot (and then syncing it to a Palm device)
	// NOTE: as JPilot's birthday handling is broken, the value in the birthday field is put into field "Custom1"
	global $dbaddresstable, $defaultorder;
	$result = db_result('select id,firstname,lastname,street,zipcode,city,birthday,phone1,phone2,phone3,email,remarks from '.$dbaddresstable.
		' where owner="'.getLoggedInUser().'" order by '.$defaultorder);
	$ret = 'CSV address: Category, Private, Last, First, Title, Company, Phone1, Phone2, Phone3,';
	$ret .= ' Phone4, Phone5, Address, City, State, ZipCode, Country, Custom1, Custom2, Custom3, Custom4, Note,';
	$ret .= ' phoneLabel1, phoneLabel2, phoneLabel3, phoneLabel4, phoneLabel5, showPhone'."\n";
	
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$ret .= '"Nicht abgelegt","0","'.encodeToUtf8($row['lastname']).'","'.encodeToUtf8($row['firstname']).'","","","'.encodeToUtf8($row['phone1']).'","'.encodeToUtf8($row['phone2']).'","'.encodeToUtf8($row['phone3']).'"';
		$ret .= ',"'.encodeToUtf8($row['email']).'","","'.encodeToUtf8($row['street']).'","'.encodeToUtf8($row['city']).'","","'.encodeToUtf8($row['zipcode']).'","","'.encodeToUtf8($row['birthday']).'","","","","'.encodeToUtf8($row['remarks']).'"';
		$ret .= ',"'.getPilotPhoneType($row['phone1']).'","'.getPilotPhoneType($row['phone2']).'","'.getPilotPhoneType($row['phone3']).'","4","1","0"'."\n";
	}
	// return the UTF8 data
	return $ret;
}

function encodeToUtf8($input) {
	return utf8_encode($input);
}

function getPilotPhoneType($phone) {
	// types:   1 = wired phone   /   7 = cell phone   (and 4 = email, but this is not used here)
	if ($phone==null || $phone=="") {
		return "1";
	} else if (preg_match('/^01[5-7]/', $phone)) {
		return "7";
	} else {
		return "1";
	}
}

function nbspIfEmpty($input) {
	if (strlen($input) > 0) {
		return $input;
	} else {
		return '&nbsp;';
	}
}

function myEncode($input) {
	return rawurlencode($input);
}

function myDecode($input) {
	return rawurldecode($input);
}

function my_escape_string($input) {
	global $link;
	if ($link == NULL) {
		db_connect();
	}
	if (get_magic_quotes_gpc()) {
		return mysql_real_escape_string(stripslashes($input), $link);
    } else {
    	return mysql_real_escape_string($input, $link);
    }
}

function insertRow($firstname, $lastname, $street, $zipcode, $city, $birthday, $phone1, $phone2, $phone3, $email, $remarks) {
	// insert one row - only called via AJAX - returns XML
	global $dbaddresstable;
	
	// get the ID that MySQL will assign to the next added row
	$tempresult = db_result('show table status like "'.$dbaddresstable.'"');
	$next_id = 0;
	while ($row = mysql_fetch_assoc($tempresult)) {
		$next_id = $row['Auto_increment'];
	}
	mysql_free_result($tempresult);
	
	// add the new row
	db_rows('insert into '.$dbaddresstable.' (firstname, lastname, street, zipcode, city, birthday, phone1, phone2, phone3, email, remarks, owner) '.
		'values ("'.my_escape_string($firstname).'", "'.my_escape_string($lastname).'", "'.my_escape_string($street).'", "'.
		my_escape_string($zipcode).'", "'.my_escape_string($city).'", "'.my_escape_string($birthday).'", "'.my_escape_string($phone1).'", "'.
		my_escape_string($phone2).'", "'.my_escape_string($phone3).'", "'.my_escape_string($email).'", "'.my_escape_string($remarks).'", "'.
		getLoggedInUser().'")');
	
	// return XML
	return '<data><id>i'.myEncode($next_id).'</id><firstname>'.myEncode($firstname).'</firstname><lastname>'.myEncode($lastname).'</lastname><street>'.
		myEncode($street).'</street><zipcode>'.myEncode($zipcode).'</zipcode><city>'.myEncode($city).'</city><birthday>'.myEncode($birthday).
		'</birthday><phone1>'.myEncode($phone1).'</phone1><phone2>'.myEncode($phone2).'</phone2><phone3>'.
		myEncode($phone3).'</phone3><email>'.myEncode($email).'</email><remarks>'.myEncode($remarks).'</remarks></data>';
}

function updateRow($id, $firstname, $lastname, $street, $zipcode, $city, $birthday, $phone1, $phone2, $phone3, $email, $remarks) {
	// update one row, coming from editing it - only called via AJAX - returns XML
	global $dbaddresstable;
	
	// update row in database
	db_rows('update '.$dbaddresstable.' set firstname="'.my_escape_string($firstname).'", lastname="'.my_escape_string($lastname).'", street="'.
		my_escape_string($street).'", zipcode="'.my_escape_string($zipcode).'", city="'.my_escape_string($city).'", birthday="'.
		my_escape_string($birthday).'", phone1="'.my_escape_string($phone1).'", phone2="'.my_escape_string($phone2).'", phone3="'.
		my_escape_string($phone3).'", email="'.my_escape_string($email).'", remarks="'.my_escape_string($remarks).'" where id="'.
		my_escape_string(substr($id,1)).'" and owner="'.getLoggedInUser().'"');
	
	// return XML
	return '<data><id>'.myEncode($id).'</id><firstname>'.myEncode($firstname).'</firstname><lastname>'.myEncode($lastname).'</lastname><street>'.
		myEncode($street).'</street><zipcode>'.myEncode($zipcode).'</zipcode><city>'.myEncode($city).'</city><birthday>'.myEncode($birthday).
		'</birthday><phone1>'.myEncode($phone1).'</phone1><phone2>'.myEncode($phone2).'</phone2><phone3>'.
		myEncode($phone3).'</phone3><email>'.myEncode($email).'</email><remarks>'.myEncode($remarks).'</remarks></data>';
}

function deleteRow($id) {
	// delete one row - only called via AJAX - returns only the ID (no XML)
	global $dbaddresstable;
	
	db_rows('delete from '.$dbaddresstable.' where id="'.my_escape_string(substr($id,1)).'" and owner="'.getLoggedInUser().'"');
	
	// return XML
	return '<data><id>'.myEncode($id).'</id></data>';
}

function getHeader() {
	return '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'.
		'<html><head><title>Address Book Continued</title>'.
		'<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />'.
		'<link rel="stylesheet" type="text/css" href="style.css" media="all" />'.
		'<link rel="stylesheet" type="text/css" href="printstyle.css" media="print" />'.
		'<script src="abc.js" type="text/javascript"></script>'.
		'<script src="sorttable.js" type="text/javascript"></script>'.
		'</head><body>';
}

function getFooter() {
	return '</body></html>';
}

?>
