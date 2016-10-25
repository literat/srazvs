<?php

namespace App\Presenters;

use Nette\Database\Context;
use Nette\DI\Container;
use Tracy\Debugger;
use Skautis;
use SkautisAuth;
use AuthService;
use UserService;

/**
 * SkautIS Auth presenters.
 */
class AuthPresenter extends BasePresenter
{

	private $container;
	private $service;
	private $user;

	public function __construct(Context $database, Container $container)
	{
		$this->database = $database;
		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->service = $this->container->getService('authService');
		$this->user = $this->container->getService('security.user');
	}

	/**
	 * This is the default function that will be called by router.php
	 *
	 * @param 	array 	$getVars 	the GET variables posted to index.php
	 */
	public function init()
	{
		if($this->meetingId = $this->requested('mid', '')){
			$_SESSION['meetingID'] = $this->meetingId;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}

		switch($this->router->getParameter('action')){
			case 'skautis':
				$method = $this->router->getParameter('id');
				$this->actionSkautis($method);
				break;
			case 'login':
				$to = $this->router->getParameter('id');
				$this->actionLogin($to);
				break;
			case 'logout':
				$from = $this->router->getParameter('id');
				$this->actionLogout($from);
				break;
		}
	}

	protected function startup()
	{
		parent::startup();
	}

	public function actionLogin($to)
	{
		$this->{$to . 'Login'}();
	}

	public function actionLogout($from)
	{
		$this->{$from . 'Logout'}();
	}

	public function actionSkautis($method)
	{
		$this->{'handleSkautis' . $method}();
	}

	/**
	 * přesměruje na stránku s přihlášením
	 * Redirects to login page
	 *
	 * @param  	string  $backlink
	 * @return  void
	 */
	protected function skautisLogin($backlink = NULL)
	{
		//$this->redirectUrl($this->service->getLoginUrl($backlink));
		redirect($this->service->getLoginUrl($backlink));
	}

	/**
	 * Handle log out from SkautIS
	 * SkautIS redirects to this action after log out
	 *
	 * @param   void
	 * @return  void
	 */
	protected function skautisLogout()
	{
		//$this->redirectUrl($this->context->authService->getLogoutUrl());
		redirect($this->context->authService->getLogoutUrl());
	}

	/**
	 * Handle SkautIS login process
	 *
	 * @param   string  $ReturnUrl
	 * @return  void
	 */
	protected function handleSkautisLogin($ReturnUrl = NULL)
	{
		//$post = $this->request->post;
		$post = $this->router->getPost();
		// if token is not set - get out from here - must log in
		if (!isset($post['skautIS_Token'])) {
			$this->redirect(":Login:");
		}
		//Debugger::log("AuthP: ".$post['skautIS_Token']." / ". $post['skautIS_IDRole'] . " / " . $post['skautIS_IDUnit'], "auth");
		try {
			//$this->context->authService->setInit($post);
			$this->container->getService('authService')->setInit($post);

			//if (!$this->context->userService->isLoggedIn()) {
			if (!$this->container->getService('userService')->isLoggedIn()) {
				throw new \Skautis\Wsdl\AuthenticationException("Nemáte platné přihlášení do skautISu");
			}
			//$me = $this->context->userService->getPersonalDetail();
			$me = $this->container->getService('userService')->getPersonalDetail();

			$this->user->setExpiration('+ 29 minutes'); // nastavíme expiraci
			$this->user->setAuthenticator(new SkautisAuth\SkautisAuthenticator());
			$this->user->login($me);

			if (isset($ReturnUrl)) {
				$this->context->application->restoreRequest($ReturnUrl);
			}
		} catch (\Skautis\Wsdl\AuthenticationException $e) {
			$this->flashMessage($e->getMessage(), "danger");
			$this->redirect(":Auth:Login");
		}
		//$this->presenter->redirect(':Dashboard:');
		redirect('/srazvs/registration');
	}

	/**
	 * Log out from SkautIS
	 *
	 * @param   void
	 * @return  void
	 */
	protected function handleSkautisLogout()
	{
		$this->user->logout(TRUE);
		$this->context->userService->resetLoginData();
		$this->presenter->flashMessage("Byl jsi úspěšně odhlášen ze SkautISu.");
		/*
		if ($this->request->post['skautIS_Logout']) {
			$this->presenter->flashMessage("Byl jsi úspěšně odhlášen ze SkautISu.");
		} else {
			$this->presenter->flashMessage("Odhlášení ze skautISu se nezdařilo", "danger");
		}
		*/
		$this->redirect(":Login:");
		//$this->redirectUrl($this->service->getLogoutUrl());
	}

}
