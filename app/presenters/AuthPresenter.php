<?php

namespace App\Presenters;

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

	/**
	 * @var AuthService
	 */
	protected $authSservice;

	/**
	 * @var UserService
	 */
	protected $userService;

	/**
	 * @param AuthService $authService
	 * @param UserService $userService
	 */
	public function __construct(AuthService $authService, UserService $userService)
	{
		$this->setAuthService($authService);
		$this->setUserService($userService);
		//$this->service = $this->container->getService('authService');
		//$this->user = $this->container->getService('security.user');
	}

	/**
	 * @return void
	 */
	protected function startup()
	{
		parent::startup();
	}

	/**
	 * @param  string $id
	 * @return void
	 */
	public function actionLogin($id)
	{
		$this->{$id . 'Login'}();
	}

	/**
	 * @param  string $id
	 * @return void
	 */
	public function actionLogout($id)
	{
		$this->{$id . 'Logout'}();
	}

	/**
	 * @param  string $id
	 * @return void
	 */
	public function actionSkautis($id)
	{
		$this->{'handleSkautis' . $id}();
	}

	/**
	 * Redirects to login page
	 *
	 * @param  	string  $backlink
	 * @return  void
	 */
	protected function skautisLogin($backlink = null)
	{
		$this->redirectUrl($this->authService->getLoginUrl($backlink));
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
		$this->redirectUrl($this->context->authService->getLogoutUrl());
	}

	/**
	 * Handle SkautIS login process
	 *
	 * @param   string  $ReturnUrl
	 * @return  void
	 */
	protected function handleSkautisLogin($ReturnUrl = NULL)
	{
		$post = $this->request->post;
		//$post = $this->router->getPost();
		// if token is not set - get out from here - must log in
		if (!isset($post['skautIS_Token'])) {
			$this->redirect(":Login:");
		}
		//Debugger::log("AuthP: ".$post['skautIS_Token']." / ". $post['skautIS_IDRole'] . " / " . $post['skautIS_IDUnit'], "auth");
		try {
			$this->context->authService->setInit($post);
			//$this->container->getService('authService')->setInit($post);

			if (!$this->context->userService->isLoggedIn()) {
			//if (!$this->container->getService('userService')->isLoggedIn()) {
				throw new \Skautis\Wsdl\AuthenticationException("Nemáte platné přihlášení do skautISu");
			}
			$me = $this->context->userService->getPersonalDetail();
			//$me = $this->container->getService('userService')->getPersonalDetail();

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
		$this->presenter->redirect(':Registration:');
	}

	/**
	 * Log out from SkautIS
	 *
	 * @param   void
	 * @return  void
	 */
	protected function handleSkautisLogout()
	{
		$this->userService->logout(TRUE);
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
