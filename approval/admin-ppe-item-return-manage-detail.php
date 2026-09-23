<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/MoneytoWords.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$ppe_id = (isset($_REQUEST['ppe_id']) && !empty($_REQUEST['ppe_id']) ) ? functions::decode($_REQUEST['ppe_id']) : 0;

$item='';$quantity='';$unit='';$brand=''; $location='';$return_qty='';
$issued_count='';$return_date=date('Y-m-d');$return_remark='';
$editTrue=0;$txItmEdt='';$available=0;
if( isset($_REQUEST['txItmEdt']) && !empty($_REQUEST['txItmEdt']) ){
	$txItmEdt = functions::decode($_REQUEST['txItmEdt']);
	$editTrue = $db->getValue('ppe_items','count(*)',array('ppei_id'=>$txItmEdt));
	$qvedt = $db->select('ppe_items','*',array('ppei_id'=>$txItmEdt));
	while($rvedt = $db->fetch_array($qvedt)):
		$item=$rvedt['item'];
		$brand=$rvedt['brand'];
		$quantity=$rvedt['quantity'];
		$return_qty = $rvedt['return_qty'];
		$unit=$rvedt['unit'];
		$location=$rvedt['location'];
		$consumed = $db->getValue('ppe_items','sum(quantity-return_qty)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
		$stored = $quantity + $db->getValue('ppe_material_storage','sum(quantity)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
		$available = $stored - $consumed;
		$return_date=$rvedt['return_date'];
		$return_remark=$rvedt['return_remark'];
	endwhile;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>IPPE Manage</title>
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
<?php
if( isset($_POST['btnSave']) ){
	$ppei_id_Edt = ( isset($_POST['txItmEdt']) && !empty($_POST['txItmEdt']) ) ? functions::decode($_POST['txItmEdt']) : 0;
	$return_qty = ( isset($_POST['txQtyReturn']) && !empty($_POST['txQtyReturn']) ) ? trim($_POST['txQtyReturn']) : '';
	$return_date = ( isset($_POST['txDateReturn']) && !empty($_POST['txDateReturn']) ) ? trim($_POST['txDateReturn']) : NULL;
	$return_remark = ( isset($_POST['txReturnRemark']) && !empty($_POST['txReturnRemark']) ) ? trim($_POST['txReturnRemark']) : '';

	if( !empty($ppei_id_Edt) && !empty($return_qty) ){
		$db->update('ppe_items',array('return_qty'=>$return_qty,'return_date'=>$return_date,'return_remark'=>$return_remark),array('ppei_id'=>$ppei_id_Edt,'ppe_id'=>$ppe_id));
		if( $db->getValue('ppe_items','count(*)',array('ppei_id'=>$ppei_id_Edt,'ppe_id'=>$ppe_id),'AND return_qty=quantity') )
			$db->update('ppe_items',array('return_stat'=>'Complete'),array('ppei_id'=>$ppei_id_Edt,'ppe_id'=>$ppe_id));
		else
			$db->update('ppe_items',array('return_stat'=>NULL),array('ppei_id'=>$ppei_id_Edt,'ppe_id'=>$ppe_id));
		$_SESSION['notif_success']='Changes Saved!';
		$_SESSION['notif_detl_list']=$ppei_id_Edt;
		functions::sendTo(functions::pageName().'?ppe_id='.functions::encode($ppe_id));
		die();
	}
	else
		functions::say('Please fill up the form properly!');
}
?>
	<style type="text/css">
	.padParLeft{padding-left:10px;}
	.padAmLeft{padding-left:60px;}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
	<form method="post">
		<div class="row-fluid">
			<div class="box span12">
				<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white edit"></i><span class="break"></span>IPPE Item Manage Return</h2>
				</div>
				<div class="box-content">
					<table width="100%" border="0" align="center" style="font-size: 12px;">
						<tr>
							<td>
								<input type="hidden" name="txItmEdt" id="txItmEdt" value="<?php echo functions::encode($txItmEdt);?>"><br><br>
								<table width="100%" border="0" align="center" class="table table-bordered">
									<tr>
										<td width="25%"><div align="center"><strong>Item</strong></div></td>
										<td width="20%"><div align="center"><strong>Brand</strong></div></td>
										<td width="10%"><div align="center"><strong>Quantity</strong></div></td>
										<td width="12%"><div align="center"><strong>Return</strong></div></td>
										<td width="14%"><div align="center"><strong>Date Return</strong></div></td>
										<td width="8%"><div align="center"><strong>Remark</strong></div></td>
										<td width="9%">&nbsp;</td>
									</tr>
									<tr>
										<td><div align="center"><?php echo ($txItmEdt) ? $item : '-----';?></div></td>
										<td><div align="center"><?php echo ($txItmEdt) ? $brand : '-----';?></td>
										<td><div align="center"><?php echo ($txItmEdt) ? $quantity.' '.$unit : '-----';?></div></td>
										<td>
											<div align="center">
												<?php if($txItmEdt){?>
													<select name="txQtyReturn" id="txQtyReturn" data-rel="chosen" style="width:100px;">
														<option value="">--select--</option>
														<?php for($i=1; $i<=$quantity; $i++):?>
														<option value="<?php echo $i?>" <?php if($return_qty==$i)echo 'selected="selected"';?>><?php echo $i?></option>
														<?php endfor;?>
													</select>
													<span class="help-inline warning" id="msgtxQty" style="font-weight:bold;" name="msgtxQty"></span>
												<?php }else echo '-----';?>
											</div>
										</td>
										<td>
											<div align="center">
												<?php if($txItmEdt){?>
												<a href="javascript:NewCssCal('txDateReturn')">
													<img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
													<input name="txDateReturn" type="text" class="span6 mytextbox" id="txDateReturn" value="<?php echo $return_date?>" style="width: 90px;" readonly>
												</a>
												<span class="help-inline warning" name="msgtxDateReturn" id="msgtxDateReturn" style="font-weight:bold;"></span>
												<?php }else echo '-----';?>
											</div>
										</td>
										<td><div align="center"><?php if($txItmEdt){?><textarea name="txReturnRemark" id="txReturnRemark"><?php echo $return_remark?></textarea><?php }else echo '-----';?></div></td>
										<td>
											<div align="center">
											<?php 
											if($editTrue){
											echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-mini btn-primary">&nbsp;';
											echo '<a href="?ppe_id='.functions::encode($ppe_id).'" class="btn btn-mini">Cancel</a>';
											}?>
											</div>
										</td>
									</tr>
								</table>
								<table width="100%" border="1" style="font-size: 11px;" class="table-hover">
									<thead>
										<tr style="background-color:#CCC;">
											<th width="21%" scope="col"><div align="center">Item</div></th>
											<th width="10%" scope="col"><div align="center">Brand</div></th>
											<th width="7%" scope="col"><div align="center">Borrowed</div></th>
											<th width="7%" scope="col"><div align="center">Returned</div></th>
											<th width="7%" scope="col"><div align="center">No. Of Times Issued</div></th>
											<th width="8%" scope="col"><div align="center">Date Issued</div></th>
											<th width="8%" scope="col"><div align="center">Return Date</div></th>
											<th width="14%" scope="col"><div align="center">Mode of Issuance</div></th>
											<th width="10%" scope="col"><div align="center">Remarks</div></th>
											<th width="8%" scope="col">&nbsp;</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$qItem = $db->select('ppe_items','*',array('ppe_id'=>$ppe_id),'ORDER BY ppei_id');
										while($rItem = $db->fetch_array($qItem)):
										$rID = $rItem['ppei_id'];
										?>
										<tr id="rw<?php echo $rID;?>">
											<td height="30"><div align="center"><?php echo $rItem['item'];?></div></td>
											<td><div align="center"><?php echo $rItem['brand'];?></div></td>
											<td><div align="center"><?php echo $rItem['quantity'];echo ($rItem['unit']) ? ' ('.$rItem['unit'].')' : '';?></div></td>
											<td><div align="center"><?php echo $rItem['return_qty'];?></div></td>
											<td><div align="center"><?php echo $rItem['issued_count'];?></div></td>
											<td><div align="center"><?php echo functions::datearr($rItem['issued_date']);?></div></td>
											<td><div align="center"><?php echo functions::datearr($rItem['return_date']);?></div></td>
											<td><div align="center"><?php echo $rItem['issuance_mode'];?></div></td>
											<td><div align="center"><?php echo $rItem['return_remark'];?></div></td>
											<td>
												<div align="center">
													<a id="edit<?php echo $rID;?>" class="btn btn-mini btn-info" title="Return this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?ppe_id=<?php echo functions::encode($ppe_id);?>&txItmEdt=<?php echo functions::encode($rID);?>"><i class="icon-share-alt"></i></a>
												</div>
											</td>
										</tr>
										<?php endwhile;?>
									</tbody>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
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
$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxDateReturn').html("");
		$('#msgtxQty').html("");

		if( $('#txQtyReturn').val()=="" ){
			$('#msgtxQty').html("Quantity Required!");
			$('#txQtyReturn').focus();
			res=false;
		}
		else if( $('#txDateReturn').val()=="" ){
			$('#msgtxDateReturn').html("Specify Date!");
			$('#txDateReturn').focus();
			res=false;
		}
		else
			res=true;

		return res;
	});
});
function projSel(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo functions::pageName()?>?proj_id="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
}
function getXMLHTTP() { //fuction to return the xml http object
	var xmlhttp=false;
	try{
		xmlhttp=new XMLHttpRequest();
	}
	catch(e){
		try{
			xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch(e){
			try{
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch(e1){
				xmlhttp=false;
			}
		}
	}
	return xmlhttp;
}
function searchMaterial(srch,loc) {   
	var strURL="admin-ppe-item-search.php?srch="+srch+"&loc="+loc+"&txItmEdt=<?php echo functions::encode($txItmEdt)?>&ppe_id=<?php echo functions::encode($ppe_id)?>";
	var req = getXMLHTTP();
	if(req){
		req.onreadystatechange = function() {
			if (req.readyState == 4) {
				// only if "OK"
				if (req.status == 200) {
					document.getElementById('materialView').innerHTML=req.responseText;
				}
				else{
					alert("Problem while using XMLHTTP:\n" + req.statusText);
				}
			}
		}
		req.open("GET", strURL, true);
		req.send(null);
	}
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
<?php if(isset($_SESSION['notif_detl_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_detl_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_detl_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_detl_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
});
</script>
<?php unset($_SESSION['notif_detl_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>