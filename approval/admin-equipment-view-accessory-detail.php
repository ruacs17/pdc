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
$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;
$txItmVw='';$diID='';
$editTrue=0;$ea_date=date('Y-m-d');$invoice='';$remarks='';$repair_type='';$location='';$description='';$qLabor=0;$qParts=0;$discount='';$incharge='';$proj_id='';
if( isset($_REQUEST['txItmVw']) && !empty($_REQUEST['txItmVw']) ){
	$txItmVw = functions::decode($_REQUEST['txItmVw']);
	$editTrue = $db->getValue('equip_accessory','count(*)',array('ea_id'=>$txItmVw));
	$qvedt = $db->select('equip_accessory','*',array('ea_id'=>$txItmVw));
	$rvedt = $db->fetch_array($qvedt);
	$ea_date = $rvedt['ea_date'];
	$description = $rvedt['description'];
	$location = $rvedt['location'];
	$remarks = $rvedt['remarks'];
	$equip_id = $rvedt['equip_id'];
	$qLabor = $rvedt['quotation_labor'];
	$qParts = $rvedt['quotation_accessory'];
	$discount = $rvedt['discount'];
	$incharge = $rvedt['incharge'];
	$proj_id = $rvedt['proj_id'];
	$invoice = $rvedt['invoice'];
	$diID = $rvedt['di_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Equipment ACCESSORY Detail</title>
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
	<style>
	.tdSpace{padding: 10px 0px 4px 10px;}
	</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>EQUIPMENT ACCESSORY DETAIL</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<table width="70%" border="0" align="center" style="background-color:#f9f6f6">
					<tr>
						<th bgcolor="#f7ebeb" scope="col" height="60px;" width="15%"><div align="center">Equipment</div></th>
						<td class="tdSpace"><div align="left"><strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong></div></td>
					</tr>
					<tr>
						<th bgcolor="#f7ebeb" scope="col"><div align="center">Date</div></th>
						<td class="tdSpace"><div align="left"><?php echo functions::datearr($ea_date);?></div></td>
					</tr> 
					<tr>
						<th bgcolor="#f7ebeb" scope="col"><div align="center">Item / Accessory</div></th>
						<td class="tdSpace"><div align="left"><?php echo $description?></div></td>
					</tr> 
					<tr>
						<th bgcolor="#f7ebeb" scope="col"><div align="center">Image</div></th>
						<td class="tdSpace">
							<div align="left">
								<?php
								$fileName = $db->getValue('doc_img','doc_name',array('di_id'=>$diID));
								if($fileName){
								?>
								<a id="vw<?php echo $rID;?>" style="cursor: pointer;" class="thickbox" onclick="showThis(this.id,'admin-equipment-view-document.php?diid=<?php echo functions::encode($diID)?>','View Document','1')"><img height="200" width="200" src="<?php echo '../img_equip/'.$fileName;?>"></a>
								<?php }?>
							</div>
						</td>
					</tr>
					<tr>
						<th bgcolor="#f7ebeb" scope="col"><div align="center">Labor Quotation</div></th>
						<td class="tdSpace"><div align="left"><?php echo ($qLabor) ? functions::formatMoney($qLabor) : '';?></div></td>
					</tr>
					<tr>
						<th bgcolor="#f7ebeb" scope="col"><div align="center">Item Quotation</div></th>
						<td class="tdSpace"><div align="left"><?php echo ($qParts) ? functions::formatMoney($qParts) : '';?></div></td>
					</tr>
					<tr>
						<th bgcolor="#f7ebeb" scope="col"><div align="center">Discount (%)</div></th>
						<td class="tdSpace"><div align="left"><?php echo $discount?></div></td>
					</tr>
					<tr>
						<th bgcolor="#f7ebeb" scope="col"><div align="center">Shop Location</div></th>
						<td class="tdSpace"><div align="left"><?php echo $location?></div></td>
					</tr> 
					<tr>
						<th bgcolor="#f7ebeb" scope="col"><div align="center">Charge To</div></th>
						<td class="tdSpace" align="center"><div align="left"><?php echo $qProj = $db->getValue('project','proj_name',array('proj_id'=>$proj_id)); ?></div></td>
					</tr>
					<tr>
						<th bgcolor="#f7ebeb" scope="col"><div align="center">Invoice</div></th>
						<td class="tdSpace"><div align="left"><?php echo $invoice?></div></td>
					</tr>
					<tr>
						<th bgcolor="#f7ebeb" scope="col"><div align="center">User</div></th>
						<td class="tdSpace"><div align="left"><?php echo $incharge?></div></td>
					</tr>
					<tr>
						<th bgcolor="#f7ebeb" scope="col"><div align="center">Remarks</div></th>
						<td class="tdSpace"><div align="left"><?php echo $remarks?></div></td>
					</tr>
				</table>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<!-- end: JavaScript-->
</body>
</html>