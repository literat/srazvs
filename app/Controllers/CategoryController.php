<?php
/**
 * This file handles the retrieval and serving of news articles
 */
class CategoryController
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
	 * heading tetxt
	 * @var string
	 */
	private $heading = '';

	/**
	 * action what to do next
	 * @var string
	 */
	private $todo = '';

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
	 * Container class
	 * @var [type]
	 */
	private $Container;

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
	public function __construct()
	{
		if($this->meetingId = requested("mid","")){
			$_SESSION['meetingID'] = $this->meetingId;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}

		$this->Container = new Container($GLOBALS['cfg'], $this->meetingId);
		$this->Category = $this->Container->createCategory();
		$this->View = $this->Container->createView();
	}

	/**
	 * This is the default function that will be called by router.php
	 * 
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init(array $getVars)
	{
		include_once(INC_DIR.'access.inc.php');

		########################## KONTROLA ###############################

		//$mid = $_SESSION['meetingID'];
		$id = requested("id",$this->categoryId);
		$this->cms = requested("cms","");
		$this->error = requested("error","");
		$this->page = requested("page",$this->page);

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
			$this->data[$key] = requested($key, "");	
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
			$db_data[$key] = requested($key, "");	
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
				
		$query = "SELECT * FROM kk_categories WHERE id = ".$id." LIMIT 1"; 
		$db_data = mysql_fetch_assoc(mysql_query($query));
				
		foreach($this->Category->dbColumns as $key) {
			$this->data[$key] = requested($key, $db_data[$key]);	
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
			$db_data[$key] = requested($key, "");
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
		$this->View->assign('config',		$GLOBALS['cfg']);
		$this->View->assign('style',		$this->Category->getStyles());
		$this->View->render(TRUE);

		/* Application Header */
		$this->View->loadTemplate('header');
		$this->View->assign('config',		$GLOBALS['cfg']);
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