<?php

namespace App\Factories;

/**
 * ExcelFactory
 *
 * factory to create instance of PHPExcel
 *
 * @created 2016-07-07
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class ExcelFactory
{
	/** @var Excel */
	private $excel;
	private $creator = 'Junák - český skaut, Kapitanát vodních skautů, z. s.';
	private $lastModifiedBy = 'Srazy VS';
	private $title = 'Srazy VS: Export';
	private $subject = 'Export';
	private $description = 'Srazy VS CMS: export dat';
	private $keywords = 'sraz vs export xlsx';
	private $category = 'Export dat';

	/** Constructor */
	public function __construct(array $configuration)
	{
		//var_dump($configuration);
		//exit;
		$this->creator 			= $configuration['creator'];
		$this->lastModifiedBy 	= $configuration['lastModifiedBy'];
		$this->title 			= $configuration['title'];
		$this->subject 			= $configuration['subject'];
		$this->description 		= $configuration['description'];
		$this->keywords 		= $configuration['keywords'];
		$this->category 		= $configuration['category'];
	}

	/**
	 * Return new PHPExcel with few settings
	 *
	 * @return	PHPExcel;
	 */
	public function create()
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
