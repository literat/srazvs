<?php

namespace App\Services;

use App\Models\SettingsModel;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Tracy\Debugger;

class Emailer
{
	/**
	 * @var IMailer
	 */
	private $mailer;

	/**
	 * @var SettingsModel
	 */
	private $settings;

	public function __construct(SettingsModel $settings, IMailer $mailer)
	{
		$this->mailer = $mailer;
		$this->settings = $settings;
	}

	/**
	 * Sends an e-mail to recipient.
	 */
	public function sendMail($recipients, string $subject, string $body, array $bccMail = null): bool
	{
		$message = new Message;
		$message->setFrom('srazyvs@hkvs.cz', 'Srazy VS');

		foreach ($recipients as $recipient) {
			if (array_key_exists('surname', $recipient)) {
				$name = trim($recipient->name . ' ' . $recipient->surname);
			} else {
				$name = $recipient->name;
			}

			$message->addTo(
				$recipient->email,
				$name
			);
		}
		// add bcc
		if (!empty($bccMail)) {
			foreach ($bccMail as $bccMail => $bccName) {
				$message->addBcc($bccMail, $bccName);
			}
		}
		// create subject
		$message->subject = $subject;
		// create HTML body
		$message->htmlBody = $body;
		// create alternative message without HTML tags
		$message->body = strip_tags($body);
		// sending e-mail or error status
		try {
			$this->mailer->send($message);
			return true;
		} catch (\Exception $e) {
			Debugger::log($e, 'error');
			return false;
		}
	}

	/**
	 * Get e-mail template from settings.
	 *
	 * @param  string type of template
	 * @return array subject and message
	 */
	public function getTemplate(string $type): array
	{
		$json = $this->settings->getMailJson($type);

		$subject = html_entity_decode($json->subject);
		$message = html_entity_decode($json->message);

		return [
			'subject' => $subject,
			'message' => $message,
		];
	}

	/**
	 * Sends an e-mail to lecture master.
	 *
	 * @param  array  recipient mail and name
	 * @param  string guid
	 * @param  string program | block
	 * @return mixed true | error information
	 */
	public function tutor($recipients, string $guid, string $type)
	{
		$lang['block']['cs'] = "bloku";
		$lang['program']['cs'] = "programu";

		$tutorFormUrl = PRJ_DIR . "annotation/edit/{$type}/{$guid}";

		// e-mail templates
		$template = $this->getTemplate('tutor');
		$subject = $template['subject'];
		$message = $template['message'];

		// replacing text variables
		$subject = preg_replace('/%%\[typ-anotace\]%%/', $lang[$type]['cs'], $subject);
		$message = preg_replace('/%%\[typ-anotace\]%%/', $lang[$type]['cs'], $message);
		$message = preg_replace('/%%\[url-formulare\]%%/', $tutorFormUrl, $message);

		// send it
		return $this->sendMail($recipients, $subject, $message);
	}

	/**
	 * Sends an after registration summary e-mail to visitor.
	 *
	 * @param  array  recipient mail
	 * @param  int    check hash code
	 * @param  string code for recognition of bank transaction
	 * @return mixed true | error information
	 */
	public function sendRegistrationSummary(array $recipientMail, int $hash, string $code4bank)
	{
		// e-mail templates
		$template = $this->getTemplate('post_reg');
		$subject = $template['subject'];
		$message = $template['message'];

		// replacing text variables
		$message = preg_replace('/%%\[kontrolni-hash\]%%/', $hash, $message);
		$message = preg_replace('/%%\[variabilni-symbol\]%%/', $code4bank, $message);

		// send it
		return $this->sendMail($recipientMail, $subject, $message);
	}

	/**
	 * Get e-mail templates from settings.
	 *
	 * @param  mixed  id numbers in row
	 * @param  string type of template
	 * @return array subject and message
	 */
	public function sendPaymentInfo($recipients, string $type)
	{
		// e-mail templates
		$template = $this->getTemplate($type);
		$subject = $template['subject'];
		$message = $template['message'];

		return $this->sendMail($recipients, $subject, $message);
	}
}
