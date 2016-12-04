<?php

namespace App\Presenters;

use Nette\Database\Context;
use Nette\DI\Container;
use App\Emailer;
use App\SettingsModel;

/**
 * This file handles the retrieval and serving of news articles
 */
class SettingsPresenter extends BasePresenter
{

	const PAGE = '/srazvs/settings';

	/**
	 * template
	 * @var string
	 */
	protected $template = 'form';

	/**
	 * template directory
	 * @var string
	 */
	protected $templateDir = 'settings';

	/**
	 * meeting ID
	 * @var integer
	 */
	protected $meetingId = 0;

	/**
	 * page where to return
	 * @var string
	 */
	protected $page = 'settings';

	/**
	 * Emailer model
	 * @var Emailer
	 */
	private $emailer;

	/**
	 * Prepare initial values
	 */
	public function __construct(Context $database, Container $container)
	{
		$this->database = $database;
		$this->setContainer($container);
		$this->setRouter($this->container->parameters['router']);
		$this->setModel($this->container->getService('settings'));
		$this->setEmailer($this->container->getService('emailer'));
		$this->setLatte($this->container->getService('latte'));

		if($this->meetingId = $this->requested('mid', '')){
			$_SESSION['meetingID'] = $this->meetingId;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}
	}

	/**
	 * @return Emailer
	 */
	protected function getEmailer()
	{
		return $this->emailer;
	}

	/**
	 * @param  Emailer $emailer
	 * @return $this
	 */
	protected function setEmailer(Emailer $emailer)
	{
		$this->emailer = $emailer;
		return $this;
	}

	/**
	 * This is the default function that will be called by router.php
	 *
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init()
	{
		########################## KONTROLA ###############################

		$this->cms = $this->requested('cms', '');
		$this->error = $this->requested('error', '');
		$this->page = $this->requested('page', $this->page);

		######################### DELETE CATEGORY #########################

		$action = $this->getRouter()->getParameter('action');

		switch($action) {
			case "update":
				$this->actionUpdate($this->requested('mail', ''));
				break;
			case "mail":
				$this->actionMail($this->requested('mail', ''), $this->requested('test-mail', ''));
			default:
				$this->actionEdit();
		}

		$this->render();
	}

	/**
	 * Prepare form page
	 * @param  int $id of item
	 * @return void
	 */
	private function actionEdit()
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
	private function actionUpdate($type)
	{
		try {
			$this->getModel()->modifyMailJSON($type, $this->requested('subject', ''), $this->requested('message', ''));
		} catch(Exception $e) {
			Debugger::log('Settings: mail type ' . $type . ' update failed!', Debugger::ERROR);
			redirect(self::PAGE . "?error=fail");
		}

		redirect(self::PAGE . "?error=ok");
	}

	/**
	 * Send test mail
	 *
	 * @param 	string 	$type 		type of mail
	 * @param 	string 	$test_mail 	e-mail address
	 * @return 	void
	 */
	private function actionMail($type, $test_mail)
	{
		if($this->getEmailer()->sendMail(array($test_mail => ''),
									$this->getModel()->getMailJSON($type)->subject,
									html_entity_decode($this->getModel()->getMailJSON($type)->message))
		) {
			redirect(self::PAGE . "?error=mail_send");
		}
	}

	/**
	 * Render entire page
	 * @return void
	 */
	private function render()
	{
		$settings = $this->getModel();
		$error = "";

		$parameters = [
			'cssDir'				=> CSS_DIR,
			'jsDir'					=> JS_DIR,
			'imgDir'				=> IMG_DIR,
			'user'					=> $this->getSunlightUser($_SESSION[SESSION_PREFIX.'user']),
			'meeting'				=> $this->getPlaceAndYear($_SESSION['meetingID']),
			'menu'					=> $this->generateMenu(),
			'error'					=> printError($this->error),
			'todo'					=> $this->todo,
			'cms'					=> $this->cms,
			'mid'					=> $this->meetingId,
			'page'					=> $this->page,
			'heading'				=> $this->heading,
			'payment_subject'		=> $settings->getMailJSON('cost')->subject,
			'payment_message'		=> $settings->getMailJSON('cost')->message,
			'payment_html_message'	=> html_entity_decode($settings->getMailJSON('cost')->message),
			'advance_subject'		=> $settings->getMailJSON('advance')->subject,
			'advance_message'		=> $settings->getMailJSON('advance')->message,
			'advance_html_message'	=> html_entity_decode($settings->getMailJSON('advance')->message),
			'tutor_subject'			=> $settings->getMailJSON('tutor')->subject,
			'tutor_message'			=> $settings->getMailJSON('tutor')->message,
			'tutor_html_message'	=> html_entity_decode($settings->getMailJSON('tutor')->message),
			'reg_subject'			=> $settings->getMailJSON('post_reg')->subject,
			'reg_message'			=> $settings->getMailJSON('post_reg')->message,
			'reg_html_message'		=> html_entity_decode($settings->getMailJSON('post_reg')->message),
		];

		$this->latte->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);
	}
}
