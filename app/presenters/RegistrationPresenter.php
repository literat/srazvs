<?php

namespace App\Presenters;

use App\Models\MeetingModel;
use App\Models\VisitorModel;
use App\Models\ProgramModel;
use App\Models\BlockModel;
use App\Models\MealModel;
use App\Services\UserService;
use App\Services\Emailer;
use Nette\Http\Request;
use Tracy\Debugger;

/**
 * Registration controller
 *
 * This file handles the registration of visitors
 *
 * @author Tomas Litera
 * @copyright 2013-06-12 <tomaslitera@hotmail.com>
 * @package srazvs
 */
class RegistrationPresenter extends BasePresenter
{

	/**
	 * @var VisitorModel
	 */
	private $visitorModel;

	/**
	 * @var Emailer
	 */
	private $emailer;

	/**
	 * @var MeetingModel
	 */
	private $meetingModel;

	/**
	 * @var MealModel
	 */
	private $mealModel;

	/**
	 * @var ProgramModel
	 */
	private $programModel;

	/**
	 * @var BlockModel
	 */
	private $blockModel;

	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @var boolean
	 */
	private $disabled = false;

	/**
	 * @param Request      $request
	 * @param MeetingModel $meetingModel
	 * @param UserService  $userService
	 * @param VisitorModel $visitorModel
	 * @param MealModel    $mealModel
	 * @param ProgramModel $programModel
	 * @param BlockModel   $blockModel
	 */
	public function __construct(
		Request $request,
		MeetingModel $meetingModel,
		UserService $userService,
		VisitorModel $visitorModel,
		MealModel $mealModel,
		ProgramModel $programModel,
		BlockModel $blockModel,
		Emailer $emailer
	) {
		$this->setRequest($request);
		$this->setMeetingModel($meetingModel);
		$this->setUserService($userService);
		$this->setVisitorModel($visitorModel);
		$this->setMealModel($mealModel);
		$this->setProgramModel($programModel);
		$this->setBlockModel($blockModel);
		$this->setEmailer($emailer);
	}

	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();

		$this->getMeetingModel()->setMeetingId($this->getMeetingId());

		if($this->getDebugMode()) {
			$this->getMeetingModel()->setRegistrationHandlers(1);
			$this->setMeetingId(1);
		} else {
			$this->getMeetingModel()->setRegistrationHandlers();
		}

		$template = $this->getTemplate();

