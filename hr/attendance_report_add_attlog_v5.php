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
function diffComp($actualAmIn='',$assignAmIn='',$actualAmOut='',$assignAmOut='',$actualPmIn='',$assignPmIn='',$actualPmOut='',$assignPmOut='',$actualOtIn='',$actualOtOut='',&$late=0,&$undertime=0,&$dutyHours=0,&$otHours=0){
	$cDate = date('Y-m-d');
	$late=$undertime=$dutyHours=0;
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
if( isset($_POST['upload']) ){


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

			//echo '<pre>';print_r($content);echo '</pre>';
			//$totalRow=30;
			$arrAtt=array();
			for($t=1;$t<$totalRow; $t++):
				$emp_no=isset($content[$t][2]) ? trim($content[$t][2]) : '';
				$time=isset($content[$t][3]) ? trim($content[$t][3]) : '';
				$state=isset($content[$t][4]) ? $content[$t][4] : '';
				$validity=isset($content[$t][6]) ? $content[$t][6] : '';
				if($state=='C/In')
					$state='in';
				else if($state=='C/Out')
					$state='out';

				$expDateTime = explode(" ",$time);
				$tmpDate = isset($expDateTime[0]) ? $expDateTime[0] : '';
				$tmpTime = isset($expDateTime[1]) ? $expDateTime[1] : '';
				$tmpAMPM = isset($expDateTime[2]) ? $expDateTime[2] : '';
				$attDay='';
				if($tmpDate){
					$tmpDateExp = explode("/",$tmpDate);
					if(count($tmpDateExp)==3)
					$attDay = $tmpDateExp[2].'-'.$tmpDateExp[1].'-'.$tmpDateExp[0];
				}
				$datein = $attDay;
				$attTime='';
				if($tmpTime){
					$tmpTimeExp = explode(":",$tmpTime);
					if(count($tmpTimeExp)==3){
						$tmpHH = $tmpTimeExp[0];
						$tmpMM = $tmpTimeExp[1];
						$tmpSS = $tmpTimeExp[2];
						if($tmpAMPM=='pm'){
							if($tmpHH==12){
								$tmpHH = '12';
							}
							else if( $tmpHH>=1 && $tmpHH<=11 ){
								$tmpHH = $tmpHH + 12;
							}
							else{
								$tmpHH = '00';
							}
						}
						else{
							$tmpHH = ($tmpHH>=1 && $tmpHH<=9) ? '0'.$tmpHH : $tmpHH;
						}
						$attTime = $tmpHH.':'.$tmpMM.':'.$tmpSS;
					}
				}
				$hrin = $attTime;

				$arrAttTemp[$emp_no][$datein][]=$hrin;
			endfor;
			//echo '<pre>';print_r($arrAttTemp);echo '</pre>';
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
				$dateRangeFrom = $date_start;
				$dateRangeTo = $date_end;
				$yearFrom = substr($dateRangeFrom,0,4);
				$monFrom = substr($dateRangeFrom,5,2);
				$dateFrom = substr($dateRangeFrom,8,2);
				$dateRangeDBFrom = $date_start;
				$dateRangeDBTo = $date_end;

				#initializing dates from start and end dates
				$arrDate=array();
				$dates = functions::date_diff($dateRangeFrom,$dateRangeTo);//Depends on the attlog date
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
				#echo '<pre>';print_r($finalAtt);echo '</pre>';
				$att_org_record='';
				foreach($arrAttTemp as $emp_no => $dateTimeArr):
					$amIn='';$amOut='';$pmIn='';$pmOut='';$dailyDate='';
					if( isset($arrSelEmp[$emp_no]) ){
						unset($arrNotFound[$emp_no]);//remove if from the not found array.
						foreach($arrDateDB as $dateDB):
							$att_org_record = '';
							$amIn = isset($dateTimeArr[$dateDB][0]) ? $dateTimeArr[$dateDB][0] : '';
							$amOut = isset($dateTimeArr[$dateDB][1]) ? $dateTimeArr[$dateDB][1] : '';
							$pmIn = isset($dateTimeArr[$dateDB][2]) ? $dateTimeArr[$dateDB][2] : '';
							$pmOut = isset($dateTimeArr[$dateDB][3]) ? $dateTimeArr[$dateDB][3] : '';
							$att_org_record = $amIn.' '.$amOut.' '.$pmIn.' '.$pmOut;

							#if employee_no is found in the array
							$real_emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$emp_no));
							$otIn = $otInFile = $db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$real_emp_id,'actual_start_date'=>$dateDB));
							$otOut = $otOutFile = $db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$real_emp_id,'actual_start_date'=>$dateDB));
							$arrAttendanceRecord[$emp_no][$dateDB] = array('amin'=>$amIn,'amout'=>$amOut,'pmin'=>$pmIn,'pmout'=>$pmOut,'otin'=>$otIn,'otout'=>$otOut,'att_record'=>$att_org_record);
						endforeach;
					}
				endforeach;

				#assigning empty attendance to not found emp no
				if(count($arrNotFound)){
					foreach($arrNotFound as $eid => $enm):
						foreach($arrDateDB as $dateDB):
							$arrAttendanceRecord[$eid][$dateDB]=array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'','att_record'=>'');
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

				//Computation of Attendance and Other Process
				require_once('attendance_report_engine.php');
			}
			else{
				$countFileUpload=0;
			}
			
		endfor;
		if($countFileUpload){
			$_SESSION['notif_success']='Attendance details Successfully Uploaded!';
			$db->update('emp_attendance',array('upload_done'=>1),array('eat_id'=>$eatid));
			functions::sendTo('attendance_summary_view.php?eatid='.functions::encode($eatid));
			die();
		}
		else{
			functions::say('Please Upload a valid excel file!');
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
				<li class="active"><a href="attendance_report_add_attlog_v2.php?eatid=<?php echo functions::encode($eatid)?>" style="opacity:.9">Upload Att. Log</a></li>
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
								<div align="center" style="padding-bottom: 35px;"><strong>VERSION 5</strong></div>
								<div align="center"><img src="../img/att_v5.jpg" height="200" width="500"></div>
								<div align="left" style="padding-top: 35px;padding-bottom: 15px;">Select Excel file to upload:<input class="input-file uniform_on" type="file" name="file[]" id="fileatt" accept=".xls,.xlsx" multiple required></div>
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
				<div align="right"><a href="attendance_report_add_attlog_option.php?eatid=<?php echo functions::encode($eatid)?>">Back to Attendance Version Option</a></div>
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