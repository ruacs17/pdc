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
$equip_id = (isset($_REQUEST['vdidEdt']) && !empty($_REQUEST['vdidEdt']) ) ? functions::decode($_REQUEST['vdidEdt']) : 0;
$_SESSION['notif_id_list']=$equip_id;
if( isset($_POST['btnSave']) && $equip_id){
	$arrDetails=array();
	$selClass = ( isset($_POST['selClass']) && !empty($_POST['selClass']) ) ? trim($_POST['selClass']) : '';
	$selCat = ( isset($_POST['selCat']) && !empty($_POST['selCat']) ) ? trim($_POST['selCat']) : '';
	$selType = ( isset($_POST['selType']) && !empty($_POST['selType']) ) ? trim($_POST['selType']) : '';
	$txInventoryNo = ( isset($_POST['txInventoryNo']) && !empty($_POST['txInventoryNo']) ) ? trim($_POST['txInventoryNo']) : '';
	$txDesc = ( isset($_POST['txDesc']) && !empty($_POST['txDesc']) ) ? trim($_POST['txDesc']) : '';
	$txProductivity = ( isset($_POST['txProductivity']) && !empty($_POST['txProductivity']) ) ? trim($_POST['txProductivity']) : '';
	$oldEquipName = ( isset($_POST['oldEquipName']) && !empty($_POST['oldEquipName']) ) ? functions::decode($_POST['oldEquipName']) : '';
	$txModel = ( isset($_POST['txModel']) && !empty($_POST['txModel']) ) ? trim($_POST['txModel']) : '';
	$txBrand = ( isset($_POST['selBrand']) && !empty($_POST['selBrand']) ) ? trim($_POST['selBrand']) : '';
	$txSerialNo = ( isset($_POST['txSerialNo']) && !empty($_POST['txSerialNo']) ) ? trim($_POST['txSerialNo']) : '';
	$txPlateNo = ( isset($_POST['txPlateNo']) && !empty($_POST['txPlateNo']) ) ? trim($_POST['txPlateNo']) : '';
	$txMVNo = ( isset($_POST['txMVNo']) && !empty($_POST['txMVNo']) ) ? trim($_POST['txMVNo']) : '';
	$txEngineNo = ( isset($_POST['txEngineNo']) && !empty($_POST['txEngineNo']) ) ? trim($_POST['txEngineNo']) : '';
	$txChassisNo = ( isset($_POST['txChassisNo']) && !empty($_POST['txChassisNo']) ) ? trim($_POST['txChassisNo']) : '';
	$txPower = ( isset($_POST['txPower']) && !empty($_POST['txPower']) ) ? trim($_POST['txPower']) : '';
	$txLocation = ( isset($_POST['txLocation']) && !empty($_POST['txLocation']) ) ? trim($_POST['txLocation']) : '';
	$txGrossWt = ( isset($_POST['txGrossWt']) && !empty($_POST['txGrossWt']) ) ? trim($_POST['txGrossWt']) : '';
	$txNetWt = ( isset($_POST['txNetWt']) && !empty($_POST['txNetWt']) ) ? trim($_POST['txNetWt']) : '';
	$txShipWt = ( isset($_POST['txShipWt']) && !empty($_POST['txShipWt']) ) ? trim($_POST['txShipWt']) : '';
	$txPrice = ( isset($_POST['txPrice']) && !empty($_POST['txPrice']) ) ? functions::moneyToDouble($_POST['txPrice']) : '0';
	$txRate = ( isset($_POST['txRate']) && !empty($_POST['txRate']) ) ? functions::moneyToDouble($_POST['txRate']) : '0';
	$txRemark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : '';
	$txQuantity = ( isset($_POST['txQuantity']) && !empty($_POST['txQuantity']) ) ? trim($_POST['txQuantity']) : 1;
	$txStatus = ( isset($_POST['txStatus']) && !empty($_POST['txStatus']) ) ? trim($_POST['txStatus']) : 'active';
	$txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : 'unit';
	$txLiterHour = ( isset($_POST['txLiterHour']) && !empty($_POST['txLiterHour']) ) ? trim($_POST['txLiterHour']) : NULL;
	$txLiterKm = ( isset($_POST['txLiterKm']) && !empty($_POST['txLiterKm']) ) ? trim($_POST['txLiterKm']) : NULL;
	$txSupplier = ( isset($_POST['txSupplier']) && !empty($_POST['txSupplier']) ) ? $_POST['txSupplier'] : NULL;

	$txProposedNoYr = ( isset($_POST['txProposedNoYr']) && !empty($_POST['txProposedNoYr']) ) ? trim($_POST['txProposedNoYr']) : '0';
	$txUsageLimit = ( isset($_POST['txUsageLimit']) && !empty($_POST['txUsageLimit']) ) ? trim($_POST['txUsageLimit']) : '0';
	$txWarranty = ( isset($_POST['txWarranty']) && !empty($_POST['txWarranty']) ) ? trim($_POST['txWarranty']) : '0';  


	$txDateAcquire = ( isset($_POST['txDateAcquired']) && !empty($_POST['txDateAcquired']) ) ? $_POST['txDateAcquired'] : NULL;
	$arrDateAcquired = ($txDateAcquire) ? explode('/', $txDateAcquire) : array();
	$txAcquiredMon = ( isset($arrDateAcquired[1]) ) ? $arrDateAcquired[1] : NULL;
	$txAcquiredDay = ( isset($arrDateAcquired[2]) ) ? $arrDateAcquired[2] : NULL;
	$txAcquiredYear = ( isset($arrDateAcquired[0]) ) ? $arrDateAcquired[0] : NULL;
	$txDateAcquired = ( functions::valid_date($txAcquiredYear.'-'.$txAcquiredMon.'-'.$txAcquiredDay) ) ? $txAcquiredYear.'-'.$txAcquiredMon.'-'.$txAcquiredDay : NULL;

	$txDateOut = ( isset($_POST['txDateOut']) && !empty($_POST['txDateOut']) ) ? $_POST['txDateOut'] : NULL;
	$arrDateOut = ($txDateOut) ? explode('/', $txDateOut) : array();
	$txOutMon = ( isset($arrDateOut[1]) ) ? $arrDateOut[1] : NULL;
	$txOutDay = ( isset($arrDateOut[2]) ) ? $arrDateOut[2] : NULL;
	$txOutYear = ( isset($arrDateOut[0]) ) ? $arrDateOut[0] : NULL;
	// if($txStatus=="Unserviceable" || $txStatus=="Sold")
	// 	$txOutDate = ( functions::valid_date($txOutYear.'-'.$txOutMon.'-'.$txOutDay) ) ? $txOutYear.'-'.$txOutMon.'-'.$txOutDay : NULL;
	// else
	// 	$txOutDate = NULL;
	$renewMon = ( isset($_POST['renewMon']) && !empty($_POST['renewMon']) ) ? trim($_POST['renewMon']) : NULL;
	$renewDay = ( isset($_POST['renewDay']) && !empty($_POST['renewDay']) ) ? trim($_POST['renewDay']) : NULL;
	$reg_renew = ($renewDay && $renewMon) ? $renewMon.'-'.$renewDay : NULL;

	$txOutDate = ( functions::valid_date($txOutYear.'-'.$txOutMon.'-'.$txOutDay) ) ? $txOutYear.'-'.$txOutMon.'-'.$txOutDay : NULL;

	if( $db->getValue('equip_condition','count(*)',array('equip_id'=>$equip_id,'con_stat'=>$txStatus,'con_date'=>$txOutDate))==0 ){
		$db->insert('equip_condition',array('equip_id'=>$equip_id,'con_stat'=>$txStatus,'con_date'=>$txOutDate));
	}

	if( empty($txInventoryNo) ){
		$set='';
		if( $txDateAcquired ){
			$set = substr($txAcquiredYear, 2,2).$txAcquiredMon.$txAcquiredDay.(date('H') + date('i') + date('s'));
		}
		$txInventoryNo = ($set) ? $set : substr(date('Ymd'), 2,6).(date('H') + date('i') + date('s'));
	}

	if( $txDesc &&  $selType && $selClass){
		$txDesc = str_replace(array("\r", "\n"), ' ', $txDesc);
		$db->update('equipment',array('inventory_id'=>$txInventoryNo,'classification'=>$selClass,'supplierID'=>$txSupplier,'category'=>$selCat,'liter_km'=>$txLiterKm,'liter_hr'=>$txLiterHour,'type'=>$selType,'name'=>$txDesc,'equip_desc'=>$txDesc,'productivity'=>$txProductivity,'model'=>$txModel,'brand'=>$txBrand,'serial_no'=>$txSerialNo,'plate_no'=>$txPlateNo,'engine_no'=>$txEngineNo,'chassis_no'=>$txChassisNo,'date_acquired'=>$txDateAcquired,'mvFileNo'=>$txMVNo,'reg_renew'=>$reg_renew,'power'=>$txPower,'location'=>$txLocation,'gross_wt'=>$txGrossWt,'net_wt'=>$txNetWt,'shipping_wt'=>$txShipWt,'rate'=>$txRate,'remarks'=>$txRemark,'limit_minutes'=>($txUsageLimit*60),'quantity'=>$txQuantity,'unit'=>$txUnit,'status'=>$txStatus,'out_date'=>$txOutDate),array('equip_id'=>$equip_id));


		//Insert the Renewal History for LTO Registration
		$acquired_year = date('Y',strtotime($txDateAcquired));
		if($acquired_year && $reg_renew){
			$renew_week='';
			if($renewDay=='01') $renew_week='first';
			else if($renewDay=='02') $renew_week='second';
			else if($renewDay=='03') $renew_week='third';
			else if($renewDay=='04') $renew_week='fourth';
			for($i=($acquired_year-1); $i<=date('Y'); $i++ ):
				$reg_term=$i;
				$date_due = date("Y-m-d", strtotime($renew_week." friday ".$i."-".$renewMon));
				$date_validity = date("Y-m-d", strtotime($renew_week." friday ".($i+1)."-".$renewMon));
				$date_due_start = date('Y-m-d',strtotime('-2 months',strtotime($date_due)));
				if( $db->getValue('equip_registration','count(*)',array('equip_id'=>$equip_id,'reg_term'=>$reg_term))==0 )
					$db->insert('equip_registration',array('equip_id'=>$equip_id,'reg_term'=>$reg_term,'date_validity'=>$date_validity,'date_due'=>$date_due,'date_due_start'=>$date_due_start,'stat'=>'For Renewal'));
				else
					$db->update('equip_registration',array('date_validity'=>$date_validity,'date_due'=>$date_due,'date_due_start'=>$date_due_start),array('equip_id'=>$equip_id,'reg_term'=>$reg_term));
			endfor;
		}

		if( empty( $db->getValue('equipment','reference_id',array('equip_id'=>$equip_id)) ) )
			$db->update('equipment',array('price'=>$txPrice),array('equip_id'=>$equip_id));

		$db->update('elm_rate',array('itemDesc'=>$txDesc,'itemRate'=>$txRate),array('itemDesc'=>$oldEquipName));

		if( !empty($_FILES['image']) && $_FILES['image']['error'] == 0 ) {
			$uploaddir = '../img_equip/';
			$max_size = 2000 * 1024; // 500 KB
			// Generates random filename and extension 
			function tempnam_sfx($path, $suffix){
				do{
					$file = $path."/".mt_rand().$suffix;
					$fp = @fopen($file, 'x');
				}
				while(!$fp);

				fclose($fp);
				return $file;
			}
			// Process image with GD library
			$verifyimg = getimagesize($_FILES['image']['tmp_name']);

			// Make sure the MIME type is an image
			$pattern = "#^(image/)[^\s\n<]+$#i";

			if( !preg_match($pattern, $verifyimg['mime']) ){
				functions::say("Only image files are allowed!");
			}
			else if( $_FILES["image"]["size"] > $max_size ){
				functions::say("Image reached the limit size!");
			}
			else{
				// Rename both the image and the extension 
				$uploadfile = tempnam_sfx($uploaddir, ".jpg");

				// Upload the file to a secure directory with the new name and extension
				if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
					$oldFileName = $db->getValue('images','name',array('equip_id'=>$equip_id));
					if($oldFileName){
						if(file_exists('../img_equip/'.$oldFileName))
							unlink('../img_equip/'.$oldFileName);
					}
					$db->delete('images',array('equip_id'=>$equip_id));
					$db->insert('images',array('name'=>basename($uploadfile),'original_name'=>basename($_FILES['image']['name']),'mime_type'=>$_FILES['image']['type'],'equip_id'=>$equip_id));
				}else{
					functions::say("Image upload failed!");
				}
			}
		}

		if( $txDateAcquired ){

			if( $txProposedNoYr > 0)
				$db->update('equipment',array('proposed_life'=>($txAcquiredYear + $txProposedNoYr).'-'.$txAcquiredMon.'-'.$txAcquiredDay),array('equip_id'=>$equip_id));
			else{
				$db->update('equipment',array('propose_life'=>NULL),array('equip_id'=>$equip_id));
			}

			if( $txWarranty > 0)
				$db->update('equipment',array('warranty'=>($txAcquiredYear + $txWarranty).'-'.$txAcquiredMon.'-'.$txAcquiredDay),array('equip_id'=>$equip_id));
			else{
				$db->update('equipment',array('warranty'=>NULL),array('equip_id'=>$equip_id));
			}
		}
		$_SESSION['notif_success']='Changes saved!';
	}
	functions::sendTo('admin-equipment-view.php?vdidVw='.functions::encode($equip_id));
	die();
}

