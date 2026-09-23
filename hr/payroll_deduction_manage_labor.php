<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$fromPayroll = (isset($_REQUEST['pr']) && !empty($_REQUEST['pr']) ) ? 1 : 0;
$eat_id = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$editItem = (isset($_REQUEST['edtID']) && !empty($_REQUEST['edtID']) ) ? functions::decode($_REQUEST['edtID']) : 0;
$deleteItem = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
$confirmed = $db->getValue('emp_attendance','confirmed',array('eat_id'=>$eat_id));
$adjustment_name='';$adjustment_value='';$adjustment_mode='';
$att_year = $db->getValue('emp_attendance','att_year',array('eat_id'=>$eat_id));
$att_month = $db->getValue('emp_attendance','att_month',array('eat_id'=>$eat_id));
$pay_confirm = $db->getValue('emp_attendance_personnel','count(*)',array('emp_id'=>$eid,'eat_id'=>$eat_id,'pay_ready'=>1));
if($fromPayroll && $pay_confirm==0)
	$_SESSION['notif_indi_id']=$eid;
if($editItem){
$_SESSION['notif_id']=$editItem;
$q = $db->select('payroll_adjustment','*',array('pa_id'=>$editItem));
while($r = $db->fetch_array($q)):
	$adjustment_name = $r['adjustment_name'];
	$adjustment_value = functions::formatMoney($r['adjustment_value']);
	$adjustment_mode = $r['adjustment_mode'];
endwhile;
}
if($deleteItem){
	$db->delete('payroll_adjustment',array('pa_id'=>$deleteItem));
	$_SESSION['notif_warning']='Item Removed!';
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&eatid='.functions::encode($eat_id));
	die();
}
$paID = (isset($_REQUEST['dID']) && !empty($_REQUEST['dID']) ) ? functions::decode($_REQUEST['dID']) : 0;
if($paID){
	$adjustment_name=$db->getValue('payroll_adjustment','adjustment_name',array('pa_id'=>$paID,'emp_id'=>$eid,'eat_id'=>$eat_id));
	$adjustment_value=$db->getValue('payroll_adjustment','adjustment_value',array('pa_id'=>$paID,'emp_id'=>$eid,'eat_id'=>$eat_id));
	if( $db->getValue('payroll_adjustment','include',array('pa_id'=>$paID,'emp_id'=>$eid,'eat_id'=>$eat_id))==1 ){
		if( $adjustment_name=="SSS" || $adjustment_name=="Pagibig" || $adjustment_name=="Philhealth"){
			if( $pp_id = $db->getValue('payroll_premium','pp_id',array('emp_id'=>$eid,'eat_id'=>$eat_id,'pa_id'=>$paID)) )
				$db->update('payroll_premium',array('ee_share'=>0,'modified'=>1),array('pp_id'=>$pp_id));
		}
		$db->update('payroll_adjustment',array('include'=>0),array('pa_id'=>$paID,'emp_id'=>$eid,'eat_id'=>$eat_id));
	}
	else{
		if( $adjustment_name=="SSS" || $adjustment_name=="Pagibig" || $adjustment_name=="Philhealth"){
			if( $pp_id = $db->getValue('payroll_premium','pp_id',array('emp_id'=>$eid,'eat_id'=>$eat_id,'pa_id'=>$paID)) ){
				$premium_value = $db->getValue('payroll_premium','(ee_orig+ee_personal) as eeorg',array('pp_id'=>$pp_id));
				#echo $db->last_query;
				#echo $premium_value.' : '.$adjustment_value;
				$modified=($premium_value==$adjustment_value) ? 0 : 1;
				$db->update('payroll_premium',array('ee_share'=>$adjustment_value,'modified'=>$modified),array('pp_id'=>$pp_id));
				#echo $db->last_query;
				#die();
			}
		}
		$db->update('payroll_adjustment',array('include'=>1),array('pa_id'=>$paID,'emp_id'=>$eid,'eat_id'=>$eat_id));
	}
	$_SESSION['notif_id']=$paID;
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&eatid='.functions::encode($eat_id));
	die();
}

