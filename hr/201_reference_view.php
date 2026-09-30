<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));

$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$emp_id = (isset($_REQUEST['emp']) && !empty($_REQUEST['emp']) ) ? functions::decode($_REQUEST['emp']) : '';
$searchVal = (isset($_POST['txSearch'])) ? trim($_POST['txSearch']) : '';

if($eid){
	$_SESSION['notif_id']=$eid;
}
if( isset($_POST['btnRemove']) ){
	$chk = isset($_POST['chkDel']) ? $_POST['chkDel'] : '';
	$countDel=0;
	if(is_array($chk)){
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

	<style>
		:root {
			--panel-bg: #ffffff;
			--border-subtle: #e7e0d8;
			--text-primary: #2c1d11;
			--text-muted: #78695c;
			--theme-brown-header: linear-gradient(135deg, #4a2c1d 0%, #2b180d 100%);
		}

		/* Maximize Entire Width of Page */
		.page-full-wrapper {
			width: 100% !important;
			max-width: 100% !important;
			padding: 15px;
			box-sizing: border-box;
		}

		.card-panel {
			width: 100%;
			background: var(--panel-bg);
			border-radius: 16px;
			border: 1px solid var(--border-subtle);
			box-shadow: 0 10px 25px -5px rgba(61, 35, 20, 0.05);
			overflow: hidden;
			box-sizing: border-box;
			margin-bottom: 25px;
		}

		/* Target Custom Card Header */
		.card-header-custom {
			background: var(--theme-brown-header);
			padding: 18px 25px;
			color: #ffffff;
			display: flex;
			align-items: center;
			justify-content: space-between;
		}

		.card-header-custom h2 {
			margin: 0;
			font-size: 18px;
			font-weight: 700;
			display: flex;
			align-items: center;
			gap: 10px;
			color: #ffffff;
			text-transform: none;
		}

		.card-body-custom {
			padding: 25px;
			width: 100%;
			box-sizing: border-box;
		}

		/* Search Bar Styling */
		.emp-search-bar {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 8px;
			padding: 14px 18px;
			margin-bottom: 20px;
			display: flex;
			justify-content: center;
			align-items: center;
			gap: 10px;
		}

		/* Enhanced Modern Table Design */
		.table-wrapper-custom {
			overflow-x: auto;
			border: 1px solid #e2e8f0;
			border-radius: 10px;
			background: #ffffff;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
		}

		.table-modern {
			width: 100%;
			border-collapse: collapse;
			text-align: left;
			margin-bottom: 0 !important;
		}

		.table-modern th {
			background: #f8fafc;
			color: #475569;
			font-size: 11px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.6px;
			padding: 14px 20px;
			border-bottom: 2px solid #e2e8f0;
		}

		.table-modern td {
			padding: 14px 20px;
			border-bottom: 1px solid #f1f5f9;
			vertical-align: middle;
			transition: background-color 0.2s ease;
		}

		.table-modern tr:last-child td {
			border-bottom: none;
		}

		.table-modern tr:hover td {
			background-color: #faf6f0;
		}

		.checklist-name-cell {
			font-size: 14px;
			font-weight: 600;
			color: #2c1d11;
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.checklist-icon-bullet {
			width: 8px;
			height: 8px;
			background-color: #7c401e;
			border-radius: 50%;
			flex-shrink: 0;
		}

		.rActive {
			background-color: #fef3c7 !important;
		}
		.rInActive {
			background-color: #FFF !important;
		}
	</style>
</head>
<body>
<!-- body content: start here-->
<div class="page-full-wrapper">
	<div class="card-panel">
		<!-- Updated Custom Card Header -->
		<div class="card-header-custom">
			<h2><i class="halflings-icon white list"></i> 201 (<?php echo $db->getValue('emp_201_reference','e2r_name',array('e2r_id'=>$eid)) ?>)</h2>
		</div>
		<div class="card-body-custom">
			<form class="form-horizontal" method="post">
				<div align="center">
					<div style="width:100%; max-width:850px;">
						<!-- Search Box Toolbar -->
						<div class="emp-search-bar">
							<label style="margin:0; font-weight:600; color:#475569;">Search Employee:</label>
							<input type="text" name="txSearch" id="txSearch" value="<?php echo htmlspecialchars($searchVal); ?>" placeholder="Enter name..." style="margin-bottom:0; width:280px; height: 30px;">
							<button type="submit" class="btn btn-primary btn-small" name="btnSearch" id="btnSearch"><i class="icon-search icon-white"></i> Search</button>
							<a href="?eid=<?php echo functions::encode($eid); ?>" class="btn btn-default btn-small" style="padding: 5px 12px; background: #e2e8f0; color: #334155; text-decoration: none; border-radius: 3px;">Clear</a>
						</div>

						<div class="table-wrapper-custom">
							<table id="tblist" class="table-modern">
								<thead>
									<tr>
										<th width="65%" height="30"><div align="left">Name</div></th>
										<th width="25%"><div align="center">File</div></th>
										<th width="10%"><div align="center"><input type="checkbox" name="checkAll" id="checkAll" value="all"></div></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$countPic=0;
									// $whereClause = array('emp2.e2r_id' => $eid);
									// $extraQuery = 'AND emp.emp_id=emp2.emp_id';
									// if(!empty($searchVal)){
									// 	$extraQuery .= " AND (emp.lname LIKE '%".$db->clean($searchVal)."%' OR emp.fname LIKE '%".$db->clean($searchVal)."%' OR concat(emp.lname, ', ', emp.fname) LIKE '%".$db->clean($searchVal)."%')";
									// }
									// $extraQuery .= ' ORDER BY lname,fname';

									#$q = $db->select('employee emp, emp_201 emp2','*',$whereClause,$extraQuery);
									if(!empty($searchVal)){
										$q = $db->prepareQ('SELECT * FROM employee emp, emp_201 emp2 WHERE emp.emp_id=emp2.emp_id AND emp2.e2r_id=? AND (emp.lname LIKE ? OR emp.fname LIKE ? OR concat(emp.lname," ",emp.fname) LIKE ? ) ORDER BY lname,fname',array($eid,'%'.$searchVal.'%','%'.$searchVal.'%','%'.$searchVal.'%'));
									}
									else
										$q = $db->prepareQ('SELECT * FROM employee emp, emp_201 emp2 WHERE emp.emp_id=emp2.emp_id AND emp2.e2r_id=? ORDER BY lname,fname',array($eid));
									
									while($r = $db->fetch_array($q)):
										$e201_file = $r['e2_file'];
										$countPic++;
									?>
									<tr>
										<td>
											<div class="checklist-name-cell">
												<span class="checklist-icon-bullet"></span>
												<?php echo $r['lname'].', '.$r['fname'] ?>
											</div>
										</td>
										<td>
											<?php if($db->getValue('emp_201_docs','count(*)',array('e2r_id'=>$r['e2r_id'],'emp_id'=>$r['emp_id']))){ ?>
											<div align="center"><a id="vwpcs<?php echo $count++?>" style="cursor: pointer; color: #0284c7; font-weight: 600; text-decoration: underline;" class="thickbox" title="Document View" data-rel="tooltip" onclick="showThis(this.id,'employee_checklist_manage_view.php?e2r_id=<?php echo functions::encode($r['e2r_id']);?>&eid=<?php echo functions::encode($r['emp_id']);?>','View Document','1')">View Document</a></div>
											<?php } else { echo '<div align="center" style="color: #94a3b8; font-style:italic;">No Document</div>'; } ?>
										</td>
										<td><div align="center"><input type="checkbox" class="chkprint" name="chkDel[]" value="<?php echo functions::encode($r['e2_id'])?>"><a href="?eid=<?php echo functions::encode($eid)?>&rm=<?php echo functions::encode($r['e2_id'])?>&emp=<?php echo functions::encode($r['emp_id'])?>" class="btn btn-mini btn-danger" title="Remove Item" data-rel="tooltip" onClick="return ask()" style="display:none;"><i class="halflings-icon white trash"></i></a></div></td>
									</tr>
									<?php endwhile; ?>
									<?php if($countPic == 0): ?>
									<tr>
										<td colspan="3" style="text-align: center; color: #64748b; font-style: italic; padding: 30px;">--- No Employees Found ---</td>
									</tr>
									<?php endif; ?>
									<tr>
										<td colspan="2" style="background: #f8fafc;">&nbsp;</td>
										<td style="background: #f8fafc; text-align: center;"><?php if($countPic){ ?><input type="submit" name="btnRemove" value="Remove" class="btn btn-danger btn-mini" onClick="return ask()"><?php } ?></td>
									</tr>
								</tbody>
							</table>
						</div>
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
		}
	});
	$(".chkprint").click(function () {
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