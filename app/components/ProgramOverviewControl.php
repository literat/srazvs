<?php

namespace App\Components;

use App\Components\ProgramsControl;
use App\Models\BlockModel;

class ProgramOverviewControl extends AProgramOverviewControl
{

	const TEMPLATE_NAME = 'ProgramOverview';

	/**
	 * @param BlocksByDayControl $control
	 */
	public function __construct(BlockModel $model, ProgramsControl $control)
	{
		$this->setBlocksBayControl(new BlocksByDayControl($model, $control));
	}

}
