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
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$_SESSION['notif_id_po_list']=$po_id;
$txPaymentTerm='';$txRemarks='';$payee='';$proj_id='';$monDelvry='';$dayDelvry='';$yearDelvry='';$mon='';$day='';$year='';$txInvoice='';$txPayee='';$txApprove='';$txTerms='';$txProjDetail='';$txReceive=0;$txServed=0;$txItemCat=0;

$q = $db->select('po','*',array('po_id'=>$po_id));
$r = $db->fetch_array($q);

$txBdate = $r['po_date'];
$proj_id = $r['proj_id'];
$dep_id = $r['dep_id'];
$txInvoice = $r['invoice'];
$txPreparedBy = $r['purchaser'];
$txPayee = $r['supplierID'];
$txApprove = $r['approved_by'];
$txTerms=$r['terms'];
$txProjDetail=$r['proj_detail'];
$txReceive = $r['received'];
$txItemCat = $r['category_id'];
$txRemarks = $r['remarks'];
$txPaymentTerm = $r['payment_term'];
$ref_id = $r['ref_id'];
$po_type = $r['po_type'];
$conforme = $r['conforme'];

$dlvryDate = $r['delivery_date'];
$xDDate = explode('-',$dlvryDate);
$monDelvry = isset($xDDate[1]) ? $xDDate[1] : "";
$dayDelvry = isset($xDDate[2]) ? $xDDate[2] : "";
$yearDelvry = isset($xDDate[0]) ? $xDDate[0] : "";

$eqp = $db->select('equip_repair','*',array('po_id'=>$po_id));
$rqp = $db->fetch_array($eqp);
$equip_id = $rqp['equip_id'];
$damageDesc = $rqp['damage'];

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
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Purchase Order Update</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<!-- start: Favicon -->
	<link rel="shortcut icon" href="../img/favicon.png">
	<!-- end: Favicon -->
