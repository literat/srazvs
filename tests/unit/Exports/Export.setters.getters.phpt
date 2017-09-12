<?php

use Tester\Assert;
use Mockery\MockInterface;
use App\Models\ExportModel;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../../app/models/BaseModel.php';
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
		$this->invokeMethod($this->export, 'setMeetingId', [321]);
		Assert::same(321, $this->invokeMethod($this->export, 'getMeetingId'));
	}

	/**
	 * Call protected/private method of a class.
	 *
	 * @param object &$object    Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array  $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	public function invokeMethod(&$object, $methodName, array $parameters = [])
	{
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}
}

$mockedDatabase = Mockery::mock(Nette\Database\Context::class);
$mockedCategory = Mockery::mock(App\Models\CategoryModel::class);

$ExportModel = new ExportModel(
	$mockedDatabase,
	$mockedCategory
);

$ExportSettersGettersTest = new ExportSettersGettersTest($ExportModel);
$ExportSettersGettersTest->run();
