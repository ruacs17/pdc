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
$eat_id = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$editItem = (isset($_REQUEST['edtID']) && !empty($_REQUEST['edtID']) ) ? functions::decode($_REQUEST['edtID']) : 0;
$deleteItem = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
$confirmed = $db->getValue('emp_attendance','confirmed',array('eat_id'=>$eat_id));
$adjustment_name='';$adjustment_value='';$adjustment_mode='';
$att_year = $db->getValue('emp_attendance','att_year',array('eat_id'=>$eat_id));
$att_month = $db->getValue('emp_attendance','att_month',array('eat_id'=>$eat_id));
$arrPosition=array();
$pay_confirm = $db->getValue('emp_attendance_personnel','count(*)',array('emp_id'=>$eid,'eat_id'=>$eat_id,'pay_ready'=>1));
$fromPayroll = (isset($_REQUEST['pr']) && !empty($_REQUEST['pr']) ) ? 1 : 0;
if($fromPayroll)
	$_SESSION['notif_indi_id']=$eid;
if($editItem){
	$q = $db->select('payroll_adjustment','*',array('pa_id'=>$editItem));
	while($r = $db->fetch_array($q)):
		$adjustment_name = $r['adjustment_name'];
		$adjustment_value = functions::formatMoney($r['adjustment_value']);
		$adjustment_mode = $r['adjustment_mode'];
	endwhile;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Payroll Deduction</title>
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
if( isset($_POST['btnSave']) && !empty($eid) && !empty($eat_id) && $editItem ){
	$adjustment_name = $db->getValue('payroll_adjustment','adjustment_name',array('pa_id'=>$editItem));
	$adjustment_value = ( isset($_POST['txAdjustmentVal']) ) ? functions::moneyToDouble($_POST['txAdjustmentVal']) : 0;

	$arrField = array('adjustment_value'=>$adjustment_value,'emp_id'=>$eid,'eat_id'=>$eat_id,'adjustment_type'=>'deduction');

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
		echo $db->last_query;
	}
	$db->update('payroll_adjustment',$arrField,array('pa_id'=>$editItem));
	$_SESSION['notif_success']='Changes Saved!';
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&eatid='.functions::encode($eat_id).'&edtID='.functions::encode($editItem));
	die();
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
				<?php
				$pp_id = $db->getValue('payroll_premium','pp_id',array('pa_id'=>$editItem));
				?>
					<table width="75%" align="center" border="0" class="tablea table-bordered table-hover">
						<thead>
							<tr style="background-color:#E4E1E1">
								<th height="30" style="padding: 10px"  width="45%"><div align="left">DEDUCTION NAME</div></th>
								<th width="20%"><div align="center">DEDUCTION AMOUNT</div></th>
								<th width="14%">&nbsp;</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td height="30" style="padding: 10px">
									<div align="left">
										<input <?php if($confirmed==1 || $pay_confirm==1){echo 'disabled="disabled"';}?> type="text" name="txAdjustmentName" id="txAdjustmentName" class="span6" style="width: 300px;" autofocus value="<?php echo $adjustment_name;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php #echo $namesAdjustment;?>]'  <?php if( $adjustment_mode=="system" ){echo 'readonly';}else if( $adjustment_name=="SSS" || $adjustment_name=="Pagibig" || $adjustment_name=="Philhealth" ){echo 'readonly';}?>>
										<?php if($pp_id){
												$premium_balance=0;
												if( $adjustment_name=="SSS" ){
													$ee_share = $db->getValue('payroll_premium','sum(ee_share)',array('emp_id'=>$eid,'pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'SSS'));
													$base_pay = $db->getValue('payroll_premium','sum(base_pay)',array('emp_id'=>$eid,'pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'SSS'));
													$qS = $db->query('SELECT * FROM table_sss WHERE "'.$base_pay.'" BETWEEN sal_from AND sal_to');
													$rS = $db->fetch_array($qS);
													$premium_balance = $rS['sss_ee_share'] - $ee_share;
												}
												if( $adjustment_name=="Pagibig"){
													$premium_balance = $db->getValue('payroll_premium','(ee_personal+ee_orig)-ee_share as sasa',array('emp_id'=>$eid,'pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'HDMF'),'ORDER BY pp_id DESC LIMIT 1');
												}
												if( $adjustment_name=="Philhealth"){
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
													#$premium_balance = $phic_ee_share - $ee_share;
													$premium_balance = round($phic_ee_share - $ee_share,12);
												}

												#$premium_balance = $db->getValue('payroll_premium','sum( (ee_orig+ee_personal)-ee_share ) as eerg',array('pp_id'=>$pp_id));
												if($premium_balance)
													echo '<div style="padding:3px 0px 0px 15px;"><i>Premium Balance Payable: '.$premium_balance.'</i></div>';
										?>
										<?php } ?>
									</div>
								</td>
								<td style="padding: 10px;"><div align="center"><input <?php if($confirmed==1 || $pay_confirm==1){echo 'disabled="disabled"';}?> type="text" name="txAdjustmentVal" id="txAdjustmentVal" style="width: 144px;text-align:center;" class="span6" value="<?php echo $adjustment_value?>" onkeyup="FormatCurrency(this);"></div></td>
								<td style="padding: 10px;"><div align="center"><input <?php if($confirmed==1 || $pay_confirm==1){echo 'disabled="disabled"';}?> type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small"></div></td>
							</tr>
							<?php if($confirmed==1 || $pay_confirm==1){ ?>
							<tr>
								<td height="50" colspan="3"><div align="center"><strong><?php echo 'Payroll is already confirmed. Cannot modify!';?></strong></div></td>
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
<script>
function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}
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
</body>
</html>