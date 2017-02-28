<?php

namespace SkautisAuth;

use Nette;

/**
 * Use for data from SkautIS only!
 * Never let user to set his ID himself.
 * @author Hána František <sinacek@gmail.com>
 */
class SkautisAuthenticator extends Nette\Object implements Nette\Security\IAuthenticator
{

	public function authenticate(array $credentials)
	{
		$data = $credentials[0];
		return new Nette\Security\Identity($data->ID_User);
	}

}