		$template->page_title = "Registrace srazu VS";
		$template->meeting_heading = $this->getMeetingModel()->getRegHeading();
		$template->isRegistrationOpen = $this->getMeetingModel()->isRegOpen($this->getDebugMode());
	}

	/**
	 * Process data from form
	 *
	 * @return void
	 */
	public function actionCreate()
	{
		try {
			$postData = $this->getRequest()->getPost();
			$postData['meeting'] = $this->getMeetingId();

			$visitor = array_intersect_key($postData, array_flip($this->getVisitorModel()->getColumns()));
			$meals = array_intersect_key($postData, array_flip($this->getMealModel()->getColumns()));

			$blocks = $this->getBlockModel()->findByMeeting($this->getMeetingId());
			$programs = [];
			$programs = array_map(function($block) use ($postData) {
				if(!array_key_exists('blck_' . $block['id'], $postData)) {
					return 0;
				}

				return $postData['blck_' . $block['id']];
			}, $blocks);

			if($guid = $this->getVisitorModel()->assemble($visitor, $meals, $programs, true)) {
				$code4bank = $this->calculateCode4Bank($visitor);

				$recipientMail = $visitor['email'];
				$recipientName = $visitor['name']." ".$visitor['surname'];
				$recipient = [$recipientMail => $recipientName];

				$result= $this->getEmailer()->sendRegistrationSummary($recipient, $guid, $code4bank);
			}

			Debugger::log('Creation of registration('. $guid .') successfull, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('Registrace(' . $guid . ') byla úspěšně založena.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Creation of registration('. $guid .') failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Creation of registration failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Registration:check', $guid);
	}

	/**
	 * @param  integer 	$id
	 * @return void
	 */
	public function actionUpdate($guid)
	{
		try {
			$postData = $this->getRequest()->getPost();

			$visitor = array_intersect_key($postData, array_flip($this->getVisitorModel()->getColumns()));
			$meals = array_intersect_key($postData, array_flip($this->getMealModel()->getColumns()));

			$blocks = $this->getBlockModel()->idsFromCurrentMeeting($postData['meeting']);

			$programs = [];
			$programs = array_map(function($block) use ($postData) {
				if(!array_key_exists('blck_' . $block['id'], $postData)) {
					return 0;
				}

				return $postData['blck_' . $block['id']];
			}, $blocks);

			$result = $this->getVisitorModel()->modifyByGuid($guid, $visitor, $meals, $programs);
			$code4bank = $this->calculateCode4Bank($postData);

			$recipient_mail = $postData['email'];
			$recipient_name = $postData['name']." ".$postData['surname'];
			$recipient = [$recipient_mail => $recipient_name];

			$return = $this->getEmailer()->sendRegistrationSummary($recipient, $guid, $code4bank);

			Debugger::log('Modification of registration('. $guid .') successfull, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('Registrace(' . $guid . ') byla úspěšně upravena.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Modification of registration('. $guid .') failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Modification of registration(' . $guid . ') failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Registration:check', $guid);
	}

	/**
	 * @return void
	 */
	public function renderDefault()
	{
		$template = $this->getTemplate();

		////otevirani a uzavirani prihlasovani
		$disabled = $this->getMeetingModel()->isRegOpen($this->getDebugMode()) ? "" : "disabled";
		$template->disabled = $disabled;
		$template->loggedIn = $this->getUserService()->isLoggedIn();

		// requested for visitors fields
		foreach($this->getVisitorModel()->columns as $column) {
			$data[$column] = '';
		}
		$template->data = $data;

		$template->meals = $this->getMealModel()->renderHtmlMealsSelect(null, $disabled);
		$template->province = $this->getMeetingModel()->renderHtmlProvinceSelect(null);
		$template->programs = $this->getVisitorModel()->renderProgramSwitcher($this->getMeetingId(), null);
		$template->meetingId = $this->getMeetingId();
		$template->cost	= $this->getMeetingModel()->getPrice('cost');

		if($this->getUserservice()->isLoggedIn()) {
			$userDetail = $this->getUserModel()->getUserDetail();
			$skautisUser = $this->getUserModel()->getPersonalDetail($userDetail->ID_Person);
			$membership = $this->getUserModel()->getPersonUnitDetail($userDetail->ID_Person);

			if(!preg_match('/^[1-9]{1}[0-9a-zA-Z]{2}\.[0-9a-zA-Z]{1}[0-9a-zA-Z]{1}$/', $membership->RegistrationNumber)) {
				$skautisUserUnit = $this->getUserModel()->getParentUnitDetail($membership->ID_Unit)[0];
			} else {
				$skautisUserUnit = $this->getUserModel()->getUnitDetail($membership->ID_Unit);
			}

			$template->data['name'] = $skautisUser->FirstName;
			$template->data['surname'] = $skautisUser->LastName;
			$template->data['nick'] = $skautisUser->NickName;
			$template->data['email'] = $skautisUser->Email;
			$template->data['street'] = $skautisUser->Street;
			$template->data['city'] = $skautisUser->City;
			$template->data['postal_code'] = preg_replace('/\s+/','',$skautisUser->Postcode);
			$template->data['birthday'] = $skautisUser->Birthday;
			$template->data['group_name'] = $skautisUserUnit->DisplayName;
			$template->data['group_num'] = $skautisUserUnit->RegistrationNumber;
			if(isset($membership->Unit)) {
				$template->data['troop_name'] = $membership->Unit;
			}
		}
	}

	/**
	 * @param  string  $guid
	 * @return void
	 */
	public function renderCheck($guid)
	{
		$data = $this->getVisitorModel()->findByGuid($guid);

		$this->getMeetingModel()->setRegistrationHandlers($data->meeting);

		$template = $this->getTemplate();
		$template->guid = $guid;
		$template->data = $data;
		$template->meetingId = $data->meeting;
		$template->meals = $this->getMealModel()->findByVisitorId($data->id);
		$template->province = $this->getMeetingModel()->getProvinceNameById($data->province);
		$template->programs = $this->getProgramModel()->getSelectedPrograms($data->id);
	}

	/**
	 * @param  string $guid
	 * @return void
	 */
	public function renderEdit($guid)
	{
		$data = $this->getVisitorModel()->findByGuid($guid);
		$mealData = $this->getMealModel()->findByVisitorId($data->id);

		$this->getMeetingModel()->setRegistrationHandlers($data->meeting);

		$template = $this->getTemplate();
		$template->guid = $guid;
		$template->data = $data;
		$template->meetingId = $data->meeting;
		$template->mealData = $mealData;
		$template->loggedIn = $this->getUserService()->isLoggedIn();
		$template->disabled = $this->getMeetingModel()->isRegOpen($this->getDebugMode()) ? "" : "disabled";
		$template->meals = $this->getMealModel()->renderHtmlMealsSelect($mealData, $this->disabled);
		$template->province = $this->getMeetingModel()->renderHtmlProvinceSelect($data->province);
		$template->programs = $this->getVisitorModel()->renderProgramSwitcher($data->meeting, $data->id);
		$template->cost	= $this->getMeetingModel()->getPrice('cost');
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
	 * @return VisitorModel
	 */
	protected function getVisitorModel()
	{
		return $this->visitorModel;
	}

	/**
	 * @param  VisitorModel $model
	 * @return $this
	 */
	protected function setVisitorModel(VisitorModel $model)
	{
		$this->visitorModel = $model;

		return $this;
	}

	/**
	 * @return ProgramModel
	 */
	protected function getProgramModel()
	{
		return $this->programModel;
	}

	/**
	 * @param  ProgramModel $model
	 * @return $this
	 */
	protected function setProgramModel(ProgramModel $model)
	{
		$this->programModel = $model;

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
	 * @return UserService
	 */
	protected function getUserService()
	{
		return $this->userService;
	}

	/**
	 * @param  UserService $service
	 * @return $this
	 */
	protected function setUserService(UserService $service)
	{
		$this->userService = $service;

		return $this;
	}

}
