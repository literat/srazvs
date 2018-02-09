<?php

namespace App\Presenters;

use App\Components\Forms\LoginForm;
use App\Components\Forms\Factories\ILoginFormFactory;
use App\Services\SkautIS\UserService;

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

	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();
		if ($backlink = $this->getParameter('backlink')) {
			$this->getSession('auth')->backlink = $backlink;
		}
	}

	/**
	 * Render entire page
	 * @return void
	 */
	public function renderDefault()
	{
		$template = $this->getTemplate();
		$template->user = $this->getUser();
		$template->page = $this->getPresenter()->getName();
	}

	/**
	 * @return LoginForm
	 */
	protected function createComponentLoginForm(): LoginForm
	{
		return $this->loginFormFactory->create();
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

	/**
	 * @param  ILoginFormFactory $factory
	 */
	protected function setLoginFormFactory(ILoginFormFactory $factory)
	{
		$this->loginFormFactory = $factory;
	}
}
