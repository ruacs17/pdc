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
$mr_emp = (isset($_REQUEST['mremp']) && !empty($_REQUEST['mremp']) ) ? functions::decode($_REQUEST['mremp']) : 0;
$chkUnreturned = (isset($_SESSION['Unreturned'])) ? $_SESSION['Unreturned'] : 1;
$chkReturned = (isset($_SESSION['Returned'])) ? $_SESSION['Returned'] : 1;
$chkTransferred = (isset($_SESSION['Transferred'])) ? $_SESSION['Transferred'] : 1;
$chkTurnedOver = (isset($_SESSION['TurnedOver'])) ? $_SESSION['TurnedOver'] : 1;
$chkDamaged = (isset($_SESSION['Damaged'])) ? $_SESSION['Damaged'] : 1;
$chkDisposed = (isset($_SESSION['Disposed'])) ? $_SESSION['Disposed'] : 1;
$position = position($mr_emp);
$eu = ucwords(strtolower($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$mr_emp))));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>MR Clearance Print</title>
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
	.padParLeft{padding-left:10px;}
	.padAmLeft{padding-left:60px;}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table width="700" border="0" align="center" style="font-size: 14px;">
	<tr>
		<td>
		<?php 
		require_once('../class/print_header.php');
		print_header('MR CLEARANCE CHECKLIST');
		?>
		</td>
	</tr>
</table><br>
<div>Name of Personnel: <u><strong><?php echo $eu?></strong></u></div>
<div>Designation: <u><strong><?php echo $position?></strong></u></div><br>
<table width="100%" border="1" align="center" class="tablea table-bordered">
	<thead>
		<tr style="background-color:#CCC">
			<th width="30%">List of MR'd Units</th>
			<th><div align="center">Qty</div></th>
			<th width="10%"><div align="center">MR Date</div></th>
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
			<td><?php echo $rm['name']; ?></td>
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
		echo '<tr><td colspan="9"><div align="center">---Nothing to Report---</div></td></tr>';
	}?>
	</tbody>
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
	window.location="admin-mr-clearance.php?mremp=<?php echo functions::encode($mr_emp)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>