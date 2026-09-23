<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
$arrWorkStat=array();
$qOR='';
$arrEmp=array();
$active = (isset($_REQUEST['active']) && !empty($_REQUEST['active']) ) ? $_REQUEST['active'] : '';
if($active){
	$selWorkStat = array('Regular','Probationary','Contractual','Part Time','OJT');
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
		$_SESSION['el_workstat']=$arrWorkStat;
		$_SESSION['el_qStat']=$qOR;
		$_SESSION['el_dep'] = (isset($_POST['selDepartment']) && !empty($_POST['selDepartment']) ) ? $_POST['selDepartment'] : '';
	}
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnSearch']) ){
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
	$_SESSION['el_workstat']=$arrWorkStat;
	$_SESSION['el_qStat']=$qOR;
	$_SESSION['el_dep'] = (isset($_POST['selDepartment']) && !empty($_POST['selDepartment']) ) ? $_POST['selDepartment'] : '';
	functions::sendTo(functions::pageName());
	die();
}
$qStat = ( isset($_SESSION['el_qStat']) && !empty($_SESSION['el_qStat']) ) ? $_SESSION['el_qStat'] : '';
$arrWorkStat = ( isset($_SESSION['el_workstat']) && !empty($_SESSION['el_workstat']) ) ? $_SESSION['el_workstat'] : array();
$selDep = ( isset($_SESSION['el_dep']) && !empty($_SESSION['el_dep']) ) ? $_SESSION['el_dep'] : '';
$countStatNotif=0;
function empDetail($rEmp,$dep_id=0,&$count=0){
	global $countStatNotif;
	global $db;
	
	$empCount=1;
	$emp_status = $db->getValue('emp_work_status','ews_stat',array('emp_id'=>$rEmp['emp_id']),'ORDER BY ews_date DESC LIMIT 1');
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
	$bgColorStat='';$title='';
	$intval = ($date_hired=='0000-00-00' || $date_hired=='') ? '----' : $display_yr.$display_mn.$display_day;
	if($emp_status=='Contractual' || $emp_status=='Probationary' ){
		
		if($diffInMonths >= 6){
			$countStatNotif++;
			$bgColorStat='background-color:red;color:#FFF;';
			$title='title="6 Months Due"';
		}
	}
    ?>
	<tr>
		<td><div align="center"><?php echo $count++; ?></div></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['emp_no'] ?></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['lname'] ?></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['fname'] ?></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['mname'] ?></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['extname'] ?></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['gender'] ?></div></td>
		<td>
			<div align="left" style="padding-left:3px">
				<?php
				$q = $db->select('emp_position ep, dep_position dp','*',array('dep_id'=>$dep_id,'emp_id'=>$rEmp['emp_id']),'AND dp.dp_id=ep.dp_id');
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
		<td><div align="left" style="padding-left:3px"><?php echo functions::datearr($date_hired) ?></div></td>
		<td><div align="left" style="padding-left:3px"><?php echo $intval; ?></div></td>
		<td style="<?php echo $bgColorStat;?>" <?php echo $title; ?>><div align="left" style="padding-left:3px;"><?php echo $emp_status; ?></div></td>
		<td>
			<div align="center" style="padding-top:3px;padding-bottom:3px;">
				<a id="vw<?php echo functions::encode($count.$rEmp['emp_id'].$dep_id)?>" class="btn btn-mini btn-info thickbox" title="Employee Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_detail.php?eid=<?php echo functions::encode($rEmp['emp_id']);?>','Employee Detail','1')"><i class="halflings-icon white zoom-in"></i></a>
				<a id="edit<?php echo functions::encode($count.$rEmp['emp_id'].$dep_id)?>" class="btn btn-mini btn-warning thickbox" title="Modify Employee Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_edit.php?eid=<?php echo functions::encode($rEmp['emp_id']);?>','Employee Detail Update')"><i class="halflings-icon white pencil"></i></a>
			</div>
		</td>
	</tr>
	<?php
}




