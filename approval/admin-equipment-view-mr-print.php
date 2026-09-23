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
$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;

$mon='';
$txProj = '';
$txPayee = '';
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';

$txProj = ( isset($_SESSION['mr_proj']) ) ? $_SESSION['mr_proj'] : '';
$txbYear = ( isset($_SESSION['mr_yr']) ) ? $_SESSION['mr_yr'] : date('Y');
$txbMon = ( isset($_SESSION['mr_mn']) ) ? $_SESSION['mr_mn'] : date('m');
$txbDay = ( isset($_SESSION['mr_day']) ) ? $_SESSION['mr_day'] : date('d');

$txbYearTo = ( isset($_SESSION['mr_yrTo']) ) ? $_SESSION['mr_yrTo'] : date('Y');
$txbMonTo = ( isset($_SESSION['mr_mnTo']) ) ? $_SESSION['mr_mnTo'] : date('m');
$txbDayTo = ( isset($_SESSION['mr_dayTo']) ) ? $_SESSION['mr_dayTo'] : date('d');

$where = ''; 
if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
	$where .=' AND (mr_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
	$where .=' AND ( LEFT(mr_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(mr_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
	$where .=' AND (SUBSTRING(mr_date,6,2)>="'.$txbMon.'" AND SUBSTRING(mr_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
	$where .=' AND (LEFT(mr_date,4) >= "'.$txbYear.'" AND LEFT(mr_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
	$where .=' AND mr_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj){
	$where .=' AND proj_id="'.$db->clean($txProj).'"';
}
$qShow = $db->query('SELECT * FROM mr, mr_item mi WHERE mr.mr_id=mi.mr_id AND equip_id="'.$db->clean($equip_id).'" '.$where.' ORDER BY mr.mr_date DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>MR Print</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/printerfoot.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">
	.padParLeft{padding-left:50px;}
	.padAmLeft{padding-left:60px;}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table width="90%" border="0" align="center">
	<thead>
		<tr>
			<td>
			<?php 
			require_once('../class/print_header.php');
			print_header('MR');
			?>
			</td>
		</tr>
		<tr>
			<td valign='bottom'>&nbsp;</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td height="500" valign="top">
				<table width="100%" border="0">
					<tr>
						<td width="50%">
							<div align="left">&nbsp;Equipment: <strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong></div>
						</td>
					</tr>
				</table><br>
				<div class="box-content">
					<table class="table table-bordered" style="font-size:12px">
						<thead>
							<tr>
								<th width="7%" scope="col"><div align="center">MR No.</div></th>
								<th width="15%" scope="col"><div align="center">User</div></th>
								<th width="32%" scope="col"><div align="center">Project</div></th>
								<th width="15%" scope="col"><div align="center">Location</div></th>
								<th width="8%" scope="col"><div align="center">Release Date</div></th>
								<th width="8%" scope="col"><div align="center">Return Date</div></th>
								<th width="5%" scope="col"><div align="center">Quantity</div></th>
								<th width="15%" scope="col"><div align="center">Remark</div></th>
							</tr>
						</thead>
						<tbody>
							<?php while($rShow = $db->fetch_array($qShow)):?>
							<tr>
								<td><div align="center"><?php echo $rShow['mr_id']?></div></td>
								<td><div align="center"><?php echo $db->getValue('employee','concat(lname,", ",fname," ",mname)',array('emp_id'=>$rShow['mr_emp']))?></div></td>
								<td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rShow['proj_id']))?></td>
								<td><div align="center"><?php echo $rShow['assigned_location']?></div></td>
								<td><div align="center"><?php echo functions::datearr($rShow['released_date'])?></div></td>
								<td><div align="center"><?php echo functions::datearr($rShow['return_date'])?></div></td>
								<td><div align="center"><?php echo $rShow['qty'];?></div></td>
								<td><div align="center"><?php echo $rShow['remark'];?></div></td>
							</tr>
							<?php endwhile; ?>
						</tbody>
					</table>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<footer>
	<div align="right">18PMD.FRM036.00-10/18</div>
</footer>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="admin-equipment-view-mr.php?vdidVw=<?php echo functions::encode($equip_id);?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>