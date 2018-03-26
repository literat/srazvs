<?php

namespace App\Repositories;

use App\Entities\UserEntity;
use App\Models\UserModel;

class UserRepository
{

	/**
	 * @var UserModel
	 */
	protected $userModel;

	/**
	 * @param UserModel $userModel
	 */
	public function __construct(UserModel $userModel)
	{
		$this->setUserModel($userModel);
	}

	/**
	 * @return array
	 */
	public function all(): array
	{
		return $this->getUserModel()->all();
	}

	/**
	 * @param  int $id
	 * @return UserEntity
	 */
	public function find(int $id): UserEntity
	{
		return $this->getUserModel()->find($id);
	}

	/**
	 * @param  $user
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function create($user)
	{
		unset($user->id);

		return $this->getUserModel()
			->save(
				$this->populate($user)
			);
	}

	/**
	 * @param  $id
	 * @param  $user
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function update($user)
	{
		return $this->getUserModel()
			->save(
				$this->populate($user)
			);
	}

	/**
	 * @param  $id
	 * @param  $user
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function save($user)
	{
		return $this->getUserModel()
			->save(
				$this->populate($user)
			);
	}

	/**
	 * @param  $id
	 * @return bool
	 */
	public function delete($id): bool
	{
		return $this->getUserModel()->delete($id);
	}

	/**
	 * @param  array $values
	 * @return UserEntity
	 */
	protected function populate($values): UserEntity
	{
		$model = $this->getUserModel();
		$user = $model->hydrate($values);

		return $user;
	}

	/**
	 * @return UserModel
	 */
	protected function getUserModel(): UserModel
	{
		return $this->userModel;
	}

	/**
	 * @param  UserModel $model
	 * @return $this
	 */
	protected function setUserModel(UserModel $model): self
	{
		$this->userModel = $model;

		return $this;
	}

}
