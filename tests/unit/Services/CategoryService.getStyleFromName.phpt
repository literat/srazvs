<?php

use Mockery\MockInterface;
use Tester\Assert;
use Tests\Unit\BaseTestCase;
use App\Services\CategoryService;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../../app/services/CategoryService.php';

class CategoryServiceGetStyleFromNameTest extends BaseTestCase
{

	private $service = null;

	public function __construct(CategoryService $service)
	{
		$this->service = $service;
	}

	public function categoryNameDataProvider()
	{
		return [
			[
				'testovací kategorie',
				'testovaci_kategorie',
			],
			[
				'snake_case',
				'snake_case',
			],
			[
				'Camel-Case',
				'Camel-Case',
			],
			[
				' _ kategorie _ ',
				'___kategorie___',
			],
		];
	}


	/**
	 * @dataProvider categoryNameDataProvider
	 */
	public function testGetStyleFromName($categoryName, $expected)
	{
		Assert::same(
			$expected,
			$this->invokeMethod($this->service, 'getStyleFromName', [$categoryName])
		);
	}

}

$mockedCategoryModel = Mockery::mock('App\Models\CategoryModel');

$categoryService = new CategoryService($mockedCategoryModel);

$test = new CategoryServiceGetStyleFromNameTest($categoryService);
$test->run();
