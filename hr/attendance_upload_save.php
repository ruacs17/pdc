<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));

$filename='';
$uploaded_file='';$filetype='';

$attendance_file = (isset($_REQUEST['fn']) && !empty($_REQUEST['fn']) ) ? functions::decode($_REQUEST['fn']) : '';
if($attendance_file){
    $arrRequest = unserialize($attendance_file);
    $uploaded_file = isset($arrRequest['filename']) ? $arrRequest['filename'] : '';
    $filetype = isset($arrRequest['filetype']) ? $arrRequest['filetype'] : '';
}
#$uploaded_file='../attendance/20190703.xlsx';
#$filetype='xlsx';
if($uploaded_file){
    $qEmpNos = $db->select('employee','*',array(),'WHERE emp_no !="" ');
    while($rEmpNos = $db->fetch_array($qEmpNos)):
        $arrSelEmp[$rEmpNos['emp_no']] = $rEmpNos['emp_no'];
    endwhile;
    #$arrSelEmp['880073'] = '880073';
    $totalRow = 0;
    if($filetype==='xlsx'){
        require_once('../class/read_excel_xlsx.php');
        if ( $xlsx = read_excel_xlsx::parse($uploaded_file) ) {
            $content = $xlsx->rows() ;
        } else {
            echo read_excel_xlsx::parseError();
        }
        $totalRow = count($content);
    }
    if($filetype==='xls'){
        require_once('../class/read_excel_xls.php');
        if ( $xls = read_excel_xls::parse($uploaded_file) ) {
            $content = $xls->rows() ;
        } else {
            echo read_excel_xls::parseError();
        }
        $totalRow = count($content);
    }
    $arrDataParam = array('filename'=>$uploaded_file,'filetype'=>$filetype);
//Saving the record Start
    $arrAttendanceRecord=array();
    $arrAttendanceDate = isset($content[3]) ? $content[3] : array();
    $dateRange = isset($content[2][2]) ? $content[2][2] : '';
    $dateRangeFrom='';$dateRangeTo='';$yearFrom='';$dateFrom='';$monFrom='';
    if($dateRange){
        $dateRangeExp = explode('~',$dateRange);
        $dateRangeFrom = isset($dateRangeExp[0]) ? trim($dateRangeExp[0]) : '';
        $dateRangeTo = isset($dateRangeExp[1]) ? trim($dateRangeExp[1]) : '';
        $yearFrom = substr($dateRangeFrom,0,4);
        $monFrom = substr($dateRangeFrom,5,2);
        $dateFrom = substr($dateRangeFrom,8,2);
    }
    $arrDataParam = array_merge(array('date_from'=>$dateRangeFrom,'date_to'=>$dateRangeTo),$arrDataParam);
    $arrDate=array();
    foreach($arrAttendanceDate as $aAindx => $aAVal):
        $arrDate[$aAindx]=$aAVal;
    endforeach;
    $emp_no=''; $emp_name='';
    #echo '<pre>';print_r($arrDate);echo '</pre>';
    $arrEmpNos = array();

    if($totalRow){//has Rows
        for($t=4;$t<$totalRow; $t++):
            if( ($t%2)==0 ){
                $emp_no=isset($content[$t][2]) ? $content[$t][2] : '';
                $emp_name=isset($content[$t][10]) ? $content[$t][10] : '';

                if( isset($arrSelEmp[$emp_no]) ){
                    $arrEmpNos['empID'][$emp_no]=$db->getValue('employee','lname',array('emp_no'=>$emp_no));
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
                                $arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'');
                            }
                            else{
                                $arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'');
                            }
                        }
                    endforeach;
                    if($countAttendance==0){//No attendance in excel
                        foreach($arrDate as $dailyIndx => $dailyVal):
                            $monFrom = (strlen($monFrom)==1) ? '0'.$monFrom : $monFrom;
                            $dailyVal = (strlen($dailyVal)==1) ? '0'.$dailyVal : $dailyVal;
                            if($dateFrom <= $dailyVal)
                                $dailyDate = $yearFrom.'-'.$monFrom.'-'.$dailyVal;
                            else{
                                $monFromA = (strlen($monFrom+1)==1) ? '0'.($monFrom+1) : ($monFrom+1);
                                $dailyDate = $yearFrom.'-'.$monFromA.'-'.$dailyVal;
                            }
                            $arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'');
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
                                #$otIn = ($otInFile) ? $otInFile : $otIn;
                                #$otOut = ($otOutFile) ? $otOutFile : $otOut;
                                $arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>$amIn,'amout'=>$amOut,'pmin'=>$pmIn,'pmout'=>$pmOut,'otin'=>$otIn,'otout'=>$otOut);
                            }
                        }
                    endforeach;
                }//Registered Emp Number
            }
        endfor;

    }//has Rows
    if( isset($arrEmpNos['empID']) && count($arrEmpNos['empID']) )
        asort($arrEmpNos['empID']);
    #print_r($arrEmpNos['empID']);
    $arrDataParam = array_merge($arrEmpNos,$arrDataParam);
    #echo '<pre>';print_r($arrAttendanceRecord);echo '</pre>';
    #die();
    function dayName($date=''){
        #$date = '2014-02-25';
        if($date)
            return date('l', strtotime($date));
        else
            return '';
    }

    function diffComp($actualAmIn='',$assignAmIn='',$actualAmOut='',$assignAmOut='',
            $actualPmIn='',$assignPmIn='',$actualPmOut='',$assignPmOut='',$actualOtIn,$actualOtOut,&$late=0,&$undertime=0,&$dutyHours=0,&$otHours=0){
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
    $totalDutyHours=0;$txSalMonth=0;$txSalDay=0;$txSalHour=0;$txSalMin=0;
    foreach($arrAttendanceRecord as $employee => $calDate):
        $totalDutyHours=0;
        $monAmIn='';$monAmOut='';$monPmIn='';$monPmOut='';$tueAmIn='';$tueAmOut='';$tuePmIn='';$tuePmOut='';$wedAmIn='';$wedAmOut='';$wedPmIn='';$wedPmOut='';$thuAmIn='';$thuAmOut='';$thuPmIn='';$thuPmOut='';$friAmIn='';$friAmOut='';$friPmIn='';$friPmOut='';$satAmIn='';$satAmOut='';$satPmIn='';$satPmOut='';
        $am_in='';$am_out='';$pm_in='';$pm_out='';
        $emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$employee));

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
            $late=0; $undertime=0;$dutyHours=0;$otHours=0;$totalDutyHours=0;
            $am_in_assign=NULL;$am_out_assign=NULL;$pm_in_assign=NULL;$pm_out_assign=NULL;

            if($dayName=="Monday"){
                diffComp($logins['amin'],$monAmIn,$logins['amout'],$monAmOut,$logins['pmin'],$monPmIn,$logins['pmout'],$monPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
                $am_in_assign = (isset($monAmIn) && !empty($monAmIn)) ? $monAmIn : NULL;
                $am_out_assign = (isset($monAmOut) && !empty($monAmOut)) ? $monAmOut : NULL;
                $pm_in_assign = (isset($monPmIn) && !empty($monPmIn)) ? $monPmIn : NULL;
                $pm_out_assign = (isset($monPmOut) && !empty($monPmOut)) ? $monPmOut : NULL;
            }
            if($dayName=="Tuesday"){
                diffComp($logins['amin'],$tueAmIn,$logins['amout'],$tueAmOut,$logins['pmin'],$tuePmIn,$logins['pmout'],$tuePmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
                $am_in_assign = (isset($tueAmIn) && !empty($tueAmIn)) ? $tueAmIn : NULL;
                $am_out_assign = (isset($tueAmOut) && !empty($tueAmOut)) ? $tueAmOut : NULL;
                $pm_in_assign = (isset($tuePmIn) && !empty($tuePmIn)) ? $tuePmIn : NULL;
                $pm_out_assign = (isset($tuePmOut) && !empty($tuePmOut)) ? $tuePmOut : NULL;
            }
            if($dayName=="Wednesday"){
                diffComp($logins['amin'],$wedAmIn,$logins['amout'],$wedAmOut,$logins['pmin'],$wedPmIn,$logins['pmout'],$wedPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
                $am_in_assign = (isset($wedAmIn) && !empty($wedAmIn)) ? $wedAmIn : NULL;
                $am_out_assign = (isset($wedAmOut) && !empty($wedAmOut)) ? $wedAmOut : NULL;
                $pm_in_assign = (isset($wedPmIn) && !empty($wedPmIn)) ? $wedPmIn : NULL;
                $pm_out_assign = (isset($wedPmOut) && !empty($wedPmOut)) ? $wedPmOut : NULL;
            }
            if($dayName=="Thursday"){
                diffComp($logins['amin'],$thuAmIn,$logins['amout'],$thuAmOut,$logins['pmin'],$thuPmIn,$logins['pmout'],$thuPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
                $am_in_assign = (isset($thuAmIn) && !empty($thuAmIn)) ? $thuAmIn : NULL;
                $am_out_assign = (isset($thuAmOut) && !empty($thuAmOut)) ? $thuAmOut : NULL;
                $pm_in_assign = (isset($thuPmIn) && !empty($thuPmIn)) ? $thuPmIn : NULL;
                $pm_out_assign = (isset($thuPmOut) && !empty($thuPmOut)) ? $thuPmOut : NULL;
            }
            if($dayName=="Friday"){
                diffComp($logins['amin'],$friAmIn,$logins['amout'],$friAmOut,$logins['pmin'],$friPmIn,$logins['pmout'],$friPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
                $am_in_assign = (isset($friAmIn) && !empty($friAmIn)) ? $friAmIn : NULL;
                $am_out_assign = (isset($friAmOut) && !empty($friAmOut)) ? $friAmOut : NULL;
                $pm_in_assign = (isset($friPmIn) && !empty($friPmIn)) ? $friPmIn : NULL;
                $pm_out_assign = (isset($friPmOut) && !empty($friPmOut)) ? $friPmOut : NULL;
            }
            if($dayName=="Saturday"){
                diffComp($logins['amin'],$satAmIn,$logins['amout'],$satAmOut,$logins['pmin'],$satPmIn,$logins['pmout'],$satPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
                $am_in_assign = (isset($satAmIn) && !empty($satAmIn)) ? $satAmIn : NULL;
                $am_out_assign = (isset($satAmOut) && !empty($satAmOut)) ? $satAmOut : NULL;
                $pm_in_assign = (isset($satPmIn) && !empty($satPmIn)) ? $satPmIn : NULL;
                $pm_out_assign = (isset($satPmOut) && !empty($satPmOut)) ? $satPmOut : NULL;
            }
            $am_in = (isset($logins['amin']) && !empty($logins['amin'])) ? $logins['amin'] : NULL;
            $am_out = (isset($logins['amout']) && !empty($logins['amout'])) ? $logins['amout'] : NULL;
            $pm_in = (isset($logins['pmin']) && !empty($logins['pmin'])) ? $logins['pmin'] : NULL;
            $pm_out = (isset($logins['pmout']) && !empty($logins['pmout'])) ? $logins['pmout'] : NULL;


            #$totalDutyHours += $dutyHours + $otHours;
            $totalDutyHours += $dutyHours;
            $emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$employee));

            $otRecordIn = $db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$emp_id,'actual_start_date'=>$cDate));
            $otRecordOut = $db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$emp_id,'actual_start_date'=>$cDate));
            $otMins = $db->getValue('attendance_overtime_detail','total_mins',array('emp_id'=>$emp_id,'actual_start_date'=>$cDate));
            $ot_in = ($otRecordIn) ? $otRecordIn : NULL;
            $ot_out = ($otRecordOut) ? $otRecordOut : NULL;
            $otHours = ($otMins) ? $otMins : 0;

            $attendance_date = $db->getValue('emp_attendance_detail','count(*)',array('emp_id'=>$emp_id,'eat_date'=>$cDate));
            if($attendance_date){
                $arrUpdateAtt=array('am_in_assign'=>$am_in_assign,'am_out_assign'=>$am_out_assign,'pm_in_assign'=>$pm_in_assign,'pm_out_assign'=>$pm_out_assign,'duty_min'=>$dutyHours,'ot_min'=>$otHours,'late_min'=>$late,'under_min'=>$undertime);
                $saved_q = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_date'=>$cDate));
                $saved_r = $db->fetch_array($saved_q);
                $saved_am_in = $saved_r['am_in'];
                $saved_am_out = $saved_r['am_out'];
                $saved_pm_in = $saved_r['pm_in'];
                $saved_pm_out = $saved_r['pm_out'];
                $saved_ot_in = $saved_r['ot_in'];
                $saved_ot_out = $saved_r['ot_out'];
                if(empty($saved_am_in))
                    $arrUpdateAtt = array_merge($arrUpdateAtt,array('am_in'=>$am_in));
                if(empty($saved_am_out))
                    $arrUpdateAtt = array_merge($arrUpdateAtt,array('am_out'=>$am_out));
                if(empty($saved_pm_in))
                    $arrUpdateAtt = array_merge($arrUpdateAtt,array('pm_in'=>$pm_in));
                if(empty($saved_pm_out))
                    $arrUpdateAtt = array_merge($arrUpdateAtt,array('pm_out'=>$pm_out));
                if(empty($saved_ot_in)){
                    if($ot_in)
                        $arrUpdateAtt = array_merge($arrUpdateAtt,array('ot_in'=>$ot_in));
                }
                if(empty($saved_ot_out)){
                    if($ot_out)
                        $arrUpdateAtt = array_merge($arrUpdateAtt,array('ot_out'=>$ot_out));
                }
                $db->update('emp_attendance_detail',$arrUpdateAtt,array('emp_id'=>$emp_id,'eat_date'=>$cDate));
                #echo '<br>'.$db->last_query;
                #echo '<br>';
            }
            else{
                #insert new record
                $db->insert('emp_attendance_detail',array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eat_day'=>$dayName,
                'am_in'=>$am_in,'am_in_assign'=>$am_in_assign,'am_out'=>$am_out,'am_out_assign'=>$am_out_assign,
                'pm_in'=>$pm_in,'pm_in_assign'=>$pm_in_assign,'pm_out'=>$pm_out,'pm_out_assign'=>$pm_out_assign,
                'ot_in'=>$ot_in,'ot_out'=>$ot_out,
                'duty_min'=>$dutyHours,'ot_min'=>$otHours,'late_min'=>$late,'under_min'=>$undertime));
                #echo '<br>'.$db->last_query;
                #echo '<br>';
            }
        endforeach;
    endforeach;
    $serData = serialize($arrDataParam);
    functions::sendTo('attendance_upload_view.php?fn='.functions::encode($serData));
// End of saving the attendance recordwin7
}
?>