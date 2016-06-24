<?php

use Tracy\Debugger;
use Nette\DI\ContainerLoader;
use Nette\Database\Context;
use Nette\Caching\Storages\FileStorage;
use Nette\Database\Structure;

require_once('inc/define.inc.php');

require_once(FRAMEWORK.'loader.php');

require_once __DIR__ . '/app/models/EmailerModel.php';
/**
 * Composer Autoloading
 */
require_once(__DIR__ . '/vendor/autoload.php');

/**
 * Enabling Debugger
 */
Debugger::enable(Debugger::DETECT, __DIR__ . '/temp/log');
Debugger::$email = $cfg['mail-admin'];

/**
 * DI Container
 */
$loader = new ContainerLoader(__DIR__ . '/temp');
$class = $loader->load('', function($compiler) {
    $compiler->loadConfig(__DIR__ . '/app/config/config.local.neon');
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

require_once(FRAMEWORK.'Routers/Router.php');

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
