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
$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$vp_id=(isset($_REQUEST['vpid']) && !empty($_REQUEST['vpid']) ) ? functions::decode($_REQUEST['vpid']) : 0;
$isFuel=(isset($_REQUEST['fl']) && !empty($_REQUEST['fl']) ) ? functions::decode($_REQUEST['fl']) : 0;
$particular_name=$db->getValue('voucher_particular','vp_title',array('voucher_id'=>$vid,'vp_id'=>$vp_id));
$category=""; $item=""; $proj_id="";  $amount=0; $txDate="";$amountIssue=0; $equip_id=0; $requester='';$liter='';$ef_id=0;
$category=(isset($_REQUEST['catID']) && !empty($_REQUEST['catID']) ) ? $_REQUEST['catID'] : '';

if( $db->getValue('voucher','supplierID',array('voucher_id'=>$vid))==142 )
functions::sendTo('voucher_part_add_admin.php?vid='.functions::encode($vid));

if( isset($_POST['btnSelectPart']) ){
	$particular_name = (isset($_POST['txPartName']) && !empty($_POST['txPartName'])) ? $_POST['txPartName'] : 0;
	if( $vp_id = $db->getValue('voucher_particular','vp_id',array('voucher_id'=>$vid,'vp_title'=>$particular_name,'vtype'=>'cash')) ){
		; 
	}
	elseif($particular_name){
		$part_ins = $db->insertPrint('voucher_particular',array('vp_title'=>$particular_name,'voucher_id'=>$vid,'vtype'=>'cash'));
		$db->query($part_ins);
		$vp_id = $db->insert_id();  
	}
	functions::sendTo('voucher_part_add.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
}
if( isset($_POST['btnEditParName']) ){
	$particular_name = (isset($_POST['txPartName']) && !empty($_POST['txPartName'])) ? $_POST['txPartName'] : 0;
	if( $particular_name ){
		$db->update('voucher_particular',array('vp_title'=>$particular_name),array('voucher_id'=>$vid,'vp_id'=>$vp_id));
	}
	functions::sendTo('voucher_part_add.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
}

if( isset($_POST['btnAdd']) ){
	$arr = array();
	$particular_name=$db->getValue('voucher_particular','vp_title',array('voucher_id'=>$vid,'vp_id'=>$vp_id,'vtype'=>'cash'));
	$category = ( isset($_POST['txItemCat']) && !empty($_POST['txItemCat']) ) ? $_POST['txItemCat'] : NULL;
	$item = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : NULL;
	$txItemEquip = ( isset($_POST['txItemEquip']) && !empty($_POST['txItemEquip']) ) ? trim($_POST['txItemEquip']) : NULL;
	$txRequester = ( isset($_POST['txRequester']) && !empty($_POST['txRequester']) ) ? trim($_POST['txRequester']) : NULL;
	$txLiter = ( isset($_POST['txLiter']) && !empty($_POST['txLiter']) ) ? trim($_POST['txLiter']) : NULL;
	$proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : NULL;
	if($proj_id)
		$arr = array('proj_id'=>$proj_id);

	if($txItemEquip && $txRequester){
		$efq = $db->insertPrint('equip_fuel',array('equip_id'=>$txItemEquip,'requester'=>$txRequester,'liter'=>$txLiter,'remarks'=>$item));
		$db->query($efq);
		$ef_id = $db->insert_id();
		$arr = array_merge($arr,array('ef_id'=>$ef_id));
		$item = $db->getValue('equipment','concat(inventory_id," ",name)',array('equip_id'=>$txItemEquip)).' '.$item;
	}
	elseif($txItemEquip){
		$efq = $db->insertPrint('equip_fuel',array('equip_id'=>$txItemEquip,'liter'=>$txLiter,'remarks'=>$item));
		$db->query($efq);
		$ef_id = $db->insert_id();
		$arr = array_merge($arr,array('ef_id'=>$ef_id));
		$item = $db->getValue('equipment','concat(inventory_id," ",name)',array('equip_id'=>$txItemEquip)).' '.$item;
	}
	elseif($txRequester){
		$efq = $db->insertPrint('equip_fuel',array('requester'=>$txRequester,'liter'=>$txLiter,'remarks'=>$item));
		$db->query($efq);
		$ef_id = $db->insert_id();
		$arr = array_merge($arr,array('ef_id'=>$ef_id));
		$item = 'Requested By: '. $db->getValue('equip_user','concat(lname," ",fname)',array('eu_id'=>$txRequester));
	}

	$amount = ( isset($_POST['txAmount']) && !empty($_POST['txAmount']) ) ? functions::moneyToDouble($_POST['txAmount']) : '0';
	$amountIssue = ( isset($_POST['txAmountIssue']) && !empty($_POST['txAmountIssue']) ) ? functions::moneyToDouble($_POST['txAmountIssue']) : '0';

	$txDate = ( isset($_POST['txDate']) && !empty($_POST['txDate']) ) ? $_POST['txDate'] : '';
	$arrDate = explode("/",$txDate);
	$date = (isset($arrDate[2]) && isset($arrDate[0]) && isset($arrDate[1]) ) ? $arrDate[2].'-'.$arrDate[0].'-'.$arrDate[1] : NULL;

	if( $category  && $amountIssue && $vid && $vp_id && $item){
		$arr = array_merge($arr,array('vd_date'=>$date,'category_id'=>$category,'item'=>$item,'amount'=>$amount,'amount_issue'=>$amountIssue,'vp_id'=>$vp_id));
		$ins = $db->insertPrint('voucher_detail',$arr);
		$db->query($ins);
		$vd_id = $db->insert_id();
		$account_type = $db->getValue('voucher','vt_id',array('voucher_id'=>$vid));
		$v_no = $db->getValue('voucher','voucher_no',array('voucher_id'=>$vid));
		$chequeDate = $db->getValue('voucher','cheque_date',array('voucher_id'=>$vid));
		$as_date = ($chequeDate) ? $chequeDate : $date;
		$return = $amountIssue - $amount;
		if($return !=0 )
			$db->insert('account_statement',array('as_type'=>'credit','transaction'=>'cash return: '.$item,'description'=>'voucher: '.$v_no,'as_date'=>$as_date,'as_amount'=>round($return,2),'account_type'=>$account_type,'tag_id'=>$vd_id,'transaction_type'=>'cash return'));
	}
	functions::sendTo('voucher_part_add.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
}

if( isset($_POST['btnSave']) ){
	$arr = array();
	$txvd_id = ( isset($_POST['txvd_id']) && !empty($_POST['txvd_id']) ) ? functions::decode($_POST['txvd_id']) : '0';
	$particular_name=$db->getValue('voucher_particular','vp_title',array('voucher_id'=>$vid,'vp_id'=>$vp_id,'vtype'=>'cash'));
	$category = ( isset($_POST['txItemCat']) && !empty($_POST['txItemCat']) ) ? $_POST['txItemCat'] : NULL;
	$item = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : NULL;
	$txItemEquip = ( isset($_POST['txItemEquip']) && !empty($_POST['txItemEquip']) ) ? trim($_POST['txItemEquip']) : NULL;
	$txRequester = ( isset($_POST['txRequester']) && !empty($_POST['txRequester']) ) ? trim($_POST['txRequester']) : NULL;
	$txLiter = ( isset($_POST['txLiter']) && !empty($_POST['txLiter']) ) ? trim($_POST['txLiter']) : NULL;
	$ef_id = $db->getValue('voucher_detail','ef_id',array('vd_id'=>$txvd_id));

	$proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : NULL;

	if($proj_id)
		$arr = array('proj_id'=>$proj_id);
	else
		$db->query("UPDATE voucher_detail SET proj_id=NULL WHERE vd_id='".$db->clean($txvd_id)."'");

	if($ef_id){
		if($txItemEquip && $txRequester){
			$db->update('equip_fuel',array('equip_id'=>$txItemEquip,'requester'=>$txRequester,'liter'=>$txLiter,'remarks'=>$item),array('ef_id'=>$ef_id));
			$item = $db->getValue('equipment','concat(inventory_id," ",name)',array('equip_id'=>$txItemEquip)).' '.$item;
		}
		elseif($txItemEquip){
			$db->update('equip_fuel',array('equip_id'=>$txItemEquip,'liter'=>$txLiter,'remarks'=>$item),array('ef_id'=>$ef_id));
			#$db->query('UPDATE equip_fuel SET requester=NULL WHERE ef_id="'.$ef_id.'"');
			$db->update('equip_fuel',array('requester'=>NULL),array('ef_id'=>$ef_id));
			$item = $db->getValue('equipment','concat(inventory_id," ",name)',array('equip_id'=>$txItemEquip)).' '.$item;
		}
		elseif($txRequester){
			$db->update('equip_fuel',array('requester'=>$txRequester,'liter'=>$txLiter,'remarks'=>$item),array('ef_id'=>$ef_id));
			#$db->query('UPDATE equip_fuel SET equip_id=NULL WHERE ef_id="'.$ef_id.'"');
			$db->update('equip_fuel',array('equip_id'=>NULL),array('ef_id'=>$ef_id));
			$item = 'Requested By: '. $db->getValue('equip_user','concat(lname," ",fname)',array('eu_id'=>$txRequester));
		}
		else{
			#$db->query("UPDATE voucher_detail SET ef_id=NULL WHERE vd_id='".$db->clean($txvd_id)."'");
			$db->update('voucher_detail',array('ef_id'=>NULL),array('vd_id'=>$txvd_id));
			$db->delete('equip_fuel',array('ef_id'=>$ef_id));
		}
	}
	else{
		if($txItemEquip && $txRequester){
			$efq = $db->insertPrint('equip_fuel',array('equip_id'=>$txItemEquip,'liter'=>$txLiter,'requester'=>$txRequester,'remarks'=>$item));
			$db->query($efq);
			$ef_id = $db->insert_id();
			$arr = array_merge($arr,array('ef_id'=>$ef_id));
			$item = $db->getValue('equipment','concat(inventory_id," ",name)',array('equip_id'=>$txItemEquip)).' '.$item;
		}
		elseif($txItemEquip){
			$efq = $db->insertPrint('equip_fuel',array('equip_id'=>$txItemEquip,'liter'=>$txLiter,'remarks'=>$item));
			$db->query($efq);
			$ef_id = $db->insert_id();
			$arr = array_merge($arr,array('ef_id'=>$ef_id));
			$item = $db->getValue('equipment','concat(inventory_id," ",name)',array('equip_id'=>$txItemEquip)).' '.$item;
		}
		elseif($txRequester){
			$efq = $db->insertPrint('equip_fuel',array('requester'=>$txRequester,'liter'=>$txLiter,'remarks'=>$item));
			$db->query($efq);
			$ef_id = $db->insert_id();
			$arr = array_merge($arr,array('ef_id'=>$ef_id));
			$item = 'Requested By: '. $db->getValue('equip_user','concat(lname," ",fname)',array('eu_id'=>$txRequester));
		}
	}

	$amount = ( isset($_POST['txAmount']) && !empty($_POST['txAmount']) ) ? functions::moneyToDouble($_POST['txAmount']) : '0';
	$amountIssue = ( isset($_POST['txAmountIssue']) && !empty($_POST['txAmountIssue']) ) ? functions::moneyToDouble($_POST['txAmountIssue']) : '0';

	$txDate = ( isset($_POST['txDate']) && !empty($_POST['txDate']) ) ? $_POST['txDate'] : '';
	$arrDate = explode("/",$txDate);
	$date = (isset($arrDate[2]) && isset($arrDate[0]) && isset($arrDate[1]) ) ? $arrDate[2].'-'.$arrDate[0].'-'.$arrDate[1] : NULL;

	if( $category && $amountIssue && $txvd_id && $item){
		$arr = array_merge($arr,array('vd_date'=>$date,'category_id'=>$category,'item'=>$item,'amount'=>$amount,'amount_issue'=>$amountIssue,'vp_id'=>$vp_id));
		$db->update('voucher_detail',$arr,array('vd_id'=>$txvd_id));

		$account_type = $db->getValue('voucher','vt_id',array('voucher_id'=>$vid));
		$chequeDate = $db->getValue('voucher','cheque_date',array('voucher_id'=>$vid));
		$v_no = $db->getValue('voucher','voucher_no',array('voucher_id'=>$vid));
		$as_date = ($chequeDate) ? $chequeDate : $date;
		$return = $amountIssue - $amount;
		if($return==0 )
			$db->delete('account_statement',array('tag_id'=>$txvd_id));
		else{
			if( $db->getValue('account_statement','count(*)',array('tag_id'=>$txvd_id)) )
				$db->update('account_statement',array('as_amount'=>round($return,2),'transaction'=>'cash return: '.$item,'as_date'=>$as_date,'confirmned'=>1),array('tag_id'=>$txvd_id));
			else
				$db->insert('account_statement',array('as_type'=>'credit','transaction'=>'cash return: '.$item,'description'=>'voucher: '.$v_no,'as_date'=>$as_date,'as_amount'=>round($return,2),'account_type'=>$account_type,'tag_id'=>$txvd_id,'transaction_type'=>'cash return'));
		}
	}
	functions::sendTo($_SERVER['PHP_SELF'].'?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
}

if( isset($_REQUEST['vdidDel']) && !empty($_REQUEST['vdidDel']) ){
	$vdidDel = functions::decode($_REQUEST['vdidDel']);
	$ef_id = $db->getValue('voucher_detail','ef_id',array('vd_id'=>$vdidDel));
	$db->delete('voucher_detail',array('vd_id'=>$vdidDel));
	$db->delete('account_statement',array('tag_id'=>$vdidDel));
	$db->delete('equip_fuel',array('ef_id'=>$ef_id));
	functions::sendTo($_SERVER['PHP_SELF'].'?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
}

$editTrue=0;
$vdidEdt=0;
$td = $db->getValue('voucher','vdate',array('voucher_id'=>$vid));
$txDateEdt = explode("-",$td);
$txDate = $txDateEdt[1].'/'.$txDateEdt[2].'/'.$txDateEdt[0];
$pc_id='';
if( isset($_REQUEST['vdidEdt']) && !empty($_REQUEST['vdidEdt']) ){
	$vdidEdt = functions::decode($_REQUEST['vdidEdt']);
	$editTrue = $db->getValue('voucher_detail','count(*)',array('vd_id'=>$vdidEdt));
	$qvedt = $db->select('voucher_detail','*',array('vd_id'=>$vdidEdt));
	$rvedt = $db->fetch_array($qvedt);
	$category = ($category) ? $category : $rvedt['category_id'];
	$item = $rvedt['item'];
	if($ef_id = $rvedt['ef_id']){
		$equip_id = $db->getValue('equip_fuel','equip_id',array('ef_id'=>$ef_id));
		$requester = $db->getValue('equip_fuel','requester',array('ef_id'=>$ef_id));
		$item = $db->getValue('equip_fuel','remarks',array('ef_id'=>$ef_id));
		$liter = $db->getValue('equip_fuel','liter',array('ef_id'=>$ef_id));
	}
	$proj_id = $rvedt['proj_id'];
	$amount = $rvedt['amount'];
	$amountIssue = $rvedt['amount_issue'];
	$txDateEdt = explode("-",$rvedt['vd_date']);
	$txDate = $txDateEdt[1].'/'.$txDateEdt[2].'/'.$txDateEdt[0];
	$pc_id=$db->getValue('project','pc_id',array('proj_id'=>$proj_id));
}
$arrPayee = array();
$qTitle = $db->select('voucher_particular','DISTINCT vp_title',array('vtype'=>'cash'));
$namesTitle='';
while($rTitle=$db->fetch_array($qTitle)):
	$string = preg_replace("/'/",'"',$rTitle['vp_title']);
	$namesTitle .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesTitle .= '"--"';
  
$qItem = $db->select('voucher_detail','DISTINCT item',array());
$namesItem='';
while($rItem=$db->fetch_array($qItem)):
	$string = preg_replace("/'/",'"',$rItem['item']);
	$namesItem .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesItem .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Voucher Particular</title>
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
	<link href="../css/select2.min.css" rel="stylesheet">
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Particular Details</h2>
		</div>
		<div class="box-content" style="visibility:hidden;">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="<?php echo 'voucher_part_add_admin.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id)?>">Admin Charges</a></li>
				<li class="active"><a href="<?php echo 'voucher_part_add.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id)?>" style="opacity:.9">Project Charges</a></li>
			</ul>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="60%" align="center" border="0">
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Particular</label>
								<div class="controls">
									<input type="text" class="span6 typeahead" name="txPartName" id="txPartName" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesTitle;?>]' value="<?php echo $particular_name?>">
									<input type="submit" name="btnSelectPart" id="btnSelectPart" value="Select" class="btn btn-primary btn-small">
									<?php if($particular_name){?><input type="submit" name="btnEditParName" id="btnEditParName" value="Edit Name" class="btn btn-warning btn-small"><?php }?>
								</div>
							</div>
						</td>
					</tr>
				</table>
					<?php if($vp_id){?>
					<input type="hidden" name="txvd_id" id="txvd_id" value="<?php echo functions::encode($vdidEdt);?>">
					<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;">
					<tr>
						<th width="11%" scope="col"><div align="center">Date</div></th>
						<th width="20%" scope="col"><div align="center">Category</div></th>
						<th width="19%" scope="col"><div align="center">Item Detail</div></th>
						<th width="20%" scope="col"><div align="center">Charge To</div></th>
						<th width="10%" scope="col"><div align="center">Amount Issued</div></th>
						<th width="5%" scope="col"><div align="center">Amount Spent</div></th>
						<th width="8%" scope="col">&nbsp;</th>
					</tr>
					<tr bgcolor="#f7ebeb">
						<td><input type="text" class="input-medium datepicker" style="width: 80px;" name="txDate" id="txDate" value="<?php echo $txDate;?>"></td>
						<td>
							<div>
								<select name="txItemCat" id="txItemCat" data-rel="chosen">
									<option value="">--select--</option>
									<?php
									$qCat = $db->select('item_deduction','*',array(),'ORDER BY name');
									while($rCat = $db->fetch_array($qCat)):
									?>
									<option value="<?php echo $rCat['item_id']?>" <?php if($category==$rCat['item_id'])echo 'selected="selected"';?>><?php echo $rCat['name']?></option>
									<?php endwhile;?>
								</select>
								<span class="help-inline warning" name="msgtxCat" id="msgtxCat" style="font-weight:bold;"></span>
							</div>
						</td>
						<td>
							<div>
								<textarea name="txItem" id="txItem" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesItem;?>]'><?php echo $item;?></textarea>
							</div>
							<?php if($isFuel==='fuel'){?>
							<div align="center" style="padding: 20px;">OR</div>
							<div>
								<select name="txItemEquip" id="txItemEquip" data-rel="chosen">
									<option value="">--Select Equipment--</option>
									<?php
									$qEquip = $db->select('equipment','*',array(),'ORDER BY type');
									while($rEquip = $db->fetch_array($qEquip)):
									?>
									<option value="<?php echo $rEquip['equip_id']?>" <?php if($equip_id==$rEquip['equip_id'])echo 'selected="selected"';?>><?php echo $rEquip['inventory_id'].' '.$rEquip['name']?></option>
									<?php endwhile;?>
								</select>
							</div><br>
							<div>
								<select name="txRequester" id="txRequester" data-rel="chosen">
									<option value="">--Requested By--</option>
									<?php
									$qEquipReq = $db->select('equip_user','*',array(),'ORDER BY lname');
									while($rEquipReq = $db->fetch_array($qEquipReq)):
									?>
									<option value="<?php echo $rEquipReq['eu_id']?>" <?php if($requester==$rEquipReq['eu_id'])echo 'selected="selected"';?>><?php echo ucwords(strtolower($rEquipReq['lname'].', '.$rEquipReq['fname']));?></option>
									<?php endwhile;?>
								</select>
							</div><br>
							<div align="left">Liter: <input type="text" style="width:80px;" name="txLiter" id="txLiter" value="<?php echo $liter?>" onkeypress="return checkinput(this, event);">
							<?php }?>
							<span class="help-inline warning" id="msgtxItem" style="font-weight:bold;" name="msgtxItem"></span>
						</td>
						<td>
							<select name="selClient" id="selClient" data-rel="chosen" onChange="getProject(this.value)" style="width:270px;">
								<option value="">--All Client--</option>
								<?php 
								$qPC = $db->select('project_client','*',array(),'ORDER BY pc_name');
								while($rPC = $db->fetch_array($qPC)):
								?>
								<option value="<?php echo $rPC['pc_id']?>" <?php if($pc_id==$rPC['pc_id']){echo 'selected="selected"';} ?>><?php echo ucwords(strtolower($rPC['pc_name']));?></option>
								<?php endwhile;?>
							</select>
							<div id="divProj">
								<select name="selProj" id="selProj" style="width:270px;">
									<option value="">--select--</option>
									<?php
									if($pc_id)
									$qProj = $db->select('project','*',array('pc_id'=>$pc_id),'ORDER BY proj_name');
									else
									$qProj = $db->select('project','*',array(),'ORDER BY proj_name');
									while($rProj = $db->fetch_array($qProj)):
									?>
									<option value="<?php echo $rProj['proj_id']?>" <?php if($proj_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo ucwords(strtolower($rProj['proj_name']));?></option>
									<?php endwhile;?>
								</select>
							</div>
							<span class="help-inline warning" name="msgtxProj" id="msgtxProj" style="font-weight:bold;"></span>
						</td>
						<td>
							<input type="text" name="txAmountIssue" id="txAmountIssue" style="width: 90px;" value="<?php echo ($amountIssue) ? functions::formatMoney($amountIssue) : 0;?>" size="30" onkeyup="FormatCurrency(this);" />
							<span class="help-inline warning" id="msgtxAmountIssue" style="font-weight:bold;" name="msgtxAmountIssue"></span>
						</td>
						<td>
							<input name="txAmount" type="text" id="txAmount" style="width: 90px;" value="<?php echo ($amount) ? functions::formatMoney($amount) : 0;?>" size="30" onkeyup="FormatCurrency(this);" />
							<span class="help-inline warning" id="msgtxAmount" style="font-weight:bold;" name="msgtxAmount"></span>
						</td>
						<td>
							<div align="center">
							<?php
							if($editTrue){
								echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-mini btn-primary">';
							?>
								<a id="cancl" class="btn btn-mini" title="Cancel Edit" data-rel="tooltip" href="?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>?>">Cancel</a>
							<?php
							}
							else
								echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary btn-small">';
							?>
							</div>
						</td>
					</tr>
					<tr><td colspan="6" height="25"></td></tr>
					<?php 
					$vdate='';$total_amount=0;$total_amount_issue=0;
					$qvDetails = $db->select('voucher_detail','*',array('vp_id'=>$vp_id),'ORDER BY vd_date');
					while($rvDetails = $db->fetch_array($qvDetails)):
						$total_amount_issue += $rvDetails['amount_issue'];
						$total_amount += $rvDetails['amount'];
						$catName = $db->getValue('item_deduction','name',array('item_id'=>$rvDetails['category_id']));
						$lnkFuel = (preg_match("/fuel/i", $catName)) ? '&fl='.functions::encode('fuel') : '';
					?>
					<tr>
						<td>
							<?php
							if($vdate != $rvDetails['vd_date']){
								$vdate = $rvDetails['vd_date'];
								echo functions::datearr($rvDetails['vd_date']);
							}
							?>
						</td>
						<td><?php echo $catName;?></td>
						<td><?php echo $rvDetails['item']?></td>
						<td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rvDetails['proj_id']));?></td>
						<td><?php echo functions::formatMoney($rvDetails['amount_issue']);?></td>
						<td><?php echo functions::formatMoney($rvDetails['amount']);?></td>
						<td>
							<div align="center">
								<a id="del<?php echo $rvDetails['vd_id']?>" class="btn btn-mini btn-warning" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="voucher_part_add.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>&vdidDel=<?php echo functions::encode($rvDetails['vd_id']);?>"><i class="halflings-icon white trash"></i></a>
								<a id="edit<?php echo $rvDetails['vd_id']?>" class="btn btn-mini btn-info" title="Update this item" data-rel="tooltip" href="voucher_part_add.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>&vdidEdt=<?php echo functions::encode($rvDetails['vd_id']);?><?php echo $lnkFuel?>"><i class="halflings-icon white pencil"></i></a>
							</div>
						</td>
					</tr>
					<?php endwhile;?>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td><div align="right"><strong>Total Amount</strong></div></td>
						<td><strong><?php echo functions::formatMoney($total_amount_issue)?></strong></td>
						<td><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
						<td></td>
					</tr>
				</table>
				<?php }#if $vp_id?><p>&nbsp;</p>
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
<script src="../js/select2.js"></script>
<script>
function delt(){
	if(confirm('Do you want to remove this item?'))
		return true;
	else
		return false; 
}
$(document).ready(function(){
	var res = false;
	var selCat;
	$('#txItemCat').change(function(){
		var catID = $('#txItemCat').val();
		selCat = $("#txItemCat option:selected").text();
		var vEdit = <?php echo ($vdidEdt) ? 1 : 0?>;
		if(vEdit==1){
			if(selCat.toLowerCase().indexOf("fuel") >= 0){
				window.location="<?php echo $_SERVER['PHP_SELF']?>?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>&fl=<?php echo functions::encode('fuel');?>&catID="+catID+"&vdidEdt=<?php echo functions::encode($vdidEdt)?>";
			}
			else{
				var isFuel = "<?php echo $isFuel?>"
				if( isFuel != 0 ){
					window.location="<?php echo $_SERVER['PHP_SELF']?>?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>&catID="+catID+"&vdidEdt=<?php echo functions::encode($vdidEdt)?>"
				}
			}
		}
		else if(vEdit==0){
			if (selCat.toLowerCase().indexOf("fuel") >= 0){
				window.location="<?php echo $_SERVER['PHP_SELF']?>?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>&fl=<?php echo functions::encode('fuel');?>&catID="+catID;
			}
			else{
				var isFuel = "<?php echo $isFuel?>"
				if( isFuel != 0 ){
					window.location="<?php echo $_SERVER['PHP_SELF']?>?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>&catID="+catID
				}
			}
		}
	});
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxCat').html("");
		$('#msgtxItem').html("");
		$('#msgtxProj').html("");
		$('#msgtxAmount').html("");
		$('#msgtxAmountIssue').html("");

		if( $('#txItemCat').val()=="" ){
			$('#msgtxCat').html("Category Required!");
			res=false;
		}
		<?php if($isFuel==='fuel' || $equip_id){?>
		else if( $('#txItem').val()=="" && $('#txItemEquip').val()=="" ){
		<?php }else{?>
		else if( $('#txItem').val()==""){
		<?php }?>
			$('#msgtxItem').html("Item Required!");
			$('#txItem').focus();
			res=false;
		}
		else if( $('#txAmountIssue').val()==0 ){
			$('#msgtxAmountIssue').html("Amount Required!");
			$('#txAmountIssue').focus();
			res=false;
		}
		else
			res=true;
		return res;
	});

	function selDt(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?vid="+PiEwgD}
});
function getXMLHTTP() { //fuction to return the xml http object
	var xmlhttp=false;
	try{
		xmlhttp=new XMLHttpRequest();
	}
	catch(e){
		try{
			xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch(e){
			try{
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch(e1){
				xmlhttp=false;
			}
		}
	}
	return xmlhttp;
}
  
function getProject(pcid) {
	var strURL="drop_down_sel.php?pcid="+pcid;
	var req = getXMLHTTP();

	if (req){
		req.onreadystatechange = function() {
			if (req.readyState == 4) {
				// only if "OK"
				if (req.status == 200) {
					document.getElementById('divProj').innerHTML=req.responseText;
					$("#selProj").select2();
				}else{
					alert("Problem while using XMLHTTP:\n" + req.statusText);
				}
			}
		}
		req.open("GET", strURL, true);
		req.send(null);
	}
}
$("#selProj").select2(); 
</script>
<!-- end: JavaScript-->
</body>
</html>