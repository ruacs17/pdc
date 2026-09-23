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

$ref_id = (isset($_REQUEST['ref_id']) && !empty($_REQUEST['ref_id']) ) ? functions::decode($_REQUEST['ref_id']) : 0;

$q = $db->select('po','ref_id,po_date,delivery_date,supplierID,terms,payment_term,proj_detail,received,invoice,item_received_by,item_encoded_by,report_received_by,receive_no,remarks',array('ref_id'=>$ref_id),'GROUP BY ref_id');
$r = $db->fetch_array($q);
$ref_id = $r['ref_id'];
$_SESSION['notif_id_list']=$ref_id;
$vdate = $r['po_date'];
$xdate = explode('-',$vdate);
$mon = isset($xdate[1]) ? $xdate[1] : '';
$day = isset($xdate[2]) ? $xdate[2] : '';
$year = isset($xdate[0]) ? $xdate[0] : '';

$txInvoice = $r['invoice'];
$txPayee = $r['supplierID'];
$txTerms = $r['terms'];
$txProjDetail = $r['proj_detail'];
$txReceive = $r['received'];
$txRemarks = $r['remarks'];
$txPaymentTerm = $r['payment_term'];
$txReceivedBy = $r['item_received_by'];
$txEncodedBy = $r['item_encoded_by'];
$txReportBy = $r['report_received_by'];

