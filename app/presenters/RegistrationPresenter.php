<?php

namespace App\Presenters;

use App\Models\MeetingModel;
use App\Models\VisitorModel;
use App\Models\ProgramModel;
use App\Models\MealModel;
use App\Services\UserService;
use App\Services\Emailer;
use App\Services\VisitorService;
use Tracy\Debugger;
use App\Components\Forms\RegistrationForm;
use App\Components\Forms\Factories\IRegistrationFormFactory;

/**
 * Registration controller
 *
 * This file handles the registration of visitors
 *
 * @author Tomas Litera
 * @copyright 2013-06-12 <tomaslitera@hotmail.com>
 * @package srazvs
 */
class RegistrationPresenter extends VisitorPresenter
{

	/**
	 * @var VisitorModel
	 */
	private $visitorModel;

	/**
	 * @var ProgramModel
	 */
	private $programModel;

	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @var boolean
	 */
	private $disabled = false;

	/**
	 * @var IRegistrationFormFactory
	 */
	private $registrationFormFactory;

	/**
	 * @param MeetingModel   $meetingModel
	 * @param UserService    $userService
	 * @param VisitorModel   $visitorModel
	 * @param MealModel      $mealModel
	 * @param ProgramModel   $programModel
	 * @param VisitorService $visitorService
	 */
	public function __construct(
		MeetingModel $meetingModel,
		UserService $userService,
		VisitorModel $visitorModel,
		MealModel $mealModel,
		ProgramModel $programModel,
		Emailer $emailer,
		VisitorService $visitorService
	) {
		$this->setMeetingModel($meetingModel);
		$this->setUserService($userService);
		$this->setVisitorModel($visitorModel);
		$this->setMealModel($mealModel);
		$this->setProgramModel($programModel);
		$this->setEmailer($emailer);
		$this->setVisitorService($visitorService);
	}

	/**
	 * @return IRegistrationFormFactory
	 */
	public function getRegistrationFormFactory(): IRegistrationFormFactory
	{
		return $this->registrationFormFactory;
	}

	/**
	 * Injector
	 *
	 * @param  IRegistrationFormFactory $factory
	 */
	public function injectRegistrationFormFactory(IRegistrationFormFactory $factory)
	{
		$this->registrationFormFactory = $factory;
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
			$this->getMeetingModel()->setRegistrationHandlers($this->getMeetingId());
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
			$postData = $this->getHttpRequest()->getPost();
			$postData['meeting'] = $this->getMeetingId();

			$guid = $this->getVisitorService()->create($postData);
			$result = $this->sendRegistrationSummary($postData, $guid);

			Debugger::log('Creation of registration('. $guid .') successfull, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('Registrace(' . $guid . ') byla úspěšně založena.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Creation of registration('. $guid .') failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Creation of registration failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Registration:check', $guid);
	}

	/**
	 * @param  string  $guid
	 * @return void
	 */
	public function actionUpdate($guid)
	{
		try {
			$postData = $this->getHttpRequest()->getPost();

			$result = $this->getVisitorService()->update($guid, $postData);
			$result = $this->sendRegistrationSummary($postData, $guid);

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
			$userDetail = $this->getUserService()->getUserDetail();
			$skautisUser = $this->getUserService()->getPersonalDetail($userDetail->ID_Person);
			$membership = $this->getUserService()->getPersonUnitDetail($userDetail->ID_Person);

			if(!preg_match('/^[1-9]{1}[0-9a-zA-Z]{2}\.[0-9a-zA-Z]{1}[0-9a-zA-Z]{1}$/', $membership->RegistrationNumber)) {
				$skautisUserUnit = $this->getUserService()->getParentUnitDetail($membership->ID_Unit)[0];
			} else {
				$skautisUserUnit = $this->getUserService()->getUnitDetail($membership->ID_Unit);
			}

			$template->data['name'] = $skautisUser->FirstName;
			$template->data['surname'] = $skautisUser->LastName;
			$template->data['nick'] = $skautisUser->NickName;
			$template->data['email'] = $skautisUser->Email;
			$template->data['street'] = $skautisUser->Street;
			$template->data['city'] = $skautisUser->City;
			$template->data['postal_code'] = preg_replace('/\s+/', '', $skautisUser->Postcode);
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
	 * @return RegistrationFormControl
	 */
	protected function createComponentRegistrationForm(): RegistrationForm
	{
		$control = $this->registrationFormFactory->create();
		$control->setMeetingId($this->getMeetingId());
		$control->onRegistrationSave[] = function(RegistrationForm $control, $newVisitor) {
			try {
				$guid = $this->getVisitorService()->create((array) $newVisitor);
				$result = $this->sendRegistrationSummary((array) $newVisitor, $guid);

				Debugger::log('Creation of visitor('. $guid .') successfull, result: ' . json_encode($result), Debugger::INFO);
				$this->flashMessage('Účastník(' . $guid . ') byl úspěšně vytvořen.', 'ok');
			} catch(Exception $e) {
				Debugger::log('Creation of visitor('. $guid .') failed, result: ' .  $e->getMessage(), Debugger::ERROR);
				$this->flashMessage('Creation of visitor failed, result: ' . $e->getMessage(), 'error');
			}

			$this->redirect('Registration:check', $guid);
		};

		return $control;
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
