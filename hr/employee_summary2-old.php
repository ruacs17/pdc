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
$selDep='';$selPos='';
$txbMon='';$txbDay='';
if( isset($_REQUEST['sd']) && !empty($_REQUEST['sd']) ){
	$_SESSION['es_dsply']=$_REQUEST['sd'];
}
$arrWorkStat=array();
$req_display = ( isset($_SESSION['es_dsply']) ) ? $_SESSION['es_dsply'] : '';
$qOR='';
if( isset($_POST['btnShow']) ){
	$_SESSION['em_id'] = (isset($_POST['txID'])) ? $_POST['txID'] : '';
	$_SESSION['em_lname'] = (isset($_POST['txlname'])) ? $_POST['txlname'] : '';
	$_SESSION['em_fname'] = (isset($_POST['txfname'])) ? $_POST['txfname'] : '';

	$_SESSION['em_gender'] = (isset($_POST['selGender'])) ? $_POST['selGender'] : '';
	$_SESSION['em_civil_status'] = (isset($_POST['selCivilStat'])) ? $_POST['selCivilStat'] : '';
	$_SESSION['em_pos'] = (isset($_POST['selPosition'])) ? $_POST['selPosition'] : '';
	$_SESSION['em_dep'] = (isset($_POST['selDepartment']) ) ? $_POST['selDepartment'] : '';
	$_SESSION['em_address'] = (isset($_POST['selAddress'])) ? $_POST['selAddress'] : '';
	$_SESSION['em_proj'] = (isset($_POST['selProj'])) ? $_POST['selProj'] : '';

	$selWorkStat = (isset($_POST['selWorkStat']) && !empty($_POST['selWorkStat']) ) ? $_POST['selWorkStat'] : '';
	if(is_array($selWorkStat)){
		$countWS=count($selWorkStat);$countArr=0;
		if($countWS){
			$qOR.=' AND (';
			foreach($selWorkStat as $workStat):
				$countArr++;
				$arrWorkStat[$workStat]=$workStat;
				$qOR .= 'work_status="'.$db->clean($workStat).'"';
				if($countWS > $countArr)
					$qOR .= ' OR ';
			endforeach;
			$qOR.=')';
		}
	}
	$_SESSION['em_workstat']=$arrWorkStat;
	$_SESSION['em_qStat']=$qOR;

	functions::sendTo(functions::pageName());
	die();
}
#$arrCol=array('p.project'=>1);
$arrCol=array();
$qOR = ( isset($_SESSION['em_qStat']) && !empty($_SESSION['em_qStat']) ) ? $_SESSION['em_qStat'] : '';
$idno = ( isset($_SESSION['em_id']) ) ? $_SESSION['em_id'] : '';
if($idno){
	$qOR.=' AND emp_no LIKE "%'.$db->clean($idno).'%" ';
}

$lname = ( isset($_SESSION['em_lname']) ) ? $_SESSION['em_lname'] : '';
if($lname){
	$qOR.=' AND lname LIKE "%'.$db->clean($lname).'%" ';
}

$fname = ( isset($_SESSION['em_fname']) ) ? $_SESSION['em_fname'] : '';
if($fname){
	$qOR.='AND fname LIKE "%'.$db->clean($fname).'%" ';
}

$civil_status = ( isset($_SESSION['em_civil_status']) ) ? $_SESSION['em_civil_status'] : '';
if($civil_status!='--All--'){
	$arrCol=array_merge($arrCol,array('civil_status'=>$civil_status));
}


$selAddress = ( isset($_SESSION['em_address']) ) ? $_SESSION['em_address'] : '';
if($selAddress){
	if($qOR){
		$qOR.='AND curr_address LIKE "%'.$db->clean($selAddress).'%" ';
	}
}

$selProj = ( isset($_SESSION['em_proj']) ) ? $_SESSION['em_proj'] : '';
if($selProj){
	$arrCol=array_merge($arrCol,array('p.proj_id'=>$selProj));
}
$selPos = ( isset($_SESSION['em_pos']) ) ? $_SESSION['em_pos'] : '';
if($selPos){
	$arrCol=array_merge($arrCol,array('dp.dp_id'=>$selPos));
}


