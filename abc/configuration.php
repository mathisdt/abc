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

// max. session duration in time format
$sessionmaxtime = '06:00:00';

// ==================== CONFIGURATION END ====================

// only change something below this line if you really know what you're doing!

$dbusertable = 'user';
$dbaddresstable = 'address';
$defaultorder = 'lastname,street,firstname';

?>
