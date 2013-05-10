<?php

//require a include
require_once('config.inc.php');

/* nastaveni cest */
//pokud jsem na vyvojovem stroji
$ISDEV = ($_SERVER["SERVER_NAME"] == 'localhost')?true:false; 
if($ISDEV){
	//vyvojova masina
	if($_SERVER["SERVER_NAME"] == 'vodni.poutnicikolin.cz'){
		//define('ROOT_DIR',"../");
  	  	//define('ROOT_DIR', "/home/www/poutnicikolin.cz/subdomains/dev/admin/");
		define('HTTP_DIR','http://'.$_SERVER['HTTP_HOST']."/vodni/srazvs/");
		//echo ROOT_DIR;
  	}
	else {
		define('ROOT_DIR',$_SERVER['DOCUMENT_ROOT'].'/skauting/vodni/srazvs/');
		define('HTTP_DIR','http://'.$_SERVER['HTTP_HOST'].'/skauting/vodni/');
	}
} 
//ostra masina
else {
	// na ostrem stroji musi byt vzdy za ROOT_DIR slash "/"
	define('ROOT_DIR', '/var/www/virtual/vodni/web/www/srazvs/');
	//define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT'].'/srazvs/');
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
define('PROJECT',		'srazvs');
/* System Directories */
define('INC_DIR',		ROOT_DIR.'inc/');
define('IMG_DIR',		HTTP_DIR.PROJECT.'/images/');
define('CSS_DIR',		HTTP_DIR.PROJECT.'/css/');
define('JS_DIR',		HTTP_DIR.PROJECT.'/js/');
define('LIBS_DIR',		ROOT_DIR.'libs/');
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
/* URLs */
define('BLOCK_DIR',		HTTP_DIR.PROJECT.'/blocks/');
define('PROG_DIR',		HTTP_DIR.PROJECT.'/programs/');
define('MEET_DIR',		HTTP_DIR.PROJECT.'/meetings/');
define('VISIT_DIR',		HTTP_DIR.PROJECT.'/visitors/');
define('CAT_DIR',		HTTP_DIR.PROJECT.'/categories/');
define('EXP_DIR',		HTTP_DIR.PROJECT.'/exports/');
define('SET_DIR',		HTTP_DIR.PROJECT.'/settings/');

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
require_once(LIBS_DIR.'Codeplex/ComponentModel/IModel.php');
require_once(LIBS_DIR.'Codeplex/ComponentModel/IComponent.php');
require_once(LIBS_DIR.'Codeplex/ComponentModel/Component.php');
require_once(LIBS_DIR.'Codeplex/ComponentModel/CodeplexModel.php');

require_once(LIBS_DIR.'PHPMailer/class.phpmailer.php');
require_once(LIBS_DIR.'Mpdf/mpdf.php');
require_once(LIBS_DIR.'PHPExcel/Classes/PHPExcel.php');

require_once(LIBS_DIR.'Codeplex/DI/Container.class.php');
require_once(LIBS_DIR.'Codeplex/Mail/PHPMailerFactory.php');
require_once(LIBS_DIR.'Codeplex/Exporting/PdfFactory.php');
require_once(LIBS_DIR.'Codeplex/Exporting/ExcelFactory.php');

/* Application */
require_once(LIBS_DIR.'Codeplex/Mail/Emailer.php');
require_once(MODEL_DIR.'CategoryModel.php');
require_once(MODEL_DIR.'BlockModel.php');
require_once(MODEL_DIR.'ProgramModel.php');
require_once(LIBS_DIR.'Codeplex/Forms/Form.php');
require_once(MODEL_DIR.'VisitorModel.php');
require_once(MODEL_DIR.'MeetingModel.php');
require_once(MODEL_DIR.'MealModel.php');
require_once(MODEL_DIR.'ExportModel.php');

require_once(VIEW_DIR.'View.php');

define('DEBUG', TRUE);


//debuggovani
if(defined('DEBUG') && DEBUG === TRUE){
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
}

$style = "";