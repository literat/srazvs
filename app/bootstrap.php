<?php

use Tracy\Debugger;
use Nette\Configurator;
use Nette\DI\ContainerLoader;
use Nette\Database\Context;
use Nette\Caching\Storages\FileStorage;
use Nette\Database\Structure;
use Nette\Loaders\RobotLoader;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../inc/define.inc.php';

define('SESSION_PREFIX', md5('localhost'.'vodni'.'vodni'.'sunlight')."-");

//nastartovani session
session_name(SESSION_PREFIX.'session');

$requestFatory = new Nette\Http\RequestFactory;
$request = $requestFatory->createHttpRequest();
$response = new Nette\Http\Response;

$session = new Nette\Http\Session($request, $response);

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

/*
$parameters = $container->getParameters();

define('SESSION_PREFIX', md5(
	$parameters['database']['host']
	. $parameters['database']['dbname']
	. $parameters['database']['user']
	. $parameters['prefix']
) . "-");

//  session starting
session_name(SESSION_PREFIX . 'session');
session_start();
*/

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

$router = new Router();

$router->setDefaults(array(
    'controller' => 'meeting',
    'id' => $_SESSION['meetingID'],
));

/**
 first route that match is used
*/
$router->connect('/', array(), true, true);
//$router->connect('/<:controller>/<:action>', array(), true, true);
$router->connect('/<:controller>', array(), true, true);
$router->connect('/<:controller>/<:action>', array(), true, true);
$routing = $router->getRouting();

$target = CONTROLLER_DIR.$routing['controller'].'Controller.php';

//get target
if(file_exists($target))
{
	//print_r(get_declared_classes());
	if(
		(
			$routing['controller'] != 'Registration'
			&& (!isset($_GET['cms']) || $_GET['cms'] != 'public')
		)
			&& (!isset($_GET['cms']) || $_GET['cms'] != 'annotation')
			&& (!isset($_POST['page']) || $_POST['page'] != 'annotation')
	) {
		include_once(INC_DIR.'access.inc.php');
	}
	require_once($target);

	//modify page to fit naming convention
	$class = $routing['controller'].'Controller';

	//instantiate the appropriate class
	if(class_exists($class))
	{
		$controller = new $class($database, $container);
		$controller->setRouting($routing);
	}
	else
	{
		//did we name our class correctly?
		die('class does not exist!');
	}
}
else
{
	//can't find the file in 'controllers'!
	die('page does not exist!');
}

$getParams = $router->getParams();

//if(empty($getParams)) {
//	redirect('?mid='.$data['id']);
//}

//once we have the controller instantiated, execute the default function
//pass any GET varaibles to the main method
$controller->init($getParams);
