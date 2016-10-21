<?php

namespace App\Presenters;

use Nette\Database\Context;
use Nette\DI\Container;

/**
 * This file handles the retrieval and serving of news articles
 */
class MeetingPresenter extends BasePresenter
{
	/**
	 * This template variable will hold the 'view' portion of our MVC for this
	 * controller
	 */
	protected $template = 'view';

	/**
	 * template directory
	 * @var string
	 */
	protected $templateDir = 'meeting';

	/**
	 * page where to return
	 * @var string
	 */
	protected $page = 'meeting';

	private $render = NULL;

	/**
	 * meeting ID
	 * @var integer
	 */
	protected $meetingId = 0;

	/**
	 * Container class
	 * @var [type]
	 */
	private $Container;

	/**
	 * Category model
	 * @var Category
	 */
	private $Category;

	private $container;
	private $Meeting;

	/**
	 * Prepare initial values
	 */
	public function __construct(Context $database, Container $container)
	{
		$this->database = $database;
		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->Meeting = $this->container->createServiceMeeting();
		$this->Category = $this->container->createServiceCategory();
		$this->latte = $this->container->getService('latte');

		if($this->meetingId = $this->requested('mid', '')){
			$_SESSION['meetingID'] = $this->meetingId;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}

		$this->Meeting->setMeetingId($this->meetingId);
		$this->Meeting->setHttpEncoding($this->container->parameters['encoding']);
	}

	/**
	 * This is the default function that will be called by router.php
	 *
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init()
	{
		$id = $this->requested('id', $this->meetingId);
		$this->cms = $this->requested('cms', '');
		$this->error = $this->requested('error', '');

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
			case "list-view":
				$this->template = 'listing';
				$this->render = $this->Meeting->getData();
				$this->data = $this->Meeting->getData($this->meetingId);
				break;
			default:
				$this->render = $this->Meeting->renderProgramOverview();
				$this->data = $this->Meeting->getData($this->meetingId);
				break;
		}

		$this->render();
	}


	/**
	 * Prepare new item
	 * @return void
	 */
	private function __new()
	{
		$this->heading = "nová kategorie";
		$this->todo = "create";
		$this->template = "form";

		foreach($this->Meeting->dbColumns as $key) {
			$this->data[$key] = $this->requested($key, "");
		}
	}

	/**
	 * Delete item
	 * @param  int $id of item
	 * @return void
	 */
	private function delete($id)
	{
		if($this->Meeting->delete($id)){
	  		redirect("?error=del");
		}
	}

	/**
	 * Create new item in DB
	 * @return void
	 */
	private function create()
	{
		foreach($this->Meeting->dbColumns as $key) {
			$db_data[$key] = $this->requested($key, "");
		}

		if($this->Meeting->create($db_data)){
			redirect(PRJ_DIR.$this->page."?error=ok");
		}
	}

	/**
	 * Prepare form page
	 * @param  int $id of item
	 * @return void
	 */
	private function edit($id)
	{
		$this->template = 'form';

		$this->todo = "modify";
		// get meeting's data
		$this->data = $this->Meeting->getData($id);

		foreach($this->Meeting->dbColumns as $key) {
			$$key = $this->requested($key, $this->data[$key]);
		}
	}

	/**
	 * Update item in DB
	 * @param  int $id of item
	 * @return void
	 */
	private function update($id)
	{
		foreach($this->Meeting->dbColumns as $key) {
			$db_data[$key] = $this->requested($key, "");
		}

		if($this->Meeting->update($this->meetingId, $db_data)){
			redirect(PRJ_DIR.$this->page."?error=ok");
		}
	}

	/**
	 * Render entire page
	 * @return void
	 */
	private function render()
	{
		////inicializace promenych
		$error_start = "";
		$error_end = "";
		$error_open_reg = "";
		$error_close_reg = "";
		$error_login = "";

		/* HTTP Header */
		$parameters = [
			'cssDir'			=> CSS_DIR,
			'jsDir'				=> JS_DIR,
			'imgDir'			=> IMG_DIR,
			'style'				=> $this->Category->getStyles(),
			'user'				=> $this->getSunlightUser($_SESSION[SESSION_PREFIX.'user']),
			'meeting'			=> $this->getPlaceAndYear($_SESSION['meetingID']),
			'menu'				=> $this->generateMenu(),
			'error'				=> printError($this->error),
			'cms'				=> $this->cms,
			'render'			=> $this->render,
			'mid'				=> $this->meetingId,
			'error_start'		=> printError($error_start),
			'error_end'			=> printError($error_end),
			'error_open_reg'	=> printError($error_open_reg),
			'error_close_reg'	=> printError($error_close_reg),
			'error_login'		=> printError($error_login),
			'page'				=> $this->page,
		];

		if(!empty($this->data)) {
			$parameters['data'] = $this->data;
			$parameters['todo'] = $this->todo;
		}

		$this->latte->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);
	}

}
