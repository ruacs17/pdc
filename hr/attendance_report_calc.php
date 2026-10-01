<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));

$fromPayroll = (isset($_REQUEST['pr']) && !empty($_REQUEST['pr']) ) ? $_REQUEST['pr'] : 0;
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$confirmed = $rEatID['confirmed'];
$attendance_ready = $rEatID['attendance_ready'];
$date_start = $rEatID['date_start'];
$date_end = $rEatID['date_end'];
$att_month = $rEatID['att_month'];
$att_year = $rEatID['att_year'];

if( !empty($eatid) && ($confirmed==0) ){

	$arrHoliday=array();$frmYr='';$toYr='';
	if($date_start && $date_end){
		$frmYr = (substr($date_start,0,4)) ? substr($date_start,0,4) : date('Y');
		$toYr = (substr($date_end,0,4)) ? substr($date_end,0,4) : date('Y');
		$qHoliday = $db->select('holiday','*',array(),'WHERE hol_year!="all" AND concat(hol_year,"-",hol_month,"-",hol_day) BETWEEN "'.$date_start.'" AND "'.$date_end.'"');
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



	$totalAbsentAmount=0;$sal_type='';
	#$qEmps = $db->select('emp_attendance_personnel eas_id, employee emp','DISTINCT eas_id.emp_id',array('eat_id'=>$eatid),'AND eas_id.emp_id=emp.emp_id ORDER BY lname,fname');
	#echo $db->last_query.'<br>';
	$eatid=152;$empid=217;
	$qEmps = $db->select('emp_attendance_personnel eas_id, employee emp','DISTINCT eas_id.emp_id',array('eat_id'=>$eatid,'emp.emp_id'=>$empid),'AND eas_id.emp_id=emp.emp_id ORDER BY lname,fname');
	while( $rEmps = $db->fetch_array($qEmps)):
		$totalAbsentAmount=0;$totalAbsent=0;$absent_amount=0;
		$emp_id = $rEmps['emp_id'];

		//Get Employee Filed Leave
		$arrEmpLeaves=array();
		#$qLeaveDates = $db->select('leave_file_detail','*',array('emp_id'=>$emp_id),'AND lfd_date BETWEEN "'.$date_start.'" AND "'.$date_end.'"');
		$qLeaveDates = $db->preparedQ('SELECT * FROM leave_config lc,leave_file lf, leave_file_detail lfd WHERE lc.lc_id=lf.lc_id AND lf.lf_id=lfd.lf_id AND lc.with_pay=1 AND lf.emp_id=? AND lfd.lfd_date BETWEEN ? AND ?',array($emp_id,$date_start,$date_end));
		while($rLD = $db->fetch_array($qLeaveDates)):
			$arrEmpLeaves[$rLD['lfd_date']]=$rLD['lfd_id'];
		endwhile;
		print_r($arrEmpLeaves);
die();
		$qEmpDeduct = $db->select('employee','*',array('emp_id'=>$emp_id));
		$rED = $db->fetch_array($qEmpDeduct);
		$pagibig_autodeduct = $rED['pagibig_autodeduct'];
		$philhealth_autodeduct = $rED['philhealth_autodeduct'];
		$sss_autodeduct = $rED['sss_autodeduct'];
		$gsis_autodeduct = $rED['gsis_autodeduct'];


		//Getting Salary
		$wage_day=0;$wage_hour=0;$wage_minute=0;$duty_amount=0;
		$empRegDutyMins=0;$empRegOTMins=0;$empUnderMins=0;$empLateMins=0;
		$qSal = $db->select('emp_salary','*',array('emp_id'=>$emp_id),'AND es_date <= "'.$date_end.'" ORDER BY es_date DESC, es_id DESC LIMIT 1');
		#echo $db->last_query;
		$rSal = $db->fetch_array($qSal);
		$txSalMonth = ($rSal['es_salary']) ? $rSal['es_salary'] : 0;
		$wage_day = ($rSal['es_daily']) ? $rSal['es_daily'] : 0;
		$wage_hour = ($rSal['es_hourly']) ? $rSal['es_hourly'] : 0;
		$wage_minute = ($rSal['es_minute']) ? $rSal['es_minute'] : 0;
		$sal_type = ($rSal['es_type']) ? $rSal['es_type'] : '';

		$db->delete('payroll_adjustment',array('emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eatd_id IS NOT NULL');
		if($sal_type){//If employee has set a salary type

			//Getting Days Duty
			//For Employee that is not applicable of attendance
			if( $db->getValue('employee','has_attendance',array('emp_id'=>$emp_id))==0 ){
				$dates = functions::date_diff($date_start,$date_end);
				if($dates){
					for($i=0;$i<=$dates;$i++):
						$empRegDutyMins=0;
						$succeeding_date = functions::AddDay($date_start,$i);

						$daysName = date('l', strtotime($succeeding_date));
						if($daysName == 'Saturday')
							$empRegDutyMins = 240; //4hr
						else if($daysName=='Sunday')
							;
						else
							$empRegDutyMins = 480; // 8hr

						if( $db->getValue('emp_attendance_detail','count(*)',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'eat_date'=>$succeeding_date)) ){
							$db->update('emp_attendance_detail',array('duty_min'=>$empRegDutyMins,'wage_day'=>$wage_day,'wage_hour'=>$wage_hour,'wage_minute'=>$wage_minute,'duty_amount'=>$wage_day),array('eat_id'=>$eatid,'emp_id'=>$emp_id,'eat_date'=>$succeeding_date));
						}
						else{
							$db->insert('emp_attendance_detail',array('eat_id'=>$eatid,'emp_id'=>$emp_id,'eat_date'=>$succeeding_date,'eat_day'=>$daysName,'duty_min'=>$empRegDutyMins,'wage_day'=>$wage_day,'wage_hour'=>$wage_hour,'wage_minute'=>$wage_minute,'duty_amount'=>$duty_amount));
						}
					endfor;
				}
			}
			else{ //For Employee applicable with attendance

				$work_status_start = $db->getValue('emp_work_status','ews_stat',array('emp_id'=>$emp_id),'AND ews_date <= "'.$date_start.'" ORDER BY ews_date DESC, ews_id DESC LIMIT 1');
				$work_status_end = $db->getValue('emp_work_status','ews_stat',array('emp_id'=>$emp_id),'AND ews_date <= "'.$date_end.'" ORDER BY ews_date DESC, ews_id DESC LIMIT 1');
				$work_status_different=0;
				if($work_status_start==$work_status_end){
					$work_status = $work_status_start;
					$project_based = $db->getValue('emp_work_status','project_based',array('emp_id'=>$emp_id,'ews_stat'=>$work_status),'ORDER BY ews_date DESC LIMIT 1');
				}
				else
					$work_status_different=1;

				$base_salary=0;
				if($sal_type=='flexible'){
					$semiMonthSal = ($txSalMonth) ? ($txSalMonth/2) : 0;
				}
				$qEmpAtt = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" ORDER BY eat_date');
				while($rA = $db->fetch_array($qEmpAtt)):
					$empRegDutyMins = $rA['duty_min'];
					$empRegOTMins = $rA['ot_min'];
					$totalAbsent = $rA['late_min'] + $rA['under_min'];
					$att_date = $rA['eat_date'];

					$ot_amount = $empRegOTMins * $wage_minute;

					$duty_amount = $empRegDutyMins * $wage_minute;
					//if no duty minutes rendered, it means absent all day, then the absent amount should be equivalent to daily wage
					if($rA['am_in_assign'] || $rA['am_out_assign'] || $rA['pm_in_assign'] || $rA['pm_out_assign']){
						#$absent_amount = ($empRegDutyMins) ? $totalAbsent * $wage_minute : $wage_day;
						$absent_amount = $totalAbsent * $wage_minute;
					}
					//Has no schedule duty
					$retain=1;
					if( empty($rA['am_in_assign']) && empty($rA['am_out_assign']) && empty($rA['pm_in_assign']) && empty($rA['pm_out_assign']) ){
						$duty_amount=$absent_amount=0;
						$retain=0;
					}
					$totalAbsentAmount += $absent_amount;
					$base_salary += $duty_amount;
					if($work_status_different==1){
						$work_status = $db->getValue('emp_work_status','ews_stat',array('emp_id'=>$emp_id),'AND ews_date <= "'.$att_date.'" ORDER BY ews_date DESC, ews_id DESC LIMIT 1');
						$project_based = $db->getValue('emp_work_status','project_based',array('emp_id'=>$emp_id,'ews_stat'=>$work_status),'ORDER BY ews_date DESC LIMIT 1');
					}

					//***********LEAVE WITH PAY CHECKING****************
					//***********LEAVE WITH PAY CHECKING END****************

					//***********HOLIDAY CHECK****************
					$addPercent=0;
					if( isset($arrHoliday[$att_date]) ){//If there is a holiday
						$exit=0;
						$fDate=$att_date;
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

						if($holidayCreditAllowed){
							if( $work_status=="Regular" || $work_status=="Probationary" ){
								//Only Regular, Probationary and Regular Project Based can avail the premium of Holiday.
								$attDateArr = explode("-",$att_date);
								$holDay = isset($attDateArr[2]) ? $attDateArr[2] : '';
								$holMon = isset($attDateArr[1]) ? $attDateArr[1] : '';
								$holYr = isset($attDateArr[0]) ? $attDateArr[0] : '';
								$qHol = $db->select('holiday','*',array('hol_month'=>$holMon,'hol_day'=>$holDay));
								$reg_duty_addon=0;
								while($rHol = $db->fetch_array($qHol)):

									if($rHol['hol_year']=='all' || $rHol['hol_year']==$holYr){//If holiday is every year or this year
										$addPercent = ($rHol['wage_percent']) ? $rHol['wage_percent'] / 100 : 0;

										if($ot_amount){//If there is overtime
											$addOT = $ot_amount * $addPercent;
											if($addOT){//If there is additional pay
												//Regular and Probationary can received additional pay for overtime
												$adjustment_desc = $empRegOTMins;
												$adjustment_name = $rHol['hol_type'].' ('.$rHol['hol_name'].') '.$rHol['wage_percent'].'%';
												$db->insert('payroll_adjustment',array('eat_id'=>$rA['eat_id'],'emp_id'=>$emp_id,'eatd_id'=>$rA['eatd_id'],'adjustment_name'=>$adjustment_name,'adjustment_desc'=>$adjustment_desc,'adjustment_value'=>$addOT,'adjustment_type'=>'addon','adjustment_mode'=>'system'));
											}//End: If there is additional pay
										}//End: If there is overtime

										if($project_based==1){//If personnel is Regular or Probationary Project Based
											//Recieves regular duty amount with additional percentage 
											$reg_duty_addon = ($duty_amount * $addPercent);
											if($reg_duty_addon){//If there is additional pay for Project Based
												$adjustment_desc = $empRegDutyMins;
												$adjustment_name = $rHol['hol_type'].' ('.$rHol['hol_name'].') '.$rHol['wage_percent'].'%';
												$db->insert('payroll_adjustment',array('eat_id'=>$rA['eat_id'],'emp_id'=>$emp_id,'eatd_id'=>$rA['eatd_id'],'adjustment_name'=>$adjustment_name,'adjustment_desc'=>$adjustment_desc,'adjustment_value'=>$reg_duty_addon,'adjustment_type'=>'addon','adjustment_mode'=>'system'));
											}//End: If there is additional pay for Project Based
										}
									}//End: If holiday is every year or this year
								endwhile;
							}//End: Only Regular, Probationary and Regular/Probationary Project Based can avail the premium of Holiday.							
						}
					}//End: If there is a holiday
					//***********END HOLIDAY CHECK************

					$db->update('emp_attendance_detail',array('eat_id'=>$eatid,'wage_day'=>$wage_day*$retain,'wage_hour'=>$wage_hour*$retain,'wage_minute'=>$wage_minute*$retain,'duty_amount'=>$duty_amount,'ot_amount'=>$ot_amount,'absent_amount'=>$absent_amount),array('emp_id'=>$emp_id,'eat_date'=>$att_date,'eat_id'=>$eatid));
					#echo $db->last_query.'<br>';
					#die();
				endwhile;
			}//For Employee applicable with attendance

			//------------deduction's area
			//------------Premium Deduction Start

			//If the employee has reported to duty, it is subject for premium deductions
			if( $db->getValue('emp_attendance_detail','sum(duty_min)',array('emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'"') ){

				if($sal_type=='flexible')
					$base_salary = ($txSalMonth / 2) - $totalAbsentAmount;

				if($sss_autodeduct){
					//Insert SSS Premium
					//get previous base pay
					$qBP = $db->select('payroll_premium pp, emp_attendance ea','sum(base_pay) as bp,sum(msc) as smsc,sum(ee_share) as see, sum(er_share) as ser, sum(ec) as sec,sum(pp_total) as stt',array('pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'SSS','emp_id'=>$emp_id),'AND ea.eat_id=pp.eat_id AND date_start <= "'.$date_start.'"');
					#echo $db->last_query.'<br>';
					$rBP = $db->fetch_array($qBP);
					$previous_base_pay = ($rBP['bp']) ? $rBP['bp'] : 0;
					$previous_msc = ($rBP['smsc']) ? $rBP['smsc'] : 0;
					$previous_ec = ($rBP['sec']) ? $rBP['sec'] : 0;
					$previous_ee_share = ($rBP['see']) ? $rBP['see'] : 0;
					$previous_er_share = ($rBP['ser']) ? $rBP['ser'] : 0;
					$previous_total = ($rBP['stt']) ? $rBP['stt'] : 0;

					//Add to current base pay
					#echo $previous_base_pay.'<br>';
					$current_base_pay = $base_salary + $previous_base_pay;
					//get the respective contribution of accumulated base pay
					$qS = $db->query('SELECT * FROM table_sss WHERE "'.$current_base_pay.'" BETWEEN sal_from AND sal_to');
					#echo $db->last_query.'<br>';
					$rS = $db->fetch_array($qS);
					$msc = $rS['msc'] - $previous_msc;
					$sss_ee_share = ($rS['sss_ee_share']+$rS['prov_ee']) - $previous_ee_share;
					#$sss_ee_share = $rS['tc_ee'] - $previous_ee_share;
					$sss_er_share = ($rS['sss_er_share']+$rS['prov_er']) - $previous_er_share;
					#$sss_er_share = $rS['tc_er'] - $previous_er_share;
					$sss_ec = $rS['sss_ec'] - $previous_ec;
					#$sss_total = $rS['sss_total'] - $previous_total;

					if($pa_id = $db->getValue('payroll_adjustment','pa_id',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'SSS')) )
						$db->update('payroll_adjustment',array('adjustment_value'=>$sss_ee_share),array('pa_id'=>$pa_id,'adjustment_mode'=>'system'));//Only the adjustment created by the system can be updated.
					else
						$pa_id = $db->insert('payroll_adjustment',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'SSS','adjustment_value'=>$sss_ee_share,'adjustment_type'=>'deduction','adjustment_mode'=>'system'));//only adjusted by system
					#echo $db->last_query.'<br>';
					if($pa_id){
						//Inserting into payroll_premium table
						if( $pp_id = $db->getValue('payroll_premium','pp_id',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'pa_id'=>$pa_id,'pp_type'=>'SSS','pp_month'=>$att_month,'pp_year'=>$att_year)) ){
							$db->update('payroll_premium',array('base_pay'=>$base_salary,'msc'=>$msc,'ee_orig'=>$sss_ee_share,'er_share'=>$sss_er_share,'ec'=>$sss_ec),array('pp_id'=>$pp_id));
							$db->update('payroll_premium',array('ee_share'=>$sss_ee_share),array('pp_id'=>$pp_id,'modified'=>0));//Unmodified by human
							$pp_total=$db->getValue('payroll_premium','(ee_share+er_share+ec)',array('pp_id'=>$pp_id));
							$db->update('payroll_premium',array('pp_total'=>$pp_total),array('pp_id'=>$pp_id));
						}
						else
							$pp_id = $db->insert('payroll_premium',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'pa_id'=>$pa_id,'base_pay'=>$base_salary,'msc'=>$msc,'ee_orig'=>$sss_ee_share,'ee_share'=>$sss_ee_share,'er_share'=>$sss_er_share,'ec'=>$sss_ec,'pp_total'=>($sss_ee_share+$sss_er_share+$sss_ec),'pp_type'=>'SSS','pp_month'=>$att_month,'pp_year'=>$att_year));
						if(empty($pp_id))//If no payroll premium record inserted, remove the deduction on payroll_adjustment
							$db->delete('payroll_adjustment',array('pa_id'=>$pa_id));
					}
				}
				else{
					$db->delete('payroll_adjustment',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'SSS','adjustment_type'=>'deduction','adjustment_mode'=>'system'));
				}

				//Philhealth
				if($philhealth_autodeduct){
					//compute previous base pay
					$qBP = $db->select('payroll_premium pp, emp_attendance ea','sum(base_pay) as bp,sum(msc) as smsc,sum(ee_share) as see, sum(er_share) as ser, sum(ec) as sec,sum(pp_total) as stt',array('pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'PHIC','emp_id'=>$emp_id),'AND ea.eat_id=pp.eat_id AND date_start <= "'.$date_start.'"');
					$rBP = $db->fetch_array($qBP);
					$previous_base_pay = ($rBP['bp']) ? $rBP['bp'] : 0;
					$previous_ee_share = ($rBP['see']) ? $rBP['see'] : 0;
					$previous_er_share = ($rBP['ser']) ? $rBP['ser'] : 0;
					$previous_total = ($rBP['stt']) ? $rBP['stt'] : 0;
					//Add to current base pay
					$current_base_pay = $base_salary + $previous_base_pay;
					//get the respective contribution of accumulated base pay
					$qPH = $db->query('SELECT * FROM table_phic WHERE "'.$current_base_pay.'" BETWEEN sal_from AND sal_to');
					$rPH = $db->fetch_array($qPH);
					if($rPH['phic_type']=='percent'){
						$phic_ee_share = (($rPH['phic_ee_share']/100) * $current_base_pay)-$previous_ee_share;
						$phic_er_share = (($rPH['phic_er_share']/100) * $current_base_pay)-$previous_er_share;
						$phic_total = (($rPH['phic_total']/100) * $current_base_pay)-$previous_total;
					}
					else{
						$phic_ee_share = $rPH['phic_ee_share']-$previous_ee_share;
						$phic_er_share = $rPH['phic_er_share']-$previous_er_share;
						$phic_total = $rPH['phic_total'] - $previous_total;
					}

					if( $pa_id = $db->getValue('payroll_adjustment','pa_id',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'Philhealth')) )
						$db->update('payroll_adjustment',array('adjustment_value'=>$phic_ee_share),array('pa_id'=>$pa_id,'adjustment_mode'=>'system'));//only adjusted by system
					else
						$pa_id = $db->insert('payroll_adjustment',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'Philhealth','adjustment_value'=>$phic_ee_share,'adjustment_type'=>'deduction','adjustment_mode'=>'system'));
					if($pa_id){
						if( $pp_id = $db->getValue('payroll_premium','pp_id',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'pa_id'=>$pa_id,'pp_type'=>'PHIC','pp_month'=>$att_month,'pp_year'=>$att_year)) ){
							$db->update('payroll_premium',array('base_pay'=>$base_salary,'ee_orig'=>$phic_ee_share,'er_share'=>$phic_er_share),array('pp_id'=>$pp_id));
							$db->update('payroll_premium',array('ee_share'=>$phic_ee_share),array('pp_id'=>$pp_id,'modified'=>0));//Unmodified by human
							$pp_total=$db->getValue('payroll_premium','(ee_share+er_share)',array('pp_id'=>$pp_id));
							$db->update('payroll_premium',array('pp_total'=>$pp_total),array('pp_id'=>$pp_id));
						}
						else
							$pp_id = $db->insert('payroll_premium',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'pa_id'=>$pa_id,'base_pay'=>$base_salary,'ee_orig'=>$phic_ee_share,'ee_share'=>$phic_ee_share,'er_share'=>$phic_er_share,'pp_total'=>($phic_ee_share+$phic_er_share),'pp_type'=>'PHIC','pp_month'=>$att_month,'pp_year'=>$att_year));
						if(empty($pp_id))//If no payroll premium record inserted, remove the deduction on payroll_adjustment
							$db->delete('payroll_adjustment',array('pa_id'=>$pa_id));
					}
				}
				else{
					$db->delete('payroll_adjustment',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'Philhealth','adjustment_type'=>'deduction','adjustment_mode'=>'system'));
				}

				//Pagibig
				if($pagibig_autodeduct){
					$pagibig_contribution = $db->getValue('employee','pagibig_contribution',array('emp_id'=>$emp_id));
					$pagibig_additional = ($pagibig_contribution) ? ($pagibig_contribution/2) : 0;

					$lacking_personal=0;$lacking_required=0;
					$previous_ee_share=0;$previous_base_pay=0;$previous_er_share=0;
					$qBP = $db->select('payroll_premium pp, emp_attendance ea','*',array('pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'HDMF','emp_id'=>$emp_id),'AND ea.eat_id=pp.eat_id AND date_start <= "'.$date_start.'"');
					while($rBP = $db->fetch_array($qBP)):
						$previous_er_share += $rBP['er_share'];
						$previous_base_pay += $rBP['base_pay'];
						$personal = $rBP['ee_personal'];
						$required = $rBP['ee_orig'];
						$paid = $rBP['ee_share'];

						$excess_of_required =  $paid - $required;
						if($excess_of_required==0){//If the required contribution matches the actual payment
							$pagibig_additional += $personal;//The personal contribution for this payroll will be added to the next payroll
							$previous_ee_share += $required;
						}
						else if($excess_of_required > 0){//If the actual payment is greater than required contribution
							$pagibig_additional += $personal - $excess_of_required;//compute how much payment it paid to know whether there is no excess or lacking for personal contribution
							$previous_ee_share += $required;
						}
						else if($excess_of_required < 0){//If the required contribution is lesser than the actual payment
							#$lacking_required += ($excess_of_required * -1);//Make it positive to identify the remaining payment for required contribution and be added to the next payroll
							$previous_ee_share += $paid;
							$pagibig_additional += $personal;//The personal contribution for this payroll will be added to the next payroll
						}
					endwhile;
					//Add to current base pay
					$current_base_pay = $base_salary + $previous_base_pay;
					//get the respective contribution of accumulated base pay
					$qHD = $db->query('SELECT * FROM table_hdmf WHERE "'.$current_base_pay.'" BETWEEN sal_from AND sal_to');
					#echo $db->last_query;
					$rHD = $db->fetch_array($qHD);
					if($rHD['hdmf_type']=='percent'){
						$hdmf_ee_share = (($rHD['hdmf_ee_share']/100) * $current_base_pay) - $previous_ee_share;
						$hdmf_er_share = (($rHD['hdmf_er_share']/100) * $current_base_pay) - $previous_er_share;
					}
					else{
						$hdmf_ee_share = $rHD['hdmf_ee_share'] - $previous_ee_share;
						$hdmf_er_share = $rHD['hdmf_er_share'] - $previous_er_share;
					}
					#$hdmf_total = ($hdmf_ee_share+$pagibig_additional) + $hdmf_er_share;
					if( $pa_id = $db->getValue('payroll_adjustment','pa_id',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'Pagibig')) )
						$db->update('payroll_adjustment',array('adjustment_value'=>($hdmf_ee_share+$pagibig_additional)),array('pa_id'=>$pa_id,'adjustment_mode'=>'system'));//only adjusted by system
					else
						$pa_id = $db->insert('payroll_adjustment',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'Pagibig','adjustment_value'=>($hdmf_ee_share+$pagibig_additional),'adjustment_type'=>'deduction','adjustment_mode'=>'system'));
					#echo $db->last_query.'<br>';
					if($pa_id){
						if( $pp_id = $db->getValue('payroll_premium','pp_id',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'pa_id'=>$pa_id,'pp_type'=>'HDMF','pp_month'=>$att_month,'pp_year'=>$att_year)) ){
							$db->update('payroll_premium',array('base_pay'=>$base_salary,'ee_personal'=>$pagibig_additional,'ee_orig'=>$hdmf_ee_share,'er_share'=>$hdmf_er_share),array('pp_id'=>$pp_id));
							$db->update('payroll_premium',array('ee_share'=>($hdmf_ee_share+$pagibig_additional)),array('pp_id'=>$pp_id,'modified'=>0));//Unmodified by human
							$pp_total=$db->getValue('payroll_premium','(ee_share+er_share)',array('pp_id'=>$pp_id));
							$db->update('payroll_premium',array('pp_total'=>$pp_total),array('pp_id'=>$pp_id));
						}
						else
							$pp_id = $db->insert('payroll_premium',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'pa_id'=>$pa_id,'base_pay'=>$base_salary,'ee_personal'=>$pagibig_additional,'ee_orig'=>$hdmf_ee_share,'ee_share'=>($hdmf_ee_share+$pagibig_additional),'er_share'=>$hdmf_er_share,'pp_total'=>($hdmf_ee_share+$pagibig_additional)+$hdmf_er_share,'pp_type'=>'HDMF','pp_month'=>$att_month,'pp_year'=>$att_year));
						if(empty($pp_id))//If no payroll premium record inserted, remove the deduction on payroll_adjustment
							$db->delete('payroll_adjustment',array('pa_id'=>$pa_id));
						#echo $db->last_query.'<br>';
					}
				}
				else{
					$db->delete('payroll_adjustment',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'Pagibig','adjustment_type'=>'deduction','adjustment_mode'=>'system'));
				}


				//Getting deductions that are not yet fully paid.
				$qD = $db->select('payroll_deduction_reference','*',array('emp_id'=>$emp_id,'adjustment_type'=>'deduction','active'=>'Active'), 'AND adjustment_start <= "'.$date_end.'"');
				while($rD = $db->fetch_array($qD)):
					$rID = $rD['pdf_id'];
					//Payment from All payroll for this deduction - payment for this current payroll deduction.
					$current_payment = $db->getValue('payroll_adjustment','sum(adjustment_value)',array('pdf_id'=>$rID,'emp_id'=>$emp_id,'eat_id'=>$eatid,'include'=>1));
					$recent_payments = $db->getValue('payroll_adjustment','sum(adjustment_value)',array('emp_id'=>$emp_id,'pdf_id'=>$rID,'include'=>1)) - $current_payment;
					$current_total_payment = $recent_payments + $rD['payroll_deduction'];

					if($rD['total_amount']==0){//For infinite deductions.
						$arrPDF = array('pdf_id'=>$rID,'emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>$rD['pdf_detail'],'adjustment_value'=>$rD['payroll_deduction'],'adjustment_type'=>'deduction','adjustment_mode'=>'system');
						if( $db->getValue('payroll_adjustment','count(*)',array('pdf_id'=>$rID,'emp_id'=>$emp_id,'eat_id'=>$eatid))==0 )//Check if there is existing payment/deduction
							$db->insert('payroll_adjustment',$arrPDF); //Insert new deduction/payment
						else
							$db->update('payroll_adjustment',$arrPDF,array('pdf_id'=>$rID,'emp_id'=>$emp_id,'eat_id'=>$eatid));//Update the existing.
					}
					else if($recent_payments < $rD['total_amount']){//If there is still payable
						$balance_payment = $rD['total_amount'] - $recent_payments;//Get Balance payable
						if($rD['payroll_deduction'] > $balance_payment){//If the to be deducted/paid is greater than balance
							$adjust_val = $rD['payroll_deduction'] - $balance_payment; //Only get the remaining balance
						}
						else //Else deduct/pay all the deduction amount
							$adjust_val = $rD['payroll_deduction'];

						$arrPDF = array('pdf_id'=>$rID,'emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>$rD['pdf_detail'],'adjustment_value'=>$adjust_val,'adjustment_type'=>'deduction','adjustment_mode'=>'system');
						if( $db->getValue('payroll_adjustment','count(*)',array('pdf_id'=>$rID,'emp_id'=>$emp_id,'eat_id'=>$eatid))==0 )//Check if there is existing payment/deduction
							$db->insert('payroll_adjustment',$arrPDF); //Insert new deduction/payment
						else
							$db->update('payroll_adjustment',$arrPDF,array('pdf_id'=>$rID,'emp_id'=>$emp_id,'eat_id'=>$eatid));//Update the existing.
					}
					else{
						$db->delete('payroll_adjustment',array('pdf_id'=>$rID,'emp_id'=>$emp_id,'eat_id'=>$eatid));
					}
				endwhile;
				//end deduction's area

				//Getting add-ons that are not yet fully paid.
				$qDaddon = $db->select('payroll_deduction_reference','*',array('emp_id'=>$emp_id,'adjustment_type'=>'addon','active'=>'Active'),'AND adjustment_start <= "'.$date_end.'"');
				while($rDA = $db->fetch_array($qDaddon)):
					$rIDDA = $rDA['pdf_id'];
					$arrPDF = array('pdf_id'=>$rIDDA,'emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>$rDA['pdf_detail'],'adjustment_value'=>$rDA['payroll_deduction'],'adjustment_type'=>'addon','adjustment_mode'=>'system');
					if( $db->getValue('payroll_adjustment','count(*)',array('pdf_id'=>$rIDDA,'emp_id'=>$emp_id,'eat_id'=>$eatid))==0 )
						$db->insert('payroll_adjustment',$arrPDF);
					else
						$db->update('payroll_adjustment',$arrPDF,array('pdf_id'=>$rIDDA,'emp_id'=>$emp_id,'eat_id'=>$eatid));
				endwhile;
				//end add-on's area

			}
			else{//remove any existing premium deductions
				if($sal_type=='flexible')
					$totalAbsentAmount = ($txSalMonth / 2);
				$db->delete('payroll_adjustment',array('emp_id'=>$emp_id,'eat_id'=>$eatid));
			}

			//------------Premium Deduction End

			//Inserting Absences
			if( $db->getValue('payroll_adjustment','count(*)',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'absences')) )
				$db->update('payroll_adjustment',array('adjustment_value'=>$totalAbsentAmount),array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'absences'));
			else
				$db->insert('payroll_adjustment',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'absences','adjustment_value'=>$totalAbsentAmount,'adjustment_type'=>'deduction','adjustment_mode'=>'system'));
			$db->delete('emp_payroll_detail',array('eat_id'=>$eatid,'emp_id'=>$emp_id));
		}//if($sal_typ)
	endwhile;
}
else{
	functions::sendTo('index.php');
	die();
}

// if($fromPayroll){
// 	$_SESSION['notif_success']='Calculating Done!';
// 	functions::sendTo('payroll_view.php?eatid='.functions::encode($eatid));
// 	die();
// }
// else{
// 	$_SESSION['notif_success']='Calculating Done!';
// 	functions::sendTo('attendance_summary_view.php?eatid='.functions::encode($eatid));
// 	die();
// }

?>