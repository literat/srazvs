<?php
/**
 * This file handles the retrieval and serving of news articles
 */
class MeetingController extends BaseController
{
	/**
	 * This template variable will hold the 'view' portion of our MVC for this
	 * controller
	 */
	public $template = 'view';

	/**
	 * template directory
	 * @var string
	 */
	private $templateDir = 'meeting';

	/**
	 * page where to return
	 * @var string
	 */
	private $page = 'meeting';

	/**
	 * action what to do
	 * @var string
	 */
	private $cms = '';

	/**
	 * error handler
	 * @var string
	 */
	private $error = '';

	private $render = NULL;

	/**
	 * meeting ID
	 * @var integer
	 */
	private $meetingId = 0;

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

	private $container;

	/**
	 * Prepare initial values
	 */
	public function __construct($database, $container)
	{
		$this->database = $database;
		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->Meeting = $this->container->createServiceMeeting();
		$this->View = $this->container->createServiceView();
		$this->Category = $this->container->createServiceCategory();

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
		######################### PRISTUPOVA PRAVA ################################

		include_once(INC_DIR.'access.inc.php');

		###########################################################################

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

		// styles in header
		$style = $this->Category->getStyles();

		/* HTTP Header */
		$this->View->loadTemplate('http_header');
		$this->View->assign('style',		$style);
		$this->View->render(TRUE);

		/* Application Header */
		$this->View->loadTemplate('header');
		$this->View->assign('user',		$this->getUser($_SESSION[SESSION_PREFIX.'user']));
		$this->View->assign('meeting',	$this->getPlaceAndYear($_SESSION['meetingID']));
		$this->View->assign('menu',		$this->generateMenu());
		$this->View->render(TRUE);

		// load and prepare template
		$this->View->loadTemplate($this->templateDir.'/'.$this->template);
		$this->View->assign('error',			printError($this->error));
		$this->View->assign('cms',				$this->cms);
		$this->View->assign('render',			$this->render);
		$this->View->assign('mid',				$this->meetingId);
		$this->View->assign('error_start',		printError($error_start));
		$this->View->assign('error_end',		printError($error_end));
		$this->View->assign('error_open_reg',	printError($error_open_reg));
		$this->View->assign('error_close_reg',	printError($error_close_reg));
		$this->View->assign('error_login',		printError($error_login));
		$this->View->assign('cms',				$this->cms);
		$this->View->assign('mid',				$this->meetingId);
		$this->View->assign('page',				$this->page);

		if(!empty($this->data)) {
			$this->View->assign('id',			$this->meetingId);
			$this->View->assign('place',		$this->data['place']);
			$this->View->assign('start_date',	$this->data['start_date']);
			$this->View->assign('end_date',		$this->data['end_date']);
			$this->View->assign('open_reg',		$this->data['open_reg']);
			$this->View->assign('close_reg',	$this->data['close_reg']);
			$this->View->assign('cost',			$this->data['cost']);
			$this->View->assign('advance',		$this->data['advance']);
			$this->View->assign('numbering',	$this->data['numbering']);
			$this->View->assign('contact',		$this->data['contact']);
			$this->View->assign('email',		$this->data['email']);
			$this->View->assign('gsm',			$this->data['gsm']);

			$this->View->assign('todo',				$this->todo);
		}

		$this->View->render(TRUE);

		/* Footer */
		$this->View->loadTemplate('footer');
		$this->View->render(TRUE);
	}
}
