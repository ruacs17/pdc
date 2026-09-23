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
$txDepName='';$txDepDesc='';$txDepType ='';
if($eid)
	$_SESSION['notif_id']=$eid;
if($eid){
	$q = $db->select('department','*',array('dep_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$txDepName = $r['dep_name'];
		$txDepDesc = $r['dep_desc'];
		$txDepType = $r['dep_type'];
	endwhile;
}
if( isset($_POST['btnSave']) ){
	$dep_name = ( isset($_POST['txDepName']) ) ? strtoupper(trim($_POST['txDepName'])) : '';
	$dep_desc = ( isset($_POST['txDepDesc']) ) ? strtoupper(trim($_POST['txDepDesc'])) : '';
	$dep_type = ( isset($_POST['txDepType']) && !empty($_POST['txDepType']) ) ? trim($_POST['txDepType']) : '';

	if($dep_name){
		$arrField = array('dep_name'=>$dep_name,'dep_desc'=>$dep_desc,'dep_type'=>$dep_type);
		if($eid){
			$_SESSION['notif_id']=$eid;
			$db->update('department',$arrField,array('dep_id'=>$eid));
			$_SESSION['notif_success']='Changes Saved!';
		}
		else{
			$ins = $db->insert('department',$arrField);
			if($ins){
				$_SESSION['notif_id']=$eid;
				$_SESSION['notif_success']='New Department Added!';
			}
		}
	}
	else{
		functions::say('Please fill up the form properly!');
	}
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Department Manage</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post" onSubmit="return ask()">
				<div align="center" style="padding-bottom: 15px;"><h2>DEPARTMENT INFORMATION</h2></div>
				<div align="center">
					<div style="width:70%;">
						<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
							<tr>
								<td width="17%" height="30"><div align="right">Short Name / Abbrevation</div></td>
								<td width="43%"><input type="text" name="txDepName" id="txDepName" class="span6" value="<?php echo $txDepName?>" style="width:500px;" required></td>
							</tr>
							<tr>
								<td height="30"><div align="right">Description / Complete name</div></td>
								<td><input type="text" name="txDepDesc" id="txDepDesc" class="span6" value="<?php echo $txDepDesc?>" style="width:500px;" /></td>
							</tr>
							<tr style="display:none;">
								<td height="30"><div align="right">Type</div></td>
								<td>
									<select name="txDepType" id="txDepType">
										<option value="">--select--</option>
										<option value="DIVISION" <?php if($txDepType=='DIVISION'){echo 'selected="selected"';} ?>>DIVISION</option>
										<option value="SECTION" <?php if($txDepType=='SECTION'){echo 'selected="selected"';} ?>>SECTION</option>
									</select>
								</td>
							</tr>
							<tr>
								<td></td>
								<td><div align="left"><input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small"></div></td>
							</tr>
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