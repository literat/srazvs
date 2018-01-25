<?php

namespace App\Entities;

class BlockEntity implements IEntity
{

	public $id;

	public $name;

	public $from;

	public $to;

	public $day;

	public $description;

	public $material;

	public $tutor;

	public $email;

	public $program;

	public $display_progs;

	public $capacity;

	public $category;

	public $meeting;

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
