<?php

namespace App\Services\Filters;

use Nette\Object;

class Number2WordFilter extends Object
{

	/**
	 * převod desitkového čísla vyjádřeného matematicky na číslo vyjádřené slovně
	 * param int $number - číslo v desítkové soustavě (od 0 do 999999)
	 * param bool $zero - true:když bude $number 0 zobrazí se na výstupu nula; false:když bude $number 0 zobrazí se na výstupu (bool)false
	 * return string nebo bool false
	 * (C) Martin Bumba, http://mbumba.cz
	 */
	public function __invoke($number, $zero = false)
	{
		$units = ['', 'jedna', 'dva', 'tři', 'čtyři', 'pět', 'šest', 'sedm', 'osm', 'devět'];
		$between = [
			11 => 'jedenáct',
			12 => 'dvanáct',
			13 => 'třináct',
			14 => 'čtrnáct',
			15 => 'patnáct',
			16 => 'šestnáct',
			17 => 'sedmnáct',
			18 => 'osmnáct',
			19 => 'devatenáct'
		];
		$dozens = [
			'',
			'deset',
			'dvacet',
			'třicet',
			'čtyřicet',
			'padesát',
			'šedesát',
			'sedmdesát',
			'osmdesát',
			'devadesát'
		];
		$number = (string) ltrim(round($number), 0);
		$length = strlen($number);

		// treatment of 0
		if($number == 0) return $zero ? 'nula' : false;
		// 1 regulation - units
		elseif($length == 1) return $units[$number];
		// 2 regulations - desítky
		elseif($length == 2) {
			$dozensAndUnits = $number{0} . $number{1};
			if($dozensAndUnits == 10) echo 'deset';
			elseif($dozensAndUnits < 20) {
				return $between[$dozensAndUnits];
			}
			else {
				return $dozens[$number{0}] . $units[$number{1}];
			}
		}
		// 3 regulations - hundreds
		elseif($length == 3) {
			if($number{0} == 1) return 'sto' . self::__invoke(substr($number, 1));
			elseif($number{0} == 2) return 'dvěstě' . self::__invoke(substr($number, 1));
			elseif($number{0} == 3 || $number{0} == 4) return $units[$number{0}] . 'sta' . self::__invoke(substr($number, 1));
			else return $units[$number{0}] . 'set' . self::__invoke(substr($number, 1));
		}
		// 4 regulations - thousands
		elseif($length == 4) {
			if($number{0} == 1) return 'tisíc' . self::__invoke(substr($number, 1));
			elseif($number{0} < 5) return $units[$number{0}] . 'tisíce' . self::__invoke(substr($number, 1));
			else return $units[$number{0}] . 'tisíc' . self::__invoke(substr($number, 1));
		}
		// 5 regulations - tens of thousands
		elseif($length == 5) {
			$tensOfThousands = $number{0} . $number{1};
			if($tensOfThousands == 10) return 'desettisíc' . self::__invoke(substr($number, 2));
			elseif($tensOfThousands < 20) return $between[$tensOfThousands] . 'tisíc' . self::__invoke(substr($number, 2));
			elseif($tensOfThousands < 100) return $dozens[$number{0}] . $units[$number{1}] . 'tisíc' . self::__invoke(substr($number, 2));
		}
		// 6 regulations - hundreds of thousands
		elseif($length == 6) {
			if($number{0} == 1) {
				if($number{1} . $number{2} == 00) return 'stotisíc' . self::__invoke(substr($number, 3));
				else return 'sto' . self::__invoke(substr($number, 1));
			}
			elseif($number{0} == 2) return 'dvěstě' . self::__invoke(substr($number, 1));
			elseif($number{0} == 3 || $number{0} == 4) return $units[$number{0}] . 'sta' . self::__invoke(substr($number, 1));
			else return $units[$number{0}] . 'set' . self::__invoke(substr($number, 1));
		}

		return false;
	}

}
