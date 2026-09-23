<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$arrProj = ( isset($_SESSION['po_arr_proj']) && is_array($_SESSION['po_arr_proj']) ) ? $_SESSION['po_arr_proj'] : array();
$projID=0;
if( isset($_REQUEST['dp']) && !empty($_REQUEST['dp']) ){
	$arrProj = functions::delete_array($arrProj,functions::decode($_REQUEST['dp']));
	$_SESSION['po_arr_proj'] = $arrProj;
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnAddProj']) && !empty($_POST['btnAddProj']) ){
	if( isset($_POST['selProj']) && !empty($_POST['selProj']) ){
		$arrProj = functions::insert_array($arrProj,functions::decode($_POST['selProj']));
		$_SESSION['po_arr_proj'] = $arrProj;
	}
	functions::sendTo(functions::pageName());
	die();
}

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
	<title>Multiple P.O. Add</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/formatCurrency.js"></script>
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
$txTerms = ( isset($_POST['txTerms']) && !empty($_POST['txTerms']) ) ? $_POST['txTerms'] : '';
$txPayee = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : '';
$txInvoice = ( isset($_POST['txInvoice']) && !empty($_POST['txInvoice']) ) ? $_POST['txInvoice'] : '';
$txBdate = ( isset($_POST['txPODate']) && functions::valid_date($_POST['txPODate']) ) ? $_POST['txPODate'] : NULL;
$txPreparedBy = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? $_POST['txPreparedBy'] : '';
$txApprove = ( isset($_POST['txApprove']) && !empty($_POST['txApprove']) ) ? $_POST['txApprove'] : '';
$txProjDetail = ( isset($_POST['txProjDetail']) && !empty($_POST['txProjDetail']) ) ? $_POST['txProjDetail'] : '';
$txItemCat = ( isset($_POST['txItemCat']) && !empty($_POST['txItemCat']) ) ? $_POST['txItemCat'] : 0;
$txReceive = ( isset($_POST['txReceive']) && !empty($_POST['txReceive']) ) ? $_POST['txReceive'] : 0;
$dep_id = ( isset($_POST['selDep']) && !empty($_POST['selDep']) ) ? trim($_POST['selDep']) : NULL;
$remarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : '';
$txPaymentTerm = ( isset($_POST['txPaymentTerm']) && !empty($_POST['txPaymentTerm']) ) ? trim($_POST['txPaymentTerm']) : '';
$txConforme = ( isset($_POST['txConforme']) && !empty($_POST['txConforme']) ) ? trim($_POST['txConforme']) : "";

