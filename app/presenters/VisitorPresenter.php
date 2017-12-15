<?php

namespace App\Presenters;

use App\Models\MeetingModel;
use App\Models\VisitorModel;
use App\Models\BlockModel;
use App\Models\MealModel;
use App\Services\Emailer;
use App\Repositories\VisitorRepository;
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
	 * @var VisitorRepository
	 */
	protected $visitorRepository;

	/**
	 * @param MealModel         $meals
	 * @param BlockModel        $blocks
	 * @param MeetingModel      $meetings
	 * @param Emailer           $emailer
	 * @param VisitorRepository $visitor
	 */
	public function __construct(
		MealModel $meals,
		BlockModel $blocks,
		MeetingModel $meetings,
		Emailer $emailer,
		VisitorRepository $visitor
	) {
		$this->setMealModel($meals);
		$this->setBlockModel($blocks);
		$this->setMeetingModel($meetings);
		$this->setEmailer($emailer);
		$this->setVisitorRepository($visitor);
	}

	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();

		$this->getMeetingModel()->setMeetingId($this->getMeetingId());
		$this->getVisitorRepository()->setMeeting($this->getMeetingId())
;	}

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

			$guid = $this->getVisitorRepository()->create($postData);
			$result = $this->sendRegistrationSummary($postData, $guid);

			$this->logInfo('Creation of visitor('. $guid .') successfull, result: ' . json_encode($result));
			$this->flashSuccess('Účastník(' . $guid . ') byl úspěšně vytvořen.');
		} catch(Exception $e) {
			$this->logError('Creation of visitor('. $guid .') failed, result: ' .  $e->getMessage());
			$this->flashError('Creation of visitor failed, result: ' . $e->getMessage());
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

			$result = $this->getVisitorRepository()->update($id, $postData);

			//$result = $this->sendRegistrationSummary($visitor, $guid);

			$this->logInfo('Modification of visitor('. $id .') successfull, result: ' . json_encode($result));
			$this->flashSuccess('Účastník(' . $id . ') byl úspěšně upraven.');
		} catch(Exception $e) {
			$this->logError('Modification of visitor('. $id .') failed, result: ' .  $e->getMessage());
			$this->flashFailure('Modification of visitor failed, result: ' . $e->getMessage());
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
			$result = $this->getVisitorRepository()->delete($id);

			$this->logInfo('Destroying of visitor('. $id .') successfull, result: ' . json_encode($result));
			$this->flashSuccess('Položka byla úspěšně smazána');
		} catch(Exception $e) {
			$this->logError('Destroying of visitor('. $id .') failed, result: ' .  $e->getMessage());
			$this->flashFailure('Destroying of visitor failed, result: ' . $e->getMessage());
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

			$this->logInfo('E-mail was send successfully, result: ' . json_encode($result));
			$this->flashSuccess('E-mail byl úspěšně odeslán');
		} catch(Exception $e) {
			$this->logError('Sending of e-mail failed, result: ' .  $e->getMessage());
			$this->flashFailure('Sending of e-mail failed, result: ' . $e->getMessage());
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
			$visitor = $this->getVisitorRepository();
			$visitor->payCostCharge($id);
			$recipients = $visitor->findRecipients($id);
			$this->getEmailer()->sendPaymentInfo($recipients, 'cost');

			$this->logInfo('Visitor: Action pay cost for id ' . $id . ' executed successfully.');
			$this->flashMessage('Platba byla zaplacena.', 'ok');
		} catch(Exception $e) {
			$this->logError('Visitor: Action pay for id ' . $id . ' failed, result: ' . $e->getMessage());
			$this->flashFailure('Visitor: Action pay for id ' . $id . ' failed, result: ' . $e->getMessage());
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
			$visitor = $this->getVisitorRepository();
			$visitor->payAdvanceCharge($id);
			$recipients = $visitor->findRecipients($id);
			$this->getEmailer()->sendPaymentInfo($recipients, 'advance');

			$this->logInfo('Visitor: Action pay advance for id ' . $id . ' executed successfully.');
			$this->flashSuccess('Záloha byla zaplacena.');
		} catch(Exception $e) {
			$this->logError('Visitor: Action pay advance for id ' . $id . ' failed, result: ' . $e->getMessage());
			$this->flashFailure('Visitor: Action advance for id ' . $id . ' failed, result: ' . $e->getMessage());
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
			$result = $this->getVisitorRepository()->setChecked($id);
			$this->logInfo('Check of visitor('. $id .') successfull, result: ' . json_encode($result));
			$this->flashSuccess('Položka byla úspěšně zkontrolována');
		} catch(Exception $e) {
			$this->logError('Check of visitor('. $id .') failed, result: ' .  $e->getMessage());
			$this->flashFailure('Check of visitor failed, result: ' . $e->getMessage());
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
			$result = $this->getVisitorRepository()->setUnchecked($id);
			$this->logInfo('Uncheck of visitor('. $id .') successfull, result: ' . json_encode($result));
			$this->flashSuccess('Položka byla nastavena jako nekontrolována');
		} catch(Exception $e) {
			$this->logError('Uncheck of visitor('. $id .') failed, result: ' .  $e->getMessage());
			$this->flashFailure('Uncheck of visitor failed, result: ' . $e->getMessage());
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
		$template->programSwitcher = $this->getVisitorRepository()->renderProgramSwitcher($this->getMeetingId(), null);
	}

	/**
	 * @param  integer $id
	 * @return void
	 */
	public function renderEdit($id)
	{
		$visitor = $this->getVisitorRepository()->findById($id);
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
		$template->programSwitcher = $this->getVisitorRepository()->renderProgramSwitcher($this->getMeetingId(), $id);
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
		$template->recipientMailAddresses = $this->getVisitorRepository()->getSerializedMailAddress($ids);
	}


	/**
	 * @return void
	 */
	public function renderListing()
	{
		$search = $this->getHttpRequest()->getQuery('search') ?: '';

		$visitorRepository = $this->getVisitorRepository();

		$template = $this->getTemplate();
		$template->render = $visitorRepository->findBySearch($search);
		$template->visitorCount = $visitorRepository->count();
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

		$code4bank = $this->getVisitorRepository()->calculateCode4Bank(
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
	 * @return VisitorRepository
	 */
	protected function getVisitorRepository(): VisitorRepository
	{
		return $this->visitorRepository;
	}

	/**
	 * @param  VisitorRepository $repository
	 * @return self
	 */
	protected function setVisitorRepository(VisitorRepository $repository): self
	{
		$this->visitorRepository = $repository;

		return $this;
	}

}
