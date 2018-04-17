<?php

namespace App\Services;

use App\Models\CategoryModel;
use Nette\Utils\Strings;

class CategoryService
{
	/**
	 * @var CategoryModel
	 */
	private $categoryModel;

	/**
	 * @param CategoryModel $categoryModel
	 */
	public function __construct(CategoryModel $categoryModel)
	{
		$this->setCategoryModel($categoryModel);
	}

	public function all(): array
	{
		return $this->getCategoryModel()->all();
	}

	protected function getStyleFromName(string $name): string
	{
		$style = Strings::toAscii($name);
		$style = str_replace(" ", "_", $style);

		return $style;
	}

	/**
	 * @return CategoryModel
	 */
	public function getCategoryModel(): CategoryModel
	{
		return $this->categoryModel;
	}

	/**
	 * @param  CategoryModel $categoryModel
	 * @return self
	 */
	public function setCategoryModel(CategoryModel $categoryModel): self
	{
		$this->categoryModel = $categoryModel;

		return $this;
	}
}
