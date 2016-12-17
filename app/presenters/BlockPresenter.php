<?php

namespace App\Presenters;

use Nette\Database\Context;
use Nette\Http\Request;
use Tracy\Debugger;
use App\Emailer;
use App\Models\BlockModel;
use \Exception;

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

	private $emailer;

	/** @var string template directory */
	protected $templateDir = 'blocks';

	public function __construct(BlockModel $model, Request $request)
	{
		$this->setModel($model);
		$this->setRequest($request);
	}

	public function startup()
	{
		parent::startup();
		$this->getModel()->setMeetingId($this->getMeetingId());
	}

	/**
	 * Prepare model classes and get meeting id
	 */
	// public function __construct(Context $database, Container $container)
	// {
	// 	$this->setRouter($this->container->parameters['router']);
	// 	$this->debugMode = $this->container->parameters['debugMode'];
	// 	$this->setEmailer($this->container->getService('emailer'));


	// 	$this->getModel()->setMeetingId($this->meetingId);
	// 	$this->getMeeting()->setMeetingId($this->meetingId);
	// 	$this->getMeeting()->setHttpEncoding($this->container->parameters['encoding']);

	// 	if($this->debugMode){
	// 		$this->getMeeting()->setRegistrationHandlers(1);
	// 		$this->meetingId = 1;
	// 	} else {
	// 		$this->getMeeting()->setRegistrationHandlers();
	// 	}
	// }

	/**
	 * This is the default function that will be called by Router.php
	 *
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init()
	{
		$parameters = $this->getRouter()->getParameters();
		$id = $this->requested('id', $this->blockId);
		$this->error = $this->requested('error', '');
		$this->page = $this->requested('page', '');

		$this->setAction($parameters['action']);

		switch($parameters['action']) {
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
	 * @return void
	 */
	public function renderNew()
	{
		$template = $this->getTemplate();

		$template->heading = 'nový blok';
		$template->page = $this->getRequest()->getQuery('page');
		$template->error_name = "";
		$template->error_description = "";
		$template->error_tutor = "";
		$template->error_email = "";
		$template->error_material = "";

		$template->day_roll = $this->renderHtmlSelectBox('day', $this->days, null, 'width:172px;');
		$template->hour_roll = $this->renderHtmlSelectBox('start_hour', $this->hours, date('H'));
		$template->minute_roll = $this->renderHtmlSelectBox('start_minute', $this->minutes, date('i'));
		$template->end_hour_roll = $this->renderHtmlSelectBox('end_hour', $this->hours, date('H')+1);
		$template->end_minute_roll = $this->renderHtmlSelectBox('end_minute', $this->minutes, date('i'));
		// is program block check box
		$template->program_checkbox = $this->renderHtmlCheckBox('program', 1, 0);
		// display programs in block check box
		$template->display_progs_checkbox = $this->renderHtmlCheckBox('display_progs', 0, null);
		$template->selectedCategory	= null;
	}

	/**
	 * @return void
	 */
	public function actionCreate()
	{
		$model = $this->getModel();
		$data = $this->getRequest()->getPost();

		$page = $data['page'];
		$data['from'] = date('H:i:s', mktime($data['start_hour'], $data['start_minute'], 0, 0, 0, 0));
		$data['to'] = date('H:i:s', mktime($data['end_hour'], $data['end_minute'], 0, 0, 0, 0));
		$data['meeting'] = $this->getMeetingId();

		unset($data['start_hour']);
		unset($data['end_hour']);
		unset($data['start_minute']);
		unset($data['end_minute']);
		unset($data['page']);

		try {
			$this->guardToGreaterThanFrom($data['from'], $data['to']);
			$result = $this->getModel()->create($data);

			Debugger::log('Creation of block successfull, result: ' . json_encode($result), Debugger::INFO);

			$this->flashMessage('Položka byla úspěšně vytvořena', 'ok');
		} catch(Exception $e) {
			Debugger::log('Creation of block with data ' . json_encode($data) . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);

			$this->flashMessage('Creation of block failed, result: ' . $e->getMessage(), 'error');
		}

		// TODO: redirect using page
		$this->redirect('Block:listing');
	}

	/**
	 * Process data from editing
	 *
	 * @param  int 	$id 	of Block
	 * @return void
	 */
	public function actionUpdate($id)
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
			$this->page = '';
		}

		redirect(self::PAGE . '/' . $this->page . $queryString);
	}

	/**
	 * Delete block by id
	 *
	 * @param  int $id of Block
	 * @return void
	 */
	public function actionDelete($id)
	{
		if($this->getModel()->delete($id)) {
				redirect(self::PAGE . "?error=del");
		}
	}

	/**
	 * Prepare data for editing
	 *
	 * @param  int $id of Block
	 * @return void
	 */
	public function renderEdit($id)
	{
		$template = $this->getTemplate();

		$template->heading = 'úprava bloku';
		$template->page = $this->getRequest()->getQuery('page');
		$template->error_name = "";
		$template->error_description = "";
		$template->error_tutor = "";
		$template->error_email = "";
		$template->error_material = "";

		$this->blockId = $id;
		$block = $this->getModel()->find($id);
		$template->block = $block;
		$template->id = $id;

		$template->day_roll = $this->renderHtmlSelectBox('day', $this->days, $block->day, 'width:172px;');
		$template->hour_roll = $this->renderHtmlSelectBox('start_hour', $this->hours, $block->from->format('%H'));
		$template->minute_roll = $this->renderHtmlSelectBox('start_minute', $this->minutes, $block->from->format('%I'));
		$template->end_hour_roll = $this->renderHtmlSelectBox('end_hour', $this->hours, $block->to->format('%H'));
		$template->end_minute_roll = $this->renderHtmlSelectBox('end_minute', $this->minutes, $block->to->format('%I'));
		// is program block check box
		$template->program_checkbox = $this->renderHtmlCheckBox('program', 1, $block->program);
		// display programs in block check box
		$template->display_progs_checkbox = $this->renderHtmlCheckBox('display_progs', 0, $block->display_progs);
		$template->selectedCategory	= $block->category;
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
	public function renderAnnotation($id)
	{
		$template = $this->getTemplate();

		$template->page_title = 'Registrace programů pro lektory';
		$template->page = $this->getRequest()->getQuery('page');
		$template->error_name = "";
		$template->error_description = "";
		$template->error_tutor = "";
		$template->error_email = "";
		$template->error_material = "";

		$block = $this->getModel()->findBy('guid', $id);
		$this->blockId = $block->id;
		$template->block = $block;
		$template->id = $id;
	}

	/**
	 * @return void
	 */
	public function renderListing()
	{
		$model = $this->getModel();
		$template = $this->getTemplate();

		$template->blocks = $model->all();
		$template->mid = $this->meetingId;
		$template->heading = $this->heading;
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
			'action'	=> $this->getAction(),
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
				'categories'		=> $this->getCategory()->all(),
				'selectedCategory'	=> $this->data['category'],
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

	/**
	 * @param  date $from
	 * @param  date $to
	 * @return Exception
	 */
	private function guardToGreaterThanFrom($from, $to)
	{
		if($from > $to) {
			throw new Exception('Starting time is greater then finishing time.');
		}
	}

}
