<?php

namespace App\Presenters;

use App\Components\Forms\LoginForm;
use App\Components\Forms\Factories\ILoginFormFactory;

class LoginPresenter extends BasePresenter
{

	/**
	 * @var ILoginFormFactory
	 */
	private $loginFormFactory;

	/**
	 */
	public function __construct()
    {
	}

    /**
     * @param  ILoginFormFactory $factory
     */
    public function injectLoginFormFactory(ILoginFormFactory $factory)
    {
        $this->loginFormFactory = $factory;
    }

	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();
	}

	/**
	 * Render entire page
	 * @return void
	 */
	public function renderDefault()
	{
		$template = $this->getTemplate();
	}

	/**
	 * @return LoginForm
	 */
	protected function createComponentLoginForm(): LoginForm
	{
		return $this->loginFormFactory->create();
	}



}
