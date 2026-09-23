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
$jid = (isset($_REQUEST['jid']) && !empty($_REQUEST['jid']) ) ? functions::decode($_REQUEST['jid']) : 0;
$edtID = ( isset($_REQUEST['edtID']) && !empty($_REQUEST['edtID']) ) ? functions::decode($_REQUEST['edtID']) : '';
$date_report = ( isset($_POST['txDateReport']) && functions::valid_date($_POST['txDateReport']) ) ? trim($_POST['txDateReport']) : NULL;
$equip_id = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? trim($_POST['selEquip']) : NULL;
$jo_desc = ( isset($_POST['txDesc']) && !empty($_POST['txDesc']) ) ? trim($_POST['txDesc']) : NULL;
$mechanic = ( isset($_POST['selMechanic']) && !empty($_POST['selMechanic']) ) ? $_POST['selMechanic'] : array();
$date_schedule = ( isset($_POST['txDateSched']) && functions::valid_date($_POST['txDateSched']) ) ? trim($_POST['txDateSched']) : NULL;
$date_accomplished = ( isset($_POST['txDateAccom']) && functions::valid_date($_POST['txDateAccom']) ) ? trim($_POST['txDateAccom']) : NULL;
$jo_status = ( isset($_POST['txStatus']) && !empty($_POST['txStatus']) ) ? trim($_POST['txStatus']) : NULL;
$date_release = ( isset($_POST['txDateRelease']) && functions::valid_date($_POST['txDateRelease']) ) ? trim($_POST['txDateRelease']) : NULL;
$date_qc = ( isset($_POST['txDateQC']) && functions::valid_date($_POST['txDateQC']) ) ? trim($_POST['txDateQC']) : NULL;
$remarks = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : NULL;
$arrEmp=array();
if(count($mechanic)){
foreach($mechanic as $mec => $emp):
	$arrEmp[$emp]=$emp;
endforeach;
}


