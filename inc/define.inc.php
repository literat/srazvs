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

/* systemove slozky */
/* do not forget to add slash at the end*/
$INCDIR 	= ROOT_DIR.'inc/';

$LAYOUTDIR 	= HTTP_DIR.'srazvs/styles/layout/';
$IMGDIR 	= HTTP_DIR.'srazvs/img/';
$ICODIR 	= $LAYOUTDIR.'icons/';
$LOGODIR 	= $LAYOUTDIR.'logos/';
$LOGDIR 	= ROOT_DIR.'log/';
/* depracated */
$STYLEDIR 	= HTTP_DIR.'srazvs/styles/';
/* depracated */
$CSSDIR 	= HTTP_DIR.'srazvs/styles/css/';
$CSS2DIR 	= HTTP_DIR.'srazvs/css/';

/* depracated */
$AJAXDIR 	= HTTP_DIR.'srazvs/js/';
$JSDIR 		= HTTP_DIR.'srazvs/js/';
$CLASSDIR 	= $INCDIR.'class/';
$LIBSDIR 	= ROOT_DIR.'libs/';
$TPL_DIR 	= ROOT_DIR.'templates/';
$TMPDIR 	= ROOT_DIR.'tmp/';

$BLOCKDIR	= HTTP_DIR.'srazvs/blocks/';
$PROGDIR 	= HTTP_DIR.'srazvs/programs/';
$MEETDIR 	= HTTP_DIR.'srazvs/meetings/';
$VISITDIR 	= HTTP_DIR.'srazvs/visitors/';
$CATDIR 	= HTTP_DIR.'srazvs/categories/';
$EXPDIR 	= HTTP_DIR.'srazvs/exports/';
$SETDIR 	= HTTP_DIR.'srazvs/settings/';

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
define('MODEL_DIR',		ROOT_DIR.'app/Models/');
define('VIEW_DIR',		ROOT_DIR.'app/Views/');
define('CONTROLLER_DIR',ROOT_DIR.'app/Controllers/');
define('TEMPLATE_DIR',	ROOT_DIR.'app/Templates/');


define('SESSION_PREFIX', md5($cfg['db_host'].$cfg['db_database'].$cfg['db_user'].$cfg['prefix'])."-");

//nastartovani session
session_name(SESSION_PREFIX.'session');
//session_save_path(SESSION_DIR);
session_start();

require_once($INCDIR.'functions.inc.php');
require_once($INCDIR.'db_connect.inc.php');
//include_once($INCDIR.'access.inc.php');
require_once($INCDIR.'errors.inc.php');

require_once($CLASSDIR.'ComponentModel/IModel.php');
require_once($CLASSDIR.'ComponentModel/IComponent.php');
require_once($CLASSDIR.'ComponentModel/Component.php');

require_once($CLASSDIR.'Emailer.class.php');
require_once($CLASSDIR.'Category.class.php');
require_once($CLASSDIR.'Block.class.php');
require_once($CLASSDIR.'Program.class.php');
require_once($CLASSDIR.'Form.class.php');
require_once($CLASSDIR.'Visitor.class.php');
require_once($CLASSDIR.'Meeting.class.php');
require_once($CLASSDIR.'Meal.class.php');
require_once($CLASSDIR.'View.class.php');
require_once($CLASSDIR.'ExportModel.class.php');

require_once($LIBSDIR.'PHPMailer/class.phpmailer.php');
require_once($LIBSDIR.'Mpdf/mpdf.php');

require_once($CLASSDIR.'DI/Container.class.php');
require_once($CLASSDIR.'Mail/PHPMailerFactory.php');
require_once($CLASSDIR.'Exporting/PdfFactory.php');

define('DEBUG', TRUE);


//debuggovani
if(defined('DEBUG') && DEBUG === TRUE){
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
}

$style = "";

?>