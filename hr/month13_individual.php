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

$emp_id = (isset($_REQUEST['empid']) && !empty($_REQUEST['empid']) ) ? functions::decode($_REQUEST['empid']) : '';
$month13year = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? functions::decode($_REQUEST['yr']) : '';

$q = $db->select('employee','*',array('emp_id'=>$emp_id));
$r = $db->fetch_array($q);
$cert_id = $r['cert_id'];

function position($emp_id){
	global $db;
	$countPos=0;$position='';
	$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
	while($rPos = $db->fetch_array($qPos)):
		if($countPos)
			$position .= ' /<br>';
		$position .= $rPos['pos_name'];
		$countPos++;
		endwhile;
		return $position;
}
$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
$file = (file_exists('../img_emp/'.$fileName) && $fileName) ? $fileName : 'blank-pic.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>13th Month Payroll Generate</title>
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
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>13th Month Payroll for the Year <?php echo $month13year; ?></h2></div>

				<table width="99%" border='0'>
					<tr>
						<td width="30%" valign="top">
							<table border='0' width="99%">
								<tr>
									<td colspan="2"><img width="300" height="300" src="../img_emp/<?php echo $file;?>"></td>
								</tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr>
									<td width="20%" height="30"><i>Name</i></td>
									<td><strong><?php echo $r['lname'].', '.$r['fname'];?></strong></td>
								</tr>
								<tr>
									<td height="30"><i>Position</i></td>
									<td><?php echo position($emp_id); ?></td>
								</tr>
								<tr>
									<td height="30"><i>Status</i></td>
									<td>
										<?php
										$qStat = $db->select('emp_work_status','*',array('emp_id'=>$emp_id,'ews_stat'=>$r['work_status']),'ORDER BY ews_date DESC');
										$rStat = $db->fetch_array($qStat);
										$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : '-----';
										$wrkDate = (isset($rStat['ews_date'])) ? ' ('.functions::datearr($rStat['ews_date']).')' : '';
										echo $wrkStat . $wrkDate;
										?>
									</td>
								</tr>
							</table>
						</td>
						<td valign="top">
							<div align="center">

								<table class="table table-bordered table-hover" style="font-size: 12px;">
									<thead>
										<tr style="background-color:#CCC">
											<th width="10%" scope="col"><div align="left">Payroll No</div></th>
											<th width="10%" scope="col"><div align="left">Inclusive Date</div></th>
											<th width="10%" scope="col"><div align="right">Gross</div></th>
											<th width="10%" scope="col"><div align="right">Absent</div></th>
											<th width="10%" scope="col"><div align="right">Net</div></th>
										</tr>
									</thead>
									<tbody>
									<?php
									$countEmp=0;
									$qDisp = $db->select('emp_attendance ea, emp_payroll_detail epd, employee emp','emp.emp_id, lname, fname, date_start,date_end,payroll_no, gross, absent, (gross-absent) as netpay',array('att_year'=>$month13year,'emp.emp_id'=>$emp_id),'AND ea.eat_id=epd.eat_id AND emp.emp_id=epd.emp_id GROUP BY date_start');
									#echo $db->last_query;
									$total_gross=0; $total_absent=0; $total_netpay=0;
									while($rDisp = $db->fetch_array($qDisp)):
										$countEmp++;
										$emp_id = $rDisp['emp_id'];
										$total_gross += $rDisp['gross'];
										$total_absent += $rDisp['absent'];
										$total_netpay += $rDisp['netpay'];
									?>
										<tr>
											<td><div align="left"><?php echo $rDisp['payroll_no'];?></div></td>
											<td><div align="left" title=""><?php echo functions::datearr($rDisp['date_start']).' - '.functions::datearr($rDisp['date_end']);?></div></td>
											<td><div align="right" title="<?php echo functions::formatMoney($rDisp['gross'],'~');?>"><?php echo functions::formatMoney($rDisp['gross']);?></div></td>
											<td><div align="right" title="<?php echo functions::formatMoney($rDisp['absent'],'~');?>"><?php echo functions::formatMoney($rDisp['absent']);?></div></td>
											<td><div align="right" title="<?php echo functions::formatMoney($rDisp['netpay'],'~');?>"><?php echo functions::formatMoney($rDisp['netpay']);?></div></td>
										</tr>
									<?php endwhile;?>
										<tr>
											<td colspan="3"><div align="right" title="<?php echo functions::formatMoney($total_gross,'~');?>"><strong><?php echo functions::formatMoney($total_gross);?></strong></div></td>
											<td><div align="right" title="<?php echo functions::formatMoney($total_absent,'~');?>"><strong><?php echo functions::formatMoney($total_absent);?></strong></div></td>
											<td><div align="right" title="<?php echo functions::formatMoney($total_netpay,'~');?>"><strong><?php echo functions::formatMoney($total_netpay);?></strong></div></td>
										</tr>
										<tr>
											<td colspan="5"><div align="right"><?php echo functions::formatMoney($total_netpay); ?> &#247 12 Months</div></td>
										</tr>
										<tr>
											<td colspan="5"><div align="right">13th Month Pay: <strong title="<?php echo functions::formatMoney($total_netpay/12,'~'); ?>"><?php echo functions::formatMoney($total_netpay/12); ?></strong></div></td>
										</tr>
									</tbody>
								</table>
							</div>
						</td>
					</tr>
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
<!-- end: JavaScript-->
</body>
</html>