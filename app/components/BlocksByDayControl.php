<?php

namespace App\Components;

use App\Models\BlockModel;

class BlocksByDayControl extends ABlocksByDayControl implements IBlocksByDayControl
{
	const TEMPLATE_NAME = 'BlocksByDay';

	public function __construct(BlockModel $model, ProgramsControl $control)
	{
		$this->setBlockModel($model);
		$this->setProgramsControl($control);
	}
}
