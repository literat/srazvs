<?php

namespace App\Repositories;

use App\Models\CategoryModel;

class CategoryRepository
{

	/**
	 * @var CategoryModel
	 */
	protected $categoryModel;

	/**
	 * @param CategoryModel $categoryModel
	 */
	public function __construct(CategoryModel $categoryModel)
	{
		$this->setCategoryModel($categoryModel);
	}

	/**
	 * @return array
	 */
	public function findAll(): array
	{
		return $this->getCategoryModel()->all();
	}

	/**
	 * @return CategoryModel
	 */
	protected function getCategoryModel(): CategoryModel
	{
		return $this->categoryModel;
	}

	/**
	 * @param  CategoryModel $model
	 * @return $this
	 */
	protected function setCategoryModel(CategoryModel $model): self
	{
		$this->categoryModel = $model;

		return $this;
	}

}
