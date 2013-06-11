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
function formatDateFromDB ($input_date, $date_format)
{
	if($input_date == "") $output_date = "";
	else {
		$input_date = str_replace(" ","",$input_date);
		//list ($r, $m, $d) = split('[/.-]', $input_date);
		list($d, $m, $r) = preg_split("[/|\.|-]", $input_date);
		$d += 0; $m += 0; $r += 0;
		$output_date = date("$date_format",mktime(0,0,0,$m,$d,$r));
	}
	return $output_date;
};

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
function formatTimeDB ($input_cas, $format_cas)
			{
			//list ($h, $m, $s) = split('[:]', $input_cas);
			list($d, $m, $r) = preg_split("[:]", $input_cas);
			$h += 0; $m += 0; $s += 0;
			$cas = date("$format_cas",mktime($h,$m,0,0,0,0));
			return $cas;
			};

/**
 * formatDateTimeFromDB()
 * - naformatuje datetime vystup z databaze podle daneho formatu
 *
 * @author tomas.litera@gmail.com
 *
 * @param datetime $input_datetime - datum a cas z databaze
 * @param string $format_datetime - PHP format data a casu
 * @return datetime $output_datetime - vraci zformatovany datum a cas
 */			
function formatDateTimeFromDB($input_datetime, $format_datetime)
			{
			//mezera je oddelovac mezi datem a casem
			$input_datetime = str_replace(" ","/",$input_datetime);
			//rozsekam do promennych
			//list ($r, $m, $d, $hod, $min, $sec) = split('[/:-]', $input_datetime);
			list($r, $m, $d, $hod, $min, $sec) = preg_split("[/|:|-]", $input_datetime);
			//zarucim, ze promenne budou opravdu cisla
			$d += 0; $m += 0; $r += 0; $hod += 0; $min += 0; $sec += 0;
			//naformatuju
			$output_datetime = date("$format_datetime",mktime($hod,$min,$sec,$m,$d,$r));
			return $output_datetime;
			}

/**
 * formatDateTime2DB()
 * - naformatuje datetime vstup do databaze podle zadaneho formatu
 *
 * @author tomas.litera@gmail.com
 *
 * @param datetime $input_datetime - datum a cas z databaze
 * @param string $format_datetime - PHP format data a casu
 * @return datetime $output_datetime - vraci zformatovany datum a cas
 */			
function formatDateTime2DB($date, $hour, $minute, $format)
			{
			//list($d, $m, $r) = split("[/.-]", $date);
			list($d, $m, $r) = preg_split("[/|\.|-]", $date);
			
			// beru prvni znak a delam z nej integer
			$rtest = $r{0};
			$rtest += 0;
			$mtest = $m{0};
			$mtest += 0;

			// pokud je to nula, musim odstranit prvni znak
			if(($rtest) == 0) $r = substr($r, 1);
			if(($mtest) == 0) $m = substr($m, 1);
			
			$d += 0; $m += 0; $r += 0;
				
				
			//mezera je oddelovac mezi datem a casem
			//$input_datet = str_replace(" ","/",$input_date);
			//rozsekam do promennych
			//list ($r, $m, $d, $hod, $min, $sec) = split('[/:-]', $input_datetime);
			//zarucim, ze promenne budou opravdu cisla
			$d += 0; $m += 0; $r += 0; $hour += 0; $minute += 0; $sec = 0;
			//naformatuju
			$output_datetime = date("$format",mktime($hour,$minute,$sec,$m,$d,$r));
			return $output_datetime;
			}

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
	
	

function getAge($rodne_cislo)
{
	//dnesni datum - rok, mesic, den
	$today_year = date("Y");
	$today_month = date("n");
	$today_day = date("j");
	//datum narozeni - rok, mesic, den	
	$year = number_format($rodne_cislo/100000000) + 1900;
	$month = substr($rodne_cislo,2,2);
	$day = substr($rodne_cislo,4,2);
	if($month > 50) {$month = $month - 50;}
	//ziskam hruby vek
	$age = $today_year - $year;
	//pokud je mensi mesic, porad je o rok mladsi
	if($today_month < $month) {$age = $age - 1;}
	//kdyz uz nastane stejny mesic
	if($today_month == $month)
		{
		//tak jeste nemusi byt stejny den
		if(today_day < $day) {$age = $age - 1;}
		}
	return $age;
}
	
function isRC($rc)
{
    // "be liberal in what you receive"
    if (!preg_match('#^\s*(\d\d)(\d\d)(\d\d)[ /]*(\d\d\d)(\d?)\s*$#', $rc, $matches)) return FALSE;
    list(, $year, $month, $day, $ext, $c) = $matches;
    // do roku 1954 přidělovaná devítimístná RČ nelze ověřit
    if ($c === '') return $year < 54;
    // kontrolní číslice
    $mod = ($year . $month . $day . $ext) % 11;
    if ($mod === 10) $mod = 0;
    if ($mod !== (int) $c) return FALSE;

    // kontrola data
    $year += $year < 54 ? 2000 : 1900;

    // k měsíci může být připočteno 20, 50 nebo 70
    if ($month > 70 && $year > 2003) $month -= 70;
    elseif ($month > 50) $month -= 50;
    elseif ($month > 20 && $year > 2003) $month -= 20;
    if (!checkdate($month, $day, $year)) return FALSE;
    // cislo je OK
    return TRUE;
}
	
