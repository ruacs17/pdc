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
$emp_id = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$e2r_id = (isset($_REQUEST['refID']) && !empty($_REQUEST['refID']) ) ? functions::decode($_REQUEST['refID']) : 0;
if($e2r_id)
	$_SESSION['notif_id']=$e2r_id;
$txtEmpNo='';$txtfName='';$txtmName='';$txtlName='';$txtExtName='';$txtNickName='';$bdate='';$txtBirthPlace='';$selGender='';$selCivilStat='';$txCitizenship='';$cert_id='';
if($emp_id){
	$q = $db->select('employee','*',array('emp_id'=>$emp_id));
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
$file = ($fileName && file_exists('../img_emp/'.$fileName) ) ? $fileName : 'blank-pic.png';

$status = $db->getValue('emp_201','e2_status',array('e2r_id'=>$e2r_id,'emp_id'=>$emp_id));
$e2_id = $db->getValue('emp_201','e2_id',array('e2r_id'=>$e2r_id,'emp_id'=>$emp_id));
$remarks = $db->getValue('emp_201','remarks',array('e2r_id'=>$e2r_id,'emp_id'=>$emp_id));

if( isset($_REQUEST['fid']) && $emp_id && $e2r_id ){
	$di_id_remove = functions::decode($_REQUEST['fid']);
	if( $di_id_remove = $db->getValue('emp_201_docs','di_id',array('e2r_id'=>$e2r_id,'emp_id'=>$emp_id,'di_id'=>$di_id_remove)) ){
		$db->delete('emp_201_docs',array('e2r_id'=>$e2r_id,'emp_id'=>$emp_id,'di_id'=>$di_id_remove));
		$db->delete('doc_img',array('di_id'=>$di_id_remove));
	}
	$_SESSION['notif_success']='File Removed!';
	functions::sendTo(functions::pageName().'?eid='.functions::encode($emp_id).'&refID='.functions::encode($e2r_id));
	die();
}

if( isset($_POST['btnSave']) && $emp_id && $e2r_id ){
	$status = (isset($_POST['rdoDone'])) ? $_POST['rdoDone'] : 0;
	$remarks = (isset($_POST['txRemarks'])) ? trim($_POST['txRemarks']) : '';

	$actionTaken=0;
	$arrField = array('emp_id'=>$emp_id,'e2r_id'=>$e2r_id,'e2_status'=>$status,'remarks'=>$remarks);
	if( $db->getValue('emp_201','count(*)',array('emp_id'=>$emp_id,'e2r_id'=>$e2r_id)) ){
		$db->update('emp_201',$arrField,array('emp_id'=>$emp_id,'e2r_id'=>$e2r_id));
		$actionTaken=1;
	}
	else{
		$db->insert('emp_201',$arrField);
		$actionTaken=1;
	}
	$e2_id = $db->getValue('emp_201','e2_id',array('e2r_id'=>$e2r_id,'emp_id'=>$emp_id));

	if( isset($_FILES['image']) ){
		$uploaddir = '../img_emp/';
		$max_size = 4000 * 1024; // 500 KB
		function tempnam_sfx($path, $suffix){
			do{
				$file = $path."/".mt_rand().$suffix;
				$fp = @fopen($file, 'x');
			}
			while(!$fp);

			fclose($fp);
			return $file;
		}

		foreach($_FILES["image"]["tmp_name"] as $key=>$tmp_name):
			if( $_FILES['image']['error'][$key] == 0 ){
				$file_name = $_FILES["image"]["name"][$key];
				$file_tmp = $_FILES["image"]["tmp_name"][$key];
				$file_size = $_FILES["image"]["size"][$key];
				$file_type = $_FILES["image"]["type"][$key];
				$verifyimg = getimagesize($_FILES['image']['tmp_name'][$key]);

				$pattern = "#^(image/)[^\s\n<]+$#i";

				if( !preg_match($pattern, $verifyimg['mime']) ){
					functions::say("Only image files are allowed!");
				}
				else if( $file_size > $max_size ){
					functions::say("Image reached the limit size!");
				}
				else{
					$uploadfile = tempnam_sfx($uploaddir, ".jpg");

					if (move_uploaded_file($file_tmp, $uploadfile)) {
						$di_id = $db->insert('doc_img',array('doc_name'=>basename($uploadfile),'doc_org_name'=>basename($file_name),'mime_type'=>$file_type));
						$db->insert('emp_201_docs',array('e2_id'=>$e2_id,'e2r_id'=>$e2r_id,'emp_id'=>$emp_id,'di_id'=>$di_id));
					}
				}					
			}
		endforeach;
	}

	$_SESSION['notif_success']='Information Saved!';
	functions::sendTo(functions::pageName().'?eid='.functions::encode($emp_id).'&refID='.functions::encode($e2r_id));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Checklist Manage</title>
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
		body {
			background-color: #f8fafc;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			color: #1e293b;
			padding: 24px 16px;
		}

		.checklist-container {
			max-width: 820px;
			margin: 0 auto;
		}

		.page-header-title {
			text-align: center;
			margin-bottom: 24px;
		}

		.page-header-title h2 {
			font-size: 22px;
			font-weight: 700;
			color: #0f172a;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			margin: 0;
		}

		/* Profile Card */
		.profile-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
			padding: 20px;
			margin-bottom: 24px;
			display: flex;
			align-items: center;
			gap: 24px;
		}

		@media (max-width: 640px) {
			.profile-card {
				flex-direction: column;
				text-align: center;
			}
		}

		.profile-avatar-wrapper {
			flex-shrink: 0;
		}

		.profile-avatar {
			width: 110px;
			height: 110px;
			border-radius: 10px;
			object-fit: cover;
			border: 3px solid #f1f5f9;
		}

		.profile-info-grid {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 12px;
			width: 100%;
		}

		@media (max-width: 600px) {
			.profile-info-grid {
				grid-template-columns: repeat(2, 1fr);
			}
		}

		.info-item {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			padding: 8px 12px;
			border-radius: 6px;
		}

		.info-label {
			font-size: 10px;
			font-weight: 700;
			text-transform: uppercase;
			color: #64748b;
			display: block;
			margin-bottom: 2px;
		}

		.info-value {
			font-size: 13px;
			font-weight: 600;
			color: #0f172a;
		}

		/* Checklist Card Component */
		.checklist-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
			overflow: hidden;
		}

		.checklist-card-header {
			background: #f8fafc;
			padding: 16px 24px;
			border-bottom: 1px solid #e2e8f0;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.checklist-card-title {
			font-size: 16px;
			font-weight: 700;
			color: #0f172a;
			margin: 0;
		}

		.checklist-card-body {
			padding: 24px;
			display: flex;
			flex-direction: column;
			gap: 20px;
		}

		/* Side-by-Side Horizontal Form Rows */
		.form-horizontal-row {
			display: flex;
			align-items: flex-start;
			gap: 20px;
		}

		@media (max-width: 640px) {
			.form-horizontal-row {
				flex-direction: column;
				gap: 8px;
			}
		}

		.field-label {
			width: 160px;
			flex-shrink: 0;
			font-size: 12px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			color: #475569;
			padding-top: 8px;
		}

		.field-content {
			flex: 1;
			width: 100%;
		}

		/* Status Radio Pill Group */
		.status-radio-group {
			display: flex;
			gap: 12px;
			max-width: 280px;
		}

		.radio-label {
			flex: 1;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			background: #ffffff;
			border: 1px solid #cbd5e1;
			padding: 8px 16px;
			border-radius: 6px;
			cursor: pointer;
			font-size: 13px;
			font-weight: 600;
			color: #475569;
			transition: all 0.2s ease;
		}

		.radio-label:hover {
			background: #f8fafc;
			border-color: #94a3b8;
		}

		.radio-label input[type="radio"] {
			margin: 0;
		}

		/* Status Badges */
		.badge-status {
			display: inline-block;
			padding: 4px 12px;
			border-radius: 20px;
			font-size: 11px;
			font-weight: 700;
			text-transform: uppercase;
		}

		.badge-done {
			background-color: #dcfce7;
			color: #15803d;
			border: 1px solid #bbf7d0;
		}

		.badge-not-done {
			background-color: #fef2f2;
			color: #b91c1c;
			border: 1px solid #fecaca;
		}

		/* Document Attachments Box */
		.document-box-container {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 8px;
			padding: 16px;
		}

		.doc-gallery {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
			margin-bottom: 12px;
		}

		.doc-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 6px;
			padding: 6px;
			text-align: center;
		}

		.doc-thumb {
			width: 85px;
			height: 85px;
			object-fit: cover;
			border-radius: 4px;
			border: 1px solid #f1f5f9;
			display: block;
		}

		.btn-remove-doc {
			display: inline-block;
			margin-top: 6px;
			color: #ef4444;
			font-size: 11px;
			font-weight: 600;
			text-decoration: none !important;
			padding: 2px 6px;
			border-radius: 4px;
			background: #fef2f2;
			border: 1px solid #fecaca;
		}

		.btn-remove-doc:hover {
			background: #ef4444;
			color: #ffffff;
		}

		.btn-view-all {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			color: #0284c7 !important;
			font-weight: 600;
			font-size: 12px;
			text-decoration: none !important;
			margin-bottom: 12px;
		}

		/* File Input */
		.file-input-wrapper {
			font-size: 12px;
			color: #64748b;
		}

		.file-input-wrapper input[type="file"] {
			margin-top: 4px;
			font-size: 12px;
			display: block;
		}

		/* Textarea Control */
		.custom-textarea {
			width: 100%;
			box-sizing: border-box;
			border: 1px solid #cbd5e1;
			border-radius: 6px;
			padding: 10px 12px;
			font-size: 13px;
			color: #1e293b;
			resize: vertical;
			min-height: 80px;
			outline: none;
			transition: border-color 0.2s ease;
		}

		.custom-textarea:focus {
			border-color: #0284c7;
			box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
		}

		/* Footer Actions */
		.checklist-card-footer {
			background: #f8fafc;
			padding: 14px 24px;
			border-top: 1px solid #e2e8f0;
			display: flex;
			justify-content: flex-end;
		}

		.btn-save-action {
			background-color: #0284c7;
			color: #ffffff !important;
			font-weight: 600;
			padding: 8px 20px;
			border-radius: 6px;
			border: none;
			cursor: pointer;
			box-shadow: 0 2px 4px rgba(2, 132, 199, 0.15);
			transition: background-color 0.2s ease;
		}

		.btn-save-action:hover {
			background-color: #0369a1;
		}
	</style>
