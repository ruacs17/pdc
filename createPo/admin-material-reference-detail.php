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
$mf_id = (isset($_REQUEST['mf_id']) && !empty($_REQUEST['mf_id']) ) ? functions::decode($_REQUEST['mf_id']) : 0;
$_SESSION['notif_id_list']=$mf_id;
$editTrue=0;
$poidEdt='';
$item='';$itemDesc='';$unit='';$brand='';$class='';$type='';$min='';$cat='';$size='';$code='';

$qvedt = $db->select('material_reference','*',array('mf_id'=>$mf_id));
$rvedt = $db->fetch_array($qvedt);
$item = $rvedt['item'];
$unit = $rvedt['unit'];
$brand = $rvedt['brand'];
$code = $rvedt['m_code'];
$class = $rvedt['classification'];
$type = $rvedt['mtype'];
$min = $rvedt['least_required'];
$size = $rvedt['msize'];
$itemDesc = $rvedt['mdescription'];
$cat = $rvedt['category'];
$cad = $rvedt['cad'];
$engr_standard = $rvedt['engr_standard'];
$dupa = $rvedt['dupa'];
$specification = $rvedt['specification'];
$product_brochure = $rvedt['product_brochure'];


if( isset($_POST['btnSearch']) ){
	$_SESSION['pmSup'] = ( isset($_POST['selSupplier']) && !empty($_POST['selSupplier']) ) ? $db->clean(functions::decode($_POST['selSupplier'])) : '';
	$_SESSION['pmMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $db->clean($_POST['bdMon']) : '';
	$_SESSION['pmYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $db->clean($_POST['bdYear']) : '';
	$_SESSION['pmMonTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $db->clean($_POST['bdMonTo']) : '';
	$_SESSION['pmYearTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $db->clean($_POST['bdYearTo']) : '';
	functions::sendTo(functions::pageName().'?mf_id='.functions::encode($mf_id));
	die();
}

$supplier = ( isset($_SESSION['pmSup']) && !empty($_SESSION['pmSup']) ) ? $_SESSION['pmSup'] : '';
$txbMon = ( isset($_SESSION['pmMon']) ) ? $_SESSION['pmMon'] : date('m');
$txbYear = ( isset($_SESSION['pmYear']) ) ? $_SESSION['pmYear'] : date('Y');
$txbMonTo = ( isset($_SESSION['pmMonTo']) ) ? $_SESSION['pmMonTo'] : date('m');
$txbYearTo = ( isset($_SESSION['pmYearTo']) ) ? $_SESSION['pmYearTo'] : date('Y');
$where='';$wherePO='';
if($supplier){
	$where .= ' AND s.supplierID="'.$supplier.'"';
	$wherePO .= ' AND s.supplierID="'.$supplier.'"';
}
if($txbMon && $txbYear && $txbMonTo && $txbYearTo){
	$where .=' AND ( LEFT(mrp_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(mrp_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'") ';
	$wherePO .=' AND ( LEFT(po_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(po_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'") ';    
}
else if($txbYear && $txbYearTo){
	$where .=' AND (LEFT(mrp_date,4) >= "'.$txbYear.'" AND LEFT(mrp_date,4) <= "'.$txbYear.'")'; 
	$wherePO .=' AND (LEFT(po_date,4) >= "'.$txbYear.'" AND LEFT(po_date,4) <= "'.$txbYear.'")';    
}
else if($txbMon && $txbYear){
	$where .=' AND ( LEFT(mrp_date,7) >= "'.$txbYear.'-'.$txbMon.'") ';
	$wherePO .=' AND ( LEFT(po_date,7) >= "'.$txbYear.'-'.$txbMon.'") ';
}
else if($txbMonTo && $txbYearTo){
	$where .=' AND ( LEFT(mrp_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'") ';
	$wherePO .=' AND ( LEFT(po_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'") ';
}

$arrItem = array();

//Dropdown Year
$arrDateFromTo=array();
$qCanvasDate = $db->select('material_reference_price','DISTINCT LEFT(mpr_date,4) as pdate',array('item'=>$item,'brand'=>$brand,'unit'=>$unit),' ORDER BY mrp_date');
while($rCD = $db->fetch_array($qCanvasDate)):
	$arrDateFromTo[$rCD['pdate']]=$rCD['pdate'];
endwhile;
$qPurchaseDate = $db->select('po_item pi, po p','DISTINCT LEFT(po_date,4) as pdate',array('item'=>$item,'brand'=>$brand,'unit'=>$unit),'AND p.po_id=pi.po_id ORDER BY po_date');
while($rPD = $db->fetch_array($qPurchaseDate)):
	$arrDateFromTo[$rPD['pdate']]=$rPD['pdate'];
endwhile;


#dropdown supplier
$arrSelSupplier=array();
$qCanvasDate = $db->select('material_reference_price mrp, supplier s','s.supplierID,s.name',array('item'=>$item,'brand'=>$brand,'unit'=>$unit),'AND s.supplierID=mrp.supplierID GROUP BY s.supplierID,s.name ORDER BY s.name');
while($rCD = $db->fetch_array($qCanvasDate)):
	$arrSelSupplier[$rCD['supplierID']]=$rCD['name'];
endwhile;
$qPurchaseSupplier = $db->select('po_item pi, po p, supplier s','s.supplierID,s.name',array('item'=>$item,'brand'=>$brand,'unit'=>$unit),'AND s.supplierID=p.supplierID AND p.po_id=pi.po_id GROUP BY s.supplierID,s.name ORDER BY s.name');
while($rPS = $db->fetch_array($qPurchaseSupplier)):
	$arrSelSupplier[$rPS['supplierID']]=$rPS['name'];
endwhile;


$arrPriceCanvass=array(); $arrPricePO=array();
$arrDate = array();
$arrSupplier = array();
$qItmList = $db->select('material_reference_price mrp LEFT JOIN supplier s ON mrp.supplierID = s.supplierID','*',array('item'=>$item,'brand'=>$brand,'unit'=>$unit),$where.' ORDER BY mrp_date DESC, s.name');
while($rL = $db->fetch_array($qItmList)):
	if($rL['mrp_date']){
		$arrSupplier[$rL['supplierID']]=$rL['name'];
		$arrDate[$rL['mrp_date']]=$rL['mrp_date'];
		$arrPriceCanvass[$rL['mrp_date']][$rL['supplierID']] = $rL['price'];
	}
endwhile;
$qItmPO = $db->select('po_item pi, po p,supplier s','po_date,p.supplierID,s.name,cost',array('item'=>$item,'brand'=>$brand,'unit'=>$unit),'AND p.supplierID=s.supplierID AND p.po_id=pi.po_id'.$wherePO.' GROUP BY po_date,s.supplierID,cost ORDER BY s.name');
#echo $db->last_query;
while($rIP = $db->fetch_array($qItmPO)):
	if($rIP['po_date']){
		$arrSupplier[$rIP['supplierID']]=$rIP['name'];
		$arrDate[$rIP['po_date']]=$rIP['po_date'];
		$arrPricePO[$rIP['po_date']][$rIP['supplierID']] = $rIP['cost'];
	}
endwhile;

sort($arrDate);
asort($arrSelSupplier);
/*echo '<pre>';
print_r($arrSelSupplier);
echo '</pre>';*/
$yrFrom='';$yrTo='';
foreach($arrDateFromTo as $dt):
	if($yrFrom=="")
		$yrFrom = $dt;
	$yrTo = $dt;
endforeach;

$qItmLst = $db->select('material_reference_price','*',array('item'=>$item,'brand'=>$brand,'unit'=>$unit),$where.'ORDER BY mrp_date DESC');

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Material Reference</title>
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Material Reference</h2>
		</div>
		<div class="box-content">
			<form method="post" enctype="multipart/form-data">
				<table width="100%">
					<tr>
						<td width="38%" bgcolor="#f8f8f8" align="center" valign="top">
							<table align="center">
								<tr>
									<td>
										<?php
										$fileName = $db->getValue('images_material','name',array('mf_id'=>$mf_id));
										$file = '../img_material/blank-pic.jpg';
										if($fileName){
										$file = file_exists('../img_material/'.$fileName) ? '../img_material/'.$fileName : '../img_material/blank-pic.jpg';
										}
										?>
										<a id="adc" href="#" class="thickbox" onclick="showThis(this.id,'admin-material-reference-image.php?mf_id=<?php echo functions::encode($mf_id)?>','Item Image Preview','1')"><img height="400" width="400" src="<?php echo $file?>"></a>
									</td>
								</tr>
							</table><br>
							<table class="table table-bordered" border="0" style="font-size:12px;" >
								<tr>
									<td width="35%">Code:</td>
									<td><?php echo $code?></td>
								</tr>
								<tr>
									<td>Category:</td>
									<td><?php echo $cat?></td>
								</tr>
								<tr>
									<td>Classification:</td>
									<td><?php echo $class?></td>
								</tr>
								<tr>
									<td>Type:</td>
									<td><?php echo $type?></td>
								</tr>
								<tr>
									<td>Item:</td>
									<td><?php echo nl2br($item)?></td>
								</tr>
								<tr>
									<td>Description:</td>
									<td><?php echo nl2br($itemDesc)?></td>
								</tr>
								<tr>
									<td>Size:</td>
									<td><?php echo nl2br($size)?></td>
								</tr>
								<tr>
									<td>Unit:</td>
									<td><?php echo $unit?></td>
								</tr>
								<tr>
									<td>Brand:</td>
									<td><?php echo $brand?></td>
								</tr>
								<tr>
									<td>CAD Detail:</td>
									<td><?php echo $cad;?></td>
								</tr>
								<tr>
									<td>Engineering Standards:</td>
									<td><?php echo $engr_standard;?></td>
								</tr>
								<tr>
									<td>Specifications:</td>
									<td><?php echo $specification;?></td>
								</tr>
								<tr>
									<td>DUPA:</td>
									<td><?php echo $dupa;?></td>
								</tr>
								<tr>
									<td>Product Brochure:</td>
									<td><?php echo $product_brochure;?></td>
								</tr>
								<tr>
									<td></td>
									<td><a class="btn btn-warning btn-small" title="Modify this Item" data-rel="tooltip" href="admin-material-reference-edit-detail.php?mf_id=<?php echo functions::encode($mf_id)?>">Modify</a></td>
								</tr>
							</table>
						</td>
						<td>&nbsp;</td>
						<td valign="top" align="center">
							<div align="right">
								<a id="supList" href="#" class="btn btn-small btn-info thickbox" onclick="showThis(this.id,'admin-material-reference-detail-supplier.php?','Supplier List','1')">Supplier List</a>
								<a id="barcodePrint" href="#" class="btn btn-small btn-info thickbox" onclick="showThis(this.id,'admin-material-reference-detail-barcode.php?mf_id=<?php echo functions::encode($mf_id)?>','Supplier List','1')">Print Barcode</a>
								
							</div>
							<table width="100%">
								<tr>
									<td align="center" valign="top">
										<table width="100%" border="0">
											<tr>
												<th width="23%" height="40">&nbsp;</th>
												<th width="28%">&nbsp;</th>
												<th width="17%"></th>
											</tr>
											<tr><td colspan="3" align="center"><h3>Price History</h3></td></tr>
											<tr>
												<td>
													<div align="center"> 
														<div align="center"><strong>FROM</strong></div>
														<select name="bdYear" id="bdYear" style="width:90px;">
															<option value="">All Year</option>
															<?php for($y=$yrTo;$y>=$yrFrom;$y--):?>
															<option value="<?php echo $y;?>" <?php if($txbYear==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
															<?php endfor;?>
														</select>
														<select name="bdMon" id="bdMon" style="width:95px;">
															<option value="">All Month</option>
															<option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
															<option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
															<option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
															<option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
															<option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
															<option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
															<option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
															<option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
															<option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
															<option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
															<option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
															<option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
														</select>
													</div><br>
													<div align="center">
														<div align="center"><strong>TO</strong></div>
														<select name="bdYearTo" id="bdYearTo" style="width:90px;">
															<option value="">All Year</option>
															<?php for($y=$yrTo;$y>=$yrFrom;$y--):?>
															<option value="<?php echo $y;?>" <?php if($txbYearTo==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
															<?php endfor;?>
														</select>
														<select name="bdMonTo" id="bdMonTo" style="width:95px;">
															<option value="">All Month</option>
															<option value="01" <?php if($txbMonTo=='01')echo 'selected="selected"';?>>Jan</option>
															<option value="02" <?php if($txbMonTo=='02')echo 'selected="selected"';?>>Feb</option>
															<option value="03" <?php if($txbMonTo=='03')echo 'selected="selected"';?>>Mar</option>
															<option value="04" <?php if($txbMonTo=='04')echo 'selected="selected"';?>>Apr</option>
															<option value="05" <?php if($txbMonTo=='05')echo 'selected="selected"';?>>May</option>
															<option value="06" <?php if($txbMonTo=='06')echo 'selected="selected"';?>>Jun</option>
															<option value="07" <?php if($txbMonTo=='07')echo 'selected="selected"';?>>Jul</option>
															<option value="08" <?php if($txbMonTo=='08')echo 'selected="selected"';?>>Aug</option>
															<option value="09" <?php if($txbMonTo=='09')echo 'selected="selected"';?>>Sep</option>
															<option value="10" <?php if($txbMonTo=='10')echo 'selected="selected"';?>>Oct</option>
															<option value="11" <?php if($txbMonTo=='11')echo 'selected="selected"';?>>Nov</option>
															<option value="12" <?php if($txbMonTo=='12')echo 'selected="selected"';?>>Dec</option>
														</select>
													</div>
												</td>
												<td align="center">
													<div align="left" style="padding-top: 12px;">
														<select name="selSupplier" id="selSupplier" data-rel="chosen" style="width:430px;">
															<option value="">-- All Supplier --</option>
															<?php foreach($arrSelSupplier as $supID => $supName):
															?>
															<option value="<?php echo functions::encode($supID)?>" <?php if($supID==$supplier)echo 'selected="selected"';?>><?php echo ucwords(strtolower($supName));?></option>
															<?php endforeach;?>
														</select>
													</div>
												</td>
												<td style="padding-top: 10px;">
													<div align="center">
														<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small">
														<a id="adPrice" href="#" class="btn btn-small btn-primary thickbox" onclick="showThis(this.id,'admin-material-reference-price-manage.php?mf_id=<?php echo functions::encode($mf_id)?>','Price History Add')">Add Price</a>
													</div>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td><br>
										<table width="80%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-striped table-hover" style="font-size:12px;">
											<thead>
												<tr style="background-color:#CCC">
													<th width="20%"><div align="center">Date</div></th>
													<th width="50%">Supplier</th>
													<th width="15%"><div align="center">Purchase Price</div></th>
													<th width="15%"><div align="center">Canvass Price</div></th>
												</tr>
											</thead>
											<tbody>
												<?php
												$countRow=0;
												$arrPrices=array();
												foreach($arrDate as $priceDate):
													foreach($arrSupplier as $suppID => $suppKey):
														$countRow++;
														#$itemID = $rIL['mrp_id'];
														$itemID=1;
														$price_canvass = isset($arrPriceCanvass[$priceDate][$suppID]) ? $arrPriceCanvass[$priceDate][$suppID] : 0;
														$price_purchase = isset($arrPricePO[$priceDate][$suppID]) ?  $arrPricePO[$priceDate][$suppID] : 0;
														if($price_canvass)
															$arrPrices[]=$price_canvass;
														if($price_purchase)
															$arrPrices[]=$price_purchase;
														if($price_canvass || $price_purchase){
												?>
												<tr>
													<td height="25px"><div align="center"><?php echo functions::datearr($priceDate)?></div></td>
													<td><div align="left"><a id="vw<?php echo $countRow?>" class="thickbox" style="cursor:pointer;" title="Supplier Detail" data-rel="tooltip" onclick="showThis(this.id,'supplier_view.php?sid=<?php echo functions::encode($suppID);?>','Supplier Detail','1')"><?php echo $suppKey; ?></a></div></td>
													<td><div align="center"><?php echo functions::formatMoney($price_purchase);?></div></td>
													<td><div align="center"><a id="editPrice<?php echo $countRow?>" class="thickbox" style="cursor:pointer;" title="Modify this Price" data-rel="tooltip" onclick="showThis(this.id,'admin-material-reference-price-manage.php?mf_id=<?php echo functions::encode($mf_id)?>&dte=<?php echo functions::encode($priceDate)?>&supid=<?php echo functions::encode($suppID)?>','Details')"><?php echo functions::formatMoney($price_canvass); ?></a></div></td>
												</tr>
												<?php }
													endforeach;
												endforeach;?>
												<?php if($countRow==0){?>
												<tr height="40px"><td colspan="4"><div align="center">----- Nothing to Report ---</div></td></tr>
												<?php }?>
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td>
										<div style="font-size:14px;">
										<?php
										$high = (count($arrPrices)) ? max($arrPrices) : 0;
										$low = (count($arrPrices)) ? min($arrPrices) : 0;
										echo ( $high ) ? '<br><br><strong>Highest Price: '. functions::formatMoney($high) .'</strong>': '';
										echo ( $low ) ? '<br><br><strong>Lowest Price: '. functions::formatMoney($low) .'</strong>': '';
										?>
										</div>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table><br><br>
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
<!-- end: JavaScript-->
</body>
</html>