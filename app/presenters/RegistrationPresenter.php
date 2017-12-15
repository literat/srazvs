<?php

namespace App\Presenters;

use DateTime;
use Exception;
use App\Entities\VisitorEntity;
use App\Models\MeetingModel;
use App\Models\VisitorModel;
use App\Models\MealModel;
use App\Services\SkautIS\UserService;
use App\Services\Emailer;
use App\Services\VisitorService;
use App\Repositories\ProgramRepository;
use Tracy\Debugger;
use App\Components\Forms\RegistrationForm;
use App\Components\Forms\Factories\IRegistrationFormFactory;
use App\Services\SkautIS\EventService;
use Skautis\Wsdl\WsdlException;
use App\Models\SettingsModel;

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
	 * @var UserService
	 */
	private $userService;

	/**
	 * @var ProgramRepository
	 */
	private $programRepository;

	/**
	 * @var SettingsModel
	 */
	protected $settingsModel;

	/**
	 * @var boolean
	 */
	private $disabled = false;

	/**
	 * @var IRegistrationFormFactory
	 */
	private $registrationFormFactory;

	/**
	 * @var EventService
	 */
	protected $skautisEventService;

	/**
	 * @param MeetingModel       $meetingModel
	 * @param UserService        $userService
	 * @param VisitorModel       $visitorModel
	 * @param MealModel          $mealModel
	 * @param ProgramRepository  $programRepository
	 * @param VisitorService     $visitorService
	 * @param SettingsModel      $settingsModel
	 */
	public function __construct(
		MeetingModel $meetingModel,
		UserService $userService,
		VisitorModel $visitorModel,
		MealModel $mealModel,
		Emailer $emailer,
		VisitorService $visitorService,
		ProgramRepository $programRepository,
		EventService $skautisEvent,
		SettingsModel $settingsModel
	) {
		$this->setMeetingModel($meetingModel);
		$this->setUserService($userService);
		$this->setVisitorModel($visitorModel);
		$this->setMealModel($mealModel);
		$this->setEmailer($emailer);
		$this->setVisitorService($visitorService);
		$this->setProgramRepository($programRepository);
		$this->setEventService($skautisEvent);
		$this->setSettingsModel($settingsModel);
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

		if($this->getDebugMode() || $this->getSettingsModel()->findDebugRegime()) {
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

			$this->logInfo('Creation of registration(%s) successfull, result: %s', [
				$guid,
				json_encode($result),
			]);
			$this->flashSuccess("Registrace({$guid}) byla úspěšně založena.");

			$this->redirect('Registration:check', $guid);
		} catch(Exception $e) {
			$this->logError('Creation of registration failed, result: %s', [
				$e->getMessage(),
			]);
			$this->flashError('Creation of registration failed, result: ' . $e->getMessage());
		}
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

			$this->logInfo('Modification of registration(%s) successfull, result: %s', [
				$guid,
				json_encode($result),
			]);
			$this->flashSuccess("Registrace({$guid}) byla úspěšně upravena.");
		} catch(Exception $e) {
			$this->logError('Modification of registration(%s) failed, result: %s', [
				$guid,
				$e->getMessage(),
			]);
			$this->flashError("Modification of registration({$guid}) failed, result: " . $e->getMessage());
		}

		$this->redirect('Registration:check', $guid);
	}

	/**
	 * Renders default template
	 */
	public function renderDefault()
	{
		$template = $this->getTemplate();
		$disabled = $this->getMeetingModel()->isRegOpen($this->getDebugMode()) ? "" : "disabled";
		$template->disabled = $disabled;
		$template->loggedIn = $this->getUserService()->isLoggedIn();

		if($this->getUserService()->isLoggedIn()) {
			$this['registrationForm']->setDefaults(($this->useLoggedVisitor())->toArray());
		}
	}

	/**
	 * Renders new template
	 */
	public function renderNew()
	{
		$template = $this->getTemplate();
		$disabled = $this->getMeetingModel()->isRegOpen($this->getDebugMode()) ? "" : "disabled";
		$template->disabled = $disabled;
		$template->loggedIn = false;
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
		$template->programs = $this->getProgramRepository()->findByVisitorId($visitor->id);
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
				$guid = $this->getParameter('guid');

				if($guid) {
					$guid = $this->getVisitorService()->update($guid, (array) $newVisitor);
				} else {
					$guid = $this->getVisitorService()->create((array) $newVisitor);
				}
/*
				if($this->getUserService()->isLoggedIn() && $this->getMeetingModel()->findCourseId()) {
					$this->getEventService()->insertEnroll(
						$this->getUserService()->getSkautis()->getUser()->getLoginId(),
						$this->getMeetingModel()->findCourseId(),
						// TODO: get real phone number
						'123456789'
					);
				}
*/
				$result = $this->sendRegistrationSummary((array) $newVisitor, $guid);

				$this->logInfo('Storage of visitor(%s) successfull, result: %s', [
					$guid,
					json_encode($result),
				]);
				$this->flashSuccess("Účastník({$guid}) byl úspěšně uložen.");
			} catch(WsdlException $e) {
				$this->logWarning('Storage of visitor(%s) failed, result: %s', [
					$guid,
					$e->getMessage(),
				]);
				$this->flashError("Uložení účastníka ({$guid}) selhalo. Účastník je již zaregistrován.");
			} catch(Exception $e) {
				$this->logError('Storage of visitor(%s) failed, result: %s', [
					$guid,
					$e->getMessage(),
				]);
				$this->flashError('Uložení účastníka selhalo, chyba: ' . $e->getMessage());
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
	 * @return ProgramRepository
	 */
	protected function getProgramRepository(): ProgramRepository
	{
		return $this->programRepository;
	}

	/**
	 * @param  ProgramRepository $repository
	 * @return $this
	 */
	protected function setProgramRepository(ProgramRepository $repository): self
	{
		$this->programRepository = $repository;

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
	 * @return EventService
	 */
	protected function getEventService(): EventService
	{
		return $this->skautisEventService;
	}

	/**
	 * @param EventService $skautisEvent
	 *
	 * @return self
	 */
	protected function setEventService(EventService $service): self
	{
		$this->skautisEventService = $service;

		return $this;
	}


	/**
	 * @return SettingsModel
	 */
	public function getSettingsModel()
	{
		return $this->settingsModel;
	}

	/**
	 * @param SettingsModel $model
	 *
	 * @return self
	 */
	public function setSettingsModel(SettingsModel $model): self
	{
		$this->settingsModel = $model;

		return $this;
	}

}
