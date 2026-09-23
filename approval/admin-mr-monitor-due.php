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
if( isset($_REQUEST['mremp']) ){
	$_SESSION['mr_monintor_emp']='';
	if( !empty($_REQUEST['mremp']) )
		$_SESSION['mr_monintor_emp']=functions::decode($_REQUEST['mremp']);
}
$mr_emp = (isset($_SESSION['mr_monintor_emp']) && !empty($_SESSION['mr_monintor_emp']) ) ? $_SESSION['mr_monintor_emp'] : 0;
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
$date_now = date('Y-m-d');
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
	<title>MR Due</title>
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
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<div id="spinner"></div>
<form method="post">
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span>MR Due By Personnel</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="admin-mr-monitor-due-by-date.php">Due By Date</a></li>
				<li class="active"><a href="admin-mr-monitor-due.php" style="opacity:.9">Due By Personnel</a></li>
				<li><a href="admin-mr-monitor-assign.php">MR Per Project</a></li>
			</ul>
		</div>
		<div class="box-content">
			<div align="left">
				<div align="right">
					<a id="mrPrint" href="admin-mr-monitor-due-print.php?mremp=<?php echo functions::encode($mr_emp);?>" class="btn btn-info"><i class="halflings-icon white print"></i></a>&nbsp;
				</div>
			</div><br><br>
			<div align="center">
				<form method="post">
					<table border="0">
						<tr>
							<td>
								<select name="selEU" id="selEU" data-rel="chosen" onChange="window.location='?mremp='+this.value" style="width:700px;" style="width:600px;">
									<option value="">--Select All--</option>
									<?php #$qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
									$qEU = $db->query('SELECT * FROM employee WHERE emp_id IN (SELECT DISTINCT mr_emp FROM mr WHERE mreturn_date<="'.$date_now.'")');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo functions::encode($rEU['emp_id'])?>" <?php if($mr_emp==$rEU['emp_id']){echo 'selected="selected"';} ?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<?php if($mr_emp){ ?>
			<div style="padding-top:20px;">Name of Personnel: <u><strong><?php echo $eu?></strong></u></div>
			<div>Designation: <u><strong><?php echo $position?></strong></u></div>
			<?php } ?>
			<br><br>
			<div class="table-wrapper">
			<table width="100%" border="0" align="center" class="table table-bordered table-hover" style="font-size: 12px;">
				<thead>
					<tr style="background-color:#CCC">
						<th width="25%">List of MR'd Units</th>
						<th><div align="center">Qty</div></th>
						<th width="7%"><div align="center">MR Date</div></th>
						<th><div align="center">MR Ref. No.</div></th>
						<th><div align="center">Prop Code</div></th>
						<th width="10%"><div align="center">Serial/Plate No.</div></th>
						<?php if(empty($mr_emp)){ ?><th><div align="center">Accountable Person</div></th><?php }?>
						<th width="7%"><div align="center">Due Date</div></th>
						<th><div align="center">Status</div></th>
						<th width="10%"><div align="center">Remarks</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$countRec=0;$totalAcq=0;
				if($mr_emp)
					$qMRd = $db->select('mr m, mr_item mi, equipment eq, employee emp','*',array('mr_emp'=>$mr_emp),'AND m.mr_emp=emp.emp_id AND m.mr_id=mi.mr_id AND eq.equip_id=mi.equip_id AND mreturn_date<="'.$date_now.'" ORDER BY mreturn_date');
				else
					$qMRd = $db->prepareQ('SELECT * FROM mr m, mr_item mi, equipment eq, employee emp WHERE m.mr_emp=emp.emp_id AND m.mr_id=mi.mr_id AND eq.equip_id=mi.equip_id AND mreturn_date<=? ORDER BY mreturn_date',array($date_now));
				while($rm = $db->fetch_array($qMRd)):
					
					$stat = ($rm['mr_status']) ? $rm['mr_status'] : 'Unreturned';
					$return_date = (functions::valid_date($rm['mreturn_date'])) ? $rm['mreturn_date'] : '';
					$return_date_item = (functions::valid_date($rm['return_date'])) ? $rm['return_date'] : '';
					$mr_date = $rm['mr_date'];
					$late_days = 0;
					if($stat=='Unreturned'){
						$countRec++;
				?>
					<tr>
						<td><?php echo $rm['name'];?></td>
						<td><div align="center"><?php echo $rm['qty']; ?></div></td>
						<td><div align="center"><?php echo functions::datearr($rm['mr_date']); ?></div></td>
						<td><div align="center"><?php echo $rm['mr_no']; ?></div></td>
						<td><div align="center"><?php echo $rm['inventory_id']; ?></div></td>
						<td><div align="center"><?php echo $rm['serial_no']; echo ($rm['serial_no'] && $rm['plate_no']) ? ' | '.$rm['plate_no'] : $rm['plate_no']; ?></div></td>
						<?php if(empty($mr_emp)){ ?><td><div align="center"><?php echo $rm['lname'].', '.$rm['fname']; ?></div></td><?php }?>
						<td><div align="center"><?php echo functions::datearr($rm['mreturn_date']); ?></div></td>
						<td><div align="center"><?php echo $stat; echo (functions::valid_date($rm['return_date'])) ? '<br><i>('.functions::datearr($rm['return_date']).')</i>' : ''; ?></div></td>
						<td><div align="center"><?php echo $rm['remark']; ?></div></td>
					</tr>
				<?php
					}
				endwhile;
				if($countRec==0){
					$colspan=($mr_emp) ? 9 : 10;
				?>
				<?php
					echo '<tr><td colspan="'.$colspan.'"><div align="center">---Nothing to Report---</div></td></tr>';
				}?>
				</tbody>
			</table>
			</div>
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