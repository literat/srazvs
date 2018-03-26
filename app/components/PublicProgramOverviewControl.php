<?php

namespace App\Components;

class PublicProgramOverviewControl extends AProgramOverviewControl
{

	const TEMPLATE_NAME = 'PublicProgramOverview';

	public function __construct(PublicBlocksByDayControl $control)
	{
		$this->setBlocksBayControl($control);
	}

}
