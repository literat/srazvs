<?php

namespace App\Presenters;

use App\Components\Forms\Factories\IRegistrationFormFactory;
use App\Components\Forms\RegistrationForm;
use App\Entities\VisitorEntity;
use App\Models\MealModel;
use App\Models\MeetingModel;
use App\Models\SettingsModel;
use App\Repositories\ProgramRepository;
use App\Repositories\VisitorRepository;
use App\Services\Emailer;
use App\Services\Skautis\EventService;
use App\Services\Skautis\UserService;
use DateTime;
use Nette\Utils\ArrayHash;
use Skautis\Wsdl\AuthenticationException;

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

	/**
	 * @var bool
	 */
	protected $error = false;

	/**
	 * @var string
	 */
	protected $hash = null;

	/**
	 * @param MeetingModel      $meetingModel
	 * @param UserService       $userService
	 * @param MealModel         $mealModel
	 * @param ProgramRepository $programRepository
	 * @param VisitorRepository $visitorRepository
	 * @param SettingsModel     $settingsModel
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
	 * @param IRegistrationFormFactory $factory
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

		if ($this->getDebugMode() || $this->getSettingsModel()->findDebugRegime()) {
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

	public function actionCreate($visitor): string
	{
		try {
			$guid = $this->getVisitorRepository()->create($visitor);
			$result = $this->sendRegistrationSummary($visitor, $guid);

			$this->logInfo(
				'Creation of registration(%s) successfull, result: %s',
				[
					$guid,
					json_encode($result),
				]
			);
			$this->flashSuccess("Registrace({$guid}) byla úspěšně založena.");
		} catch (\Exception $e) {
			$this->logError(
				'Creation of registration failed, result: %s',
				[
					$e->getMessage(),
				]
			);
			$this->flashError('Uložení účastníka selhalo, chyba: ' . $e->getMessage());
		}

		return $guid;
	}

	public function actionUpdate($guid, $visitor): string
	{
		try {
			$result = $this->getVisitorRepository()->updateByGuid($guid, $visitor);
			$result = $this->sendRegistrationSummary($visitor, $guid);

			$this->logInfo(
				'Modification of registration(%s) successfull, result: %s',
				[
					$guid,
					json_encode($result),
				]
			);
			$this->flashSuccess("Registrace({$guid}) byla úspěšně upravena.");
		} catch (\Exception $e) {
			$this->logError(
				'Modification of registration(%s) failed, result: %s',
				[
					$guid,
					$e->getMessage(),
				]
			);
			$this->flashError('Uložení účastníka selhalo, chyba: ' . $e->getMessage());
			$result = false;
		}

		return $guid;
	}

	public function beforeRender()
	{
		parent::beforeRender();
	}

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

	public function renderNew()
	{
		$template = $this->getTemplate();
		$disabled = $this->getMeetingModel()->isRegOpen($this->getDebugMode()) ? "" : "disabled";
		$template->disabled = $disabled;
		$template->loggedIn = false;
	}

	public function renderCheck(string $guid)
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

	protected function createComponentRegistrationForm(): RegistrationForm
	{
		$control = $this->registrationFormFactory->create();
		$control->setMeetingId($this->getMeetingId());
		$control->onRegistrationSave[] = function ($visitor) {
			$guid = $this->getParameter('guid');

			if ($guid) {
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

	protected function useLoggedVisitor(): VisitorEntity
	{
		$userDetail = $this->getUserService()->getUserDetail();
		$skautisUser = $this->getUserService()->getPersonalDetail($userDetail->ID_Person);
		$membership = $this->getUserService()->getPersonUnitDetail($userDetail->ID_Person);

		if (!preg_match('/^[1-9]{1}[0-9a-zA-Z]{2}\.[0-9a-zA-Z]{1}[0-9a-zA-Z]{1}$/', $membership->RegistrationNumber)) {
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
		if (isset($membership->Unit)) {
			$visitor->troop_name = $membership->Unit;
		}

		return $visitor;
	}

	protected function getMealModel(): MealModel
	{
		return $this->mealModel;
	}

	protected function setMealModel(MealModel $model): self
	{
		$this->mealModel = $model;

		return $this;
	}

	protected function getMeetingModel(): MeetingModel
	{
		return $this->meetingModel;
	}

	protected function getUserService(): UserService
	{
		return $this->userService;
	}

	protected function setUserService(UserService $service): self
	{
		$this->userService = $service;

		return $this;
	}

	protected function getEventService(): EventService
	{
		return $this->skautisEventService;
	}

	protected function setEventService(EventService $service): self
	{
		$this->skautisEventService = $service;

		return $this;
	}

	protected function getSettingsModel(): SettingsModel
	{
		return $this->settingsModel;
	}

	protected function setSettingsModel(SettingsModel $model): self
	{
		$this->settingsModel = $model;

		return $this;
	}

	protected function getProgramRepository(): ProgramRepository
	{
		return $this->programRepository;
	}

	protected function setProgramRepository(ProgramRepository $repository): self
	{
		$this->programRepository = $repository;

		return $this;
	}
}
