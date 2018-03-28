<?php

namespace App\Entities;

class VisitorEntity
{

	public $name;

	public $surname;

	public $nick;

	public $email;

	public $street;

	public $city;

	public $postalCode;

	public $birthday;

	public $groupName;

	public $groupNum;

	public $troopName;

	/**
	 * @return array
	 */
	public function toArray()
	{
		return array_filter(get_object_vars($this));
	}

}
