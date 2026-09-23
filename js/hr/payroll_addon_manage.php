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
$confirmed = $db->getValue('emp_attendance','confirmed',array('eat_id'=>$eat_id));
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$editItem = (isset($_REQUEST['edtID']) && !empty($_REQUEST['edtID']) ) ? functions::decode($_REQUEST['edtID']) : 0;
$deleteItem = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
$paID = (isset($_REQUEST['dID']) && !empty($_REQUEST['dID']) ) ? functions::decode($_REQUEST['dID']) : 0;
$adjustment_name='';$adjustment_value='';
$arrPosition=array();
$pay_confirm = $db->getValue('emp_attendance_personnel','count(*)',array('emp_id'=>$eid,'eat_id'=>$eat_id,'pay_ready'=>1));
if($editItem){
	$q = $db->select('payroll_adjustment','*',array('pa_id'=>$editItem));
	while($r = $db->fetch_array($q)):
		$adjustment_name = $r['adjustment_name'];
		$adjustment_value = functions::formatMoney($r['adjustment_value']);
	endwhile;
}
if($deleteItem){
	$db->delete('payroll_adjustment',array('pa_id'=>$deleteItem));
	$_SESSION['notif_warning']='Adjustment Removed!';
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&eatid='.functions::encode($eat_id));
	die();
}
if($paID){
	if( $db->getValue('payroll_adjustment','include',array('pa_id'=>$paID,'emp_id'=>$eid,'eat_id'=>$eat_id))==1 ){
		$db->update('payroll_adjustment',array('include'=>0),array('pa_id'=>$paID,'emp_id'=>$eid,'eat_id'=>$eat_id));
		$_SESSION['notif_warning']='Adjustment Excluded!';
	}
	else{
		$db->update('payroll_adjustment',array('include'=>1),array('pa_id'=>$paID,'emp_id'=>$eid,'eat_id'=>$eat_id));
		$_SESSION['notif_success']='Adjustment Included!';
	}
	$_SESSION['notif_id']=$paID;
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&eatid='.functions::encode($eat_id));
	die();
}

