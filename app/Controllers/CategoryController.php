<?php
/**
 * This file handles the retrieval and serving of news articles
 */
class CategoryController extends BaseController
{
	/**
	 * template
	 * @var string
	 */
	private $template = 'listing';

	/**
	 * template directory
	 * @var string
	 */
	private $templateDir = 'category';

	/**
	 * meeting ID
	 * @var integer
	 */
	private $meetingId = 0;

	/**
	 * category ID
	 * @var integer
	 */
	private $categoryId = NULL;

	/**
	 * action what to do
	 * @var string
	 */
	private $cms = '';

	/**
	 * page where to return
	 * @var string
	 */
	private $page = 'category';

	/**
	 * data
	 * @var array
	 */
	private $data = array();

	/**
	 * error handler
	 * @var string
	 */
	private $error = '';

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
	 * View model
	 * @var View
	 */
	private $View;

	/**
	 * Prepare initial values
	 */
	public function __construct($database, $container)
	{
		$this->database = $database;
		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->Category = $this->container->createServiceCategory();
		$this->View = $this->container->createServiceView();

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
		include_once(INC_DIR.'access.inc.php');

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

		if($this->Category->modify($id, $db_data)){
			redirect("?".$this->page."&error=ok");
		}
	}

	/**
	 * Render entire page
	 * @return void
	 */
	private function render()
	{
		$error = "";

		/* HTTP Header */
		$this->View->loadTemplate('http_header');
		$this->View->assign('style',		$this->Category->getStyles());
		$this->View->render(TRUE);

		/* Application Header */
		$this->View->loadTemplate('header');
		$this->View->assign('database',		$this->database);
		$this->View->render(TRUE);

		// load and prepare template
		$this->View->loadTemplate($this->templateDir.'/'.$this->template);
		$this->View->assign('heading',	$this->heading);
		$this->View->assign('todo',		$this->todo);
		$this->View->assign('error',	printError($this->error));
		$this->View->assign('render',	$this->Category->getData());
		$this->View->assign('cms',		$this->cms);
		$this->View->assign('mid',		$this->meetingId);
		$this->View->assign('page',		$this->page);

		if(!empty($this->data)) {
			$this->View->assign('id',		$this->categoryId);
			$this->View->assign('name',		$this->data['name']);
			$this->View->assign('bgcolor',	$this->data['bgcolor']);
			$this->View->assign('bocolor',	$this->data['bocolor']);
			$this->View->assign('focolor',	$this->data['focolor']);
		}

		$this->View->render(TRUE);

		/* Footer */
		$this->View->loadTemplate('footer');
		$this->View->render(TRUE);
	}
}
