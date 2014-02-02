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
	 * Heading of the page
	 * @var string
	 */
	private $heading = '';

	/**
	 * Action what to do next
	 * @var string
	 */
	private $todo = '';

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

	private $Container;
	private $Block;
	private $View;
	private $Emailer;

	/**
	 * Prepare model classes and get meeting id
	 */
	public function __construct()
	{
		if($this->meetingId = requested("mid","")){
			$_SESSION['meetingID'] = $this->meetingId;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}

		$this->Container = new Container($GLOBALS['cfg'], $this->meetingId);
		$this->Block = $this->Container->createBlock();
		$this->View = $this->Container->createView();
		$this->Emailer = $this->Container->createEmailer();
	}

	/**
	 * This is the default function that will be called by Router.php
	 * 
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init(array $getVars)
	{
		$id = requested("id",$this->blockId);
		$this->cms = requested("cms","");
		$this->error = requested("error","");
		$this->page = requested("page","");

		######################### PRISTUPOVA PRAVA ################################

		if($this->cms != 'annotation') {
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
				$formkey = intval(requested("formkey",""));
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
				elseif($key == 'program') $value = 0;
				elseif($key == 'display_progs') $value = 1;
				else $value = "";
				$this->data[$key] = requested($key, $value);
		}
	}

	/**
	 * Create new item in DB
	 * @return void
	 */
	private function create()
	{
		foreach($this->Block->formNames as $key) {
				if($key == 'start_hour') $value = date("H");
				elseif($key == 'end_hour') $value = date("H")+1;
				elseif($key == 'start_minute') $value = date("i");
				elseif($key == 'end_minute') $value = date("i");
				elseif($key == 'program') $value = 0;
				elseif($key == 'display_progs') $value = 1;
				else $value = "";
				$$key = requested($key, $value);	
		}

		$from = date("H:i:s",mktime($start_hour,$start_minute,0,0,0,0));
		$to = date("H:i:s",mktime($end_hour,$end_minute,0,0,0,0));
		
		//TODO: dodelat osetreni chyb
		if($from > $to) echo "chyba";
		else {
			foreach($this->Block->dbColumns as $key) {
				$db_data[$key] = $$key;	
			}
			$db_data['from'] = $from;
			$db_data['to'] = $to;
			$db_data['capacity'] = 0;
			$db_data['meeting'] = $this->meetingId;
		}

		if($this->Block->create($db_data)){	
			redirect("?page=".$this->page."&error=ok");
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
		
		$dbData = mysql_fetch_assoc($this->Block->getData($id));
		
		foreach($this->Block->formNames as $key) {
			$this->data[$key] = requested($key, $dbData[$key]);
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
		foreach($this->Block->formNames as $key) {
				if($key == 'start_hour') $value = date("H");
				elseif($key == 'end_hour') $value = date("H")+1;
				elseif($key == 'start_minute') $value = date("i");
				elseif($key == 'end_minute') $value = date("i");
				elseif($key == 'program') $value = 0;
				elseif($key == 'display_progs') $value = 1;
				else $value = "";
				$$key = requested($key, $value);	
		}

		$from = date("H:i:s",mktime($start_hour,$start_minute,0,0,0,0));
		$to = date("H:i:s",mktime($end_hour,$end_minute,0,0,0,0));
		
		//TODO: dodelat osetreni chyb
		if($from > $to) echo "chyba";
		else {
			foreach($this->Block->dbColumns as $key) {
				$DB_data[$key] = $$key;	
			}
			$DB_data['from'] = $from;
			$DB_data['to'] = $to;
			$DB_data['capacity'] = 0;
			$DB_data['meeting'] = $this->meetingId;
		}
		
		if($this->Block->update($id, $DB_data)){	
			redirect("?page=".$this->page."&error=ok");
		}
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
		$pid = requested("pid","");
		if($this->Emailer->tutor($pid, $mid, "block")) {
			redirect("?block&error=mail_send");
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

		$mid = (($hash - 39147) / 116)%10;
		$id = floor((($formkey - 39147) / 116) / 10);

		$this->blockId = $id;
		
		$dbData = mysql_fetch_assoc($this->Block->getData($id));
		
		foreach($this->Block->formNames as $key) {
			$this->data[$key] = requested($key, $dbData[$key]);
		}
		$this->data['formkey'] = requested("formkey", "");
		$this->data['type'] = requested("type", "");
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


			$hours_array = array (0 => "00","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23");
			$minutes_array = array (00 => "00", 05 => "05", 10 => "10",15 => "15", 20 => "20",25 => "25", 30 => "30",35 => "35", 40 => "40", 45 => "45", 50 => "50", 55 => "55");

			// category select box
			$cat_roll = CategoryModel::renderHtmlSelect($this->data['category']);
			// time select boxes
			$day_roll = Form::renderHtmlSelectBox('day', array('pátek'=>'pátek', 'sobota'=>'sobota', 'neděle'=>'neděle'), $this->data['day'], 'width:172px;');
			$hour_roll = Form::renderHtmlSelectBox('start_hour', $hours_array, $this->data['start_hour']);
			$minute_roll = Form::renderHtmlSelectBox('start_minute', $minutes_array, $this->data['start_minute']);
			$end_hour_roll = Form::renderHtmlSelectBox('end_hour', $hours_array, $this->data['end_hour']);
			$end_minute_roll = Form::renderHtmlSelectBox('end_minute', $minutes_array, $this->data['end_minute']);
			// is program block check box
			$program_checkbox = Form::renderHtmlCheckBox('program', 1, $this->data['program']);
			// display programs in block check box
			$display_progs_checkbox = Form::renderHtmlCheckBox('display_progs', 0, $this->data['display_progs']);
		}

		if($this->cms != 'annotation') {
			/* HTTP Header */
			$this->View->loadTemplate('http_header');
			$this->View->assign('config',		$GLOBALS['cfg']);
			$this->View->assign('style',		CategoryModel::getStyles());
			$this->View->render(TRUE);

			/* Application Header */
			$this->View->loadTemplate('header');
			$this->View->assign('config',		$GLOBALS['cfg']);
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
			$this->View->assign('hour_roll',			$hour_roll);
			$this->View->assign('minute_roll',			$minute_roll);
			$this->View->assign('end_hour_roll',		$end_hour_roll);
			$this->View->assign('end_minute_roll',		$end_minute_roll);
			$this->View->assign('program_checkbox',		$program_checkbox);
			$this->View->assign('display_progs_checkbox',$display_progs_checkbox);
			$this->View->assign('type',					$this->data['type']);
			$this->View->assign('hash',					$this->data['formkey']);
			$this->View->assign('formkey',				((int)$this->blockId.$this->meetingId) * 116 + 39147);
		}

		$this->View->render(TRUE);

		/* Footer */
		$this->View->loadTemplate('footer');
		$this->View->render(TRUE);
	}
}