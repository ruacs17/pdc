<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/read_excel_xlsx.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$filename='';$uploaded_file='';$filetype='';

function dayName($date=''){
	#$date = '2014-02-25';
	if($date)
		return date('l', strtotime($date));
	else
		return '';
}
#computing the absent
function absentDay($actualAmIn='',$assignAmIn='',$actualAmOut='',$assignAmOut='',$actualPmIn='',$assignPmIn='',$actualPmOut='',$assignPmOut='',&$am_absent=0,&$pm_absent=0,&$am_absent_min=0,&$pm_absent_min=0){
	$cDate = date('Y-m-d');

	if( $assignAmIn && $assignAmOut ){//If there is required time in and out
		//Count Minutes LATE
		if($actualAmIn){//If there is time in
			if( strtotime($actualAmIn) > strtotime($assignAmIn) &&  strtotime($actualAmIn) <= strtotime($assignAmOut) ){//Actual in is between assign in and out
				$am_absent_min += functions::min_diff($assignAmIn,$cDate,$actualAmIn,$cDate);//Minutes from assign in to actual in
			}
			else if( strtotime($actualAmIn) > strtotime($assignAmOut) ){ // Actual in is beyond assign out
				$am_absent_min += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);//Count the required minutes for morning duty.
			}
		}

		//Count Minutes UNDERTIME
		if($actualAmOut){//If there is morning time out
			if( strtotime($actualAmOut) < strtotime($assignAmOut) && strtotime($actualAmOut) > strtotime($assignAmIn) ){//Actual out is between assign in and out
				$am_absent_min += functions::min_diff($actualAmOut,$cDate,$assignAmOut,$cDate);//Minutes from Actual Out to Assign Out.
			}
			else if( strtotime($actualAmOut) < strtotime($assignAmIn) ){//Actual out is less then assign in
				$am_absent_min += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);//Count the required minutes for morning duty.
			}
		}
		else if( $actualAmIn ){//if there is no morning out but there is morning time in
			if( strtotime($actualAmIn) > strtotime($assignAmOut) ){//if time in is greater than assign in
				//Do nothing It's already been deducted by late.
			}
			else if( strtotime($actualAmIn) > strtotime($assignAmIn) && strtotime($actualAmIn) < strtotime($assignAmOut)){//Actual in is between assign in and out
				$am_absent_min += functions::min_diff($actualAmIn,$cDate,$assignAmOut,$cDate);
			}
			else{//if time in is before morning assign in and after morning assign out
				$am_absent_min += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
			}
		}
		else{//If there's no actual out.
			$am_absent_min += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
		}
		if( $am_absent_min ){
			$requiredMins = functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
			if( $am_absent_min >= $requiredMins )
				$am_absent=.5;
			else
				$am_absent=0;
		}
	}

	if( $assignPmIn && $assignPmOut ){
		if($actualPmIn){
			if( strtotime($actualPmIn) > strtotime($assignPmIn) &&  strtotime($actualPmIn) <= strtotime($assignPmOut) ){//Actual in is between assign in and out
				$pm_absent_min += functions::min_diff($assignPmIn,$cDate,$actualPmIn,$cDate);
			}
			else if( strtotime($actualPmIn) > strtotime($assignPmOut) ){ // Actual in is beyond assign out
				$pm_absent_min += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			}
		}
		if($actualPmOut){
			if( strtotime($actualPmOut) < strtotime($assignPmOut) && strtotime($actualPmOut) > strtotime($assignPmIn) ){//Actual out is between assign in and out
				$pm_absent_min += functions::min_diff($actualPmOut,$cDate,$assignPmOut,$cDate);
			}
			else if( strtotime($actualPmOut) < strtotime($assignPmIn) ){//Actual out is less then assign in
				$pm_absent_min += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			}
		}
		else if( $actualPmIn ){
			if( strtotime($actualPmIn) > strtotime($assignPmOut) ){//Actual in is greather than assign out.
			//Do nothing It's already been deducted by late.
			}
			else if( strtotime($actualPmIn) > strtotime($assignPmIn) && strtotime($actualPmIn) < strtotime($assignPmOut)){//Actual in is between assign in and out
				$pm_absent_min += functions::min_diff($actualPmIn,$cDate,$assignPmOut,$cDate);
			}
			else{
				$pm_absent_min += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			}
		}
		else{//If there's no actual out.
			$pm_absent_min += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
		}
		if( $pm_absent_min ){
			$requiredMins = functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			if( $pm_absent_min >= $requiredMins )
				$pm_absent=.5;
			else
				$pm_absent=0;
		}
	}
}

#computing the difference
function diffComp($actualAmIn='',$assignAmIn='',$actualAmOut='',$assignAmOut='',$actualPmIn='',$assignPmIn='',$actualPmOut='',$assignPmOut='',$actualOtIn,$actualOtOut,&$late=0,&$undertime=0,&$dutyHours=0,&$otHours=0){
	$cDate = date('Y-m-d');
	if( $assignAmIn && $assignAmOut )
		$dutyHours += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
	if( $assignPmIn && $assignPmOut )
		$dutyHours += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
	if( $actualOtIn && $actualOtOut ){
		$otTimeIn = functions::hm_to_minute($actualOtIn);
		$otTimeOut = functions::hm_to_minute($actualOtOut);
		$cDateTo = $cDate;
		if($otTimeIn > $otTimeOut){//If timefrom is bigger than timeto, then timeto is the next day.
			$cDateTo = functions::AddDay($cDate,1);
		}
		$otHours += functions::min_diff($actualOtIn,$cDate,$actualOtOut,$cDateTo);
	}

	if( $assignAmIn && $assignAmOut ){
		if($actualAmIn){
			if( strtotime($actualAmIn) > strtotime($assignAmIn) &&  strtotime($actualAmIn) <= strtotime($assignAmOut) ){//Actual in is between assign in and out
				$late += functions::min_diff($assignAmIn,$cDate,$actualAmIn,$cDate);
			}
			else if( strtotime($actualAmIn) > strtotime($assignAmOut) ){ // Actual in is beyond assign out
				$late += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
			}
		}

		if($actualAmOut){
			if( strtotime($actualAmOut) < strtotime($assignAmOut) && strtotime($actualAmOut) > strtotime($assignAmIn) ){//Actual out is between assign in and out
				$undertime += functions::min_diff($actualAmOut,$cDate,$assignAmOut,$cDate);
			}
			else if( strtotime($actualAmOut) < strtotime($assignAmIn) ){//Actual out is less then assign in
				$undertime += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
			}
		}
		else if( $actualAmIn){
			if( strtotime($actualAmIn) > strtotime($assignAmOut) ){
				//Do nothing It's already been deducted by late.
			}
			else if( strtotime($actualAmIn) > strtotime($assignAmIn) && strtotime($actualAmIn) < strtotime($assignAmOut)){//Actual in is between assign in and out
				$undertime += functions::min_diff($actualAmIn,$cDate,$assignAmOut,$cDate);
			}
			else{
				$undertime += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
			}
		}
		else//If there's no actual out.
			$undertime += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
	}

	if( $assignPmIn && $assignPmOut ){
		if($actualPmIn){
			if( strtotime($actualPmIn) > strtotime($assignPmIn) &&  strtotime($actualPmIn) <= strtotime($assignPmOut) ){//Actual in is between assign in and out
				$late += functions::min_diff($assignPmIn,$cDate,$actualPmIn,$cDate);
			}
			else if( strtotime($actualPmIn) > strtotime($assignPmOut) ){ // Actual in is beyond assign out
				$late += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			}
		}
		if($actualPmOut){
			if( strtotime($actualPmOut) < strtotime($assignPmOut) && strtotime($actualPmOut) > strtotime($assignPmIn) ){//Actual out is between assign in and out
				$undertime += functions::min_diff($actualPmOut,$cDate,$assignPmOut,$cDate);
			}
			else if( strtotime($actualPmOut) < strtotime($assignPmIn) ){//Actual out is less then assign in
				$undertime += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			}
		}
		else if( $actualPmIn ){
			if( strtotime($actualPmIn) > strtotime($assignPmOut) ){//Actual in is greather than assign out.
				//Do nothing It's already been deducted by late.
			}
			else if( strtotime($actualPmIn) > strtotime($assignPmIn) && strtotime($actualPmIn) < strtotime($assignPmOut)){//Actual in is between assign in and out
				$undertime += functions::min_diff($actualPmIn,$cDate,$assignPmOut,$cDate);
			}
			else{
				$undertime += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			}
		}
		else//If there's no actual out.
			$undertime += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
	}
	$dutyHours -= $undertime + $late;
	$dutyHours = ( $dutyHours >=0 ) ? $dutyHours : 0;
}
#computing the difference of duty minutes
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;

