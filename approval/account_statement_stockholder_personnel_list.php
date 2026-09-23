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

if( isset($_POST['btnSearch']) ){
	$_SESSION['sh_stype']=( isset($_POST['selSType']) && !empty($_POST['selSType']) ) ? $_POST['selSType'] : '';
	functions::sendTo(functions::pageName());
	die();
}

$rm_sp_id = ( isset($_REQUEST['rm']) && !empty($_REQUEST['rm']) ) ? functions::decode($_REQUEST['rm']) : '';
if($rm_sp_id){
	$db->delete('stockshare_personnel',array('sp_id'=>$rm_sp_id));
	$_SESSION['notif_warning']='Personnel Removed from Stockholder List!';
	functions::sendTo(functions::pageName());
	die();
}
$stocktype = ( isset($_SESSION['sh_stype']) && !empty($_SESSION['sh_stype']) ) ? $_SESSION['sh_stype'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>STOCKHOLDER LIST</title>
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
	<link href="../css/select2.min.css" rel="stylesheet">
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
$insertFailed=0; $emp_id='';$sp_type='';
if( isset($_POST['btnAdd']) ){
	$emp_id = ( isset($_POST['txStockholder']) && !empty($_POST['txStockholder']) ) ? $_POST['txStockholder'] : '';
	$sp_type = ( isset($_POST['selSTypeAdd']) && !empty($_POST['selSTypeAdd']) ) ? $_POST['selSTypeAdd'] : '';
	if( $emp_id && $sp_type){

		if( $db->getValue('stockshare_personnel','count(*)',array('emp_id'=>$emp_id,'sp_type'=>$sp_type))==0 ){
			$ins = $db->insert('stockshare_personnel',array('emp_id'=>$emp_id,'sp_type'=>$sp_type));
			if($ins){
				$_SESSION['notif_success']='New Stockholder Added!';
				functions::sendTo(functions::pageName());
				die();				
			}
			else
				functions::say('Please fill up the form properly!');

		}
		else{
			$insertFailed=1;
			functions::say('Stockholder already exist!');
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>STOCKHOLDER LIST</h2>
		</div>
		<div class="box-content">
			<div align="left">
				<a id="mrPrint" href="account_statement_stockholder_print.php?" class="btn btn-info btn-xs" style="display:none;"><i class="halflings-icon white print"></i></a>
				<a id="btndisplayADD" href="#" class="btn btn-info btn-small" title="Add New Stockholder"><i class="halflings-icon white plus"></i> Add Stockholder</a>
			</div>
			<form class="form-horizontal" method="post">
				<div id="shadd"><br>
					<select name="txStockholder" id="txStockholder" style="width:300px;text-align:left;" required>
						<option value="">-- All Stockholders --</option>
						<?php $qEU = $db->query('SELECT * FROM employee ORDER BY lname');
						while($rEU = $db->fetch_array($qEU)):
						?>
						<option value="<?php echo $rEU['emp_id']?>" <?php if($emp_id==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
						<?php endwhile;?>
					</select>
					<select name="selSTypeAdd" id="selSTypeAdd" required>
						<option value="">--select Type--</option>
						<option value="common" <?php if($sp_type=='common')echo 'selected="selected"';?>>COMMON</option>
						<option value="preferred" <?php if($sp_type=='preferred')echo 'selected="selected"';?>>PREFERRED</option>
					</select>
					<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary btn-small">
					<a id="btndisplayCancel" href="#" class="btn btn-warning btn-small" title="Add New Stockholder">Cancel</a>
				</div>
			</form>
			<form class="form-horizontal" method="post">
				<div align="center"><br>&nbsp;&nbsp;
					<select name="selSType" id="selSType">
						<option value="">All Type</option>
						<option value="common" <?php if($stocktype=='common')echo 'selected="selected"';?>>COMMON</option>
						<option value="preferred" <?php if($stocktype=='preferred')echo 'selected="selected"';?>>PREFERRED</option>
					</select>
					<input type="submit" name="btnSearch" id="btnSearch" value="View" class="btn btn-primary btn-small">
				</div>
				<br>
				<div align="center">
				<div style="width:45%">
				<table align="center" border="0" class="table table-bordered table-striped table-hover" style="font-size:12px;" >
					<thead>
						<tr style="background-color:#CCC;">
							<th width="70%"><div align="center">Name</div></th>
							<th width="20%"><div align="center">Type</div></th>
							<th width="10%"><div align="center">Remove</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$countRec=1;
						if($stocktype)
							$q = $db->query('SELECT * FROM stockshare_personnel sp, employee emp WHERE sp.emp_id=emp.emp_id AND sp_type="'.$db->clean($stocktype).'" ORDER BY lname, fname');
						else
							$q = $db->query('SELECT * FROM stockshare_personnel sp, employee emp WHERE sp.emp_id=emp.emp_id ORDER BY lname, fname');
						while($r = $db->fetch_array($q)):
						?>
						<tr>
							<td><div align="left"><?php echo $countRec++.'. '.$r['lname'].', '.$r['fname']; ?></div></td>
							<td><div align="center"><?php echo $r['sp_type'] ?></div></td>
							<td><div align="center"><a href="?rm=<?php echo functions::encode($r['sp_id']) ?>" onClick="if(confirm('Do you want to remove this stockholder?')){return true;}else{return false;}" class="btn btn-danger btn-mini" title="Remove this stockholder"><i class="halflings-icon white trash"></a></div></td>
						</tr>
						<?php endwhile; ?>
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
<script src="../js/select2.js"></script>
<script type="text/javascript">$("#txStockholder").select2(); </script>
<script type="text/javascript">
$(document).ready(function(){
	<?php if($insertFailed){ ?>
	$('#shadd').show();
	$('#btndisplayCancel').show();
	$('#btndisplayADD').hide();
	<?php }else{ ?>
	$('#shadd').hide();
	$('#btndisplayCancel').hide();
	<?php } ?>
	$('#btndisplayADD').click(function(){
		$('#shadd').fadeIn();
		$('#btndisplayADD').hide();
		$('#btndisplayCancel').fadeIn();
	});
	$('#btndisplayCancel').click(function(){
		$('#shadd').fadeOut();
		$('#btndisplayCancel').hide();
		$('#btndisplayADD').fadeIn();
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