<?php

namespace App\Presenters;

use App\Services\SkautIS\AuthService as SkautisAuthService;
use App\Services\SkautIS\UserService as SkautisUserService;
use App\Services\UserService;
use Skautis\Wsdl\AuthenticationException;
use App\Services\Authenticator;


/**
 * SkautIS Auth presenters.
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
     * AuthPresenter constructor.
     * @param SkautisAuthService $skautisAuthService
     * @param SkautisUserService $skautisUserService
     * @param UserService        $userService
     */
	public function __construct(
		SkautisAuthService $skautisAuthService,
		SkautisUserService $skautisUserService,
		UserService $userService
	) {
		$this->setSkautisAuthService($skautisAuthService);
		$this->setSkautisUserService($skautisUserService);
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
		$this->redirectUrl($this->getSkautisAuthService()->getLoginUrl($backlink));
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
		$this->redirectUrl($this->getSkautisAuthService()->getLogoutUrl());
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
			$credentials = $this->getHttpRequest()->getPost();

			$this->guardToken($credentials['skautIS_Token']);
            $this->getSkautisAuthService()->setInit($credentials);
			$this->guardSkautisLoggedIn();

			$this->logInfo('Auth: ' . $credentials['skautIS_Token'] . ' / '. $credentials['skautIS_IDRole'] . ' / ' . $credentials['skautIS_IDUnit']);

			if($user = $this->getUserService()->findByProviderAndToken('skautis', $credentials['skautIS_Token'])) {
			} else {
				$this->getSkautisAuthService()->setInit($credentials);
				$userDetail = $this->getSkautisUserService()->getPersonalDetail();
				$user = $this->getUserService()->createAccount($credentials['skautIS_Token'], $userDetail);
			}

			$this->login($user);

			if (isset($ReturnUrl)) {
				$this->context->application->restoreRequest($ReturnUrl);
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
	 * @param string $token
	 * @throws \Nette\Application\AbortException
	 */
	protected function guardToken(string $token = '')
	{
		if (!$token) {
            dd($token);
			$this->redirect(":Login:");
		}
	}

	/**
	 * @throws AuthenticationException
	 */
	protected function guardSkautisLoggedIn()
	{
		if (!$this->getSkautisUserService()->isLoggedIn()) {
		    dd('exp');
			throw new AuthenticationException('Nemáte platné přihlášení do skautISu!');
		}
	}

	/**
	 * @throws \Nette\Security\AuthenticationException
	 */
	protected function login($user)
	{
		$this->getUser()->setExpiration('+ 29 minutes');
		$this->getUser()->setAuthenticator(new Authenticator());
		$this->getUser()->login($user);
	}

	/**
	 * @return SkautisAuthService
	 */
	protected function getSkautisAuthService(): SkautisAuthService
	{
		return $this->skautisAuthService;
	}

	/**
	 * @param  SkautisAuthService $service
	 * @return self
	 */
	protected function setskautisAuthService(SkautisAuthService $service): self
	{
		$this->skautisAuthService = $service;

		return $this;
	}

	/**
	 * @return SkautisUserService
	 */
	protected function getSkautisUserService(): SkautisUserService
	{
		return $this->skautisUserService;
	}

	/**
	 * @param  SkautisUserService $service
	 * @return self
	 */
	protected function setSkautisUserService(SkautisUserService $service): self
	{
		$this->skautisUserService = $service;

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
