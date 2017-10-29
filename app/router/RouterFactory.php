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
		$router[] = new Route('registrace/[<action>/[<guid>/]]', 'Registration:default', Route::ONE_WAY);
		$router[] = new Route('registration/[<action>/[<guid>/]]', 'Registration:default');
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

		return $router;
	}

}
