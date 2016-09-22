<?php
/**
 * This file handles the retrieval and serving of news articles
 */
class SettingsController extends BaseController
{
	/**
	 * template
	 * @var string
	 */
	private $template = 'form';

	/**
	 * template directory
	 * @var string
	 */
	private $templateDir = 'settings';

	/**
	 * meeting ID
	 * @var integer
	 */
	private $meetingId = 0;

	/**
	 * action what to do
	 * @var string
	 */
	private $cms = '';

	/**
	 * page where to return
	 * @var string
	 */
	private $page = 'settings';

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
	private $container;

	/**
	 * Category model
	 * @var Category
	 */
	private $Settings;

	/**
	 * View model
	 * @var View
	 */
	private $view;

	/**
	 * Emailer model
	 * @var Emailer
	 */
	private $Emailer;

	/**
	 * Prepare initial values
	 */
	public function __construct($database, $container)
	{
		$this->database = $database;
		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->Settings = $this->container->createServiceSettings();
		$this->view = $this->container->createServiceView();
		$this->Emailer = $this->container->createServiceEmailer();

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

		$this->cms = $this->requested('cms', '');
		$this->error = $this->requested('error', '');
		$this->page = $this->requested('page', $this->page);

		######################### DELETE CATEGORY #########################

		switch($this->cms) {
			case "update":
				$this->update($this->requested('mail', ''));
				break;
			case "mail":
				$this->mail($this->requested('mail', ''), $this->requested('test-mail', ''));
			default:
				$this->edit();
		}

		$this->render();
	}

	/**
	 * Prepare form page
	 * @param  int $id of item
	 * @return void
	 */
	private function edit()
	{
		$this->heading = "úprava kategorie";
		$this->template = "form";
	}

	/**
	 * Update item in DB
	 *
	 * @param 	string 	$type 	type of mail
	 * @return 	void
	 */
	private function update($type)
	{
		$error = $this->Settings->modifyMailJSON($type, $this->requested('subject', ''), $this->requested('message', ''));

		if($error == 'ok') {
			redirect("?page=".$this->page."&error=ok");
		}
	}

	/**
	 * Send test mail
	 *
	 * @param 	string 	$type 		type of mail
	 * @param 	string 	$test_mail 	e-mail address
	 * @return 	void
	 */
	private function mail($type, $test_mail)
	{
		if($this->Emailer->sendMail(array($test_mail => ''),
									$this->Settings->getMailJSON($type)->subject,
									html_entity_decode($this->Settings->getMailJSON($type)->message))
		) {
			redirect("?error=mail_send");
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
		$this->view->loadTemplate('http_header');
		$this->view->render(TRUE);

		/* Application Header */
		$this->view->loadTemplate('header');
		$this->view->assign('user',		$this->getUser($_SESSION[SESSION_PREFIX.'user']));
		$this->view->assign('meeting',	$this->getPlaceAndYear($_SESSION['meetingID']));
		$this->view->assign('menu',		$this->generateMenu());
		$this->view->render(TRUE);

		// load and prepare template
		$this->view->loadTemplate($this->templateDir.'/'.$this->template);
		$this->view->assign('heading',	$this->heading);
		$this->view->assign('todo',		$this->todo);
		$this->view->assign('error',	printError($this->error));
		$this->view->assign('cms',		$this->cms);
		$this->view->assign('mid',		$this->meetingId);
		$this->view->assign('page',		$this->page);

		$this->view->assign('payment_subject',		$this->Settings->getMailJSON('cost')->subject);
		$this->view->assign('payment_message',		$this->Settings->getMailJSON('cost')->message);
		$this->view->assign('payment_html_message',	html_entity_decode($this->Settings->getMailJSON('cost')->message));

		$this->view->assign('advance_subject',		$this->Settings->getMailJSON('advance')->subject);
		$this->view->assign('advance_message',		$this->Settings->getMailJSON('advance')->message);
		$this->view->assign('advance_html_message',	html_entity_decode($this->Settings->getMailJSON('advance')->message));

		$this->view->assign('tutor_subject',		$this->Settings->getMailJSON('tutor')->subject);
		$this->view->assign('tutor_message',		$this->Settings->getMailJSON('tutor')->message);
		$this->view->assign('tutor_html_message',	html_entity_decode($this->Settings->getMailJSON('tutor')->message));

		$this->view->assign('reg_subject',			$this->Settings->getMailJSON('post_reg')->subject);
		$this->view->assign('reg_message',			$this->Settings->getMailJSON('post_reg')->message);
		$this->view->assign('reg_html_message',		html_entity_decode($this->Settings->getMailJSON('post_reg')->message));

		$this->view->render(TRUE);

		/* Footer */
		$this->view->loadTemplate('footer');
		$this->view->render(TRUE);
	}
}
