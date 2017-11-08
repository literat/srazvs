<?php

namespace App\Components;

use App\Models\ProgramModel;

class PublicProgramsControl extends AProgramsControl
{

	const TEMPLATE_NAME = 'PublicPrograms';

	/**
	 * @var PublicProgramDetailControl
	 */
	private $publicProgramDetail;

	/**
	 * @param ProgramModel $model
	 */
	public function __construct(ProgramModel $model, PublicProgramDetailControl $control)
	{
		$this->setProgramModel($model);
		$this->setPublicProgramDetailControl($control);
	}

	/**
	 * @return ProgramsControl
	 */
	protected function createComponentPublicProgramDetail()
	{
		return $this->publicProgramDetail;
	}

	/**
	 * @param  PublicProgramDetailControl $control
	 * @return self
	 */
	protected function setPublicProgramDetailControl(PublicProgramDetailControl $control): self
	{
		$this->publicProgramDetail = $control;

		return $this;
	}

}
