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

	const TEMPLATE_DIR = __DIR__ . '/../templates/visitor/';
	const TEMPLATE_EXT = 'latte';
	const PATH = '/srazvs/visitor';

	/**
	 * @var Emailer
	 */
	protected $emailer;

	/**
	 * @var MealModel
	 */
	protected $mealModel;

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
	 * @param MeetingModel $meetings
	 * @param Emailer      $emailer
	 * @param Request      $request
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
	}

	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();

		$this->getMeetingModel()->setMeetingId($this->getMeetingId());
	}

	/**
	 * This is the default function that will be called by Router.php
	 *
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init()
	{
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
			case "modify":
				$this->actionUpdate($id);
				break;
		}

		$this->render();
	}

	/**
	 * Process data from form
	 *
	 * @return void
	 */
	public function actionCreate()
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
	public function actionUpdate($id = NULL)
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
	public function actionSend()
	{
		try {
			$request = $this->getRequest();
			$subject = $request->getPost('subject', '');
			$message = $request->getPost('message', '');
			$bcc = explode(',', preg_replace("/\s+/", "", $request->getPost('recipients', '')));
			// fill bcc name and address
			$bcc = array_combine($bcc, $bcc);

			$mailParameters = $this->getContainer()->parameters['mail'];
			$recipient = [
				$mailParameters['senderAddress'] => $mailParameters['senderName'],
			];

			$template = $this->createMailTemplate();
			$template->subject = $subject;
			$template->message = $message;

			$result = $this->getEmailer()->sendMail($recipient, $subject, (string) $template, $bcc);

			Debugger::log('E-mail was send successfully, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('E-mail byl úspěšně odeslán', 'ok');
		} catch(Exception $e) {
			Debugger::log('Sending of e-mail failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Sending of e-mail failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Visitor:listing');
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
		$template->meals = $this->getMealModel()->renderHtmlMealsSelect(null, null);
		$template->cost = $this->getMeetingModel()->getPrice('cost');
		$template->programSwitcher = $this->getModel()->renderProgramSwitcher($this->getMeetingId(), null);
	}

	/**
	 * @param  integer $id
	 * @return void
	 */
	public function renderEdit($id)
	{
		$visitor = $this->getModel()->findById($id);
		$meals = $this->getMealModel()->findByVisitorId($id);

		$template = $this->getTemplate();

		$template->heading = 'úprava účastníka';
		$template->error_name = '';
		$template->error_surname = '';
		$template->error_nick = '';
		$template->error_email = '';
		$template->error_postal_code = '';
		$template->error_group_num = '';
		$template->error_bill = '';
		$template->error_cost = '';
		$template->province = $this->getMeetingModel()->renderHtmlProvinceSelect($visitor->province);
		$template->meals = $this->getMealModel()->renderHtmlMealsSelect($meals, null);
		$template->cost = $this->getMeetingModel()->getPrice('cost');
		$template->programSwitcher = $this->getModel()->renderProgramSwitcher($this->getMeetingId(), $id);
		$template->data = $visitor;
	}

	/**
	 * Prepare mass mail form
	 *
	 * @return void
	 */
	public function renderMail()
	{
		$ids = $this->getRequest()->getPost('checker');

		$template = $this->getTemplate();
		$template->recipientMailAddresses = $this->getModel()->getSerializedMailAddress($ids);
	}


	/**
	 * @return void
	 */
	public function renderListing()
	{
		$search = $this->getRequest()->getQuery('search');

		$model = $this->getModel();

		$template = $this->getTemplate();
		$template->render = $model->setSearch($search)->all();
		$template->visitorCount = $model->getCount();
		$template->meetingPrice	= $this->getMeetingModel()->getPrice('cost');
		$template->search = $search;
	}

	/**
	 * @return Latte
	 */
	protected function createMailTemplate()
	{
		$template = $this->createTemplate();
		$template->setFile(
			sprintf(
				'%s%s.%s',
				self::TEMPLATE_DIR,
				'mail_body',
				self::TEMPLATE_EXT
			)
		);

		return $template;
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
