<?php

namespace App\Presenters;

use Nette\Database\Context;
use Nette\DI\Container;
use Tracy\Debugger;

/**
 * Visitor controller
 *
 * This file handles the retrieval and serving of visitors
 *
 * @author Tomas Litera
 * @copyright 2013-06-12 <tomaslitera@hotmail.com>
 * @package srazvs
 */
class VisitorPresenter extends BasePresenter
{

	const PATH = '/srazvs/visitor';

	/**
	 * Object farm container
	 * @var Container
	 */
	private $container;

	/** @var VisitorModel */
	private $model;

	/** @var Emailer */
	private $emailer;

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

	private $disabled;
	private $mealData;

	/** @var Block */
	protected $block;

	/**
	 * Prepare model classes and get meeting id
	 */
	public function __construct(Context $database, Container $container)
	{
		$this->templateDir = 'visitor';
		$this->template = "listing";
		$this->page = 'visitor';

		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->setModel($this->container->getService('visitor'));
		$this->setEmailer($this->container->getService('emailer'));
		$this->Export = $this->container->getService('exports');
		$this->Meeting = $this->container->getService('meeting');
		$this->Meal = $this->container->getService('meal');
		$this->Category = $this->container->getService('category');
		$this->setBlock($this->container->getService('block'));
		$this->latte = $this->container->getService('latte');

		if($this->meetingId = $this->requested('mid', '')) {
			$_SESSION['meetingID'] = $this->meetingId;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}

		$this->getModel()->setMeetingId($this->meetingId);
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
		$id = $this->requested('id', $this->itemId);
		$this->error = $this->requested('error', '');
		$this->page = $this->requested('page', '');

		$search = $this->requested('search', '');
		if(isset($search)){
			$this->getModel()->setSearch($search);
		}

		$this->disabled = $this->requested('disabled', '');

		if($ids = $this->requested('checker')) {
			$query_id = array();
			foreach($ids as $key => $value) {
				$query_id[] = $value;
			}
		} elseif($id) {
			$query_id = $id;
		} else {
			$query_id = $ids;
		}

		$action = $this->requested('action', '');

		switch($action) {
			case "delete":
				$this->actionDelete($query_id);
				break;
			case "new":
				$this->actionNew();
				break;
			case "create":
				$this->actionCreate();
				break;
			case "edit":
				$this->actionEdit($id);
				break;
			case "modify":
				$this->actionUpdate($id);
				break;
			case "massmail":
				$this->actionMassmail($query_id);
				break;
			case "send":
				$this->actionSend($this->requested('recipients', ''));
			// pay full charge
			case "pay":
				$this->actionPay($query_id);
				break;
			// pay advance
			case "advance":
				$this->actionAdvance($query_id);
				break;
			// export all visitors to excel
			case "export":
				$this->Export->printVisitorsExcel();
				break;
			case "checked":
				$this->actionChecked($id);
				break;
			case "unchecked":
				$this->actionUnchecked($id);
				break;
		}

		$this->render();
	}

