<?php
/**
 * This file handles the retrieval and serving of news articles
 */
class CategoryController
{
	/**
	 * This template variable will hold the 'view' portion of our MVC for this 
	 * controller
	 */
	public $template = 'categories';

	private $Container;

	private $Category;

	private $View;

	/** Constructor */
	public function __construct()
	{
		$mid = $_SESSION['meetingID'];

		$this->Container = new Container($GLOBALS['cfg'], $mid);
		$this->Category = $this->Container->createCategory();
		$this->View = $this->Container->createView();
	}

	private function newItem()
	{
		$heading = "nová kategorie";
		$todo = "create";
		$this->template = "process";
			
		foreach($this->Category->DB_columns as $key) {
			$$key = requested($key, "");	
		}
	}

	private function delete($id)
	{
		if($this->Category->delete($id)){			
	  		redirect("?category&error=del");
		}
	}

	private function create()
	{
		foreach($this->Category->DB_columns as $key) {
			$DB_data[$key] = requested($key, "");	
		}
			
		if($this->Category->create($DB_data)){	
			redirect("index.php?error=ok");
		}
	}

	private function edit()
	{
		$heading = "úprava kategorie";
		$todo = "modify";
		$this->template = "process";
				
		$query = "SELECT * FROM kk_categories WHERE id = ".$id." LIMIT 1"; 
		$DB_data = mysql_fetch_assoc(mysql_query($query));
				
		foreach($this->Category->DB_columns as $key) {
			$$key = requested($key, $DB_data[$key]);	
		}
	}

	private function modify()
	{
		foreach($this->Category->DB_columns as $key) {
			$DB_data[$key] = requested($key, "");	
		}
				
		if($this->Category->modify($id, $DB_data)){
			redirect("index.php?error=ok");
		}
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
		$id = requested("id","");
		$cms = requested("cms","");
		$error = requested("error","");

		######################### DELETE CATEGORY #########################

		switch($cms) {
			case "del":
				$this->delete();
				break;
			case "new":
				$this->newItem();
				break;
			case "create":
				$this->create();
				break;
			case "edit":
				$this->edit();
				break;
			case "modify":
				$this->modify();
				break;
		}

		$this->render();
	}

	private function process()
	{
		$ViewHandler->assign('id',		$id);
		$ViewHandler->assign('todo',	$todo);
		$ViewHandler->assign('name',	$name);
		$ViewHandler->assign('bgcolor',	bgcolor);
		$ViewHandler->assign('bocolor',	bocolor);
		$ViewHandler->assign('focolor',	focolor);
		$ViewHandler->render(TRUE);
	}

	private function render()
	{
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
		$this->View->loadTemplate('categories/'.$this->template);
		$this->View->assign('error',	printError($error));
		$this->View->assign('render',	$this->Category->render());
		$this->View->render(TRUE);
		//$this->renderContent();

		/* Footer */
		$this->View->loadTemplate('footer');
		$this->View->render(TRUE);
	}
}