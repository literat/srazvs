<?php

namespace App\Presenters;

use Nette\Database\Context;
use Nette\DI\Container;
use App\Emailer;
use App\MeetingModel;
use App\CategoryModel;
use App\BlockModel;
use App\ExportModel;

/**
 * Program controller
 *
 * This file handles the retrieval and serving of blocks
 *
 * @created 2013-06-05
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class ProgramPresenter extends BasePresenter
{

	const PAGE = '/srazvs/program';
	/**
	 * This template variable will hold the 'this->View' portion of our MVC for this
	 * controller
	 */
	protected $template = 'listing';

	/**
	 * Template directory
	 * @var string
	 */
	protected $templateDir = 'program';

	/**
	 * ID of item
	 * @var integer
	 */
	private $programId = NULL;

	/**
	 * ID of meeting
	 * @var integer
	 */
	protected $meetingId = 0;

	/**
	 * Emailer class
	 * @var Emailer
	 */
	private $emailer;

	/**
	 * Export class
	 * @var Export
	 */
	private $export;

	/**
	 * Category class
	 * @var Category
	 */
	private $category;

	/**
	 * Meeting class
	 * @var Meeting
	 */
	private $meeting;

	private $block;

	/**
	 * Prepare model classes and get meeting id
	 */
	public function __construct(Context $database, Container $container)
	{
		$this->database = $database;
		$this->setContainer($container);
		$this->debugMode = $this->container->parameters['debugMode'];
		$this->setRouter($this->container->parameters['router']);
		$this->setModel($this->container->getService('program'));
		$this->setBlock($this->container->getService('block'));
		$this->setEmailer($this->container->getService('emailer'));
		$this->setExport($this->container->getService('exports'));
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
	 * @return Block
	 */
	protected function getBlock()
	{
		return $this->block;
	}

	/**
	 * @param  BlockModel $block
	 * @return $this
	 */
	protected function setBlock(BlockModel $block)
	{
		$this->block = $block;
		return $this;
	}

	/**
	 * @return Export
	 */
	protected function getExport()
	{
		return $this->export;
	}

	/**
	 * @param  ExportModel $export
	 * @return $this
	 */
	protected function setExport(ExportModel $export)
	{
		$this->export = $export;
		return $this;
	}

	/**
	 * This is the default function that will be called by Router.php
	 *
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init()
	{
		$parameters = $this->getRouter()->getParameters();
		$this->setAction($parameters['action']);
		$id = $this->requested('id', $this->programId);
		$this->cms = $this->requested('cms', '');
		$this->error = $this->requested('error', '');
		$this->page = $this->requested('page', '');

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
			case "public":
				$this->publicView();
				$this->render();
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

		$this->heading = "nový program";
		$this->todo = "create";

		foreach($this->getModel()->formNames as $key) {
				if($key == 'display_in_reg') $value = '1';
				if($key == 'capacity') $value = '0';
				else $value = "";
				$this->data[$key] = $this->requested($key, $value);
		}
	}

	/**
	 * Process data from form
	 *
	 * @return void
	 */
	private function actionCreate()
	{
		$postData = $this->router->getPost();

		foreach($this->getModel()->formNames as $key) {
				if(array_key_exists($key, $postData) && !is_null($postData[$key])) {
					$$key = $postData[$key];
				}
				elseif($key == 'display_in_reg') $$key = '1';
				elseif($key == 'capacity') $$key = '0';
				else $$key = '';
		}

		foreach($this->getModel()->dbColumns as $key) {
			$DB_data[$key] = $$key;
		}

		if($this->getModel()->create($DB_data)){
			redirect(PRJ_DIR . $this->page."?error=ok");
		}
	}


	/**
	 * Process data from editing
	 *
	 * @param  int 	$id 	of item
	 * @return void
	 */
	private function actionUpdate($id = NULL)
	{
		$postData = $this->router->getPost();

		foreach($this->getModel()->formNames as $key) {
				if(array_key_exists($key, $postData) && !is_null($postData[$key])) {
					$$key = $postData[$key];
				}
				elseif($key == 'display_in_reg') $$key = '1';
				else $$key = '';
		}

		foreach($this->getModel()->dbColumns as $key) {
			$DB_data[$key] = $$key;
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
	 * Prepare data for editing
	 *
	 * @param  int $id of item
	 * @return void
	 */
	private function actionEdit($id)
	{
		$this->template = 'form';

		$this->heading = "úprava programu";
		$this->todo = "modify";

		$this->programId = $id;

		$dbData = $this->getModel()->getData($id);

		foreach($this->getModel()->formNames as $key) {
			$this->data[$key] = $this->requested($key, $dbData[$key]);
		}
	}

	/**
	 * Delete item by id
	 *
	 * @param  int $id of item
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

		if($this->getEmailer()->tutor($recipients, $tutors->guid, 'program')) {
			redirect(self::PAGE . '?error=mail_send');
		} else {
			redirect(self::PAGE . '/edit/' . $id . '?error=email');
		}
	}

	/**
	 * View public program
	 *
	 * @return void
	 */
	private function publicView()
	{
		$this->template = 'view';
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

		$this->heading = "úprava programu";
		$this->todo = "modify";

		$data = $this->getModel()->annotation($guid);

		$this->getMeeting()->setRegistrationHandlers();

		$this->programId = $data->id;

		$error_name = "";
		$error_description = "";
		$error_tutor = "";
		$error_email = "";
		$error_material = "";

		$parameters = [
			'cssDir'	=> CSS_DIR,
			'jsDir'		=> JS_DIR,
			'imgDir'	=> IMG_DIR,
			'wwwDir'	=> HTTP_DIR,
			'error'		=> printError($this->error),
			'todo'		=> $this->todo,
			'action'	=> $this->getAction(),
			'data'		=> $data,
			'mid'		=> $this->meetingId,
			'id'		=> $this->programId,
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

		$this->latte->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);
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

			// blocks select box
			$block_select = $this->getBlock()->renderHtmlSelect($this->data['block']);
			// display in registration check box
			$display_in_reg_checkbox = $this->renderHtmlCheckBox('display_in_reg', 0, $this->data['display_in_reg']);
			// time select boxes
		}

		$parameters = [
			'cssDir'	=> CSS_DIR,
			'jsDir'		=> JS_DIR,
			'imgDir'	=> IMG_DIR,
			'wwwDir'	=> HTTP_DIR,
			'expDir'	=> EXP_DIR,
			'progDir'	=> PROG_DIR,
			'error'		=> printError($this->error),
			'todo'		=> $this->todo,
			'action'		=> $this->getAction(),
			'render'	=> $this->getModel()->getData(),
			'mid'		=> $this->meetingId,
			'page'		=> $this->page,
			'heading'	=> $this->heading,
		];

		if($this->action != 'public' && $this->action != 'annotation') {
			$parameters = array_merge($parameters, [
				'style'		=> $this->getStyles(),
				'user'		=> $this->getSunlightUser($_SESSION[SESSION_PREFIX.'user']),
				'meeting'	=> $this->getPlaceAndYear($_SESSION['meetingID']),
				'menu'		=> $this->generateMenu(),
			]);
		}

		if(!empty($this->data)) {
			$parameters = array_merge($parameters, [
				'id'				=> $this->programId,
				'data'				=> $this->data,
				'error_name'		=> printError($error_name),
				'error_description'	=> printError($error_description),
				'error_tutor'		=> printError($error_tutor),
				'error_email'		=> printError($error_email),
				'error_material'	=> printError($error_material),
				'categories'		=> $this->getCategory()->all(),
				'selectedCategory'	=> $this->data['category'],
				'block_select'		=> $block_select,
				'program_visitors'	=> $this->getModel()->getProgramVisitors($this->programId),
				'display_in_reg_checkbox'	=> $display_in_reg_checkbox,
				'formkey'			=> ((int)$this->programId.$this->meetingId) * 116 + 39147,
				'meeting_heading'	=> $this->getMeeting()->getRegHeading(),
				'block'				=> $this->itemId,
				'page_title'		=> 'Registrace programů pro lektory',
				'type'				=> isset($this->data['type']) ? $this->data['type'] : NULL,
				'hash'				=> isset($this->data['formkey']) ? $this->data['formkey'] : NULL,
			]);
		}

		if($this->action == 'public') {
			$parameters['meeting_heading'] = $this->getMeeting()->getRegHeading();
			////otevirani a uzavirani prihlasovani
			if(($this->getMeeting()->getRegOpening() < time()) || $this->debugMode) {
				$parameters['display_program'] = true;
			} else {
				$parameters['display_program'] = false;
			}
			$parameters['public_program'] = $this->getMeeting()->renderPublicProgramOverview();
			$parameters['page_title'] = 'Srazy VS - veřejný program';
			$parameters['style'] = 'table { border-collapse:separate; width:100%; }
				td { .width:100%; text-align:center; padding:0px; }
				td.day { border:1px solid black; background-color:#777777; width:80px; }
				td.time { background-color:#cccccc; width:80px; }';
		} elseif($this->cms == 'annotation') {
			$parameters['meeting_heading'] = $this->getMeeting()->getRegHeading();
			////otevirani a uzavirani prihlasovani
			if(($this->getMeeting()->getRegOpening() < time()) || $this->debugMode) {
				$parameters['display_program'] = true;
			} else {
				$parameters['display_program'] = false;
			}
			$parameters['type'] = $this->data['type'];
			$parameters['formkey'] = $this->data['formkey'];
		}

		$this->latte->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);
	}
}
