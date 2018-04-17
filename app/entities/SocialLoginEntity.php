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

	public function getId(): int
	{
		return $this->id;
	}

	public function toArray(): array
	{
		$userId = $this->user;
		$properties = get_object_vars($this);
		$properties['user_id'] = $userId;
		unset($properties['user']);

		return $properties;
	}
}
