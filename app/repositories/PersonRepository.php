<?php

namespace App\Repositories;

use App\Entities\PersonEntity;
use App\Models\PersonModel;

class PersonRepository
{

	/**
	 * @var PersonModel
	 */
	protected $personModel;

	/**
	 * @param PersonModel $personModel
	 */
	public function __construct(PersonModel $personModel)
	{
		$this->setPersonModel($personModel);
	}

	/**
	 * @return array
	 */
	public function all(): array
	{
		return $this->getPersonModel()->all();
	}

	/**
	 * @param  int $id
	 * @return PersonEntity
	 */
	public function find(int $id): PersonEntity
	{
		return $this->getPersonModel()->find($id);
	}

	/**
	 * @param  $person
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function create($person)
	{
		unset($person->id);

		return $this->getPersonModel()
			->save(
				$this->populate($person)
			);
	}

	/**
	 * @param  $id
	 * @param  $person
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function update($id, $person)
	{
		return $this->getPersonModel()
			->save(
				$this->populate($person)
			);
	}

	/**
	 * @param  $id
	 * @param  $person
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function save($person)
	{
		return $this->getPersonModel()
			->save(
				$this->populate($person)
			);
	}

	/**
	 * @param  $id
	 * @return bool
	 */
	public function delete($id): bool
	{
		return $this->getPersonModel()->delete($id);
	}

	/**
	 * @param  array $values
	 * @return PersonEntity
	 */
	protected function populate($values): PersonEntity
	{
		$model = $this->getPersonModel();
		$person = $model->hydrate($values);

		return $person;
	}

	/**
	 * @return PersonModel
	 */
	protected function getPersonModel(): PersonModel
	{
		return $this->personModel;
	}

	/**
	 * @param  PersonModel $model
	 * @return $this
	 */
	protected function setPersonModel(PersonModel $model): self
	{
		$this->personModel = $model;

		return $this;
	}

}
