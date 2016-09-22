<?php

/**
 * shortenText()
 * - zkrati text pro zobrazeni na pozadovanou delku a prida tri tecky
 *
 * @author tomas.litera@gmail.com
 *
 * @param string $text - zkracovany text
 * @param integer $limit - pocet znaku, ktere se zobrazi
 * @param string $delimiter - za jakym znakem se bude delit
 */
function shortenText($text, $limit, $delimiter)
{
			if (strlen($text) <= $limit){
    		$out_text = $text;
		}
		else {
    		$text = substr($text, 0, $limit);
    		$pos = strrpos($text, $delimiter); // v PHP 5 by se dal použít parametr offset
    		$out_text = substr($text, 0, ($pos ? $pos : -1)) . "...";
		}

		return $out_text;
}

/**
 * requested()
 * - ziska promenne z GET a POST
 *
 * @author tomasliterahotmail.com
 *
 * @param string $var - nazev pole GET nebo POST
 * @param $default - defaultni hodnota v pripade neexistence GET nebo POST
 */
function requested($var, $default = NULL)
{
	//if(isset($_SESSION['data'][$var])){
	//	$out = $_SESSION['data'][$var];
	//	session_unset();
	//}
	//else {
		if(isset($_GET[$var])) $out = clearString($_GET[$var]);
		elseif(isset($_POST[$var])) $out = clearString($_POST[$var]);
		else $out = $default;
		//$_SESSION['data'][$var] = $out;
	//}

	return $out;
}

/**
 *
 *
 */
function getUser($uid, $index, $database)
{
	$user = $database->table('sunlight-users')->where('id', $uid)->fetch();

	return $user[$index];
}
