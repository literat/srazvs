<?php

use Nette\Configurator;

require_once __DIR__ . '/../vendor/autoload.php';

$configurator = new Configurator;

/**
 * Enabling Debugger
 */
//$configurator->setDebugMode(TRUE);
$configurator->enableDebugger(__DIR__ . '/../temp/log', 'tomaslitera@outlook.com');
$configurator->setTempDirectory(__DIR__ . '/../temp');

/**
 * Autoloading
 */
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

if ($configurator->isDebugMode()) {
    $configurator->addConfig(__DIR__ . '/config/config.development.neon');
} else {
    $configurator->addConfig(__DIR__ . '/config/config.production.neon');
}
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

/**
 * DI Container and Session
 */
RadekDostal\NetteComponents\DateTimePicker\TbDatePicker::register();

$container = $configurator->createContainer();
$parameters = $container->getParameters();

define('ROOT_DIR', $parameters['wwwDir']);
define('HTTP_DIR', '//' . $_SERVER['HTTP_HOST'] . '/');

/**
 * Application's definitions
 *
 * * do not forget to add slash at the end
 */

/* System Directories */
define('PRJ_DIR',		HTTP_DIR . 'srazvs/');
define('INC_DIR',		ROOT_DIR . '/inc/');
define('IMG_DIR',		PRJ_DIR . 'www/images/');
define('CSS_DIR',		PRJ_DIR . 'www/css/');
define('JS_DIR',		PRJ_DIR . 'www/js/');

/* Application */
define('TEMPLATE_DIR',	$parameters['appDir'] . '/templates/');
define('TPL_DIR',		TEMPLATE_DIR);

/* URLs */
define('BLOCK_DIR',		PRJ_DIR . 'block');
define('PROG_DIR',		PRJ_DIR . 'program');
define('MEET_DIR',		PRJ_DIR . 'meeting');
define('VISIT_DIR',		PRJ_DIR . 'visitor');
define('CAT_DIR',		PRJ_DIR . 'category');
define('EXP_DIR',		PRJ_DIR . 'export');
define('SET_DIR',		PRJ_DIR . 'settings');

return $container;
