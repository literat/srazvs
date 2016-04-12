<?php

/**
 * Test: App\Emailer tutor.
 */

use Mockery\MockInterface;
use Nette\Mail\Message;
use Tester\Assert;
use App\Emailer;

//require_once __DIR__ . '/../../inc/define.inc.php';
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestMailer.php';
require_once __DIR__ . '/../../app/models/EmailerModel.php';

define('PRJ_DIR', 'http://vodni.skauting.cz/srazvs/');


class EmailerTutorTest extends Tester\TestCase
{

	private $mailer = null;

	public function __construct($mailer)
	{
		$this->mailer = $mailer;
	}

	public function testSendingTutor()
	{
		$recipient = array(
			'prilis.zlutoucky@kun.cz' => 'Příliš žluťoučký kůň',
		);
		$hash = 12345;
		$type = 'block';

		//$mockedEmailer = Mockery::mock('Emailer[getTemplate]');
		//$mockedEmailer->shouldReceive('getTemplate')->with('block')->andReturn('rimmer');

		$this->mailer->tutor($recipient, $hash, $type);

		Assert::match(<<<'EOD'
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: Srazy VS <srazyvs@hkvs.cz>
To: =?UTF-8?B?UMWZw61sacWhIMW+bHXFpW91xI1rw70ga8WvxYg=?=
	 <prilis.zlutoucky@kun.cz>
Subject: =?UTF-8?B?w5pwxJtsIMSPw6FiZWxza8OpIMOzZHk=?=
Message-ID: <%a%@%a%>
Content-Type: multipart/alternative;
	boundary="--------%S%"

----------%S%
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit

Testování
----------%S%
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 8bit

Testování
----------%S%--
EOD
		, TestMailer::$output);
	}

	public function testSendingMultipleTutors()
	{
		# code...
	}

	public function testSendingMultipleTutorsWithoutNames()
	{
		# code...
	}
}

$mockedSettings = Mockery::mock(App\SettingsModel::class);

$testMailer = new TestMailer();
//$emailer = new Emailer($mockedSettings, $testMailer);

$mockedEmailer = Mockery::mock(App\Emailer::class);
$mockedEmailer->shouldReceive('getTemplate')->with('tutor')->andReturn('rimmer');

//$mockedEmailer->tutor($recipient, $hash, $type);


//var_dump($emailer);
//die;

$EmailerTutorTest = new EmailerTutorTest($mockedEmailer);
$EmailerTutorTest->run();
