<?php require_once('templ_up.php');?>
<?php
$startrow=0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;

$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$po_idDel = (isset($_REQUEST['po_idDel']) && !empty($_REQUEST['po_idDel']) ) ? functions::decode($_REQUEST['po_idDel']) : 0;
$arr = array();
$day='';$mon='';
$txProj = '';$supplier='';
$txPayee = '';
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';
$equip_id='';
$txSearchPO='';
$totalAmount=0;
$unpaid=0;
unset($_SESSION['po_arr_proj'],$_SESSION['po_arr_equip'],$_SESSION['RefID']);
if( isset($_POST['btnSearchPO']) ){
	unset($_SESSION['pfCharge'],$_SESSION['pfEquip'],$_SESSION['pfMon'],$_SESSION['pfYear']);
	$txSearchPO = ( isset($_POST['txSearchPO']) && !empty($_POST['txSearchPO']) ) ? $_POST['txSearchPO'] : '';
}

if( isset($_POST['btnViewAll']) ){
	unset($_SESSION['pfCharge'],$_SESSION['pfEquip'],$_SESSION['pfMon'],$_SESSION['pfYear'],$_SESSION['pfDay']);
}
if( isset($_POST['btnSearch']) ){
	$_SESSION['pfCharge'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $db->clean($_POST['selProj']) : '';
	$_SESSION['pfSuplr'] = ( isset($_POST['selSupplier']) && !empty($_POST['selSupplier']) ) ? $db->clean(functions::decode($_POST['selSupplier'])) : '';
	$_SESSION['pfEquip'] = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? $db->clean(functions::decode($_POST['selEquip'])) : '';
	$_SESSION['pfMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $db->clean($_POST['bdMon']) : '';
	$_SESSION['pfYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $db->clean($_POST['bdYear']) : '';
	$_SESSION['pfDay'] = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $db->clean($_POST['bdDay']) : '';
	$_SESSION['pfMonTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $db->clean($_POST['bdMonTo']) : '';
	$_SESSION['pfYearTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $db->clean($_POST['bdYearTo']) : '';
	$_SESSION['pfDayTo'] = ( isset($_POST['bdDayTo']) && !empty($_POST['bdDayTo']) ) ? $db->clean($_POST['bdDayTo']) : '';
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnUnpaid']) ){
	$_SESSION['pfCharge'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $db->clean($_POST['selProj']) : '';
	$_SESSION['pfSuplr'] = ( isset($_POST['selSupplier']) && !empty($_POST['selSupplier']) ) ? $db->clean(functions::decode($_POST['selSupplier'])) : '';
	$_SESSION['pfEquip'] = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? $db->clean(functions::decode($_POST['selEquip'])) : '';
	$_SESSION['pfMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $db->clean($_POST['bdMon']) : '';
	$_SESSION['pfYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $db->clean($_POST['bdYear']) : '';
	$_SESSION['pfDay'] = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $db->clean($_POST['bdDay']) : '';
	$_SESSION['pfMonTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $db->clean($_POST['bdMonTo']) : '';
	$_SESSION['pfYearTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $db->clean($_POST['bdYearTo']) : '';
	$_SESSION['pfDayTo'] = ( isset($_POST['bdDayTo']) && !empty($_POST['bdDayTo']) ) ? $db->clean($_POST['bdDayTo']) : '';
	$unpaid=1;
}

$arrChargeList=array();
$qProj = $db->query('SELECT * FROM project WHERE proj_id IN (SELECT DISTINCT proj_id FROM po WHERE po_type="fuel") ORDER BY proj_name');
while($rProj = $db->fetch_array($qProj)):
	if($rProj['proj_id'])
		$arrChargeList[$rProj['proj_name']]=$rProj['proj_id'];
endwhile;

$qPayee = $db->query('SELECT DISTINCT payee FROM po_fuel ORDER BY payee');
while($rPayee = $db->fetch_array($qPayee)):
	if($rPayee['payee'])
		$arrChargeList[strtoupper($rPayee['payee'])]=$rPayee['payee'];
endwhile;
ksort($arrChargeList);


$arrEquipList=array();
#$qEquip = $db->query('SELECT * FROM equipment e, po_fuel_equip pfe WHERE e.equip_id=pfe.equip_id');
$qEquip = $db->query('SELECT * FROM equipment ORDER BY name');
while($rEquip = $db->fetch_array($qEquip)):
	if($rEquip['name'])
		$arrEquipList[$rEquip['inventory_id'].' '.$rEquip['name']]=$rEquip['equip_id'];
endwhile;

$qOtherEquip = $db->query('SELECT DISTINCT other_equip FROM po_fuel_equip ORDER BY other_equip');
while($rOtherEquip = $db->fetch_array($qOtherEquip)):
	if($rOtherEquip['other_equip'])
		$arrEquipList[strtoupper($rOtherEquip['other_equip'])]=$rOtherEquip['other_equip'];
endwhile;
ksort($arrEquipList);

$where = ''; $charge='';
if($po_idDel){
	$amount = $db->getValue('po_item','sum( (qty_delivered * cost) )',array('po_id'=>$po_idDel));
	if($amount == 0){
		$db->delete('po',array('po_id'=>$po_idDel));
		$db->delete('po_fuel_equipment',array('po_id'=>$po_idDel));
		$_SESSION['notif_warning']='Fuel P.O. Removed!';
		functions::sendTo(functions::pageName());
		die();
    }
}
if($txSearchPO){
	$where .= ' AND po_no LIKE "%'.$db->clean($txSearchPO).'%"';
}
else{
	$charge = ( isset($_SESSION['pfCharge']) && !empty($_SESSION['pfCharge']) ) ? $_SESSION['pfCharge'] : '';
	$equip_id = ( isset($_SESSION['pfEquip']) && !empty($_SESSION['pfEquip']) ) ? $_SESSION['pfEquip'] : '';
	$supplier = ( isset($_SESSION['pfSuplr']) && !empty($_SESSION['pfSuplr']) ) ? $_SESSION['pfSuplr'] : '';
	$txbMon = ( isset($_SESSION['pfMon']) ) ? $_SESSION['pfMon'] : date('m');
	$txbYear = ( isset($_SESSION['pfYear']) ) ? $_SESSION['pfYear'] : date('Y');
	$txbDay = ( isset($_SESSION['pfDay']) ) ? $_SESSION['pfDay'] : date('d');
	$txbMonTo = ( isset($_SESSION['pfMonTo']) ) ? $_SESSION['pfMonTo'] : date('m');
	$txbYearTo = ( isset($_SESSION['pfYearTo']) ) ? $_SESSION['pfYearTo'] : date('Y');
	$txbDayTo = ( isset($_SESSION['pfDayTo']) ) ? $_SESSION['pfDayTo'] : date('d'); 
	$arr = array('po_type'=>'fuel');
      
	if($charge){
		if( $db->getValue('project','count(proj_id)',array('proj_id'=>$charge)) )
			$where .= ' AND p.proj_id="'.$charge.'"';
		else
			$where .= ' AND pf.payee="'.$charge.'"';
	}
	if($equip_id){
		if( $db->getValue('equipment','count(equip_id)',array('equip_id'=>$equip_id)) )
			$where .= ' AND pfe.equip_id="'.$equip_id.'"';
		else
			$where .=' AND pfe.other_equip="'.$equip_id.'"';
	}

	if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
		$where .=' AND (po_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
	else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
		$where .=' AND ( LEFT(po_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(po_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
	else if($txbMon && $txbMonTo)
		$where .=' AND (SUBSTRING(po_date,6,2)>="'.$txbMon.'" AND SUBSTRING(po_date,6,2) <= "'.$txbMonTo.'")';
	else if($txbYear && $txbYearTo)
		$where .=' AND (LEFT(po_date,4) >= "'.$txbYear.'" AND LEFT(po_date,4) <= "'.$txbYearTo.'")';
	else if($txbMon && $txbYear && $txbDay)
		$where .=' AND po_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';
	if($supplier)
		$where .=' AND supplierID="'.$db->clean($supplier).'"';
}
if($equip_id){
	$qPO = $db->query('SELECT * FROM po p, po_fuel pf, po_fuel_equipment pfe WHERE p.po_id=pf.po_id AND p.po_id=pfe.po_id AND po_type="fuel" '.$where.' ORDER BY po_date DESC LIMIT '.$startrow.', '.$rowdisplay);
	if($unpaid)
		$qPO = $db->query('SELECT * FROM po p, po_fuel pf, po_fuel_equipment pfe WHERE p.po_id=pf.po_id AND p.po_id=pfe.po_id AND po_type="fuel" AND vp_id is NULL '.$where.' ORDER BY po_date DESC');
	$nr = $db->query('SELECT * FROM po p, po_fuel pf, po_fuel_equipment pfe WHERE p.po_id=pf.po_id AND p.po_id=pfe.po_id AND po_type="fuel" '.$where);
	$num_record = $db->num_rows($nr);
}
else{
	$qPO = $db->query('SELECT * FROM po p, po_fuel pf WHERE p.po_id=pf.po_id AND po_type="fuel" '.$where.' ORDER BY po_date DESC LIMIT '.$startrow.', '.$rowdisplay);
	if($unpaid)
		$qPO = $db->query('SELECT * FROM po p, po_fuel pf WHERE p.po_id=pf.po_id AND po_type="fuel" AND vp_id is NULL '.$where.' ORDER BY po_date DESC');
	$nr = $db->query('SELECT * FROM po p, po_fuel pf WHERE p.po_id=pf.po_id AND po_type="fuel" '.$where);
	$num_record = $db->num_rows($nr);
}
?>
<!-- body content: start here-->
<div>
	<table width="100%" border="0">
		<tr>
			<td>
				<div align="right">
					<a id="adc" href="#" class="btn btn-info btn-setting thickbox btn-small" onclick="showThis(this.id,'po_fuel_add.php?','Add P.O. Fuel')">Add P.O. Fuel</a>&nbsp;
					<a id="adcd" href="#" class="btn btn-info btn-setting thickbox btn-small" onclick="showThis(this.id,'po_fuel_add_multiple.php?','Add Multipe P.O. Fuel')">Add Multiple P.O. Fuel</a>&nbsp;
					<a id="updateMultiplePO" href="#" class="btn btn-info btn-setting thickbox btn-small" onclick="showThis(this.id,'po_fuel_edit_multiple.php?','Update Multiple P.O.')">Update P.O. With Multiple Projects</a>
					<a id="report" href="#" class="btn btn-info btn-setting thickbox btn-small" onclick="showThis(this.id,'po_fuel_report.php?','Fuel P.O. Report')">Show Report</a>
				</div>
			</td>
		</tr>
	</table>
</div><br><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Fuel Purchase Order List</h2>
		</div>
		<div class="box-content">
			<table cellspacing="0" cellpadding="0" border='0' align="right">
				<tr>
					<td width="10" height='30'><div style="background-color:#e6eb9c; width:20px;">&nbsp;</div></td>
					<td width="90">&nbsp;&nbsp; No Invoice</td>
				</tr>
			</table><br><br>
			<form method="post">
				<table border="0">
					<tr>
						<td colspan="4" height="70">P.O. No: <input type="text" name="txSearchPO" id="txSearchPO" value="<?php echo $txSearchPO;?>">&nbsp;<input type="submit" name="btnSearchPO" id="btnSearchPO" value="Search P.O." class="btn btn-primary btn-small">&nbsp;<input type="submit" name="btnViewAll" id="btnViewAll" value="View All" class="btn btn-primary btn-small">&nbsp;<input type="submit" name="btnUnpaid" id="btnUnpaid" value="View Unpaid P.O." class="btn <?php echo ($unpaid) ? ' btn-small': 'btn-primary btn-small';?>"></td>
					</tr>
					<tr>
						<td colspan="4"><hr width="100%"></td>
					</tr>
					<tr>
						<td width="22%">
							<div align="center">
								<div align="center"><strong>FROM</strong></div>
								<select name="bdYear" id="bdYear" style="width:80px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('po','DISTINCT LEFT(po_date,4) as yr',array('po_type'=>'fuel'),'ORDER BY po_date DESC');
									while($rYr = $db->fetch_array($qYr)):
									?>
									<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
									<?php endwhile;?>
								</select>
								<select name="bdMon" id="bdMon" style="width:85px;">
									<option value="">All Month</option>
									<option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
									<option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
									<option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
									<option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
									<option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
									<option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
									<option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
									<option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
									<option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
									<option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
									<option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
									<option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
								</select>
								<select name="bdDay" id="bdDay" style="width:65px;">
									<option value="">Day</option>
									<?php for($i=1;$i<=31;$i++):?>
									<option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDay)echo 'selected="selected"';?>><?php echo $i;?></option>
									<?php endfor;?>
								</select>
							</div>
							<div align="center">
								<div align="center"><strong>TO</strong></div>
								<select name="bdYearTo" id="bdYearTo" style="width:80px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('po','DISTINCT LEFT(po_date,4) as yr',array('po_type'=>'fuel'),'ORDER BY po_date DESC');
									while($rYr = $db->fetch_array($qYr)):
									?>
									<option value="<?php echo $rYr['yr']?>" <?php if($txbYearTo==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
									<?php endwhile;?>
								</select>
								<select name="bdMonTo" id="bdMonTo" style="width:85px;">
									<option value="">All Month</option>
									<option value="01" <?php if($txbMonTo=='01')echo 'selected="selected"';?>>Jan</option>
									<option value="02" <?php if($txbMonTo=='02')echo 'selected="selected"';?>>Feb</option>
									<option value="03" <?php if($txbMonTo=='03')echo 'selected="selected"';?>>Mar</option>
									<option value="04" <?php if($txbMonTo=='04')echo 'selected="selected"';?>>Apr</option>
									<option value="05" <?php if($txbMonTo=='05')echo 'selected="selected"';?>>May</option>
									<option value="06" <?php if($txbMonTo=='06')echo 'selected="selected"';?>>Jun</option>
									<option value="07" <?php if($txbMonTo=='07')echo 'selected="selected"';?>>Jul</option>
									<option value="08" <?php if($txbMonTo=='08')echo 'selected="selected"';?>>Aug</option>
									<option value="09" <?php if($txbMonTo=='09')echo 'selected="selected"';?>>Sep</option>
									<option value="10" <?php if($txbMonTo=='10')echo 'selected="selected"';?>>Oct</option>
									<option value="11" <?php if($txbMonTo=='11')echo 'selected="selected"';?>>Nov</option>
									<option value="12" <?php if($txbMonTo=='12')echo 'selected="selected"';?>>Dec</option>
								</select>
								<select name="bdDayTo" id="bdDayTo" style="width:65px;">
									<option value="">Day</option>
									<?php for($i=1;$i<=31;$i++):?>
									<option value="<?php echo $i;?>" <?php if($i==$txbDayTo)echo 'selected="selected"';?>><?php echo $i;?></option>
									<?php endfor;?>
								</select>
							</div>
						</td>
						<td width="18%">
							<div align="left">
								<table border="0" class="tablea">
									<tr>
										<td style="padding: 15px 10px 15px 0px;">Charge To:</td>
										<td>
											<select name="selProj" id="selProj" data-rel="chosen" style="width:550px;">
												<option value="">--All Charge To--</option>
												<?php foreach($arrChargeList as $name => $projid): ?>
												<option value="<?php echo $projid?>" <?php if($charge===$projid)echo 'selected="selected"';?>><?php echo ucwords(strtolower($name));?></option>
												<?php endforeach;?>
											</select>
										</td>
									</tr>
									<tr>
										<td style="padding: 15px 10px 15px 0px;">Supplier</td>
										<td>
											<select name="selSupplier" id="selSupplier" data-rel="chosen" style="width:550px;">
												<option value="">-- All Supplier --</option>
												<?php
												$qItmD = $db->select('po p, po_fuel pf, supplier s','p.supplierID, name, count(*)',array(),'WHERE s.supplierID=p.supplierID AND p.po_id=pf.po_id AND po_type="fuel" GROUP BY p.supplierID ORDER BY s.name');
												while($rItmD = $db->fetch_array($qItmD)):
												?>
												<option value="<?php echo functions::encode($rItmD['supplierID'])?>" <?php if($rItmD['supplierID']===$supplier)echo 'selected="selected"';?>><?php echo ucwords(strtolower($rItmD['name']));?></option>
												<?php endwhile;?>
											</select>
										</td>
									</tr>
									<tr>
										<td style="padding: 15px 10px 15px 0px;">Equipment</td>
										<td>
											<select name="selEquip" id="selEquip" data-rel="chosen" style="width:550px;">
												<option value="">-- All Equipment --</option>
												<?php foreach($arrEquipList as $equip_name => $equipid): ?>
												<option value="<?php echo functions::encode($equipid)?>" <?php if($equipid===$equip_id)echo 'selected="selected"';?>><?php echo ucwords(strtolower($equip_name));?></option>
												<?php endforeach;?>
											</select>
										</td>
									</tr>
								</table>
							</div>
						</td>
						<td width="8%">
							<div align="center">
								<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small">
							</div>
						</td>
					</tr>
					<tr><td colspan="4"><hr width="100%"></td></tr>
				</table>
			</form>
			<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_po_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px">
				<thead>
					<tr style="background-color:#b8acac;">
						<th width="4%"><div align="center">Voucher</div></th>
						<th width="9%">P.O. No</th>
						<th width="5%">Invoice</th>
						<th width="7%">P.O. Date</th>
						<th width="9%">Supplier</th>
						<th width="15%">Equipment</th>
						<th width="20%">Charge To</th>
						<th width="8%">Amount</th>
						<th width="11%"><div align="center">Options</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$count=0;
				$totalUnpaidAmount=0;
				while($rPO = $db->fetch_array($qPO)):
					$count++;
					$payee = $db->getValue('po_fuel','payee',array('po_id'=>$rPO['po_id']));
					$amount = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id']));
					$amount = ($amount) ? $amount : $db->getValue('po_item','sum( cost - (cost * (discount/100)) )',array('po_id'=>$rPO['po_id']));
					$totalUnpaidAmount += $amount;
					$vid = $db->getValue('voucher_particular','voucher_id',array('vp_id'=>$rPO['vp_id']));
					$qty = $db->getValue('po_item','sum(quantity)',array('po_id'=>$rPO['po_id']));
					$qty_delivered = $db->getValue('po_item','sum(qty_delivered)',array('po_id'=>$rPO['po_id']));
					$approved=$db->getValue('voucher','count(approved)',array('voucher_id'=>$vid));
					$ref_id = $rPO['ref_id'];
				?>
					<tr id="pl<?php echo $rPO['po_id']?>" <?php echo ($rPO['invoice']) ? '' : 'style="background-color:#e6eb9c;"';?>>
						<td>
						<?php 
						$qVid = $db->query('SELECT vp.voucher_id FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vpp.po_id="'.$db->clean($rPO['po_id']).'"');
						while($rVid = $db->fetch_array($qVid)):
						?>
						<div align="center"><a href="voucher_view.php?vid=<?php echo functions::encode($rVid['voucher_id'])?>"><?php echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$rVid['voucher_id']));?></a></div>
						<?php endwhile;?>
						</td>
						<td><?php echo $rPO['po_no'];?></td>
						<td><?php echo $rPO['invoice']?></td>
						<td><?php echo functions::datearr($rPO['po_date']);?></td>
						<td><?php echo $db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID']));?></td>
						<td>
							<table>
								<?php
								if($equip_id){
									if( $db->getValue('equipment','count(equip_id)',array('equip_id'=>$equip_id)) )
										$qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$rPO['po_id'],'equip_id'=>$equip_id));
									else
										$qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$rPO['po_id'],'other_equip'=>$equip_id));
								}
								else
									$qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$rPO['po_id']));
								$arrUsedEquip=array();
								while($rEqp = $db->fetch_array($qEqp)):
									$eqpName = $db->getValue('equipment','name',array('equip_id'=>$rEqp['equip_id']));
									$eqpName = ($eqpName) ? $eqpName :  $rEqp['other_equip'];
									$arrUsedEquip = functions::insert_array($arrUsedEquip,$eqpName);
								endwhile;
								sort($arrUsedEquip);
								$allEqp = count($arrUsedEquip);
								$cntEqp=1;
								foreach($arrUsedEquip as $eqp_name):
								$cntEqpDisp = ($allEqp > 1) ? '<strong>'.$cntEqp++.')</strong>' : '&nbsp;';
									echo '<tr>';
										echo '<td style="border: none;">'.$cntEqpDisp.'</td>';
										echo '<td style="border: none;">'.$eqp_name.'</td>';
									echo '</tr>';
								endforeach;
								?>
							</table>
						</td>
						<td valign="center"><?php echo ($rPO['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id'])) : $payee.' <i>(outsider)</i>';?></td>
						<td><a id="costdetail<?php echo $rPO['po_id']?>" class="label label-info thickbox" title="View P.O. Details" data-rel="tooltip" onclick="showThis(this.id,'po_fuel_view_only.php?po_id=<?php echo functions::encode($rPO['po_id']);?>','P.O. Details','1')"><?php echo ($amount) ? functions::formatMoney($amount) : '--';?></a></td>
						<td>
							<div align="left">
							<?php if($approved==0){?>
								<a id="detail<?php echo $rPO['po_id']?>" class="btn btn-mini btn-info thickbox" title="Manage P.O. item" data-rel="tooltip" onclick="showThis(this.id,'po_fuel_view.php?po_id=<?php echo functions::encode($rPO['po_id']);?>','P.O. Details')"><i class="halflings-icon white plus-sign"></i> </a>
								<?php
									if($cntEqp >= 2)
										$poPage='po_fuel_edit_multiple.php';
									else if( $db->getValue('po','count(ref_id)',array('ref_id'=>$rPO['ref_id'])) >= 2 )
										$poPage='po_fuel_edit_multiple.php';
									else
										$poPage='po_fuel_edit.php';
								?>
								<a id="edit<?php echo $rPO['po_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify this P.O." data-rel="tooltip" onclick="showThis(this.id,'<?php echo $poPage?>?po_id=<?php echo functions::encode($rPO['po_id']);?>&refID=<?php echo functions::encode($ref_id)?>','P.O. Details')"><i class="halflings-icon white pencil"></i></a>
							<?php }?>
								<a id="print<?php echo $rPO['po_id']?>" class="btn btn-mini btn-success thickbox" title="Print this P.O." data-rel="tooltip" onclick="showThis(this.id,'po_fuel_print.php?po_id=<?php echo functions::encode($rPO['po_id']);?>&eqid=<?php echo functions::encode($equip_id)?>','P.O. Details','1')"><i class="halflings-icon white print"></i></a>
							<?php if( $db->getValue('po','count(*)',array('ref_id'=>$rPO['ref_id'])) > 1 ){?>
								<a id="print1<?php echo $rPO['po_id']?>" class="btn btn-mini btn-success thickbox" title="Print this P.O. as Group" data-rel="tooltip" onclick="showThis(this.id,'po_fuel_print_1.php?ref_id=<?php echo functions::encode($rPO['ref_id']);?>&eqid=<?php echo functions::encode($equip_id)?>','P.O. Details','1')"><i class="halflings-icon white list-alt"></i></a>
							<?php }?>
							<?php if($approved==0 && $amount==0){?>
								<a id="del<?php echo $rPO['po_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this P.O." data-rel="tooltip" href="<?php echo functions::pageName()?>?po_idDel=<?php echo functions::encode($rPO['po_id']);?>"><i class="halflings-icon white trash"></i></a>
							<?php }?>
							</div>
						</td>
					</tr>
				<?php endwhile; ?>
				<?php if($unpaid){?>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td><div align="right"><strong>Total Amount</strong></div></td>
						<td><strong><?php echo functions::formatMoney($totalUnpaidAmount)?></strong></td>
						<td></td>
					</tr>
				<?php }//if($unpaid)?>
				<?php
				if($count==0){
					echo '<tr><td colspan="9"><div align="center"><strong>-- No result --</strong></div></td></tr>';
				}
				?>
				</tbody>
			</table>
			<div align="center"><?php if($unpaid==0)functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=functions::pageName().'?',$search="");?></div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function delt(){
	if(confirm('Do you want to remove this P.O.?'))
		return true;
	else
		return false; 
}
</script>
<?php require_once('templ_down.php');?>
<?php if(isset($_SESSION['notif_id_po_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#pl<?php echo $_SESSION['notif_id_po_list'] ?>').centerView();
	$('#pl<?php echo $_SESSION['notif_id_po_list'] ?>').css('border','3px solid green');
	$("#pl<?php echo $_SESSION['notif_id_po_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#pl<?php echo $_SESSION['notif_id_po_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#polist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_po_list']);} ?>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>