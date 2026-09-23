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
$mode = (isset($_REQUEST['mode']) && !empty($_REQUEST['mode']) ) ? $_REQUEST['mode'] : '';
$arrMonth = array();
for($i=1; $i<=12; $i++):
	$mn = ($i<=9) ? '2020-0'.$i.'-01' : '2020-'.$i.'-01';
	#echo '<br>'.date('M',strtotime($mn));
	#echo '<br>'.date('F',strtotime($mn));
	$arrMonth[date('m',strtotime($mn))]=date('F',strtotime($mn));
endfor;
$countIncome=0;
$total_inside=0;
$inside_monthly=array();
$outside_monthly=array();
$inside_monthly_total=array();
$outside_monthly_total=array();
$overall_yearly_total=array();
$yrStart=2017;
for($yr=(date('Y')+1); $yr>=$yrStart;$yr-- ):
	$overall_yearly_total[$yr]=0;
	foreach($arrMonth as $month_num => $month_name):
		$mnyr = $yr.'-'.$month_num;
		$inside_monthly[$yr][$month_num]=0;
		$outside_monthly[$yr][$month_num]=0;
		$inside_monthly_total[$month_num][$yr]=0;
		$outside_monthly_total[$month_num][$yr]=0;
		
		$inside_cost = 0;
		$outside_cost = 0;
		$arr = array('left(iel_date,7)'=>$mnyr);
		if($mode=='paid')
			$arr = array_merge($arr,array('is_paid'=>1));
		else if($mode=='unpaid')
			$arr = array_merge($arr,array('is_paid'=>2));

		$q = $db->select('inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli','*',$arr,'AND iel.iel_id=ieli.iel_id');
		#echo $db->last_query;
		while($r = $db->fetch_array($q)):
			$amount = ($r['duration'] * $r['cost']);
			$discount = ($r['discount']) ? $r['discount']/100 : 0;
			if( !empty($r['proj_id']) ){
				$inside_cost = ($discount) ? $amount - ($amount*$discount) : $amount;
				$inside_monthly[$yr][$month_num]+=$inside_cost;
				$inside_monthly_total[$month_num][$yr]+=$inside_cost;
				$overall_yearly_total[$yr]+=$inside_cost;
			}
			else if( !empty($r['payee']) ){
				$outside_cost = ($discount) ? $amount - ($amount*$discount) : $amount;
				$outside_monthly[$yr][$month_num]+=$outside_cost;
				$outside_monthly_total[$month_num][$yr]+=$outside_cost;
				$overall_yearly_total[$yr]+=$outside_cost;
			}
			#echo $overall_yearly_total[$yr].'<br>';
		endwhile;
	endforeach;
endfor;
// echo '<pre>';
// print_r($overall_yearly_total);
// echo '</pre>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Property Leasing Income Summary Report</title>
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
	<style type="text/css">
	.padright{padding-right:3px;}
	</style>
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span>Property Leasing Income Report</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="right" style="padding:10px 0px 10px 0px;">
					<select name="selMode" onChange="window.location='?mode='+this.value">
						<option value="all" <?php if($mode=='all'){echo 'selected="selected"';} ?>>Paid/Unpaid</option>
						<option value="paid" <?php if($mode=='paid'){echo 'selected="selected"';} ?>>Paid</option>
						<option value="unpaid" <?php if($mode=='unpaid'){echo 'selected="selected"';} ?>>Unpaid</option>
					</select>
				</div>
				<table class="table-hover" width="100%" border="1" style="font-size:12px">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="5%" height="25">Year</th>
							<th width="5%"></th>
							<th colspan="<?php echo count($arrMonth)+2 ?>"><div align="center">Month</div></th>
						</tr>
						<tr style="background-color:#CCC;">
							<th height="25"></th>
							<th></th>
							<?php foreach($arrMonth as $month_num => $month_name): ?>
							<th width="5%"><div align="center"><?php echo $month_name ?></div></th>
							<?php endforeach; ?>
							<th width="5%"><div align="center">Total</th>
							<th width="5%"><div align="center">Overall</div></th>
						</tr>
					</thead>
					<tbody>
						<?php for($yr=(date('Y')+1); $yr>=$yrStart;$yr-- ): ?>
						<tr>
							<th rowspan="2" style="background-color:#CCC;"><?php echo $yr; ?></th>
							<th height="30" style="background-color:#CCC;">Inside</th>
							<?php foreach($arrMonth as $month_num => $month_name): ?>
							<td>
								<div align="right" class="padright">
									<a id="vw<?php echo $countIncome++?>" class="thickbox" style="cursor:pointer;" title="View Details" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-leasing-income-summary-detail.php?yr=<?php echo functions::encode($yr);?>&mn=<?php echo functions::encode($month_num);?>&mode=inside&paidunpaid=<?php echo $mode?>','Leasing Income Details','1')">
									<?php echo ( isset($inside_monthly[$yr][$month_num]) && $inside_monthly[$yr][$month_num] ) ? functions::formatMoney($inside_monthly[$yr][$month_num]) : '';?>
									</a>
								</div>
							</td>
							<?php endforeach; ?>
							<td><div align="right" class="padright"><strong><?php echo functions::formatMoney(array_sum($inside_monthly[$yr])); ?></strong></div></td>
							<td rowspan="2" valign="middle"><div align="right" class="padright"><strong><?php echo functions::formatMoney(array_sum($inside_monthly[$yr])+array_sum($outside_monthly[$yr])); ?></strong></div></td>
						</tr>
						<tr>
							<th height="30" style="background-color:#CCC;">Outside</th>
							<?php $total_outside=0; foreach($arrMonth as $month_num => $month_name): ?>
							<td>
								<div align="right" class="padright">
									<a id="vw<?php echo $countIncome++?>" class="thickbox" style="cursor:pointer;" title="View Details" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-leasing-income-summary-detail.php?yr=<?php echo functions::encode($yr);?>&mn=<?php echo functions::encode($month_num);?>&mode=outside&paidunpaid=<?php echo $mode?>','Leasing Income Details','1')">
									<?php echo ( isset($outside_monthly[$yr][$month_num]) && $outside_monthly[$yr][$month_num] ) ? functions::formatMoney($outside_monthly[$yr][$month_num]) : '';?>
									</a>
								</div>
							</td>
							<?php endforeach; ?>
							<td><div align="right" class="padright"><strong><?php echo functions::formatMoney(array_sum($outside_monthly[$yr])); ?></strong></div></td>
						</tr>
						<?php endfor; ?>
						<tr>
							<th height="30"></th>
							<th></th>
							<?php foreach($arrMonth as $month_num => $month_name): ?>
							<th width="5%"><div align="right" class="padright"><?php echo functions::formatMoney(array_sum($outside_monthly_total[$month_num]) + array_sum($inside_monthly_total[$month_num]) );?></div></th>
							<?php endforeach; ?>
							<td colspan="2" valign="middle"><div align="right" class="padright"><strong><?php echo functions::formatMoney( array_sum($overall_yearly_total)); ?></strong></div></td>
						</tr>
					</tbody>
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