<?php

namespace App\Components;

use App\Services\CategoryService;

class CategoryStylesControl extends BaseControl
{
	const TEMPLATE_NAME = 'CategoryStyles';

	/**
	 * @var CategoryService
	 */
	private $categoryService;

	/**
	 * @param CategoryService $service
	 */
	public function __construct(CategoryService $service)
	{
		$this->setCategoryService($service);
	}

	public function render()
	{
		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->categories = $this->getCategoryService()->all();
		$template->render();
	}

	/**
	 * @return CategoryService
	 */
	protected function getCategoryService()
	{
		return $this->categoryService;
	}

	/**
	 * @param  CategoryService $service
	 * @return self
	 */
	protected function setCategoryService(CategoryService $service): self
	{
		$this->categoryService = $service;

		return $this;
	}
}
