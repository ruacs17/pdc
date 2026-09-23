<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/MoneytoWords.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$mr_emp = (isset($_REQUEST['mremp']) && !empty($_REQUEST['mremp']) ) ? functions::decode($_REQUEST['mremp']) : 0;
if( isset($_POST['btnSubmit']) ){
	$emp = ( isset($_POST['selEU']) && !empty($_POST['selEU']) ) ? $_POST['selEU'] : '';
	if($emp){
		$_SESSION['Unreturned'] = (isset($_POST['Unreturned'])) ? 1 : 0;
		$_SESSION['Returned'] = (isset($_POST['Returned'])) ? 1 : 0;
		$_SESSION['Transferred'] = (isset($_POST['Transferred'])) ? 1 : 0;
		$_SESSION['TurnedOver'] = (isset($_POST['TurnedOver'])) ? 1 : 0;
		$_SESSION['Damaged'] = (isset($_POST['Damaged'])) ? 1 : 0;
		$_SESSION['Disposed'] = (isset($_POST['Disposed'])) ? 1 : 0;
		functions::sendTo(functions::pageName().'?mremp='.$emp);
		die();
	}
	else{
		functions::say('Please select employee!');
	}
}
$chkUnreturned = (isset($_SESSION['Unreturned'])) ? $_SESSION['Unreturned'] : 1;
$chkReturned = (isset($_SESSION['Returned'])) ? $_SESSION['Returned'] : 1;
$chkTransferred = (isset($_SESSION['Transferred'])) ? $_SESSION['Transferred'] : 1;
$chkTurnedOver = (isset($_SESSION['TurnedOver'])) ? $_SESSION['TurnedOver'] : 1;
$chkDamaged = (isset($_SESSION['Damaged'])) ? $_SESSION['Damaged'] : 1;
$chkDisposed = (isset($_SESSION['Disposed'])) ? $_SESSION['Disposed'] : 1;

function position($emp_id){
	global $db;
	$countPos=0;$position='';
	$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
	while($rPos = $db->fetch_array($qPos)):
		if($countPos)
			$position .= ' /<br>';
		$position .= $rPos['pos_name'];
		$countPos++;
	endwhile;
	return $position;
}

$position = position($mr_emp);
$eu = ucwords(strtolower($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$mr_emp))));
$colorUnreturned="#fcb77b";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>MR Clearance</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
	<script src="../js/formatCurrency.js"></script>
	<script src="../js/inputInt.js"></script>
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">
	.padParLeft{padding-left:10px;}
	.padAmLeft{padding-left:60px;}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<div id="spinner"></div>
