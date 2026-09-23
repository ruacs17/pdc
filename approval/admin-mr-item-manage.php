<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/MoneytoWords.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$last_of_old_form = '2021-01-26';
function position($emp_id){
	global $db;
	$countPos=0;$position='';
	$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
	while($rPos = $db->fetch_array($qPos)):
		if($countPos)
			$position .= ' /<br>';
		$position .= $rPos['pos_name'];
		$countPos++;
	endwhile;
	return $position;
}

$mr_id = (isset($_REQUEST['mr_id']) && !empty($_REQUEST['mr_id']) ) ? functions::decode($_REQUEST['mr_id']) : 0;
$_SESSION['notif_id_list']=$mr_id;
$eid = ( isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : '';
if( isset($_POST['btnAdd']) ){
	$selEquip = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? functions::decode($_POST['selEquip']) : 0;
	$qty = ( isset($_POST['txQuantity']) && !empty($_POST['txQuantity']) ) ? trim($_POST['txQuantity']) : 0;
	$unit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : "";
	$remark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : '';
	$_SESSION['notif_warning']='Fail to Add!';
	if( !empty($selEquip) && !empty($qty) && !empty($unit) ){
		$ins = $db->insert('mr_item',array('mr_id'=>$mr_id,'equip_id'=>$selEquip,'qty'=>$qty,'unit'=>$unit,'remark'=>$remark));
		if($ins){
			$_SESSION['notif_id2_list']=$ins;
			$_SESSION['notif_success']='New Item Added!';
			unset($_SESSION['notif_warning']);
		}
	}
	functions::sendTo(functions::pageName().'?mr_id='.functions::encode($mr_id));
	die();
}

if( isset($_POST['btnSave']) ){
	$mri_id_Edt = ( isset($_POST['txItmEdt']) && !empty($_POST['txItmEdt']) ) ? functions::decode($_POST['txItmEdt']) : 0;
	$selEquip = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? functions::decode($_POST['selEquip']) : 0;
	$qty = ( isset($_POST['txQuantity']) && !empty($_POST['txQuantity']) ) ? trim($_POST['txQuantity']) : 0;
	$unit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : "";
	$remark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : '';
	$_SESSION['notif_warning']='Fail to Update!';
	if( !empty($mri_id_Edt) && !empty($selEquip) && !empty($qty) && !empty($unit) ){
		$db->update('mr_item',array('equip_id'=>$selEquip,'qty'=>$qty,'unit'=>$unit,'remark'=>$remark),array('mri_id'=>$mri_id_Edt));
		$_SESSION['notif_success']='Changes Saved!';
		$_SESSION['notif_id2_list']=$mri_id_Edt;
		unset($_SESSION['notif_warning']);
	}
	functions::sendTo(functions::pageName().'?mr_id='.functions::encode($mr_id));
	die();
}

if( isset($_REQUEST['txItmDel']) && !empty($_REQUEST['txItmDel']) ){
	$txItmDel = functions::decode($_REQUEST['txItmDel']);
	$db->delete('mr_item',array('mri_id'=>$txItmDel));
	$_SESSION['notif_warning']='Item Removed!';
	functions::sendTo(functions::pageName().'?mr_id='.functions::encode($mr_id));
	die();
}
$editTrue=0;
$txItmEdt='';
$qtyAvailable=0;
$equip_id=$eid; $qty='';$remark='';$unit='';
if( isset($_REQUEST['txItmEdt']) && !empty($_REQUEST['txItmEdt']) ){
	$txItmEdt = functions::decode($_REQUEST['txItmEdt']);
	$editTrue = $db->getValue('mr_item','count(*)',array('mri_id'=>$txItmEdt));
	$qvedt = $db->select('mr_item','*',array('mri_id'=>$txItmEdt));
	$rvedt = $db->fetch_array($qvedt);
	$qty = $rvedt['qty'];
	$qtyAvailable = $qty;
	$remark = $rvedt['remark'];
	$unit = $rvedt['unit'];
	$equip_id = $rvedt['equip_id'];

	$allUnit = $db->getValue('equipment','quantity',array('equip_id'=>$equip_id)) + $qty;
	$countUsed = $db->getValue('mr_item','sum(qty)',array('equip_id'=>$equip_id,'returned'=>'1'));
	#$qtyAvailable = $allUnit - $countUsed; //ibalik ni if ma finalize na ang ila borrowing
	$qtyAvailable=$allUnit;
}

if($eid){
	$unit = $db->getValue('equipment','unit',array('equip_id'=>$eid));
	$allUnit = $db->getValue('equipment','quantity',array('equip_id'=>$eid));
	$countUsed = $db->getValue('mr_item','sum(qty)',array('equip_id'=>$eid,'returned'=>'1'));
	#$qtyAvailable = $allUnit - $countUsed; //ibalik ni if ma finalize na ang ila borrowing
	$qtyAvailable=$allUnit;
}


$qUnit = $db->query('SELECT DISTINCT unit FROM mr_item');
$namesUnit='';
while($rUnit=$db->fetch_array($qUnit)):
	$string = preg_replace("/'/",'"',$rUnit['unit']);
	$namesUnit .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesUnit .= '"--"';


$qMR = $db->select('mr','*',array('mr_id'=>$mr_id));
$rMR = $db->fetch_array($qMR);
$mr_emp = $rMR['mr_emp'];
$mr_date = $rMR['mr_date'];
$return_date = $rMR['mreturn_date'];
$employer = $db->getValue('mr','employer',array('mr_id'=>$mr_id));
$employer_add = $db->getValue('mr','employer_add',array('mr_id'=>$mr_id));
$assigned_location = $db->getValue('mr','assigned_location',array('mr_id'=>$mr_id));
$proj_id = $db->getValue('mr','proj_id',array('mr_id'=>$mr_id));
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$proj_id));
$date_hired = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$mr_emp),'ORDER BY ews_id LIMIT 1');

