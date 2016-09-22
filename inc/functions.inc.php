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
