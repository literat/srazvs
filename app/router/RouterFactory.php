<?php

namespace App\Routers;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
		$router[] = new Route('', 'Dashboard:default', Route::ONE_WAY);
		$router[] = new Route('index.php', 'Dashboard:default', Route::ONE_WAY);
		$router[] = new Route('dashboard/', 'Dashboard:default');
		$router[] = new Route('<presenter>/[<action>[/<id>][/<actionId>]]', 'Dashboard:listing');

		return $router;
	}

}
