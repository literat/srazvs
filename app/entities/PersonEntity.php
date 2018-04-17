<?php

namespace App\Entities;

class PersonEntity implements IEntity
{
	public $id;

	public $guid;

	public $name;

	public $surname;

	public $nick;

	public $birthday;

	public $email;

	public $street;

	public $city;

	public $postalCode;

	public $createdAt;

	public $updatedAt;

	public $deletedAt;

	public function getId(): int
	{
		return $this->id;
	}

	public function toArray(): array
	{
		return get_object_vars($this);
	}
}
