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
$db->utf8();
$add = (isset($_REQUEST['add']) && !empty($_REQUEST['add']) ) ? $_REQUEST['add'] : '';
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$txtEmpNo='';$txtfName='';$txtmName='';$txtlName='';$txtExtName='';$txtNickName='';$bdate='';$txtBirthPlace='';$selGender='';$selCivilStat='';$txCitizenship='';$cert_id='';$txReligion='';$txHeight='';$txWeight='';$txBloodType='';$txTIN='';$txpagibig='';$txphilhealth='';$txtSSS='';$txtGSIS='';$selCurProvince='';$selCurCityMun='';$selCurBrngy='';$curStreet='';$curr_zipcode='';$curTelNo='';$selProvincePerm='';$selCityMunPerm='';$selBrngyPerm='';$permStreet='';$permZipCode='';$permTelNo='';$txEmail='';$txCellphone='';

$txtspfName='';$txtspmName='';$txtsplName='';$txtspExtName='';$txtExtName='';$txtspOccupation='';$txspbusname='';$txtspbusadd='';$txspTelNo='';$txtfrfName='';$txtfrmName='';$txtfrlName='';$txtfrExtName='';$txtmrfName='';$txtmrmName='';$txtmrlName='';
$current_address=''; $permanent_address='';
$ctc_no='';$ctc_issue_place='';$ctc_issue_date='';$date_accomplished='';$work_status='';$dp_id='';
$monAmIn='';$monAmOut='';$monPmIn='';$monPmOut='';$tueAmIn='';$tueAmOut='';$tuePmIn='';$tuePmOut='';$wedAmIn='';$wedAmOut='';$wedPmIn='';$wedPmOut='';$thuAmIn='';$thuAmOut='';$thuPmIn='';$thuPmOut='';$friAmIn='';$friAmOut='';$friPmIn='';$friPmOut='';$satAmIn='';$satAmOut='';$satPmIn='';$satPmOut='';
$txtPagIbigContrib='';$txtPHContrib=''; $txtSSSContrib='';$es_type='';$txSalMonth='';$txSalWorkDays='';$txSalDay='';$txSalHour='';$txSalMin='';$selSalMon='';$selSalDay='';$selSalYr='';$txSalDate=''; $has_attendance=0;
if($eid){
	$q = $db->select('employee','*',array('emp_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$cert_id = $r['cert_id'];
		$txtEmpNo = $r['emp_no'];
		$txtfName = $r['fname'];
		$txtmName = $r['mname'];
		$txtlName = $r['lname'];
		$txtExtName = $r['extname'];
		$txtNickName = $r['nickname'];
		$bdate = $r['bdate'];
		$txtBirthPlace = $r['bplace'];
		$selGender = $r['gender'];
		$selCivilStat = $r['civil_status'];
		$txCitizenship = $r['citizenship'];
		$txReligion = $r['religion'];
		$txHeight = $r['height'];
		$txWeight = $r['weight'];
		$txBloodType = $r['bloodtype'];
		$txTIN = $r['tin'];
		$txpagibig = $r['pagibig'];
		$txphilhealth = $r['philhealth'];
		$txtSSS = $r['sss'];
		$txtGSIS = $r['gsis'];

		$txtPagIbigContrib = ($r['pagibig_contribution']) ? functions::formatMoney($r['pagibig_contribution']) : '';
		$pagibig_autodeduct = $r['pagibig_autodeduct'];
		$txtPHContrib = functions::formatMoney($r['philhealth_contribution']);
		$philhealth_autodeduct = $r['philhealth_autodeduct'];
		$txtSSSContrib = functions::formatMoney($r['sss_contribution']);
		$sss_autodeduct = $r['sss_autodeduct'];
		$gsis_autodeduct = $r['gsis_autodeduct'];

		$curStreet = $r['curr_street'];
		$curProvince = $db->getValue('refprovince','provDesc',array('provCode'=>$r['curr_province']));
		$curProvince = ($curProvince) ? ', '.$curProvince : '';
		$curCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$r['curr_cityMun']));
		$curCity = ($curCity) ? ', '.$curCity : '';
		$curBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$r['curr_add']));
		$curr_zipcode = ($r['curr_zipcode']) ? ', '.$r['curr_zipcode'] : '';
		$current_address = $curStreet.' '.strtoupper($curBrngy).$curCity.$curProvince.$curr_zipcode;
		$curTelNo = $r['curr_tel'];

		$permStreet = $r['perm_street'];
		$permProvince = $db->getValue('refprovince','provDesc',array('provCode'=>$r['perm_province']));
		$permCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$r['perm_cityMun']));
		$permBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$r['perm_add']));
		$permanent_address = $permStreet.' '.strtoupper($permBrngy).', '.$permCity.', '.$permProvince.', '.$r['perm_zipcode'];
		$permTelNo = $r['perm_tel'];

		$txEmail = $r['email'];
		$txCellphone = $r['cell_no'];

		$txtspfName = $r['sp_fname'];
		$txtspmName = $r['sp_mname'];
		$txtsplName = $r['sp_lname'];
		$txtspExtName = $r['sp_extname'];
		$txtExtName = $r['extname'];
		$txtspOccupation = $r['sp_occupation'];
		$txspbusname = $r['sp_bus_name'];
		$txtspbusadd = $r['sp_bus_address'];
		$txspTelNo = $r['sp_tel_no'];
		$txtfrfName = $r['fr_fname'];
		$txtfrmName = $r['fr_mname'];
		$txtfrlName = $r['fr_lname'];
		$txtfrExtName = $r['fr_extname'];
		$txtmrfName = $r['mr_fname'];
		$txtmrmName = $r['mr_mname'];
		$txtmrlName = $r['mr_lname'];
		$ctc_no = $r['ctc_no'];
		$ctc_issue_place = $r['ctc_issue_place'];
		$ctc_issue_date = $r['ctc_issue_date'];
		$date_accomplished = $r['date_accomplished'];
		$work_status = $r['work_status'];
		$has_attendance = $r['has_attendance'];
	endwhile;
	if($has_attendance){
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
		endwhile;
	}
	$qSal = $db->select('emp_salary','*',array('emp_id'=>$eid),'ORDER BY es_date DESC, es_id DESC');
	if( $db->num_rows($qSal) > 0 ){
		$rSal = $db->fetch_array($qSal);
		$txSalMonth = functions::formatMoney($rSal['es_salary']);
		$txSalDay = functions::formatMoney($rSal['es_daily']);
		$txSalHour = functions::formatMoney($rSal['es_hourly']);
		$txSalMin = functions::formatMoney($rSal['es_minute']);
		$txSalWorkDays = $rSal['working_days'];
		$txSalDate = $rSal['es_date'];
		$es_type = $rSal['es_type'];
		$salDateExp = explode('-',$txSalDate);
		if( count($salDateExp)==3 ){
			$selSalMon=$salDateExp[1];
			$selSalDay=$salDateExp[2];
			$selSalYr=$salDateExp[0];
		}
	}
}
$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
$file = ($fileName && file_exists('../img_emp/'.$fileName)) ? $fileName : 'blank-pic.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Detail</title>
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
</head>
<body bgcolor="#FFF">
<!-- body content: start here-->

