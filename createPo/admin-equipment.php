<?php require_once('templ_up.php');?>
<?php
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$count=0;
$search='';
$txSearch='';
$selStat='';
$selYrAcquired='';$selMonthAcquired='';

if( isset($_REQUEST['vdidDel']) ){
	$delItem = functions::decode($_REQUEST['vdidDel']);
	if( $db->getValue('inhouse_equip_leasing_item','count(*)',array('equip_id'=>$delItem))==0 && $db->getValue('mr_item','count(*)',array('equip_id'=>$delItem))==0  ){

		//Remove Document uploaded
		$qFiles = $db->select('equip_docs','*',array('equip_id'=>$delItem));
		while($rF = $db->fetch_array($qFiles)):
			if($filename=$rF['eds_name']){
				if( file_exists('../img_equip/'.$filename) ){
					unlink('../img_equip/'.$filename);
				}
			}
		endwhile;
		$db->delete('equip_docs',array('equip_id'=>$delItem));
		$db->delete('equip_doc',array('equip_id'=>$delItem));
		//End: Remove Document uploaded

		$db->delete('equipment',array('equip_id'=>$delItem));
		$_SESSION['notif_warning']='Property Removed!';
	}
	else
		$_SESSION['notif_warning']='Property Unable to Removed!';
	functions::sendTo(functions::pageName());
	die();
}
$orderBy = (isset($_REQUEST['orderBy']) && !empty($_REQUEST['orderBy']) ) ? functions::decode($_REQUEST['orderBy']) : 'type';
$ascDes = (isset($_REQUEST['ascDes']) && !empty($_REQUEST['ascDes']) ) ? $_REQUEST['ascDes'] : 'DESC';

$sort_AscDesc="ASC";

if($ascDes==="DESC"){
	$ascDes="ASC";
	$sort_AscDesc="ASC";
	$_SESSION['eqp_AscDesc']='ASC';
}
else if($ascDes==="ASC"){
	$ascDes="DESC";
	$sort_AscDesc="DESC";
	$_SESSION['eqp_AscDesc']='DESC';
}

$order_by='';
if($orderBy=='type')
	$order_by = 'type';
elseif($orderBy=='inventory_id')
	$order_by = 'inventory_id';
elseif($orderBy=='equip_desc')
	$order_by = 'equip_desc';
elseif($orderBy=='brand')
	$order_by = 'brand';
elseif($orderBy=='model')
	$order_by = 'model';
elseif($orderBy=='serial_no')
	$order_by = 'serial_no';
elseif($orderBy=='location')
	$order_by = 'location';
elseif($orderBy=='date_acquired')
	$order_by = 'date_acquired';
elseif($orderBy=='price')
	$order_by = 'price';
elseif($orderBy=='status')
	$order_by = 'status';
// elseif($orderBy=='forMaintenance')
// 	$order_by = 'forMaintenance';
if($order_by)
	$_SESSION['eqp_OrderBy']=$order_by;   
//echo $order_by;
if( isset($_REQUEST['srch']) ){
	$txSearch = ( isset($_REQUEST['srch']) ) ? $db->clean(trim($_REQUEST['srch'])) : '';
	$_SESSION['eqp_searchVal'] = $txSearch;
	#$search = "WHERE type LIKE '%".$txSearch."%' OR inventory_id LIKE '%".$txSearch."%' OR brand LIKE '%".$txSearch."%' OR model LIKE '%".$txSearch."%' OR serial_no LIKE '%".$txSearch."%'";
}
if( isset($_POST['btnSearch']) ){
	$selCat = ( isset($_POST['selCat']) && !empty($_POST['selCat']) ) ? trim($_POST['selCat']) : '';
	$_SESSION['eqp_Category'] = $selCat;

	$selYrAcquired = ( isset($_POST['selYrAcquired']) ) ? $db->clean(trim($_POST['selYrAcquired'])) : '';
	$_SESSION['eqp_YrAcquired'] = $selYrAcquired;

	$selMonthAcquired = ( isset($_POST['selMonthAcquired']) ) ? $db->clean(trim($_POST['selMonthAcquired'])) : '';
	$_SESSION['eqp_MonthAcquired'] = $selMonthAcquired;

	$selStat = ( isset($_POST['selStat']) ) ? $db->clean(trim($_POST['selStat'])) : '';
	$_SESSION['eqp_stat'] = ($selStat) ? $selStat : '';

	$txSearch = ( isset($_POST['txSearch']) ) ? $db->clean(trim($_POST['txSearch'])) : '';
	$_SESSION['eqp_searchVal'] = $txSearch;
}

