<?php
/**
 * funkce pro pripojeni databaze
 *
 * @author tomas.litera@gmail.com
 *
 * @param string $host - server
 * @param string $user - uzivatel databaze
 * @param string $password - heslo do databaze
 * @param string $db - databaze
 * @param string $encoding - kodovani pouzite pro spojeni
 * @return int $connected - vraci 0 (false) nebo 1 (true)
 */

function connectMySQL($host, $user, $password, $db, $encoding) {
	//pripojeni
	$result = mysql_connect ($host, $user, $password);
	$result = mysql_select_db ($db);
	//chybovy stav
	//pripojeno
	if($result == true) $connected = true;
		//nepripojeno - chyba
	else {
		$connected = false;
		die;
	}
	//nastaveni kodovani
	mysql_query("SET NAMES '".$encoding."'");
	mysql_query("SET CHARACTER SET '".$encoding."'");
	mysql_query("set session character_set_result = '$encoding'");
	mysql_query("set session character_set_client = '$encoding'");
	mysql_query("set session character_set_connection = '$encoding'");
	//navratova hodnota
	return $connected;
};

$error = connectMySQL($cfg['db_host'], $cfg['db_user'], $cfg['db_passwd'], $cfg['db_database'], $cfg['encoding']);
//echo $error;
?>