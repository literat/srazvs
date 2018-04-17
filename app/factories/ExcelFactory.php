<?php

namespace App\Factories;

use PHPExcel;

class ExcelFactory
{
	/** @var \PHPExcel */
	private $excel;

	/**
	 * @var string
	 */
	private $creator = 'Junák - český skaut, Kapitanát vodních skautů, z. s.';

	/**
	 * @var string
	 */
	private $lastModifiedBy = 'Srazy VS';

	/**
	 * @var string
	 */
	private $title = 'Srazy VS: Export';

	/**
	 * @var string
	 */
	private $subject = 'Export';

	/**
	 * @var string
	 */
	private $description = 'Srazy VS CMS: export dat';

	/**
	 * @var string
	 */
	private $keywords = 'sraz vs export xlsx';

	/**
	 * @var string
	 */
	private $category = 'Export dat';

	public function __construct(array $configuration)
	{
		$this->creator 			= $configuration['creator'];
		$this->lastModifiedBy 	= $configuration['lastModifiedBy'];
		$this->title 			= $configuration['title'];
		$this->subject 			= $configuration['subject'];
		$this->description 		= $configuration['description'];
		$this->keywords 		= $configuration['keywords'];
		$this->category 		= $configuration['category'];
	}

	/**
	 * Return new PHPExcel with few settings.
	 */
	public function create(): PHPExcel
	{
		$this->excel = new \PHPExcel();

		$this->excel->getProperties()->setCreator($this->creator)
			->setLastModifiedBy($this->lastModifiedBy)
			->setTitle($this->title)
			->setSubject($this->subject)
			->setDescription($this->description)
			->setKeywords($this->keywords)
			->setCategory($this->category);

		return $this->excel;
	}
}
