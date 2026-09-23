<?php require_once('templ_up.php');?>
<?php
$txcMon='';$txcDay='';$txcYear='';$txbYear='';$txbDay='';$txbMon='';$txPayee='';$txCheque='';$txVoNo='';$txPrepare='';$txChecker='';$txApprover='';$receiver='';$txVoType='';$txRemark='';$txWitholding='';$witholding_vat='';$txPF='';
$txPayee = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : NULL;
$txCheque = ( isset($_POST['txCheque']) && !empty($_POST['txCheque']) ) ? $_POST['txCheque'] : '';
$txVoNo = ( isset($_POST['txVoNo']) && !empty($_POST['txVoNo']) ) ? $_POST['txVoNo'] : '';
$txBdate = ( isset($_POST['txVoDate']) && functions::valid_date($_POST['txVoDate']) ) ? $_POST['txVoDate'] : NULL;
$txCdate = ( isset($_POST['txChqDate']) && functions::valid_date($_POST['txChqDate']) ) ? $_POST['txChqDate'] : NULL;

$txPrepare = ( isset($_POST['txPrepare']) && !empty($_POST['txPrepare']) ) ? $_POST['txPrepare'] : NULL;
$txChecker = ( isset($_POST['txChecker']) && !empty($_POST['txChecker']) ) ? $_POST['txChecker'] : NULL;
$txApprover = ( isset($_POST['txApprover']) && !empty($_POST['txApprover']) ) ? $_POST['txApprover'] : NULL;
$txVoType = ( isset($_POST['txVoType']) && !empty($_POST['txVoType']) ) ? $_POST['txVoType'] : NULL;
$txRemark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? $_POST['txRemark'] : '';
$txWitholding = ( isset($_POST['txWitholding']) && !empty($_POST['txWitholding']) ) ? $_POST['txWitholding'] : 0;
$witholding_vat = ( isset($_POST['selWVat']) && !empty($_POST['selWVat']) ) ? $_POST['selWVat'] : 1;
$receiver = (isset($_POST['txReceiver'])) ? $_POST['txReceiver'] : '';
$txPF = ( isset($_POST['txPF']) && !empty($_POST['txPF']) ) ? $_POST['txPF'] : 0;

$arrInsert = array();
if( isset($_POST['btnCreate']) ){
	$insertID=0;

	$arrInsert = array('witholding_vat'=>$witholding_vat,'voucher_no'=>$txVoNo,'vdate'=>$txBdate,'cheque_date'=>$txCdate,'supplierID'=>$txPayee,'vt_id'=>$txVoType,'prof_fee'=>$txPF,'witholding_tax'=>$txWitholding,'remarks'=>$txRemark,'cheque_id'=>$txCheque,'receivedBy'=>$receiver);

	if($txPrepare){
		if( $db->getValue('users','count(user_id)',array('user_id'=>$txPrepare)) ){
			$arrInsert = array_merge($arrInsert,array('preparedBy'=>$txPrepare));
		}
	}
	if($txChecker){
		if( $db->getValue('users','count(user_id)',array('user_id'=>$txChecker)) ){
			$arrInsert = array_merge($arrInsert,array('checkedBy'=>$txChecker));
		}
	}
	if($txApprover){
		if( $db->getValue('users','count(user_id)',array('user_id'=>$txApprover)) ){
			$arrInsert = array_merge($arrInsert,array('approvedBy'=>$txApprover));
		}
	}

	if($txPayee && $txBdate && $txPrepare){
		$allowed=1;
		if($txVoNo){
			if( $db->getValue('voucher','count(*)',array('voucher_no'=>$txVoNo)) )
				$allowed=0;
		}
		if($allowed){
			$insertID = $db->insert('voucher',$arrInsert);
			if($insertID){
				$_SESSION['notif_success']='New Voucher Successfully Created!';
				functions::sendTo('voucher_view.php?vid='.functions::encode($insertID));
				die();
			}
		}
		else
			functions::say("Voucher Number already exist!");
	}
}
$qList = $db->select('voucher','DISTINCT receivedBy',array());
$namesReceivedBy='';
while($rList=$db->fetch_array($qList)):
	$namesReceivedBy .= '"'.$rList['receivedBy'].'",';
