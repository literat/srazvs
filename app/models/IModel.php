<?php

namespace App\Models;

use App\Entities\IEntity;

interface IModel
{
	/**
	 * @return mixed
	 */
	public function all();

	/**
	 * @param  $id
	 * @return mixed
	 */
	public function find($id);

	/**
	 * @param  array $values
	 * @return mixed
	 */
	public function findBy(string $column, $value);

	/**
	 * @param  $entity
	 * @return mixed
	 */
	public function save(IEntity $entity);
}
