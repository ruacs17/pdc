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

$removeDoc = (isset($_REQUEST['rmdoc']) && !empty($_REQUEST['rmdoc']) ) ? functions::decode($_REQUEST['rmdoc']) : 0;
$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;
$ed_id_edt = (isset($_REQUEST['txItmEdt']) && !empty($_REQUEST['txItmEdt']) ) ? functions::decode($_REQUEST['txItmEdt']) : 0;
$txItmEdt='';$txName='';$txRemarks='';
if($ed_id_edt)
	$_SESSION['notif_id_list2']=$ed_id_edt;
$editTrue=0;$diID=0;
if( isset($_REQUEST['txItmEdt']) && !empty($_REQUEST['txItmEdt']) ){
	$txItmEdt = functions::decode($_REQUEST['txItmEdt']);
	$editTrue = $db->getValue('equip_doc','count(*)',array('ed_id'=>$txItmEdt));
	$qvedt = $db->select('equip_doc','*',array('ed_id'=>$txItmEdt));
	$rvedt = $db->fetch_array($qvedt);
	$txName = $rvedt['ed_name'];
	$txRemarks = $rvedt['ed_remarks'];
	$diID = $rvedt['di_id'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Property Detail</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
	.tdSpace{padding: 10px 0px 4px 10px;}
		img { 
			width: 30%; 
			/*height: 500px;*/
			object-fit: contain;
		}
	</style>
<?php
if($removeDoc && $ed_id_edt){
	$filename = $db->getValue('equip_docs','eds_name',array('eds_id'=>$removeDoc));
	if($filename){
		if( file_exists('../img_equip/'.$filename) ){
			unlink('../img_equip/'.$filename);
		}
	}

	$db->delete('equip_docs',array('eds_id'=>$removeDoc));
	$_SESSION['notif_warning']='File removed!';
	functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id).'&txItmEdt='.functions::encode($ed_id_edt));
	die();
}
if( isset($_POST['btnAdd']) && $equip_id){

	$ed_name = ( isset($_POST['txName']) && !empty($_POST['txName']) ) ? trim($_POST['txName']) : "";
	$ed_remarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : "";
	$arrDetails = array('equip_id'=>$equip_id,'ed_name'=>$ed_name,'ed_remarks'=>$ed_remarks);
	$countUploaded=0;
	$file_type='';
	if( $ed_name && $equip_id ){
		$docName='image';
		$error=0;
		foreach ($_FILES[$docName]['tmp_name'] as $count => $value):
			$file_name = $_FILES[$docName]['name'][$count]; 
			$file_tmpname = $_FILES[$docName]['tmp_name'][$count];
			$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
			$verifyimg = getimagesize($file_tmpname);
			// Make sure the MIME type is an image
			$pattern = "#^(image/)[^\s\n<]+$#i";
			if(strtolower($file_ext)=='pdf'){
				$file_type='pdf';
			}
			else{
				$file_type='img';
				if( !preg_match($pattern, $verifyimg['mime']) ){
					$error+=1;
				}
				else{
					$error += ( strtolower($file_ext)=="jpg" || strtolower($file_ext)=="jpeg" || strtolower($file_ext)=="png" || strtolower($file_ext)=="bmp" ) ? 0 : 1;
				}
			}
		endforeach;

		if(empty($error)){
			$ed_id = $db->insert('equip_doc',$arrDetails);
			$_SESSION['notif_id_list2']=$ed_id;
			$uploaddir = '../img_equip/';
			foreach ($_FILES[$docName]['tmp_name'] as $count => $value) {
				
				$file_tmpname = $_FILES[$docName]['tmp_name'][$count];
				if($file_tmpname){
					$file_name = $_FILES[$docName]['name'][$count]; 
					$file_size = $_FILES[$docName]['size'][$count]; 
					$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
					$verifyimg = getimagesize($file_tmpname);
					// Make sure the MIME type is an image
					$pattern = "#^(image/)[^\s\n<]+$#i";

					if( strtolower($file_ext)=='pdf' || strtolower($file_ext)=="jpg" || strtolower($file_ext)=="jpeg" || strtolower($file_ext)=="png" || strtolower($file_ext)=="bmp" ){
						$extnsn = '.jpg';
						if(strtolower($file_ext)=="jpg")
							$extnsn = '.jpg';
						else if(strtolower($file_ext)=="jpeg")
							$extnsn = '.jpeg';
						else if(strtolower($file_ext)=="png")
							$extnsn = '.png';
						else if(strtolower($file_ext)=="bmp")
							$extnsn = '.bmp';
						else if(strtolower($file_ext)=="pdf")
							$extnsn = '.pdf';

						$eds_id = $db->insert('equip_docs',array('ed_id'=>$ed_id,'equip_id'=>$equip_id,'eds_org_name'=>$_FILES[$docName]['name'][$count],'mime_type'=>$_FILES[$docName]['type'][$count]));
						if($eds_id){
							$eds_name = $uploaddir.$ed_id.'_'.$equip_id.'_'.$eds_id.$extnsn;
							if (move_uploaded_file($_FILES[$docName]['tmp_name'][$count], $eds_name)) {
								$eds_name = $ed_id.'_'.$equip_id.'_'.$eds_id.$extnsn;
								$db->update('equip_docs',array('eds_name'=>$eds_name),array('eds_id'=>$eds_id));
								$countUploaded++;
							}
						}
					}
				}
			}//foreach ($_FILES[$docName]['tmp_name'] as $count => $value)
		}
		if($countUploaded){
			$_SESSION['notif_success']='New Document successfully Added!';
		}
		else{
			$_SESSION['notif_warning']='Only Image and PDF files are allowed!';
		}

		functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id));
		die();
	}
	else
		functions::say('Please Fill up the form properly!');
}

