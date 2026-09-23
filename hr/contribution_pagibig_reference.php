<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/read_excel_xlsx.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
$benefit_type = 'pagibig';
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : '';
$salary_monthly = $db->getValue('emp_salary','es_salary',array('emp_id'=>$eid),'ORDER BY es_date DESC');
$assign_ebr_id = $db->getValue('emp_benefit_ref','ebr_id',array('emp_id'=>$eid,'benefit_type'=>$benefit_type),'ORDER BY date_start DESC LIMIT 1');
$refid = (isset($_REQUEST['refid']) && !empty($_REQUEST['refid']) ) ? functions::decode($_REQUEST['refid']) : '';

$editRefID = (isset($_REQUEST['editRefID']) && !empty($_REQUEST['editRefID']) ) ? functions::decode($_REQUEST['editRefID']) : '';
$date_start="";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>HDMF Contribution</title>
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
	<?php
	if(isset($_POST['btnSave']) && $refid){
		$txDateStart = (isset($_POST['txDateStart']) && !empty($_POST['txDateStart']) ) ? $_POST['txDateStart'] : NULL;

		$q = $db->select('table_hdmf','*',array('thdmf_id'=>$refid));
		$r = $db->fetch_array($q);
		$arrField = array('emp_id'=>$eid,'sal_from'=>$r['sal_from'],'sal_to'=>$r['sal_to'],'er_share'=>$r['hdmf_er_share'],'ee_share'=>$r['hdmf_ee_share'],'ec'=>NULL,'benefit_total'=>$r['hdmf_total'],'date_start'=>$txDateStart,'benefit_type'=>$benefit_type);
		if( $edtRefID = $db->getValue('emp_benefit_ref','ebr_id',array('emp_id'=>$eid,'date_start'=>$txDateStart)) ){
			$db->update('emp_benefit_ref',$arrField,array('ebr_id'=>$edtRefID));
		}
		else{
			$db->insert('emp_benefit_ref',$arrField);
		}
		functions::say('Changes Saved!');
		functions::sendTo($_SERVER['PHP_SELF'].'?eid='.functions::encode($eid));
	}

	if( isset($_POST['btnUpdate']) && $editRefID ){
		$txDateStart = (isset($_POST['txDateStart']) && !empty($_POST['txDateStart']) ) ? $_POST['txDateStart'] : NULL;
		$db->update('emp_benefit_ref',array('date_start'=>$txDateStart),array('ebr_id'=>$editRefID));
		functions::say('Changes Saved!');
		functions::sendTo($_SERVER['PHP_SELF'].'?eid='.functions::encode($eid));
	}
	?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>HDMF Contribution</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="center">
					<div style="width:80%;">
						<?php
						$assign_sal_from='';$assign_sal_to='';
						if( $assign_ebr_id ){
							$qRef = $db->select('emp_benefit_ref','*',array('ebr_id'=>$assign_ebr_id));
							$rRef = $db->fetch_array($qRef);
							$assign_sal_from=$rRef['sal_from'];
							$assign_sal_to=$rRef['sal_to'];						
						?>
						<div align="left"><h2>Currently Assigned HDMF Bracket</h2></div>
						<table class="table table-bordered table-hover" style="font-size: 12px;">
							<thead>
								<tr style="background-color:#CCC">
									<th scope="col"><div align="left">SALARY</div></th>
									<th scope="col"><div align="left">COMPENSATION BRACKET</div></th>
									<th scope="col"><div align="right">EE SHARE</div></th>
									<th scope="col"><div align="right">ER SHARE</div></th>
									<th scope="col"><div align="right">TOTAL</div></th>
									<th scope="col" width="15%"><div align="center">DATE START</div></th>
									<th scope="col" width="15%"><div align="center">&nbsp;</div></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><div align="left"><?php echo functions::formatMoney($salary_monthly)?></div></td>
									<td><div align="left"><?php echo functions::formatMoney($rRef['sal_from']).' - '.functions::formatMoney($rRef['sal_to'])?></div></td>
									<td><div align="right"><?php echo functions::formatMoney($rRef['er_share'])?></div></td>
									<td><div align="right"><?php echo functions::formatMoney($rRef['ee_share'])?></div></td>
									<td><div align="right"><?php echo functions::formatMoney($rRef['benefit_total'])?></div></td>
									<td><div align="center"><?php if($editRefID){ ?><input name="txDateStart" style="font-size:12px;width:70px;" type="text" id="txDateStart" value="<?php echo $rRef['date_start'];?>" style="width: 90px;" required><?php }else{echo functions::datearr($rRef['date_start']);} ?></div></td>
									<td>
										<div align="center">
											<?php if($editRefID){ ?>
												<input type="submit" class="btn btn-info btn-mini" name="btnUpdate" value="Save">
												<a href="?eid=<?php echo functions::encode($eid) ?>" class="btn btn-mini">Cancel</a>
											<?php }else{ ?>
											<a href="?eid=<?php echo functions::encode($eid)?>&editRefID=<?php echo functions::encode($assign_ebr_id)?>" class="btn btn-warning btn-mini"><i class="halflings-icon white pencil"></i></a>
											<?php } ?>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
						<?php }else{?>
							<div align="left">
								<h2>No Assigned HDMF Bracket, Please choose from the reference list below.</h2>
								<div>Current Monthly Salary: <strong><?php echo functions::formatMoney($salary_monthly)?></strong></div>
							</div>
						<?php }?>
						<?php if($refid){ ?>
						<div align="left" style="padding-top:50px;"><h2>NEW REFERENCE SELECTED</h2></div>
						<table class="table table-bordered table-hover" style="font-size: 12px;">
							<thead>
								<tr style="background-color:#CCC">
									<th scope="col"><div align="left">COMPENSATION BRACKET</div></th>
									<th scope="col"><div align="right">EE SHARE</div></th>
									<th scope="col"><div align="right">ER SHARE</div></th>
									<th scope="col"><div align="right">TOTAL</div></th>
									<th scope="col"><div align="center">DATE START</div></th>
									<th scope="col"><div align="center">&nbsp;</div></th>
								</tr>
							</thead>
							<tbody>
							<?php
							$qDisp = $db->select('table_hdmf','*',array('thdmf_id'=>$refid));
							while($rDisp = $db->fetch_array($qDisp)):
							$id = $rDisp['thdmf_id'];
							?>
								<tr>
									<td><div align="left"><?php echo functions::formatMoney($rDisp['sal_from']).' - '.functions::formatMoney($rDisp['sal_to'])?></div></td>
									<td><div align="right"><?php echo functions::formatMoney($rDisp['hdmf_er_share'])?></div></td>
									<td><div align="right"><?php echo functions::formatMoney($rDisp['hdmf_ee_share'])?></div></td>
									<td><div align="right"><?php echo functions::formatMoney($rDisp['hdmf_total'])?></div></td>
									<td><div align="center"><input name="txDateStart" type="text" id="txDateStart" value="<?php echo $date_start;?>" style="width: 90px;" required></div></td>
									<td>
										<div align="center">
											<input type="submit" class="btn btn-info btn-small" name="btnSave" value="Save">
											<a href="?eid=<?php echo functions::encode($eid) ?>" class="btn btn-small">Cancel</a>
										</div>
									</td>
								</tr>
							<?php endwhile;?>
							</tbody>
						</table>
						<?php } ?>
						<?php if(empty($refid)){ ?>
						<div align="left" style="padding-top:70px;"><h2>REFERENCES</h2></div>
						<table class="table table-bordered table-hover" style="font-size: 12px;">
							<thead>
								<tr style="background-color:#CCC">
									<th scope="col"><div align="left">COMPENSATION BRACKET</div></th>
									<th scope="col"><div align="right">EE SHARE</div></th>
									<th scope="col"><div align="right">ER SHARE</div></th>
									<th scope="col"><div align="right">TOTAL</div></th>
									<th scope="col" width="15%"><div align="center">SELECT</div></th>
								</tr>
							</thead>
							<tbody>
							<?php
							$qDisp = $db->select('table_hdmf','*',array(),'ORDER BY sal_from');
							while($rDisp = $db->fetch_array($qDisp)):
							$id = $rDisp['thdmf_id'];
							$bg='';$recommend='';$selected='';
							if($assign_sal_from==$rDisp['sal_from'] && $assign_sal_to==$rDisp['sal_to']){
								$bg = 'background-color:#FF9';
								$selected="selected";
							}
							if( empty($assign_ebr_id) && ($salary_monthly>=$rDisp['sal_from'] && $salary_monthly<=$rDisp['sal_to']) ){
								$recommend='&nbsp;&nbsp;&nbsp;&nbsp;(Recommended)';
								$bg = 'background-color:#FF9';
							}
							?>
								<tr style="<?php echo $bg ?>" onClick="window.location='?eid=<?php echo functions::encode($eid)?>&refid=<?php echo functions::encode($id)?>'">
									<td><div align="left"><?php echo functions::formatMoney($rDisp['sal_from']).' - '.functions::formatMoney($rDisp['sal_to'])?><?php echo $recommend; ?></div></td>
									<td><div align="right"><?php echo functions::formatMoney($rDisp['hdmf_er_share'])?></div></td>
									<td><div align="right"><?php echo functions::formatMoney($rDisp['hdmf_ee_share'])?></div></td>
									<td><div align="right"><?php echo functions::formatMoney($rDisp['hdmf_total'])?></div></td>
									<td>
										<div align="center">
										<?php if(empty($selected)){ ?>
											<input type="radio" name="rdo" id="rdo<?php echo $id?>" onClick="window.location='?eid=<?php echo functions::encode($eid)?>&refid=<?php echo functions::encode($id)?>'">
										<?php }else{echo 'Currently Assigned';}?>
									</div>
									</td>
								</tr>
							<?php endwhile;?>
							</tbody>
						</table>
						<?php } ?>
					</div>
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
<script>

$(document).ready(function(){
	$('#txDateStart').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		hideIfNoPrevNext: true,
		showMonthAfterYear: true,
	});
});

function delt(){
	if(confirm('Do you want to remove this Reference?'))
		return true;
	else
		return false; 
}
</script>
<!-- end: JavaScript-->
</body>
</html>