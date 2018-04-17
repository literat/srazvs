<?php

namespace App\Services\Skautis;

class AuthService extends SkautisService
{
	public function getLoginUrl(string $backlink): string
	{
		return $this->getSkautis()->getLoginUrl($backlink);
	}

	/**
	 * Sets initial data after login to Skautis.
	 *
	 * @param  array $arr
	 * @return void
	 */
	public function setInit(array $arr)
	{
		//$this->skautis->setLoginData($arr['skautIS_Token'], $arr['skautIS_IDRole'], $arr['skautIS_IDUnit']);
		$this->getSkautis()->setLoginData($arr);
	}

	/**
	 * Return url for log out.
	 */
	public function getLogoutUrl(): string
	{
		return $this->getSkautis()->getLogoutUrl();
	}
}
