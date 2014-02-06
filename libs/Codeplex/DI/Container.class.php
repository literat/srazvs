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
	 * Create instance of PdfFactory
	 *
	 * @return	PdfFactory
	 */
	public function createPdfFactory()
	{
		return new PdfFactory();
	}
	
	/**
	 * Create instance of ExcelFactory
	 *
	 * @return	ExcelFactory
	 */
	public function createExcelFactory()
	{
		return new ExcelFactory();
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
	 * Create instance of View
	 *
	 * @return	View
	 */
	public function createView()
	{
		return new View();
	}
	
	/**
	 * Create instance of Visitor
	 *
	 * @return	Visitor
	 */
	public function createVisitor()
	{
		return new VisitorModel(
			$this->meetingId,
			$this->createEmailer(),
			$this->createMeeting(),
			$this->createMeal(),
			$this->createProgram(),
			$this->createBlock(),
			$this->configuration
		);
	}
	
	/**
	 * Create instance of Meeting
	 *
	 * @return	Meeting
	 */
	public function createMeeting()
	{
		return new MeetingModel($this->meetingId, $this->configuration);
	}
	
	/**
	 * Create instance of Program
	 *
	 * @return	Program
	 */
	public function createProgram()
	{
		return new ProgramModel($this->meetingId, $this->configuration);
	}
	
	/**
	 * Create instance of Meal
	 *
	 * @return	Meal
	 */
	public function createMeal()
	{
		return new MealModel($this->meetingId);
	}
	
	/**
	 * Create instance of Block
	 *
	 * @return	Block
	 */
	public function createBlock()
	{
		return new BlockModel($this->meetingId);
	}

	/**
	 * Create instance of Category
	 *
	 * @return	CategoryModel
	 */
	public function createCategory()
	{
		return new CategoryModel();
	}
	
	/**
	 * Create instance of Export
	 *
	 * @return	Export
	 */
	public function createExport()
	{
		return new ExportModel($this->meetingId, $this->createPdfFactory(), $this->createView(), $this->createProgram(), $this->createExcelFactory());
	}

	/**
	 * Create instance of Settings
	 *
	 * @return	SettingsModel
	 */
	public function createSettings()
	{
		return new SettingsModel();
	}
}