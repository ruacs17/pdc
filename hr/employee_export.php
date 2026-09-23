<?php
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once dirname(__FILE__) . '/PHPExcel/Classes/PHPExcel.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Delacasio")
							 ->setLastModifiedBy("Delacasio")
							 ->setTitle("Office 2007 XLSX Document")
							 ->setSubject("Office 2007 XLSX Document")
							 ->setDescription("Document for Office 2007 XLSX")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Result file");
$wizard = new PHPExcel_Helper_HTML;

// Add some data
$headLetter = "A";
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue($headLetter.'1', 'EMPLOYEE ID')
			->setCellValue(++$headLetter.'1', 'POSITION')
			->setCellValue(++$headLetter.'1', 'MONTHLY')
			->setCellValue(++$headLetter.'1', 'DAILY')
			->setCellValue(++$headLetter.'1', 'DATE HIRED')
			->setCellValue(++$headLetter.'1', 'DATE PROBATIONARY')
			->setCellValue(++$headLetter.'1', 'DATE REGULARIZATION')
			->setCellValue(++$headLetter.'1', 'STATUS')
			->setCellValue(++$headLetter.'1', 'LAST NAME')
			->setCellValue(++$headLetter.'1', 'FIRST NAME')
			->setCellValue(++$headLetter.'1', 'MIDDLE NAME')
			->setCellValue(++$headLetter.'1', 'NICK NAME')
			->setCellValue(++$headLetter.'1', 'GENDER')
			->setCellValue(++$headLetter.'1', 'BIRTH DATE')
			->setCellValue(++$headLetter.'1', 'BIRTH PLACE')
			->setCellValue(++$headLetter.'1', 'CIVIL STATUS')
			->setCellValue(++$headLetter.'1', 'CITIZENSHIP')
			->setCellValue(++$headLetter.'1', 'RELIGION')
			->setCellValue(++$headLetter.'1', 'HEIGHT')
			->setCellValue(++$headLetter.'1', 'WEIGHT')
			->setCellValue(++$headLetter.'1', 'BLOODTYPE')
			->setCellValue(++$headLetter.'1', 'TIN')
			->setCellValue(++$headLetter.'1', 'PHILHEALTH')
			->setCellValue(++$headLetter.'1', 'SSS')
			->setCellValue(++$headLetter.'1', 'GSIS')
			->setCellValue(++$headLetter.'1', 'RESIDENTIAL ADDRESS')
			->setCellValue(++$headLetter.'1', 'TEL NO')
			->setCellValue(++$headLetter.'1', 'PERMANENT ADDRESS')
			->setCellValue(++$headLetter.'1', 'TEL NO')
			->setCellValue(++$headLetter.'1', 'EMAIL')
			->setCellValue(++$headLetter.'1', 'CELL NO')
			->setCellValue(++$headLetter.'1', "SPOUSE'S SURNAME")
			->setCellValue(++$headLetter.'1', 'FIRST NAME')
			->setCellValue(++$headLetter.'1', 'MIDDLE NAME')
			->setCellValue(++$headLetter.'1', 'OCCUPATION')
			->setCellValue(++$headLetter.'1', 'EMPLOYER/BUS.NAME')
			->setCellValue(++$headLetter.'1', 'BUSINESS ADDRESS')
			->setCellValue(++$headLetter.'1', 'TEL NO')
			->setCellValue(++$headLetter.'1', "FATHER'S SURNAME")
			->setCellValue(++$headLetter.'1', 'FIRST NAME')
			->setCellValue(++$headLetter.'1', 'MIDDLE NAME')
			->setCellValue(++$headLetter.'1', "MOTHER'S SURNAME")
			->setCellValue(++$headLetter.'1', 'FIRST NAME')
			->setCellValue(++$headLetter.'1', 'MIDDLE NAME')
			->setCellValue(++$headLetter.'1', 'CHILD')
			->setCellValue(++$headLetter.'1', 'EDUCATION')
			->setCellValue(++$headLetter.'1', 'PRC')
			->setCellValue(++$headLetter.'1', 'WORK EXPERIENCE')
			->setCellValue(++$headLetter.'1', 'TRAINING')
			->setCellValue(++$headLetter.'1', 'SPECIAL LICENSE')
			->setCellValue(++$headLetter.'1', 'SPECIAL AWARD')
			->setCellValue(++$headLetter.'1', 'SEMINAR');

