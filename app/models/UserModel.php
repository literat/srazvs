<?php

namespace App\Models;

use App\Entities\UserEntity;
use Nette\Database\Context;
use Nette\Reflection\ClassType;
use App\Entities\IEntity;
use \Exception;

/**
 * Social Logins
 *
 * class for handling social logins
 */
class UserModel extends BaseModel implements IModel
{

	/** @var string */
	protected $table = 'kk_users';

	/**
	 * @param Context  $database
	 */
	public function __construct(Context $database)
	{
		$this->setDatabase($database);
	}

	/**
	 * @return	ActiveRow
	 */
	public function all()
	{
		return;
	}

	/**
	 * @param  int $id
	 * @return UserEntity
	 */
	public function find($id): UserEntity
	{
		$block = parent::find($id);
		return $this->hydrate($block);
	}

	/**
	 * @param IEntity $entity
	 * @return bool|mixed
	 * @throws Exception
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


	/**
	 * @param  $item
	 * @param  $id
	 * @return mixed
	 */
	private function setIdentity($item, $id)
	{
		$ref = new ClassType($item);
		$idProperty = $ref->getProperty('data');
		//dd($idProperty->id);
		$idProperty->setAccessible(true);
		$idProperty->setValue($item, $id);
dd($item);
		return $item;
	}

}
