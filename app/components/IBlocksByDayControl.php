<?php

namespace App\Components;

interface IBlocksByDayControl
{

	/**
	 * @param  string $day
	 * @return void
	 */
	public function render($day);

}
