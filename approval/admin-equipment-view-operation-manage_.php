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
$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;
$txDateFrom='';$txDateTo='';$txPH='';$txSBA='';$txHRR='';$txDetails='';$txRemarks='';
$txItmEdt='';$txDT='';


$dte = (isset($_REQUEST['dte']) && !empty($_REQUEST['dte']) ) ? functions::decode($_REQUEST['dte']) : '';
if($dte){
	$txDateFrom=$dte;
	$txDateTo=$dte;
}


$editTrue=0;$ea_date=date('Y-m-d');$invoice='';$remarks='';$repair_type='';$location='';$description='';$qLabor=0;$qParts=0;$discount='';$incharge='';$proj_id='';$diID='';
if( isset($_REQUEST['txItmEdt']) && !empty($_REQUEST['txItmEdt']) ){
	$txItmEdt = functions::decode($_REQUEST['txItmEdt']);
	$editTrue = $db->getValue('equip_operation','count(eop_id)',array('eop_id'=>$txItmEdt));
	$qvedt = $db->select('equip_operation','*',array('eop_id'=>$txItmEdt));
	$rvedt = $db->fetch_array($qvedt);
	$txDateFrom = $rvedt['eop_date_start'];
	$txDateTo = $rvedt['eop_date_end'];
	$txRemarks = $rvedt['remarks'];
	$txDetails = $rvedt['eop_details'];
	$equip_id = $rvedt['equip_id'];
	$txPH = $rvedt['ph'];
	$txDT = $rvedt['dt'];
	$txSBA = $rvedt['sba'];
	$txHRR = $rvedt['hr_reading'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Equipment Accessory Add Form</title>
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
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
	<style>
	.tdSpace{padding: 10px 0px 4px 10px;}
	</style>
<?php
if( isset($_POST['btnAdd']) && $equip_id){
	$txDateFrom = ( isset($_POST['txDateFrom']) ) ? $_POST['txDateFrom'] : '';
	$txDateTo = ( isset($_POST['txDateTo']) ) ? $_POST['txDateTo'] : '';
	$txRemarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : "";
	$txDetails = ( isset($_POST['txDetails']) && !empty($_POST['txDetails']) ) ? trim($_POST['txDetails']) : "";
	$txDT = ( isset($_POST['txDT']) && !empty($_POST['txDT']) ) ? functions::moneyToDouble($_POST['txDT']) : 0;
	$txPH = ( isset($_POST['txPH']) && !empty($_POST['txPH']) ) ? functions::moneyToDouble($_POST['txPH']) : 0;
	$txSBA = ( isset($_POST['txSBA']) && !empty($_POST['txSBA']) ) ? functions::moneyToDouble($_POST['txSBA']) : 0;
	$txHRR = ( isset($_POST['txHRR']) && !empty($_POST['txHRR']) ) ? functions::moneyToDouble($_POST['txHRR']) : 0;

	$arrDetails = array('equip_id'=>$equip_id,'eop_date_start'=>$txDateFrom,'eop_date_end'=>$txDateTo,'eop_details'=>$txDetails,'ph'=>$txPH,'dt'=>$txDT,'sba'=>$txSBA,'hr_reading'=>$txHRR,'remarks'=>$txRemarks);

	if( $txDateTo && $txDateFrom && $equip_id && $txDetails ){
		if($txDateTo >= $txDateFrom){
			if( $db->getValue('equip_operation','count(*)',array('equip_id'=>$equip_id),'AND ( eop_date_start BETWEEN "'.$txDateFrom.'" AND "'.$txDateTo.'" OR eop_date_end BETWEEN "'.$txDateFrom.'" AND "'.$txDateTo.'" ) ') ){
				functions::say('Date Conflict! Please change the date.');
			}
			else{
				$eop_id = $db->insert('equip_operation',$arrDetails);
				if($eop_id){
					$_SESSION['notif_success']='New Record Added!';
					functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id));
					die();
				}
				else{
					functions::say('Record Failed to Add!');
				}
			}
		}
		else{
			functions::say('Please fill up the date properly!');
		}
	}
	else{
		functions::say('Please fill up the form properly!');
	}
}

if( isset($_POST['btnSave']) && $equip_id){
	echo $_POST['txSBA'];echo '<br>';
	function moneyToDoubleS($money){
		$number=0;
		$decimal=0;
		$num_break = explode('.',$money);
		if(count($num_break)>1){
			$decimal = $num_break[1];
			$number = $num_break[0];
		}
		else
			$number = $money;
		$s = preg_replace('|[^0-9]|i', '', $number);
		$s = ($money < 0) ? '-'.$s : $s;
		if($money < 999.99)
			$final = $money;
		else
			$final = ($decimal) ? floatval($s.'.'.$decimal) : $s;
		return $final;
	}
	echo '<br>';
	echo moneyToDoubleS($_POST['txSBA']);
	echo '<br>';
	echo moneyToDoubleS('999.45') * 1;
	// echo '<br>';
	#$a = '8.'.'45';
	#echo floatval($a);


	$eop_id = ( isset($_POST['txItmEdt']) && !empty($_POST['txItmEdt']) ) ? functions::decode($_POST['txItmEdt']) : 0;
	$txDateFrom = ( isset($_POST['txDateFrom']) ) ? $_POST['txDateFrom'] : '';
	$txDateTo = ( isset($_POST['txDateTo']) ) ? $_POST['txDateTo'] : '';
	$txRemarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : "";
	$txDetails = ( isset($_POST['txDetails']) && !empty($_POST['txDetails']) ) ? trim($_POST['txDetails']) : "";
	$txDT = ( isset($_POST['txDT']) && !empty($_POST['txDT']) ) ? functions::moneyToDouble($_POST['txDT']) : 0;
	$txPH = ( isset($_POST['txPH']) && !empty($_POST['txPH']) ) ? functions::moneyToDouble($_POST['txPH']) : 0;
	$txSBA = ( isset($_POST['txSBA']) && !empty($_POST['txSBA']) ) ? functions::moneyToDouble($_POST['txSBA']) : 0;
	$txHRR = ( isset($_POST['txHRR']) && !empty($_POST['txHRR']) ) ? functions::moneyToDouble($_POST['txHRR']) : 0;

	$arrUpdate = array('equip_id'=>$equip_id,'eop_date_start'=>$txDateFrom,'eop_date_end'=>$txDateTo,'eop_details'=>$txDetails,'ph'=>$txPH,'dt'=>$txDT,'sba'=>$txSBA,'hr_reading'=>$txHRR,'remarks'=>$txRemarks);
	if( $eop_id && $txDateTo && $txDateFrom && $equip_id && $txDetails ){
		echo $db->updatePrint('equip_operation',$arrUpdate,array('eop_id'=>$eop_id));
		#echo $db->last_query;
		#$_SESSION['notif_success']='Record Successfully updated!';
		#functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id).'&txItmEdt='.functions::encode($eop_id));
		#die();
	}
}

