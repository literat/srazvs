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
 * clearString()
 * - ocisti retezec od html, backslashu a specialnich znaku
 *
 * @author tomas.litera@gmail.com
 *
 * @param string $string - retezec znaku
 * @return string $string - ocisteny retezec
 */
function clearString($string)
{
	//specialni znaky
	$string = htmlspecialchars($string);
	//html tagy
	$string = strip_tags($string);
	//slashes
	$string = stripslashes($string);
	return $string;
}

/**
 * redirect()
 * - presmeruje stranku na zadane misto
 *
 * @author tomas.litera@gmail.com
 *
 * @param string $location - misto presmerovani
 */
function redirect($location)
{
	header("Location: $location");
	//header("Connection: close");

	/*echo "<script type='javascript'>
	   	   window.location='".$location."';
	</script>";*/
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


  /*
    * převod desitkového čísla vyjádřeného matematicky na číslo vyjádřené slovně
    * param int $number - číslo v desítkové soustavě (od 0 do 999999)
    * param bool $zero - true:když bude $number 0 zobrazí se na výstupu nula; false:když bude $number 0 zobrazí se na výstupu (bool)false
    * return string nebo bool false
    * (C) Martin Bumba, http://mbumba.cz
  */
function number2word($number, $zero = false)
{
    $jednotky = array("", "jedna","dva","tři","čtyři","pět","šest","sedm","osm","devět");
    $mezi = array(11=>"jedenáct",12=>"dvanáct",13=>"třináct",14=>"čtrnáct",15=>"patnáct",16=>"šestnáct",17=>"sedmnáct",18=>"osmnáct",19=>"devatenáct");
    $desitky = array("", "deset","dvacet","třicet","čtyřicet","padesát","šedesát","sedmdesát","osmdesát","devadesát");
    $number = (string) ltrim(round($number), 0);
    $delka = strlen($number);

    if($number==0)  			return $zero ? "nula":false;             //ošetření 0
    elseif($delka==1)        	return $jednotky[$number];  //1 řád - jednotky
    elseif($delka==2) {                                 //2 řády - desítky
      $desitkyAJednotky = $number{0}.$number{1};
      if($desitkyAJednotky==10) echo "deset";
      elseif($desitkyAJednotky<20) {
        return $mezi[$desitkyAJednotky];
      }
      else {
        return $desitky[$number{0}].$jednotky[$number{1}];
      }
    }
    elseif($delka==3) {                                 //3 řády - stovky
      if($number{0}==1)     return "sto".number2word(substr($number,1));
      elseif($number{0}==2) return "dvěstě".number2word(substr($number,1));
      elseif($number{0}==3 OR $number{0}==4) return $jednotky[$number{0}]."sta".number2word(substr($number,1));
      else                 return $jednotky[$number{0}]."set".number2word(substr($number,1));
    }
    elseif($delka==4) {                                //4 řády - tisíce
      if($number{0}==1) return "tisíc".number2word(substr($number,1));
      elseif($number{0}<5) return $jednotky[$number{0}]."tisíce".number2word(substr($number,1));
      else             return $jednotky[$number{0}]."tisíc".number2word(substr($number,1));
    }
    elseif($delka==5) {                                //5 řádů - desítky tisíc
      $desitkyTisic = $number{0}.$number{1};
      if($desitkyTisic==10)      return "desettisíc".number2word(substr($number,2));
      elseif($desitkyTisic<20)   return $mezi[$desitkyTisic]."tisíc".number2word(substr($number,2));
      elseif($desitkyTisic<100)  return $desitky[$number{0}].$jednotky[$number{1}]."tisíc".number2word(substr($number,2));
    }
    elseif($delka==6) {                                //6 řádů - stovky tisíc
		if($number{0}==1) {
    		if($number{1}.$number{2}==00) 	    return "stotisíc".cislo_na_slovo(substr($number,3));
    		else						   	    return "sto".cislo_na_slovo(substr($number,1));
		}
		elseif($number{0}==2)					return "dvěstě".number2word(substr($number,1));
		elseif($number{0}==3 OR $number{0}==4)	return $jednotky[$number{0}]."sta".number2word(substr($number,1));
		else						  			return $jednotky[$number{0}]."set".number2word(substr($number,1));
	}

	return false;
}

// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
function form_key_hash($id, $meetingId) {
	return ((int)$id . $meetingId) * 116 + 39147;
}

function parse_tutor_email($item) {
	$mails = explode(',', $item->email);
	$names = explode(',', $item->tutor);

	$i = 0;
	foreach ($mails as $mail) {
		$mail = trim($mail);
		$name = trim($names[$i]);

		$recipient[$mail] = ($name) ? $name : '';
	}

	return $recipient;
}

/**
 * Render check box
 *
 * @param	string	name
 * @param	mixed	value
 * @param	var		variable that match with value
 * @return	string	html of chceck box
 */
function renderHtmlCheckBox($name, $value, $checked_variable)
{
	if($checked_variable == $value) {
		$checked = 'checked';
	} else {
		$checked = '';
	}
	$html_checkbox = "<input name='".$name."' type='checkbox' value='".$value."' ".$checked." />";

	return $html_checkbox;
}

/**
 * Render select box
 *
 * @param	string	name
 * @param	array	content of slect box
 * @param	var		variable that match selected option
 * @param	string	inline styling
 * @return	string	html of select box
 */
function renderHtmlSelectBox($name, $select_content, $selected_option, $inline_style = NULL)
{
	if(isset($inline_style) && $inline_style != NULL){
		$style = " style='".$inline_style."'";
	} else {
		$style = "";
	}
	$html_select = "<select name='".$name."'".$style.">";
	foreach ($select_content as $key => $value) {
		if($key == $selected_option) {
			$selected = 'selected';
		} else {
			$selected = '';
		}
		$html_select .= "<option value='".$key."' ".$selected.">".$value."</option>";
	}
	$html_select .= '</select>';

	return $html_select;
}