<?php
if( isset($_POST['btnSave']) ){
	$insertID=0;
	$arrInsert = array();

	$poid = ( isset($_POST['poid']) && !empty($_POST['poid']) ) ? functions::decode($_POST['poid']) : '';
	$selPoType = ( isset($_POST['selPoType']) && !empty($_POST['selPoType']) ) ? $_POST['selPoType'] : '';
	$txPayee = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : '';
	$txProj = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
	$txBdate = ( isset($_POST['txPODate']) && functions::valid_date($_POST['txPODate']) ) ? $_POST['txPODate'] : NULL;

	$txInvoice = ( isset($_POST['txInvoice']) && !empty($_POST['txInvoice']) ) ? $_POST['txInvoice'] : '';
	$txPreparedBy = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? $_POST['txPreparedBy'] : '';
	$txApprove = ( isset($_POST['txApprove']) && !empty($_POST['txApprove']) ) ? $_POST['txApprove'] : '';
	$txTerms = ( isset($_POST['txTerms']) && !empty($_POST['txTerms']) ) ? $_POST['txTerms'] : '';
	$txProjDetail = ( isset($_POST['txProjDetail']) && !empty($_POST['txProjDetail']) ) ? $_POST['txProjDetail'] : '';
	$txItemCat = ( isset($_POST['txItemCat']) && !empty($_POST['txItemCat']) ) ? $_POST['txItemCat'] : 0;

	$remarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : '';
	$txPaymentTerm = ( isset($_POST['txPaymentTerm']) && !empty($_POST['txPaymentTerm']) ) ? trim($_POST['txPaymentTerm']) : '';
	#$po_type = $db->getValue('po','po_type',array('ref_id'=>$ref_id));
	$dep_id = ( isset($_POST['selDep']) && !empty($_POST['selDep']) ) ? trim($_POST['selDep']) : NULL;
	$equip_id = (isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? functions::decode($_POST['selEquip']) : '';
	$damageDesc = ( isset($_POST['txDamageDesc']) && !empty($_POST['txDamageDesc']) ) ? trim($_POST['txDamageDesc']) : "";
	$txConforme = ( isset($_POST['txConforme']) && !empty($_POST['txConforme']) ) ? trim($_POST['txConforme']) : "";

	if( $txPayee && $txBdate && $poid && $txPreparedBy && $selPoType){


		$allow_update=1;
		if($selPoType=='parts'){
			if( empty($equip_id) ){
				functions::say("Equipment Required!");
				$allow_update=0;
			}
			else{
				#$existing_po = $db->getValue();
				$qPODetails = $db->select('po, po_item','*',array('po.po_id'=>$po_id),'AND po.po_id=po_item.po_id');
				$spare='';
				$po_amount=0;
				while($rPODetails = $db->fetch_array($qPODetails)):
					$amount=0;
					$amount = $rPODetails['cost'] * $rPODetails['qty_delivered'];
					$disc_amount = ($rPODetails['discount']) ? $amount * ($rPODetails['discount'] / 100) : 0;
					$amount = $amount - $disc_amount;
					$po_amount += $amount;
					$spare .= $rPODetails['item'].' ( '.round($rPODetails['qty_delivered'],2).' '.$rPODetails['unit'].' x '.functions::formatMoney($rPODetails['cost']).')<br>';
				endwhile;
				if( $db->getValue('equip_repair','count(*)',array('po_id'=>$po_id))==0 ){//check if there is existing record
					$db->insert('equip_repair',array('po_id'=>$po_id,'equip_id'=>$equip_id,'proj_id'=>$txProj,'er_date'=>$txBdate,'damage'=>$damageDesc,'quotation_part'=>$po_amount,'invoice'=>'P.O. No:'.$ref_id,'repair_type'=>'repair'));
				}
				else{
					$db->update('equip_repair',array('equip_id'=>$equip_id,'proj_id'=>$txProj,'er_date'=>$txBdate,'damage'=>$damageDesc,'quotation_part'=>$po_amount,'invoice'=>'P.O. No:'.$ref_id),array('po_id'=>$po_id));
				}
				$db->prepareQ('UPDATE equip_repair SET description=? WHERE po_id=?',array($spare,$po_id));
				#echo $db->last_query;
				#die();
			}
		}
		if($allow_update){

			if( $po_type=='service' ){
				//Don't update invoice display on voucher particular because if po is service, there is posibility that every voucher has different invoice.
			}
			else{
				$oldInvoice = $db->getValue('po','invoice',array('po_id'=>$poid));
				$newInvoice = $txInvoice;
				if( $oldInvoice != $newInvoice){
					if($newInvoice){
						if( $db->getValue('po','count(invoice)',array('invoice'=>$newInvoice),'AND ref_id <> "'.$ref_id.'"') )//Check if invoice exist other than this po.
							functions::say("Warning: Invoice already used!");
					}
					$qVPP = $db->select('voucher_po_payment','*',array('po_id'=>$poid));
					while($rVPP = $db->fetch_array($qVPP)):
						$vpInvoice = ($newInvoice) ? $newInvoice : ' -- ';
						$db->update('voucher_particular',array('vp_title'=>'P.O. PAYMENT - Invoice: '.$vpInvoice),array('vp_id'=>$rVPP['vp_id']));
					endwhile;
				}
			}

			$db->update('po',array('proj_id'=>$txProj,'supplierID'=>$txPayee,'dep_id'=>$dep_id,'category_id'=>$txItemCat,'po_date'=>$txBdate,'purchaser'=>$txPreparedBy,'invoice'=>$txInvoice,'approved_by'=>$txApprove,'terms'=>$txTerms,'proj_detail'=>$txProjDetail,'po_type'=>$selPoType,'remarks'=>$remarks,'payment_term'=>$txPaymentTerm,'conforme'=>$txConforme),array('po_id'=>$poid));
			if($po_type=='parts' && $selPoType!='parts' ){//if old po type is parts and the new po type is not parts, then remove the po record from equip_repair table
				$db->delete('equip_repair',array('po_id'=>$poid));
			}

			$_SESSION['notif_success']='Changes Saved!';
			require_once('../class/class-po-history.php');
			PO_history::poModify($poid,$user_id);	
		}
		functions::sendTo(functions::pageName().'?po_id='.functions::encode($poid));
		die();
	}
	else
		functions::say('Please fill up the form properly!');
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Purchase Order Form</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="po_print_preview.php?po_id=<?php echo functions::encode($po_id);?>">Print Preview</a></li>
				<li class="active"><a href="#" style="opacity:.8">Edit</a></li>
			</ul>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<input type="hidden" name="poid" id="poid" value="<?php echo functions::encode($po_id);?>">
				<table width="60%" align="center" border="0" style="background-color:#E4E1E1">
					<tr><td>&nbsp;</td></tr>
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
									<select name="selPoType" id="selPoType" style="width:400px;" required>
										<option value="material" <?php if($po_type=='material'){echo 'selected="selected"';} ?>>Materials / Supplies</option>
										<option value="equipment" <?php if($po_type=='equipment'){echo 'selected="selected"';} ?>>Tools / Equipments</option>
										<option value="parts" <?php if($po_type=='parts'){echo 'selected="selected"';} ?>>Tools / Equipments / Machine / Parts / Repair</option>
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
								<label class="control-label" for="inputSuccess">Repair / Damage Description</label>
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
									<select name="txPayee" id="txPayee" data-rel="chosen" style="width:400px;">
										<option value="">--select--</option>
										<?php $qSup = $db->select('supplier','*',array(),'ORDER BY name');
										while($rSup = $db->fetch_array($qSup)):
										?>
										<option value="<?php echo $rSup['supplierID']?>" <?php if($txPayee==$rSup['supplierID'])echo 'selected="selected"';?>><?php echo ($rSup['name']);?></option>
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
									<select name="selProj" id="selProj" data-rel="chosen" style="width:400px;">
										<option value="">--select--</option>
										<?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
										while($rProj = $db->fetch_array($qProj)):
										?>
										<option value="<?php echo $rProj['proj_id']?>" <?php if($proj_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?></option>
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
										<?php 
										$qCat = $db->select('item_deduction','*',array(),'ORDER BY name');
										while($rCat = $db->fetch_array($qCat)):
										?>
										<option value="<?php echo $rCat['item_id']?>" <?php if($txItemCat==$rCat['item_id'])echo 'selected="selected"';?>><?php echo $rCat['name']?></option>
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
									<input type="text" name="txProjDetail" id="txProjDetail" style="width:300px;" value="<?php echo $txProjDetail;?>" />
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
									<input type="text" name="txConforme" id="txConforme" style="width:300px;" value="<?php echo $conforme?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesConfrme;?>]' />
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group hidden-phone">
								<label class="control-label" for="textarea2">Terms of Payment</label>
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
									<textarea class="cleditor" id="txTerms" name="txTerms" rows="2"><?php echo $txTerms;?></textarea>
								</div>
							</div>
						</td>
					</tr>
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td>
							<div class="control-group hidden-phone">
								<label class="control-label" for="textarea2">Remarks</label>
								<div class="controls">
									<textarea id="txRemarks" name="txRemarks" rows="2"><?php echo $txRemarks?></textarea>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="controls">
								<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-small btn-primary">
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
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="../js/jquery.ui.touch-punch.js"></script>
<script src="../js/modernizr.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.cookie.js"></script>
<script src='../js/fullcalendar.min.js'></script>
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
<script>
$(document).ready(function(){
	var res = false;

	<?php if($po_type=='parts'){ ?>
	$('#partsEqp').show();
	$('#partsDesc').show();
	<?php }else{ ?>
	$('#partsEqp').hide();
	$('#partsDesc').hide();
	<?php } ?>

	$('#selPoType').change(function(){
		if( $(this).val()=='parts' ){
			$('#partsEqp').show('fade');
			$('#partsDesc').show('fade');
		}
		else{
			$('#partsEqp').hide('fade');
			$('#partsDesc').hide('fade');
		}
	});

	$('#btnSave').click(function(){
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
		else if( $('#selProj').val()=="" ){
			$('#msgProject').html("Project Required!");
			$('#selProj').focus();
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
		yearRange:'2010:<?php echo date('Y')+1 ?>'
	});
});
</script>
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<!-- end: JavaScript-->
</body>
</html>