$qDetails = $db->query('SELECT DISTINCT eop_details FROM equip_operation ORDER BY eop_details');
$namesDetails='';
while($rDetails=$db->fetch_array($qDetails)):
	$string = preg_replace("/'/",'"',$rDetails['eop_details']);
	$namesDetails .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesDetails .= '"--"';
?>
</head>
<body>
<!-- body content: start here-->
<form method="post">
	<div class="row-fluid">
		<div class="box span12">
			<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white edit"></i><span class="break"></span>PROPERTY OPERATION MANAGE</h2>
			</div>
			<div class="box-content">
				<div align="center">
					<input type="hidden" name="txItmEdt" id="txItmEdt" value="<?php echo functions::encode($txItmEdt);?>">
					<table width="40%" border="0" align="center" style="background-color:#f9f6f6">
						<tr>
							<th bgcolor="#f7ebeb" scope="col" height="60px;" width="30%"><div align="center">Property</div></th>
							<td class="tdSpace"><div align="left"><strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong></div></td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Date Start</div></th>
							<td class="tdSpace">
								<div align="left">
									<a href="javascript:NewCssCal('txDateFrom')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp" onclick="javascript:NewCssCal('txDateFrom');"></a>
									<input name="txDateFrom" type="text" class="span6 mytextbox" style="width:180px;" id="txDateFrom" value="<?php echo $txDateFrom?>" style="width: 90px;" onclick="javascript:NewCssCal('txDateFrom')" onblur=" document.getElementById('txDateTo').value=this.value;" required>
								</div>
							</td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Date End</div></th>
							<td class="tdSpace">
								<div align="left">
									<a href="javascript:NewCssCal('txDateTo')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp" onclick="javascript:NewCssCal('txDateTo')"></a>
									<input name="txDateTo" type="text" class="span6 mytextbox" style="width:180px;" id="txDateTo" value="<?php echo $txDateTo?>" style="width: 90px;" onclick="javascript:NewCssCal('txDateTo')" required>
								</div>
							</td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Details</div></th>
							<td class="tdSpace">
								<div align="left">
									<textarea style="width:300px;" class="span6 typeahead" name="txDetails" id="txDetails" autocomplete='off' autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesDetails;?>]' required><?php echo $txDetails?></textarea>
								</div>
							</td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Program Hours</div></th>
							<td class="tdSpace">
								<div align="left">
									<input type="text" style="width:150px;" name="txPH" id="txPH" value="<?php echo ($txPH) ? functions::formatMoney($txPH) : '';?>" onkeyup="FormatCurrency(this);" onkeypress="return checkinput(this, event);">
								</div>
							</td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Down Time</div></th>
							<td class="tdSpace">
								<div align="left">
									<input type="text" style="width:150px;" name="txDT" id="txDT" value="<?php echo ($txDT) ? functions::formatMoney($txDT) : '';?>" onkeyup="FormatCurrency(this);" onkeypress="return checkinput(this, event);">
								</div>
							</td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">SBA</div></th>
							<td class="tdSpace"><div align="left"><input type="text" style="width:150px;" name="txSBA" id="txSBA" value="<?php echo ($txSBA) ? functions::formatMoney($txSBA) : '';?>" onkeyup="FormatCurrency(this);" onkeypress="return checkinput(this, event);"></div></td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">HR READING</div></th>
							<td class="tdSpace"><div align="left"><input type="text" style="width:150px;" class="span6 typeahead" name="txHRR" id="txHRR" autocomplete='off' autocomplete="off" value="<?php echo $txHRR?>"></div></td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">REMARKS / STATUS</div></th>
							<td class="tdSpace">
								<div align="left">
									<textarea style="width:300px;" rows="5" class="span6 typeahead" name="txRemarks" id="txRemarks" autocomplete='off'><?php echo $txRemarks?></textarea>
									<span class="help-inline warning" id="msgtxRemarks" style="font-weight:bold;" name="msgtxRemarks"></span>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div align="center">
								<?php 
								if($editTrue)
								echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary btn-small">';
								else
								echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary btn-small">';
								?>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div><!--/span-->
	</div><!--/row-->
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
<script>
$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		if(confirm('Do you want to submit this information?'))
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
<!-- end: JavaScript-->
</body>
</html>