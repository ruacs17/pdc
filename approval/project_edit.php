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

$pid = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$_SESSION['notif_id_list']=$pid;
if( isset($_POST['btnCancel']) ){
	functions::sendTo('project_view.php?pid='.functions::encode($pid));
}
if( isset($_POST['btnSave']) ){
	$arrUpdate=array();
	$inchargeID="";
	$txProj_name = ( isset($_POST['txProj_name']) && !empty($_POST['txProj_name']) ) ? trim($_POST['txProj_name']) : NULL;
	$txProj_desc = ( isset($_POST['txProj_desc']) && !empty($_POST['txProj_desc']) ) ? trim($_POST['txProj_desc']) : NULL;
	$txProj_location = ( isset($_POST['txProj_location']) && !empty($_POST['txProj_location']) ) ? trim($_POST['txProj_location']) : NULL;
	$txProj_remark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : NULL;
	$txProj_status = ( isset($_POST['projStatus']) && !empty($_POST['projStatus']) ) ? $_POST['projStatus'] : NULL;
	$txCommitment = ( isset($_POST['txCommitment']) && !empty($_POST['txCommitment']) ) ? $_POST['txCommitment'] : '0';
	$txConsultancy = ( isset($_POST['txConsultancy']) && !empty($_POST['txConsultancy']) ) ? $_POST['txConsultancy'] : '0';
	$txFinders = ( isset($_POST['txFinders']) && !empty($_POST['txFinders']) ) ? $_POST['txFinders'] : '0';
	$txTechnical = ( isset($_POST['txTechnical']) && !empty($_POST['txTechnical']) ) ? $_POST['txTechnical'] : '0';
	$txUnder = ( isset($_POST['txUnder']) && !empty($_POST['txUnder']) ) ? $_POST['txUnder'] : NULL;
	$txProj_cost = ( isset($_POST['txProjCost']) && !empty($_POST['txProjCost']) ) ? functions::moneyToDouble($_POST['txProjCost']) : "0";
	$targetDayCompletion = ( isset($_POST['targetDayCompletion']) && !empty($_POST['targetDayCompletion']) ) ? $_POST['targetDayCompletion'] : 0; 
	$reviseTargetDayCompletion = ( isset($_POST['reviseTargetDayCompletion']) && !empty($_POST['reviseTargetDayCompletion']) ) ? $_POST['reviseTargetDayCompletion'] : 0; 
	$inchargeArr = (isset($_POST['txIncharge'])) ? $_POST['txIncharge'] : array();
	$warehouseArr = (isset($_POST['selWarehouseMan'])) ? $_POST['selWarehouseMan'] : array();
	$txTIN = ( isset($_POST['txTIN']) && !empty($_POST['txTIN']) ) ? trim($_POST['txTIN']) : NULL;

	$txMPFee = ( isset($_POST['txMPFee']) && !empty($_POST['txMPFee']) ) ? $_POST['txMPFee'] : 0;
	$txGenOver = ( isset($_POST['txGenOver']) && !empty($_POST['txGenOver']) ) ? $_POST['txGenOver'] : 0;
	$txProfit = ( isset($_POST['txProfit']) && !empty($_POST['txProfit']) ) ? $_POST['txProfit'] : 0;
	$txRoyalty = ( isset($_POST['txRoyalty']) && !empty($_POST['txRoyalty']) ) ? $_POST['txRoyalty'] : 0;

	// $txMon = ( isset($_POST['txMon']) && !empty($_POST['txMon']) ) ? $_POST['txMon'] : '';
	// $txDay = ( isset($_POST['txDay']) && !empty($_POST['txDay']) ) ? $_POST['txDay'] : '';
	// $txYear = ( isset($_POST['txYear']) && !empty($_POST['txYear']) ) ? $_POST['txYear'] : '';
	$txDateStart = ( isset($_POST['txDateStart']) && functions::valid_date($_POST['txDateStart']) ) ? $_POST['txDateStart'] : NULL;
	$pc_id = ( isset($_POST['selClient']) && !empty($_POST['selClient']) ) ? $_POST['selClient'] : NULL;
	if( $txDateStart ){
		$arrUpdate = array_merge($arrUpdate,array('date_start'=>$txDateStart));
		$txDateCompletion = functions::AddDay($txDateStart,$targetDayCompletion);
		$arrUpdate = array_merge($arrUpdate,array('date_completion'=>$txDateCompletion));
		$txDateReviseCompletion = functions::AddDay($txDateCompletion,$reviseTargetDayCompletion);
		$arrUpdate = array_merge($arrUpdate,array('date_revise_completion'=>$txDateReviseCompletion));
	}

	// $txCompletedMon = ( isset($_POST['txCompletedMon']) && !empty($_POST['txCompletedMon']) ) ? $_POST['txCompletedMon'] : '';
	// $txCompletedDay = ( isset($_POST['txCompletedDay']) && !empty($_POST['txCompletedDay']) ) ? $_POST['txCompletedDay'] : '';
	// $txCompletedYear = ( isset($_POST['txCompletedYear']) && !empty($_POST['txCompletedYear']) ) ? $_POST['txCompletedYear'] : '';
	// $txDateCompleted = ($txCompletedMon && $txCompletedDay && $txCompletedYear) ? $txCompletedYear.'-'.$txCompletedMon.'-'.$txCompletedDay : NULL;
	$txDateCompleted = ( isset($_POST['txDateCompleted']) && functions::valid_date($_POST['txDateCompleted']) ) ? $_POST['txDateCompleted'] : NULL;

	// $txContractMon = ( isset($_POST['txContractMon']) && !empty($_POST['txContractMon']) ) ? $_POST['txContractMon'] : '';
	// $txContractDay = ( isset($_POST['txContractDay']) && !empty($_POST['txContractDay']) ) ? $_POST['txContractDay'] : '';
	// $txContractYear = ( isset($_POST['txContractYear']) && !empty($_POST['txContractYear']) ) ? $_POST['txContractYear'] : '';
	// $txDateContract = ($txContractMon && $txContractDay && $txContractYear) ? $txContractYear.'-'.$txContractMon.'-'.$txContractDay : NULL;
	$txDateContract = ( isset($_POST['txDateContract']) && functions::valid_date($_POST['txDateContract']) ) ? $_POST['txDateContract'] : NULL;


	// $txNoaMon = ( isset($_POST['txNoaMon']) && !empty($_POST['txNoaMon']) ) ? $_POST['txNoaMon'] : '';
	// $txNoaDay = ( isset($_POST['txNoaDay']) && !empty($_POST['txNoaDay']) ) ? $_POST['txNoaDay'] : '';
	// $txNoaYear = ( isset($_POST['txNoaYear']) && !empty($_POST['txNoaYear']) ) ? $_POST['txNoaYear'] : '';
	// $txDateNoa = ($txNoaMon && $txNoaDay && $txNoaYear) ? $txNoaYear.'-'.$txNoaMon.'-'.$txNoaDay : NULL;
	$txDateNoa = ( isset($_POST['txDateNoa']) && functions::valid_date($_POST['txDateNoa']) ) ? $_POST['txDateNoa'] : NULL;


	// $txNtpMon = ( isset($_POST['txNtpMon']) && !empty($_POST['txNtpMon']) ) ? $_POST['txNtpMon'] : '';
	// $txNtpDay = ( isset($_POST['txNtpDay']) && !empty($_POST['txNtpDay']) ) ? $_POST['txNtpDay'] : '';
	// $txNtpYear = ( isset($_POST['txNtpYear']) && !empty($_POST['txNtpYear']) ) ? $_POST['txNtpYear'] : '';
	// $txDateNtp = ($txNtpMon && $txNtpDay && $txNtpYear) ? $txNtpYear.'-'.$txNtpMon.'-'.$txNtpDay : NULL;
	$txDateNtp = ( isset($_POST['txDateNtp']) && functions::valid_date($_POST['txDateNtp']) ) ? $_POST['txDateNtp'] : NULL;

	$arrUpdate = array_merge($arrUpdate,array('proj_name'=>$txProj_name,'proj_location'=>$txProj_location,'owned_by'=>$txUnder,'proj_cost'=>($txProj_cost * 1),'remarks'=>$txProj_remark,'proj_desc'=>$txProj_desc,'royalty'=>$txRoyalty,'profit'=>$txProfit,'genover'=>$txGenOver,'commitment'=>$txCommitment,'consultancy'=>$txConsultancy,'finders'=>$txFinders,'technical'=>$txTechnical,'mpfee'=>$txMPFee,'tin'=>$txTIN,'date_completed'=>$txDateCompleted,'date_contract'=>$txDateContract,'date_noa'=>$txDateNoa,'date_ntp'=>$txDateNtp,'pc_id'=>$pc_id,'status'=>$txProj_status));

	if( $txProj_name ){
		$db->update('project',$arrUpdate,array('proj_id'=>$pid));
		if(count($inchargeArr)){
			$db->delete('project_incharge',array('proj_id'=>$pid,'incharge_type'=>'project_incharge'));
			foreach($inchargeArr as $in_id):
				$db->insert('project_incharge',array('user_id'=>$in_id,'proj_id'=>$pid,'incharge_type'=>'project_incharge'));
			endforeach;
		}
		if(count($warehouseArr)){
			$db->delete('project_incharge',array('proj_id'=>$pid,'incharge_type'=>'warehouseman'));
			foreach($warehouseArr as $w_id):
				$db->insert('project_incharge',array('user_id'=>$w_id,'proj_id'=>$pid,'incharge_type'=>'warehouseman'));
			endforeach;
		}
		$_SESSION['notif_success']='Changes Saved!';
		functions::sendTo('project_view.php?pid='.functions::encode($pid));
		die();
	}
	else{
		functions::sendTo(functions::pageName().'?pid='.functions::encode($pid));
		die();		
	}

}

