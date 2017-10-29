<?php

/**
 * Test: App\Emailer tutor.
 */

use Mockery\MockInterface;
use Nette\Mail\Message;
use Tester\Assert;
use App\Services\Emailer;

//require_once __DIR__ . '/../../inc/define.inc.php';
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestMailer.php';
require_once __DIR__ . '/../../../app/services/Emailer.php';

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

		$this->mailer->tutor($recipient, $hash, $type);

		Assert::match(<<<'EOD'
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: Srazy VS <srazyvs@hkvs.cz>
To: =?UTF-8?B?UMWZw61sacWhIMW+bHXFpW91xI1rw70ga8WvxYg=?=
	 <prilis.zlutoucky@kun.cz>
Subject: =?UTF-8?B?U3JheiB2b2Ruw61jaCBza2F1dMWvOiBhbm90YWNlIGJsb2s=?=
	=?UTF-8?B?dQ==?=
Message-ID: <%a%@%a%>
Content-Type: multipart/alternative;
	boundary="--------%S%"

----------%S%
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit



		Anotace bloku na sraz VS


		Ahoj,
		jako přednášející/ho bychom Tě chtěli požádat o vyplnění anotace k Tvému bloku nejpozději do 18. 10.
		Údaje můžeš doplnit a dále měnit a upravovat na adrese: http://vodni.skauting.cz/srazvs/annotation/edit/block/12345


		Popis, Tvé jméno a Tvůj e-mail se bude zobrazovat účastníkům srazu na webu, aby věděli, co je čeká. Můžeš změnit i název programu a stanov také maximální počet lidí, kteří se můžou programu zúčastnit.


		Pro případné otázky piš na katka.kaderova(at)seznam.cz.


		Na setkání se těší přípravný tým srazů VS.


----------%S%
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 8bit

<html>
	<head>
		<title>Anotace bloku na sraz VS</title>
	</head>
	<body>
		Ahoj,<br /><br />
		jako přednášející/ho bychom Tě chtěli požádat o vyplnění anotace k Tvému bloku nejpozději do 18. 10.<br />
		Údaje můžeš doplnit a dále měnit a upravovat na adrese: <a href='http://vodni.skauting.cz/srazvs/annotation/edit/block/12345'>http://vodni.skauting.cz/srazvs/annotation/edit/block/12345</a>
		<br />
		<br />
		Popis, Tvé jméno a Tvůj e-mail se bude zobrazovat účastníkům srazu na webu, aby věděli, co je čeká. Můžeš změnit i název programu a stanov také maximální počet lidí, kteří se můžou programu zúčastnit.
		<br />
		<br />
		Pro případné otázky piš na <a href='mailto:katka.kaderova@seznam.cz'>katka.kaderova(at)seznam.cz</a>.
		<br />
		<br />
		Na setkání se těší přípravný tým srazů VS.
	</body>
</html>
----------%S%--
EOD
		, TestMailer::$output);
	}

	public function testSendingMultipleTutors()
	{
		$recipient = array(
			'prilis.zlutoucky@kun.cz'	=> 'Příliš žluťoučký kůň',
			'info@example.com'			=> 'Info',
			'john@doe.com'				=> 'John Doe',
		);
		$hash = 12345;
		$type = 'block';

		$this->mailer->tutor($recipient, $hash, $type);

		Assert::match(<<<'EOD'
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: Srazy VS <srazyvs@hkvs.cz>
To: =?UTF-8?B?UMWZw61sacWhIMW+bHXFpW91xI1rw70ga8WvxYg=?=
	 <prilis.zlutoucky@kun.cz>,Info <info@example.com>,John Doe
	 <john@doe.com>
Subject: =?UTF-8?B?U3JheiB2b2Ruw61jaCBza2F1dMWvOiBhbm90YWNlIGJsb2s=?=
	=?UTF-8?B?dQ==?=
Message-ID: <%a%@%a%>
Content-Type: multipart/alternative;
	boundary="--------%S%"

----------%S%
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit



		Anotace bloku na sraz VS


		Ahoj,
		jako přednášející/ho bychom Tě chtěli požádat o vyplnění anotace k Tvému bloku nejpozději do 18. 10.
		Údaje můžeš doplnit a dále měnit a upravovat na adrese: http://vodni.skauting.cz/srazvs/annotation/edit/block/12345


		Popis, Tvé jméno a Tvůj e-mail se bude zobrazovat účastníkům srazu na webu, aby věděli, co je čeká. Můžeš změnit i název programu a stanov také maximální počet lidí, kteří se můžou programu zúčastnit.


		Pro případné otázky piš na katka.kaderova(at)seznam.cz.


		Na setkání se těší přípravný tým srazů VS.


----------%S%
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 8bit

<html>
	<head>
		<title>Anotace bloku na sraz VS</title>
	</head>
	<body>
		Ahoj,<br /><br />
		jako přednášející/ho bychom Tě chtěli požádat o vyplnění anotace k Tvému bloku nejpozději do 18. 10.<br />
		Údaje můžeš doplnit a dále měnit a upravovat na adrese: <a href='http://vodni.skauting.cz/srazvs/annotation/edit/block/12345'>http://vodni.skauting.cz/srazvs/annotation/edit/block/12345</a>
		<br />
		<br />
		Popis, Tvé jméno a Tvůj e-mail se bude zobrazovat účastníkům srazu na webu, aby věděli, co je čeká. Můžeš změnit i název programu a stanov také maximální počet lidí, kteří se můžou programu zúčastnit.
		<br />
		<br />
		Pro případné otázky piš na <a href='mailto:katka.kaderova@seznam.cz'>katka.kaderova(at)seznam.cz</a>.
		<br />
		<br />
		Na setkání se těší přípravný tým srazů VS.
	</body>
</html>
----------%S%--
EOD
		, TestMailer::$output);
	}

	public function testSendingMultipleTutorsWithoutNames()
	{
		$recipient = array(
			'prilis.zlutoucky@kun.cz'	=> '',
			'info@example.com'			=> '',
			'john@doe.com'				=> '',
		);		$hash = 12345;
		$type = 'block';

		$this->mailer->tutor($recipient, $hash, $type);

		Assert::match(<<<'EOD'
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: Srazy VS <srazyvs@hkvs.cz>
To: prilis.zlutoucky@kun.cz,info@example.com,john@doe.com
Subject: =?UTF-8?B?U3JheiB2b2Ruw61jaCBza2F1dMWvOiBhbm90YWNlIGJsb2s=?=
	=?UTF-8?B?dQ==?=
Message-ID: <%a%@%a%>
Content-Type: multipart/alternative;
	boundary="--------%S%"

----------%S%
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit



		Anotace bloku na sraz VS


		Ahoj,
		jako přednášející/ho bychom Tě chtěli požádat o vyplnění anotace k Tvému bloku nejpozději do 18. 10.
		Údaje můžeš doplnit a dále měnit a upravovat na adrese: http://vodni.skauting.cz/srazvs/annotation/edit/block/12345


		Popis, Tvé jméno a Tvůj e-mail se bude zobrazovat účastníkům srazu na webu, aby věděli, co je čeká. Můžeš změnit i název programu a stanov také maximální počet lidí, kteří se můžou programu zúčastnit.


		Pro případné otázky piš na katka.kaderova(at)seznam.cz.


		Na setkání se těší přípravný tým srazů VS.


----------%S%
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 8bit

<html>
	<head>
		<title>Anotace bloku na sraz VS</title>
	</head>
	<body>
		Ahoj,<br /><br />
		jako přednášející/ho bychom Tě chtěli požádat o vyplnění anotace k Tvému bloku nejpozději do 18. 10.<br />
		Údaje můžeš doplnit a dále měnit a upravovat na adrese: <a href='http://vodni.skauting.cz/srazvs/annotation/edit/block/12345'>http://vodni.skauting.cz/srazvs/annotation/edit/block/12345</a>
		<br />
		<br />
		Popis, Tvé jméno a Tvůj e-mail se bude zobrazovat účastníkům srazu na webu, aby věděli, co je čeká. Můžeš změnit i název programu a stanov také maximální počet lidí, kteří se můžou programu zúčastnit.
		<br />
		<br />
		Pro případné otázky piš na <a href='mailto:katka.kaderova@seznam.cz'>katka.kaderova(at)seznam.cz</a>.
		<br />
		<br />
		Na setkání se těší přípravný tým srazů VS.
	</body>
</html>
----------%S%--
EOD
		, TestMailer::$output);
	}
}

