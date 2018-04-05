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
		$personId = $this->person;
		$properties = get_object_vars($this);
		$properties['person_id'] = $personId;
		unset($properties['person']);

		return $properties;
	}

}
