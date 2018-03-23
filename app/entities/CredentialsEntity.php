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
		return get_object_vars($this);
	}

}
