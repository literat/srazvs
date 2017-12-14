<?php

namespace App\Components;

use App\Models\BlockModel;

class PublicBlockDetailControl extends BaseControl
{

	const TEMPLATE_NAME = 'PublicBlockDetail';

	/**
	 * @var BlockModel
	 */
	private $blockModel;

	/**
	 * @param BlockModel $model
	 */
	public function __construct(BlockModel $model)
	{
		$this->setBlockModel($model);
	}

	/**
	 * @return void
	 */
	public function render($blockId)
	{
		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->block = $this->getBlockModel()->find($blockId);
		$template->render();
	}

	/**
	 * @return BlockModel
	 */
	protected function getBlockModel(): BlockModel
	{
		return $this->blockModel;
	}

	/**
	 * @param BlockModel $model
	 * @return self
	 */
	protected function setBlockModel(BlockModel $model): self
	{
		$this->blockModel = $model;

		return $this;
	}

}
