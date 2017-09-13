<?php

namespace App\Services;

use App\Models\VisitorModel;
use App\Models\ProgramModel;

class ProgramService
{

	/**
	 * @var VisitorModel
	 */
	protected $visitorModel;

	/**
	 * @var ProgramModel
	 */
	protected $programModel;

	/**
	 * @param VisitorModel $visitor
	 * @param ProgramModel $program
	 */
	public function __construct(
		VisitorModel $visitor,
		ProgramModel $program
	) {
		$this->setVisitorModel($visitor);
		$this->setProgramModel($program);
	}

	/**
	 * @param  int    $visitorId
	 * @return array
	 */
	public function assembleFormPrograms(int $visitorId): array
	{
		$visitorPrograms = $this->getVisitorModel()->findVisitorPrograms($visitorId);

		$formPrograms = [];

		foreach ($visitorPrograms as $visitorProgram) {
			if($visitorProgram->program !== 0) {
				$program = $this->getProgramModel()->findByProgramId($visitorProgram->program);
				$formPrograms['blck_' . $program->block] = $visitorProgram->program;
			}
		}

		return $formPrograms;
	}

	/**
	 * @return ProgramModel
	 */
	protected function getProgramModel()
	{
		return $this->programModel;
	}

	/**
	 * @param  ProgramModel $model
	 * @return $this
	 */
	protected function setProgramModel(ProgramModel $model)
	{
		$this->programModel = $model;

		return $this;
	}

	/**
	 * @return VisitorModel
	 */
	protected function getVisitorModel()
	{
		return $this->visitorModel;
	}

	/**
	 * @param  VisitorModel $model
	 * @return VisitorService
	 */
	protected function setVisitorModel(VisitorModel $model): ProgramService
	{
		$this->visitorModel = $model;

		return $this;
	}

}
