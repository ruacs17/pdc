<?php require_once('templ_up.php');?>
<?php
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
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

$count=0;$search='';$txSearch='';
if( isset($_REQUEST['srch']) ){
	$txSearch = ( isset($_REQUEST['srch']) ) ? $db->clean(trim($_REQUEST['srch'])) : '';
	$search = "WHERE type LIKE '%".$txSearch."%' OR inventory_id LIKE '%".$txSearch."%' OR brand LIKE '%".$txSearch."%' OR model LIKE '%".$txSearch."%' OR serial_no LIKE '%".$txSearch."%'";
}
if( isset($_POST['btnSearch']) ){
	$txSearch = ( isset($_POST['txSearch']) ) ? $db->clean(trim($_POST['txSearch'])) : '';
	$search = "WHERE type LIKE '%".$txSearch."%' OR inventory_id LIKE '%".$txSearch."%' OR brand LIKE '%".$txSearch."%' OR model LIKE '%".$txSearch."%' OR serial_no LIKE '%".$txSearch."%'";
}
$arrEquipment = array();
$num_record = $db->getValue('equipment','count(*)',array(),$search);
$q_equip = $db->query("SELECT * FROM equipment ". $search.' LIMIT 100');
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
	$arrEquipment[] = array('inventory_id'=>$rEquip['inventory_id'],'equip_id'=>$rEquip['equip_id'],'type'=>$rEquip['type'],'name'=>$rEquip['name'],'equip_desc'=>$rEquip['equip_desc'],'brand'=>$rEquip['brand'],'serial_no'=>$rEquip['serial_no'],'plate_no'=>$rEquip['plate_no'],'model'=>$rEquip['model'],'location'=>$location,'date_acquired'=>$rEquip['date_acquired'],'price'=>$rEquip['price'] * $rEquip['quantity'],'forMaintenance'=>$forMaintenance,'lifeYear'=>$lifeYear,'lifeYearDetail'=>$lifeYearDetail,'status'=>$status,'rate'=>$rEquip['rate']);
endwhile;
if(count($arrEquipment))
	functions::sortMultiArray($arrEquipment,$orderBy,$sort_AscDesc);
?>
<!-- body content: start here-->
<form method="post">
	<div class="row-fluid">
		<div class="box span12">
			<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Property</h2>
			</div><br>
			<div>
				<table width="100%" border="0">
					<tr>
						<td width="50%" style="padding-left: 10px;">
							<table width="180" cellspacing="0" cellpadding="0" border="0" align="left">
								<tr>
									<td width="10" height='30'><div style="background-color:#fcb77b; width:20px;">&nbsp;</div></td>
									<td width="90"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('forMaintenance').'&ascDes='.$ascDes?>">Need Maintenance</a></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div align="center">Search: <input type="text" name="txSearch" id="txSearch" value="<?php echo $txSearch;?>">&nbsp;<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-small btn-primary"></div>
			<div class="box-content">
				<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="10%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('type').'&ascDes='.$ascDes?>">TYPE</a></div></th>
							<th width="8%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('inventory_id').'&ascDes='.$ascDes?>">CODE</a></div></th>
							<th width="15%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('equip_desc').'&ascDes='.$ascDes?>">DESCRIPTION</a></div></th>
							<th width="8%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('brand').'&ascDes='.$ascDes?>">BRAND/MADE</a></div></th>
							<th width="7%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('model').'&ascDes='.$ascDes?>">MODEL</a></div></th>
							<th width="7%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('serial_no').'&ascDes='.$ascDes?>">SERIAL</a></div></th>
							<th width="9%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('location').'&ascDes='.$ascDes?>">LOCATION</a></div></th>
							<th width="14%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('date_acquired').'&ascDes='.$ascDes?>">ACQUISITION<br>DATE</a></div></th>
							<th width="9%" scope="col"><div align="right"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('price').'&ascDes='.$ascDes?>">ACQUISITION<br>PRICE</a></div></th>
							<th width="8%" scope="col"><div align="right"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('rate').'&ascDes='.$ascDes?>">Rate/Hour</a></div></th>
							<th width="9%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('status').'&ascDes='.$ascDes?>">STATUS</a></div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach($arrEquipment as $equip):
						$equip_id = $equip['equip_id'];
						$bgColor=( $equip['forMaintenance'] ) ? 'bgcolor="#fcb77b"' : '';
					?>
						<tr <?php echo $bgColor;?>>
							<td><?php echo $equip['type']?></td>
							<td><?php echo $equip['inventory_id']?></td>
							<td><a id="view<?php echo $equip['equip_id']?>" style="text-decoration:none;cursor:pointer;" class="thickbox" title="View Detail" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-view.php?vdidVw=<?php echo functions::encode($equip['equip_id']);?>','Equipment Detail','1')"><?php echo $equip['equip_desc']; echo ($equip['plate_no']) ? ' ('.$equip['plate_no'].')' : '';?></a></td>
							<td><?php echo $equip['brand']?></td>
							<td><?php echo $equip['model']?></td>
							<td><?php echo $equip['serial_no']?></td>
							<td><?php echo $equip['location']?></td>
							<td>
								<?php
								echo functions::datearr($equip['date_acquired']);
								echo '<br>';
								echo '<i>'.$equip['lifeYearDetail'].'</i>';
								?>
							</td>
							<td><div align="right"><?php echo number_format($equip['price']);?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($equip['rate']);?></div></td>
							<td><?php echo $equip['status']?></td>
						</tr>
					<?php endforeach;?>
					</tbody>
				</table>
				<div align="right"><?php echo $num_record.' item/s'?></div>
			</div>
		</div><!--/span-->
	</div><!--/row-->
</form>
<!-- body content: end here-->
<script>
document.getElementById('txSearch').focus();
</script>
<?php require_once('templ_down.php');?>