<?php

namespace App\Presenters;

use DateTime;
use App\Entities\VisitorEntity;
use App\Models\MeetingModel;
use App\Models\VisitorModel;
use App\Models\ProgramModel;
use App\Models\MealModel;
use App\Services\SkautIS\UserService;
use App\Services\Emailer;
use App\Services\VisitorService;
use App\Services\ProgramService;
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
	 * @var ProgramService
	 */
	private $programService;

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
		VisitorService $visitorService,
		ProgramService $programService
	) {
		$this->setMeetingModel($meetingModel);
		$this->setUserService($userService);
		$this->setVisitorModel($visitorModel);
		$this->setMealModel($mealModel);
		$this->setProgramModel($programModel);
		$this->setEmailer($emailer);
		$this->setVisitorService($visitorService);
		$this->setProgramService($programService);
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

	protected $error = FALSE;

	protected $hash = NULL;
	private $item;
	private $mealData;
	private $user;
	private $event;

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

		//$this->user = $this->container->getService('userService');
		//$this->event = $this->container->getService('eventService');

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
			$this->flashMessage('Registrace(' . $guid . ') byla úspěšně založena.', self::FLASH_TYPE_OK);
		} catch(Exception $e) {
			Debugger::log('Creation of registration('. $guid .') failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Creation of registration failed, result: ' . $e->getMessage(), self::FLASH_TYPE_ERROR);
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
			$this->flashMessage('Registrace(' . $guid . ') byla úspěšně upravena.', self::FLASH_TYPE_OK);
		} catch(Exception $e) {
			Debugger::log('Modification of registration('. $guid .') failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Modification of registration(' . $guid . ') failed, result: ' . $e->getMessage(), self::FLASH_TYPE_ERROR);
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

		if($this->getUserService()->isLoggedIn()) {
			$this['registrationForm']->setDefaults(($this->useLoggedVisitor())->toArray());
		}
	}

	/**
	 * @param  string  $guid
	 * @return void
	 */
	public function renderCheck($guid)
	{
		$visitor = $this->getVisitorModel()->findByGuid($guid);

		$this->getMeetingModel()->setRegistrationHandlers($visitor->meeting);

		$template = $this->getTemplate();
		$template->guid = $guid;
		$template->visitor = $visitor;
		$template->meetingId = $visitor->meeting;
		$template->meals = $this->getMealModel()->findByVisitorId($visitor->id);
		$template->province = $this->getMeetingModel()->getProvinceNameById($visitor->province);
		$template->programs = $this->getProgramModel()->findByVisitorId($visitor->id);
	}

	/**
	 * @param  string $guid
	 * @return void
	 */
	public function renderEdit($guid)
	{
		$visitor = $this->getVisitorService()->findByGuid($guid);
		$meetingId = $visitor['meeting'];

		$this->getMeetingModel()->setRegistrationHandlers($meetingId);

		$template = $this->getTemplate();
		$template->guid = $guid;
		$template->meetingId = $meetingId;
		$template->loggedIn = $this->getUserService()->isLoggedIn();
		$template->disabled = $this->getMeetingModel()->isRegOpen($this->getDebugMode()) ? "" : "disabled";

			// create
			// if($vid = $this->Visitor->create($db_data, $meals_data, $programs_data)) {
			// 	//dd($this->user->isLoggedIn(), $this->Meeting->getEventId(), $this->Meeting->getCourseId());
			// 	if($this->user->isLoggedIn() && $this->Meeting->getEventId() && $this->Meeting->getCourseId()) {
			// 		$personId = $this->user->getUserDetail()->ID_Person;
			// 		dd($this->event->insertParticipant($personId, $this->Meeting->eventId, $this->Meeting->courseId));
			// 	}

		$this['registrationForm']->setDefaults($visitor);
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

				Debugger::log('Storage of visitor('. $guid .') successfull, result: ' . json_encode($result), Debugger::INFO);
				$this->flashMessage('Účastník(' . $guid . ') byl úspěšně uložen.', self::FLASH_TYPE_OK);
			} catch(Exception $e) {
				Debugger::log('Storage of visitor('. $guid .') failed, result: ' .  $e->getMessage(), Debugger::ERROR);
				$this->flashMessage('Uložení účastníka selhalo, chyba: ' . $e->getMessage(), self::FLASH_TYPE_ERROR);
			}

			$this->redirect('Registration:check', $guid);
		};

		return $control;
	}

	/**
	 * @return VisitorEntity
	 */
	protected function useLoggedVisitor(): VisitorEntity
	{
		$userDetail = $this->getUserService()->getUserDetail();
		$skautisUser = $this->getUserService()->getPersonalDetail($userDetail->ID_Person);
		$membership = $this->getUserService()->getPersonUnitDetail($userDetail->ID_Person);

		if(!preg_match('/^[1-9]{1}[0-9a-zA-Z]{2}\.[0-9a-zA-Z]{1}[0-9a-zA-Z]{1}$/', $membership->RegistrationNumber)) {
			$skautisUserUnit = $this->getUserService()->getParentUnitDetail($membership->ID_Unit)[0];
		} else {
			$skautisUserUnit = $this->getUserService()->getUnitDetail($membership->ID_Unit);
		}

		$visitor = new VisitorEntity;
		$visitor->name = $skautisUser->FirstName;
		$visitor->surname = $skautisUser->LastName;
		$visitor->nick = $skautisUser->NickName;
		$visitor->email = $skautisUser->Email;
		$visitor->street = $skautisUser->Street;
		$visitor->city = $skautisUser->City;
		$visitor->postal_code = preg_replace('/\s+/', '', $skautisUser->Postcode);
		$visitor->birthday = (new DateTime($skautisUser->Birthday))->format('d. m. Y');
		$visitor->group_name = $skautisUserUnit->DisplayName;
		$visitor->group_num = $skautisUserUnit->RegistrationNumber;
		if(isset($membership->Unit)) {
			$visitor->troop_name = $membership->Unit;
		}

		return $visitor;
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

	/**
	 * @return ProgramService
	 */
	protected function getProgramService()
	{
		return $this->programService;
	}

	/**
	 * @param  ProgramService $service
	 * @return $this
	 */
	protected function setProgramService(ProgramService $service)
	{
		$this->programService = $service;

		return $this;
	}

}
