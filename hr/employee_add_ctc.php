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
$txCTCNo='';$txCTCPlace='';$bdCTCMon='';$bdCTCDay='';$bdCTCYear='';$ctc_issued_date='';
if($itemEdt){
	$q = $db->select('emp_ctc','*',array('ctc_id'=>$itemEdt,'emp_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$txCTCNo = $r['ctc_no'];
		$txCTCPlace = $r['ctc_issued_at'];
		$ctc_issued_date=$r['ctc_issued_date'];
	endwhile;
}
if($itemDel){
	$_SESSION['notif_warning']='Information Removed!';
	$db->delete('emp_ctc',array('ctc_id'=>$itemDel,'emp_id'=>$eid));
	$q = $db->select('emp_ctc','*',array('emp_id'=>$eid),'ORDER BY ctc_issued_date DESC');
	$r = $db->fetch_array($q);
	$ctc_no = $r['ctc_no'];
	$ctc_issued_date = ($r['ctc_issued_date']) ? $r['ctc_issued_date'] : NULL;
	$ctc_issued_at = $r['ctc_issued_at'];
	$db->update('employee',array('ctc_no'=>$ctc_no,'ctc_issue_date'=>$ctc_issued_date,'ctc_issue_place'=>$ctc_issued_at),array('emp_id'=>$eid));
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}

if( isset($_POST['btnSave']) ){

	$ctc_no = ( isset($_POST['txCTCNo']) ) ? trim($_POST['txCTCNo']) : '';
	$ctc_issued_at = ( isset($_POST['txCTCPlace']) ) ? strtoupper(trim($_POST['txCTCPlace'])) : '';
	$ctc_issued_date = ( isset($_POST['txIssuedDate']) && !empty($_POST['txIssuedDate']) ) ? trim($_POST['txIssuedDate']) : NULL;
	if($ctc_no){
		$arrField = array('emp_id'=>$eid,'ctc_no'=>$ctc_no,'ctc_issued_date'=>$ctc_issued_date,'ctc_issued_at'=>$ctc_issued_at);
		if($itemEdt){
			$_SESSION['notif_id_list']=$itemEdt;
			$_SESSION['notif_success']='Changes Saved!';
			$db->update('emp_ctc',$arrField,array('ctc_id'=>$itemEdt,'emp_id'=>$eid));
		}
		else{
			if( $ctc_id = $db->getValue('emp_ctc','ctc_id',array('ctc_no'=>$ctc_no)) ){
				$_SESSION['notif_id_list']=$ctc_id;
				$_SESSION['notif_success']='Changes Saved!';
				$db->update('emp_ctc',$arrField,array('ctc_no'=>$ctc_no,'emp_id'=>$eid));
			}
			else{
				$ins = $db->insert('emp_ctc',$arrField);
				if($ins){
					$_SESSION['notif_id_list']=$ins;
					$_SESSION['notif_success']='New Information Added!';
				}
			}
		}
		$q = $db->select('emp_ctc','*',array('emp_id'=>$eid),'ORDER BY ctc_issued_date DESC');
		$r = $db->fetch_array($q);
		$ctc_no = $r['ctc_no'];
		$ctc_issued_date = ($r['ctc_issued_date']) ? $r['ctc_issued_date'] : NULL;
		$ctc_issued_at = $r['ctc_issued_at'];
		$db->update('employee',array('ctc_no'=>$ctc_no,'ctc_issue_date'=>$ctc_issued_date,'ctc_issue_place'=>$ctc_issued_at),array('emp_id'=>$eid));
	}
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
$qCTCPlace = $db->query('SELECT DISTINCT ctc_issued_at as "itm" FROM emp_ctc ORDER BY ctc_issued_at');
$namesCTCPlace='';
while($rItemC=$db->fetch_array($qCTCPlace)):
	$string = preg_replace("/'/",'"',$rItemC['itm']);
	$namesCTCPlace .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesCTCPlace .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee CTC</title>
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
				<div align="center" style="padding-bottom: 15px;"><h2>Community TAX Certificate </h2></div>
				<table id="tblist" width="80%" align="center" border="0" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="background-color:#E4E1E1; font-size: 12px;">
					<tr>
						<td width="35%"><strong>CTC No.</strong></td>
						<td width="25%"><strong>ISSUED AT</strong></td>
						<td width="25%"><strong>ISSUED DATE</strong></td>
						<td width="10%"><div align="center"><strong>Option</strong></div></td>
					</tr>
					<?php
					$q = $db->select('emp_ctc','*',array('emp_id'=>$eid),'ORDER BY ctc_issued_date DESC');
					while($r = $db->fetch_array($q)):
					$rID = $r['ctc_id'];
					?>
					<tr id="rw<?php echo $rID?>" style="background-color:#ececec">
						<td><?php echo $r['ctc_no']?></td>
						<td><?php echo $r['ctc_issued_at']?></td>
						<td><?php echo functions::datearr($r['ctc_issued_date'])?></td>
						<td>
							<div align="center">
								<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeEdt=<?php echo functions::encode($rID)?>&fromED=<?php echo $fromED;?>" title="Modify" class="btn btn-warning btn-mini"><i class="halflings-icon white pencil"></i></a>
								<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeDlt=<?php echo functions::encode($rID)?>&fromED=<?php echo $fromED;?>" onClick="return askDel()" title="Remove" class="btn btn-danger btn-mini"><i class="halflings-icon white trash"></i></a>
							</div>
						</td>
					</tr>
					<?php endwhile;?>
				</table><br><br><br>
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1;">
					<tr>
						<td width="17%" height="30"><div align="right">Community TAX Certificate No</div></td>
						<td width="43%"><input type="text" name="txCTCNo" id="txCTCNo" class="span6" value="<?php echo $txCTCNo?>" required></td>
					</tr>
					<tr>
						<td height="30"><div align="right">ISSUED AT</div></td>
						<td><input type="text" name="txCTCPlace" id="txCTCPlace" class="span6" value="<?php echo $txCTCPlace?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesCTCPlace;?>]'></td>
					</tr>
					<tr>
						<td height="30"><div align="right">ISSUED ON</div></td>
						<td>
							<a href="javascript:NewCssCal('txIssuedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
							<input name="txIssuedDate" type="text" class="span6 mytextbox" id="txIssuedDate" value="<?php echo $ctc_issued_date; ?>" style="width: 90px;" readonly>
						</td>
					</tr>
				</table>
				<div align="center">
					<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-small btn-primary">
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
function ask(){
	if(confirm('Do you want to save this information?'))
		return true;
	else
		return false;
}
function askDel(){
if(confirm('Do you want to remove this item?'))
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