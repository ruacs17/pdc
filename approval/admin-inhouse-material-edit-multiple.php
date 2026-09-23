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
$txRefID = ( isset($_SESSION['RefID']) && !empty($_SESSION['RefID']) ) ? trim($_SESSION['RefID']) : '';
$im_id = (isset($_REQUEST['im_id']) && !empty($_REQUEST['im_id']) ) ? functions::decode($_REQUEST['im_id']) : 0;
$arrProj = ( isset($_SESSION['im_arr_proj']) && is_array($_SESSION['im_arr_proj']) ) ? $_SESSION['im_arr_proj'] : array();
$txDate='';$proj_id='';$mon='';$day='';$year='';$discount='';$brand=''; $address='';$prepared_by='';$approved_by='';$checked_by='';$delivered_by='';$received_by='';$terms='';$is_paid=0; $payee='';$txItemCat=0;$txPurpose='';$txRemarks='';

$txRequestRefID = ( isset($_REQUEST['ref_id']) && !empty($_REQUEST['ref_id']) ) ? functions::decode($_REQUEST['ref_id']) : '';
if( (isset($_POST['btnSearch']) && isset($_POST['txRefID']) && !empty($_POST['txRefID'])) || $txRequestRefID ){
	$txRefID = ( isset($_POST['txRefID']) && !empty($_POST['txRefID']) ) ? trim($_POST['txRefID']) : $txRequestRefID;
	$_SESSION['RefID']=$txRefID;
	$im_id = $db->getValue('inhouse_material','im_id',array('ref_id'=>$txRefID));

	unset($_SESSION['im_arr_proj']);
	$arrProj=array();
	$qProjs = $db->select('inhouse_material','proj_id',array('ref_id'=>$txRefID));
	while($rProjs = $db->fetch_array($qProjs)):
		$arrProj[] = $rProjs['proj_id'];
	endwhile;
	$_SESSION['im_arr_proj'] = $arrProj;
}

if( isset($_REQUEST['dp']) && !empty($_REQUEST['dp']) ){
	$arrProj = functions::delete_array($arrProj,functions::decode($_REQUEST['dp']));
	$_SESSION['im_arr_proj'] = $arrProj;
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnAddProj']) && !empty($_POST['btnAddProj']) ){
	if( isset($_POST['selProj']) && !empty($_POST['selProj']) ){
		$arrProj = functions::insert_array($arrProj,functions::decode($_POST['selProj']));
		$_SESSION['im_arr_proj'] = $arrProj;
	}
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnAddOtherProj']) && !empty($_POST['btnAddOtherProj']) ){
	if( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ){
		$arrProj = functions::insert_array($arrProj,$_POST['txPayee']);
		$_SESSION['im_arr_proj'] = $arrProj;
	}
	functions::sendTo(functions::pageName());
	die();
}

if( $txRefID ){
	$q = $db->select('inhouse_material','*',array('ref_id'=>$txRefID));
	$r = $db->fetch_array($q);
	$address = $r['address'];
	$prepared_by = $r['prepared_id'];
	$approved_by = $r['approved_id'];
	$checked_by = $r['checked_id'];
	$delivered_by = $r['delivered_id'];
	$received_by = $r['received_id'];
	$proj_id = $r['proj_id'];
	$txDate = $r['im_date'];
	$terms = $r['terms'];
	$is_paid = $r['is_paid'];
	$payee = $r['payee'];
	$txItemCat = $r['category_id'];
	$txRemarks = $r['remarks'];
	$txPurpose = $r['purpose'];
}