$total_adjustment=0;
$qItemAdjustment = $db->query('SELECT DISTINCT adjustment_name FROM payroll_adjustment WHERE adjustment_type="deduction" ORDER BY adjustment_name');
$namesAdjustment='';
while($rItemAdjustment=$db->fetch_array($qItemAdjustment)):
	$string = preg_replace("/'/",'"',$rItemAdjustment['adjustment_name']);
	$namesAdjustment .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesAdjustment .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Payroll Deduction Manage (Labor)</title>
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
<?php
if( isset($_POST['btnCancel']) && !empty($eid) && !empty($eat_id) ){
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&eatid='.functions::encode($eat_id));
	die();
}
if( isset($_POST['btnSave']) && !empty($eid) && !empty($eat_id) ){
	$adjustment_name = ( isset($_POST['txAdjustmentName']) ) ? trim($_POST['txAdjustmentName']) : '';
	$adjustment_value = ( isset($_POST['txAdjustmentVal']) ) ? functions::moneyToDouble($_POST['txAdjustmentVal']) : 0;
	$arrField = array('adjustment_name'=>$adjustment_name,'adjustment_value'=>$adjustment_value,'emp_id'=>$eid,'eat_id'=>$eat_id,'adjustment_type'=>'deduction');
	if($adjustment_name){
		if($editItem){
			if( $adjustment_name=="SSS" || $adjustment_name=="Pagibig" || $adjustment_name=="Philhealth"){
				if( $pp_id = $db->getValue('payroll_premium','pp_id',array('emp_id'=>$eid,'eat_id'=>$eat_id,'pa_id'=>$editItem)) ){//If there's a premium
					$premium_value = $db->getValue('payroll_premium','(ee_orig+ee_personal) as eeorg',array('pp_id'=>$pp_id));//get the original premium value
					if($premium_value!=$adjustment_value){
						$arrField = array_merge($arrField,array('adjustment_mode'=>'manual'));//Tag it as manually adjusted
						$db->update('payroll_premium',array('ee_share'=>$adjustment_value,'modified'=>1),array('pp_id'=>$pp_id));//Update and tag is as modified so if recalculated again, it wont compute this premium
					}
					else{
						$arrField = array_merge($arrField,array('adjustment_mode'=>'system'));//Tag it as manually adjusted
						$db->update('payroll_premium',array('ee_share'=>$adjustment_value,'modified'=>0),array('pp_id'=>$pp_id));//Update and tag is as modified so if recalculated again, it wont compute this premium
					}
				}
			}
			$db->update('payroll_adjustment',$arrField,array('pa_id'=>$editItem));
			$_SESSION['notif_id']=$editItem;
			$_SESSION['notif_success']='Changes Saved!';
		}
		else{
			$ins = $db->insert('payroll_adjustment',array_merge($arrField,array('adjustment_mode'=>'manual')));
			$_SESSION['notif_id']=$ins;
			$_SESSION['notif_success']='Deduction Added!';
		}
		functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&eatid='.functions::encode($eat_id));
		die();
	}
	else
		functions::say('Please fill up the form properly!');
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>PAYROLL DEDUCTION MANAGE</h2></div>
				<div align="left">Name: <strong><?php echo $db->getValue('employee','concat(lname, ", ", fname)',array('emp_id'=>$eid));?></strong><br><br></div>
				<div align="center">
					<table id="tblist" width="75%" align="center" border="1" class="table-hover table-striped" style="border-color: #F1F2E3;">
						<thead>
							<tr style="background-color:#E4E1E1">
								<th width="8%"><div align="center">INCLUDE</div></th>
								<th height="30" style="padding: 10px"  width="45%"><div align="left">DEDUCTION NAME</div></th>
								<th width="20%"><div align="center">DEDUCTION AMOUNT</div></th>
								<th width="14%">&nbsp;</th>
							</tr>
						</thead>
						<tbody>
							<?php if( ($confirmed==0 && $pay_confirm==0) && empty($editItem)){?>
							<tr>
								<td></td>
								<td height="30" style="padding: 10px"><div align="left"><input type="text" name="txAdjustmentName" id="txAdjustmentName" class="span6" style="width: 300px;" autofocus value="<?php echo $adjustment_name;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesAdjustment;?>]' <?php if( $adjustment_mode=="system" )echo 'readonly';?> required></div></td>
								<td style="padding: 10px"><div align="center"><input type="text" name="txAdjustmentVal" id="txAdjustmentVal" style="width: 144px;text-align:center;" class="span6" value="<?php echo $adjustment_value?>" onkeyup="FormatCurrency(this);"></div></td>
								<td style="padding: 10px">
									<div align="center">
										<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small">
										<input type="reset" name="btnCancel" id="btnCancel" value="CANCEL" class="btn btn-warning btn-small">
									</div>
								</td>
							</tr>
							<?php }
							else{echo '<tr><td colspan="4" height="30" style="padding: 10px">&nbsp;</td></tr>';}?>
							<?php
							$q = $db->select('payroll_adjustment','*',array('emp_id'=>$eid,'eat_id'=>$eat_id,'adjustment_type'=>'deduction'),'ORDER BY adjustment_name');
							#echo $db->last_query;
							while($r = $db->fetch_array($q)):
								$rID = $r['pa_id'];
								$pdf_total_amount = ($r['pdf_id']) ? functions::formatMoney($db->getValue('payroll_deduction_reference','total_amount',array('pdf_id'=>$r['pdf_id']))) : '';
								$pp_id = $db->getValue('payroll_premium','pp_id',array('pa_id'=>$rID));
								if($editItem==$rID){
							?>
							<?php if($confirmed==0 && $pay_confirm==0){?>
							<tr id="rw<?php echo $rID;?>">
								<td></td>
								<td height="30" style="padding: 10px"><div align="left"><input type="text" name="txAdjustmentName" id="txAdjustmentName" class="span6" style="width: 300px;" autofocus value="<?php echo $adjustment_name;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesAdjustment;?>]' <?php if( $adjustment_mode=="system" )echo 'readonly';?> required></div></td>
								<td style="padding: 10px"><div align="center"><input type="text" name="txAdjustmentVal" id="txAdjustmentVal" style="width: 144px;text-align:center;" class="span6" value="<?php echo $adjustment_value?>" onkeyup="FormatCurrency(this);"></div></td>
								<td style="padding: 15px">
									<div align="left" style="padding-left:30px;">
										<button type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small" title="Save" data-rel="tooltip"><i class="icon-save"></i></button>
										<button type="submit" name="btnCancel" id="btnCancel" value="CANCEL" class="btn btn-small" title="Cancel" data-rel="tooltip"><i class="icon-ban-circle"></i></button>
									</div>
								</td>
							</tr>
							<?php }?>
							<?php
								}
								else{//If not the edit
							?>
							<tr id="rw<?php echo $rID;?>" <?php if(empty($r['include'])){echo 'style="background-color:#E3E2DF"';}?>>
								<td><div align="center"><input type="checkbox" name="chck<?php echo $rID?>" id="chck<?php echo $rID?>" value="<?php echo functions::encode($rID)?>" <?php if($r['include']==1){echo 'checked="checked"';}?> onClick="chkInclude(this.value)" <?php if($confirmed || $pay_confirm || $r['adjustment_name']=="absences")echo 'disabled="disabled"';?>></div></td>
								<td style="padding: 10px">
									<div align="left">
										<?php if($r['adjustment_name']=="absences")echo '<i>';?><?php echo $r['adjustment_name']?><?php if($r['adjustment_name']=="absences")echo '</i>';echo ($pdf_total_amount) ? '<div style="font-size: 10px;">Total Amount: <i>('.$pdf_total_amount.')</i></div>' : '';?>
										<?php if($pp_id){
												$premium_balance=0;
												if( $r['adjustment_name']=="SSS" ){
													$ee_share = $db->getValue('payroll_premium','sum(ee_share)',array('emp_id'=>$eid,'pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'SSS'));
													$base_pay = $db->getValue('payroll_premium','sum(base_pay)',array('emp_id'=>$eid,'pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'SSS'));
													$qS = $db->query('SELECT * FROM table_sss WHERE "'.$base_pay.'" BETWEEN sal_from AND sal_to');
													$rS = $db->fetch_array($qS);
													$premium_balance = $rS['tc_ee'] - $ee_share;
												}
												if( $r['adjustment_name']=="Pagibig"){
													$premium_balance = $db->getValue('payroll_premium','(ee_personal+ee_orig)-ee_share as sasa',array('emp_id'=>$eid,'pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'HDMF'),'ORDER BY pp_id DESC LIMIT 1');
												}
												if( $r['adjustment_name']=="Philhealth"){
													$ee_share = $db->getValue('payroll_premium','sum(ee_share)',array('emp_id'=>$eid,'pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'PHIC'));
													$base_pay = $db->getValue('payroll_premium','sum(base_pay)',array('emp_id'=>$eid,'pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'PHIC'));
													$qPH = $db->query('SELECT * FROM table_phic WHERE "'.$base_pay.'" BETWEEN sal_from AND sal_to');
													$rPH = $db->fetch_array($qPH);
													if($rPH['phic_type']=='percent'){
														$phic_ee_share = (($rPH['phic_ee_share']/100) * $base_pay);
													}
													else{
														$phic_ee_share = $rPH['phic_ee_share'];
													}
													$premium_balance = $phic_ee_share - $ee_share;
												}
												#$premium_balance = $db->getValue('payroll_premium','sum( (ee_orig+ee_personal)-ee_share ) as eerg',array('pp_id'=>$pp_id));
												if($premium_balance)
													echo '<div style="padding:3px 0px 0px 15px;"><i>Premium Balance Payable: '.$premium_balance.'</i></div>';
										?>
										<?php } ?>
									</div>
								</td>
								<td style="padding: 10px"><div align="center"><?php if($r['adjustment_name']=="absences")echo '<i>(';?><?php echo functions::formatMoney($r['adjustment_value'])?><?php if($r['adjustment_name']=="absences")echo ')</i>';?></div></td>
								<td style="padding: 15px">
									<div align="left" style="padding-left:30px;">
										<?php
										if($r['adjustment_name']=="absences" ){
										}else if( !empty($r['pdf_id']) ){
											if($r['include']==1){
												$total_adjustment += $r['adjustment_value'];
											}
										}elseif($r['adjustment_name']=="SSS" || $r['adjustment_name']=="Pagibig" || $r['adjustment_name']=="Philhealth"){
											if($r['include']==1){
												$total_adjustment += $r['adjustment_value'];
											}
										?>
										<a id="edt<?php echo $rID;?>" <?php if($confirmed==1 || $pay_confirm==1){echo 'style="display:none;"';}?> class="btn btn-mini btn-warning" title="Modify" data-rel="tooltip" href="?eid=<?php echo functions::encode($eid);?>&eatid=<?php echo functions::encode($eat_id);?>&edtID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white pencil"></i></a>
										<?php }else{if($r['include']==1){$total_adjustment += $r['adjustment_value'];}?>
										<a id="edt<?php echo $rID;?>" <?php if($confirmed==1 || $pay_confirm==1){echo 'style="display:none;"';}?> class="btn btn-mini btn-warning" title="Modify" data-rel="tooltip" href="?eid=<?php echo functions::encode($eid);?>&eatid=<?php echo functions::encode($eat_id);?>&edtID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white pencil"></i></a>
										<a id="del<?php echo $rID;?>" <?php if($confirmed==1 || $pay_confirm==1){echo 'style="display:none;"';}?> class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Item" data-rel="tooltip" href="?eid=<?php echo functions::encode($eid);?>&eatid=<?php echo functions::encode($eat_id);?>&delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
										<?php }?>
									</div>
								</td>
							</tr>
							<?php } ?>
							<?php endwhile;?>
							<tr>
								<td colspan="2"><div align="right">&nbsp;<strong>Total</strong> &nbsp;&nbsp;</div></td>
								<td style="padding: 10px"><div align="center"><strong><?php echo functions::formatMoney($total_adjustment)?></strong></div></td>
								<td>&nbsp;</td>
							</tr>
							<?php if($confirmed==1 || $pay_confirm==1){ ?>
							<tr>
								<td height="50" colspan="4"><div align="center"><strong>Payroll is already <?php if($confirmed){echo 'confirmed';}else if($pay_confirm){echo 'verfified';}?>. Cannot modify!</strong></div></td>
							</tr>
							<?php } ?>
						</tbody>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function delt(){if(confirm('Do you want to remove this item?'))return true; else return false;}
function chkInclude(dID){window.location="<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eat_id)?>&eid=<?php echo functions::encode($eid)?>&dID="+dID;}
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
<?php if(isset($_SESSION['notif_id'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','1px solid black');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id']);} ?>
</body>
</html>