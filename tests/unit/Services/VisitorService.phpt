<?php

use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;
use App\Services\VisitorService;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../../app/services/BaseService.php';
require_once __DIR__ . '/../../../app/services/VisitorService.php';

class VisitorServiceTest extends TestCase
{

	private $service = null;

	public function __construct(VisitorService $service)
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
		Assert::same($expected, $this->service->calculateCode4Bank($visitor));
	}

}

$mockedVisitorModel = Mockery::mock('App\Models\VisitorModel');
$mockedMealModel = Mockery::mock('App\Models\MealModel');
$mockedBLockModel = Mockery::mock('App\Models\BlockModel');
$mockedProgramService = Mockery::mock('App\Services\ProgramService');

$visitorService = new VisitorService(
	$mockedVisitorModel,
	$mockedMealModel,
	$mockedBLockModel,
	$mockedProgramService
);

$test = new VisitorServiceTest($visitorService);
$test->run();
