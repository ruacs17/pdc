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
$to_id = (isset($_REQUEST['to']) && !empty($_REQUEST['to']) ) ? functions::decode($_REQUEST['to']) : 0;

$project_id = $db->getValue('travel_order','proj_id',array('to_id'=>$to_id));
$origin='';$destination='';$est_time_from='';$est_time_to='';$act_time_from='';$act_time_to='';$travel_date=date('Y-m-d');

$to_date = $db->getValue('travel_order','to_date',array('to_id'=>$to_id));
$travel_date = ($to_date) ? $to_date : date('Y-m-d');
if( isset($_REQUEST['itmDlt']) && !empty($_REQUEST['itmDlt']) ){
	$itmDlt = functions::decode($_REQUEST['itmDlt']);
	$db->delete('travel_order_detail',array('tod_id'=>$itmDlt));
	functions::sendTo(functions::pageName().'?to='.functions::encode($to_id));
}

$editTrue=0;
$itmEdt='';
$submitButtonName='btnAdd';$est_time_from='';$est_time_to='';$act_time_from='';$act_time_to='';
$act_time_from_hr='';$act_time_from_mn='';$act_time_to_hr='';$act_time_to_mn='';
$est_time_from_hr='';$est_time_from_mn='';$est_time_to_hr='';$est_time_to_mn='';
if( isset($_REQUEST['itmEdt']) && !empty($_REQUEST['itmEdt']) ){
	$submitButtonName='btnSave';
	$itmEdt = functions::decode($_REQUEST['itmEdt']);
	$editTrue = $db->getValue('travel_order_detail','count(*)',array('tod_id'=>$itmEdt));
	$qvedt = $db->select('travel_order_detail','*',array('tod_id'=>$itmEdt));
	$rvedt = $db->fetch_array($qvedt);
	$origin = $rvedt['origin'];
	$destination = $rvedt['destination'];
	$est_time_from = $rvedt['est_time_from'];
	$est_time_to = $rvedt['est_time_to'];
	$act_time_from = $rvedt['act_time_from'];
	$act_time_to = $rvedt['act_time_to'];
	$travel_date = $rvedt['travel_date'];

	$act_time_from_hr = ( strlen($act_time_from)==8 ) ? $act_time_from[0].$act_time_from[1] : '';
	$act_time_from_mn = ( strlen($act_time_from)==8 ) ? $act_time_from[3].$act_time_from[4] : '';

	$act_time_to_hr = ( strlen($act_time_to)==8 ) ? $act_time_to[0].$act_time_to[1] : '';
	$act_time_to_mn = ( strlen($act_time_to)==8 ) ? $act_time_to[3].$act_time_to[4] : '';

	$est_time_from_hr = ( strlen($est_time_from)==8 ) ? $est_time_from[0].$est_time_from[1] : '';
	$est_time_from_mn = ( strlen($est_time_from)==8 ) ? $est_time_from[3].$est_time_from[4] : '';

	$est_time_to_hr = ( strlen($est_time_to)==8 ) ? $est_time_to[0].$est_time_to[1] : '';
	$est_time_to_mn = ( strlen($est_time_to)==8 ) ? $est_time_to[3].$est_time_to[4] : '';
}
$qOrigin = $db->query('SELECT DISTINCT origin as "itm" FROM travel_order_detail ORDER BY origin');
$namesOrigin='';
while($rOrigin=$db->fetch_array($qOrigin)):
	$string = preg_replace("/'/",'"',$rOrigin['itm']);
	$namesOrigin .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesOrigin .= '"--"';