function dispHead($depid,$level,&$emp_count=0){
	global $qStat;
	global $db;
	global $arrEmp;
	$string='';
	$q = $db->select('department','*',array('dep_id'=>$depid),'ORDER BY dep_name');

	while($r = $db->fetch_array($q)):
		$dep_name = ($r['dep_name']) ? $r['dep_name'] : "";
		$dep_head = ($r['dep_head']) ? $r['dep_head'] : "";
		$dep_desc = ($r['dep_desc']) ? $r['dep_desc'] : "";
		$s = '';
		$pad=0;
		for($i=1; $i<=$level; $i++):
			$pad+=40;
		endfor;
		#$arrEmp=array();
		$qEmp = $db->select('employee emp, emp_position ep, dep_position dp','emp.*,dp.dep_id',array('dp.dep_id'=>$r['dep_id']),'AND emp.emp_id=ep.emp_id AND ep.dp_id=dp.dp_id '.$qStat.' GROUP BY emp.emp_id,dp.dep_id ORDER BY work_status,lname, fname');
		while($rEmp = $db->fetch_array($qEmp)):
			$arrEmp[$rEmp['emp_id']]=$rEmp['emp_id'];
			empDetail($rEmp,$r['dep_id'],$emp_count);
		endwhile;
		$emp_count=1;
    endwhile;
}
function disp($parentItem,$level,&$emp_count=0){
	global $qStat;
	global $db;
	global $arrEmp;
	$string='';
	$q = $db->select('department','*',array('dep_head'=>$parentItem),'ORDER BY dep_name');

	while($r = $db->fetch_array($q)):
		$dep_name = ($r['dep_name']) ? $r['dep_name'] : "";
		$dep_head = ($r['dep_head']) ? $r['dep_head'] : "";
		$dep_desc = ($r['dep_desc']) ? $r['dep_desc'] : "";
		$s = '';
		$pad=0;
		for($i=1; $i<=$level; $i++):
			$pad+=40;
		endfor;
		$dep_name = ($dep_name) ? ' ('.$dep_name.')' : '';
		$person_per_dep = $db->getValue('employee emp, emp_position ep, dep_position dp','count(*)',array('dp.dep_id'=>$r['dep_id']),'AND emp.emp_id=ep.emp_id AND ep.dp_id=dp.dp_id '.$qStat.'');
		$string ='
		<tr id="rw'.$r['dep_id'].'">
			<td colspan="12" bgcolor="#6be86b">
				<div style="padding-left:'.$pad.'px;">
					<div style="display:flex;">
						<div style="border:0px solid;"><img src="../img/arrow-down-right.png" height="16" width="12">&nbsp;&nbsp;&nbsp;<strong>'.$dep_desc.'</strong></div>
						<div style="border:0px solid;padding-left:15px;"><strong>'.$dep_name.' '.$person_per_dep.'</strong></div>
					</div>
				</div>
			</td>
		</tr>
		';
		echo $string;
		#$arrEmp=array();
		$qEmp = $db->select('employee emp, emp_position ep, dep_position dp','emp.*,dp.dep_id',array('dp.dep_id'=>$r['dep_id']),'AND emp.emp_id=ep.emp_id AND ep.dp_id=dp.dp_id '.$qStat.' GROUP BY emp.emp_id,dp.dep_id ORDER BY work_status,lname, fname');
		while($rEmp = $db->fetch_array($qEmp)):
			$arrEmp[$rEmp['emp_id']]=$rEmp['emp_id'];
			empDetail($rEmp,$r['dep_id'],$emp_count);
		endwhile;
		$emp_count=1;

		disp($r['dep_id'],$level + 1,$emp_count);
		
    endwhile;
}