$dlvryDate = $r['delivery_date'];
$xDDate = explode('-',$dlvryDate);
$monDelvry = isset($xDDate[1]) ? $xDDate[1] : '';
$dayDelvry = isset($xDDate[2]) ? $xDDate[2] : '';
$yearDelvry = isset($xDDate[0]) ? $xDDate[0] : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Purchase Order Received Update</title>
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
if( isset($_POST['btnSave']) && $ref_id ){
	$insertID=0;
	$arrInsert = array();
	$txInvoice = ( isset($_POST['txInvoice']) && !empty($_POST['txInvoice']) ) ? $_POST['txInvoice'] : '';
	$txEncodedBy = ( isset($_POST['txEncodedBy']) && !empty($_POST['txEncodedBy']) ) ? $_POST['txEncodedBy'] : NULL;
	$txReceivedBy = ( isset($_POST['txReceivedBy']) && !empty($_POST['txReceivedBy']) ) ? $_POST['txReceivedBy'] : NULL;
	$txReportBy = ( isset($_POST['txReportBy']) && !empty($_POST['txReportBy']) ) ? $_POST['txReportBy'] : NULL;
	$txReceive = ( isset($_POST['txReceive']) && !empty($_POST['txReceive']) ) ? $_POST['txReceive'] : 0;
	$txRemarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : '';
	$delivery_date = ( isset($_POST['txDelvryDate']) && functions::valid_date($_POST['txDelvryDate']) ) ? $_POST['txDelvryDate'] : date('Y-m-d');
	if($txReceive==0)
		$delivery_date=NULL;

	if( $ref_id ){
		$newInvoice = $txInvoice;
		$has_invoice_update_attempt_failed=0;
		$invoice_updated=0;
		$oldInvoice = $db->getValue('po','DISTINCT invoice',array('ref_id'=>$ref_id));
		if($newInvoice != $oldInvoice){//If there's new invoice
			if($newInvoice==""){
				$db->update('po',array('invoice'=>$newInvoice),array('ref_id'=>$ref_id));
				$invoice_updated=1;
			}
			if( $db->getValue('po','count(*)',array('invoice'=>$newInvoice))==0 ){
				$db->update('po',array('invoice'=>$newInvoice),array('ref_id'=>$ref_id));
				$invoice_updated=1;
			}
			else{
				functions::say('Invoice already exist!');
				$has_invoice_update_attempt_failed=1;
			}
		}
		//Changing the invoice number of particular title
		$po_type = $db->getValue('po','po_type',array('ref_id'=>$ref_id));
		if( $po_type =='service' ){
			//Don't update invoice display on voucher particular because if po is service, there is posibility that every voucher has different invoice.
		}
		else{
			//Update attached invoice on voucher particular
			$qUPO = $db->select('po','*',array('ref_id'=>$ref_id),'AND vp_id IS NOT NULL');
			while($rUPO = $db->fetch_array($qUPO)):
				$db->update('voucher_particular',array('vp_title'=>'P.O. PAYMENT - Invoice: '.$rUPO['invoice']),array('vp_id'=>$rUPO['vp_id']));
			endwhile;
		}

		if($txReceive==1){
			#Assigning of Receive Number
			$tempRR='';
			if( $db->getValue('po','receive_no',array('ref_id'=>$ref_id))==0 ){//Assign Receive Number if it doesn't have one.
				$latestRR_count=0;$latestRR='';
				//Get the latest rr-no
				$txbYearDelvry = date('Y',strtotime($delivery_date));
				$txbMonDelvry = date('m',strtotime($delivery_date));
				$delvryYr = ($txbYearDelvry) ? $txbYearDelvry : date('Y');
				$delvryMn = ($txbMonDelvry) ? $txbMonDelvry : date('m');
				$delvryYrMn = ($txbYearDelvry && $txbMonDelvry) ? $txbYearDelvry.'-'.$txbMonDelvry : date('Y-m');

				if( $po_type=='material' || $po_type=='parts' ){
					echo $latestRR = $db->getValue('po','receive_no',array('LEFT(delivery_date,4)'=>$delvryYr),' AND po_type IN ("material","parts") ORDER BY receive_no DESC LIMIT 1');
					echo $db->last_query;
				}
				else if( $po_type=='fuel' ){
					$latestRR = $db->getValue('po','receive_no',array('LEFT(delivery_date,4)'=>$delvryYr,'po_type'=>'fuel'),'ORDER BY receive_no DESC LIMIT 1');
				}
				die();
				$latestRRExp = explode('-', $latestRR);
				$latestRR_count = isset($latestRRExp[1]) ? $latestRRExp[1] : 0;
				$available=false;
				do{
					$latestRR_count++;
					if($po_type=='material' || $po_type=='parts')
						$tempRR = $delvryYr.$delvryMn.'-'.$latestRR_count;
					else if($po_type=='fuel')
						$tempRR = $delvryYr.$delvryMn.'-0'.$latestRR_count;
					if( $db->getValue('po','count(*)',array('receive_no'=>$tempRR)) )
						$available=false;
					else
						$available=true;
				}while ( $available===false );

				$receive_no = $tempRR;
				$db->update('po',array('receive_no'=>$receive_no),array('ref_id'=>$ref_id));
			}
		}
		/*
		$db->update('po',array('item_encoded_by'=>$txEncodedBy,'item_received_by'=>$txReceivedBy,'report_received_by'=>$txReportBy,'invoice'=>$txInvoice,'received'=>$txReceive,'delivery_date'=>$delivery_date,'remarks'=>$txRemarks),array('ref_id'=>$ref_id));
		if($has_invoice_update_attempt_failed==0)
			$_SESSION['notif_success']="Changes Saved!";
		#functions::sendTo('po_receive_view_only.php?ref_id='.functions::encode($ref_id));
		#die();
		*/
	}
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid sortable">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Purchase Order Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="70%" align="center" border="0" style="background-color:#E4E1E1;font-size:14px;">
					<tr>
						<td height="30" style="padding-left:15px"><div align="left">P.O Number</div></td>
						<td width="75%"><div align="left"><strong><?php echo $ref_id?></strong></div></td>
					</tr>
					<tr>
						<td height="30" style="padding-left:15px"><div align="left">P.O Date</div></td>
						<td><div align="left"><strong><?php echo functions::datearr($vdate)?></strong></div></td>
					</tr>
					<tr>
						<td height="30" style="padding-left:15px"><div align="left">Payee / Supplier</div></td>
						<td><div align="left"><strong><?php echo $db->getValue('supplier','name',array('supplierID'=>$txPayee))?></strong></div></td>
					</tr>
					<?php if($txProjDetail){?>
					<tr>
						<td height="30" style="padding-left:15px"><div align="left">Additional description</div></td>
						<td><div align="left"><strong><?php echo $txProjDetail;?></strong></div></td>
					</tr>
					<?php }?>
					<?php if($txPaymentTerm){?>
					<tr>
						<td height="30" style="padding-left:15px"><div align="left">Terms of Payment</div></td>
						<td><div align="left"><strong><?php echo $txPaymentTerm;?></strong></div></td>
					</tr>
					<?php }?>
					<?php if($txTerms){?>
					<tr>
						<td height="30" style="padding-left:15px" valign="top"><div align="left">Terms and Conditions</div></td>
						<td><div align="left"><?php echo $txTerms;?></div></td>
					</tr>
					<?php }?>
					<tr>
						<td height="30" style="padding-left:15px"><div align="left">Invoice #</div></td>
						<td><div align="left"><input type="text" name="txInvoice" id="txInvoice" style="width:390px;" value="<?php echo $txInvoice;?>" /></div></td>
					</tr>
					<tr>
						<td height="40" style="padding-left:15px"><div align="left">Receive Status</div></td>
						<td>
							<div align="left">
								<select name="txReceive" id="txReceive" style="width:150px;">
									<option value="0">--select--</option>
									<option value="1" <?php if($txReceive==1)echo 'selected="selected"';?>>Received</option>
									<option value="0" <?php if($txReceive==0)echo 'selected="selected"';?>>Not Received</option>
								</select>
								<input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDelvryDate" id="txDelvryDate" value="<?php echo $dlvryDate ?>" required>
								<span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
							</div>
						</td>
					</tr>
					<tr>
						<td height="40" style="padding-left:15px"><div align="left">Item Received By</div></td>
						<td>
							<div align="left">
								<select name="txReceivedBy" id="txReceivedBy" data-rel="chosen" style="width:400px;">
									<option value="">--select--</option>
									<?php $qPrep = $db->select('employee','*',array(),'ORDER BY lname');
									while($rPrep = $db->fetch_array($qPrep)):
									?>
									<option value="<?php echo $rPrep['emp_id']?>" <?php if($txReceivedBy==$rPrep['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rPrep['lname'].', '.$rPrep['fname']);?></option>
									<?php endwhile;?>
								</select>
								<span class="help-inline warning" style="font-weight:bold;" id="msgReceivedBy" name="msgReceivedBy"></span>
							</div>
						</td>
					</tr>
					<tr>
						<td height="40" style="padding-left:15px"><div align="left">Item Encoded By</div></td>
						<td>
							<div align="left">
								<select name="txEncodedBy" id="txEncodedBy" data-rel="chosen" style="width:400px;">
									<option value="">--select--</option>
									<?php $qEnc = $db->select('employee','*',array(),'ORDER BY lname');
									while($rEnc = $db->fetch_array($qEnc)):
									?>
									<option value="<?php echo $rEnc['emp_id']?>" <?php if($txEncodedBy==$rEnc['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEnc['lname'].', '.$rEnc['fname']);?></option>
									<?php endwhile;?>
								</select> 
								<span class="help-inline warning" style="font-weight:bold;" id="msgEncodedBy" name="msgEncodedBy"></span>
							</div>
						</td>
					</tr>
					<tr>
						<td height="40" style="padding-left:15px"><div align="left">Report Received By</div></td>
						<td>
							<div align="left">
								<select name="txReportBy" id="txReportBy" data-rel="chosen" style="width:400px;">
									<option value="">--select--</option>
									<?php $qRep = $db->select('employee','*',array(),'ORDER BY lname');
									while($rRep = $db->fetch_array($qRep)):
									?>
									<option value="<?php echo $rRep['emp_id']?>" <?php if($txReportBy==$rRep['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rRep['lname'].', '.$rRep['fname']);?></option>
									<?php endwhile;?>
								</select> 
								<span class="help-inline warning" style="font-weight:bold;" id="msgReportBy" name="msgReportBy"></span>
							</div>
						</td>
					</tr>
					<tr>
						<td height="30" style="padding-left:15px"><div align="left">Remarks</div></td>
						<td><div align="left"><textarea name="txRemarks" id="txRemarks" style="width:386px;"><?php echo $txRemarks;?></textarea></div></td>
					</tr>
					<tr>
						<td height="50"></td>
						<td>
							<div align="left">
								<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary btn-small">
								<a href="po_receive_view_only.php?ref_id=<?php echo functions::encode($ref_id);?>" class="btn btn-small">Cancel</a>
							</div>
						</td>
					</tr>
				</table><br><br>
				<table width="100%" border="0" align="center" class="table table-striped table-hover table-bordered" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="30%" scope="col"><div align="left">Item</div></th>
							<th width="30%" scope="col"><div align="left">Brand</div></th>
							<th width="7%" scope="col"><div align="center">Unit</div></th>
							<th width="7%" scope="col"><div align="center">Qty Request</div></th>
							<th width="7%" scope="col"><div align="right">Price</div></th>
							<th width="7%" scope="col"><div align="center">Discount</div></th>
							<th width="7%" scope="col"><div align="right">Amount</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$total_amount=0;$disc_amount=0;$amount=0;
						$qPOI = $db->query('SELECT pi.item as items,sum(quantity) as qty,unit,brand,cost,discount FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$ref_id.'" GROUP by pi.item,unit,brand,cost');
						while($rPOI = $db->fetch_array($qPOI)):
							$amount = $rPOI['cost'] * $rPOI['qty'];
							$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
							$amount = $amount - $disc_amount;
							$total_amount += $amount;
						?>
						<tr>
							<td><?php echo $rPOI['items'];?></td>
							<td><?php echo $rPOI['brand'];?></td>
							<td><div align="center"><?php echo $rPOI['unit'];?></div></td>
							<td><div align="center"><?php echo round($rPOI['qty']);?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($rPOI['cost']);?></div></td>
							<td><div align="center"><?php echo $rPOI['discount'];?>%</div></td>
							<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td></td>
							<td>&nbsp;</td>
							<td><div align="right"><strong>Total Amount</strong></div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
						</tr>
					</tbody>
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
	<?php if($txReceive==1){?>
	$('#txDelvryDate').show();
	$('#txDelvryDate').attr('readonly',false);
	$('#txDelvryDate').attr('disabled',false);
	<?php }else{?>
	$('#txDelvryDate').hide();
	$('#txDelvryDate').attr('readonly',true);
	$('#txDelvryDate').attr('disabled',true);
	<?php }?>

	$('#txReceive').change(function(){
		$('#msgBdate').html("");
		if( $('#txReceive').val()=="1" ){
			$('#txDelvryDate').show();
			$('#txDelvryDate').attr('readonly',false);
			$('#txDelvryDate').attr('disabled',false);
		}
		else{
			$('#txDelvryDate').hide();
			$('#txDelvryDate').attr('disabled',true);
		}
	});

	$('#btnSave').click(function(){
		$('#msgBdate').html("");
		$('#msgReportBy').html("");
		$('#msgReceivedBy').html("");
		$('#msgEncodedBy').html("");

		if( $('#txReceive').val()=="1" && $('#txDelvryDate').val()=="" ){
			$('#txDelvryDate').focus();
			$('#msgBdate').html("Receive Date Required!");
		}
		else if( $('#txReceivedBy').val()=="" ){
			$('#txReceivedBy').focus();
			$('#msgReceivedBy').html("Received By Required!");
			res=false;
		}
		else if( $('#txEncodedBy').val()=="" ){
			$('#txEncodedBy').focus();
			$('#msgEncodedBy').html("Encoded By Required!");
			res=false;
		}
		else if( $('#txReportBy').val()=="" ){
			$('#txReportBy').focus();
			$('#msgReportBy').html("Report Receiver Required!");
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
	$('#txDelvryDate').datepicker({
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