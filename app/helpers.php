<?php

if(!function_exists('dd')) {
	/**
	 * Dump the passed variables and end the script.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  mixed
	 * @return void
	 */
	function dd() {
		array_map(function ($x){
			(\Tracy\Debugger::dump($x));
		}, func_get_args());

		die(1);
	}
}

if(!function_exists('appVersion')) {
	/**
	 * Get application version from package.json
	 *
	 * @return string
	 */
	function appVersion() {
		$packagePath = realpath(__DIR__ . '/../package.json');
		$package = json_decode(file_get_contents($packagePath));

		return $package->version;
	}
}
