<?php

namespace App\Services;

use Nette;

class Authenticator extends Nette\Object implements Nette\Security\IAuthenticator
{

    public function authenticate(array $credentials)
    {
        $identity = $credentials[0];
        return new Nette\Security\Identity($identity->id, null,  ['username' => $identity->nick]);
    }

}
