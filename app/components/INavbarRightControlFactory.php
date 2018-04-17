<?php

namespace App\Components;

interface INavbarRightControlFactory
{
	/**
	 * @return NavbarRightControl
	 */
	public function create(): NavbarRightControl;
}
