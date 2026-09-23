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
$por_id_edt = (isset($_REQUEST['poridupt']) && !empty($_REQUEST['poridupt']) ) ? functions::decode($_REQUEST['poridupt']) : 0;

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
$txPaymentTerm = $r['payment_term'];

$dlvryDate = $r['delivery_date'];
$xDDate = explode('-',$dlvryDate);
$monDelvry = isset($xDDate[1]) ? $xDDate[1] : '';
$dayDelvry = isset($xDDate[2]) ? $xDDate[2] : '';
$yearDelvry = isset($xDDate[0]) ? $xDDate[0] : '';

$qpr = $db->select('po_receive','*',array('por_id'=>$por_id_edt));
$rpr = $db->fetch_array($qpr);
$txReceive = $rpr['received'];
$txRemarks = $rpr['remarks'];
$txReceivedBy = $rpr['item_received_by'];
$txEncodedBy = $rpr['item_encoded_by'];
$txReportBy = $rpr['report_received_by'];

$dlvryDate = $rpr['delivery_date'];
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
	$all_received=1;
	if($por_id_edt){//For update only
		$db->update('po_receive',array('item_encoded_by'=>$txEncodedBy,'item_received_by'=>$txReceivedBy,'report_received_by'=>$txReportBy,'delivery_date'=>$delivery_date,'received'=>$txReceive,'remarks'=>$txRemarks,'modification_by'=>$user_id),array('ref_id'=>$ref_id,'por_id'=>$por_id_edt));
		$qPOI = $db->query('SELECT pi.item as items,sum(quantity) as qty,unit,brand FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$ref_id.'" GROUP by pi.item,unit,brand');
		while($rPOI = $db->fetch_array($qPOI)):
			$arr = array('item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']);
			$selItm = functions::encode(serialize($arr));
			if( isset($_POST[$selItm]) ){
				$receiving = $_POST[$selItm];
				if($receiving>0){//if there is something to be received.
					//check for it's existing from po_receive_item, if there's any then update, and add otherwise.
					if( $db->getValue('po_receive_item','count(*)',array('por_id'=>$por_id_edt,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand'])) ){
						$db->update('po_receive_item',array('qty_received'=>$receiving),array('por_id'=>$por_id_edt,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']));
					}
					else{
						$db->insert('po_receive_item',array('por_id'=>$por_id_edt,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand'],'qty_received'=>$receiving));
					}
				}
				else{
					//if there's nothing to receive, then be sure to remove if there's any item of this kind from this record.
					$db->delete('po_receive_item',array('por_id'=>$por_id_edt,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']));
				}
			}

			$qty_request = round($rPOI['qty']);
			$qty_received = $db->getValue('po_receive por, po_receive_item pori','sum(qty_received)',array('ref_id'=>$ref_id,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']),'AND por.por_id=pori.por_id');
			$qty_receivable = $qty_request - $qty_received;
			//Update the actual P.O.
			$db->update('po p, po_item poi',array('qty_delivered'=>$qty_received),array('ref_id'=>$ref_id,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']),'AND p.po_id=poi.po_id');
			
			//checking all the items if all items were completed in receiving, it will assigned the variable into 0 if there's one item is not yet complete.
			if($qty_receivable)
				$all_received=0;
		endwhile;
		//update PO receiving no
		$countRR=1;
		$receive_no = '';
		$qrn = $db->select('po_receive','receive_no',array('ref_id'=>$ref_id));
		$actualRR = $db->num_rows($qrn);
		while($rrn = $db->fetch_array($qrn)):
			$receive_no.=$rrn['receive_no'];
			if($countRR<$actualRR)
				$receive_no.=", ";
			$countRR++;
		endwhile;
		$db->update('po',array('receive_no'=>$receive_no,'received'=>$all_received),array('ref_id'=>$ref_id));
		$_SESSION['porid_update']=$por_id_edt;
		$_SESSION['notif_success']='Changes Saved!';
	}
	else{//For creating new receiving report
		$allow_insert=0;$all_received=1;
		$qPOI = $db->query('SELECT pi.item as items,sum(quantity) as qty,unit,brand FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$ref_id.'" GROUP by pi.item,unit,brand');
		while($rPOI = $db->fetch_array($qPOI)):
			$qty_request = round($rPOI['qty']);
			$qty_received = $db->getValue('po_receive por, po_receive_item pori','sum(qty_received)',array('ref_id'=>$ref_id,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']),'AND por.por_id=pori.por_id');
			$qty_receivable = $qty_request - $qty_received;
			$arr = array('item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']);
			$selItm = functions::encode(serialize($arr));
			if( isset($_POST[$selItm]) ){
				$receiving = $_POST[$selItm];
				if($receiving)
					$allow_insert=1;
			}
		endwhile;
		if($allow_insert){
			$por_id = $db->insert('po_receive',array('po_type'=>$po_type,'item_encoded_by'=>$txEncodedBy,'item_received_by'=>$txReceivedBy,'report_received_by'=>$txReportBy,'delivery_date'=>$delivery_date,'received'=>$txReceive,'remarks'=>$txRemarks,'ref_id'=>$ref_id,'creation_by'=>$user_id));
			if($por_id){
				#Assigning of Receive Number
				$tempRR='';
				if( $db->getValue('po_receive','receive_no',array('por_id'=>$por_id))=="" ){//Assign Receive Number if it doesn't have one.
					$latestRR_count=0;$latestRR='';
					//Get the latest rr-no
					$txbYearDelvry = date('Y',strtotime($delivery_date));
					$txbMonDelvry = date('m',strtotime($delivery_date));
					$delvryYr = ($txbYearDelvry) ? $txbYearDelvry : date('Y');
					$delvryMn = ($txbMonDelvry) ? $txbMonDelvry : date('m');
					$delvryYrMn = ($txbYearDelvry && $txbMonDelvry) ? $txbYearDelvry.'-'.$txbMonDelvry : date('Y-m');

					//if another receiving report from the same po.
					if( $latestRR = $db->getValue('po_receive','receive_no',array('ref_id'=>$ref_id),'ORDER BY receive_no DESC') ){
						$latestRRExp = explode('-', $latestRR);
						$latestRRBase = isset($latestRRExp[0]) ? $latestRRExp[0] : 0;
						$latestRR_count = isset($latestRRExp[1]) ? $latestRRExp[1] : 0;
						$latestRR_subcount = isset($latestRRExp[2]) ? $latestRRExp[2] : 0;
						$available=false;
						do{
							$latestRR_subcount++;
							$tempRR = $latestRRBase.'-'.$latestRR_count.'-'.$latestRR_subcount;
							if( $db->getValue('po_receive','count(*)',array('receive_no'=>$tempRR)) )
								$available=false;
							else
								$available=true;
						}while ( $available===false );
					}
					else{//if it's a new and first receiving report of this po
						if( $po_type=='material' || $po_type=='parts' || $po_type=='accessory' ){
							$latestRR = $db->getValue('po_receive','receive_no',array('LEFT(delivery_date,4)'=>$delvryYr),' AND po_type in ("material","parts") ORDER BY receive_no DESC LIMIT 1');
						}
						else if( $po_type=='fuel' ){
							$latestRR = $db->getValue('po_receive','receive_no',array('LEFT(delivery_date,4)'=>$delvryYr,'po_type'=>'fuel'),'ORDER BY receive_no DESC LIMIT 1');
						}

						$latestRRExp = explode('-', $latestRR);
						$latestRR_count = isset($latestRRExp[1]) ? $latestRRExp[1] : 0;
						$available=false;
						do{
							$latestRR_count++;
							if( $po_type=='material' || $po_type=='parts' || $po_type=='accessory' )
								$tempRR = $delvryYr.$delvryMn.'-'.$latestRR_count;
							else if($po_type=='fuel')
								$tempRR = $delvryYr.$delvryMn.'-0'.$latestRR_count;
							if( $db->getValue('po_receive','count(*)',array('receive_no'=>$tempRR)) )
								$available=false;
							else
								$available=true;
						}while ( $available===false );
					}

					$receive_no = $tempRR;
					$db->update('po_receive',array('receive_no'=>$receive_no),array('por_id'=>$por_id));
				}
				#End Assigning of Receive Number

				$qPOI = $db->query('SELECT pi.item as items,sum(quantity) as qty,unit,brand FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$ref_id.'" GROUP by pi.item,unit,brand');
				while($rPOI = $db->fetch_array($qPOI)):
					$qty_request = round($rPOI['qty']);
					$qty_received = $db->getValue('po_receive por, po_receive_item pori','count(*)',array('ref_id'=>$ref_id,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']),'AND por.por_id=pori.por_id');
					$qty_receivable = $qty_request - $qty_received;
					$arr = array('item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']);
					$selItm = functions::encode(serialize($arr));
					if( isset($_POST[$selItm]) ){
						$receiving = $_POST[$selItm];
						if($receiving){
							$db->insert('po_receive_item',array('por_id'=>$por_id,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand'],'qty_received'=>$receiving));
						}
					}
					$qty_received = $db->getValue('po_receive por, po_receive_item pori','sum(qty_received)',array('ref_id'=>$ref_id,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']),'AND por.por_id=pori.por_id');
					$qty_receivable = $qty_request - $qty_received;
					//Update the actual P.O.
					$db->update('po p, po_item poi',array('qty_delivered'=>$qty_received),array('ref_id'=>$ref_id,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']),'AND p.po_id=poi.po_id');
					//checking all the items if all items were completed in receiving, it will assigned the variable into 0 if there's one item is not yet complete.
					if($qty_receivable)
						$all_received=0;
				endwhile;
			}//if($por_id)	
			
			//update PO receiving no
			$countRR=1;
			$receive_no = '';
			$qrn = $db->select('po_receive','receive_no',array('ref_id'=>$ref_id));
			$actualRR = $db->num_rows($qrn);
			while($rrn = $db->fetch_array($qrn)):
				$receive_no.=$rrn['receive_no'];
				if($countRR<$actualRR)
					$receive_no.=", ";
				$countRR++;
			endwhile;
			$db->update('po',array('receive_no'=>$receive_no,'received'=>$all_received),array('ref_id'=>$ref_id));
			$_SESSION['notif_success']='Report Created!';
			$_SESSION['porid_update']=$por_id;
		}//if($allow_insert)
	}// End For creating new receiving report

	functions::sendTo('po_receive_view_only.php?ref_id='.functions::encode($ref_id));
	die();
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

						</td>
					</tr>
				</table><br><br>
				<table width="90%" border="0" align="center" class="tables table-striped table-hover table-bordered" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="40%" scope="col" height="40px;"><div align="left">Item</div></th>
							<th width="26%" scope="col"><div align="left">Brand</div></th>
							<th width="8%" scope="col"><div align="center">Unit</div></th>
							<th width="8%" scope="col"><div align="center">Qty Request</div></th>
							<th width="8%" scope="col"><div align="center">Qty Receivable</div></th>
							<th width="10%" scope="col"><div align="center">Qty Received</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$total_amount=0;$disc_amount=0;$amount=0;
						$qPOI = $db->query('SELECT pi.item as items,sum(quantity) as qty,unit,brand FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$ref_id.'" GROUP by pi.item,unit,brand ORDER BY pi.item');
						while($rPOI = $db->fetch_array($qPOI)):
							$qty_request = round($rPOI['qty']);
							$qty_received = $db->getValue('po_receive por, po_receive_item pori','sum(qty_received)',array('ref_id'=>$ref_id,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']),'AND por.por_id=pori.por_id');
							$qty_receivable = $qty_request - $qty_received;
							$arr = array('item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']);
							$selItm = functions::encode(serialize($arr));
							$received = $qty_receivable;
							if($por_id_edt){
								$received = $db->getValue('po_receive por, po_receive_item pori','sum(qty_received)',array('por.por_id'=>$por_id_edt,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']),'AND por.por_id=pori.por_id');
								$qty_receivable +=$received;
							}
							
						?>
						<tr>
							<td height="40px;"><?php echo $rPOI['items'];?></td>
							<td><?php echo $rPOI['brand'];?></td>
							<td><div align="center"><?php echo $rPOI['unit'];?></div></td>
							<td><div align="center"><?php echo $qty_request;?></div></td>
							<td><div align="center"><?php echo $qty_receivable;?></div></td>
							<td>
								<div align="center">
									<input type="number" name="<?php echo $selItm;?>" id="<?php echo $selItm;?>" min="0" max="<?php echo $qty_receivable ?>" step=".1" style="width:70px;font-size:12px;"  onkeyup="sve(this.id,this.value,this.max)" value="<?php echo $received; ?>">
								</div>
							</td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table>
				<div align="center" style="padding-top:30px;">
					<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary btn-small">
					<a href="po_receive_view_only.php?ref_id=<?php echo functions::encode($ref_id);?>" class="btn btn-small">Cancel</a>
				</div>
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
function sve(nme,vle,mx) {
	var mx = parseFloat(mx);
	var vle = parseFloat(vle);
	if(vle > mx){
		vle=mx;
		document.getElementById(nme).value=vle;
	}
}
</script>
<!-- end: JavaScript-->
</body>
</html>