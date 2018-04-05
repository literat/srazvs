<?php

namespace App\Presenters;

use App\Services\Skautis\AuthService as SkautisAuthService;
use App\Services\Skautis\UserService as SkautisUserService;
use App\Services\Skautis\Authenticator as SkautisAuthenticator;
use App\Services\UserService;
use Skautis\Wsdl\AuthenticationException;

/**
 * Skautis Auth presenters.
 */
class AuthPresenter extends BasePresenter
{

	/**
	 * @var SkautisAuthService
	 */
	protected $skautisAuthService;

	/**
	 * @var SkautisUserService
	 */
	protected $skautisUserService;

	/**
	 * @var UserService
	 */
	protected $userService;

	/**
	 * @var SkautisAuthenticator
	 */
	private $skautisAuthenticator;

	/**
	 * AuthPresenter constructor.
	 * @param SkautisAuthService $skautisAuthService
	 * @param SkautisUserService $skautisUserService
	 * @param UserService        $userService
	 */
	public function __construct(
		SkautisAuthService $skautisAuthService,
		SkautisUserService $skautisUserService,
		UserService $userService,
		SkautisAuthenticator $skautisAuthenticator
	) {
		$this->setSkautisAuthService($skautisAuthService);
		$this->setSkautisUserService($skautisUserService);
		$this->setUserService($userService);
		$this->skautisAuthenticator = $skautisAuthenticator;
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
	 * @param   string $backlink
	 * @return  void
	 * @throws \Nette\Application\AbortException
	 */
	protected function skautisLogin($backlink = null)
	{
		$this->redirectUrl($this->getSkautisAuthService()->getLoginUrl($backlink));
	}

	/**
	 * Handle log out from Skautis
	 * Skautis redirects to this action after log out
	 *
	 * @return  void
	 * @throws \Nette\Application\AbortException
	 */
	protected function skautisLogout()
	{
		$this->redirectUrl($this->getSkautisAuthService()->getLogoutUrl());
	}

	/**
	 * Handle Skautis login process
	 *
	 * @param   string $returnUrl
	 * @return  void
	 * @throws \Nette\Application\AbortException
	 * @throws \Nette\Security\AuthenticationException
	 */
	protected function handleSkautisLogin($returnUrl = null)
	{
		try {
			$credentials = $this->getHttpRequest()->getPost();
			$this->logInfo('Auth: %s / %s / %s', [
				$credentials['skautIS_Token'],
				$credentials['skautIS_IDRole'],
				$credentials['skautIS_IDUnit']
			]);

			$this->guardToken($credentials['skautIS_Token']);

			$this->getUser()->setExpiration('+ 29 minutes');
			$this->getUser()->setAuthenticator($this->skautisAuthenticator);
			$this->getUser()->login($credentials);

			if (isset($returnUrl)) {
				$this->context->application->restoreRequest($returnUrl);
			}

			if($backlink = $this->getSession('auth')->backlink) {
				unset($this->getSession('auth')->backlink);
				$this->redirectUrl($backlink);
			} else {
				$this->redirect(':Dashboard:');
			}
		} catch (AuthenticationException $e) {
			$this->logWarning($e->getMessage());
			$this->flashFailure($e->getMessage());
			$this->redirect(":Auth:Login");
		}
	}

	/**
	 * Log out from Skautis
	 *
	 * @param   void
	 * @return  void
	 */
	protected function handleSkautisLogout()
	{
		$this->getUser()->logout(true);
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
	 * @param string $token
	 * @throws \Nette\Application\AbortException
	 */
	protected function guardToken(string $token = '')
	{
		if (!$token) {
			$this->redirect(":Login:");
		}
	}

	/**
	 * @return SkautisAuthService
	 */
	protected function getSkautisAuthService(): SkautisAuthService
	{
		return $this->skautisAuthService;
	}

	protected function setSkautisAuthService(SkautisAuthService $service): self
	{
		$this->skautisAuthService = $service;

		return $this;
	}

	protected function getSkautisUserService(): SkautisUserService
	{
		return $this->skautisUserService;
	}

	protected function setSkautisUserService(SkautisUserService $service): self
	{
		$this->skautisUserService = $service;

		return $this;
	}

	protected function getUserService(): UserService
	{
		return $this->userService;
	}

	protected function setUserService(UserService $service): self
	{
		$this->userService = $service;

		return $this;
	}



}
