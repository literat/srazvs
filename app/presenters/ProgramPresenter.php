<?php

namespace App\Presenters;

use App\Components\CategoryStylesControl;
use App\Components\Forms\Factories\IProgramFormFactory;
use App\Components\Forms\ProgramForm;
use App\Components\IProgramOverviewControl;
use App\Components\ProgramOverviewControl;
use App\Components\ProgramVisitorsControl;
use App\Components\PublicProgramOverviewControl;
use App\Models\BlockModel;
use App\Models\MeetingModel;
use App\Repositories\ProgramRepository;
use App\Services\Emailer;
use Nette\Utils\ArrayHash;

class ProgramPresenter extends BasePresenter
{

	/**
	 * @var Emailer
	 */
	private $emailer;

	/**
	 * @var BlockModel
	 */
	private $blockModel;

	/**
	 * @var MeetingModel
	 */
	private $meetingModel;

	/**
	 * @var ProgramRepository
	 */
	private $programRepository;

	/**
	 * @var IProgramFormFactory
	 */
	private $programFormFactory;

	/**
	 * @var ProgramOverviewControl
	 */
	private $programOverview;

	/**
	 * @var CategoryStylesControl
	 */
	private $categoryStyles;

	/**
	 * @var ProgramVisitorsControl
	 */
	private $programVisitors;

	/**
	 * @var int
	 */
	protected $programId;

	public function __construct(
		Emailer $emailer,
		BlockModel $blockModel,
		MeetingModel $meetingModel,
		ProgramRepository $programRepository,
		PublicProgramOverviewControl $publicProgramOverview,
		CategoryStylesControl $categoryStyles,
		ProgramVisitorsControl $programVisitors
	) {
		$this->setEmailer($emailer);
		$this->setBlockModel($blockModel);
		$this->setMeetingModel($meetingModel);
		$this->setProgramRepository($programRepository);
		$this->setProgramOverviewControl($publicProgramOverview);
		$this->setCategoryStylesControl($categoryStyles);
		$this->setProgramVisitorsControl($programVisitors);
	}

	/**
	 * @param IProgramFormFactory $factory
	 */
	public function injectProgramFormFactory(IProgramFormFactory $factory)
	{
		$this->programFormFactory = $factory;
	}

	public function startup()
	{
		parent::startup();

		$meetingId = $this->getMeetingId();
		$this->getProgramRepository()->setMeetingId($meetingId);
		$this->getMeetingModel()->setMeetingId($meetingId);
		$this->getBlockModel()->setMeetingId($meetingId);
	}

	/**
	 * Stores program into storage.
	 *
	 * @param  ArrayHash                         $program
	 * @return boolean
	 * @throws \Nette\Application\AbortException
	 */
	public function actionCreate(ArrayHash $program)
	{
		$this->allowAdminAccessOnly();
		try {
			$result = false;
			$this->logInfo('Storing new program.');

			$result = $this->getProgramRepository()->create($program);

			$this->logInfo(
				'Storing of new program with data %s successfull, result: %s',
				[
					json_encode($program),
					json_encode($result),
				]
			);

			$this->flashSuccess('Položka byla úspěšně vytvořena');
		} catch (\Exception $e) {
			$this->logError(
				'Creation of program with data %s failed, result: %s',
				[
					json_encode($program),
					$e->getMessage()
				]
			);

			$this->flashError('Položku se nepodařilo vytvořit.');
		}

		return $result;
	}

	/**
	 * Updates program in storage.
	 *
	 * @param  int                               $id
	 * @param  ArrayHash                         $program
	 * @return boolean
	 * @throws \Nette\Application\AbortException
	 */
	public function actionUpdate(int $id, ArrayHash $program)
	{
		$this->allowAdminAccessOnly();
		try {
			$this->logInfo('Updating program(%s).', [$id]);

			$result = $this->getProgramRepository()->update($id, $program);

			$this->logInfo('Updating of program(%s) with data %s successfull, result: %s', [
				$id,
				json_encode($program),
				json_encode($result),
			]);

			$this->flashSuccess('Položka byla úspěšně upravena');
		} catch (\Exception $e) {
			$this->logError('Updating of program(%s) failed, result: %s', [
				$program->guid,
				$e->getMessage(),
			]);

			$this->flashError('Položku se nepodařilo upravit.');
		}

		return $result;
	}

	/**
	 * @param  integer                           $id
	 * @return void
	 * @throws \Nette\Application\AbortException
	 */
	public function actionDelete($id)
	{
		$this->allowAdminAccessOnly();
		try {
			$result = $this->getProgramRepository()->delete($id);

			$this->logInfo('Destroying of program successfull, result: %s', [
				json_encode($result)
			]);
			$this->flashSuccess('Položka byla úspěšně smazána.');
		} catch (\Exception $e) {
			$this->logError(
				'Destroying of program failed, result: %s',
				[
					$e->getMessage()
				]
			);
			$this->flashError(sprintf('Smazání programu se nezdařilo, result: %s', $e->getMessage()));
		}

		$this->redirect('Program:listing');
	}