function getDateOfBirth($rodne_cislo)
{
	//matematicke operace jsou prece jenom rychlejsi
	$year = number_format($rodne_cislo/100000000) + 1900;
	$month = substr($rodne_cislo,2,2);
	if($month > 50 and $month < 63) {$month = $month - 50;}
	//else echo $error['rodne_cislo'];
	//else $error_item = "rodne_cislo";
	$day = substr($rodne_cislo,4,2);
	$date_of_birth = date("j. n. Y", mktime (0,0,0,$month,$day,$year));
	return $date_of_birth;
}

function getSex($rodne_cislo)
{
	$month = substr($rodne_cislo,2,2);
	if($month > 50) {$sex = "žena";}
	else $sex = "muž";
	return $sex;
}
	
//---Check Czech phone number optional interneational preposition 
//---+420 and gaps betweeen trinity of numbers
//---Kontroluje Ceske teefonni cislo, nepovinny mezinarodni predpona
//--- +420 a nepovinne mezery mezi trojicemi cisel
function isPhoneNumber($phone)
	{
  	$RegExp = "/^(\+420)? ?\d{3} ?\d{3} ?\d{3}$/";
  	return preg_match($RegExp,$phone);
	}


//---Check Czech format of Zip Code optional gap between third and
//---fourth number
//---Kontroluje ceske smerovaci cislo, nepovinna mezera mezi treti a
//---ctvrtou cislici
function isZipCode($zip_code)
{
  	$RegExp = "/^\d{3} ?\d{2}$/";
  	return preg_match($RegExp,$zip_code);
}

/** Kontrola e-mailové adresy
* @param string $email e-mailová adresa
* @return bool syntaktická správnost adresy
*/
function isEmail($email)
{
    $atom = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]'; // znaky tvořící uživatelské jméno
    $domain = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])'; // jedna komponenta domény
    return eregi("^$atom+(\\.$atom+)*@($domain?\\.)+$domain\$", $email);
}

/**
 * isGroupNumber()
 * - zjisti, jestli se jedna o cislo jednotky
 *
 * @author tomaslitera@hotmail.com
 *
 * @param string $number - vstupni retezec
 * @return bool - pri shode vraci True (1)
 */
function isGroupNumber($number)
{
	// hledam retezec, kteru zacina tremi po sobe jdoucimi cislicemi
	// pak nasleduje tecka
	// a dale jsou dvě po sobě jdouci cislice
  	$RegExp = "/^[0-9]{3}\.[0-9]{2}$/";
  	return preg_match($RegExp,$number);
}

/**
 *
 *
 *
 */
function isEmpty($value)
{
	if($value == "") return true;
	else return false;
}

$day_name = array(1 => "", "pondělí", "úterý", "středa", "čtvrtek", "pátek", "sobota", "neděle");
$month_name = array(1 => "leden", "únor", "březen", "duben", "květen", "červen", "červenec", "srpen", "září", "říjen", "listopad", "prosinec");

/**
 * getCategoryStyle()
 * - funkce vytvori styly pro vlozeni kategorii
 * 
 * @return string <style>...</style>
 */
function getCategoryStyle()
{
	$style = "<style type='text/css'>\n";

	$cat_sql = "SELECT * FROM kk_categories WHERE 1";
	$cat_result = mysql_query($cat_sql);
	while($cat_data = mysql_fetch_assoc($cat_result)){
		$style .= ".cat-".$cat_data['style']." {
		border:2px solid #".$cat_data['bocolor'].";
		background-color:#".$cat_data['bgcolor'].";
		color:#".$cat_data['focolor'].";
		padding:0px;
		min-width:125px;
		}\n";
	}
	$style .= "</style>\n";
	
	return $style;
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

function removeDiacritic($string){
	$diakritika = array("č","ď","ě","ň","ř","š","ť","ž","á","é","í","ó","ú","ů","ý","Č","Ď","Ň","Ř","Š","Ť","Ž","Á","É","Í","Ó","Ú");
	$nodiakritika = array("c","d","e","n","r","s","t","z","a","e","i","o","u","u","y","C","D","N","R","S","T","Z","A","E","I","O","U");
	$string = str_replace($diakritika, $nodiakritika, $string);
	return $string;
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
function getUser($uid, $index){
	$sql = "SELECT *
			FROM `sunlight-users` AS usr
			WHERE id = '".$uid."' 
			LIMIT 0,1";
	$result = mysql_query($sql);
	$user = mysql_fetch_assoc($result);	
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

function backtrace_debug() {
//* * * * start of backtrace debug code * * *
	$dbt = debug_backtrace();
	echo "<div><br>= = = = = = = = Backtrace = = = = = = = =<br>\n";
	for($d_b_t = 0 ; $d_b_t < count($dbt) ; $d_b_t++) {
        if($d_b_t == 0) {
          echo basename( __FILE__ ) . ' is referenced in ';
        } else {
          echo $dbt[$d_b_t - 1]['file'] . ' is referenced in ';
        }
        if(isset($dbt[$d_b_t]['file'])) {
          echo $dbt[$d_b_t]['file'] . ' on line ';
        }
        if (isset($dbt[$d_b_t]['line'])) {
          echo $dbt[$d_b_t]['line'] . ' in a "';
        }
        if(isset($dbt[$d_b_t]['function'])) {
          echo $dbt[$d_b_t]['function'] . '"<br>' . "\n";
        }
	}
	echo "<br>= = = = = = = = = = = = = = = = = = = = =<br>\n</div>";
	//* * * * end of backtrace debug code * * *
}