<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
  
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');

$ao_id = (isset($_REQUEST['ao']) && !empty($_REQUEST['ao']) ) ? functions::decode($_REQUEST['ao']) : 0;
$aod_id = (isset($_REQUEST['aod']) && !empty($_REQUEST['aod']) ) ? functions::decode($_REQUEST['aod']) : 0;

$project_id = $db->getValue('attendance_overtime','proj_id',array('ao_id'=>$ao_id));

$emp_id=''; $projected_hours='';$projected_output='';$actual_output='';$actual_start_date='';$actual_start_time='';$actual_end_date='';$actual_end_time='';$total_mins='';
$ao_date = $db->getValue('attendance_overtime','ao_date',array('ao_id'=>$ao_id));
$actual_start_date =$ao_date;
$actual_end_date = $ao_date;

$editTrue=0;
$itmEdt='';
$submitButtonName='btnAdd';
$item='';$quantity='';$cost='';$unit='';$discount='';$brand=''; $location='';
if( isset($_REQUEST['itmEdt']) && !empty($_REQUEST['itmEdt']) ){
	$submitButtonName='btnSave';
	$itmEdt = functions::decode($_REQUEST['itmEdt']);
	$editTrue = $db->getValue('attendance_overtime_detail','count(*)',array('aod_id'=>$itmEdt));
	$qvedt = $db->select('attendance_overtime_detail','*',array('aod_id'=>$itmEdt));
	$rvedt = $db->fetch_array($qvedt);
	$emp_id = $rvedt['emp_id'];
	$projected_hours = $rvedt['projected_hours'];
	$projected_output = $rvedt['projected_output'];
	$actual_output = $rvedt['actual_output'];
	$actual_start_date = $rvedt['actual_start_date'];
	$actual_start_time = $rvedt['actual_start_time'];
	$actual_end_date = $rvedt['actual_end_date'];
	$actual_end_time = $rvedt['actual_end_time'];
	$ot_min = functions::min_diff($actual_start_time,$actual_start_date,$actual_end_time,$actual_end_date);
	$db->update('emp_attendance_detail',array('ot_in'=>$actual_start_time,'ot_out'=>$actual_end_time,'ot_min'=>$ot_min),array('emp_id'=>$emp_id,'eat_date'=>$actual_start_date));
}
$qProjOut = $db->query('SELECT DISTINCT projected_output as "itm" FROM attendance_overtime_detail ORDER BY projected_output');
$namesProjOut='';
while($rProjOut=$db->fetch_array($qProjOut)):
	$string = preg_replace("/'/",'"',$rProjOut['itm']);
	$namesProjOut .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesProjOut .= '"--"';
