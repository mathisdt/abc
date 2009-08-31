<?php

/*
 * ABC - Address Book Continued
 *
 * Author:     Mathis Dirksen-Thedens <zephyrsoft@users.sourceforge.net>
 * Homepage:   http://www.zephyrsoft.net/
 * License:    GPL v2
 */

define( '_VALID_ABC', 1 );

require_once('configuration.php');
require_once('functions.php');

// start or restore session
ini_set('session.use_only_cookies', 1);
session_name('address_book_continued_'.str_replace('.', '_', $_SERVER['REMOTE_ADDR']).'_'.$abc_id);
session_start();

if (getLoggedInUser() == null && $_SERVER['REQUEST_METHOD'] == 'POST') {
	// login a user
	if (login($_POST['user'], $_POST['password'])) {
		header('Location: '.getURL());
	} else {
		header('Location: '.getURL() . '?msg=loginfail');
	}
} else if (getLoggedInUser() == null && $_SERVER['REQUEST_METHOD'] == 'GET') {
	if ($allow_passwordless_login && $_GET['login'] != null && $_GET['login'] !='' && $_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
		// login this user without password
		if (login($_GET['login'], null, true)) {
			header('Location: '.getURL());
		} else {
			header('Location: '.getURL() . '?msg=passlessloginfail');
		}
	} else {
		// show login form
		echo getHeader();
		echo '<span id="title"><h2>Address Book Continued: Login</h2></span>';
		echo '<form method="POST" action="'.getURL().'">';
		echo '<table border="0">';
		echo '<tr><td style="text-align: right;">Username:</td><td><input type="text" name="user" size="30" style="padding-left: 3px;" /></td></tr>';
		echo '<tr><td style="text-align: right;">Password:</td><td><input type="password" name="password" size="30" style="padding-left: 3px;" /></td></tr>';
		echo '<tr><td colspan="2" style="text-align: right;"><input type="submit" value="Login!"/></td></tr>';
		echo '</table>';
		echo '</form>';
		if ($_GET['msg'] != null && $_GET['msg'] == 'loginfail') {
			echo '<br /><span style="color: red;"><small>This combination of username and password is incorrect!</small></span>';
		} else if ($_GET['msg'] != null && $_GET['msg'] == 'passlessloginfail') {
			echo '<br /><span style="color: red;"><small>This username is incorrect!</small></span>';
		}
		echo getFooter();
	}
} else if (getLoggedInUser() != null && $_SERVER['REQUEST_METHOD'] == 'GET') {
	// user has logged in and sends a frontend request
	if ($_GET['abc_action'] == null || $_GET['abc_action'] == "" || $_GET['abc_action'] == "list") {
		// show the address list
		echo getHeader();
		echo '<div id="title"><h2>Address Book Continued</h2></div>';
		echo '<div id="printtitle"><h2>Address Book of '.ucfirst(getLoggedInUser()).'</h2></div>';
		echo '<span id="navigation"><a href="#" onclick="window.print(); return false;">Print</a>&nbsp;-&nbsp;<a id="remarkslink" href="#" onclick="showRemarks(); return false;">Show remarks</a>&nbsp;-&nbsp;<a href="#" onclick="bigger(); return false;">'.
			'Increase font size</a>&nbsp;-&nbsp;<a href="#" onclick="smaller(); return false;">Decrease font size</a>&nbsp;-&nbsp;<a href="'.getURL().
			'?abc_action=palmexport">Export CSV for JPilot (for Palm devices)</a>&nbsp;-&nbsp;<a href="'.getURL().'?abc_action=logout">Logout</a></span>';
		echo '<div id="debug"></div>';
		echo '<br /><br />';
		echo getAllRows();
		echo '<br /><br />';
		echo getFooter();
	} else if ($_GET['abc_action'] == "palmexport") {
		// export all data for use with JPilot
		header('Content-type: application/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="abc-jpilot.csv"');
		echo getJPilotCSV();
	} else if ($_GET['abc_action'] == "logout") {
		// logout the user
		logout();
		header('Location: '.getURL());
	} else {
		die(getErrorPage('incorrect menu item'));
	}
} else if (getLoggedInUser() != null && $_SERVER['REQUEST_METHOD'] == 'POST') {
	// user has logged in and sends a database update request via AJAX
	if ($_POST['abc_action'] == "insert") {
		// insert one record
		header('Content-type: text/xml');
		echo insertRow(myDecode($_POST['firstname']), myDecode($_POST['lastname']), myDecode($_POST['street']), myDecode($_POST['zipcode']),
			myDecode($_POST['city']), myDecode($_POST['birthday']), myDecode($_POST['phone1']),
			myDecode($_POST['phone2']), myDecode($_POST['phone3']), myDecode($_POST['email']), myDecode($_POST['remarks']));
	} else if ($_POST['abc_action'] == "update") {
		// update one record
		header('Content-type: text/xml');
		echo updateRow(myDecode($_POST['id']), myDecode($_POST['firstname']), myDecode($_POST['lastname']), myDecode($_POST['street']),
			myDecode($_POST['zipcode']), myDecode($_POST['city']), myDecode($_POST['birthday']),
			myDecode($_POST['phone1']), myDecode($_POST['phone2']), myDecode($_POST['phone3']), myDecode($_POST['email']), myDecode($_POST['remarks']));
	} else if ($_POST['abc_action'] == "delete") {
		// delete one record
		header('Content-type: text/xml');
		echo deleteRow(myDecode($_POST['id']));
	}
} else {
	die(getErrorPage('incorrect http method'));
}

?>
