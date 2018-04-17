<?php

namespace App\Components;

use App\Repositories\ProgramRepository;

class ProgramVisitorsControl extends BaseControl
{
	const TEMPLATE_NAME = 'ProgramVisitors';

	/**
	 * @var ProgramRepository
	 */
	private $programRepository;

	/**
	 * @param ProgramRepository $repository
	 */
	public function __construct(ProgramRepository $repository)
	{
		$this->setProgramRepository($repository);
	}

	/**
	 * @param  string $mealColumn
	 * @param  string $mealName
	 * @return void
	 */
	public function render(int $programId)
	{
		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->imgDir = IMG_DIR;
		$template->programId = $programId;
		$template->visitors = $this->getProgramRepository()->findVisitors($programId);
		$template->render();
	}

	/**
	 * @return ProgramRepository
	 */
	protected function getProgramRepository(): ProgramRepository
	{
		return $this->programRepository;
	}

	/**
	 * @param  ProgramRepository $repository
	 * @return self
	 */
	protected function setProgramRepository(ProgramRepository $repository): self
	{
		$this->programRepository = $repository;

		return $this;
	}
}
