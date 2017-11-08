<?php

namespace App\Components;

use App\Components\PublicProgramsControl;
use App\Models\BlockModel;

class PublicProgramOverviewControl extends AProgramOverviewControl
{

	const TEMPLATE_NAME = 'PublicProgramOverview';

	/**
	 * @param BlockModel               $model
	 * @param PublicBlockDetailControl $blockDetailControl
	 */
	public function __construct(BlockModel $model, PublicBlocksByDayControl $control)
	{
		$this->setBlocksBayControl($control);
	}

}
