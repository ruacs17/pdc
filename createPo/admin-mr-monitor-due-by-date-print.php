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
$txbYear = ( isset($_SESSION['mr_monintor_yr']) ) ? $_SESSION['mr_monintor_yr'] : date('Y');
$txbMon = ( isset($_SESSION['mr_monintor_mn']) ) ? $_SESSION['mr_monintor_mn'] : date('m');
$txbDay = ( isset($_SESSION['mr_monintor_day']) ) ? $_SESSION['mr_monintor_day'] : date('d');
$due_date = $txbYear.'-'.$txbMon.'-'.$txbDay;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>MR Due Print</title>
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
		print_header('LIST OF MR DUE on '.date('F d, Y',strtotime($due_date)));
		?>
		</td>
	</tr>
</table><br><br>
<table width="100%" border="1" align="center">
	<thead>
		<tr class="thd">
			<th width="25%">List of MR'd Units</th>
			<th><div align="center">Qty</div></th>
			<th width="7%"><div align="center">MR Date</div></th>
			<th><div align="center">MR Ref. No.</div></th>
			<th><div align="center">Prop Code</div></th>
			<th width="10%"><div align="center">Serial/Plate No.</div></th>
			<th><div align="center">Accountable Person</div></th>
			<th width="7%"><div align="center">Due Date</div></th>
			<th><div align="center">Status</div></th>
			<th width="10%"><div align="center">Remarks</div></th>
		</tr>
	</thead>
	<tbody>
	<?php
	$countRec=0;$totalAcq=0;
	$qMRd = $db->prepareQ('SELECT * FROM mr m, mr_item mi, equipment eq, employee emp WHERE m.mr_emp=emp.emp_id AND m.mr_id=mi.mr_id AND eq.equip_id=mi.equip_id AND mreturn_date<=? ORDER BY mreturn_date DESC',array($due_date));
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
				<td class="padParLeft"><?php echo $rm['name'];?></td>
				<td><div align="center"><?php echo $rm['qty']; ?></div></td>
				<td><div align="center"><?php echo functions::datearr($rm['mr_date']); ?></div></td>
				<td><div align="center"><?php echo $rm['mr_no']; ?></div></td>
				<td><div align="center"><?php echo $rm['inventory_id']; ?></div></td>
				<td><div align="center"><?php echo $rm['serial_no']; echo ($rm['serial_no'] && $rm['plate_no']) ? ' | '.$rm['plate_no'] : $rm['plate_no']; ?></div></td>
				<td><div align="center"><?php echo $rm['lname'].', '.$rm['fname']; ?></div></td>
				<td><div align="center"><?php echo functions::datearr($rm['mreturn_date']); ?></div></td>
				<td><div align="center"><?php echo $stat; echo (functions::valid_date($rm['return_date'])) ? '<br><i>('.functions::datearr($rm['return_date']).')</i>' : ''; ?></div></td>
				<td><div align="center"><?php echo $rm['remark']; ?></div></td>
			</tr>
		<?php
			}
		endwhile;
		if($countRec==0){
			echo '<tr><td colspan="10"><div align="center">---Nothing to Report---</div></td></tr>';
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
	window.location="admin-mr-monitor-due-by-date.php?";
}
</script>
<!-- end: JavaScript-->
</body>
</html>