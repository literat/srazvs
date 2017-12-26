<?php

use Mockery\MockInterface;
use Tester\Assert;
use Tests\Unit\BaseTestCase;
use App\Repositories\VisitorRepository;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../../app/repositories/VisitorRepository.php';

class VisitorRepositoryTest extends BaseTestCase
{

	private $service = null;

	public function __construct(VisitorRepository $service)
	{
		$this->service = $service;
	}

	public function code4BankCalculationDataProvider()
	{
		return [
			[
				[
					'name'		=> 'Alenka',
					'surname'	=> 'Nováková',
					'birthday'	=> '12.12.2012',
				],
				'AN12',
			],
		];
	}


	/**
	 * @dataProvider code4BankCalculationDataProvider
	 */
	public function testCalculationOfCodeForBank($visitor, $expected)
	{
		Assert::same(
			$expected,
			$this->service->calculateCode4Bank(
				$visitor['name'],
				$visitor['surname'],
				$visitor['birthday']
			)
		);
	}

	public function fieldsFilteringDataProvider()
	{
		return [
			[
				[
					'name'		=> 'Alenka',
					'surname'	=> 'Nováková',
					'birthday'	=> '12.12.2012',
				],
				[
					'name',
					'surname',
				],
				[
					'name'		=> 'Alenka',
					'surname'	=> 'Nováková',
				],
			],
		];
	}

	/**
	 * @dataProvider fieldsFilteringDataProvider
	 */
	public function testFilteringFields($data, $fields, $expected)
	{
		Assert::same(
			$expected,
			$this->invokeMethod($this->service, 'filterFields', [$data, $fields])
		);
	}

	public function datetimeDataProvider()
	{
		return [
			[
				'2017-08-10',
				new Datetime('2017-08-10'),
			],
			[
				new DateTime('2017-10-08'),
				new Datetime('2017-10-08'),
			],
		];
	}

	/**
	 * @dataProvider datetimeDataProvider
	 */
	public function testConvertingToDatetime($datetime, $expected)
	{
		Assert::type(
			'DateTime',
			$this->invokeMethod($this->service, 'convertToDatetime', [$datetime])
		);

		Assert::same(
			$expected->format('d. m. Y'),
			($this->invokeMethod($this->service, 'convertToDatetime', [$datetime]))->format('d. m. Y')
		);
	}

	public function filterProgramFieldsDataProvider()
	{
		return [
			[
				[
					'meeting' => 0,
					'blck_6' => 1,
					'blck_8' => 2,
					'blck_10' => 3,
					'blck_12' => 4,
					'blck_14' => 5,
					'blck_15' => 6,
				],
				[
					6 => 1,
					8 => 2,
					10 => 3,
					12 => 4,
					14 => 5,
					15 => 6,
					18 => 0,
					19 => 0,
					391 => 0,
					396 => 0,
				],
			],
		];
	}

	/**
	 * @dataProvider filterProgramFieldsDataProvider
	 */
	public function testFilteringProgramFields($data, $expected)
	{
		Assert::same(
			$expected,
			$this->invokeMethod($this->service, 'filterProgramFields', [$data])
		);
	}

}

$mockedVisitorModel = Mockery::mock('App\Models\VisitorModel');
$mockedMealModel = Mockery::mock('App\Models\MealModel');
$mockedBLockModel = Mockery::mock('App\Models\BlockModel');
$mockedProgramRepository = Mockery::mock('App\Repositories\ProgramRepository');

$mockedBLockModel->shouldReceive('idsFromCurrentMeeting')->andReturn([
	6 => ['id' => 6],
	8 => ['id' => 8],
	10 => ['id' => 10],
	12 => ['id' => 12],
	14 => ['id' =>14],
	15 => ['id' => 15],
	18 => ['id' => 18],
	19 => ['id' => 19],
	391 => ['id' => 391],
	396 => ['id' => 396],
]);

$visitorRepository = new VisitorRepository(
	$mockedVisitorModel,
	$mockedMealModel,
	$mockedBLockModel,
	$mockedProgramRepository
);

$test = new VisitorRepositoryTest($visitorRepository);
$test->run();
