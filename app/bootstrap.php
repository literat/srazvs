<?php

use Tracy\Debugger;
use Nette\Configurator;
use Nette\DI\ContainerLoader;
use Nette\Database\Context;
use Nette\Caching\Storages\FileStorage;
use Nette\Database\Structure;
use Nette\Loaders\RobotLoader;
use App\Routers\RouterFactory;

require_once __DIR__ . '/../vendor/autoload.php';

define('SESSION_PREFIX', md5('localhost'.'vodni'.'vodni'.'sunlight')."-");

//nastartovani session
session_name(SESSION_PREFIX . 'session');

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
define('HTTP_DIR', '//'.$_SERVER['HTTP_HOST'] . '/');

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
	/**
	 * Dump the passed variables and end the script.
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

if(!function_exists('redirect')) {
	function redirect($url) {
		$httpResponse = new Nette\Http\Response;
		$httpResponse->redirect($url);
		exit;
	}
}

if(!function_exists('appVersion')) {
	function appVersion() {
		$packagePath = realpath(__DIR__ . '/../package.json');
		$package = json_decode(file_get_contents($packagePath));
		return $package->version;
	}
}

/**
 * Connecting to Database
 */
$connection = $container->getService('database.default.connection');
$database = $container->getService('database.default.context');

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
$router = $container->getService('routing.router');

$appRequest = $router->match($httpRequest);

if($appRequest) {
	$controllerName = $appRequest->getPresenterName();
	$action = $appRequest->getParameter('action');
	$id = $appRequest->getParameter('id');
} else {
	$badRequestException = new \Nette\Application\BadRequestException('Page not found!', 404);
	$error = new \App\Presenters\Error4xxPresenter();
	$httpResponse->setCode(Nette\Http\Response::S404_NOT_FOUND);
	$error->render404($badRequestException);
	die;
}

$target = $parameters['appDir'] . '/presenters/' . $controllerName . 'Presenter.php';
$container->parameters['router'] = $appRequest;

$publicPages = [
	'Block.annotation',
	'Program.annotation',
	'Program.public',
	'Export.program',
	'Export.program',
	'Registration.default',
	'Registration.check',
	'Registration.create',
	'Registration.edit',
	'Registration.update',
	'Auth.login',
	'Auth.skautis',
];

/**
 * Including access control
 */
if(file_exists($target)) {
	// access control
	if(array_search($controllerName.'.'.$action, $publicPages) === false) {
		include_once(INC_DIR . 'access.inc.php');
	}
}

return $container;
