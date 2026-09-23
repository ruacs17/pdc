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
$q = $db->select('employee','*',array('emp_id'=>$eid));
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

$deleteItem = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
$pdf_detail='';$pdf_total_amount='';$pdf_payroll_deduction='';
$arrPosition=array();
if($deleteItem){
	$_SESSION['notif_warning']='Add-on Removed!';
	$db->delete('payroll_deduction_reference',array('pdf_id'=>$deleteItem));
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Payroll Deduction</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/formatCurrency.js"></script>
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
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li class="active"><a href="payroll_addon_reference_detail.php?eid=<?php echo functions::encode($eid)?>" style="opacity:.9">Add-ons</a></li>
				<li><a href="payroll_deduction_reference_detail.php?eid=<?php echo functions::encode($eid)?>">Deduction</a></li>
			</ul>
			<div align="right"><a id="adc" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'payroll_addon_reference_manage.php?eid=<?php echo functions::encode($eid);?>','Add-on Manage')">Add New Add-ons</a></div><br>
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>PAYROLL ADD-ON MANAGE</h2></div>
				<table width="99%" border='0'>
					<tr>
						<td width="30%">
							<table border='0' width="99%">
								<tr>
									<td colspan="2"><img width="300" height="300" src="../img_emp/<?php echo $file;?>"></td>
								</tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr>
									<td width="20%" height="30"><i>Name</i></td>
									<td><strong><?php echo $db->getValue('employee','concat(lname, ", ", fname)',array('emp_id'=>$eid));?></strong></td>
								</tr>
								<tr>
									<td height="30"><i>Position</i></td>
									<td><?php echo position($eid); ?></td>
								</tr>
								<tr>
									<td height="30"><i>Status</i></td>
									<td>
										<?php
										$qStat = $db->select('emp_work_status','*',array('emp_id'=>$eid,'ews_stat'=>$r['work_status']),'ORDER BY ews_date DESC');
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
								<table id="tblist" width="99%" align="center" border="0" class="table <?php if(!isset($_SESSION['notif_indi'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size:12px;">
									<thead>
										<tr style="background-color:#CCC">
											<th width="12%"><div align="center">Date Start</div></th>
											<th width="30%" style="padding: 10px"><div align="left">Add-on Details</div></th>
											<th width="20%"><div align="center">Amount Per Payroll</div></th>
											<th width="10%"><div align="center">Status</div></th>
											<th width="8%"><div align="center">Option</div></th>
										</tr>
									</thead>
									<tbody>
									<?php
									$count=0;
									$qD = $db->select('payroll_deduction_reference','*',array('emp_id'=>$eid,'adjustment_type'=>'addon'),'ORDER BY adjustment_start DESC');
									while($rD = $db->fetch_array($qD)):
										$rID = $rD['pdf_id'];
										$total_deduction_amount = $rD['total_amount'];
										$hasRecord = $db->getValue('payroll_adjustment','count(pdf_id)',array('pdf_id'=>$rID));
										$payment = $db->getValue('payroll_adjustment pa, emp_attendance ea','sum(adjustment_value)',array('pdf_id'=>$rID,'confirmed'=>1),'AND pa.eat_id=ea.eat_id');
										$count++;
									?>
										<tr id="rw<?php echo $rID;?>">
											<td style="padding: 10px"><div align="center"><?php echo functions::datearr($rD['adjustment_start'])?></div></td>
											<td style="padding: 10px"><div align="left"><?php echo $rD['pdf_detail']?></div></td>
											<td style="padding: 10px"><div align="center"><?php echo functions::formatMoney($rD['payroll_deduction'])?></div></td>
											<td style="padding: 10px"><div align="center"><?php echo $rD['active'];?></div></td>
											<td style="padding: 10px">
												<div align="left">
													<a id="edt<?php echo $rID?>" class="btn btn-mini btn-warning thickbox" title="Deduction Detail" data-rel="tooltip" onclick="showThis(this.id,'payroll_addon_reference_manage.php?eid=<?php echo functions::encode($eid);?>&edtID=<?php echo functions::encode($rID);?>','Deduction Detail')"><i class="halflings-icon white pencil"></i></a>
													<?php if($hasRecord==0){?><a id="del<?php echo $rID;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Item" data-rel="tooltip" href="?eid=<?php echo functions::encode($eid);?>&eid=<?php echo functions::encode($eid);?>&delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a><?php }?>
												</div>
											</td>
										</tr>
									<?php endwhile;
									if($count==0){?>
										<tr>
											<td colspan="6" style="padding: 10px"><div align="center">--Nothing to Report--</div></td>
										</tr>
									<?php }?>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function delt(){if(confirm('Do you want to remove this Add-on?'))return true; else return false;}
</script>
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
<?php if(isset($_SESSION['notif_indi'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_indi'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_indi'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_indi'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	window.setTimeout(function(){$('#rw<?php echo $_SESSION['notif_indi'] ?>').css('border','1px solid black');}, 5000);
});
</script>
<?php unset($_SESSION['notif_indi']);} ?>
</body>
</html>