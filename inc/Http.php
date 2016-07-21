<?php

/**
 * Http
 *
 * @author      Tomas Litera    <tomaslitera@hotmail.com>
 * @version     2014-02-19
 */
class Http
{
	/** @var string */
	public static $domain;

	/** @var string */
	public static $serverURL;

	/** @var string */
	public static $baseURL;

	/** @var HFHttpRequest */
	public static $request;

	/** @var HFHttpResponse */
	public static $response;

	/**
	 * Initializes HTTP class
	 */
	public function __construct()
	{
		self::sanitizeData();
		self::$domain = $_SERVER['SERVER_NAME'];
		self::$serverURL = 'http' . (@$_SERVER['HTTPS'] ? 's' : '') . '://' . self::$domain;

		$base = trim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
		if(!empty($base)) {
			self::$baseURL = "/$base";
		}

		self::$request = new Request();
		self::$response = new Response();
	}

	/**
	 * Sanitizes superglobal variables ($_GET, $_POST, $_COOKIE a $_REQUEST)
	 */
	public static function sanitizeData()
	{
		if(!get_magic_quotes_gpc()) {
			return;
		}

		$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
		while(list($key, $val) = each($process)) {
			foreach($val as $k => $v) {
				unset($process[$key][$k]);
				if(is_array($v)) {
					$process[$key][$k] = $v;
					$process[] = & $process[$key][$k];
				} else {
					$process[$key][$k] = stripslashes($v);
				}
			}
		}

		unset($process);
	}
}