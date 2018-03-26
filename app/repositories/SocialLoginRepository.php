<?php

namespace App\Repositories;

use App\Entities\SocialLoginEntity;
use App\Models\SocialLoginModel;

class SocialLoginRepository
{

	/**
	 * @var SocialLoginModel
	 */
	protected $socialLoginModel;

	/**
	 * @param SocialLoginModel $socialLoginModel
	 */
	public function __construct(SocialLoginModel $socialLoginModel)
	{
		$this->setSocialLoginModel($socialLoginModel);
	}

	/**
	 * @return array
	 */
	public function all(): array
	{
		return $this->getSocialLoginModel()->all();
	}

	/**
	 * @param  int $id
	 * @return SocialLoginEntity
	 */
	public function find(int $id): SocialLoginEntity
	{
		return $this->getSocialLoginModel()->find($id);
	}

	/**
	 * @param  string $provider
	 * @param  string $token
	 * @return SocialLoginEntity
	 */
	public function findByProviderAndToken(string $provider, string $token)//: SocialLoginEntity
	{
		return $this->getSocialLoginModel()->findByProviderAndToken($provider, $token);
	}

	/**
	 * @param  $socialLogin
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function create($socialLogin)
	{
		unset($socialLogin->id);

		return $this->getSocialLoginModel()
			->save(
				$this->populate($socialLogin)
			);
	}

	/**
	 * @param  $id
	 * @param  $socialLogin
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function update($socialLogin)
	{
		return $this->getSocialLoginModel()
			->save(
				$this->populate($socialLogin)
			);
	}

	/**
	 * @param  $id
	 * @param  $socialLogin
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function save($socialLogin)
	{
		return $this->getSocialLoginModel()
			->save(
				$this->populate($socialLogin)
			);
	}

	/**
	 * @param  $id
	 * @return bool
	 */
	public function delete($id): bool
	{
		return $this->getSocialLoginModel()->delete($id);
	}

	/**
	 * @param  array $values
	 * @return SocialLoginEntity
	 */
	protected function populate($values): SocialLoginEntity
	{
		$model = $this->getSocialLoginModel();
		$socialLogin = $model->hydrate($values);

		return $socialLogin;
	}

	/**
	 * @return SocialLoginModel
	 */
	protected function getSocialLoginModel(): SocialLoginModel
	{
		return $this->socialLoginModel;
	}

	/**
	 * @param  SocialLoginModel $model
	 * @return $this
	 */
	protected function setSocialLoginModel(SocialLoginModel $model): self
	{
		$this->socialLoginModel = $model;

		return $this;
	}

}