$mon='';$day='';$year='';$proj_name='';$proj_desc='';$incharge=0;$cost='';$remarks='';$status='';
$monCompletion='';$dayCompletion='';$yearCompletion='';
$monReviseCompletion='';$dayReviseCompletion='';$yearReviseCompletion='';
$monCompleted='';$dayCompleted='';$yearCompleted='';
$monContract='';$dayContract='';$yearContract='';
$monNoa='';$dayNoa='';$yearNoa='';
$monNtp='';$dayNtp='';$yearNtp='';
$targetDayCompletion=0;
$reviseTargetDayCompletion=0;$tin='';
$commitment=0; $consultancy=0; $finders=0; $technical=0;$owned_by='';$profit=0;$genover=0;$royalty=0;$mpfee=0;
$qProj = $db->select('project','*',array('proj_id'=>$pid));

$projInfo = $db->fetch_array($qProj);
$vdate = $projInfo['date_start'];
$xdate = explode('-',$vdate);
if(count($xdate)==3){
	$mon = $xdate[1];
	$day = $xdate[2];
	$year = $xdate[0];
}
$proj_name = $projInfo['proj_name'];
$proj_desc = $projInfo['proj_desc'];
$proj_location = $projInfo['proj_location'];
$mpfee = $projInfo['mpfee'];
$cost = ($projInfo['proj_cost']) ? functions::formatMoney($projInfo['proj_cost']) : 0;
$commitment = $projInfo['commitment'];
$consultancy = $projInfo['consultancy'];
$finders = $projInfo['finders'];
$technical = $projInfo['technical'];
$profit = $projInfo['profit'];
$genover = $projInfo['genover'];
$royalty = $projInfo['royalty'];
$remarks=$projInfo['remarks'];
$status=$projInfo['status'];
$tin=$projInfo['tin'];
$datestart = $projInfo['date_start'];
$owned_by = $projInfo['owned_by'];
$dateReviseCompletion=$projInfo['date_revise_completion'];
$targetDayCompletion = ($projInfo['date_completion']) ? functions::date_diff($datestart,$projInfo['date_completion']) : 0;
$reviseTargetDayCompletion = ($projInfo['date_revise_completion'] && $projInfo['date_completion'] ) ? functions::date_diff($projInfo['date_completion'],$projInfo['date_revise_completion']) : 0;
$date_completed = $projInfo['date_completed'];
$date_contract = $projInfo['date_contract'];
$date_noa = $projInfo['date_noa'];
$date_ntp = $projInfo['date_ntp'];
$dateCompleted = explode('-',$projInfo['date_completed']);

