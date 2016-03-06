<?php

use Codeplex\Routers;
use Tracy\Debugger;

require_once('inc/define.inc.php');

require_once(FRAMEWORK.'loader.php');

require_once(__DIR__ . '/vendor/autoload.php');

Debugger::enable(Debugger::DETECT, __DIR__ . '/temp/log');
Debugger::$email = $cfg['mail-admin'];

$sql = "SELECT id
		FROM kk_meetings
		ORDER BY id DESC
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

if(!isset($_SESSION['meetingID'])) {
	$_SESSION['meetingID'] = $data['id'];
}

require_once(FRAMEWORK.'Routers/Router.php');

$router = new Nix\Routers\Router();

$router->setDefaults(array(
    'controller' => 'meeting',
    'id' => $data['id']
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
		$controller = new $class;
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
