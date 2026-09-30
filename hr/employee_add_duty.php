<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$ha = (isset($_REQUEST['ha']) && !empty($_REQUEST['ha']) ) ? functions::decode($_REQUEST['ha']) : '';
$fromED = (isset($_REQUEST['fromED']) && !empty($_REQUEST['fromED']) ) ? $_REQUEST['fromED'] : '';
if(!empty($ha) && $eid){
	if($ha==1)
		$db->update('employee',array('has_attendance'=>0),array('emp_id'=>$eid));
	if($ha==2)
		$db->update('employee',array('has_attendance'=>1),array('emp_id'=>$eid));
	$_SESSION['notif_success']='Changes Saved!';
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
$monAmIn='';$monAmOut='';$monPmIn='';$monPmOut='';$tueAmIn='';$tueAmOut='';$tuePmIn='';$tuePmOut='';$wedAmIn='';$wedAmOut='';$wedPmIn='';$wedPmOut='';$thuAmIn='';$thuAmOut='';$thuPmIn='';$thuPmOut='';$friAmIn='';$friAmOut='';$friPmIn='';$friPmOut='';$satAmIn='';$satAmOut='';$satPmIn='';$satPmOut='';$sunAmIn='';$sunAmOut='';$sunPmIn='';$sunPmOut='';
$has_attendance = $db->getValue('employee','has_attendance',array('emp_id'=>$eid));
if($eid){
	$qTimeIn = $db->select('emp_timein','*',array('emp_id'=>$eid));
	while($rTI = $db->fetch_array($qTimeIn)):
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
}
if( isset($_POST['btnSave']) ){
	if( !empty($eid) ){
		$monAmIn = ( isset($_POST['monAmIn']) && !empty($_POST['monAmIn']) ) ? trim($_POST['monAmIn']) : NULL;
		$monAmOut = ( isset($_POST['monAmOut']) && !empty($_POST['monAmOut']) ) ? trim($_POST['monAmOut']) : NULL;
		$monPmIn = ( isset($_POST['monPmIn']) && !empty($_POST['monPmIn']) ) ? trim($_POST['monPmIn']) : NULL;
		$monPmOut = ( isset($_POST['monPmOut']) && !empty($_POST['monPmOut']) ) ? trim($_POST['monPmOut']) : NULL;
		$tueAmIn = ( isset($_POST['tueAmIn']) && !empty($_POST['tueAmIn']) ) ? trim($_POST['tueAmIn']) : NULL;
		$tueAmOut = ( isset($_POST['tueAmOut']) && !empty($_POST['tueAmOut']) ) ? trim($_POST['tueAmOut']) : NULL;
		$tuePmIn = ( isset($_POST['tuePmIn']) && !empty($_POST['tuePmIn']) ) ? trim($_POST['tuePmIn']) : NULL;
		$tuePmOut = ( isset($_POST['tuePmOut']) && !empty($_POST['tuePmOut']) ) ? trim($_POST['tuePmOut']) : NULL;
		$wedAmIn = ( isset($_POST['wedAmIn']) && !empty($_POST['wedAmIn']) ) ? trim($_POST['wedAmIn']) : NULL;
		$wedAmOut = ( isset($_POST['wedAmOut']) && !empty($_POST['wedAmOut']) ) ? trim($_POST['wedAmOut']) : NULL;
		$wedPmIn = ( isset($_POST['wedPmIn']) && !empty($_POST['wedPmIn']) ) ? trim($_POST['wedPmIn']) : NULL;
		$wedPmOut = ( isset($_POST['wedPmOut']) && !empty($_POST['wedPmOut']) ) ? trim($_POST['wedPmOut']) : NULL;

		$thuAmIn = ( isset($_POST['thuAmIn']) && !empty($_POST['thuAmIn']) ) ? trim($_POST['thuAmIn']) : NULL;
		$thuAmOut = ( isset($_POST['thuAmOut']) && !empty($_POST['thuAmOut']) ) ? trim($_POST['thuAmOut']) : NULL;
		$thuPmIn = ( isset($_POST['thuPmIn']) && !empty($_POST['thuPmIn']) ) ? trim($_POST['thuPmIn']) : NULL;
		$thuPmOut = ( isset($_POST['thuPmOut']) && !empty($_POST['thuPmOut']) ) ? trim($_POST['thuPmOut']) : NULL;
		$friAmIn = ( isset($_POST['friAmIn']) && !empty($_POST['friAmIn']) ) ? trim($_POST['friAmIn']) : NULL;
		$friAmOut = ( isset($_POST['friAmOut']) && !empty($_POST['friAmOut']) ) ? trim($_POST['friAmOut']) : NULL;
		$friPmIn = ( isset($_POST['friPmIn']) && !empty($_POST['friPmIn']) ) ? trim($_POST['friPmIn']) : NULL;
		$friPmOut = ( isset($_POST['friPmOut']) && !empty($_POST['friPmOut']) ) ? trim($_POST['friPmOut']) : NULL;
		$satAmIn = ( isset($_POST['satAmIn']) && !empty($_POST['satAmIn']) ) ? trim($_POST['satAmIn']) : NULL;
		$satAmOut = ( isset($_POST['satAmOut']) && !empty($_POST['satAmOut']) ) ? trim($_POST['satAmOut']) : NULL;
		$satPmIn = ( isset($_POST['satPmIn']) && !empty($_POST['satPmIn']) ) ? trim($_POST['satPmIn']) : NULL;
		$satPmOut = ( isset($_POST['satPmOut']) && !empty($_POST['satPmOut']) ) ? trim($_POST['satPmOut']) : NULL;
		$sunAmIn = ( isset($_POST['sunAmIn']) && !empty($_POST['sunAmIn']) ) ? trim($_POST['sunAmIn']) : NULL;
		$sunAmOut = ( isset($_POST['sunAmOut']) && !empty($_POST['sunAmOut']) ) ? trim($_POST['sunAmOut']) : NULL;
		$sunPmIn = ( isset($_POST['sunPmIn']) && !empty($_POST['sunPmIn']) ) ? trim($_POST['sunPmIn']) : NULL;
		$sunPmOut = ( isset($_POST['sunPmOut']) && !empty($_POST['sunPmOut']) ) ? trim($_POST['sunPmOut']) : NULL;

		$dayCount=0;
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

		$db->update('emp_timein',array('am_in'=>$monAmIn,'am_out'=>$monAmOut,'pm_in'=>$monPmIn,'pm_out'=>$monPmOut),array('emp_id'=>$eid,'eti_day'=>'Monday','day_no'=>'1'));
		$db->update('emp_timein',array('am_in'=>$tueAmIn,'am_out'=>$tueAmOut,'pm_in'=>$tuePmIn,'pm_out'=>$tuePmOut),array('emp_id'=>$eid,'eti_day'=>'Tuesday','day_no'=>'2'));
		$db->update('emp_timein',array('am_in'=>$wedAmIn,'am_out'=>$wedAmOut,'pm_in'=>$wedPmIn,'pm_out'=>$wedPmOut),array('emp_id'=>$eid,'eti_day'=>'Wednesday','day_no'=>'3'));
		$db->update('emp_timein',array('am_in'=>$thuAmIn,'am_out'=>$thuAmOut,'pm_in'=>$thuPmIn,'pm_out'=>$thuPmOut),array('emp_id'=>$eid,'eti_day'=>'Thursday','day_no'=>'4'));
		$db->update('emp_timein',array('am_in'=>$friAmIn,'am_out'=>$friAmOut,'pm_in'=>$friPmIn,'pm_out'=>$friPmOut),array('emp_id'=>$eid,'eti_day'=>'Friday','day_no'=>'5'));
		$db->update('emp_timein',array('am_in'=>$satAmIn,'am_out'=>$satAmOut,'pm_in'=>$satPmIn,'pm_out'=>$satPmOut),array('emp_id'=>$eid,'eti_day'=>'Saturday','day_no'=>'6'));
		if($sunAmIn || $sunAmOut || $sunPmIn || $sunPmOut){
			if( $db->getValue('emp_timein','count(*)',array('emp_id'=>$eid,'eti_day'=>'Sunday','day_no'=>'7'))==0 )
				$db->insert('emp_timein',array('am_in'=>$sunAmIn,'am_out'=>$sunAmOut,'pm_in'=>$sunPmIn,'pm_out'=>$sunPmOut,'emp_id'=>$eid,'eti_day'=>'Sunday','day_no'=>'7'));
		}
		$db->update('emp_timein',array('am_in'=>$sunAmIn,'am_out'=>$sunAmOut,'pm_in'=>$sunPmIn,'pm_out'=>$sunPmOut),array('emp_id'=>$eid,'eti_day'=>'Sunday','day_no'=>'7'));

		$qSel = $db->select('emp_salary','*',array('emp_id'=>$eid),'ORDER BY es_date DESC,es_id DESC');
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
				$db->update('emp_salary',$arrField,array('emp_id'=>$eid,'es_id'=>$es_id));
			}
		}
		$_SESSION['notif_success']='Duty Hours Assignment Saved!';
		functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
		die();
	}
	else{
		functions::say('Fail to save.');
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Duty Hours</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/inputInt.js"></script>
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
	<style type="text/css">body{font-size: 12px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<?php if(empty($fromED)){ ?>
		<div class="box-content">
			<div align="right" class="nav tab-menu nav-tabs" style="padding-top: 3px;"><?php require_once('employee_options.php');?></div>
		</div>
		<?php } ?>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>Duty Hours Assignment </h2></div>
				<select name="selHasDuty" id="selHasDuty" onChange="window.location=this.value+'&eid=<?php echo functions::encode($eid)?>'">
					<option value="?ha=<?php echo functions::encode(1)?>" <?php if($has_attendance==0){echo 'selected="selected"';}?>>Duty Hours Not Applicable</option>
					<option value="?ha=<?php echo functions::encode(2)?>" <?php if($has_attendance==1){echo 'selected="selected"';}?>>Set Duty Hours</option>
				</select><br><br>
				<?php if($has_attendance){?>
				<table width="90%" class="table">
					<tr style="background-color:#CCC;">
						<th>&nbsp;</th>
						<th colspan="2"><div align="center">Morning</div></th>
						<th colspan="2"><div align="center">Afternoon</div></th>
					</tr>
					<tr style="background-color:#CCC;">
						<th>&nbsp;</th>
						<th width="20%"><div align="center">In</div></th>
						<th width="20%"><div align="center">Out</div></th>
						<th width="20%"><div align="center">In</div></th>
						<th width="20%"><div align="center">Out</div></th>
					</tr>
					<tr>
						<td>Monday</td>
						<td>
							<div align="center">
								<select name="monAmIn" id="monAmIn" class="hrDuty" data-rel="chosen" style="width:110px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $monAmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="monAmOut" id="monAmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $monAmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="monPmIn" id="monPmIn" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $monPmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="monPmOut" id="monPmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $monPmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
					</tr>
					<tr>
						<td>Tuesday</td>
						<td>
							<div align="center">
								<select name="tueAmIn" id="tueAmIn" class="hrDuty" data-rel="chosen" style="width:110px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $tueAmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="tueAmOut" id="tueAmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $tueAmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="tuePmIn" id="tuePmIn" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $tuePmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="tuePmOut" id="tuePmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $tuePmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
					</tr>
					<tr>
						<td>Wednesday</td>
						<td>
							<div align="center">
								<select name="wedAmIn" id="wedAmIn" class="hrDuty" data-rel="chosen" style="width:110px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $wedAmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="wedAmOut" id="wedAmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $wedAmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="wedPmIn" id="wedPmIn" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $wedPmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="wedPmOut" id="wedPmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $wedPmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
					</tr>
					<tr>
						<td>Thurdays</td>
						<td>
							<div align="center">
								<select name="thuAmIn" id="thuAmIn" class="hrDuty" data-rel="chosen" style="width:110px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $thuAmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="thuAmOut" id="thuAmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $thuAmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="thuPmIn" id="thuPmIn" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $thuPmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="thuPmOut" id="thuPmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $thuPmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
					</tr>
					<tr>
						<td>Friday</td>
						<td>
							<div align="center">
								<select name="friAmIn" id="friAmIn" class="hrDuty" data-rel="chosen" style="width:110px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $friAmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="friAmOut" id="friAmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $friAmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="friPmIn" id="friPmIn" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $friPmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="friPmOut" id="friPmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $friPmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
					</tr>
					<tr>
						<td>Saturday</td>
						<td>
							<div align="center">
								<select name="satAmIn" id="satAmIn" class="hrDuty" data-rel="chosen" style="width:110px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $satAmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="satAmOut" id="satAmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $satAmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="satPmIn" id="satPmIn" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $satPmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="satPmOut" id="satPmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $satPmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
					</tr>
					<tr>
						<td>Sunday</td>
						<td>
							<div align="center">
								<select name="sunAmIn" id="sunAmIn" class="hrDuty" data-rel="chosen" style="width:110px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $sunAmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="sunAmOut" id="sunAmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $sunAmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="sunPmIn" id="sunPmIn" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $sunPmIn)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<select name="sunPmOut" id="sunPmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
									<option value="">N/A</option>
									<?php
									for($hr=0; $hr<24;$hr++):
										for($mn=0;$mn<60;$mn+=30):
											$hrDisplay = ($hr<10) ? '0'.$hr : $hr;
											$mnDisplay = ($mn<10) ? '0'.$mn : $mn;
											$tmVal = $hrDisplay.':'.$mnDisplay.':00';
											$hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
											$timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
											if($hr>=12){
												$tmphr = $hr - 12;
												$tmphr = ($tmphr==0) ? 12 : $tmphr;
												$tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
												$timeDisp = $tmphr.':'.$mnDisplay.' PM';
											}
									?>
									<option value="<?php echo $tmVal?>" <?php if($tmVal == $sunPmOut)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
									<?php endfor;
									endfor;?>
								</select>
							</div>
						</td>
					</tr>
				</table>
				<div align="center">
					<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small">
				</div>
				<?php }//has attendance?>
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
<script src="../js/custom.js"></script>
<script src="../js/wxh.js"></script>
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<!-- end: JavaScript-->
</body>
</html>