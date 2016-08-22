<?php

function cleardate2DB ($input_datum, $format_datum)
{
			//list($d, $m, $r) = split("[/.-]", $input_datum);
			list($d, $m, $r) = preg_split("[/|\.|-]", $input_datum);
			// beru prvni znak a delam z nej integer
			$rtest = $r{0};
			$rtest += 0;
			$mtest = $m{0};
			$mtest += 0;

			// pokud je to nula, musim odstranit prvni znak
			if(($rtest) == 0) $r = substr($r, 1);
			if(($mtest) == 0) $m = substr($m, 1);

			$d += 0; $m += 0; $r += 0;
			$datum = date("$format_datum",mktime(0,0,0,$m,$d,$r));
			return $datum;
}

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
 * wordGen()
 * - vygeneruje nahodne slovo
 *
 * @author sunlight CMS
 *
 * @param integer $length - delka slova
 * @param integer $numlength - delka cisel
 */
function wordGen($length = 10, $numlength = 3)
{
	if($length > $numlength){
		$wordlength = $length - $numlength;
	}
	else{
		$wordlength = $length;
		$numlength = 0;
	}
	$output = "";

  	//priprava poli
  	$letters1 = array("a", "e", "i", "o", "u", "y");
  	$letters2 = array("b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "q", "r", "s", "t", "v", "w", "x", "z");

  	//textova cast
  	$t = true;
  	for($x = 0; $x < $wordlength; $x++){
    	if($t){
			$r = array_rand($letters2);
			$output .= $letters2[$r];
		}
    	else{
			$r = array_rand($letters1);
			$output .= $letters1[$r];
		}
  	$t =! $t;
	}

  	//ciselna cast
  	if($numlength != 0){
    	$output .= mt_rand(pow(10, $numlength - 1), pow(10, $numlength) - 1);
  	}

	return $output;
}

/*---- vraceni md5 hashe se zvolenym nebo nahodne vygenerovanym saltem  (pri generovani je vysledkem pole s klici 0-hash, 1-salt, 2-vstup) ----*/

/**
 * hashSalt()
 * - generuje md5 hash s zvolenym nebo nadohne vygenerovanym saltem
 * - vraci vysledek v poli (0 -hash, 1 - salt, 2 - vstup)
 *
 * @author sunlight CMS
 *
 * @param string $string - vstupni retezec znaku
 * @param mixed var $hash_key - sifrovaci klic (cisla i pismena)
 * @param mixed var $usesalt - moznost vlozeni vlastni soli
 */
function hashSalt($string, $hash_key, $usesalt = null)
{
	if($usesalt === null){
		$salt = wordGen(8,3);
	}
	else{$salt = $usesalt;}
  	$hash = custom_hmac("md5", $hash_key, $salt.$string.$salt);
  	if($usesalt === null){
		return array($hash, $salt, $string);
	}
	else{return $hash;}
}

/**
 * custom_hmac()
 * - nahrazuje funkco php hash_hmac(), ktera nefunguje na nekterych hostinzich
 *
 * @author php.net
 *
 * @param string $algo - algoritmus hashe (md5, sha1, ...)
 * @param string $data - co se bude hashovat
 * @param string $key - hashovaci klic
 * @param string $raw_output - cisty vystup
 */
function custom_hmac($algo, $data, $key, $raw_output = false)
{
    $algo = strtolower($algo);
    $pack = 'H'.strlen($algo('test'));
    $size = 64;
	$opad = str_repeat(chr(0x5C), $size);
    $ipad = str_repeat(chr(0x36), $size);

    if (strlen($key) > $size){
    	$key = str_pad(pack($pack, $algo($key)), $size, chr(0x00));
    }
	else{
        $key = str_pad($key, $size, chr(0x00));
    }

    for($i = 0; $i < strlen($key) - 1; $i++){
        $opad[$i] = $opad[$i] ^ $key[$i];
        $ipad[$i] = $ipad[$i] ^ $key[$i];
    }

    $output = $algo($opad.pack($pack, $algo($ipad.$data)));

    return ($raw_output) ? pack($pack, $output) : $output;
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




// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
function form_key_hash($id, $meetingId) {
	return ((int)$id . $meetingId) * 116 + 39147;
}
