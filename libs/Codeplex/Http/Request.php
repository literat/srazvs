<?php

namespace Codeplex\Http;

use Codeplex;

class Request
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