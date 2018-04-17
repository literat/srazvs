<?php

namespace App\Presenters;

use App\Components\ProgramOverviewControl;
use App\Models\MeetingModel;

class DashboardPresenter extends BasePresenter
{

	/**
	 * @var ProgramOverviewControl
	 */
	private $programOverview;

	/**
	 * @param MeetingModel           $model
	 * @param ProgramOverviewControl $control
	 */
	public function __construct(
		MeetingModel $model,
		ProgramOverviewControl $control
	) {
		$this->setModel($model);
		$this->setProgramOverviewControl($control);
	}

	public function startup()
	{
		parent::startup();

		$this->getModel()->setMeetingId($this->getMeetingId());

		$this->allowAdminAccessOnly();
	}

	/**
	 * Render entire page.
	 *
	 * @return void
	 */
	public function renderDefault()
	{
		$template = $this->getTemplate();
		$template->data = $this->getModel()->find($this->getMeetingId());

		$template->error_start = "";
		$template->error_end = "";
		$template->error_open_reg = "";
		$template->error_close_reg = "";
		$template->error_login = "";
	}

	/**
	 * @return ProgramOverviewControl
	 */
	protected function createComponentProgramOverview()
	{
		return $this->programOverview->setMeetingId($this->getMeetingId());
	}

	/**
	 * @param  ProgramOverviewControl $control
	 * @return self
	 */
	protected function setProgramOverviewControl(ProgramOverviewControl $control): self
	{
		$this->programOverview = $control;

		return $this;
	}
}
