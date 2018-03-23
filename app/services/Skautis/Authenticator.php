<?php

namespace App\Services\Skautis;

use Nette\SmartObject;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use App\Repositories\SocialLoginRepository;
use App\Repositories\PersonRepository;
use App\Repositories\UserRepository;
use App\Services\UserService;
use App\Services\SkautIS\UserService as SkautisUserService;
use App\Services\SkautIS\AuthService as SkautisAuthService;

class Authenticator implements IAuthenticator
{
	use SmartObject;

	/**
	 * @var SocialLoginRepository
	 */
	private $socialLoginRepository;

	/**
	 * @var PersonRepository
	 */
	private $personRepository;

	/**
	 * @var UserRepository
	 */
	private $userRepository;

    /**
     * @var AuthService
     */
	private $skautisAuthService;

    /**
     * @var \App\Services\SkautIS\UserService
     */
	private $skautisUserService;

    /**
     * @var UserService
     */
	private $userService;

	public function __construct(
		SocialLoginRepository $socialLoginRepository,
		PersonRepository $personRepository,
		UserRepository $userRepository,
		SkautisAuthService $skautisAuthService,
		SkautisUserService $skautisUserService,
		UserService $userService
	) {
		$this->socialLoginRepository = $socialLoginRepository;
		$this->personRepository = $personRepository;
		$this->userRepository = $userRepository;
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
		if(!$user) {
			$userDetail = $this->skautisUserService->getPersonalDetail();
			$user = $this->userService->createAccount($token, $userDetail);
		}

		return new Identity($user->id, $user->role,  ['username' => $user->nick]);
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
