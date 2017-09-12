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

	public $postal_code;

	public $birthday;

	public $group_name;

	public $group_num;

	public $troop_name;

	/**
	 * @return array
	 */
	public function toArray()
	{
		return array_filter(get_object_vars($this));
	}

}
