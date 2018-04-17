<?php

namespace App\Components\Forms\Factories;

use App\Components\Forms\RegistrationForm;

interface IRegistrationFormFactory
{
	/**
	 * @return \App\Forms\RegistrationForm
	 */
	public function create(): RegistrationForm;
}
