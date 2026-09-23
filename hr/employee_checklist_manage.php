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
	#echo '<br>'.$db->last_query;

	if( isset($_FILES['image']) ){
		$uploaddir = '../img_emp/';
		$max_size = 4000 * 1024; // 500 KB
		// Generates random filename and extension 
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
				// Process image with GD library
				$verifyimg = getimagesize($_FILES['image']['tmp_name'][$key]);

				// Make sure the MIME type is an image
				$pattern = "#^(image/)[^\s\n<]+$#i";

				if( !preg_match($pattern, $verifyimg['mime']) ){
					functions::say("Only image files are allowed!");
				}
				else if( $file_size > $max_size ){
					functions::say("Image reached the limit size!");
				}
				else{
					// Rename both the image and the extension 
					$uploadfile = tempnam_sfx($uploaddir, ".jpg");

					// Upload the file to a secure directory with the new name and extension
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
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<!-- start: Favicon -->
	<link rel="shortcut icon" href="../img/favicon.png">
	<style type="text/css">
	body{background-color: #FFF;}
	</style>
	<!-- end: Favicon -->
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<div align="center">
<form class="form-horizontal" method="post" enctype="multipart/form-data">
	<table  width="90%" border="0" align="center">
		<tr>
			<td>
				<div align="center" style="padding-bottom:30px;"><h2>PERSONAL DATA SHEET</h2></div>
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
				<table width="100%" align="center" border="0" class="table table-bordered" style="font-size: 12px;">
					<thead>
						<tr style="background-color:#ececec">
							<th width="25%"><strong>CHECKLIST</strong></th>
							<th width="20%"><div align="center"><strong>STATUS</strong></div></th>
							<th width="20%"><div align="center"><strong>FILE</strong></div></th>
							<th width="20%"><div align="center"><strong>REMARKS</strong></div></th>
							<th width="10%"></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$countHours=0;$countSeminar=0;
						$q = $db->select('emp_201_reference','*',array('e2r_id'=>$e2r_id),'ORDER BY e2r_name');
						while($r = $db->fetch_array($q)):
							$rID = 0;
							$refID = $r['e2r_id'];
						?>
						<tr>
							<td><?php echo $r['e2r_name'];?></td>
							<td>
								<div align="left" style="padding-left:40px;">
									<label><input type="radio" name="rdoDone" value="1" <?php if($status=='1'){echo 'checked="checked"';}?>> Done<br></label>
									<label><input type="radio" name="rdoDone" value="0" <?php if($status=='0'){echo 'checked="checked"';}?>> Not Done</label>
								</div>
							</td>
							<td>
								<div align="center">

									<?php
									$countPic=0;
									$qpc = $db->select('emp_201_docs docs, doc_img img','*',array('e2r_id'=>$e2r_id,'emp_id'=>$emp_id),'AND docs.di_id=img.di_id');
									while( $rpc = $db->fetch_array($qpc)):
										$fileName = $rpc['doc_name'];
										$countPic++;
										if($fileName && file_exists('../img_emp/'.$fileName) ){
										$file = '../img_emp/'.$fileName;
									?>
										<div style="padding-top:50px;">
											<div>
												<a id="vwpc<?php echo $rpc['di_id']?>" style="cursor: pointer;" class="thickbox" title="Document View" onclick="showThis(this.id,'employee_checklist_manage_view.php?e2r_id=<?php echo functions::encode($e2r_id);?>&eid=<?php echo functions::encode($emp_id);?>&diid=<?php echo functions::encode($rpc['di_id']);?>','View Document','1')"><img height="200" width="200" src="<?php echo $file ?>" style="border:3px solid #1B2F4E;border-radius:3px;"></a>
											</div>
											<div><a href="?eid=<?php echo functions::encode($emp_id)?>&refID=<?php echo functions::encode($e2r_id)?>&fid=<?php echo functions::encode($rpc['di_id'])?>" onClick="askDel()">Remove</a></div>
										</div>
									<?php
										}
									endwhile;
									?>
									<?php if($countPic){ ?>
									<div style="padding-top:50px;">
										<a id="vwpc" style="cursor: pointer;" class="thickbox" title="Document View" data-rel="tooltip" onclick="showThis(this.id,'employee_checklist_manage_view.php?e2r_id=<?php echo functions::encode($e2r_id);?>&eid=<?php echo functions::encode($emp_id);?>','View Document','1')">View All Document</a>
									</div>
									<?php } ?>
									<div style="padding-top:50px;">Select image to upload: <input type="file" name="image[]" accept="image/png, image/gif, image/jpeg, image/jpg, image/bmp" multiple></div>
								</div>
							</td>
							<td><div align="center"><textarea name="txRemarks"><?php echo $remarks?></textarea></div></td>
							<td><div align="center"><input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-small btn-primary" onClick="if(confirm('Do you want to save this information?')){return true;}else{return false;}"></div></td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table><br><br>
			</td>
		</tr>
	</table>
</form>
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
	if(confirm('Do you want to remove this file?'))
		return true;
	else
		return false;
}
</script>
<!-- end: JavaScript-->
</body>
</html>