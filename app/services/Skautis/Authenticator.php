<?php

namespace App\Services\Skautis;

use App\Services\Skautis\AuthService as SkautisAuthService;
use App\Services\Skautis\UserService as SkautisUserService;
use App\Services\UserService;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\SmartObject;

class Authenticator implements IAuthenticator
{
	use SmartObject;

	/**
	 * @var AuthService
	 */
	private $skautisAuthService;

	/**
	 * @var \App\Services\Skautis\UserService
	 */
	private $skautisUserService;

	/**
	 * @var UserService
	 */
	private $userService;

	public function __construct(
		SkautisAuthService $skautisAuthService,
		SkautisUserService $skautisUserService,
		UserService $userService
	) {
		$this->skautisAuthService = $skautisAuthService;
		$this->skautisUserService = $skautisUserService;
		$this->userService = $userService;
	}

	public function authenticate(array $credentials): Identity
	{
		$credentials = $credentials[0];

		$this->skautisAuthService->setInit($credentials);
		$this->guardSkautisLoggedIn();
		$userDetail = $this->skautisUserService->getPersonalDetail();
		$token = $userDetail->ID;

		$user = $this->userService->findByProviderAndToken('skautis', $token);
		if (!$user) {
			$userDetail = $this->skautisUserService->getPersonalDetail();
			$user = $this->userService->createAccount($token, $userDetail);
		}

		return new Identity($user->id, $user->role, ['username' => $user->nick]);
	}

	/**
	 * @throws AuthenticationException
	 */
	protected function guardSkautisLoggedIn()
	{
		if (!$this->skautisUserService->isLoggedIn()) {
			throw new AuthenticationException('Nemáte platné přihlášení do skautISu!');
		}
	}
}