$qEmpAdd = $db->select('employee','*',array('emp_id'=>$mr_emp));
$rEA = $db->fetch_array($qEmpAdd);
$curProvince = $db->getValue('refprovince','provDesc',array('provCode'=>$rEA['curr_province']));
$curCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$rEA['curr_cityMun']));
$curBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$rEA['curr_add']));
$address = $rEA['curr_street'].' '.strtoupper($curBrngy).', '.$curCity.', '.$curProvince;
$position = position($mr_emp);
$marital_status = $rEA['civil_status'];
$eu = ucwords(strtolower($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$mr_emp))));


$prepared_date = $rMR['prepared_date'];
$prepared_byID = $rMR['prepared_id'];
$prepared_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$prepared_byID)));
$prepared_by_title = $rMR['prepared_by_title'];

$checked_date = $rMR['checked_date'];
$checked_byID = $rMR['checked_id'];
$checked_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$checked_byID)));
$checked_by_title = $rMR['checked_by_title'];

$recommended_date = $rMR['recommended_date'];
$recommended_byID = $rMR['recommended_id'];
$recommended_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$recommended_byID)));
$recommended_by_title = $rMR['recommended_by_title'];

$approved_date = $rMR['approved_date'];
$approved_byID = $rMR['approved_id'];
$approved_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$approved_byID)));
$approved_by_title = $rMR['approved_by_title'];

$released_date = $rMR['released_date'];
$released_byID = $rMR['released_id'];
$released_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$released_byID)));
$released_by_title = $rMR['released_by_title'];

