<?php

namespace App\Presenters;

use Nette\Database\Context;
use Nette\DI\Container;

/**
 * This file handles the retrieval and serving of news articles
 */
class CategoryPresenter extends BasePresenter
{
	/**
	 * template
	 * @var string
	 */
	protected $template = 'listing';

	/**
	 * template directory
	 * @var string
	 */
	protected $templateDir = 'category';

	/**
	 * meeting ID
	 * @var integer
	 */
	protected $meetingId = 0;

	/**
	 * category ID
	 * @var integer
	 */
	private $categoryId = NULL;

	/**
	 * page where to return
	 * @var string
	 */
	protected $page = 'category';

	/**
	 * DI container
	 * @var Nette\DI\Container
	 */
	private $container;

	/**
	 * Category model
	 * @var Category
	 */
	private $Category;

	/**
	 * Prepare initial values
	 */
	public function __construct(Context $database, Container $container)
	{
		$this->database = $database;
		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->Category = $this->container->createServiceCategory();
		$this->latte = $this->container->getService('latte');

		if($this->meetingId = $this->requested('mid', '')){
			$_SESSION['meetingID'] = $this->meetingId;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}
	}

	/**
	 * This is the default function that will be called by router.php
	 *
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init()
	{
		########################## KONTROLA ###############################

		//$mid = $_SESSION['meetingID'];
		$id = $this->requested('id', $this->categoryId);
		$this->cms = $this->requested('cms', '');
		$this->error = $this->requested('error', '');
		$this->page = $this->requested('page', $this->page);

		######################### DELETE CATEGORY #########################

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

		foreach($this->Category->dbColumns as $key) {
			$this->data[$key] = $this->requested($key, '');
		}
	}

	/**
	 * Delete item
	 * @param  int $id of item
	 * @return void
	 */
	private function delete($id)
	{
		if($this->Category->delete($id)){
	  		redirect("?category&error=del");
		}
	}

	/**
	 * Create new item in DB
	 * @return void
	 */
	private function create()
	{
		foreach($this->Category->dbColumns as $key) {
			$db_data[$key] = $this->requested($key, '');
		}

		if($this->Category->create($db_data)){
			redirect("?".$this->page."&error=ok");
		}
	}

	/**
	 * Prepare form page
	 * @param  int $id of item
	 * @return void
	 */
	private function edit($id)
	{
		$this->heading = "úprava kategorie";
		$this->todo = "modify";
		$this->template = "form";
		$this->categoryId = $id;

		$category = $this->database
			->table('kk_categories')
			->where('id', $id)
			->limit(1)
			->fetch();

		foreach($this->Category->dbColumns as $key) {
			$this->data[$key] = $this->requested($key, $category[$key]);
		}
	}

	/**
	 * Update item in DB
	 * @param  int $id of item
	 * @return void
	 */
	private function update($id)
	{
		foreach($this->Category->dbColumns as $key) {
			$db_data[$key] = $this->requested($key, '');
		}

		$this->Category->modify($id, $db_data);

		redirect("?".$this->page."&error=ok");
	}

	/**
	 * Render entire page
	 * @return void
	 */
	private function render()
	{
		$parameters = [
			'cssDir'	=> CSS_DIR,
			'jsDir'		=> JS_DIR,
			'imgDir'	=> IMG_DIR,
			'catDir'	=> CAT_DIR,
			'style'		=> $this->Category->getStyles(),
			'user'		=> $this->getSunlightUser($_SESSION[SESSION_PREFIX.'user']),
			'meeting'	=> $this->getPlaceAndYear($_SESSION['meetingID']),
			'menu'		=> $this->generateMenu(),
			'error'		=> printError($this->error),
			'todo'		=> $this->todo,
			'cms'		=> $this->cms,
			'render'	=> $this->Category->getData(),
			'mid'		=> $this->meetingId,
			'page'		=> $this->page,
			'heading'	=> $this->heading,
		];

		if(!empty($this->data)) {
			$parameters['id'] = $this->categoryId;
			$parameters['data'] = $this->data;

		}

		$this->latte->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);
	}
}
