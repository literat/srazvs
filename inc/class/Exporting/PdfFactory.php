<?php
/**
 * PdfFactory
 *
 * factory to create instance of mPDF
 *
 * @created 2013-02-18
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class PdfFactory
{
	/** @var Pdf */
	public $Pdf;
	
	/** @var configuration */
	private $configuration;
	
	private $encoding = 'utf-8';
	private $paperFormat = 'A4';
	private $fontSize = 0;
	private $font = '';
	private $marginLeft = 15;
	private $marginRight = 15;
	private $marginTop = 16;
	private $marginBottom = 16;
	
	/** Constructor */
	public function __construct()
	{
	}
	
	/**
	 * Return new Mpdf with few settings
	 *
	 * @return	Mpdf;
	 */
	public function create()
	{
		$this->Pdf = new mPdf($this->encoding,
							  $this->paperFormat,
							  $this->fontSize,
							  $this->font,
							  $this->marginLeft,
							  $this->marginRight,
							  $this->marginTop,
							  $this->marginBottom
							 );
		
		// debugging on demand
		if(defined('DEBUG') && DEBUG === TRUE){
			$this->Pdf->debug = true;
		}
		$this->Pdf->useOnlyCoreFonts = true;
		$this->Pdf->SetDisplayMode('fullpage');
		$this->Pdf->SetAutoFont(0);
		$this->Pdf->defaultfooterfontsize = 16;
		$this->Pdf->defaultfooterfontstyle = 'B';
		
		return $this->Pdf;
	}
	
	/**
	 * Set margins of PDF
	 */
	public function setMargins($left, $right, $top, $bottom)
	{
		$this->marginLeft = $left;
		$this->marginRight = $right;
		$this->marginTop = $top;
		$this->marginBottom = $bottom;
	}
}