<?php

use App\Components\Forms\ProgramForm;
use Mockery\MockInterface;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tests\Unit\BaseTestCase;
use Nette\Utils\DateTime;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../../../app/components/BaseControl.php';
require_once __DIR__ . '/../../../../app/components/Forms/BaseForm.php';
require_once __DIR__ . '/../../../../app/components/Forms/ProgramForm.php';

class ProgramFormTest extends BaseTestCase
{

	private $form = null;

	public function __construct(ProgramForm $form)
	{
		$this->form = $form;
	}

	public function testBuildBlockSelect()
	{
		Assert::same(
			[
				111 => 'sun, %00:%0:%th - %00:%0:%th : test'
			],
			$this->invokeMethod($this->form, 'buildBlockSelect')
		);
	}

	public function testBuildCategorySelect()
	{
		Assert::same(
			[
				111 => 'test'
			],
			$this->invokeMethod($this->form, 'buildCategorySelect')
		);
	}

}

$mockedBlockRepository = Mockery::mock('App\Repositories\BlockRepository');
$mockedCategoryRepository = Mockery::mock('App\Repositories\CategoryRepository');

$mockedBlocks = [
	'id'   => 111,
	'day'  => 'sun',
	'from' => DateTime::from('2017-12-12 00:00:00'),
	'to'   => DateTime::from('2017-12-13 00:00:00'),
	'name' => 'test',
];
$mockedBlocks = ArrayHash::from($mockedBlocks);

$mockedCategories = [
	'id'   => 111,
	'name' => 'test',
];
$mockedCategories = ArrayHash::from($mockedCategories);

$mockedBlockRepository->shouldReceive('findByMeeting')->andReturn([$mockedBlocks]);
$mockedCategoryRepository->shouldReceive('findAll')->andReturn([$mockedCategories]);

$programForm = new ProgramForm(
	$mockedBlockRepository,
	$mockedCategoryRepository
);

$test = new ProgramFormTest($programForm);
$test->run();