$selClass='';$selType='';$txDesc='';$txModel='';$txBrand='';$txSerialNo='';$txPlateNo='';$txEngineNo='';$txChassisNo='';$txAcquiredMon='';$txAcquiredDay='';$txAcquiredYear='';$txOutMon='';$txOutDay='';$txOutYear=''; $selBrand='';$txInventoryNo='';$txQuantity=0;$txStatus='';
$txMVNo='';$txPower='';$txLocation='';$txGrossWt='';$txNetWt='';$txShipWt='';$txPrice='';$txRate=0;$txRemark='';$txProposedDay='';$txProposedYear=''; $txProposedMon=''; $txRemark=''; $txProposedNoYr=0; $txUsageLimit=0;$txWarranty=0;$txUnit='';
$q = $db->select('equipment','*',array('equip_id'=>$equip_id));
$r = $db->fetch_array($q);
$txInventoryNo = $r['inventory_id'];
$selClass = $r['classification'];
$selCat = $r['category'];
$selType = $r['type'];
$txDesc = $r['equip_desc'];
$txProductivity = $r['productivity'];
$txSupplier = $r['supplierID'];
$txModel = $r['model'];
$txBrand = $r['brand'];
$txSerialNo = $r['serial_no'];
$txPlateNo = $r['plate_no'];
$txEngineNo = $r['engine_no'];
$txChassisNo = $r['chassis_no'];
$txDateAcquired = $r['date_acquired'];
$txOutDate = $r['out_date'];
$txMVNo = $r['mvFileNo'];
$txPower = $r['power'];
$txLocation = $r['location'];
$txGrossWt = $r['gross_wt'];
$txNetWt = $r['net_wt'];
$txShipWt = $r['shipping_wt'];
$txPrice = ($r['price']) ? functions::formatMoney($r['price']) : 0;
$txRemark = $r['remarks'];
$reg_renew = $r['reg_renew'];
$renw = explode('-', $reg_renew);
$renMon = isset($renw[0]) ? $renw[0] : NULL;
$renWeek = isset($renw[1]) ? $renw[1] : NULL;