if( isset($_POST['btnSave']) && $equip_id){

	$ed_name = ( isset($_POST['txName']) && !empty($_POST['txName']) ) ? trim($_POST['txName']) : "";
	$ed_remarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : "";
	$arrDetails = array('equip_id'=>$equip_id,'ed_name'=>$ed_name,'ed_remarks'=>$ed_remarks);

	$countUploaded=0;
	if( $ed_name && $ed_id_edt && $equip_id ){
		$ed_id = $ed_id_edt;
		$db->update('equip_doc',$arrDetails,array('equip_id'=>$equip_id,'ed_id'=>$ed_id_edt));
		$docName='image';
		$error=0;
		foreach ($_FILES[$docName]['tmp_name'] as $count => $value):
			$file_name = $_FILES[$docName]['name'][$count];
			$file_tmpname = $_FILES[$docName]['tmp_name'][$count];
			if($file_tmpname){
				$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
				$verifyimg = getimagesize($file_tmpname);
				// Make sure the MIME type is an image
				$pattern = "#^(image/)[^\s\n<]+$#i";

				if(strtolower($file_ext)=='pdf'){

				}
				else{
					if( !preg_match($pattern, $verifyimg['mime']) ){
						$error+=1;
						$verifyimg['mime'];
					}
					else{
						$error += ( strtolower($file_ext)=="jpg" || strtolower($file_ext)=="jpeg" || strtolower($file_ext)=="png" || strtolower($file_ext)=="bmp" ) ? 0 : 1;
					}					
				}
				

			}
		endforeach;
		if(empty($error)){

			$uploaddir = '../img_equip/';
			foreach ($_FILES[$docName]['tmp_name'] as $count => $value) {
				
				$file_tmpname = $_FILES[$docName]['tmp_name'][$count];
				if($file_tmpname){
					$file_name = $_FILES[$docName]['name'][$count]; 
					$file_size = $_FILES[$docName]['size'][$count]; 
					$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
					$verifyimg = getimagesize($file_tmpname);
					// Make sure the MIME type is an image
					$pattern = "#^(image/)[^\s\n<]+$#i";

					if( strtolower($file_ext)=='pdf' || strtolower($file_ext)=="jpg" || strtolower($file_ext)=="jpeg" || strtolower($file_ext)=="png" || strtolower($file_ext)=="bmp" ){
						$extnsn = '.jpg';
						if(strtolower($file_ext)=="jpg")
							$extnsn = '.jpg';
						else if(strtolower($file_ext)=="jpeg")
							$extnsn = '.jpeg';
						else if(strtolower($file_ext)=="png")
							$extnsn = '.png';
						else if(strtolower($file_ext)=="bmp")
							$extnsn = '.bmp';
						else if(strtolower($file_ext)=="pdf")
							$extnsn = '.pdf';

						$eds_id = $db->insert('equip_docs',array('ed_id'=>$ed_id,'equip_id'=>$equip_id,'eds_org_name'=>$_FILES[$docName]['name'][$count],'mime_type'=>$_FILES[$docName]['type'][$count]));
						if($eds_id){
							$eds_name = $uploaddir.$ed_id.'_'.$equip_id.'_'.$eds_id.$extnsn;
							if (move_uploaded_file($_FILES[$docName]['tmp_name'][$count], $eds_name)) {
								$eds_name = $ed_id.'_'.$equip_id.'_'.$eds_id.$extnsn;
								$db->update('equip_docs',array('eds_name'=>$eds_name),array('eds_id'=>$eds_id));
								$countUploaded++;
							}
						}
					}
				}
			}//foreach ($_FILES[$docName]['tmp_name'] as $count => $value)
			$_SESSION['notif_success']='Changes Saved!';
		}
		else
			$_SESSION['notif_warning']='Only Image and PDF files are allowed!';
		functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id).'&txItmEdt='.functions::encode($ed_id_edt));
		die();
	}
	else
		functions::say('Please Fill up the form properly!');
}
?>
</head>
<body>
<!-- body content: start here-->
<form method="post" enctype="multipart/form-data">
	<div class="row-fluid">
		<div class="box span12">
			<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white edit"></i><span class="break"></span>PROPERTY DOCUMENT REFERENCE</h2>
			</div>
			<div class="box-content">
				<div align="center">
					<input type="hidden" name="txItmEdt" id="txItmEdt" value="<?php echo functions::encode($txItmEdt);?>">
					<table width="70%" border="0" align="center" style="background-color:#f9f6f6">
						<tr>
							<th width="40%" bgcolor="#f7ebeb" scope="col" height="60px;"><div align="center">Property</div></th>
							<td class="tdSpace"><div align="left"><strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong></div></td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Document Name</div></th>
							<td class="tdSpace">
								<div align="left">
									<input type="text" style="width:350px;" class="span6 typeahead" name="txName" id="txName" value="<?php echo $txName;?>" autocomplete='off'>
									<span class="help-inline warning" id="msgtxDesc" style="font-weight:bold;" name="msgtxDesc"></span>
								</div>
							</td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Document</div></th>
							<td class="tdSpace">
								<div align="center">
									<?php
									$q = $db->select('equip_docs','*',array('ed_id'=>$ed_id_edt));
									while($r = $db->fetch_array($q)):
										$fileName = $r['eds_name'];
										$type = ($r['mime_type']=='application/pdf') ? 'pdf' : 'img';
										$file = file_exists('../img_equip/'.$fileName) ? $fileName : 'blank-pic.png';
									?>
									<div style="padding:10px;">
										<?php if($type=='img'){ ?>
										<img src="../img_equip/<?php echo $file;?>" style="border:3px solid #1B2F4E;border-radius:3px;">
										<?php }elseif($type=='pdf'){ ?>
										<embed style="padding:5px;border:3px solid;" src="../img_equip/<?php echo $file; ?>#toolbar=1" width="100%" height="500px" />
										<?php } ?>
										<div align="center"><a href="?vdidVw=<?php echo functions::encode($equip_id);?>&txItmEdt=<?php echo functions::encode($ed_id_edt);?>&rmdoc=<?php echo functions::encode($r['eds_id']);?>" onClick="if(confirm('Do you want to remove this file?')){return true;}else{return false;}">Remove</a></div>
									</div>
									
									<?php endwhile; ?>
								</div>
							</td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Upload New Document</div></th>
							<td class="tdSpace">
								<div align="left">
									<table border="0" width="98%" align="center">
										<tr>
											<td align="center">
												<div align="center">
												<img height="200" width="200" src="../img_emp/blank-pic.png"><br>
												Select image to upload: <input type="file" name="image[]" multiple="multiple" accept="application/image/*">
												</div><br>
											</td>
										</tr>
									</table>
								</div>
							</td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Remarks</div></th>
							<td class="tdSpace">
								<div align="left">
									<textarea style="width:350px;" class="span6 typeahead" name="txRemarks" id="txRemarks" autocomplete='off'><?php echo $txRemarks?></textarea>
									<span class="help-inline warning" id="msgtxRemarks" style="font-weight:bold;" name="msgtxRemarks"></span>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div align="center">
									<?php 
									if($editTrue)
									echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary btn-small">';
									else
									echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary btn-small">';
									?>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div><!--/span-->
	</div><!--/row-->
</form>
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

$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxDesc').html("");

		if( $('#txName').val()=="" ){
			$('#msgtxDesc').html("Specify Description!");
			$('#txName').focus();
			res=false;
		}
		else{
			if(confirm('Do you want to submit this information?'))
				res=true;
		}
		return res;
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