</head>
<body>

<div class="checklist-container">
	<div class="page-header-title">
		<h2>Personal Data Sheet</h2>
	</div>

	<form class="form-horizontal" method="post" enctype="multipart/form-data">
		<!-- Employee Information Overview -->
		<div class="profile-card">
			<div class="profile-avatar-wrapper">
				<img class="profile-avatar" src="../img_emp/<?php echo $file;?>" alt="Employee Picture">
			</div>
			<div class="profile-info-grid">
				<div class="info-item">
					<span class="info-label">ID Number</span>
					<span class="info-value"><?php echo $txtEmpNo ?: '--';?></span>
				</div>
				<div class="info-item">
					<span class="info-label">Last Name</span>
					<span class="info-value"><?php echo $txtlName ?: '--';?></span>
				</div>
				<div class="info-item">
					<span class="info-label">First Name</span>
					<span class="info-value"><?php echo $txtfName ?: '--';?></span>
				</div>
				<div class="info-item">
					<span class="info-label">Middle Name</span>
					<span class="info-value"><?php echo $txtmName ?: '--';?></span>
				</div>
				<div class="info-item">
					<span class="info-label">Suffix</span>
					<span class="info-value"><?php echo $txtExtName ?: '--';?></span>
				</div>
				<div class="info-item">
					<span class="info-label">Nickname</span>
					<span class="info-value"><?php echo $txtNickName ?: '--';?></span>
				</div>
			</div>
		</div>

		<!-- Checklist Item Form View -->
		<?php
		$q = $db->select('emp_201_reference','*',array('e2r_id'=>$e2r_id),'ORDER BY e2r_name');
		while($r = $db->fetch_array($q)):
			$refID = $r['e2r_id'];
		?>
		<div class="checklist-card">
			<div class="checklist-card-header">
				<h3 class="checklist-card-title"><?php echo $r['e2r_name'];?></h3>
				<?php if($status == '1'): ?>
					<span class="badge-status badge-done">Done</span>
				<?php else: ?>
					<span class="badge-status badge-not-done">Not Done</span>
				<?php endif; ?>
			</div>

			<div class="checklist-card-body">
				<!-- Row 1: Status -->
				<div class="form-horizontal-row">
					<label class="field-label">Status</label>
					<div class="field-content">
						<div class="status-radio-group">
							<label class="radio-label">
								<input type="radio" name="rdoDone" value="1" <?php if($status=='1'){echo 'checked="checked"';}?>>
								<span>Done</span>
							</label>
							<label class="radio-label">
								<input type="radio" name="rdoDone" value="0" <?php if($status=='0'){echo 'checked="checked"';}?>>
								<span>Not Done</span>
							</label>
						</div>
					</div>
				</div>

				<!-- Row 2: Documents -->
				<div class="form-horizontal-row">
					<label class="field-label">Attachments</label>
					<div class="field-content">
						<div class="document-box-container">
							<?php
							$countPic=0;
							$qpc = $db->select('emp_201_docs docs, doc_img img','*',array('e2r_id'=>$e2r_id,'emp_id'=>$emp_id),'AND docs.di_id=img.di_id');
							if($qpc):
							?>
							<div class="doc-gallery">
								<?php
								while( $rpc = $db->fetch_array($qpc)):
									$fileName = $rpc['doc_name'];
									$countPic++;
									if($fileName && file_exists('../img_emp/'.$fileName) ){
										$file = '../img_emp/'.$fileName;
								?>
									<div class="doc-card">
										<a id="vwpc<?php echo $rpc['di_id']?>" style="cursor: pointer;" class="thickbox" title="Document View" onclick="showThis(this.id,'employee_checklist_manage_view.php?e2r_id=<?php echo functions::encode($e2r_id);?>&eid=<?php echo functions::encode($emp_id);?>&diid=<?php echo functions::encode($rpc['di_id']);?>','View Document','1')">
											<img class="doc-thumb" src="<?php echo $file ?>">
										</a>
										<a class="btn-remove-doc" href="?eid=<?php echo functions::encode($emp_id)?>&refID=<?php echo functions::encode($e2r_id)?>&fid=<?php echo functions::encode($rpc['di_id'])?>" onClick="return askDel();">Remove</a>
									</div>
								<?php
									}
								endwhile;
								?>
							</div>
							<?php endif; ?>

							<?php if($countPic){ ?>
								<div>
									<a id="vwpc" style="cursor: pointer;" class="btn-view-all thickbox" title="Document View" data-rel="tooltip" onclick="showThis(this.id,'employee_checklist_manage_view.php?e2r_id=<?php echo functions::encode($e2r_id);?>&eid=<?php echo functions::encode($emp_id);?>','View Document','1')">
										<i class="halflings-icon file"></i> View All Uploaded Documents
									</a>
								</div>
							<?php } ?>

							<div class="file-input-wrapper">
								<span>Select files to upload:</span>
								<input type="file" name="image[]" accept="image/png, image/gif, image/jpeg, image/jpg, image/bmp" multiple>
							</div>
						</div>
					</div>
				</div>

				<!-- Row 3: Remarks -->
				<div class="form-horizontal-row">
					<label class="field-label">Remarks</label>
					<div class="field-content">
						<textarea class="custom-textarea" name="txRemarks" placeholder="Enter remarks or notes here..."><?php echo $remarks?></textarea>
					</div>
				</div>
			</div>

			<div class="checklist-card-footer">
				<input type="submit" name="btnSave" id="btnSave" value="Save Changes" class="btn-save-action" onClick="if(confirm('Do you want to save this information?')){return true;}else{return false;}">
			</div>
		</div>
		<?php endwhile;?>
	</form>
</div>

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

<script type="text/javascript">
function askDel(){
	return confirm('Do you want to remove this file?');
}
</script>
<!-- end: JavaScript-->
</body>
</html>