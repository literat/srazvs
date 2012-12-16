<?php
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
	
	/** @var EmailerFactory */
	private $PHPMailerFactory;
	
	/** @var Emailer */
	private $Emailer;
	
	/* Constructor */
	public function __construct(PHPMailerFactory $PHPMailerFactory)
	{
		// jestli jsem na vyvojove masine - true/false
		$this->isDev = $GLOBALS['ISDEV'];
		// use PHPMailerFactory
		$this->PHPMailerFactory = $PHPMailerFactory;
		// use PHPMailer
		$this->Emailer = $this->PHPMailerFactory->PHPMailer;
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
	public function sendMail($recipient_mail, $recipient_name, $subject, $message, $bcc_mail = NULL)
	{	
		// add recipient address and name
		$this->Emailer->AddAddress($recipient_mail, $recipient_name);
		// add bcc
		if(isset($bcc_mail) && $bcc_mail == '') {
			$this->Emailer->AddBCC($bcc_mail);
		}
		// create subject
		$this->Emailer->Subject = $subject;
		// create HTML body
		$this->Emailer->Body = $message;
		// create alternative message without HTML tags
		$this->Emailer->AltBody = strip_tags($message);
		// e-mail word wrapping
		$this->Emailer->WordWrap = 50;
		// sending e-mail or error status
		if(!$this->Emailer->Send()) {
			return $this->Emailer->ErrorInfo;
		} else {
			return true;
		}
	}

	/**
	 * Get e-mail templates from settings
	 *
	 * @param	string	type of template
	 * @return	array	subject and message
	 */	
	public function getTemplates($type)
	{
		$query = "SELECT * FROM kk_settings WHERE name = 'mail_".$type."'";
		$result = mysql_query($query);
		$data = mysql_fetch_assoc($result);

		$json = json_decode($data['value']);

		$subject = html_entity_decode($json->subject);
		$message = html_entity_decode($json->message);
		
		return array('subject' => $subject, 'message' => $message);
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
	
		$query = "SELECT	* FROM kk_".$type."s AS content
				WHERE id='".$contentId."' AND deleted='0'
				LIMIT 1";
		$result = mysql_query($query);
		$data = mysql_fetch_assoc($result);
	
		// kontroluju, jestli je zadan e-mail
		if($data['email'] == ""){
			redirect("process.php?id=".$contentId."&error=email&cms=edit");
			die();
		}	
	
		// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
		$hash = ((int)$contentId.$meetingId) * 116 + 39147;	
		
		// multiple recipients
		$recipient_mail = $data['email'];
		$recipient_name = $data['tutor'];
		$tutor_form_url = "http://vodni.skauting.cz/srazvs/registrace/setcontent.php?type=".$type."&formkey=".$hash;
		
		// e-mail templates
		$templates = $this->getTemplates('tutor');
		$subject = $templates['subject'];
		$message = $templates['message'];
		
		// replacing text variables
		$subject = preg_replace('/%%\[typ-anotace\]%%/', $lang[$type]['cs'], $subject);
		$message = preg_replace('/%%\[typ-anotace\]%%/', $lang[$type]['cs'], $message);
		$message = preg_replace('/%%\[url-formulare\]%%/', $tutor_form_url, $message);
		
		// send it
		return $this->sendMail($recipient_mail, $recipient_name, $subject, $message);
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