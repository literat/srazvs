<?php

/**
 * Program controller
 *
 * This file handles the retrieval and serving of blocks
 *
 * @created 2013-06-05
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class ProgramController
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
	 * Heading of the page
	 * @var string
	 */
	private $heading = '';

	/**
	 * Action what to do next
	 * @var string
	 */
	private $todo = '';

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
	private $Container;

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
	 * Prepare model classes and get meeting id
	 */
	public function __construct()
	{
		if($this->meetingId = requested("mid","")){
			$_SESSION['meetingID'] = $this->meetingId;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}

		$this->Container = new Container($GLOBALS['cfg'], $this->meetingId);
		$this->Program = $this->Container->createProgram();
		$this->View = $this->Container->createView();
		$this->Emailer = $this->Container->createEmailer();
		$this->Export = $this->Container->createExport();
	}

	/**
	 * This is the default function that will be called by Router.php
	 * 
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init(array $getVars)
	{
		######################### PRISTUPOVA PRAVA ################################

		include_once(INC_DIR.'access.inc.php');

		###########################################################################

		$id = requested("id",$this->programId);
		$this->cms = requested("cms","");
		$this->error = requested("error","");
		$this->page = requested("page","");


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
				if($key == 'display_in_reg') $value = 1;
				else $value = "";
				$this->data[$key] = requested($key, $value);
		}
	}

	/**
	 * Process data from form
	 * 
	 * @return void
	 */
	private function create()
	{
		foreach($this->Program->formNames as $key) {
				if($key == 'display_in_reg') {
					$value = 0;
				} else {
					$value = NULL;
				}
				$$key = requested($key, $value);
		}

		foreach($this->Program->dbColumns as $key) {
			$DB_data[$key] = $$key;	
		}
		
		if($this->Program->create($DB_data)){	
			redirect("?page=".$this->page."&error=ok");
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
		foreach($this->Program->formNames as $key) {
			if($key == 'display_in_reg' && !isset($$key)) {
				$value = 1;
			} else {
				$value = NULL;
			}
			$$key = requested($key, $value);
		}


		foreach($this->Program->dbColumns as $key) {
			$DB_data[$key] = $$key;	
		}
		
		if($this->Program->update($id, $DB_data)){	
			redirect("?page=".$this->page."&error=ok");
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
		
		$dbData = mysql_fetch_assoc($this->Program->getData($id));
		
		foreach($this->Program->formNames as $key) {
			$this->data[$key] = requested($key, $dbData[$key]);
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
		$pid = requested("pid","");
		if($this->Emailer->tutor($pid, $mid, "program")) {
			redirect("?program&error=mail_send");
		}
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
			$cat_roll = CategoryModel::renderHtmlSelect($this->data['category']);
			// blocks select box
			$block_select = BlockModel::renderHtmlSelect($this->data['block']);
			// display in registration check box
			$display_in_reg_checkbox = Form::renderHtmlCheckBox('display_in_reg', 0, $this->data['display_in_reg']);
			// time select boxes
		}

		/* HTTP Header */
		$this->View->loadTemplate('http_header');
		$this->View->assign('config',		$GLOBALS['cfg']);
		$this->View->assign('style',		CategoryModel::getStyles());
		$this->View->render(TRUE);

		/* Application Header */
		$this->View->loadTemplate('header');
		$this->View->assign('config',		$GLOBALS['cfg']);
		$this->View->render(TRUE);

		// load and prepare template
		$this->View->loadTemplate($this->templateDir.'/'.$this->template);
		$this->View->assign('heading',	$this->heading);
		$this->View->assign('todo',		$this->todo);
		$this->View->assign('error',	printError($this->error));
		
		$this->View->assign('cms',		$this->cms);
		$this->View->assign('render',	$this->Program->getData());
		$this->View->assign('mid',		$this->meetingId);
		$this->View->assign('page',		$this->page);

		if(!empty($this->data)) {
			$this->View->assign('id',						$this->programId);
			$this->View->assign('name',						$this->data['name']);
			$this->View->assign('description',				$this->data['description']);
			$this->View->assign('tutor',					$this->data['tutor']);
			$this->View->assign('email',					$this->data['email']);
			$this->View->assign('material',					$this->data['material']);
			$this->View->assign('capacity',					$this->data['capacity']);
			$this->View->assign('error_name',				printError($error_name));
			$this->View->assign('error_description',		printError($error_description));
			$this->View->assign('error_tutor',				printError($error_tutor));
			$this->View->assign('error_email',				printError($error_email));
			$this->View->assign('error_material',			printError($error_material));
			$this->View->assign('cat_roll',					$cat_roll);
			$this->View->assign('block_select',				$block_select);
			$this->View->assign('display_in_reg_checkbox',	$display_in_reg_checkbox);
			$this->View->assign('program_visitors',			$this->Program->getProgramVisitors($this->programId));
		}

		$this->View->render(TRUE);

		/* Footer */
		$this->View->loadTemplate('footer');
		$this->View->render(TRUE);
	}
}