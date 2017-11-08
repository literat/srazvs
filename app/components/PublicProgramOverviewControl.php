<?php

namespace App\Components;

use App\Components\PublicProgramsControl;
use App\Models\BlockModel;

class PublicProgramOverviewControl extends AProgramOverviewControl
{

	const TEMPLATE_NAME = 'ProgramOverview';

	/**
	 * @param BlockModel               $model
	 * @param PublicProgramsControl    $programsControl
	 * @param PublicBlockDetailControl $blockDetailControl
	 */
	public function __construct(BlockModel $model, PublicProgramsControl $programsControl, PublicBlockDetailControl $blockDetailControl)
	{
		$this->setBlocksBayControl(new PublicBlocksByDayControl($model, $programsControl, $blockDetailControl));
	}

}