<div align="center" style="padding:10px;background-color:#FFF;">
<?php if($add){?>
<div align="right" style="padding-top:5px;background-color:#FFF;"><a href="employee_add.php" class="btn btn-info btn-small">Add New Employee</a></div>
<?php } ?>
			<div align="center"><h2>PERSONAL DATA SHEET</h2></div>
			<table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;background-color:#FFF;">
				<tr>
					<td colspan="3" style="background-color:#E4E1E1"><div align="center"><strong>I. PERSONAL INFORMATION</strong> &nbsp;&nbsp;&nbsp; <a id="lnkEmpEdt" href="#" class="thickbox" onclick="showThis(this.id,'employee_edit.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Employee Detail')"><i class="halflings-icon pencil"></i></a></div></td>
				</tr>
				<tr>
					<td width="20%"><strong>EMPLOYEE NUMBER</strong></td>
					<td width="45%"><?php echo $txtEmpNo;?></td>
					<td rowspan="10"><div align="center"><img width="300" height="300" src="../img_emp/<?php echo $file;?>"></div></td>
				</tr>
				<tr>
					<td><strong>LAST NAME</strong></td>
					<td><?php echo $txtlName;?></td>
				</tr>
				<tr>
					<td><strong>FIRST NAME</strong></td>
					<td><?php echo $txtfName;?></td>
				</tr>
				<tr>
					<td><strong>MIDDLE NAME</strong></td>
					<td><?php echo $txtmName;?></td>
				</tr>
				<tr>
					<td><strong>Suffix (JR, SR, III)</strong></td>
					<td><?php echo $txtExtName;?></td>
				</tr>
				<tr>
					<td><strong>NICK NAME</strong></td>
					<td><?php echo $txtNickName;?></td>
				</tr>
				<tr>
					<td><strong>DATE OF BIRTH</strong></td>
					<td><?php echo functions::datearr($bdate); if($bdate){?>&nbsp;&nbsp;&nbsp;&nbsp; (AGE: <?php echo functions::year_diff($bdate,date('Y-m-d'));?>)<?php }?></td>
				</tr>
				<tr>
					<td><strong>PLACE OF BIRTH</strong></td>
					<td><?php echo $txtBirthPlace;?></td>
				</tr>
				<tr>
					<td><strong>GENDER</strong></td>
					<td><?php echo $selGender;?></td>
				</tr>
				<tr>
					<td><strong>CIVIL STATUS</strong></td>
					<td><?php echo $selCivilStat;?></td>
				</tr>
			</table>
			<table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;background-color:#FFF;">
				<tr>
					<td width="20%"><strong>CITIZENSHIP</strong></td>
					<td><?php echo $txCitizenship;?></td>
				</tr>
				<tr>
					<td><strong>HEIGHT (m)</strong></td>
					<td><?php echo $txHeight;?></td>
				</tr>
				<tr>
					<td><strong>WEIGHT (kg)</strong></td>
					<td><?php echo $txWeight;?></td>
				</tr>
				<tr>
					<td><strong>BLOOD TYPE</strong></td>
					<td><?php echo $txBloodType;?></td>
				</tr>
				<tr>
					<td><strong>TIN</strong></td>
					<td><?php echo $txTIN;?></td>
				</tr>
				<tr>
					<td><strong>PAG-IBIG ID</strong></td>
					<td><?php echo $txpagibig;?><?php if($txtPagIbigContrib){ ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Additional Contribution: <?php echo $txtPagIbigContrib;?></strong><?php } ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>Payroll auto-deduction <?php if($pagibig_autodeduct){echo '<strong>(enabled)</strong>';}else{echo '<strong>(disabled)</strong>';}?></i></td>
				</tr>
				<tr>
					<td><strong>PHILHEALTH No</strong></td>
					<td><?php echo $txphilhealth;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>Payroll auto-deduction <?php if($philhealth_autodeduct){echo '<strong>(enabled)</strong>';}else{echo '<strong>(disabled)</strong>';}?></i></td>
				</tr>
				<tr>
					<td><strong>SSS No</strong></td>
					<td><?php echo $txtSSS;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>Payroll auto-deduction <?php if($sss_autodeduct){echo '<strong>(enabled)</strong>';}else{echo '<strong>(disabled)</strong>';}?></i></td>
				</tr>
				<tr>
					<td><strong>GSIS No</strong></td>
					<td><?php echo $txtGSIS;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>Payroll auto-deduction <?php if($gsis_autodeduct){echo '<strong>(enabled)</strong>';}else{echo '<strong>(disabled)</strong>';}?></i></td>
				</tr>
				<tr>
					<td><strong>PRESENT ADDRESS</strong></td>
					<td><?php echo $current_address;?></td>
				</tr>
				<tr>
					<td><strong>TELEPHONE No.</strong></td>
					<td><?php echo $curTelNo;?></td>
				</tr>
				<tr>
					<td><strong>HOME ADDRESS</strong></td>
					<td><?php echo $permanent_address;?></td>
				</tr>
				<tr>
					<td><strong>TELEPHONE No.</strong></td>
					<td><?php echo $permTelNo;?></td>
				</tr>
				<tr>
					<td><strong>E-MAIL ADDRESS</strong></td>
					<td><?php echo $txEmail;?></td>
				</tr>
				<tr>
					<td><strong>CELLPHONE No.</strong></td>
					<td><?php echo $txCellphone;?></td>
				</tr>
			</table>
			<table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;background-color:#FFF;">
				<tr>
					<td colspan="2" style="background-color:#E4E1E1"><div align="center"><strong>II. FAMILY BACKGROUND</strong> &nbsp;&nbsp;&nbsp; <a id="lnkfamback" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_family.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Family Background')"><i class="halflings-icon pencil"></i></a></div></td>
				</tr>
				<tr>
					<td><strong>FATHER'S NAME</strong></td>
					<td><?php echo $txtfrfName.' '.$txtfrmName.' '.$txtfrlName; echo ($txtfrExtName) ? ', '.$txtfrExtName : '';?></td>
				</tr>
				<tr>
					<td><strong>MOTHER'S NAME</strong></td>
					<td><?php echo $txtmrfName.' '.$txtmrmName.' '.$txtmrlName;?></td>
				</tr>
				<tr>
					<td width="20%"><strong>SPOUSE'S NAME</strong></td>
					<td><?php echo $txtspfName.' '.$txtspmName.' '.$txtsplName; echo ($txtspExtName) ? ', '.$txtspExtName : '';?></td>
				</tr>
				<tr>
					<td><strong>OCCUPATION</strong></td>
					<td><?php echo $txtspOccupation;?></td>
				</tr>
				<tr>
					<td><strong>BUSINESS NAME</strong></td>
					<td><?php echo $txspbusname;?></td>
				</tr>
				<tr>
					<td><strong>BUS. ADDRESS</strong></td>
					<td><?php echo $txtspbusadd;?></td>
				</tr>
				<tr>
					<td><strong>TELEPHONE No.</strong></td>
					<td><?php echo $txspTelNo;?></td>
				</tr>
			</table>
			<table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;background-color:#FFF;">
				<tr>
					<td colspan="3"><strong>CHILDREN</strong></td>
				</tr>
				<tr>
					<td width="20%">&nbsp;</td>
					<td><strong>NAME</strong></td>
					<td width="25%"><strong>DATE OF BIRTH</strong></td>
				</tr>
				<?php
				$countChild=0;
				$qChild = $db->select('emp_child','*',array('emp_id'=>$eid));
				while($rChild = $db->fetch_array($qChild)):
					$countChild++;
				?>
				<tr>
					<td>&nbsp;</td>
					<td><?php echo $countChild.'. '.$rChild['ec_fname'].' '.$rChild['ec_mname'].' '.$rChild['ec_lname']; echo ($rChild['ec_extname']) ? ', '.$rChild['ec_extname'] : '';?></td>
					<td><?php echo functions::datearr($rChild['ec_bdate']);?>&nbsp;&nbsp;&nbsp;&nbsp; (AGE: <?php echo functions::year_diff($rChild['ec_bdate'],date('Y-m-d'));?>)</td>
				</tr>
				<?php endwhile;
				if($countChild==0){
				echo '<tr><td colspan="3"><div align="center">--- No Record --</div></td></tr>';
				}
				?>
			</table><br><br>
			<table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;background-color:#FFF;">
				<tr>
					<td colspan="8" style="background-color:#E4E1E1"><div align="center"><strong>III. EDUCATIONAL BACKGROUND</strong> &nbsp;&nbsp;&nbsp; <a id="lnkfedback" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_educ.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Educational Background')"><i class="halflings-icon pencil"></i></a></div></td>
				</tr>
				<tr style="background-color:#ececec">
					<td rowspan="2"><strong>LEVEL</strong></td>
					<td rowspan="2"><strong>NAME OF SCHOOL</strong></td>
					<td rowspan="2"><strong>DEGREE COURSE</strong></td>
					<td rowspan="2"><div align="center"><strong>YEAR GRADUATED</strong> <br><i>(if graduated)</i></div></td>
					<td rowspan="2"><strong>HIGHEST GRADE /<br>LEVEL /<br>UNITS EARNED</strong> <br><i>(if NOT graduated)</i></td>
					<td colspan="2"><div align="center"><strong>INCLUSIVE DATES OF ATTENDANCE</strong></div></td>
					<td><strong>SCHOLARSHIP /<br>ACADEMIC HONORS RECEIVED</strong></td>
				</tr>
				<tr style="background-color:#ececec">
					<td><div align="center"><strong>FROM</strong></div></td>
					<td><div align="center"><strong>TO</strong></div></td>
					<td></td>
				</tr>
				<?php
				$countAllLevel=0;
				$arrLevel = array('ELEMENTARY','SECONDARY','VOCATIONAL','COLLEGE','GRADUATE');
				foreach($arrLevel as $level):
					$qCol = $db->select('emp_education','*',array('emp_id'=>$eid,'ee_level'=>$level));
					while($rCol = $db->fetch_array($qCol)):
						$countAllLevel++;
				?>
				<tr>
					<td><?php echo $rCol['ee_level']?></td>
					<td><?php echo ucwords(strtolower($rCol['ee_school']))?></td>
					<td><?php echo ucwords(strtolower($rCol['ee_course']))?></td>
					<td><div align="center"><?php echo $rCol['ee_year_grad']?></div></td>
					<td><?php echo $rCol['ee_highest_level']?></td>
					<td><div align="center"><?php echo $rCol['ee_date_from']?></div></td>
					<td><div align="center"><?php echo $rCol['ee_date_to']?></div></td>
					<td><?php echo ucwords(strtolower($rCol['ee_honors']))?></td>
				</tr>
				<?php
					endwhile;
				endforeach;
				if($countAllLevel==0){
				echo '<tr><td colspan="8"><div align="center">--- No Record --</div></td></tr>';
				}
				?>
			</table><br><br>
			<table width="100%" align="center" border="1" class="table table-bordered" style="font-size: 12px;background-color:#FFF;">
				<tr>
					<td colspan="6" style="background-color:#E4E1E1"><div align="center"><strong>IV. PRC ISSUED LICENSE(S)</strong> &nbsp;&nbsp;&nbsp; <a id="lnkprc" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_prc.php?eid=<?php echo functions::encode($eid)?>&fromED=t','PRC ISSUED LICENSE(S)')"><i class="halflings-icon pencil"></i></a></div></td>
				</tr>
				<tr style="background-color:#ececec">
					<td rowspan="2" width="32%"><strong>RA 1080 (BOARD/BAR)<br>UNDER SPECIAL LAWS / CES / CESS</strong></td>
					<td rowspan="2" width="8%"><div align="center"><strong>RATING</strong></div></td>
					<td rowspan="2" width="10%"><div align="center"><strong>DATE OF EXAMINATION</strong></div></td>
					<td rowspan="2" width="30%"><strong>PLACE OF EXAMINATION</strong></td>
					<td colspan="2" width="20%"><strong>LICENSE</strong> (if applicable)</td>
				</tr>
				<tr style="background-color:#ececec">
					<td width="10%"><div align="center"><strong>Number</strong></div></td>
					<td width="10%"><div align="center"><strong>Date of Release</strong></div></td>
				</tr>
				<?php
				 $countLicenses=0;
				 $q = $db->select('emp_prc_license','*',array('emp_id'=>$eid),'ORDER BY el_exam_date');
				 while($r = $db->fetch_array($q)):
				 	$countLicenses++;
				 ?>
				<tr>
					<td><?php echo $r['el_title']?></td>
					<td><div align="center"><?php echo $r['el_rating']?></div></td>
					<td><div align="center"><?php echo functions::datearr($r['el_exam_date'])?></div></td>
					<td><?php echo $r['el_exam_place']?></td>
					<td><div align="center"><?php echo $r['el_no']?></div></td>
					<td><div align="center"><?php echo functions::datearr($r['el_date_release'])?></div></td>
				</tr>
				<?php endwhile;
				if($countLicenses==0){
				echo '<tr><td colspan="6"><div align="center">--- No Record --</div></td></tr>';
				}else{
				?>
				<tr>
					<td colspan="6"><div align="center">TOTAL NUMBER PRC ISSUED LICENSE(S): <?php echo $countLicenses;?></div></td>
				</tr>
				<?php }?>
			</table><br><br>
			<table width="100%" align="center" border="1" class="table table-bordered" style="font-size: 12px;background-color:#FFF;">
				<tr>
					<td colspan="5" style="background-color:#E4E1E1"><div align="center"><strong>V. WORK EXPERIENCES</strong> <i>(Start from your current work)</i> &nbsp;&nbsp;&nbsp; <a id="lnkworkexp" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_work_exp.php?eid=<?php echo functions::encode($eid)?>&fromED=t','WORK EXPERIENCES')"><i class="halflings-icon pencil"></i></a></div></td>
				</tr>
				<tr style="background-color:#ececec">
					<td colspan="2" width="15%"><div align="center"><strong>INCLUSIVE DATE(S)</strong></div></td>
					<td rowspan="2" width="35%"><strong>POSITION</strong></td>
					<td rowspan="2" width="35%"><strong>DEPARTMENT / AGENCY / OFFICE / COMPANY</strong></td>
					<td rowspan="2" width="10%"><div align="center"><strong>YEAR(S) IN SERVICE</strong></div></td>
				</tr>
				<tr style="background-color:#ececec">
					<td width="7%"><div align="center"><strong>From</strong></div></td>
					<td width="7%"><div align="center"><strong>To</strong></div></td>
				</tr>
				<?php
				$countYears=0;
				$countWrkExp=0;
				$q = $db->select('emp_work_exp','*',array('emp_id'=>$eid),'ORDER BY ewe_date_from DESC');
				while($r = $db->fetch_array($q)):
					$countWrkExp++;
					$countYears +=functions::year_diff($r['ewe_date_from'],$r['ewe_date_to']);
				?>
				<tr>
					<td><div align="center"><?php echo functions::datearr($r['ewe_date_from'])?></div></td>
					<td><div align="center"><?php echo functions::datearr($r['ewe_date_to'])?></div></td>
					<td><?php echo $r['ewe_position']?></td>
					<td><?php echo $r['ewe_company']?></td>
					<td><div align="center"><?php echo functions::year_diff($r['ewe_date_from'],$r['ewe_date_to']);?></div></td>
				</tr>
				<?php endwhile;
				if($countWrkExp==0){
				echo '<tr><td colspan="6"><div align="center">--- No Record --</div></td></tr>';
				}else{?>
				<tr>
					<td colspan="6"><div align="center">TOTAL YEARS OF WORK EXPERIENCES: <?php echo $countYears;?></div></td>
				</tr>
				<?php }?>
			</table><br><br>
			<table width="100%" align="center" border="1" class="table table-bordered" style="font-size: 12px;background-color:#FFF;">
				<tr>
					<td colspan="6" style="background-color:#E4E1E1"><div align="center"><strong>VI. RELEVANT TRAININGS </strong> <i>(with Certificate Attached)</i> &nbsp;&nbsp;&nbsp; <a id="lnkreltraining" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_training.php?eid=<?php echo functions::encode($eid)?>&fromED=t','RELEVANT TRAININGS')"><i class="halflings-icon pencil"></i></a></div></td>
				</tr>
				<tr style="background-color:#ececec">
					<td rowspan="2" width="30%"><strong>TITLE OF SEMINAR / CONFERENCE / <br>WORKSHOP / SHORT COURSES</strong></td>
					<td colspan="2" width="20%"><div align="center"><strong>INCLUSIVE DATE(S) OF ATTENDANCE</strong></div></td>
					<td rowspan="2" width="8%"><div align="center"><strong>NO. OF HOUR(S)</strong></div></td>
					<td rowspan="2" width="30%"><strong>CONDUCTED / SPONSORED BY</strong></td>
					<td rowspan="2" width="12%"><div align="center"><strong>CERTIFICATE</strong></div></td>
				</tr>
				<tr style="background-color:#ececec">
					<td width="10%"><div align="center"><strong>From</strong></div></td>
					<td width="10%"><div align="center"><strong>To</strong></div></td>
				</tr>
				<?php
				$countHours=0;$countTraining=0;
				$q = $db->select('emp_training','*',array('emp_id'=>$eid,'et_type'=>'training'),'ORDER BY et_date_from DESC');
				while($r = $db->fetch_array($q)):
					$countTraining++;
					if($r['et_no_hour'])
						$countHours+=is_numeric($r['et_no_hour']) ? $r['et_no_hour'] : 0;
				?>
				<tr>
					<td><?php echo $r['et_title']?></td>
					<td><div align="center"><?php echo functions::datearr($r['et_date_from'])?></div></td>
					<td><div align="center"><?php echo functions::datearr($r['et_date_to'])?></div></td>
					<td><div align="center"><?php echo $r['et_no_hour'];?></div></td>
					<td><?php echo $r['et_sponsored_by']?></td>
					<td>
						<?php
						$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$r['cert_id']));
						$file = ($fileName) ? $fileName : '';
						?>
						<div align="center">
							<?php if($file){?>
							<a id="vw<?php echo $r['et_id']?>" style="cursor: pointer;" class="thickbox" onclick="showThis(this.id,'employee_cert_view.php?crtID=<?php echo functions::encode($r['cert_id'])?>','View Certificate','1')"><img height="100" width="100" src="../img_emp/<?php echo $file;?>"></a>
							<?php }else{echo 'None';} ?>
						</div>
					</td>
				</tr>
				<?php endwhile;
				if($countTraining==0){
				echo '<tr><td colspan="6"><div align="center">--- No Record --</div></td></tr>';
				}else{?>
				<tr>
					<td colspan="6"><div align="center">TOTAL NUMBER OF HOURS: <?php echo $countHours?></div></td>
				</tr>
				<?php }?>
			</table><br><br>
			<table width="100%" align="center" border="1" class="table table-bordered" style="font-size: 12px;background-color:#FFF;">
				<tr>
					<td colspan="3" style="background-color:#E4E1E1"><div align="center"><strong>VII. SPECIAL LICENSES ISSUED BY PROFESSIONAL BODY </strong> <i>(e.g, TESDA, Driver's License, etc.)</i> &nbsp;&nbsp;&nbsp; <a id="lnkaddlicense" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_license.php?eid=<?php echo functions::encode($eid)?>&fromED=t','SPECIAL LICENSES')"><i class="halflings-icon pencil"></i></a></div></td>
				</tr>
				<tr style="background-color:#ececec">
					<td width="50%"><strong>TITLE OF SPECIAL LICENSES</strong></td>
					<td width="30%"><strong>LICENSES No.</strong></td>
					<td width="10%"><div align="center"><strong>DATE ISSUED</strong></div></td>
				</tr>
				<?php
				$countSpeLi=0;
				$q = $db->select('emp_special_license','*',array('emp_id'=>$eid),'ORDER BY esl_date DESC,esl_title');
				while($r = $db->fetch_array($q)):
					$countSpeLi++;
				?>
				<tr>
					<td><?php echo $r['esl_title']?></td>
					<td><?php echo $r['esl_no']?></td>
					<td><div align="center"><?php echo functions::datearr($r['esl_date'])?></div></td>
				</tr>
				<?php endwhile;
				if($countSpeLi==0){
				echo '<tr><td colspan="3"><div align="center">--- No Record --</div></td></tr>';
				}else{?>
				<tr>
					<td colspan="3"><div align="center">TOTAL NUMBER OF SPECIAL LICENSES: <?php echo $countSpeLi?></div></td>
				</tr>
				<?php }?>
			</table><br><br>
			<table width="100%" align="center" border="1" class="table table-bordered" style="font-size: 12px;background-color:#FFF;">
				<tr>
					<td colspan="2" style="background-color:#E4E1E1"><div align="center"><strong>VIII. SPECIAL AWARD AND RECOGNITION RECEIVED </strong> &nbsp;&nbsp;&nbsp; <a id="lnkspecaward" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_spec_award.php?eid=<?php echo functions::encode($eid)?>&fromED=t','SPECIAL AWARD AND RECOGNITION')"><i class="halflings-icon pencil"></i></a></div></td>
				</tr>
				<tr style="background-color:#ececec">
					<td width="35%"><strong>SPECIAL AWARDS AND RECOGNITIONS RECEIVED</strong></td>
					<td width="25%"><strong>DATE RECEIVED</strong></td>
				</tr>
				<?php
				$countAward=0;
				$q = $db->select('emp_special_award','*',array('emp_id'=>$eid),'ORDER BY esa_date');
				while($r = $db->fetch_array($q)):
					$countAward++;
				?>
				<tr>
					<td><?php echo ucwords(strtolower($r['esa_title']))?></td>
					<td><?php echo functions::datearr($r['esa_date'])?></td>
				</tr>
				<?php endwhile;
				if($countAward==0){
				echo '<tr><td colspan="2"><div align="center">--- No Record --</div></td></tr>';
				}else{?>
				<tr>
					<td colspan="2"><div align="center">TOTAL NUMBER OF SPECIAL AWARDS AND RECOGNITIONS RECEIVED: <?php echo $countAward?></div></td>
				</tr>
				<?php }?>
			</table><br><br>
			<table width="100%" align="center" border="1" class="table table-bordered" style="font-size: 12px;background-color:#FFF;">
				<tr>
					<td colspan="6" style="background-color:#E4E1E1"><div align="center"><strong>IX. TRAININGS / SEMINAR </strong> <i>(with Certificate Attached)</i> &nbsp;&nbsp;&nbsp; <a id="lnktrainingseminar" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_seminar.php?eid=<?php echo functions::encode($eid)?>&fromED=t','TRAININGS / SEMINAR')"><i class="halflings-icon pencil"></i></a></div></td>
				</tr>
				<tr style="background-color:#ececec">
					<td rowspan="2" width="35%"><strong>TITLE OF SEMINAR / CONFERENCE / <br>WORKSHOP / SHORT COURSES</strong></td>
					<td colspan="2" width="15%"><div align="center"><strong>INCLUSIVE DATE(S) OF ATTENDANCE</strong></div></td>
					<td rowspan="2" width="5%"><div align="center"><strong>NO. OF HOUR(S)</strong></div></td>
					<td rowspan="2" width="25%"><strong>CONDUCTED / SPONSORED BY</strong></td>
					<td rowspan="2" width="10%"><div align="center"><strong>CERTIFICATE</strong></div></td>
				</tr>
				<tr style="background-color:#ececec">
					<td width="7%"><div align="center"><strong>From</strong></div></td>
					<td width="7%"><div align="center"><strong>To</strong></div></td>
				</tr>
				<?php
				$countHours=0;$countSeminar=0;
				$q = $db->select('emp_training','*',array('emp_id'=>$eid,'et_type'=>'seminar'),'ORDER BY et_date_from DESC');
				while($r = $db->fetch_array($q)):
					$countHours+=is_numeric($r['et_no_hour']) ? $r['et_no_hour'] : 0;
					$countSeminar++;
				?>
				<tr>
					<td><?php echo $r['et_title']?></td>
					<td><div align="center"><?php echo functions::datearr($r['et_date_from'])?></div></td>
					<td><div align="center"><?php echo functions::datearr($r['et_date_to'])?></div></td>
					<td><div align="center"><?php echo $r['et_no_hour'];?></div></td>
					<td><?php echo $r['et_sponsored_by']?></td>
					<td>
						<?php
						$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$r['cert_id']));
						$file = ($fileName) ? $fileName : '';
						?>
						<div align="center">
							<?php if($file){ ?>
							<a id="vw<?php echo $r['et_id']?>" style="cursor: pointer;" class="thickbox" onclick="showThis(this.id,'employee_cert_view.php?crtID=<?php echo functions::encode($r['cert_id'])?>','View Certificate','1')"><img height="100" width="100" src="../img_emp/<?php echo $file;?>"></a>
							<?php }else{echo 'None';} ?>
						</div>
					</td>
				</tr>
				<?php endwhile;
				if($countSeminar==0){
				echo '<tr><td colspan="6"><div align="center">--- No Record --</div></td></tr>';
				}else{?>
				<tr>
					<td colspan="6"><div align="center">TOTAL NUMBER OF HOURS: <?php echo $countHours?></div></td>
				</tr>
				<?php }?>
			</table><br><br>
			<table width="100%" align="center" border="0" class="table table-bordered" style="font-size: 12px;background-color:#FFF;">
				<tr>
					<td colspan="3" style="background-color:#E4E1E1"><div align="center"><strong>REFERENCES </strong> <i>(Person not related by consanguinity)</i> &nbsp;&nbsp;&nbsp; <a id="lnkreferences" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_references.php?eid=<?php echo functions::encode($eid)?>&fromED=t','REFERENCES')"><i class="halflings-icon pencil"></i></a></div></td>
				</tr>
				<tr style="background-color:#ececec">
					<td width="35%"><strong>NAME</strong></td>
					<td width="25%"><strong>ADDRESS</strong></td>
					<td width="25%"><strong>CONTACT No.</strong></td>
				</tr>
				<?php
				$countReferences=0;
				$q = $db->select('emp_references','*',array('emp_id'=>$eid),'ORDER BY er_name');
				while($r = $db->fetch_array($q)):
					$countReferences++;
				?>
				<tr>
					<td><?php echo $r['er_name']?></td>
					<td><?php echo $r['er_address']?></td>
					<td><?php echo $r['er_tel_no']?></td>
				</tr>
				<?php endwhile;
				if($countReferences==0){
				echo '<tr><td colspan="6"><div align="center">--- No Record --</div></td></tr>';
				}
				?>
			</table><br><br>
			<div align="right"><a id="adcCTC" href="#" class="thickbox" title="Manage CTC" onclick="showThis(this.id,'employee_add_ctc.php?eid=<?php echo functions::encode($eid)?>&fromED=t','CTC Details')"><i class="halflings-icon pencil"></i> Manage</a></a></div>
			<table width="100%" align="center" border="0" class="table table-bordered" style="font-size: 12px;">
				<tr>
					<td width="20%">COMMUNITY TAX CERTIFICATE NO.</td>
					<td><?php echo $ctc_no?></td>
				</tr>
				<tr>
					<td>ISSUED AT</td>
					<td><?php echo $ctc_issue_place?></td>
				</tr>
				<tr>
					<td>ISSUED ON</td>
					<td><?php echo functions::datearr($ctc_issue_date);?></td>
				</tr>
				<tr>
					<td>DATE ACCOMPLISHED</td>
					<td><?php echo functions::datearr($date_accomplished);?></td>
				</tr>
				<tr>
					<td>DEPARTMENT / POSITION</td>
					<td>
						<?php
						$qDep = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$eid),'AND ep.dp_id=dp.dp_id ORDER BY dp.pos_name');
						$countDep = $db->num_rows($qDep);
						if($countDep>1){
						?>
						<table width="70%">
							<tr>
								<td><strong>Department</strong></td>
								<td><strong>Position</strong></td>
							</tr>
							<?php
							while($rDep = $db->fetch_array($qDep)):
							?>
							<tr>
								<td><?php echo $db->getValue('department','dep_name',array('dep_id'=>$rDep['dep_id']))?></td>
								<td><?php echo $rDep['pos_name']?></td>
							</tr>
						<?php endwhile;?>
						</table><br><br>
						<?php
						}else if($countDep==1){
							$rDep = $db->fetch_array($qDep);
						?>
							<strong><?php echo $db->getValue('department','dep_name',array('dep_id'=>$rDep['dep_id']));?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<?php echo $rDep['pos_name']?>)</strong>
						<?php }?>
					</td>
				</tr>

				<tr>
					<td>SITE / PROJECT ASSIGNMENT</td>
					<td>
						<div align="right"><a id="adcProjAssgnmnt" href="#" class="thickbox" title="Manage Site/Project Assignment" onclick="showThis(this.id,'employee_add_assign.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Employee Detail')"><i class="halflings-icon pencil"></i> Manage</a></div>
						<?php
						$qSite = $db->select('emp_site_assign esa, project proj','*',array('emp_id'=>$eid),'AND esa.proj_id=proj.proj_id ORDER BY proj.date_start,proj.proj_name');
						$countSite = $db->num_rows($qSite);
						if($countSite){
						?>
						<table width="90%">
							<tr>
								<th ><strong>Site / Project</strong></th>
								<th width="15%">Duration</th>
								<th width="15%"><strong>Status</strong></th>
							</tr>
							<?php
							while($rSite = $db->fetch_array($qSite)):
							$current='';
							if($rSite['date_started'] && $rSite['date_ended']){
								if($rSite['date_started']<=date('Y-m-d') && $rSite['date_ended']>=date('Y-m-d'))
									$current='current';
							}
							else if($rSite['date_started']){
								if( $rSite['date_started']<=date('Y-m-d') )
									$current='current';
							}
							else if($rSite['date_ended']){
								if( $rSite['date_ended']>=date('Y-m-d') )
									$current='current';
							}
							?>
							<tr>
								<td><?php echo strtoupper($rSite['proj_name'])?></td>
								<td><?php echo functions::datearr($rSite['date_started']).' - '.functions::datearr($rSite['date_ended']); ?></td>
								<td><?php echo ($current) ? 'current assignment' : '';?></td>
							</tr>
						<?php endwhile;?>
						</table><br><br>
						<?php
						}else{?>
							<strong>No Assignment</strong>
						<?php }?>
					</td>
				</tr>
				<tr>
					<td>WORK STATUS</td>
					<td>
						<?php
						$qStat = $db->select('emp_work_status','*',array('emp_id'=>$eid,'ews_stat'=>$work_status),'ORDER BY ews_date DESC');
						$rStat = $db->fetch_array($qStat);
						$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : '-----';
						$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? '&nbsp;(Project Based)' : '';
						$wrkDate = (isset($rStat['ews_date'])) ? functions::datearr($rStat['ews_date']) : '-----';
						?>
						<strong><?php echo $wrkStat.$projBased;?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<?php echo $wrkDate?>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a id="adcworkstat" href="#" class="thickbox" title="Manage Status" onclick="showThis(this.id,'employee_add_work_stat.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Work Status')"><i class="halflings-icon pencil"></i> Manage</a>
					</td>
				</tr>
				<tr>
					<td>DUTY</td>
					<td>
						<div align="right"><a id="adcDuty" href="#" class="thickbox" title="Manage Duty Hours" onclick="showThis(this.id,'employee_add_duty.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Manage Duty Hours')"><i class="halflings-icon pencil"></i> Manage</a></div>
						<?php if($has_attendance){?>
						<table width="90%">
							<tr>
								<td>&nbsp;</td>
								<td colspan="2"><div align="center">Morning</div></td>
								<td colspan="2"><div align="center">Afternoon</div></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td width="20%"><div align="center">In</div></td>
								<td width="20%"><div align="center">Out</div></td>
								<td width="20%"><div align="center">In</div></td>
								<td width="20%"><div align="center">Out</div></td>
							</tr>
							<tr>
								<td>Monday</td>
								<td><div align="center"><?php echo functions::MilToTwelve($monAmIn);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($monAmOut);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($monPmIn);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($monPmOut);?></div></td>
							</tr>
							<tr>
								<td>Tuesday</td>
								<td><div align="center"><?php echo functions::MilToTwelve($tueAmIn);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($tueAmOut);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($tuePmIn);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($tuePmOut);?></div></td>
							</tr>
							<tr>
								<td>Wednesday</td>
								<td><div align="center"><?php echo functions::MilToTwelve($wedAmIn);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($wedAmOut);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($wedPmIn);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($wedPmOut);?></div></td>
							</tr>
							<tr>
								<td>Thurdays</td>
								<td><div align="center"><?php echo functions::MilToTwelve($thuAmIn);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($thuAmOut);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($thuPmIn);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($thuPmOut);?></div></td>
							</tr>
							<tr>
								<td>Friday</td>
								<td><div align="center"><?php echo functions::MilToTwelve($friAmIn);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($friAmOut);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($friPmIn);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($friPmOut);?></div></td>
							</tr>
							<tr>
								<td>Saturday</td>
								<td><div align="center"><?php echo functions::MilToTwelve($satAmIn);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($satAmOut);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($satPmIn);?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($satPmOut);?></div></td>
							</tr>
						</table>
						<?php }
						else{?>
							<div align="left">Duty Hours Not Applicable</div>
						<?php }?>
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td>SALARY</td>
					<td>
						<?php if($es_type=='fixed'){ ?>
							<strong>Daily Fixed Wage</strong><br><br>
							Daily: <strong><?php echo $txSalDay;?></strong><br><br>
							Date Started: <strong><?php echo functions::datearr($txSalDate);?></strong><br><br>
						<?php }elseif($es_type=='flexible'){ ?>
							Monthly: <strong><?php echo $txSalMonth;?></strong><br><br>
							Date Started: <strong><?php echo functions::datearr($txSalDate);?></strong><br><br>
						<?php }else{echo 'Salary Type Not Specified. Please Click Manage Salary Button and Specify Salary Type';} ?>
						<div align="left"><a id="adcSal" href="#" class="btn btn-info btn-setting thickbox btn-small" onclick="showThis(this.id,'employee_manage_salary.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Salary Details')">Manage Salary</a></div>
					</td>
				</tr>
				<tr>
					<td>SALARY ADJUSTMENT</td>
					<td><div align="left"><a id="btnAdjustment" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'payroll_deduction_reference_detail.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Salary Adjustment Details','1')">Manage Salary Adjustment</a></div></td>
				</tr>
				<tr>
					<td>LEAVE RECORD</td>
					<td><div align="left"><a id="btnleaveRec" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'employee_leave.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Leave Detail','1')">View Leave Record</a></div></td>
				</tr>
			</table>
</div>
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
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
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