<?php

/**
 * Test: App\Emailer sendMail.
 */

use Mockery\MockInterface;
use Nette\Mail\Message;
use Tester\Assert;
use App\Services\Emailer;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestMailer.php';
require_once __DIR__ . '/../../../app/services/Emailer.php';

class EmailerModelTest extends Tester\TestCase
{

	private $mailer = null;

	public function __construct($mailer)
	{
		$this->mailer = $mailer;
	}

	public function testSendedSuccessfully()
	{
		$recipient = array(
			'prilis.zlutoucky@kun.cz' => 'Příliš žluťoučký kůň',
		);
		$subject = 'Úpěl ďábelské ódy';
		$body = 'Testování';

		$return =  $this->mailer->sendMail($recipient, $subject, $body);

		Assert::true($return);
	}

/*
	public function testSendingFailed()
	{
		$recipient = array(
			'' => 'Příliš žluťoučký kůň',
		);
		$subject = 'Úpěl ďábelské ódy';
		$body = 'Testování';

		$return =  $this->mailer->sendMail($recipient, $subject, $body);

		Assert::false($return);
	}
*/

	public function testSendingMail()
	{
		$recipient = array(
			'prilis.zlutoucky@kun.cz' => 'Příliš žluťoučký kůň',
		);
		$subject = 'Úpěl ďábelské ódy';
		$body = 'Testování';

		$this->mailer->sendMail($recipient, $subject, $body);

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

	public function testSendingMailWithBcc()
	{
		$recipient = array(
			'prilis.zlutoucky@kun.cz' => 'Příliš žluťoučký kůň',
		);
		$subject = 'Úpěl ďábelské ódy';
		$body = 'Testování';
		$bcc = array(
			'info@example.com' => 'Info at Example',
		);

		$this->mailer->sendMail($recipient, $subject, $body, $bcc);

		Assert::match(<<<'EOD'
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: Srazy VS <srazyvs@hkvs.cz>
To: =?UTF-8?B?UMWZw61sacWhIMW+bHXFpW91xI1rw70ga8WvxYg=?=
	 <prilis.zlutoucky@kun.cz>
Bcc: Info at Example <info@example.com>
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

	public function testSendingMultipleMailAddresses()
	{
		$recipient = array(
			'prilis.zlutoucky@kun.cz' => 'Příliš žluťoučký kůň',
			'john@doe.com' => 'John Doe',
			'info@example.com' => 'Info at Example',
		);
		$subject = 'Úpěl ďábelské ódy';
		$body = 'Testování';
		$bcc = array(
			'info@example.com' => 'Info at Example',
		);

		$this->mailer->sendMail($recipient, $subject, $body);

		Assert::match(<<<'EOD'
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: Srazy VS <srazyvs@hkvs.cz>
To: =?UTF-8?B?UMWZw61sacWhIMW+bHXFpW91xI1rw70ga8WvxYg=?=
	 <prilis.zlutoucky@kun.cz>,John Doe <john@doe.com>,Info at Example
	 <info@example.com>
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

	public function testSendingMultipleBcc()
	{
		$recipient = array(
			'prilis.zlutoucky@kun.cz' => 'Příliš žluťoučký kůň',
		);
		$subject = 'Úpěl ďábelské ódy';
		$body = 'Testování';
		$bcc = array(
			'info@example.com' => 'Info at Example',
			'john@doe.com' => 'John Doe',
			'Test@example.com' => 'Test at Example',
		);

		$this->mailer->sendMail($recipient, $subject, $body, $bcc);

		Assert::match(<<<'EOD'
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: Srazy VS <srazyvs@hkvs.cz>
To: =?UTF-8?B?UMWZw61sacWhIMW+bHXFpW91xI1rw70ga8WvxYg=?=
	 <prilis.zlutoucky@kun.cz>
Bcc: Info at Example <info@example.com>,John Doe <john@doe.com>,
	Test at Example <Test@example.com>
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

}

$mockedSettings = Mockery::mock(App\Models\SettingsModel::class);
$testMailer = new TestMailer();
$emailer = new Emailer($mockedSettings, $testMailer);

$EmailerModelTest = new EmailerModelTest($emailer);
$EmailerModelTest->run();