	public function actionMail($id)
	{
		$this->allowAdminAccessOnly();
		try {
			$tutors = $this->getProgramRepository()->findTutor($id);
			$recipients = $this->parseTutorEmail($tutors);

			$this->getEmailer()->tutor($recipients, $tutors->guid, 'program');

			$this->logInfo(
				'Sending email to program tutor successfull, result: %s, %s',
				[
					json_encode($recipients),
					$tutors->guid
				]
			);
			$this->flashSuccess('Email lektorovi byl odeslán.');
		} catch (\Exception $e) {
			$this->logError(
				'Sending email to program tutor failed, result: %s',
				[
					$e->getMessage()
				]
			);
			$this->flashError(sprintf('Email lektorovi nebyl odeslán, result: %s', $e->getMessage()));
		}

		$this->redirect('Program:edit', $id);
	}

	/**
	 * View public program.
	 *
	 * @return void
	 */
	public function renderPublic()
	{
		$this->getMeetingModel()->setRegistrationHandlers($this->getMeetingId());

		$template = $this->getTemplate();
		$template->meeting_heading = $this->getMeetingModel()->getRegHeading();
		////otevirani a uzavirani prihlasovani
		if (($this->getMeetingModel()->getRegOpening() < time()) || $this->getDebugMode()) {
			$template->display_program = true;
		} else {
			$template->display_program = false;
		}
		$template->page_title = 'Srazy VS - veřejný program';
		$template->style = 'table { border-collapse:separate; width:100%; }
				td { .width:100%; text-align:center; padding:0px; }
				td.day { border:1px solid black; background-color:#777777; width:80px; }
				td.time { background-color:#cccccc; width:80px; }';
	}

	public function renderListing()
	{
		$this->allowAdminAccessOnly();
		$template = $this->getTemplate();
		$template->programs = $this->getProgramRepository()->all();
		$template->mid = $this->meetingId;
		$template->heading = $this->heading;
	}

	public function renderNew()
	{
		$this->allowAdminAccessOnly();
		$template = $this->getTemplate();
		$template->heading = 'nový program';
	}

	/**
	 * @param  integer                           $id
	 * @return void
	 * @throws \Nette\Application\AbortException
	 */
	public function renderEdit($id)
	{
		$this->allowAdminAccessOnly();
		$this->programId = $id;
		$program = $this->getProgramRepository()->find($id);

		$template = $this->getTemplate();
		$template->heading = 'úprava programu';
		$template->program = $program;
		$template->id = $id;

		$this['programForm']->setDefaults($program);
	}

	protected function createComponentProgramForm(): ProgramForm
	{
		$control = $this->programFormFactory->create();
		$control->setMeetingId($this->getMeetingId());
		$control->onProgramSave[] = function ($program) {
			//$guid = $this->getParameter('guid');
			$id = $this->getParameter('id');

			$this->setBacklinkFromArray($program);

			if ($id) {
				$this->actionUpdate($id, $program);
			} else {
				$this->actionCreate($program);
			}

			$this->redirect($this->getBacklink() ?: 'Program:listing');
		};

		$control->onProgramReset[] = function ($program) {
			$this->setBacklinkFromArray($program);

			$this->redirect($this->getBacklink() ?: 'Program:listing');
		};

		return $control;
	}

	protected function createComponentProgramOverview(): IProgramOverviewControl
	{
		return $this->programOverview->setMeetingId($this->getMeetingId());
	}

	protected function createComponentCategoryStyles(): CategoryStylesControl
	{
		return $this->categoryStyles;
	}

	protected function createComponentProgramVisitors(): ProgramVisitorsControl
	{
		return $this->programVisitors;
	}

	protected function setProgramOverviewControl(IProgramOverviewControl $control): self
	{
		$this->programOverview = $control;

		return $this;
	}

	protected function getEmailer(): Emailer
	{
		return $this->emailer;
	}

	protected function setEmailer(Emailer $emailer): self
	{
		$this->emailer = $emailer;

		return $this;
	}

	protected function getBlockModel(): BlockModel
	{
		return $this->blockModel;
	}

	protected function setBlockModel(BlockModel $blockModel): self
	{
		$this->blockModel = $blockModel;

		return $this;
	}

	protected function getMeetingModel(): MeetingModel
	{
		return $this->meetingModel;
	}

	protected function setMeetingModel(MeetingModel $model): self
	{
		$this->meetingModel = $model;

		return $this;
	}

	protected function getProgramRepository(): ProgramRepository
	{
		return $this->programRepository;
	}

	protected function setProgramRepository(ProgramRepository $repository): self
	{
		$this->programRepository = $repository;

		return $this;
	}

	public function setCategoryStylesControl(CategoryStylesControl $control): self
	{
		$this->categoryStyles = $control;

		return $this;
	}

	public function setProgramVisitorsControl(ProgramVisitorsControl $control): self
	{
		$this->programVisitors = $control;

		return $this;
	}
}
