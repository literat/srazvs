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

	/** Constructor */
	public function __construct()
	{

	}

	/**
	 * This is the default function that will be called by router.php
	 * 
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function main(array $getVars)
	{
		include_once(INC_DIR.'access.inc.php');

		########################## KONTROLA ###############################

		$mid = $_SESSION['meetingID'];
		$id = requested("id","");
		$cms = requested("cms","");
		$error = requested("error","");

		$Container = new Container($GLOBALS['cfg'], $mid);
		$CategoryHandler = $Container->createCategory();
		$ViewHandler = $Container->createView();

		######################### DELETE CATEGORY #########################

		switch($cms) {
			case "del":
				if($CategoryHandler->delete($id)){	
			  		redirect("?category&error=del");
				}
				break;
			case "new":
				$heading = "nová kategorie";
				$todo = "create";
				$this->template = "process";
				
				foreach($CategoryHandler->DB_columns as $key) {
					$$key = requested($key, "");	
				}
				break;
			case "create":
				foreach($CategoryHandler->DB_columns as $key) {
					$DB_data[$key] = requested($key, "");	
				}
				
				if($CategoryHandler->create($DB_data)){	
					redirect("index.php?error=ok");
				}
				break;
			case "edit":
				$heading = "úprava kategorie";
				$todo = "modify";
				$this->template = "process";
				
				$query = "SELECT * FROM kk_categories WHERE id = ".$id." LIMIT 1"; 
				$DB_data = mysql_fetch_assoc(mysql_query($query));
				
				foreach($CategoryHandler->DB_columns as $key) {
					$$key = requested($key, $DB_data[$key]);	
				}
				break;
			case "modify":
				foreach($CategoryHandler->DB_columns as $key) {
					$DB_data[$key] = requested($key, "");	
				}
				
				if($CategoryHandler->modify($id, $DB_data)){
					redirect("index.php?error=ok");
				}
				break;
		}

		/* HTTP Header */
		$ViewHandler->loadTemplate('http_header');
		$ViewHandler->assign('config',		$GLOBALS['cfg']);
		$ViewHandler->assign('style',		$CategoryHandler->getStyles());
		$ViewHandler->render(TRUE);

		/* Application Header */
		$ViewHandler->loadTemplate('header');
		$ViewHandler->assign('config',		$GLOBALS['cfg']);
		$ViewHandler->render(TRUE);

		// load and prepare template
		$ViewHandler->loadTemplate('categories/'.$this->template);
		$ViewHandler->assign('error',	printError($error));
		$ViewHandler->assign('render',	$CategoryHandler->render());
		$ViewHandler->render(TRUE);
		$this->renderContent();

		/* Footer */
		$ViewHandler->loadTemplate('footer');
		$ViewHandler->render(TRUE);
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

	private function renderContent($cms)
	{

	}
}