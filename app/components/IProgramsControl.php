<?php

namespace App\Components;

interface IProgramsControl
{
	/**
	 * @param  integer $blockId
	 * @return void
	 */
	public function render($blockId);
}
