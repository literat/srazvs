<?php

namespace App\Presenters;

use Nette\Database\Context;
use Nette\DI\Container;
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
	 * Visitor model
	 * @var VisitorModel
	 */
	private $Visitor;

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
	 * Meeting class
	 * @var Meeting
	 */
	private $Meeting;

	/**
	 * Meal class
	 * @var Meal
	 */
	private $Meal;

	/**
	 * Program class
	 * @var Program
	 */
	private $Program;

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
	private $user;

	/**
	 * Prepare model classes and get meeting id
	 */
	public function __construct(Context $database, Container $container)
	{
		$this->item = '';

		$this->templateDir = 'registration';
		$this->page = 'registration';

		$this->database = $database;
		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->Visitor = $this->container->createServiceVisitor();
		$this->Emailer = $this->container->createServiceEmailer();
		$this->Export = $this->container->createServiceExports();
		$this->Meeting = $this->container->createServiceMeeting();
		$this->Meal = $this->container->createServiceMeal();
		$this->Program = $this->container->createServiceProgram();
		$this->block = $this->container->createServiceBlock();
		$this->latte = $this->container->getService('latte');

		$this->debugMode = $this->container->parameters['debugMode'];

		if($this->meetingId = $this->requested('mid', '')){
			$_SESSION['meetingID'] = $this->meetingId;
		} elseif($this->debugMode) {
			$this->meetingId = 1;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}

		$this->Program->setMeetingId($this->meetingId);
		$this->Meeting->setMeetingId($this->meetingId);
		$this->Meeting->setHttpEncoding($this->container->parameters['encoding']);

		if($this->debugMode) {
			$this->Meeting->setRegistrationHandlers(1);
			$this->meetingId = 1;
		} else {
			$this->Meeting->setRegistrationHandlers();
		}

		$this->user = $this->container->getService('userService');
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
			case "new":
				$this->__new();
				break;
			case "create":
				$this->create();
				break;
			case "edit":
				$this->edit($this->router->getParameter('id'));
				break;
			case "update":
				$this->update($this->router->getParameter('id'));
				break;
			case "check":
				$this->check($this->router->getParameter('id'));
				break;
			default:
				$this->__new();
				break;
		}

		$this->render();
	}

	/**
	 * Prepare page for new item
	 *
	 * @return void
	 */
	private function __new()
	{
		$this->template = 'form';

		$this->heading = "nový program";
		$this->todo = "create";

		// requested for meals
		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = $this->requested($var_name, 'ne');
			$this->mealData[$var_name] = $$var_name;
		}

		// requested for visitors fields
		foreach($this->Visitor->dbColumns as $key) {
			if($key == 'bill') $value = 0;
			else $value = "";
			$this->data[$key] = $this->requested($key, $value);
		}
	}

	/**
	 * Process data from form
	 *
	 * @return void
	 */
	private function create()
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
	private function update($guid)
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
	 * Prepare data for editing
	 *
	 * @param  string  $guid
	 * @return void
	 */
	private function edit($guid)
	{
		$this->template = 'form';

		$this->heading = "úprava programu";
		$this->todo = "update";

		$this->data = $this->Visitor->findByGuid($guid);
		$this->itemId = $this->data->id;
		$this->meetingId = $this->data->meeting;
		$this->Meeting->setRegistrationHandlers($this->data->meeting);
		$this->mealData = $this->Meal->findByVisitorId($this->data->id);
	}

	/**
	 * Prepare data for check
	 *
	 * @param  string  $guid
	 * @return void
	 */
	private function check($guid)
	{
		$this->template = 'check';

		$this->heading = "kontrola přihlášky";
		$this->todo = NULL;

		$this->data = $this->Visitor->findByGuid($guid);
		$this->itemId = $this->data->id;
		$this->meetingId = $this->data->meeting;
		$this->Meeting->setRegistrationHandlers($this->data->meeting);
		$this->mealData = $this->Meal->findByVisitorId($this->data->id);
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
}
