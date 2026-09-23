<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/voucher_advance.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$arr = array();
$txbYear=date('Y');
$supplierID='';
$txbMon=date('m');
$txVtype='';

function getFullWeeksOfMonth($iYear, $iMonth, $sFirstDayOfWeek='Monday', $bExclusive = false) {

	$iYear = filter_var($iYear, FILTER_VALIDATE_INT, array('options' => array('default' => (int) date('Y')) ));
	$aDay = array('monday'=>1, 'sunday'=>1);
	$sFirstDayOfWeek = filter_var($sFirstDayOfWeek , FILTER_VALIDATE_REGEXP, array(
		'options' => array(
			'default' => 'saturday',
			'regexp' => '/^monday|sunday$/'
		)
	));
	$bExclusive =  filter_var($bExclusive,FILTER_VALIDATE_BOOLEAN);
	$oStart = new DateTime($iYear . '-' . $iMonth . '-01');

	if ($bExclusive === true || ($bExclusive === false && isset($aDay[strtolower($oStart->format('l'))]))) {
		if ((int) $oStart->format('d') === 1) {
			$oStart->modify('-1 day');
		}
		$oStart->modify('first ' . $sFirstDayOfWeek . ' ' . $oStart->format('H:i'));
	}else{
		$oStart->modify('last ' . $sFirstDayOfWeek . ' ' . $oStart->format('H:i'));
	}

	$oEnd = clone($oStart);
	if((int)$oStart->format('m')===$iMonth){
		$oEnd->modify('last day of this month');
	}else{
		$oEnd->modify('last day of next month');
	}

	$oInterval = new DateInterval('P1W7D');
	$oDaterange = new DatePeriod($oStart, $oInterval, $oEnd);

	$aDate = array();
	$i = 1;
	foreach ($oDaterange as $oDate) {
		$oTestDate = clone $oDate;
		$oLastWeekDay = $oTestDate->modify('+6 days');
		if (
			((int) $oDate->format('m') === (int) $iMonth || (int) $oLastWeekDay->format('m') === (int) $iMonth) &&
			(($bExclusive === true && (int) $oLastWeekDay->format('m') === (int) $iMonth) ||
			($bExclusive === false))
		) {
			$aDate[$i]['First'] = $oDate->format('Y-m-d');
			$aDate[$i]['Last'] = $oLastWeekDay->format('Y-m-d');
		}
		$i++;
	}
	return $aDate;
}

$txbYear = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');
$txbMon = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : date('m');
$txVtype = ( isset($_REQUEST['vt']) && !empty($_REQUEST['vt']) ) ? functions::decode($_REQUEST['vt']) : '';
if(isset($_POST['btnSearch'])){
	$txbYear = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : date('Y');
	$txbMon = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : 0;
	$txVtype = ( isset($_POST['vType']) && !empty($_POST['vType']) ) ? $_POST['vType'] : '';
}
if($txbMon && $txbYear)
	$arr = array('LEFT(cheque_date,7)'=>$txbYear.'-'.$txbMon);
elseif($txbYear)
	$arr = array('LEFT(cheque_date,4)'=>$txbYear);
if($txVtype)
	$arr = array_merge($arr,array('vt_id'=>$txVtype));