$txProposedNoYr = $db->getValue('equipment','if( IFNULL(left(proposed_life,4),0) > IFNULL(left(date_acquired,4),0), IFNULL(left(proposed_life,4),0)-IFNULL(left(date_acquired,4),0),0) as proposedLife',array('equip_id'=>$r['equip_id']));
$txWarranty = $db->getValue('equipment','if( IFNULL(left(warranty,4),0) > IFNULL(left(date_acquired,4),0), IFNULL(left(warranty,4),0)-IFNULL(left(date_acquired,4),0),0) as yrWarranty',array('equip_id'=>$r['equip_id']));
$txUsageLimit = ($r['limit_minutes'] > 0) ? $r['limit_minutes'] / 60 : 0;
$txRate = $r['rate'];
$txQuantity = $r['quantity'];
$txUnit = $r['unit'];
$txStatus = $r['status'];
$liter_hour = $r['liter_hr'];
$liter_km = $r['liter_km'];
$selRefType = $r['reference_type'];
$txRefValue = $r['reference_value'];

$xdate = explode('-',$txDateAcquired);
if(count($xdate)==3){
	$txAcquiredMon = $xdate[1];
	$txAcquiredDay = $xdate[2];
	$txAcquiredYear = $xdate[0];
	$txDateAcquired = $txAcquiredYear.'/'.$txAcquiredMon.'/'.$txAcquiredDay;
}

