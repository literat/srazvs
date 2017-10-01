<?php

namespace App\Services;

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
	public function findByType(string $id, string $type)
	{
		switch ($type) {
			case 'block':
				$annotation = $this->getBlockModel()->find($id);
				break;
			case 'program':
				$annotation = $this->getProgramModel()->find($id);
				break;
			default:
				throw new Exception('Annotation model not found!');
				break;
		}

		return $annotation;
	}

	/**
	 * @param  ActiveRow $annotation
	 * @return ActiveRow
	 */
	public function findParentProgram(ActiveRow $annotation): ActiveRow
	{
		if($annotation->block) {
			$parentProgram = $this->getBlockModel()->find($annotation->block);
		} else {
			$parentProgram = $annotation;
		}

		return $parentProgram;
	}

	public function update($updatedItems)
	{
		dd($updatedItems);
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
