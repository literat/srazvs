<?php

namespace App\Routers;

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use Nette\Application\IRouter;

/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter(): IRouter
	{
		$router = new RouteList();
		$router[] = new Route('', 'Login:default', Route::ONE_WAY);
		$router[] = new Route('index.php', 'Dashboard:default', Route::ONE_WAY);
		$router[] = new Route('dashboard/', 'Dashboard:default');
		$router[] = new Route('registrace/[<action>/[<guid>/]]', 'Registration:default', Route::ONE_WAY);
		$router[] = new Route('registration/[<action>/[<guid>/]]', 'Registration:default');
		$router[] = new Route('prihlaseni/[<action>/[<guid>/]]', 'Login:default', Route::ONE_WAY);
		$router[] = new Route('login/[<action>/[<guid>/]]', 'Login:default');
		$router[] = new Route('export/[<action>/[<type>/[<id>/]]]', 'Export:default');
		$router[] = new Route('block/annotation/<guid>', [
			'presenter' => 'Annotation',
			'action'    => 'edit',
			'type'      => 'block',
		], Route::ONE_WAY);
		$router[] = new Route('program/annotation/<guid>', [
			'presenter' => 'Annotation',
			'action'    => 'edit',
			'type'      => 'program',
		], Route::ONE_WAY);
		$router[] = new Route('annotation/<action>/<type>/<guid>/', 'Annotation:default');
		$router[] = new Route('<presenter>/[<action>/[<id>/]]', 'Dashboard:listing');
		$router[] = new Route('<presenter>/[<action>/[<guid>/]]', 'Dashboard:listing');

		return $router;
	}

}