$qDestination = $db->query('SELECT DISTINCT destination as "itm" FROM travel_order_detail ORDER BY destination');
$namesDestination='';
while($rDestination=$db->fetch_array($qDestination)):
	$string = preg_replace("/'/",'"',$rDestination['itm']);
	$namesDestination .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesDestination .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Travel Order Details</title>
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
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
	<link rel="stylesheet" href="../css/timepicker.css">
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
if( isset($_POST['btnAdd']) ){ 
	$travel_date = ( isset($_POST['txDOT']) && !empty($_POST['txDOT']) ) ? $_POST['txDOT'] : NULL;
	$origin = ( isset($_POST['txOrigin']) && !empty($_POST['txOrigin']) ) ? trim($_POST['txOrigin']) : '';
	$destination = ( isset($_POST['txDestination']) && !empty($_POST['txDestination']) ) ? trim($_POST['txDestination']) : '';
	$est_time_from = ( isset($_POST['selEstStart']) && !empty($_POST['selEstStart']) ) ? trim($_POST['selEstStart']) : NULL;
	$est_time_to = ( isset($_POST['selEstEnd']) && !empty($_POST['selEstEnd']) ) ? trim($_POST['selEstEnd']) : NULL;
	$act_time_from = ( isset($_POST['selActStart']) && !empty($_POST['selActStart']) ) ? trim($_POST['selActStart']) : NULL;
	$act_time_to = ( isset($_POST['selActEnd']) && !empty($_POST['selActEnd']) ) ? trim($_POST['selActEnd']) : NULL; 

	$est_time_from = (isset($_POST['selEstIn']) && !empty($_POST['selEstIn']) ) ? trim($_POST['selEstIn']) : NULL;
	$est_time_to = (isset($_POST['selEstOut']) && !empty($_POST['selEstOut']) ) ? trim($_POST['selEstOut']) : NULL;
	$act_time_from = (isset($_POST['selActIn']) && !empty($_POST['selActIn']) ) ? trim($_POST['selActIn']) : NULL;
	$act_time_to = (isset($_POST['selActOut']) && !empty($_POST['selActOut']) ) ? trim($_POST['selActOut']) : NULL;

	$total_mins = ($act_time_from && $act_time_to && $travel_date ) ? functions::min_diff($act_time_from,$travel_date,$act_time_to,$travel_date) : NULL;
	if( $travel_date && $origin && $destination && $to_id && ($total_mins>0) ){
		$db->insert('travel_order_detail',array('to_id'=>$to_id,'travel_date'=>$travel_date,'origin'=>$origin,'destination'=>$destination,'est_time_from'=>$est_time_from,'est_time_to'=>$est_time_to,'act_time_from'=>$act_time_from,'act_time_to'=>$act_time_to,'mins_travel'=>$total_mins));
		$_SESSION['notif_success']='Travel Added!';
		functions::sendTo(functions::pageName().'?to='.functions::encode($to_id));
		die();
	}
	else
		functions::say('Please fill up the form properly!');
}

