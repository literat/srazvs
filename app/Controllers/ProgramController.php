<?php

/**
 * Program controller
 *
 * This file handles the retrieval and serving of blocks
 *
 * @created 2013-06-05
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class ProgramController extends BaseController
{
	/**
	 * This template variable will hold the 'this->View' portion of our MVC for this
	 * controller
	 */
	private $template = 'listing';

	/**
	 * Template directory
	 * @var string
	 */
	private $templateDir = 'program';

	/**
	 * ID of item
	 * @var integer
	 */
	private $programId = NULL;

	/**
	 * ID of meeting
	 * @var integer
	 */
	private $meetingId = 0;

	/**
	 * Error
	 * @var string
	 */
	private $error = '';
	/**
	 * Action command
	 * @var string
	 */
	private $cms;

	/**
	 * Page where to go
	 * @var string
	 */
	private $page = '';

	/**
	 * DB data
	 * @var array
	 */
	private $data = array();

	/**
	 * Object farm container
	 * @var Container
	 */
	private $container;

	/**
	 * Program model
	 * @var ProgramModel
	 */
	private $Program;

	/**
	 * View model
	 * @var View
	 */
	private $View;

	/**
	 * Emailer class
	 * @var Emailer
	 */
	private $Emailer;

	/**
	 * Export class
	 * @var Export
	 */
	private $Export;

	/**
	 * Category class
	 * @var Category
	 */
	private $Category;

	/**
	 * Meeting class
	 * @var Meeting
	 */
	private $Meeting;

	private $block;

	/**
	 * Prepare model classes and get meeting id
	 */
	public function __construct($database, $container)
	{
		$this->database = $database;
		$this->container = $container;
		$this->debugMode = $this->container->parameters['debugMode'];
		$this->router = $this->container->parameters['router'];
		$this->Program = $this->container->createServiceProgram();
		$this->block = $this->container->createServiceBlock();
		$this->View = $this->container->createServiceView();
		$this->Emailer = $this->container->createServiceEmailer();
		$this->Export = $this->container->createServiceExports();
		$this->Meeting = $this->container->createServiceMeeting();
		$this->Category = $this->container->createServiceCategory();

		if($this->meetingId = $this->requested('mid', '')){
			$_SESSION['meetingID'] = $this->meetingId;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}

		$this->Program->setMeetingId($this->meetingId);
		$this->Meeting->setMeetingId($this->meetingId);
		$this->Meeting->setHttpEncoding($this->container->parameters['encoding']);

		if($this->debugMode){
			$this->Meeting->setRegistrationHandlers(1);
			$this->meetingId = 1;
		} else {
			$this->Meeting->setRegistrationHandlers();
		}
	}

	/**
	 * This is the default function that will be called by Router.php
	 *
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init()
	{
		$id = $this->requested('id', $this->programId);
		$this->cms = $this->requested('cms', '');
		$this->error = $this->requested('error', '');
		$this->page = $this->requested('page', '');

		######################### PRISTUPOVA PRAVA ################################
		if(
			$this->cms != 'public'
			&& $this->cms != 'annotation'
			&& $this->page != 'annotation'
		) {
			include_once(INC_DIR.'access.inc.php');
		}
		###########################################################################

		switch($this->cms) {
			case "delete":
				$this->delete($id);
				break;
			case "new":
				$this->__new();
				break;
			case "create":
				$this->create();
				break;
			case "edit":
				$this->edit($id);
				break;
			case "modify":
				$this->update($id);
				break;
			case "mail":
				$this->mail();
				break;
			case 'export-visitors':
				$this->Export->printProgramVisitors($id);
				break;
			case "public":
				$this->publicView();
				break;
			case "annotation":
				$formkey = intval($this->requested('formkey', ''));
				$this->annotation($formkey);
				break;
		}

		$this->render();
	}

	/**
	 * Prepare page for new item
	 *
	 * @return void
	 */
	private function __new()
	{
		$this->template = 'form';

		$this->heading = "nový program";
		$this->todo = "create";

		foreach($this->Program->formNames as $key) {
				if($key == 'display_in_reg') $value = '1';
				if($key == 'capacity') $value = '0';
				else $value = "";
				$this->data[$key] = $this->requested($key, $value);
		}
	}

	/**
	 * Process data from form
	 *
	 * @return void
	 */
	private function create()
	{
		$postData = $this->router->getPost();

		foreach($this->Program->formNames as $key) {
				if(array_key_exists($key, $postData) && !is_null($postData[$key])) {
					$$key = $postData[$key];
				}
				elseif($key == 'display_in_reg') $$key = '1';
				elseif($key == 'capacity') $$key = '0';
				else $$key = '';
		}

		foreach($this->Program->dbColumns as $key) {
			$DB_data[$key] = $$key;
		}

		if($this->Program->create($DB_data)){
			redirect(PRJ_DIR.$this->page."?error=ok");
		}
	}


	/**
	 * Process data from editing
	 *
	 * @param  int 	$id 	of item
	 * @return void
	 */
	private function update($id = NULL)
	{
		$postData = $this->router->getPost();

		foreach($this->Program->formNames as $key) {
				if(array_key_exists($key, $postData) && !is_null($postData[$key])) {
					$$key = $postData[$key];
				}
				elseif($key == 'display_in_reg') $$key = '1';
				else $$key = '';
		}

		foreach($this->Program->dbColumns as $key) {
			$DB_data[$key] = $$key;
		}

		if($this->Program->update($id, $DB_data)) {

			if($this->page == 'annotation') {
				redirect("?cms=".$this->page."&error=ok&formkey=".$this->requested("formkey", "")."&type=".$this-´>requested("type", ""));
			} else {
				redirect(PRJ_DIR.$this->page."?error=ok");
			}
		}
	}

	/**
	 * Prepare data for editing
	 *
	 * @param  int $id of item
	 * @return void
	 */
	private function edit($id)
	{
		$this->template = 'form';

		$this->heading = "úprava programu";
		$this->todo = "modify";

		$this->programId = $id;

		$dbData = $this->Program->getData($id);

		foreach($this->Program->formNames as $key) {
			$this->data[$key] = $this->requested($key, $dbData[$key]);
		}
	}

	/**
	 * Delete item by id
	 *
	 * @param  int $id of item
	 * @return void
	 */
	private function delete($id)
	{
		if($this->Program->delete($id)) {
			  	redirect("?program&error=del");
		}
	}

	/**
	 * Send mail to tutor
	 *
	 * @return void
	 */
	private function mail()
	{
		$pid = $this->requested('pid', '');
		//$hash = $this->formKeyHash($pid, $this->meetingId);
		$tutors = $this->Program->getTutor($pid);
		$recipients = $this->parseTutorEmail($tutors);

		if($this->Emailer->tutor($recipients, $this->meetingId, 'program')) {
			redirect("?program&error=mail_send");
		} else {
			redirect("process.php?id=".$pid."&error=email&cms=edit");
		}
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
	 * Prepare data for annotation
	 *
	 * @param  int $id of item
	 * @return void
	 */
	private function annotation($formkey)
	{
		$this->template = 'annotation';

		$this->heading = "úprava programu";
		$this->todo = "modify";

		$mid = (($formkey - 39147) / 116)%100;
		$id = floor((($formkey - 39147) / 116) / 100);

		$this->Meeting->setRegistrationHandlers();
		$this->programId = $id;

		$dbData = $this->Program->getData($id);

		foreach($this->Program->formNames as $key) {
			$this->data[$key] = $this->requested($key, $dbData[$key]);
		}
		$this->data['formkey'] = $this->requested('formkey', '');
		$this->data['type'] = $this->requested('type', '');
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

			// category select box
			$cat_roll = $this->Category->renderHtmlSelect($this->data['category']);
			// blocks select box
			$block_select = $this->block->renderHtmlSelect($this->data['block']);
			// display in registration check box
			$display_in_reg_checkbox = $this->renderHtmlCheckBox('display_in_reg', 0, $this->data['display_in_reg']);
			// time select boxes
		}

		if($this->cms != 'public' && $this->cms != 'annotation') {
			/* HTTP Header */
			$this->View->loadTemplate('http_header');
			$this->View->assign('style',		$this->Category->getStyles());
			$this->View->render(TRUE);

			/* Application Header */
			$this->View->loadTemplate('header');
			$this->View->assign('user',		$this->getUser($_SESSION[SESSION_PREFIX.'user']));
			$this->View->assign('database',	$this->database);
			$this->View->assign('menu',		$this->generateMenu());
			$this->View->render(TRUE);
		}

		// load and prepare template
		$this->View->loadTemplate($this->templateDir.'/'.$this->template);
		$this->View->assign('heading',	$this->heading);
		$this->View->assign('todo',		$this->todo);
		$this->View->assign('error',	printError($this->error));

		$this->View->assign('cms',		$this->cms);
		$this->View->assign('render',	$this->Program->getData());
		$this->View->assign('mid',		$this->meetingId);
		$this->View->assign('page',		$this->page);
		$this->View->assign('css',		$this->Category->getStyles());

		if(!empty($this->data)) {
			$this->View->assign('id',						$this->programId);
			$this->View->assign('name',						$this->data['name']);
			$this->View->assign('description',				$this->data['description']);
			$this->View->assign('tutor',					$this->data['tutor']);
			$this->View->assign('email',					$this->data['email']);
			$this->View->assign('material',					$this->data['material']);
			$this->View->assign('capacity',					$this->data['capacity']);
			$this->View->assign('block',					$this->data['block']);
			$this->View->assign('category',					$this->data['category']);
			$this->View->assign('error_name',				printError($error_name));
			$this->View->assign('error_description',		printError($error_description));
			$this->View->assign('error_tutor',				printError($error_tutor));
			$this->View->assign('error_email',				printError($error_email));
			$this->View->assign('error_material',			printError($error_material));
			$this->View->assign('cat_roll',					$cat_roll);
			$this->View->assign('block_select',				$block_select);
			$this->View->assign('display_in_reg_checkbox',	$display_in_reg_checkbox);
			$this->View->assign('program_visitors',			$this->Program->getProgramVisitors($this->programId));
			$this->View->assign('page_title',				'Registrace programů pro lektory');
			$this->View->assign('meeting_heading',			$this->Meeting->getRegHeading());
			$this->View->assign('type',						isset($this->data['type']) ? $this->data['type'] : NULL);
			$this->View->assign('hash',						isset($this->data['formkey']) ? $this->data['formkey'] : NULL);
			$this->View->assign('formkey',					((int)$this->programId.$this->meetingId) * 116 + 39147);
		} elseif($this->cms == 'public') {
			$this->View->assign('meeting_heading',			$this->Meeting->getRegHeading());
			////otevirani a uzavirani prihlasovani
			if(($this->Meeting->getRegOpening() < time()) || $this->debugMode){
				$this->View->assign('display_program',	TRUE);
			} else {
				$this->View->assign('display_program',	FALSE);
			}
			$this->View->assign('public_program',		$this->Meeting->renderPublicProgramOverview());
			$this->View->assign('page_title',			'Srazy VS - veřejný program');
			$this->View->assign('style',				'table { border-collapse:separate; width:100%; }
														td { .width:100%; text-align:center; padding:0px; }
														td.day { border:1px solid black; background-color:#777777; width:80px; }
														td.time { background-color:#cccccc; width:80px; }'
			);
		} elseif($this->cms == 'annotation') {
			$this->View->assign('meeting_heading',			$this->Meeting->getRegHeading());
			////otevirani a uzavirani prihlasovani
			if(($this->Meeting->getRegOpening() < time()) || $this->debugMode){
				$this->View->assign('display_program',	TRUE);
			} else {
				$this->View->assign('display_program',	FALSE);
			}
			$this->View->assign('type',		$this->data['type']);
			$this->View->assign('formkey',	$this->data['formkey']);
		} else {

		}

		$this->View->render(TRUE);

		/* Footer */
		if($this->cms != 'public' && $this->cms != 'annotation') {
			$this->View->loadTemplate('footer');
			$this->View->render(TRUE);
		}
	}
}
