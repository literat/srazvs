<?php

/**
 * Registration controller
 *
 * This file handles the registration of visitors
 *
 * @author Tomas Litera
 * @copyright 2013-06-12 <tomaslitera@hotmail.com>
 * @package srazvs
 */
class RegistrationController extends BaseController
{
	/**
	 * template
	 * @var string
	 */
	protected $template = 'form';
	
	/**
	 * Object farm container
	 * @var Container
	 */
	private $Container;

	/**
	 * Visitor model
	 * @var VisitorModel
	 */
	private $Visitor;

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
	 * Meeting class
	 * @var Meeting
	 */
	private $Meeting;

	/**
	 * Meal class
	 * @var Meal
	 */
	private $Meal;

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

		$this->templateDir = 'registration';
		$this->page = 'registration';

		$this->Container = new Container($GLOBALS['cfg'], $this->meetingId);
		$this->Visitor = $this->Container->createVisitor();
		$this->View = $this->Container->createView();
		$this->Emailer = $this->Container->createEmailer();
		$this->Export = $this->Container->createExport();
		$this->Meeting = $this->Container->createMeeting();
		$this->Meal = $this->Container->createMeal();

		if(defined('DEBUG') && DEBUG === TRUE){
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
	public function init(array $getVars)
	{
		######################### PRISTUPOVA PRAVA ################################

		include_once(INC_DIR.'access.inc.php');

		###########################################################################

		$id = requested("id",$this->itemId);
		$this->cms = requested("cms","");
		$this->error = requested("error","");
		$this->page = requested("page","");
		$search = requested("search", "");
		$this->disabled = requested("disabled", "");

		if(isset($_POST['checker'])){
			$id = $_POST['checker'];
			$query_id = NULL;
			foreach($id as $key => $value) {
				$query_id .= $value.',';
			}
			$query_id = rtrim($query_id, ',');
		}
		else {
			$query_id = $id;	
		}

		switch($this->cms) {
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
			default:
				$this->__new();
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
		
		// requested for meals
		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = requested($var_name, "ne");
			$this->mealData[$var_name] = $$var_name;
		}
	
		// requested for visitors fields
		foreach($this->Visitor->dbColumns as $key) {
			if($key == 'bill') $value = 0;
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
		// TODO
		////ziskani zvolenych programu
		$blockSql = "SELECT 	id
					 FROM kk_blocks
					 WHERE meeting='".$this->meetingId."' AND program='1' AND deleted='0'";
		$blockResult = mysql_query($blockSql);
		while($blockData = mysql_fetch_assoc($blockResult)){
			$$blockData['id'] = requested($blockData['id'],0);
			$programs_data[$blockData['id']] = $$blockData['id'];
			//echo $blockData['id'].":".$$blockData['id']."|";
		}

		// requested for visitors
		foreach($this->Visitor->dbColumns as $key) {
				if($key == 'bill') $$key = requested($key, 0);
				else $$key = requested($key, null);
				$DB_data[$key] = $$key;	
		}

		// i must add visitor's ID because it is empty
		$DB_data['meeting'] = $this->meetingId;

		// requested for meals
		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = requested($var_name, null);
			$meals_data[$var_name] = $$var_name;
		}
		// create
		if($this->Visitor->create($DB_data, $meals_data, $programs_data)){	
			redirect("?page=".$this->page."&error=ok");
		} else {
			redirect("?page=".$this->page."&error=error");
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
		// TODO
		////ziskani zvolenych programu
		$blockSql = "SELECT 	id
					 FROM kk_blocks
					 WHERE meeting='".$this->meetingId."' AND program='1' AND deleted='0'";
		$blockResult = mysql_query($blockSql);
		while($blockData = mysql_fetch_assoc($blockResult)){
			$$blockData['id'] = requested($blockData['id'],0);
			$programs_data[$blockData['id']] = $$blockData['id'];
			//echo $blockData['id'].":".$$blockData['id']."|";
		}

		foreach($this->Visitor->dbColumns as $key) {
				if($key == 'bill') $$key = requested($key, 0);
				else $$key = requested($key, null);
				$DB_data[$key] = $$key;	
		}

		// i must add visitor's ID because it is empty
		$DB_data['meeting'] = $this->meetingId;

		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = requested($var_name, null);
			$meals_data[$var_name] = $$var_name;
		}
		// i must add visitor's ID because it is empty
		$meals_data['visitor'] = $id;

		if($this->Visitor->modify($id, $DB_data, $meals_data, $programs_data)){	
			redirect("?page=".$this->page."&error=ok");
		} else {
			redirect("?page=".$this->page."&error=error");
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

		$this->itemId = $id;
		
		$dbData = mysql_fetch_assoc($this->Visitor->getData($id));
		foreach($this->Visitor->dbColumns as $key) {
			$this->data[$key] = requested($key, $dbData[$key]);
		}

		$query = "SELECT	*
					FROM kk_meals
					WHERE visitor='".$this->itemId."'
					LIMIT 1"; 

		$DB_data = mysql_fetch_assoc(mysql_query($query));

		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = requested($var_name, $DB_data[$var_name]);
			$this->mealData[$var_name] = $$var_name;
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
		if($this->Emailer->tutor($pid, $this->meetingId, "program")) {
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
		$disabled = NULL;
		if(!empty($this->data)) {
			$error_name = "";
			$error_surname = "";
			$error_nick = "";
			$error_email = "";
			$error_postal_code = "";
			$error_group_num = "";
			$error_bill = "";
			$error_birthday = "";
			$error_street = "";
			$error_city = "";
			$error_group_name = "";

			$program_switcher = $this->Visitor->renderProgramSwitcher($this->meetingId, $this->itemId);
			$meals_select = $this->Meal->renderHtmlMealsSelect($this->mealData, $this->disabled);
			$province_select = $this->Meeting->renderHtmlProvinceSelect($this->data['province']);
		}

		/* Application Header */
		$this->View->loadTemplate('vodni_header');
		$this->View->assign('config',		$GLOBALS['cfg']);
		$this->View->assign('page_title',	"Registrace srazu VS");
		$this->View->render(TRUE);

		// load and prepare template
		$this->View->loadTemplate($this->templateDir.'/'.$this->template);
		$this->View->assign('heading',	$this->heading);
		$this->View->assign('todo',		$this->todo);
		$this->View->assign('error',	printError($this->error));
		
		$this->View->assign('cms',		$this->cms);
		$this->View->assign('render',	$this->Visitor->getData());
		$this->View->assign('mid',		$this->meetingId);
		$this->View->assign('page',		$this->page);

		$this->View->assign('meeting_heading',	$this->Meeting->getRegHeading());

		////otevirani a uzavirani prihlasovani
		if(($this->Meeting->getRegOpening() < time()) && (time() < $this->Meeting->getRegClosing()) || DEBUG === TRUE){
			$this->View->assign('disabled',				"");
			$this->View->assign('display_registration',	TRUE);
		} else {
			$this->View->assign('disabled',				"disabled");
			$this->View->assign('display_registration',	FALSE);
		}


		if(!empty($this->data)) {
			$this->View->assign('id',				$this->itemId);
			$this->View->assign('name',				$this->data['name']);
			$this->View->assign('surname',			$this->data['surname']);
			$this->View->assign('nick',				$this->data['nick']);
			$this->View->assign('email',			$this->data['email']);
			$this->View->assign('birthday',			$this->data['birthday']);
			$this->View->assign('street',			$this->data['street']);
			$this->View->assign('city',				$this->data['city']);
			$this->View->assign('postal_code',		$this->data['postal_code']);
			$this->View->assign('group_num',		$this->data['group_num']);
			$this->View->assign('group_name',		$this->data['group_name']);
			$this->View->assign('troop_name',		$this->data['troop_name']);
			$this->View->assign('province',			$province_select);
			$this->View->assign('meals',			$meals_select);
			$this->View->assign('arrival',			$this->data['arrival']);
			$this->View->assign('departure',		$this->data['departure']);
			$this->View->assign('comment',			$this->data['comment']);
			$this->View->assign('question',			$this->data['question']);
			$this->View->assign('bill',				$this->data['bill']);
			$this->View->assign('programs',			$program_switcher);
			
			$this->View->assign('error_name',			printError($error_name));
			$this->View->assign('error_surname',		printError($error_surname));
			$this->View->assign('error_nick',			printError($error_nick));
			$this->View->assign('error_email',			printError($error_email));
			$this->View->assign('error_postal_code',	printError($error_postal_code));
			$this->View->assign('error_surname',		printError($error_surname));
			$this->View->assign('error_group_num',		printError($error_group_num));
			$this->View->assign('error_bill',			printError($error_bill));
			$this->View->assign('error_birthday',		printError($error_birthday));
			$this->View->assign('error_street',			printError($error_street));
			$this->View->assign('error_city',			printError($error_city));
			$this->View->assign('error_group_name',		printError($error_group_name));

		}

		$this->View->render(TRUE);

		/* Footer */
		$this->View->loadTemplate('vodni_footer');
		$this->View->render(TRUE);
	}
}