$template = array(
	"subject" => "Sraz vodních skautů: anotace %%[typ-anotace]%%",
	"message" => "<html>
	<head>
		<title>Anotace %%[typ-anotace]%% na sraz VS</title>
	</head>
	<body>
		Ahoj,<br /><br />
		jako přednášející/ho bychom Tě chtěli požádat o vyplnění anotace k Tvému %%[typ-anotace]%% nejpozději do 18. 10.<br />
		Údaje můžeš doplnit a dále měnit a upravovat na adrese: <a href='%%[url-formulare]%%'>%%[url-formulare]%%</a>
		<br />
		<br />
		Popis, Tvé jméno a Tvůj e-mail se bude zobrazovat účastníkům srazu na webu, aby věděli, co je čeká. Můžeš změnit i název programu a stanov také maximální počet lidí, kteří se můžou programu zúčastnit.
		<br />
		<br />
		Pro případné otázky piš na <a href='mailto:katka.kaderova@seznam.cz'>katka.kaderova(at)seznam.cz</a>.
		<br />
		<br />
		Na setkání se těší přípravný tým srazů VS.
	</body>
</html>"
);

$mockedSettings = Mockery::mock(App\Models\SettingsModel::class);

$testMailer = new TestMailer();

$mockedEmailer = Mockery::mock('App\Services\Emailer[getTemplate]', array($mockedSettings, $testMailer));
$mockedEmailer->shouldReceive('getTemplate')->with('tutor')->andReturn($template);

$EmailerTutorTest = new EmailerTutorTest($mockedEmailer);
$EmailerTutorTest->run();