$qVTid = ($txVtype) ? 'AND vt_id="'.$db->clean($txVtype).'"' : '';
$qDate = $db->select('voucher','DISTINCT LEFT(cheque_date,7) as dte',$arr,'ORDER BY cheque_date');
$count=0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Voucher Weekly List</title>
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
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Weekly Voucher List View</h2>
		</div>
		<div class="box-content" align="center">
			<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
				<tr>
					<td width="50%"><div align="right"><a id="whprint" class="btn btn-info btn-small" href="voucher_list_weekly_print.php?y=<?php echo functions::encode($txbYear)?>&m=<?php echo functions::encode($txbMon)?>&vt=<?php echo functions::encode($txVtype)?>"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div></td>
				</tr>
			</table>
			<form method="post">
				<div align="center">
					<table width="40%" border="0">
						<tr>
							<td align="left" style="padding: 12px 5px 4px 0px">
								<div align="right">
									<select name="bdYear" id="bdYear" style="width:90px;">
										<option value="">Select Year</option>
										<?php
										$qYr = $db->select('voucher','DISTINCT LEFT(cheque_date,4) as yr',array(),'WHERE cheque_date IS NOT NULL ORDER BY cheque_date DESC');
										while($rYr = $db->fetch_array($qYr)):
										?>
										<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
										<?php endwhile;?>
									</select>
									<select name="bdMon" id="bdMon" style="width:95px;">
										<option value="">All Month</option>
										<option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
										<option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
										<option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
										<option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
										<option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
										<option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
										<option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
										<option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
										<option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
										<option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
										<option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
										<option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
									</select>
									<select name="vType" id="vType">
										<option value="">All Voucher Type</option>
										<?php 
										$qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
										while($rVtype = $db->fetch_array($qVtype)):
										?>
										<option value="<?php echo $rVtype['vt_id']?>" <?php if($txVtype==$rVtype['vt_id'])echo 'selected="selected"';?>><?php echo $rVtype['vt_name']?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
							<td valign="middle"><input type="submit" name="btnSearch" id="btnSearch" value="View" class="btn btn-small btn-primary"></td>
						</tr>
					</table>
				</div><br><br>
				<table align="center" border="1" class="table table-bordered">
				<?php
				$curMonth='';$totalCost=0;$monthlyCost=0;$cost=0;$rank=0;$monthly=0;$total=0;$count=0;$monthly_advance_paymet=0;$monthly_withholding=0;$payable_amount=0;$weekly_payable=0;$monthly_payable=0;
				while($rDate = $db->fetch_array($qDate)):
					$monthly_payable=0;
					$monthly_advance_paymet=0;
					$monthly_withholding=0;
					$monthly=0;
					$rank++;
					$time = mktime(0,0,0,substr($rDate['dte'],5,2),1,$txbYear);
					$monthName = date('F',$time);
					$txbMon = date('m',$time);
				?>
					<tr>
						<td height="30" width="10%"><strong><?php echo $monthName.' '.$txbYear;?></strong></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td height="30">
							<?php $a = getFullWeeksOfMonth($txbYear, $txbMon, 'Sunday');?>
							<table class="table table-hover">
								<thead>
									<tr style="background-color:#CCC;">
										<th>Sat</th>
										<th>Sun</th>
										<th>Mon</th>
										<th>Tue</th>
										<th>Wed</th>
										<th>Thu</th>
										<th>Fri</th>
										<th style="text-align:right;padding-right:5px;">Payable Amount</th>
									</tr>
								</thead>
								<tbody>
								<?php foreach($a as $wk): $weekly_payable=0;?>
									<tr>
									<?php
									for($i=0; $i<=6; $i++):
										$date_arr = explode('-',$wk['First']);
										$mn = ( isset($date_arr[1]) ) ? $date_arr[1] : date('m');
										$dy = ( isset($date_arr[2]) ) ? $date_arr[2] : date('d');
										$Yr = ( isset($date_arr[0]) ) ? $date_arr[0] : date('Y');
										$time = mktime(0,0,0,$mn,($dy + $i),$Yr);
									?>
										<td <?php if( date('Y-m-d')==date('Y-m-d',$time) )echo 'bgcolor="#f5ae00"';?>><?php echo date('M. d',$time)?></td>
									<?php
									endfor;
									$qVoucher = $db->query('SELECT * FROM voucher WHERE cheque_date BETWEEN "'.$db->clean($wk['First']).'" AND "'.$db->clean($wk['Last']).'" '.$qVTid.' ORDER BY cheque_date');
									while($rVouch = $db->fetch_array($qVoucher)):
										$payable_amount=0;
										$otherSurcharge  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$rVouch['voucher_id'],'charge_type'=>'surcharge'));
										$payable_amount += $otherSurcharge;
										$amount_q = $db->query('SELECT SUM(vd.amount) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rVouch['voucher_id']).'"'.$qVTid);
										$non_po = $db->result($amount_q);
										$payable_amount += $non_po;

										$amount_po_q = $db->query('SELECT sum(amount) FROM voucher v,voucher_particular vp, voucher_po_payment vpp WHERE v.voucher_id=vp.voucher_id AND vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($rVouch['voucher_id']).'"'.$qVTid);
										$po = $db->result($amount_po_q);
										$payable_amount += $po;

										$withholding = $rVouch['witholding_tax'];
										$withholding_vat = $rVouch['witholding_vat'];
										$withholding_percent = ($withholding) ? $withholding / 100 : 0;

										$hasAdvance = $db->getValue('voucher_advance_payment','count(*)',array('voucher_id_owner'=>$rVouch['voucher_id']));
										//if voucher has advance payment, it also computed the withholding tax.
										if($hasAdvance){
											$va = new voucherAdvance($rVouch['voucher_id']);
											$payable_amount = $va->current_voucher_payable_amount;
										}
										else{//Deduction of the withholding tax.
											if($withholding_vat==1)
												$w_tax = ($withholding_percent && $payable_amount) ? (($payable_amount) / 1.12) * $withholding_percent : 0;
											else
												$w_tax = ($withholding_percent && $payable_amount) ? $payable_amount * $withholding_percent : 0;
											$payable_amount -= $w_tax;
										}

										$otherDeduction  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$rVouch['voucher_id'],'charge_type'=>'deduction'));
										if($otherDeduction)
											$payable_amount -= $otherDeduction;

										$total += $payable_amount;
										$monthly += $payable_amount;
										$weekly_payable += $payable_amount;
										$monthly_payable += $payable_amount;
									endwhile;
									?>
										<td width="15%" style="text-align:right;padding-right:15px;"><a id="costdetail<?php echo $count++?>" class="label label-info thickbox" title="View Voucher List" data-rel="tooltip" onclick="showThis(this.id,'voucher_list_weekly_detail.php?wkF=<?php echo functions::encode($wk['First'])?>&wkE=<?php echo functions::encode($wk['Last'])?>&vt=<?php echo functions::encode($txVtype)?>','Voucher List','1')"><?php echo functions::formatMoney($weekly_payable);?></a></td>
									</tr>
								<?php endforeach;?>
									<tr>
										<td colspan="7"><div align="right">Monthly</div></td>
										<td style="text-align:right;padding-right:15px;"><strong><?php echo functions::formatMoney($monthly_payable);?></strong></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
					<?php
					endwhile;#endwhile PODate?>
					<tr>
						<td>&nbsp;</td>
						<td><div align="right">Total&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo functions::formatMoney($total);?></strong></div></td>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>