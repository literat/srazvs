<?php

use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/bootstrap.php';

class HelpersTest extends TestCase
{

	public function testAppVersion()
	{
		$packagePath = realpath(__DIR__ . '/../../package.json');
		$package = json_decode(file_get_contents($packagePath));
		$expected = $package->version;

		Assert::same($expected, appVersion());
	}

}

(new HelpersTest)->run();