$arrWorkStat = ( isset($_SESSION['em_workstat']) && !empty($_SESSION['em_workstat']) ) ? $_SESSION['em_workstat'] : array();
// print_r($arrCol);
// echo $qOR;
$where = (count($arrCol)==0) ? ' WHERE ' : '';
// $qq = $db->select('employee emp, emp_position ep, dep_position dp, emp_site_assign esa, project p','*',$arrCol,' AND emp.emp_id=ep.emp_id AND esa.emp_id=emp.emp_id AND esa.proj_id=p.proj_id AND ep.dp_id=dp.dp_id '.$qOR.' ORDER BY lname, fname');
// echo $db->last_query;
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
				<li class="active"><a href="employee_summary2.php" style="opacity:.9">Summary 2</a></li>
				<li><a href="employee_summary.php">Summary</a></li>
				<li><a href="employee_list.php">List</a></li>
			</ul>
			<form method="post">
				<div style="padding:10px;" align="center">
					<table border="0" width="80%">
						<tr>
							<td>
								<label class="checkbox"><input type="checkbox"  name="selWorkStat[]" id="selWorkStat1" value="Regular" <?php if(isset($arrWorkStat['Regular']))echo 'checked';?>> Regular</label>
								<label class="checkbox"><input type="checkbox" name="selWorkStat[]" id="selWorkStat2" value="Probationary" <?php if(isset($arrWorkStat['Probationary']))echo 'checked';?>> Probationary</label>
								<label class="checkbox"><input type="checkbox" name="selWorkStat[]" id="selWorkStat3" value="Contractual" <?php if(isset($arrWorkStat['Contractual']))echo 'checked';?>> Contractual</label>
								<label class="checkbox"><input type="checkbox" name="selWorkStat[]" id="selWorkStat3" value="Part Time" <?php if(isset($arrWorkStat['Part Time']))echo 'checked';?>> Part Time</label>
								<label class="checkbox"><input type="checkbox" name="selWorkStat[]" id="selWorkStat3" value="OJT" <?php if(isset($arrWorkStat['OJT']))echo 'checked';?>> OJT</label>
								<label class="checkbox"><input type="checkbox" name="selWorkStat[]" id="selWorkStat3" value="Retired" <?php if(isset($arrWorkStat['Retired']))echo 'checked';?>> Retired</label>
								<label class="checkbox"><input type="checkbox" name="selWorkStat[]" id="selWorkStat3" value="Resigned" <?php if(isset($arrWorkStat['Resigned']))echo 'checked';?>> Resigned</label>
								<label class="checkbox"><input type="checkbox" name="selWorkStat[]" id="selWorkStat3" value="Separated" <?php if(isset($arrWorkStat['Separated']))echo 'checked';?>> Separated</label>
								<label class="checkbox"><input type="checkbox" name="selWorkStat[]" id="selWorkStat3" value="AWOL" <?php if(isset($arrWorkStat['AWOL']))echo 'checked';?>> AWOL</label>
								<label class="checkbox"><input type="checkbox" name="selWorkStat[]" id="selWorkStat3" value="End of Contract" <?php if(isset($arrWorkStat['End of Contract']))echo 'checked';?>> End of Contract</label>
								<label class="checkbox"><input type="checkbox" name="selWorkStat[]" id="selWorkStat3" value="Blocklisted" <?php if(isset($arrWorkStat['Blocklisted']))echo 'checked';?>> Blocklisted</label>
							</td>
							<td width="10px;"></td>
							<td>
								<table border="0" width="100%">
									<tr>
										<td width="30%"></td>
										<td width="10px;"></td>
										<td></td>
									</tr>
									<tr style="display:none;">
										<td align="right" height="40">ID</td>
										<td></td>
										<td><input type="text" name="txID" id="txID" value="<?php echo $idno; ?>"></td>
									</tr>
									<tr style="display:none;">
										<td align="right" height="40">Last Name</td>
										<td></td>
										<td><input type="text" name="txlname" id="txlname" value="<?php echo $lname; ?>"></td>
									</tr>
									<tr style="display:none;">
										<td align="right" height="40">First Name</td>
										<td></td>
										<td><input type="text" name="txfname" id="txfname" value="<?php echo $fname; ?>"></td>
									</tr>
									<tr style="display:none;">
										<td align="right" height="40">Civil Status</td>
										<td></td>
										<td>
											<select name="selCivilStat" id="selCivilStat" style="text-align:left;" data-rel="chosens">
												<option value="--All--">--All--</option>
												<?php
												$qWS = $db->query('SELECT DISTINCT civil_status FROM employee ORDER BY civil_status');
												while($rWS = $db->fetch_array($qWS)):
												?>
												<option value="<?php echo $rWS['civil_status'] ?>" <?php if($civil_status==$rWS['civil_status']){echo 'selected="selected"';} ?>><?php echo $rWS['civil_status'] ?></option>
												<?php endwhile; ?>
											</select>
										</td>
									</tr>
									<tr>
										<td align="right" height="50">Position</td>
										<td></td>
										<td>
											<select name="selPosition" id="selPosition" data-rel="chosen" style="width:500px;">
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
										</td>
									</tr>
									<tr>
										<td align="right" height="50">Current Address</td>
										<td></td>
										<td>
											<input type="text" placeholder="Keyword" name="selAddress" id="selAddress" style="width:500px;" value="<?php echo $selAddress ?>">
										</td>
									</tr>
									<tr>
										<td align="right" height="40">Project Assignment</td>
										<td></td>
										<td valign="middle">
											<div>
												<select name="selProj" id="selProj" data-rel="chosen" style="width:500px;">
													<option value="">--All Projects--</option>
													<?php 
													$qProj = $db->select('project','*',array(),'ORDER BY proj_name');
													while($rProj = $db->fetch_array($qProj)):
													?>
													<option value="<?php echo $rProj['proj_id']?>" <?php if($selProj==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo $rProj['proj_name'];?></option>
													<?php endwhile;?>
												</select>
												
											</div>
										</td>
									</tr>
									<tr>
										<td></td>
										<td></td>
										<td style="padding-top:40px;"><input type="submit" name="btnShow" id="btnShow" class="btn btn-primary btn-small" value="Submit"></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>

				<div><br><br>
				<div class="table-wrapper">
					<table class="table-hover" border="1" style="font-size: 12px;">
						<thead>
							<tr bgcolor="#CCC">
								<th width="1%" scope="col" height="35px">No</th>
								<th width="2%" scope="col">ID</th>
								<th width="7%" scope="col">NAME</th>
								<th width="3%" scope="col">GENDER</th>
								<th width="3%" scope="col">CIVIL STATUS</th>
								<th width="7%" scope="col">POSITION</th>
								<th width="7%" scope="col">ADDRESS</th>
								<th width="2%" scope="col">CONTACT #</th>
								<th width="12%" scope="col">PROJECT / SITE</th>
								<th width="2%" scope="col">WORK STATUS</th>
								<th width="3%" scope="col"><div align="center">DETAILS</div></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$countEmpEntireDep=0;
						$empDepPosCount=1;
						$empSecPosCount=0;
						$arrEmp=array();
						$arBP=array();
						$count=1;
						$arrInfo=array();
						$arrPosition=array();
						$arrProjectAssign=array();
						$qEmp = $db->select('employee emp, emp_position ep, dep_position dp, emp_site_assign esa, project p','*',$arrCol,$where.' AND emp.emp_id=ep.emp_id AND esa.emp_id=emp.emp_id AND esa.proj_id=p.proj_id AND ep.dp_id=dp.dp_id '.$qOR.' ORDER BY lname, fname');
						#$qEmp = $db->query('SELECT * FROM employee emp, emp_position ep, dep_position dp, emp_site_assign esa, project p WHERE emp.emp_id=ep.emp_id AND esa.emp_id=emp.emp_id AND esa.proj_id=p.proj_id AND ep.dp_id=dp.dp_id AND p.project=1 ORDER BY lname, fname');
						#echo $db->last_query;
						while($rEmp = $db->fetch_array($qEmp)):
							$endofcontractCount=0;
							$dep_id = $rEmp['dep_id'];
							$emp_status = $db->getValue('emp_work_status','ews_stat',array('emp_id'=>$rEmp['emp_id']),'ORDER BY ews_date DESC LIMIT 1');
							if($emp_status=='End of Contract'){
								$endofcontractCount = $db->getValue('emp_work_status','count(*)',array('emp_id'=>$rEmp['emp_id'],'ews_stat'=>$emp_status));
							}
							if($endofcontractCount > 1)
								$emp_status.=' ('.$endofcontractCount.')';

							$current='';
							if($rEmp['date_started'] && $rEmp['date_ended']){
								if($rEmp['date_started']<=date('Y-m-d') && $rEmp['date_ended']>=date('Y-m-d'))
									$current='current';
							}
							else if($rEmp['date_started']){
								if( $rEmp['date_started']<=date('Y-m-d') )
									$current='current';
							}
							else if($rEmp['date_ended']){
								if( $rEmp['date_ended']>=date('Y-m-d') )
									$current='current';
							}

							$arr = ($rEmp['dep_id']) ? array('dep_id'=>$rEmp['dep_id'],'emp_id'=>$rEmp['emp_id']) : array('emp_id'=>$rEmp['emp_id']);
							$q = $db->select('emp_position ep, dep_position dp','*',$arr,'AND dp.dp_id=ep.dp_id');
							$cntPos = $db->num_rows($q);
							$countPos=1;
							$position='';
							while($r = $db->fetch_array($q)):
								$position .= $r['pos_name'];
								if($countPos < $cntPos)
									$position.=' / ';
								$countPos++;
								$arrPosition[$rEmp['emp_id']][$r['pos_name']]=$r['dep_id'];
							endwhile;

							$arrProjectAssign[$rEmp['emp_id']][$rEmp['proj_id']]=array('proj_name'=>$rEmp['proj_name'],'date_started'=>$rEmp['date_started'],'date_ended'=>$rEmp['date_ended'],'current'=>$current);
							$empname = $rEmp['lname'].' '.$rEmp['extname'].', '.$rEmp['fname'].' '.$rEmp['mname'];
							$arrInfo[$rEmp['emp_id']]=array('emp_id'=>$rEmp['emp_id'],'emp_no'=>$rEmp['emp_no'],'name'=>$empname,'gender'=>$rEmp['gender'],'civil_status'=>$rEmp['civil_status'],'status'=>$emp_status,'curr_address'=>$rEmp['curr_address'],'cell_no'=>$rEmp['cell_no']);
						endwhile;
						foreach($arrInfo as $rEmp):
						?>
							<tr>
								<td><div align="center"><?php echo $count++; ?></div></td>
								<td><div align="left" style="padding-left:3px"><?php echo $rEmp['emp_no'] ?></td>
								<td><div align="left" style="padding-left:3px"><?php echo $rEmp['name'];?></td>
								<td><div align="center"><?php echo $rEmp['gender'] ?></div></td>
								<td><div align="center"><?php echo $rEmp['civil_status']; ?></div></td>
								<td>
									<div align="left" style="padding-left:3px">
										<?php
										foreach($arrPosition[$rEmp['emp_id']] as $pos => $dep):
											echo '<div>'.$pos.'</div>';
										endforeach;
										?>
									</div>
								</td>
								<td><div align="left" style="padding-left:3px"><?php echo $rEmp['curr_address'] ?></div></td>
								<td><div align="center"><?php echo $rEmp['cell_no']; ?></div></td>
								<td>
									<div align="left" style="padding:1px">
										<?php
										foreach($arrProjectAssign[$rEmp['emp_id']] as $prj):
											$pad = (count($prj)>1) ? 'style="padding-top:5px;"' : '';
											$cur = ($prj['current']) ? ' <strong>(Current Assignment)</strong>' : '';
											#if($cur)
											echo '<div '.$pad.'>'.$prj['proj_name'].$cur.'</div>';
										endforeach;
										?>
									</div>
								</td>
								<td><div align="left" style="padding-left:3px;"><?php echo $rEmp['status']; ?></div></td>
								<td width="3%">
									<div align="center" style="padding-top:3px;padding-bottom:3px;">
										<a id="vw<?php echo functions::encode($count.$rEmp['emp_id'])?>" class="btn btn-mini btn-info thickbox" title="Employee Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_detail.php?eid=<?php echo functions::encode($rEmp['emp_id']);?>','Employee Detail','1')"><i class="halflings-icon white zoom-in"></i></a>
										<a id="edit<?php echo functions::encode($count.$rEmp['emp_id'])?>" class="btn btn-mini btn-warning thickbox" title="Modify Employee Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_edit.php?eid=<?php echo functions::encode($rEmp['emp_id']);?>','Employee Detail Update')"><i class="halflings-icon white pencil"></i></a>
									</div>
								</td>
							</tr>
						<?php
						endforeach;
						?>
						</tbody>
					</table>
					<div align="right" style="padding-top:20px;">Total: <strong><?php echo count($arrInfo) ?></strong></div>
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
<script src="../js/custom.js"></script>
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>