$qItemAdjustment = $db->query('SELECT DISTINCT adjustment_name FROM payroll_adjustment WHERE adjustment_type="addon" ORDER BY adjustment_name');
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
	<title>Payroll Add-on</title>
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
}
if( isset($_POST['btnSave']) && !empty($eid) && !empty($eat_id) ){
	$adjustment_name = ( isset($_POST['txAdjustmentName']) ) ? trim($_POST['txAdjustmentName']) : '';
	$adjustment_value = ( isset($_POST['txAdjustmentVal']) ) ? functions::moneyToDouble($_POST['txAdjustmentVal']) : 0;
	$arrField = array('adjustment_name'=>$adjustment_name,'adjustment_value'=>$adjustment_value,'emp_id'=>$eid,'eat_id'=>$eat_id,'adjustment_type'=>'addon');
	if($adjustment_name){
		if($editItem){
			$db->update('payroll_adjustment',$arrField,array('pa_id'=>$editItem));
			$_SESSION['notif_id']=$editItem;
			$_SESSION['notif_success']='Changes saved!';
		}
		else{
			$ins = $db->insert('payroll_adjustment',array_merge($arrField,array('adjustment_mode'=>'manual')));
			$_SESSION['notif_success']='Adjustment Added!';
			$_SESSION['notif_id']=$ins;
		}
			
		$_SESSION['notif_success']='Saved Successfully!';
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>PAYROLL ADD-ON MANAGE</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="left">Name: <strong><?php echo $db->getValue('employee','concat(lname, ", ", fname)',array('emp_id'=>$eid));?></strong><br><br></div>
				<div align="center">
				<table id="tblist" align="center" width="75%" border="1" class="table-hover" style="border-color: #F1F2E3; font-size:12px;">
					<thead>
						<tr style="background-color:#E4E1E1">
							<th width="8%"><div align="center">INCLUDE</div></th>
							<th height="30" style="padding: 10px" width="45%"><div align="left">ADD-ON NAME</div></th>
							<th width="20%"><div align="center">AMOUNT</div></th>
							<th width="14%">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php if( ($confirmed==0 && $pay_confirm==0) && empty($editItem)){?>
						<tr>
							<td></td>
							<td height="30" style="padding: 10px"><div align="left"><input type="text" name="txAdjustmentName" id="txAdjustmentName" class="span6" style="width: 300px;" autofocus value="<?php echo $adjustment_name;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesAdjustment;?>]' required></div></td>
							<td style="padding: 10px"><div align="center"><input type="text" name="txAdjustmentVal" id="txAdjustmentVal" style="width: 144px;text-align:center;" class="span6" value="<?php echo $adjustment_value?>" onkeyup="FormatCurrency(this);" required></div></td>
							<td style="padding: 10px">
								<div align="center">
									<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-mini">
									<input type="reset" name="btnReset" id="btnReset" value=" Cancel " class="btn btn-mini">
								</div>
							</td>
						</tr>
						<?php }?>
						<?php
						$total_adjustment=0;
						$q = $db->select('payroll_adjustment','*',array('emp_id'=>$eid,'eat_id'=>$eat_id,'adjustment_type'=>'addon'),'ORDER BY adjustment_name');
						while($r = $db->fetch_array($q)):
							$rID = $r['pa_id'];
							if($r['include'])
								$total_adjustment += $r['adjustment_value'];
							if($editItem==$rID){
						?>
						<?php if($confirmed==0 && $pay_confirm==0){?>
						<tr>
							<td></td>
							<td height="30" style="padding: 10px"><div align="left"><input type="text" name="txAdjustmentName" id="txAdjustmentName" class="span6" style="width: 300px;" autofocus value="<?php echo $adjustment_name;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesAdjustment;?>]' required></div></td>
							<td style="padding: 10px"><div align="center"><input type="text" name="txAdjustmentVal" id="txAdjustmentVal" style="width: 144px;text-align:center;" class="span6" value="<?php echo $adjustment_value?>" onkeyup="FormatCurrency(this);" required></div></td>
							<td style="padding: 10px">
								<div align="center">
									<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-mini">
									<a href="?eid=<?php echo functions::encode($eid)?>&eatid=<?php echo functions::encode($eat_id) ?>" class="btn btn-mini">Cancel</a>
								</div>
							</td>
						</tr>
						<?php }?>
						<?php }else{ ?>
						<tr id="rw<?php echo $rID;?>"  <?php if(empty($r['include'])){echo 'style="background-color:#C7C5C1"';}?> >
							<td><div align="center"><input type="checkbox" name="chck<?php echo $rID?>" id="chck<?php echo $rID?>" value="<?php echo functions::encode($rID)?>" <?php if($r['include']==1){echo 'checked="checked"';}?> onClick="chkInclude(this.value)" <?php if($confirmed)echo 'disabled="disabled"';?>></div></td>
							<td style="padding: 10px"><div align="left"><?php echo $r['adjustment_name']?><?php //echo ($r['adjustment_desc']) ? '<br><i>'.$r['adjustment_desc'].'</i>' : '';?></div></td>
							<td style="padding: 10px"><div align="center"><?php echo functions::formatMoney($r['adjustment_value'])?></div></td>
							<td style="padding: 15px">
								<?php if($confirmed==0 && $r['adjustment_mode']=='manual'){?>
								<div align="center">
									<a id="edt<?php echo $rID;?>" class="btn btn-mini btn-warning" title="Modify" data-rel="tooltip" href="?eid=<?php echo functions::encode($eid);?>&eatid=<?php echo functions::encode($eat_id);?>&edtID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white pencil"></i></a>
									<a id="del<?php echo $rID;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Item" data-rel="tooltip" href="?eid=<?php echo functions::encode($eid);?>&eatid=<?php echo functions::encode($eat_id);?>&delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
								</div>
								<?php }?>
							</td>
						</tr>
						<?php } ?>
						<?php endwhile;?>
						<tr>
							<td height="40">&nbsp;</td>
							<td>&nbsp;</td>
							<td><div align="center">Total <strong><?php echo functions::formatMoney($total_adjustment)?></strong></div></td>
							<td>&nbsp;</td>
						</tr>
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
function delt(){if(confirm('Do you want to remove this adjustment?'))return true; else return false;}
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