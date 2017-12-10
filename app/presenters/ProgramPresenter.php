<?php

namespace App\Presenters;

use App\Models\ProgramModel;
use App\Models\BlockModel;
use App\Models\MeetingModel;
use App\Services\Emailer;
use Tracy\Debugger;
use App\Repositories\ProgramRepository;
use App\Components\Forms\Factories\IProgramFormFactory;
use App\Components\Forms\ProgramForm;

/**
 * Program controller
 *
 * This file handles the retrieval and serving of blocks
 *
 * @created 2013-06-05
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class ProgramPresenter extends BasePresenter
{

	/**
	 * @var integer
	 */
	private $programId = null;

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
	 * Prepare model classes and get meeting id
	 */
	public function __construct(
		ProgramModel $model,
		Emailer $emailer,
		BlockModel $blockModel,
		MeetingModel $meetingModel,
		ProgramRepository $programRepository
	) {
		$this->setModel($model);
		$this->setEmailer($emailer);
		$this->setBlockModel($blockModel);
		$this->setMeetingModel($meetingModel);
		$this->setProgramRepository($programRepository);
	}

	/**
	 * @param  IProgramFormFactory $factory
	 */
	public function injectProgramFormFactory(IProgramFormFactory $factory)
	{
		$this->programFormFactory = $factory;
	}

	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();

		$meetingId = $this->getMeetingId();
		$this->getModel()->setMeetingId($meetingId);
		$this->getMeetingModel()->setMeetingId($meetingId);
		$this->getBlockModel()->setMeetingId($meetingId);
	}

	/**
	 * @return void
	 */
	public function actionCreate()
	{
		$model = $this->getModel();
		$data = $this->getHttpRequest()->getPost();

		$this->setBacklink($data['backlink']);
		unset($data['backlink']);

		if(!array_key_exists('display_in_reg', $data)) {
			$data['display_in_reg'] = 1;
		}

		try {
			$result = $this->getModel()->create($data);

			Debugger::log('Creation of program successfull, result: ' . json_encode($result), Debugger::INFO);

			$this->flashMessage('Položka byla úspěšně vytvořena', 'ok');
		} catch(Exception $e) {
			Debugger::log('Creation of program with data ' . json_encode($data) . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);

			$this->flashMessage('Záznam se nepodařilo uložit, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect($this->getBacklink() ?: 'Program:listing');
	}

	/**
	 * @param  integer $id
	 * @return void
	 */
	public function actionUpdate($id)
	{
		$model = $this->getModel();
		$data = $this->getHttpRequest()->getPost();

		$this->setBacklink($data['backlink']);
		unset($data['backlink']);

		if(!array_key_exists('display_in_reg', $data)) {
			$data['display_in_reg'] = 1;
		}

		try {
			$result = $this->getModel()->update($id, $data);

			Debugger::log('Modification of program id ' . $id . ' with data ' . json_encode($data) . ' successfull, result: ' . json_encode($result), Debugger::INFO);

			$this->flashMessage('Položka byla úspěšně upravena.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Modification of program id ' . $id . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);

			$this->flashMessage('Modification of program id ' . $id . ' failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect($this->getBacklink() ?: 'Program:listing');
	}

	/**
	 * @param  integer  $id
	 * @return void
	 */
	public function actionDelete($id)
	{
		try {
			$result = $this->getModel()->delete($id);
			Debugger::log('Destroying of program successfull, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('Položka byla úspěšně smazána.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Destroying of program failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Smazání programu se nezdařilo, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Program:listing');
	}

	/**
	 * @return void
	 */
	public function actionMail($id)
	{
		try {
			$tutors = $this->getModel()->getTutor($id);
			$recipients = $this->parseTutorEmail($tutors);

			$this->getEmailer()->tutor($recipients, $tutors->guid, 'program');

			Debugger::log('Sending email to program tutor successfull, result: ' . json_encode($recipients) . ', ' . $tutors->guid, Debugger::INFO);
			$this->flashMessage('Email lektorovi byl odeslán..', 'ok');
		} catch(Exception $e) {
			Debugger::log('Sending email to program tutor failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Email lektorovi nebyl odeslán, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Program:edit', $id);
	}

	/**
	 * View public program
	 *
	 * @return void
	 */
	public function renderPublic()
	{
		$this->getMeetingModel()->setRegistrationHandlers($this->getMeetingId());

		$template = $this->getTemplate();
		$template->meeting_heading = $this->getMeetingModel()->getRegHeading();
			////otevirani a uzavirani prihlasovani
		if(($this->getMeetingModel()->getRegOpening() < time()) || $this->getDebugMode()) {
			$template->display_program = true;
		} else {
			$template->display_program = false;
		}
		$template->public_program = $this->getMeetingModel()->renderPublicProgramOverview($this->getMeetingId());
		$template->page_title = 'Srazy VS - veřejný program';
		$template->style = 'table { border-collapse:separate; width:100%; }
				td { .width:100%; text-align:center; padding:0px; }
				td.day { border:1px solid black; background-color:#777777; width:80px; }
				td.time { background-color:#cccccc; width:80px; }';
	}

	/**
	 * @return void
	 */
	public function renderListing()
	{
		$model = $this->getModel();
		$template = $this->getTemplate();

		$template->programs = $model->all();
		$template->mid = $this->meetingId;
		$template->heading = $this->heading;
	}

	/**
	 * @return void
	 */
	public function renderNew()
	{
		$template = $this->getTemplate();

		$template->heading = 'nový program';
		$template->page = $this->getHttpRequest()->getQuery('page');
		$template->error_name = '';
		$template->error_description = '';
		$template->error_tutor = '';
		$template->error_email = '';
		$template->error_material = '';
		$template->display_in_reg_checkbox = $this->renderHtmlCheckBox('display_in_reg', 0, 1);
		$template->block_select = $this->getBlockModel()->renderHtmlSelect(null);
		$template->selectedCategory	= null;
	}

	/**
	 * @param  integer $id
	 * @return void
	 */
	public function renderEdit($id)
	{
		$this->programId = $id;
		$program = $this->getModel()->find($id);

		$template = $this->getTemplate();
		$template->heading = 'úprava programu';
		$template->error_name = '';
		$template->error_description = '';
		$template->error_tutor = '';
		$template->error_email = '';
		$template->error_material = '';
		$template->display_in_reg_checkbox = $this->renderHtmlCheckBox('display_in_reg', 1, $program->display_in_reg);
		$template->block_select = $this->getBlockModel()->renderHtmlSelect($program->block);
		$template->selectedCategory	= $program->category;
		$template->program_visitors = $this->getModel()->getProgramVisitors($id);
		$template->program = $program;
		$template->id = $id;
	}

	/**
	 * @return ProgramForm
	 */
	protected function createComponentProgramForm(): ProgramForm
	{
		$control = $this->programFormFactory->create();
		$control->setMeetingId($this->getMeetingId());
		$type = $this->getParameter('type');
		$control->onProgramSave[] = function(ProgramForm $control, $program) use ($type) {
			try {
				$guid = $this->getParameter('guid');

				$this->setBacklink($program['backlink']);
				unset($program['backlink']);

				if($guid) {
					$result = $this->getProgramRespository()->update($program);
				} else {
					$result = $this->getProgramRepository()->create($program);
				}

				$this->logInfo('Modification of program id %s with data %s successfull, result: %s',	[
					$program->guid,
					json_encode($program),
					json_encode($result),
				]);

				$this->flashSuccess('Položka byla úspěšně upravena');
			} catch(Exception $e) {
				$this->logError("Modification of program id {$program->guid} failed, result: {$e->getMessage()}");

				$this->flashError("Modification of program id {$program->guid} failed, result: {$e->getMessage()}");
			}

			$this->redirect($this->getBacklink() ?: 'Program:listing');
		};

		return $control;
	}

	/**
	 * @return Emailer
	 */
	protected function getEmailer()
	{
		return $this->emailer;
	}

	/**
	 * @param  Emailer $emailer
	 * @return $this
	 */
	protected function setEmailer(Emailer $emailer)
	{
		$this->emailer = $emailer;
		return $this;
	}

	/**
	 * @return BlockModel
	 */
	protected function getBlockModel()
	{
		return $this->blockModel;
	}

	/**
	 * @param  BlockModel $blockModel
	 * @return $this
	 */
	protected function setBlockModel(BlockModel $blockModel)
	{
		$this->blockModel = $blockModel;
		return $this;
	}

	/**
	 * @return MeetingModel
	 */
	protected function getMeetingModel(): MeetingModel
	{
		return $this->meetingModel;
	}

	/**
	 * @param  MeetingModel $model
	 * @return $this
	 */
	protected function setMeetingModel(MeetingModel $model): self
	{
		$this->meetingModel = $model;

		return $this;
	}

	/**
	 * @return ProgramRepository
	 */
	protected function getProgramRepository(): ProgramRepository
	{
		return $this->programRepository;
	}

	/**
	 * @param  ProgramRepository $model
	 * @return $this
	 */
	protected function setProgramRepository(ProgramRepository $repository):self
	{
		$this->programRepository = $repository;

		return $this;
	}

}