$txSearch = (isset($_SESSION['eqp_searchVal']) && !empty($_SESSION['eqp_searchVal']) ) ? $_SESSION['eqp_searchVal'] : '';
$selCat = ( isset($_SESSION['eqp_Category']) && !empty($_SESSION['eqp_Category']) ) ? $_SESSION['eqp_Category'] : '';
$selStat = ( isset($_SESSION['eqp_stat']) && !empty($_SESSION['eqp_stat']) ) ? $_SESSION['eqp_stat'] : '';
$selYrAcquired = ( isset($_SESSION['eqp_YrAcquired']) && !empty($_SESSION['eqp_YrAcquired']) ) ? $_SESSION['eqp_YrAcquired'] : '';
$selMonthAcquired = ( isset($_SESSION['eqp_MonthAcquired']) && !empty($_SESSION['eqp_MonthAcquired']) ) ? $_SESSION['eqp_MonthAcquired'] : '';
$sort = ( isset($_SESSION['eqp_AscDesc']) && !empty($_SESSION['eqp_AscDesc']) ) ? $_SESSION['eqp_AscDesc'] : '';
$order = ( isset($_SESSION['eqp_OrderBy']) && !empty($_SESSION['eqp_OrderBy']) ) ? $_SESSION['eqp_OrderBy'] : '';
$qSearch='';
$qSearchArr = array();
if($txSearch){
	$qSearch = "WHERE (type LIKE '%".$txSearch."%' OR inventory_id LIKE '%".$txSearch."%' OR brand LIKE '%".$txSearch."%' OR model LIKE '%".$txSearch."%' OR serial_no LIKE '%".$txSearch."%') ";    
}
if($selStat){
	$qSearch .= ($qSearch) ? ' AND status="'.$selStat.'"' : 'WHERE status="'.$selStat.'"';
	$qSearchArr = array('status'=>$selStat);
}
if($selCat){
	$qSearch .= ($qSearch) ? ' AND category="'.$selCat.'"' : 'WHERE status="'.$selStat.'"';
	$qSearchArr = array_merge($qSearchArr,array('category'=>$selCat));
}
if($selYrAcquired && $selMonthAcquired){
	$qSearchArr = array_merge($qSearchArr,array('LEFT(date_acquired,7)'=>$selYrAcquired.'-'.$selMonthAcquired));
	$qSearch .= ($qSearch || $selStat) ? ' AND LEFT(date_acquired,7)="'.$selYrAcquired.'-'.$selMonthAcquired.'"' : ' WHERE LEFT(date_acquired,7)="'.$selYrAcquired.'-'.$selMonthAcquired.'"';
}
else if($selYrAcquired){
	$qSearchArr = array_merge($qSearchArr,array('LEFT(date_acquired,4)'=>$selYrAcquired));
	$qSearch .= ($qSearch || $selStat) ? ' AND LEFT(date_acquired,4)="'.$selYrAcquired.'"' : ' WHERE LEFT(date_acquired,4)="'.$selYrAcquired.'"';
}
else if($selMonthAcquired){
	$qSearchArr = array_merge($qSearchArr,array('SUBSTRING(date_acquired,6,2)'=>$selMonthAcquired));
	$qSearch .= ($qSearch || $selStat) ? ' AND SUBSTRING(date_acquired,6,2)="'.$selMonthAcquired.'"' : ' WHERE SUBSTRING(date_acquired,6,2)="'.$selMonthAcquired.'"';
}

$qSearch .= ($order) ? ' ORDER BY '.$order : '';
$qSearch .= ($sort) ? ' '.$sort : '';
$qOrder = ($order) ? ' ORDER BY '.$order : '';
$qOrder .= ($qOrder && $sort) ? ' '.$sort : '';
$num_record=0;

