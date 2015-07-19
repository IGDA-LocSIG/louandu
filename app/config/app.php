<?php

// ---- LOG and DEBUGGING -----------------------------------------------------

$GLOBALS['config']['log_front'] = 0;
$GLOBALS['config']['log_debug'] = 1;
$GLOBALS['config']['log_message'] = 1;
$GLOBALS['config']['log_warn'] = 0;
$GLOBALS['config']['log_error'] = 2;
$GLOBALS['config']['log_core'] = 0;

$GLOBALS['config']['log_signature'] = '[TF]';

// --- APPLICATION CONTOLLER, ACTION and PAGES---------------------------------

$GLOBALS['config']['app'] = array(
	'default_path'		=> 'task',
		// default path to lookup (home page)
	'default_controller'	=> 'index',
		// default controller to call (home page)
	'default_action'		=> 'main'
		// default action to call (home page)
);

$GLOBALS['config']['pages'] = array(
	'todo'		=> 'task/index',
	'done'		=> 'task/done',
	'invoiced'	=> 'task/invoiced',
	'paid'		=> 'task/paid',
	'div1'		=> '',
	'archived'	=> 'task/archived',
	// 'div2'		=> '',
	'reports'	=> 'reports/index'
);

// ---- DATE / TIME FORMATS ---------------------------------------------------

// date/time timezone and formats defaults
$GLOBALS['config']['datetime'] = array(
	'timezone_server'	=> new DateTimeZone(APP_TIMEZONE_SERVER),
	// 'timezone_user'		=> new DateTimeZone('Asia/Bangkok'),
	'timezone_user'		=> new DateTimeZone(APP_TIMEZONE_SERVER),
	'timezone_model'	=> 'server', // date/times timezone : user or server
	'us_format'			=> false
);
$GLOBALS['config']['datetime']['now'] = new DateTime('now', $GLOBALS['config']['datetime']['timezone_server']);

// --- Specific DATE FORMATS -------------------------------------------------

define("APP_DATE","<small>%A</small> %d/%m");
define('APP_DATE_OVERDUE','%d/%m');
define('APP_DATE_LATER','<small>%a %d/%m</small>');
define("APP_DATETIME","%d/%m <small>%H:%M</small>");

// --- APP DEFAULTS ---------------------------------------------------------

$GLOBALS['config']['users']['class'] = array(
	'options' 	=> array( // for an HTML <select>
		'client'		=> 'clients', // option value => label
		'freelancer'	=> 'freelancers',
		'staff'			=> 'staff',
		'manager'		=> 'managers'
	),
	'acl'	=> array( // to convert from HTML <option> to ACL in database
		'client'		=> array(),
		'freelancer'	=> array('create_memo'),
		'staff'			=> array('view_user','create_user','create_memo','task_see_all','task_edit_all','create_project'),
		'manager'		=> array('admin_user','view_user','create_user','task_see_all','task_edit_all','invoicing','create_memo','create_project','view_report')
	),
	'default'	=> 'client'
);

$GLOBALS['config']['users']['invoice'] = array(
	'options'	=> array(
		0	=> '-',
		30	=> '30 days',
		60	=> '60 days',
		90	=> '90 days'
	),
	'mode'	=> 't', // t : end of the month, d : on the date
	'auto'	=> true, // automatically adte invoice date on creation
	'default'	=> 0
);

$GLOBALS['config']['task']['work'] = array(
	'options'	=> array(
		0	=> 'Other',
		1	=> 'Translate',
		2	=> 'Revision',
		3	=> 'Hour Rate',
		4	=> 'Fixed Rate'
	),
	'sell'	=> array(
		1	=> 0.20,
		2	=> 0.15,
		3	=> 100
	),
	'buy'	=> array(
		1	=> 0.15,
		2	=> 0.08,
		3	=> 75
	),
	'short'	=> array(
		'O'	=> 0,
		'T'	=> 1,
		'R'	=> 2,
		'H'	=> 3,
		'F'	=> 4
	),
	'default'	=> 0
);

$GLOBALS['config']['task']['context'] = array(
	'options'	=> array(
		0	=> 'memo',
		1	=> 'client',
		2	=> 'vendor',
		3	=> 'invoice'
	),
	'default'	=> 0
);

$GLOBALS['config']['task']['status'] = array(
	'options'	=> array(
		0	=> 'todo',
		1	=> 'done',
		2	=> 'invoiced',
		3	=> 'paid'
	),
	'default'	=> 0
);

$GLOBALS['config']['task']['pagination'] = array(10=>10, 20=>20,40=>40,60=>60,'all'=>0);
$GLOBALS['config']['task']['pagination_default'] = 20;

$GLOBALS['config']['billing'] = array(
	'address' 	=>
		"Alain Dellepiane\n"
		."Sonnen Heime R103\n"
		."4-44-17 Minami-Ogikubo\n"
		."Suginami-ku, Tokyo\n"
		."Japan 167-0052",
	'logo'		=> APP_ASSET_PATH.'/img/logo-pdf.jpg',
	'footer'	=> 
		'BANK: Banca Carige Spa Cassa Di Risparmio Di Genova e Imperia - ADDRESS: Piazza Banchi 2 R, 16123 Genova Italy - BIC CODE: CRGEITGG132<br />'
		.'BRANCH: 132 - BANK ACCOUNT: 1230880 - IBAN: IT98 C061 7501 4320 0000 1230 880 - ACCOUNT NAME: Alain Dellepiane<br /><br />'
		.'Provided materials remain the copyright of Alain Dellepiane until paid for in full'
);

// ---- DEFAULT Javascript ----------------------------------------------------

$GLOBALS['config']['header']['js'] = array(
	'jquery.1.7.2.js',
	'bootstrap.min.js',
	'app.js'
);


// ---- SKINS and Templates ---------------------------------------------------

$GLOBALS['config']['skin'] = 'default';

$GLOBALS['config']['header']['css'] = array(
	'bootstrap.css',
	'bootstrap.responsive.css',
	'app.css'
);

// ---- LANGUAGE --------------------------------------------------------------

$GLOBALS['config']['lang'] = array(
	'default'		=> 'en',
	'user'			=> 'en',
	'specialchars'	=> 2
);

$GLOBALS['config']['lang']['options'] = array(
		'en'	=> 'English',
		'fr'	=> 'Français (French)',
		'de'	=> 'Deutsch (German)',
		'no'	=> 'Norsk (Norwegian)'
);

$GLOBALS['config']['lang']['files'] = array(
	'common.php'	=> APP_INCLUDE_PATH.'lib/lang/',
	'freak.php'		=> APP_INCLUDE_PATH.'app/lang/'
);

ini_set('session.gc_maxlifetime', 3600);