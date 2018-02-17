<?php

namespace App\Components;

use Nette\Security\User;

class NavbarRightControl extends BaseControl
{

	const TEMPLATE_NAME = 'NavbarRight';

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @param User $user
	 */
	public function __construct(User $user)
	{
		$this->setUser($user);
	}

	/**
	 * @return void
	 */
	public function render($backlink)
	{
		$template = $this->getTemplate();
		$template->setFile(__DIR__ . './templates/'.self::TEMPLATE_NAME.'.'.self::TEMPLATE_EXT);
		$template->user = $this->getUser();
		$template->page = $this->getPresenter()->getName();
		$template->backlink = $backlink;
		$template->render();
	}

	/**
	 * @return User
	 */
	protected function getUser(): User
	{
		return $this->user;
	}

	/**
	 * @param  User $user
	 * @return $this
	 */
	protected function setUser(User $user): self
	{
		$this->user = $user;

		return $this;
	}

}
