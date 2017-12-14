<?php

namespace App\Repositories;

use App\Models\ProgramModel;
use App\Models\VisitorModel;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

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

	/**
	 * @param int $meetingId
	 */
	public function setMeetingId(int $meetingId): self
	{
		$this->getProgramModel()->setMeetingId($meetingId);
		$this->getVisitorModel()->setMeetingId($meetingId);

		return $this;
	}

	/**
	 * @return array
	 */
	public function all(): array
	{
		return $this->getProgramModel()->all();
	}

	/**
	 * @param  int  $id
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function find(int $id): ActiveRow
	{
		return $this->getProgramModel()->find($id);
	}

	/**
	 * @param  int  $VisitorId
	 * @return array
	 */
	public function findByVisitorId(int $VisitorId): array
	{
		return $this->getProgramModels()->findByVisitorId($visitorId);
	}

	/**
	 * @param  int    $programId
	 * @return array
	 */
	public function findTutor(int $programId): ActiveRow
	{
		return $this->getProgramModel()->getTutor($programId);
	}

	/**
	 * @param  int   $programId
	 * @return string
	 */
	public function findVisitors(int $programId): string
	{
		return $this->getProgramModel()->getProgramVisitors($programId);
	}

	/**
	 * @param  Nette\Utils\ArrayHash $program
	 * @return boolean
	 */
	public function create(ArrayHash $program)
	{
		$program = $this->transformDisplayInRegValue($program);

		return $this->getProgramModel()->create((array) $program);
	}

	/**
	 * @param  Nette\Utils\ArrayHash $program
	 * @return boolean
	 */
	public function update(int $id, ArrayHash $program)
	{
		$program = $this->transformDisplayInRegValue($program);

		return $this->getProgramModel()->update($id, (array) $program);
	}

	/**
	 * @param  int    $id
	 * @return boolean
	 */
	public function delete(int $id)
	{
		return $this->getProgramModel()->delete($id);
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
	 * @param  Nette\Utils\ArrayHash $program
	 * @return Nette\Utils\ArrayHash
	 */
	protected function transformDisplayInRegValue(ArrayHash $program): ArrayHash
	{
		if(array_key_exists('display_in_reg', $program) && empty($program['display_in_reg'])) {
			$program['display_in_reg'] = '1';
		} else {
			$program['display_in_reg'] = '0';
		}

		return $program;
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
