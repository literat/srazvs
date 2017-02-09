<?php

namespace App\Presenters;

use App\Models\MeetingModel;
use App\Models\VisitorModel;
use App\Models\BlockModel;
use App\Models\MealModel;
use App\Services\Emailer;
use Nette\Http\Request;
use Tracy\Debugger;
use Exception;

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

	/** @var Emailer */
	private $emailer;

	/**
	 * Meeting class
	 * @var Meeting
	 */
	private $Meeting;

	/**
	 * @var MealModel
	 */
	private $mealModel;

	/**
	 * Recipients
	 * @var recipients
	 */
	private $recipients = NULL;

	private $disabled;
	private $mealData;

	/**
	 * @var BlockModel
	 */
	protected $blockModel;

	/**
	 * @var MeetingModel
	 */
	protected $meetingModel;

	/**
	 * @param VisitorModel $visitors
	 * @param MealModel    $meals
	 * @param BlockModel   $blocks
	 * @param Emailer      $emailer
	 */
	public function __construct(
		VisitorModel $visitors,
		MealModel $meals,
		BlockModel $blocks,
		MeetingModel $meetings,
		Emailer $emailer,
		Request $request
	) {
		$this->setModel($visitors);
		$this->setMealModel($meals);
		$this->setBlockModel($blocks);
		$this->setMeetingModel($meetings);
		$this->setEmailer($emailer);
		$this->setRequest($request);

		$this->template = "listing";
		$this->page = 'visitor';
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
		}

		$this->render();
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
	 * @param  int  $id
	 * @return void
	 */
	public function actionDelete($id)
	{
		try {
			$result = $this->getModel()->delete($id);
			Debugger::log('Destroying of visitor('. $id .') successfull, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('Položka byla úspěšně smazána', 'ok');
		} catch(Exception $e) {
			Debugger::log('Destroying of visitor('. $id .') failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Destroying of visitor failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Visitor:listing');
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
	 * @param  integer|string $ids
	 * @return void
	 */
	public function actionPay($ids)
	{
		try {
			$visitor = $this->getModel();
			$visitor->payCharge($ids, 'cost');
			$recipients = $visitor->getRecipients($ids);
			$this->getEmailer()->sendPaymentInfo($recipients, 'advance');

			Debugger::log('Visitor: Action pay for id ' . $ids . ' successfull, result: ' . $e->getMessage(), Debugger::INFO);
			$this->flashMessage('Platba byla zaplacena.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Visitor: Action pay for id ' . $ids . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Visitor: Action pay for id ' . $ids . ' failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Visitor:listing');
	}

	/**
	 * @param  string|interger $ids
	 * @return void
	 */
	public function actionAdvance($ids)
	{
		try {
			$visitor = $this->getModel();
			$visitor->payCharge($ids, 'advance');
			$recipients = $visitor->getRecipients($ids);
			$this->getEmailer()->sendPaymentInfo($recipients, 'advance');

			Debugger::log('Visitor: Action advance for id ' . $ids . ' successfull, result: ' . $e->getMessage(), Debugger::INFO);
			$this->flashMessage('Záloha byla zaplacena.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Visitor: Action advance for id ' . $ids . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Visitor: Action advance for id ' . $ids . ' failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Visitor:listing');
	}

	/**
	 * Set item as checked by id
	 *
	 * @param  integer $id
	 * @return void
	 */
	public function actionChecked($id)
	{
		try {
			$result = $this->getModel()->checked($id, '1');
			Debugger::log('Check of visitor('. $id .') successfull, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('Položka byla úspěšně zkontrolována', 'ok');
		} catch(Exception $e) {
			Debugger::log('Check of visitor('. $id .') failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Check of visitor failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Visitor:listing');
	}

	/**
	 * Set item as unchecked by id
	 *
	 * @param  integer $id
	 * @return void
	 */
	public function actionUnchecked($id)
	{
		try {
			$result = $this->getModel()->checked($id, 0);
			Debugger::log('Uncheck of visitor('. $id .') successfull, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('Položka byla nastavena jako nekontrolována', 'ok');
		} catch(Exception $e) {
			Debugger::log('Uncheck of visitor('. $id .') failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Uncheck of visitor failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Visitor:listing');
	}

	/**
	 * Prepare page for new item
	 *
	 * @return void
	 */
	public function renderNew()
	{
		$template = $this->getTemplate();

		$template->heading = 'nový účastník';
		$template->error_name = '';
		$template->error_surname = '';
		$template->error_nick = '';
		$template->error_email = '';
		$template->error_postal_code = '';
		$template->error_group_num = '';
		$template->error_bill = '';
		$template->error_cost = '';
		$template->province = $this->getMeetingModel()->renderHtmlProvinceSelect(null);
		$template->meals = $this->getMealModel()->renderHtmlMealsSelect($this->mealData, $this->disabled);
		$template->cost = $this->getMeetingModel()->getPrice('cost');
		$template->programSwitcher = $this->getModel()->renderProgramSwitcher($this->meetingId, $this->itemId);
	}


	/**
	 * @return void
	 */
	public function renderListing()
	{
		$template = $this->getTemplate();

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

//		$parameters = [
//			'cssDir'			=> CSS_DIR,
//			'jsDir'				=> JS_DIR,
//			'imgDir'			=> IMG_DIR,
			//$template->visitDir	= VISIT_DIR;
			//$template->expDir = EXP_DIR;
//			'style'				=> $this->getStyles(),
//			'user'				=> $this->getSunlightUser($_SESSION[SESSION_PREFIX.'user']),
//			'meeting'			=> $this->getPlaceAndYear($_SESSION['meetingID']),
//			'menu'				=> $this->generateMenu(),
//			'error'				=> printError($this->error),
//			'todo'				=> $this->todo,
			$template->render = $this->getModel()->getData();
//			'mid'				=> $this->meetingId,
//			'page'				=> $this->page,
//			'heading'			=> $this->heading,
			$template->visitorCount = $this->getModel()->getCount();
			$template->meetingPrice	= $this->getMeetingModel()->getPrice('cost');
			$template->search = $this->getModel()->search;
//			'recipient_mails'	=> $this->recipients,
//		];

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
	}

	/**
	 * @return BlockModel
	 */
	protected function getBlockModel()
	{
		return $this->blockModel;
	}

	/**
	 * @param  BlockModel $model
	 * @return $this
	 */
	protected function setBlockModel(BlockModel $model)
	{
		$this->blockModel = $model;
		return $this;
	}

	/**
	 * @return MealModel
	 */
	protected function getMealModel()
	{
		return $this->mealModel;
	}

	/**
	 * @param  MealModel $model
	 * @return $this
	 */
	protected function setMealModel(MealModel $model)
	{
		$this->mealModel = $model;
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
