<?php require_once('templ_up.php');?>
<?php
if( isset($_POST['btnCreate']) ){
	$insertID=0;
	$arrInsert = array();
	$inchargeID=0;
	$txProj_name = ( isset($_POST['txProj_name']) && !empty($_POST['txProj_name']) ) ? trim($_POST['txProj_name']) : NULL;
	$txProj_desc = ( isset($_POST['txProj_desc']) && !empty($_POST['txProj_desc']) ) ? trim($_POST['txProj_desc']) : NULL;
	$txProj_location = ( isset($_POST['txProj_location']) && !empty($_POST['txProj_location']) ) ? trim($_POST['txProj_location']) : NULL;

	$txProj_remark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : NULL;
	$txProj_status = ( isset($_POST['projStatus']) && !empty($_POST['projStatus']) ) ? $_POST['projStatus'] : NULL;
	$txUnder = ( isset($_POST['txUnder']) && !empty($_POST['txUnder']) ) ? $_POST['txUnder'] : NULL;
	$inchargeArr = (isset($_POST['txIncharge'])) ? $_POST['txIncharge'] : array();
	$warehouseArr = (isset($_POST['selWarehouseMan'])) ? $_POST['selWarehouseMan'] : array();
	$txTIN = ( isset($_POST['txTIN']) && !empty($_POST['txTIN']) ) ? trim($_POST['txTIN']) : NULL;

	$txMPFee = ( isset($_POST['txMPFee']) && !empty($_POST['txMPFee']) ) ? $_POST['txMPFee'] : 0;
	$txCommitment = ( isset($_POST['txCommitment']) && !empty($_POST['txCommitment']) ) ? $_POST['txCommitment'] : 0;
	$txConsultancy = ( isset($_POST['txConsultancy']) && !empty($_POST['txConsultancy']) ) ? $_POST['txConsultancy'] : 0;
	$txFinders = ( isset($_POST['txFinders']) && !empty($_POST['txFinders']) ) ? $_POST['txFinders'] : 0;
	$txTechnical = ( isset($_POST['txTechnical']) && !empty($_POST['txTechnical']) ) ? $_POST['txTechnical'] : 0;
	$txGenOver = ( isset($_POST['txGenOver']) && !empty($_POST['txGenOver']) ) ? $_POST['txGenOver'] : 0;
	$txProfit = ( isset($_POST['txProfit']) && !empty($_POST['txProfit']) ) ? $_POST['txProfit'] : 0;
	$txRoyalty = ( isset($_POST['txRoyalty']) && !empty($_POST['txRoyalty']) ) ? $_POST['txRoyalty'] : 0;
	$txProj_cost = ( isset($_POST['txProjCost']) && !empty($_POST['txProjCost']) ) ? functions::moneyToDouble($_POST['txProjCost']) : 0;
	$targetDayCompletion = ( isset($_POST['targetDayCompletion']) && !empty($_POST['targetDayCompletion']) ) ? $_POST['targetDayCompletion'] : 0; 
	$reviseTargetDayCompletion = ( isset($_POST['reviseTargetDayCompletion']) && !empty($_POST['reviseTargetDayCompletion']) ) ? $_POST['reviseTargetDayCompletion'] : 0;


	$pc_id = ( isset($_POST['selClient']) && !empty($_POST['selClient']) ) ? $_POST['selClient'] : NULL;

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
	


	$arrInsert = array('date_ntp'=>$txDateNtp,'date_noa'=>$txDateNoa,'date_contract'=>$txDateContract,'date_completed'=>$txDateCompleted,'proj_name'=>$txProj_name,'status'=>$txProj_status,'pc_id'=>$pc_id,'proj_desc'=>$txProj_desc,'proj_location'=>$txProj_location,'remarks'=>$txProj_remark,'tin'=>$txTIN,'proj_cost'=>($txProj_cost * 1),'MPFee'=>$txMPFee,'commitment'=>$txCommitment,'consultancy'=>$txConsultancy,'finders'=>$txFinders,'technical'=>$txTechnical,'profit'=>$txProfit,'royalty'=>$txRoyalty,'owned_by'=>$txUnder);

	// $txMon = ( isset($_POST['txMon']) && !empty($_POST['txMon']) ) ? $_POST['txMon'] : NULL;
	// $txDay = ( isset($_POST['txDay']) && !empty($_POST['txDay']) ) ? $_POST['txDay'] : NULL;
	// $txYear = ( isset($_POST['txYear']) && !empty($_POST['txYear']) ) ? $_POST['txYear'] : NULL;
	$txDateStart = ( isset($_POST['txDateStart']) && functions::valid_date($_POST['txDateStart']) ) ? $_POST['txDateStart'] : NULL;
	//$txDateStart = ($txMon && $txDay && $txYear) ? $txYear.'-'.$txMon.'-'.$txDay : NULL;
	if( $txDateStart  ){
		$arrInsert = array_merge($arrInsert,array('date_start'=>$txDateStart));
		$txDateCompletion = functions::AddDay($txDateStart,$targetDayCompletion);
		$arrInsert = array_merge($arrInsert,array('date_completion'=>$txDateCompletion));
		$txDateReviseCompletion = functions::AddDay($txDateCompletion,$reviseTargetDayCompletion);
		$arrInsert = array_merge($arrInsert,array('date_revise_completion'=>$txDateReviseCompletion));
	}

	if( $txProj_name && $txDateStart ){
		if( $db->getValue('project','count(proj_id)',array('proj_name'=>$txProj_name))==0 ){
			$insertID =  $db->insert('project',$arrInsert);

			if(count($inchargeArr)){
				foreach($inchargeArr as $in_id):
				$db->insert('project_incharge',array('user_id'=>$in_id,'proj_id'=>$insertID,'incharge_type'=>'project_incharge'));
				endforeach;
			}
			if(count($warehouseArr)){
				foreach($warehouseArr as $w_id):
				$db->insert('project_incharge',array('user_id'=>$w_id,'proj_id'=>$insertID,'incharge_type'=>'warehouseman'));
				endforeach;
			}
			if($insertID){
				$_SESSION['notif_id_list']=$insertID;
				$_SESSION['notif_success']='New Project Successfully Created!';
				functions::sendTo('project_list.php?pid='.functions::encode($insertID));
			}
		}
	}
	else
		functions::say('Please fill up the form properly!');
}
?>
<script src="../js/formatCurrency.js"></script>
<link rel="stylesheet" href="../css/bootstrap-multiselect.css" type="text/css">
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Create Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="95%" align="center" border="0" style="background-color:#E4E1E1">
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Project Name</label>
								<div class="controls">
									<input type="text" class="span6" name="txProj_name" id="txProj_name" value="" style="width:70%" required>
									<span class="help-inline warning" style="font-weight:bold;" id="msgProj_name" name="msgProj_name"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Project Description</label>
								<div class="controls">
									<input type="text" class="span6" name="txProj_desc" id="txProj_desc" value="" style="width:70%" />
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Project Location</label>
								<div class="controls">
									<input type="text" class="span6" name="txProj_location" id="txProj_location" value="" style="width:70%" />
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">In Charge</label>
								<div class="controls">
									<select name="txIncharge[]" id="txIncharge" multiple="multiple">
										<?php
										$qInCharge = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id ORDER BY u.lname');
										while($rInCharge = $db->fetch_array($qInCharge)):
										?>
										<option value="<?php echo $rInCharge['user_id']?>" ><?php echo $rInCharge['lname'].', '.$rInCharge['fname']?></option>
										<?php endwhile;?>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Warehouse Man</label>
								<div class="controls">
									<select name="selWarehouseMan[]" id="selWarehouseMan" multiple="multiple">
										<?php
										$qWarehouse = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id ORDER BY u.lname');
										while($rWarehouse = $db->fetch_array($qWarehouse)):
										?>
										<option value="<?php echo $rWarehouse['user_id']?>" ><?php echo $rWarehouse['lname'].', '.$rWarehouse['fname']?></option>
										<?php endwhile;?>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Cost</label>
								<div class="controls">
									<input type="text" class="span6"  name="txProjCost" id="txProjCost" autocomplete='off' value="" onkeyup="FormatCurrency(this);">
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Date Start</label>
								<div class="controls">
									<input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDateStart" id="txDateStart" value="" readonly required>
									<span class="help-inline warning" style="font-weight:bold;" id="msgDateStart" name="msgDateStart"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Target Completion(No of Days)</label>
								<div class="controls">
									<input type="text" class="span6" style="width:10%" name="targetDayCompletion" id="targetDayCompletion" value="" />
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Revise Completion (No of Days)</label>
								<div class="controls">
									<input type="text" class="span6" style="width:10%" name="reviseTargetDayCompletion" id="reviseTargetDayCompletion" value="" />
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Date Completed</label>
								<div class="controls">
									<input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDateCompleted" id="txDateCompleted" readonly value="">
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Date Contract</label>
								<div class="controls">
									<input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDateContract" id="txDateContract" readonly value="">
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Date NOA</label>
								<div class="controls">
									<input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDateNoa" id="txDateNoa" readonly value="">
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Date NTP</label>
								<div class="controls">
									<input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDateNtp" id="txDateNtp" readonly value="<?php #echo date('Y-m-d')?>">
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">MPFee (%)</label>
								<div class="controls"><input type="text" style="width: 90px;" name="txMPFee" id="txMPFee" value=".8" /></div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Commitment (%)</label>
								<div class="controls"><input type="text" style="width: 90px;" name="txCommitment" id="txCommitment" value="" /></div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Consultancy (%)</label>
								<div class="controls"><input type="text" style="width: 90px;" name="txConsultancy" id="txConsultancy" value="" /></div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Finders (%)</label>
								<div class="controls"><input type="text" style="width: 90px;" name="txFinders" id="txFinders" value="" /></div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Technical (%)</label>
								<div class="controls"><input type="text" style="width: 90px;" name="txTechnical" id="txTechnical" value="" /></div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Gen. Over (%)</label>
								<div class="controls"><input type="text" style="width: 90px;" name="txGenOver" id="txGenOver" value="12" /></div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Profit (%)</label>
								<div class="controls"><input type="text" style="width: 90px;" name="txProfit" id="txProfit" value="10" /></div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Royalty (%)</label>
								<div class="controls"><input type="text" style="width: 90px;" name="txRoyalty" id="txRoyalty" value="0" /></div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Under </label>
								<div class="controls">
									<select name="txUnder" id="txUnder" style="width:300px;" required>
										<option value="">--select--</option>
										<?php
										$qUnder = $db->query('SELECT * FROM project WHERE project="0"');
										while($rUnder = $db->fetch_array($qUnder)):?>
										<option value="<?php echo $rUnder['proj_id']?>" ><?php echo $rUnder['proj_name']?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" style="font-weight:bold;" id="msgUnder" name="msgUnder"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">TIN</label>
								<div class="controls"><input type="text" class="span6" name="txTIN" id="txTIN" value=""></div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Remark</label>
								<div class="controls"><input type="text" class="span6" name="txRemark" id="txRemark" value=""></div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Client </label>
								<div class="controls">
									<select name="selClient" id="selClient" data-rel="chosen" style="width:410px;font-size:12px;">
										<option value="">--select--</option>
										<?php
										$qpc = $db->query('SELECT * FROM project_client ORDER BY pc_name');
										while($rpc = $db->fetch_array($qpc)):?>
										<option value="<?php echo $rpc['pc_id']?>" ><?php echo $rpc['pc_name']?></option>
										<?php endwhile;?>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Status</label>
								<div class="controls">
									<select class="span6" name="projStatus" id="projStatus">
										<option value="ongoing">On Going</option>
										<option value="done">Done</option>
										<option value="closed">Closed</option>
									</select>
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
					<tr>
						<td>&nbsp;</td>
					</tr>
				</table>
			</form>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script type="text/javascript" src="../js/bootstrap-multiselect.js"></script>
<script>
$(document).ready(function(){
	var res = false;
	$('#btnCreate').click(function(){
		$('#msgProj_name').html("");
		$('#msgUnder').html("");
		$('#msgDateStart').html("");
		if( $('#txProj_name').val()=="" ){
			$('#txProj_name').focus();
			$('#msgProj_name').html("Project Name Required!");
			res=false;
		}
		else if( $('#txDateStart').val()=="" ){
			$('#msgDateStart').html("Field Required!");
			$('#txDateStart').focus();
			res=false;
		}
		else if( $('#txUnder').val()=="" ){
			$('#msgUnder').html("Field Required!");
			$('#txUnder').focus();
			res=false;
		}
		else{
			if(confirm('Do you want to create new project?'))
				res=true;
			else
				res=false;
		}
		return res;
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
});
</script>