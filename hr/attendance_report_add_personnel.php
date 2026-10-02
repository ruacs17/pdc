<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));

$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;

$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$proj_id = $rEatID['proj_id'];
$date_start = $rEatID['date_start'];
$date_end = $rEatID['date_end'];
$worker_type = $rEatID['payroll_type'];
$note = $rEatID['note'];

$attendance_detail = $db->getValue('emp_attendance_detail','count(eat_id)',array('eat_id'=>$eatid));
$count_personnel = $db->getValue('emp_attendance_personnel','count(*)',array('eat_id'=>$eatid));

if( isset($_POST['btnRemoved']) && $eatid ){
	$ArrRemoved = isset($_POST['chkDel']) ? $_POST['chkDel'] : '';
	if(is_array($ArrRemoved)){
		foreach($ArrRemoved as $remID):
			$eas_emp_id = $db->getValue('emp_attendance_personnel','emp_id',array('eap_id'=>$remID));
			$db->delete('emp_attendance_personnel',array('eat_id'=>$eatid,'eap_id'=>$remID));
			$db->delete('emp_attendance_detail',array('emp_id'=>$eas_emp_id,'eat_id'=>$eatid));
			$db->delete('payroll_adjustment',array('emp_id'=>$eas_emp_id,'eat_id'=>$eatid),'AND eatd_id IS NOT NULL');
			$db->delete('payroll_premium',array('emp_id'=>$eas_emp_id,'eat_id'=>$eatid));
		endforeach;
		$_SESSION['notif_warning']='Personnel Removed!';
	}
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
	die();
}

function position($emp_id){
	global $db;
	$countPos=0;$position='';
	$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
	while($rPos = $db->fetch_array($qPos)):
		if($countPos)
			$position .= ' /<br>';
		$position .= $rPos['pos_name'];
		$countPos++;
		endwhile;
		return $position;
}
function work_status($emp_id){
	global $db;
	$qStat = $db->select('emp_work_status','*',array('emp_id'=>$emp_id),'ORDER BY ews_date DESC');
	$rStat = $db->fetch_array($qStat);
	$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : '-----';
	$wrkDate = (isset($rStat['ews_date'])) ? ' ('.functions::datearr($rStat['ews_date']).')' : '';
	$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? ' <i>Project Based</i>' : '';
	return $wrkStat. $projBased . $wrkDate;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Group Select</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
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
	<style type="text/css">
	.tdpadleft{padding-left: 5px;}
	</style>
	<?php
	$delID = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
	if( $eatid && $delID ){
		$eas_emp_id = $db->getValue('emp_attendance_personnel','emp_id',array('eap_id'=>$delID));
		$db->delete('emp_attendance_personnel',array('eap_id'=>$delID));
		$db->delete('emp_attendance_detail',array('emp_id'=>$eas_emp_id,'eat_id'=>$eatid));
		$_SESSION['notif_warning']='Personnel Removed!';
		functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
		die();
	}
	if( isset($_POST['btnAdd']) ){
		$emp_id = ( isset($_POST['selEmp']) && !empty($_POST['selEmp']) ) ? trim($_POST['selEmp']) : '';
		if( $eatid && $emp_id ){
			$arrField = array('emp_id'=>$emp_id,'eat_id'=>$eatid);
			if( $db->getValue('emp_attendance_personnel','count(*)',$arrField)==0 ){
				$db->insert('emp_attendance_personnel',$arrField);
				$_SESSION['notif_success']='Personnel Added!';
				functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
				die();
			}
			else{
				$_SESSION['notif_warning']='Employee already assigned!';
				functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
				die();
			}
		}
	}
	?>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>PERSONNEL</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<?php if($attendance_detail){?><li><a href="attendance_summary_view.php?eatid=<?php echo functions::encode($eatid)?>">Summary</a></li><?php }?>
				<?php if($count_personnel){?><li><a href="attendance_report_add_attlog_option.php?eatid=<?php echo functions::encode($eatid)?>">Upload Att. Log</a></li><?php }?>
				<li class="active"><a href="attendance_report_add_personnel.php?eatid=<?php echo functions::encode($eatid)?>" style="opacity:.9">Personnel</a></li>
				<li><a href="attendance_report_add_charge.php?eatid=<?php echo functions::encode($eatid)?>">Charge To</a></li>
			</ul>
		</div>
		<div class="box-content">
			<table border="0" width="99%">
				<tr>
					<td align="left" width="12%">Project / Department </td>
					<td valign="middle" height="25px"><div align="left" style="font-weight:bold;"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$proj_id)); ?></div></td>
				</tr>
				<tr>
					<td align="left">Period Cover:</td>
					<td valign="middle" height="25px"><div align="left" style="font-weight:bold;"><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end); echo '&nbsp;&nbsp;&nbsp;&nbsp;('.functions::date_diff($date_start,$date_end,$includeDay1=1).') days'; ?></div></td>
				</tr>
				<tr>
					<td height="25">Worker Type </td>
					<td><div align="left" style="font-weight:bold;"><?php if($worker_type=="admin"){echo 'Office Personnel';}elseif($worker_type=="labor"){echo 'Labor Group';}else{echo "Undefined";} ?></div></td>
				</tr>
				<?php if($note){?>
				<tr>
					<td height="25">Note: </td>
					<td><div align="left" style="font-weight:bold;"><?php echo $note; ?></div></td>
				</tr>
				<?php }?>
			</table><br>
			<form class="form-horizontal" id="showform" name="showform" method="post">
				<div align="center">
					<table border="0">
						<tr>
							<td>
								<div align="left" style="padding-top: 2px;">
									<select name="selEmp" id="selEmp" data-rel="chosen" style="width:480px;">
										<option value="">--Select Employee--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>"><?php echo strtoupper($rEU['emp_no'].' - '.$rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
							<td>&nbsp;</td>
							<td>
								<div align="left">
									<input type="submit" name="btnAdd" id="btnAdd" title="Add Individual Personnel" data-rel="tooltip" value=" ADD PERSONNEL " class="btn btn-primary btn-small">
									<a id="imprt" class="btn btn-small btn-success" title="Import Group Personnel" href="attendance_report_add_personnel_import.php?projid=<?php echo functions::encode($proj_id);?>&eatid=<?php echo functions::encode($eatid)?>" data-rel="tooltip">Import Personnel</a>
									<?php if( $count_personnel ){ ?>
									<a id="lnkUpld" href="attendance_report_add_attlog_option.php?eatid=<?php echo functions::encode($eatid)?>" class="btn btn-info btn-small" title="Upload the excel file of attendance log" data-rel="tooltip">Upload Att. Log</a>
									<i id="UpldWrning" style="display:none;">&nbsp;&nbsp;(Personnel Status Unidentified)</i>
									<?php }?>
								</div>
							</td>
						</tr>
					</table><br><br>
				</div>
				<div align="center">
					<table id="tblist" width="90%" align="center" class="tablea table-hover" border="1" style="font-size:12px;">
						<thead>
							<tr style="background-color:#CCC;">
								<td>&nbsp;</td>
								<th width="12%" height="30" class="tdpadleft"><div align="left">ID Number</div></th>
								<th class="tdpadleft"><div align="left">Name</div></th>
								<th class="tdpadleft"><div align="left">Position</div></th>
								<th class="tdpadleft" width="23%"><div align="left">Status</div></th>
								<th width="5%"><input type="checkbox" name="checkAll" id="checkAll" value="all"></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$count=1;
							$cancelUpload=0;
							$qDisp = $db->select('emp_attendance_personnel ead, employee e','*',array('eat_id'=>$eatid),'AND ead.emp_id=e.emp_id ORDER BY lname,fname');
							while($rDisp = $db->fetch_array($qDisp)):
							$rID = $rDisp['eap_id'];
							$workStat=work_status($rDisp['emp_id']);
							if($workStat=='-----')
								$cancelUpload++;
							?>
							<tr>
								<td><div align="center"><?php echo $count++;?></div></td>
								<td height="30" class="tdpadleft"><?php echo $rDisp['emp_no']?></td>
								<td class="tdpadleft"><?php echo $rDisp['lname'].', '.$rDisp['fname']?></td>
								<td class="tdpadleft"><?php echo position($rDisp['emp_id'])?></td>
								<td class="tdpadleft"><?php echo $workStat?>&nbsp;&nbsp;<a id="adcworkstat<?php echo $rID;?>" href="#" class="thickbox" title="Manage Status" onclick="showThis(this.id,'employee_add_work_stat.php?eid=<?php echo functions::encode($rDisp['emp_id'])?>&fromED=t','Work Status')"><i class="halflings-icon pencil"></i></a></td>
								<td>
									<div align="center">
										<input type="checkbox" class="chkDel" name="chkDel[<?php echo $rID; ?>]" id="chkDel[<?php echo $rID; ?>]" value="<?php echo $rID; ?>">
										<a id="del<?php echo $rID;?>" style="display:none;" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Employee" data-rel="tooltip" href="?eatid=<?php echo functions::encode($eatid);?>&delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
									</div>
								</td>
							</tr>
							<?php endwhile;?>
							<tr>
								<td colspan="5">&nbsp;</td>
								<td><div align="center" style="padding:10px;"><input type="submit" class="btn btn-small btn-danger" name="btnRemoved" id="btnRemoved" value="Remove"></div></td>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<style type="text/css">
