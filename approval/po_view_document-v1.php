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

$vw = (isset($_REQUEST['vw']) && !empty($_REQUEST['vw']) ) ? $_REQUEST['vw'] : 0;
$v_page = ($vw) ? 'po_view_only.php' : 'po_view.php';
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$docid = (isset($_REQUEST['docid']) && !empty($_REQUEST['docid']) ) ? functions::decode($_REQUEST['docid']) : 0;
$url_exnt = ($vw) ? '&vw=vw' : '';
if( $docid ){
	if( $filetodelete = $db->getValue('po_docs','doc_name',array('pods_id'=>$docid)) ){
		if( file_exists('../po_doc/'.$filetodelete) )
			unlink('../po_doc/'.$filetodelete);
		$db->delete('po_docs',array('pods_id'=>$docid));
		$_SESSION['notif_warning']='Document Successfully Removed!';
	}
	functions::sendTo(functions::pageName().'?po_id='.functions::encode($po_id).$url_exnt);
	die();
}
if( isset($_POST['btnSave']) && $po_id){
	if( !empty($_FILES['docfile']) && $_FILES['docfile']['error'] == 0 ) {
		$uploaddir = '../po_doc/';
		// Make sure the MIME type is an image
		#$pattern = "#^(/pdf)[^\s\n<]+$#i";
		$doc_org_name = $_FILES['docfile']['name'];
		$pattern = '/pdf/';
		$pattern_img = "#^(image/)[^\s\n<]+$#i";
		$file_ext = $_FILES['docfile']['type'];
		if( !preg_match($pattern, $_FILES['docfile']['type']) && !preg_match($pattern_img, $_FILES['docfile']['type']) ){
			functions::say("Only PDF or Image is allowed!");
		}
		else{
			$extnsn='';
			if( strtolower($file_ext)=="image/jpg" || strtolower($file_ext)=="image/jpeg" || strtolower($file_ext)=="image/png" || strtolower($file_ext)=="image/bmp" || strtolower($file_ext)=="application/pdf" ){
				if(strtolower($file_ext)=="image/jpg")
					$extnsn = '.jpg';
				else if(strtolower($file_ext)=="image/jpeg")
					$extnsn = '.jpeg';
				else if(strtolower($file_ext)=="image/png")
					$extnsn = '.png';
				else if(strtolower($file_ext)=="image/bmp")
					$extnsn = '.bmp';
				else if(strtolower($file_ext)=="application/pdf")
					$extnsn = '.pdf';
			}
			
			if( $extnsn ){
				$pods_id = $db->insert('po_docs',array('po_id'=>$po_id,'doc_org_name'=>$doc_org_name,'doc_type'=>$file_ext));
				if( $pods_id ){
					$doc_name = $po_id.'_'.$pods_id.$extnsn;
					$db->update('po_docs',array('doc_name'=>$doc_name),array('pods_id'=>$pods_id));

					// Upload the file to a secure directory with the new name and extension
					if( file_exists('../po_doc/'.$doc_name) )
						unlink('../po_doc/'.$doc_name);
					move_uploaded_file($_FILES['docfile']['tmp_name'], '../po_doc/'.$doc_name);

					$_SESSION['notif_success']='Document Successfully uploaded!';
					functions::sendTo(functions::pageName().'?po_id='.functions::encode($po_id).$url_exnt);
					die();
				}
			}

		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Purchase Order Document</title>
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
		width: 70%; 
		/*height: 500px;*/
		object-fit: contain;
	}
	</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>P.O Document</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li class="active"><a href="po_view_document.php?po_id=<?php echo functions::encode($po_id)?>" style="opacity:.9">Document</a></li>
				<li><a href="<?php echo $v_page?>?po_id=<?php echo functions::encode($po_id)?>">P.O. Detail</a></li>
				<li><a href="po_history.php?po_id=<?php echo functions::encode($po_id)?>">Activity Log</a></li>
			</ul>
			<form method="post" enctype="multipart/form-data">
				<div align="center">
					<table border="1">
						<tr>
							<td align="center" style="padding:10px;">
								Select PDF or Image File to upload: <input type="file" name="docfile" accept="application/pdf, image/jpeg"><br><br>
								<input type="submit" name="btnSave" id="btnSave" value="Upload" class="btn btn-primary btn-small">
							</td>
						</tr>
					</table>
				</div><br>
				<div align="center" style="padding-top:150px;">
				<?php
				$q = $db->select('po_docs','*',array('po_id'=>$po_id),'ORDER BY doc_created DESC');
				while($r = $db->fetch_array($q)):
					$file_name = '../po_doc/'.$r['doc_name'];
					#$doc_created = strtotime($['doc_created']);
					$date_created = date('F m, Y - h:i a',strtotime($r['doc_created']));

					if($r['doc_type']=='application/pdf'){
				?>
					<embed style="padding:5px;border:3px solid;" src="<?php echo $file_name?>" width="80%" height="1000px" />
				<?php }
					else{ ?>
				<div style="padding:10px;"><img style="padding:5px;border:3px solid;" src="<?php echo $file_name?>"></div>
					<?php } ?>
				<div style="padding-bottom:50px;">Uploaded on <?php echo $date_created; ?> | <a onClick="return ask()" href="?po_id=<?php echo functions::encode($po_id); echo ($vw) ? '&vw=vw' : '';?>&docid=<?php echo functions::encode($r['pods_id']);?>">Remove</a></div>
				<?php endwhile; ?>
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
	if(confirm('Do you want to remove this file?'))
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