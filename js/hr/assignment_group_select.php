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
#$fn = (isset($_REQUEST['fn']) && !empty($_REQUEST['fn']) ) ? functions::decode($_REQUEST['fn']).'.xlsx' : 0;
$eas_id = (isset($_REQUEST['easid']) && !empty($_REQUEST['easid']) ) ? functions::decode($_REQUEST['easid']) : 0;
$proj_id = $db->getValue('emp_assignment','proj_id',array('eas_id'=>$eas_id));
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$proj_id));
$group_name = $db->getValue('emp_assignment','eas_name',array('eas_id'=>$eas_id));

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
	$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? ' <strong><i>Project Based</i></strong>' : '';
	return $wrkStat. $projBased . $wrkDate;
}
if( isset($_POST['btnRemoved']) && $eas_id ){
	$ArrRemoved = isset($_POST['chkDel']) ? $_POST['chkDel'] : '';
	if(is_array($ArrRemoved)){
		foreach($ArrRemoved as $remID):
			$db->delete('emp_assign_detail',array('ead_id'=>$remID,'eas_id'=>$eas_id));
		endforeach;
		$_SESSION['notif_warning']='Personnel Removed!';
	}
	functions::sendTo(functions::pageName().'?easid='.functions::encode($eas_id));
	die();
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
if( $eas_id && $delID ){
	$db->delete('emp_assign_detail',array('ead_id'=>$delID));
	functions::sendTo(functions::pageName().'?easid='.functions::encode($eas_id));
	die();
}
if( isset($_POST['btnAdd']) ){
	$emp_id = ( isset($_POST['selEmp']) && !empty($_POST['selEmp']) ) ? trim($_POST['selEmp']) : '';
	if( $eas_id && $emp_id ){
		$arrField = array('emp_id'=>$emp_id,'eas_id'=>$eas_id);
		if( $db->getValue('emp_assign_detail','count(*)',$arrField)==0 ){
			$db->insert('emp_assign_detail',$arrField);
			functions::sendTo(functions::pageName().'?easid='.functions::encode($eas_id));
			die();
		}
		else{
			functions::say('Employee already assigned!');
			functions::sendTo(functions::pageName().'?easid='.functions::encode($eas_id));
			die();
		}
	}
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
			<ul class="nav tab-menu nav-tabs">
				<li class="active"><a href="assignment_group_select.php?easid=<?php echo functions::encode($eas_id)?>" style="opacity:.9">Select Name</a></li>
				<li><a href="assignment_manage.php?easid=<?php echo functions::encode($eas_id)?>">Manage Assignment</a></li>
			</ul>
		</div>
		<div class="box-content">
			<div align="left">Project / Department: <strong><?php echo $proj_name;?></strong></div>
			<?php if($group_name){?><div align="left">Group Name: <strong><?php echo $group_name;?></strong></div><?php }?><br>
			<form class="form-horizontal" id="showform" name="showform" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>GROUP ASSIGNMENT MEMBER</h2></div>
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
							<td><div align="left"><input type="submit" name="btnAdd" id="btnAdd" value=" ADD MEMBER " class="btn btn-primary btn-small"></div></td>
						</tr>
					</table><br><br>
				</div>
				<div align="center">
					<table id="tblist" width="90%" align="center" class="tablea table-hover" border="1" style="font-size:12px;">
						<thead>
							<tr style="background-color:#CCC;">
								<th>&nbsp;</th>
								<th height="30" class="tdpadleft" width="13%"><div align="left">ID Number</div></th>
								<th class="tdpadleft"><div align="left">Name</div></th>
								<th class="tdpadleft"><div align="left">Position</div></th>
								<th class="tdpadleft" width="20%"><div align="left">Status</div></th>
								<th width="5%"><input type="checkbox" name="checkAll" id="checkAll" value="all"></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$qDisp = $db->select('emp_assign_detail ead, employee e','*',array('eas_id'=>$eas_id),'AND ead.emp_id=e.emp_id ORDER BY lname');
							$count=1;
							while($rDisp = $db->fetch_array($qDisp)):
								$rID = $rDisp['ead_id'];
							?>
							<tr>
								<td><div align="center"><?php echo $count++;?></div></td>
								<td height="30" class="tdpadleft"><?php echo $rDisp['emp_no']?></td>
								<td class="tdpadleft"><?php echo $rDisp['lname'].', '.$rDisp['fname']?></td>
								<td class="tdpadleft"><?php echo position($rDisp['emp_id'])?></td>
								<td class="tdpadleft"><?php echo work_status($rDisp['emp_id'])?>&nbsp;&nbsp;<a id="adcworkstat<?php echo $rID;?>" href="#" class="thickbox" title="Manage Status" onclick="showThis(this.id,'employee_add_work_stat.php?eid=<?php echo functions::encode($rDisp['emp_id'])?>&fromED=t','Work Status')"><i class="halflings-icon pencil"></i></a></td>
								<td>
									<div align="center">
										<input type="checkbox" class="chkDel" name="chkDel[<?php echo $rID; ?>]" id="chkDel[<?php echo $rID; ?>]" value="<?php echo $rID; ?>">
										<a id="del<?php echo $rID;?>" style="display:none;" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Employee" data-rel="tooltip" href="?easid=<?php echo functions::encode($eas_id);?>&delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
									</div>
								</td>
							</tr>
							<?php endwhile;?>
							<tr>
								<td colspan="5">&nbsp;</td>
								<td><div align="center" style="padding:10px;"><input type="submit" class="btn btn-small btn-danger" name="btnRemoved" id="btnRemoved" value="Removed"></div></td>
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
function delt(){if(confirm('Do you want to remove this Personnel?'))return true; else return false;}
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