<?php

use Tester\Assert;
use Mockery\MockInterface;
use App\ExportModel;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../../app/models/ExportModel.php';

class ExportSettersGettersTest extends Tester\TestCase
{

	private $export = null;

	public function __construct($export)
	{
		$this->export = $export;
	}

	public function testSettingGraphHeight()
	{
		$this->export->setGraphHeight(123);
		Assert::same(123, $this->export->getGraphHeight());
	}

	public function testSettingMeetingId()
	{
		$this->export->setMeetingId(321);
		Assert::same(321, $this->export->getMeetingId());
	}
}

$mockedDatabase = Mockery::mock(Nette\Database\Context::class);
$mockedPdf = Mockery::mock(App\PdfFactory::class);
$mockedExcel = Mockery::mock(App\ExcelFactory::class);
$mockedView = Mockery::mock(App\View::class);
$mockedCategory = Mockery::mock(App\CategoryModel::class);
$mockedDebug = true;

$ExportModel = new ExportModel(
	$mockedDatabase,
	$mockedPdf,
	$mockedExcel,
	$mockedView,
	$mockedCategory,
	$mockedDebug
);

$ExportSettersGettersTest = new ExportSettersGettersTest($ExportModel);
$ExportSettersGettersTest->run();
