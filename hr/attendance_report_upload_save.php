<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
$dateRange = '';
$filename='';
$uploaded_file='';$filetype='';
$arrSelEmp=array();
$arrNotFound=array();
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$proj_id='';
if($eatid){
	$uploaded_file = '../attendance/'.$db->getValue('emp_attendance','excelfile',array('eat_id'=>$eatid));
	$filetype = $db->getValue('emp_attendance','filetype',array('eat_id'=>$eatid));
	$proj_id = $db->getValue('emp_attendance','proj_id',array('eat_id'=>$eatid));
	$dateRange = $db->getValue('emp_attendance','concat(date_start,"~",date_end)',array('eat_id'=>$eatid));
	#$dateRange = '2022-04-11~2022-04-11';
	$db->update('emp_attendance',array('upload_done'=>1),array('eat_id'=>$eatid));
}
if($uploaded_file){
	$qGroupEmpNos = $db->select('emp_attendance_personnel ead, employee e','*',array('eat_id'=>$eatid),'AND ead.emp_id=e.emp_id ORDER BY lname');
	#$qGroupEmpNos = $db->select('emp_attendance_personnel ead, employee e','*',array('eat_id'=>$eatid,'e.emp_id'=>68),'AND ead.emp_id=e.emp_id ORDER BY lname');
	while($rGroupEmpNos = $db->fetch_array($qGroupEmpNos)):
		$arrSelEmp[$rGroupEmpNos['emp_no']] = $rGroupEmpNos['lname'].', '.$rGroupEmpNos['fname'].' '.$rGroupEmpNos['mname'];
	endwhile;
	$arrNotFound=$arrSelEmp;
	$totalRow = 0;
	if($filetype==='xlsx'){
		require_once('../class/read_excel_xlsx.php');
		if ( $xlsx = read_excel_xlsx::parse($uploaded_file) ) {
			$content = $xlsx->rows() ;
			$totalRow = count($content);
		} else {
			echo read_excel_xlsx::parseError();
		}
	}
	if($filetype==='xls'){
		require_once('../class/read_excel_xls.php');
		if ( $xls = read_excel_xls::parse($uploaded_file) ) {
			$content = $xls->rows() ;
			$totalRow = count($content);
		}else{
			echo read_excel_xls::parseError();
		}
	}
	//Saving the record Start
	$arrAttendanceRecord=array();
	$arrAttendanceDate = isset($content[3]) ? $content[3] : array();
	$dateRangeFrom='';$dateRangeTo='';$yearFrom='';$dateFrom='';$monFrom='';

	if($dateRange){
		$dateRangeExp = explode('~',$dateRange);
		$dateRangeFrom = isset($dateRangeExp[0]) ? trim($dateRangeExp[0]) : '';
		$dateRangeTo = isset($dateRangeExp[1]) ? trim($dateRangeExp[1]) : '';
		$yearFrom = substr($dateRangeFrom,0,4);
		$monFrom = substr($dateRangeFrom,5,2);
		$dateFrom = substr($dateRangeFrom,8,2);
	}

	$arrDate=array();
	$dates = functions::date_diff($dateRangeFrom,$dateRangeTo);
	#$dates=1;
	if($dates>=0){
		for($i=0;$i<=$dates;$i++):
			$succeeding_date = functions::AddDay($dateRangeFrom,$i);
			$daysDate = date('d', strtotime($succeeding_date));
			$arrDate[]=$daysDate;
		endfor;
	}    
	$emp_no=''; $emp_name='';
	#echo '<pre>';print_r($arrDate);echo '</pre>';
	if($totalRow){//has Rows
		for($t=4;$t<$totalRow; $t++):
			if( ($t%2)==0 ){
				$emp_no=isset($content[$t][2]) ? $content[$t][2] : '';
				$emp_name=isset($content[$t][10]) ? $content[$t][10] : '';

				if( isset($arrSelEmp[$emp_no]) ){
					unset($arrNotFound[$emp_no]);
					$countAttendance=0;
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
								$arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'','att_record'=>'');
							}
							else{
								$arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'','att_record'=>'');
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
								$arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'','att_record'=>'');
							}
						endforeach;
					}
				}
			}

			if( ($t%2)==1 ){
				if( isset($arrSelEmp[$emp_no]) ){
					$arrAttendanceDaily = isset($content[$t]) ? $content[$t] : array();
					foreach($arrDate as $dailyIndx => $dailyVal):
						$amIn='';$amOut='';$pmIn='';$pmOut='';$otIn='';$otOut='';
						if( isset($arrAttendanceDaily[$dailyIndx]) && $arrAttendanceDaily[$dailyIndx]){
							$tme = $arrAttendanceDaily[$dailyIndx];
							if($tme){
								$amIn='';$amOut='';$pmIn='';$pmOut='';$otIn='';$otOut='';$att_record='';
								$arrRec=array();
								for($x=0; $x<50; $x+=5):
									$timePerDay = substr($tme,$x,5);
									if($timePerDay){
										$arrRec[$timePerDay]=$timePerDay;
									}
								endfor;
								foreach($arrRec as $timeDay):
									if($timeDay){
										if($amIn=="")
											$amIn=$timeDay;
										elseif($amOut=='')
											$amOut=$timeDay;
										elseif($pmIn=='')
											$pmIn=$timeDay;
										elseif($pmOut=='')
											$pmOut=$timeDay;
										elseif($otIn=='')
											$otIn=$timeDay;
										elseif($otOut=='')
											$otOut=$timeDay;
										$att_record .= $timeDay.' ';
									}
								endforeach;
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
								#$otIn = ($otInFile) ? $otInFile : $otIn;
								#$otOut = ($otOutFile) ? $otOutFile : $otOut;
								$arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>$amIn,'amout'=>$amOut,'pmin'=>$pmIn,'pmout'=>$pmOut,'otin'=>$otIn,'otout'=>$otOut,'att_record'=>$att_record);
							}
						}
					endforeach;
				}//Registered Emp Number
			}
		endfor;
	}//has Rows

	#echo '<pre>';print_r($arrNotFound);echo '</pre>';
	#echo '<pre>';print_r($arrDate);echo '</pre>';
	if(count($arrNotFound)){
		foreach($arrNotFound as $eid => $enm):
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
					$arrAttendanceRecord[$eid][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'','att_record'=>'');
				}
			endforeach;
		endforeach;
	}

	function dayName($date=''){
		#$date = '2014-02-25';
		if($date)
			return date('l', strtotime($date));
		else
			return '';
	}

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

	function diffComp($actualAmIn='',$assignAmIn='',$actualAmOut='',$assignAmOut='',$actualPmIn='',$assignPmIn='',$actualPmOut='',$assignPmOut='',$actualOtIn=0,$actualOtOut=0,&$late=0,&$undertime=0,&$dutyHours=0,&$otHours=0){
		$cDate = date('Y-m-d');
		$late=$undertime=$dutyHours=0;
		if( $assignAmIn && $assignAmOut )//compute the required duty mins for morning
			$dutyHours += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
		if( $assignPmIn && $assignPmOut )//compute the required duty mins for afternoon
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

		if( $assignAmIn && $assignAmOut ){//If there is required time in and out
			//Count Minutes LATE
			if($actualAmIn){//If there is time in
				if( strtotime($actualAmIn) > strtotime($assignAmIn) &&  strtotime($actualAmIn) <= strtotime($assignAmOut) ){//Actual in is between assign in and out
					$late += functions::min_diff($assignAmIn,$cDate,$actualAmIn,$cDate);//Minutes from assign in to actual in
				}
				else if( strtotime($actualAmIn) > strtotime($assignAmOut) ){ // Actual in is beyond assign out
					$late += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);//Count the required minutes for morning duty.
				}
			}

			//Count Minutes UNDERTIME
			if($actualAmOut){//If there is morning time out
				if( strtotime($actualAmOut) < strtotime($assignAmOut) && strtotime($actualAmOut) > strtotime($assignAmIn) ){//Actual out is between assign in and out
					$undertime += functions::min_diff($actualAmOut,$cDate,$assignAmOut,$cDate);//Minutes from Actual Out to Assign Out.
				}
				else if( strtotime($actualAmOut) < strtotime($assignAmIn) ){//Actual out is less then assign in
					$undertime += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);//Count the required minutes for morning duty.
				}
			}
			else if( $actualAmIn ){//if there is no morning out but there is morning time in
				if( strtotime($actualAmIn) > strtotime($assignAmOut) ){//if time in is greater than assign in
					//Do nothing It's already been deducted by late.
				}
				else if( strtotime($actualAmIn) > strtotime($assignAmIn) && strtotime($actualAmIn) < strtotime($assignAmOut)){//Actual in is between assign in and out
					$undertime += functions::min_diff($actualAmIn,$cDate,$assignAmOut,$cDate);
				}
				else{//if time in is before morning assign in and after morning assign out
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

	$txSalMonth=0;$txSalDay=0;$txSalHour=0;$txSalMin=0;
	foreach($arrAttendanceRecord as $employee => $calDate):
		$monAmIn='';$monAmOut='';$monPmIn='';$monPmOut='';$tueAmIn='';$tueAmOut='';$tuePmIn='';$tuePmOut='';$wedAmIn='';$wedAmOut='';$wedPmIn='';$wedPmOut='';$thuAmIn='';$thuAmOut='';$thuPmIn='';$thuPmOut='';$friAmIn='';$friAmOut='';$friPmIn='';$friPmOut='';$satAmIn='';$satAmOut='';$satPmIn='';$satPmOut='';$sunAmIn='';$sunAmOut='';$sunPmIn='';$sunPmOut='';$am_in='';$am_out='';$pm_in='';$pm_out='';
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
			if($rTI['eti_day']=="Sunday"){
				$sunAmIn=$rTI['am_in'];$sunAmOut=$rTI['am_out'];$sunPmIn=$rTI['pm_in'];$sunPmOut=$rTI['pm_out'];
			}
		endwhile;
		foreach($calDate as $cDate => $logins):
			$am_in='';$am_out='';$pm_in='';$pm_out='';$att_record=$logins['att_record'];
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
			if($dayName=="Sunday"){
				diffComp($actualAmIn,$sunAmIn,$actualAmOut,$sunAmOut,$actualPmIn,$sunPmIn,$actualPmOut,$sunPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
				$am_in_assign = (isset($sunAmIn) && !empty($sunAmIn)) ? $sunAmIn : NULL;
				$am_out_assign = (isset($sunAmOut) && !empty($sunAmOut)) ? $sunAmOut : NULL;
				$pm_in_assign = (isset($sunPmIn) && !empty($sunPmIn)) ? $sunPmIn : NULL;
				$pm_out_assign = (isset($sunPmOut) && !empty($sunPmOut)) ? $sunPmOut : NULL;
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
			$duty_min_new=$under_min_new=$late_min_new=0;
			if($eatd_id){//has attendance record
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
						$exit=0;
						$fDate=$cDate;
						$prev=0;
						$holidayCreditAllowed=0;
						//backwarding to find out if there's no absent prior to this holiday
						while($exit==0):
							$prev++;
							$month=date('m',strtotime($fDate));
							$day=date('d',strtotime($fDate));
							$year=date('Y',strtotime($fDate));
							$fDate = date('Y-m-d',mktime(0,0,0,$month,$day - $prev,$year));
							$dayNumber = date('N',strtotime($fDate));
							if( isset($arrHoliday[$fDate]) ){
								//if yesterday is holiday, proceed to backwarding
							}
							else if( $db->getValue('emp_timein','count(*)',array('emp_id'=>$emp_id,'day_no'=>$dayNumber)) ){// if has a regular duty
								//Check if there's a leave life yesterday
								if( isset($arrEmpLeaves[$fDate]) ){
									$lf_id = $db->getValue('leave_file_detail','lf_id',array('lfd_id'=>$arrEmpLeaves[$fDate]));
									$with_pay = $db->getValue('leave_config lc, leave_file lf','count(*)',array('lf_id'=>$lf_id,'with_pay'=>1),'AND lf.lc_id=lc.lc_id');
									if($with_pay){//Check if it is leave with pay
										$holidayCreditAllowed=1;
									}
								}
								else{
									$yesterdayAttQ = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'eat_date'=>$fDate));
									$rya = $db->fetch_array($yesterdayAttQ);
									$attCount=0;
									if($rya['am_in'])
										$attCount++;
									if($rya['am_out'])
										$attCount++;
									if($rya['pm_in'])
										$attCount++;
									if($rya['pm_out'])
										$attCount++;
									if($attCount >=2 ){
										$holidayCreditAllowed=1;
									}
								}
								$exit=1;
							}
							if($prev>15)
								break;
						endwhile;

						//If allowed to have the holiday credit
						if( $holidayCreditAllowed ){
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
										if( $db->getValue('emp_attendance_adjustment','count(*)',array('emp_id'=>$emp_id,'eta_date'=>$cDate,'remarks'=>$rHol['hol_type']))==0 ){
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
										}
										$is_holiday=1;
										diffComp($am_in_assign,$am_in_assign,$am_out_assign,$am_out_assign,$pm_in_assign,$pm_in_assign,$pm_out_assign,$pm_out_assign,$ot_in,$ot_out,$late,$undertime,$dutyHours,$otHours);
										$arrUpdateAtt=array('am_in'=>NULL,'am_in_assign'=>$am_in_assign,'am_out'=>NULL,'am_out_assign'=>$am_out_assign,'pm_in'=>NULL,'pm_in_assign'=>$pm_in_assign,'pm_out'=>NULL,'pm_out_assign'=>$pm_out_assign,'ot_in'=>$ot_in,'ot_out'=>$ot_out,'duty_min'=>$dutyHours,'ot_min'=>$otHours,'late_min'=>$late,'under_min'=>$undertime,'is_holiday'=>$is_holiday);
										$db->update('emp_attendance_detail',$arrUpdateAtt,array('emp_id'=>$emp_id,'eatd_id'=>$eatd_id,'eat_date'=>$cDate));
									}
									else if($rHol['hol_type']=='Special Non-Working Holiday'){
										if($sal_type=='flexible'){//Only monthly wager can avail the paid special holiday
											//check if there is an adjustment already of this kind of holiday
											if( $db->getValue('emp_attendance_adjustment','count(*)',array('emp_id'=>$emp_id,'eta_date'=>$cDate,'remarks'=>$rHol['hol_type']))==0 ){
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
											}
											$is_holiday=1;
											diffComp($am_in_assign,$am_in_assign,$am_out_assign,$am_out_assign,$pm_in_assign,$pm_in_assign,$pm_out_assign,$pm_out_assign,$ot_in,$ot_out,$late,$undertime,$dutyHours,$otHours);
											$arrUpdateAtt=array('am_in'=>NULL,'am_in_assign'=>$am_in_assign,'am_out'=>NULL,'am_out_assign'=>$am_out_assign,'pm_in'=>NULL,'pm_in_assign'=>$pm_in_assign,'pm_out'=>NULL,'pm_out_assign'=>$pm_out_assign,'ot_in'=>$ot_in,'ot_out'=>$ot_out,'duty_min'=>$dutyHours,'ot_min'=>$otHours,'late_min'=>$late,'under_min'=>$undertime,'is_holiday'=>$is_holiday);
											$db->update('emp_attendance_detail',$arrUpdateAtt,array('emp_id'=>$emp_id,'eatd_id'=>$eatd_id,'eat_date'=>$cDate));
										}//End: Only monthly wager can avail the paid special holiday
									}
								}
							endwhile;
						}//End: If allowed to have the holiday credit
					}//End: If regular or Probationary
				}//End: Check if there is a holiday

				//***********LEAVE CHECK**************
				//Check if there is a leave file
				#if( $lfd_id = $db->getValue('leave_file_detail','lfd_id',array('emp_id'=>$emp_id,'lfd_date'=>$cDate)) ){

				if( isset($arrEmpLeaves[$cDate]) ){
					$am_absent_min=$pm_absent_min=$am_absent=$pm_absent=0;
					$lfd_id = $arrEmpLeaves[$cDate];
					$qLv = $db->select('leave_file_detail','*',array('lfd_id'=>$lfd_id));
					$rLv = $db->fetch_array($qLv);
					$hasAmLeave = $rLv['time_am'];
					$hasPmLeave = $rLv['time_pm'];
					$leave_name = $db->getValue('leave_config lc, leave_file lf','leave_name',array('lf_id'=>$rLv['lf_id']),'AND lf.lc_id=lc.lc_id');
					$leave_reason = $db->getValue('leave_file lf, leave_file_detail lfd','reason',array('lfd_id'=>$rLv['lfd_id']),'AND lf.lf_id=lfd.lf_id');
					$remarks = $leave_name.' : '.$leave_reason;
					$leave_with_pay = $db->getValue('leave_config lc, leave_file lf','with_pay',array('lf_id'=>$rLv['lf_id']),'AND lf.lc_id=lc.lc_id');

					$arrFieldInsert = array('eatd_id'=>$eatd_id,'emp_id'=>$emp_id,'eta_date'=>$cDate,'eta_day'=>$dayName,'am_in_org'=>$am_in,'am_out_org'=>$am_out,'pm_in_org'=>$pm_in,'pm_out_org'=>$pm_out,'ot_in'=>$ot_in,'ot_out'=>$ot_out,'duty_min_org'=>$dutyHours,'ot_min_org'=>$otHours,'late_min_org'=>$late,'under_min_org'=>$undertime,'change_date'=>date('Y-m-d'),'change_time'=>date('H:i:s'),'remarks'=>$remarks,'refer_id'=>$lfd_id);

					$am_in_new = $am_in;
					$am_out_new = $am_out;
					$pm_in_new = $pm_in;
					$pm_out_new = $pm_out;
					if($leave_with_pay==0){
						if($hasAmLeave && $hasPmLeave){//Whole day leave, null the actual in/out, zero the duty/under/late mins, except the OT
							$am_in_new=$am_out_new=$pm_in_new=$pm_out_new=NULL;
							diffComp($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in_new,$pm_in_assign,$pm_out_new,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
							absentDay($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in_new,$pm_in_assign,$pm_out_new,$pm_out_assign,$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
						}
						else if($hasAmLeave){//Morning only leave, make assigned in/out as actual am in/out so that no minutes deductions in morning
							$am_in_new=$am_out_new=NULL;
							diffComp($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in,$pm_in_assign,$pm_out,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
							absentDay($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in,$pm_in_assign,$pm_out,$pm_out_assign,$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
							$pm_in_new=$pm_in;$pm_out_new=$pm_out;
						}
						else if($hasPmLeave){//Afternoon only leave, make assigned in/out as actual am in/out so that no minutes deductions in morning
							$pm_in_new=$pm_out_new=NULL;
							diffComp($am_in,$am_in_assign,$am_out,$am_out_assign,$pm_in_new,$pm_in_assign,$pm_out_new,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
							absentDay($am_in,$am_in_assign,$am_out,$am_out_assign,$pm_in_new,$pm_in_assign,$pm_out_new,$pm_out_assign,$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
							$am_in_new=$am_in;$am_out_new=$am_out;
						}
					}
					else if($leave_with_pay){
						if($hasAmLeave && $hasPmLeave){//Whole day leave, null the actual in/out, zero the duty/under/late mins, except the OT
							diffComp($am_in_assign,$am_in_assign,$am_out_assign,$am_out_assign,$pm_in_assign,$pm_in_assign,$pm_out_assign,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
							$am_in_new=$am_out_new=$pm_in_new=$pm_out_new=NULL;
						}
						else if($hasAmLeave){//Morning only leave, make assigned in/out as actual am in/out so that no minutes deductions in morning
							diffComp($am_in_assign,$am_in_assign,$am_out_assign,$am_out_assign,$pm_in,$pm_in_assign,$pm_out,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
							$am_in_new=$am_out_new=NULL;
							absentDay($am_in_assign,$am_in_assign,$am_out_assign,$am_out_assign,$pm_in,$pm_in_assign,$pm_out,$pm_out_assign,$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
							$pm_in_new=$pm_in;$pm_out_new=$pm_out;
						}
						else if($hasPmLeave){//Afternoon only leave, make assigned in/out as actual am in/out so that no minutes deductions in morning
							diffComp($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in_assign,$pm_in_assign,$pm_out_assign,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
							$pm_in_new=$pm_out_new=NULL;
							absentDay($am_in,$am_in_assign,$am_out,$am_out_assign,$pm_in_assign,$pm_in_assign,$pm_out_assign,$pm_out_assign,$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
							$am_in_new=$am_in;$am_out_new=$am_out;
						}
					}

					//Check if there is adjustments already with this kind of leave
					$qAttAdj = $db->select('emp_attendance_adjustment','*',array('emp_id'=>$emp_id,'eta_date'=>$cDate,'refer_id'=>$lfd_id));
					$rAA = $db->fetch_array($qAttAdj);
					$eta_id = $rAA['eta_id'] ?? NULL;
					if($eta_id){
						$arrFieldUpdate = array('am_in_new'=>$am_in_new,'am_out_new'=>$am_out_new,'pm_in_new'=>$pm_in_new,'pm_out_new'=>$pm_out_new,'duty_min_new'=>$dutyHours,'ot_min_new'=>$ot_min_new,'late_min_new'=>$late_min_new,'under_min_new'=>$under_min_new);
						$db->update('emp_attendance_adjustment',$arrFieldUpdate,array('eta_id'=>$eta_id));
					}
					else{
					#if( $db->getValue('emp_attendance_adjustment','count(*)',array('emp_id'=>$emp_id,'eta_date'=>$cDate,'refer_id'=>$lfd_id))==0 ){
						//If there is none, then add adjustment for this leave
						//insert the original attendance record
						$inserted_etaID = $db->insert('emp_attendance_adjustment',$arrFieldInsert);
						#$db->insert('emp_attendance_adjustment',$arrField);
						#echo '<br>Add Adjustemnt: '.$db->last_query.'<br>';
					}
					//update the new record
					$is_holiday = ($is_holiday==1) ? 3 : 2;//1-holiday 2-leave 3-both
					$db->update('emp_attendance_detail',array(
					'am_in'=>$am_in_new,'am_in_assign'=>$am_in_assign,'am_out'=>$am_out_new,'am_out_assign'=>$am_out_assign,
					'pm_in'=>$pm_in_new,'pm_in_assign'=>$pm_in_assign,'pm_out'=>$pm_out_new,'pm_out_assign'=>$pm_out_assign,
					'ot_in'=>$ot_in,'ot_out'=>$ot_out,'am_absent'=>$am_absent,'am_absent_min'=>$am_absent_min,'pm_absent'=>$pm_absent,'pm_absent_min'=>$pm_absent_min,
					'duty_min'=>$duty_min_new,'ot_min'=>$otHours,'late_min'=>$late_min_new,'under_min'=>$under_min_new,'is_holiday'=>$is_holiday),array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
				}
				//End: Check if there is a leave file

				//Checking for absent
				$am_absent=0;$pm_absent=0;$am_absent_min=0;$pm_absent_min=0;
				$q_absent = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id,'is_holiday'=>NULL));
				$ra = $db->fetch_array($q_absent);
				if( isset($ra['eatd_id']) ){
					absentDay($ra['am_in'],$ra['am_in_assign'],$ra['am_out'],$ra['am_out_assign'],$ra['pm_in'],$ra['pm_in_assign'],$ra['pm_out'],$ra['pm_out_assign'],$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
					$db->update('emp_attendance_detail',array('am_absent'=>$am_absent,'pm_absent'=>$pm_absent,'am_absent_min'=>$am_absent_min,'pm_absent_min'=>$pm_absent_min),array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
					#echo $db->last_query.'<br>';
				}
				//End checking absent
			}//has already attendance record
			else{
				#insert new record
				$eatd_id = $db->insert('emp_attendance_detail',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'eat_date'=>$cDate,'eat_day'=>$dayName,
				'am_in'=>$am_in,'am_in_assign'=>$am_in_assign,'am_out'=>$am_out,'am_out_assign'=>$am_out_assign,
				'pm_in'=>$pm_in,'pm_in_assign'=>$pm_in_assign,'pm_out'=>$pm_out,'pm_out_assign'=>$pm_out_assign,
				'ot_in'=>$ot_in,'ot_out'=>$ot_out,
				'duty_min'=>$dutyHours,'ot_min'=>$otHours,'late_min'=>$late,'under_min'=>$undertime,'att_record'=>$att_record,'is_holiday'=>$is_holiday));
				#echo '<br>Inserted '.$db->last_query.'<br>';
				#echo '<br>';

				//Check if there is a leave filed
				#echo $db->getValue('leave_file_detail','lfd_id',array('emp_id'=>$emp_id,'lfd_date'=>$cDate));
				#echo '<br>Leave Check: '.$db->last_query;
				#echo '<br>';
				//***********LEAVE CHECK**************
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


					if($leave_with_pay==0){
						if($hasAmLeave && $hasPmLeave){//Whole day leave, null the actual in/out, zero the duty/under/late mins, except the OT
							$am_in_new=$am_out_new=$pm_in_new=$pm_out_new=NULL;
							diffComp($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in_new,$pm_in_assign,$pm_out_new,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
							absentDay($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in_new,$pm_in_assign,$pm_out_new,$pm_out_assign,$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
						}
						else if($hasAmLeave){//Morning only leave, make assigned in/out as actual am in/out so that no minutes deductions in morning
							$am_in_new=$am_out_new==NULL;
							diffComp($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in,$pm_in_assign,$pm_out,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
							absentDay($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in,$pm_in_assign,$pm_out,$pm_out_assign,$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
						}
						else if($hasPmLeave){//Afternoon only leave, make assigned in/out as actual am in/out so that no minutes deductions in morning
							$pm_in_new=$pm_out_new==NULL;
							diffComp($am_in,$am_in_assign,$am_out,$am_out_assign,$pm_in_new,$pm_in_assign,$pm_out_new,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
							$pm_in_new=$pm_out_new=NULL;
							absentDay($am_in,$am_in_assign,$am_out,$am_out_assign,$pm_in_new,$pm_in_assign,$pm_out_new,$pm_out_assign,$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
						}
					}
					else if($leave_with_pay){
						if($hasAmLeave && $hasPmLeave){//Whole day leave, null the actual in/out, zero the duty/under/late mins, except the OT
							diffComp($am_in_assign,$am_in_assign,$am_out_assign,$am_out_assign,$pm_in_assign,$pm_in_assign,$pm_out_assign,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
							$am_in_new=$am_out_new=$pm_in_new=$pm_out_new=NULL;
						}
						else if($hasAmLeave){//Morning only leave, make assigned in/out as actual am in/out so that no minutes deductions in morning
							diffComp($am_in_assign,$am_in_assign,$am_out_assign,$am_out_assign,$pm_in,$pm_in_assign,$pm_out,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
							$am_in_new=$am_out_new=NULL;
							absentDay($am_in_assign,$am_in_assign,$am_out_assign,$am_out_assign,$pm_in,$pm_in_assign,$pm_out,$pm_out_assign,$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
						}
						else if($hasPmLeave){//Afternoon only leave, make assigned in/out as actual am in/out so that no minutes deductions in morning
							diffComp($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in_assign,$pm_in_assign,$pm_out_assign,$pm_out_assign,$ot_in,$ot_out,$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
							$pm_in_new=$pm_out_new=NULL;
							absentDay($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in_assign,$pm_in_assign,$pm_out_assign,$pm_out_assign,$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
						}
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
					'ot_in'=>$ot_in,'ot_out'=>$ot_out,'am_absent'=>$am_absent,'am_absent_min'=>$am_absent_min,'pm_absent'=>$pm_absent,'pm_absent_min'=>$pm_absent_min,
					'duty_min'=>$duty_min_new,'ot_min'=>$otHours,'late_min'=>$late_min_new,'under_min'=>$under_min_new,'is_holiday'=>$is_holiday),array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
					#echo '<br>Change Leave: '.$db->last_query.'<br>';
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
						$exit=0;
						$fDate=$cDate;
						$prev=0;
						$holidayCreditAllowed=0;
						//backwarding to find out if there's no absent prior to this holiday
						while($exit==0):
							$prev++;
							$month=date('m',strtotime($fDate));
							$day=date('d',strtotime($fDate));
							$year=date('Y',strtotime($fDate));
							$fDate = date('Y-m-d',mktime(0,0,0,$month,$day - $prev,$year));
							$dayNumber = date('N',strtotime($fDate));
							if( isset($arrHoliday[$fDate]) ){
								//if yesterday is holiday, proceed to backwarding
							}
							else if( $db->getValue('emp_timein','count(*)',array('emp_id'=>$emp_id,'day_no'=>$dayNumber)) ){// if has a regular duty
								//Check if there's a leave life yesterday
								if( isset($arrEmpLeaves[$fDate]) ){
									$lf_id = $db->getValue('leave_file_detail','lf_id',array('lfd_id'=>$arrEmpLeaves[$fDate]));
									$with_pay = $db->getValue('leave_config lc, leave_file lf','count(*)',array('lf_id'=>$lf_id,'with_pay'=>1),'AND lf.lc_id=lc.lc_id');
									if($with_pay){//Check if it is leave with pay
										$holidayCreditAllowed=1;
									}
								}
								else{
									$yesterdayAttQ = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'eat_date'=>$fDate));
									$rya = $db->fetch_array($yesterdayAttQ);
									$attCount=0;
									if($rya['am_in'])
										$attCount++;
									if($rya['am_out'])
										$attCount++;
									if($rya['pm_in'])
										$attCount++;
									if($rya['pm_out'])
										$attCount++;
									if($attCount >=2 ){
										$holidayCreditAllowed=1;
									}
								}
								$exit=1;
							}
						endwhile;
						//If allowed to have the holiday credit
						if( $holidayCreditAllowed ){

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

										diffComp($am_in_assign,$am_in_assign,$am_out_assign,$am_out_assign,$pm_in_assign,$pm_in_assign,$pm_out_assign,$pm_out_assign,$ot_in,$ot_out,$late,$undertime,$dutyHours,$otHours);
										$arrUpdateAtt=array('am_in'=>NULL,'am_in_assign'=>$am_in_assign,'am_out'=>NULL,'am_out_assign'=>$am_out_assign,'pm_in'=>NULL,'pm_in_assign'=>$pm_in_assign,'pm_out'=>NULL,'pm_out_assign'=>$pm_out_assign,'ot_in'=>$ot_in,'ot_out'=>$ot_out,'duty_min'=>$dutyHours,'ot_min'=>$otHours,'late_min'=>$late,'under_min'=>$undertime,'is_holiday'=>$is_holiday);
										$db->update('emp_attendance_detail',$arrUpdateAtt,array('emp_id'=>$emp_id,'eatd_id'=>$eatd_id,'eat_date'=>$cDate));
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

											diffComp($am_in_assign,$am_in_assign,$am_out_assign,$am_out_assign,$pm_in_assign,$pm_in_assign,$pm_out_assign,$pm_out_assign,$ot_in,$ot_out,$late,$undertime,$dutyHours,$otHours);
											$arrUpdateAtt=array('am_in'=>NULL,'am_in_assign'=>$am_in_assign,'am_out'=>NULL,'am_out_assign'=>$am_out_assign,'pm_in'=>NULL,'pm_in_assign'=>$pm_in_assign,'pm_out'=>NULL,'pm_out_assign'=>$pm_out_assign,'ot_in'=>$ot_in,'ot_out'=>$ot_out,'duty_min'=>$dutyHours,'ot_min'=>$otHours,'late_min'=>$late,'under_min'=>$undertime,'is_holiday'=>$is_holiday);
											$db->update('emp_attendance_detail',$arrUpdateAtt,array('emp_id'=>$emp_id,'eatd_id'=>$eatd_id,'eat_date'=>$cDate));
										}//End: Only monthly wager can avail the paid special holiday
									}
								}
							endwhile;
						}//End: If allowed to have the holiday credit
					}//End: If regular or Probationary
				}//End: Check if there is a holiday
				//***********END: HOLIDAY CHECK**************

				//Checking for absent
				$am_absent=0;$pm_absent=0;$am_absent_min=0;$pm_absent_min=0;
				$q_absent = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id,'is_holiday'=>NULL));
				$ra = $db->fetch_array($q_absent);
				if( isset($ra['eatd_id']) ){
					absentDay($ra['am_in'],$ra['am_in_assign'],$ra['am_out'],$ra['am_out_assign'],$ra['pm_in'],$ra['pm_in_assign'],$ra['pm_out'],$ra['pm_out_assign'],$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
					$db->update('emp_attendance_detail',array('am_absent'=>$am_absent,'pm_absent'=>$pm_absent,'am_absent_min'=>$am_absent_min,'pm_absent_min'=>$pm_absent_min),array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
					#echo '<br>Last Update: '.$db->last_query.'<br>';
				}
				//End checking absent
			}
		endforeach;
	endforeach;
	functions::sendTo('attendance_summary_view.php?eatid='.functions::encode($eatid));
	die();
// End of saving the attendance record
}
?>