<?php

namespace App\Models;

use App\Entities\IEntity;
use App\Entities\UserEntity;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

class UserModel extends BaseModel implements IModel
{
	/**
	 * @var string
	 */
	protected $table = 'kk_users';

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
	 * @param  int        $id
	 * @return UserEntity
	 */
	public function find($id): UserEntity
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
	 * @return UserEntity
	 */
	public function hydrate($values): UserEntity
	{
		$entity = new UserEntity();
		//$this->setIdentity($entity, $values->id);

		// unset($values['id']);
		foreach ($values as $property => $value) {
			$entity->$property = $value;
		}

		return $entity;
	}
}
