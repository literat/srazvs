<?php
/**
 * Emailer
 *
 * Class fro hadling and sending e-mails
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
		
		$this->PHPMailerFactory = $PHPMailerFactory;
	}
	
	/**
	 * Create new PHPMailer for sending e-mails
	 *
	 * @return	PHPMailer
	 */
	public function createPHPMailer()
	{
		return $this->PHPMailerFactory->create();	
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
	public function sendMail($recipient_mail, $recipient_name, $subject, $message)
	{	
		// create PHPMailer
		$this->Emailer = $this->createPHPMailer();
		// add recipient address and name
		$this->Emailer->AddAddress($recipient_mail, $recipient_name);
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
		
		$setQuery = "SELECT * FROM kk_settings WHERE name = 'mail_tutor'";
		$setResult = mysql_query($setQuery);
		$setData = mysql_fetch_assoc($setResult);

		$json = json_decode($setData['value']);

		$subject = html_entity_decode($json->subject);
		$message = html_entity_decode($json->message);
		
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
		$query = "SELECT * FROM kk_settings WHERE name = 'mail_post_reg'";
		$result = mysql_query($query);
		$data = mysql_fetch_assoc($result);

		$json = json_decode($data['value']);

		$subject = html_entity_decode($json->subject);
		$message = html_entity_decode($json->message);
		
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
	function noticeVisitor($id, $subject, $message)
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
}