$row=1;
$qEMp = $db->select('employee','*',array(),'ORDER BY lname,fname,mname');
while($r = $db->fetch_array($qEMp)):
	$row++;
	$col=0;

	$qPOS = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$r['emp_id']),'AND dp.dp_id=ep.dp_id');
	$cntPos = $db->num_rows($qPOS);
	$countPos=1;
	$position='';
	while($rPOS = $db->fetch_array($qPOS)):
		$position .= $rPOS['pos_name'];
		if($countPos < $cntPos)
			$position.=' / ';
			$countPos++;
	endwhile;


	$curStreet = $r['curr_street'];
	$curProvince = $db->getValue('refprovince','provDesc',array('provCode'=>$r['curr_province']));
	$curProvince = ($curProvince) ? ', '.$curProvince : '';
	$curCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$r['curr_cityMun']));
	$curCity = ($curCity) ? ', '.$curCity : '';
	$curBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$r['curr_add']));
	$curr_zipcode = ($r['curr_zipcode']) ? ', '.$r['curr_zipcode'] : '';
	$current_address = $curStreet.' '.strtoupper($curBrngy).$curCity.$curProvince.$curr_zipcode;


	$permStreet = $r['perm_street'];
	$permProvince = $db->getValue('refprovince','provDesc',array('provCode'=>$r['perm_province']));
	$permProvince = ($permProvince) ? ', '.$permProvince : '';
	$permCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$r['perm_cityMun']));
	$permCity = ($permCity) ? ', '.$permCity : '';
	$permBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$r['perm_add']));
	$perm_zipcode = ($r['perm_zipcode']) ? ', '.$r['perm_zipcode'] : '';
	$permanent_address = $permStreet.' '.strtoupper($permBrngy).$permCity.$permProvince.$perm_zipcode;

	$countChild=0;
	$qChild = $db->select('emp_child','*',array('emp_id'=>$r['emp_id']));
	$child_detail='';
	while($rC = $db->fetch_array($qChild)):
		$countChild++;
		$child_detail .='<p>';
		$child_detail .= $countChild.'. '.$rC['ec_fname'].' '.$rC['ec_mname'].' '.$rC['ec_lname'].' '.$rC['ec_extname'].' &nbsp;&nbsp;&nbsp; ';
		$child_detail .= ($rC['ec_bdate']) ? functions::datearr($rC['ec_bdate']) : '';
		$child_detail .='</p>';
	endwhile;

	$education='';
	$qEduc = $db->select('emp_education','*',array('emp_id'=>$r['emp_id']),'ORDER BY ee_year_grad');
	while($rEd = $db->fetch_array($qEduc)):
		$education.='<p>';
		$education.=$rEd['ee_level'].'&nbsp;&nbsp;&nbsp;'.$rEd['ee_school'].'&nbsp;&nbsp;&nbsp;'.$rEd['ee_course'].'&nbsp;&nbsp;&nbsp;'.$rEd['ee_year_grad'];
		$education.='</p>';
	endwhile;
	$prc='';
	$qLic = $db->select('emp_prc_license','*',array('emp_id'=>$r['emp_id']));
	while($rLic = $db->fetch_array($qLic)):
		$prc.='<p>'.$rLic['el_title'].'</p>';
	endwhile;
	$we='';
	$qWE = $db->select('emp_work_exp','*',array('emp_id'=>$r['emp_id']),'ORDER BY ewe_date_from');
	while($rWE = $db->fetch_array($qWE)):
		$we.='<p>';
		$we.=$rWE['ewe_company'].'&nbsp;&nbsp;&nbsp;'.$rWE['ewe_position'].'&nbsp;&nbsp;&nbsp;';
		if($rWE['ewe_no_year'])
			$we.=($rWE['ewe_no_year']>1) ? '('.$rWE['ewe_no_year']. ' years)' : '('.$rWE['ewe_no_year']. ' year)';
		$we.='</p>';
	endwhile;

	$training='';
	$qET = $db->select('emp_training','*',array('emp_id'=>$r['emp_id'],'et_type'=>'training'),'ORDER BY et_date_from');
	while($rET = $db->fetch_array($qET)):
		$training.='<p>';
		$training.=$rET['et_title'].'&nbsp;&nbsp;&nbsp;';
		if($rET['et_no_hour'])
			$training.=($rET['et_no_hour']>1) ? '('.$rET['et_no_hour']. ' hours)' : '('.$rET['et_no_hour']. ' hour)';
		$training.='</p>';
	endwhile;

	$esp_license='';
	$qLic = $db->select('emp_special_license','*',array('emp_id'=>$r['emp_id']));
	while($rLic = $db->fetch_array($qLic)):
		$esp_license.='<p>'.$rLic['esl_title'].'</p>';
	endwhile;

	$esp_award='';
	$qSA = $db->select('emp_special_award','*',array('emp_id'=>$r['emp_id']),'ORDER BY esa_date');
	while($rSA = $db->fetch_array($qSA)):
		$esp_award.='<p>';
		$esp_award.=$rSA['esa_title'];
		$esp_award.=($rSA['esa_date']) ? '&nbsp;&nbsp;&nbsp;'.functions::datearr($rSA['esa_date']) : '';
		$esp_award.='</p>';
	endwhile;

	$seminar='';
	$qSEM = $db->select('emp_training','*',array('emp_id'=>$r['emp_id'],'et_type'=>'seminar'),'ORDER BY et_date_from');
	while($rSEM = $db->fetch_array($qSEM)):
		$seminar.='<p>';
		$seminar.=$rSEM['et_title'];
		if($rSEM['et_no_hour'])
			$seminar.=($rSEM['et_no_hour']>1) ? '&nbsp;&nbsp;&nbsp;('.$rSEM['et_no_hour']. ' hours)' : '&nbsp;&nbsp;&nbsp;('.$rSEM['et_no_hour']. ' hour)';
		$seminar.='<p>';
	endwhile;

	$emp_status = $db->getValue('emp_work_status','ews_stat',array('emp_id'=>$r['emp_id']),'ORDER BY ews_date DESC LIMIT 1');
	$emp_status = ($emp_status) ? $emp_status : '--';
	$date_hired = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$r['emp_id']),'ORDER BY ews_date LIMIT 1');
	$date_hired = ($date_hired) ? functions::datearr($date_hired) : '';
	$date_probitionary = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$r['emp_id'],'ews_stat'=>'Probationary'),'ORDER BY ews_date LIMIT 1');
	$date_probitionary = ($date_probitionary) ? functions::datearr($date_probitionary) : '';
	$date_regular = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$r['emp_id'],'ews_stat'=>'Regular'),'ORDER BY ews_date LIMIT 1');
	$date_regular = ($date_regular) ? functions::datearr($date_regular) : '';


	$salary_monthly=''; $salary_daily='';$es_type='';
	$qSal = $db->select('emp_salary','*',array('emp_id'=>$r['emp_id']),'ORDER BY es_date DESC, es_id DESC LIMIT 1');
	if( $db->num_rows($qSal) > 0 ){
		$rSal = $db->fetch_array($qSal);
		$txSalMonth = functions::formatMoney($rSal['es_salary']);
		$txSalDay = functions::formatMoney($rSal['es_daily']);
		$es_type = $rSal['es_type'];
		if($es_type=='fixed')
			$salary_daily=$txSalDay;
		if($es_type=='flexible')
			$salary_monthly=$txSalMonth;
	}

