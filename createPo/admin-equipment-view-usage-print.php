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

$txProj = ( isset($_SESSION['eu_proj']) ) ? $_SESSION['eu_proj'] : '';
$txbYear = ( isset($_SESSION['eu_yr']) ) ? $_SESSION['eu_yr'] : date('Y');
$txbMon = ( isset($_SESSION['eu_mn']) ) ? $_SESSION['eu_mn'] : date('m');
$txbDay = ( isset($_SESSION['eu_day']) ) ? $_SESSION['eu_day'] : date('d');

$txbYearTo = ( isset($_SESSION['eu_yrTo']) ) ? $_SESSION['eu_yrTo'] : date('Y');
$txbMonTo = ( isset($_SESSION['eu_mnTo']) ) ? $_SESSION['eu_mnTo'] : date('m');
$txbDayTo = ( isset($_SESSION['eu_dayTo']) ) ? $_SESSION['eu_dayTo'] : date('d');

$where = ''; 
if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
	$where .=' AND (iel_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
	$where .=' AND ( LEFT(iel_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(iel_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
	$where .=' AND (SUBSTRING(iel_date,6,2)>="'.$txbMon.'" AND SUBSTRING(iel_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
	$where .=' AND (LEFT(iel_date,4) >= "'.$txbYear.'" AND LEFT(iel_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
	$where .=' AND iel_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj){
	if( $db->getValue('inhouse_equip_leasing','count(*)',array('proj_id'=>$txProj)) )
		$where .=' AND iel.proj_id="'.$db->clean($txProj).'"';
	else
		$where .=' AND iel.payee="'.$db->clean($txProj).'"';
}
$qShow = $db->query('SELECT * FROM inhouse_equip_leasing_item ieli, inhouse_equip_leasing iel WHERE ieli.iel_id=iel.iel_id AND ieli.equip_id="'.$db->clean($equip_id).'" '.$where.' ORDER BY iel_date DESC');?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Property Usage Print</title>
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
				print_header('PROPERTY USAGE');
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
						<td width="50%"><div align="left">&nbsp;Property: <strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong></div></td>
					</tr>
				</table><br>
				<div class="box-content">
					<table class="table table-bordered" border="0" style="font-size:12px">
						<thead>
							<tr>
								<th width="10%" scope="col"><div align="center">Date</div></th>
								<th width="53%" scope="col"><div align="center">Project</div></th>
								<th width="8%" scope="col"><div align="center">Duration (Hrs)</div></th>
								<th width="7%" scope="col"><div align="center">Cost</div></th>
								<th width="7%" scope="col"><div align="center">Amount</div></th>
								<th width="5%" scope="col"><div align="center">Discount</div></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$totalAmount=0; $totalDuration=0;
						while($rShow = $db->fetch_array($qShow)):
							$discount = ($rShow['discount']) ? $rShow['discount'] / 100 : 0;
							$less = ($rShow['duration'] * $rShow['cost']) * $discount;
							$totalQuotation = ($rShow['duration'] * $rShow['cost']) - $less;
							$outside=$rShow['payee'];
							$totalAmount += $totalQuotation;
							$totalDuration += $rShow['duration'];
						?>
							<tr>
								<td><div align="center"><?php echo functions::datearr($rShow['iel_date'])?></div></td>
								<td><?php echo ($rShow['payee']) ? $rShow['payee'].' <i>(Outsider)</i>' : $db->getValue('project','proj_name',array('proj_id'=>$rShow['proj_id']))?></td>
								<td><div align="center"><?php echo $rShow['duration']?></div></td>
								<td><div align="center"><?php echo functions::formatMoney($rShow['cost'])?></div></td>
								<td><div align="center"><?php echo functions::formatMoney($totalQuotation)?></div></td>
								<td><div align="center"><?php echo ($rShow['discount']) ? $rShow['discount'].'%' : "";?></div></td>
							</tr>
							<?php endwhile;?>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td><div align="center"><strong><?php echo functions::min_to_hour($totalDuration * 60)?></strong></div></td>
								<td>&nbsp;</td>
								<td><div align="center"><strong><?php echo functions::formatMoney($totalAmount)?></strong></div></td>
								<td>&nbsp;</td>
							</tr>
						</tbody>
					</table>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<footer>
	<div align="right">18PMD.FRM038.00-10/18</div>
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
	window.location="admin-equipment-view-usage.php?vdidVw=<?php echo functions::encode($equip_id);?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>