<?php

/*
 * ABC - Address Book Continued
 *
 * Author:     Mathis Dirksen-Thedens <zephyrsoft@users.sourceforge.net>
 * Homepage:   http://zephyrsoftware.sourceforge.net/
 * License:    GPL v2
 */

defined( '_VALID_ABC' ) or die( 'Direct access forbidden!' );

// =================== CONFIGURATION START ===================

// mysql database configuration
$dbhost = 'localhost';
$dbinstance = 'abc';
$dbuser = 'abc';
$dbpassword = 'abc';

// max. session duration in format hh:mm:ss
$sessionmaxtime = '06:00:00';

// passwordless login from localhost using ?login=username
$allow_passwordless_login = TRUE;

// put a unique ID in here (only characters a-z and underscore _)
// if you want to install ABC more than once on a server
// (important - else your users will be logged out of all ABC installations when they click "logout")
$abc_id = 'default';

// ==================== CONFIGURATION END ====================

// only change something below this line if you really know what you're doing!

$dbusertable = 'user';
$dbaddresstable = 'address';
$defaultorder = 'lastname,street,firstname';

?>