$letter='A';
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue($letter.$row, $r['emp_no'])
			->setCellValue(++$letter.$row, $position)
			->setCellValue(++$letter.$row, $salary_monthly)
			->setCellValue(++$letter.$row, $salary_daily)
			->setCellValue(++$letter.$row, $date_hired)
			->setCellValue(++$letter.$row, $date_probitionary)
			->setCellValue(++$letter.$row, $date_regular)
			->setCellValue(++$letter.$row, $emp_status)
			->setCellValue(++$letter.$row, $r['lname'])
			->setCellValue(++$letter.$row, $r['fname'])
			->setCellValue(++$letter.$row, $r['mname'])
			->setCellValue(++$letter.$row, $r['nickname'])
			->setCellValue(++$letter.$row, $r['gender'])
			->setCellValue(++$letter.$row, functions::datearr($r['bdate']))
			->setCellValue(++$letter.$row, $r['bplace'])
			->setCellValue(++$letter.$row, $r['civil_status'])
			->setCellValue(++$letter.$row, $r['citizenship'])
			->setCellValue(++$letter.$row, $r['religion'])
			->setCellValue(++$letter.$row, $r['height'])
			->setCellValue(++$letter.$row, $r['weight'])
			->setCellValue(++$letter.$row, $r['bloodtype'])
			->setCellValue(++$letter.$row, $wizard->toRichTextObject($r['tin']))
			->setCellValue(++$letter.$row, $wizard->toRichTextObject($r['philhealth']))
			->setCellValue(++$letter.$row, $wizard->toRichTextObject($r['sss']))
			->setCellValue(++$letter.$row, $wizard->toRichTextObject($r['gsis']))
			->setCellValue(++$letter.$row, $current_address)
			->setCellValue(++$letter.$row, $r['curr_tel'])
			->setCellValue(++$letter.$row, $permanent_address)
			->setCellValue(++$letter.$row, $r['perm_tel'])
			->setCellValue(++$letter.$row, $r['email'])
			->setCellValue(++$letter.$row, $r['cell_no'])
			->setCellValue(++$letter.$row, ($r['sp_extname']) ? $r['sp_lname'].', '.$r['sp_extname'] : $r['sp_lname'])
			->setCellValue(++$letter.$row, $r['sp_fname'])
			->setCellValue(++$letter.$row, $r['sp_mname'])
			->setCellValue(++$letter.$row, $r['sp_occupation'])
			->setCellValue(++$letter.$row, $r['sp_bus_name'])
			->setCellValue(++$letter.$row, $r['sp_bus_address'])
			->setCellValue(++$letter.$row, $r['sp_tel_no'])
			->setCellValue(++$letter.$row, ($r['fr_extname']) ? $r['fr_lname'].', '.$r['fr_extname'] : $r['fr_lname'])
			->setCellValue(++$letter.$row, $r['fr_fname'])
			->setCellValue(++$letter.$row, $r['fr_mname'])
			->setCellValue(++$letter.$row, $r['mr_lname'])
			->setCellValue(++$letter.$row, $r['mr_fname'])
			->setCellValue(++$letter.$row, $r['mr_mname']);

			$col = ++$letter.$row;
			$richText = $wizard->toRichTextObject($child_detail);
			$objPHPExcel->getActiveSheet()->setCellValue($col, $richText);
			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(-1);
			$objPHPExcel->getActiveSheet()->getStyle($col)->getAlignment()->setWrapText(true);

			$col = ++$letter.$row;
			$richText = $wizard->toRichTextObject($education);
			$objPHPExcel->getActiveSheet()->setCellValue($col, $richText);
			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(-1);
			$objPHPExcel->getActiveSheet()->getStyle($col)->getAlignment()->setWrapText(true);

			$col = ++$letter.$row;
			$richText = $wizard->toRichTextObject($prc);
			$objPHPExcel->getActiveSheet()->setCellValue($col, $richText);
			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(-1);
			$objPHPExcel->getActiveSheet()->getStyle($col)->getAlignment()->setWrapText(true);

			$col = ++$letter.$row;
			$richText = $wizard->toRichTextObject($we);
			$objPHPExcel->getActiveSheet()->setCellValue($col, $richText);
			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(-1);
			$objPHPExcel->getActiveSheet()->getStyle($col)->getAlignment()->setWrapText(true);

			$col = ++$letter.$row;
			$richText = $wizard->toRichTextObject($training);
			$objPHPExcel->getActiveSheet()->setCellValue($col, $richText);
			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(-1);
			$objPHPExcel->getActiveSheet()->getStyle($col)->getAlignment()->setWrapText(true);

			$col = ++$letter.$row;
			$richText = $wizard->toRichTextObject($esp_license);
			$objPHPExcel->getActiveSheet()->setCellValue($col, $richText);
			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(-1);
			$objPHPExcel->getActiveSheet()->getStyle($col)->getAlignment()->setWrapText(true);

			$col = ++$letter.$row;
			$richText = $wizard->toRichTextObject($esp_award);
			$objPHPExcel->getActiveSheet()->setCellValue($col, $richText);
			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(-1);
			$objPHPExcel->getActiveSheet()->getStyle($col)->getAlignment()->setWrapText(true);

			$col = ++$letter.$row;
			$richText = $wizard->toRichTextObject($seminar);
			$objPHPExcel->getActiveSheet()->setCellValue($col, $richText);
			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(-1);
			$objPHPExcel->getActiveSheet()->getStyle($col)->getAlignment()->setWrapText(true);						
endwhile;

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Employee List');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="employee_list.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 2030 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');

exit;
?>
