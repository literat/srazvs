<?php

namespace App\Services\Skautis;

/**
 * Authentication service
 */
class AuthService extends SkautisService
{

	/**
	 * Gets login url
	 *
	 * @param   string  $backlink
	 * @return  string
	 */
	public function getLoginUrl($backlink)
	{
		return $this->getSkautis()->getLoginUrl($backlink);
	}


	/**
	 * Sets initial data after login to Skautis
	 *
	 * @param   array  $arr
	 * @return  void
	 */
	public function setInit(array $arr)
	{
		//$this->skautis->setLoginData($arr['skautIS_Token'], $arr['skautIS_IDRole'], $arr['skautIS_IDUnit']);
		$this->getSkautis()->setLoginData($arr);
	}


	/**
	 * Return url for log out
	 *
	 * @param   void
	 * @return  string
	 */
	public function getLogoutUrl()
	{
		return $this->getSkautis()->getLogoutUrl();
	}

}
