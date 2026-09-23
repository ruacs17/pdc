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
$mri_id_Edt=(isset($_REQUEST['txItmEdt']) && !empty($_REQUEST['txItmEdt']) ) ? functions::decode($_REQUEST['txItmEdt']) : 0;

if( isset($_POST['btnSave']) ){
	$mri_id_Edt = ( isset($_POST['txItmEdt']) && !empty($_POST['txItmEdt']) ) ? functions::decode($_POST['txItmEdt']) : NULL;
	$remark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : '';
	$selReturned = ( isset($_POST['selReturned']) && !empty($_POST['selReturned']) ) ? trim($_POST['selReturned']) : NULL;
	$txReturnDate = ( isset($_POST['txReturnDate']) && functions::valid_date($_POST['txReturnDate']) ) ? $_POST['txReturnDate'] : NULL;
	$_SESSION['notif_warning']='Fail to Update!';
	if( !empty($mri_id_Edt) ){
		$db->update('mr_item',array('remark'=>$remark,'mr_status'=>$selReturned,'return_date'=>$txReturnDate),array('mri_id'=>$mri_id_Edt));
		$_SESSION['notif_success']='Changes Saved!';
		unset($_SESSION['notif_warning']);
		$_SESSION['notif_id3_list']=$mri_id_Edt;
	}
	functions::sendTo(functions::pageName().'?mr_id='.functions::encode($mr_id));
	die();
}

$editTrue=0;
$txItmEdt='';
$equip_id=''; $qty='';$remark='';$unit=''; $returned='';$mr_status='';
if( isset($_REQUEST['txItmEdt']) && !empty($_REQUEST['txItmEdt']) ){
	$txItmEdt = functions::decode($_REQUEST['txItmEdt']);
	$editTrue = $db->getValue('mr_item','count(*)',array('mri_id'=>$txItmEdt));
	$qvedt = $db->select('mr_item','*',array('mri_id'=>$txItmEdt));
	$rvedt = $db->fetch_array($qvedt);
	$qty = $rvedt['qty'];
	$remark = $rvedt['remark'];
	$unit = $rvedt['unit'];
	$equip_id = $rvedt['equip_id'];
	$returned = $rvedt['returned'];
	$mr_status = $rvedt['mr_status'];
	$return_date = (functions::valid_date($rvedt['return_date'])) ? $rvedt['return_date'] : '';
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
$mreturn_date = $rMR['mreturn_date'];
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

$ret_turnover_byID = $rMR['ret_turnover_id'];
$ret_turnover_by_title = $rMR['ret_turnover_by_title'];
$ret_turnover_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$ret_turnover_byID)));
$ret_turnover_date = $rMR['ret_turnover_date'];

$ret_received_byID = $rMR['ret_received_id'];
$ret_received_by_title = $rMR['ret_received_by_title'];
$ret_received_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$ret_received_byID)));
$ret_received_date = $rMR['ret_received_date'];

$ret_checked_byID = $rMR['ret_checked_id'];
$ret_checked_by_title = $rMR['ret_checked_by_title'];
$ret_checked_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$ret_checked_byID)));
$ret_checked_date = $rMR['ret_checked_date'];

$ret_noted_byID = $rMR['ret_noted_id'];
$ret_noted_by_title = $rMR['ret_noted_by_title'];
$ret_noted_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$ret_noted_byID)));
$ret_noted_date = $rMR['ret_noted_date'];