if( isset($_POST['btnSave']) ){
	$txitmEdt = ( isset($_POST['txitmEdt']) && !empty($_POST['txitmEdt']) ) ? functions::decode($_POST['txitmEdt']) : '0';
	$travel_date = ( isset($_POST['txDOT']) && !empty($_POST['txDOT']) ) ? $_POST['txDOT'] : NULL;
	$origin = ( isset($_POST['txOrigin']) && !empty($_POST['txOrigin']) ) ? trim($_POST['txOrigin']) : '';
	$destination = ( isset($_POST['txDestination']) && !empty($_POST['txDestination']) ) ? trim($_POST['txDestination']) : '';
	
	$est_time_from = ( isset($_POST['selEstStart']) && !empty($_POST['selEstStart']) ) ? trim($_POST['selEstStart']) : NULL;
	$est_time_to = ( isset($_POST['selEstEnd']) && !empty($_POST['selEstEnd']) ) ? trim($_POST['selEstEnd']) : NULL;
	$act_time_from = ( isset($_POST['selActStart']) && !empty($_POST['selActStart']) ) ? trim($_POST['selActStart']) : NULL;
	$act_time_to = ( isset($_POST['selActEnd']) && !empty($_POST['selActEnd']) ) ? trim($_POST['selActEnd']) : NULL; 


	$est_time_from = (isset($_POST['selEstIn']) && !empty($_POST['selEstIn']) ) ? trim($_POST['selEstIn']) : NULL;
	$est_time_to = (isset($_POST['selEstOut']) && !empty($_POST['selEstOut']) ) ? trim($_POST['selEstOut']) : NULL;
	$act_time_from = (isset($_POST['selActIn']) && !empty($_POST['selActIn']) ) ? trim($_POST['selActIn']) : NULL;
	$act_time_to = (isset($_POST['selActOut']) && !empty($_POST['selActOut']) ) ? trim($_POST['selActOut']) : NULL;

	$total_mins = ($act_time_from && $act_time_to && $travel_date ) ? functions::min_diff($act_time_from,$travel_date,$act_time_to,$travel_date) : NULL;
	if( $travel_date && $origin && $destination && $to_id && $txitmEdt && ($total_mins>0) ){
		$db->update('travel_order_detail',array('to_id'=>$to_id,'travel_date'=>$travel_date,'origin'=>$origin,'destination'=>$destination,'est_time_from'=>$est_time_from,'est_time_to'=>$est_time_to,'act_time_from'=>$act_time_from,'act_time_to'=>$act_time_to,'mins_travel'=>$total_mins),array('tod_id'=>$txitmEdt));
		$_SESSION['notif_success']='Changes saved!';
		functions::sendTo(functions::pageName().'?to='.functions::encode($to_id));
		die();
	}
	else
		functions::say('Please fill up the form properly!');
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Itinerary Details</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="travel_manage_personnel.php?to=<?php echo functions::encode($to_id);?>">Personnel</a></li>
				<li class="active"><a style="opacity:.9" href="travel_manage_detail.php?to=<?php echo functions::encode($to_id);?>">Itinerary</a></li>
				<li><a href="travel_manage.php?to=<?php echo functions::encode($to_id);?>">Travel Order</a></li>
			</ul>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="left">
					<div>Project / Department:
						<strong>
							<?php
							if($project_id){
								$qProj = $db->select('project','*',array('proj_id'=>$project_id));
								while($rProj = $db->fetch_array($qProj)):
									echo strtoupper($rProj['proj_name']);
									echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';
								endwhile;
							}
							?>
						</strong>
					</div>
					<div>Date: <strong><?php echo functions::datearr($to_date);?></strong></div><br><br>
				</div>
				<input type="hidden" name="txitmEdt" id="txitmEdt" value="<?php echo functions::encode($itmEdt);?>">
				<table width="100%" border="1" align="center" class="table table-striped table-bordered" style="font-size: 12px">
					<tr>
						<th width="8%" scope="col"><div align="center">TRAVEL DATE</div></th>
						<th width="18%" scope="col"><div align="center">ORIGIN</div></th>
						<th width="18%" scope="col"><div align="center">DESTINATION</div></th>
						<th colspan="2" scope="col"><div align="center">ESTIMATED TIME </div></th>
						<th colspan="2" scope="col"><div align="center">ACTUAL TIME </div></th>
						<th width="8%" scope="col"><div align="center">&nbsp;</div></th>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td width="11%"><div align="center"><strong>Start</strong></div></td>
						<td width="11%"><div align="center"><strong>End</strong></div></td>
						<td width="11%"><div align="center"><strong>Start</strong></div></td>
						<td width="11%"><div align="center"><strong>End</strong></div></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>
							<div align="center"><input name="txDOT" type="text" id="txDOT" value="<?php echo $travel_date;?>" style="width: 90px;" required></div>
						</td>
						<td><div align="left"><input type="text" name="txOrigin" id="txOrigin" value="<?php echo $origin?>" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesOrigin;?>]'/ required></div></td>
						<td><div align="left"><input type="text" name="txDestination" id="txDestination" value="<?php echo $destination?>" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesDestination;?>]'/ required></div></td>
						<td><div align="left"><div align="left"><input id="selEstIn" type="text" name="selEstIn"></div></div></td>
						<td><div align="left"><div align="left"><input id="selEstOut" type="text" name="selEstOut"></div></div></td>
						<td><div align="left"><div align="left"><input id="selActIn" type="text" name="selActIn"></div></div></td>
						<td><div align="left"><div align="left"><input id="selActOut" type="text" name="selActOut"></div></div></td>
						<td>
							<div align="center">
								<input type="submit" class="btn btn-mini btn-primary" value="Save" name="<?php echo $submitButtonName;?>">
								<?php if($itmEdt){?>
								<a href="?to=<?php echo functions::encode($to_id)?>" class="btn btn-mini btn-warning">Cancel</a>
								<?php }?>
							</div>
						</td>
					</tr>
				</table> <br><br>
				<table width="100%" border="0" align="center" class="table table-striped table-bordered table-hover" style="font-size: 12px">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="8%" scope="col"><div align="center">TRAVEL DATE</div></th>
							<th width="18%" scope="col"><div align="center">ORIGIN</div></th>
							<th width="18%" scope="col"><div align="center">DESTINATION</div></th>
							<th colspan="3" scope="col"><div align="center">ESTIMATED TIME </div></th>
							<th colspan="3" scope="col"><div align="center">ACTUAL TIME </div></th>
							<th width="8%" scope="col"><div align="center">&nbsp;</div></th>
						</tr>
						<tr style="background-color:#CCC;">
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td width="7%"><div align="center"><strong>Start</strong></div></td>
							<td width="7%"><div align="center"><strong>End</strong></div></td>
							<td width="7%"><div align="center"><strong>Duration</strong></div></td>
							<td width="7%"><div align="center"><strong>Start</strong></div></td>
							<td width="7%"><div align="center"><strong>End</strong></div></td>
							<td width="7%"><div align="center"><strong>Duration</strong></div></td>
							<td>&nbsp;</td>
						</tr>
					</thead>
					<tbody>
						<?php
						$qDisp = $db->select('travel_order_detail','*',array('to_id'=>$to_id),'ORDER BY travel_date');
						while($rDisp = $db->fetch_array($qDisp)):
							$rID = $rDisp['tod_id'];
							$est_mins = ($rDisp['est_time_from'] && $rDisp['est_time_to']) ? functions::min_diff($rDisp['est_time_from'],$rDisp['travel_date'],$rDisp['est_time_to'],$rDisp['travel_date']) : 0;
						?>
						<tr>
							<td><div align="center"><?php echo functions::datearr($rDisp['travel_date']);?></div></td>
							<td><div align="left"><?php echo $rDisp['origin'];?></div></td>
							<td><div align="left"><?php echo $rDisp['destination'];?></div></td>
							<td><div align="center"><?php echo functions::MilToTwelve($rDisp['est_time_from']);?></div></td>
							<td><div align="center"><?php echo functions::MilToTwelve($rDisp['est_time_to']);?></div></td>
							<td><div align="center"><?php echo functions::min_to_hour($est_mins);?></div></td>
							<td><div align="center"><?php echo functions::MilToTwelve($rDisp['act_time_from']);?></div></td>
							<td><div align="center"><?php echo functions::MilToTwelve($rDisp['act_time_to']);?></div></td>
							<td><div align="center"><?php echo functions::min_to_hour($rDisp['mins_travel']);?></div></td>
							<td>
								<div align="center">
									<a class="btn btn-mini btn-warning" title="Edit Detail" data-rel="tooltip" href="?itmEdt=<?php echo functions::encode($rID);?>&to=<?php echo functions::encode($to_id);?>"><i class="halflings-icon white pencil"></i></a>
									<a class="btn btn-mini btn-danger" title="Delete Detail" data-rel="tooltip" onClick="return delt()" href="?itmDlt=<?php echo functions::encode($rID);?>&to=<?php echo functions::encode($to_id);?>"><i class="halflings-icon white trash"></i></a>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table><br><br>
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
<script src="../js/timepicker.js"></script>
<script>
$(document).ready(function(){
	$('#txDOT').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2017:<?php echo date('Y')+1 ?>',

	});

});
$('#selEstIn').timepicker({
	time:'<?php echo ($est_time_from) ? $est_time_from : '08:00:00'; ?>'
});
$('#selEstOut').timepicker({
	time:'<?php echo ($est_time_to) ? $est_time_to : '08:00:00'; ?>'
});
$('#selActIn').timepicker({
	time:'<?php echo ($act_time_from) ? $act_time_from : '08:00:00'; ?>'
});
$('#selActOut').timepicker({
	time:'<?php echo ($act_time_to) ? $act_time_to : '08:00:00'; ?>'
});
function delt(){
	if(confirm('Do you want to remove this?'))
		return true;
	else
		return false; 
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