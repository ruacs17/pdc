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
$add = (isset($_REQUEST['add']) && !empty($_REQUEST['add']) ) ? $_REQUEST['add'] : '';
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$txtEmpNo='';$txtfName='';$txtmName='';$txtlName='';$txtExtName='';$txtNickName='';$bdate='';$txtBirthPlace='';$selGender='';$selCivilStat='';$txCitizenship='';$cert_id='';$txReligion='';$txHeight='';$txWeight='';$txBloodType='';$txTIN='';$txpagibig='';$txphilhealth='';$txtSSS='';$txtGSIS='';$selCurProvince='';$selCurCityMun='';$selCurBrngy='';$curStreet='';$curr_zipcode='';$curTelNo='';$selProvincePerm='';$selCityMunPerm='';$selBrngyPerm='';$permStreet='';$permZipCode='';$permTelNo='';$txEmail='';$txCellphone='';
$selDep='';$selPos='';
$txbMon='';$txbDay='';
if( isset($_REQUEST['sd']) && !empty($_REQUEST['sd']) ){
	$_SESSION['es_dsply']=$_REQUEST['sd'];
}
$req_display = ( isset($_SESSION['es_dsply']) ) ? $_SESSION['es_dsply'] : '';

if( isset($_POST['btnShow']) ){
	
	$_SESSION['es_religion'] = (isset($_POST['selReligion'])) ? $_POST['selReligion'] : '';

	$_SESSION['es_gender'] = (isset($_POST['selGender'])) ? $_POST['selGender'] : '';
	$_SESSION['es_civil_status'] = (isset($_POST['selCivilStat'])) ? $_POST['selCivilStat'] : '';
	$_SESSION['es_bloodtype'] = (isset($_POST['selBloodType'])) ? $_POST['selBloodType'] : '';
	$_SESSION['es_wstat'] = (isset($_POST['selWstat'])) ? $_POST['selWstat'] : '';
	$_SESSION['es_bmonth'] = (isset($_POST['bdMon'])) ? $_POST['bdMon'] : '';
	$_SESSION['es_bdate'] = (isset($_POST['bdDay'])) ? $_POST['bdDay'] : '';
	$_SESSION['es_pos'] = (isset($_POST['selPosition'])) ? $_POST['selPosition'] : '';
	$_SESSION['es_dep'] = (isset($_POST['selDepartment']) ) ? $_POST['selDepartment'] : '';
	$_SESSION['es_bplace'] = (isset($_POST['selBplace'])) ? functions::decode($_POST['selBplace']) : '';
	#$_SESSION['es_address'] = (isset($_POST['selAddress'])) ? functions::decode($_POST['selAddress']) : '';
	$_SESSION['es_address'] = (isset($_POST['selAddress'])) ? $_POST['selAddress'] : '';
	$_SESSION['es_proj'] = (isset($_POST['selProj'])) ? $_POST['selProj'] : '';
	
	functions::sendTo(functions::pageName());
	die();
}
$arrCol=array();
$workStat = ( isset($_SESSION['es_wstat']) ) ? $_SESSION['es_wstat'] : '';
if($workStat!='--All--' && $req_display=='wstatus'){
	$arrCol=array_merge($arrCol,array('work_status'=>$workStat));
}

$religion = ( isset($_SESSION['es_religion']) ) ? $_SESSION['es_religion'] : '';
if($religion!='--All--' && $req_display=='religion'){
	$arrCol=array_merge($arrCol,array('religion'=>$religion));
}


$bloodtype = ( isset($_SESSION['es_bloodtype']) ) ? $_SESSION['es_bloodtype'] : '';

if($bloodtype!='--All--' && $req_display=='bloodtype'){
	$arrCol=array_merge($arrCol,array('bloodtype'=>$bloodtype));
}

$civil_status = ( isset($_SESSION['es_civil_status']) ) ? $_SESSION['es_civil_status'] : '';
if($civil_status!='--All--' && $req_display=='civilstatus'){
	$arrCol=array_merge($arrCol,array('civil_status'=>$civil_status));
}

$gender = ( isset($_SESSION['es_gender']) ) ? $_SESSION['es_gender'] : '';
if($gender!='--All--' && $req_display=='gender'){
	$arrCol=array_merge($arrCol,array('gender'=>$gender));
}

$selBplace = ( isset($_SESSION['es_bplace']) ) ? $_SESSION['es_bplace'] : '';
$selDep = ( isset($_SESSION['es_dep']) ) ? $_SESSION['es_dep'] : '';
$selPos = ( isset($_SESSION['es_pos']) ) ? $_SESSION['es_pos'] : '';
$bdMon = ( isset($_SESSION['es_bmonth']) ) ? $_SESSION['es_bmonth'] : '';
$bdDay = ( isset($_SESSION['es_bdate']) ) ? $_SESSION['es_bdate'] : '';

