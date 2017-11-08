<?php

namespace App\Components;

use App\Models\BlockModel;

class PublicBlocksByDayControl extends ABlocksByDayControl implements IBlocksByDayControl
{

	const TEMPLATE_NAME = 'PublicBlocksByDay';

	/**
	 * @var PublicBlockDetailControl
	 */
	private $publicBlockDetailControl;

	/**
	 * @param BlockModel               $model
	 * @param PublicProgramsControl    $programsControl
	 * @param PublicBlockDetailControl $blockDetailControl
	 */
	public function __construct(
		BlockModel $model,
		PublicProgramsControl $programsControl,
		PublicBlockDetailControl $blockDetailControl
	)
	{
		$this->setBlockModel($model);
		$this->setProgramsControl($programsControl);
		$this->setPublicBlockDetailControl($blockDetailControl);
	}

	/**
	 * @return PublicBlockDetailControl
	 */
	protected function createComponentPublicBlockDetail(): PublicBlockDetailControl
	{
		return $this->publicBlockDetailControl;
	}

	/**
	 * @param  IProgramsControl $control
	 * @return self
	 */
	protected function setPublicBlockDetailControl(PublicBlockDetailControl $control): self
	{
		$this->publicBlockDetailControl = $control;

		return $this;
	}

}
