<?php

namespace App\Presenters;

use Nette\Database\Context;
use Nette\DI\Container;

/**
 * This file handles the retrieval and serving of news articles
 */
class CategoryPresenter extends BasePresenter
{

	const PATH = '/srazvs/category';
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
	 * Prepare initial values
	 */
	public function __construct(Context $database, Container $container)
	{
		$this->database = $database;
		$this->setContainer($container);
		$this->setRouter($container->parameters['router']);
		$this->setModel($container->getService('category'));
		$this->setLatte($container->getService('latte'));

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
		$this->error = $this->requested('error', '');
		$this->page = $this->requested('page', $this->page);

		######################### DELETE CATEGORY #########################

		$action = $this->getRouter()->getParameter('action');

		switch($action) {
			case "delete":
				$this->actionDelete($id);
				break;
			case "new":
				$this->actionNew();
				break;
			case "create":
				$this->actionCreate();
				break;
			case "edit":
				$this->actionEdit($id);
				break;
			case "modify":
				$this->actionUpdate($id);
				break;
		}

		$this->render();
	}

	/**
	 * Prepare new item
	 * @return void
	 */
	private function actionNew()
	{
		$this->heading = "nová kategorie";
		$this->todo = "create";
		$this->template = "form";

		foreach($this->getModel()->dbColumns as $key) {
			$this->data[$key] = $this->requested($key, '');
		}
	}

	/**
	 * Delete item
	 * @param  int $id of item
	 * @return void
	 */
	private function actionDelete($id)
	{
		if($this->getModel()->delete($id)){
			redirect(self::PATH . "?page=category&error=del");
		}
	}

	/**
	 * Create new item in DB
	 * @return void
	 */
	private function actionCreate()
	{
		$model = $this->getModel();

		foreach($model->dbColumns as $key) {
			$db_data[$key] = $this->requested($key, '');
		}

		if($model->create($db_data)){
			redirect(self::PATH . "?page=" . $this->page . "&error=ok");
		}
	}

	/**
	 * Prepare form page
	 * @param  int $id of item
	 * @return void
	 */
	private function actionEdit($id)
	{
		$this->heading = "úprava kategorie";
		$this->todo = "modify";
		$this->template = "form";
		$this->categoryId = $id;

		$model = $this->getModel();
		$category = $model->find($id);

		foreach($model->dbColumns as $key) {
			$this->data[$key] = $this->requested($key, $category[$key]);
		}
	}

	/**
	 * Update item in DB
	 * @param  int $id of item
	 * @return void
	 */
	private function actionUpdate($id)
	{
		foreach($this->getModel()->dbColumns as $key) {
			$db_data[$key] = $this->requested($key, '');
		}

		$this->getModel()->modify($id, $db_data);

		redirect(self::PATH . "?page=" . $this->page . "&error=ok");
	}

	/**
	 * Render entire page
	 * @return void
	 */
	private function render()
	{
		$model = $this->getModel();

		$parameters = [
			'cssDir'	=> CSS_DIR,
			'jsDir'		=> JS_DIR,
			'imgDir'	=> IMG_DIR,
			'catDir'	=> CAT_DIR,
			'style'		=> $model->getStyles(),
			'user'		=> $this->getSunlightUser($_SESSION[SESSION_PREFIX.'user']),
			'meeting'	=> $this->getPlaceAndYear($_SESSION['meetingID']),
			'menu'		=> $this->generateMenu(),
			'error'		=> printError($this->error),
			'todo'		=> $this->todo,
			'cms'		=> $this->cms,
			'render'	=> $model->getData(),
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