$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$proj_id = $rEatID['proj_id'];
$date_start = $rEatID['date_start'];
$date_end = $rEatID['date_end'];
$worker_type = $rEatID['payroll_type'];
$note = $rEatID['note'];


$attendance_detail = $db->getValue('emp_attendance_detail','count(eat_id)',array('eat_id'=>$eatid));
$frmSummary = (isset($_REQUEST['frm']) && !empty($_REQUEST['frm']) ) ? $_REQUEST['frm'] : 0;
$personnel = $db->getValue('emp_attendance_personnel','count(eat_id)',array('eat_id'=>$eatid));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Attendance Upload</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">

	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<!-- start: Favicon -->
	<link rel="shortcut icon" href="../img/favicon.png">
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<?php
if( isset($_POST['upload']) && $eatid ){
	$filetype = $db->getValue('emp_attendance','filetype',array('eat_id'=>$eatid));
	$proj_id = $db->getValue('emp_attendance','proj_id',array('eat_id'=>$eatid));
	$dateRangeDBFrom = $db->getValue('emp_attendance','date_start',array('eat_id'=>$eatid));
	$dateRangeDBTo = $db->getValue('emp_attendance','date_end',array('eat_id'=>$eatid));

	$fileuploads = $_FILES['file'];
	$countFileUpload=0;
	//name, type, tmp_name, error, size
	if(count($fileuploads['name'])){
		$countFileUpload++;
		for($i=0;$i<(count($fileuploads['name'])); $i++):
			$fileName = $fileuploads['name'][$i];
			$fileType = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));
			$fileError = $fileuploads['error'][$i];
			$fileSize = $fileuploads['size'][$i];
			$fileTempName = $fileuploads['tmp_name'][$i];
			$totalRow = 0;
			if($fileType==='xlsx'){
				require_once('../class/read_excel_xlsx.php');
				if ( $xlsx = read_excel_xlsx::parse($fileTempName) ){
					$content = $xlsx->rows();
					$totalRow = count($content);
				}else{
					echo read_excel_xlsx::parseError();
				}
			}
			if($fileType==='xls'){
				require_once('../class/read_excel_xls.php');
				if( $xls = read_excel_xls::parse($fileTempName) ){
					$content = $xls->rows();
					$totalRow = count($content);
				}else{
					echo read_excel_xls::parseError();
				}
			}

			#echo '<pre>';print_r($content);echo '</pre>';
			#echo '<br>------<br>';
			if($totalRow){//If file is not empty
				$arrSelEmp=array();
				$arrNotFound=array();
				$qGroupEmpNos = $db->select('emp_attendance_personnel ead, employee e','*',array('eat_id'=>$eatid),'AND ead.emp_id=e.emp_id ORDER BY lname');
				while($rGroupEmpNos = $db->fetch_array($qGroupEmpNos)):
					$arrSelEmp[$rGroupEmpNos['emp_no']] = $rGroupEmpNos['lname'].', '.$rGroupEmpNos['fname'].' '.$rGroupEmpNos['mname'];
				endwhile;
				$arrNotFound=$arrSelEmp;

				#getting start and end of date
				$arrAttendanceRecord=array();
				$dateRange = $content[2][2];
				$dateRangeFrom='';$dateRangeTo='';$yearFrom='';$dateFrom='';$monFrom='';
				if($dateRange){
					$dateRangeExp = explode('~',$dateRange);
					$dateRangeFrom = isset($dateRangeExp[0]) ? trim($dateRangeExp[0]) : '';
					$dateRangeTo = isset($dateRangeExp[1]) ? trim($dateRangeExp[1]) : '';
					$yearFrom = substr($dateRangeFrom,0,4);
					$monFrom = substr($dateRangeFrom,5,2);
					$dateFrom = substr($dateRangeFrom,8,2);
				}

				#initializing dates from start and end dates
				$arrDate=array();
				#$dates = functions::date_diff($dateRangeFrom,$dateRangeTo);//Depends on the attlog date
				$dates = functions::date_diff($dateRangeDBFrom,$dateRangeDBTo);
				if($dates){
					for($iDate=0;$iDate<=$dates;$iDate++):
						$succeeding_date = functions::AddDay($dateRangeFrom,$iDate);
						$daysDate = date('d', strtotime($succeeding_date));
						$arrDate[]=$daysDate;
					endfor;
				}

				$arrDateDB=array();
				$datesDB = functions::date_diff($dateRangeDBFrom,$dateRangeDBTo);
				if($datesDB){
					for($iDateDB=0;$iDateDB<=$datesDB;$iDateDB++):
						$succeeding_dateDB = functions::AddDay($dateRangeDBFrom,$iDateDB);
						$daysDateDB = date('Y-m-d', strtotime($succeeding_dateDB));
						$arrDateDB[]=$daysDateDB;
					endfor;
				}

				#getting attendance from the excel file
				for($t=4;$t<$totalRow; $t++):
					if( ($t%2)==0 ){
						$emp_no=isset($content[$t][2]) ? $content[$t][2] : '';
						$emp_name=isset($content[$t][10]) ? $content[$t][10] : '';
						#if employee no is found in the array
						if( isset($arrSelEmp[$emp_no]) ){
							unset($arrNotFound[$emp_no]);//remove if from the not found array.
							$countAttendance=0;
							#displaying all the dates in Arrdate to get from the excel
							foreach($arrDate as $dailyIndx => $dailyVal):
								if($dailyVal){
									$monFrom = (strlen($monFrom)==1) ? '0'.$monFrom : $monFrom;
									$dailyVal = (strlen($dailyVal)==1) ? '0'.$dailyVal : $dailyVal;
									if($dateFrom <= $dailyVal)
										$dailyDate = $yearFrom.'-'.$monFrom.'-'.$dailyVal;
									else{
										$monFromA = (strlen($monFrom+1)==1) ? '0'.($monFrom+1) : ($monFrom+1);
										$dailyDate = $yearFrom.'-'.$monFromA.'-'.$dailyVal;
									}
									if( isset($arrAttendanceDaily[$dailyIndx]) && $arrAttendanceDaily[$dailyIndx]){
										$countAttendance++;
										$arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'');
									}
									else{
										$arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'');
									}
								}
							endforeach;
							if($countAttendance==0){//No attendance in excel
								foreach($arrDate as $dailyIndx => $dailyVal):
									if($dailyVal){
										$monFrom = (strlen($monFrom)==1) ? '0'.$monFrom : $monFrom;
										$dailyVal = (strlen($dailyVal)==1) ? '0'.$dailyVal : $dailyVal;
										if($dateFrom <= $dailyVal)
											$dailyDate = $yearFrom.'-'.$monFrom.'-'.$dailyVal;
										else{
											$monFromA = (strlen($monFrom+1)==1) ? '0'.($monFrom+1) : ($monFrom+1);
											$dailyDate = $yearFrom.'-'.$monFromA.'-'.$dailyVal;
										}
										$arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'');
									}
								endforeach;
							}
							ksort($arrAttendanceRecord[$emp_no]);
						}
					}

					if( ($t%2)==1 ){
						if( isset($arrSelEmp[$emp_no]) ){
							$arrAttendanceDaily = isset($content[$t]) ? $content[$t] : array();
							foreach($arrDateDB as $dateDB):
								$arrAttendanceRecord[$emp_no][$dateDB]=array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'');
							endforeach;
							foreach($arrDate as $dailyIndx => $dailyVal):
								$amIn='';$amOut='';$pmIn='';$pmOut='';$otIn='';$otOut='';
								if( isset($arrAttendanceDaily[$dailyIndx]) && $arrAttendanceDaily[$dailyIndx]){
									$tme = $arrAttendanceDaily[$dailyIndx];
									if($tme){
										$amIn='';$amOut='';$pmIn='';$pmOut='';$otIn='';$otOut='';
										for($x=0; $x<50; $x+=5):
											$timePerDay = substr($tme,$x,5);
											if($timePerDay){
												if($amIn=="")
													$amIn=$timePerDay;
												elseif($amOut=='')
													$amOut=$timePerDay;
												elseif($pmIn=='')
													$pmIn=$timePerDay;
												elseif($pmOut=='')
													$pmOut=$timePerDay;
												elseif($otIn=='')
													$otIn=$timePerDay;
												elseif($otOut=='')
													$otOut=$timePerDay;
											}
										endfor;
										$monFrom = (strlen($monFrom)==1) ? '0'.$monFrom : $monFrom;
										$dailyVal = (strlen($dailyVal)==1) ? '0'.$dailyVal : $dailyVal;
										if($dateFrom <= $dailyVal)
											$dailyDate = $yearFrom.'-'.$monFrom.'-'.$dailyVal;
										else{
											$monFromA = (strlen($monFrom+1)==1) ? '0'.($monFrom+1) : ($monFrom+1);
											$dailyDate = $yearFrom.'-'.$monFromA.'-'.$dailyVal;
										}
										$real_emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$emp_no));
										$otIn = $otInFile = $db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$real_emp_id,'actual_start_date'=>$dailyDate));
										$otOut = $otOutFile = $db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$real_emp_id,'actual_start_date'=>$dailyDate));
										$arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>$amIn,'amout'=>$amOut,'pmin'=>$pmIn,'pmout'=>$pmOut,'otin'=>$otIn,'otout'=>$otOut);
									}
								}
							endforeach;
							ksort($arrAttendanceRecord[$emp_no]);
						}//Registered Emp Number
					}
				endfor;
				#getting attendance from the excel file

				#assigning empty attendance to not found emp no
				if(count($arrNotFound)){
					foreach($arrNotFound as $eid => $enm):
						foreach($arrDateDB as $dateDB):
							$arrAttendanceRecord[$eid][$dateDB]=array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'');
						endforeach;
						foreach($arrDate as $dailyIndx => $dailyVal):
							if($dailyVal){
								$monFrom = (strlen($monFrom)==1) ? '0'.$monFrom : $monFrom;
								$dailyVal = (strlen($dailyVal)==1) ? '0'.$dailyVal : $dailyVal;
								if($dateFrom <= $dailyVal)
									$dailyDate = $yearFrom.'-'.$monFrom.'-'.$dailyVal;
								else{
									$monFromA = (strlen($monFrom+1)==1) ? '0'.($monFrom+1) : ($monFrom+1);
									$dailyDate = $yearFrom.'-'.$monFromA.'-'.$dailyVal;
								}
								$arrAttendanceRecord[$eid][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'');
							}
						endforeach;
						ksort($arrAttendanceRecord[$eid]);
					endforeach;
				}
				#assigning empty attendance to not found emp no

				#getting holidays based on the date start and date end of the attendance
				$arrHoliday=array();
				if($dateRangeFrom && $dateRangeTo){
					$frmYr = (substr($dateRangeFrom,0,4)) ? substr($dateRangeFrom,0,4) : date('Y');
					$toYr = (substr($dateRangeTo,0,4)) ? substr($dateRangeTo,0,4) : date('Y');
					$qHoliday = $db->select('holiday','*',array(),'WHERE hol_year!="all" AND concat(hol_year,"-",hol_month,"-",hol_day) BETWEEN "'.$dateRangeFrom.'" AND "'.$dateRangeTo.'"');
					while($rHol = $db->fetch_array($qHoliday)):
						$arrHoliday[$frmYr.'-'.$rHol['hol_month'].'-'.$rHol['hol_day']]=$rHol['hol_name'];
					endwhile;

					$qHolidayAllYear = $db->select('holiday','*',array(),'WHERE hol_year="all"');
					if($frmYr != $toYr){
					while($rHolAY = $db->fetch_array($qHolidayAllYear)):
						$arrHoliday[$frmYr.'-'.$rHolAY['hol_month'].'-'.$rHolAY['hol_day']]=$rHolAY['hol_name'];
						$arrHoliday[$toYr.'-'.$rHolAY['hol_month'].'-'.$rHolAY['hol_day']]=$rHolAY['hol_name'];
					endwhile;
					}
					else{
					while($rHolAY = $db->fetch_array($qHolidayAllYear)):
						$arrHoliday[$frmYr.'-'.$rHolAY['hol_month'].'-'.$rHolAY['hol_day']]=$rHolAY['hol_name'];
					endwhile;
					}
				}
				#getting holidays based on the date start and date end of the attendance

				#echo '<pre>';print_r($arrAttendanceRecord);echo '</pre>';
				$txSalMonth=0;$txSalDay=0;$txSalHour=0;$txSalMin=0;
				foreach($arrAttendanceRecord as $employee => $calDate):
					$monAmIn='';$monAmOut='';$monPmIn='';$monPmOut='';$tueAmIn='';$tueAmOut='';$tuePmIn='';$tuePmOut='';$wedAmIn='';$wedAmOut='';$wedPmIn='';$wedPmOut='';$thuAmIn='';$thuAmOut='';$thuPmIn='';$thuPmOut='';$friAmIn='';$friAmOut='';$friPmIn='';$friPmOut='';$satAmIn='';$satAmOut='';$satPmIn='';$satPmOut='';$am_in='';$am_out='';$pm_in='';$pm_out='';
					$emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$employee));
					//Get Employee Filed Leave
					$arrEmpLeaves=array();
					if($dateRangeFrom && $dateRangeTo){
						#$lfd_id = $db->getValue('leave_file_detail','lfd_id',array('emp_id'=>$emp_id,'lfd_date'=>$cDate))
						$qLeaveDates = $db->select('leave_file_detail','*',array('emp_id'=>$emp_id),'AND lfd_date BETWEEN "'.$dateRangeFrom.'" AND "'.$dateRangeTo.'"');
						while($rLD = $db->fetch_array($qLeaveDates)):
							$arrEmpLeaves[$rLD['lfd_date']]=$rLD['lfd_id'];
						endwhile;
					}
					$qSal = $db->select('emp_salary','*',array('emp_id'=>$emp_id),'ORDER BY es_date DESC, es_id DESC');
					if( $db->num_rows($qSal) > 0 ){
						$rSal = $db->fetch_array($qSal);
						$txSalMonth = functions::formatMoney($rSal['es_salary'],4);
						$txSalDay = functions::formatMoney($rSal['es_daily'],4);
						$txSalHour = functions::formatMoney($rSal['es_hourly'],4);
						$txSalMin = functions::formatMoney($rSal['es_minute'],4);
					}
					$qTimeIn = $db->select('emp_timein','*',array('emp_id'=>$emp_id));
					while($rTI = $db->fetch_array($qTimeIn)):
						if($rTI['eti_day']=='Monday'){
							$monAmIn=$rTI['am_in'];$monAmOut=$rTI['am_out'];$monPmIn=$rTI['pm_in'];$monPmOut=$rTI['pm_out'];
						}
						if($rTI['eti_day']=='Tuesday'){
							$tueAmIn=$rTI['am_in'];$tueAmOut=$rTI['am_out'];$tuePmIn=$rTI['pm_in'];$tuePmOut=$rTI['pm_out'];
						}
						if($rTI['eti_day']=='Wednesday'){
							$wedAmIn=$rTI['am_in'];$wedAmOut=$rTI['am_out'];$wedPmIn=$rTI['pm_in'];$wedPmOut=$rTI['pm_out'];
						}
						if($rTI['eti_day']=='Thursday'){
							$thuAmIn=$rTI['am_in'];$thuAmOut=$rTI['am_out'];$thuPmIn=$rTI['pm_in'];$thuPmOut=$rTI['pm_out'];
						}
						if($rTI['eti_day']=='Friday'){
							$friAmIn=$rTI['am_in'];$friAmOut=$rTI['am_out'];$friPmIn=$rTI['pm_in'];$friPmOut=$rTI['pm_out'];
						}
						if($rTI['eti_day']=="Saturday"){
							$satAmIn=$rTI['am_in'];$satAmOut=$rTI['am_out'];$satPmIn=$rTI['pm_in'];$satPmOut=$rTI['pm_out'];
						}
					endwhile;
					foreach($calDate as $cDate => $logins):
						$am_in='';$am_out='';$pm_in='';$pm_out='';
						$dayName = dayName($cDate);
						$late=0; $undertime=0;$dutyHours=0;$otHours=0;
						$am_in_assign=NULL;$am_out_assign=NULL;$pm_in_assign=NULL;$pm_out_assign=NULL;
						$actualAmIn = $logins['amin']; $actualAmOut = $logins['amout']; $actualPmIn = $logins['pmin']; $actualPmOut = $logins['pmout'];

						$eatd_id = $db->getValue('emp_attendance_detail','eatd_id',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'eat_date'=>$cDate));
						if($eatd_id){
							//For re uploaded attendance,
							//Get the recent attendance
							$qRecentRecord = $db->select('emp_attendance_detail','*',array('eatd_id'=>$eatd_id));
							$rRR = $db->fetch_array($qRecentRecord);
							$recentAmIn = $rRR['am_in'];$recentAmOut = $rRR['am_out'];$recentPmIn = $rRR['pm_in'];$recentPmOut = $rRR['pm_out'];
							$countActualAM=0;$countRecentAM=0;$countActualPM=0;$countRecentPM=0;
							if($actualAmIn) $countActualAM++;
							if($actualAmOut) $countActualAM++;
							if($recentAmIn) $countRecentAM++;
							if($recentAmOut) $countRecentAM++;
							//Compare the recent logins/logout from the newly upload's logins/logout
							if($countRecentAM > $countActualAM){
								//If the recent logins/logout has more input than the later upload, then recent will set as login and logout.
								$actualAmIn = $recentAmIn;
								$actualAmOut = $recentAmOut;
							}//If the recent logins/logout has lesser input than the later upload, then the later upload will be its value.
							if($actualPmIn) $countActualPM++;
							if($actualAmOut) $countActualPM++;
							if($recentPmIn) $countRecentPM++;
							if($recentPmOut) $countRecentPM++;
							if($countRecentPM > $countActualPM){
								$actualPmIn = $recentPmIn;
								$actualPmOut = $recentPmOut;
							}
						}
						if($dayName=="Monday"){
							diffComp($actualAmIn,$monAmIn,$actualAmOut,$monAmOut,$actualPmIn,$monPmIn,$actualPmOut,$monPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
							$am_in_assign = (isset($monAmIn) && !empty($monAmIn)) ? $monAmIn : NULL;
							$am_out_assign = (isset($monAmOut) && !empty($monAmOut)) ? $monAmOut : NULL;
							$pm_in_assign = (isset($monPmIn) && !empty($monPmIn)) ? $monPmIn : NULL;
							$pm_out_assign = (isset($monPmOut) && !empty($monPmOut)) ? $monPmOut : NULL;
						}
						if($dayName=="Tuesday"){
							diffComp($actualAmIn,$tueAmIn,$actualAmOut,$tueAmOut,$actualPmIn,$tuePmIn,$actualPmOut,$tuePmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
							$am_in_assign = (isset($tueAmIn) && !empty($tueAmIn)) ? $tueAmIn : NULL;
							$am_out_assign = (isset($tueAmOut) && !empty($tueAmOut)) ? $tueAmOut : NULL;
							$pm_in_assign = (isset($tuePmIn) && !empty($tuePmIn)) ? $tuePmIn : NULL;
							$pm_out_assign = (isset($tuePmOut) && !empty($tuePmOut)) ? $tuePmOut : NULL;
						}
						if($dayName=="Wednesday"){
							diffComp($actualAmIn,$wedAmIn,$actualAmOut,$wedAmOut,$actualPmIn,$wedPmIn,$actualPmOut,$wedPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
							$am_in_assign = (isset($wedAmIn) && !empty($wedAmIn)) ? $wedAmIn : NULL;
							$am_out_assign = (isset($wedAmOut) && !empty($wedAmOut)) ? $wedAmOut : NULL;
							$pm_in_assign = (isset($wedPmIn) && !empty($wedPmIn)) ? $wedPmIn : NULL;
							$pm_out_assign = (isset($wedPmOut) && !empty($wedPmOut)) ? $wedPmOut : NULL;
						}
						if($dayName=="Thursday"){
							diffComp($actualAmIn,$thuAmIn,$actualAmOut,$thuAmOut,$actualPmIn,$thuPmIn,$actualPmOut,$thuPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
							$am_in_assign = (isset($thuAmIn) && !empty($thuAmIn)) ? $thuAmIn : NULL;
							$am_out_assign = (isset($thuAmOut) && !empty($thuAmOut)) ? $thuAmOut : NULL;
							$pm_in_assign = (isset($thuPmIn) && !empty($thuPmIn)) ? $thuPmIn : NULL;
							$pm_out_assign = (isset($thuPmOut) && !empty($thuPmOut)) ? $thuPmOut : NULL;
						}
						if($dayName=="Friday"){
							diffComp($actualAmIn,$friAmIn,$actualAmOut,$friAmOut,$actualPmIn,$friPmIn,$actualPmOut,$friPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
							$am_in_assign = (isset($friAmIn) && !empty($friAmIn)) ? $friAmIn : NULL;
							$am_out_assign = (isset($friAmOut) && !empty($friAmOut)) ? $friAmOut : NULL;
							$pm_in_assign = (isset($friPmIn) && !empty($friPmIn)) ? $friPmIn : NULL;
							$pm_out_assign = (isset($friPmOut) && !empty($friPmOut)) ? $friPmOut : NULL;
						}
						if($dayName=="Saturday"){
							diffComp($actualAmIn,$satAmIn,$actualAmOut,$satAmOut,$actualPmIn,$satPmIn,$actualPmOut,$satPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
							$am_in_assign = (isset($satAmIn) && !empty($satAmIn)) ? $satAmIn : NULL;
							$am_out_assign = (isset($satAmOut) && !empty($satAmOut)) ? $satAmOut : NULL;
							$pm_in_assign = (isset($satPmIn) && !empty($satPmIn)) ? $satPmIn : NULL;
							$pm_out_assign = (isset($satPmOut) && !empty($satPmOut)) ? $satPmOut : NULL;
						}
						$am_in = (isset($actualAmIn) && !empty($actualAmIn)) ? $actualAmIn : NULL;
						$am_out = (isset($actualAmOut) && !empty($actualAmOut)) ? $actualAmOut : NULL;
						$pm_in = (isset($actualPmIn) && !empty($actualPmIn)) ? $actualPmIn : NULL;
						$pm_out = (isset($actualPmOut) && !empty($actualPmOut)) ? $actualPmOut : NULL;

						$emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$employee));

						$otRecordIn = $db->getValue('attendance_overtime ao,attendance_overtime_detail aod','actual_start_time',array('emp_id'=>$emp_id,'actual_start_date'=>$cDate,'proj_id'=>$proj_id),'AND ao.ao_id=aod.ao_id');
						$otRecordOut = $db->getValue('attendance_overtime ao,attendance_overtime_detail aod','actual_end_time',array('emp_id'=>$emp_id,'actual_start_date'=>$cDate,'proj_id'=>$proj_id),'AND ao.ao_id=aod.ao_id');
						$otMins = $db->getValue('attendance_overtime_detail','total_mins',array('emp_id'=>$emp_id,'actual_start_date'=>$cDate));
						$ot_in = ($otRecordIn) ? $otRecordIn : NULL;
						$ot_out = ($otRecordOut) ? $otRecordOut : NULL;
						$otHours = ($otMins) ? $otMins : 0;
						$is_holiday=NULL;
						if($eatd_id){
							$am_in_org = ($am_in) ? $am_in.':00' : NULL;
							$am_out_org = ($am_out) ? $am_out.':00' : NULL;
							$pm_in_org = ($pm_in) ? $pm_in.':00' : NULL;
							$pm_out_org = ($pm_out) ? $pm_out.':00' : NULL;

							//This is for an attendance that has adjustment
							if( $adjstEATid = $db->getValue('emp_attendance_adjustment','eatd_id',array('emp_id'=>$emp_id,'eta_date'=>$cDate),'LIMIT 1') ){// the first adjustment record
								//compare the updated record from the first record of the adjustment table
								#$qnotFound = $db->getValuePrint('emp_attendance_adjustment','count(*)',array('emp_id'=>$emp_id,'eta_date'=>$cDate,'am_in_org'=>$am_in_org,'am_out_org'=>$am_out_org,'pm_in_org'=>$pm_in_org,'pm_out_org'=>$pm_out_org,'eatd_id'=>$adjstEATid));
								if( $db->getValue('emp_attendance_adjustment','count(*)',array('emp_id'=>$emp_id,'eta_date'=>$cDate,'am_in_org'=>$am_in_org,'am_out_org'=>$am_out_org,'pm_in_org'=>$pm_in_org,'pm_out_org'=>$pm_out_org,'eatd_id'=>$adjstEATid)) ){
									//if the first adjustment record matches the newly uploaded record, it means nothing changes
									//So we get the in and out of the LAST adjustment record and write it as an updated record
									$qAdjst = $db->select('emp_attendance_adjustment','*',array('eatd_id'=>$adjstEATid),'ORDER BY eta_id DESC LIMIT 1');
									$rAdjst = $db->fetch_array($qAdjst);
									$am_in = $rAdjst['am_in_new'];
									$am_out = $rAdjst['am_out_new'];
									$pm_in = $rAdjst['pm_in_new'];
									$pm_out = $rAdjst['pm_out_new'];
									$dutyHours = $rAdjst['duty_min_new'];
									#$otHours = $rAdjst['ot_min_new'];
									$late = $rAdjst['late_min_new'];
									$undertime = $rAdjst['under_min_new'];
								}
								else{
									//if the first adjustment record didn't matches the newly uploaded record, it means it is not the old record before
									//therefore we should delete the ALL adjustments that were made.
									#echo $qnotFound;
									#$db->delete('emp_attendance_adjustment',array('emp_id'=>$emp_id,'eta_date'=>$cDate));
									#die();
								}
							}

							//***********HOLIDAY CHECK**************
							//Check if there is a holiday
							if( isset($arrHoliday[$cDate]) ){
								//Getting status of employee whether regular, probationary, contractual, etc
								$work_status = $db->getValue('emp_work_status','ews_stat',array('emp_id'=>$emp_id),'AND ews_date <= "'.$cDate.'" ORDER BY ews_date DESC, ews_id DESC LIMIT 1');
								#$project_based = $db->getValue('emp_work_status','project_based',array('emp_id'=>$emp_id,'ews_stat'=>$work_status),'ORDER BY ews_date DESC LIMIT 1');
								if( ($work_status=='Regular' || $work_status=='Probationary' ) ){//If regular or Probationary
									
									//Getting salary type or wager type/ monthly or daily
									$qSal = $db->select('emp_salary','*',array('emp_id'=>$emp_id),'AND es_date <= "'.$cDate.'" ORDER BY es_date DESC, es_id DESC LIMIT 1');
									$rSal = $db->fetch_array($qSal);
									$sal_type = ($rSal['es_type']) ? $rSal['es_type'] : '';//fixed for daily wager | flexible for monthly wager

									$attDateArr = explode("-",$cDate);
									$holDay = isset($attDateArr[2]) ? $attDateArr[2] : '';
									$holMon = isset($attDateArr[1]) ? $attDateArr[1] : '';
									$holYr = isset($attDateArr[0]) ? $attDateArr[0] : '';
									$qHol = $db->select('holiday','*',array('hol_month'=>$holMon,'hol_day'=>$holDay));
									while($rHol = $db->fetch_array($qHol)):
										if($rHol['hol_year']=='all' || $rHol['hol_year']==$holYr){
											if($rHol['hol_type']=='Regular Holiday'){//Monthly wager and daily wager can avail this holiday
												//check if there is an adjustment already of this kind of holiday
												if( $db->getValue('emp_attendance_adjustment','count(*)',array('emp_id'=>$emp_id,'eta_date'=>$cDate,'remarks'=>$arrHoliday[$cDate]))==0 ){
													//if there is non, then add record
													//insert the original attendance record
													$newAddetaID = $db->insert('emp_attendance_adjustment',array('eatd_id'=>$eatd_id,'emp_id'=>$emp_id,'eta_date'=>$cDate,'eta_day'=>$dayName,
													'am_in_new'=>$am_in_assign,'am_in_org'=>$am_in,
													'am_out_new'=>$am_out_assign,'am_out_org'=>$am_out,
													'pm_in_new'=>$pm_in_assign,'pm_in_org'=>$pm_in,
													'pm_out_new'=>$pm_out_assign,'pm_out_org'=>$pm_out,
													'ot_in'=>$ot_in,'ot_out'=>$ot_out,
													'duty_min_org'=>$dutyHours,'ot_min_org'=>$otHours,
													'late_min_org'=>$late,'under_min_org'=>$undertime,
													'change_date'=>date('Y-m-d'),'change_time'=>date('H:i:s'),'remarks'=>$rHol['hol_type']));
													#echo '<br>'.$db->last_query;
													#echo '<br>';
													$am_in = $am_in_assign;
													$am_out = $am_out_assign;
													$pm_in = $pm_in_assign;
													$pm_out = $pm_out_assign;
													$dutyHours = functions::min_diff($am_in,$cDate,$am_out,$cDate);
													$dutyHours += functions::min_diff($pm_in,$cDate,$pm_out,$cDate);
													$late=0;
													$undertime=0;
													#$otHours=0;
													#$ot_in=NULL;
													#$ot_out = NULL;
													//Update the recently added adjustment
													$db->update('emp_attendance_adjustment',array('duty_min_new'=>$dutyHours,'ot_min_new'=>$otHours,'late_min_new'=>$late,'under_min_new'=>$undertime),array('eta_id'=>$newAddetaID));
													#echo '<br>'.$db->last_query;
													#echo '<br>';
													$is_holiday=1;
												}
											}
											else if($rHol['hol_type']=='Special Non-Working Holiday'){
												if($sal_type=='flexible'){//Only monthly wager can avail the paid special holiday
													//check if there is an adjustment already of this kind of holiday
													if( $db->getValue('emp_attendance_adjustment','count(*)',array('emp_id'=>$emp_id,'eta_date'=>$cDate,'remarks'=>$arrHoliday[$cDate]))==0 ){
														//if there is non, then add record
														//insert the original attendance record
														$newAddetaID = $db->insert('emp_attendance_adjustment',array('eatd_id'=>$eatd_id,'emp_id'=>$emp_id,'eta_date'=>$cDate,'eta_day'=>$dayName,
														'am_in_new'=>$am_in_assign,'am_in_org'=>$am_in,
														'am_out_new'=>$am_out_assign,'am_out_org'=>$am_out,
														'pm_in_new'=>$pm_in_assign,'pm_in_org'=>$pm_in,
														'pm_out_new'=>$pm_out_assign,'pm_out_org'=>$pm_out,
														'ot_in'=>$ot_in,'ot_out'=>$ot_out,
														'duty_min_org'=>$dutyHours,'ot_min_org'=>$otHours,
														'late_min_org'=>$late,'under_min_org'=>$undertime,
														'change_date'=>date('Y-m-d'),'change_time'=>date('H:i:s'),'remarks'=>$rHol['hol_type']));
														#echo '<br>'.$db->last_query;
														#echo '<br>';
														$am_in = $am_in_assign;
														$am_out = $am_out_assign;
														$pm_in = $pm_in_assign;
														$pm_out = $pm_out_assign;
														$dutyHours = functions::min_diff($am_in,$cDate,$am_out,$cDate);
														$dutyHours += functions::min_diff($pm_in,$cDate,$pm_out,$cDate);
														$late=0;
														$undertime=0;
														#$otHours=0;
														#$ot_in=NULL;
														#$ot_out = NULL;
														//Update the recently added adjustment
														$db->update('emp_attendance_adjustment',array('duty_min_new'=>$dutyHours,'ot_min_new'=>$otHours,'late_min_new'=>$late,'under_min_new'=>$undertime),array('eta_id'=>$newAddetaID));
														#echo '<br>'.$db->last_query;
														#echo '<br>';
														$is_holiday=1;//1-holiday 2-leave 3-both
													}
												}//End: Only monthly wager can avail the paid special holiday
											}
										}
									endwhile;
								}//End: If regular or Probationary
							}//End: Check if there is a holiday


							$arrUpdateAtt=array('am_in'=>$am_in,'am_in_assign'=>$am_in_assign,'am_out'=>$am_out,'am_out_assign'=>$am_out_assign,'pm_in'=>$pm_in,'pm_in_assign'=>$pm_in_assign,'pm_out'=>$pm_out,'pm_out_assign'=>$pm_out_assign,'ot_in'=>$ot_in,'ot_out'=>$ot_out,'duty_min'=>$dutyHours,'ot_min'=>$otHours,'late_min'=>$late,'under_min'=>$undertime,'is_holiday'=>$is_holiday);
							$db->update('emp_attendance_detail',$arrUpdateAtt,array('emp_id'=>$emp_id,'eat_id'=>$eatid,'eat_date'=>$cDate));
							#echo '<br>upd: '.$db->last_query;
							#echo '<br>';

							//***********LEAVE CHECK**************
							//Check if there is a leave file
							#if( $lfd_id = $db->getValue('leave_file_detail','lfd_id',array('emp_id'=>$emp_id,'lfd_date'=>$cDate)) ){
							if( isset($arrEmpLeaves[$cDate]) ){
								$lfd_id = $arrEmpLeaves[$cDate];
								$qLv = $db->select('leave_file_detail','*',array('lfd_id'=>$lfd_id));
								$rLv = $db->fetch_array($qLv);

								$leave_name = $db->getValue('leave_config lc, leave_file lf','leave_name',array('lf_id'=>$rLv['lf_id']),'AND lf.lc_id=lc.lc_id');
								$leave_reason = $db->getValue('leave_file lf, leave_file_detail lfd','reason',array('lfd_id'=>$rLv['lfd_id']),'AND lf.lf_id=lfd.lf_id');
								$remarks = $leave_name.' : '.$leave_reason;
								$leave_with_pay = $db->getValue('leave_config lc, leave_file lf','with_pay',array('lf_id'=>$rLv['lf_id']),'AND lf.lc_id=lc.lc_id');
								//Check if there is adjustments already with this kind of leave
								if( $db->getValue('emp_attendance_adjustment','count(*)',array('emp_id'=>$emp_id,'eta_date'=>$cDate,'refer_id'=>$lfd_id))==0 ){
									//If there is none, then add adjustment for this leave
									$arrField = array(
									'eatd_id'=>$eatd_id,'emp_id'=>$emp_id,'eta_date'=>$cDate,'eta_day'=>$dayName,
									'am_in_org'=>$am_in,'am_out_org'=>$am_out,
									'pm_in_org'=>$pm_in,'pm_out_org'=>$pm_out,
									'ot_in'=>$ot_in,'ot_out'=>$ot_out,
									'duty_min_org'=>$dutyHours,'ot_min_org'=>$otHours,'late_min_org'=>$late,'under_min_org'=>$undertime,
									'change_date'=>date('Y-m-d'),'change_time'=>date('H:i:s'),'remarks'=>$remarks,'refer_id'=>$lfd_id);

									$am_in_new = ($rLv['time_am_from']) ? $rLv['time_am_from'] : $am_in;
									$am_out_new = ($rLv['time_am_to']) ? $rLv['time_am_to'] : $am_out;
									$pm_in_new = ($rLv['time_pm_from']) ? $rLv['time_pm_from'] : $pm_in;
									$pm_out_new = ($rLv['time_pm_to']) ? $rLv['time_pm_to'] : $pm_out;
									diffComp($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in_new,$pm_in_assign,$pm_out_new,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
									if($leave_with_pay==0){
										$duty_min_new =  0;
										$under_min_new = functions::min_diff($am_in_assign,$cDate,$am_out_assign,$cDate);
										$under_min_new += functions::min_diff($pm_in_assign,$cDate,$pm_out_assign,$cDate);
										$am_in_new = NULL; $am_out_new = NULL; $pm_in_new = NULL; $pm_out_new=NULL;
									}

									$arrField = array_merge($arrField,array('am_in_new'=>$am_in_new,'am_out_new'=>$am_out_new,'pm_in_new'=>$pm_in_new,'pm_out_new'=>$pm_out_new,'duty_min_new'=>$dutyHours,'ot_min_new'=>$ot_min_new,'late_min_new'=>$late_min_new,'under_min_new'=>$under_min_new));
									//insert the original attendance record
									$inserted_etaID = $db->insert('emp_attendance_adjustment',$arrField);
									#$db->insert('emp_attendance_adjustment',$arrField);
									#echo '<br>Add Adjustemnt: '.$db->last_query.'<br>';
									//update the new record
									$is_holiday = ($is_holiday==1) ? 3 : 2;//1-holiday 2-leave 3-both
									$db->update('emp_attendance_detail',array(
									'am_in'=>$am_in_new,'am_in_assign'=>$am_in_assign,'am_out'=>$am_out_new,'am_out_assign'=>$am_out_assign,
									'pm_in'=>$pm_in_new,'pm_in_assign'=>$pm_in_assign,'pm_out'=>$pm_out_new,'pm_out_assign'=>$pm_out_assign,
									'ot_in'=>$ot_in,'ot_out'=>$ot_out,
									'duty_min'=>$duty_min_new,'ot_min'=>$otHours,'late_min'=>$late_min_new,'under_min'=>$under_min_new,'is_holiday'=>$is_holiday),array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
									#echo '<br>Change: '.$db->last_query.'<br>';
								}
							}
							//End: Check if there is a leave file

							//Checking for absent
							$am_absent=0;$pm_absent=0;$am_absent_min=0;$pm_absent_min=0;
							$q_absent = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
							$ra = $db->fetch_array($q_absent);
							absentDay($ra['am_in'],$ra['am_in_assign'],$ra['am_out'],$ra['am_out_assign'],$ra['pm_in'],$ra['pm_in_assign'],$ra['pm_out'],$ra['pm_out_assign'],$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
							$db->update('emp_attendance_detail',array('am_absent'=>$am_absent,'pm_absent'=>$pm_absent,'am_absent_min'=>$am_absent_min,'pm_absent_min'=>$pm_absent_min),array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
							//End checking absent
						}
						else{
							#insert new record
							$eatd_id = $db->insert('emp_attendance_detail',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'eat_date'=>$cDate,'eat_day'=>$dayName,
							'am_in'=>$am_in,'am_in_assign'=>$am_in_assign,'am_out'=>$am_out,'am_out_assign'=>$am_out_assign,
							'pm_in'=>$pm_in,'pm_in_assign'=>$pm_in_assign,'pm_out'=>$pm_out,'pm_out_assign'=>$pm_out_assign,
							'ot_in'=>$ot_in,'ot_out'=>$ot_out,
							'duty_min'=>$dutyHours,'ot_min'=>$otHours,'late_min'=>$late,'under_min'=>$undertime,'is_holiday'=>$is_holiday));
							#echo '<br>Inserted '.$db->last_query;
							#echo '<br>';

							//Check if there is a leave filed
							#echo $db->getValue('leave_file_detail','lfd_id',array('emp_id'=>$emp_id,'lfd_date'=>$cDate));
							#echo '<br>Leave Check: '.$db->last_query;
							#echo '<br>';
							//***********LEAVE CHECK**************
							#if( $lfd_id = $db->getValue('leave_file_detail','lfd_id',array('emp_id'=>$emp_id,'lfd_date'=>$cDate)) ){
							if( isset($arrEmpLeaves[$cDate]) ){
								$lfd_id = $arrEmpLeaves[$cDate];
								$qLv = $db->select('leave_file_detail','*',array('lfd_id'=>$lfd_id));
								$rLv = $db->fetch_array($qLv);
								$leave_name = $db->getValue('leave_config lc, leave_file lf','leave_name',array('lf_id'=>$rLv['lf_id']),'AND lf.lc_id=lc.lc_id');
								$leave_reason = $db->getValue('leave_file lf, leave_file_detail lfd','reason',array('lfd_id'=>$rLv['lfd_id']),'AND lf.lf_id=lfd.lf_id');
								$remarks = $leave_name.' : '.$leave_reason;
								$leave_with_pay = $db->getValue('leave_config lc, leave_file lf','with_pay',array('lf_id'=>$rLv['lf_id']),'AND lf.lc_id=lc.lc_id');

								$arrField = array(
								'eatd_id'=>$eatd_id,'emp_id'=>$emp_id,'eta_date'=>$cDate,'eta_day'=>$dayName,
								'am_in_org'=>$am_in,'am_out_org'=>$am_out,
								'pm_in_org'=>$pm_in,'pm_out_org'=>$pm_out,
								'ot_in'=>$ot_in,'ot_out'=>$ot_out,
								'duty_min_org'=>$dutyHours,'ot_min_org'=>$otHours,'late_min_org'=>$late,'under_min_org'=>$undertime,
								'change_date'=>date('Y-m-d'),'change_time'=>date('H:i:s'),'remarks'=>$remarks,'refer_id'=>$lfd_id);

								$am_in_new = ($rLv['time_am_from']) ? $rLv['time_am_from'] : $am_in;
								$am_out_new = ($rLv['time_am_to']) ? $rLv['time_am_to'] : $am_out;
								$pm_in_new = ($rLv['time_pm_from']) ? $rLv['time_pm_from'] : $pm_in;
								$pm_out_new = ($rLv['time_pm_to']) ? $rLv['time_pm_to'] : $pm_out;
								diffComp($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in_new,$pm_in_assign,$pm_out_new,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
								if($leave_with_pay==0){
									$duty_min_new =  0;
									$under_min_new = functions::min_diff($am_in_assign,$cDate,$am_out_assign,$cDate);
									$under_min_new += functions::min_diff($pm_in_assign,$cDate,$pm_out_assign,$cDate);
									$am_in_new = NULL; $am_out_new = NULL; $pm_in_new = NULL; $pm_out_new=NULL;
								}
								$arrField = array_merge($arrField,array('am_in_new'=>$am_in_new,'am_out_new'=>$am_out_new,'pm_in_new'=>$pm_in_new,'pm_out_new'=>$pm_out_new,'duty_min_new'=>$duty_min_new,'ot_min_new'=>$ot_min_new,'late_min_new'=>$late_min_new,'under_min_new'=>$under_min_new));
								//insert the original attendance record
								$inserted_etaID = $db->insert('emp_attendance_adjustment',$arrField);
								#echo $db->insertPrint('emp_attendance_adjustment',$arrField);
								#echo '<br>';
								#echo '<br>Add Adjustemnt: '.$db->last_query.'<br>';
								//update the new record
								$is_holiday=2;
								$db->update('emp_attendance_detail',array(
								'am_in'=>$am_in_new,'am_in_assign'=>$am_in_assign,'am_out'=>$am_out_new,'am_out_assign'=>$am_out_assign,
								'pm_in'=>$pm_in_new,'pm_in_assign'=>$pm_in_assign,'pm_out'=>$pm_out_new,'pm_out_assign'=>$pm_out_assign,
								'ot_in'=>$ot_in,'ot_out'=>$ot_out,
								'duty_min'=>$duty_min_new,'ot_min'=>$otHours,'late_min'=>$late_min_new,'under_min'=>$under_min_new,'is_holiday'=>$is_holiday),array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
								#echo '<br>Change: '.$db->last_query.'<br>';
								#die();
							}
							//End: Check if there is a leave filed

							//***********HOLIDAY CHECK**************
							//Check if there is a holiday
							if( isset($arrHoliday[$cDate]) ){
								//Getting status of employee whether regular, probationary, contractual, etc
								$work_status = $db->getValue('emp_work_status','ews_stat',array('emp_id'=>$emp_id),'AND ews_date <= "'.$cDate.'" ORDER BY ews_date DESC, ews_id DESC LIMIT 1');
								#$project_based = $db->getValue('emp_work_status','project_based',array('emp_id'=>$emp_id,'ews_stat'=>$work_status),'ORDER BY ews_date DESC LIMIT 1');
								if( ($work_status=='Regular' || $work_status=='Probationary' ) ){//If regular or Probationary
									
									//Getting salary type or wager type/ monthly or daily
									$qSal = $db->select('emp_salary','*',array('emp_id'=>$emp_id),'AND es_date <= "'.$cDate.'" ORDER BY es_date DESC, es_id DESC LIMIT 1');
									$rSal = $db->fetch_array($qSal);
									$sal_type = ($rSal['es_type']) ? $rSal['es_type'] : '';//fixed for daily wager | flexible for monthly wager

									$attDateArr = explode("-",$cDate);
									$holDay = isset($attDateArr[2]) ? $attDateArr[2] : '';
									$holMon = isset($attDateArr[1]) ? $attDateArr[1] : '';
									$holYr = isset($attDateArr[0]) ? $attDateArr[0] : '';
									$qHol = $db->select('holiday','*',array('hol_month'=>$holMon,'hol_day'=>$holDay));
									while($rHol = $db->fetch_array($qHol)):
										if($rHol['hol_year']=='all' || $rHol['hol_year']==$holYr){
											if($rHol['hol_type']=='Regular Holiday'){//Monthly wager and daily wager can avail this holiday
												//insert the original attendance record
												$inserted_etaID = $db->insert('emp_attendance_adjustment',array('eatd_id'=>$eatd_id,'emp_id'=>$emp_id,'eta_date'=>$cDate,'eta_day'=>$dayName,
												'am_in_new'=>$am_in_assign,'am_in_org'=>$am_in,
												'am_out_new'=>$am_out_assign,'am_out_org'=>$am_out,
												'pm_in_new'=>$pm_in_assign,'pm_in_org'=>$pm_in,
												'pm_out_new'=>$pm_out_assign,'pm_out_org'=>$pm_out,
												'ot_in'=>$ot_in,'ot_out'=>$ot_out,
												'duty_min_org'=>$dutyHours,'ot_min_org'=>$otHours,
												'late_min_org'=>$late,'under_min_org'=>$undertime,
												'change_date'=>date('Y-m-d'),'change_time'=>date('H:i:s'),'remarks'=>$rHol['hol_type']));
												$am_in = $am_in_assign;
												$am_out = $am_out_assign;
												$pm_in = $pm_in_assign;
												$pm_out = $pm_out_assign;
												$dutyHours = functions::min_diff($am_in,$cDate,$am_out,$cDate);
												$dutyHours += functions::min_diff($pm_in,$cDate,$pm_out,$cDate);

												$db->update('emp_attendance_adjustment',array('duty_min_new'=>$dutyHours,'ot_min_new'=>$otHours,'late_min_new'=>0,'under_min_new'=>0),array('eta_id'=>$inserted_etaID));
												$is_holiday = ($is_holiday==2) ? 3 : 1;//1-holiday 2-leave 3-both
												//update the new record
												$db->update('emp_attendance_detail',array(
												'am_in'=>$am_in,'am_in_assign'=>$am_in_assign,'am_out'=>$am_out,'am_out_assign'=>$am_out_assign,
												'pm_in'=>$pm_in,'pm_in_assign'=>$pm_in_assign,'pm_out'=>$pm_out,'pm_out_assign'=>$pm_out_assign,
												'ot_in'=>NULL,'ot_out'=>NULL,
												'duty_min'=>$dutyHours,'ot_min'=>$otHours,'late_min'=>0,'under_min'=>0,'is_holiday'=>$is_holiday),array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
											}
											else if($rHol['hol_type']=='Special Non-Working Holiday'){
												if($sal_type=='flexible'){//Only monthly wager can avail the paid special holiday
													//insert the original attendance record
													$inserted_etaID = $db->insert('emp_attendance_adjustment',array('eatd_id'=>$eatd_id,'emp_id'=>$emp_id,'eta_date'=>$cDate,'eta_day'=>$dayName,
													'am_in_new'=>$am_in_assign,'am_in_org'=>$am_in,
													'am_out_new'=>$am_out_assign,'am_out_org'=>$am_out,
													'pm_in_new'=>$pm_in_assign,'pm_in_org'=>$pm_in,
													'pm_out_new'=>$pm_out_assign,'pm_out_org'=>$pm_out,
													'ot_in'=>$ot_in,'ot_out'=>$ot_out,
													'duty_min_org'=>$dutyHours,'ot_min_org'=>$otHours,
													'late_min_org'=>$late,'under_min_org'=>$undertime,
													'change_date'=>date('Y-m-d'),'change_time'=>date('H:i:s'),'remarks'=>$rHol['hol_type']));
													$am_in = $am_in_assign;
													$am_out = $am_out_assign;
													$pm_in = $pm_in_assign;
													$pm_out = $pm_out_assign;
													$dutyHours = functions::min_diff($am_in,$cDate,$am_out,$cDate);
													$dutyHours += functions::min_diff($pm_in,$cDate,$pm_out,$cDate);

													$db->update('emp_attendance_adjustment',array('duty_min_new'=>$dutyHours,'ot_min_new'=>$otHours,'late_min_new'=>0,'under_min_new'=>0),array('eta_id'=>$inserted_etaID));
													$is_holiday = ($is_holiday==2) ? 3 : 1;//1-holiday 2-leave 3-both
													//update the new record
													$db->update('emp_attendance_detail',array(
													'am_in'=>$am_in,'am_in_assign'=>$am_in_assign,'am_out'=>$am_out,'am_out_assign'=>$am_out_assign,
													'pm_in'=>$pm_in,'pm_in_assign'=>$pm_in_assign,'pm_out'=>$pm_out,'pm_out_assign'=>$pm_out_assign,
													'ot_in'=>NULL,'ot_out'=>NULL,
													'duty_min'=>$dutyHours,'ot_min'=>$otHours,'late_min'=>0,'under_min'=>0,'is_holiday'=>$is_holiday),array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
												}//End: Only monthly wager can avail the paid special holiday
											}
										}
									endwhile;
								}//End: If regular or Probationary
							}//End: Check if there is a holiday

							//Checking for absent
							$am_absent=0;$pm_absent=0;$am_absent_min=0;$pm_absent_min=0;
							$q_absent = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
							$ra = $db->fetch_array($q_absent);
							absentDay($ra['am_in'],$ra['am_in_assign'],$ra['am_out'],$ra['am_out_assign'],$ra['pm_in'],$ra['pm_in_assign'],$ra['pm_out'],$ra['pm_out_assign'],$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
							$db->update('emp_attendance_detail',array('am_absent'=>$am_absent,'pm_absent'=>$pm_absent,'am_absent_min'=>$am_absent_min,'pm_absent_min'=>$pm_absent_min),array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
							//End checking absent
						}
					endforeach;
				endforeach;
			}//if($totalRow)
		endfor;
		if($countFileUpload){
			$_SESSION['notif_success']='Attendance details Successfully Uploaded!';
			functions::sendTo('attendance_summary_view.php?eatid='.functions::encode($eatid));
			die();
		}
		else{
			functions::say('Please Upload an excel file!');
		}
	}
}
?>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>ATTENDANCE UPLOAD</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<?php if($attendance_detail){?><li><a href="attendance_summary_view.php?eatid=<?php echo functions::encode($eatid)?>">Summary</a></li><?php }?>
				<li class="active"><a href="attendance_report_add_attlog.php?eatid=<?php echo functions::encode($eatid)?>" style="opacity:.9">Upload Att. Log</a></li>
				<li><a href="attendance_report_add_personnel.php?eatid=<?php echo functions::encode($eatid)?>">Personnel</a></li>
				<li><a href="attendance_report_add_charge.php?eatid=<?php echo functions::encode($eatid)?>">Charge To</a></li>
			</ul>
		</div>
		<div class="box-content">
		<?php if($frmSummary){?>
		<div align="right">
			<a id="icnReupload" href="attendance_summary_view.php?eatid=<?php echo functions::encode($eatid)?>" class="btn btn-info" title="Back To Attendance Summary">Back To Attendance Summary</a>&nbsp;
		</div>
		<?php }?>
				<table border="0" width="99%">
					<tr>
						<td align="left" width="12%">Project / Department </td>
						<td valign="middle" height="25px"><div align="left" style="font-weight:bold;"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$proj_id)); ?></div></td>
					</tr>
					<tr>
						<td align="left">Period Cover:</td>
						<td valign="middle" height="25px"><div align="left" style="font-weight:bold;"><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end); echo '&nbsp;&nbsp;&nbsp;&nbsp;('.functions::date_diff($date_start,$date_end,$includeDay1=1).') days'; ?></div></td>
					</tr>
					<tr>
						<td height="25">Worker Type: </td>
						<td><div align="left" style="font-weight:bold;"><?php if($worker_type=="admin"){echo 'Office Personnel';}elseif($worker_type=="labor"){echo 'Labor Group';}else{echo "Undefined";} ?></div></td>
					</tr>
					<?php if($note){?>
					<tr>
						<td height="25">Note: </td>
						<td><div align="left" style="font-weight:bold;"><?php echo $note; ?></div></td>
					</tr>
					<?php }?>
				</table><br>
			<form method="post" enctype="multipart/form-data">
				<div align="center">
					<?php if($personnel){?>
					<table border="0">
						<tr>
							<td style="padding-top: 35px;">
								<div align="left" style="padding-bottom: 15px;">Select Excel file to upload:<input class="input-file uniform_on" type="file" name="file[]" id="fileatt" multiple required></div>
								<div align="center" style="padding-top: 30px;">
									<input type="submit" name="upload" id="upload" value=" UPLOAD " class="btn btn-primary btn-small">
								</div>
							</td>
						</tr>
					</table>
					<?php }
					else{
					?>
						<div>No Specified Personnel, Please choose <a href="attendance_report_add_personnel.php?eatid=<?php echo functions::encode($eatid)?>">personnel</a>.
					<?php }?>
				</div>
			</form>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="../js/jquery.ui.touch-punch.js"></script>
<script src="../js/modernizr.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.cookie.js"></script>
<script src='../js/fullcalendar.min.js'></script>
<script src='../js/jquery.dataTables.min.js'></script>
<script src="../js/excanvas.js"></script>
<script src="../js/jquery.flot.js"></script>
<script src="../js/jquery.flot.pie.js"></script>
<script src="../js/jquery.flot.stack.js"></script>
<script src="../js/jquery.flot.resize.min.js"></script>
<script src="../js/jquery.chosen.min.js"></script>
<script src="../js/jquery.uniform.min.js"></script>
<script src="../js/jquery.cleditor.min.js"></script>
<script src="../js/jquery.noty.js"></script>
<script src="../js/jquery.elfinder.min.js"></script>
<script src="../js/jquery.raty.min.js"></script>
<script src="../js/jquery.iphone.toggle.js"></script>
<script src="../js/jquery.uploadify-3.1.min.js"></script>
<script src="../js/jquery.gritter.min.js"></script>
<script src="../js/jquery.imagesloaded.js"></script>
<script src="../js/jquery.masonry.min.js"></script>
<script src="../js/jquery.knob.modified.js"></script>
<script src="../js/jquery.sparkline.min.js"></script>
<script src="../js/counter.js"></script>
<script src="../js/retina.js"></script>
<script src="../js/customx.js"></script>
<script src="../js/wxh.js"></script>
<script type="text/javascript">
$(window).ready(function(){
	$("#spinner").fadeOut("slow");
	$("#upload").click(function(){
		if($("#fileatt").val()){
			if(confirm('Do you want to upload the attendance log?')){
				$("#spinner").fadeIn();
				return true;
			}else{
				return false;
			}
			//$("#spinner").fadeIn();
		}
	});
});
</script>
<!-- end: JavaScript-->
</body>
</html>