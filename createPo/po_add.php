<?php require_once('templ_up.php');?>
<?php
$insertID=0;
$arrInsert = array();
$selPoType = ( isset($_POST['selPoType']) && !empty($_POST['selPoType']) ) ? $_POST['selPoType'] : '';
$txTerms = ( isset($_POST['txTerms']) && !empty($_POST['txTerms']) ) ? $_POST['txTerms'] : '';
$txPayee = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : '';
$txInvoice = ( isset($_POST['txInvoice']) && !empty($_POST['txInvoice']) ) ? $_POST['txInvoice'] : '';
$txProj = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
$txBdate = ( isset($_POST['txPODate']) && functions::valid_date($_POST['txPODate']) ) ? $_POST['txPODate'] : NULL;
$txPreparedBy = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? $_POST['txPreparedBy'] : '';
$txApprove = ( isset($_POST['txApprove']) && !empty($_POST['txApprove']) ) ? $_POST['txApprove'] : '';
$txProjDetail = ( isset($_POST['txProjDetail']) && !empty($_POST['txProjDetail']) ) ? $_POST['txProjDetail'] : '';
$txItemCat = ( isset($_POST['txItemCat']) && !empty($_POST['txItemCat']) ) ? $_POST['txItemCat'] : 0;
$txReceive = 0;
$remarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : '';
$txPaymentTerm = ( isset($_POST['txPaymentTerm']) && !empty($_POST['txPaymentTerm']) ) ? trim($_POST['txPaymentTerm']) : '';
$dep_id = ( isset($_POST['selDep']) && !empty($_POST['selDep']) ) ? trim($_POST['selDep']) : NULL;
$equip_id = (isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? functions::decode($_POST['selEquip']) : '';
$damageDesc = ( isset($_POST['txDamageDesc']) && !empty($_POST['txDamageDesc']) ) ? trim($_POST['txDamageDesc']) : "";
$txConforme = ( isset($_POST['txConforme']) && !empty($_POST['txConforme']) ) ? trim($_POST['txConforme']) : "";
if( isset($_POST['btnCreate']) ){
	if( $txPayee && $txBdate && $txPreparedBy && $txApprove && $txItemCat && $selPoType ){
		if( $txInvoice && $db->getValue('po','count(*)',array('invoice'=>$txInvoice)) ){
			functions::say("Warning: Invoice already used in voucher!");
		}
		$allow_insert=1;
		$insertID=1;
		if($selPoType=='parts' || $selPoType=='accessory'){
			if( empty($equip_id) ){
				functions::say("Equipment Required!");
				$allow_insert=0;
			}
		}
		if($allow_insert){
			$insertID = $db->insert('po',array('proj_id'=>$txProj,'supplierID'=>$txPayee,'dep_id'=>$dep_id,'category_id'=>$txItemCat,'po_date'=>$txBdate,'purchaser'=>$txPreparedBy,'invoice'=>$txInvoice,'approved_by'=>$txApprove,'terms'=>$txTerms,'proj_detail'=>$txProjDetail,'po_type'=>$selPoType,'remarks'=>$remarks,'received'=>$txReceive,'payment_term'=>$txPaymentTerm,'conforme'=>$txConforme));
		}
		if($insertID){
			$ref_id = $db->getValue('po','concat(substring(po_date,1,4),substring(po_date,6,2),po_id) as dte',array('po_id'=>$insertID));
			$db->update('po',array('ref_id'=>$ref_id,'po_no'=>$ref_id),array('po_id'=>$insertID));
			if($selPoType=='parts'){
				$er_id = $db->insert('equip_repair',array('po_id'=>$insertID,'invoice'=>'P.O. No:'.$ref_id,'proj_id'=>$txProj,'er_date'=>$txBdate,'damage'=>$damageDesc,'repair_type'=>'repair','equip_id'=>$equip_id));
			}
			else if($selPoType=='accessory'){
				$db->insert('equip_accessory',array('po_id'=>$insertID,'proj_id'=>$txProj,'ea_date'=>$txBdate,'invoice'=>'P.O. No:'.$ref_id,'description'=>$damageDesc,'equip_id'=>$equip_id));
			}
			require_once('../class/class-po-history.php');
			PO_history::poCreate($insertID,$user_id);
			$_SESSION['notif_success']='New P.O. Successfully Created!';
			functions::sendTo('po_list.php?po_id='.functions::encode($insertID));
			die();
		}
		else
			functions::say('Failed to create po!');
	}
	else
		functions::say('Please fill up the form properly!');
}
unset($_SESSION['po_arr_proj'],$_SESSION['RefID']);

$qItem = $db->select('po','DISTINCT payment_term',array());
$namesPaymentTerm='';
while($rItem=$db->fetch_array($qItem)):
	$string = preg_replace("/'/",'"',$rItem['payment_term']);
	$namesPaymentTerm .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesPaymentTerm .= '"--"';

$qConfrme = $db->select('po','DISTINCT conforme',array());
$namesConfrme='';
while($rConfrme=$db->fetch_array($qConfrme)):
	$string = preg_replace("/'/",'"',$rConfrme['conforme']);
	$namesConfrme .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesConfrme .= '"--"';
?>
<!-- body content: start here-->
<div align="right">
	<a id="sadc" href="#" class="btn btn-info btn-small thickbox" onclick="showThis(this.id,'po_service_add.php?','SERVICE P.O.')">Create SERVICE P.O.</a> &nbsp; 
	<a id="adc" href="#" class="btn btn-info btn-small thickbox" onclick="showThis(this.id,'po_add_multiple.php?','Create Multiple P.O.')">Create P.O. With Multiple Projects</a> &nbsp; 
	<a id="updateMultiplePO" href="#" class="btn btn-info btn-small thickbox" onclick="showThis(this.id,'po_edit_multiple.php?','Update Multiple P.O.')">Update P.O. With Multiple Projects</a>
</div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span>Purchase Order Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="60%" align="center" border="0" style="background-color:#E4E1E1">
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">P.O Date</label>
								<div class="controls">
									<input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txPODate" id="txPODate" value="<?php echo $txBdate ?>" required>
									<span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Type</label>
								<div class="controls">
									<select name="selPoType" id="selPoType" style="width:400px;">
										<option value="">-- Select Type --</option>
										<option value="material" <?php if($selPoType=='material'){echo 'selected="selected"';} ?>>Materials / Supplies</option>
										<option value="parts" <?php if($selPoType=='parts'){echo 'selcted="selected"';} ?>>Repair (Tools / Equipments / Machine / Parts)</option>
										<option value="accessory" <?php if($selPoType=='accessory'){echo 'selected="selected"';} ?>>Accessory (Tools / Equipments / Parts)</option>
									</select>
									<span class="help-inline warning" style="font-weight:bold;" id="msgType" name="msgType"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr id="partsEqp">
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Equipment</label>
								<div class="controls">
									<select name="selEquip" id="selEquip" data-rel="chosen" style="width:700px;">>
										<option value="">-- Select Equipment --</option>
										<?php
										$qEquip = $db->select('equipment','*',array());
										while($rEquip = $db->fetch_array($qEquip)):
										?>
										<option value="<?php echo functions::encode($rEquip['equip_id'])?>" <?php if($equip_id==$rEquip['equip_id']){echo 'selected="selected"';} ?>><?php echo strtolower($rEquip['inventory_id']).' '.ucwords(strtolower($rEquip['equip_desc'])).' '.$rEquip['plate_no'].' '.$rEquip['serial_no'];?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgEqp" style="font-weight:bold;" name="msgEqp"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr id="partsDesc">
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess"><div id="accrepDesc">Repair / Damage Description</div></label>
								<div class="controls">
									<textarea style="width:80%;" rows="3" class="span6 typeahead" name="txDamageDesc" id="txDamageDesc" autocomplete='off'><?php echo $damageDesc?></textarea>
									<span class="help-inline warning" id="msgtxDamageDesc" style="font-weight:bold;" name="msgtxDamageDesc"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Payee / Supplier</label>
									<div class="controls">
									<select name="txPayee" id="txPayee" data-rel="chosen" style="width:500px;">
										<option value="">--select--</option>
										<?php $qSup = $db->select('supplier','*',array(),'ORDER BY name');
										while($rSup = $db->fetch_array($qSup)):
										?>
										<option value="<?php echo $rSup['supplierID']?>" <?php if($txPayee==$rSup['supplierID'])echo 'selected="selected"';?>><?php echo ucwords(strtolower($rSup['name']));?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgPayee" style="font-weight:bold;" name="msgPayee"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Project</label>
								<div class="controls">
									<select name="selProj" id="selProj" data-rel="chosen" style="width:700px;">
										<option value="">--select--</option>
										<?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
										while($rProj = $db->fetch_array($qProj)):
										?>
										<option value="<?php echo $rProj['proj_id']?>" <?php if($txProj==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo ucwords(strtolower($rProj['proj_name']));?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgProject" style="font-weight:bold;" name="msgProject"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Department</label>
								<div class="controls">
									<select name="selDep" id="selDep" data-rel="chosen" style="width:500px;">
										<option value="">--select--</option>
										<?php $qDep = $db->select('department','*',array(),'ORDER BY dep_desc');
										while($rDep = $db->fetch_array($qDep)):
										?>
										<option value="<?php echo $rDep['dep_id']?>" <?php if($dep_id==$rDep['dep_id'])echo 'selected="selected"';?>><?php echo $rDep['dep_desc'].' ('.$rDep['dep_name'].')';?></option>
										<?php endwhile;?>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Category</label>
								<div class="controls">
									<select name="txItemCat" id="txItemCat" data-rel="chosen" style="width:400px;">
										<option value="">--select--</option>
										<?php 
										$qCat = $db->select('item_deduction','*',array(),'ORDER BY name');
										while($rCat = $db->fetch_array($qCat)):
										?>
										<option value="<?php echo $rCat['item_id']?>" <?php if('MATERIALS'==$rCat['name']){echo 'selected="selected"';}else if($txItemCat==$rCat['item_id'])echo 'selected="selected"';?>><?php echo $rCat['name']?></option>
										<?php endwhile;?>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Additional description</label>
								<div class="controls">
									<input type="text" name="txProjDetail" id="txProjDetail" style="width:300px;"  value="<?php echo $txProjDetail;?>" />
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Invoice #</label>
								<div class="controls">
									<input type="text" name="txInvoice" id="txInvoice" style="width:300px;" value="<?php echo $txInvoice;?>" />
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Purchaser</label>
								<div class="controls">
									<select name="txPreparedBy" id="txPreparedBy" data-rel="chosen" style="width:400px;">
										<option value="">--select--</option>
										<?php $qPrep = $db->select('users','*',array('status'=>'active'),'ORDER BY lname');
										while($rPrep = $db->fetch_array($qPrep)):
										?>
										<option value="<?php echo $rPrep['user_id']?>" <?php if($txPreparedBy==$rPrep['user_id'])echo 'selected="selected"';?>><?php echo strtoupper($rPrep['lname'].', '.$rPrep['fname']);?></option>
										<?php endwhile;?>
									</select> 
									<span class="help-inline warning" id="msgPrepare" style="font-weight:bold;" name="msgPrepare"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Approval</label>
								<div class="controls">
									<select name="txApprove" id="txApprove" data-rel="chosen" style="width:400px;">
										<option value="">--select--</option>
										<?php $qApprove = $db->select('users','*',array('status'=>'active'),'ORDER BY lname');
										while($rApprove = $db->fetch_array($qApprove)):
										?>
										<option value="<?php echo $rApprove['user_id']?>" <?php if($txApprove==$rApprove['user_id'])echo 'selected="selected"';?>><?php echo strtoupper($rApprove['lname'].', '.$rApprove['fname']);?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgApprove" style="font-weight:bold;" name="msgApprove"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Conforme</label>
								<div class="controls">
									<input type="text" name="txConforme" id="txConforme" style="width:300px;" value="<?php echo $txConforme?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesConfrme;?>]' />
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Terms of Payment</label>
								<div class="controls">
									<input type="text" name="txPaymentTerm" id="txPaymentTerm" style="width:300px;" value="<?php echo $txPaymentTerm?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPaymentTerm;?>]' />
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group hidden-phone">
								<label class="control-label" for="textarea2">Terms and Conditions</label>
								<div class="controls">
									<textarea class="cleditor" id="txTerms" name="txTerms" rows="2">
									<?php if($txTerms){
									echo $txTerms;
									}else{
									?>
									<div style="font-family: Arial, Verdana; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: normal; font-size: 10pt;"><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;"><b>TERMS AND CONDITIONS:</b></span></font></div><div style="font-family: Arial, Verdana; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: normal; font-size: 10pt;"><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;"><b><br></b></span></font></div><div style="font-family: Arial, Verdana; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: normal; text-align: center;"><b style=""><span lang="EN-PH" style="line-height: 107%; font-family: Calibri, sans-serif; color: red;">FOR PICK-UP ITEMS</span></b></div><div style=""><ol style=""><li style=""><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">Enumerated items in this order be ready one (1) hour before it will be picked-up by our representative;</span></font></li><li style=""><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">Cost and Quantity ordered is not subject to change without prior notice and approval from the herein purchaser;</span></font></li><li style=""><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">This item is for PICK-UP with;</span><br><span style="font-size: 13.3333px;">• with check on date with ___ days clearing<br>•&nbsp;with check on Post-dated with ___ days of terms</span></font><br style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;"><span style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;">•&nbsp;</span><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">with cash only</span></font><br style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;"><span style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;">•&nbsp;</span><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">without check with ____ days of terms;</span></font></li><li style=""><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">Price is inclusive of 12% VAT and require issuance of official receipt or Sales Invoice;</span></font></li><li style=""><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">This transaction is subject to __% expanded Withholding Tax, BIR Form 2307 is issued upon payment;</span></font></li><li style=""><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">This item is priority to pick-up on or before ten (10) days after the approval of PO.</span></font></li></ol><div><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;"><br></span></font></div><div><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;"><br></span></font></div><div><br></div><div><div style="font-family: Arial, Verdana; font-size: 10pt;"><font color="#333333" face="Tahoma"><b><br></b></font></div><div style="font-family: Arial, Verdana; text-align: center;"><b><span lang="EN-PH" style="line-height: 17.12px; font-family: Calibri, sans-serif; color: red;">FOR DELIVERY ITEMS</span></b></div><div><ol><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">Enumerated items in this order be ready one (1) hour before it will be picked-up by our representative;</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">Cost and Quantity ordered is not subject to change without prior notice and approval from the herein purchaser;</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">This item is for DIRECT DELIVERY to __________________ with;</span><br><span style="font-size: 13.3333px;">•&nbsp;with check on date with ___ days clearing<br>•&nbsp;with check on Post-dated with ___ days of terms</span></font><br style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;"><span style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;">•&nbsp;</span><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">with cash only</span></font><br style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;"><span style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;">•&nbsp;</span><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">without check with ____ days of terms;</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">All risk of loss shall remain with seller until goods have actually been received and accepted by our representative at the applicable destination.</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">If the total or any portion of the goods received either exceeds or falls below the quantities ordered, we have the right to reject and return, any such delivery or portion thereof at seller’s expenses for transportation both ways and all related Labor and Parking Cost;</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">Price is inclusive of 12% VAT and require issuance of official receipt or Sales Invoice;</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">This transaction is subject to __% expanded Withholding Tax, BIR Form 2307 is issued upon payment;</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">This item is priority to pick-up on or before ten (10) days after the approval of PO.</span></font></li></ol><div><br></div></div></div></div>
									<?php }?>
									</textarea>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group hidden-phone">
								<label class="control-label" for="textarea2">Remarks</label>
								<div class="controls">
									<textarea id="txRemarks" name="txRemarks" rows="2"><?php echo $remarks?></textarea>
								</div>
							</div>
						</td>
					</tr>
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td>
							<div class="controls">
								<input type="submit" name="btnCreate" id="btnCreate" value="Create" class="btn btn-small btn-primary">
								<button class="btn btn-small">Cancel</button>
							</div>
						</td>
					</tr>
					<tr><td>&nbsp;</td></tr>
				</table>
			</form>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script>
$(document).ready(function(){
	var res = false;
	<?php if($selPoType=='parts' || $selPoType=='accessory'){ ?>
	$('#partsEqp').show();
	$('#partsDesc').show();
	if($(this).val()=='parts')
		$('#accrepDesc').html('Repair / Damage Description');
	else
		$('#accrepDesc').html('Accessory Description');
	<?php }else{ ?>
	$('#partsEqp').hide();
	$('#partsDesc').hide();
	<?php } ?>


	$('#selPoType').change(function(){
		if( $(this).val()=='parts' || $(this).val()=='accessory' ){
			$('#partsEqp').show('fade');
			$('#partsDesc').show('fade');
			if($(this).val()=='parts')
				$('#accrepDesc').html('Repair / Damage Description');
			else
				$('#accrepDesc').html('Accessory Description');
		}
		else{
			$('#partsEqp').hide('fade');
			$('#partsDesc').hide('fade');
		}
	});

	$('#btnCreate').click(function(){

		$('#msgBdate').html("");
		$('#msgPayee').html("");
		$('#msgProject').html("");
		$('#msgPrepare').html("");
		$('#msgApprove').html("");
		$('#msgType').html("");
		$('#msgEqp').html("");
		$('#msgtxDamageDesc').html("");

		if( $('#txPODate').val()=="" ){
			$('#txPODate').focus();
			$('#msgBdate').html("Date Required!");
			res=false;
		}
		else if( $('#selPoType').val()=="" ){
			$('#selPoType').focus();
			$('#msgType').html("Type Required!");
			res=false;
		}
		else if( $('#selPoType').val()=="parts" && $('#selEquip').val()=="" ){
			$('#selEquip').focus();
			$('#msgEqp').html("Equipment Required!");
			res=false;
		}
		else if( $('#txPayee').val()=="" ){
			$('#txPayee').focus();
			$('#msgPayee').html("Supplier Required!");
			res=false;
		}
		else if( $('#txPreparedBy').val()=="" ){
			$('#msgPrepare').html("Purchase Required!");
			$('#txPreparedBy').focus();
			res=false;
		}
		else if( $('#txApprove').val()=="" ){
			$('#msgApprove').html("Approval Required!");
			$('#txApprove').focus();
			res=false;
		}
		else{
			if(confirm('Do you want to submit this information?'))
				res=true;
			else
				res=false;
		}
		return res;
	});
	$('#txPODate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2010:<?php echo date('Y')+10 ?>'
	});
});
</script>