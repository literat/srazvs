<?php

namespace App\Components\Forms\Factories;

use App\Components\Forms\BlockForm;

interface IBlockFormFactory
{
	/**
	 * @return \App\Components\Forms\BlockForm
	 */
	public function create(): BlockForm;
}
