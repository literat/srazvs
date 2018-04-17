<?php

namespace App\Entities;

class UserEntity implements IEntity
{
	public $id;

	public $guid;

	public $login;

	public $person;

	public $createdAt;

	public $updatedAt;

	public $deletedAt;

	public function getId(): int
	{
		return $this->id;
	}

	public function toArray(): array
	{
		$personId = $this->person;
		$properties = get_object_vars($this);
		$properties['person_id'] = $personId;
		unset($properties['person']);

		return $properties;
	}
}
