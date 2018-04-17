<?php

namespace App\Components\Forms\Factories;

use App\Components\Forms\VisitorForm;

interface IVisitorFormFactory
{
	/**
	 * @return \App\Components\Forms\VisitorForm
	 */
	public function create(): VisitorForm;
}