$mr_remarks = $rMR['mr_remarks'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>MR Return Manage</title>
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
				<li class="active"><a href="#" style="opacity:.9">MR Return</a></li>
				<li><a href="admin-mr-item-manage.php?mr_id=<?php echo functions::encode($mr_id);?>">MR Release</a></li>
			</ul>
		</div>
		<div align="left">
			<table width="100%" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td width="50%">
						<div align="left">
							<table width="180" cellspacing="0" cellpadding="0" border="0" align="left">
								<tr>
									<td width="10" height='30'><div style="background-color:#fcb77b; width:20px;">&nbsp;</div></td>
									<td width="90"> Unreturned</td>
								</tr>
							</table>
						</div>
					</td>
					<td>
						<div align="right">
							<a id="mrUpdate" href="admin-mr-return-edit.php?mr_id=<?php echo functions::encode($mr_id);?>" class="btn btn-info btn-small"><i class="halflings-icon white pencil"></i> Update Return Slip</a>
							<a id="mrPrint" href="admin-mr-return-print.php?mr_id=<?php echo functions::encode($mr_id);?>" class="btn btn-info btn-small"><i class="halflings-icon white print"></i></a>&nbsp;
						</div>
					</td>
				</tr>
			</table>
		</div><br><br>
		<div class="box-content">
			<table width="100%" border="0" align="center" style="font-size: 12px;">
				<tr>
					<td>
						<table width="100%" border="0" style="font-size: 12px;">
							<tr>
								<td height="15" align="center">
									<div><strong>RETURN SLIP <br> MR No. <?php echo $db->getValue('mr','mr_no',array('mr_id'=>$mr_id));?></strong></div>
								</td>
							</tr>
							<tr>
								<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I, <strong><u><?php echo $eu?></u></strong>, of legal age, <strong><u><?php echo $marital_status?></u></strong> and a resident of <strong><u><?php echo $address?></u></strong> has been employed as <strong><u><?php echo $position?></u></strong> at <strong><u><?php echo $employer?></u></strong> with an office address in <strong><u><?php echo $employer_add?></u></strong> from <strong><u><?php echo functions::datearr($date_hired)?></u></strong> to present. I am currently assigned at <strong><u><?php echo $assigned_location?></u></strong> for the <strong><u><?php echo $proj_name?></u></strong> project.</td>
							</tr>
							<tr height="5">
								<td></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;That I have to return the following tools and equipments under my accountability:
						<?php if($mri_id_Edt){?>
						<div align="center"><br><br><br>
							<input type="hidden" name="txItmEdt" id="txItmEdt" value="<?php echo functions::encode($txItmEdt);?>">
							<table width="95%" border="0" align="center">
								<tr>
									<th width="30%" scope="col" height="30"><div align="center">Equipment</div></th>
									<th width="25%" scope="col"><div align="center">Remark</div></th>
									<th width="12%" scope="col"><div align="center">Status</div></th>
									<th width="22%" scope="col"><div align="center">Return Date</div></th>
									<th width="15%" scope="col">&nbsp;</th>
								</tr>
								<tr>
									<td>
										<div align="center">
										<?php
										$qEqp = $db->select('equipment','*',array('equip_id'=>$equip_id));
										while($rEqp = $db->fetch_array($qEqp)):
										$equipmentName = ucwords(strtolower($rEqp['equip_desc']." ".$rEqp['plate_no']));
										endwhile;
										echo $equipmentName;
										?>
										</div>
									</td>
									<td><div align="center"><input type="text" style="width:300px;" name="txRemark" id="txRemark" value="<?php echo $remark?>"></div></td>
									<td>
										<div align="center">
											<select name="selReturned" id="selReturned" style="width:120px;font-size:12px;">
												<option value="">-- Select --</option>
												<option value="Unreturned" <?php if($mr_status=="Unreturned")echo 'selected="selected"';?>>Unreturned</option>
												<option value="Returned" <?php if($mr_status=="Returned")echo 'selected="selected"';?>>Returned</option>
												<option value="Transferred" <?php if($mr_status=="Transferred")echo 'selected="selected"';?>>Transferred</option>
												<option value="Turned Over" <?php if($mr_status=="Turned Over")echo 'selected="selected"';?>>Turned Over</option>
												<option value="Damaged" <?php if($mr_status=="Damaged")echo 'selected="selected"';?>>Damaged</option>
												<option value="Disposed" <?php if($mr_status=="Disposed")echo 'selected="selected"';?>>Disposed</option>
											</select>
											<div class="warning" id="msgRetStat" style="font-weight:bold;" name="msgRetStat"></div>
										</div>
									</td>
									<td>
										<div align="center">
											<input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txReturnDate" id="txReturnDate" value="<?php echo $return_date ?>">
											<div class="warning" id="msgRetDate" style="font-weight:bold;" name="msgRetDate"></div>
										</div>
									</td>
									<td>
										<div align="center">
											<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary btn-mini">
											<a class="btn btn-warning btn-mini" href="?mr_id=<?php echo functions::encode($mr_id)?>">Cancel</a>
										</div>
									</td>
								</tr>
							</table><br><br><br>
						</div>
						<?php }?> 
						<table id="tblist" width="100%" border="1" style="font-size: 11px;">
							<thead>
								<tr style="background-color:#CCC;">
									<th width="3%" scope="col"><div align="center">Qty</div></th>
									<th width="4%" scope="col"><div align="center">Unit</div></th>
									<th width="25%" scope="col"><div align="left">Equipment</div></th>
									<th width="7%" scope="col"><div align="center">Serial No.</div></th>
									<th width="7%" scope="col"><div align="center">Inventory No.</div></th>
									<th width="8%" scope="col"><div align="center">Unit Cost</div></th>
									<th width="6%" scope="col"><div align="center">Date Purchased</div></th>
									<th width="6%" scope="col"><div align="center">Status</div></th>
									<th width="5%" scope="col"><div align="center">Estimated<br>Useful<br>Life</div></th>
									<th width="11%" scope="col"><div align="center">Remark</div></th>
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
									$bgColor='';
									if($rItem['mr_status']=="" || $rItem['mr_status']=="Unreturned")
										$bgColor = 'bgcolor="#fcb77b"';
								?>
								<tr id="rw<?php echo $rItem['mri_id'];?>" <?php echo $bgColor;?>>
									<td height="30"><div align="center"><?php echo $rItem['qty'];?></div></td>
									<td><div align="center"><?php echo $rItem['unit'];?></div></td>
									<td><?php echo $equipmentName;?></td>
									<td><div align="center"><?php echo $db->getValue('equipment','serial_no',array('equip_id'=>$rItem['equip_id']));?></div></td>
									<td><div align="center"><?php echo $db->getValue('equipment','inventory_id',array('equip_id'=>$rItem['equip_id']));?></div></td>
									<td><div align="center"><?php echo functions::formatMoney($db->getValue('equipment','price',array('equip_id'=>$rItem['equip_id'])));?></div></td>
									<td><div align="center"><?php echo functions::datearr($db->getValue('equipment','date_acquired',array('equip_id'=>$rItem['equip_id'])));?></div></td>
									<td><div align="center"><?php echo $rItem['mr_status'].'<br>('.functions::datearr($rItem['return_date']).')';?></div></td>
									<td><div align="center"><?php $pl = $db->getValue('equipment','round(datediff(proposed_life,date_acquired)/365,1)',array('equip_id'=>$rItem['equip_id'])); echo ($pl) ? $pl. ' Year/s' : '';?></div></td>
									<td><div align="center"><?php echo $rItem['remark'];?></div></td>
									<td>
										<div align="center">
											<a id="edit<?php echo $rItem['mri_id'];?>" class="btn btn-mini btn-warning" title="Update this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?mr_id=<?php echo functions::encode($mr_id);?>&txItmEdt=<?php echo functions::encode($rItem['mri_id']);?>"><i class="halflings-icon white pencil"></i> </a>
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
					<td height="20"><div align="center">&nbsp;</div></td>
				</tr>
				<tr>
					<td align="center">
						<table width="100%" border="0" style="font-size: 12px;">
							<tr>
								<td align="center" width="25%">Received and Checked by:</td>
								<td align="center" width="25%">Inspected by: </td>
								<td align="center" width="25%">Noted by:</td>
							</tr>
							<tr>
								<td height="30" align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $ret_received_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
								<td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $ret_checked_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
								<td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $ret_noted_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
							</tr>
							<tr>
								<td height="10" align="center"><div><strong><?php echo $ret_received_by_title;?></strong></div></td>
								<td align="center"><div><strong><?php echo $ret_checked_by_title;?></strong></div></td>
								<td align="center"><div><strong><?php echo $ret_noted_by_title;?></strong></div></td>
							</tr>
							<tr>
								<td height="20" align="center" valign="bottom">Date: <strong><?php #echo functions::datearr($ret_turnover_date);?></strong></td>
								<td align="center" valign="bottom">Date: <strong><?php #echo functions::datearr($ret_checked_date);?></strong></td>
								<td align="center" valign="bottom">Date: <strong><?php #echo functions::datearr($ret_noted_date);?></strong></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td height="20"><div align="center">&nbsp;</div></td>
				</tr>
				<tr>
					<td height="20"><div align="lef"><?php if($mr_remarks){?>Remarks: <strong><?php echo $mr_remarks;?></strong><?php }?></div></td>
				</tr>
				<tr>
					<td height="20"><div align="center"><strong>--------------------- CLEARED ---------------------</strong></div></td>
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
<script>
function delt(){
	if(confirm('Do you want to remove this item?'))
		return true;
	else
		return false;
}
$(document).ready(function(){
	var res = false;
	$('#btnSave').click(function(){
		$('#msgtxEquip').html("");
		$('#msgRetDate').html("");
		$('#msgRetStat').html("");

		if( $('#selEquip').val()=="" ){
			$('#msgtxEquip').html("Specify Equipment!");
			$('#selEquip').focus();
			res=false;
		}
		else if( $('#selReturned').val()=="" && $('#txReturnDate').val() ){
			$('#msgRetStat').html('Status Required!');
			$('#selReturned').focus();
		}
		else
			res=true;
		return res;
	});
	$('#txReturnDate').datepicker({
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
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<?php if(isset($_SESSION['notif_id3_list'])){ ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id3_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id3_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
});
</script>
<?php unset($_SESSION['notif_id3_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>