<?php

use App\Repositories\SocialLoginRepository;
use App\Repositories\PersonRepository;
use App\Repositories\UserRepository;s

class AuthService
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

    public function authenticate(string $provider, string $token)
    {
        if($socialLogin = $this->getSocialLoginRepository()->findByProviderAndToken('skautis', $credentials['skautIS_Token'])) {
            $user = $socialLogin->user;
            // return user
        } else {
            $this->createUser();
            // neexistuje
            // založím osobu (person) - osobní data jako adresa a tak
            $newPersonalDetail = $this->getUserService()->getPersonalDetail();
            //var_dump($newPersonalDetail);

            $newPersonalDetail = [
                'name'        => $newPersonalDetail->FirstName,
                'surname'     => $newPersonalDetail->LastName,
                'nick'        => $newPersonalDetail->NickName,
                'birthday'    => $newPersonalDetail->Birthday,
                'email'       => $newPersonalDetail->Email,
                'street'      => $newPersonalDetail->Street,
                'city'        => $newPersonalDetail->City,
                'postal_code' => $newPersonalDetail->Postcode,
            ];

            $newPerson = $this->getPersonRepository()->create($newPersonalDetail);
            //dd($newPerson);
            //var_dump($newPerson->toArray());
            // založím uživatele (user), email jako login
            $newUser = [
                'login' => $newPerson->email,
                'person' => $newPerson->id,
            ];
            //dd($newUser);
            $newUser = $this->getUserRepository()->create($newUser);


            // založím social login
            $newSocialLogin = [
                'user'     => $newUser->id,
                'token'    => $credentials['skautIS_Token'],
                'provider' => 'skautis'
            ];
            //dd($newSocialLogin);
            $this->getSocialLoginRepository()->create($newSocialLogin);
        }
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
