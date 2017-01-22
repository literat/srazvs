<?php

namespace App\Factories;

/**
 * PdfFactory
 *
 * factory to create instance of Mpdf
 *
 * @created 2016-07-07
 * @author Tomas Litera <tomas@litera.me>
 */
class PdfFactory
{
	/** @var Pdf */
	public $pdf;

	/** @var defaults */
	private $encoding = 'utf-8';
	private $paperFormat = 'A4';
	private $fontSize = 0;
	private $font = '';
	private $marginLeft = 15;
	private $marginRight = 15;
	private $marginTop = 16;
	private $marginBottom = 16;
	private $debugMode = false;

	/** Constructor */
	public function __construct(array $configuration)
	{
		$this->encoding 	= $configuration['encoding'];
		$this->paperFormat 	= $configuration['paperFormat'];
		$this->fontSize 	= $configuration['fontSize'];
		$this->font 		= $configuration['font'];
		$this->marginLeft 	= $configuration['marginLeft'];
		$this->marginRight 	= $configuration['marginRight'];
		$this->marginTop 	= $configuration['marginTop'];
		$this->marginBottom = $configuration['marginBottom'];
		$this->debugMode	= $configuration['debugMode'];
	}

	/**
	 * Return new Mpdf with few settings
	 *
	 * @return	Mpdf;
	 */
	public function create()
	{
		$this->pdf = new \mPDF(
			$this->encoding,
			$this->paperFormat,
			$this->fontSize,
			$this->font,
			$this->marginLeft,
			$this->marginRight,
			$this->marginTop,
			$this->marginBottom
		);

		// debugging on demand
		if($this->debugMode){
			$this->pdf->debug = true;
		}
		$this->pdf->useOnlyCoreFonts = true;
		$this->pdf->SetDisplayMode('fullpage');
		$this->pdf->autoScriptToLang = false;
		$this->pdf->defaultfooterfontsize = 16;
		$this->pdf->defaultfooterfontstyle = 'B';

		return $this->pdf;
	}

	/**
	 * Set margins of PDF
	 */
	public function setMargins($left, $right, $top, $bottom)
	{
		$this->marginLeft 	= $left;
		$this->marginRight 	= $right;
		$this->marginTop 	= $top;
		$this->marginBottom = $bottom;
	}

	/**
	 * Set paper format of PDF
	 */
	public function setPaperFormat($paperFormat)
	{
		$this->paperFormat = $paperFormat;
	}
}
