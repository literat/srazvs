<?php

namespace App\Presenters;

use App\Models\MeetingModel;
use App\Components\ProgramOverviewControl;
use Nette\Http\Request;

/**
 * This file handles the retrieval and serving of news articles
 */
class DashboardPresenter extends BasePresenter
{

	/**
	 * @var ProgramOverviewControl
	 */
	private $programOverview;

	/**
	 * @param MeetingModel $model
	 * @param Request      $request
	 */
	public function __construct(
		MeetingModel $model,
		Request $request,
		ProgramOverviewControl $control
	) {
		$this->setModel($model);
		$this->setRequest($request);
		$this->setProgramOverviewControl($control);
	}

	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();

		$this->getModel()->setMeetingId($this->getMeetingId());
	}

	/**
	 * Render entire page
	 * @return void
	 */
	public function renderDefault()
	{
		$template = $this->getTemplate();
		//$template->render = $this->getModel()->renderProgramOverview();
		$template->data = $this->getModel()->find($this->getMeetingId());

		$template->error_start = "";
		$template->error_end = "";
		$template->error_open_reg = "";
		$template->error_close_reg = "";
		$template->error_login = "";
	}

	protected function createComponentProgramOverview()
	{
		return $this->programOverview->setMeetingId($this->getMeetingId());
	}

	protected function setProgramOverviewControl(ProgramOverviewControl $control)
	{
		$this->programOverview = $control;

		return $this;
	}

}
