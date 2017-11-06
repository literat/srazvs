<?php

namespace App\Presenters;

use App\Models\ProgramModel;
use App\Models\BlockModel;
use App\Models\MeetingModel;
use App\Services\Emailer;
use Tracy\Debugger;

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
	 * Prepare model classes and get meeting id
	 */
	public function __construct(
		ProgramModel $model,
		Emailer $emailer,
		BlockModel $blockModel,
		MeetingModel $meetingModel
	) {
		$this->setModel($model);
		$this->setEmailer($emailer);
		$this->setBlockModel($blockModel);
		$this->setMeetingModel($meetingModel);
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

		try {
			if(array_key_exists('backlink', $data) && isset($data['backlink'])) {
				$this->setBacklink($data['backlink']);
				unset($data['backlink']);
			}

			if(!array_key_exists('display_in_reg', $data)) {
				$data['display_in_reg'] = 1;
			}

			$result = $this->getModel()->create($data);

			$this->logInfo('Creation of program successfull, result: %s', [
				json_encode($result)
			]);

			$this->flashSuccess('Položka byla úspěšně vytvořena');
		} catch(Exception $e) {
			$this->logError('Creation of program with data %s failed, result: %s', [
				json_encode($data),
				$e->getMessage()
			]);

			$this->flashError('Záznam se nepodařilo uložit, result: %s', [
				$e->getMessage()
			]);
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

		try {
			if(array_key_exists('backlink', $data) && isset($data['backlink'])) {
				$this->setBacklink($data['backlink']);
				unset($data['backlink']);
			}

			if(!array_key_exists('display_in_reg', $data)) {
				$data['display_in_reg'] = 1;
			}

			$result = $this->getModel()->update($id, $data);

			$this->logInfo('Modification of program id %s with data %s successfull, result: %s', [
				$id,
				json_encode($data),
				json_encode($result)
			]);

			$this->flashSuccess('Položka byla úspěšně upravena.');
		} catch(Exception $e) {
			$this->logError('Modification of program id %s failed, result: %s', [
				$id,
				$e->getMessage()
			]);

			$this->flashError('Modification of program id %s failed, result: %s', [
				$id,
				$e->getMessage()
			]);
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
			$this->logInfo('Destroying of program successfull, result: %s', [
				json_encode($result)
			]);
			$this->flashSuccess('Položka byla úspěšně smazána.');
		} catch(Exception $e) {
			$this->logError('Destroying of program failed, result: %s', [
				$e->getMessage()
			]);
			$this->flashError('Smazání programu se nezdařilo, result: %s', [
				$e->getMessage()
			]);
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

			$this->logInfo('Sending email to program tutor successfull, result: %s, %s', [
				json_encode($recipients),
				$tutors->guid
			]);
			$this->flashSuccess('Email lektorovi byl odeslán..');
		} catch(Exception $e) {
			$this->logError('Sending email to program tutor failed, result: %s', [
				$e->getMessage()
			]);
			$this->flashError('Email lektorovi nebyl odeslán, result: %s', [
				$e->getMessage()
			]);
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
	protected function getMeetingModel()
	{
		return $this->meetingModel;
	}

	/**
	 * @param  MeetingModel $model
	 * @return $this
	 */
	protected function setMeetingModel(MeetingModel $model)
	{
		$this->meetingModel = $model;
		return $this;
	}

}
