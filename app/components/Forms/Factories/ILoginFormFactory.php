<?php

namespace App\Components\Forms\Factories;

use App\Components\Forms\LoginForm;

interface ILoginFormFactory
{
	/**
	 * @return \App\Forms\LoginForm
	 */
	public function create(): LoginForm;
}
