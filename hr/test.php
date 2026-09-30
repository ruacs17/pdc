<?php
require_once('../class/database.php');
$db = new Database();
#$q = $db->query('SELECT DISTINCT emp_id FROM emp_timein WHERE emp_id=8 ORDER BY emp_id');
$q = $db->query('SELECT DISTINCT emp_id FROM emp_timein ORDER BY emp_id');
while($r = $db->fetch_array($q)):
	$emp_id = $r['emp_id'];
	$workingDays=$dayCount=0;
	$qTI = $db->select('emp_timein','*',array('emp_id'=>$emp_id));
	$sunPmOut =$sunPmIn =$sunAmOut =$sunAmIn =$satPmOut =$satPmIn =$satAmOut =$satAmIn =$friPmOut =$friPmIn =$friAmOut =$friAmIn =$thuPmOut =$thuPmIn =$thuAmOut =$thuAmIn =$wedPmOut =$wedPmIn =$wedAmOut =$wedAmIn =$tuePmOut =$tuePmIn =$tueAmOut =$monAmIn=$monAmOut=$monPmIn=$monPmOut=$tueAmIn='';
	while($rTI = $db->fetch_array($qTI)):
		if($rTI['eti_day']=='Monday'){
			$monAmIn = $rTI['am_in'];
			$monAmOut = $rTI['am_out'];
			$monPmIn = $rTI['pm_in'];
			$monPmOut = $rTI['pm_out'];
		}
		if($rTI['eti_day']=='Tuesday'){
			$tueAmIn = $rTI['am_in'];
			$tueAmOut = $rTI['am_out'];
			$tuePmIn = $rTI['pm_in'];
			$tuePmOut = $rTI['pm_out'];   
		}
		if($rTI['eti_day']=='Wednesday'){
			$wedAmIn = $rTI['am_in'];
			$wedAmOut = $rTI['am_out'];
			$wedPmIn = $rTI['pm_in'];
			$wedPmOut = $rTI['pm_out'];
		}
		if($rTI['eti_day']=='Thursday'){
			$thuAmIn = $rTI['am_in'];
			$thuAmOut = $rTI['am_out'];
			$thuPmIn = $rTI['pm_in'];
			$thuPmOut = $rTI['pm_out']; 
		}
		if($rTI['eti_day']=='Friday'){
			$friAmIn = $rTI['am_in'];
			$friAmOut = $rTI['am_out'];
			$friPmIn = $rTI['pm_in'];
			$friPmOut = $rTI['pm_out'];
		}
		if($rTI['eti_day']=='Saturday'){
			$satAmIn = $rTI['am_in'];
			$satAmOut = $rTI['am_out'];
			$satPmIn = $rTI['pm_in'];
			$satPmOut = $rTI['pm_out']; 
		}
		if($rTI['eti_day']=='Sunday'){
			$sunAmIn = $rTI['am_in'];
			$sunAmOut = $rTI['am_out'];
			$sunPmIn = $rTI['pm_in'];
			$sunPmOut = $rTI['pm_out']; 
		}
		

	endwhile;
	$dayCount += ( !empty($monAmIn) && !empty($monAmOut) ) ? .5 : 0;
	$dayCount += ( !empty($monPmIn) && !empty($monPmOut) ) ? .5 : 0;
	$dayCount += ( !empty($tueAmIn) && !empty($tueAmOut) ) ? .5 : 0;
	$dayCount += ( !empty($tuePmIn) && !empty($tuePmOut) ) ? .5 : 0;
	$dayCount += ( !empty($wedAmIn) && !empty($wedAmOut) ) ? .5 : 0;
	$dayCount += ( !empty($wedPmIn) && !empty($wedPmOut) ) ? .5 : 0;
	$dayCount += ( !empty($thuAmIn) && !empty($thuAmOut) ) ? .5 : 0;
	$dayCount += ( !empty($thuPmIn) && !empty($thuPmOut) ) ? .5 : 0;
	$dayCount += ( !empty($friAmIn) && !empty($friAmOut) ) ? .5 : 0;
	$dayCount += ( !empty($friPmIn) && !empty($friPmOut) ) ? .5 : 0;
	$dayCount += ( !empty($satAmIn) && !empty($satAmOut) ) ? .5 : 0;
	$dayCount += ( !empty($satPmIn) && !empty($satPmOut) ) ? .5 : 0;
	$dayCount += ( !empty($sunAmIn) && !empty($sunAmOut) ) ? .5 : 0;
	$dayCount += ( !empty($sunPmIn) && !empty($sunPmOut) ) ? .5 : 0;
	if($dayCount==5)
		$workingDays=261;
	else if($dayCount==5.5)
		$workingDays=287;
	else if($dayCount==6)
		$workingDays=313;
	else if($dayCount==7)
		$workingDays=365;
	else if($dayCount==4)
		$workingDays=261;
	else
		$workingDays=156;

	if($dayCount){
		$qSel = $db->select('emp_salary','*',array('emp_id'=>$emp_id),'ORDER BY es_date DESC,es_id DESC');
		$rSel = $db->fetch_array($qSel);
		$es_id = $rSel['es_id'] ?? NULL;
		if($es_id){
			$es_salary = $rSel['es_salary'] ?? NULL;
			$es_daily = $rSel['es_daily'] ?? NULL;
			$es_hourly = $rSel['es_hourly'] ?? NULL;
			$es_minute = $rSel['es_minute'] ?? NULL;
			$es_date = $rSel['es_date'] ?? NULL;
			$es_type = $rSel['es_type'] ?? NULL;
			if($es_type=='fixed'){//daily laborer
				$es_salary = ($es_daily*$workingDays) / 12;
				$es_hourly = $es_daily / 8;
				$es_minute = $es_hourly / 60;
			}
			else if($es_type == 'flexible'){
				$es_daily = ($es_salary * 12)  / $workingDays;
				$es_hourly = $es_daily / 8;
				$es_minute = $es_hourly / 60;
			}
			if( $es_type && ($es_salary || $es_daily) ){
				$arrField = array('es_salary'=>$es_salary,'es_daily'=>$es_daily,'es_hourly'=>$es_hourly,'es_minute'=>$es_minute,'working_days'=>$workingDays);
				$db->update('emp_salary',$arrField,array('emp_id'=>$emp_id,'es_id'=>$es_id));
				echo $db->last_query.'<br>';
			}
		}		
	}

endwhile;
?>