if( isset($_POST['btnCreate']) ){
	$insertID=0;
	$arrInsert = array();
	$proj=0;
	$chkProj = $arrProj;
	$numOfProj=0;
	if(is_array($arrProj)){
		$proj=1;
		$numOfProj = count($arrProj);
	}
	if( $proj && $txPayee && $txBdate && $txPreparedBy && $txItemCat){
		if( $txInvoice && $db->getValue('po','count(*)',array('invoice'=>$txInvoice)) ){
			functions::say("Warning: Invoice already used!");
		}
		$insertCount=1;
		$ref_id=0;
		$po_count=0;
		foreach($chkProj as $projID):
			$po_count++;
			$insertID = $db->insert('po',array('proj_id'=>$projID,'supplierID'=>$txPayee,'dep_id'=>$dep_id,'category_id'=>$txItemCat,'po_date'=>$txBdate,'purchaser'=>$txPreparedBy,'invoice'=>$txInvoice,'approved_by'=>$txApprove,'terms'=>$txTerms,'proj_detail'=>$txProjDetail,'ref_id'=>$ref_id,'po_type'=>'material','remarks'=>$remarks,'payment_term'=>$txPaymentTerm,'conforme'=>$txConforme));

			$ref_id = ($ref_id===0) ? $db->getValue('po','concat(substring(po_date,1,4),substring(po_date,6,2),po_id) as dte',array('po_id'=>$insertID)) : $ref_id;
			$po_no = ( $numOfProj > 1 ) ? $ref_id.'-'.$po_count : $ref_id;
			$db->update('po',array('ref_id'=>$ref_id,'po_no'=>$po_no),array('po_id'=>$insertID));
			require_once('../class/class-po-history.php');
			PO_history::poCreate($insertID,$user_id);
		endforeach;
		$_SESSION['notif_success']='New Multiple P.O. Successfully Created!';
		functions::sendTo('po_list_multiple.php?ref_id='.functions::encode($ref_id));
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
			<h2><i class="halflings-icon white list"></i><span class="break"></span>Purchase Order Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post" id="poForm" name="poForm">
				<table width="60%" align="center" border="0" style="background-color:#E4E1E1">
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Project</label>
								<div class="controls">
									<table border="0" width="99%">
										<tr>
											<td>
												<table border="0" width="100%">
													<tr>
														<td>
															<select name="selProj" id="selProj" data-rel="chosen" style="width:550px;">
																<option value="">--select--</option>
																<?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
																while($rProj = $db->fetch_array($qProj)):
																?>
																<option value="<?php echo functions::encode($rProj['proj_id'])?>"><?php echo ucwords(strtolower($rProj['proj_name']));?></option>
																<?php endwhile;?>
															</select>
														</td>
														<td><input type="submit" name="btnAddProj" id="btnAddProj" value="Add" class="btn btn-small btn-primary"></td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td>
												<table border="1" width="100%">
													<?php foreach($arrProj as $projID):?>
													<tr>
														<td style="padding: 5px 0px 5px 3px"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$projID));?></td>
														<td>
															<div align="center"><a id="del<?php echo $projID;?>" class="btn btn-mini btn-danger" title="Remove this Project" data-rel="tooltip" href="<?php echo functions::pageName()?>?dp=<?php echo functions::encode($projID);?>"><i class="halflings-icon white trash"></i></a></div>
														</td>
													</tr>
													<?php endforeach;?>
												</table>
											</td>
										</tr>
									</table>
									<span class="help-inline warning" id="msgProject" style="font-weight:bold;" name="msgProject"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">P.O Date</label>
								<div class="controls">
									<input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txPODate" id="txPODate" value="<?php echo $txBdate ?>">
									<span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
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
										<option value="<?php echo $rSup['supplierID']?>"><?php echo ($rSup['name']);?></option>
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
								<label class="control-label" for="inputSuccess">Department</label>
								<div class="controls">
									<select name="selDep" id="selDep" data-rel="chosen" style="width:500px;">
										<option value="">--select--</option>
										<?php $qDep = $db->select('department','*',array(),'ORDER BY dep_desc');
										while($rDep = $db->fetch_array($qDep)):
										?>
										<option value="<?php echo $rDep['dep_id']?>"><?php echo $rDep['dep_desc'].' ('.$rDep['dep_name'].')';?></option>
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
										<option value="<?php echo $rCat['item_id']?>" <?php if('MATERIALS'==$rCat['name'])echo 'selected="selected"';?>><?php echo $rCat['name']?></option>
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
									<input type="text" name="txProjDetail" id="txProjDetail" style="width:300px;" value="" />
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Invoice #</label>
								<div class="controls">
									<input type="text" name="txInvoice" id="txInvoice" style="width:300px;" value="" />
									<span class="help-inline warning" id="msgInvoice" style="font-weight:bold;" name="msgInvoice"></span>
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
										<option value="<?php echo $rPrep['user_id']?>"><?php echo strtoupper($rPrep['lname'].', '.$rPrep['fname']);?></option>
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
										<option value="<?php echo $rApprove['user_id']?>"><?php echo strtoupper($rApprove['lname'].', '.$rApprove['fname']);?></option>
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
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Terms of Payment</label>
								<div class="controls">
									<input type="text" name="txPaymentTerm" id="txPaymentTerm" style="width:300px;" value="<?php echo $txPaymentTerm;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPaymentTerm;?>]' />
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
										<div style="font-family: Arial, Verdana; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: normal; font-size: 10pt;"><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;"><b>TERMS AND CONDITIONS:</b></span></font></div><div style="font-family: Arial, Verdana; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: normal; font-size: 10pt;"><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;"><b><br></b></span></font></div><div style="font-family: Arial, Verdana; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: normal; text-align: center;"><b style=""><span lang="EN-PH" style="line-height: 107%; font-family: Calibri, sans-serif; color: red;">FOR PICK-UP ITEMS</span></b></div><div style=""><ol style=""><li style=""><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">Enumerated items in this order be ready one (1) hour before it will be picked-up by our representative;</span></font></li><li style=""><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">Cost and Quantity ordered is not subject to change without prior notice and approval from the herein purchaser;</span></font></li><li style=""><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">This item is for PICK-UP with;</span><br><span style="font-size: 13.3333px;">• with check on date with ___ days clearing<br>•&nbsp;with check on Post-dated with ___ days of terms</span></font><br style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;"><span style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;">•&nbsp;</span><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">with cash only</span></font><br style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;"><span style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;">•&nbsp;</span><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">without check with ____ days of terms;</span></font></li><li style=""><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">Price is inclusive of 12% VAT and require issuance of official receipt or Sales Invoice;</span></font></li><li style=""><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">This transaction is subject to __% expanded Withholding Tax, BIR Form 2307 is issued upon payment;</span></font></li><li style=""><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">This item is priority to pick-up on or before ten (10) days after the approval of PO.</span></font></li></ol><div><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;"><br></span></font></div><div><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;"><br></span></font></div><div><br></div><div><div style="font-family: Arial, Verdana; font-size: 10pt;"><font color="#333333" face="Tahoma"><b><br></b></font></div><div style="font-family: Arial, Verdana; text-align: center;"><b><span lang="EN-PH" style="line-height: 17.12px; font-family: Calibri, sans-serif; color: red;">FOR DELIVERY ITEMS</span></b></div><div><ol><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">Enumerated items in this order be ready one (1) hour before it will be picked-up by our representative;</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">Cost and Quantity ordered is not subject to change without prior notice and approval from the herein purchaser;</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">This item is for DIRECT DELIVERY to __________________ with;</span><br><span style="font-size: 13.3333px;">•&nbsp;with check on date with ___ days clearing<br>•&nbsp;with check on Post-dated with ___ days of terms</span></font><br style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;"><span style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;">•&nbsp;</span><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">with cash only</span></font><br style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;"><span style="color: rgb(51, 51, 51); font-family: Tahoma; font-size: 13.3333px;">•&nbsp;</span><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">without check with ____ days of terms;</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">All risk of loss shall remain with seller until goods have actually been received and accepted by our representative at the applicable destination.</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">If the total or any portion of the goods received either exceeds or falls below the quantities ordered, we have the right to reject and return, any such delivery or portion thereof at seller’s expenses for transportation both ways and all related Labor and Parking Cost;</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">Price is inclusive of 12% VAT and require issuance of official receipt or Sales Invoice;</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">This transaction is subject to __% expanded Withholding Tax, BIR Form 2307 is issued upon payment;</span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">This item is priority to pick-up on or before ten (10) days after the approval of PO.</span></font></li></ol><div><br></div></div></div></div>
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
									<textarea id="txRemarks" name="txRemarks" rows="2"></textarea>
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
function checkChecked(formname) {
	var anyBoxesChecked = false;
	$('#' + formname + ' input[type="checkbox"]').each(function() {
		if ($(this).is(":checked")) {
			anyBoxesChecked = true;
		}
	});
	return anyBoxesChecked;
}

$(document).ready(function(){
	var res = false;

	$('#btnAddProj').click(function(){
		if( $('#selProj').val()=="" ){
			$('#msgProject').html("Please select Project!");
			res=false;
		}
		else
			res=true;
		return res;
	});
	$('#btnCreate').click(function(){
		$('#msgBdate').html("");
		$('#msgPayee').html("");
		$('#msgProject').html("");
		$('#msgPrepare').html("");
		$('#msgApprove').html("");
		var prj = "<?php echo $projID?>";

		if( prj==0 ){
			$('#msgProject').html("Project Required!");
			res=false;
		}
		else if( $('#bdYear').val()=="" || $('#bdMon').val()=="" || $('#bdDay').val()=="" ){
			$('#msgBdate').html("Date Required!");
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
<!-- end: JavaScript-->
</body>
</html>