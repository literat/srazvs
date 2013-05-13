<?php
 
/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id$
 * @package     Haefko
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
        public static function init()
        {
                self::sanitizeData();
                self::$domain = $_SERVER['SERVER_NAME'];
                self::$serverURL = 'http' . (@$_SERVER['HTTPS'] ? 's' : '') . '://' . self::$domain;

                $base = trim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                if (!empty($base))
                        self::$baseURL = "/$base";

                self::$request = new HFHttpRequest();
                self::$response = new HFHttpResponse();
        }
 
        /**
         * Sanitizes superglobal variables ($_GET, $_POST, $_COOKIE a $_REQUEST)
         */
        public static function sanitizeData()
        {
                if (!get_magic_quotes_gpc())
                        return;

                $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
                while (list($key, $val) = each($process)) {
                        foreach ($val as $k => $v) {
                                unset($process[$key][$k]);
                                if (is_array($v)) {
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
 
Http::init();
 
class HFHttpRequest extends Object
{
        /** @var bool */
        public $isAjax;
 
        /** @var string */
        public $method;
 
        /** @var string */
        public $request;
 
        /**
         * Constructor
         * @return HFHttpRequest
         */
        public function __construct()
        {
                $this->isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                        && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';

                $this->method = strtolower($_SERVER['REQUEST_METHOD']);
                $this->request = self::getRequest();
        }
 
        /**
         * Returns user IP
         * @return string
         */
        public function getIp()
        {
                if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
                        return $_SERVER['HTTP_X_FORWARDED_FOR'];
                else
                        return $_SERVER['REMOTE_ADDR'];
        }
 
        /**
         * Returns request data for form
         * @param string $method form method: post/get
         * @return array
         */
        public function getForm($method = 'post')
        {
                $method = strtolower($method);
                if ($method == 'get')
                        return $_GET;
 
                $data = array();
                if (!empty($_FILES)) {
                        foreach ($_FILES as $form => $value) {
                                $controls = array_keys($value['name']);
                                foreach ($controls as $control) {
                                        $fields = array_keys($value);
                                        foreach ($fields as $field)
                                                $data[$form][$control][$field] = $value[$field][$control];
                                }
                        }
                }
 
                return array_merge_recursive($data, $_POST);
        }
 
        /**
         * Returns referer
         * @return string|null
         */
        public function getReferer()
        {
                if (!isset($_SERVER['HTTP_REFERER']))
                        return null;

                return $_SERVER['HTTP_REFERER'];
        }
 
        /**
         * Returns full url request
         * @return string
         */
        public function getFullRequest()
        {
                return Http::$serverURL . '/' . $this->request;
        }
 
        /**
         * Returns url request
         * @return string
         */
 
        protected function getRequest()
        {
                $url = urldecode($_SERVER['REQUEST_URI']);
                $qm = strpos($url, '?');
                if ($qm !== false)
                        $url = substr($url, 0, $qm);
 
                $script = dirname($_SERVER['SCRIPT_NAME']);
                if (strpos($url, $script) === 0)
                        $url = substr($url, strlen($script));

                $script = basename($_SERVER['SCRIPT_NAME']);
                if (strpos($url, $script) === 0)
                        $url = substr($url, strlen($script));

                return trim($url, '/\\');
        }
}
 
class HFHttpResponse extends Object
{
        /**
         * Sends error header
         * @param int $code error code
         * @return HFHttpResponse
         */
        public function error($code = 404)
        {
                switch ($code) {
                case 401:
                        header('HTTP/1.1 401 Unauthorized');
                        break;
                case 404:
                        header('HTTP/1.1 404 Not Found');
                        break;
                case 500:
                        header('HTTP/1.1 500 Internal Server Error');
                        break;
                default:
                        throw new Exception("Unsupported error code '$code'.");
                        break;
                }

                return $this;
        }
 
        /**
         * Sends redirect header
         * @param string $url absolute url
         * @param int $code redirect code
         * @return HFHttpResponse
         */
        public function redirect($url, $code = 300)
        {
                header("Location: $url", true, $code);
                return $this;
        }
 
        /**
         * Sends mime-type header
         * @param string $mime mime-type
         * @return HFHttoResponse
         */
        public function mimetype($mime)
        {
                header("Content-type: $mime");
                return $this;
        }
}
 



