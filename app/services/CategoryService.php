<?php

namespace App\Services;

use Tracy\Debugger;
use Nette\Utils\Strings;
use App\Models\CategoryModel;
use Nette\Database\Table\ActiveRow;

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

	/**
	 * @return array
	 */
	public function all(): array
	{
		return $this->getCategoryModel()->all();
	}

	/**
	 * @param  string $name
	 * @return string
	 */
	protected function getStyleFromName($name)
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
	 * @param CategoryModel $categoryModel
	 *
	 * @return self
	 */
	public function setCategoryModel(CategoryModel $categoryModel): self
	{
		$this->categoryModel = $categoryModel;

		return $this;
	}

}
