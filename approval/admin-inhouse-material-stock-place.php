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

$item = (isset($_REQUEST['itm']) && !empty($_REQUEST['itm']) ) ? functions::decode($_REQUEST['itm']) : '';
$brand = (isset($_REQUEST['brnd']) && !empty($_REQUEST['brnd']) ) ? functions::decode($_REQUEST['brnd']) : '';
$unit = (isset($_REQUEST['unt']) && !empty($_REQUEST['unt']) ) ? functions::decode($_REQUEST['unt']) : '';
$category = (isset($_REQUEST['cat']) && !empty($_REQUEST['cat']) ) ? functions::decode($_REQUEST['cat']) : '--undefined--';
$raw = (isset($_REQUEST['raw']) && !empty($_REQUEST['raw']) ) ? functions::decode($_REQUEST['raw']) : '';
$class = (isset($_REQUEST['class']) && !empty($_REQUEST['class']) ) ? functions::decode($_REQUEST['class']) : '--undefined--';
$stand = (isset($_REQUEST['stand']) && !empty($_REQUEST['stand']) ) ? functions::decode($_REQUEST['stand']) : '';
$divider = (isset($_REQUEST['divider']) && !empty($_REQUEST['divider']) ) ? functions::decode($_REQUEST['divider']) : '';
$type = (isset($_REQUEST['type']) && !empty($_REQUEST['type']) ) ? functions::decode($_REQUEST['type']) : '--undefined--';
$sequence = (isset($_REQUEST['sequence']) && !empty($_REQUEST['sequence']) ) ? functions::decode($_REQUEST['sequence']) : '';
$series = (isset($_REQUEST['series']) && !empty($_REQUEST['series']) ) ? functions::decode($_REQUEST['series']) : '';
$location = (isset($_REQUEST['location']) && !empty($_REQUEST['location']) ) ? functions::decode($_REQUEST['location']) : '';


if( isset($_POST['btnAdd']) ){
	$txRawNo = ( isset($_POST['txRawNo']) && !empty($_POST['txRawNo']) ) ? trim($_POST['txRawNo']) : '';
	$txStandNo = ( isset($_POST['txStandNo']) && !empty($_POST['txStandNo']) ) ? trim($_POST['txStandNo']) : '';
	$txDividerNo = ( isset($_POST['txDividerNo']) && !empty($_POST['txDividerNo']) ) ? trim($_POST['txDividerNo']) : '';
	$txSequenceNo = ( isset($_POST['txSequenceNo']) && !empty($_POST['txSequenceNo']) ) ? trim($_POST['txSequenceNo']) : '';
	$txSeriesNo = ( isset($_POST['txSeriesNo']) && !empty($_POST['txSeriesNo']) ) ? trim($_POST['txSeriesNo']) : '';
	
	$_SESSION['notif_warning']='Stock Adding Fail!';
	if( $item && $unit && $location ){

		$db->update('inhouse_material_storage',array('raw_no'=>$txRawNo,'stand_no'=>$txStandNo,'divider_no'=>$txDividerNo,'sequence_no'=>$txSequenceNo,'series_no'=>$txSeriesNo),array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location,'raw_no'=>$raw,'stand_no'=>$stand,'divider_no'=>$divider,'sequence_no'=>$sequence,'series_no'=>$series));
		$_SESSION['notif_success']='Inventory Updated!';
		unset($_SESSION['notif_warning']);
		$lnk = '?itm='.functions::encode($item).'&brnd='.functions::encode($brand).'&unt='.functions::encode($unit).'&cat='.functions::encode($category);
		$lnk .= '&raw='.functions::encode($txRawNo).'&class='.functions::encode($class).'&stand='.functions::encode($txStandNo).'&divider='.functions::encode($txDividerNo);
		$lnk .= '&type='.functions::encode($type).'&sequence='.functions::encode($txSequenceNo).'&series='.functions::encode($txSeriesNo).'&location='.functions::encode($location);
		functions::sendTo(functions::pageName().$lnk);
		die();
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Material Stock Add</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
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
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Material Stock Update</h2>
		</div>
		<div class="box-content">
			<div align="center">
			<div class="box-content" style="width:750px;">
				<form class="form-horizontal" method="post">
					<fieldset>
						<div class="control-group">
							<label class="control-label" for="txItemID"><strong>Item</strong></label>
							<div class="controls" align="left">
								<textarea style="width:80%;" rows="2" name="txItemNme" id="txItemNme" readonly><?php echo $item?></textarea>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="txUnit"><strong>Unit</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:70px;" class="span6 typeahead" name="txUnit" id="txUnit" value="<?php echo $unit?>" readonly>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txBrand"><strong>Brand</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:150px;" name="txBrand" id="txBrand" value="<?php echo $brand;?>" readonly>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txCat"><strong>Location</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:385px;" name="txLoc" id="txLoc" value="<?php echo $location?>" readonly>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txCat"><strong>Category</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:385px;" name="txCat" id="txCat" value="<?php echo $category?>" readonly>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txRawNo"><strong>Raw No.</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:120px;" name="txRawNo" id="txRawNo" value="<?php echo $raw;?>">
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txClass"><strong>Classification</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:385px;" name="txClass" id="txClass" value="<?php echo $class;?>" readonly>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txStandNo"><strong>Stand No.</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:120px;" name="txStandNo" id="txStandNo" value="<?php echo $stand;?>">
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txDividerNo"><strong>Divider No.</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:120px;" name="txDividerNo" id="txDividerNo" value="<?php echo $divider;?>">
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txType"><strong>Type</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:385px;" name="txType" id="txType" value="<?php echo $type;?>" readonly>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txSequenceNo"><strong>Sequence No.</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:120px;" name="txSequenceNo" id="txSequenceNo" value="<?php echo $sequence;?>">
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txSeriesNo"><strong>Series No.</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:120px;" name="txSeriesNo" id="txSeriesNo" value="<?php echo $series;?>">
							</div>
						</div>
						<div class="control-group hidden-phone" style="padding-top:20px;">
							<label class="control-label" for="btnAdd"><strong>&nbsp;</strong></label>
							<div class="controls" align="left">
								<input type="submit" name="btnAdd" id="btnAdd" value="Save" class="btn btn-small btn-primary">
							</div>
						</div>
					</fieldset>
				</form>
			</div>
			</div>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){

		if(confirm('Do you want to save this information?'))
			res=true;
		else
			res=false;
		return res;
	});
});
</script>
<!-- end: JavaScript-->
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
</body>
</html>