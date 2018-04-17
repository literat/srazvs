<?php

namespace App\Entities;

class CredentialsEntity implements IEntity
{
	public $id;

	public $guid;

	public $login;

	public $name;

	public $surname;

	public $nick;

	public $birthday;

	public $email;

	public function getId(): int
	{
		return $this->id;
	}

	public function toArray(): array
	{
		return get_object_vars($this);
	}
}
