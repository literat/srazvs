<?php

namespace App\Components;

use App\Models\ProgramModel;

interface IProgramsControl
{

	/**
	 * @param  integer $blockId
	 * @return void
	 */
	public function render($blockId);

}
