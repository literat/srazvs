<?php
/**
 * Block controller
 *
 * This file handles the retrieval and serving of blocks
 *
 * @created 2013-06-03
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class BlockController extends BaseController
{
	/**
	 * This template variable will hold the 'this->View' portion of our MVC for this
	 * controller
	 */
	private $template = 'listing';

	/**
	 * ID of item
	 * @var integer
	 */
	private $blockId = NULL;

	/**
	 * ID of meeting
	 * @var integer
	 */
	private $meetingId = 0;

	/**
	 * Error
	 * @var string
	 */
	private $error = '';
	/**
	 * Action command
	 * @var string
	 */
	private $cms;

	/**
	 * Page where to go
	 * @var string
	 */
	private $page = '';

	/**
	 * DB data
	 * @var array
	 */
	private $data = array();

	private $container;
	private $Block;
	private $View;
	private $Emailer;
	private $Meeting;
	private $Category;

	/**
	 * Prepare model classes and get meeting id
	 */
	public function __construct($database, $container)
	{
		$this->database = $database;
		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->debugMode = $this->container->parameters['debugMode'];
		$this->Block = $this->container->createServiceBlock();
		$this->View = $this->container->createServiceView();
		$this->Emailer = $this->container->createServiceEmailer();
		$this->Meeting = $this->container->createServiceMeeting();
		$this->Category = $this->container->createServiceCategory();

		if($this->meetingId = $this->requested('mid', '')){
			$_SESSION['meetingID'] = $this->meetingId;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}

		$this->Block->setMeetingId($this->meetingId);
		$this->Meeting->setMeetingId($this->meetingId);
		$this->Meeting->setHttpEncoding($this->container->parameters['encoding']);

		if($this->debugMode){
			$this->Meeting->setRegistrationHandlers(1);
			$this->meetingId = 1;
		} else {
			$this->Meeting->setRegistrationHandlers();
		}
	}

	/**
	 * This is the default function that will be called by Router.php
	 *
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init()
	{
		$id = $this->requested('id', $this->blockId);
		$this->cms = $this->requested('cms', '');
		$this->error = $this->requested('error', '');
		$this->page = $this->requested('page', '');

		######################### PRISTUPOVA PRAVA ################################

		if($this->cms != 'annotation' && $this->page != 'annotation') {
			include_once(INC_DIR.'access.inc.php');
		}

		###########################################################################

		switch($this->cms) {
			case "delete":
				$this->delete($id);
				break;
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
			case "mail":
				$this->mail();
				break;
			case "annotation":
				$formkey = intval($this->requested('formkey', ''));
				$this->annotation($formkey);
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

		$this->heading = "nový blok";
		$this->todo = "create";

		foreach($this->Block->formNames as $key) {
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
	private function create()
	{
		$postData = $this->router->getPost();

		foreach($this->Block->formNames as $key) {
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
			foreach($this->Block->dbColumns as $key) {
				$db_data[$key] = $$key;
			}
			$from = date("H:i:s",mktime($start_hour,$start_minute,0,0,0,0));
			$to = date("H:i:s",mktime($end_hour,$end_minute,0,0,0,0));
			$db_data['from'] = $from;
			$db_data['to'] = $to;
			$db_data['capacity'] = 0;
			$db_data['meeting'] = $this->meetingId;
		}

		if($this->Block->create($db_data)){
			redirect(PRJ_DIR.$this->page."?error=ok");
		}
	}

	/**
	 * Prepare data for editing
	 *
	 * @param  int $id of Block
	 * @return void
	 */
	private function edit($id)
	{
		$this->template = 'form';

		$this->heading = "úprava bloku";
		$this->todo = "modify";

		$this->blockId = $id;

		$dbData = $this->Block->getData($id);

		foreach($this->Block->formNames as $key) {
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
	private function update($id)
	{
		$postData = $this->router->getPost();

		foreach($this->Block->formNames as $key) {
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
			foreach($this->Block->dbColumns as $key) {
				$DB_data[$key] = $$key;
			}

			if($this->page != 'annotation') {
				$from = date("H:i:s",mktime($start_hour, $start_minute,0,0,0,0));
				$to = date("H:i:s",mktime($end_hour, $end_minute,0,0,0,0));
				$DB_data['from'] = $from;
				$DB_data['to'] = $to;
				//$DB_data['capacity'] = 0;
			} else {
				$DB_data['program'] = $_POST['program'];
			}

			//$DB_data['meeting'] = $this->meetingId;
		}

		$this->Block->update($id, $DB_data);

		if($this->page == 'annotation') {
			$queryString = "&error=ok&formkey=".$this->requested('formkey', '')."&type=".$this->requested('type', '');
		} else {
			$queryString = "?error=ok";
		}

		redirect(PRJ_DIR . $this->page . $queryString);
	}

	/**
	 * Delete block by id
	 *
	 * @param  int $id of Block
	 * @return void
	 */
	private function delete($id)
	{
		if($this->Block->delete($id)) {
			  	redirect("?block&error=del");
		}
	}

	/**
	 * Send mail to tutor
	 *
	 * @return void
	 */
	private function mail()
	{
		$pid = $this->requested('pid', '');
		//$hash = $this->formKeyHash($pid, $this->meetingId);
		$tutors = $this->Block->getTutor($pid);
		$recipients = $this->parseTutorEmail($tutors);

		if($this->Emailer->tutor($recipients, $this->meetingId, 'block')) {
			redirect("?block&error=mail_send");
		} else {
			redirect("block?id=".$pid."&error=email&cms=edit");
		}
	}

	/**
	 * Prepare data for annotation
	 *
	 * @param  int $id of item
	 * @return void
	 */
	private function annotation($formkey)
	{
		$this->template = 'annotation';

		$this->heading = "úprava bloku";
		$this->todo = "modify";

		$mid = (($formkey - 39147) / 116)%100;
		$id = floor((($formkey - 39147) / 116) / 100);

		$this->Meeting->setRegistrationHandlers();

		$this->blockId = $id;

		$dbData = $this->Block->getData($id);

		foreach($this->Block->formNames as $key) {
			$this->data[$key] = $this->requested($key, $dbData[$key]);
		}
		$this->data['formkey'] = $this->requested('formkey', '');
		$this->data['type'] = $this->requested('type', '');
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
			$cat_roll = $this->Category->renderHtmlSelect($this->data['category'], $this->database);
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

		if($this->cms != 'annotation') {
			/* HTTP Header */
			$this->View->loadTemplate('http_header');
			$this->View->assign('style',		$this->Category->getStyles());
			$this->View->render(TRUE);

			/* Application Header */
			$this->View->loadTemplate('header');
			$this->View->assign('user',		$this->getUser($_SESSION[SESSION_PREFIX.'user']));
			$this->View->assign('meeting',	$this->getPlaceAndYear($_SESSION['meetingID']));
			$this->View->assign('menu',		$this->generateMenu());
			$this->View->render(TRUE);
		}

		// load and prepare template
		$this->View->loadTemplate('blocks/'.$this->template);
		$this->View->assign('heading',	$this->heading);
		$this->View->assign('todo',		$this->todo);
		$this->View->assign('error',	printError($this->error));

		$this->View->assign('cms',		$this->cms);
		$this->View->assign('render',	$this->Block->getData());
		$this->View->assign('mid',		$this->meetingId);
		$this->View->assign('page',		$this->page);

		if(!empty($this->data)) {
			$this->View->assign('id',					$this->blockId);
			$this->View->assign('name',					$this->data['name']);
			$this->View->assign('description',			$this->data['description']);
			$this->View->assign('tutor',				$this->data['tutor']);
			$this->View->assign('email',				$this->data['email']);
			$this->View->assign('error_name',			printError($error_name));
			$this->View->assign('error_description',	printError($error_description));
			$this->View->assign('error_tutor',			printError($error_tutor));
			$this->View->assign('error_email',			printError($error_email));
			$this->View->assign('cat_roll',				$cat_roll);
			$this->View->assign('day_roll',				$day_roll);
			$this->View->assign('day',					$this->data['day']);
			$this->View->assign('from',					$this->data['from']);
			$this->View->assign('to',					$this->data['to']);
			$this->View->assign('program',				$this->data['program']);
			$this->View->assign('hour_roll',			$hour_roll);
			$this->View->assign('minute_roll',			$minute_roll);
			$this->View->assign('end_hour_roll',		$end_hour_roll);
			$this->View->assign('end_minute_roll',		$end_minute_roll);
			$this->View->assign('program_checkbox',		$program_checkbox);
			$this->View->assign('display_progs_checkbox',$display_progs_checkbox);
			$this->View->assign('type',					isset($this->data['type']) ? $this->data['type'] : NULL);
			$this->View->assign('hash',					isset($this->data['formkey']) ? $this->data['formkey'] : NULL);
			$this->View->assign('formkey',				((int)$this->blockId.$this->meetingId) * 116 + 39147);
			$this->View->assign('meeting_heading',		$this->Meeting->getRegHeading());
			$this->View->assign('capacity',				$this->data['capacity']);
			$this->View->assign('category',				$this->data['category']);
			$this->View->assign('block',				$this->itemId);
			$this->View->assign('error_material',		printError($error_material));
			$this->View->assign('material',				$this->data['material']);
			$this->View->assign('page_title',			'Registrace programů pro lektory');
		}

		$this->View->render(TRUE);

		/* Footer */
		$this->View->loadTemplate('footer');
		$this->View->render(TRUE);
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
