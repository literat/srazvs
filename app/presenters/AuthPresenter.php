<?php

namespace App\Presenters;

use Skautis;
use SkautisAuth;
use App\Services\AuthService;
use App\Services\UserService;
use Nette\Http\Request;
use Tracy\Debugger;

/**
 * SkautIS Auth presenters.
 */
class AuthPresenter extends BasePresenter
{

	/**
	 * @var AuthService
	 */
	protected $authService;

	/**
	 * @var UserService
	 */
	protected $userService;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @param AuthService $authService
	 * @param UserService $userService
	 * @param Request     $request
	 * @param User        $user
	 */
	public function __construct(
		AuthService $authService,
		UserService $userService,
		Request $request
	) {
		$this->setAuthService($authService);
		$this->setUserService($userService);
		$this->setRequest($request);
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
		$this->redirectUrl($this->getAuthService()->getLoginUrl($backlink));
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
		$this->redirectUrl($this->getAuthService()->getLogoutUrl());
	}

	/**
	 * Handle SkautIS login process
	 *
	 * @param   string  $ReturnUrl
	 * @return  void
	 */
	protected function handleSkautisLogin($ReturnUrl = NULL)
	{
		$post = $this->getRequest()->post;
		//$post = $this->router->getPost();
		// if token is not set - get out from here - must log in
		if (!isset($post['skautIS_Token'])) {
			$this->redirect(":Login:");
		}
		//Debugger::log("AuthP: ".$post['skautIS_Token']." / ". $post['skautIS_IDRole'] . " / " . $post['skautIS_IDUnit'], "auth");
		try {
			$this->getAuthService()->setInit($post);
			//$this->container->getService('authService')->setInit($post);

			if (!$this->getUserService()->isLoggedIn()) {
			//if (!$this->container->getService('userService')->isLoggedIn()) {
				throw new \Skautis\Wsdl\AuthenticationException("Nemáte platné přihlášení do skautISu");
			}
			$me = $this->getUserService()->getPersonalDetail();
			//$me = $this->container->getService('userService')->getPersonalDetail();

			$this->getUser()->setExpiration('+ 29 minutes'); // nastavíme expiraci
			$this->getUser()->setAuthenticator(new SkautisAuth\SkautisAuthenticator());
			$this->getUser()->login($me);

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
		$this->getUserService()->logout(TRUE);
		$this->getUserService()->resetLoginData();
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

	/**
	 * @return AuthService
	 */
	protected function getAuthService()
	{
		return $this->authService;
	}

	/**
	 * @param  AuthService $service
	 * @return $this
	 */
	protected function setAuthService(AuthService $service)
	{
		$this->authService = $service;

		return $this;
	}

	/**
	 * @return UserService
	 */
	protected function getUserService()
	{
		return $this->userService;
	}

	/**
	 * @param  UserService $service
	 * @return $this
	 */
	protected function setUserService(UserService $service)
	{
		$this->userService = $service;

		return $this;
	}

}
