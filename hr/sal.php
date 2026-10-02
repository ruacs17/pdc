<?php
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
function diffComp($actualAmIn='',$assignAmIn='',$actualAmOut='',$assignAmOut='',$actualPmIn='',$assignPmIn='',$actualPmOut='',$assignPmOut='',$actualOtIn=0,$actualOtOut=0,&$late=0,&$undertime=0,&$dutyHours=0,&$otHours=0){
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
$late=$undertime=$dutyHours=$otHours=0;
diffComp($actualAmIn='07:00:00',$assignAmIn='07:00:00',$actualAmOut='12:00:00',$assignAmOut='12:00:00',$actualPmIn='13:00:00',$assignPmIn='13:00:00',$actualPmOut='17:00:00',$assignPmOut='17:00:00',$actualOtIn="",$actualOtOut="",$late,$undertime,$dutyHours,$otHours);
echo $dutyHours;

?>