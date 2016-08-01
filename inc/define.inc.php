<?php

define('DEBUG', FALSE);

//debuggovani
if(defined('DEBUG') && DEBUG === TRUE){
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
}

$style = "";
