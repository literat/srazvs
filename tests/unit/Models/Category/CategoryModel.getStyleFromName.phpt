<?php

use Mockery\MockInterface;
use Tester\Assert;
use Tests\Unit\BaseTestCase;
use App\Models\CategoryModel;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../../../app/models/BaseModel.php';
require_once __DIR__ . '/../../../../app/models/CategoryModel.php';

class CategoryModelGetStyleFromNameTest extends BaseTestCase
{

	private $model = null;

	public function __construct(CategoryModel $model)
	{
		$this->model = $model;
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
			$this->invokeMethod($this->model, 'getStyleFromName', [$categoryName])
		);
	}

}

$mockedContext = Mockery::mock('Nette\Database\Context');
$mockedCache = Mockery::mock('Nette\Caching\Cache');

$categoryModel = new CategoryModel($mockedContext, $mockedCache);

$test = new CategoryModelGetStyleFromNameTest($categoryModel);
$test->run();
