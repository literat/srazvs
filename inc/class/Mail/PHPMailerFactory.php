<?php
/**
 * PHPMailerFactory
 *
 * factory to create instance of PHPMAiler
 *
 * @created 2012-10-08
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class PHPMailerFactory
{
	/** @var PHPMailer */
	public $PHPMailer;
	
	/** @var configuration */
	private $configuration;
	
	/** Constructor */
	public function __construct(PHPMailer $PHPMailer, $configuration)
	{
		$this->PHPMailer = $PHPMailer;
		$this->configuration = $configuration;
	}
	
	/**
	 * Return new PHPMailer with few settings
	 *
	 * @return	PHPMailer;
	 */
	public function create()
	{
		// use PHP function mail()
		$this->PHPMailer->IsMail();
		// e-mail is in HTML format
		$this->PHPMailer->IsHTML(true);
		// encoding
		$this->PHPMailer->CharSet = $this->configuration['mail-encoding'];
		// language
		$this->PHPMailer->SetLanguage($this->configuration['cfg']['mail-language']);
		// sender e-mail address
		$this->PHPMailer->From = $this->configuration['cfg']['mail-sender-address'];
		// sender name
		$this->PHPMailer->FromName = $this->configuration['cfg']['mail-sender-name'];
		
		return $this->PHPMailer;
	}
}