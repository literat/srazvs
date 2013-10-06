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
	 * Recipients
	 * @var recipients
	 */
	private $recipients = NULL;

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
			case "massmail":
				$this->massmail($query_id);
				break;
			case "send":
				$this->send(requested("recipients", ""));
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

		$this->heading = "nový účastník";
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

		$this->heading = "úprava účastníka";
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
			  redirect("?error=del");
		}
	}

	/**
	 * Prepare mass mail form
	 * 
	 * @return void
	 */
	private function massmail($query_id)
	{
		$this->template = 'mail';

		$recipient_mails = $this->Visitor->getMail($query_id);	
		$this->recipients = rtrim($recipient_mails, "\n,");
	}

	/**
	 * Prepare mass mail form
	 * 
	 * @return void
	 */
	private function send($recipients)
	{
		$bcc_mail = preg_replace("/\n/", "", requested("recipients", ""));

		$recipient_name = $this->Visitor->configuration['mail-sender-name'];
		$recipient_mail = $this->Visitor->configuration['mail-sender-address'];

		$subject = requested("subject", "");

		// space to &nbsp;
		$message = str_replace(" ","&nbsp;", requested("message", ""));
		// new line to <br> and tags stripping
		$message = nl2br(strip_tags($message));

		$message = "<html><head><title>".$subject."</title></head><body>\n".$message."\n</body>\n</html>";
			
		$return = $this->Emailer->sendMail($recipient_mail, $recipient_name, $subject, $message, $bcc_mail);
			
		if($return){
			$error = 'E_MAIL_NOTICE';
			$error = 'mail_send';
			redirect("?error=".$error);
		}
		else {
			$error = 'E_MAIL_ERROR';
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

		$this->View->assign('recipient_mails',	$this->recipients);

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