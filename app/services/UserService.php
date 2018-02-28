<?php

namespace App\Services;

use App\Repositories\SocialLoginRepository;
use App\Repositories\PersonRepository;
use App\Repositories\UserRepository;
use App\Entities\CredentialsEntity;

class UserService
{

	/**
	 * @var SocialLoginRepository
	 */
	protected $socialLoginRepository;

	/**
	 * @var PersonRepository
	 */
	protected $personRepository;

	/**
	 * @var UserRepository
	 */
	protected $userRepository;

	function __construct(
		SocialLoginRepository $socialLoginRepository,
		PersonRepository $personRepository,
		UserRepository $userRepository
	)
	{
		$this->setSocialLoginRepository($socialLoginRepository);
		$this->setPersonRepository($personRepository);
		$this->setUserRepository($userRepository);
	}

	/**
	 * @param string $provider
	 * @param string $token
	 * @return CredentialsEntity
	 */
	public function findByProviderAndToken(string $provider, string $token)//: ?CredentialsEntity
	{
		$socialLogin = $this->getSocialLoginRepository()->findByProviderAndToken($provider, $token);

		$credentials = null;
		if($socialLogin) {
            $credentials = new CredentialsEntity();
            $credentials->id = $socialLogin->user->id;
            $credentials->guid = $socialLogin->user->guid;
            $credentials->login = $socialLogin->user->login;
            $credentials->role = $socialLogin->user->is_admin ? 'admin' : 'guest';
            $credentials->name = $socialLogin->user->person->name;
            $credentials->surname = $socialLogin->user->person->surname;
            $credentials->nick = $socialLogin->user->person->nick;
            $credentials->birthday = $socialLogin->user->person->birthday;
            $credentials->email = $socialLogin->user->person->email;
        }

		return $credentials;
	}

	public function createAccount(string $token, $userDetail): CredentialsEntity
	{
		$newPersonalDetail = [
			'name'        => $userDetail->FirstName,
			'surname'     => $userDetail->LastName,
			'nick'        => $userDetail->NickName,
			'birthday'    => $userDetail->Birthday,
			'email'       => $userDetail->Email,
			'street'      => $userDetail->Street,
			'city'        => $userDetail->City,
			'postal_code' => $userDetail->Postcode,
		];

		$newPerson = $this->getPersonRepository()->create($newPersonalDetail);

		$newUser = [
			'login' => $newPerson->email,
			'person' => $newPerson->id,
		];

		$newUser = $this->getUserRepository()->create($newUser);

		$newSocialLogin = [
			'user'     => $newUser->id,
			'token'    => $token,
			'provider' => 'skautis'
		];

		$this->getSocialLoginRepository()->create($newSocialLogin);

		$credentials = new CredentialsEntity();
		$credentials->id = $newUser->id;
		$credentials->guid = $newUser->guid;
		$credentials->login = $newUser->login;
		$credentials->name = $newPerson->name;
		$credentials->surname = $newPerson->surname;
		$credentials->nick = $newPerson->nick;
		$credentials->role = 'guest';

		return $credentials;
	}

	/**
	 * @return PersonRepository
	 */
	protected function getPersonRepository(): PersonRepository
	{
		return $this->personRepository;
	}

	/**
	 * @param  PersonRepository $repository
	 * @return self
	 */
	protected function setPersonRepository(PersonRepository $repository): self
	{
		$this->personRepository = $repository;

		return $this;
	}

	/**
	 * @return UserRepository
	 */
	public function getUserRepository(): UserRepository
	{
		return $this->userRepository;
	}

	/**
	 * @param  UserRepository $userRepository
	 * @return self
	 */
	public function setUserRepository(UserRepository $repository): self
	{
		$this->userRepository = $repository;

		return $this;
	}

	/**
	 * @param  SocialLoginRepository $repository
	 * @return $this
	 */
	protected function setSocialLoginRepository(SocialLoginRepository $repository): self
	{
		$this->socialLoginRepository = $repository;

		return $this;
	}

	/**
	 * @return SocialLoginRepository
	 */
	protected function getSocialLoginRepository(): SocialLoginRepository
	{
		return $this->socialLoginRepository;
	}
}
