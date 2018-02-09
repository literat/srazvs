<?php

namespace App\Presenters;

use App\Services\SkautIS\AuthService;
use App\Services\SkautIS\UserService;
use Skautis\Wsdl\AuthenticationException;
use SkautisAuth\SkautisAuthenticator;

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
	 */
	public function __construct(
		AuthService $authService,
		UserService $userService
	) {
		$this->setAuthService($authService);
		$this->setUserService($userService);
	}

	/**
	 * @return void
	 */
	protected function startup()
	{
		parent::startup();
	}

	/**
	 * @param  string $provider
	 * @return void
	 */
	public function actionLogin($provider)
	{
		$this->{$provider . 'Login'}();
	}

	/**
	 * @param  string $provider
	 * @return void
	 */
	public function actionLogout($provider)
	{
		$this->getSession('auth')->backlink = $this->getParameter('backlink') ?? null;
		$this->{$provider . 'Logout'}();
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
		try {
			$post = $this->getHttpRequest()->post;
			// if token is not set - get out from here - must log in
			if (!isset($post['skautIS_Token'])) {
				$this->redirect(":Login:");
			}

			$this->logInfo('Auth: ' . $post['skautIS_Token'] . ' / '. $post['skautIS_IDRole'] . ' / ' . $post['skautIS_IDUnit']);

			$this->getAuthService()->setInit($post);

			if (!$this->getUserService()->isLoggedIn()) {
				throw new AuthenticationException('Nemáte platné přihlášení do skautISu!');
			}

			$user = $this->getUserService()->getPersonalDetail();
			$this->getUser()->setExpiration('+ 29 minutes');
			$this->getUser()->setAuthenticator(new SkautisAuthenticator());
			$this->getUser()->login($user);

			if (isset($ReturnUrl)) {
				$this->context->application->restoreRequest($ReturnUrl);
			}

			if($backlink = $this->getSession('auth')->backlink) {
				unset($this->getSession('auth')->backlink);
				$this->redirectUrl($backlink);
			} else {
				$this->redirect(':Login:');
			}
		} catch (AuthenticationException $e) {
			$this->logNotice($e->getMessage());
			$this->flashFailure($e->getMessage());
			$this->redirect(":Auth:Login");
		}
	}

	/**
	 * Log out from SkautIS
	 *
	 * @param   void
	 * @return  void
	 */
	protected function handleSkautisLogout()
	{
		$this->getUser()->logout(TRUE);
		//$this->getUserService()->resetLoginData();
		$this->flashMessage("Byl jsi úspěšně odhlášen ze SkautISu.");
		/*
		if ($this->request->post['skautIS_Logout']) {
			$this->presenter->flashMessage("Byl jsi úspěšně odhlášen ze SkautISu.");
		} else {
			$this->presenter->flashMessage("Odhlášení ze skautISu se nezdařilo", "danger");
		}
		*/

		if($backlink = $this->getSession('auth')->backlink) {
			unset($this->getSession('auth')->backlink);
			$this->redirectUrl($backlink);
		} else {
			$this->redirect(':Login:');
		}
	}

	/**
	 * @return AuthService
	 */
	protected function getAuthService(): AuthService
	{
		return $this->authService;
	}

	/**
	 * @param  AuthService $service
	 * @return self
	 */
	protected function setAuthService(AuthService $service): self
	{
		$this->authService = $service;

		return $this;
	}

	/**
	 * @return UserService
	 */
	protected function getUserService(): UserService
	{
		return $this->userService;
	}

	/**
	 * @param  UserService $service
	 * @return self
	 */
	protected function setUserService(UserService $service): self
	{
		$this->userService = $service;

		return $this;
	}

}
