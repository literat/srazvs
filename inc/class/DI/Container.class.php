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
	
	/** @var meeting ID */
	private $meetingId;
	
	/** Constructor */
	public function __construct($configuration, $meetingId = NULL)
	{
		$this->configuration = $configuration;
		$this->meetingId = $meetingId;
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
	 * @return	PHPMailerFactory
	 */
	public function createPHPMailerFactory()
	{
		return new PHPMailerFactory($this->createPHPMailer(), $this->configuration);
	}
	
	/**
	 * Create instance of Emailer
	 *
	 * @return	Emailer
	 */
	public function createEmailer()
	{
		return new Emailer($this->createPHPMailerFactory());
	}
	
	/**
	 * Create instance of Visitor
	 *
	 * @return	Visitor
	 */
	public function createVisitor()
	{
		return new Visitor(
			$this->meetingId,
			$this->createEmailer(),
			$this->createMeeting(),
			$this->createMeal(),
			$this->createProgram(),
			$this->createBlock()
		);
	}
	
	/**
	 * Create instance of Meeting
	 *
	 * @return	Meeting
	 */
	public function createMeeting()
	{
		return new Meeting($this->meetingId);
	}
	
	/**
	 * Create instance of Program
	 *
	 * @return	Program
	 */
	public function createProgram()
	{
		return new Program($this->meetingId);
	}
	
	/**
	 * Create instance of Meal
	 *
	 * @return	Meal
	 */
	public function createMeal()
	{
		return new Meal($this->meetingId);
	}
	
	/**
	 * Create instance of Block
	 *
	 * @return	Block
	 */
	public function createBlock()
	{
		return new Block($this->meetingId);
	}
}