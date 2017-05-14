<?php

namespace App\Components;

use Nette\Application\UI\Control;

abstract class BaseControl extends Control
{

	const TEMPLATE_DIR = __DIR__ . '/../templates/components';
	const TEMPLATE_EXT = 'latte';

	/**
	 * @var integer
	 */
	protected $meetingId = null;

	/**
	 * @return integer
	 */
	public function getMeetingId()
	{
		return $this->meetingId;
	}

	/**
	 * @param  integer $id
	 * @return $this
	 */
	public function setMeetingId($id = null)
	{
		$this->meetingId = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	protected function buildTemplatePath()
	{
		return sprintf(
			'%s/%s.%s',
			static::TEMPLATE_DIR,
			static::TEMPLATE_NAME,
			self::TEMPLATE_EXT
		);
	}

}
