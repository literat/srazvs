<?php
/**
 * Konfiguracni soubor pro administracni system
 */

/* prihlasovani do databaze */
$cfg['db_user']			= 'vodni';
$cfg['db_passwd']		= 'aY%79%2me4xJsuw#rFrA2DmP';
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
$cfg['title']			= 'Srazy VS | CodePlex::admin';
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
?>