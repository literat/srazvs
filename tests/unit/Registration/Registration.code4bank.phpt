<?php

/**
 * @skip
 */

use Tester\Assert;
use Mockery\MockInterface;
use App\ExportModel;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../../app/controllers/BaseController.php';
require_once __DIR__ . '/../../../app/controllers/RegistrationController.php';

class RegistrationCode4BankTest extends Tester\TestCase
{

	private $registration = null;
	private $data = [
		'name'		=> 'Adam',
		'surname'	=> 'Černík',
		'birthday'	=> '1987-07-12',
	];

	public function __construct($registration)
	{
		$this->registration = $registration;
	}

	public function testCode4Bank()
	{
		Assert::same('AC87', $this->registration->code4Bank($this->data));
	}

}

$mockedDatabase = Mockery::mock(Nette\Database\Context::class);
$mockedContainer = Mockery::mock(Nette\DI\Container::class);
$mockedContainer->parameters['router'] = null;

$RegistrationController = new RegistrationController(
	$mockedDatabase,
	$mockedContainer
);

$RegistrationCode4BankTest = new RegistrationCode4BankTest($RegistrationController);
$RegistrationCode4BankTest->run();
