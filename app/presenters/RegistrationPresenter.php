<?php

namespace App\Presenters;

use App\Models\MeetingModel;
use App\Models\VisitorModel;
use App\Models\ProgramModel;
use App\Models\MealModel;
use App\Services\UserService;
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
	 * template
	 * @var string
	 */
	protected $template = 'form';

	/**
	 * @var VisitorModel
	 */
	private $visitorModel;

	/**
	 * Emailer class
	 * @var Emailer
	 */
	private $Emailer;

	/**
	 * Export class
	 * @var Export
	 */
	private $Export;

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
	 * Block class
	 * @var Block
	 */
	private $block;

	/**
	 * Error
	 * @var array
	 */
	protected $error = FALSE;

	protected $hash = NULL;
	private $item;
	private $disabled;
	private $mealData;

	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @param Request      $request
	 * @param MeetingModel $meetingModel
	 * @param UserService  $userService
	 * @param VisitorModel $visitorModel
	 * @param MealModel    $mealModel
	 * @param ProgramModel $programModel
	 */
	public function __construct(
		Request $request,
		MeetingModel $meetingModel,
		UserService $userService,
		VisitorModel $visitorModel,
		MealModel $mealModel,
		ProgramModel $programModel
	) {
		$this->setRequest($request);
		$this->setMeetingModel($meetingModel);
		$this->setUserService($userService);
		$this->setVisitorModel($visitorModel);
		$this->setMealModel($mealModel);
		$this->setProgramModel($programModel);
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
	}

	/**
	 * This is the default function that will be called by Router.php
	 *
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init()
	{
		$id = $this->requested('id', (isset($this->itemId)) ? $this->itemId : '');
		$this->cms = $this->requested('cms', '');
		$this->error = $this->requested('error', '');
		$this->page = $this->requested('page', '');
		$search = $this->requested('search', '');
		$this->disabled = $this->requested('disabled', '');

		if($ids = $this->requested('checker')){
			$query_id = NULL;
			foreach($ids as $key => $value) {
				$query_id .= $value.',';
			}
			$query_id = rtrim($query_id, ',');
		}
		else {
			$query_id = $ids;
		}

		$this->action = $this->router->getParameter('action') ? $this->router->getParameter('action') : $this->cms;

		switch($this->action) {
			case "create":
				$this->create();
				break;
			case "update":
				$this->update($this->router->getParameter('id'));
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
		$blocks = $this->block->idsFromCurrentMeeting($this->meetingId);

		foreach($blocks as $block){
			$$block['id'] = $this->requested('blck_' . $block->id, 0);
			$programs_data[$block->id] = $$block['id'];
			//echo $block->id.":".$$block->id."|";
		}

		// requested for visitors
		foreach($this->Visitor->dbColumns as $column) {
				if($column == 'bill') $$column = $this->requested($column, 0);
				elseif($column == 'birthday') {
					$$column = $this->cleardate2DB($this->requested($column, 0), 'Y-m-d');
				}
				else $$column = $this->requested($column, '');
				$newVisitor[$column] = $$column;
		}

		// i must add visitor's ID because it is empty
		$newVisitor['meeting'] = $this->meetingId;

		// requested for meals
		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = $this->requested($var_name, null);
			$meals_data[$var_name] = $$var_name;
		}

		// create
		if($guid = $this->Visitor->create($newVisitor, $meals_data, $programs_data, true)) {
			######################## ODESILAM EMAIL ##########################
			Debugger::log('Creating Visitor ' . $guid, 'info');
			// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
			$code4bank = $this->code4Bank($newVisitor);

			$recipient_mail = $newVisitor['email'];
			$recipient_name = $newVisitor['name']." ".$newVisitor['surname'];
			$recipient = [$recipient_mail => $recipient_name];

			$return = $this->Emailer->sendRegistrationSummary($recipient, $guid, $code4bank);

			if($return === TRUE) {
				Debugger::log('Mail send to ' . $recipient_mail, 'info');
				redirect('/srazvs/registration/check/' . $guid . '?error=ok');
			} else {
				Debugger::log('Mail not send to ' . $recipient_mail, 'error');
				redirect('/srazvs/registration/check/' . $guid . '?error=email');
			}
		} else {
			Debugger::log('Visitor not created', 'error');
			redirect("?error=error");
		}
	}

	/**
	 * Process data from editing
	 *
	 * @param  int 	$id 	of item
	 * @return void
	 */
	public function actionUpdate($guid)
	{
		// TODO
		////ziskani zvolenych programu
		$blocks = $this->block->idsFromCurrentMeeting($this->meetingId);

		foreach($blocks as $block){
			$$block['id'] = $this->requested('blck_' . $block->id, 0);
			$programs_data[$block->id] = $$block['id'];
			//echo $block->id.":".$$block->id."|";
		}

		foreach($this->Visitor->dbColumns as $column) {
			if($column == 'bill') $$column = $this->requested($column, 0);
			elseif($column == 'birthday') {
				$$column = $this->cleardate2DB($this->requested($column, 0), 'Y-m-d');
			}
			else $$column = $this->requested($column, null);
			$db_data[$column] = $$column;
		}

		// I must add meeting's ID because it is empty
		$db_data['meeting'] = $this->meetingId;

		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = $this->requested($var_name, null);
			$meals_data[$var_name] = $$var_name;
		}

		// I must add visitor's ID because it is empty
		$meals_data['visitor'] = $this->router->getpost('id');

		if($guid = $this->Visitor->modifyByGuid($guid, $db_data, $meals_data, $programs_data)){
			######################## ODESILAM EMAIL ##########################
			Debugger::log('Visitor ' . $guid . ' was modified', 'info');
			// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
			$code4bank = substr($db_data['name'], 0, 1).substr($db_data['surname'], 0, 1).substr($db_data['birthday'], 2, 2);

			$recipient_mail = $db_data['email'];
			$recipient_name = $db_data['name']." ".$db_data['surname'];
			$recipient = [$recipient_mail => $recipient_name];

			$return = $this->Emailer->sendRegistrationSummary($recipient, $guid, $code4bank);

			if($return === TRUE) {
				Debugger::log('Mail send to ' . $recipient_mail, 'info');
				redirect('/srazvs/registration/check/' . $guid . '?error=ok');
			} else {
				Debugger::log('Mail not send to ' . $recipient_mail, 'error');
				redirect('/srazvs/registration/check/' . $guid . '?error=email');
			}
		} else {
			Debugger::log('Visitor modification failed!', 'error');
			redirect("?error=error");
		}
	}

	/**
	 * @return void
	 */
	public function renderDefault()
	{
		$template = $this->getTemplate();

		$template->page_title = "Registrace srazu VS";
		$template->meeting_heading = $this->getMeetingModel()->getRegHeading();
		////otevirani a uzavirani prihlasovani
		$template->disabled = $this->getMeetingModel()->isRegOpen($this->getDebugMode()) ? "" : "disabled";
		$template->loggedIn = $this->getUserService()->isLoggedIn();
		$template->isRegistrationOpen = $this->getMeetingModel()->isRegOpen($this->getDebugMode());

		// requested for visitors fields
		foreach($this->getVisitorModel()->columns as $column) {
			$data[$column] = '';
		}
		$template->data = $data;

		$template->meals = $this->getMealModel()->renderHtmlMealsSelect($this->mealData, $this->disabled);
		$template->province = $this->getMeetingModel()->renderHtmlProvinceSelect(null);
		$template->programs = $this->getVisitorModel()->renderProgramSwitcher($this->meetingId, $this->itemId);
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
		$template = $this->getTemplate();

		$this->heading = "kontrola přihlášky";

		$data = $this->getVisitorModel()->findByGuid($guid);
		$template->meals = $this->getMealModel()->findByVisitorId($data->id);

		$template->data = $data;
		$this->itemId = $data->id;
		$this->meetingId = $data->meeting;
		$this->getMeetingModel()->setRegistrationHandlers($data->meeting);
		$this->mealData = $this->getMealModel()->findByVisitorId($data->id);
		$template->page_title = "Registrace srazu VS";
		$template->isRegistrationOpen = $this->getMeetingModel()->isRegOpen($this->getDebugMode());
		$template->guid = $guid;
		$template->province = $this->getMeetingModel()->getProvinceNameById($data->province);
		$template->programs = $this->getProgramModel()->getSelectedPrograms($data->id);
	}

	public function renderEdit($guid)
	{
		$template = $this->getTemplate();

		$template->heading = 'úprava programu';

		$data = $this->getVisitorModel()->findByGuid($guid);
		$template->data = $data;
		$template->meetingId = $data->meeting;
		$this->getMeetingModel()->setRegistrationHandlers($data->meeting);
		$mealData = $this->getMealModel()->findByVisitorId($data->id);
		$template->mealData = $mealData;
		$template->page_title = "Registrace srazu VS";
		$template->isRegistrationOpen = $this->getMeetingModel()->isRegOpen($this->getDebugMode());
		$template->loggedIn = $this->getUserService()->isLoggedIn();
		$template->disabled = $this->getMeetingModel()->isRegOpen($this->getDebugMode()) ? "" : "disabled";
		$template->meals = $this->getMealModel()->renderHtmlMealsSelect($mealData, $this->disabled);
		$template->province = $this->getMeetingModel()->renderHtmlProvinceSelect($data->province);
		$template->programs = $this->getVisitorModel()->renderProgramSwitcher($this->getMeetingId(), $data->id);
		$template->guid = $guid;
		$template->cost	= $this->getMeetingModel()->getPrice('cost');
	}

	/**
	 * Render all page
	 *
	 * @return void
	 */
	public function render()
	{
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
			$error_birthday = "";
			$error_street = "";
			$error_city = "";
			$error_group_name = "";

			if($this->action == 'check') {
				$meals_select = $this->mealData;
				$province_select = $this->Meeting->getProvinceNameById($this->data['province']);
				$program_switcher = $this->Program->getSelectedPrograms($this->itemId);
			} else {
				$meals_select = $this->Meal->renderHtmlMealsSelect($this->mealData, $this->disabled);
				$province_select = $this->Meeting->renderHtmlProvinceSelect($this->data['province']);
				$program_switcher = $this->Visitor->renderProgramSwitcher($this->meetingId, $this->itemId);
			}

		}

		$parameters = [
			'cssDir'	=> CSS_DIR,
			'jsDir'		=> JS_DIR,
			'imgDir'	=> IMG_DIR,
			'wwwDir'	=> HTTP_DIR,
			'error'		=> printError($this->error),
			'todo'		=> $this->todo,
			'cms'		=> $this->cms,
			'render'	=> $this->Visitor->getData(),
			'mid'		=> $this->meetingId,
			'page'		=> $this->page,
			'heading'	=> $this->heading,
			'page_title'=> "Registrace srazu VS",
			'meeting_heading'	=> $this->Meeting->getRegHeading(),
			////otevirani a uzavirani prihlasovani
			'disabled'	=> $this->Meeting->isRegOpen($this->debugMode) ? "" : "disabled",
			'loggedIn'	=> $this->user->isLoggedIn(),
		];

		if($this->user->isLoggedIn()) {
			$userDetail = $this->user->getUserDetail();
			$skautisUser = $this->user->getPersonalDetail($userDetail->ID_Person);
			$membership = $this->user->getPersonUnitDetail($userDetail->ID_Person);

			if(!preg_match('/^[1-9]{1}[0-9a-zA-Z]{2}\.[0-9a-zA-Z]{1}[0-9a-zA-Z]{1}$/', $membership->RegistrationNumber)) {
				$skautisUserUnit = $this->user->getParentUnitDetail($membership->ID_Unit)[0];
			} else {
				$skautisUserUnit = $this->user->getUnitDetail($membership->ID_Unit);
			}

			$this->data['name'] = $skautisUser->FirstName;
			$this->data['surname'] = $skautisUser->LastName;
			$this->data['nick'] = $skautisUser->NickName;
			$this->data['email'] = $skautisUser->Email;
			$this->data['street'] = $skautisUser->Street;
			$this->data['city'] = $skautisUser->City;
			$this->data['postal_code'] = preg_replace('/\s+/','',$skautisUser->Postcode);
			$this->data['birthday'] = $skautisUser->Birthday;
			$this->data['group_name'] = $skautisUserUnit->DisplayName;
			$this->data['group_num'] = $skautisUserUnit->RegistrationNumber;
			if(isset($membership->Unit)) {
				$this->data['troop_name'] = $membership->Unit;
			}
		}

		if(!empty($this->data)) {
			$parameters = array_merge($parameters, [
				'id'				=> isset($this->itemId) ? $this->itemId : '',
				'data'				=> $this->data,
				'birthday'			=> date_format(date_create($this->data['birthday']),"d.m.Y"),
				'province'			=> $province_select,
				'meals'				=> $meals_select,
				'cost'				=> $this->Meeting->getPrice('cost'),
				'checked'			=> empty($this->data['checked']) ? '0' : $this->data['checked'],
				'programs'			=> $program_switcher,
				'guid'				=> isset($this->data->guid) ? $this->data->guid : '',
				'isRegistrationOpen'		=> $this->Meeting->isRegOpen($this->debugMode),
			]);
		}

		$this->latte->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);
	}

	private function cleardate2DB ($inputDate, $formatDate)
	{
				//list($d, $m, $r) = split("[/.-]", $input_datum);
				list($d, $m, $r) = preg_split("[/|\.|-]", $inputDate);
				// beru prvni znak a delam z nej integer
				$rtest = $r{0};
				$rtest += 0;
				$mtest = $m{0};
				$mtest += 0;

				// pokud je to nula, musim odstranit prvni znak
				if(($rtest) == 0) $r = substr($r, 1);
				if(($mtest) == 0) $m = substr($m, 1);

				$d += 0; $m += 0; $r += 0;
				$date = date("$formatDate",mktime(0,0,0,$m,$d,$r));
				return $date;
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
