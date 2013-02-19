<?php
/**
 * Konfiguracni soubor pro administracni system
 */

/* prihlasovani do databaze */
$cfg['db_user']			= 'vodni';
$cfg['db_passwd']		= 'g0DqNdouBQ';
$cfg['db_database'] 	= 'vodni';
$cfg['db_host']			= 'localhost';

/* nastaveni kodovani */
$cfg['encoding']		= 'utf8';
$cfg['db-encoding']		= 'utf-8';
$cfg['http-encoding']	= 'utf-8';

/* jine */
$cfg['hash']			= '';

$cfg['timeout']			= '-15 minutes';

$cfg['prefix']			= "sunlight";

$cfg['paper-format']	= "A4";


/* nastaveni hlavicky */
$cfg['title']			= 'Srazy VS | PlexIS::CMS';
$cfg['description']		= 'administrační systém pro Srazy K + K';
$cfg['keywords']		= 'plexis, administrace, cms';
$cfg['author']			= 'LITERA Tomáš(slunda); tomaslitera(zavinac)hotmail(tecka)com';
$cfg['owner']			= 'Litera Tomáš, Kolín';

/* mailing */
$cfg['mail-html-header'] = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>\n
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='cs' lang='cs'>\n
<head>\n
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />\n
</head>\n";

$cfg['mail-language']		= "cz";
$cfg['mail-encoding']		= 'utf-8';
$cfg['mail-sender-address']	= 'srazyvs@hkvs.cz';
$cfg['mail-sender-name']	= 'Srazy VS';

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
?>