$selAddress = ( isset($_SESSION['es_address']) ) ? $_SESSION['es_address'] : '';
$selProj = ( isset($_SESSION['es_proj']) ) ? $_SESSION['es_proj'] : '';
function empDetail($rEmp,$dep_id=0,&$count=0,$hide=''){
	global $countStatNotif;
	global $db;
	$empCount=1;
	$endofcontractCount=0;
	$emp_status = $db->getValue('emp_work_status','ews_stat',array('emp_id'=>$rEmp['emp_id']),'ORDER BY ews_date DESC LIMIT 1');
	if($emp_status=='End of Contract'){
		$endofcontractCount = $db->getValue('emp_work_status','count(*)',array('emp_id'=>$rEmp['emp_id'],'ews_stat'=>$emp_status));
	}
	$date_hired = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$rEmp['emp_id']),'ORDER BY ews_date LIMIT 1');
	$datetime1 = new DateTime($date_hired);
	$datetime2 = new DateTime(date('Y-m-d'));
	$interval = $datetime1->diff($datetime2);
	$intrval_year = $interval->format('%y');
	$intrval_month = $interval->format('%m');
	$intrval_day = $interval->format('%d');
	$diffInMonths = ($intrval_year * 12) + $intrval_month;
	$display_yr=''; $display_mn=''; $display_day='';
	if($intrval_year){
		$display_yr = ($intrval_year > 1) ? $intrval_year.' Years, ' : $intrval_year.' Year, ';
	}
	if($intrval_month){
		$display_mn = ($intrval_month > 1) ? $intrval_month.' Months, ' : $intrval_month.' Month, ';
	}
	if($intrval_day){
		$display_day = ($intrval_day > 1) ? $intrval_day.' Days ' : $intrval_day.' Day ';
	}
	$bgColorStat='';
	$intval = ($date_hired=='0000-00-00' || $date_hired=='') ? '----' : $display_yr.$display_mn.$display_day;
    ?>
	<tr>
		<td><div align="center"><?php echo $count++; ?></div></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['emp_no'] ?></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['lname'].' '.$rEmp['extname'].', '.$rEmp['fname'].' '.$rEmp['mname'] ?></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['gender'] ?></div></td>
		<td><div align="left" style="padding-left:3px"><?php echo functions::datearr($rEmp['bdate']) ?></div></td>
		<?php if($hide!='bplace'){ ?><td><div align="left" style="padding-left:3px"><?php echo $rEmp['bplace'] ?></div></td><?php } ?>
		<?php if($hide!='position'){ ?>
		<td>
			<div align="left" style="padding-left:3px">
				<?php
				$arr = ($dep_id) ? array('dep_id'=>$dep_id,'emp_id'=>$rEmp['emp_id']) : array('emp_id'=>$rEmp['emp_id']);
				$q = $db->select('emp_position ep, dep_position dp','*',$arr,'AND dp.dp_id=ep.dp_id');
				$cntPos = $db->num_rows($q);
				$countPos=1;
				$position='';
				while($r = $db->fetch_array($q)):
					$position .= $r['pos_name'];
					if($countPos < $cntPos)
						$position.=' / ';
					$countPos++;
				endwhile;
				echo $position;
				?>
			</div>
		</td>
		<?php } ?>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['religion']; ?></div></td>
		<td><div align="center"><?php echo $rEmp['bloodtype']; ?></div></td>
		<td><div align="center"><?php echo $rEmp['civil_status']; ?></div></td>
		<td><div align="center"><?php echo $rEmp['cell_no']; ?></div></td>
		<td style="<?php echo $bgColorStat;?>"><div align="left" style="padding-left:3px;"><?php echo $emp_status; echo ($endofcontractCount > 1) ? ' ('.$endofcontractCount.')' : ''; ?></div></td>
		<td width="3%">
			<div align="center" style="padding-top:3px;padding-bottom:3px;">
				<a id="vw<?php echo functions::encode($count.$rEmp['emp_id'].$dep_id)?>" class="btn btn-mini btn-info thickbox" title="Employee Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_detail.php?eid=<?php echo functions::encode($rEmp['emp_id']);?>','Employee Detail','1')"><i class="halflings-icon white zoom-in"></i></a>
				<a id="edit<?php echo functions::encode($count.$rEmp['emp_id'].$dep_id)?>" class="btn btn-mini btn-warning thickbox" title="Modify Employee Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_edit.php?eid=<?php echo functions::encode($rEmp['emp_id']);?>','Employee Detail Update')"><i class="halflings-icon white pencil"></i></a>
			</div>
		</td>
	</tr>
	<?php
}
function empDetailPerProject($rEmp,$dep_id=0,&$count=0){
	global $countStatNotif;
	global $db;
	$empCount=1;
	$endofcontractCount=0;
	$emp_status = $db->getValue('emp_work_status','ews_stat',array('emp_id'=>$rEmp['emp_id']),'ORDER BY ews_date DESC LIMIT 1');
	if($emp_status=='End of Contract'){
		$endofcontractCount = $db->getValue('emp_work_status','count(*)',array('emp_id'=>$rEmp['emp_id'],'ews_stat'=>$emp_status));
	}
	$date_hired = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$rEmp['emp_id']),'ORDER BY ews_date LIMIT 1');
	$datetime1 = new DateTime($date_hired);
	$datetime2 = new DateTime(date('Y-m-d'));
	$interval = $datetime1->diff($datetime2);
	$intrval_year = $interval->format('%y');
	$intrval_month = $interval->format('%m');
	$intrval_day = $interval->format('%d');
	$diffInMonths = ($intrval_year * 12) + $intrval_month;
	$display_yr=''; $display_mn=''; $display_day='';
	if($intrval_year){
		$display_yr = ($intrval_year > 1) ? $intrval_year.' Years, ' : $intrval_year.' Year, ';
	}
	if($intrval_month){
		$display_mn = ($intrval_month > 1) ? $intrval_month.' Months, ' : $intrval_month.' Month, ';
	}
	if($intrval_day){
		$display_day = ($intrval_day > 1) ? $intrval_day.' Days ' : $intrval_day.' Day ';
	}
	$bgColorStat='';
	$intval = ($date_hired=='0000-00-00' || $date_hired=='') ? '----' : $display_yr.$display_mn.$display_day;
    ?>
	<tr>
		<td><div align="center"><?php echo $count++; ?></div></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['emp_no'] ?></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['lname'].' '.$rEmp['extname'].', '.$rEmp['fname'].' '.$rEmp['mname'] ?></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['gender'] ?></div></td>
		<td><div align="center"><?php echo $rEmp['civil_status']; ?></div></td>
		<td>
			<div align="left" style="padding-left:3px">
				<?php
				$arr = ($dep_id) ? array('dep_id'=>$dep_id,'emp_id'=>$rEmp['emp_id']) : array('emp_id'=>$rEmp['emp_id']);
				$q = $db->select('emp_position ep, dep_position dp','*',$arr,'AND dp.dp_id=ep.dp_id');
				$cntPos = $db->num_rows($q);
				$countPos=1;
				$position='';
				while($r = $db->fetch_array($q)):
					$position .= $r['pos_name'];
					if($countPos < $cntPos)
						$position.=' / ';
					$countPos++;
				endwhile;
				echo $position;
				?>
			</div>
		</td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['curr_address'] ?></div></td>
		<td><div align="center"><?php echo $rEmp['cell_no']; ?></div></td>
		<td style="<?php echo $bgColorStat;?>"><div align="left" style="padding-left:3px;"><?php echo $emp_status; echo ($endofcontractCount > 1) ? ' ('.$endofcontractCount.')' : ''; ?></div></td>
		<td width="3%">
			<div align="center" style="padding-top:3px;padding-bottom:3px;">
				<a id="vw<?php echo functions::encode($count.$rEmp['emp_id'].$dep_id)?>" class="btn btn-mini btn-info thickbox" title="Employee Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_detail.php?eid=<?php echo functions::encode($rEmp['emp_id']);?>','Employee Detail','1')"><i class="halflings-icon white zoom-in"></i></a>
				<a id="edit<?php echo functions::encode($count.$rEmp['emp_id'].$dep_id)?>" class="btn btn-mini btn-warning thickbox" title="Modify Employee Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_edit.php?eid=<?php echo functions::encode($rEmp['emp_id']);?>','Employee Detail Update')"><i class="halflings-icon white pencil"></i></a>
			</div>
		</td>
	</tr>
	<?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Summary</title>
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
	<style type="text/css">
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span>EMPLOYEE SUMMARY</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="employee_summary2.php">Summary 2</a></li>
				<li class="active"><a href="employee_summary.php" style="opacity:.9">Summary</a></li>
				<li><a href="employee_list.php">List</a></li>
			</ul>
			<form method="post">
				<div style="padding:10px;">
					<table border="0" width="100%">
						<tr>
							<td valign="middle" width="30%">&nbsp;</td>
							<td valign="middle">
								<div>
									<select name="selDisp" id="selDisp" data-rel="chosen" onChange="window.location='?sd='+this.value">
										<option value="">--select--</option>
										<option value="birthday" <?php if($req_display=='birthday'){echo 'selected="selected"';} ?>>Birthday</option>
										<option value="bloodtype" <?php if($req_display=='bloodtype'){echo 'selected="selected"';} ?>>Blood Type</option>
										<option value="civilstatus" <?php if($req_display=='civilstatus'){echo 'selected="selected"';} ?>>Civil Status</option>
										<option value="department" <?php if($req_display=='department'){echo 'selected="selected"';} ?>>Department</option>
										<option value="gender" <?php if($req_display=='gender'){echo 'selected="selected"';} ?>>Gender</option>
										<option value="birthplace" <?php if($req_display=='birthplace'){echo 'selected="selected"';} ?>>Place of Birth</option>
										<option value="address" <?php if($req_display=='address'){echo 'selected="selected"';} ?>>Address</option>
										<option value="position" <?php if($req_display=='position'){echo 'selected="selected"';} ?>>Position</option>
										<option value="religion" <?php if($req_display=='religion'){echo 'selected="selected"';} ?>>Religion</option>
										<option value="wstatus" <?php if($req_display=='wstatus'){echo 'selected="selected"';} ?>>Work Status</option>
										<option value="projass" <?php if($req_display=='projass'){echo 'selected="selected"';} ?>>Project Assignment</option>
									</select>
									<?php if($req_display=='gender'){ ?>
									<select name="selGender" id="selGender" style="text-align:left;" data-rel="chosens">
										<option value="--All--">--All--</option>
										<?php
										$qWS = $db->query('SELECT DISTINCT gender FROM employee ORDER BY gender');
										while($rWS = $db->fetch_array($qWS)):
										?>
										<option value="<?php echo $rWS['gender'] ?>" <?php if($gender==$rWS['gender']){echo 'selected="selected"';} ?>><?php echo $rWS['gender'] ?></option>
										<?php endwhile; ?>
									</select>
									<?php } ?>
									<?php if($req_display=='wstatus'){ ?>
									<select name="selWstat" id="selWstat" style="text-align:left;" data-rel="chosens">
										<option value="--All--">--All--</option>
										<?php
										$qWS = $db->query('SELECT DISTINCT work_status FROM employee ORDER BY work_status');
										while($rWS = $db->fetch_array($qWS)):
										?>
										<option value="<?php echo $rWS['work_status'] ?>" <?php if($workStat==$rWS['work_status']){echo 'selected="selected"';} ?>><?php echo $rWS['work_status'] ?></option>
										<?php endwhile; ?>
									</select>
									<?php } ?>

									<?php if($req_display=='religion'){ ?>
									<select name="selReligion" id="selReligion" style="text-align:left;" data-rel="chosens">
										<option value="--All--">--All--</option>
										<?php
										$qWS = $db->query('SELECT DISTINCT religion FROM employee ORDER BY religion');
										while($rWS = $db->fetch_array($qWS)):
										?>
										<option value="<?php echo $rWS['religion'] ?>" <?php if($religion==$rWS['religion']){echo 'selected="selected"';} ?>><?php echo $rWS['religion'] ?></option>
										<?php endwhile; ?>
									</select>
									<?php } ?>

									<?php if($req_display=='bloodtype'){ ?>
									<select name="selBloodType" id="selBloodType" style="text-align:left;" data-rel="chosens">
										<option value="--All--">--All--</option>
										<?php
										$qWS = $db->query('SELECT DISTINCT bloodtype FROM employee ORDER BY bloodtype');
										while($rWS = $db->fetch_array($qWS)):
										?>
										<option value="<?php echo $rWS['bloodtype'] ?>" <?php if($bloodtype==$rWS['bloodtype']){echo 'selected="selected"';} ?>><?php echo $rWS['bloodtype'] ?></option>
										<?php endwhile; ?>
									</select>
									<?php } ?>

									<?php if($req_display=='civilstatus'){ ?>
									<select name="selCivilStat" id="selCivilStat" style="text-align:left;" data-rel="chosens">
										<option value="--All--">--All--</option>
										<?php
										$qWS = $db->query('SELECT DISTINCT civil_status FROM employee ORDER BY civil_status');
										while($rWS = $db->fetch_array($qWS)):
										?>
										<option value="<?php echo $rWS['civil_status'] ?>" <?php if($civil_status==$rWS['civil_status']){echo 'selected="selected"';} ?>><?php echo $rWS['civil_status'] ?></option>
										<?php endwhile; ?>
									</select>
									<?php } ?>

									<?php if($req_display=='department'){ ?>
									<select name="selDepartment" id="selDepartment" style="width:550px;text-align:left;" data-rel="chosen">
										<option value="">--All Department--</option>
										<?php
										$qDH = $db->select('department','*',array(),'WHERE dep_head is NULL ORDER BY dep_name');
										while($r = $db->fetch_array($qDH)):
										?>
										<option value="<?php echo $r['dep_id']?>" <?php if($selDep==$r['dep_id'])echo 'selected="selected"';?> style="font-weight:bold;"><?php echo $r['dep_name']?></option>
										<?php
										$qDHS = $db->select('department','*',array('dep_head'=>$r['dep_id']),'ORDER BY dep_name');
										while($rS = $db->fetch_array($qDHS)):
										?>
										<option value="<?php echo $rS['dep_id']?>" <?php if($selDep==$rS['dep_id'])echo 'selected="selected"';?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $rS['dep_name']?></option>
										<?php endwhile;?>
										<?php endwhile;?>
									</select>
									<?php 
										}//if($req_display=='department')
									if($req_display=='position'){
									?>
										<select name="selPosition" id="selPosition" data-rel="chosen" style="width:550px;">
											<option value="">--All Positions--</option>
											<?php 
											$qPos = $db->select('dep_position','*',array(),'ORDER BY pos_name');
											while($rPos = $db->fetch_array($qPos)):
											$dept = $db->getValue('department','dep_name',array('dep_id'=>$rPos['dep_id']));
											$depName = ($dept) ? '('.$dept.')' : '(no assigned department)';
											?>
											<option value="<?php echo $rPos['dp_id']?>" <?php if($selPos==$rPos['dp_id'])echo 'selected="selected"';?>><?php echo $rPos['pos_name']; echo '&nbsp;&nbsp;'.$depName;?></option>
											<?php endwhile;?>
										</select>
									<?php 
										}//if($req_display=='position')
									if($req_display=='birthday'){
									?>
										<select name="bdMon" id="bdMon" data-rel="chosen" style="width:125px;">
											<option value="">Month</option>
											<option value="01" <?php if($bdMon=='01')echo 'selected="selected"';?>>Jan</option>
											<option value="02" <?php if($bdMon=='02')echo 'selected="selected"';?>>Feb</option>
											<option value="03" <?php if($bdMon=='03')echo 'selected="selected"';?>>Mar</option>
											<option value="04" <?php if($bdMon=='04')echo 'selected="selected"';?>>Apr</option>
											<option value="05" <?php if($bdMon=='05')echo 'selected="selected"';?>>May</option>
											<option value="06" <?php if($bdMon=='06')echo 'selected="selected"';?>>Jun</option>
											<option value="07" <?php if($bdMon=='07')echo 'selected="selected"';?>>Jul</option>
											<option value="08" <?php if($bdMon=='08')echo 'selected="selected"';?>>Aug</option>
											<option value="09" <?php if($bdMon=='09')echo 'selected="selected"';?>>Sep</option>
											<option value="10" <?php if($bdMon=='10')echo 'selected="selected"';?>>Oct</option>
											<option value="11" <?php if($bdMon=='11')echo 'selected="selected"';?>>Nov</option>
											<option value="12" <?php if($bdMon=='12')echo 'selected="selected"';?>>Dec</option>
										</select>
										<select name="bdDay" id="bdDay" data-rel="chosen" style="width:70px;">
											<option value="">Day</option>
											<?php for($i=1;$i<=31;$i++):?>
											<option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$bdDay)echo 'selected="selected"';?>><?php echo $i;?></option>
											<?php endfor;?>
										</select>
									<?php
										}//if($req_display=='birthday')
									if($req_display=='birthplace'){
									?>
										<select name="selBplace" id="selBplace" data-rel="chosen" style="width:550px;">
											<option value="">--All Birth Place--</option>
											<?php 
											$qBplc = $db->query('SELECT DISTINCT bplace FROM employee ORDER BY bplace');
											while($rBplc = $db->fetch_array($qBplc)):
												if($rBplc['bplace']){
											?>
											<option value="<?php echo functions::encode($rBplc['bplace'])?>" <?php if(functions::encode($selBplace)==functions::encode($rBplc['bplace']))echo 'selected="selected"';?>><?php echo $rBplc['bplace'];?></option>
											<?php }endwhile;?>
										</select>
									<?php 
										}//if($req_display=='address')
									if($req_display=='address'){
									?>
										<select name="selAddressx" id="selAddressx" style="width:550px; display:none;">
											<option value="">--All Address--</option>
											<?php 
											$qAdr = $db->query('SELECT DISTINCT curr_address FROM employee ORDER BY curr_address');
											while($rAdr = $db->fetch_array($qAdr)):
											?>
											<option value="<?php echo functions::encode($rAdr['curr_address'])?>" <?php if($selAddress==$rAdr['curr_address'])echo 'selected="selected"';?>><?php echo $rAdr['curr_address'];?></option>
											<?php endwhile;?>
										</select>
										<input type="text" placeholder="Keyword" name="selAddress" id="selAddress" style="width:550px;" value="<?php echo $selAddress ?>">
									<?php 
										}//if($req_display=='address')
										if($req_display=='projass'){
									?>
										<select name="selProj" id="selProj" data-rel="chosen" style="width:550px;">
											<option value="">--All Projects--</option>
											<?php 
											$qProj = $db->select('project','*',array('project'=>1),'ORDER BY proj_name');
											while($rProj = $db->fetch_array($qProj)):
											?>
											<option value="<?php echo $rProj['proj_id']?>" <?php if($selProj==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo $rProj['proj_name'];?></option>
											<?php endwhile;?>
										</select>
									<?php } ?>
										<input type="submit" name="btnShow" id="btnShow" class="btn btn-primary btn-small" value="Submit">
								</div>
							</td>
						</tr>
					</table>
				<div><br><br>
			<?php if($req_display=='department'){ ?>
					<div class="table-wrapper">
						<table class="table-hover" border="1" style="font-size: 12px;">
							<thead>
								<tr bgcolor="#CCC">
									<th width="2%" scope="col" height="35px">No</th>
									<th width="4%" scope="col">ID</th>
									<th width="7%" scope="col">NAME</th>
									<th width="3%" scope="col">GENDER</th>
									<th width="3%" scope="col">BIRTHDAY</th>
									<th width="8%" scope="col">BIRTH PLACE</th>
									<th width="8%" scope="col">POSITION</th>
									<th width="5%" scope="col">RELIGION</th>
									<th width="4%" scope="col">BLOOD TYPE</th>
									<th width="4%" scope="col">CIVIL STATUS</th>
									<th width="2%" scope="col">CONTACT #</th>
									<th width="4%" scope="col">EMP. STATUS</th>
									<th width="4%" scope="col"><div align="center">DETAILS</div></th>
								</tr>
							</thead>
							<tbody>
							<?php
							$countEmpEntireDep=0;
							$empDepPosCount=0;
							$empSecPosCount=0;
							$arrEmp=array();
							if($selDep)
								$qDep = $db->select('department','*',array('dep_id'=>$selDep),'ORDER BY dep_name');
							else
								$qDep = $db->select('department','*',array(),'WHERE dep_head is NULL ORDER BY dep_name');
							while($rDep = $db->fetch_array($qDep)):
								echo '<tr><td colspan="13">&nbsp;</td></tr>';
								echo '<tr><td colspan="13" bgcolor="#CCFF00" height="30"><strong>'.$rDep['dep_name'].'</strong></td></tr>';
								$empDepPosCount++;

								$qEmp = $db->select('employee emp, emp_position ep, dep_position dp','emp.*,dp.dep_id',array('dp.dep_id'=>$rDep['dep_id']),'AND emp.emp_id=ep.emp_id AND ep.dp_id=dp.dp_id GROUP BY emp.emp_id,dp.dep_id ORDER BY work_status,lname, fname');
								while($rEmp = $db->fetch_array($qEmp)):
									$arrEmp[$rEmp['emp_id']]=$rEmp['emp_id'];
									empDetail($rEmp,$rDep['dep_id'],$empDepPosCount);
								endwhile;
								$countEmpEntireDep += $empDepPosCount-1;

								$empDepPosCount=0;

								$qSec = $db->select('department','*',array('dep_head'=>$rDep['dep_id']),'ORDER BY dep_name');
								while($rSec = $db->fetch_array($qSec)):
									$empSecPosCount++;
									echo '<tr><td colspan="13">&nbsp;</td></tr>';
									echo '<tr><td colspan="13" style="padding-left:35px;" bgcolor="#009900" height="30"><strong><i>'.$rSec['dep_name'].'</i></strong></td></tr>';

									$qEmp = $db->select('employee emp, emp_position ep, dep_position dp','emp.*,dp.dep_id',array('dp.dep_id'=>$rSec['dep_id']),'AND emp.emp_id=ep.emp_id AND ep.dp_id=dp.dp_id GROUP BY emp.emp_id,dp.dep_id ORDER BY  work_status,lname, fname');
									while($rEmp = $db->fetch_array($qEmp)):
										$arrEmp[$rEmp['emp_id']]=$rEmp['emp_id'];
										empDetail($rEmp,$rSec['dep_id'],$empSecPosCount);
									endwhile;
									$countEmpEntireDep += $empSecPosCount-1;
									$empSecPosCount=0;

								endwhile;//while($rSec = $db->fetch_array($qSec)):
								echo '<tr><td colspan="13" style="padding-left:10px;"><div align="center"><strong><i>'.$rDep['dep_name'].' : ('.$countEmpEntireDep.')'.'</i></strong></div></td></tr>';
								$countEmpEntireDep=0;
								echo '<tr><td colspan="13">&nbsp;</td></tr>';
							endwhile;//while($Dep = $db->fetch_array($qDep)):
							if(empty($selDep)){//Display no department
							echo '<tr><td colspan="13" style="padding-left:35px;" bgcolor="#FF0000"><div style="color:#FFF"><strong>No Department</strong></div></td></tr>';
							$countNoPos=1;
							$qNoPos = $db->query('SELECT * FROM employee WHERE emp_id NOT IN (SELECT DISTINCT emp_id FROM emp_position) ORDER BY lname,fname');
							while($rNoPos = $db->fetch_array($qNoPos)):
								$arrEmp[$rNoPos['emp_id']]=$rNoPos['emp_id'];
								empDetail($rNoPos,0,$countNoPos);
							endwhile;
							}
							?>
							</tbody>
						</table>
						<div align="right" style="padding-top:20px;">Total: <strong><?php echo count($arrEmp) ?></strong></div>
					</div>
			<?php 
				}//if($req_display=='department')
				if($req_display=='position' || $req_display=='wstatus' || $req_display=='religion' || $req_display=='bloodtype' || $req_display=='civilstatus' || $req_display=='gender'){
			?>
					<div class="table-wrapper">
						<table class="table-hover" border="1" style="font-size: 12px;">
							<thead>
								<tr bgcolor="#CCC">
									<th width="2%" scope="col" height="35px">No</th>
									<th width="4%" scope="col">ID</th>
									<th width="7%" scope="col">NAME</th>
									<th width="3%" scope="col">GENDER</th>
									<th width="3%" scope="col">BIRTHDAY</th>
									<th width="8%" scope="col">BIRTH PLACE</th>
									<th width="5%" scope="col">RELIGION</th>
									<th width="4%" scope="col">BLOOD TYPE</th>
									<th width="4%" scope="col">CIVIL STATUS</th>
									<th width="2%" scope="col">CONTACT #</th>
									<th width="4%" scope="col">WORK STATUS</th>
									<th width="4%" scope="col"><div align="center">DETAILS</div></th>
								</tr>
							</thead>
							<tbody>
							<?php
							$countEmpEntireDep=0;
							$empDepPosCount=1;
							$empSecPosCount=0;
							$arrEmp=array();
							if($selPos)
								$qPos = $db->select('dep_position','*',array('dp_id'=>$selPos),'ORDER BY pos_name');
							else
								$qPos = $db->select('dep_position','*',array(),'ORDER BY pos_name');
							while($rPos = $db->fetch_array($qPos)):

								$qEmp = $db->select('employee emp, emp_position ep, dep_position dp','emp.*,dp.dep_id',array_merge($arrCol,array('dp.dp_id'=>$rPos['dp_id'])),'AND emp.emp_id=ep.emp_id AND ep.dp_id=dp.dp_id GROUP BY emp.emp_id,dp.dep_id ORDER BY work_status,lname, fname');
								#echo $db->last_query;
								#die();
								if( $db->num_rows($qEmp) ){

								echo '<tr><td colspan="13">&nbsp;</td></tr>';
								echo '<tr><td colspan="13" bgcolor="#CCC" height="30"><strong>'.$db->getValue('department','dep_name',array('dep_id'=>$rPos['dep_id'])).' <i>('.$rPos['pos_name'].')</i></strong></td></tr>';

								while($rEmp = $db->fetch_array($qEmp)):
									$arrEmp[$rEmp['emp_id']]=$rEmp['emp_id'];
									empDetail($rEmp,$rEmp['dep_id'],$empDepPosCount,$hide='position');
								endwhile;
								}
								$countEmpEntireDep=0;
							endwhile;//while($Dep = $db->fetch_array($qDep)):
							?>
							</tbody>
						</table>
						<div align="right" style="padding-top:20px;">Total: <strong><?php echo count($arrEmp) ?></strong></div>
					</div>
			<?php
				}//if($req_display=='position')
				if( ($req_display=='birthday' && $bdMon) ){
			?>
					<div class="table-wrapper">
						<table class="table-hover" border="1" style="font-size: 12px;">
							<thead>
								<tr bgcolor="#CCC">
									<th width="2%" scope="col" height="35px">No</th>
									<th width="4%" scope="col">ID</th>
									<th width="7%" scope="col">NAME</th>
									<th width="3%" scope="col">GENDER</th>
									<th width="3%" scope="col">BIRTHDAY</th>
									<th width="8%" scope="col">BIRTH PLACE</th>
									<th width="8%" scope="col">POSITION</th>
									<th width="5%" scope="col">RELIGION</th>
									<th width="4%" scope="col">BLOOD TYPE</th>
									<th width="4%" scope="col">CIVIL STATUS</th>
									<th width="2%" scope="col">CONTACT #</th>
									<th width="4%" scope="col">WORK STATUS</th>
									<th width="4%" scope="col"><div align="center">DETAILS</div></th>
								</tr>
							</thead>
							<tbody>
							<?php
							$countEmpEntireDep=0;
							$empDepPosCount=1;
							$empSecPosCount=0;
							$arrEmp=array();
								echo '<tr><td colspan="13">&nbsp;</td></tr>';
								#$empDepPosCount++;
								$arDt=array('bdate'=>'');
								if($bdMon && $bdDay)
									$arDt = array('right(bdate,5)'=>$bdMon.'-'.$bdDay);
								else if($bdMon)
									$arDt = array('substr(bdate,6,2)'=>$bdMon);
								$qEmp = $db->select('employee emp, emp_position ep, dep_position dp','emp.*,dp.dep_id',$arDt,'AND emp.emp_id=ep.emp_id AND ep.dp_id=dp.dp_id GROUP BY emp.emp_id,dp.dep_id ORDER BY right(bdate,5),work_status,lname, fname');
								#echo $db->last_query;
								while($rEmp = $db->fetch_array($qEmp)):
									$arrEmp[$rEmp['emp_id']]=$rEmp['emp_id'];
									empDetail($rEmp,$rEmp['dep_id'],$empDepPosCount);
								endwhile;
							?>
							</tbody>
						</table>
						<div align="right" style="padding-top:20px;">Total: <strong><?php echo count($arrEmp) ?></strong></div>
					</div>
			<?php 
				}//if( ($req_display=='birthday' && $bdMon) ){
				if( $req_display=='birthplace' ){//if( $req_display=='birthplace' ){
			?>
					<div class="table-wrapper">
						<table class="table-hover" border="1" style="font-size: 12px;">
							<thead>
								<tr bgcolor="#CCC">
									<th width="2%" scope="col" height="35px">No</th>
									<th width="4%" scope="col">ID</th>
									<th width="7%" scope="col">NAME</th>
									<th width="3%" scope="col">GENDER</th>
									<th width="3%" scope="col">BIRTHDAY</th>
									<th width="8%" scope="col">POSITION</th>
									<th width="5%" scope="col">RELIGION</th>
									<th width="4%" scope="col">BLOOD TYPE</th>
									<th width="4%" scope="col">CIVIL STATUS</th>
									<th width="2%" scope="col">CONTACT #</th>
									<th width="4%" scope="col">WORK STATUS</th>
									<th width="4%" scope="col"><div align="center">DETAILS</div></th>
								</tr>
							</thead>
							<tbody>
							<?php
							$countEmpEntireDep=0;
							$empDepPosCount=1;
							$empSecPosCount=0;
							$arrEmp=array();
							$arBP=array();
							if($selBplace){
								$arBP=array('sha1(bplace)'=>sha1($selBplace));
								$qBP = $db->select('employee','DISTINCT bplace',$arBP,'ORDER BY bplace');
							}
							else
								$qBP = $db->query('SELECT DISTINCT bplace FROM employee ORDER BY bplace');
							while($rBP = $db->fetch_array($qBP)):

								echo '<tr><td colspan="12">&nbsp;</td></tr>';
								echo '<tr><td colspan="12" style="padding-left:35px;" bgcolor="#CCC" height="30"><strong><i>'.$rBP['bplace'].'</i></strong></td></tr>';
								$arDt=array('bplace'=>'');
								if($rBP['bplace']){
									$arDt=array('sha1(bplace)'=>sha1($rBP['bplace']));
								}

								$qEmp = $db->select('employee emp, emp_position ep, dep_position dp','emp.*,dp.dep_id',$arDt,'AND emp.emp_id=ep.emp_id AND ep.dp_id=dp.dp_id GROUP BY emp.emp_id,dp.dep_id ORDER BY lname, fname');
								#echo $db->last_query;
								while($rEmp = $db->fetch_array($qEmp)):
									$arrEmp[$rEmp['emp_id']]=$rEmp['emp_id'];
									empDetail($rEmp,$rEmp['dep_id'],$empDepPosCount,$hide='bplace');
								endwhile;
							endwhile;
							?>
							</tbody>
						</table>
						<div align="right" style="padding-top:20px;">Total: <strong><?php echo count($arrEmp) ?></strong></div>
					</div>
			<?php }//if( $req_display=='birthplace' ){
				if( $req_display=='address' ){
			?>
					<div class="table-wrapper">
						<table class="table-hover" border="1" style="font-size: 12px;">
							<thead>
								<tr bgcolor="#CCC">
									<th width="2%" scope="col" height="35px">No</th>
									<th width="4%" scope="col">ID</th>
									<th width="7%" scope="col">NAME</th>
									<th width="3%" scope="col">GENDER</th>
									<th width="3%" scope="col">BIRTHDAY</th>
									<th width="8%" scope="col">POSITION</th>
									<th width="5%" scope="col">RELIGION</th>
									<th width="4%" scope="col">BLOOD TYPE</th>
									<th width="4%" scope="col">CIVIL STATUS</th>
									<th width="2%" scope="col">CONTACT #</th>
									<th width="4%" scope="col">WORK STATUS</th>
									<th width="4%" scope="col"><div align="center">DETAILS</div></th>
								</tr>
							</thead>
							<tbody>
							<?php
							$countEmpEntireDep=0;
							$empDepPosCount=1;
							$empSecPosCount=0;
							$arrEmp=array();
							$arBP=array();
							if($selAddress){
								$arBP=array('sha1(curr_address)'=>sha1($selAddress));
								#$qBP = $db->select('employee','DISTINCT curr_address',$arBP,'ORDER BY curr_address');
								$qBP = $db->prepareQ('SELECT DISTINCT curr_address FROM employee WHERE curr_address LIKE ? ORDER BY curr_address',array('%'.$selAddress.'%'));
							}
							else
								$qBP = $db->query('SELECT DISTINCT curr_address FROM employee ORDER BY curr_address');
							while($rBP = $db->fetch_array($qBP)):

								echo '<tr><td colspan="12">&nbsp;</td></tr>';
								echo '<tr><td colspan="12" style="padding-left:35px;" bgcolor="#CCC" height="30"><strong><i>'.$rBP['curr_address'].'</i></strong></td></tr>';
								$arDt=array('curr_address'=>'');
								if($rBP['curr_address']){
									$arDt=array('sha1(curr_address)'=>sha1($rBP['curr_address']));
								}

								$qEmp = $db->select('employee emp, emp_position ep, dep_position dp','emp.*,dp.dep_id',$arDt,'AND emp.emp_id=ep.emp_id AND ep.dp_id=dp.dp_id GROUP BY emp.emp_id,dp.dep_id ORDER BY lname, fname');
								#echo $db->last_query;
								while($rEmp = $db->fetch_array($qEmp)):
									$arrEmp[$rEmp['emp_id']]=$rEmp['emp_id'];
									empDetail($rEmp,$rEmp['dep_id'],$empDepPosCount,$hide='bplace');
								endwhile;
							endwhile;
							?>
							</tbody>
						</table>
						<div align="right" style="padding-top:20px;">Total: <strong><?php echo count($arrEmp) ?></strong></div>
					</div>
			<?php }//if( $req_display=='address' ){
				if( $req_display=='projass' ){
			?>
					<div class="table-wrapper">
						<table class="table-hover" border="1" style="font-size: 12px;">
							<thead>
								<tr bgcolor="#CCC">
									<th width="2%" scope="col" height="35px">No</th>
									<th width="4%" scope="col">ID</th>
									<th width="7%" scope="col">NAME</th>
									<th width="3%" scope="col">GENDER</th>
									<th width="3%" scope="col">CIVIL STATUS</th>
									<th width="8%" scope="col">POSITION</th>
									<th width="10%" scope="col">ADDRESS</th>
									<th width="2%" scope="col">CONTACT #</th>
									<th width="4%" scope="col">WORK STATUS</th>
									<th width="4%" scope="col"><div align="center">DETAILS</div></th>
								</tr>
							</thead>
							<tbody>
							<?php
							$countEmpEntireDep=0;
							$empDepPosCount=1;
							$empSecPosCount=0;
							$arrEmp=array();
							$arBP=array();
							if($selProj){
								$qProj = $db->select('project','*',array('proj_id'=>$selProj));
							}
							else
								$qProj = $db->query('SELECT * FROM project WHERE project=1 ORDER BY proj_name');
							while($rProj = $db->fetch_array($qProj)):

								echo '<tr><td colspan="12">&nbsp;</td></tr>';
								echo '<tr><td colspan="12" style="padding-left:35px;" bgcolor="#CCC" height="30"><strong><i>'.$rProj['proj_name'].'</i></strong></td></tr>';
								$qEmp = $db->select('employee emp, emp_position ep, dep_position dp, emp_site_assign esa, project p','emp.*,dp.dep_id',array('p.proj_id'=>$rProj['proj_id']),'AND emp.emp_id=ep.emp_id AND esa.emp_id=emp.emp_id AND esa.proj_id=p.proj_id AND ep.dp_id=dp.dp_id GROUP BY emp.emp_id,dp.dep_id ORDER BY lname, fname');
								#echo $db->last_query;
								while($rEmp = $db->fetch_array($qEmp)):
									$arrEmp[$rEmp['emp_id']]=$rEmp['emp_id'];
									empDetailPerProject($rEmp,$rEmp['dep_id'],$empDepPosCount);
								endwhile;
							endwhile;
							?>
							</tbody>
						</table>
						<div align="right" style="padding-top:20px;">Total: <strong><?php echo count($arrEmp) ?></strong></div>
					</div>
			<?php }//if( $req_display=='projass' ) ?>
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
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>