$arrRenewal=array();
$qEquip = $db->query('SELECT eq.* FROM equipment eq, equip_registration er WHERE eq.equip_id=er.equip_id AND eq.reg_renew IS NOT NULL AND eq.status NOT IN ("Trade In","Sold","Inactive","Unserviceable") AND "'.date('Y-m-d').'" BETWEEN er.date_due_start AND er.date_validity AND er.stat="For Renewal"');
while($rEquip = $db->fetch_array($qEquip)):
$arrRenewal[$rEquip['equip_id']]=$rEquip['equip_id'];
endwhile;
if(count($qSearchArr)){
	$q_equip = $db->select('equipment','*',$qSearchArr,"AND (type LIKE '%".$txSearch."%' OR inventory_id LIKE '%".$txSearch."%' OR brand LIKE '%".$txSearch."%' OR model LIKE '%".$txSearch."%' OR serial_no LIKE '%".$txSearch."%') ".$qOrder.' LIMIT 100');
	$_SESSION['equip_list_q']=$db->last_query;
	$num_record = $db->getValue('equipment','count(*)',$qSearchArr,"AND (type LIKE '%".$txSearch."%' OR inventory_id LIKE '%".$txSearch."%' OR brand LIKE '%".$txSearch."%' OR model LIKE '%".$txSearch."%' OR serial_no LIKE '%".$txSearch."%') ");
}
else{
	$q_equip = $db->select('equipment','*',array(),"WHERE (type LIKE '%".$txSearch."%' OR inventory_id LIKE '%".$txSearch."%' OR brand LIKE '%".$txSearch."%' OR model LIKE '%".$txSearch."%' OR serial_no LIKE '%".$txSearch."%') ".$qOrder.' LIMIT 100');
	$_SESSION['equip_list_q']=$db->last_query;
	$num_record = $db->getValue('equipment','count(*)',array(),"WHERE (type LIKE '%".$txSearch."%' OR inventory_id LIKE '%".$txSearch."%' OR brand LIKE '%".$txSearch."%' OR model LIKE '%".$txSearch."%' OR serial_no LIKE '%".$txSearch."%') ");
}
$arrEquipment = array();
$color_expired='#D66C5B';
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
	$forRenewal = ( isset($arrRenewal[$equip_id]) ) ? 1 : 0;
	$loc = $db->query('SELECT * FROM `mr_item` mi, mr WHERE mi.mr_id=mr.mr_id AND equip_id="'.$db->clean($equip_id).'" ORDER BY mr.mr_date DESC');
	$rLoc = $db->fetch_array($loc);
	if($db->num_rows($loc)==0){
		$location=$rEquip['location']."<br><i>(in possession)</i>";
	}
	else if($rLoc['mr_status']=='Returned' || $rLoc['mr_status']=='Turned Over' || $rLoc['mr_status']=='Transferred' || $rLoc['mr_status']=='Damaged'){
		$location=$rEquip['location']."<br><i>(in possession)</i>";
	}
	elseif($rLoc['mr_status']=='Unreturned'){
		$location = !empty($rLoc['assigned_location']) ? $rLoc['assigned_location'] : 'Unspecified' ;
		if($rLoc['mr_emp']){
			$location .= '<br><br>'.strtoupper($db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$rLoc['mr_emp'])));
		}
	}
	else{
		$location = !empty($rLoc['assigned_location']) ? $rLoc['assigned_location'] : 'Unspecified' ;
		if($rLoc['mr_emp']){
			$location .= '<br><br>'.strtoupper($db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$rLoc['mr_emp'])));
		}
	}

	$arrEquipment[] = array('inventory_id'=>$rEquip['inventory_id'],'equip_id'=>$rEquip['equip_id'],'type'=>$rEquip['type'],'name'=>$rEquip['name'],'equip_desc'=>$rEquip['equip_desc'],'brand'=>$rEquip['brand'],'serial_no'=>$rEquip['serial_no'],'plate_no'=>$rEquip['plate_no'],'model'=>$rEquip['model'],'location'=>$location,'date_acquired'=>$rEquip['date_acquired'],'price'=>$rEquip['price'] * $rEquip['quantity'],'forMaintenance'=>$forMaintenance,'forRenewal'=>$forRenewal,'lifeYear'=>$lifeYear,'lifeYearDetail'=>$lifeYearDetail,'status'=>$status);
