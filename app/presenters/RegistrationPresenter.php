<?php

namespace App\Presenters;

use DateTime;
use Exception;
use App\Entities\VisitorEntity;
use App\Models\MeetingModel;
use App\Models\MealModel;
use App\Services\SkautIS\UserService;
use App\Services\Emailer;
use App\Repositories\VisitorRepository;
use App\Repositories\ProgramRepository;
use App\Components\Forms\RegistrationForm;
use App\Components\Forms\Factories\IRegistrationFormFactory;
use App\Services\SkautIS\EventService;
use Nette\Utils\ArrayHash;
use App\Models\SettingsModel;
use Skautis\Wsdl\AuthenticationException;

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
	 * @var UserService
	 */
	private $userService;

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
	 * @var MealModel
	 */
	private $mealModel;

	/**
	 * @var ProgramRepository
	 */
	private $programRepository;

	protected $error = FALSE;
	protected $hash = NULL;
	private $user;

	/**
	 * @param MeetingModel       $meetingModel
	 * @param UserService        $userService
	 * @param MealModel          $mealModel
	 * @param ProgramRepository  $programRepository
	 * @param VisitorRepository  $visitorRepository
	 * @param SettingsModel      $settingsModel
	 */
	public function __construct(
		MeetingModel $meetingModel,
		UserService $userService,
		MealModel $mealModel,
		Emailer $emailer,
		VisitorRepository $visitorRepository,
		ProgramRepository $programRepository,
		EventService $skautisEvent,
		SettingsModel $settingsModel
	) {
		$this->setMeetingModel($meetingModel);
		$this->setUserService($userService);
		$this->setMealModel($mealModel);
		$this->setEmailer($emailer);
		$this->setVisitorRepository($visitorRepository);
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
	public function actionCreate($visitor)
	{
		try {
			$guid = $this->getVisitorRepository()->create($visitor);
			$result = $this->sendRegistrationSummary($visitor, $guid);

			$this->logInfo('Creation of registration(%s) successfull, result: %s', [
				$guid,
				json_encode($result),
			]);
			$this->flashSuccess("Registrace({$guid}) byla úspěšně založena.");
		} catch(Exception $e) {
			$this->logError('Creation of registration failed, result: %s', [
				$e->getMessage(),
			]);
			$this->flashError('Uložení účastníka selhalo, chyba: ' . $e->getMessage());
		}

		return $guid;
	}

	/**
	 * @param  string  $guid
	 * @return void
	 */
	public function actionUpdate($guid, $visitor)
	{
		try {
			$result = $this->getVisitorRepository()->updateByGuid($guid, $visitor);
			$result = $this->sendRegistrationSummary($visitor, $guid);

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
			$this->flashError('Uložení účastníka selhalo, chyba: ' . $e->getMessage());
			$result = false;
		}

		return $guid;
	}

	public function beforeRender()
	{
		parent::beforeRender();
	}

	/**
	 * Renders default template
	 */
	public function renderDefault()
	{
		$template = $this->getTemplate();
		$disabled = $this->getMeetingModel()->isRegOpen($this->getDebugMode()) ? "" : "disabled";
		$template->disabled = $disabled;
		$template->user = $this->getUser();
		$template->backlink = $this->getHttpRequest()->getUrl()->getAbsoluteUrl();

		try {
			if ($this->getUser()->isLoggedIn()) {
				$this['registrationForm']->setDefaults(($this->useLoggedVisitor())->toArray());
			}
		} catch (AuthenticationException $e) {
			$this->flashFailure('Uživatel byl odhlášen! Přihlaste se prosím znovu.');
			$this->getUser()->logout();
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
		$visitor = $this->getVisitorRepository()->findByGuid($guid);

		$this->getMeetingModel()->setRegistrationHandlers($visitor->meeting);

		$template = $this->getTemplate();
		$template->guid = $guid;
		$template->visitor = $visitor;
		$template->meetingId = $visitor->meeting;
		$template->meals = ArrayHash::from($this->getMealModel()->findByVisitorId($visitor->id));
		$template->province = $this->getMeetingModel()->getProvinceNameById($visitor->province);
		$template->programs = $this->getProgramRepository()->findByVisitorId($visitor->id);
	}

	/**
	 * @param  string $guid
	 * @return void
	 */
	public function renderEdit($guid)
	{
		$visitor = $this->getVisitorRepository()->findExpandedByGuid($guid);
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
		$control->onRegistrationSave[] = function(RegistrationForm $control, $visitor) {
			$guid = $this->getParameter('guid');

			if($guid) {
				$guid = $this->actionUpdate($guid, $visitor);
			} else {
				$guid = $this->actionCreate($visitor);
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
	protected function getSettingsModel(): SettingsModel
	{
		return $this->settingsModel;
	}

	/**
	 * @param SettingsModel $model
	 *
	 * @return self
	 */
	protected function setSettingsModel(SettingsModel $model): self
	{
		$this->settingsModel = $model;

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
	 * @return RegistrationPresenter
	 */
	protected function setProgramRepository(ProgramRepository $repository): self
	{
		$this->programRepository = $repository;

		return $this;
	}

}
