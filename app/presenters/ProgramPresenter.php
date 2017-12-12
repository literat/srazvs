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
use Nette\Utils\ArrayHash;

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
		$template = $this->getTemplate();
		$template->programs = $this->getProgramRepository()->all();
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
	}

	/**
	 * @param  integer $id
	 * @return void
	 */
	public function renderEdit($id)
	{
		$this->programId = $id;
		$program = $this->getProgramRepository()->find($id);

		$template = $this->getTemplate();
		$template->heading = 'úprava programu';
		$template->program_visitors = $this->getModel()->getProgramVisitors($id);
		$template->program = $program;
		$template->id = $id;

		$this['programForm']->setDefaults($program);
	}

	/**
	 * Stores program into storage
	 *
	 * @param  Nette\Utils\ArrayHash  $program
	 * @return boolean
	 */
	protected function store(ArrayHash $program)
	{
		try {
			$this->logInfo('Storing new program.');

			$result = $this->getProgramRepository()->create($program);

			$this->logInfo('Storing of new program with data %s successfull, result: %s', [
				json_encode($program),
				json_encode($result),
			]);

			$this->flashSuccess('Položka byla úspěšně vytvořena');
		} catch(Exception $e) {
			$this->logError("Storing of new program failed, result: {$e->getMessage()}");
			$this->flashError('Položku se nepodařilo vytvořit.');
		}

		return $result;
	}

	/**
	 * Updates program in storage
	 *
	 * @param  int                    $id
	 * @param  Nette\Utils\ArrayHash  $program
	 * @return boolean
	 */
	protected function update(int $id, ArrayHash $program)
	{
		try {
			$this->logInfo('Updating program(%s).', [$id]);

			$result = $this->getProgramRepository()->update($id, $program);

			$this->logInfo('Updating of program(%s) with data %s successfull, result: %s', [
				$id,
				json_encode($program),
				json_encode($result),
			]);

			$this->flashSuccess('Položka byla úspěšně upravena');
		} catch(Exception $e) {
			$this->logError('Updating of program(%s) failed, result: %s', [
				$program->guid,
				$e->getMessage(),
			]);
			$this->flashError('Položku se nepodařilo upravit.');
		}

		return $result;
	}

	/**
	 * @return ProgramForm
	 */
	protected function createComponentProgramForm(): ProgramForm
	{
		$control = $this->programFormFactory->create();
		$control->setMeetingId($this->getMeetingId());
		$control->onProgramSave[] = function(ProgramForm $control, $program) {
			//$guid = $this->getParameter('guid');
			$id = $this->getParameter('id');

			$this->setBacklink($program['backlink']);
			unset($program['backlink']);

			if($id) {
				$this->update($id, $program);
			} else {
				$this->store($program);
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
