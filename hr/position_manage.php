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
$txName='';$txDesc='';$selHead='';$selDept='';$pos_name='';$pos_desc='';$head_id='';$dep_id='';
if($eid){
	$_SESSION['notif_id']=$eid;
	$q = $db->select('dep_position','*',array('dp_id'=>$eid));
	$r = $db->fetch_array($q);
	$pos_name = $r['pos_name'];
	$pos_desc = $r['pos_desc'];
	$head_id = $r['head_id'];
	$dep_id = $r['dep_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Position Manage</title>
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
<?php
if( isset($_POST['btnSave']) ){
	$pos_name = ( isset($_POST['txName']) ) ? strtoupper(trim($_POST['txName'])) : '';
	$pos_desc = ( isset($_POST['txDesc']) ) ? strtoupper(trim($_POST['txDesc'])) : '';
	$head_id = ( isset($_POST['selHead']) ) ? trim($_POST['selHead']) : '';
	$dep_id = ( isset($_POST['selDept']) ) ? trim($_POST['selDept']) : '';
	if($pos_name && $dep_id){
		$arrField = array('pos_name'=>$pos_name,'pos_desc'=>$pos_desc,'dep_id'=>$dep_id);
		if($head_id)
			$arrField = array_merge($arrField,array('head_id'=>$head_id));

		if($eid){
			$_SESSION['notif_id']=$eid;
			$db->update('dep_position',$arrField,array('dp_id'=>$eid));
			if(empty($head_id))
				$db->update('dep_position',array('head_id'=>NULL),array('dp_id'=>$eid));
			$_SESSION['notif_success']='Changes Saved!';
			functions::sendTo(functions::pageName().'?eid='.functions::encode($eid));
			die();
		}
		else{
			if( $db->getValue('dep_position','count(*)',array('pos_name'=>$pos_name,'dep_id'=>$dep_id)) )
				functions::say('Entry already exist!');
			else{
				$ins = $db->insert('dep_position',$arrField);
				$_SESSION['notif_id']=$ins;
				$_SESSION['notif_success']='New Position Added!';
				functions::sendTo(functions::pageName().'?eid='.functions::encode($eid));
				die();
			}
		}
	}
	else{
		functions::say('Please fill up the form properly!');
	}   
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post" onSubmit="return ask()">
				<div align="center" style="padding-bottom: 15px;"><h2>POSITION INFORMATION</h2></div>
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="17%" height="30"><div align="right">Name</div></td>
						<td width="43%"><input type="text" name="txName" id="txName" class="span6" value="<?php echo $pos_name?>" autocomplete='off' required></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Description</div></td>
						<td><textarea name="txDesc" id="txDesc" rows="3" style="width:500px;"><?php echo $pos_desc?></textarea></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Department</div></td>
						<td>
							<select name="selDept" id="selDept" data-rel="chosen" style="width:450px;">
								<option value="">--select--</option>
								<?php 
								$qDept = $db->select('department','*',array(),'ORDER BY dep_name');
								while($rDept = $db->fetch_array($qDept)):?>
								<option value="<?php echo $rDept['dep_id']?>" <?php if($rDept['dep_id']==$dep_id)echo 'selected="selected"';?>><?php echo $rDept['dep_desc'].' ('.$rDept['dep_name'].')'?></option>
								<?php endwhile;?>
							</select>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Reports To</div></td>
						<td>
							<select name="selHead" id="selHead" data-rel="chosen" style="width:650px;">
								<option value="">NONE</option>
								<?php 
								$q = $db->select('dep_position','*',array(),'ORDER BY pos_name');
								while($r = $db->fetch_array($q)):
								$dept = $db->getValue('department','dep_desc',array('dep_id'=>$r['dep_id']));
								$depName = ($dept) ? '('.$dept.')' : '(no department)';
								?>
								<option value="<?php echo $r['dp_id']?>" <?php if($r['dp_id']==$head_id)echo 'selected="selected"';?>><?php echo $r['pos_name']; echo '&nbsp;&nbsp;'.$depName;?></option>
								<?php endwhile;?>
							</select>
						</td>
					</tr>
					<tr>
						<td></td>
						<td><div align="left"><input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small"></div></td>
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
<script type="text/javascript">
function ask(){
	if(confirm('Do you want to save this information?'))
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
</body>
</html>