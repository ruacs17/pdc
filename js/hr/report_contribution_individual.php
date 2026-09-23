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
$contribution_type = (isset($_REQUEST['type']) && !empty($_REQUEST['type']) ) ? functions::decode($_REQUEST['type']) : '';

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
$txbYear = (isset($_REQUEST['slyr']) && !empty($_REQUEST['slyr']) ) ? $_REQUEST['slyr'] : $db->getValue('emp_attendance ea, emp_payroll_detail epd','DISTINCT ea.att_year',array('epd.emp_id'=>$emp_id),'AND ea.eat_id=epd.eat_id ORDER BY ea.att_year DESC LIMIT 1');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title><?php echo strtoupper($contribution_type)?> Contribution Detail</title>
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
				<div align="center" style="padding-bottom: 15px;"><h2><?php #echo strtoupper($contribution_type) ?> Premium</h2></div>
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
										$wrkStat .= (isset($rStat['project_based']) && $rStat['project_based']==1) ? ' <i>Project Based</i> ' : '';
										$wrkDate = (isset($rStat['ews_date'])) ? ' ('.functions::datearr($rStat['ews_date']).')' : '';
										echo $wrkStat . $wrkDate;
										?>
									</td>
								</tr>
							</table>
						</td>
						<td valign="top">
							<div align="center">
								Year: 
								<select name="selYr" id="selYr" style="width:95px;font-size:12px;border:2px solid #000;" onChange="getYr(this.value)">
									<option value="">--select--</option>
									<?php
									$qYr = $db->select('emp_attendance ea, emp_payroll_detail epd','DISTINCT ea.att_year yr',array('epd.emp_id'=>$emp_id),'AND ea.eat_id=epd.eat_id ORDER BY ea.att_year DESC');
									while($rYr = $db->fetch_array($qYr)):
									?>
									<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
									<?php endwhile;?>
								</select>
								<div style="width:95%;padding-top:20px;">
									<table class="table table-bordered table-hover table-striped" style="font-size: 12px;">
										<thead>
											<tr style="background-color:#CCC;">
												<th width="10%" scope="col"><div align="left">Month</div></th>
												<th width="10%" scope="col"><div align="right">Base Pay</div></th>
												<th width="10%" scope="col"><div align="right">SSS</div></th>
												<th width="10%" scope="col"><div align="right">Philhealth</div></th>
												<th width="10%" scope="col"><div align="right">HDMF</div></th>
												<th width="10%" scope="col"><div align="right">Total</div></th>
											</tr>
										</thead>
										<tbody>
										<?php
										$countEmp=0;
										$qDisp = $db->select('emp_attendance ea, emp_payroll_detail epd','epd.emp_id,ea.att_month,ea.att_year,sum(regular-absent) as totalBasePayOffice,sum(regular) as totalBasePayLabor,sum(sss) as totalSSS,sum(hdmf) as totalhdmf,sum(ph) as totalph,salary_type',array('epd.emp_id'=>$emp_id,'ea.att_year'=>$txbYear),'AND ea.eat_id=epd.eat_id GROUP BY epd.emp_id,ea.att_month,ea.att_year ORDER BY ea.att_year DESC,ea.att_month DESC');
										#echo $db->last_query;
										$total_contribution=0;
										$total_sss=0; $total_ph=0;$total_hdmf=0;
										while($rDisp = $db->fetch_array($qDisp)):
											$countEmp++;
											$base_pay = ($rDisp['salary_type']=='flexible') ? $rDisp['totalBasePayOffice'] : $rDisp['totalBasePayLabor'];
											$total_sss += $rDisp['totalSSS'];
											$total_ph += $rDisp['totalph'];
											$total_hdmf += $rDisp['totalhdmf'];
										?>
											<tr>
												<td><div align="left"><?php echo date('F',strtotime('2020-'.$rDisp['att_month'].'-01'));#functions::datearr($rDisp['date_start']).' - '.functions::datearr($rDisp['date_end']);?></div></td>
												<td><div align="right"><?php echo functions::formatMoney($base_pay);?></div></td>
												<td><div align="right"><?php echo functions::formatMoney($rDisp['totalSSS']);?></div></td>
												<td><div align="right"><?php echo functions::formatMoney($rDisp['totalph']);?></div></td>
												<td><div align="right"><?php echo functions::formatMoney($rDisp['totalhdmf']);?></div></td>
												<td><div align="right"><?php echo functions::formatMoney($rDisp['totalSSS']+$rDisp['totalph']+$rDisp['totalhdmf']);?></div></td>
											</tr>
										<?php endwhile;?>
											<tr>
												<td><div align="center"><strong>Total</strong></div></td>
												<td><div align="right"><strong><?php #echo functions::formatMoney($total_sss);?></strong></div></td>
												<td><div align="right"><strong><?php echo functions::formatMoney($total_sss);?></strong></div></td>
												<td><div align="right"><strong><?php echo functions::formatMoney($total_ph);?></strong></div></td>
												<td><div align="right"><strong><?php echo functions::formatMoney($total_hdmf);?></strong></div></td>
												<td><div align="right"><strong><?php echo functions::formatMoney($total_hdmf+$total_sss+$total_ph);?></strong></div></td>
											</tr>
										</tbody>
									</table>
								</div>
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
<script type="text/javascript">
function getYr(yr){
	window.location='?empid=<?php echo functions::encode($emp_id)?>&type=<?php echo functions::encode($contribution_type)?>&slyr='+yr;
}
</script>
<!-- end: JavaScript-->
</body>
</html>