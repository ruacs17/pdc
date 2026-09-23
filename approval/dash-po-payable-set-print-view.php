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

$yr = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? $db->clean(functions::decode($_REQUEST['yr'])) : 0;
$mn = (isset($_REQUEST['mn']) && !empty($_REQUEST['mn']) ) ? $db->clean(functions::decode($_REQUEST['mn'])) : 0;
$supplierID = (isset($_REQUEST['sup']) && !empty($_REQUEST['sup']) ) ? functions::decode($_REQUEST['sup']) : 0;
$fMon=0;$fDay=0;$fYr=0; $tMon=0;$tDay=0;$tYr=0;
$dteFrom = (isset($_REQUEST['fmDte']) && !empty($_REQUEST['fmDte']) ) ? $db->clean(functions::decode($_REQUEST['fmDte'])) : 0;
if($dteFrom){
	$rDteFrm = explode("-",$dteFrom);
	if( count($rDteFrm) ){
		$fMon = $rDteFrm[1];
		$fDay = $rDteFrm[2];
		$fYr = $rDteFrm[0];
	}
}
$dteTo = (isset($_REQUEST['toDte']) && !empty($_REQUEST['toDte']) ) ? $db->clean(functions::decode($_REQUEST['toDte'])) : 0;
if($dteTo){
	$rDteTo = explode("-",$dteTo);
	if( count($rDteFrm) ){
		$tMon = $rDteTo[1];
		$tDay = $rDteTo[2];
		$tYr = $rDteTo[0];
	}
}
$fromMon = ( isset($_POST['fromMon']) ) ? $_POST['fromMon'] : $fMon;
$fromDay = ( isset($_POST['fromDay']) ) ? $_POST['fromDay'] : $fDay;
$fromYear = ( isset($_POST['fromYear']) ) ? $_POST['fromYear'] : $fYr;
$dateFrom = ($fromMon && $fromDay && $fromYear) ? $fromYear.'-'.$fromMon.'-'.$fromDay : '';

