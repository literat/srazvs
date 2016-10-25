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
	 * Object farm container
	 * @var Container
	 */
	private $container;

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

		if($this->hash = $this->requested('hash')) {
			$data = $this->database
				->table('kk_visitors')
				->select('id, meeting')
				->where('hash', $this->hash)
				->fetch();

			//$this->meetingId = (($getVars['hash'] - 49873) / 147)%10;
			//$id = floor((($getVars['hash'] - 49873) / 147)/10);
			$this->meetingId = $data['meeting'];
			$id = $data['id'];

			$this->Meeting->setRegistrationHandlers($this->meetingId);
			if($this->cms == '') {
				$this->cms = "edit";
			}
		}

		switch($this->cms) {
			case "new":
				$this->__new();
				break;
			case "create":
				$this->create();
				break;
			case "edit":
				$this->edit($id);
				break;
			case "modify":
				$this->update($id);
				break;
			case "check":
				$this->check($id);
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

	private function getblocks()
	{
		return $this->database
			->table('kk_blocks')
			->select('id')
			->where('meeting ? AND program ? AND deleted ?', $this->meetingId, '1', '0')
			->fetchAll();
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
		$blocks = $this->getblocks();

		foreach($blocks as $blockData){
			$$blockData['id'] = $this->requested('blck_' . $blockData['id'], 0);
			$programs_data[$blockData['id']] = $$blockData['id'];
			//echo $blockData['id'].":".$$blockData['id']."|";
		}

		// requested for visitors
		foreach($this->Visitor->dbColumns as $key) {
				if($key == 'bill') $$key = $this->requested($key, 0);
				elseif($key == 'birthday') {
					$$key = $this->cleardate2DB($this->requested($key, 0), 'Y-m-d');
				}
				else $$key = $this->requested($key, '');
				$db_data[$key] = $$key;
		}

		// i must add visitor's ID because it is empty
		$db_data['meeting'] = $this->meetingId;
		$db_data['hash'] = hash('sha1', microtime());

		// requested for meals
		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = $this->requested($var_name, null);
			$meals_data[$var_name] = $$var_name;
		}
		//if(!$this->error) {
			// create
			if($vid = $this->Visitor->create($db_data, $meals_data, $programs_data)) {
				######################## ODESILAM EMAIL ##########################
				Debugger::log('Creating Visitor ' . $vid, 'info');
				// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
				$code4bank = $this->code4Bank($db_data);
				//$hash = ((int)$vid.$this->meetingId) * 147 + 49873;
				$hash = $db_data['hash'];

				$recipient_mail = $db_data['email'];
				$recipient_name = $db_data['name']." ".$db_data['surname'];
				$recipient = [$recipient_mail => $recipient_name];

				$return = $this->Emailer->sendRegistrationSummary($recipient, $hash, $code4bank);

				if($return === TRUE) {
					Debugger::log('Mail send to ' . $recipient_mail, 'info');
					if(is_int($vid)) {
						$vid = "ok";
					}
					redirect("?hash=".$hash."&error=".$vid."&cms=check");
				} else {
					Debugger::log('Mail not send to ' . $recipient_mail, 'error');
					redirect("?hash=".$hash."&error=email&cms=check");
					//echo 'Došlo k chybě při odeslání e-mailu.';
					//echo 'Chybová hláška: ' . $return;
				}
				//redirect("?page=".$this->page."&error=ok");
			} else {
				Debugger::log('Visitor not created', 'error');
				redirect("?page=".$this->page."&error=error");
			}
		//} else {
		//	redirect("?page=".$this->page."&error=error");
		//}
	}

	/**
	 * Process data from editing
	 *
	 * @param  int 	$id 	of item
	 * @return void
	 */
	private function update($id = NULL)
	{
		// TODO
		////ziskani zvolenych programu
		$blocks = $this->getblocks();

		foreach($blocks as $blockData){
			$$blockData['id'] = $this->requested('blck_' . $blockData['id'],0);
			$programs_data[$blockData['id']] = $$blockData['id'];
			//echo $blockData['id'].":".$$blockData['id']."|";
		}

		foreach($this->Visitor->dbColumns as $key) {
				if($key == 'bill') $$key = $this->requested($key, 0);
				elseif($key == 'birthday') {
					$$key = $this->cleardate2DB($this->requested($key, 0), 'Y-m-d');
				}
				else $$key = $this->requested($key, null);
				$db_data[$key] = $$key;
		}

		// i must add visitor's ID because it is empty
		$db_data['meeting'] = $this->meetingId;
		$db_data['hash'] = hash('sha1', microtime());

		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = $this->requested($var_name, null);
			$meals_data[$var_name] = $$var_name;
		}
		// i must add visitor's ID because it is empty
		$meals_data['visitor'] = $id;

		if($vid = $this->Visitor->modify($id, $db_data, $meals_data, $programs_data)){
			######################## ODESILAM EMAIL ##########################
			Debugger::log('Visitor ' . $id . ' was modified', 'info');
			// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
			$code4bank = substr($db_data['name'], 0, 1).substr($db_data['surname'], 0, 1).substr($db_data['birthday'], 2, 2);
			//$hash = ((int)$vid.$this->meetingId) * 147 + 49873;
			$hash = $db_data['hash'];

			$recipient_mail = $db_data['email'];
			$recipient_name = $db_data['name']." ".$db_data['surname'];
			$recipient = [$recipient_mail => $recipient_name];

			$return = $this->Emailer->sendRegistrationSummary($recipient, $hash, $code4bank);

			if($return === TRUE) {
				Debugger::log('Mail send to ' . $recipient_mail, 'info');
				if(is_numeric($vid)) {
					$vid = "ok";
				}
				redirect("?hash=".$hash."&error=".$vid."&cms=check");
			} else {
				Debugger::log('Mail not send to ' . $recipient_mail, 'error');
				redirect("?hash=".$hash."&error=email&cms=check");
				//echo 'Došlo k chybě při odeslání e-mailu.';
				//echo 'Chybová hláška: ' . $return;
			}
			//redirect("?page=".$this->page."&error=ok");
		} else {
			Debugger::log('Visitor modification failed!', 'error');
			redirect("?page=".$this->page."&error=error");
		}
	}

	/**
	 * Prepare data for editing
	 *
	 * @param  int $id of item
	 * @return void
	 */
	private function edit($id)
	{
		$this->template = 'form';

		$this->heading = "úprava programu";
		$this->todo = "modify";

		$this->itemId = $id;

		$dbData = $this->Visitor->getData($id);
		foreach($this->Visitor->dbColumns as $key) {
			$this->data[$key] = $this->requested($key, $dbData[$key]);
		}

		$DB_data = $this->database
			->table('kk_meals')
			->where('visitor', $this->itemId)
			->limit(1)
			->fetch();

		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = $this->requested($var_name, $DB_data[$var_name]);
			$this->mealData[$var_name] = $$var_name;
		}
	}

	/**
	 * Prepare data for check
	 *
	 * @param  int $id of item
	 * @return void
	 */
	private function check($id)
	{
		$this->template = 'check';

		$this->heading = "kontrola přihlášky";
		$this->todo = NULL;

		$this->itemId = $id;

		$dbData = $this->Visitor->getData($id);
		foreach($this->Visitor->dbColumns as $key) {
			$this->data[$key] = $this->requested($key, $dbData[$key]);
		}

		$DB_data = $this->database
			->table('kk_meals')
			->where('visitor', $this->itemId)
			->limit(1)
			->fetch();

		foreach($this->Meal->dbColumns as $var_name) {
			$$var_name = $this->requested($var_name, $DB_data[$var_name]);
			$this->mealData[$var_name] = $$var_name;
		}
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

			if($this->cms == 'check') {
				$meals_select = $this->Meal->getMealsArray($this->itemId);
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
		];

		if($this->user->isLoggedIn()) {
			$skautisUser = $this->user->getPersonalDetail();
			$skautisUnit = $this->user->getUnitDetail();
			$skautisUserUnit = $this->user->getPersonUnitDetail();

			$this->data['name'] = $skautisUser->FirstName;
			$this->data['surname'] = $skautisUser->LastName;
			$this->data['nick'] = $skautisUser->NickName;
			$this->data['email'] = $skautisUser->Email;
			$this->data['street'] = $skautisUser->Street;
			$this->data['city'] = $skautisUser->City;
			$this->data['postal_code'] = preg_replace('/\s+/','',$skautisUser->Postcode);
			$this->data['birthday'] = $skautisUser->Birthday;
			$this->data['group_name'] = $skautisUnit->DisplayName;
			$this->data['group_num'] = $skautisUnit->RegistrationNumber;
			$this->data['troop_name'] = $skautisUserUnit->Unit;
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
				'hash'				=> $this->hash,
				'isRegistrationOpen'		=> $this->Meeting->isRegOpen($this->debugMode),
			]);
		}

		$this->latte->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);
	}
}
