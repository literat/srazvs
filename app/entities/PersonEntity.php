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