if( isset($_POST['btnSave']) ){
 
	$arr = array();
	$insertID=0;
	$arrInsert = array();
	$proj=0;
	$chkProj = $arrProj;
	$numOfProj=0;
	if(is_array($chkProj)){
		$proj=1;
		$numOfProj = count($arrProj);
	}

	$txRefID = ( isset($_POST['txRefID']) && !empty($_POST['txRefID']) ) ? $_POST['txRefID'] : '';
	$txCheckedBy = ( isset($_POST['txCheckedBy']) && !empty($_POST['txCheckedBy']) ) ? $_POST['txCheckedBy'] : NULL;
	$txDeliveredBy = ( isset($_POST['txDeliveredBy']) && !empty($_POST['txDeliveredBy']) ) ? $_POST['txDeliveredBy'] : NULL;
	$txReceivedBy = ( isset($_POST['txReceivedBy']) && !empty($_POST['txReceivedBy']) ) ? $_POST['txReceivedBy'] : NULL;
	$txAddress = ( isset($_POST['txAddress']) && !empty($_POST['txAddress']) ) ? $_POST['txAddress'] : '';
	$txTerms = ( isset($_POST['txTerms']) && !empty($_POST['txTerms']) ) ? $_POST['txTerms'] : ''; 
	$txPayment = ( isset($_POST['txPayment']) && !empty($_POST['txPayment']) ) ? $_POST['txPayment'] : '';
	$txItemCat = ( isset($_POST['txItemCat']) && !empty($_POST['txItemCat']) ) ? $_POST['txItemCat'] : 0;
	$txDate = ( isset($_POST['txMRDate']) && functions::valid_date($_POST['txMRDate']) ) ? $_POST['txMRDate'] : NULL;
	$selPreparedBy = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? $_POST['txPreparedBy'] : NULL;
	$selApprovedBy = ( isset($_POST['txApprovedBy']) && !empty($_POST['txApprovedBy']) ) ? $_POST['txApprovedBy'] : NULL;
	$txPurpose = ( isset($_POST['txPurpose']) && !empty($_POST['txPurpose']) ) ? trim($_POST['txPurpose']) : '';
	$txRemarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : '';

	if( $txDate && $txPayment && $txItemCat && $numOfProj ){
		#getting items from po
		$data = array();
		$im_id = $db->getValue('inhouse_material','im_id',array('ref_id'=>$txRefID));
		$countRecentProject = $db->getValue('inhouse_material','count(im_id)',array('ref_id'=>$txRefID));
		$q = $db->select('inhouse_material_item','*',array('im_id'=>$im_id));
		$countItem=0;
		while( $r = $db->fetch_array($q) ):
			$item = $r['item'];
			$qty = ($countRecentProject * $r['quantity']) / count($chkProj);
			$unit = $r['unit'];
			$brand = $r['brand'];
			$cost = $r['cost'];
			$discount = $r['discount'];
			$location = $r['location'];

			$data[$countItem]['item']=$item;
			$data[$countItem]['quantity']=$qty;
			$data[$countItem]['unit']=$unit;
			$data[$countItem]['brand']=$brand;
			$data[$countItem]['location']=$location;
			$data[$countItem]['cost']=$cost;
			$data[$countItem]['discount']=$discount;
			$countItem++;
		endwhile;
		#end getting items from P.O.

		$db->delete('inhouse_material',array('ref_id'=>$txRefID));
		$arr = array('im_date'=>$txDate,'prepared_id'=>$selPreparedBy,'approved_id'=>$selApprovedBy,'address'=>$txAddress,'checked_id'=>$txCheckedBy,'delivered_id'=>$txDeliveredBy,'received_id'=>$txReceivedBy,'terms'=>$txTerms,'category_id'=>$txItemCat,'purpose'=>$txPurpose,'remarks'=>$txRemarks,'is_paid'=>$txPayment);

		$insertCount=1;
		$ref_id=0;
		$im_count=0;
		foreach($chkProj as $projID):
			$im_count++;
			if( $db->getValue('project','count(proj_id)',array('proj_id'=>$projID)) ){
				$arr = array_merge($arr,array('proj_id'=>$projID));
				$txPayee='';
			}
			else{
				$txPayee = $projID;
				$arr = array_merge($arr,array('payee'=>$txPayee));
			}

			$im_id = $db->insert('inhouse_material',$arr);

			$ref_id = ($ref_id===0) ? $db->getValue('inhouse_material','concat(substring(im_date,1,4),substring(im_date,6,2),im_id) as dte',array('im_id'=>$im_id)) : $ref_id;

			$po_no = $ref_id.'-'.$im_count;
			if( $numOfProj > 1 )
				$db->update('inhouse_material',array('ref_id'=>$ref_id,'im_no'=>$po_no),array('im_id'=>$im_id));
			else
				$db->update('inhouse_material',array('ref_id'=>$ref_id,'im_no'=>$ref_id),array('im_id'=>$im_id));

			#insert Item from Old P.O.
			if($countItem > 0){
				$arrInsert=array();
				foreach($data as $row):
					$arrRow=array('im_id'=>$im_id);
					foreach($row as $column => $value):
						$arrRow = array_merge(array($column=>$value),$arrRow);
					endforeach;
					$q = $db->insertPrint('inhouse_material_item',$arrRow);
					$db->query($q);
				endforeach;
			}
		endforeach;
		$_SESSION['notif_success']='Changes saved!';
		functions::sendTo('admin-inhouse-material-item-manage-multiple.php?ref_id='.functions::encode($ref_id));
		die();
	}
}
$qPayee = $db->query('SELECT DISTINCT payee FROM inhouse_material');
$namesPayee='';
while($rPayee=$db->fetch_array($qPayee)):
	$string = preg_replace("/'/",'"',$rPayee['payee']);
	$namesPayee .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesPayee .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Material Charge Update Multiple</title>
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
	<style>.tdSpace{padding: 12px 0px 4px 0px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Material Charge Update Multiple</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post">
					<table width="70%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<th width="35%" align="right" scope="row">&nbsp;</th>
							<td width="2%">&nbsp;</td>
							<td width="50%">&nbsp;</td>
							<td width="13%">&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Order Number</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<input type="text" name="txRefID" id="txRefID" style="width:300px;" value="<?php echo $txRefID;?>" />
								<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-small btn-primary">
								<span class="help-inline warning" id="msgInvoice" style="font-weight:bold;" name="msgInvoice"></span>
							</td>
						</tr>
<?php if($txRefID){?>
						<tr>
							<th align="right" scope="row">Charge To</th>
							<td>&nbsp;</td>
							<td style="padding: 25px 0px 4px 0px;">
								<table border="0" width="99%">
									<tr>
										<td>
											<table border="0">
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
													<td><input type="submit" name="btnAddProj" id="btnAddProj" value="Add" class="btn btn-mini btn-primary"></td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td>
											<table border="0">
												<tr>
													<td style="padding: 10px 5px 4px 4px"><input type="text" name="txPayee" id="txPayee" style="width:525px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPayee;?>]'/></td>
													<td><input type="submit" name="btnAddOtherProj" id="btnAddOtherProj" value="Add" class="btn btn-mini btn-primary"></td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td>
											<table border="1">
												<?php 
												foreach($arrProj as $projID):
												$projName = $db->getValue('project','proj_name',array('proj_id'=>$projID));
												?>
												<tr>
													<td style="padding: 5px 0px 5px 3px"><?php echo ($projName) ? $projName : $projID;?></td>
													<td><div align="center"><a id="del<?php echo $projID;?>" class="btn btn-mini btn-danger" title="Remove this Project" data-rel="tooltip" href="<?php echo functions::pageName()?>?dp=<?php echo functions::encode($projID);?>"><i class="halflings-icon white trash"></i></a></div></td>
												</tr>
												<?php endforeach;?>
											</table>
										</td>
									</tr>
								</table>
								<span class="help-inline warning" id="msgProject" style="font-weight:bold;" name="msgProject"></span>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Category</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<select name="txItemCat" id="txItemCat" data-rel="chosen" style="width:400px;">
									<?php 
									$qCat = $db->select('item_deduction','*',array(),'ORDER BY name');
									while($rCat = $db->fetch_array($qCat)):
									?>
									<option value="<?php echo $rCat['item_id']?>" <?php if($txItemCat==$rCat['item_id'])echo 'selected="selected"';?>><?php echo $rCat['name']?></option>
									<?php endwhile;?>
								</select>
								<span class="help-inline warning" id="msgItemCat" style="font-weight:bold;" name="msgItemCat"></span>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Address</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left"><input type="text" name="txAddress" id="txAddress" style="width:300px;" value="<?php echo $address?>" /></td>
						</tr>
						<tr>
							<th align="right" scope="row">Purchase Date</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txMRDate" id="txMRDate" value="<?php echo $txDate ?>">
								<span class="help-inline warning" style="font-weight:bold;" id="msgTxdate" name="msgTxdate"></span>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Prepared By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<div align="left">
									<select name="txPreparedBy" id="txPreparedBy" data-rel="chosen" style="width:300px; text-align:left;">
										<option value="">--select--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($prepared_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Approved By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<div align="left">
									<select name="txApprovedBy" id="txApprovedBy" data-rel="chosen" style="width:300px; text-align:left;">
										<option value="">--select--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($approved_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Checked By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<div align="left">
									<select name="txCheckedBy" id="txCheckedBy" data-rel="chosen" style="width:300px; text-align:left;">
										<option value="">--select--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($checked_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Delivered By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<div align="left">
									<select name="txDeliveredBy" id="txDeliveredBy" data-rel="chosen" style="width:300px; text-align:left;">
										<option value="">--select--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($delivered_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Received By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<div align="left">
									<select name="txReceivedBy" id="txReceivedBy" data-rel="chosen" style="width:300px; text-align:left;">
										<option value="">--select--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($received_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<th>Terms and Conditions</th>
							<td>&nbsp;</td>
							<td><textarea class="cleditor" id="txTerms" name="txTerms" rows="2"><?php echo $terms;?></textarea></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<th align="right" scope="row">Payment Status</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<select name="txPayment" id="txPayment">
									<option value="">--select--</option>
									<option value="2" <?php if($is_paid=='2')echo 'selected="selected"';?>>Paid</option>
									<option value="1" <?php if($is_paid=='1')echo 'selected="selected"';?>>Unpaid</option>
								</select>
								<span class="help-inline warning" id="msgTxPayment" style="font-weight:bold;" name="msgTxPayment"></span>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Purpose</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left"><input type="text" name="txPurpose" id="txPurpose" style="width:300px;" value="<?php echo $txPurpose?>" /></td>
						</tr>
						<tr>
							<th align="right" scope="row">Remarks</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left"><textarea id="txRemarks" name="txRemarks" rows="2" style="width:300px;"><?php echo $txRemarks?></textarea></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td class="tdSpace" colspan="2" align="left"><input type="submit" name="btnSave" id="btnSave" value="Save Purchase" class="btn btn-small btn-primary"></td>
						</tr>
<?php }#if($txRefID)?>
					</table>
				</form>
			</div>
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
<script src="../js/wxhBox.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
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

    $('#btnSave').click(function(){
		var prj = "<?php echo count($arrProj)?>";
		$('#msgProject').html("");
		$('#msgTxdate').html("");
		$('#msgTxPayment').html("");

		if( prj==0 ){
			$('#msgProject').html("Project Required!");
			res=false;
		}
		else if( $('#txItemCat').val()=="" ){
			$('#msgItemCat').html("Category Required!");
			$('#txItemCat').focus();
			res=false;
		}
		else if( $('#txYear').val()=="" || $('#txMon').val()=="" || $('#txdDay').val()=="" ){
			$('#msgTxdate').html("Date Required!");
			res=false;
		}
		else if( $('#txPayment').val()=="" ){
			$('#msgTxPayment').html("Payment Status Required!");
			res=false;
		}
		else{
			if(confirm('Do you want to save this information?'))
				res=true;
			else
				res=false;
		}
		return res;
	});
	$('#txMRDate').datepicker({
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
<!-- end: JavaScript-->
</body>
</html>