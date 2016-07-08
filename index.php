<?php

use Tracy\Debugger;
use Nette\DI\ContainerLoader;
use Nette\Database\Context;
use Nette\Caching\Storages\FileStorage;
use Nette\Database\Structure;
use Nette\Loaders\RobotLoader;

/**
 * Autoloading
 */
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/inc/define.inc.php');

$loader = new RobotLoader;
// Add directories for RobotLoader to index
$loader->addDirectory('app');
$loader->addDirectory('libs');
// And set caching to the 'temp' directory on the disc
$loader->setCacheStorage(new FileStorage('temp'));
// Run the RobotLoader
$loader->register();

/**
 * Enabling Debugger
 */
Debugger::enable(Debugger::DETECT, __DIR__ . '/temp/log');
Debugger::$email = $cfg['mail-admin'];

/**
 * DI Container
 */
$containerLoader = new ContainerLoader(__DIR__ . '/temp');
$class = $containerLoader->load('', function($compiler) {
    $compiler->loadConfig(__DIR__ . '/app/config/config.local.neon');
    $compiler->loadConfig(__DIR__ . '/app/config/config.neon');
});
$container = new $class;

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

$router = new Nix\Routers\Router();

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
