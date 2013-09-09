<?php

use Codeplex\Routers;

require_once('inc/define.inc.php');
include_once(INC_DIR.'access.inc.php');

$sql = "SELECT id
		FROM kk_meetings
		ORDER BY id DESC
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

require_once(LIBS_DIR.'Codeplex/Routers/Router.php');

$router = new Codeplex\Routers\Router();

$router->setDefaults(array(
    'controller' => 'meeting',
    'id' => $data['id']
));

$router->connect('/', array(), true, true);
$router->connect('/<:controller>', array(), true, true);
$router->connect('/<:controller>/<:action>', array(), true, true);
$routing = $router->getRouting();

$target = CONTROLLER_DIR.$routing['controller'].'Controller.php';

//var_dump($target);

//get target
if(file_exists($target))
{
	//print_r(get_declared_classes());
	require_once($target);

	//modify page to fit naming convention
	$class = $routing['controller'].'Controller';

	//instantiate the appropriate class
	if(class_exists($class))
	{
		$controller = new $class;
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

$getVars = $router->getParams();

//once we have the controller instantiated, execute the default function
//pass any GET varaibles to the main method
$controller->init($getVars);

//redirect("meetings/?mid=".$data['id']."");

/*echo "<script type='javascript'>
	   window.location='meetings/?mid=".$data['id']."';
	  </script>";*/