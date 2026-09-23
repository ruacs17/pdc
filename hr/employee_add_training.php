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
$et_type='training';
$fromED = (isset($_REQUEST['fromED']) && !empty($_REQUEST['fromED']) ) ? $_REQUEST['fromED'] : '';
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$itemEdt = (isset($_REQUEST['eeEdt']) && !empty($_REQUEST['eeEdt']) ) ? functions::decode($_REQUEST['eeEdt']) : 0;
$itemDel = (isset($_REQUEST['eeDlt']) && !empty($_REQUEST['eeDlt']) ) ? functions::decode($_REQUEST['eeDlt']) : 0;
$txTitle='';$txSponsoredBy='';$txNoHr ='';$selMonFrom='';$selDayFrom='';$selYrFrom='';$selMonTo='';$selDayTo='';$selYrTo='';$cert_id='';$et_date_from='';$et_date_to='';
if($itemEdt){
	$q = $db->select('emp_training','*',array('et_id'=>$itemEdt,'emp_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$txTitle = $r['et_title'];
		$txSponsoredBy = $r['et_sponsored_by'];
		$txNoHr = $r['et_no_hour'];
		$cert_id = $r['cert_id'];
		$et_date_from = $r['et_date_from'];
		$et_date_to = $r['et_date_to'];
	endwhile;
}
if($itemDel){
	$del_cert_id = $db->getValue('emp_training','cert_id',array('et_id'=>$itemDel,'emp_id'=>$eid));
	$uploaded_file = $db->getValue('cert_img','cert_name',array('cert_id'=>$del_cert_id));
	if($uploaded_file){
		if(file_exists('../img_emp/'.$uploaded_file))
			unlink('../img_emp/'.$uploaded_file);
	}
	$_SESSION['notif_warning']='Training Removed!';
	$db->delete('emp_training',array('et_id'=>$itemDel,'emp_id'=>$eid));
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Training/Seminar</title>
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
	<style type="text/css">body{font-size: 12px;}</style>
<?php
if( isset($_POST['btnSave']) ){

	$et_title = ( isset($_POST['txTitle']) ) ? strtoupper(trim($_POST['txTitle'])) : '';
	$et_sponsored_by = ( isset($_POST['txSponsoredBy']) ) ? strtoupper(trim($_POST['txSponsoredBy'])) : '';
	$et_no_hour = ( isset($_POST['txNoHr']) ) ? strtoupper(trim($_POST['txNoHr'])) : '';
	$txTitle = $et_title;$txSponsoredBy=$et_sponsored_by;$txNoHr=$et_no_hour;

	$et_date_from = ( isset($_POST['txFromDate']) && !empty($_POST['txFromDate']) ) ? trim($_POST['txFromDate']) : NULL;
	$et_date_to = ( isset($_POST['txToDate']) && !empty($_POST['txToDate']) ) ? trim($_POST['txToDate']) : NULL;

	$et_id=0;
	if($eid && $et_date_from && $et_date_to && $et_title){
		$et_id = $itemEdt;
		$arrInsert = array('emp_id'=>$eid,'et_title'=>$et_title,'et_sponsored_by'=>$et_sponsored_by,'et_date_from'=>$et_date_from,'et_date_to'=>$et_date_to,'et_no_hour'=>$et_no_hour,'et_type'=>$et_type);
		if($itemEdt){
			$_SESSION['notif_id_list']=$itemEdt;
			$db->update('emp_training',$arrInsert,array('et_id'=>$itemEdt,'emp_id'=>$eid));
			$_SESSION['notif_success']='Changes Saved!';
		}else{
			$et_id = $db->insert('emp_training',$arrInsert);
			if($et_id){
				$_SESSION['notif_id_list']=$et_id;
				$_SESSION['notif_success']='New Training Added!';
			}
		}
	}

	if($et_id){
		if( !empty($_FILES['image']) && $_FILES['image']['error'] == 0 ) {

			$uploaddir = '../img_emp/';
			$max_size = 2000 * 1024; // 500 KB
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
			// Process image with GD library
			$verifyimg = getimagesize($_FILES['image']['tmp_name']);

			// Make sure the MIME type is an image
			$pattern = "#^(image/)[^\s\n<]+$#i";

			if( !preg_match($pattern, $verifyimg['mime']) ){
				functions::say("Only image files are allowed!");
			}
			else if( $_FILES["image"]["size"] > $max_size ){
				functions::say("Image reached the limit size!");
			}
			else{
				// Rename both the image and the extension 
				$uploadfile = tempnam_sfx($uploaddir, ".jpg");
				// Upload the file to a secure directory with the new name and extension
				if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
					$qPicIns = $db->insertPrint('cert_img',array('cert_name'=>basename($uploadfile),'cert_org_name'=>basename($_FILES['image']['name']),'mime_type'=>$_FILES['image']['type']));
					$db->query($qPicIns);
					$newCertID = $db->insert_id();
					$db->update('emp_training',array('cert_id'=>$newCertID),array('et_id'=>$et_id));
				}
			}
		}
		functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
		die();
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<?php if(empty($fromED)){ ?>
		<div class="box-content">
			<div align="right" class="nav tab-menu nav-tabs" style="padding-top: 3px;"><?php require_once('employee_options.php');?></div>
		</div>
		<?php } ?>
		<div class="box-content">
			<form class="form-horizontal" method="post" enctype="multipart/form-data" onSubmit="return ask()">
				<div align="center" style="padding-bottom: 15px;"><h2>RELEVANT TRAININGS (with Certificate Attached)</h2></div>
				<table id="tblist" width="80%" align="center" border="0" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="background-color:#E4E1E1; font-size: 12px;">
					<thead>
						<tr>
							<td rowspan="2" width="25%"><strong>TITLE OF SEMINAR / CONFERENCE / <br>WORKSHOP / SHORT COURSES</strong></td>
							<td colspan="2" width="20%"><div align="center"><strong>INCLUSIVE DATE(S) OF ATTENDANCE</strong></div></td>
							<td rowspan="2" width="10%"><strong>NO. OF HOUR(S)</strong></td>
							<td rowspan="2" width="20%"><strong>CONDUCTED / SPONSORED BY</strong></td>
							<td rowspan="2" width="15%"><div align="center"><strong>CERTIFICATE</strong></div></td>
							<td rowspan="2" width="15%"><div align="center"><strong>Option</strong></div></td>
						</tr>
						<tr>
							<td width="10%"><strong>From</strong></td>
							<td width="10%"><strong>To</strong></td>
						</tr>
					</thead>
					<tbody>
						<?php
						$q = $db->select('emp_training','*',array('emp_id'=>$eid,'et_type'=>$et_type),'ORDER BY et_date_from DESC');
						while($r = $db->fetch_array($q)):
							$rID=$r['et_id'];
						?>
						<tr id="rw<?php echo $rID?>" style="background-color:#ececec">
							<td><?php echo $r['et_title']?></td>
							<td><?php echo functions::datearr($r['et_date_from'])?></td>
							<td><?php echo functions::datearr($r['et_date_to'])?></td>
							<td><div align="center"><?php echo $r['et_no_hour'];?></div></td>
							<td><?php echo $r['et_sponsored_by']?></td>
							<td>
								<?php
								$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$r['cert_id']));
								$file = ($fileName) ? $fileName : 'blank-pic.png';
								?>
								<div align="center">
									<a id="vw<?php echo $r['et_id']?>" style="cursor: pointer;" class="thickbox" onclick="showThis(this.id,'employee_cert_view.php?crtID=<?php echo functions::encode($r['cert_id'])?>','View Certificate','1')"><img height="100" width="100" src="../img_emp/<?php echo $file;?>"></a>
								</div>
							</td>
							<td>
								<div align="center">
									<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeEdt=<?php echo functions::encode($r['et_id'])?>&fromED=<?php echo $fromED;?>" title="Modify" class="btn btn-warning btn-mini"><i class="halflings-icon white pencil"></i></a>
									<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeDlt=<?php echo functions::encode($r['et_id'])?>&fromED=<?php echo $fromED;?>" onClick="return askDel()" title="Remove" class="btn btn-danger btn-mini"><i class="halflings-icon white trash"></i></a>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table>
				<br><br><br>
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1;">
					<tr>
						<td width="17%" height="30"><div align="right">TITLE OF SEMINAR / CONFERENCE / <br>WORKSHOP / SHORT COURSES</div></td>
						<td width="43%"><input type="text" name="txTitle" id="txTitle" class="span6" value="<?php echo $txTitle;?>" required/></td>
					</tr>
					<tr>
						<td height="30"><div align="right">INCLUSIVE DATE (FROM)</div></td>
						<td><input name="txFromDate" type="text" id="txFromDate" value="<?php echo $et_date_from; ?>" style="width: 90px;" required></td>
					</tr>
					<tr>
						<td height="30"><div align="right">INCLUSIVE DATE (TO)</div></td>
						<td><input name="txToDate" type="text" id="txToDate" value="<?php echo $et_date_to; ?>" style="width: 90px;" required></td>
					</tr>
					<tr>
						<td height="30"><div align="right">NO. OF HOUR(S)</div></td>
						<td><input type="text" name="txNoHr" id="txNoHr" class="span6" value="<?php echo $txNoHr;?>" /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">CONDUCTED / SPONSORED BY</div></td>
						<td><input type="text" name="txSponsoredBy" id="txSponsoredBy" class="span6" value="<?php echo $txSponsoredBy;?>" /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">CERTIFICATE</div></td>
						<td>
							<table border="0" width="98%" align="center">
								<tr>
									<td align="center">
										<div align="center">
											<?php
											$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
											if($fileName){
												$file = '../img_emp/'.$fileName;
												echo '<img height="200" width="200" src="'.$file.'">';
											}
											else
												echo '<img height="200" width="200" src="../img_emp/blank-pic.png">';
											?>
											Select <strong>2x2</strong> image to upload: <input type="file" name="image">
										</div><br>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<div align="center">
					<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small">
					<?php if ($itemEdt): ?><a class="btn btn-small" href="?eid=<?php echo functions::encode($eid)?>&fromED=<?php echo $fromED;?>">Cancel</a><?php endif ?>
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
$(document).ready(function(){
	$('#txFromDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'1970:<?php echo date('Y')+1 ?>',
		beforeShow:function(selectdate){
			 var dt=new Date($('#txFromDate').val());
			dt.setDate(dt.getDate())
			$('#txToDate').datepicker('option','minDate',dt);
			$('#txToDate').datepicker('option','defaultDate',$('#txFromDate').val());
			var dt=new Date($('#txToDate').val());
			dt.setDate(dt.getDate())
			$('#txFromDate').datepicker('option','maxDate',dt);
		},
		onSelect:function(selectdate){
			//alert(selectdate)
			var dt=new Date(selectdate);
			dt.setDate(dt.getDate())
			$('#txToDate').datepicker('option','minDate',dt);
			$('#txToDate').datepicker('option','defaultDate',$('#txFromDate').val());
		}
	});
	$('#txToDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'1970:<?php echo date('Y')+1 ?>',
		yearRange:'1970:<?php echo date('Y')+1 ?>',
		beforeShow:function(selectdate){
			 var dt=new Date($('#txFromDate').val());
			dt.setDate(dt.getDate())
			$('#txToDate').datepicker('option','minDate',dt);
			$('#txToDate').datepicker('option','defaultDate',$('#txFromDate').val());
			var dt=new Date($('#txToDate').val());
			dt.setDate(dt.getDate())
			$('#txFromDate').datepicker('option','maxDate',dt);
		},
		onSelect:function(selectdate){
			var dt=new Date(selectdate);
			dt.setDate(dt.getDate())
			$('#txFromDate').datepicker('option','maxDate',dt);
		}
	});
});
function askDel(){
	if(confirm('Do you want to remove this item?'))
		return true;
	else
		return false;
}
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
<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>