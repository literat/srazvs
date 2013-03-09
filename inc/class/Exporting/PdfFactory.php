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
	
	/** Constructor */
	public function __construct(mPDF $Mpdf, $configuration)
	{
		$this->Pdf = $Mpdf;
		$this->configuration = $configuration;
	}
	
	/**
	 * Return new Mpdf with few settings
	 *
	 * @return	Mpdf;
	 */
	public function create()
	{
		// debugging on demand
		if(defined('DEBUG') && DEBUG === TRUE){
			$this->Pdf->debug = true;
		}
		$this->Pdf->useOnlyCoreFonts = true;
		$this->Pdf->SetDisplayMode('fullpage');
		$this->Pdf->SetAutoFont(0);
		$this->Pdf->defaultfooterfontsize = 16;
		$this->Pdf->defaultfooterfontstyle = 'B';
		$this->Pdf->mgl = 15;
		
		return $this->Pdf;
	}
}