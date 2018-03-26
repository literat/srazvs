<?php

namespace App\Services;

use App\Entities\BlockEntity;
use Nette\Utils\ArrayHash;
use App\Models\BlockModel;
use App\Models\ProgramModel;
use Nette\Database\Table\ActiveRow;

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

	/**
	 * @param BlockModel $block
	 * @param ProgramModel $program
	 */
	public function __construct(BlockModel $block, ProgramModel $program)
	{
		$this->setBlockModel($block);
		$this->setProgramModel($program);
	}

	/**
	 * @param  string $id
	 * @param  string $type
	 * @return Row
	 */
	public function findByType(string $guid, string $type)
	{
		return $this->getModelByType($type)->findBy('guid', $guid);
	}

	/**
	 * @param  ActiveRow $annotation
	 * @return BlockEntity
	 */
	public function findParentProgram(ActiveRow $annotation): BlockEntity
	{
		if(array_key_exists('block', $annotation->toArray())) {
			$parentProgram = $this->getBlockModel()->find($annotation->block);
		} else {
			$parentProgram = $annotation;
		}

		return $parentProgram;
	}

	/**
	 * @param  string    $type
	 * @param  ArrayHash $annotation
	 * @return Row
	 */
	public function updateByType(string $type, ArrayHash $annotation)
	{
		return $this->getModelByType($type)->updateBy('guid', $annotation->guid, (array) $annotation);
	}

	/**
	 * @param  string $type
	 * @return ProgramModel | BLockModel
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
				throw new Exception('Annotation model not found!');
		}

		return $model;
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
	 * @return self
	 */
	protected function setProgramModel(ProgramModel $model): self
	{
		$this->programModel = $model;

		return $this;
	}

	/**
	 * @return BlockModel
	 */
	protected function getBlockModel(): BlockModel
	{
		return $this->blockModel;
	}

	/**
	 * @param  BlockModel $model
	 * @return self
	 */
	protected function setBlockModel(BlockModel $model): self
	{
		$this->blockModel = $model;

		return $this;
	}

}
