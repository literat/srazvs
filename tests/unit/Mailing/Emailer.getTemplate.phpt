<?php

/**
 * Test: App\Emailer getTemplate.
 */

use Mockery\MockInterface;
use Tester\Assert;
use App\Services\Emailer;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestMailer.php';
require_once __DIR__ . '/../../../app/services/Emailer.php';

class EmailerGetTemplateTest extends Tester\TestCase
{

	private $mailer = null;

	public function __construct($mailer)
	{
		$this->mailer = $mailer;
	}

	public function testGetTutorTemplate()
	{
		Assert::same(
			$this->mailer->getTemplate('tutor'),
			[
				'subject' => 'Sraz vodních skautů: anotace %%[typ-anotace]%%',
				'message' => '<html>
  <head>
	 <title>Anotace %%[typ-anotace]%% na sraz VS</title>
  </head>
  <body>
	 Ahoj,<br /><br />
	 jako přednášející/ho bychom Tě chtěli požádat o vyplnění anotace k Tvému %%[typ-anotace]%% nejpozději do 18. 10.<br />
	 Údaje můžeš doplnit a dále měnit a upravovat na adrese: <a href="%%[url-formulare]%%">%%[url-formulare]%%</a>
	 <br />
	 <br />
	 Popis, Tvé jméno a Tvůj e-mail se bude zobrazovat účastníkům srazu na webu, aby věděli, co je čeká. Můžeš změnit i název programu a stanov také maximální počet lidí, kteří se můžou programu zúčastnit.
	 <br />
	 <br />
	 Pro případné otázky piš na <a href="mailto:katka.kaderova@seznam.cz">katka.kaderova(at)seznam.cz</a>.
	 <br />
	 <br />
	 Na setkání se těší přípravný tým srazů VS.
  </body>
</html>',
]);
	}
}

$json = json_decode('{"subject":"Sraz vodn\u00edch skaut\u016f: anotace %%[typ-anotace]%%","message":"&lt;html&gt;\n  &lt;head&gt;\n\t &lt;title&gt;Anotace %%[typ-anotace]%% na sraz VS&lt;\/title&gt;\n  &lt;\/head&gt;\n  &lt;body&gt;\n\t Ahoj,&lt;br \/&gt;&lt;br \/&gt;\n\t jako p\u0159edn\u00e1\u0161ej\u00edc\u00ed\/ho bychom T\u011b cht\u011bli po\u017e\u00e1dat o vypln\u011bn\u00ed anotace k Tv\u00e9mu %%[typ-anotace]%% nejpozd\u011bji do 18. 10.&lt;br \/&gt;\n\t \u00dadaje m\u016f\u017ee\u0161 doplnit a d\u00e1le m\u011bnit a upravovat na adrese: &lt;a href=\"%%[url-formulare]%%\"&gt;%%[url-formulare]%%&lt;\/a&gt;\n\t &lt;br \/&gt;\n\t &lt;br \/&gt;\n\t Popis, Tv\u00e9 jm\u00e9no a Tv\u016fj e-mail se bude zobrazovat \u00fa\u010dastn\u00edk\u016fm srazu na webu, aby v\u011bd\u011bli, co je \u010dek\u00e1. M\u016f\u017ee\u0161 zm\u011bnit i n\u00e1zev programu a stanov tak\u00e9 maxim\u00e1ln\u00ed po\u010det lid\u00ed, kte\u0159\u00ed se m\u016f\u017eou programu z\u00fa\u010dastnit.\n\t &lt;br \/&gt;\n\t &lt;br \/&gt;\n\t Pro p\u0159\u00edpadn\u00e9 ot\u00e1zky pi\u0161 na &lt;a href=\"mailto:katka.kaderova@seznam.cz\"&gt;katka.kaderova(at)seznam.cz&lt;\/a&gt;.\n\t &lt;br \/&gt;\n\t &lt;br \/&gt;\n\t Na setk\u00e1n\u00ed se t\u011b\u0161\u00ed p\u0159\u00edpravn\u00fd t\u00fdm sraz\u016f VS.\n  &lt;\/body&gt;\n&lt;\/html&gt;"}');

$mockedSettings = Mockery::mock('App\Models\SettingsModel');
$mockedSettings->shouldReceive('getMailJson')->with('tutor')->andReturn($json);

$testMailer = new TestMailer();
$emailer = new Emailer($mockedSettings, $testMailer);

$EmailerGetTemplateTest = new EmailerGetTemplateTest($emailer);
$EmailerGetTemplateTest->run();
