<?php

namespace App\Presenters;

use App\Models\ProgramModel;
use App\Models\BlockModel;
use App\Services\Emailer;
use Nette\Http\Request;
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
	 * Prepare model classes and get meeting id
	 */
	public function __construct(ProgramModel $model, Request $request, Emailer $emailer, BlockModel $blockModel)
	{
		$this->setModel($model);
		$this->setRequest($request);
		$this->setEmailer($emailer);
		$this->setBlockModel($blockModel);
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
	 * This is the default function that will be called by Router.php
	 *
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init()
	{
		$parameters = $this->getRouter()->getParameters();
		$this->setAction($parameters['action']);
		$id = $this->requested('id', $this->programId);
		$this->cms = $this->requested('cms', '');
		$this->error = $this->requested('error', '');
		$this->page = $this->requested('page', '');

		switch($parameters['action']) {
			case "public":
				$this->publicView();
				$this->render();
				break;
		}
	}

	/**
	 * @return void
	 */
	public function actionCreate()
	{
		$model = $this->getModel();
		$data = $this->getRequest()->getPost();
		$page = $data['page'];
		unset($data['page']);

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

		// TODO: redirect using page
		$this->redirect('Program:listing');
	}

	/**
	 * @param  integer $id
	 * @return void
	 */
	public function actionUpdate($id)
	{
		$model = $this->getModel();
		$data = $this->getRequest()->getPost();

		$page = $data['page'];
		unset($data['page']);

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

		// TODO: redirect using page
		$this->redirect('Program:listing');
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
	 * @param  integer $id
	 * @return void
	 */
	public function actionAnnotationupdate($id)
	{
		try {
			$data = $this->getRequest()->getPost();
			$result = $this->updateByGuid($id, $data);

			Debugger::log('Modification of program annotation id ' . $id . ' with data ' . json_encode($data) . ' successfull, result: ' . json_encode($result), Debugger::INFO);

			$this->flashMessage('Položka byla úspěšně upravena.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Modification of program annotation guid ' . $id . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);

			$this->flashMessage('Modification of program annotation guid ' . $id . ' failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Program:annotation', $id);
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
	private function publicView()
	{
		$this->template = 'view';
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
		$template->page = $this->getRequest()->getQuery('page');
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
		$template->page = $this->getRequest()->getQuery('page');
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
	 * @param  integer $id
	 * @return void
	 */
	public function renderAnnotation($id)
	{
		$template = $this->getTemplate();

		$template->page_title = 'Registrace programů pro lektory';
		$template->page = $this->getRequest()->getQuery('page');
		$template->error_name = "";
		$template->error_description = "";
		$template->error_tutor = "";
		$template->error_email = "";
		$template->error_material = "";

		$program = $this->getModel()->findBy('guid', $id);
		$this->programId = $program->id;
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
	 * Render all page
	 *
	 * @return void
	 */
	public function render()
	{
		$error = "";
		if(!empty($this->data)) {
			$error_name = "";
			$error_description = "";
			$error_tutor = "";
			$error_email = "";
			$error_material = "";

			// blocks select box
			$block_select = $this->getBlock()->renderHtmlSelect($this->data['block']);
			// display in registration check box
			$display_in_reg_checkbox = $this->renderHtmlCheckBox('display_in_reg', 0, $this->data['display_in_reg']);
			// time select boxes
		}

		$parameters = [
			'cssDir'	=> CSS_DIR,
			'jsDir'		=> JS_DIR,
			'imgDir'	=> IMG_DIR,
			'wwwDir'	=> HTTP_DIR,
			'expDir'	=> EXP_DIR,
			'progDir'	=> PROG_DIR,
			'error'		=> printError($this->error),
			'todo'		=> $this->todo,
			'action'		=> $this->getAction(),
			'render'	=> $this->getModel()->getData(),
			'mid'		=> $this->meetingId,
			'page'		=> $this->page,
			'heading'	=> $this->heading,
		];

		if($this->action != 'public' && $this->action != 'annotation') {
			$parameters = array_merge($parameters, [
				'style'		=> $this->getStyles(),
				'user'		=> $this->getSunlightUser($_SESSION[SESSION_PREFIX.'user']),
				'meeting'	=> $this->getPlaceAndYear($_SESSION['meetingID']),
				'menu'		=> $this->generateMenu(),
			]);
		}

		if(!empty($this->data)) {
			$parameters = array_merge($parameters, [
				'id'				=> $this->programId,
				'data'				=> $this->data,
				'error_name'		=> printError($error_name),
				'error_description'	=> printError($error_description),
				'error_tutor'		=> printError($error_tutor),
				'error_email'		=> printError($error_email),
				'error_material'	=> printError($error_material),
				'categories'		=> $this->getCategory()->all(),
				'selectedCategory'	=> $this->data['category'],
				'block_select'		=> $block_select,
				'program_visitors'	=> $this->getModel()->getProgramVisitors($this->programId),
				'display_in_reg_checkbox'	=> $display_in_reg_checkbox,
				'formkey'			=> ((int)$this->programId.$this->meetingId) * 116 + 39147,
				'meeting_heading'	=> $this->getMeeting()->getRegHeading(),
				'block'				=> $this->itemId,
				'page_title'		=> 'Registrace programů pro lektory',
				'type'				=> isset($this->data['type']) ? $this->data['type'] : NULL,
				'hash'				=> isset($this->data['formkey']) ? $this->data['formkey'] : NULL,
			]);
		}

		if($this->action == 'public') {
			$parameters['meeting_heading'] = $this->getMeeting()->getRegHeading();
			////otevirani a uzavirani prihlasovani
			if(($this->getMeeting()->getRegOpening() < time()) || $this->debugMode) {
				$parameters['display_program'] = true;
			} else {
				$parameters['display_program'] = false;
			}
			$parameters['public_program'] = $this->getMeeting()->renderPublicProgramOverview();
			$parameters['page_title'] = 'Srazy VS - veřejný program';
			$parameters['style'] = 'table { border-collapse:separate; width:100%; }
				td { .width:100%; text-align:center; padding:0px; }
				td.day { border:1px solid black; background-color:#777777; width:80px; }
				td.time { background-color:#cccccc; width:80px; }';
		} elseif($this->cms == 'annotation') {
			$parameters['meeting_heading'] = $this->getMeeting()->getRegHeading();
			////otevirani a uzavirani prihlasovani
			if(($this->getMeeting()->getRegOpening() < time()) || $this->debugMode) {
				$parameters['display_program'] = true;
			} else {
				$parameters['display_program'] = false;
			}
			$parameters['type'] = $this->data['type'];
			$parameters['formkey'] = $this->data['formkey'];
		}

		$this->latte->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);
	}
}