.rActive{
	background-color: #CCE;
}
.rInActive{
	background-color: #FFF;
}
</style>
<script>
function ask(){
	if(confirm('Do you want to remove this personnel?'))
		return true;
	else
		return false;
}
$(window).ready(function(){
	$('#UpldWrning').hide();
	$("#spinner").fadeOut("slow");
	<?php if($cancelUpload){ ?>
		$('#lnkUpld').hide();
		$('#UpldWrning').show();
	<?php } ?>
	$('#btnRemoved').click(function(){
		return ask();
	});
	$(".chkDel").each(function() {
		if(this.checked==true){
			$(this).parent().parent().parent().attr('class','rActive');
		}
	});
	if ($('.chkDel:checked').length == $('.chkDel').length){
		$('#checkAll').prop('checked',true);
	}
	else {
		$('#checkAll').prop('checked',false);
	}
	$("#checkAll").change(function() {
		if (this.checked) {
			$(".chkDel").each(function() {
				this.checked=true;
				$(this).parent().parent().parent().attr('class','rActive');
			});
		} else {
			$(".chkDel").each(function() {
				this.checked=false;
				$(this).parent().parent().parent().attr('class','');
			});
		}
	});
	$('#tblist tr').click(function(event) {
		if (event.target.type !== 'checkbox') {
			$(':checkbox', this).trigger('click');
		}
	});
	$(".chkDel").click(function () {
		$(this).closest('tr').toggleClass('rActive');
		if ($('.chkDel:checked').length == $('.chkDel').length){
			$('#checkAll').prop('checked',true);
		}
		else {
			$('#checkAll').prop('checked',false);
		}
	});
});
<?php $delMsg = ($attendance_detail) ? "It it will also delete employee's attendance. Do you want to continue?" : "Do you want to remove this personnel?"?>
function delt(){if(confirm("<?php echo $delMsg?>"))return true; else return false;}
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