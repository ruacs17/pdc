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
$pc_id = (isset($_REQUEST['cid']) && !empty($_REQUEST['cid']) ) ? functions::decode($_REQUEST['cid']) : 0;
if($pc_id)
	$arrVal = array('project'=>'1','pc_id'=>$pc_id);
else
	$arrVal = array('project'=>'1');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Client Project</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Client Projects</h2>
		</div>
		<div align="right" style="padding-top:3px;">
			<table cellspacing="4" cellpadding="6" border='0' align="right">
				<tr>
					<td width="25"><div style="background-color:#86f59b; width:20px;">&nbsp;</div></td>
					<td width="59"> DONE</td>
				</tr>
			</table>
		</div>
		<div align="left"><br>&nbsp;&nbsp;
			<select name="selClient" id="selClient" data-rel="chosen" style="width:710px;font-size:12px;" onChange="clientSel(this.value)">
				<option value="">--All Clients--</option>
				<?php
				$qpc = $db->query('SELECT * FROM project_client ORDER BY pc_name');
				while($rpc = $db->fetch_array($qpc)):?>
				<option value="<?php echo functions::encode($rpc['pc_id'])?>" <?php if($pc_id==$rpc['pc_id']){echo 'selected="selected"';} ?> ><?php echo $rpc['pc_name']?></option>
				<?php endwhile;?>
			</select>
		</div>
		<div class="box-content">
			<table class="table table-bordered table-hover table-striped" style="font-size:12px; font-family:Tahoma;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="30%">Project Name</th>
						<th width="7%"><div align="center">Date Started</div></th>
						<th width="5%"><div align="center">Contract Duration<br>(Days)</div></th>
						<th width="7%"><div align="center">Target Date Completion</div></th>
						<th width="5%"><div align="center">Approved Time Extension</div></th>
						<th width="9%"><div align="center">Revised Target Date of Completion</div></th>
						<th width="9%"><div align="center">Contract Amount</div></th>
						<th width="4%"><div align="center">Days Elapsed</div></th>
						<th width="4%"><div align="center">Days Remain</div></th>
						<th width="5%"><div align="center">Detail</div></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$qProj = $db->select('project','*',$arrVal,'AND pc_id IN (SELECT pc_id FROM project_client) ORDER BY date_start DESC');
					$num_record = $db->getValue('project','count(*)',$arrVal);
					while($rProj = $db->fetch_array($qProj)):
						$amount=0;
						$po_amount=0;
						$dateStart = $rProj['date_start'];
						$dateCompletion = $rProj['date_completion'];
						$dateReviseCompletion = $rProj['date_revise_completion'];
						$dateCompleted = $rProj['date_completed'];
						$non_po=$db->getValue('voucher_detail','sum(amount)',array('proj_id'=>$rProj['proj_id']));
						$amount += $non_po;
						$daysExtension = functions::date_diff($dateCompletion,$dateReviseCompletion);
						$daysDuration = functions::date_diff($dateStart,$dateCompletion);

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

						$bgColor='';
						$consumedPercent=0;
						if( isset($rProj['date_completed']) ){
							$bgColor = 'bgcolor="#86f59b"';
						}
					?>
					<tr <?php echo $bgColor;?>>
						<td><?php echo $rProj['proj_name'];?></td>
						<td><div align="center"><?php echo functions::datearr($rProj['date_start']);?></i></div></td>
						<td><div align="center"><?php echo $daysDuration;?></div></td>
						<td><div align="center"><?php echo functions::datearr($dateCompletion);?></div></td>
						<td><div align="center"><?php echo $daysExtension;?></div></td>
						<td><div align="center"><?php echo functions::datearr($dateReviseCompletion);?></div></td>
						<td><div align="center"><?php echo functions::formatMoney($rProj['proj_cost']);?></div></td>
						<td><div align="center"><?php echo $daysElapsed;?></div></td>
						<td><div align="center"><?php echo $daysRemaining;?></div></td>
						<td>
							<div align="center">
								<a id="detail<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Project Detail" data-rel="tooltip" onclick="showThis(this.id,'project_view.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Detail')"><i class="halflings-icon white zoom-in"></i></a>
							</div>
						</td>
					</tr>
					<?php endwhile;?>
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
<script>
function clientSel(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo functions::pageName()?>?cid="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
}
</script>
<!-- end: JavaScript-->
</body>
</html>