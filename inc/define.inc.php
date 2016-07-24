<?php

/* nastaveni cest */
//pokud jsem na vyvojovem stroji
$ISDEV = ($_SERVER["SERVER_NAME"] == 'localhost' || $_SERVER["SERVER_NAME"] == 'vodni.skauting.local') ? true : false;
if($ISDEV) {
	//vyvojova masina
	if($_SERVER["SERVER_NAME"] == 'vodni.skauting.local') {
		define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/srazvs/');
		define('HTTP_DIR', 'http://'.$_SERVER['HTTP_HOST'] . '/');
	} else {
		define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/skauting/vodni/srazvs/');
		define('HTTP_DIR', 'http://'.$_SERVER['HTTP_HOST'] . '/skauting/vodni/');
	}
}
//ostra masina
else {
	// na ostrem stroji musi byt vzdy za ROOT_DIR slash "/"
	define('ROOT_DIR', __DIR__ . '/../');
	define('HTTP_DIR', 'http://' . $_SERVER['HTTP_HOST'].'/');
}

/**
 * This is Nix definitions
 *
 * * do not forget to add slash at the end
 */

/* System Directories */
define('PRJ_DIR',		HTTP_DIR.'srazvs/');
define('INC_DIR',		ROOT_DIR.'inc/');
define('IMG_DIR',		PRJ_DIR.'www/images/');
define('CSS_DIR',		PRJ_DIR.'www/css/');
define('JS_DIR',		PRJ_DIR.'www/js/');

/* Temporary Files */
define('TEMP_DIR',		ROOT_DIR.'temp/');
define('LOG_DIR',		TEMP_DIR.'log/');
define('SESSION_DIR',	TEMP_DIR.'session/');
define('CACHE_DIR',		TEMP_DIR.'cache/');

/* Application */
define('APP_DIR',		ROOT_DIR.'app/');
define('MODEL_DIR',		APP_DIR.'models/');
define('VIEW_DIR',		APP_DIR.'views/');
define('CONTROLLER_DIR',APP_DIR.'controllers/');
define('TEMPLATE_DIR',	APP_DIR.'templates/');
define('TPL_DIR',		APP_DIR.'templates/');

/* URLs */
define('BLOCK_DIR',		PRJ_DIR.'block');
define('PROG_DIR',		PRJ_DIR.'program');
define('MEET_DIR',		PRJ_DIR.'meeting');
define('VISIT_DIR',		PRJ_DIR.'visitor');
define('CAT_DIR',		PRJ_DIR.'category');
define('EXP_DIR',		PRJ_DIR.'export');
define('SET_DIR',		PRJ_DIR.'settings');

require_once(INC_DIR.'functions.inc.php');
require_once(INC_DIR.'errors.inc.php');

define('DEBUG', FALSE);

//debuggovani
if(defined('DEBUG') && DEBUG === TRUE){
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
}

$style = "";
