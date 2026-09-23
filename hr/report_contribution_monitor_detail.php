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
#$contribution_type = (isset($_REQUEST['type']) && !empty($_REQUEST['type']) ) ? functions::decode($_REQUEST['type']) : '';
$contribution_type = $selType = ( isset($_SESSION['pr_type']) && !empty($_SESSION['pr_type']) ) ? $_SESSION['pr_type'] : '';
$selYear = ( isset($_SESSION['pr_yr']) && !empty($_SESSION['pr_yr']) ) ? $_SESSION['pr_yr'] : '';
$selMon = ( isset($_SESSION['pr_mon']) && !empty($_SESSION['pr_mon']) ) ? $_SESSION['pr_mon'] : '';
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
				<div align="center" style="padding-bottom: 15px;"><h2><?php echo strtoupper($contribution_type) ?> Premium</h2></div>
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
									<td><strong><?php echo $r['lname'].', '.$r['fname'].' '.$r['extname'];?></strong></td>
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
								<div style="width:95%;padding-top:20px;">
									<table class="table-hover" border="1" style="font-size: 12px;">
										<thead>
											<tr bgcolor="#CCC">
												<th width="5%" scope="col" height="25">Payroll No.</th>
												<th width="7%" scope="col">DATE</th>
												<th width="5%" scope="col">EE SHARE</th>
												<th width="5%" scope="col">ER SHARE</th>
												<?php if($selType=='SSS'){ ?>
												<th width="5%" scope="col">EC</th>
												<?php } ?>
												<th width="5%" scope="col">TOTAL</th>
											</tr>
										</thead>
										<tbody>
											<?php
											if($selMon)
												$q = $db->select('payroll_premium pp, emp_attendance ea','*',array('pp.pp_year'=>$selYear,'pp.pp_month'=>$selMon,'pp.pp_type'=>$selType,'submit'=>1,'pp.emp_id'=>$emp_id),'AND ea.eat_id=pp.eat_id AND ea.confirmed=1');
											else
												$q = $db->select('payroll_premium pp, emp_attendance ea','*',array('pp.pp_year'=>$selYear,'pp.pp_type'=>$selType,'submit'=>1,'pp.emp_id'=>$emp_id),'AND ea.eat_id=pp.eat_id AND ea.confirmed=1');
											#echo $db->last_query;
											$totalEE=0;$totalER=0;$totalEC=0;$totalShare=0;
											while($r = $db->fetch_array($q)):
												$totalEE+=$r['ee_share'];
												$totalER+=$r['er_share'];
												$totalEC+=$r['ec'];
												$totalShare+=$r['pp_total'];
											?>
											<tr>
												<td height="25"><div align="center"><?php echo $r['payroll_no']; ?></div></td>
												<td><div align="center"><?php echo functions::datearr($r['date_start']).' - '.functions::datearr($r['date_end']) ?></div></td>
												<td><div align="center"><?php echo functions::formatMoney($r['ee_share']); ?></div></td>
												<td><div align="center"><?php echo functions::formatMoney($r['er_share']); ?></div></td>
												<?php if($selType=='SSS'){ ?>
												<td><div align="center"><?php echo functions::formatMoney($r['ec']); ?></div></td>
												<?php } ?>
												<td><div align="center"><?php echo functions::formatMoney($r['pp_total']); ?></div></td>
											</tr>
											<?php endwhile; ?>
											<tr>
												<td height="25" colspan="2"><div align="center"></div></td>
												<td><div align="center"><strong><?php echo functions::formatMoney($totalEE); ?></strong></div></td>
												<td><div align="center"><strong><?php echo functions::formatMoney($totalER); ?></strong></div></td>
												<?php if($selType=='SSS'){ ?>
												<td><div align="center"><strong><?php echo functions::formatMoney($totalEC); ?></strong></div></td>
												<?php } ?>
												<td><div align="center"><strong><?php echo functions::formatMoney($totalShare); ?></strong></div></td>
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