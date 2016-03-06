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
	private $Container;

	/**
	 * Category model
	 * @var Category
	 */
	private $Settings;

	/**
	 * View model
	 * @var View
	 */
	private $View;

	/**
	 * Emailer model
	 * @var Emailer
	 */
	private $Emailer;

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
		global $database;
		$this->Container = new Container($GLOBALS['cfg'], $this->meetingId, $database);
		$this->Settings = $this->Container->createSettings();
		$this->View = $this->Container->createView();
		$this->Emailer = $this->Container->createEmailer();
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

		$this->cms = requested("cms","");
		$this->error = requested("error","");
		$this->page = requested("page",$this->page);

		######################### DELETE CATEGORY #########################

		switch($this->cms) {
			case "update":
				$this->update(requested('mail', ''));
				break;
			case "mail":
				$this->mail(requested('mail', ''), requested('test-mail', ''));
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
		$error = $this->Settings->modifyMailJSON($type, requested('subject', ''), requested('message', ''));

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
		if($this->Emailer->sendMail($test_mail,
									$test_mail,
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
		$this->View->loadTemplate('http_header');
		$this->View->assign('config',		$GLOBALS['cfg']);
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
		$this->View->assign('cms',		$this->cms);
		$this->View->assign('mid',		$this->meetingId);
		$this->View->assign('page',		$this->page);

		$this->View->assign('payment_subject',		$this->Settings->getMailJSON('cost')->subject);
		$this->View->assign('payment_message',		$this->Settings->getMailJSON('cost')->message);
		$this->View->assign('payment_html_message',	html_entity_decode($this->Settings->getMailJSON('cost')->message));

		$this->View->assign('advance_subject',		$this->Settings->getMailJSON('advance')->subject);
		$this->View->assign('advance_message',		$this->Settings->getMailJSON('advance')->message);
		$this->View->assign('advance_html_message',	html_entity_decode($this->Settings->getMailJSON('advance')->message));

		$this->View->assign('tutor_subject',		$this->Settings->getMailJSON('tutor')->subject);
		$this->View->assign('tutor_message',		$this->Settings->getMailJSON('tutor')->message);
		$this->View->assign('tutor_html_message',	html_entity_decode($this->Settings->getMailJSON('tutor')->message));

		$this->View->assign('reg_subject',			$this->Settings->getMailJSON('post_reg')->subject);
		$this->View->assign('reg_message',			$this->Settings->getMailJSON('post_reg')->message);
		$this->View->assign('reg_html_message',		html_entity_decode($this->Settings->getMailJSON('post_reg')->message));

		$this->View->render(TRUE);

		/* Footer */
		$this->View->loadTemplate('footer');
		$this->View->render(TRUE);
	}
}
