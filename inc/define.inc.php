<?php

//require a include
require_once('config.inc.php');

/* nastaveni cest */
//pokud jsem na vyvojovem stroji
$ISDEV = ($_SERVER["SERVER_NAME"] == 'localhost' || $_SERVER["SERVER_NAME"] == 'vodni.skauting.local') ? true : false; 
if($ISDEV) {
	//vyvojova masina
	if($_SERVER["SERVER_NAME"] == 'vodni.poutnicikolin.cz') {
		//define('ROOT_DIR',"../");
		//define('ROOT_DIR', "/home/www/poutnicikolin.cz/subdomains/dev/admin/");
		define('HTTP_DIR','http://'.$_SERVER['HTTP_HOST']."/vodni/srazvs/");
		//echo ROOT_DIR;
	} elseif($_SERVER["SERVER_NAME"] == 'vodni.skauting.local') {
		define('ROOT_DIR',$_SERVER['DOCUMENT_ROOT'].'/srazvs/');
		define('HTTP_DIR','http://'.$_SERVER['HTTP_HOST'].'/');
	} else {
		define('ROOT_DIR',$_SERVER['DOCUMENT_ROOT'].'/skauting/vodni/srazvs/');
		define('HTTP_DIR','http://'.$_SERVER['HTTP_HOST'].'/skauting/vodni/');
	}
} 
//ostra masina
else {
	// na ostrem stroji musi byt vzdy za ROOT_DIR slash "/"
	define('ROOT_DIR', '/var/www/virtual/vodni/web/www/srazvs/');
	define('HTTP_DIR', 'http://'.$_SERVER['HTTP_HOST'].'/');
}

//echo $_SERVER['DOCUMENT_ROOT']."<BR />";
//echo $_SERVER['HTTP_HOST']."<br />";
//echo ROOT_DIR."<br />";
//echo HTTP_DIR."<br />";

//echo $_SERVER['HTTP_REFERER'];

/**
 * This is Codeplex definitions
 *
 * * do not forget to add slash at the end
 */

/* System Directories */
define('PRJ_DIR',		HTTP_DIR.'srazvs/');
define('INC_DIR',		ROOT_DIR.'inc/');
define('IMG_DIR',		PRJ_DIR.'images/');
define('CSS_DIR',		PRJ_DIR.'css/');
define('JS_DIR',		PRJ_DIR.'js/');
define('LIBS_DIR',		ROOT_DIR.'libs/');

/* Libraries */
define('FRAMEWORK',		LIBS_DIR.'Nix/');

/* Temporary Files */
define('TEMP_DIR',		ROOT_DIR.'temp/');
define('LOG_DIR',		TEMP_DIR.'log/');
define('SESSION_DIR',	TEMP_DIR.'session/');
define('CACHE_DIR',		TEMP_DIR.'cache/');

/* Application */
define('APP_DIR',		ROOT_DIR.'app/');
define('MODEL_DIR',		APP_DIR.'Models/');
define('VIEW_DIR',		APP_DIR.'Views/');
define('CONTROLLER_DIR',APP_DIR.'Controllers/');
define('TEMPLATE_DIR',	APP_DIR.'Templates/');
define('TPL_DIR',		APP_DIR.'Templates/');

/* URLs */
define('BLOCK_DIR',		PRJ_DIR.'block');
define('PROG_DIR',		PRJ_DIR.'program');
define('MEET_DIR',		PRJ_DIR.'meeting');
define('VISIT_DIR',		PRJ_DIR.'visitor');
define('CAT_DIR',		PRJ_DIR.'category');
define('EXP_DIR',		PRJ_DIR.'export');
define('SET_DIR',		PRJ_DIR.'settings');

define('SESSION_PREFIX', md5($cfg['db_host'].$cfg['db_database'].$cfg['db_user'].$cfg['prefix'])."-");

//nastartovani session
session_name(SESSION_PREFIX.'session');
//session_save_path(SESSION_DIR);
session_start();

require_once(INC_DIR.'functions.inc.php');
require_once(INC_DIR.'db_connect.inc.php');
//include_once($INCDIR.'access.inc.php');
require_once(INC_DIR.'errors.inc.php');

/* Libraries */	
require_once(FRAMEWORK.'ComponentModel/IModel.php');
require_once(FRAMEWORK.'ComponentModel/IComponent.php');
require_once(FRAMEWORK.'ComponentModel/Component.php');
require_once(FRAMEWORK.'ComponentModel/NixModel.php');

require_once(LIBS_DIR.'PHPMailer/class.phpmailer.php');
require_once(LIBS_DIR.'PHPMailer/class.smtp.php');
require_once(LIBS_DIR.'Mpdf/mpdf.php');
require_once(LIBS_DIR.'PHPExcel/Classes/PHPExcel.php');

require_once(FRAMEWORK.'DI/Container.class.php');
require_once(FRAMEWORK.'Mail/PHPMailerFactory.php');
require_once(FRAMEWORK.'Exporting/PdfFactory.php');
require_once(FRAMEWORK.'Exporting/ExcelFactory.php');

require_once(FRAMEWORK.'Http/Http.php');
require_once(FRAMEWORK.'Http/Request.php');
require_once(FRAMEWORK.'Http/Response.php');

require_once(FRAMEWORK.'Utils/Tools.php');

/* Application */
require_once(FRAMEWORK.'Mail/Emailer.php');
require_once(MODEL_DIR.'CategoryModel.php');
require_once(MODEL_DIR.'BlockModel.php');
require_once(MODEL_DIR.'ProgramModel.php');
require_once(FRAMEWORK.'Forms/Form.php');
require_once(MODEL_DIR.'VisitorModel.php');
require_once(MODEL_DIR.'MeetingModel.php');
require_once(MODEL_DIR.'MealModel.php');
require_once(MODEL_DIR.'ExportModel.php');
require_once(MODEL_DIR.'SettingsModel.php');

require_once(VIEW_DIR.'View.php');

require_once(CONTROLLER_DIR.'BaseController.php');

define('DEBUG', FALSE);


//debuggovani
if(defined('DEBUG') && DEBUG === TRUE){
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
}

$style = "";