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
$edtID = (isset($_REQUEST['edtID']) && !empty($_REQUEST['edtID']) ) ? functions::decode($_REQUEST['edtID']) : 0;
$_SESSION['notif_id_list']=$edtID;
$qBusType = $db->query('SELECT DISTINCT establishment_type FROM project_client ORDER BY establishment_type');
$namesBusiness='';
while($rBT=$db->fetch_array($qBusType)):
	$string = preg_replace("/'/",'"',$rBT['establishment_type']);
	$namesBusiness .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesBusiness .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Client Manage</title>
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
	$q = $db->select('project_client','*',array('pc_id'=>$edtID));
	$r = $db->fetch_array($q);
	$pc_name = $r['pc_name'];
	$address = $r['address'];
	$contact_no = $r['contact_no'];
	$establishment_type = $r['establishment_type'];

	if( isset($_POST['btnSave']) ){

		$pc_name = ( isset($_POST['txCName']) && !empty($_POST['txCName']) ) ? trim($_POST['txCName']) : '';
		$address = ( isset($_POST['txtAddress']) && !empty($_POST['txtAddress']) ) ? trim($_POST['txtAddress']) : '';
		$contact_no = ( isset($_POST['txContactNo']) && !empty($_POST['txContactNo']) ) ? trim($_POST['txContactNo']) : '';
		$establishment_type = ( isset($_POST['txEstType']) && !empty($_POST['txEstType']) ) ? trim($_POST['txEstType']) : '';

		if($pc_name){

			if($edtID){//Update
				$allowed_update=1;
				if( $pc_name != $db->getValue('project_client','pc_name',array('pc_id'=>$edtID)) ){
					if( $db->getValue('project_client','count(*)',array('pc_name'=>$pc_name)) ){
						functions::say('Client Name already Exist!');
						$allowed_update=0;
					}
				}
				if($allowed_update==1){
					$db->update('project_client',array('pc_name'=>$pc_name,'address'=>$address,'contact_no'=>$contact_no,'establishment_type'=>$establishment_type),array('pc_id'=>$edtID));
					$_SESSION['notif_success']='Changes Saved!';
					functions::sendTo(functions::pageName().'?edtID='.functions::encode($edtID));
					die();
				}
			}
			else{//Insert
				if( $db->getValue('project_client','count(*)',array('pc_name'=>$pc_name)) ){
					functions::say('Client Name already Exist!');
				}
				else{
					$db->insert('project_client',array('pc_name'=>$pc_name,'address'=>$address,'contact_no'=>$contact_no,'establishment_type'=>$establishment_type));
					$_SESSION['notif_success']='Client Information Added!';
					functions::sendTo(functions::pageName());
					die();
				}
			}
		}
		else
			functions::say('Please fill up the form properly!');
	}
	?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Client Manage Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="20%" height="30"><div align="right">Client Name</div></td>
						<td width="43%"><input type="text" name="txCName" id="txCName" class="span6" style="width:60%" value="<?php echo $pc_name?>" required></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Address</div></td>
						<td><input type="text" name="txtAddress" id="txtAddress" class="span6" style="width:60%" value="<?php echo $address?>" /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Contact Number</div></td>
						<td><input type="text" name="txContactNo" id="txContactNo" class="span6" style="width:60%" value="<?php echo $contact_no?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Type of Establishment</div></td>
						<td><input type="text" class="span6 typeahead" style="width:60%" name="txEstType" id="txEstType" value="<?php echo $establishment_type?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesBusiness;?>]'></td>
					</tr>
				</table>
				<div align="center"><input type="submit" name="btnSave" id="btnSave" value=" SAVE " onClick="return ask()" class="btn btn-primary btn-small"></div>
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
	if(confirm('Do you want to submit this information?'))
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
<!-- end: JavaScript-->
</body>
</html>