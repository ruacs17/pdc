<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$pi_id = ( isset($_REQUEST['piid']) && !empty($_REQUEST['piid']) ) ? functions::decode($_REQUEST['piid']) : '';
$_SESSION['notif_id3_list']=$pi_id;
if(isset($_REQUEST['alter_calc']) && $pi_id){
	$alter_calc="";
	$selCalc = ( isset($_REQUEST['alter_calc']) ) ? $_REQUEST['alter_calc'] : '';
	if($selCalc==1)
		$alter_calc=1;
	if($selCalc==0)
		$alter_calc=0;
	if( ($alter_calc==1) || ($alter_calc==0) ){
		$db->update('project_income',array('alter_calc'=>$selCalc),array('pi_id'=>$pi_id));
		$_SESSION['notif_success']='Changes saved!';
		functions::sendTo(functions::pageName().'?piid='.functions::encode($pi_id));
		die();
	}
}
$proj_id = $db->getValue('project_income','proj_id',array('pi_id'=>$pi_id));
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$proj_id));
$tin = $db->getValue('project','tin',array('proj_id'=>$proj_id));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Project Billing Calculation Option</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
	<style>.padleft{padding-right: 5px;}</style>
	<!-- end: Favicon -->
	<style>
	.lnkOpt{
		opacity: 0.4;
		filter: alpha(opacity=20);
		cursor:pointer
	}
	.lnkOpt:hover {
		opacity: 1.0;
		filter: alpha(opacity=100);
		cursor:pointer
	}
	.scrollme {
		overflow-y: auto;
	}
	</style>
	</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div> 
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header">
			<h2><i class="halflings-icon white th"></i><span class="break"></span>Income Billing Report</h2>
		</div>
		<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
			<tr>
				<td width="50%"><div align="right"><a id="adc" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'dash-proj-billing-income-add.php?y=<?php echo functions::encode($year)?>&m=<?php echo functions::encode($selMonth)?>','Statement Add')">Add New Statement</a>&nbsp;&nbsp;&nbsp;</div></td>
			</tr>
		</table>
		<form method="post">
			<div class="box-content">
				<table class="table table-bordered" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="25%">PROJECT</th>
							<th width="12%"><div align="right">AMOUNT BILLED</div></th>
							<th width="12%"><div align="right">AMOUNT COLLECTED</div></th>
							<th width="10%"><div align="right">SALES SERVICE</div></th>
							<th width="10%"><div align="right">VAT</div></th>
							<th width="10%"><div align="right">EWT</div></th>
						</tr>
					</thead>
					<tbody>
						<?php $monthlyCollected=0;$monthlyBilled=0;$monthlySalesService=0;$monthlyVat=0;$monthlyEWT=0;$vat=0;$ewt=0;$name='';$monthlyContraTax=0;?>
						<tr>
							<td colspan="6"><strong><?php echo $proj_name?></strong></td>
						</tr>
						<tr>
							<td><div style="font-size:14px;"><?php echo ($tin) ? 'TIN: <strong>'.$tin.'</strong>' : '';?></div></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>Billing Date: <strong><?php echo functions::datearr($db->getValue('project_income','pi_date',array('pi_id'=>$pi_id)))?></strong></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<?php
						$contracTax=0; $recoupment=0; $retention=0;
						$qProj = $db->select('project_income','*',array('pi_id'=>$pi_id));
						$rProj = $db->fetch_array($qProj);
						$alter_calc = $rProj['alter_calc'];
						$vat=0; $ewt=0;
						$contracTax += $rProj['contractor'];
						$recoupment += $rProj['recoupment'];
						$retention += $rProj['retention'];

						$amountBilled = $rProj['amount'];
						$amountCollected = $amountBilled - ($rProj['vat']+$rProj['ewt']+$rProj['retention']+$rProj['contractor']+$rProj['recoupment']);

						$salesService = $amountBilled / 1.12;
						$vat = $rProj['vat'];
						$ewt = $rProj['ewt'];
						?>
						<tr>
							<td colspan="6" height="30">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="6"><label><input type="radio" name="rdoCalc" value="0" onClick="selCalc(this.value)" <?php echo ($alter_calc==0) ? 'checked="checked"' : '';?>>&nbsp;&nbsp;Calculation 1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>Payment / 1.12</strong></label></td>
						</tr>
						<tr>
							<td>&nbsp;&nbsp;<?php echo $rProj['name'];?></td>
							<td><div align="right"><?php echo functions::formatMoney($amountBilled);?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($amountCollected)?></div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($salesService);?></strong></div></td>
							<td><div align="right"><?php echo functions::formatMoney($vat);?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($ewt);?></div></td>
						</tr>
						<?php
						if($recoupment){?>
						<tr>
							<td>&nbsp;&nbsp;<i>Recoupment</i></td>
							<td><div align="right"><?php echo functions::formatMoney($recoupment);?></div></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<?php }//end if($recoupment){
						if($contracTax){?>
						<tr>
							<td>&nbsp;&nbsp;<i>Contractor's Tax</i></td>
							<td><div align="right"><?php echo functions::formatMoney($contracTax);?></div></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<?php }//end if($contracTax){
						if($retention){?>
						<tr>
							<td>&nbsp;&nbsp;<i>Retention</i></td>
							<td><div align="right"><?php echo functions::formatMoney($retention);?></div></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<?php }//end if($retention){?>
						<tr>
							<td colspan="6" height="50">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="6"><label><input type="radio" name="rdoCalc" value="1" onClick="selCalc(this.value)" <?php echo ($alter_calc==1) ? 'checked="checked"' : '';?>>&nbsp;&nbsp;Calculation 2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>(Payment - Recoupment) / 1.12</strong></label></td>
						</tr>
						<?php
						$salesService = ($amountBilled-$recoupment) / 1.12;
						?>
						<tr>
							<td>&nbsp;&nbsp;<?php echo $rProj['name'];?></td>
							<td><div align="right"><?php echo functions::formatMoney($amountBilled);?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($amountCollected)?></div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($salesService);?></strong></div></td>
							<td><div align="right"><?php echo functions::formatMoney($vat);?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($ewt);?></div></td>
						</tr>
						<?php
						if($recoupment){?>
						<tr>
							<td>&nbsp;&nbsp;<i>Recoupment</i></td>
							<td><div align="right"><?php echo functions::formatMoney($recoupment);?></div></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<?php }//end if($recoupment){
						if($contracTax){?>
						<tr>
							<td>&nbsp;&nbsp;<i>Contractor's Tax</i></td>
							<td><div align="right"><?php echo functions::formatMoney($contracTax);?></div></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<?php }//end if($contracTax){
						if($retention){?>
						<tr>
							<td>&nbsp;&nbsp;<i>Retention</i></td>
							<td><div align="right"><?php echo functions::formatMoney($retention);?></div></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<?php }//end if($retention){?>
					</tbody>
				</table>
			</div>
		</form>
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
<script>
function delt(){
	if(confirm('Do you want to remove this Billing?'))
		return true;
	else
		return false; 
}
function selCalc(choice){
	window.location="<?php echo functions::pageName() ?>?piid=<?php echo functions::encode($pi_id)?>&alter_calc="+choice
}
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
<!-- end: JavaScript-->
</body>
</html>