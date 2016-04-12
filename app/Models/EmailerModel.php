<?php

namespace App;

use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Database\Context;
use Nette\Utils\Json;
use Tracy\Debugger;

/**
 * Emailer
 *
 * Class for hadling and sending e-mails
 *
 * @created 2011-09-16
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class Emailer
{
	/** @var SmtpMailer */
	private $mailer;

	/** @var Context */
	private $database;

	/* Constructor */
	public function __construct(Context $database, IMailer $mailer)
	{
		$this->mailer = $mailer;
		$this->database = $database;
	}

	/**
	 * Send an e-mail to recipient
	 *
	 * @param	array	recipient e-mail and name
	 * @param	string	subject
	 * @param	string	message
	 * @param	array	bcc
	 * @return	bool	true | false (log the exception)
	 */
	public function sendMail(array $recipient, $subject, $body, array $bccMail = NULL)
	{
		$message = new Message;
		$message->setFrom('srazyvs@hkvs.cz', 'Srazy VS');

		foreach($recipient as $mail => $name) {
			// add recipient address and name
			$message->addTo($mail, $name);
		}
		// add bcc
		if(!empty($bccMail)) {
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
		} catch(Exception $e) {
			Debugger::log($e, 'error');
			return false;
		}
	}

	/**
	 * Get e-mail template from settings
	 *
	 * @param	string	type of template
	 * @return	array	subject and message
	 */
	public function getTemplate($type)
	{
		$data = $this->database
			->table('kk_settings')
			->where('name', 'mail_' . $type)
			->fetch();

		$json = Json::decode($data->value);

		$subject = html_entity_decode($json->subject);
		$message = html_entity_decode($json->message);

		return array(
			'subject' => $subject,
			'message' => $message,
		);
	}

	/**
	 * Sends an e-mail to lecture master
	 *
	 * @param	int		ID of program/block
	 * @param	int		ID of meeting
	 * @param	string	program | block
	 * @return	mixed	true | error information
	 */
	public function tutor(array $recipients, $hash, $type)
	{
		$lang['block']['cs'] = "bloku";
		$lang['program']['cs'] = "programu";

		$tutorFormUrl = PRJ_DIR . $type . "/?cms=annotation&type=" . $type . "&formkey=" . $hash;

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
	 * Sends an after registration summary e-mail to visitor
	 *
	 * @param	string	recipient mail
	 * @param	string	recipient name
	 * @param	int		check hash code
	 * @param	string	code for recognition of bank transaction
	 * @return	mixed	true | error information
	 */
	public function sendRegistrationSummary($recipientMail, $hash, $code4bank)
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
	 * Get e-mail templates from settings
	 *
	 * @param	mixed	id numbers in row
	 * @param	string	type of template
	 * @return	array	subject and message
	 */
	public function sendPaymentInfo($recipients, $type)
	{
		// e-mail templates
		$template = $this->getTemplate($type);
		$subject = $template['subject'];
		$message = $template['message'];

		return $this->sendMail($recipients, $subject, $message);
	}
}