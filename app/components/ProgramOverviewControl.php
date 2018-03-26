<?php

namespace App\Components;

use App\Models\BlockModel;

class ProgramOverviewControl extends AProgramOverviewControl
{

	const TEMPLATE_NAME = 'ProgramOverview';

	public function __construct(BlockModel $model, ProgramsControl $control)
	{
		$this->setBlocksBayControl(new BlocksByDayControl($model, $control));
	}

}