function dispSelect($dep_head,$level,$currentItemID){
	global $db;
	$string='';
	
		$q = $db->select('department','*',array('dep_head'=>$dep_head),'ORDER BY dep_name');
		while($r = $db->fetch_array($q)):
			$s = '';
			for($i=1; $i<=$level; $i++):
				$s .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			endfor;
			#if($r['dep_id'] != $itemID){
			$dep_desc = ($r['dep_desc']) ? '&nbsp;&nbsp;('.$r['dep_desc'].')': '';
			$s .= $r['dep_name'].$dep_desc;
			$selected = ($currentItemID==$r['dep_id']) ? 'selected="selected"' : "";
			$string ='
			<option value="'.$r['dep_id'].'" '.$selected.'>'.$s.'</option>
			';
			// $string ='
			// <option value="'.$r['dep_id'].'" '.$selected.'>'.$s.' '.$itemID.':'.$r['dep_id'].'</option>
			// ';
			echo $string;
			
			dispSelect($r['dep_id'],$level + 1,$currentItemID);
			#}
		endwhile;		
	
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee List</title>
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
			<h2><i class="halflings-icon white list"></i><span class="break"></span>EMPLOYEE LIST</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="employee_summary2.php">Summary 2</a></li>
				<li><a href="employee_summary.php">Summary</a></li>
				<li class="active"><a href="employee_list.php" style="opacity:.9">List</a></li>
			</ul>
			<form class="form-horizontal" method="post">
				<div style="padding:30px;">
					<table width="100%" border="0">
						<tr>
							<td width="20%">&nbsp;</td>
							<td width="15%">
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
							<td width="45%">
							<select name="selDepartment" id="selDepartment" style="width:650px;text-align:left;" data-rel="chosen">
								<option value="">--All Department--</option>
								<?php
								$q = $db->select('department','*',array('dep_head'=>NULL),'ORDER BY dep_name');
								while($r = $db->fetch_array($q)):
									$dep_desc = ($r['dep_desc']) ? '&nbsp;&nbsp;('.$r['dep_desc'].')': '';
								?>
								<option value="<?php echo $r['dep_id']?>" <?php if($selDep==$r['dep_id'])echo 'selected="selected"';?>><?php echo $r['dep_name'].$dep_desc;?></option>
								<?php
								#if($itemID!=$r['dep_id'])
								dispSelect($r['dep_id'],1,$selDep);
								endwhile;
								?>
							</select>
							</td>
							<td><input type="submit" name="btnSearch" id="btnSearch" value="View" class="btn btn-primary btn-small"></td>
						</tr>
					</table>
				</div>
				<div class="table-wrapper">
					<table class="table-hover" border="1" style="font-size: 12px;">
						<thead>
							<tr bgcolor="#CCC">
								<th width="2%" scope="col" height="35px">No</th>
								<th width="4%" scope="col">ID</th>
								<th width="7%" scope="col">LAST NAME</th>
								<th width="7%" scope="col">FIRST NAME</th>
								<th width="4%" scope="col">MIDDLE NAME</th>
								<th width="2%" scope="col">EXT.</th>
								<th width="3%" scope="col">GENDER</th>
								<th width="15%" scope="col">POSITION</th>
								<th width="5%" scope="col">DATE HIRED</th>
								<th width="9%" scope="col">LENGTH OF SERVICE</th>
								<th width="4%" scope="col">STATUS</th>
								<th width="4%" scope="col"><div align="center">DETAILS</div></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$countEmpEntireDep=0;
						$empDepPosCount=1;
						$empSecPosCount=0;
						#$arrEmp=array();
						if($selDep)
							$qDep = $db->select('department','*',array('dep_id'=>$selDep),'ORDER BY dep_name');
						else
							$qDep = $db->select('department','*',array(),'WHERE dep_head is NULL ORDER BY dep_name');
						while($rDep = $db->fetch_array($qDep)):
							echo '<tr><td colspan="12">&nbsp;</td></tr>';
							echo '<tr><td colspan="12" bgcolor="#009900"><strong>'.$rDep['dep_desc'].' ('.$rDep['dep_name'].')'.'</strong></td></tr>';
							dispHead($rDep['dep_id'],$level=1,$empDepPosCount);
							disp($parentItem=$rDep['dep_id'],$level=1,$empDepPosCount);#CCFF00
						endwhile;//while($Dep = $db->fetch_array($qDep)):
						?>
						</tbody>
					</table>
					<div align="right" style="padding-top:20px;">Total: <strong><?php echo count($arrEmp) ?></strong></div>
					<?php if($countStatNotif){ ?><div align="right" style="padding-top:20px;">6 Months Due Total : <strong><?php echo $countStatNotif; ?></strong></div><?php }?>
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