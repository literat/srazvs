<?php
//echo "die bitch!";


//header
require_once('../inc/define.inc.php');

/* Nastavení cesty ke třídám PHPExcel */  
//ini_set('include_path', ini_get('include_path').';./Classes/');  

/* Vložení potřebných tříd pro práci a tvorbu souboru */  
include_once($INCDIR."phpexcel/Classes/PHPExcel.php");  
include_once($INCDIR."phpexcel/Classes/PHPExcel/Writer/Excel2007.php"); 

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

$mid = requested("mid", "");

/*class createXLSX
{
	var $xlsxrow = 1;
 	var $xlsxcolumn = 1;
 	var $maxcolumn = 0;

 	function getColumn($column){
  		if($this->maxcolumn < $column)
   			$this->maxcolumn = $column;
  		if ($column > 26)
   			return chr(ord('A') + floor(($column - 1)/26) - 1) . chr(ord('A') + ($column % 26) - 1);
  		else
   			return chr(ord('A') + $column - 1);
 	}
 	function writeRow($x, $data, $bold = false) {
  		$i = 0;
  		foreach ($data as $s) {
   			$c = $this->getColumn($this->xlsxcolumn + $i++).strval($this->xlsxrow);
   			$x->setActiveSheetIndex(0)->setCellValue($c,$s);
   			if ($bold)
    			$x->getActiveSheet()->getStyle($c)->getFont()->setBold(true);
  		}
  		$this->xlsxrow++;
 	}
}*/


// vytvoreni XLSX
$objExport = new PHPExcel();
$objExport->getProperties()->setCreator("HKVS Srazy K + K")->setTitle("Návštěvníci");

$objExport->setActiveSheetIndex(0);

// Zde si vyvoláme aktivní list (nastavený nahoře) a vyplníme buňky A1 a A2

$list = $objExport->getActiveSheet();

$list->setCellValue('A1', 'ID');
$list->setCellValue('B1', 'symbol');
$list->setCellValue('C1', 'Jméno');
$list->setCellValue('D1', 'Příjmení');
$list->setCellValue('E1', 'Přezdívka');
$list->setCellValue('F1', 'Narození');
$list->setCellValue('G1', 'E-mail');
$list->setCellValue('H1', 'Adresa');
$list->setCellValue('I1', 'Město');
$list->setCellValue('J1', 'PSČ');
$list->setCellValue('K1', 'Kraj');
$list->setCellValue('L1', 'Evidence');
$list->setCellValue('M1', 'Středisko/Přístav');
$list->setCellValue('N1', 'Oddíl');
$list->setCellValue('O1', 'Účet');
$list->setCellValue('P1', 'Připomínky');
$list->setCellValue('Q1', 'Příjezd');
$list->setCellValue('R1', 'Odjezd');
$list->setCellValue('S1', 'Otázka');

$objExport->getActiveSheet()->getStyle('A1:Z1')->getFont()->setBold(true);

$objExport->getActiveSheet()->getColumnDimension('C')->setWidth(15);  
$objExport->getActiveSheet()->getColumnDimension('D')->setWidth(15);  
$objExport->getActiveSheet()->getColumnDimension('F')->setWidth(15);  
$objExport->getActiveSheet()->getColumnDimension('G')->setWidth(30);  
$objExport->getActiveSheet()->getColumnDimension('H')->setWidth(20);
$objExport->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$objExport->getActiveSheet()->getColumnDimension('K')->setWidth(20);
$objExport->getActiveSheet()->getColumnDimension('M')->setWidth(30);  
$objExport->getActiveSheet()->getColumnDimension('N')->setWidth(20);
$objExport->getActiveSheet()->getColumnDimension('P')->setWidth(20);
$objExport->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
$objExport->getActiveSheet()->getColumnDimension('R')->setWidth(20);
$objExport->getActiveSheet()->getColumnDimension('S')->setWidth(20);

$sql = "
SELECT vis.id AS id,
	code,
	vis.name,
	surname,
	nick,
 	DATE_FORMAT(birthday, '%d. %m. %Y') AS birthday,
	vis.email,
	street,
	city,
	postal_code,
	province_name AS province,
	group_num,
	group_name,
	troop_name,
	bill,
	`comment`,
	arrival,
	departure,
	question,
	`all`,
	fry_dinner,
	sat_breakfast,
	sat_lunch,
	sat_dinner,
	sun_breakfast,
	sun_lunch,
	meeting
FROM `kk_visitors` AS vis
LEFT JOIN `kk_provinces` AS provs ON provs.id = vis.province
/*LEFT JOIN `kk_visitor-program` AS visprog ON visprog.visitor = vis.id
LEFT JOIN `kk_programs` AS progs ON visprog.program = progs.id*/
LEFT JOIN `kk_meals` AS mls ON mls.visitor = vis.id
WHERE vis.deleted = '0' AND meeting = '".$mid."'
";

$query = mysql_query($sql);

$i = 2;
while($data = mysql_fetch_assoc($query)){
	$list->setCellValue('A'.$i, $data['id']);
	$list->setCellValue('B'.$i, $data['code']);
	$list->setCellValue('C'.$i, $data['name']);
	$list->setCellValue('D'.$i, $data['surname']);
	$list->setCellValue('E'.$i, $data['nick']);
	$list->setCellValue('F'.$i, $data['birthday']);
	$list->setCellValue('G'.$i, $data['email']);
	$list->setCellValue('H'.$i, $data['street']);
	$list->setCellValue('I'.$i, $data['city']);
	$list->setCellValue('J'.$i, $data['postal_code']);
	$list->setCellValue('K'.$i, $data['province']);
	$list->setCellValue('L'.$i, $data['group_num']);
	$list->setCellValue('M'.$i, $data['group_name']);
	$list->setCellValue('N'.$i, $data['troop_name']);
	$list->setCellValue('O'.$i, $data['bill']);
	$list->setCellValue('P'.$i, $data['comment']);
	$list->setCellValue('Q'.$i, $data['arrival']);
	$list->setCellValue('R'.$i, $data['departure']);
	$list->setCellValue('S'.$i, $data['question']);
	$list->setCellValue('T'.$i, $data['all']);
	$list->setCellValue('U'.$i, $data['fry_dinner']);
	$list->setCellValue('V'.$i, $data['sat_breakfast']);
	$list->setCellValue('W'.$i, $data['sat_lunch']);
	$list->setCellValue('X'.$i, $data['sat_dinner']);
	$list->setCellValue('Y'.$i, $data['sun_breakfast']);
	$list->setCellValue('Z'.$i, $data['sun_lunch']);
	$i++;
}





// zapsani dat z VIEW
//$xls = new createXLSX();
//$q = mysql_query("SELECT * FROM `kk_export`");
//$i = 0;
//while($d = mysql_fetch_assoc($q)){
// if(!$i++) $xls->writeRow($x, array_keys($d), true );
// $xls->writeRow($x, array_values($d) );
//}

// autosize
/*for ($i = 1; $i<=$xls->maxcolumn; $i++)
 $x->setActiveSheetIndex(0)->getColumnDimension($xls->getColumn($i))->setAutoSize(true);*/

// stahnuti souboru
$filename = 'export-MS_'.date('d.m.Y',time()).'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="export.xlsx"');
header('Cache-Control: max-age=0');

//$objWriter = PHPExcel_IOFactory::createWriter($objExport, 'Excel2007');
$objWriter = new PHPExcel_Writer_Excel2007($objExport); 
$objWriter->save('php://output');
//$objWriter->save('export.xlsx');

?>