<?php

/**
 * Test: App\Services\Emailer sendRegistrationSummary.
 */

use App\Emailer;
use Mockery\MockInterface;
use Nette\Mail\Message;
use Nette\Utils\ArrayHash;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestMailer.php';
require_once __DIR__ . '/../../../app/services/Emailer.php';

class EmailerRegistrationSummaryTest extends Tester\TestCase
{

	private $mailer = null;

	public function __construct($mailer)
	{
		$this->mailer = $mailer;
	}

	public function testSendingRegistrationSummary()
	{
		$recipient = [
			'email' => 'prilis.zlutoucky@kun.cz',
			'name'  => 'Příliš žluťoučký kůň',
		];
		$recipient = [ArrayHash::from($recipient)];
		$hash = 12345;
		$code4bank = 'CD82';

		$this->mailer->sendRegistrationSummary($recipient, $hash, $code4bank);

		Assert::match(<<<'EOD'
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: Srazy VS <srazyvs@hkvs.cz>
To: =?UTF-8?B?UMWZw61sacWhIMW+bHXFpW91xI1rw70ga8WvxYg=?=
	 <prilis.zlutoucky@kun.cz>
Subject: Sraz VS: registrace
Message-ID: <%a%@%a%>
Content-Type: multipart/alternative;
	boundary="--------%S%"

----------%S%
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit



		Registrační údaje na sraz vodních skautů


		Ahoj,
		tvoje registrace na sraz vodních skautů byla úspěšně přijata.
		Svoje údaje si můžeš zkontrolovat na adrese: http://vodni.skauting.cz/srazvs/registration/?hash=12345&cms=check


		K tebou vybraným programům budeš přiřazen až po zaplacení nevratné zálohy 200,- na účet KVS 2300183549/2010, Variabilní symbol 11111XXXXX (místo písmen doplň číslo přístavu/střediska) a do poznámky uveď svůj kód!


		Zálohu zaplať nejpozději do 13. listopadu (počítá se odeslání). Při nezaplacení zálohy včas bude účastnický poplatek na místě 400 Kč.


		Kód do poznámky: CD82


Pokud budete platit za víc osob najednou, uveďte kódy za VŠECHNY, za které platíte!!!

		Pro případné otázky pište na srazyvs@hkvs.cz.


		Na setkání se těší přípravný tým srazu VS.


----------%S%
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 8bit

<html>
	<head>
		<title>Registrační údaje na sraz vodních skautů</title>
	</head>
	<body>
		Ahoj,<br />
		tvoje registrace na sraz vodních skautů byla úspěšně přijata.<br />
		Svoje údaje si můžeš zkontrolovat na adrese: <a href='http://vodni.skauting.cz/srazvs/registration/?hash=12345&cms=check'>http://vodni.skauting.cz/srazvs/registration/?hash=12345&cms=check</a>
		<br />
		<br />
		K tebou vybraným programům budeš přiřazen až po zaplacení nevratné zálohy 200,- na účet KVS 2300183549/2010, Variabilní symbol 11111XXXXX (místo písmen doplň číslo přístavu/střediska) a do poznámky uveď svůj kód!
		<br />
		<br />
		Zálohu zaplať nejpozději do 13. listopadu (počítá se odeslání). Při nezaplacení zálohy včas bude účastnický poplatek na místě 400 Kč.
		<br />
		<br />
		Kód do poznámky: <b>CD82</b>
		<br />
		<br />
Pokud budete platit za víc osob najednou, uveďte kódy za VŠECHNY, za které platíte!!!
		<br />
		Pro případné otázky pište na <a href='mailto:srazyvs@hkvs.cz'>srazyvs@hkvs.cz</a>.
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
	"subject" => "Sraz VS: registrace",
	"message" => "<html>
	<head>
		<title>Registrační údaje na sraz vodních skautů</title>
	</head>
	<body>
		Ahoj,<br />
		tvoje registrace na sraz vodních skautů byla úspěšně přijata.<br />
		Svoje údaje si můžeš zkontrolovat na adrese: <a href='http://vodni.skauting.cz/srazvs/registration/?hash=%%[kontrolni-hash]%%&cms=check'>http://vodni.skauting.cz/srazvs/registration/?hash=%%[kontrolni-hash]%%&cms=check</a>
		<br />
		<br />
		K tebou vybraným programům budeš přiřazen až po zaplacení nevratné zálohy 200,- na účet KVS 2300183549/2010, Variabilní symbol 11111XXXXX (místo písmen doplň číslo přístavu/střediska) a do poznámky uveď svůj kód!
		<br />
		<br />
		Zálohu zaplať nejpozději do 13. listopadu (počítá se odeslání). Při nezaplacení zálohy včas bude účastnický poplatek na místě 400 Kč.
		<br />
		<br />
		Kód do poznámky: <b>%%[variabilni-symbol]%%</b>
		<br />
		<br />
Pokud budete platit za víc osob najednou, uveďte kódy za VŠECHNY, za které platíte!!!
		<br />
		Pro případné otázky pište na <a href='mailto:srazyvs@hkvs.cz'>srazyvs@hkvs.cz</a>.
		<br />
		<br />
		Na setkání se těší přípravný tým srazu VS.
	</body>
</html>"
);

$mockedSettings = Mockery::mock(App\Models\SettingsModel::class);

$testMailer = new TestMailer();

$mockedEmailer = Mockery::mock('App\Services\Emailer[getTemplate]', [$mockedSettings, $testMailer]);
$mockedEmailer->shouldReceive('getTemplate')->with('post_reg')->andReturn($template);

$EmailerRegistrationSummaryTest = new EmailerRegistrationSummaryTest($mockedEmailer);
$EmailerRegistrationSummaryTest->run();
