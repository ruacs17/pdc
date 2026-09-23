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
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$txtEmpNo='';$txtfName='';$txtmName='';$txtlName='';$txtExtName='';$txtNickName='';$bdate='';$txtBirthPlace='';$selGender='';$selCivilStat='';$txCitizenship='';$cert_id='';
if($eid){
	$q = $db->select('employee','*',array('emp_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$cert_id = $r['cert_id'];
		$txtEmpNo = $r['emp_no'];
		$txtfName = $r['fname'];
		$txtmName = $r['mname'];
		$txtlName = $r['lname'];
		$txtExtName = $r['extname'];
		$txtNickName = $r['nickname'];
	endwhile;
}
$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
#$file = ($fileName) ? $fileName : 'blank-pic.png';
$file = ($fileName && file_exists('../img_emp/'.$fileName) ) ? $fileName : 'blank-pic.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Check List</title>
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
	body{background-color: #FFF;}
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<div align="center">
<table width="90%" border="0" align="center">
	<tr>
		<td>
			<div align="center" style="padding-bottom:30px;"><h2>201 FILE CHECKLIST</h2></div>
			<table width="90%" border="0" style="font-size:12px;">
				<tr>
					<td><div align="right" style="padding-right:20px;"><img width="200" height="200" src="../img_emp/<?php echo $file;?>"></div></td>
					<td>
						<div align="left" style="padding:20px 0px 0px 20px;">
							<table class="table">
								<tr>
									<th width="30%"><div align="left">ID Number</div></th>
									<td><?php echo $txtEmpNo;?></td>
								</tr>
								<tr>
									<th><div align="left">LAST NAME</div></th>
									<td><?php echo $txtlName;?></td>
								</tr>
								<tr>
									<th><div align="left">FIRST NAME</div></th>
									<td><?php echo $txtfName;?></td>
								</tr>
								<tr>
									<th><div align="left">MIDDLE NAME</div></th>
									<td><?php echo $txtmName;?></td>
								</tr>
								<tr>
									<th><div align="left">Suffix (JR, SR, III)</div></th>
									<td><?php echo $txtExtName;?></td>
								</tr>
								<tr>
									<th><div align="left">NICK NAME</div></th>
									<td><?php echo $txtNickName;?></td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
			</table>
			<div class="table-wrapper">
				<table width="100%" align="center" border="0" class="table <?php if(!isset($_SESSION['notif_id'])){echo 'table-bordered';} ?> table-hover" style="font-size: 12px;">
					<thead>
						<tr style="background-color:#ececec">
							<th width="25%"><strong>CHECKLIST</strong></th>
							<th width="20%"><div align="center"><strong>STATUS</strong></div></th>
							<th width="20%"><div align="center"><strong>FILE</strong></div></th>
							<th width="20%"><div align="center"><strong>REMARKS</strong></div></th>
							<th width="10%"><div align="center"><strong>MANAGE</strong></div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$countHours=0;$countSeminar=0;$count=0;
						$q = $db->select('emp_201_reference','*',array(),'ORDER BY e2r_name');
						while($r = $db->fetch_array($q)):
							$rID = 0;
							$refID = $r['e2r_id'];
							$status = $db->getValue('emp_201','e2_status',array('emp_id'=>$eid,'e2r_id'=>$refID));
							$e201_file = $db->getValue('emp_201','e2_file',array('emp_id'=>$eid,'e2r_id'=>$refID));
							$remarks = $db->getValue('emp_201','remarks',array('emp_id'=>$eid,'e2r_id'=>$refID));
						?>
						<tr id="rw<?php echo $refID?>">
							<td><?php echo $r['e2r_name'];?></td>
							<td><div align="center"><?php echo ($status) ? 'Done' : 'Not Done';?></div></td>
							<td>
								<?php if($db->getValue('emp_201_docs','count(*)',array('e2r_id'=>$refID,'emp_id'=>$eid))){ ?>
								<div align="center"><a id="vwpcs<?php echo $count++?>" style="cursor: pointer;" class="thickbox" title="Document View" data-rel="tooltip" onclick="showThis(this.id,'employee_checklist_manage_view.php?e2r_id=<?php echo functions::encode($refID);?>&eid=<?php echo functions::encode($eid);?>','View Document','1')">View Document</a></div>
								<?php } ?>
							</td>
							<td><div align="center"><?php echo $remarks;?></div></td>
							<td><div align="center"><a id="edit<?php echo  $count++?>" class="btn btn-mini btn-warning thickbox" title="Modify Employee Checklist Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_checklist_manage.php?eid=<?php echo functions::encode($eid);?>&refID=<?php echo functions::encode($refID);?>','Employee Checklist Update')"><i class="halflings-icon white pencil"></i></a></div></td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table>
			</div>
		</td>
	</tr>
</table><br><br>
</div>
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
<?php if(isset($_SESSION['notif_id'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id']);} ?>
<!-- end: JavaScript-->
</body>
</html>