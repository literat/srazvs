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

	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();

		$this->getModel()->setMeetingId($this->getMeetingId());

		$user = $this->getUser();
		if($user->isLoggedIn() && !$user->isInRole('administrator')) {
		    $this->flashFailure('Nemáte oprávnění pro Dashboard.');
		    $this->redirect('Registration:default');
        }
	}

	/**
	 * Render entire page
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
	 * @return $this
	 */
	protected function setProgramOverviewControl(ProgramOverviewControl $control)
	{
		$this->programOverview = $control;

		return $this;
	}

}
