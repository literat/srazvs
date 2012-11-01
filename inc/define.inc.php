<?php
//debuggovani
error_reporting(E_ALL);
ini_set('display_errors', '1');

//require a include
require_once('config.inc.php');

define('SESSION_PREFIX', md5($cfg['db_host'].$cfg['db_database'].$cfg['db_user'].$cfg['prefix'])."-");

//nastartovani session
session_name(SESSION_PREFIX.'session');
//session_save_path($TMPDIR);
session_start();

require_once($INCDIR.'functions.inc.php');
require_once($INCDIR.'db_connect.inc.php');
//include_once($INCDIR.'access.inc.php');
require_once($INCDIR.'errors.inc.php');

require_once($CLASSDIR.'Emailer.class.php');
require_once($CLASSDIR.'Category.class.php');
require_once($CLASSDIR.'Blocks.class.php');
require_once($CLASSDIR.'Program.class.php');
require_once($CLASSDIR.'Form.class.php');

require_once($LIBSDIR.'PHPMailer/class.phpmailer.php');

require_once($CLASSDIR.'DI/Container.class.php');
require_once($CLASSDIR.'Mail/PHPMailerFactory.php');

$style = "";

?>