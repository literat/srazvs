<?php

namespace App;

use Nette\Mail\SmtpMailer;
use Nette\Mail\Message;
use Nette\Database\Context;
use Nette\Utils\Json;

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
	/* jsem na vyvoji nebo na produkci? */
	protected $isDev;

	/** @var SmtpMailer */
	private $mailer;

	/** @var Context */
	private $database;

	/* Constructor */
	public function __construct(Context $database, SmtpMailer $mailer)
	{
		// jestli jsem na vyvojove masine - true/false
		$this->isDev = $GLOBALS['ISDEV'];
		$this->mailer = $mailer;
		$this->database = $database;
	}

	/**
	 * Send an e-mail to recipient
	 *
	 * @param	string	recipient e-mail
	 * @param	string	recipient name
	 * @param	string	subject
	 * @param	string	message
	 * @return	mixed	true or error information
	 */
	public function sendMail($recipientMail, $recipientName, $subject, $body, $bccMail = NULL)
	{
		//$this->Emailer->clearAddresses();
		//$this->Emailer->clearAllRecipients();
		$message = new Message;
		$message->setFrom('Srazy VS <srazyvs@hkvs.cz>');

		$recipientMail = explode(',', $recipientMail);

		foreach($recipientMail as $mail) {
			// add recipient address and name
			$message->addTo($mail);
		}
		// add bcc
		if(isset($bccMail) && $bccMail != '') {
			$message->addBcc($bccMail);
		}
		// create subject
		$message->subject = $subject;
		// create HTML body
		$message->htmlBody = $body;
		// create alternative message without HTML tags
		$message->body = strip_tags($body);
		// e-mail word wrapping
		//$this->Emailer->WordWrap = 50;
		// sending e-mail or error status
		try {
			$this->mailer->send($message);
			return true;
		} catch(Exception $e) {
			return $e;
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
	public function tutor($contentId, $meetingId, $type)
	{
		$lang['block']['cs'] = "bloku";
		$lang['program']['cs'] = "programu";

		$data = $this->database
			->table('kk_' . $type . 's AS content')
			->where('id ? AND deleted ?', $contentId, 0)
			->limit(1)
			->fetch();

		// kontroluju, jestli je zadan e-mail
		if(empty($data['email'])){
			redirect("process.php?id=".$contentId."&error=email&cms=edit");
			die();
		}

		// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
		$hash = ((int)$contentId . $meetingId) * 116 + 39147;

		// multiple recipients
		$recipientMail = $data->email;
		$recipientName = $data->tutor;
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
		return $this->sendMail($recipientMail, $recipientName, $subject, $message);
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
	public function sendRegistrationSummary($recipient_mail, $recipient_name, $hash, $code4bank)
	{
		// e-mail templates
		$templates = $this->getTemplates('post_reg');
		$subject = $templates['subject'];
		$message = $templates['message'];

		// replacing text variables
		$message = preg_replace('/%%\[kontrolni-hash\]%%/', $hash, $message);
		$message = preg_replace('/%%\[variabilni-symbol\]%%/', $code4bank, $message);

		// send it
		return $this->sendMail($recipient_mail, $recipient_name, $subject, $message);
	}

	/**
	 * Send an information mail to multiple visitors
	 *
	 * @param	integer	ID of visitor
	 * @param	string	subject
	 * @param	string	message
	 * @return	mixed	true | error information
	 */
	public function noticeVisitor($id, $subject, $message)
	{
		// multiple IDs, multiple visitors
		if(is_array($id)){
			$query_id = NULL;
			foreach($id as $key => $value) {
				$query_id .= $value.',';
			}
			$query_id = rtrim($query_id, ',');
		} else {
			$query_id = $id;
		}

		$sql = "SELECT	* FROM kk_visitors AS vis
				WHERE id IN (".$query_id.") AND deleted='0'";
		$result = mysql_query($sql);
		$recipient = NULL;
		while($data = mysql_fetch_assoc($result)) {
			// multiple recipients
			$recipient_mail = $data['email']; // note the comma
			$recipient_name = $data['name']." ".$data['surname'];

			// send it
			if(!$this->sendMail($recipient_mail, $recipient_name, $subject, $message)) {
				$return = $EmailHandler->ErrorInfo;
			} else {
				$return = true;
			}
		}

		return $return;
	}

	/**
	 * Get e-mail templates from settings
	 *
	 * @param	mixed	id numbers in row
	 * @param	string	type of template
	 * @return	array	subject and message
	 */
	public function sendPaymentInfo($query_id, $type)
	{
		// e-mail templates
		$templates = $this->getTemplates($type);
		$subject = $templates['subject'];
		$message = $templates['message'];

		$return = $this->noticeVisitor($query_id, $subject, $message);

		return $return;
	}
}
