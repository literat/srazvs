<?php

namespace App\Factories;

use Mpdf\Mpdf;

class PdfFactory
{
	/** @var Mpdf */
	public $pdf;

	/** @var string */
	private $encoding = 'utf-8';

	/** @var string  */
	private $paperFormat = 'A4';

	/** @var int  */
	private $fontSize = 0;

	/** @var mixed  */
	private $font = '';

	/** @var int  */
	private $marginLeft = 15;

	/** @var int  */
	private $marginRight = 15;

	/** @var int  */
	private $marginTop = 16;

	/** @var int  */
	private $marginBottom = 16;

	/** @var bool */
	private $debugMode = false;

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
	 * Return new Mpdf with few settings.
	 *
	 * @return Mpdf;
	 */
	public function create()
	{
		$this->pdf = new Mpdf(
			[
				$this->encoding,
				$this->paperFormat,
				$this->fontSize,
				$this->font,
				$this->marginLeft,
				$this->marginRight,
				$this->marginTop,
				$this->marginBottom
			]
		);

		// debugging on demand
		if ($this->debugMode) {
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
	 * Set margins of PDF.
	 */
	public function setMargins($left, $right, $top, $bottom)
	{
		$this->marginLeft 	= $left;
		$this->marginRight 	= $right;
		$this->marginTop 	= $top;
		$this->marginBottom = $bottom;
	}

	/**
	 * Set paper format of PDF.
	 */
	public function setPaperFormat($paperFormat)
	{
		$this->paperFormat = $paperFormat;
	}
}
