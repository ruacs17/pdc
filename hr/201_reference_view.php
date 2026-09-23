<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$db->utf8();
#$logs = new Logs();
#$logs->save('visit');
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$emp_id = (isset($_REQUEST['emp']) && !empty($_REQUEST['emp']) ) ? functions::decode($_REQUEST['emp']) : '';
$txName='';$txDepDesc='';

if($eid){
	$_SESSION['notif_id']=$eid;
}
if( isset($_POST['btnRemove']) ){
	$chk = isset($_POST['chkDel']) ? $_POST['chkDel'] : '';
	$countDel=0;
	if(is_array($chk)){
		#print_r($chk);
		foreach($chk as $rid):
			$countDel++;
			$rm_e2_id = functions::decode($rid);
			$qpc = $db->select('emp_201_docs docs, doc_img img','*',array('e2_id'=>$rm_e2_id),'AND docs.di_id=img.di_id');
			while( $rpc = $db->fetch_array($qpc)):
				$fileName = $rpc['doc_name'];
				if($fileName && file_exists('../img_emp/'.$fileName) ){
					unlink('../img_emp/'.$fileName);
				}
				$db->delete('doc_img',array('di_id'=>$rpc['di_id']));
			endwhile;
			$db->delete('emp_201',array('e2_id'=>$rm_e2_id));
		endforeach;
	}
	$_SESSION['notif_success']=$countDel.' Document(s) Removed!';
	functions::sendTo('?eid='.functions::encode($eid));
	die();
}
if( isset($_REQUEST['rm']) && !empty($_REQUEST['rm']) && $eid && $emp_id ){
	$rm_e2_id = functions::decode($_REQUEST['rm']);
	$qpc = $db->select('emp_201_docs docs, doc_img img','*',array('e2_id'=>$rm_e2_id,'emp_id'=>$emp_id),'AND docs.di_id=img.di_id');
	while( $rpc = $db->fetch_array($qpc)):
		$fileName = $rpc['doc_name'];
		if($fileName && file_exists('../img_emp/'.$fileName) ){
			unlink('../img_emp/'.$fileName);
		}
		$db->delete('doc_img',array('di_id'=>$rpc['di_id']));
	endwhile;
	$db->delete('emp_201',array('e2_id'=>$rm_e2_id,'emp_id'=>$emp_id));
	$_SESSION['notif_success']='File Removed!';
	functions::sendTo('?eid='.functions::encode($eid));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>201 Checklist Add</title>
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post" >
				<div align="center" style="padding-bottom: 15px;"><h2>201 (<?php echo $db->getValue('emp_201_reference','e2r_name',array('e2r_id'=>$eid)) ?>)</h2></div>
				<div align="center">
					<div style="width:700px;">
						<table id="tblist" border="0" class="table table-bordered table-hover" style="background-color:#E4E1E1">
							<thead>
								<tr style="background-color:#CCC;">
									<th width="60%" height="30"><div align="left">Name</div></th>
									<th width="25%"><div align="center">File</div></th>
									<th width="10%"><div align="center"><input type="checkbox" name="checkAll" id="checkAll" value="all"></div></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$countPic=0;
								$q = $db->select('employee emp, emp_201 emp2','*',array('emp2.e2r_id'=>$eid),'AND emp.emp_id=emp2.emp_id ORDER BY lname,fname');
								while($r = $db->fetch_array($q)):
									$e201_file = $r['e2_file'];
									$countPic++;
								?>
								<tr>
									<td><div align="left"><?php echo $r['lname'].', '.$r['fname'] ?></div></td>
									<td>
										<?php if($db->getValue('emp_201_docs','count(*)',array('e2r_id'=>$r['e2r_id'],'emp_id'=>$r['emp_id']))){ ?>
										<div align="center"><a id="vwpcs<?php echo $count++?>" style="cursor: pointer;" class="thickbox" title="Document View" data-rel="tooltip" onclick="showThis(this.id,'employee_checklist_manage_view.php?e2r_id=<?php echo functions::encode($r['e2r_id']);?>&eid=<?php echo functions::encode($r['emp_id']);?>','View Document','1')">View Document</a></div>
										<?php } ?>
									</td>
									<td><div align="center"><input type="checkbox" class="chkprint" name="chkDel[]" value="<?php echo functions::encode($r['e2_id'])?>"><a href="?eid=<?php echo functions::encode($eid)?>&rm=<?php echo functions::encode($r['e2_id'])?>&emp=<?php echo functions::encode($r['emp_id'])?>" class="btn btn-mini btn-danger" title="Remove Item" data-rel="tooltip" onClick="return ask()" style="display:none;"><i class="halflings-icon white trash"></i></a></div></td>
								</tr>
								<?php endwhile; ?>
								<tr>
									<td colspan="2">&nbsp;</td>
									<td><?php if($countPic){ ?><div align="center"><input type="submit" name="btnRemove" value="Remove" class="btn btn-danger btn-mini" onClick="return ask()"></div><?php } ?></td>
								</tr>
							</tbody>

						</table>
					</div>
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
<script type="text/javascript">
function ask(){
	if(confirm('Do you want to remove this data?'))
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
<!-- end: JavaScript-->
<style type="text/css">
.rActive{
	background-color: #CCC;
}
.rInActive{
	background-color: #FFF;
}
</style>
<script type="text/javascript">
$(document).ready(function(){
	$(".chkprint").each(function() {
		if(this.checked==true){
			$(this).parent().parent().parent().attr('class','rActive');
		}
	});
	if ($('.chkprint:checked').length == $('.chkprint').length){
		$('#checkAll').prop('checked',true);
	}
	else {
		$('#checkAll').prop('checked',false);
	}
	$("#checkAll").change(function() {
		if (this.checked) {
			$(".chkprint").each(function() {
				this.checked=true;
				$(this).parent().parent().parent().attr('class','rActive');
			});
		} else {
			$(".chkprint").each(function() {
				this.checked=false;
				$(this).parent().parent().parent().attr('class','');
			});
		}
	});
	$('#tblist tr').click(function(event) {
		if (event.target.type !== 'checkbox') {
			$(':checkbox', this).trigger('click');
			//$(this).closest('tr').toggleClass('rActive');
		}
	});
	$(".chkprint").click(function () {
		//$(this).closest('tr').removeClass('rInActive');
		$(this).closest('tr').toggleClass('rActive');
		if ($('.chkprint:checked').length == $('.chkprint').length){
			$('#checkAll').prop('checked',true);
		}
		else {
			$('#checkAll').prop('checked',false);
		}
	});
});
</script>
</body>
</html>