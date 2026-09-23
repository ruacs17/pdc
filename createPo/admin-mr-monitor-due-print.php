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
$issued_id=35;
$issued_position = position($issued_id);
$issued_name = $db->getValue('employee','concat(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$issued_id));


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
		print_header('LIST OF DUE MR');
		?>
		</td>
	</tr>
</table><br>

<?php if($mr_emp){ ?>
<div style="padding-top:20px;">Name of Personnel: <u><strong><?php echo $eu?></strong></u></div>
<div>Designation: <u><strong><?php echo $position?></strong></u></div>
<?php } ?>
<br>
<table width="100%" border="1" align="center">
	<thead>
		<tr class="thd">
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
				<td class="padParLeft"><?php echo $rm['name'];?></td>
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
<div style="padding-top:15px;">&nbsp;</div>
<table width="100%">
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
			<?php
			#$qPI = $db->select('project_incharge pi, users usr','*',array('proj_id'=>$proj_id),'AND pi.user_id=usr.user_id');
			#while($rPI = $db->fetch_array($qPI)):
				#$proj_inchrg = $rPI['fname'].' '.$rPI['lname'];
			?>
				<tr>
					<td align="center">&nbsp;<strong><?php #echo $proj_inchrg; ?></strong></td>
				</tr>
			<?php #endwhile; ?>
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
	window.location="admin-mr-monitor-due.php?mremp=<?php echo functions::encode($mr_emp)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>