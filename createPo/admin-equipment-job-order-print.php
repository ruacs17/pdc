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
$jid = (isset($_REQUEST['jid']) && !empty($_REQUEST['jid']) ) ? functions::decode($_REQUEST['jid']) : 0;
$itemDel = (isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ) ? functions::decode($_REQUEST['itemDel']) : 0;
$mon='';$txProj = '';$txPayee = '';$txbMon = '';$txbYear = ''; 
$arr = array();
if( isset($_POST['btnSearchJo']) && !empty($_POST['btnSearchJo']) ){
	$_SESSION['jo_search'] = ( isset($_POST['txSearchJO']) && isset($_POST['txSearchJO']) ) ? $_POST['txSearchJO'] : '';
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnSearch']) ){
	$_SESSION['jo_eqp'] = (isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? $_POST['selEquip'] : 0;
	$_SESSION['jo_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : 0;
	$_SESSION['jo_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : 0;
	unset($_SESSION['jo_search']);
	functions::sendTo(functions::pageName());
	die();
}
if($itemDel){
	$db->delete('equip_job_order_mechanic',array('jo_id'=>$itemDel));
	$db->delete('equip_job_order',array('jo_id'=>$itemDel));
	$_SESSION['notif_warning']='Job Order Removed!';
	functions::sendTo(functions::pageName());
	die();
}

$equip_id = ( isset($_SESSION['jo_eqp']) ) ? $_SESSION['jo_eqp'] : '';
$txbYear = ( isset($_SESSION['jo_yr']) ) ? $_SESSION['jo_yr'] : date('Y');
$txbMon = ( isset($_SESSION['jo_mn']) ) ? $_SESSION['jo_mn'] : date('m');
$txSearchJO = ( isset($_SESSION['jo_search']) ) ? $_SESSION['jo_search'] : '';
$jo_status = ( isset($_SESSION['jo_status']) ) ? $_SESSION['jo_status'] : '';
if($jid){
	$arr=array('jo_id'=>$jid);
}
else{
	if($txbMon && $txbYear)
		$arr = array('LEFT(date_report,7)'=>$txbYear.'-'.$txbMon);
	elseif($txbYear)
		$arr = array('LEFT(date_report,4)'=>$txbYear);
	elseif($txbMon)
		$arr = array('SUBSTRING(date_report,6,2)'=>$txbMon);
	if($equip_id){
		$arr = array_merge($arr,array('jo.equip_id'=>$equip_id));
	}
	if($txSearchJO)
		$arr=array('jo_no'=>$txSearchJO);
	if($jo_status)
		$arr = array_merge($arr,array('jo.jo_status'=>$jo_status));	
}

if(count($arr)){
	$rowdisplay=1000;
	$startrow=0;
}
if(count($arr))
	$qList = $db->select('equip_job_order jo, equipment eqp','*,jo.remarks as rmks',$arr,'AND jo.equip_id=eqp.equip_id ORDER BY date_report DESC');
else
	$qList = $db->query('SELECT *,jo.remarks as rmks FROM equip_job_order jo, equipment eqp WHERE jo.equip_id=eqp.equip_id ORDER BY date_report DESC');
#$qList = $db->select('equip_job_order jo, equipment eqp','*,jo.remarks as rmks',$arr,'AND jo.equip_id=eqp.equip_id ORDER BY date_report DESC');
#echo $db->last_query;
$num_record = $db->getValue('equip_job_order','count(*)',$arr);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Job Order Printing</title>
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
	<style>
	.tdSpace{padding: 0px 3px 0px 3px;}
		@media print {
			body{
				-webkit-print-color-adjust: exact;
			}
		}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table width="98%" border="0" align="center">
	<tr>
		<td>
			<?php
			require_once('../class/print_header.php');
			print_header('Job Order Monitoring');
			?><br>
		</td>
	</tr>
	<tr>
		<td>
			<table id="tblist" border="1" style="font-size:11px;">
				<thead>
					<tr style="background-color:#CCC !important;">
					<th width="5%"><div align="center">Job Order No.</div></th>
					<th width="5%"><div align="center">Date Report</div></th>
					<th width="10%"><div align="center">Equipment Description</div></th>
					<th width="5%"><div align="center">Property Code No.</div></th>
					<th width="5%"><div align="center">Plate No./Serial No.</div></th>
					<th width="5%"><div align="center">Problem and Repair  Description</div></th>
					<th width="5%"><div align="center">Assigned Personnel / Mechanic</div></th>
					<th width="5%"><div align="center">Priority Schedule and Target</div></th>
					<th width="5%"><div align="center">Date Accomplished</div></th>
					<th width="5%"><div align="center">Status</div></th>
					<th width="5%"><div align="center">Q.C. Date</div></th>
					<th width="5%"><div align="center">Date Post and Release</div></th>
					<th width="5%"><div align="center">REMARKS</div></th>
					</tr>
				</thead>
				<tbody>
				<?php while($rList = $db->fetch_array($qList)): $rID = $rList['jo_id']?>
					<tr id="rw<?php echo $rID?>">
						<td class="tdSpace"><div align="left"><?php echo $rList['jo_no'];?></div></td>
						<td><div align="center"><?php echo functions::datearr($rList['date_report']);?></div></td>
						<td class="tdSpace"><div align="left"><?php echo $rList['name'];?></div></td>
						<td class="tdSpace"><div align="left"><?php echo $rList['inventory_id'];?></div></td>
						<td class="tdSpace"><div align="left"><?php echo $rList['serial_no']; echo ($rList['serial_no'] && $rList['engine_no']) ? ' / '.$rList['engine_no'] : $rList['engine_no']; ?></div></td>
						<td><div align="left"><?php echo $rList['jo_desc'];?></div></td>
						<td class="tdSpace">
							<div align="left" style="font-size:10px;">
								<?php
									$qM = $db->select('equip_job_order_mechanic mec, employee emp','*',array('jo_id'=>$rID),'AND mec.emp_id=emp.emp_id');
									while($rM = $db->fetch_array($qM)):
										echo '<div>'.$rM['lname'].', '.$rM['fname'].'</div>';
									endwhile;
								?>
							</div>
						</td>
						<td><div align="center"><?php echo functions::datearr($rList['date_schedule']);?></div></td>
						<td><div align="center"><?php echo functions::datearr($rList['date_accomplished']);?></div></td>
						<td class="tdSpace"><div align="left"><?php echo $rList['jo_status'];?></div></td>
						<td><div align="center"><?php echo functions::datearr($rList['date_qc']);?></div></td>
						<td><div align="center"><?php echo functions::datearr($rList['date_release']);?></div></td>
						<td class="tdSpace"><div align="left"><?php echo $rList['rmks'];?></div></td>
					</tr>
				<?php endwhile;?>
				</tbody>
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
});
</script>
<!-- end: JavaScript-->
</body>
</html>