<?php

namespace App\Models;

use App\Entities\SocialLoginEntity;
use Nette\Database\Context;
use Nette\Reflection\ClassType;
use App\Entities\IEntity;
use \Exception;

/**
 * Social Logins
 *
 * class for handling social logins
 */
class SocialLoginModel extends BaseModel implements IModel
{

	/** @var string */
	protected $table = 'kk_social_logins';

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
	 * @return SocialLoginEntity
	 */
	public function find($id): SocialLoginEntity
	{
		$block = parent::find($id);
		return $this->hydrate($block);
	}

    /**
     * @param  string $provider
     * @param  string $token
     * @return SocialLoginEntity
     */
    public function findByProviderAndToken(string $provider, string $token)//: SocialLoginEntity
    {
        $socialLogin = $this->getDatabase()
            ->table($this->getTable())
            ->where('provider', $provider)
            ->where('token', $token)
            ->fetchAll();

        if($socialLogin) {
            $socialLogin = $this->hydrate($socialLogin);
        }

        return $socialLogin ?: null;
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
			//$result = $this->setIdentity($entity, $id);

		} else {
			$values = $entity->toArray();
			$result = $this->update($entity->getId(), $values);
		}

		return $result;
	}

	/**
	 * @param  $values
	 * @return SocialLoginEntity
	 */
	public function hydrate($values): SocialLoginEntity
	{
		$entity = new SocialLoginEntity();
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
		$idProperty = $ref->getProperty('id');
		$idProperty->setAccessible(true);
		$idProperty->setValue($item, $id);

		return $item;
	}

}
