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
$arrVal = array('project'=>'1');
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
if($p_id)
	$arrVal = array_merge($arrVal,array('proj_id'=>$p_id));
$proj_date = (isset($_REQUEST['pdt']) && !empty($_REQUEST['pdt']) ) ? functions::decode($_REQUEST['pdt']) : '';
if($proj_date)
	$arrVal = array_merge($arrVal,array('left(date_start,4)'=>$proj_date));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Project Report</title>
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
		<div class="box-header">
			<h2><i class="halflings-icon white th"></i><span class="break"></span>Project Cost Control Report</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="dash-proj-cost-control2.php?pdt=<?php echo functions::encode($proj_date);?>&pid=<?php echo functions::encode($p_id);?>">Cost View</a></li>
				<li class="active"><a href="#">Deductions</a></li>
			</ul>
		</div>
		<div class="box-content" align="center">
			<div>
				<table width="100%" border="0">
					<tr>
						<td>
							<select name="selProj" id="selProj" data-rel="chosen" style="width:700px;font-size:14px;" onChange="projSel(this.value)">
								<option value="">--All Projects--</option>
								<?php 
								$qProj = $db->select('project','*',$arrVal,'ORDER BY proj_name');
								while($rProj = $db->fetch_array($qProj)):
								?>
								<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($p_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
								<?php endwhile;?>
							</select>
						</td>
						<td valign="middle">
							<table width="100%" border="0">
								<tr>
									<td width="68%" valign="middle">Year Started: 
										<select name="proj_yr" id="proj_yr" onChange="selDt(this.value)">
											<option value="">-- All Projects --</option>
											<?php
											$qYr = $db->query('SELECT DISTINCT left(date_start,4) as dt FROM `project` where project=1 AND date_start IS NOT NULL ORDER BY date_start DESC ');
											while($rYr = $db->fetch_array($qYr)):
											?>
											<option value="<?php echo functions::encode($rYr['dt'])?>" <?php if($proj_date==$rYr['dt'])echo 'selected="selected"';?>><?php echo $rYr['dt']?></option>
											<?php endwhile;?>
										</select>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<table class="table table-bordered" border="0" style="font-size:12px; font-family:Tahoma;">
			<?php
			$qProj = $db->select('project','*',$arrVal,'ORDER BY proj_name');
			$num_record = $db->getValue('project','count(*)',$arrVal);
			$totalProj_cost=0; $totalVat=0; $totalEwt=0; $totalMpF=0; $totalGenover=0; $totalProfit=0; $totalRoyalty=0; $totalTechnical=0; $totalConsultancy=0; $totalBudget=0;
			$totalCommitment=0; $totalFinders=0;
			while($rProj = $db->fetch_array($qProj)):
				$amount=0; $po_amount=0; $commitment=0; $consultancy=0; $finders=0; $technical=0; $budget=0;
				$projRoyalty = ($rProj['royalty']) ? ( $rProj['royalty'] / 100 ) : 0;
				$projGenover = ($rProj['genover']) ? ( $rProj['genover'] / 100 ) : 0;
				$projProfit = ($rProj['profit']) ? ( $rProj['profit'] / 100 ) : 0;
				$projMPFee = ($rProj['mpfee']) ? ( $rProj['mpfee'] / 100 ) : 0;
				$dateStart = $rProj['date_start'];
				$dateCompletion = $rProj['date_completion'];
				$dateReviseCompletion = $rProj['date_revise_completion'];
				$dateCompleted = $rProj['date_completed'];
				$totalProj_cost += $proj_cost = $rProj['proj_cost'];
				$totalVat += $totalVat = $vat12 = ($proj_cost / 1.12) * .12;
				$totalEwt += $ewt = ($proj_cost / 1.12) * .02;
				$totalMpF += $mpFee = $proj_cost * $projMPFee;
				$totalGenover += $genover = $proj_cost * $projGenover;
				$totalProfit += $profit = $proj_cost * $projProfit;
				$totalRoyalty += $royalty = $proj_cost * $projRoyalty;
				$budget=0;
				if($projRoyalty==0){
					$totalCommitment += $commitment = $proj_cost * ($rProj['commitment'] / 100);
					$totalConsultancy += $consultancy = $proj_cost * ($rProj['consultancy'] / 100);
					$totalFinders += $finders = $proj_cost * ($rProj['finders'] / 100);
					$totalTechnical += $technical = $proj_cost * ($rProj['technical'] / 100);
					$totalBudget += $budget = $proj_cost - ($vat12 + $ewt + $mpFee + $genover + $profit + $commitment + $consultancy + $finders + $technical);
				}
			?>
				<tr style="background-color:#CCC;">
					<td colspan="12"><strong><?php echo $rProj['proj_name'];?></strong></td>
				</tr>
				<tr style="background-color:#E7E7E7;">
					<td width="5%"><div align="right"><strong>Project Cost</strong></div></td>
					<td width="5%"><div align="right"><strong>VAT<br>(12%)</strong></div></td>
					<td width="5%"><div align="right"><strong>EWT<br>(2%)</strong></div></td>
					<td width="5%"><div align="right"><strong>MPFee<br>(<?php echo $rProj['mpfee'];?>%)</strong></div></td>
					<td width="5%"><div align="right"><strong>GenOver<br>(12%)</strong></div></td>
					<td width="5%"><div align="right"><strong>Profit<br>(10%)</strong></div></td>
					<td width="5%"><div align="right"><strong>Royalty<br>(<?php echo $rProj['royalty'];?>%)</strong></div></td>
					<td width="5%"><div align="right"><strong>Commit<br>(<?php echo $rProj['commitment'];?>%)</strong></div></td>
					<td width="5%"><div align="right"><strong>Consultancy<br>(<?php echo $rProj['consultancy'];?>%)</strong></div></td>
					<td width="5%"><div align="right"><strong>Finders Fee<br>(<?php echo $rProj['finders'];?>%)</strong></div></td>
					<td width="5%"><div align="right"><strong>Technical Fee<br>(<?php echo $rProj['technical'];?>%)</strong></div></td>
					<td width="5%"><div align="right"><strong>Total Budget<br>For Operation</strong></div></td>
				</tr>
				<tr style="background-color:#A0FAE1;">
					<td><div align="right"><?php echo functions::formatMoney($rProj['proj_cost']);?> </i></div></td>
					<td><div align="right"><?php echo functions::formatMoney($vat12);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($ewt);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($mpFee);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($genover);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($profit);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($royalty);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($commitment);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($consultancy);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($finders);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($technical);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($budget);?></div></td>
				</tr>
				<tr>
					<td colspan="12" height="50" valign="center">&nbsp;</td>
				</tr>
			<?php endwhile;?>
				<tr>
					<td colspan="12"><strong>Total of <?php echo $proj_date?></strong></td>
				</tr>
				<tr>
					<td width="5%"><div align="right"><strong>Project Cost</strong></div></td>
					<td width="5%"><div align="right"><strong>VAT<br>(12%)</strong></div></td>
					<td width="5%"><div align="right"><strong>EWT<br>(2%)</strong></div></td>
					<td width="5%"><div align="right"><strong>MPFee<br>(0.80%)</strong></div></td>
					<td width="5%"><div align="right"><strong>GenOver<br></strong></div></td>
					<td width="5%"><div align="right"><strong>Profit<br></strong></div></td>
					<td width="5%"><div align="right"><strong>Royalty</strong></div></td>
					<td width="5%"><div align="right"><strong>Commit</strong></div></td>
					<td width="5%"><div align="right"><strong>Consultancy</strong></div></td>
					<td width="5%"><div align="right"><strong>Finders Fee</strong></div></td>
					<td width="5%"><div align="right"><strong>Technical Fee</strong></div></td>
					<td width="5%"><div align="right"><strong>Total Budget<br>For Operation</strong></div></td>
				</tr>
				<tr>
					<td><div align="right"><?php echo functions::formatMoney($totalProj_cost);?> </i></div></td>
					<td><div align="right"><?php echo functions::formatMoney($totalVat);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($totalEwt);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($totalMpF);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($totalGenover);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($totalProfit);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($totalRoyalty);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($totalCommitment);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($totalConsultancy);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($totalFinders);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($totalTechnical);?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($totalBudget);?></div></td>
				</tr>
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
<script>
	function selDt(PiEwgD){window.location="<?php echo functions::pageName()?>?pdt="+PiEwgD}
	function projSel(PiEwgD){window.location="<?php echo functions::pageName()?>?pid="+PiEwgD+"&pdt=<?php echo functions::encode($proj_date);?>"}
</script>
<!-- end: JavaScript-->
</body>
</html>