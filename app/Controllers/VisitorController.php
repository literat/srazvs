<?php

/**
 * Visitor controller
 *
 * This file handles the retrieval and serving of visitors
 *
 * @author Tomas Litera
 * @copyright 2013-06-12 <tomaslitera@hotmail.com>
 * @package srazvs
 */
class VisitorController extends BaseController
{
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

		$this->templateDir = 'visitor';
		$this->page = 'visitor';

		$this->Container = new Container($GLOBALS['cfg'], $this->meetingId);
		$this->Visitor = $this->Container->createVisitor();
		$this->View = $this->Container->createView();
		$this->Emailer = $this->Container->createEmailer();
		$this->Export = $this->Container->createExport();
		$this->Meeting = $this->Container->createMeeting();
		$this->Meal = $this->Container->createMeal();
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
			case "delete":
				$this->delete($query_id);
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
			// pay full charge
			case "pay":
				$this->pay($query_id, 'cost');
				break;
			// pay advance	
			case "advance":
				$this->pay($query_id, 'advance');
				break;
			// searching
			case "search":
				if(isset($search)){
					$this->Visitor->setSearch($search);
				}
				break;
			// export all visitors to excel
			case "export":
				$this->Export->printVisitorsExcel();
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
			$db_data[$key] = $$key;	
		}
		
		if($this->Program->create($db_data)){	
			redirect("?".$this->page."&error=ok");
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
			if($key == 'display_in_reg' && $$key == '') {
				$value = 1;
			} else {
				$value = NULL;
			}
			$$key = requested($key, $value);
		}

		foreach($this->Program->dbColumns as $key) {
			$db_data[$key] = $$key;	
		}
		
		if($this->Program->update($id, $db_data)){	
			redirect("?".$this->page."&error=ok");
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
	 * Delete item by id
	 * 
	 * @param  int $id of item
	 * @return void
	 */
	private function delete($id)
	{
		if($this->Visitor->delete($id)) {	
			  	redirect("?visitor&error=del");
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
	 * Pay charge
	 * 
	 * @param  int 		$query_id     of visitors
	 * @param  string 	$payment_type cost|advance
	 * @return void
	 */
	private function pay($query_id, $payment_type)
	{
		if($return = $this->Visitor->payCharge($query_id, $payment_type)) {
			redirect("?".$this->page."&error=mail_send");
		} else {
			if($return == 'already_paid') {
				$error = $return;	
			} else {
				echo 'Došlo k chybě při odeslání e-mailu.';
				echo 'Chybová hláška: ' . $return;
			}
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

			$program_switcher = $this->Visitor->renderProgramSwitcher($this->meetingId, $this->itemId);
			$meals_select = $this->Meal->renderHtmlMealsSelect($this->mealData, $this->disabled);
			$province_select = $this->Meeting->renderHtmlProvinceSelect($this->data['province']);
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
		$this->View->assign('render',	$this->Visitor->getData());
		$this->View->assign('mid',		$this->meetingId);
		$this->View->assign('page',		$this->page);

		$this->View->assign('visitor-count',	$this->Visitor->getCount());
		$this->View->assign('meeting-price',	$this->Visitor->meeting_price);
		$this->View->assign('search',			$this->Visitor->search);

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
			$this->View->assign('program_switcher',	$program_switcher);
			
			$this->View->assign('error_name',			printError($error_name));
			$this->View->assign('error_surname',		printError($error_surname));
			$this->View->assign('error_nick',			printError($error_nick));
			$this->View->assign('error_email',			printError($error_email));
			$this->View->assign('error_postal_code',	printError($error_postal_code));
			$this->View->assign('error_surname',		printError($error_surname));
			$this->View->assign('error_group_num',		printError($error_group_num));
			$this->View->assign('error_bill',			printError($error_bill));
		}

		$this->View->render(TRUE);

		/* Footer */
		$this->View->loadTemplate('footer');
		$this->View->render(TRUE);
	}
}