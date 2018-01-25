<?php

namespace App\Models;

use App\Entities\IEntity;

interface IModel
{

	/**
	 * @return mixed
	 */
	function all();

	/**
	 * @param $id
	 * @return mixed
	 */
	function find($id);

	/**
	 * @param array $values
	 * @return mixed
	 */
	function findBy($column, $value);

	/**
	 * @param $entity
	 * @return mixed
	 */
	function save(IEntity $entity);

}