endwhile;
$namesReceivedBy .= '"--"';
?>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Voucher Create Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="right"><a id="adc" href="#" class="btn btn-info btn-small btn-setting thickbox" onclick="showThis(this.id,'voucher_add_online.php?','Create Voucher for Online Payments')">Create Voucher for Online Payments</a></div><br>      
				<table width="60%" align="center" border="0" style="background-color:#E4E1E1">
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Voucher Type</label>
									<div class="controls">
									<select name="txVoType" id="txVoType" data-rel="chosen" style="width:300px;">
										<option value="">--select--</option>
										<?php 
										$qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
										while($rVtype = $db->fetch_array($qVtype)):
										?>
										<option value="<?php echo $rVtype['vt_id']?>" <?php if($txVoType==$rVtype['vt_id'])echo 'selected="selected"';?> ><?php echo $rVtype['vt_name']?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgVoType" style="font-weight:bold;" name="msgVoType"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Voucher No:</label>
								<div class="controls">
									<input type="text" name="txVoNo" id="txVoNo" style="width:300px;" value="<?php echo $txVoNo;?>" />
									<span class="help-inline warning" id="msgVono" style="font-weight:bold;" name="msgVono"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Voucher Date</label>
								<div class="controls">
									<input type="text" style="width: 90px;" name="txVoDate" id="txVoDate" placeholder="YYYY-MM-DD" value="<?php echo $txBdate ?>">
									<span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Payee</label>
								<div class="controls">
									<select name="txPayee" id="txPayee" data-rel="chosen" style="width:400px;" onChange="searchVID(this.value)">
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
								<label class="control-label" for="inputSuccess">Cheque #</label>
								<div class="controls">
									<input type="text" name="txCheque" id="txCheque" style="width:300px;" value="<?php echo $txCheque?>" />
									<span class="help-inline warning" id="msgCheque" style="font-weight:bold;" name="msgCheque"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Cheque Date</label>
								<div class="controls">
									<input type="text" style="width: 90px;" name="txChqDate" id="txChqDate" placeholder="YYYY-MM-DD" value="<?php echo $txCdate ?>">
									<span class="help-inline warning" id="msgChequeDate" style="font-weight:bold;" name="msgChequeDate"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Prepared By</label>
								<div class="controls">
									<select name="txPrepare" id="txPrepare" data-rel="chosen" style="width:300px;">
										<option value="">--select--</option>
										<?php
										$qPrep = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND u.status="active" ORDER BY u.lname');
										while($rPrep = $db->fetch_array($qPrep)):
										?>
										<option value="<?php echo $rPrep['user_id']?>" <?php if($txPrepare==$rPrep['user_id']) echo 'selected="selected"';?> ><?php echo $rPrep['lname'].', '.$rPrep['fname']?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgPrepared" style="font-weight:bold;" name="msgPrepared"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Checker</label>
								<div class="controls">
									<select name="txChecker" id="txChecker" data-rel="chosen" style="width:300px;">
										<option value="">--select--</option>
										<?php
										$qCheck = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND u.status="active" ORDER BY u.lname');
										while($rCheck = $db->fetch_array($qCheck)):
										?>
										<option value="<?php echo $rCheck['user_id']?>" <?php if($txChecker==$rCheck['user_id']) echo 'selected="selected"';?> ><?php echo $rCheck['lname'].', '.$rCheck['fname']?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgChecker" style="font-weight:bold;" name="msgChecker"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Approval</label>
								<div class="controls">
									<select name="txApprover" id="txApprover" data-rel="chosen" style="width:300px;">
										<option value="">--select--</option>
										<?php
										$qApprove = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND u.status="active" ORDER BY u.lname');
										while($rApprove = $db->fetch_array($qApprove)):
										?>
										<option value="<?php echo $rApprove['user_id']?>" <?php if($txApprover==$rApprove['user_id']) echo 'selected="selected"';?>><?php echo $rApprove['lname'].', '.$rApprove['fname']?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgApprove" style="font-weight:bold;" name="msgAprove"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Receiver</label>
								<div class="controls">
									<input type="text" class="span6 typeahead" name="txReceiver" id="txReceiver" style="width:300px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesReceivedBy;?>]' value="<?php echo $receiver;?>">
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Witholding Tax (%)</label>
								<div class="controls">
									<input type="text" style="width:80px;" name="txWitholding" id="txWitholding" value="<?php echo $txWitholding?>" placeholder="0" onkeypress="return checkinput(this, event);">
									<select name="selWVat" id="selWVat" style="width:100px;">
										<option value="2" <?php if($witholding_vat==2)echo 'selected="selected"';?>>Non VAT</option>
										<option value="1" <?php if($witholding_vat==1)echo 'selected="selected"';?>>With VAT</option>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Professional Fee (%)</label>
								<div class="controls">
									<input type="text" style="width:80px;" name="txPF" id="txPF" value="<?php echo $txPF; ?>" placeholder="0" onkeypress="return checkinput(this, event);">
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Remarks</label>
								<div class="controls">
									<input type="text" class="span6" name="txRemark" id="txRemark" value="<?php echo $txRemark ?>">
								</div>
							</div>
						</td>
					</tr>
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
<script src="../js/formatCurrency.js"></script>
<script src="../js/inputInt.js"></script>
<script>
$(document).ready(function(){
	var res = false;
	$('#btnCreate').click(function(){  
		$('#msgVoType').html("");
		$('#msgBdate').html("");
		$('#msgPayee').html("");
		$('#msgProject').html("");
		$('#msgPrepared').html("");
		$('#msgChecker').html("");
		$('#msgApprove').html("");
		$('#msgVono').html("");

		if( $('#txVoType').val()=="" ){
			$('#txVoType').focus();
			$('#msgVoType').html("Type Required!");
			res=false;
		}
		else if( $('#txVoNo').val()=="" ){
			$('#txVoNo').focus();
			$('#msgVono').html("Voucher Number Required!");
			res=false;
		}
		else if( $('#txVoDate').val()=="" ){
			$('#txVoDate').focus();
			$('#msgBdate').html("Date Required!");
			res=false;
		}
		else if( $('#txPayee').val()=="" ){
			$('#txPayee').focus();
			$('#msgPayee').html("Supplier Required!");
			res=false;
		}
		else if( $('#txPrepare').val()=="" ){
			$('#msgPrepared').html("Required!");
			$('#txPrepare').focus();
			res=false;
		}
		else if( $('#txChecker').val()=="" ){
			$('#msgChecker').html("Required!");
			$('#txChecker').focus();
			res=false;
		}
		else if( $('#txApprover').val()=="" ){
			$('#msgApprove').html("Approval Required!");
			$('#txApprover').focus();
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
	$('#txVoDate, #txChqDate').datepicker({
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