<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/read_excel_xlsx.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
if( isset($_POST['btnSubmit']) ){
	$emp_id = ( isset($_POST['txRequestedBy']) && !empty($_POST['txRequestedBy']) ) ? $_POST['txRequestedBy'] : '';
	$term = ( isset($_POST['selTerm']) && !empty($_POST['selTerm']) ) ? $_POST['selTerm'] : '';
	$_SESSION['leave_term']=$term;
	$arrterm = explode('-', $term);
	$_SESSION['leave_term_year_start'] = isset($arrterm[0]) ? $db->clean($arrterm[0]) : '';
	$_SESSION['leave_empID']=$emp_id;
	functions::sendTo(functions::pageName());
	die();
}
$term = (isset($_SESSION['leave_term'])) ? $_SESSION['leave_term'] : '';
$term_year_start = (isset($_SESSION['leave_term_year_start'])) ? $_SESSION['leave_term_year_start'] : '';
$emp_id = (isset($_SESSION['leave_empID'])) ? $_SESSION['leave_empID'] : '';

$arrLeaveType=array();
$qLT = $db->select('leave_config','*',array('with_pay'=>1));
while($rLT = $db->fetch_array($qLT)):
	$customize = ( $db->getValue('leave_config_add','count(*)',array('lc_id'=>$rLT['lc_id'])) ) ? 1: 0;
	$arrLeaveType[]=array('leave_id'=>$rLT['lc_id'],'leave_name'=>$rLT['leave_name'],'allowed'=>$rLT['allowed_days'],'date_from_mon'=>$rLT['date_from_mon'],'date_from_day'=>$rLT['date_from_day'],'date_to_mon'=>$rLT['date_to_mon'],'date_to_day'=>$rLT['date_to_day'],'with_pay'=>$rLT['with_pay'],'customize'=>$customize);
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Leave</title>
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
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
	<style>.tdSpace{padding: 12px 0px 4px 0px;}</style>
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Leave Details</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post">
					<table>
						<tr>
							<td>
								<div align="left">
									<select name="txRequestedBy" id="txRequestedBy" data-rel="chosen" style="width:400px; text-align:left;">
										<option value="">--All Employee--</option>
										<?php
										$qEU = $db->query('SELECT * FROM employee WHERE (work_status="Regular" OR work_status="Contractual" OR work_status="Probationary") ORDER BY lname,fname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($emp_id==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
							<td>
								<div align="left" style="padding-left:5px;">
									<select name="selTerm" id="selTerm" data-rel="chosen">
										<option value="">--Year--</option>
										<?php
										for($y=(date('Y'));$y>=2016;$y--):
										$ytrm = $y;
										?>
										<option value="<?php echo $ytrm ?>" <?php if($ytrm==$term)echo 'selected="selected"'; ?>><?php echo $y ?></option>
										<?php endfor; ?>
									</select>
								</div>
							</td>
							<td>
								<div align="left" style="padding-left:5px;padding-bottom:5px;"><input type="submit" class="btn btn-primary btn-small" name="btnSubmit"></div>
							</td>
						</tr>
					</table>
					<div style="padding:10px;"><?php if($term_year_start){ ?><h2>Only Regular Employee as of <?php echo $term_year_start ?> will appear.</h2><?php } ?></div>
					<table class="table table-bordered" style="font-size: 12px;">
						<tr style="background-color:#CCC">
							<th width="15%" scope="col">Employee</th>
							<th width="3%" scope="col"><div align="center">Regularization Date</div></th>
							<th width="3%" scope="col"><div align="center">Year(s) From Regularization</div></th>
							<?php foreach($arrLeaveType as $lid):?>
							<th width="8%" scope="col"><div align="center"><?php echo $lid['leave_name'];?><br>( Allowed / Consumed )</div></th>
							<?php endforeach;?>
							<th width="4%" scope="col"><div align="center">Remaining Leave Credit</div></th>
							<th width="4%" scope="col"><div align="center">Conversion Date</div></th>
							<th width="6%" scope="col"><div align="right" style="padding-right:25px;">Amount</div></th>
						</tr>
					</table>
					<div style="overflow-y:auto;height:480px;border:solid 1px #CCC;">
					<table class="table table-bordered table-hover table-striped" style="font-size: 12px;">
						<thead>
							<tr style="background-color:#CCC;display:none;">
								<th width="15%" scope="col">Employee</th>
								<th width="5%" scope="col"><div align="center">Regularization Date</div></th>
								<th width="5%" scope="col"><div align="center">Year(s) From Regularization</div></th>
								<?php foreach($arrLeaveType as $lid):?>
								<th width="8%" scope="col"><div align="center"><?php echo $lid['leave_name'];?><br>( Allowed / Consumed )</div></th>
								<?php endforeach;?>
								<th width="5%" scope="col"><div align="center">Remaining Leave Credit</div></th>
								<th width="5%" scope="col"><div align="center">Conversion Date</div></th>
								<th width="5%" scope="col"><div align="right">Amount</div></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$countEmp=0;
						$arrSearch = array('ews_stat'=>'Regular');
						if($emp_id)
							$arrSearch = array_merge($arrSearch,array('emp.emp_id'=>$emp_id));

						$qEmp = $db->select('employee emp, emp_work_status ews','*',$arrSearch,'AND emp.emp_id=ews.emp_id AND left(ews_date,4)<="'.$term_year_start.'" ORDER BY lname,fname');
						#echo $db->last_query;
						while($rEmp = $db->fetch_array($qEmp)):
							$countEmp++;
							$date_regular = $rEmp['ews_date'];
							$salary_daily = $db->getValue('emp_salary','es_daily',array('emp_id'=>$rEmp['emp_id']),'AND left(es_date,4)<="'.$term_year_start.'" ORDER BY es_date DESC');
							#$date_regular = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$rEmp['emp_id'],'ews_stat'=>'Regular'),'ORDER BY ews_date DESC LIMIT 1');
							$allow_with_pay=0;
							if($date_regular){
								$years_regular = functions::year_diff($date_regular,$dateto = ($term_year_start==date('Y')) ? date('Y-m-d') : date($term_year_start.'-12-31'));
								if($years_regular){
									$allow_with_pay=1;
								}
							}
						?>
							<tr>
								<td width="15%"><a id="vw<?php echo $countEmp ?>" style="cursor:pointer;" class="thickbox" title="Employee Detail" data-rel="tooltip" onclick="showThis(this.id,'leave_monitor_detail.php?emprq=<?php echo functions::encode($rEmp['emp_id'])?>','Leave Monitoring Detail','1')"> <?php echo $rEmp['lname'].', '.$rEmp['fname']?></a></td>
								<td width="5%"><div align="center"><?php echo functions::datearr($date_regular);?></div></td>
								<td width="5%"><div align="center"><?php echo $regular_year = (functions::valid_date($date_regular)) ? functions::year_diff($date_regular,$dateto = ($term_year_start==date('Y')) ? date('Y-m-d') : date($term_year_start.'-12-31')) : '---'; ?></div></td>
								<?php
								$leave_allowed_days=0;$consumedLeaves=0;$leave_remained=0;$yearService=0;
								foreach($arrLeaveType as $lid):

									if($date_regular){
										$yearService = functions::year_diff($date_regular,$dateto = ($term_year_start==date('Y')) ? date('Y-m-d') : date($term_year_start.'-12-31'));
										$leave_allowed_days = $db->getValue('leave_config_add','allowed_days',array('lc_id'=>$lid['leave_id']),'AND "'.$yearService.'" BETWEEN service_year_from AND service_year_to');
									}
									else
										$leave_allowed_days = $lid['allowed'];

									$consumedLeaves = $db->getValue('leave_file_detail lfd, leave_file lf','sum(leave_count)',array('lfd.emp_id'=>$rEmp['emp_id'],'lc_id'=>$lid['leave_id'],'term'=>$term),'AND lfd.lf_id=lf.lf_id');
									$leave_consumed = ($consumedLeaves) ? $consumedLeaves : 0;
									$display='';
									if($allow_with_pay==1 && $lid['with_pay']==1){
										$leave_allowed_days = ($leave_allowed_days) ? $leave_allowed_days : 0;
										#$display = $leave_allowed_days.' / '.$leave_consumed;
										$display = '<table width="100%"><tr><td width="50%"><div align="center">'.$leave_allowed_days.'</div></td><td width="50%"><div align="center">'.$leave_consumed.'</div></td></tr></table>';
										$leave_remained = $leave_allowed_days - $leave_consumed;
									}
									$term_end = $db->getValue('leave_file_detail','DISTINCT term_end',array('emp_id'=>$rEmp['emp_id'],'term'=>$term));
								?>
								<td width="8%"><div align="center"><?php echo($regular_year!="---") ?  $display : 'N/A';?></div></td>
								<?php endforeach;?>
								<td width="5%"><div align="center"><?php echo ($regular_year!="---") ? $leave_remained : 'N/A'; ?></div></td>
								<td width="5%"><div align="center"><?php echo ($regular_year!="---") ? functions::datearr(date($term_year_start.'-m-d',strtotime($date_regular))) : 'N/A'; ?></div></td>
								<td width="5%"><div align="right" <?php if($regular_year!="---"){ ?>data-rel="tooltip" title="<?php echo functions::formatMoney($salary_daily,'~').' x '.$leave_remained ?>" <?php } ?>><?php echo ($regular_year!="---") ? functions::formatMoney($salary_daily*$leave_remained) : 'N/A'; ?></div></td>
							</tr>
						<?php endwhile;?>
						</tbody>
					</table>
					</div>
					<div align="right"><?php echo $countEmp; ?></div>
				</form>
			</div>
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
<script>
function selEMp(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo functions::pageName()?>?empid="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
}
</script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>