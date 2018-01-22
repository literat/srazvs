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

if(!function_exists('webpackManifest')) {
    /**
     * Get object from manifest.json
     *
     * @return string
     */
    function webpackManifest() {
        $manifestPath = realpath(__DIR__ . '/../www/manifest.json');
        $manifest = json_decode(file_get_contents($manifestPath));

        return $manifest;
    }
}

if(!function_exists('mainCss')) {
    /**
     * Get main css file from manifest.json
     *
     * @return string
     */
    function mainCss() {
        return webpackManifest()->main[1];
    }
}

if(!function_exists('mainJs')) {
    /**
     * Get main css file from manifest.json
     *
     * @return string
     */
    function mainJs() {
        return webpackManifest()->main[0];
    }
}

if(!function_exists('vendorJs')) {
    /**
     * Get main css file from manifest.json
     *
     * @return string
     */
    function vendorJs() {
        return webpackManifest()->vendor;
    }
}
