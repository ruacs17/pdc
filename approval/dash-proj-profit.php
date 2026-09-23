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
$arr = array('project'=>'1');
$proj_date = (isset($_REQUEST['pdt']) && !empty($_REQUEST['pdt']) ) ? functions::decode($_REQUEST['pdt']) : 0;
$sort = (isset($_REQUEST['sort']) && !empty($_REQUEST['sort']) ) ? functions::decode($_REQUEST['sort']) : 0;

if($proj_date)
	$arr = array('left(date_start,4)'=>$proj_date,'project'=>'1');
	#$arr = array('left(date_contract,4)'=>$proj_date,'project'=>'1');

$orderBy = "proj_name";
$qOrderBy = '';
$ascDesc='';
if($sort){
	if($sort=='proj_name')
		$orderBy = "proj_name"; 
	elseif($sort=='cd')
		$orderBy = "date_contract";
	elseif($sort=='ds')
		$orderBy = "date_start";
	elseif($sort=='date_completion')
		$orderBy = "date_completion";
	elseif($sort=='date_revise')
		$orderBy = "date_revise_completion";
	elseif($sort=='proj_cost')
		$orderBy = "proj_cost";
	elseif($sort=='accomp')
		$orderBy = "daysCompletePercent";
	elseif($sort=='condur')
		$orderBy = "daysDuration";
	elseif($sort=='time_extend')
		$orderBy = "daysDuration";
	elseif($sort=='actual_cost')
		$orderBy = "amount";
	elseif($sort=='wt')
		$orderBy = "weight";
	elseif($sort=='day_elapse')
		$orderBy = "daysElapsed";
	elseif($sort=='day_remain')
		$orderBy = "daysRemaining";    
}
$ascDes = (isset($_REQUEST['ascDes']) && !empty($_REQUEST['ascDes']) ) ? $_REQUEST['ascDes'] : 'DESC';
$sortAscDesc=SORT_ASC;
if($ascDes==="DESC"){
	$ascDes="ASC";
	$sortAscDesc=SORT_ASC;
}
else if($ascDes==="ASC"){
	$ascDes="DESC";
	$sortAscDesc=SORT_DESC; 
}
 
$projects = array();
$qProj = $db->select('project','*',$arr);
$totalCost=0;
$allProjCost = $db->getValue('project','round(sum(proj_cost),2)',$arr);
$allWt=0;
while($rProj = $db->fetch_array($qProj)):
	$totalCost += $rProj['proj_cost'];
	$amount=0;
	$po_amount=0;
	$dateStart = $rProj['date_start'];
	$dateCompletion = $rProj['date_completion'];  
	$dateReviseCompletion = $rProj['date_revise_completion'];
	$dateCompleted = $rProj['date_completed'];
	$non_po=$db->getValue('voucher_detail','sum(amount)',array('proj_id'=>$rProj['proj_id']));
	$daysExtension = functions::date_diff($dateCompletion,$dateReviseCompletion);
	$daysDuration = functions::date_diff($dateStart,$dateCompletion);
	$totalDaysDuration = $daysDuration + $daysExtension;
	$daysElapsed=0;
	$daysRemaining=0;

	if($dateStart <= date('Y-m-d')){
		if( isset($rProj['date_completed']) ){
			$daysElapsed = functions::date_diff($dateStart,$rProj['date_completed']);
			if($rProj['date_revise_completion'])
				$daysRemaining = functions::date_diff($rProj['date_completed'],$dateReviseCompletion);
			else if($rProj['date_completion'])
				$daysRemaining = functions::date_diff($rProj['date_completed'],$rProj['date_completion']);
		}
		else if( !isset($rProj['date_completed']) ){
			$daysElapsed = functions::date_diff($dateStart,date('Y-m-d'));
			if($rProj['date_revise_completion'])
				$daysRemaining = functions::date_diff(date('Y-m-d'),$dateReviseCompletion);
			else if($rProj['date_completion'])
				$daysRemaining = functions::date_diff(date('Y-m-d'),$rProj['date_completion']);
		}
	}

	$qPO_amount = $db->query('SELECT round( sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ),2) FROM po, po_item WHERE po.po_id=po_item.po_id AND po.proj_id="'.$db->clean($rProj['proj_id']).'"');
	$po_amount = $db->result();
	$amount = $non_po + $po_amount;
	$bgColor='';
	$consumedPercent=0;
	$allWt += $wt = ($rProj['proj_cost'] / $allProjCost) * 100;
	$daysCompletePercent = ($totalDaysDuration && $daysElapsed) ? ($daysElapsed / $totalDaysDuration) * 100 : 0;
	if($daysCompletePercent > 100)
		$daysCompletePercent = ($daysRemaining / $totalDaysDuration) * 100;
	if($amount && $rProj['proj_cost'])
		$consumedPercent = ($amount/$rProj['proj_cost']) * 100;

	$projects[]=array('proj_id'=>$rProj['proj_id'],'proj_name'=>trim($rProj['proj_name']),'proj_cost'=>$rProj['proj_cost'],
	'date_contract'=>$rProj['date_contract'],'date_start'=>$rProj['date_start'],'date_completion'=>$rProj['date_completion'],
	'date_revise_completion'=>$rProj['date_revise_completion'],'date_completed'=>$rProj['date_completed'],
	'daysRemaining'=>$daysRemaining,'daysExtension'=>$daysExtension,'daysDuration'=>$daysDuration,
	'daysCompletePercent'=>$daysCompletePercent,'totalDaysDuration'=>$totalDaysDuration,'daysElapsed'=>$daysElapsed,'amount'=>$amount,'weight'=>$wt);