	/**
	 * Prepare page for new item
	 *
	 * @return void
	 */
	private function actionNew()
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
		foreach($this->getModel()->dbColumns as $key) {
			if($key == 'bill') $value = 0;
			elseif($key == 'cost') $value = $this->Meeting->getPrice('cost');
			else $value = "";
			$this->data[$key] = $this->requested($key, $value);
		}
	}

	/**
	 * Process data from form
	 *
	 * @return void
	 */
	private function actionCreate()
	{
		// TODO
		////ziskani zvolenych programu
		$blocks = $this->block->findByMeeting($this->meetingId);

		foreach($blocks as $blockData){
			$$blockData['id'] = $this->requested($blockData['id'], 0);
			$programs_data[$blockData['id']] = $$blockData['id'];
			//echo $blockData['id'].":".$$blockData['id']."|";
		}

		// requested for visitors
		foreach($this->getModel()->dbColumns as $key) {
				if($key == 'bill') $$key = $this->requested($key, 0);
				elseif($key == 'cost') $$key = $this->requested($key, 0);
				elseif($key == 'checked') $$key = $this->requested($key, 0);
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
		if($vid = $this->getModel()->create($DB_data, $meals_data, $programs_data)){
			######################## ODESILAM EMAIL ##########################

			// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
			$code4bank = $this->code4Bank($DB_data);
			$hash = ((int)$vid.$this->meetingId) * 147 + 49873;

			$recipient_mail = $DB_data['email'];
			$recipient_name = $DB_data['name']." ".$DB_data['surname'];
			$recipient = [$recipient_mail => $recipient_name];

			if($return = $this->getEmailer()->sendRegistrationSummary($recipient, $hash, $code4bank)) {
				if(is_int($vid)) {
					$vid = "ok";
				}
				redirect(self::PATH . "?page=".$this->page."&error=ok");
			} else {
				redirect(self::PATH . "?page=".$this->page."&error=error");
			}
		} else {
			redirect(self::PATH . "?page=".$this->page."&error=error");
		}
	}

	/**
	 * Process data from editing
	 *
	 * @param  int 	$id 	of item
	 * @return void
	 */
	private function actionUpdate($id = NULL)
	{
		// TODO
		////ziskani zvolenych programu
		$blocks = $this->getBlock()->findByMeeting($this->meetingId);

		foreach($blocks as $blockData){
			$$blockData['id'] = $this->requested('blck_' . $blockData['id'], 0);
			$programs_data[$blockData['id']] = $$blockData['id'];
		}

		foreach($this->getModel()->dbColumns as $key) {
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

		if($this->getModel()->modify($id, $DB_data, $meals_data, $programs_data)){
			redirect(self::PATH . "?page=".$this->page."&error=ok");
		} else {
			redirect(self::PATH . "?page=".$this->page."&error=error");
		}
	}

	/**
	 * Prepare data for editing
	 *
	 * @param  int $id of item
	 * @return void
	 */
	private function actionEdit($id)
	{
		$this->template = 'form';

		$this->heading = "úprava účastníka";
		$this->todo = "modify";

		$this->itemId = $id;

		$dbData = $this->getModel()->getData($id);

		foreach($this->getModel()->dbColumns as $key) {
			$this->data[$key] = $this->requested($key, $dbData[$key]);
		}

		$data = $this->Meal->findByVisitorId($this->itemId);

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
	private function actionDelete($id)
	{
		if($this->getModel()->delete($id)) {
			  redirect(self::PATH . "?error=del");
		}
	}

	/**
	 * Prepare mass mail form
	 *
	 * @return void
	 */
	private function actionMassmail($query_id)
	{
		$this->template = 'mail';

		$recipient_mails = $this->getModel()->getMail($query_id);
		$this->recipients = rtrim($recipient_mails, "\n,");
	}

	/**
	 * Prepare mass mail form
	 *
	 * @return void
	 */
	private function actionSend($recipients)
	{
		$bcc_mail = preg_replace("/\n/", "", $this->requested('recipients', ''));

		$recipient_name = $this->getModel()->configuration['mail-sender-name'];
		$recipient_mail = $this->getModel()->configuration['mail-sender-address'];

		$subject = $this->requested('subject', '');

		// space to &nbsp;
		$message = str_replace(" ","&nbsp;", $this->requested('message', ''));
		// new line to <br> and tags stripping
		$message = nl2br(strip_tags($message));

		$message = "<html><head><title>".$subject."</title></head><body>\n".$message."\n</body>\n</html>";

		$return = $this->getEmailer()->sendMail($recipient_mail, $recipient_name, $subject, $message, $bcc_mail);

		if($return){
			$error = 'E_MAIL_NOTICE';
			$error = 'mail_send';
			redirect(self::PATH . "?error=".$error);
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
	private function actionPay($ids)
	{
		$visitor = $this->getModel();

		try {
			$visitor->payCharge($ids, 'cost');
		} catch(\Exception $e) {
			Debugger::log('Visitor: Action pay for id ' . $ids . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);
			redirect(self::PATH . "?".$this->page."&error=already_paid");
		}

		$recipients = $visitor->getRecipients($ids);
		$this->getEmailer()->sendPaymentInfo($recipients, 'cost');
		redirect(self::PATH . "?".$this->page."&error=mail_send");
	}

	protected function actionAdvance($ids)
	{
		$visitor = $this->getModel();

		try {
			$this->Visitor->payCharge($ids, 'advance');
		} catch(\Exception $e) {
			Debugger::log('Visitor: Action advance for id ' . $ids . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);
			redirect(self::PATH . "?".$this->page."&error=already_paid");
		}

		$recipients = $visitor->getRecipients($ids);
		$this->getEmailer()->sendPaymentInfo($recipients, 'advance');
		redirect(self::PATH . "?".$this->page."&error=mail_send");
	}

	/**
	 * Set item as checked by id
	 *
	 * @param  int $id of item
	 * @return void
	 */
	private function actionChecked($id)
	{
		if($this->getModel()->checked($id, '1')) {
			  redirect(self::PATH . "?error=checked");
		}
	}

	/**
	 * Set item as unchecked by id
	 *
	 * @param  int $id of item
	 * @return void
	 */
	private function actionUnchecked($id)
	{
		if($this->getModel()->checked($id, 0)) {
			  redirect(self::PATH . "?error=unchecked");
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
		}

		$parameters = [
			'cssDir'			=> CSS_DIR,
			'jsDir'				=> JS_DIR,
			'imgDir'			=> IMG_DIR,
			'visitDir'			=> VISIT_DIR,
			'expDir'			=> EXP_DIR,
			'style'				=> $this->Category->getStyles(),
			'user'				=> $this->getSunlightUser($_SESSION[SESSION_PREFIX.'user']),
			'meeting'			=> $this->getPlaceAndYear($_SESSION['meetingID']),
			'menu'				=> $this->generateMenu(),
			'error'				=> printError($this->error),
			'todo'				=> $this->todo,
			'render'			=> $this->getModel()->getData(),
			'mid'				=> $this->meetingId,
			'page'				=> $this->page,
			'heading'			=> $this->heading,
			'visitorCount'		=> $this->getModel()->getCount(),
			'meetingPrice'		=> $this->Meeting->getPrice('cost'),
			'search'			=> $this->getModel()->search,
			'recipient_mails'	=> $this->recipients,
		];

		if(!empty($this->data)) {
			$parameters['id'] = $this->itemId;
			$parameters['data'] = $this->data;
			$parameters['birthday'] = (empty($this->data['birthday'])) ? '' : $this->data['birthday']->format('Y-m-d');
			$parameters['province'] = $this->Meeting->renderHtmlProvinceSelect($this->data['province']);
			$parameters['meals'] = $this->Meal->renderHtmlMealsSelect($this->mealData, $this->disabled);
			$parameters['program_switcher'] = $this->getModel()->renderProgramSwitcher($this->meetingId, $this->itemId);
			$parameters['error_name'] = printError($error_name);
			$parameters['error_surname'] = printError($error_surname);
			$parameters['error_nick'] = printError($error_nick);
			$parameters['error_email'] = printError($error_email);
			$parameters['error_postal_code'] = printError($error_postal_code);
			$parameters['error_surname'] = printError($error_surname);
			$parameters['error_group_num'] = printError($error_group_num);
			$parameters['error_bill'] = printError($error_bill);
			$parameters['error_cost'] = printError($error_cost);
			$parameters['checked'] = empty($this->data['checked']) ? '0' : $this->data['checked'];
			$parameters['cost']	= $this->Meeting->getPrice('cost');
			$parameters['guid'] = isset($this->data['guid']) ? $this->data['guid'] : '';

		}

		$this->latte->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);
	}

	/**
	 * @return Block
	 */
	protected function getBlock()
	{
		return $this->block;
	}

	/**
	 * @param  Blocks $block
	 * @return $this
	 */
	protected function setBlock($block)
	{
		$this->block = $block;
		return $this;
	}

	/**
	 * @return Visitor
	 */
	protected function getModel()
	{
		return $this->model;
	}

	/**
	 * @param  Visitor $model
	 * @return $this
	 */
	protected function setModel($model)
	{
		$this->model = $model;
		return $this;
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
	protected function setEmailer($emailer)
	{
		$this->emailer = $emailer;
		return $this;
	}

}
