<?php

namespace Codeplex\Http;

use Codeplex;

class Response
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