<?php
/**
 * Container
 *
 * Default factory container for creating instaces of differend classes
 *
 * @created 2012-10-08
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class Container
{
	/** @var configuration */
	private $configuration;
	
	/** Constructor */
	public function __construct($configuration)
	{
		$this->configuration = $configuration;
	}
	
	/**
	 * Create instance of PHPMailer
	 *
	 * @return	PHPMailer
	 */
	public function createPHPMailer()
	{
		return new PHPMailer();
	}
	
	/**
	 * Create instance of PHPMailerFactory
	 *
	 * @param	PHPMailer
	 * @param	configuration settings
	 * @return	PHPMailerFactory
	 */
	public function createPHPMailerFactory()
	{
		return new PHPMailerFactory($this->createPHPMailer(), $this->configuration);
	}
	
	/**
	 * Create instance of Emailer
	 *
	 * @param	PHPMailerFactory
	 * @return	Emailer
	 */
	public function createEmailer()
	{
		return new Emailer($this->createPHPMailerFactory());
	}
}