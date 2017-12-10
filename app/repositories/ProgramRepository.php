<?php

namespace App\Repositories;

use App\Models\VisitorModel;
use App\Models\ProgramModel;

class ProgramRepository
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
	 * @param VisitorModel $visitorModel
	 * @param ProgramModel $programModel
	 */
	public function __construct(
		VisitorModel $visitorModel,
		ProgramModel $programModel
	) {
		$this->setVisitorModel($visitorModel);
		$this->setProgramModel($programModel);
	}

	public function create($program)
	{
		if(array_key_exists('display_in_reg', $program) && empty($program['display_in_reg'])) {
			$program['display_in_reg'] = '0';
		} else {
			$program['display_in_reg'] = '1';
		}

		return $this->getProgramModel()->create((array) $program);
	}

	public function update($program)
	{
		return $this->getProgramModel()->update($program);
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
	protected function setProgramModel(ProgramModel $model): self
	{
		$this->programModel = $model;

		return $this;
	}

	/**
	 * @return VisitorModel
	 */
	protected function getVisitorModel(): VisitorModel
	{
		return $this->visitorModel;
	}

	/**
	 * @param  VisitorModel $model
	 * @return VisitorService
	 */
	protected function setVisitorModel(VisitorModel $model): self
	{
		$this->visitorModel = $model;

		return $this;
	}

}
