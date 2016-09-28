<?php

use Tracy\Debugger;
use Nette\Configurator;
use Nette\DI\ContainerLoader;
use Nette\Database\Context;
use Nette\Caching\Storages\FileStorage;
use Nette\Database\Structure;
use Nette\Loaders\RobotLoader;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../inc/errors.inc.php';

define('SESSION_PREFIX', md5('localhost'.'vodni'.'vodni'.'sunlight')."-");

//nastartovani session
session_name(SESSION_PREFIX . 'session');

//$httpRequest = $container->getByType('Nette\Http\Request');
$requestFatory = new Nette\Http\RequestFactory;
$httpRequest = $requestFatory->createHttpRequest();
$httpResponse = new Nette\Http\Response;

$session = new Nette\Http\Session($httpRequest, $httpResponse);

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
	->addDirectory(__DIR__ . '/../inc')
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
$configurator->addServices(array(
    'session.session' => $session,
));
$container = $configurator->createContainer();
$parameters = $container->getParameters();

define('ROOT_DIR', $parameters['wwwDir']);
define('HTTP_DIR', 'http://'.$_SERVER['HTTP_HOST'] . '/');

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
define('BLOCK_DIR',		PRJ_DIR.'block');
define('PROG_DIR',		PRJ_DIR.'program');
define('MEET_DIR',		PRJ_DIR.'meeting');
define('VISIT_DIR',		PRJ_DIR.'visitor');
define('CAT_DIR',		PRJ_DIR.'category');
define('EXP_DIR',		PRJ_DIR.'export');
define('SET_DIR',		PRJ_DIR.'settings');

if(!function_exists('dd')) {
	function dd($variable) {
		\Tracy\Debugger::dump($variable);
		die;
	}
}

if(!function_exists('redirect')) {
	function redirect($url) {
		$httpResponse = new Nette\Http\Response;
		$httpResponse->redirect($url);
		exit;
	}
}

/**
 * Connecting to Database
 */
$connection = $container->createServiceConnection();
$database = $container->createServiceDatabase();

// Tracy database panel
Nette\Database\Helpers::createDebugPanel($connection);

if(!isset($_SESSION['meetingID'])) {
	$_SESSION['meetingID'] = $database
		->table('kk_meetings')
		->select('id')
		->order('id DESC')
		->limit(1)
		->fetchField();
}

/**
 * Routing
 */
$router = new RouteList;
$router[] = new Route('/', [
	'presenter' => 'Meeting',
    'action' => 'index',
    'id' => $_SESSION['meetingID'],
]);
$router[] = new Route('<presenter>[/<action>]', 'Meeting:index');
$router[] = new Route('<presenter>/<action>[/<id>]', 'Meeting:index');

$appRequest = $router->match($httpRequest);
$controllerName = $appRequest->getPresenterName();

$container->addService('router', $router);
$target = $parameters['appDir'] . '/controllers/' . $controllerName . 'Controller.php';
$container->parameters['router'] = $appRequest;

/**
 * Templating
 */
$latte = new \Latte\Engine;
$latte->setTempDirectory(__DIR__ . '/../temp');
$container->addService('latte', $latte);

//get target
if(file_exists($target)) {
	//print_r(get_declared_classes());
	if(
		(
			$controllerName != 'Registration'
			&& ($httpRequest->getQuery('cms') != 'public')
		)
			&& ($httpRequest->getQuery('cms') != 'annotation')
			&& ($httpRequest->getPost('page') != 'annotation')
	) {
		include_once(INC_DIR . 'access.inc.php');
	}

	require_once($target);

	//modify page to fit naming convention
	$class = $controllerName . 'Controller';

	//instantiate the appropriate class
	if(class_exists($class)) {
		$controller = new $class($database, $container);
	} else {
		//did we name our class correctly?
		die('class does not exist!');
	}
} else {
	//can't find the file in 'controllers'!
	die('page does not exist!');
}

//once we have the controller instantiated, execute the default function
$controller->init();
