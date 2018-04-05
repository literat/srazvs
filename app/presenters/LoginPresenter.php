<?php

namespace App\Presenters;

use App\Components\Forms\LoginForm;
use App\Components\Forms\Factories\ILoginFormFactory;
use App\Services\Skautis\UserService;

class LoginPresenter extends BasePresenter
{

	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @var ILoginFormFactory
	 */
	private $loginFormFactory;

	/**
	 */
	public function __construct(UserService $userService, ILoginFormFactory $loginFormFactory)
	{
		$this->setUserService($userService);
		$this->setLoginFormFactory($loginFormFactory);
	}

	public function startup(): void
	{
		parent::startup();

		if ($backlink = $this->getParameter('backlink')) {
			$this->getSession('auth')->backlink = $backlink;
		}

		$user = $this->getUser();
		if ($user->isLoggedIn() && $user->isInRole('administrator')) {
			$this->redirect('Dashboard:default');
		}
	}

	/**
	 * Render entire page
	 */
	public function renderDefault(): void
	{
		$template = $this->getTemplate();
		$template->user = $this->getUser();
		$template->page = $this->getPresenter()->getName();
	}

	protected function createComponentLoginForm(): LoginForm
	{
		return $this->loginFormFactory->create();
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

	protected function setLoginFormFactory(ILoginFormFactory $factory): ILoginFormFactory
	{
		$this->loginFormFactory = $factory;
	}

}