endwhile;
if(count($projects))
	functions::sortMultiArray($projects,$orderBy,$ascDes);
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
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Report</h2>
		</div>
		<div class="box-content" align="center">
			<div>
				<table width="35%" border="0">
					<tr>
						<td width="50%" align="right"><strong>Year Started:</strong>&nbsp;</td>
						<td width="50%" align="left" style="padding: 12px 0px 4px 0px">
							<select name="proj_yr" id="proj_yr" onChange="selDt(this.value)">
								<option value="">-- All Projects --</option>
								<?php
								#$qYr = $db->query('SELECT DISTINCT left(date_contract,4) as dt FROM `project` where date_contract IS NOT NULL ORDER BY date_contract DESC ');
								$qYr = $db->query('SELECT DISTINCT left(date_start,4) as dt FROM `project` where project=1 AND date_start IS NOT NULL ORDER BY date_start DESC ');
								while($rYr = $db->fetch_array($qYr)):
								?>
								<option value="<?php echo functions::encode($rYr['dt'])?>" <?php if($proj_date==$rYr['dt'])echo 'selected="selected"';?>><?php echo $rYr['dt']?></option>
								<?php endwhile;?>
							</select>
						</td>
					</tr>
				</table><br><br>
			</div>
			<table width="80%" align="center" border="0" class="table table-bordered table-striped table-hover" style="font-size: 12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="25%"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('proj_name').'&ascDes='.$ascDes?>">Project Name</a></th>
						<th width="7%"><div align="center"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('cd').'&ascDes='.$ascDes?>">Contract Date</a></div></th>
						<th width="7%"><div align="center"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('ds').'&ascDes='.$ascDes?>">Date Started</a></div></th>
						<th width="5%"><div align="right"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('condur').'&ascDes='.$ascDes?>">Contract Duration<br>(Days)</a></div></th>
						<th width="7%"><div align="center"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('date_completion').'&ascDes='.$ascDes?>">Target Date Completion</a></div></th>
						<th width="5%"><div align="center"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('time_extend').'&ascDes='.$ascDes?>">Approved Time Extension</a></div></th>
						<th width="7%"><div align="center"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('date_revise').'&ascDes='.$ascDes?>">Revised Target Date of Completion</a></div></th>
						<th width="7%"><div align="center"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('actual_cost').'&ascDes='.$ascDes?>">Actual Cost</a></div></th>
						<th width="7%"><div align="center"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('proj_cost').'&ascDes='.$ascDes?>">Contract Amount</a></div></th>
						<th width="5%"><div align="center"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('wt').'&ascDes='.$ascDes?>">Wt(%)</a></div></th>
						<th width="5%"><div align="center"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('day_elapse').'&ascDes='.$ascDes?>">Elapsed<br>(Days)</a></div></th>
						<th width="5%"><div align="center"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('day_remain').'&ascDes='.$ascDes?>">Remaining<br>(Days)</a></div></th>
						<th width="5%"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('accomp').'&ascDes='.$ascDes?>">Accomp<br>(Straight Line Diagram)</a></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($projects as $proj):?>
					<tr>
						<td><?php echo $proj['proj_name'];?></td>
						<td><div align="center"><?php echo functions::datearr($proj['date_contract']);?> </i></div></td>
						<td><div align="center"><?php echo functions::datearr($proj['date_start']);?> </i></div></td>
						<td><div align="right"><?php echo $proj['daysDuration'];?></div></td>
						<td><div align="center"><?php echo functions::datearr($proj['date_completion']);?></div></td>
						<td><div align="right"><?php echo $proj['daysExtension'];?></div></td>
						<td><div align="center"><?php echo functions::datearr($proj['date_revise_completion']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($proj['amount']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($proj['proj_cost']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($proj['weight']);?>%</div></td>
						<td><div align="right"><?php echo $proj['daysElapsed'];?></div></td>
						<td><div align="right"><?php echo $proj['daysRemaining'];?></div></td>
						<td><div align="right"><?php echo ($proj['daysCompletePercent'] >= 0) ? functions::formatMoney($proj['daysCompletePercent']) : '<strong>'.functions::formatMoney($proj['daysCompletePercent']).'</strong>';?>%</div></td>
					</tr>
					<?php endforeach;?>
					<tr>
						<td height="30" colspan="8"><div align="right"><strong>Total</strong></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($totalCost);?></strong></div></td>
						<td><div align="right"><?php echo $allWt;?>%</div></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
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
<script>function selDt(PiEwgD){window.location="<?php echo functions::pageName()?>?pdt="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>