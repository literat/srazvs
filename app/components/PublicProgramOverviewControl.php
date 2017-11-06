<?php

namespace App\Components;

use App\Components\PublicProgramsControl;
use App\Models\BlockModel;

class PublicProgramOverviewControl extends AProgramOverviewControl
{

	const TEMPLATE_NAME = 'ProgramOverview';

	/**
	 * @param BlocksByDayControl $control
	 */
	public function __construct(BlockModel $model, PublicProgramsControl $control)
	{
		$this->setBlocksBayControl(new BlocksByDayControl($model, $control));
	}

}
