<?php

namespace App\Presenters;

use Nette\Database\Context;
use Nette\DI\Container;
use App\Emailer;
use App\MeetingModel;
use App\CategoryModel;

/**
 * Block controller
 *
 * This file handles the retrieval and serving of blocks
 *
 * @created 2013-06-03
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class BlockPresenter extends BasePresenter
{

	const PAGE = '/srazvs/block';

	/**
	 * This template variable will hold the 'this->View' portion of our MVC for this
	 * controller
	 */
	protected $template = 'listing';

	/**
	 * ID of item
	 * @var integer
	 */
	private $blockId = NULL;

	/**
	 * ID of meeting
	 * @var integer
	 */
	protected $meetingId = 0;

	private $block;
	private $emailer;
	private $meeting;
	private $category;

	/** @var string template directory */
	protected $templateDir = 'blocks';

	private $action;

	/**
	 * Prepare model classes and get meeting id
	 */
	public function __construct(Context $database, Container $container)
	{
		$this->database = $database;
		$this->setContainer($container);
		$this->setRouter($this->container->parameters['router']);
		$this->debugMode = $this->container->parameters['debugMode'];
		$this->setModel($this->container->getService('block'));
		$this->setEmailer($this->container->getService('emailer'));
		$this->setMeeting($this->container->getService('meeting'));
		$this->setCategory($this->container->getService('category'));
		$this->setLatte($this->container->getService('latte'));

		if($this->meetingId = $this->requested('mid', '')){
			$_SESSION['meetingID'] = $this->meetingId;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}

		$this->getModel()->setMeetingId($this->meetingId);
		$this->getMeeting()->setMeetingId($this->meetingId);
		$this->getMeeting()->setHttpEncoding($this->container->parameters['encoding']);

		if($this->debugMode){
			$this->getMeeting()->setRegistrationHandlers(1);
			$this->meetingId = 1;
		} else {
			$this->getMeeting()->setRegistrationHandlers();
		}
	}

	/**
	 * This is the default function that will be called by Router.php
	 *
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init()
	{
		$this->action = $this->requested('action');
		$id = $this->requested('id', $this->blockId);
		$this->cms = $this->requested('cms', '');
		$this->error = $this->requested('error', '');
		$this->page = $this->requested('page', '');

		$action = $this->cms ? $this->cms : $this->action;

		switch($action) {
			case "delete":
				$this->actionDelete($id);
				$this->render();
				break;
			case "new":
				$this->actionNew();
				$this->render();
				break;
			case "create":
				$this->actionCreate();
				$this->render();
				break;
			case "edit":
				$this->actionEdit($id);
				$this->render();
				break;
			case "modify":
				$this->actionUpdate($id);
				$this->render();
				break;
			case "mail":
				$this->mailRender($id);
				break;
			case "annotation":
				if(is_numeric($id)) {
					$this->update($id);
				}
				$this->annotationRender($id);
				break;
			default:
				$this->render();
				break;
		}
	}

	/**
	 * Prepare page for new item
	 *
	 * @return void
	 */
	private function actionNew()
	{
		$this->template = 'form';

		$this->heading = "nový blok";
		$this->todo = "create";

		foreach($this->getModel()->formNames as $key) {
				if($key == 'start_hour') $value = date("H");
				elseif($key == 'end_hour') $value = date("H")+1;
				elseif($key == 'start_minute') $value = date("i");
				elseif($key == 'end_minute') $value = date("i");
				elseif($key == 'program') $value = '0';
				elseif($key == 'display_progs') $value = '1';
				else $value = "";
				$this->data[$key] = $this->requested($key, $value);
		}
	}

	/**
	 * Create new item in DB
	 * @return void
	 */
	private function actionCreate()
	{
		$postData = $this->getRouter()->getPost();

		foreach($this->getModel()->formNames as $key) {
				if(array_key_exists($key, $postData) && !is_null($postData[$key])) {
					$$key = $postData[$key];
				}
				elseif($key == 'start_hour') $$key = date('H');
				elseif($key == 'end_hour') $$key = date('H')+1;
				elseif($key == 'start_minute') $$key = '0';
				elseif($key == 'end_minute') $$key = '0';
				elseif($key == 'program') $$key = '0';
				elseif($key == 'display_progs') $$key = '1';
				else $$key = '';
		}

		//TODO: dodelat osetreni chyb
		if($from > $to) new Exception('From greater than to!');
		else {
			foreach($this->getModel()->dbColumns as $key) {
				$db_data[$key] = $$key;
			}
			$from = date("H:i:s",mktime($start_hour,$start_minute,0,0,0,0));
			$to = date("H:i:s",mktime($end_hour,$end_minute,0,0,0,0));
			$db_data['from'] = $from;
			$db_data['to'] = $to;
			$db_data['capacity'] = 0;
			$db_data['meeting'] = $this->meetingId;
		}

		if($this->getModel()->create($db_data)){
			redirect(PRJ_DIR.$this->page."?error=ok");
		}
	}

	/**
	 * Prepare data for editing
	 *
	 * @param  int $id of Block
	 * @return void
	 */
	private function actionEdit($id)
	{
		$this->template = 'form';

		$this->heading = "úprava bloku";
		$this->todo = "modify";

		$this->blockId = $id;

		$dbData = $this->getModel()->getData($id);

		foreach($this->getModel()->formNames as $key) {
			if($key == 'from' || $key == 'to') $value = $dbData[$key]->format('%H:%I:%S');
			else $value = $dbData[$key];
			$this->data[$key] = $this->requested($key, $value);
		}
	}

	/**
	 * Process data from editing
	 *
	 * @param  int 	$id 	of Block
	 * @return void
	 */
	private function actionUpdate($id)
	{
		$postData = $this->getRouter()->getPost();

		foreach($this->getModel()->formNames as $key) {
				if(array_key_exists($key, $postData) && !is_null($postData[$key])) {
					$$key = $postData[$key];
				}
				elseif($key == 'start_hour') $$key = date('H');
				elseif($key == 'end_hour') $$key = date('H')+1;
				elseif($key == 'start_minute') $$key = '0';
				elseif($key == 'end_minute') $$key = '0';
				elseif($key == 'program') $$key = '0';
				elseif($key == 'display_progs') $$key = '1';
				else $$key = '';
		}

		//TODO: dodelat osetreni chyb
		if($from > $to) echo "chyba";
		else {
			foreach($this->getModel()->dbColumns as $key) {
				$DB_data[$key] = $$key;
			}

			$from = date("H:i:s",mktime($start_hour, $start_minute,0,0,0,0));
			$to = date("H:i:s",mktime($end_hour, $end_minute,0,0,0,0));
			$DB_data['from'] = $from;
			$DB_data['to'] = $to;
			$DB_data['program'] = $this->requested('program');
		}

		$this->getModel()->update($id, $DB_data);

		if($this->page == 'annotation') {
			$queryString = '/' . $DB_data['guid'] . '?error=ok';
		} else {
			$queryString = "?error=ok";
		}

		redirect(self::PAGE . '/' . $this->page . $queryString);
	}

	/**
	 * Delete block by id
	 *
	 * @param  int $id of Block
	 * @return void
	 */
	private function actionDelete($id)
	{
		if($this->getModel()->delete($id)) {
				redirect(self::PAGE . "?error=del");
		}
	}

	/**
	 * Send mail to tutor
	 *
	 * @return void
	 */
	private function mailRender($id)
	{
		$tutors = $this->getModel()->getTutor($id);
		$recipients = $this->parseTutorEmail($tutors);

		if($this->getEmailer()->tutor($recipients, $tutors->guid, 'block')) {
			redirect(self::PAGE . '?error=mail_send');
		} else {
			redirect(self::PAGE . '/edit/' . $id . '?error=email');
		}
	}

	/**
	 * Prepare data for annotation
	 *
	 * @param  int $id of item
	 * @return void
	 */
	private function annotationRender($guid)
	{
		$this->template = 'annotation';

		$this->heading = "úprava bloku";
		$this->todo = "modify";

		$data = $this->getModel()->annotation($guid);

		$this->getMeeting()->setRegistrationHandlers();

		$this->blockId = $data['id'];

		$error_name = "";
		$error_description = "";
		$error_tutor = "";
		$error_email = "";
		$error_material = "";

		$parameters = [
			'cssDir'	=> CSS_DIR,
			'jsDir'		=> JS_DIR,
			'imgDir'	=> IMG_DIR,
			'blockDir'	=> BLOCK_DIR,
			'wwwDir'	=> HTTP_DIR,
			'error'		=> printError($this->error),
			'todo'		=> $this->todo,
			'cms'		=> $this->cms,
			'data'		=> $data,
			'mid'		=> $this->meetingId,
			'id'		=> $this->blockId,
			'page'		=> $this->page,
			'heading'	=> $this->heading,
			'page_title'=> 'Registrace programů pro lektory',
			'meeting_heading'	=> $this->getMeeting()->getRegHeading(),
			'error_name'		=> printError($error_name),
			'error_description'	=> printError($error_description),
			'error_tutor'		=> printError($error_tutor),
			'error_email'		=> printError($error_email),
			'error_material'	=> printError($error_material),
		];

		$this->getLatte()->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);
	}

	/**
	 * Render all page
	 *
	 * @return void
	 */
	public function render()
	{
		$error = "";
		if(!empty($this->data)) {
			$error_name = "";
			$error_description = "";
			$error_tutor = "";
			$error_email = "";
			$error_material = "";


			$hours_array = array (0 => "00","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23");
			$minutes_array = array (00 => "00", 05 => "05", 10 => "10",15 => "15", 20 => "20",25 => "25", 30 => "30",35 => "35", 40 => "40", 45 => "45", 50 => "50", 55 => "55");

			// category select box
			$cat_roll = $this->getCategory()->renderHtmlSelect($this->data['category'], $this->database);
			// time select boxes
			$day_roll = $this->renderHtmlSelectBox('day', array('pátek'=>'pátek', 'sobota'=>'sobota', 'neděle'=>'neděle'), $this->data['day'], 'width:172px;');
			$hour_roll = $this->renderHtmlSelectBox('start_hour', $hours_array, $this->data['start_hour']);
			$minute_roll = $this->renderHtmlSelectBox('start_minute', $minutes_array, $this->data['start_minute']);
			$end_hour_roll = $this->renderHtmlSelectBox('end_hour', $hours_array, $this->data['end_hour']);
			$end_minute_roll = $this->renderHtmlSelectBox('end_minute', $minutes_array, $this->data['end_minute']);
			// is program block check box
			$program_checkbox = $this->renderHtmlCheckBox('program', 1, $this->data['program']);
			// display programs in block check box
			$display_progs_checkbox = $this->renderHtmlCheckBox('display_progs', 0, $this->data['display_progs']);
		}

		$parameters = [
			'cssDir'	=> CSS_DIR,
			'jsDir'		=> JS_DIR,
			'imgDir'	=> IMG_DIR,
			'blockDir'	=> BLOCK_DIR,
			'wwwDir'	=> HTTP_DIR,
			'error'		=> printError($this->error),
			'todo'		=> $this->todo,
			'cms'		=> $this->cms,
			'render'	=> $this->getModel()->getData(),
			'mid'		=> $this->meetingId,
			'page'		=> $this->page,
			'heading'	=> $this->heading,
		];

		if($this->cms != 'annotation') {
			$parameters = array_merge($parameters, [
				'style'		=> $this->getStyles(),
				'user'		=> $this->getSunlightUser($_SESSION[SESSION_PREFIX.'user']),
				'meeting'	=> $this->getPlaceAndYear($_SESSION['meetingID']),
				'menu'		=> $this->generateMenu(),
			]);
		}

		if(!empty($this->data)) {

			$parameters = array_merge($parameters, [
				'id'				=> $this->blockId,
				'data'				=> $this->data,
				'error_name'		=> printError($error_name),
				'error_description'	=> printError($error_description),
				'error_tutor'		=> printError($error_tutor),
				'error_email'		=> printError($error_email),
				'cat_roll'			=> $cat_roll,
				'day_roll'			=> $day_roll,
				'hour_roll'			=> $hour_roll,
				'minute_roll'		=> $minute_roll,
				'end_hour_roll'		=> $end_hour_roll,
				'end_minute_roll'	=> $end_minute_roll,
				'program_checkbox'	=> $program_checkbox,
				'display_progs_checkbox'	=> $display_progs_checkbox,
				'formkey'			=> ((int)$this->blockId.$this->meetingId) * 116 + 39147,
				'meeting_heading'	=> $this->getMeeting()->getRegHeading(),
				'block'				=> $this->itemId,
				'error_material'	=> printError($error_material),
				'type'				=> isset($this->data['type']) ? $this->data['type'] : NULL,
				'hash'				=> isset($this->data['formkey']) ? $this->data['formkey'] : NULL,
			]);
		}

		$this->getLatte()->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);
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
	 * @return Meeting
	 */
	protected function getMeeting()
	{
		return $this->meeting;
	}

	/**
	 * @param  MeetingModel $meeting
	 * @return $this
	 */
	protected function setMeeting(MeetingModel $meeting)
	{
		$this->meeting = $meeting;
		return $this;
	}

	/**
	 * @return Category
	 */
	protected function getCategory()
	{
		return $this->category;
	}

	/**
	 * @param  CategoryModel $Category
	 * @return $this
	 */
	protected function setCategory(CategoryModel $category)
	{
		$this->category = $category;
		return $this;
	}

	/**
	 * Render select box
	 *
	 * @param	string	name
	 * @param	array	content of slect box
	 * @param	var		variable that match selected option
	 * @param	string	inline styling
	 * @return	string	html of select box
	 */
	private function renderHtmlSelectBox($name, $select_content, $selected_option, $inline_style = NULL)
	{
		if(isset($inline_style) && $inline_style != NULL){
			$style = " style='".$inline_style."'";
		} else {
			$style = "";
		}
		$html_select = "<select name='".$name."'".$style.">";
		foreach ($select_content as $key => $value) {
			if($key == $selected_option) {
				$selected = 'selected';
			} else {
				$selected = '';
			}
			$html_select .= "<option value='".$key."' ".$selected.">".$value."</option>";
		}
		$html_select .= '</select>';

		return $html_select;
	}
}
