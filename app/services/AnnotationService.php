<?php

namespace App\Services;

use App\Entities\BlockEntity;
use App\Models\BlockModel;
use App\Models\ProgramModel;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

class AnnotationService
{
	/**
	 * @var BlockModel
	 */
	protected $blockModel;

	/**
	 * @var ProgramModel
	 */
	protected $programModel;

	public function __construct(BlockModel $block, ProgramModel $program)
	{
		$this->setBlockModel($block);
		$this->setProgramModel($program);
	}

	public function findByType(string $guid, string $type): ActiveRow
	{
		return $this->getModelByType($type)->findBy('guid', $guid);
	}

	public function findParentProgram(ActiveRow $annotation): BlockEntity
	{
		if (array_key_exists('block', $annotation->toArray())) {
			$parentProgram = $this->getBlockModel()->find($annotation->block);
		} else {
			$parentProgram = $annotation;
		}

		return $parentProgram;
	}

	public function updateByType(string $type, ArrayHash $annotation): ActiveRow
	{
		return $this->getModelByType($type)->updateBy('guid', $annotation->guid, (array) $annotation);
	}

	/**
	 * @param  string                   $type
	 * @return \App\Models\ProgramModel | \App\Models\BLockModel
	 * @throws \Exception
	 */
	protected function getModelByType(string $type)
	{
		switch ($type) {
			case 'block':
				$model = $this->getBlockModel();
				break;
			case 'program':
				$model = $this->getProgramModel();
				break;
			default:
				throw new \Exception('Annotation model not found!');
		}

		return $model;
	}

	protected function getProgramModel(): ProgramModel
	{
		return $this->programModel;
	}

	protected function setProgramModel(ProgramModel $model): self
	{
		$this->programModel = $model;

		return $this;
	}

	protected function getBlockModel(): BlockModel
	{
		return $this->blockModel;
	}

	protected function setBlockModel(BlockModel $model): self
	{
		$this->blockModel = $model;

		return $this;
	}
}
