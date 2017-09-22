<?php

namespace App\Presenters;

use App\Models\MeetingModel;
use App\Models\VisitorModel;
use App\Models\BlockModel;
use App\Models\MealModel;
use App\Services\Emailer;
use App\Services\VisitorService;
use Nette\Utils\Strings;
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
	 * @var VisitorSErvice
	 */
	protected $visitorService;

	/**
	 * @param VisitorModel $visitors
	 * @param MealModel    $meals
	 * @param BlockModel   $blocks
	 * @param MeetingModel $meetings
	 * @param Emailer      $emailer
	 */
	public function __construct(
		VisitorModel $visitors,
		MealModel $meals,
		BlockModel $blocks,
		MeetingModel $meetings,
		Emailer $emailer,
		VisitorService $visitor
	) {
		$this->setModel($visitors);
		$this->setMealModel($meals);
		$this->setBlockModel($blocks);
		$this->setMeetingModel($meetings);
		$this->setEmailer($emailer);
		$this->setVisitorService($visitor);
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
	 * Process data from form
	 *
	 * @return void
	 */
	public function actionCreate()
	{
		try {
			$postData = $this->getHttpRequest()->getPost();
			$postData['meeting'] = $this->getMeetingId();

			$guid = $this->getVisitorService()->create($postData);
			$result = $this->sendRegistrationSummary($postData, $guid);

			Debugger::log('Creation of visitor('. $guid .') successfull, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('Účastník(' . $guid . ') byl úspěšně vytvořen.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Creation of visitor('. $guid .') failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Creation of visitor failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Visitor:listing');
	}

	/**
	 * Process data from editing
	 *
	 * @param  integer 	$id
	 * @return void
	 */
	public function actionUpdate($id)
	{
		try {
			$postData = $this->getHttpRequest()->getPost();
			$postData['meeting'] = $this->getMeetingId();
			$postData['visitor'] = $id;

			$result = $this->getVisitorService()->update($id, $postData);

			//$result = $this->sendRegistrationSummary($visitor, $guid);

			Debugger::log('Modification of visitor('. $id .') successfull, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('Účastník(' . $id . ') byl úspěšně upraven.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Modification of visitor('. $id .') failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Modification of visitor failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Visitor:listing');
	}

	/**
	 * @param  int  $id
	 * @return void
	 */
	public function actionDelete($id)
	{
		try {
			$result = $this->getVisitorService()->delete($id);

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
			$request = $this->getHttpRequest();
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
	public function actionPay($id)
	{
		try {
			$visitor = $this->getModel();
			$visitor->payCharge($id, 'cost');
			$recipients = $visitor->getRecipients($id);
			$this->getEmailer()->sendPaymentInfo($recipients, 'advance');

			Debugger::log('Visitor: Action pay for id ' . $id . ' successfull, result: ' . $e->getMessage(), Debugger::INFO);
			$this->flashMessage('Platba byla zaplacena.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Visitor: Action pay for id ' . $id . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Visitor: Action pay for id ' . $id . ' failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Visitor:listing');
	}

	/**
	 * @param  string|interger $ids
	 * @return void
	 */
	public function actionAdvance($id)
	{
		try {
			$visitor = $this->getModel();
			$visitor->payCharge($id, 'advance');
			$recipients = $visitor->getRecipients($id);
			$this->getEmailer()->sendPaymentInfo($recipients, 'advance');

			Debugger::log('Visitor: Action advance for id ' . $id . ' successfull, result: ' . $e->getMessage(), Debugger::INFO);
			$this->flashMessage('Záloha byla zaplacena.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Visitor: Action advance for id ' . $id . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Visitor: Action advance for id ' . $id . ' failed, result: ' . $e->getMessage(), 'error');
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
		$ids = $this->getHttpRequest()->getPost('checker');

		$template = $this->getTemplate();
		$template->recipientMailAddresses = $this->getModel()->getSerializedMailAddress($ids);
	}


	/**
	 * @return void
	 */
	public function renderListing()
	{
		$search = $this->getHttpRequest()->getQuery('search');

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
	 * @param  array   $visitor
	 * @return boolean
	 */
	protected function sendRegistrationSummary(array $visitor, $guid)
	{
		$recipient = [
			$visitor['email'] => $visitor['name']. ' ' . $visitor['surname'],
		];

		$code4bank = $this->getVisitorService()->calculateCode4Bank(
			$visitor['name'],
			$visitor['surname'],
			$visitor['birthday']->format('d. m. Y')
		);
		$result = $this->getEmailer()->sendRegistrationSummary($recipient, $guid, $code4bank);

		return $result;
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
	protected function setEmailer(Emailer $emailer)
	{
		$this->emailer = $emailer;

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
	 * @return VisitorService
	 */
	protected function getVisitorService()
	{
		return $this->visitorService;
	}

	/**
	 * @param  VisitorService $service
	 * @return $this
	 */
	protected function setVisitorService(VisitorService $service)
	{
		$this->visitorService = $service;

		return $this;
	}

}
