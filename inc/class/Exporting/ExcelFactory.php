<?php
/**
 * ExcelFactory
 *
 * factory to create instance of PHPExcel
 *
 * @created 2013-03-10
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class ExcelFactory
{
	/** @var Excel */
	public $Excel;
	
	/** Constructor */
	public function __construct()
	{
	}
	
	/**
	 * Return new PHPExcel with few settings
	 *
	 * @return	PHPExcel;
	 */
	public function create()
	{
		$this->Excel = new PHPExcel();
		
		$this->Excel->getProperties()->setCreator("Junák ČR - Hlavní kapitanát vodních skautů")
							 	->setLastModifiedBy("Srazy VS")
							 	->setTitle("Srazy VS: Export")
							 	->setSubject("Export")
							 	->setDescription("Srazy VS CMS: export dat")
							 	->setKeywords("sraz vs export xlsx")
							 	->setCategory("Export dat");
							 
		return $this->Excel;
	}
}