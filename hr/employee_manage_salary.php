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
$fromED = (isset($_REQUEST['fromED']) && !empty($_REQUEST['fromED']) ) ? $_REQUEST['fromED'] : '';
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$itemEdt = (isset($_REQUEST['eeEdt']) && !empty($_REQUEST['eeEdt']) ) ? functions::decode($_REQUEST['eeEdt']) : 0;
$itemDel = (isset($_REQUEST['eeDlt']) && !empty($_REQUEST['eeDlt']) ) ? functions::decode($_REQUEST['eeDlt']) : 0;
$selMon='';$selDay='';$selYr='';$txSalMonth='';$txSalWorkDays='';$txSalDay='';$txSalHour='';$txSalMin='';$selSalType='';$txSalDate='';
if($itemEdt){
	$qSal = $db->select('emp_salary','*',array('es_id'=>$itemEdt,'emp_id'=>$eid));
	if( $db->num_rows($qSal) > 0 ){
		$rSal = $db->fetch_array($qSal);
		$txSalMonth = $rSal['es_salary'];
		$txSalDay = $rSal['es_daily'];
		$txSalHour = $rSal['es_hourly'];
		$txSalMin = $rSal['es_minute'];
		$txSalWorkDays = $rSal['working_days'];
		$txSalDate = $rSal['es_date'];
		$selSalType = $rSal['es_type'];
	}
}
if($itemDel){
	$db->delete('emp_salary',array('es_id'=>$itemDel,'emp_id'=>$eid));
	$q = $db->select('emp_salary','*',array('emp_id'=>$eid),'ORDER BY es_date DESC,es_id DESC');
	$r = $db->fetch_array($q);
	$essalary = $r['es_salary'];
	$db->update('employee',array('salary'=>$essalary),array('emp_id'=>$eid));
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Salary</title>
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
	<style type="text/css">body{font-size: 12px;}</style>
<?php
if( isset($_POST['btnSave']) ){

	$es_salary = ( isset($_POST['txSalMonth']) && !empty($_POST['txSalMonth']) ) ? functions::moneyToDouble($_POST['txSalMonth']) : 0;
	$working_days = ( isset($_POST['txSalWorkDays']) && !empty($_POST['txSalWorkDays']) ) ? trim($_POST['txSalWorkDays']) : 0;
	$es_daily = ( isset($_POST['txSalDay']) && !empty($_POST['txSalDay']) ) ? functions::moneyToDouble($_POST['txSalDay']) : 0;
	$es_hourly = ( isset($_POST['txSalHour']) && !empty($_POST['txSalHour']) ) ? functions::moneyToDouble($_POST['txSalHour']) : 0;
	$es_minute = ( isset($_POST['txSalMin']) && !empty($_POST['txSalMin']) ) ? functions::moneyToDouble($_POST['txSalMin']) : 0;
	$es_type = ( isset($_POST['selSalType']) && !empty($_POST['selSalType']) ) ? $_POST['selSalType'] : 0;
    
	if($es_type=='fixed'){
		$working_days = 26;
		$es_salary = $es_daily * $working_days;
		$es_hourly = $es_daily / 8;
		$es_minute = $es_hourly / 60;
	}
	else if($es_type == 'flexible'){
		$working_days = 26;
		$es_daily = $es_salary / $working_days;
		$es_hourly = $es_daily / 8;
		$es_minute = $es_hourly / 60;
	}

	$es_date = ( isset($_POST['txStatDate']) && !empty($_POST['txStatDate']) ) ? trim($_POST['txStatDate']) : NULL;

	if($es_type && ($es_salary || $es_daily) ){
		$arrField = array('emp_id'=>$eid,'es_date'=>$es_date,'es_salary'=>$es_salary,'es_daily'=>$es_daily,'es_hourly'=>$es_hourly,'es_minute'=>$es_minute,'working_days'=>$working_days,'es_type'=>$es_type);
		if($itemEdt){
			$_SESSION['notif_id_list']=$itemEdt;
			$db->update('emp_salary',$arrField,array('es_id'=>$itemEdt,'emp_id'=>$eid));
		}
		else{
			if( $es_id=$db->getValue('emp_salary','es_id',array('es_date'=>$es_date,'emp_id'=>$eid)) ){
				$db->update('emp_salary',$arrField,array('es_date'=>$es_date,'emp_id'=>$eid));
				$_SESSION['notif_id_list']=$es_id;
			}
			else{
				$es_id=$db->insert('emp_salary',$arrField);
				$_SESSION['notif_id_list']=$es_id;
			}
				
		}
		$q = $db->select('emp_salary','*',array('emp_id'=>$eid),'ORDER BY es_date DESC,es_id DESC');
		$r = $db->fetch_array($q);
		$essalary = $r['es_salary'];
		$db->update('employee',array('salary'=>$essalary),array('emp_id'=>$eid));
		#functions::say('Information Saved!');
		$_SESSION['notif_success']='Information Saved!';
		functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
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
		<?php if(empty($fromED)){ ?>
		<div class="box-content">
			<div align="right" class="nav tab-menu nav-tabs" style="padding-top: 3px;"><?php require_once('employee_options.php');?></div>
		</div>
		<?php } ?>
		<div class="box-content">
			<form class="form-horizontal" method="post" onSubmit="return ask()">
				<div align="center" style="padding-bottom: 15px;"><h2>SALARY DETAILS</h2></div>
				<table id="tblist" width="80%" align="center" border="0" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="background-color:#E4E1E1; font-size: 12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<td width="10%"><strong>DATE</strong></td>
							<td width="10%"><strong>MONTHLY</strong></td>
							<td width="10%"><strong>DAYS PER MONTH</strong></td>
							<td width="10%"><strong>DAILY</strong></td>
							<td width="10%"><strong>HOURLY</strong></td>
							<td width="10%"><strong>MINUTE</strong></td>
							<td width="10%"><strong>Type</strong></td>
							<td width="10%"><div align="center">Option</div></td>
						</tr>
					</thead>
					<tbody>
						<?php
						$q = $db->select('emp_salary','*',array('emp_id'=>$eid),'ORDER BY es_date DESC');
						while($r = $db->fetch_array($q)):
						$rID = $r['es_id'];
						?>
						<tr id="rw<?php echo $rID?>" style="background-color:#ececec">
							<td><?php echo functions::datearr($r['es_date'])?></td>
							<td><?php echo functions::formatMoney($r['es_salary'])?></td>
							<td><?php echo $r['working_days']?></td>
							<td><?php echo functions::formatMoney($r['es_daily'],'~')?></td>
							<td><?php echo functions::formatMoney($r['es_hourly'],'~')?></td>
							<td><?php echo functions::formatMoney($r['es_minute'],'~')?></td>
							<td><?php if($r['es_type']=='fixed')echo 'Daily'; elseif($r['es_type']=='flexible')echo 'Monthly';?></td>
							<td>
								<div align="center">
									<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeEdt=<?php echo functions::encode($rID)?>&fromED=<?php echo $fromED;?>" title="Modify" class="btn btn-warning btn-mini"><i class="halflings-icon white pencil"></i></a>
									<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeDlt=<?php echo functions::encode($rID)?>&fromED=<?php echo $fromED;?>" onClick="return askDel()" title="Remove" class="btn btn-danger btn-mini"><i class="halflings-icon white trash"></i></a>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table><br><br><br>
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1;">
					<tr>
						<td></td>
						<td>
							<div align="left">
								<select name="selSalType" id="selSalType" required>
									<option value="">--select-</option>
									<option value="fixed" <?php if($selSalType=='fixed'){echo 'selected="selected"';} ?>>(Labor) Daily Fixed Wage</option>
									<option value="flexible" <?php if($selSalType=='flexible'){echo 'selected="selected"';} ?>>(Office Personnel) Monthly</option>
								</select>
							</div>
						</td>
					</tr>
					<tr>
						<td width="40%" height="30"><div align="right">DATE STARTED</div></td>
						<td>
							<div id="divDate" align="left">
								<a href="javascript:NewCssCal('txStatDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
								<input name="txStatDate" type="text" class="span6 mytextbox" id="txStatDate" onClick="javascript:NewCssCal(this.id)" onkeyup="document.getElementById(this.id).value=''" value="<?php echo $txSalDate; ?>" style="width: 90px;" required>
							</div>
						</td>
					</tr>
					<tr id="trMonthly">
						<td height="30"><div align="right">MONTHLY</div></td>
						<td>
							<div id="divSalMonth">
								<input type="text" name="txSalMonth" id="txSalMonth" class="span6" value="<?php echo $txSalMonth?>" style="width:150px;" onkeyup="FormatCurrency(this);" required>
							</div>
						</td>
					</tr>
					<tr id="trDaily">
						<td height="30"><div align="right">DAILY</div></td>
						<td><input type="text" name="txSalDay" id="txSalDay" class="span6" value="<?php echo $txSalDay?>" style="width:150px;" onkeyup="FormatCurrency(this);" required></td>
					</tr>
					<tr>
						<td height="30"></td>
						<td>
							<div align="left">
								<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small">
								<?php if($itemEdt){?><a class="btn btn-small" href="?eid=<?php echo functions::encode($eid)?>&fromED=<?php echo $fromED;?>">CANCEL</a><?php }?>
							</div>
						</td>
					</tr>
				</table>
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
$(document).ready(function(){
	var salType='';
	$('#txSalMonth').attr('readonly',true);
	$('#txSalDay').attr('readonly',true);

	<?php
	if($selSalType=='fixed'){echo "$('#txSalDay').attr('readonly',false);";}
	if($selSalType=='flexible'){echo "$('#txSalMonth').attr('readonly',false);";}
	?>

	$('#selSalType').change(function(){
		salType = $(this).val();
		if(salType == 'fixed'){
			$('#txSalDay').attr('readonly',false);
			$('#txSalMonth').attr('readonly',true);
			<?php if(empty($itemEdt)){?>$('#txSalMonth').val('');<?php } ?>
		}
		else if(salType=='flexible'){
			$('#txSalMonth').attr('readonly',false);
			$('#txSalDay').attr('readonly',true);
			<?php if(empty($itemEdt)){?>$('#txSalDay').val('');<?php } ?>
		}
		else{
			$('#txSalMonth').attr('readonly',true);
			$('#txSalDay').attr('readonly',true);
		}
	});
});
function askDel(){
	if(confirm('Do you want to remove this information?'))
		return true;
	else
		return false;
}
function ask(){
	if(confirm('Do you want to save this information?'))
		return true;
	else
		return false;
}
document.getElementById("spinner").style.display = "none";//$(window).ready(function(){$("#spinner").fadeOut("slow");});
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

<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>