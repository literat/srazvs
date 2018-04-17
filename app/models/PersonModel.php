<?php

namespace App\Models;

use App\Entities\IEntity;
use App\Entities\PersonEntity;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

class PersonModel extends BaseModel implements IModel
{
	/**
	 * @var string
	 */
	protected $table = 'kk_persons';

	/**
	 * @param Context $database
	 */
	public function __construct(Context $database)
	{
		$this->setDatabase($database);
	}

	public function all(): ActiveRow
	{
		//return;
	}

	/**
	 * @param  int          $id
	 * @return PersonEntity
	 */
	public function find($id): PersonEntity
	{
		$block = parent::find($id);

		return $this->hydrate($block);
	}

	/**
	 * @param  IEntity    $entity
	 * @return bool|mixed
	 * @throws \Exception
	 */
	public function save(IEntity $entity)
	{
		if ($entity->getId() === null) {
			$values = $entity->toArray();

			$result = $this->create($values);
		//$result = $this->setIdentity($row, $row->id);
		} else {
			$values = $entity->toArray();
			$result = $this->update($entity->getId(), $values);
		}

		return $result;
	}

	/**
	 * @param  $values
	 * @return PersonEntity
	 */
	public function hydrate($values): PersonEntity
	{
		$entity = new PersonEntity();
		//$this->setIdentity($entity, $values->id);

		// unset($values['id']);
		foreach ($values as $property => $value) {
			$entity->$property = $value;
		}

		return $entity;
	}
}