endwhile;
?>
<style>
.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
</style>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Property</h2>
		</div><br>
		<div>
			<table width="100%" border="0">
				<tr>
					<td width="50%" style="padding-left: 10px;">
						<table width="480" cellspacing="0" cellpadding="0" border="0" align="left">
							<tr>
								<td width="10" height='30'><div style="background-color:#fcb77b; width:20px;">&nbsp;</div></td>
								<td width="120"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('forMaintenance').'&ascDes='.$ascDes?>">Need Maintenance</a></td>
								<td width="10"><div style="background-color:<?php echo $color_expired?>; width:20px;">&nbsp;</div></td>
								<td width="190">&nbsp;<a id="adcd" href="#" class="thickbox" onclick="showThis(this.id,'admin-equipment-registration-pending.php?','LTO Registration')">LTO Registration Renewal (<?php echo count($arrRenewal); ?>)</a></td>
							</tr>
						</table>
					</td>
					<td>
						<div align="right">
							<a id="joborder" href="#" class="btn btn-info btn-small btn-setting thickbox" onclick="showThis(this.id,'admin-equipment-job-order.php?','Job Order Details','1')">Job Order</a>&nbsp;&nbsp;&nbsp;</a>
							<a id="adc" href="#" style="display:none;" class="btn btn-info btn-small btn-setting thickbox" onclick="showThis(this.id,'admin-equipment-add.php?','Add Property')">Add New Property</a>&nbsp;&nbsp;&nbsp;
						</div>
					</td>
					<td width="7%"><div align="center"><a id="mrPrint" href="" class="btn btn-info btn-small thickbox" title="Print Property List" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-list-print.php?txSrch=<?php echo functions::encode($txSearch)?>','Print Property','1')"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div></td>
				</tr>
			</table>
		</div>
		<form method="post"><br>
			<div align="center">
				<table border="0" width="50%">
					<tr>
						<td><div align="right">Search &nbsp;</div></td>
						<td style="padding: 10px 0px 0px 0px;"><input type="text" name="txSearch" id="txSearch" value="<?php echo $txSearch;?>"></td>
					</tr>
					<tr>
						<td><div align="right">Condition &nbsp;</div></td>
						<td style="padding: 10px 0px 0px 0px;">
							<select name="selStat" id="selStat">
								<option value="">--Select--</option>
								<?php
								$qStat = $db->select('equipment','DISTINCT status',array(),'WHERE status<>"" ORDER BY status');
								while($rStat = $db->fetch_array($qStat)):
								?>
								<option value="<?php echo $rStat['status']?>" <?php if($selStat==$rStat['status']){echo 'selected="selected"';}?>><?php echo $rStat['status']?></option>
								<?php endwhile;?>
							</select>
						</td>
					</tr>
					<tr>
						<td><div align="right">Date Acquire &nbsp;</div></td>
						<td style="padding: 10px 0px 0px 0px;">
							<select name="selMonthAcquired" id="selMonthAcquired" style="width:100px;">
								<option value="">--Month--</option>
								<option value="01" <?php if($selMonthAcquired=='01')echo 'selected="selected"';?>>Jan</option>
								<option value="02" <?php if($selMonthAcquired=='02')echo 'selected="selected"';?>>Feb</option>
								<option value="03" <?php if($selMonthAcquired=='03')echo 'selected="selected"';?>>Mar</option>
								<option value="04" <?php if($selMonthAcquired=='04')echo 'selected="selected"';?>>Apr</option>
								<option value="05" <?php if($selMonthAcquired=='05')echo 'selected="selected"';?>>May</option>
								<option value="06" <?php if($selMonthAcquired=='06')echo 'selected="selected"';?>>Jun</option>
								<option value="07" <?php if($selMonthAcquired=='07')echo 'selected="selected"';?>>Jul</option>
								<option value="08" <?php if($selMonthAcquired=='08')echo 'selected="selected"';?>>Aug</option>
								<option value="09" <?php if($selMonthAcquired=='09')echo 'selected="selected"';?>>Sep</option>
								<option value="10" <?php if($selMonthAcquired=='10')echo 'selected="selected"';?>>Oct</option>
								<option value="11" <?php if($selMonthAcquired=='11')echo 'selected="selected"';?>>Nov</option>
								<option value="12" <?php if($selMonthAcquired=='12')echo 'selected="selected"';?>>Dec</option>
							</select>
							<select name="selYrAcquired" id="selYrAcquired" style="width:100px;">
								<option value="">--Year--</option>
								<?php
								$qYr = $db->select('equipment','DISTINCT LEFT(date_acquired,4) as yr',array(),' WHERE date_acquired <> "0000-00-00" ORDER BY date_acquired DESC');
								while($rYr = $db->fetch_array($qYr)):
								?>
								<option value="<?php echo $rYr['yr']?>" <?php if($selYrAcquired==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
								<?php endwhile;?>
							</select>
						</td>
					</tr>
					<tr>
						<td><div align="right">Category &nbsp;</div></td>
						<td style="padding: 10px 0px 0px 0px;">
							<select name="selCat" id="selCat" style="width:250px;">
								<option value="">-- Select --</option>
								<option value="Fixed Assets" <?php if($selCat=="Fixed Assets")echo 'selected="selected"';?>>Fixed Assets</option>
								<option value="Consumable Tools and Supplies" <?php if($selCat=="Consumable Tools and Supplies")echo 'selected="selected"';?>>Consumable Tools and Supplies</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td style="padding: 10px 0px 0px 0px;"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small"></td>
					</tr>
				</table>
			</div>
		</form>
		<div class="table-wrapper">
			<table id="tblist" width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="10%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('type').'&ascDes='.$ascDes?>">TYPE</a></div></th>
						<th width="8%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('inventory_id').'&ascDes='.$ascDes?>">CODE</a></div></th>
						<th width="15%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('equip_desc').'&ascDes='.$ascDes?>">DESCRIPTION</a></div></th>
						<th width="8%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('brand').'&ascDes='.$ascDes?>">BRAND/MADE</a></div></th>
						<th width="7%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('model').'&ascDes='.$ascDes?>">MODEL</a></div></th>
						<th width="7%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('serial_no').'&ascDes='.$ascDes?>">SERIAL</a></div></th>
						<th width="9%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('location').'&ascDes='.$ascDes?>">LOCATION / INCHARGE</a></div></th>
						<th width="10%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('date_acquired').'&ascDes='.$ascDes?>">DATE ACQUIRED</a></div></th>
						<th width="8%" scope="col"><div align="center"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('price').'&ascDes='.$ascDes?>">ACQUISITION<br>PRICE</a></div></th>
						<th width="9%" scope="col"><div align="left"><a href="?srch=<?php echo $txSearch?>&orderBy=<?php echo functions::encode('status').'&ascDes='.$ascDes?>">STATUS</a></div></th>
						<th width="17%" scope="col"><div align="center">Options</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$totalPrice=0;
				foreach($arrEquipment as $equip):
					$equip_id = $equip['equip_id'];
					$totalPrice += $equip['price'];
					$bgColor='';
					if( $equip['forRenewal'] )
						$bgColor = 'bgcolor="'.$color_expired.'"';
					elseif( $equip['forMaintenance'] )
						$bgColor = 'bgcolor="#fcb77b"';
				?>
					<tr id="rw<?php echo $equip['equip_id']?>" <?php echo $bgColor;?>>
						<td><?php echo $equip['type']?></td>
						<td><?php echo $equip['inventory_id']?></td>
						<td><a id="view<?php echo $equip['equip_id']?>" style="text-decoration:none;cursor:pointer;" class="thickbox" title="View Detail" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-view.php?vdidVw=<?php echo functions::encode($equip['equip_id']);?>','Property Detail','1')"><?php echo $equip['equip_desc']; echo ($equip['plate_no']) ? ' ('.$equip['plate_no'].')' : '';?></a></td>
						<td><?php echo $equip['brand']?></td>
						<td><?php echo $equip['model']?></td>
						<td><?php echo $equip['serial_no']?></td>
						<td><?php echo $equip['location']?></td>
						<td><?php echo functions::datearr($equip['date_acquired']);echo '<br>';echo '<i>'.$equip['lifeYearDetail'].'</i>';?></td>
						<td><div align="right"><?php echo functions::formatMoney($equip['price']);?></div></td>
						<td><?php echo $equip['status']?></td>
						<td>
							<div align="left">
								<a id="edit<?php echo $equip['equip_id']?>" style="display:none;" class="btn btn-mini btn-warning thickbox" title="Update this item" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-edit.php?vdidEdt=<?php echo functions::encode($equip['equip_id']);?>','Property Update')"><i class="halflings-icon white pencil"></i></a>
								<a id="print<?php echo $equip['equip_id']?>" class="btn btn-mini btn-success thickbox" title="Print this equipment" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-print.php?vdidVw=<?php echo functions::encode($equip['equip_id']);?>','Property Print','1')"><i class="halflings-icon white print"></i></a>
								<?php if( $db->getValue('inhouse_equip_leasing_item','count(*)',array('equip_id'=>$equip['equip_id']))==0 && $db->getValue('mr_item','count(*)',array('equip_id'=>$equip['equip_id']))==0 ){?>
								<a id="del<?php echo $equip['equip_id']?>" style="display:none;" class="btn btn-mini btn-danger" onClick="return askDel()" title="Remove this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?vdidDel=<?php echo functions::encode($equip['equip_id']);?>"><i class="halflings-icon white trash"></i></a>
								<?php }?>
							</div>
						</td>
					</tr>
				<?php endforeach;?>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($totalPrice);?></strong></div></td>
						<td></td>
						<td></td>
					</tr>
				</tbody>
			</table>
			<div align="right"><?php echo $num_record.' item/s'?></div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function askDel(){
	if(confirm('Do you want to delete this property?'))
		return true;
	else
		return false; 
}
</script>
<?php require_once('templ_down.php');?>
<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>