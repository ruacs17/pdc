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

$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;

$txSearch=( isset($_REQUEST['txSrch']) && !empty($_REQUEST['txSrch']) ) ? functions::decode($_REQUEST['txSrch']) : 0;
$orderBy = (isset($_REQUEST['orderBy']) && !empty($_REQUEST['orderBy']) ) ? functions::decode($_REQUEST['orderBy']) : 'type';
$ascDes = (isset($_REQUEST['ascDes']) && !empty($_REQUEST['ascDes']) ) ? $_REQUEST['ascDes'] : 'DESC';
$sort_AscDesc="ASC";
if($ascDes==="DESC"){
	$ascDes="ASC";
	$sort_AscDesc="ASC";
}
else if($ascDes==="ASC"){
	$ascDes="DESC";
	$sort_AscDesc="DESC";
}

$count=0;
$search='';
if( $txSearch ){
	$txSearch = $db->clean($txSearch);
	$search = "WHERE type LIKE '%".$txSearch."%' OR inventory_id LIKE '%".$txSearch."%' OR brand LIKE '%".$txSearch."%' OR model LIKE '%".$txSearch."%' OR serial_no LIKE '%".$txSearch."%'";
}

$arrEquipment = array();
$num_record = $db->getValue('equipment','count(*)',array(),$search);
#$q_equip = $db->query("SELECT * FROM equipment ".$search.' LIMIT 10');
$q_equip = isset($_SESSION['equip_list_q']) ? $db->query($_SESSION['equip_list_q']) : $db->query("SELECT * FROM equipment ". $search.' LIMIT 100');
while($rEquip=$db->fetch_array($q_equip)):
	$equip_id = $rEquip['equip_id'];
	$status = $rEquip['status'];
	$lastDateMaintenance = $db->getValue('equip_repair','er_date',array('equip_id'=>$equip_id),' AND repair_type LIKE "%maintenance%" ORDER BY er_date DESC');
	$qHrs = $db->query('SELECT sum(duration) FROM inhouse_equip_leasing_item ieli, inhouse_equip_leasing iel WHERE ieli.iel_id=iel.iel_id AND ieli.equip_id="'.$db->clean($equip_id).'" AND iel.iel_date > "'.$lastDateMaintenance.'" ');
	$hrsFromLastMaintenance = $db->result($qHrs) * 60;
	$mLimit = $db->getValue('equipment','limit_minutes',array('equip_id'=>$equip_id));
	$maintenanceLimit = (is_numeric($mLimit)) ? $mLimit : 0;
	$lifeYear = $db->getValue('equipment','(datediff(curdate(),"'.$rEquip['date_acquired'].'") / 365) as yr',array(),'LIMIT 1');
	$lifeYearDetail = $db->getValue('equipment',"CONCAT(TIMESTAMPDIFF( YEAR, '".$rEquip['date_acquired']."', now() ),'yr/s ', TIMESTAMPDIFF( MONTH, '".$rEquip['date_acquired']."', now() ) % 12,'mon/s') as AGE",array(),'LIMIT 1');
	$forMaintenance = ( ($maintenanceLimit > 0) && ($hrsFromLastMaintenance > 0) && ($hrsFromLastMaintenance >= $maintenanceLimit) ) ? 1 : 0;
	$loc = $db->query('SELECT * FROM `mr_item` mi, mr WHERE mi.mr_id=mr.mr_id AND equip_id="'.$db->clean($equip_id).'" ORDER BY mr.mr_date DESC');
	$rLoc = $db->fetch_array($loc);
	if($rLoc['returned'] == 1){
		$location = !empty($rLoc['assigned_location']) ? $rLoc['assigned_location'] : 'Unspecified' ;
	}
	else
		$location=$rEquip['location']."<br><i>(in possession)</>";
	$arrEquipment[] = array('inventory_id'=>$rEquip['inventory_id'],'equip_id'=>$rEquip['equip_id'],'type'=>$rEquip['type'],'name'=>$rEquip['name'],'equip_desc'=>$rEquip['equip_desc'],'brand'=>$rEquip['brand'],'serial_no'=>$rEquip['serial_no'],'plate_no'=>$rEquip['plate_no'],'model'=>$rEquip['model'],'location'=>$location,'date_acquired'=>$rEquip['date_acquired'],'price'=>$rEquip['price'] * $rEquip['quantity'],'forMaintenance'=>$forMaintenance,'lifeYear'=>$lifeYear,'lifeYearDetail'=>$lifeYearDetail,'status'=>$status);
endwhile;
if(count($arrEquipment))
	functions::sortMultiArray($arrEquipment,$orderBy,$sort_AscDesc);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Property List Print</title>
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
	.padParLeft{padding-left:10px;}
	.padAmLeft{padding-left:60px;}
	</style>
	<script>window.print();</script>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table width="90%" border="0" align="center">
	<thead>
		<tr>
			<td>
				<?php
				require_once('../class/print_header.php');
				print_header('PROPERTY INVENTORY');
				?><br>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th width="10%" scope="col"><div align="left">TYPE</div></th>
							<th width="8%" scope="col"><div align="left">CODE</div></th>
							<th width="15%" scope="col"><div align="left">DESCRIPTION</div></th>
							<th width="8%" scope="col"><div align="left">BRAND/MADE</div></th>
							<th width="7%" scope="col"><div align="left">MODEL</div></th>
							<th width="7%" scope="col"><div align="left">SERIAL</div></th>
							<th width="7%" scope="col"><div align="left">LOCATION</div></th>
							<th width="14%" scope="col"><div align="left">DATE ACQUIRED</div></th>
							<th width="9%" scope="col"><div align="left">ACQUISITION<br>PRICE</div></th>
							<th width="9%" scope="col"><div align="left">STATUS</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach($arrEquipment as $equip):
						$equip_id = $equip['equip_id'];
					?>
						<tr>
							<td><?php echo $equip['type']?></td>
							<td><?php echo $equip['inventory_id']?></td>
							<td><?php echo $equip['equip_desc']; echo ($equip['plate_no']) ? ' ('.$equip['plate_no'].')' : '';?></td>
							<td><?php echo $equip['brand']?></td>
							<td><?php echo $equip['model']?></td>
							<td><?php echo $equip['serial_no']?></td>
							<td><?php echo $equip['location']?></td>
							<td>
								<?php
								echo functions::datearr($equip['date_acquired']);echo '<br>';
								echo '<i>'.$equip['lifeYearDetail'].'</i>';
								?>
							</td>
							<td><?php echo number_format($equip['price']);?></td>
							<td><?php echo $equip['status']?></td>
						</tr>
					<?php endforeach;?>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="../js/jquery.ui.touch-punch.js"></script>
<script src="../js/modernizr.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.cookie.js"></script>
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
<!-- end: JavaScript-->
</body>
</html>