#print_r($a);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Job Order Manage</title>
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
<?php
if( isset($_POST['btnSave']) ){

	if( $equip_id && $mechanic && $date_report){
		$arrField = array('date_report'=>$date_report,'equip_id'=>$equip_id,
			'jo_desc'=>$jo_desc,'date_schedule'=>$date_schedule,'date_accomplished'=>$date_accomplished,
			'jo_status'=>$jo_status,'date_qc'=>$date_qc,'date_release'=>$date_release,'remarks'=>$remarks);

		if($edtID){
			$db->update('equip_job_order',$arrField,array('jo_id'=>$edtID));
			$db->delete('equip_job_order_mechanic',array('jo_id'=>$edtID));
			foreach($mechanic as $mec => $emp):
				$db->insert('equip_job_order_mechanic',array('jo_id'=>$edtID,'emp_id'=>$emp));
			endforeach;
			$_SESSION['notif_success']='Changes Saved';
			functions::sendTo(functions::pageName().'?edtID='.functions::encode($edtID));
			die();
		}
		else{

			$dateArr = explode('-', $date_report);
			$current_jono_yr = isset($dateArr[0]) ? $dateArr[0] : '';
			$last_jo_no_db = $db->getValue('equip_job_order','jo_no',array('left(jo_no,2)'=>$current_jono_yr[2].$current_jono_yr[3]),'ORDER BY jo_no DESC LIMIT 1');
			$last_jo_no = ($last_jo_no_db) ? $last_jo_no_db : $date_report[2].$date_report[3].'-0';
			$jo_no_generated = 0;
			if( $last_jo_no ){

				$jonoArr = explode('-', $last_jo_no);
				$last_jono_yr = isset($jonoArr[0]) ? $jonoArr[0] : '';
				$last_jono_number = isset($jonoArr[1]) ? $jonoArr[1] : 0;
				if( $last_jono_yr ){//2021
					$exit=false;
					while($exit==false):
						$last_jono_number++;
						$jo_no_generated = $last_jono_yr.'-'.$last_jono_number;

						#$temp_jono=$last_jono_number;
						if(strlen($last_jono_number)==1)
							$jo_no_generated = $last_jono_yr.'-00'.$last_jono_number;
						else if(strlen($last_jono_number)==2)
							$jo_no_generated = $last_jono_yr.'-0'.$last_jono_number;

						if( $db->getValue('equip_job_order','count(*)',array('jo_no'=>$jo_no_generated)) ){
							//exist, don't exist
						}
						else{
							$exit=true;
						}
					endwhile;
				}
			}
			$jono = $jo_no_generated;

			if( $db->getValue('equip_job_order','count(*)',array('jo_no'=>$jono))==0 ){
				$arrField = array_merge($arrField,array('jo_no'=>$jono));
				$jo_id = $db->insert('equip_job_order',$arrField);
				if($jo_id){
					$_SESSION['notif_success']='New Job Order Added!';
					foreach($mechanic as $mec => $emp):
						$db->insert('equip_job_order_mechanic',array('jo_id'=>$jo_id,'emp_id'=>$emp));
					endforeach;
					$_SESSION['notif_id']=$jo_id;
				}
				functions::sendTo('admin-equipment-job-order.php?jid='.functions::encode($jo_id));
				die();
			}
			else{
				functions::say('Job Order Number already exist!');
			}
		}
	}
}
if($edtID){
	$_SESSION['notif_id']=$edtID;
	$q = $db->select('equip_job_order','*',array('jo_id'=>$edtID));
	$r = $db->fetch_array($q);

	$jono = $r['jo_no'];
	$date_report = $r['date_report'];
	$equip_id = $r['equip_id'];
	$jo_desc = $r['jo_desc'];
	$date_schedule = $r['date_schedule'];
	$date_qc = $r['date_qc'];
	$date_accomplished = $r['date_accomplished'];
	$jo_status = $r['jo_status'];
	$date_release = $r['date_release'];
	$remarks = $r['remarks'];
	$qM = $db->select('equip_job_order_mechanic','*',array('jo_id'=>$edtID));
	while($rM = $db->fetch_array($qM)):
		$arrEmp[$rM['emp_id']]=$rM['emp_id'];
	endwhile;
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Job Order Manage</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="admin-equipment-job-order-report.php?">Report</a></li>
				<li><a href="admin-equipment-job-order.php?">Monitoring</a></li>
				<li class="active"><a href="admin-equipment-job-order-manage.php?" style="opacity:.9">Create Job Order</a></li>
			</ul>
		</div>
		<div class="box-content">
			<div align="center">
				<form id="frm" method="post" enctype="multipart/form-data">
					<table width="80%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<th width="35%" align="right" scope="row">&nbsp;</th>
							<td width="2%">&nbsp;</td>
							<td width="50%">&nbsp;</td>
							<td width="13%">&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Date Report</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<input type="text" style="width: 80px;" name="txDateReport" id="txDateReport" value="<?php echo $date_report ?>">
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Equipment Description</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<select name="selEquip" id="selEquip" data-rel="chosen" style="width:550px;" required>
									<option value="">-- Select --</option>
									<?php
									$qEquip = $db->query('SELECT * FROM equipment  ORDER BY name ');
									while($rEquip = $db->fetch_array($qEquip)):
									?>
									<option value="<?php echo $rEquip['equip_id']?>" <?php if($rEquip['equip_id']==$equip_id){echo 'selected="selected"';} ?>><?php echo $rEquip['inventory_id'].' - '.$rEquip['name'].' - '.$rEquip['plate_no']?></option>
									<?php endwhile;?>
								</select>
								<span class="help-inline warning" id="MsgSelEquip" style="font-weight:bold;" name="MsgSelEquip"></span>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Problem and Repair Description</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
							<textarea type="text" name="txDesc" id="txDesc" style="width:80%;" rows="4"><?php echo $jo_desc ?></textarea>
							<span class="help-inline warning" id="MsgtxDesc" style="font-weight:bold;" name="MsgtxDesc"></span>
							</td>
							<td>&nbsp;</td>
						<tr>
							<th align="right" scope="row">Assigned Personnel / Mechanic</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<select name="selMechanic[]" id="selMechanic" multiple data-rel="chosen" style="width:300px; text-align:left;">
									<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo $rEU['emp_id']?>" <?php if( isset($arrEmp[$rEU['emp_id']]))echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>
								<span class="help-inline warning" id="MsgtxMec" style="font-weight:bold;" name="MsgtxMec"></span>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Priority Schedule and Target</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" style="width: 80px;" name="txDateSched" id="txDateSched" value="<?php echo $date_schedule ?>"></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Date Accomplished</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" style="width: 80px;" name="txDateAccom" id="txDateAccom" value="<?php echo $date_accomplished ?>"></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Status</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<select name="txStatus" id="txStatus" style="width:200px;" required>
									<option value="">--Select--</option>
									<option value="Accomplished" <?php if($jo_status=='Accomplished'){echo 'selected="selected"';} ?>>Accomplished</option>
									<option value="On-going Repair" <?php if($jo_status=='On-going Repair'){echo 'selected="selected"';} ?>>On-going Repair</option>
									<option value="Waiting Parts" <?php if($jo_status=='Waiting Parts'){echo 'selected="selected"';} ?>>Waiting Parts</option>
									<option value="Pending" <?php if($jo_status=='Pending'){echo 'selected="selected"';} ?>>Pending</option>
									<option value="for Schedule" <?php if($jo_status=='for Schedule'){echo 'selected="selected"';} ?>>for Schedule</option>
									<option value="for QC" <?php if($jo_status=='for QC'){echo 'selected="selected"';} ?>>for QC</option>
									<option value="Done Post" <?php if($jo_status=='Done Post'){echo 'selected="selected"';} ?>>Done Post</option>
									<option value="Release" <?php if($jo_status=='Release'){echo 'selected="selected"';} ?>>Release</option>
								</select>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Quality Control Date</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" style="width: 80px;" name="txDateQC" id="txDateQC" value="<?php echo $date_qc; ?>"></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Date Post and Release</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><input type="text" style="width: 80px;" name="txDateRelease" id="txDateRelease" value="<?php echo $date_release; ?>"></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Remarks</th>
							<td>&nbsp;</td>
							<td class="tdSpace"><textarea type="text" name="txRemark" id="txRemark" style="width:80%;" rows="4"><?php echo $remarks; ?></textarea></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="4" align="center">
								<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary btn-small">
								<?php if($edtID){ ?><a href="admin-equipment-job-order.php?jid=<?php echo functions::encode($jid);?>" class="btn btn-small">Back</a><?php } ?>
							</td>
						</tr>
					</table>
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
function ask(){
	if(confirm('Do you want to save this Job Order?'))
		return true;
	else
		return false; 
}
$(document).ready(function(){
	var res = false;
	$('#MsgtxDesc, #MsgtxMec').html(''); 
	$('#frm').submit(function(){
		if( $('#selEquip').val()=="" ){
			$('#MsgtxDesc').html('Equipment Required!');
		}
		else if( $('#selMechanic').val()=="" ){
			$('#MsgtxMec').html('Mechanic / Personnel Required!');
		}
		else{
			if(ask())
				res=true;			
		}
		return res;
	});

	$('#txDateReport, #txDateSched, #txDateAccom, #txDateRelease, #txDateQC').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
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
<!-- end: JavaScript-->
</body>
</html>