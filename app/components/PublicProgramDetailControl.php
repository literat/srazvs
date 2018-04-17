<?php

namespace App\Components;

use App\Models\ProgramModel;

class PublicProgramDetailControl extends BaseControl
{
	const TEMPLATE_NAME = 'PublicProgramDetail';

	/**
	 * @var ProgramModel
	 */
	private $programModel;

	/**
	 * @param ProgramModel $model
	 */
	public function __construct(ProgramModel $model)
	{
		$this->setProgramModel($model);
	}

	/**
	 * @return void
	 */
	public function render($programId)
	{
		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->visitorsOnProgram = $this->getProgramModel()->countProgramVisitors($programId);
		$template->program = $this->getProgramModel()->find($programId);
		$template->render();
	}

	/**
	 * @return ProgramModel
	 */
	public function getProgramModel(): ProgramModel
	{
		return $this->programModel;
	}

	/**
	 * @param  ProgramModel $programModel
	 * @return self
	 */
	public function setProgramModel(ProgramModel $programModel): self
	{
		$this->programModel = $programModel;

		return $this;
	}
}
