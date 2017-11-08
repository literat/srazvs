<?php

namespace App\Components;

use App\Models\BlockModel;

class PublicBlocksByDayControl extends BaseControl implements IBlocksByDayControl
{

	const TEMPLATE_NAME = 'PublicBlocksByDay';

	/**
	 * @var ProgramsControl
	 */
	private $programs;

	/**
	 * @var BlockModel
	 */
	private $blockModel;

	/**
	 * @var PublicBlockDetailControl
	 */
	private $publicBlockDetailControl;

	/**
	 * @param BlockModel               $model
	 * @param PublicProgramsControl    $programsControl
	 * @param PublicBlockDetailControl $blockDetailControl
	 */
	public function __construct(BlockModel $model, PublicProgramsControl $programsControl, PublicBlockDetailControl $blockDetailControl)
	{
		$this->setBlockModel($model);
		$this->setProgramsControl($programsControl);
		$this->setPublicBlockDetailControl($blockDetailControl);
	}

	/**
	 * @param  string $day
	 * @return void
	 */
	public function render($day)
	{
		$this->getBlockModel()->setMeetingId($this->getMeetingId());
		$blocks = $this->getBlockModel()->findByDay($day);

		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->blocks = $blocks;
		$template->render();
	}

	/**
	 * @return ProgramsControl
	 */
	protected function createComponentPublicPrograms()
	{
		return $this->programs;
	}

	/**
	 * @param  IProgramsControl $control
	 * @return $this
	 */
	protected function setProgramsControl(IProgramsControl $control)
	{
		$this->programs = $control;

		return $this;
	}

	/**
	 * @return PublicBlockDetailControl
	 */
	protected function createComponentPublicBlockDetail()
	{
		return $this->publicBlockDetailControl;
	}

	/**
	 * @param  IProgramsControl $control
	 * @return $this
	 */
	protected function setPublicBlockDetailControl(PublicBlockDetailControl $control)
	{
		$this->publicBlockDetailControl = $control;

		return $this;
	}

	/**
	 * @return BlockModel
	 */
	protected function getBlockModel()
	{
		return  $this->blockModel;
	}

	/**
	 * @param  BlockModel $model
	 * @return $this
	 */
	protected function setBlockModel(BlockModel $model)
	{
		$this->blockModel = $model;

		return $this;
	}

}
