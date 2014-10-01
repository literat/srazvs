<?php
/**
 * Konfiguracni soubor pro administracni system
 */

/* prihlasovani do databaze */
$cfg['db_user']			= 'dbuser';
$cfg['db_passwd']		= 'dbpassword';
$cfg['db_database'] 	= 'database';
$cfg['db_host']			= 'localhost';

/* nastaveni kodovani */
$cfg['encoding']		= 'utf8';
$cfg['db-encoding']		= 'utf-8';
$cfg['http-encoding']	= 'utf-8';

/* jine */
$cfg['hash']			= '';

$cfg['timeout']			= '-15 minutes';

$cfg['prefix']			= "prefix";

$cfg['paper-format']	= "A4";


/* nastaveni hlavicky */
$cfg['title']			= 'some title';
$cfg['description']		= 'some description';
$cfg['keywords']		= 'some keywords';
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
$cfg['mail-sender-address']	= 'mail@example';
$cfg['mail-sender-name']	= 'some name';

$cfg['gmail_user'] = "example@gmail.com";
$cfg['gmail_passwd'] = "password";
?>