if(count($dateCompleted)==3){
	$monCompleted=$dateCompleted[1];
	$dayCompleted=$dateCompleted[2];
	$yearCompleted=$dateCompleted[0];  
}

$dateContract = explode('-',$projInfo['date_contract']);
if(count($dateContract)==3){
	$monContract=$dateContract[1];
	$dayContract=$dateContract[2];
	$yearContract=$dateContract[0];  
}
$dateNoa = explode('-',$projInfo['date_noa']);
if(count($dateNoa)==3){
	$monNoa=$dateNoa[1];
	$dayNoa=$dateNoa[2];
	$yearNoa=$dateNoa[0];  
}
$dateNtp = explode('-',$projInfo['date_ntp']);
if(count($dateNtp)==3){
	$monNtp=$dateNtp[1];
	$dayNtp=$dateNtp[2];
	$yearNtp=$dateNtp[0];  
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Project Modify</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link rel="stylesheet" href="../css/bootstrap-multiselect.css" type="text/css">
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Update Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="20%" height="30">Project Name</td>
						<td width="40%"><input type="text" name="txProj_name" id="txProj_name" class="span6" value="<?php echo $proj_name;?>" style="width:90%" required></td>
						<td width="10%">NTP</td>
						<td><input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDateNtp" id="txDateNtp" value="<?php echo $date_ntp?>"></td>
					</tr>
					<tr>
						<td height="30">Project Description</td>
						<td><input type="text" name="txProj_desc" id="txProj_desc" class="span6" value="<?php echo $proj_desc?>" style="width:90%" /></td>
						<td>MPFee (%)</td>
						<td><input type="text" class="span6" name="txMPFee" id="txMPFee" style="width:20%" value="<?php echo $mpfee?>" /></td>
					</tr>
					<tr>
						<td height="30">Project Location</td>
						<td><input type="text" name="txProj_location" id="txProj_location" class="span6" value="<?php echo $proj_location?>" style="width:90%" /></td>
						<td>Commitment (%)</td>
						<td><input type="text" class="span6" name="txCommitment" id="txCommitment" style="width:20%" value="<?php echo $commitment?>" /></td>
					</tr>
					<tr>
						<td height="30">In Charge</td>
						<td>
							<select name="txIncharge[]" id="txIncharge" multiple="multiple">
								<?php
								$qInCharge = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id ORDER BY u.lname');
								while($rInCharge = $db->fetch_array($qInCharge)):
								?>
								<option value="<?php echo $rInCharge['user_id']?>" <?php if( $db->getValue('project_incharge','count(*)',array('user_id'=>$rInCharge['user_id'],'proj_id'=>$pid,'incharge_type'=>'project_incharge')) )echo 'selected="selected"';?> ><?php echo $rInCharge['lname'].', '.$rInCharge['fname']?></option>
								<?php endwhile;?>
							</select>
						</td>
						<td>Consultancy (%)</td>
						<td><input type="text" class="span6" name="txConsultancy" id="txConsultancy"  style="width:20%" value="<?php echo $consultancy?>" /></td>
					</tr>
					<tr>
						<td height="30">Warehouseman</td>
						<td>
							<select name="selWarehouseMan[]" id="selWarehouseMan" multiple="multiple">
								<?php
								$qWM = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id ORDER BY u.lname');
								while($rWM = $db->fetch_array($qWM)):
								?>
								<option value="<?php echo $rWM['user_id']?>" <?php if( $db->getValue('project_incharge','count(*)',array('user_id'=>$rWM['user_id'],'proj_id'=>$pid,'incharge_type'=>'warehouseman')) )echo 'selected="selected"';?> ><?php echo $rWM['lname'].', '.$rWM['fname']?></option>
								<?php endwhile;?>
							</select>
						</td>
						<td>Finders (%)</td>
						<td><input type="text" class="span6" name="txFinders" id="txFinders" style="width:20%" value="<?php echo $finders?>" /></td>
					</tr>
					<tr>
						<td height="30">Cost</td>
						<td><input type="text" class="span6" name="txProjCost" id="txProjCost" style="width:40%" autocomplete='off' value="<?php echo $cost;?>" onkeyup="FormatCurrency(this);"></td>
						<td>Technical (%)</td>
						<td><input type="text" class="span6" name="txTechnical" id="txTechnical" style="width:20%" value="<?php echo $technical?>" /></td>
					</tr>
					<tr>
						<td height="30">Date Started</td>
						<td>
							<input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDateStart" id="txDateStart" value="<?php echo $datestart?>" required>
							<span class="help-inline warning" style="font-weight:bold;" id="msgDateStart" name="msgDateStart"></span>
						</td>
						<td>Gen. Over (%)</td>
						<td><input type="text" class="span6" name="txGenOver" id="txGenOver" style="width:20%" value="<?php echo $genover?>" /></td>
					</tr>
					<tr>
						<td height="30">Target Completion(No of Days)</td>
						<td><input type="text" class="span6" name="targetDayCompletion" id="targetDayCompletion" style="width:17%" value="<?php echo $targetDayCompletion;?>" /></td>
						<td>Profit (%)</td>
						<td><input type="text" class="span6" name="txProfit" id="txProfit" style="width:20%" value="<?php echo $profit?>" /></td>
					</tr>
					<tr>
						<td>Revise Completion (No of Days)</td>
						<td><input type="text" class="span6" name="reviseTargetDayCompletion" id="reviseTargetDayCompletion" style="width:17%" value="<?php echo $reviseTargetDayCompletion;?>" /></td>
						<td>Royalty (%)</td>
						<td><input type="text" class="span6" name="txRoyalty" id="txRoyalty" style="width:20%" value="<?php echo $royalty?>" /></td>
					</tr>
					<tr>
						<td>Date Completed</td>
						<td><input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDateCompleted" id="txDateCompleted" value="<?php echo $date_completed;?>"></td>
						<td>Under</td>
						<td>
							<select name="txUnder" id="txUnder" style="width:220px;">
								<option value="">--select--</option>
								<?php
								$qUnder = $db->query('SELECT * FROM project WHERE project="0"');
								while($rUnder = $db->fetch_array($qUnder)):
								?>
								<option value="<?php echo $rUnder['proj_id']?>" <?php if($owned_by==$rUnder['proj_id'])echo 'selected="selected"';?>><?php echo $rUnder['proj_name']?></option>
								<?php endwhile;?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Contract</td>
						<td><input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDateContract" id="txDateContract" value="<?php echo $date_contract?>"></td>
						<td>Status</td>
						<td>
							<select name="projStatus" id="projStatus" class="span6" style="width:220px;">
								<option value="">--select--</option>
								<option value="done" <?php if($status=='done')echo 'selected="selected"';?>>Done</option>
								<option value="ongoing" <?php if($status=='ongoing')echo 'selected="selected"';?>>On Going</option>
								<option value="closed" <?php if($status=='closed')echo 'selected="selected"';?>>Closed</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>NOA</td>
						<td><input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDateNoa" id="txDateNoa" value="<?php echo $date_noa;?>"></td>
						<td>Remark</td>
						<td><textarea name="txRemark" id="txRemark" cols="7" style="width:90%"><?php echo $remarks;?></textarea></td>
					</tr>
					<tr>
						<td>TIN</td>
						<td><input type="text" class="span6" name="txTIN" id="txTIN" style="width:220px;" value="<?php echo $tin;?>" /></td>
						<td>Client</td>
						<td>
							<select name="selClient" id="selClient" data-rel="chosen" style="width:410px;font-size:12px;">
								<option value="">--select--</option>
								<?php
								$qpc = $db->query('SELECT * FROM project_client ORDER BY pc_name');
								while($rpc = $db->fetch_array($qpc)):?>
								<option value="<?php echo $rpc['pc_id']?>" <?php if($rpc['pc_id']==$projInfo['pc_id']){echo 'selected="selected"';} ?> ><?php echo $rpc['pc_name']?></option>
								<?php endwhile;?>
							</select>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				</table>
				<div align="center">
					<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-small btn-primary">
					<input type="submit" name="btnCancel" id="btnCancel" value="Cancel" class="btn btn-small">
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
<script src="../js/formatCurrency.js"></script>
<script type="text/javascript" src="../js/bootstrap-multiselect.js"></script>
<script>
$(document).ready(function(){
	var res = false;
	$('#btnSave').click(function(){
		if( $('#txProj_name').val()=="" ){
			alert('Project Name Required!');
			$('#txProj_name').focus();
			res=false;
		}
		else if( $('#txUnder').val()=="" ){
			alert('Admin Under Required!');
			$('#txUnder').focus();
			res=false;
		}
		else{
			if(confirm('Do you want to save information?'))
				res=true;
			else
				res=false;
		}
		return res;
	});
});
$('#txIncharge').multiselect({
	includeSelectAllOption: true,
	buttonWidth: 300,
	enableFiltering: true
});
$('#selWarehouseMan').multiselect({
	includeSelectAllOption: true,
	buttonWidth: 300,
	enableFiltering: true
});
$('#txDateStart, #txDateCompleted, #txDateContract, #txDateNoa, #txDateNtp').datepicker({
	numberOfMonths:1,
	dateFormat:'yy-mm-dd',
	changeYear: true,
	changeMonth: true,
	showButtonPanel: true,
	showOtherMonths: true,
	navigationAsDateFormat: true,
	hideIfNoPrevNext: true,
	yearRange:'2015:<?php echo date('Y')+1 ?>',
});
</script>
<!-- end: JavaScript-->
</body>
</html>