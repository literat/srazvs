<?php

/**
 * Tools
 *
 * @author      Tomas Litera    <tomaslitera@hotmail.com>
 * @version     2014-02-19
 * @package     Nix
 * @subpackage  Utils
 */
class Tools
{
	/**
	 * Camelizes string
	 *
	 * @param string $string
	 * @return string
	 */
	public static function camelize($string)
	{
		return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
	}

	/**
	 * Dashs string
	 *
	 * @param string $string
	 * @return string
	 */
	public static function dash($string)
	{
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '-\\1', $string));
	}

	/**
	 * Underscores string
	 *
	 * @param string $string
	 * @return string
	 */
	public static function underscore($string)
	{
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $string));
	}

	/**
	 * Strips diacritics
	 *
	 * @param string $string
	 * @return string
	 */
	public static function toAscii($string)
	{
		if(defined('ICONV_IMPL') && ICONV_IMPL != 'libiconv') {

			/**
			 * @author David GRUDL
			 * @link http://davidgrudl.cz
			 */
			static $table = array(
				"\xc3\xa1"=>"a","\xc3\xa4"=>"a","\xc4\x8d"=>"c","\xc4\x8f"=>"d","\xc3\xa9"=>"e",
				"\xc4\x9b"=>"e","\xc3\xad"=>"i","\xc4\xbe"=>"l","\xc4\xba"=>"l","\xc5\x88"=>"n",
				"\xc3\xb3"=>"o","\xc3\xb6"=>"o","\xc5\x91"=>"o","\xc3\xb4"=>"o","\xc5\x99"=>"r",
				"\xc5\x95"=>"r","\xc5\xa1"=>"s","\xc5\xa5"=>"t","\xc3\xba"=>"u","\xc5\xaf"=>"u",
				"\xc3\xbc"=>"u","\xc5\xb1"=>"u","\xc3\xbd"=>"y","\xc5\xbe"=>"z","\xc3\x81"=>"A",
				"\xc3\x84"=>"A","\xc4\x8c"=>"C","\xc4\x8e"=>"D","\xc3\x89"=>"E","\xc4\x9a"=>"E",
				"\xc3\x8d"=>"I","\xc4\xbd"=>"L","\xc4\xb9"=>"L","\xc5\x87"=>"N","\xc3\x93"=>"O",
				"\xc3\x96"=>"O","\xc5\x90"=>"O","\xc3\x94"=>"O","\xc5\x98"=>"R","\xc5\x94"=>"R",
				"\xc5\xa0"=>"S","\xc5\xa4"=>"T","\xc3\x9a"=>"U","\xc5\xae"=>"U","\xc3\x9c"=>"U",
				"\xc5\xb0"=>"U","\xc3\x9d"=>"Y","\xc5\xbd"=>"Z"
			);

			return strtr($string, $table);
		}

		return iconv("utf-8", "us-ascii//TRANSLIT", $string);
	}

	/**
	 * Renders cool url
	 *
	 * Strips diacritics and replaces non-alfanumeric chars by dash
	 *
	 * @author Jakub Vrana
	 * @link http://php.vrana.cz
	 * @param string $string
	 * @return string
	 */
	public static function toCoolUrl($string) {
		$string = preg_replace('~[^\\pL0-9_]+~u', '-', $string);
		$string = trim($string, '-');
		$string = self::toAscii($string);
		$string = strtolower($string);
		$string = preg_replace('~[^-a-z0-9_]+~', '', $string);

		return $string;
	}

	/**
	 * Returns true when the $string start with $substring
	 *
	 * @param string $string
	 * @param string $sub substring
	 * @return bool
	 */
	public static function startWith($string, $sub)
	{
		return strpos($string, $sub) === 0;
	}

	/**
	 * Returns true when the $string end with $substring
	 *
	 * @param string $string
	 * @param string $sub substring
	 * @return bool
	 */
	public static function endWith($string, $sub)
	{
		return strrpos($string, $sub) === strlen($string) - strlen($sub);
	}

	/**
	 * Returns relative application path
	 *
	 * @param string $path
	 * @return string
	 */
	public static function relativePath($path)
	{
		return preg_replace("#^$_SERVER[DOCUMENT_ROOT]#i", '', str_replace('\\', '/', $path));
	}
}

/******************** FUNCTIONS ********************/

/**
 * Returns argument's array
 *
 * @param mixed arg
 * @return array
 */
function a()
{
	return func_get_args();
}

/**
 * Returns associated argument's array
 *
 * @param string key
 * @param mixed value
 * @return array
 */
function aa()
{
	$args = func_get_args();
	for ($l = 0, $c = count($args); $l < $c; $l++) {
		if ($l + 1 < count($args))
			$a[$args[$l++]] = $args[$l];
		else
			$a[$args[$l++]] = null;
	}

	return $a;
}

if(!function_exists('lcfirst')) {
	function lcfirst($string)
	{
		$string[0] = strtolower($string[0]);
		return (string) $string;
	}
}