$outdate = explode('-',$txOutDate);
if(count($outdate)==3){
	$txOutMon = $outdate[1];
	$txOutDay = $outdate[2];
	$txOutYear = $outdate[0];
	$txOutDate=$txOutYear.'/'.$txOutMon.'/'.$txOutDay;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Property Update</title>
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
	<script src="../js/inputInt.js"></script>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>PROPERTY EDIT</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post" enctype="multipart/form-data">
					<input type="hidden" name="oldEquipName" id="oldEquipName" value="<?php echo functions::encode($txDesc)?>">
					<table width="80%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<th width="35%" align="right" scope="row">&nbsp;</th>
							<td width="2%">&nbsp;</td>
							<td width="50%">&nbsp;</td>
							<td width="13%">&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Property Classification</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<select name="selClass" id="selClass" style="width:250px;" required>
									<option value="">-- Select --</option>
									<?php
									$qClass = $db->query('SELECT * FROM equipment_type WHERE type_name="classification" ORDER BY type_desc ');
									while($rClass = $db->fetch_array($qClass)):
									?>
									<option value="<?php echo $rClass['type_desc']?>" <?php if($selClass==$rClass['type_desc'])echo 'selected="selected"';?>><?php echo $rClass['type_desc']?></option>
									<?php endwhile;?>
								</select>
								<a id="mngClass" class="thickbox" style="cursor:pointer" onclick="showThis(this.id,'admin-equipment-add-class.php?','Manage Property Classification','1')" title="Manage Property Classification" data-rel="tooltip"><i class="halflings-icon plus-sign"></i></a>
								<span class="help-inline warning" id="MsgSelClass" style="font-weight:bold;" name="MsgSelClass"></span>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Property Category</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<select name="selCat" id="selCat" style="width:250px;" required>
									<option value="">-- Select --</option>
									<option value="Fixed Assets" <?php if($selCat=="Fixed Assets")echo 'selected="selected"';?>>Fixed Assets</option>
									<option value="Consumable Tools and Supplies" <?php if($selCat=="Consumable Tools and Supplies")echo 'selected="selected"';?>>Consumable Tools and Supplies</option>
								</select>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Property Type</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<select name="selType" id="selType" style="width:250px;">
									<option value="">-- Select --</option>
									<?php
									$qType = $db->query('SELECT * FROM equipment_type WHERE type_name="type" ORDER BY type_desc ');
									while($rType = $db->fetch_array($qType)):
									?>
									<option value="<?php echo $rType['type_desc']?>" <?php if($selType==$rType['type_desc'])echo 'selected="selected"';?>><?php echo $rType['type_desc']?></option>
									<?php endwhile;?>
								</select>
								<a id="mngType" class="thickbox" style="cursor:pointer" onclick="showThis(this.id,'admin-equipment-add-type.php?','Manage Property Type','1')" title="Manage Property Type" data-rel="tooltip"><i class="halflings-icon plus-sign"></i></a>
								<span class="help-inline warning" id="MsgSelType" style="font-weight:bold;" name="MsgSelType"></span>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Brand/Made</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<select name="selBrand" id="selBrand" style="width:250px;">
									<option value="">-- Select --</option>
									<?php
									$qBrand = $db->query('SELECT * FROM equipment_type WHERE type_name="brand" ORDER BY type_desc ');
									while($rBrand = $db->fetch_array($qBrand)):
									?>
									<option value="<?php echo $rBrand['type_desc']?>" <?php if($txBrand==$rBrand['type_desc'])echo 'selected="selected"';?>><?php echo $rBrand['type_desc']?></option>
									<?php endwhile;?>
								</select>
								<a id="mngBrand" class="thickbox" style="cursor:pointer" onclick="showThis(this.id,'admin-equipment-add-brand.php?','Manage Property Brand / Made','1')" title="Manage Property Brand" data-rel="tooltip"><i class="halflings-icon plus-sign"></i></a>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Property Picture</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<?php
								$fileName = $db->getValue('images','name',array('equip_id'=>$equip_id));
								$file = ($fileName && file_exists('../img_equip/'.$fileName)) ? '../img_equip/'.$fileName : '../img_equip/blank-pic.png';
								?>
								<img height="400" width="400" src="<?php echo $file?>"><br>
								Select image to upload <input type="file" name="image">
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Property Code</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txInventoryNo" id="txInventoryNo" value="<?php echo $txInventoryNo;?>" readonly /></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Description</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<textarea type="text" name="txDesc" id="txDesc" style="width:80%;" rows="4"><?php echo $txDesc?></textarea>
								<span class="help-inline warning" id="MsgtxDesc" style="font-weight:bold;" name="MsgtxDesc"></span>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Productivity</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<textarea type="text" name="txProductivity" id="txProductivity" style="width:80%;" rows="4"><?php echo $txProductivity?></textarea>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Model</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txModel" id="txModel" value="<?php echo $txModel?>" /></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Serial Number</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txSerialNo" id="txSerialNo" value="<?php echo $txSerialNo?>" /></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Plate Number</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txPlateNo" id="txPlateNo" value="<?php echo $txPlateNo?>" /></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">MV File Number</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txMVNo" id="txMVNo" value="<?php echo $txMVNo?>" /></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">LTO Renewal Schedule</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<select name="renewMon" id="renewMon" style="width:100px;">
									<option value="">--select--</option>
									<option value="01" <?php if($renMon=='01')echo 'selected="selected"';?>>Jan</option>
									<option value="02" <?php if($renMon=='02')echo 'selected="selected"';?>>Feb</option>
									<option value="03" <?php if($renMon=='03')echo 'selected="selected"';?>>Mar</option>
									<option value="04" <?php if($renMon=='04')echo 'selected="selected"';?>>Apr</option>
									<option value="05" <?php if($renMon=='05')echo 'selected="selected"';?>>May</option>
									<option value="06" <?php if($renMon=='06')echo 'selected="selected"';?>>Jun</option>
									<option value="07" <?php if($renMon=='07')echo 'selected="selected"';?>>Jul</option>
									<option value="08" <?php if($renMon=='08')echo 'selected="selected"';?>>Aug</option>
									<option value="09" <?php if($renMon=='09')echo 'selected="selected"';?>>Sep</option>
									<option value="10" <?php if($renMon=='10')echo 'selected="selected"';?>>Oct</option>
									<option value="11" <?php if($renMon=='11')echo 'selected="selected"';?>>Nov</option>
									<option value="12" <?php if($renMon=='12')echo 'selected="selected"';?>>Dec</option>
								</select>
								<select name="renewDay" id="renewDay" style="width:110px;">
									<option value="">--select--</option>
									<option value="01" <?php if($renWeek=='01')echo 'selected="selected"';?>>1st Week</option>
									<option value="02" <?php if($renWeek=='02')echo 'selected="selected"';?>>2nd Week</option>
									<option value="03" <?php if($renWeek=='03')echo 'selected="selected"';?>>3rd Week</option>
									<option value="04" <?php if($renWeek=='04')echo 'selected="selected"';?>>4th Week</option>
								</select>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Engine Number</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txEngineNo" id="txEngineNo" value="<?php echo $txEngineNo?>" /></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Chassis Number</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txChassisNo" id="txChassisNo" value="<?php echo $txChassisNo?>" /></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Power / Capacity</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><textarea type="text" name="txPower" id="txPower" style="width:70%;" rows="3"><?php echo $txPower?></textarea></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Gross Weight</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txGrossWt" id="txGrossWt" value="<?php echo $txGrossWt?>" /></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Net Weight</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txNetWt" id="txNetWt" value="<?php echo $txNetWt?>" /></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Shipping Weight</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txShipWt" id="txShipWt" value="<?php echo $txShipWt?>" /></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Average Liter/Hour</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txLiterHour" id="txLiterHour" value="<?php echo $liter_hour?>" /></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Average Liter/Km</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txLiterKm" id="txLiterKm" value="<?php echo $liter_km;?>" /></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Acquisition Cost</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txPrice" id="txPrice" autocomplete='off' value="<?php echo $txPrice?>" onkeyup="FormatCurrency(this);" <?php if($txRefValue)echo 'readonly'; ?>></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Location</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" class="span6" name="txLocation" id="txLocation" value="<?php echo $txLocation?>" /></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Date Acquired</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" style="width: 80px;" name="txDateAcquired" id="txDateAcquired" value="<?php echo $txDateAcquired ?>"></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Warranty (No. of Years)</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txWarranty" id="txWarranty" value="<?php echo $txWarranty?>" onkeypress="return checkinput(this, event);"/></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Useful Life (No. of Years)</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txProposedNoYr" id="txProposedNoYr" value="<?php echo $txProposedNoYr?>" onkeypress="return checkinput(this, event);"/></td><td>&nbsp;</td>
							</tr>
						<tr>
							<th align="right" scope="row">Rate Per Hour</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" class="span6"  name="txRate" id="txRate" autocomplete='off' value="<?php echo $txRate?>" onkeyup="FormatCurrency(this);"></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Maintenance Period (No. of Hours)</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" name="txUsageLimit" id="txUsageLimit" value="<?php echo $txUsageLimit?>" onkeypress="return checkinput(this, event);"/></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Quantity</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<select name="txQuantity" id="txQuantity" data-rel="chosen" style="width:80px;">
									<?php for($i=1;$i<=100;$i++):?>
									<option value="<?php echo $i;?>" <?php if($txQuantity==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
									<?php endfor;?>
								</select>
								<select name="txUnit" id="txUnit" style="width:80px;">
									<option value="unit" <?php if($txUnit=="unit")echo 'selected="selected"';?>>Unit</option>
									<option value="piece" <?php if($txUnit=="piece")echo 'selected="selected"';?>>Piece</option>
									<option value="set" <?php if($txUnit=="set")echo 'selected="selected"';?>>Set</option>
									<option value="pair" <?php if($txUnit=="pair")echo 'selected="selected"';?>>Pair</option>
								</select>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Status</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<select name="txStatus" id="txStatus" style="width:180px;" required>
									<option value="">--Select--</option>
									<option value="active" <?php if($txStatus=="active")echo 'selected="selected"';?>>Active</option>
									<option value="inactive" <?php if($txStatus=="inactive")echo 'selected="selected"';?>>Inactive</option>
									<option value="Good Condition" <?php if($txStatus=="Good Condition")echo 'selected="selected"';?>>Good Condition</option>
									<option value="For Repair" <?php if($txStatus=="For Repair")echo 'selected="selected"';?>>For Repair</option>
									<option value="For Rehab" <?php if($txStatus=="For Rehab")echo 'selected="selected"';?>>For Rehab</option>
									<option value="Trade In" <?php if($txStatus=="Trade In")echo 'selected="selected"';?>>Trade In</option>
									<option value="Unserviceable" <?php if($txStatus=="Unserviceable")echo 'selected="selected"';?>>Unserviceable</option>
									<option value="Sold" <?php if($txStatus=="Sold")echo 'selected="selected"';?>>Sold</option>
								</select>
								<input type="text" style="width: 80px;" name="txDateOut" id="txDateOut" value="<?php echo $txOutDate ?>" required>
								<a id="manageCondition" class="thickbox" style="cursor:pointer" onclick="showThis(this.id,'admin-equipment-condition-manage.php?eid=<?php echo functions::encode($equip_id)?>','Property Condition History','1')" title="View Property Condition History" data-rel="tooltip"><i class="halflings-icon info-sign"></i></a>
								<span class="help-inline warning" id="MsgStatus" style="font-weight:bold;" name="MsgStatus"></span>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Remarks</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><textarea type="text" name="txRemark" id="txRemark" style="width:70%;" rows="3"><?php echo $txRemark;?></textarea></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Supplier</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<select name="txSupplier" id="txSupplier" data-rel="chosen" style="width:500px;">
									<option value="">--select--</option>
									<?php $qSup = $db->select('supplier','*',array(),'ORDER BY name');
									while($rSup = $db->fetch_array($qSup)):
									?>
									<option value="<?php echo $rSup['supplierID']?>" <?php if($txSupplier==$rSup['supplierID'])echo 'selected="selected"';?>><?php echo ucwords(strtolower($rSup['name']));?></option>
									<?php endwhile;?>
								</select>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Reference</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<?php $page_ref = ($selRefType=='voucher') ? 'admin-equipment-reference-amount-vo.php' : 'admin-equipment-reference-amount-po.php'; ?>
								<a id="amntRef" class="thickbox" onclick="showThis(this.id,'<?php echo $page_ref ?>?vdidVw=<?php echo functions::encode($equip_id);?>','Reference','1')" style="cursor:pointer;">Manage</a>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="4" align="center">
								<input type="submit" name="btnSave" id="btnSave" value="Save Changes" class="btn btn-primary btn-small">
								<a href="admin-equipment-view.php?vdidVw=<?php echo functions::encode($equip_id) ?>" class="btn btn-small">Cancel</a>
							</td>
						</tr>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function ask(){
	if(confirm('Do you want to update this property?'))
		return true;
	else
		return false; 
}
$(document).ready(function(){
	var res = false;
	<?php if($selRefType=="po" || $selRefType=="voucher"){?>
	$('#txRefValue').show();
	<?php }else{?>
	$('#txRefValue').hide();
	<?php }?>


	$('#selClass').focus(function(){
		var deflt=$(this).val();
		$.get("admin-equipment-sel-class.php?sel="+deflt+"&typ=class", function( data ) {
			$('#selClass').empty().append(data).val(deflt);
		});
	});

	$('#selType').focus(function(){
		var deflt=$(this).val();
		$.get("admin-equipment-sel-class.php?sel="+deflt+"&typ=type", function( data ) {
			$('#selType').empty().append(data).val(deflt);
		});
	});

	$('#selBrand').focus(function(){
		var deflt=$(this).val();
		$.get("admin-equipment-sel-class.php?sel="+deflt+"&typ=brand", function( data ) {
			$('#selBrand').empty().append(data).val(deflt);
		});
	});


	$('#selRefType').change(function(){
		if( $('#selRefType').val()=="po" || $('#selRefType').val()=="voucher" ){
			$('#txRefValue').show();
		}
		else{
			$('#txRefValue').hide();
		}
	});
	$('#btnSave').click(function(){
		$('#MsgSelClass').html("");
		$('#MsgSelType').html("");
		$('#MsgtxDesc').html("");

		if( $('#selClass').val()=="" ){
			$('#MsgSelClass').html("Classification Required!");
			$('#selClass').focus();
			res=false;
		}
		else if( $('#selType').val()=="" ){
			$('#MsgSelType').html("Type Required!");
			$('#selType').focus();
			res=false;
		}
		else if( $('#txDesc').val()=="" ){
			$('#MsgtxDesc').html("Description Required!");
			$('#txDesc').focus();
			res=false;
		}
		else if( $('#txDateOut').val()=="" ){
			$('#MsgStatus').html("Date Required!");
			$('#txDateOut').focus();
			res=false;
		}
		else{
			if(ask())
				res=true;
		}
		return res;
	});
	$('#txDateAcquired').datepicker({
		numberOfMonths:1,
		dateFormat:'yy/mm/dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2010:<?php echo date('Y')+2 ?>',
	});
	$('#txDateOut').datepicker({
		numberOfMonths:1,
		dateFormat:'yy/mm/dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2010:<?php echo date('Y')+2 ?>',
	});
});
</script>
<!-- end: JavaScript-->
</body>
</html>