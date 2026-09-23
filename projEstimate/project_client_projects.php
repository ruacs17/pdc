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
$p_id=0;
$pc_id = (isset($_REQUEST['pcID']) && !empty($_REQUEST['pcID']) ) ? functions::decode($_REQUEST['pcID']) : 0;
$del_project = (isset($_REQUEST['pidDel']) && !empty($_REQUEST['pidDel']) ) ? functions::decode($_REQUEST['pidDel']) : 0;
$arrVal=array('project'=>'1','pc_id'=>$pc_id);
$proj_id='';
if( isset($_POST['btnMember']) && $pc_id ){
	$proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? functions::decode($_POST['selProj']) : '';
	if($proj_id){

		if( $db->getValue('project_client','pc_id',array('proj_id'=>$proj_id)) ){
			functions::say('Project Already been assigned a Client!');
		}
		else{
			$db->update('project',array('pc_id'=>$pc_id),array('proj_id'=>$proj_id));
			functions::say('Project Added!');
			functions::sendTo(functions::pageName().'?pcID='.functions::encode($pc_id));
		}

	}

}
if($del_project && $pc_id){
	$db->update('project',array('pc_id'=>NULL),array('pc_id'=>$pc_id,'proj_id'=>$del_project));
}
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
		<div class="box-content">
			<form method="post">
			<table>
				<tr>
					<td>
						<div style="padding-top:5px;">
						<select name="selProj" id="selProj" data-rel="chosen" style="width:1200px;font-size:12px;" onChange="projSel(this.value)">
							<option value="">--Select Project--</option>
							<?php 
							$qProj = $db->select('project','*',array('project'=>'1'),'AND pc_id IS NULL ORDER BY proj_name');
							while($rProj = $db->fetch_array($qProj)):?>
							<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($proj_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
							<?php endwhile;?>
						</select>
						</div>
					</td>
					<td><div>&nbsp;<input type="submit" name="btnMember" value="Add" class="btn btn-info btn-small"></div></td>
				</tr>
			</table>
			</form>
			<table class="table table-bordered" style="font-size:12px; font-family:Tahoma;">
				<thead>
					<tr>
						<th width="30%">Project Name</th>
						<th width="7%"><div align="center">Date Started</div></th>
						<th width="5%"><div align="center">Contract Duration<br>(Days)</div></th>
						<th width="7%"><div align="center">Target Date Completion</div></th>
						<th width="5%"><div align="center">Approved Time Extension</div></th>
						<th width="7%"><div align="center">Revised Target Date of Completion</div></th>
						<th width="9%"><div align="center">Contract Amount</div></th>
						<th width="4%"><div align="center">Days Elapsed</div></th>
						<th width="4%"><div align="center">Days Remain</div></th>
						<th width="6%"><div align="center">Detail</div></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$qProj = $db->select('project','*',$arrVal,'ORDER BY date_start DESC');
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
						<td><div align="center"><?php echo functions::datearr($rProj['date_start']);?> </i></div></td>
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
								<a href="?pidDel=<?php echo functions::encode($rProj['proj_id']);?>&pcID=<?php echo functions::encode($pc_id) ?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Project" data-rel="tooltip"><i class="halflings-icon white trash"></i></a>
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
<script type="text/javascript">
function delt(){
if(confirm('Do you want to remove this Project from Client List?'))
	return true;
else
	return false; 
}
</script>
<!-- end: JavaScript-->
</body>
</html>