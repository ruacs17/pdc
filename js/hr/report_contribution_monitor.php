<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
$arrWorkStat=array();
$txbMon='';
$txbYear='';
if( isset($_POST['btnSearch']) ){
	$selWorkStat = (isset($_POST['selWorkStat']) && !empty($_POST['selWorkStat']) ) ? $_POST['selWorkStat'] : '';
	if(is_array($selWorkStat)){
		$countWS=count($selWorkStat);$countArr=0;
		$qOR='';
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
		$_SESSION['pr_qStat']=$qOR;
		$_SESSION['pr_workstat']=$arrWorkStat;
	}
	
	$_SESSION['pr_type'] = (isset($_POST['selType']) && !empty($_POST['selType']) ) ? $_POST['selType'] : '';
	$_SESSION['pr_mon'] = (isset($_POST['selMon']) && !empty($_POST['selMon']) ) ? $_POST['selMon'] : '';
	$_SESSION['pr_yr'] = (isset($_POST['selYear']) && !empty($_POST['selYear']) ) ? $_POST['selYear'] : '';
	functions::sendTo(functions::pageName());
	die();
}
$qStat = ( isset($_SESSION['pr_qStat']) && !empty($_SESSION['pr_qStat']) ) ? $_SESSION['pr_qStat'] : '';
$arrWorkStat = ( isset($_SESSION['pr_workstat']) && !empty($_SESSION['pr_workstat']) ) ? $_SESSION['pr_workstat'] : array();
$selType = ( isset($_SESSION['pr_type']) && !empty($_SESSION['pr_type']) ) ? $_SESSION['pr_type'] : '';
$selYear = ( isset($_SESSION['pr_yr']) && !empty($_SESSION['pr_yr']) ) ? $_SESSION['pr_yr'] : '';
$selMon = ( isset($_SESSION['pr_mon']) && !empty($_SESSION['pr_mon']) ) ? $_SESSION['pr_mon'] : '';
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
	<style type="text/css">
	.padleft{padding-left:3px;}
	.padright{padding-right:3px;}
	</style>
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>PREMIUM CONTRIBUTION</h2></div>
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
							</td>
							<td width="45%">
								<select name="selYear" id="selYear" style="width:120px;font-size:12px;">
									<option value="">--Select Year--</option>
									<?php
									$qYr = $db->query('SELECT DISTINCT LEFT(pp_year,4) as yr FROM payroll_premium ORDER BY pp_year DESC');
									while($rYr = $db->fetch_array($qYr)):
									?>
									<option value="<?php echo $rYr['yr']?>" <?php if($selYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
									<?php endwhile;?>
								</select>
								<select name="selMon" id="selMon" style="width:100px;font-size:12px;">
									<option value="">All Month</option>
									<option value="01" <?php if($selMon=='01')echo 'selected="selected"';?>>Jan</option>
									<option value="02" <?php if($selMon=='02')echo 'selected="selected"';?>>Feb</option>
									<option value="03" <?php if($selMon=='03')echo 'selected="selected"';?>>Mar</option>
									<option value="04" <?php if($selMon=='04')echo 'selected="selected"';?>>Apr</option>
									<option value="05" <?php if($selMon=='05')echo 'selected="selected"';?>>May</option>
									<option value="06" <?php if($selMon=='06')echo 'selected="selected"';?>>Jun</option>
									<option value="07" <?php if($selMon=='07')echo 'selected="selected"';?>>Jul</option>
									<option value="08" <?php if($selMon=='08')echo 'selected="selected"';?>>Aug</option>
									<option value="09" <?php if($selMon=='09')echo 'selected="selected"';?>>Sep</option>
									<option value="10" <?php if($selMon=='10')echo 'selected="selected"';?>>Oct</option>
									<option value="11" <?php if($selMon=='11')echo 'selected="selected"';?>>Nov</option>
									<option value="12" <?php if($selMon=='12')echo 'selected="selected"';?>>Dec</option>
								</select>
								<select name="selType" id="selType" style="width:120px;font-size:12px;">
									<option value="">--Select--</option>
									<option value="SSS" <?php if($selType=='SSS')echo 'selected="selected"';?>>SSS</option>
									<option value="HDMF" <?php if($selType=='HDMF')echo 'selected="selected"';?>>HDMF</option>
									<option value="PHIC" <?php if($selType=='PHIC')echo 'selected="selected"';?>>PHILHEALTH</option>
								</select>
							</td>
							<td><input type="submit" name="btnSearch" id="btnSearch" value="View" class="btn btn-primary btn-small"></td>
						</tr>
					</table>
				</div>
				<table class="table-hover table-striped" border="1" style="font-size: 12px;">
					<thead>
						<tr bgcolor="#CCC">
							<th width="5%" scope="col" height="25"><?php echo $selType ?> No.</th>
							<th width="7%" scope="col">LAST NAME</th>
							<th width="7%" scope="col">FIRST NAME</th>
							<th width="4%" scope="col">MIDDLE NAME</th>
							<th width="2%" scope="col">EXT.</th>
							<th width="5%" scope="col">BIRTHDAY</th>
							<th width="5%" scope="col">STATUS</th>
							<th width="5%" scope="col">EE SHARE</th>
							<th width="5%" scope="col">ER SHARE</th>
							<?php if($selType=='SSS'){ ?>
							<th width="5%" scope="col">EC</th>
							<?php } ?>
							<th width="5%" scope="col">TOTAL</th>
							<th width="4%" scope="col"><div align="center">DETAILS</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						if($selMon)
							$q = $db->select('employee emp, payroll_premium pp','emp.emp_id,sss,philhealth,pagibig,lname,fname,mname,extname,bdate,work_status,sum(pp.ee_share) as totalEE,sum(er_share) as totalER,sum(ec) as totalEC,sum(pp_total) as totalShare',array('pp.pp_year'=>$selYear,'pp.pp_month'=>$selMon,'pp.pp_type'=>$selType,'submit'=>1),$qStat.' AND pp.emp_id=emp.emp_id AND eat_id IN (SELECT DISTINCT eat_id FROM emp_attendance WHERE confirmed=1) GROUP BY pp.emp_id ORDER BY lname,fname');
						else
							$q = $db->select('employee emp, payroll_premium pp','emp.emp_id,sss,philhealth,pagibig,lname,fname,mname,extname,bdate,work_status,sum(pp.ee_share) as totalEE,sum(er_share) as totalER,sum(ec) as totalEC,sum(pp_total) as totalShare',array('pp.pp_year'=>$selYear,'pp.pp_type'=>$selType,'submit'=>1),$qStat.' AND pp.emp_id=emp.emp_id AND eat_id IN (SELECT DISTINCT eat_id FROM emp_attendance WHERE confirmed=1) GROUP BY pp.emp_id ORDER BY lname,fname');
						#echo $db->last_query;
						$count=0;
						$totalEE=0;$totalER=0;$totalShare=0;$totalEC=0;
						while($r = $db->fetch_array($q)):
							$emp_id=$r['emp_id'];
							$totalEE+=$r['totalEE'];
							$totalER+=$r['totalEE'];
							$totalShare+=$r['totalShare'];
							$totalEC+=$r['totalEC'];
						?>
						<tr>
							<td height="25"><div align="left" class="padleft"><?php if($selType=='SSS'){echo $r['sss'];}else if($selType=='HDMF'){echo $r['philhealth'];}else if($selType=='PHIC'){echo $r['pagibig'];}?></div></td>
							<td><div align="left" class="padleft"><?php echo $r['lname']; ?></div></td>
							<td><div align="left" class="padleft"><?php echo $r['fname']; ?></div></td>
							<td><div align="left" class="padleft"><?php echo $r['mname']; ?></div></td>
							<td><div align="center"><?php echo $r['extname']; ?></div></td>
							<td><div align="center"><?php echo functions::datearr($r['bdate']); ?></div></td>
							<td><div align="center"><?php echo $r['work_status']; ?></div></td>
							<td><div align="right" class="padright"><?php echo functions::formatMoney($r['totalEE']); ?></div></td>
							<td><div align="right" class="padright"><?php echo functions::formatMoney($r['totalER']); ?></div></td>
							<?php if($selType=='SSS'){ ?>
							<td><div align="right" class="padright"><?php echo functions::formatMoney($r['totalEC']); ?></div></td>
							<?php } ?>
							<td><div align="right" class="padright"><?php echo functions::formatMoney($r['totalShare']); ?></div></td>
							<td><div align="center" style="padding:3px;"><a id="vw<?php echo $count++?>" class="btn btn-info btn-mini thickbox" style="cursor:pointer;" title="Contribution Detail" data-rel="tooltip" onclick="showThis(this.id,'report_contribution_monitor_detail.php?empid=<?php echo functions::encode($emp_id);?>','Premium Detail','1')"><i class="halflings-icon white zoom-in"></i></a></div></td>
						</tr>
						<?php endwhile; ?>
						<tr>
							<td colspan="7"></td>
							<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalEE); ?></strong></div></td>
							<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalER); ?></strong></div></td>
							<?php if($selType=='SSS'){ ?>
							<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalEC); ?></strong></div></td>
							<?php } ?>
							<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalShare); ?></strong></div></td>
							<td></td>
						</tr>
					</tbody>
				</table>
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