<?php

namespace App\Entities;

class SocialLoginEntity implements IEntity
{

	public $id;

	public $guid;

	public $user;

	public $token;

	public $provider;

	public $createdAt;

	public $updatedAt;

	public $deletedAt;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		$userId = $this->user;
		$properties = get_object_vars($this);
		$properties['user_id'] = $userId;
		unset($properties['user']);

		return $properties;
	}

}