<form method="post">
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>MR Clearance</h2>
		</div>
		<div class="box-content">
			<div align="left">
				<div align="right">
					<a id="mrPrint" href="admin-mr-clearance-print.php?mremp=<?php echo functions::encode($mr_emp);?>" class="btn btn-info"><i class="halflings-icon white print"></i></a>&nbsp;
				</div>
			</div><br><br>
			<div align="center">
				<form method="post">
					<table border="0">
						<tr>
							<td style="padding-right:30px;">
								<label class="checkbox inline"><input type="checkbox" name="Unreturned" value="1" <?php if($chkUnreturned){echo 'checked';} ?>> Unreturned</label><br>
								<label class="checkbox inline"><input type="checkbox" name="Returned" value="1" <?php if($chkReturned){echo 'checked';} ?>> Returned</label><br>
								<label class="checkbox inline"><input type="checkbox" name="Transferred" value="1" <?php if($chkTransferred){echo 'checked';} ?>> Transferred</label><br>
								<label class="checkbox inline"><input type="checkbox" name="TurnedOver" value="1" <?php if($chkTurnedOver){echo 'checked';} ?>> Turned Over</label><br>
								<label class="checkbox inline"><input type="checkbox" name="Damaged" value="1" <?php if($chkDamaged){echo 'checked';} ?>> Damaged</label><br>
								<label class="checkbox inline"><input type="checkbox" name="Disposed" value="1" <?php if($chkDisposed){echo 'checked';} ?>> Disposed</label><br>
							</td>
							<td>
								<select name="selEU" id="selEU" data-rel="chosen" style="width:600px;">
									<option value="">--select--</option>
									<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo functions::encode($rEU['emp_id'])?>" <?php if($mr_emp==$rEU['emp_id']){echo 'selected="selected"';} ?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>
							</td>
							<td style="padding-left:30px;"><input type="submit" class="btn btn-primary btn-small" name="btnSubmit" value="Submit"></td>
						</tr>
					</table>
				</form>
			</div>
			<div style="padding-top:20px;">Name of Personnel: <u><strong><?php echo $eu?></strong></u></div>
			<div>Designation: <u><strong><?php echo $position?></strong></u></div><br><br>
			<table width="100%" border="0" align="center" class="table table-bordered table-hover" style="font-size: 12px;">
				<thead>
					<tr style="background-color:#CCC">
						<th width="30%">List of MR'd Units</th>
						<th><div align="center">Qty</div></th>
						<th><div align="center">MR Date</div></th>
						<th><div align="center">MR Ref. No.</div></th>
						<th><div align="center">Prop Code</div></th>
						<th width="10%"><div align="center">Serial/Plate No.</div></th>
						<th><div align="center">Acquisition Cost</div></th>
						<th><div align="center">Status</div></th>
						<th width="15%"><div align="center">Remarks</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$countRec=0;$totalAcq=0;
				$qMRd = $db->select('mr m, mr_item mi, equipment eq','*',array('mr_emp'=>$mr_emp),'AND m.mr_id=mi.mr_id AND eq.equip_id=mi.equip_id ORDER BY mr_date');
				#$qMRd = $db->select('mr m, mr_item mi, equipment eq','*',array(),'WHERE m.mr_id=mi.mr_id AND eq.equip_id=mi.equip_id ORDER BY mr_date');
				while($rm = $db->fetch_array($qMRd)):
					$stat = ($rm['mr_status']) ? $rm['mr_status'] : 'Unreturned';
					$return_date = (functions::valid_date($rm['mreturn_date'])) ? $rm['mreturn_date'] : '';
					$return_date_item = (functions::valid_date($rm['return_date'])) ? $rm['return_date'] : '';
					$date_now = date('Y-m-d');
					$mr_date = $rm['mr_date'];
					$late_days = 0;
					if($stat=='Unreturned'){
						if($return_date){
							$late_days = functions::date_diff($return_date,$date_now,1);
						}
					}
					$display=0;
					if($stat=='Unreturned')
						$display = ($chkUnreturned) ? 1 : 0;
					else if($stat=='Returned')
						$display = ($chkReturned) ? 1 : 0;
					else if($stat=='Transferred')
						$display = ($chkTransferred) ? 1 : 0;
					else if($stat=='Turned Over')
						$display = ($chkTurnedOver) ? 1 : 0;
					else if($stat=='Damaged')
						$display = ($chkDamaged) ? 1 : 0;
					else if($stat=='Disposed')
						$display = ($chkDisposed) ? 1 : 0;
					if($display){
						$countRec++;
						$totalAcq+=$rm['price'];
				?>
					<tr>
						<td><?php echo $rm['name']; echo ' '.$late_days.' '.$return_date.' '.$date_now; ?></td>
						<td><div align="center"><?php echo $rm['qty']; ?></div></td>
						<td><div align="center"><?php echo functions::datearr($rm['mr_date']); ?></div></td>
						<td><div align="center"><?php echo $rm['mr_no']; ?></div></td>
						<td><div align="center"><?php echo $rm['inventory_id']; ?></div></td>
						<td><div align="center"><?php echo $rm['serial_no']; echo ($rm['serial_no'] && $rm['plate_no']) ? ' | '.$rm['plate_no'] : $rm['plate_no']; ?></div></td>
						<td><div align="right" style="padding-right:3px;"><?php echo functions::formatMoney($rm['price']); ?></div></td>
						<td><div align="center"><?php echo $stat; echo (functions::valid_date($rm['return_date'])) ? ' ('.functions::datearr($rm['return_date']).')' : ''; ?></div></td>
						<td><div align="center"><?php echo $rm['remark']; ?></div></td>
					</tr>
				<?php }
				endwhile;
				?>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td><div align="right" style="padding-right:3px;"><strong>Total</strong></div></td>
						<td><div align="right" style="padding-right:3px;"><strong><?php echo functions::formatMoney($totalAcq); ?></strong></div></td>
						<td></td>
						<td></td>
					</tr>
				<?php
				if($countRec==0){
				?>
				<?php
					echo '<tr><td colspan="9"><div align="center">---Nothing to Report---</div></td></tr>';
				}?>
				</tbody>
			</table>
        </div>
    </div>
</div>
</form>
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