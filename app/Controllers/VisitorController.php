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
	private $container;

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
	 * Category class
	 * @var Category
	 */
	private $Category;

	/**
	 * Recipients
	 * @var recipients
	 */
	private $recipients = NULL;

	/**
	 * Prepare model classes and get meeting id
	 */
	public function __construct($database, $container)
	{
		$this->templateDir = 'visitor';
		$this->template = "listing";
		$this->page = 'visitor';

		$this->database = $database;
		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->Visitor = $this->container->createServiceVisitor();
		$this->View = $this->container->createServiceView();
		$this->Emailer = $this->container->createServiceEmailer();
		$this->Export = $this->container->createServiceExports();
		$this->Meeting = $this->container->createServiceMeeting();
		$this->Meal = $this->container->createServiceMeal();
		$this->Category = $this->container->createServiceCategory();

		if($this->meetingId = $this->requested('mid', '')) {
			$_SESSION['meetingID'] = $this->meetingId;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}

		$this->Visitor->setMeetingId($this->meetingId);
		$this->Meeting->setMeetingId($this->meetingId);
		$this->Meeting->setHttpEncoding($this->container->parameters['encoding']);
	}

	/**
	 * This is the default function that will be called by Router.php
	 *
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init()
	{
		######################### PRISTUPOVA PRAVA ################################

		include_once(INC_DIR.'access.inc.php');

		###########################################################################

		$id = $this->requested('id', $this->itemId);
		$this->cms = $this->requested('cms', '');
		$this->error = $this->requested('error', '');
		$this->page = $this->requested('page', '');
		$search = $this->requested('search', '');
		$this->disabled = $this->requested('disabled', '');

		if($ids = $this->requested('checker')) {
			$query_id = array();
			foreach($ids as $key => $value) {
				$query_id[] = $value;
			}
		} else {
			$query_id = $ids;
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
				$this->send($this->requested('recipients', ''));
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
			case "checked":
				$this->checked($id);
				break;
			case "unchecked":
				$this->unchecked($id);
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
			$$var_name = $this->requested($var_name, 'ne');
			$this->mealData[$var_name] = $$var_name;
		}

		// requested for visitors fields
		foreach($this->Visitor->dbColumns as $key) {
			if($key == 'bill') $value = 0;
			elseif($key == 'cost') $value = 0;
			else $value = "";
			$this->data[$key] = $this->requested($key, $value);
		}
	}

	private function getblocks()
	{
		return $this->database
			->table('kk_blocks')
			->select('id')
			->where('meeting ? AND program ? AND deleted ?', $this->meetingId, '1', '0')
			->fetchAll();
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
		$blocks = $this->getblocks();

		foreach($blocks as $blockData){
			$$blockData['id'] = $this->requested($blockData['id'], 0);
			$programs_data[$blockData['id']] = $$blockData['id'];
			//echo $blockData['id'].":".$$blockData['id']."|";
		}

		// requested for visitors
		foreach($this->Visitor->dbColumns as $key) {
				if($key == 'bill') $$key = $this->requested($key, 0);
				elseif($key == 'cost') $$key = $this->requested($key, 0);
				else $$key = $this->requested($key, null);
				$DB_data[$key] = $$key;
		}

		// i must add visitor's ID because it is empty
		$DB_data['meeting'] = $this->meetingId;
		$DB_data['hash'] = hash('sha1', microtime());

		// requested for meals
		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = $this->requested($var_name, null);
			$meals_data[$var_name] = $$var_name;
		}
		// create
		if($vid = $this->Visitor->create($DB_data, $meals_data, $programs_data)){
			######################## ODESILAM EMAIL ##########################

			// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
			$code4bank = $this->code4Bank($DB_data);
			$hash = ((int)$vid.$this->meetingId) * 147 + 49873;

			$recipient_mail = $DB_data['email'];
			$recipient_name = $DB_data['name']." ".$DB_data['surname'];
			$recipient = [$recipient_mail => $recipient_name];

			if($return = $this->Emailer->sendRegistrationSummary($recipient, $hash, $code4bank)) {
				if(is_int($vid)) {
					$vid = "ok";
				}
				redirect("?page=".$this->page."&error=ok");
			} else {
				redirect("?page=".$this->page."&error=error");
			}
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
		$blocks = $this->getblocks();

		foreach($blocks as $blockData){
			$$blockData['id'] = $this->requested($blockData['id'], 0);
			$programs_data[$blockData['id']] = $$blockData['id'];
		}

		foreach($this->Visitor->dbColumns as $key) {
				if($key == 'bill') $$key = $this->requested($key, 0);
				else $$key = $this->requested($key, null);
				$DB_data[$key] = $$key;
		}

		// i must add visitor's ID because it is empty
		$DB_data['meeting'] = $this->meetingId;

		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = $this->requested($var_name, null);
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

		$dbData = $this->Visitor->getData($id);
		foreach($this->Visitor->dbColumns as $key) {
			$this->data[$key] = $this->requested($key, $dbData[$key]);
		}

		$data = $this->database
			->table('kk_meals')
			->where('visitor', $this->itemId)
			->limit(1)
			->fetch();

		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = $this->requested($var_name, $data[$var_name]);
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
		$bcc_mail = preg_replace("/\n/", "", $this->requested('recipients', ''));

		$recipient_name = $this->Visitor->configuration['mail-sender-name'];
		$recipient_mail = $this->Visitor->configuration['mail-sender-address'];

		$subject = $this->requested('subject', '');

		// space to &nbsp;
		$message = str_replace(" ","&nbsp;", $this->requested('message', ''));
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
		$return = $this->Visitor->payCharge($query_id, $payment_type);

		if($return != 'already_paid') {
			$recipients = $this->Visitor->getRecipients($query_id);
			$this->Emailer->sendPaymentInfo($recipients, $payment_type);
			redirect("?".$this->page."&error=mail_send");
		} else {
			echo 'Došlo k chybě při odeslání e-mailu.';
			echo 'Chybová hláška: ' . $return;
		}
	}

	/**
	 * Set item as checked by id
	 *
	 * @param  int $id of item
	 * @return void
	 */
	private function checked($id)
	{
		if($this->Visitor->checked($id, '1')) {
			  redirect("?error=checked");
		}
	}

	/**
	 * Set item as unchecked by id
	 *
	 * @param  int $id of item
	 * @return void
	 */
	private function unchecked($id)
	{
		if($this->Visitor->checked($id, 0)) {
			  redirect("?error=unchecked");
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
			$error_cost = "";

			$program_switcher = $this->Visitor->renderProgramSwitcher($this->meetingId, $this->itemId);
			$meals_select = $this->Meal->renderHtmlMealsSelect($this->mealData, $this->disabled);
			$province_select = $this->Meeting->renderHtmlProvinceSelect($this->data['province']);
		}

		/* HTTP Header */
		$this->View->loadTemplate('http_header');
		$this->View->assign('style',		$this->Category->getStyles());
		$this->View->render(TRUE);

		/* Application Header */
		$this->View->loadTemplate('header');
		$this->View->assign('database',		$this->database);
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
			$this->View->assign('birthday',			(empty($this->data['birthday'])) ? '' : $this->data['birthday']->format('Y-m-d'));
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
			$this->View->assign('question2',		$this->data['question2']);
			$this->View->assign('bill',				$this->data['bill']);
			$this->View->assign('cost',				$this->data['cost']);
			$this->View->assign('checked',			$this->data['checked']);
			$this->View->assign('program_switcher',	$program_switcher);

			$this->View->assign('error_name',			printError($error_name));
			$this->View->assign('error_surname',		printError($error_surname));
			$this->View->assign('error_nick',			printError($error_nick));
			$this->View->assign('error_email',			printError($error_email));
			$this->View->assign('error_postal_code',	printError($error_postal_code));
			$this->View->assign('error_surname',		printError($error_surname));
			$this->View->assign('error_group_num',		printError($error_group_num));
			$this->View->assign('error_bill',			printError($error_bill));
			$this->View->assign('error_cost',			printError($error_cost));
		}

		$this->View->render(TRUE);

		/* Footer */
		$this->View->loadTemplate('footer');
		$this->View->render(TRUE);
	}
}
