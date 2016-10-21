<?php

/**
 * Authentication service
 */
class AuthService extends \BaseService
{

	/**
	 * Gets login url
	 *
	 * @param   string  $backlink
	 * @return  string
	 */
	public function getLoginUrl($backlink)
	{
		return $this->skautis->getLoginUrl($backlink);
	}


	/**
	 * Sets initial data after login to SkautIS
	 *
	 * @param   array  $arr
	 * @return  void
	 */
	public function setInit(array $arr)
	{
		$this->skautis->setLoginData($arr['skautIS_Token'], $arr['skautIS_IDRole'], $arr['skautIS_IDUnit']);
	}


	/**
	 * Return url for log out
	 *
	 * @param   void
	 * @return  string
	 */
	public function getLogoutUrl()
	{
		return $this->skautis->getLogoutUrl();
	}

}
