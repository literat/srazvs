<?php

/**
 * Test: App\Emailer sendPaymentInfo.
 */

use App\Services\Emailer;
use Mockery\MockInterface;
use Nette\Mail\Message;
use Nette\Utils\ArrayHash;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestMailer.php';
require_once __DIR__ . '/../../../app/services/Emailer.php';

class EmailerPaymentInfoTest extends Tester\TestCase
{

	private $mailer = null;

	public function __construct($mailer)
	{
		$this->mailer = $mailer;
	}

	public function testSendingPaymentInfoAdvance()
	{
		$recipient = [
			'email' => 'prilis.zlutoucky@kun.cz',
			'name'  => 'Příliš',
			'surname' => 'žluťoučký kůň',
		];
		$recipient = [ArrayHash::from($recipient)];
		$type = 'advance';

		$this->mailer->sendPaymentInfo($recipient, $type);

		Assert::match(<<<'EOD'
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: Srazy VS <srazyvs@hkvs.cz>
To: =?UTF-8?B?UMWZw61sacWhIMW+bHXFpW91xI1rw70ga8WvxYg=?=
	 <prilis.zlutoucky@kun.cz>
Subject: =?UTF-8?B?U3JheiBWUzogemFwbGFjZW7DrSB6w6Fsb2h5?=
Message-ID: <%a%@%a%>
Content-Type: multipart/alternative;
	boundary="--------%S%"

----------%S%
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit



		Zaplacení zálohy na sraz VS


		Ahoj,
		tvoje záloha byla úspěšně přijata!


		Pro případné dotazy piš na srazyvs@hkvs.cz.


		Na setkání se těší přípravný tým srazu VS.


----------%S%
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 8bit

<html>
	<head>
		<title>Zaplacení zálohy na sraz VS</title>
	</head>
	<body>
		Ahoj,<br /><br />
		tvoje záloha byla úspěšně přijata!
		<br />
		<br />
		Pro případné dotazy piš na <a href='mailto:srazyvs@hkvs.cz'>srazyvs@hkvs.cz</a>.
		<br />
		<br />
		Na setkání se těší přípravný tým srazu VS.
	</body>
</html>
----------%S%--
EOD
		, TestMailer::$output);
	}
}

$template = array(
	"subject" => "Sraz VS: zaplacení zálohy",
	"message" => "<html>
	<head>
		<title>Zaplacení zálohy na sraz VS</title>
	</head>
	<body>
		Ahoj,<br /><br />
		tvoje záloha byla úspěšně přijata!
		<br />
		<br />
		Pro případné dotazy piš na <a href='mailto:srazyvs@hkvs.cz'>srazyvs@hkvs.cz</a>.
		<br />
		<br />
		Na setkání se těší přípravný tým srazu VS.
	</body>
</html>"
);

$mockedSettings = Mockery::mock(App\Models\SettingsModel::class);

$testMailer = new TestMailer();

$mockedEmailer = Mockery::mock('App\Services\Emailer[getTemplate]', array($mockedSettings, $testMailer));
$mockedEmailer->shouldReceive('getTemplate')->with('advance')->andReturn($template);

$EmailerPaymentInfoTest = new EmailerPaymentInfoTest($mockedEmailer);
$EmailerPaymentInfoTest->run();