$received_date = $rMR['received_date'];
$received_byID = $rMR['received_id'];
$received_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$received_byID)));
$received_by_title = $rMR['received_by_title'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>MR Manage</title>
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
	<style type="text/css">
	.padParLeft{padding-left:10px;}
	.padAmLeft{padding-left:60px;}
	.bisaya{color:red;font-style: italic; padding-bottom:5px;}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
	<form method="post">
		<div class="row-fluid">
			<div class="box span12">
				<div class="box-header" data-original-title>
					<h2><i class="halflings-icon white edit"></i><span class="break"></span>MR Item Manage</h2>
				</div>
				<div class="box-content">
					<ul class="nav tab-menu nav-tabs">
						<li><a href="admin-mr-return-item-manage.php?mr_id=<?php echo functions::encode($mr_id);?>">MR Return</a></li>
						<li class="active"><a href="#" style="opacity:.9">MR Release</a></li>
					</ul>
				</div>
				<div align="right">
					<a id="mrUpdate" href="admin-mr-edit.php?mr_id=<?php echo functions::encode($mr_id);?>" class="btn btn-info btn-small"><i class="halflings-icon white pencil"></i> Update This MR</a>
					<a id="mrPrint" href="admin-mr-release-print.php?mr_id=<?php echo functions::encode($mr_id);?>" class="btn btn-info btn-small" title="Print in English"><i class="halflings-icon white print"></i></a>&nbsp;
					<a id="mrPrint" href="admin-mr-release-print-bis.php?mr_id=<?php echo functions::encode($mr_id);?>" class="btn btn-warning btn-small" title="Print in Bisaya"><i class="halflings-icon white print"></i></a>&nbsp;
				</div><br>
				<div class="box-content">
					<table width="100%" border="0" align="center" style="font-size: 12px;">
						<tr>
							<td>
								<table width="100%" border="0" style="font-size: 12px;">
									<tr>
										<td height="15" align="center">
											<div><strong>MEMORANDUM OF RECEIPT <br> MR No. <?php echo $db->getValue('mr','mr_no',array('mr_id'=>$mr_id));?></strong></div>
										</td>
									</tr>
									<tr>
										<td>
											<div>
												<div align="justify">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I, <strong><u><?php echo $eu?></u></strong>, of legal age, <strong><u><?php echo $marital_status?></u></strong> and a resident of <strong><u><?php echo $address?></u></strong>
												has been employed as <strong><u><?php echo $position?></u></strong> with an office address in <strong><u><?php echo $employer_add?></u></strong> from <strong><u><?php echo functions::datearr($date_hired)?></u></strong> to present.
												I am currently assigned at <strong><u><?php echo $assigned_location?></u></strong> for the <strong><u><?php echo $proj_name?></u></strong>  due date on or before <strong><u><?php echo functions::datearr($return_date); ?></u></strong>.</div>
											</div>
										</td>
									</tr>
									<tr height="5">
										<td></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td><div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;That I have received the following tools and equipment:</div>
								<input type="hidden" name="txItmEdt" id="txItmEdt" value="<?php echo functions::encode($txItmEdt);?>">
								<table width="100%" border="0" align="center" class="table">
									<thead>
										<tr style="background-color:#CCC;">
											<th width="20%" scope="col"><div align="center">Equipment</div></th>
											<th width="10%" scope="col"><div align="center">Quantity</div></th>
											<th width="8%" scope="col"><div align="center">Unit</div></th>
											<th width="16%" scope="col"><div align="center">Remark</div></th>
											<th width="12%" scope="col">&nbsp;</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<div align="left">
													<select name="selEquip" id="selEquip" data-rel="chosen" style="width:550px;font-size:12px;height:60px;" onchange='selEq(this.value)'>
														<option value="">-- Select Equipment --</option>
														<?php $qEquip = $db->select('equipment','*',array(),'ORDER BY type');
														while($rEquip = $db->fetch_array($qEquip)):
														?>
														<option value="<?php echo functions::encode($rEquip['equip_id'])?>" <?php if($equip_id==$rEquip['equip_id'])echo 'selected="selected"';?>><?php echo strtolower($rEquip['inventory_id']).' '.ucwords(strtolower($rEquip['equip_desc'])).' '.$rEquip['plate_no'].' '.$rEquip['serial_no'];?></option>
														<?php endwhile;?>
													</select><br>
													<span class="help-inline warning" id="msgtxEquip" style="font-weight:bold;" name="msgtxEquip"></span>
												</div>
											</td>
											<td>
												<div align="center">
													<select name="txQuantity" id="txQuantity" style="width:60px;">
														<?php for($i=0;$i<=$qtyAvailable;$i++):?>
														<option value="<?php echo $i;?>" <?php if($qty==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
														<?php endfor;?>
													</select>
												</div>
												<div align="center">
													<span class="warning" id="msgtxQuantity" style="font-weight:bold;" name="msgtxQuantity"></span>
												</div>
											</td>
											<td>
												<div align="center"><input type="text" style="width:80px;text-align:center;" name="txUnit" id="txUnit" value="<?php echo $unit?>" readonly></div>
												<span class="warning" id="msgtxUnit" style="font-weight:bold;" name="msgtxUnit"></span>
											</td>
											<td><div align="center"><input type="text" style="width:300px;" name="txRemark" id="txRemark" value="<?php echo $remark?>"></div></td>
											<td>
												<div align="center">
												<?php 
												if($editTrue){
													?>
													<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary btn-mini">
													<a class="btn btn-warning btn-mini" href="?mr_id=<?php echo functions::encode($mr_id)?>">Cancel</a>
												<?php }
												else
													echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary btn-mini">';
												?>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
								<table id="tblist" width="100%" border="1" style="font-size: 11px;">
									<thead>
										<tr style="background-color:#CCC;">
											<th width="4%" scope="col"><div align="center">Qty</div></th>
											<th width="4%" scope="col"><div align="center">Unit</div></th>
											<th width="25%" scope="col"><div align="left">Equipment</div></th>
											<th width="7%" scope="col"><div align="center">Serial No.</div></th>
											<th width="10%" scope="col"><div align="center">Inventory No.</div></th>
											<th width="7%" scope="col"><div align="center">Unit Cost</div></th>
											<th width="8%" scope="col"><div align="center">Date Purchased</div></th>
											<th width="7%" scope="col"><div align="center">Estimated<br>Useful<br>Life</div></th>
											<th width="14%" scope="col"><div align="center">Remark</div></th>
											<th width="5%" scope="col">&nbsp;</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$qItem = $db->select('mr_item','*',array('mr_id'=>$mr_id));
										while($rItem = $db->fetch_array($qItem)):
											$equipmentName = '';
											$qEqp = $db->select('equipment','*',array('equip_id'=>$rItem['equip_id']));
											while($rEqp = $db->fetch_array($qEqp)):
											$equipmentName = ucwords(strtolower($rEqp['equip_desc']." ".$rEqp['plate_no']));
											endwhile;
										?>
										<tr id="rw<?php echo $rItem['mri_id'];?>">
											<td height="30"><div align="center"><?php echo $rItem['qty'];?></div></td>
											<td><div align="center"><?php echo $rItem['unit'];?></div></td>
											<td><?php echo $equipmentName;?></td>
											<td><div align="center"><?php echo $db->getValue('equipment','serial_no',array('equip_id'=>$rItem['equip_id']));?></div></td>
											<td><div align="center"><?php echo $db->getValue('equipment','inventory_id',array('equip_id'=>$rItem['equip_id']));?></div></td>
											<td><div align="center"><?php echo functions::formatMoney($db->getValue('equipment','price',array('equip_id'=>$rItem['equip_id'])));?></div></td>
											<td><div align="center"><?php echo functions::datearr($db->getValue('equipment','date_acquired',array('equip_id'=>$rItem['equip_id'])));?></div></td>
											<td><div align="center"><?php $pl = $db->getValue('equipment','round(datediff(proposed_life,date_acquired)/365,1)',array('equip_id'=>$rItem['equip_id'])); echo ($pl) ? $pl. ' Year/s' : '';?></div></td>
											<td><div align="center"><?php echo $rItem['remark'];?></div></td>
											<td>
												<div align="center">
													<a id="edit<?php echo $rItem['mri_id'];?>" class="btn btn-mini btn-warning" title="Update this item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?mr_id=<?php echo functions::encode($mr_id);?>&txItmEdt=<?php echo functions::encode($rItem['mri_id']);?>"><i class="halflings-icon white pencil"></i></a>
													<a id="del<?php echo $rItem['mri_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?mr_id=<?php echo functions::encode($mr_id);?>&txItmDel=<?php echo functions::encode($rItem['mri_id']);?>"><i class="halflings-icon white trash"></i></a>
												</div>
											</td>
										</tr>
										<?php endwhile;?>
									</tbody>
								</table>
							</td>
						</tr>
						<tr>
							<td height="20"><div align="center"><strong>---------- NOTHING FOLLOWS ----------</strong></div></td>
						</tr>
						<tr>
							<td align="justify">At present, the above tools and equipment was under my custody that I am accountable and liable for its money value in case of illegal, improper or unauthorized use or misapplication and damage to property. I promise that I am responsible to properly take good care of the above mentioned tools and equipment and use for official projects function only. </td>
						</tr>
						<tr>
							<td height="20"><div align="center">&nbsp;</div></td>
						</tr>
						<tr>
							<td align="center">
								<?php if($mr_date<=$last_of_old_form){ ?>
								<table width="100%" border="0" style="font-size: 12px;">
									<tr>
										<td align="center" width="20%">Prepared by:</td>
										<td align="center" width="20%">Checked by: </td>
										<td align="center" width="20%">Recommended by: </td>
									</tr>
									<tr>
										<td height="30" align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $prepared_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
										<td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $checked_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
										<td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $recommended_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
									</tr>
									<tr>
										<td height="10" align="center"><div><strong><?php echo $prepared_by_title;?></strong></div></td>
										<td align="center"><div><strong><?php echo $checked_by_title;?></strong></div></td>
										<td align="center"><div><strong><?php echo $recommended_by_title;?></strong></div></td>
									</tr>
									<tr>
										<td height="20" align="center" valign="bottom">Date: <strong><?php #echo functions::datearr($prepared_date);?></strong></td>
										<td align="center" valign="bottom">Date: <strong><?php #echo functions::datearr($checked_date);?></strong></td>
										<td align="center" valign="bottom">Date: <strong><?php #echo functions::datearr($recommended_date);?></strong></td>
									</tr>
								</table><br><br><br>
								<table width="100%" border="0" style="font-size: 12px;">
									<tr>
										<td align="center" width="20%">Approved by:</td>
										<td align="center" width="20%">Released by:</td>
										<td align="center" width="20%">Received by:</td>
									</tr>
									<tr>
										<td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $approved_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
										<td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $released_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
										<td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $received_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
									</tr>
									<tr>
										<td align="center"><strong><?php echo $approved_by_title;?></strong></td>
										<td align="center"><strong><?php echo $released_by_title;?></strong></td>
										<td align="center"><strong><?php echo $received_by_title;?></strong></td>
									</tr>
									<tr>
										<td align="center" valign="bottom">Date: <strong><?php #echo functions::datearr($approved_date);?></strong></td>
										<td align="center" valign="bottom">Date: <strong><?php #echo functions::datearr($released_date);?></strong></td>
										<td align="center" valign="bottom">Date: <strong><?php #echo functions::datearr($received_date);?></strong></td>
									</tr>
								</table>
								<?php
								}else{
									#$recommended_by_new = 
									?>
								<table width="100%" border="0" style="font-size: 12px;">
									<tr>
										<td align="center" width="20%">Prepared and Checked by:</td>
										<td align="center" width="20%">Recommended by: </td>
										<td align="center" width="20%">Approved by:</td>
									</tr>
									<tr>
										<td height="30" align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $prepared_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
										<td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $recommended_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
										<td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $approved_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
									</tr>
									<tr>
										<td height="10" align="center"><div><strong><?php echo $prepared_by_title;?></strong></div></td>
										<td align="center"><div><strong><?php echo $recommended_by_title;?></strong></div></td>
										<td align="center"><strong><?php echo $approved_by_title;?></strong></td>
									</tr>
									<tr>
										<td height="20" align="center" valign="bottom">Date: <strong></strong></td>
										<td align="center" valign="bottom">Date: <strong></strong></td>
										<td align="center" valign="bottom">Date: <strong></strong></td>
									</tr>
								</table><br><br><br>
								<table width="100%" border="0" style="font-size: 12px;">
									<tr>
										<td align="center" width="20%">Released by:</td>
										<td align="center" width="20%">Delivered by: </td>
										<td align="center" width="20%">Checked and Received by:</td>
									</tr>
									<tr>
										<td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $released_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
										<td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $checked_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
										<td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $received_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
									</tr>
									<tr>
										<td align="center"><strong><?php echo $released_by_title;?></strong></td>
										<td align="center"><div><strong><?php echo $checked_by_title;?></strong></div></td>
										<td align="center"><strong><?php echo $received_by_title;?></strong></td>
									</tr>
									<tr>
										<td align="center" valign="bottom">Date: <strong></strong></td>
										<td align="center" valign="bottom">Date: <strong></strong></td>
										<td align="center" valign="bottom">Date: <strong></strong></td>
									</tr>
								</table>
								<?php } ?>
							</td>
						</tr>
					</table><br><br><br>
				</div>
			</div>
		</div>
	</form>
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
<script>function selEq(PiEwgD){window.location="<?php echo functions::pageName()?>?eid="+PiEwgD+"&mr_id=<?php echo functions::encode($mr_id)?>"}</script>
<script>
function delt(){
	if(confirm('Do you want to remove this?'))
		return true;
	else
		return false; 
}
$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxEquip').html("");
		$('#msgtxQuantity').html("");

		if( $('#selEquip').val()=="" ){
			$('#msgtxEquip').html("Specify Equipment!");
			$('#selEquip').focus();
			res=false;
		}
		else if( $('#txQuantity').val()=="0" ){
			$('#msgtxQuantity').html("Quantity Required!");
			$('#txQuantity').focus();
			res=false;
		}
		else
			res=true;
		return res;
	});
});
</script>
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<?php if(isset($_SESSION['notif_id2_list'])){ ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id2_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id2_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
});
</script>
<?php unset($_SESSION['notif_id2_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>