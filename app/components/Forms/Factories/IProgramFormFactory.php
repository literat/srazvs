<?php

namespace App\Components\Forms\Factories;

use App\Components\Forms\ProgramForm;

interface IProgramFormFactory
{
	/**
	 * @return \App\Components\Forms\ProgramForm
	 */
	public function create(): ProgramForm;
}