$toMon = ( isset($_POST['toMon']) ) ? $_POST['toMon'] : $tMon;
$toDay = ( isset($_POST['toDay']) ) ? $_POST['toDay'] : $tDay;
$toYear = ( isset($_POST['toYear']) ) ? $_POST['toYear'] : $tYr;
$dateTo = ($toMon && $toDay && $toYear) ? $toYear.'-'.$toMon.'-'.$toDay : '';
$qYrMn='';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>P.O. Items</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
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
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Purchase Order Details By Project</h2>
		</div>
		<div class="box-content"><br>
			<div>Supplier: <strong><?php echo $db->getValue('supplier','name',array('supplierID'=>$supplierID));?></strong></div>
			<div align="center"><br>
				<form method="post">
					<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
						<tr>
							<td width="50%"><div align="right"><a id="whprint" href="dash-po-payable-set-print.php?sup=<?php echo functions::encode($supplierID)?>&fmDte=<?php echo functions::encode($dateFrom)?>&toDte=<?php echo functions::encode($dateTo)?>" class="btn btn-info"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div></td>
						</tr>
					</table>
					<table align="center" border="0">
						<tr>
							<td>
								<div align="center">Date From: 
									<select name="fromMon" id="fromMon" style="width:80px;">
										<option value="">Month</option>
										<option value="01" <?php if($fromMon=='01')echo 'selected="selected"';?>>Jan</option>
										<option value="02" <?php if($fromMon=='02')echo 'selected="selected"';?>>Feb</option>
										<option value="03" <?php if($fromMon=='03')echo 'selected="selected"';?>>Mar</option>
										<option value="04" <?php if($fromMon=='04')echo 'selected="selected"';?>>Apr</option>
										<option value="05" <?php if($fromMon=='05')echo 'selected="selected"';?>>May</option>
										<option value="06" <?php if($fromMon=='06')echo 'selected="selected"';?>>Jun</option>
										<option value="07" <?php if($fromMon=='07')echo 'selected="selected"';?>>Jul</option>
										<option value="08" <?php if($fromMon=='08')echo 'selected="selected"';?>>Aug</option>
										<option value="09" <?php if($fromMon=='09')echo 'selected="selected"';?>>Sep</option>
										<option value="10" <?php if($fromMon=='10')echo 'selected="selected"';?>>Oct</option>
										<option value="11" <?php if($fromMon=='11')echo 'selected="selected"';?>>Nov</option>
										<option value="12" <?php if($fromMon=='12')echo 'selected="selected"';?>>Dec</option>
									</select>
									<select name="fromDay" id="fromDay" style="width:60px;">
										<option value="">Day</option>
										<?php for($i=1;$i<=31;$i++):?>
										<option value="<?php echo ($i<10) ? '0'.$i : $i;?>" <?php if($fromDay==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
										<?php endfor;?>
									</select>
									<select name="fromYear" id="fromYear" style="width:70px;">
										<option value="">Year</option>
										<?php for($y=(date('Y') + 2);$y>=2012;$y--):?>
										<option value="<?php echo $y;?>" <?php if($fromYear==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
										<?php endfor;?>
									</select>
								</div>
							</td>
							<td style="padding-left: 70px;">
								<div align="center">Date To: 
									<select name="toMon" id="toMon" style="width:80px;">
										<option value="">Month</option>
										<option value="01" <?php if($toMon=='01')echo 'selected="selected"';?>>Jan</option>
										<option value="02" <?php if($toMon=='02')echo 'selected="selected"';?>>Feb</option>
										<option value="03" <?php if($toMon=='03')echo 'selected="selected"';?>>Mar</option>
										<option value="04" <?php if($toMon=='04')echo 'selected="selected"';?>>Apr</option>
										<option value="05" <?php if($toMon=='05')echo 'selected="selected"';?>>May</option>
										<option value="06" <?php if($toMon=='06')echo 'selected="selected"';?>>Jun</option>
										<option value="07" <?php if($toMon=='07')echo 'selected="selected"';?>>Jul</option>
										<option value="08" <?php if($toMon=='08')echo 'selected="selected"';?>>Aug</option>
										<option value="09" <?php if($toMon=='09')echo 'selected="selected"';?>>Sep</option>
										<option value="10" <?php if($toMon=='10')echo 'selected="selected"';?>>Oct</option>
										<option value="11" <?php if($toMon=='11')echo 'selected="selected"';?>>Nov</option>
										<option value="12" <?php if($toMon=='12')echo 'selected="selected"';?>>Dec</option>
									</select>
									<select name="toDay" id="toDay" style="width:60px;">
										<option value="">Day</option>
										<?php for($i=1;$i<=31;$i++):?>
										<option value="<?php echo ($i<10) ? '0'.$i : $i;?>" <?php if($toDay==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
										<?php endfor;?>
									</select>
									<select name="toYear" id="toYear" style="width:70px;">
										<option value="">Year</option>
										<?php for($y=(date('Y') + 2);$y>=2012;$y--):?>
										<option value="<?php echo $y;?>" <?php if($toYear==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
										<?php endfor;?>
									</select>
								</div>
							</td>
							<td style="padding-left: 70px;"><input type="submit" name="btnSelect" id="btnSelect" value="Select" class="btn btn-small btn-primary"></td>
						</tr>
					</table>
				</form><br>
			</div>
			<table width="50%" align="center" border="0" class="table table-bordered table-hover table-striped" style="font-size:12px;" >
				<thead>
					<tr style="background-color:#E4E1E1">
						<th width="70%"><div align="left">Project</div></th>
						<th width="10%"><div align="right">Amount</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$totalAmount=0;$amount=0;$count=0;
				if($dateFrom && $dateTo)
					$qShow = $db->query('SELECT vwpp.proj_id,round(sum(balance),2) as bal FROM view_po_payment vwpp, project p WHERE vwpp.proj_id=p.proj_id AND balance > 0 AND vwpp.supplierID="'.$db->clean($supplierID).'" AND vwpp.po_date BETWEEN "'.$db->clean($dateFrom).'" AND "'.$db->clean($dateTo).'" GROUP BY vwpp.proj_id ORDER BY p.proj_name');
				else
					$qShow = $db->query('SELECT vwpp.proj_id,round(sum(balance),2) as bal FROM view_po_payment vwpp, project p WHERE vwpp.proj_id=p.proj_id AND balance > 0 AND vwpp.supplierID="'.$db->clean($supplierID).'" GROUP BY vwpp.proj_id ORDER BY p.proj_name');
				while($rShow = $db->fetch_array($qShow)):
				$totalAmount += $amount = $rShow['bal'];
				?>
					<tr>
						<td><div align="left"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rShow['proj_id']));?></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($amount)?></strong></div></td>
					</tr>
					<?php endwhile;?>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><div align="right"><strong><?php echo functions::formatMoney($totalAmount)?></strong></div></td>
					</tr>
				</tbody>
			</table>
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
<!-- end: JavaScript-->
</body>
</html>