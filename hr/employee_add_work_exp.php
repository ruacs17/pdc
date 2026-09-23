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
$txPosition='';$txCompany='';$txNoYr ='';$selMonFrom='';$selDayFrom='';$selYrFrom='';$selMonTo='';$selDayTo='';$selYrTo='';$ewe_date_from='';$ewe_date_to='';
if($itemEdt){
	$q = $db->select('emp_work_exp','*',array('ewe_id'=>$itemEdt));
	while($r = $db->fetch_array($q)):
		$txPosition = $r['ewe_position'];
		$txCompany = $r['ewe_company'];
		$txNoYr = $r['ewe_no_year'];
		$ewe_date_from = $r['ewe_date_from'];
		$ewe_date_to = $r['ewe_date_to'];
		$dateFrom = explode("-",$r['ewe_date_from']);
		$dateTo = explode("-",$r['ewe_date_to']);
		if(count($dateFrom)==3){
			$selMonFrom=$dateFrom[1];
			$selDayFrom=$dateFrom[2];
			$selYrFrom=$dateFrom[0];
		}
		if(count($dateTo)==3){
			$selMonTo=$dateTo[1];
			$selDayTo=$dateTo[2];
			$selYrTo=$dateTo[0];
		}
	endwhile;
}
$childDelete = (isset($_REQUEST['cidDel']) && !empty($_REQUEST['cidDel']) ) ? functions::decode($_REQUEST['cidDel']) : 0;
if($itemDel){
	$_SESSION['notif_warning']='Information Removed!';
	$db->delete('emp_work_exp',array('ewe_id'=>$itemDel,'emp_id'=>$eid));
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
if( isset($_POST['btnSave']) ){

	$ewe_company = ( isset($_POST['txCompany']) ) ? strtoupper(trim($_POST['txCompany'])) : '';
	$ewe_position = ( isset($_POST['txPosition']) ) ? strtoupper(trim($_POST['txPosition'])) : '';

	$ewe_date_from = ( isset($_POST['txFromDate']) && !empty($_POST['txFromDate']) ) ? trim($_POST['txFromDate']) : NULL;
	$ewe_date_to = ( isset($_POST['txToDate']) && !empty($_POST['txToDate']) ) ? trim($_POST['txToDate']) : NULL;

	$ewe_no_year = functions::year_diff($ewe_date_from,$ewe_date_to);

	$arrInsert = array('emp_id'=>$eid,'ewe_position'=>$ewe_position,'ewe_company'=>$ewe_company,'ewe_no_year'=>$ewe_no_year,'ewe_date_from'=>$ewe_date_from,'ewe_date_to'=>$ewe_date_to);
	if($itemEdt){
		$_SESSION['notif_id_list']=$itemEdt;
		$db->update('emp_work_exp',$arrInsert,array('ewe_id'=>$itemEdt,'emp_id'=>$eid));
		$_SESSION['notif_success']='Changes Saved!';
	}else{
		$ins = $db->insert('emp_work_exp',$arrInsert);
		if($ins){
			$_SESSION['notif_id_list']=$ins;
			$_SESSION['notif_success']='Information Added!';
		}
	}
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Working Experience</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
				<div align="center" style="padding-bottom: 15px;"><h2>WORK EXPERIENCES </h2><i>(Start from your current work)</i></div>
				<table id="tblist" width="80%" align="center" border="0" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="background-color:#E4E1E1; font-size: 12px;">
					<thead>
						<tr>
							<td colspan="2" width="20%"><div align="center"><strong>INCLUSIVE DATE(S)</strong></div></td>
							<td rowspan="2" width="30%"><strong>POSITION</strong></td>
							<td rowspan="2" width="30%"><strong>DEPARTMENT / AGENCY / OFFICE / COMPANY</strong></td>
							<td rowspan="2" width="10%"><strong>NO. OF YEAR(S) IN SERVICE</strong></td>
							<td rowspan="2" width="10%"><div align="center"><strong>Option</strong></div></td>
						</tr>
						<tr>
							<td width="10%"><strong>From</strong></td>
							<td width="10%"><strong>To</strong></td>
						</tr>
					</thead>
					<tbody>
						<?php
						$countWrkExp=0;
						$q = $db->select('emp_work_exp','*',array('emp_id'=>$eid),'ORDER BY ewe_date_from DESC');
						while($r = $db->fetch_array($q)):
							$yrExp = functions::year_diff($r['ewe_date_from'],$r['ewe_date_to']);
							$countWrkExp += $yrExp;
							$rID = $r['ewe_id'];
						?>
						<tr id="rw<?php echo $rID?>" style="background-color:#ececec">
							<td><?php echo functions::datearr($r['ewe_date_from'])?></td>
							<td><?php echo functions::datearr($r['ewe_date_to'])?></td>
							<td><?php echo $r['ewe_position']?></td>
							<td><?php echo $r['ewe_company']?></td>
							<td><div align="center"><?php echo $yrExp?></div></td>
							<td>
								<div align="center">
									<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeEdt=<?php echo functions::encode($r['ewe_id'])?>&fromED=<?php echo $fromED;?>" title="Modify" class="btn btn-warning btn-mini"><i class="halflings-icon white pencil"></i></a>
									<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeDlt=<?php echo functions::encode($r['ewe_id'])?>&fromED=<?php echo $fromED;?>" onClick="return askDel()" title="Remove" class="btn btn-danger btn-mini"><i class="halflings-icon white trash"></i></a>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td colspan="6"><div align="center">TOTAL YEARS OF WORK EXPERIENCES: <?php echo $countWrkExp;?></div></td>
						</tr>
					</tbody>
				</table><br><br><br>
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1;">
					<tr>
						<td height="30" width="30%"><div align="right">INCLUSIVE DATE (FROM)</div></td>
						<td><input name="txFromDate" type="text" id="txFromDate" value="<?php echo $ewe_date_from; ?>" style="width: 90px;"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">INCLUSIVE DATE (TO)</div></td>
						<td><input name="txToDate" type="text" id="txToDate" value="<?php echo $ewe_date_to; ?>" style="width: 90px;"></td>
					</tr>
					<tr>
						<td width="17%" height="30"><div align="right">POSITION TITLE</div></td>
						<td width="43%"><input type="text" name="txPosition" id="txPosition" class="span6" value="<?php echo $txPosition;?>" required /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">DEPARTMENT / AGENCY / OFFICE / COMPANY</div></td>
						<td><input type="text" name="txCompany" id="txCompany" class="span6" value="<?php echo $txCompany;?>" required /></td>
					</tr>
				</table>
				<div align="center">
					<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small">
					<?php if ($itemEdt): ?><a class="btn btn-small" href="?eid=<?php echo functions::encode($eid)?>&fromED=<?php echo $fromED;?>">Cancel</a><?php endif ?>
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
$(document).ready(function(){
	$('#txFromDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'1970:<?php echo date('Y')+1 ?>',
		beforeShow:function(selectdate){
			 var dt=new Date($('#txFromDate').val());
			dt.setDate(dt.getDate())
			$('#txToDate').datepicker('option','minDate',dt);
			$('#txToDate').datepicker('option','defaultDate',$('#txFromDate').val());
			var dt=new Date($('#txToDate').val());
			dt.setDate(dt.getDate())
			$('#txFromDate').datepicker('option','maxDate',dt);
		},
		onSelect:function(selectdate){
			//alert(selectdate)
			var dt=new Date(selectdate);
			dt.setDate(dt.getDate())
			$('#txToDate').datepicker('option','minDate',dt);
			$('#txToDate').datepicker('option','defaultDate',$('#txFromDate').val());
		}
	});
	$('#txToDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'1970:<?php echo date('Y')+1 ?>',
		yearRange:'1970:<?php echo date('Y')+1 ?>',
		beforeShow:function(selectdate){
			 var dt=new Date($('#txFromDate').val());
			dt.setDate(dt.getDate())
			$('#txToDate').datepicker('option','minDate',dt);
			$('#txToDate').datepicker('option','defaultDate',$('#txFromDate').val());
			var dt=new Date($('#txToDate').val());
			dt.setDate(dt.getDate())
			$('#txFromDate').datepicker('option','maxDate',dt);
		},
		onSelect:function(selectdate){
			var dt=new Date(selectdate);
			dt.setDate(dt.getDate())
			$('#txFromDate').datepicker('option','maxDate',dt);
		}
	});
});
function askDel(){
	if(confirm('Do you want to remove this detail?'))
		return true;
	else
		return false;
}
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
<script type="text/javascript">
function ask(){
	if(confirm('Do you want to save this information?'))
		return true;
	else
		return false;
}
</script>
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