$qActOut = $db->query('SELECT DISTINCT actual_output as "itm" FROM attendance_overtime_detail ORDER BY actual_output');
$namesActOut='';
while($rActOut=$db->fetch_array($qActOut)):
	$string = preg_replace("/'/",'"',$rActOut['itm']);
	$namesActOut .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesActOut .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Overtime Attachment</title>
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
<?php
if( isset($_POST['btnSave']) && $ao_id ){

	if( !empty($_FILES['imageBefore']) && $_FILES['imageBefore']['error'] == 0 ){

		$uploaddir = '../img_material/';
		// Process image with GD library
		$verifyimg = getimagesize($_FILES['imageBefore']['tmp_name']);
		// Make sure the MIME type is an image
		$pattern = "#^(image/)[^\s\n<]+$#i";

		if( !preg_match($pattern, $verifyimg['mime']) ){
			functions::say("Only image files are allowed!");
		}
		else{
			if( $imgBeforeID = $db->getValue('attendance_overtime','img_before',array('ao_id'=>$ao_id)) ){
				$imgBeforeName = $uploaddir.$db->getValue('doc_img','doc_name',array('di_id'=>$imgBeforeID));
				if(file_exists($imgBeforeName))
					unlink($imgBeforeName);
			}
			else{
				// Rename both the image and the extension
				$imgBeforeID = $db->insert('doc_img',array('doc_name'=>''));
				$imgBeforeName = $uploaddir.$imgBeforeID.mt_rand().'.jpg';
			}
			// Upload the file to a secure directory with the new name and extension
			if (move_uploaded_file($_FILES['imageBefore']['tmp_name'], $imgBeforeName)) {
				$db->update('doc_img',array('doc_name'=>basename($imgBeforeName),'doc_org_name'=>basename($_FILES['imageBefore']['name']),'mime_type'=>$_FILES['imageBefore']['type']),array('di_id'=>$imgBeforeID));
				$db->update('attendance_overtime',array('img_before'=>$imgBeforeID),array('ao_id'=>$ao_id));
				echo $db->last_query;
			}
		}
	}

	if( !empty($_FILES['imageAfter']) && $_FILES['imageAfter']['error'] == 0 ) {

		$uploaddir = '../img_material/';
		// Process image with GD library
		$verifyimg = getimagesize($_FILES['imageAfter']['tmp_name']);
		// Make sure the MIME type is an image
		$pattern = "#^(image/)[^\s\n<]+$#i";

		if( !preg_match($pattern, $verifyimg['mime']) ){
			functions::say("Only image files are allowed!");
		}
		else{
			if( $imgAfterID = $db->getValue('attendance_overtime','img_after',array('ao_id'=>$ao_id)) ){
				$imgAfterName = $uploaddir.$db->getValue('doc_img','doc_name',array('di_id'=>$imgAfterID));
				if(file_exists($imgAfterName))
					unlink($imgAfterName);
			}
			else{
				// Rename both the image and the extension
				$imgAfterID = $db->insert('doc_img',array('doc_name'=>''));
				$imgAfterName = $uploaddir.$imgAfterID.mt_rand().'.jpg';
			}
			// Upload the file to a secure directory with the new name and extension
			if (move_uploaded_file($_FILES['imageAfter']['tmp_name'], $imgAfterName)) {
				$db->update('doc_img',array('doc_name'=>basename($imgAfterName),'doc_org_name'=>basename($_FILES['imageAfter']['name']),'mime_type'=>$_FILES['imageAfter']['type']),array('di_id'=>$imgAfterID));
				$db->update('attendance_overtime',array('img_after'=>$imgAfterID),array('ao_id'=>$ao_id));
			}
		}
	}
	functions::sendTo($_SERVER['PHP_SELF'].'?ao='.functions::encode($ao_id));
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Overtime Attachment</h2>
		</div>
		<div class="box-content">
			<form method="post" enctype="multipart/form-data">
				<div align="left"><br>
					<div>Project / Department:
						<strong>
							<?php
							if($project_id){
								$qProj = $db->select('project','*',array('proj_id'=>$project_id));
								while($rProj = $db->fetch_array($qProj)):
									echo strtoupper($rProj['proj_name']);
									echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';
								endwhile;
							}
							?>
						</strong>
					</div>
					<div align="left">Date: <strong><?php echo functions::datearr($ao_date);?></strong></div>
					<div align="left">Reason: <strong><?php echo $db->getValue('attendance_overtime','reason',array('ao_id'=>$ao_id)); ?></strong></div><br><br>
				</div>
				<div align="center">
					<table width="99%" border="0">
						<tr>
							<td><div align="center">Before</div></td>
							<td><div align="center">After</div></td>
						</tr>
						<tr>
							<td width="50%">
								<div align="center">
								<?php
								$ot_imgBefore = $db->getValue('attendance_overtime','img_before',array('ao_id'=>$ao_id));
								$fileName = $db->getValue('doc_img','doc_name',array('di_id'=>$ot_imgBefore));
								if($fileName){
									$file = '../img_material/'.$fileName;
									echo '<img height="500" width="500" src="'.$file.'">';
								}
								else
									echo '<img height="200" width="200" src="../img_material/blank-pic.png">';
								?>
								<br>Select image to upload: <input type="file" name="imageBefore">
								</div>
							</td>
							<td>
								<div align="center">
									<?php
									$ot_imgAfter = $db->getValue('attendance_overtime','img_after',array('ao_id'=>$ao_id));
									$fileName = $db->getValue('doc_img','doc_name',array('di_id'=>$ot_imgAfter));
									if($fileName){
										$file = '../img_material/'.$fileName;
										echo '<img height="500" width="500" src="'.$file.'">';
									}
									else
										echo '<img height="200" width="200" src="../img_material/blank-pic.png">';
									?>
									<br>Select image to upload: <input type="file" name="imageAfter">
								</div>
							</td>
						</tr>
					</table>
				</div>
				<div align="center"><br><br>
					<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary">
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
function delt(){
	if(confirm('Do you want to remove this?'))
		return true;
	else
		return false; 
}
</script>
<!-- end: JavaScript-->
</body>
</html>