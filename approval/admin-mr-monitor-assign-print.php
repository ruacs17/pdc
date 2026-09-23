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
$proj_id = (isset($_REQUEST['projid']) && !empty($_REQUEST['projid']) ) ? functions::decode($_REQUEST['projid']) : 0;

$chkUnreturned = (isset($_SESSION['mrp_Unreturned'])) ? $_SESSION['mrp_Unreturned'] : 1;
$chkReturned = (isset($_SESSION['mrp_Returned'])) ? $_SESSION['mrp_Returned'] : 1;
$chkTransferred = (isset($_SESSION['mrp_Transferred'])) ? $_SESSION['mrp_Transferred'] : 1;
$chkTurnedOver = (isset($_SESSION['mrp_TurnedOver'])) ? $_SESSION['mrp_TurnedOver'] : 1;
$chkDamaged = (isset($_SESSION['mrp_Damaged'])) ? $_SESSION['mrp_Damaged'] : 1;
$chkDisposed = (isset($_SESSION['mrp_Disposed'])) ? $_SESSION['mrp_Disposed'] : 1;

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
$issued_id=35;
$issued_position = position($issued_id);
$issued_name = $db->getValue('employee','concat(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$issued_id));


$prjQ = $db->select('project','*',array('proj_id'=>$proj_id));
$rPrj = $db->fetch_array($prjQ);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>MR Assigned Per Project Print</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">
	body{font-size:10px;}
	.padParLeft{padding-left:5px;}
	.padAmLeft{padding-left:60px;}
	.thd{background-color:#CCC !important;}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table width="700" border="0" align="center" style="font-size: 14px;">
	<tr>
		<td>
		<?php 
		require_once('../class/print_header.php');
		print_header('LIST OF PROPERTY ASSIGNED');
		?>
		</td>
	</tr>
</table><br>

<?php if($rPrj['proj_name']){ ?>
<div>Name of Project: <u><strong><?php echo $rPrj['proj_name'];?></strong></u></div>
<div>Location: <u><strong><?php echo ($rPrj['proj_location']) ? $rPrj['proj_location'] : '----';?></strong></u></div>
<?php } ?>
<br>
<table width="100%" border="1" align="center">
	<thead>
		<tr class="thd">
			<th width="30%">List of MR'd Units</th>
			<th><div align="center">Qty</div></th>
			<th><div align="center">MR Date</div></th>
			<th><div align="center">MR Ref. No.</div></th>
			<th><div align="center">Prop Code</div></th>
			<th width="10%"><div align="center">Serial/Plate No.</div></th>
			<th><div align="center">Accountable Person</div></th>
			<th><div align="center">Status</div></th>
			<th width="15%"><div align="center">Remarks</div></th>
		</tr>
	</thead>
	<tbody>
	<?php
	$countRec=0;$totalAcq=0;
	$qMRd = $db->select('mr m, mr_item mi, equipment eq, employee emp','*',array('proj_id'=>$proj_id),'AND m.mr_emp=emp.emp_id AND m.mr_id=mi.mr_id AND eq.equip_id=mi.equip_id ORDER BY mr_date');
	while($rm = $db->fetch_array($qMRd)):
		$stat = ($rm['mr_status']) ? $rm['mr_status'] : 'Unreturned';
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
	?>
		<tr>
			<td class="padParLeft"><?php echo $rm['name'];?></td>
			<td><div align="center"><?php echo $rm['qty']; ?></div></td>
			<td><div align="center"><?php echo functions::datearr($rm['mr_date']); ?></div></td>
			<td><div align="center"><?php echo $rm['mr_no']; ?></div></td>
			<td><div align="center"><?php echo $rm['inventory_id']; ?></div></td>
			<td><div align="center"><?php echo $rm['serial_no']; echo ($rm['serial_no'] && $rm['plate_no']) ? ' | '.$rm['plate_no'] : $rm['plate_no']; ?></div></td>
			<td><div align="center"><?php echo $rm['lname'].', '.$rm['fname']; ?></div></td>
			<td><div align="center"><?php echo $stat; echo (functions::valid_date($rm['return_date'])) ? '<br><i>('.functions::datearr($rm['return_date']).')</i>' : ''; ?></div></td>
			<td><div align="center"><?php echo $rm['remark']; ?></div></td>
		</tr>
	<?php
		}
	endwhile;
	if($countRec==0){
		echo '<tr><td colspan="9"><div align="center">---Nothing to Report---</div></td></tr>';
	}?>
	</tbody>
</table>
<div style="padding-top:15px;">&nbsp;</div>
<table width="100%" border="0">
	<tr>
		<td width="10%">&nbsp;</td>
		<td width="25%">
			<table>
				<tr>
					<td style="padding-bottom:15px;">Issued by:</td>
				</tr>
				<tr>
					<td align="center"><strong><?php echo $issued_name; ?></strong></td>
				</tr>
				<tr>
					<td align="center"><?php echo $issued_position; ?></td>
				</tr>
			</table>
		</td>
		<td width="10%"></td>
		<td width="25%">
			<table>
				<tr>
					<td style="padding-bottom:15px;">Received by:</td>
				</tr>
				<tr>
					<td align="center"><strong><?php echo $db->getValue('mr_project_incharge mrpi, employee emp','concat(fname," ",left(mname,1),". ",lname)',array('proj_id'=>$proj_id),'AND mrpi.emp_id=emp.emp_id'); ?></strong></td>
				</tr>
				<tr>
					<td align="center">Project in Charge / Site Engineer</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="admin-mr-monitor-assign.php?projid=<?php echo functions::encode($proj_id)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>