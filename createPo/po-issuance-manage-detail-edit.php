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

$rm = (isset($_REQUEST['rm']) && !empty($_REQUEST['rm']) ) ? functions::decode($_REQUEST['rm']) : 0;
$pos_id = (isset($_REQUEST['id']) && !empty($_REQUEST['id']) ) ? functions::decode($_REQUEST['id']) : 0;
$posd_id = ( isset($_REQUEST['posd']) && !empty($_REQUEST['posd']) ) ? functions::decode($_REQUEST['posd']) : '';
$po_id = ( isset($_REQUEST['poid']) && !empty($_REQUEST['poid']) ) ? functions::decode($_REQUEST['poid']) : '';
#$is_received = $db->getValue('po','count(*)',array('po_id'=>$po_id,'received'=>1));

$txpono = $db->getValue('po','po_no',array('po_id'=>$po_id));
$ref_id = $db->getValue('po','ref_id',array('po_id'=>$po_id));
$is_received = $db->getValue('po_receive','count(*)',array('ref_id'=>$ref_id));
$q = $db->select('po_issuance','*',array('pos_id'=>$pos_id));
$r = $db->fetch_array($q);
if($rm){
	$db->delete('po_issuance_details',array('pos_id'=>$pos_id,'po_id'=>$rm));
	$_SESSION['notif_delt']='Purchase Order Removed!';
	functions::sendTo(functions::pageName().'?id='.functions::encode($pos_id));
	die();
}
if( isset($_POST['btnSearch']) ){
	$txpono = ( isset($_POST['txpono']) && !empty($_POST['txpono']) ) ? $_POST['txpono'] : '';
	$po_id = $db->getValue('po','po_id',array('po_no'=>$txpono));
	if($po_id){
		if( $posd_id = $db->getValue('po_issuance_details','posd_id',array('pos_id'=>$pos_id,'po_id'=>$po_id)) ){
			functions::sendTo(functions::pageName().'?id='.functions::encode($pos_id).'&poid='.functions::encode($po_id).'&posd='.functions::encode($posd_id));
			die();
		}
		else{
			functions::sendTo(functions::pageName().'?id='.functions::encode($pos_id).'&poid='.functions::encode($po_id));
			die();
		}
	}
	else{
		functions::say('Invalid P.O. Number!');
		functions::sendTo('po-issuance-manage-detail.php?id='.functions::encode($pos_id));
		die();
	}
}

if( isset($_POST['btnAdd']) ){
	$txpono = ( isset($_POST['txpono']) && !empty($_POST['txpono']) ) ? $_POST['txpono'] : '';
	$po_id = $db->getValue('po','po_id',array('po_no'=>$txpono));
	//if( $db->getValue('po_issuance_details','count(*)',array('pos_id'=>$pos_id,'po_id'=>$po_id))==0 ){
	if( $pos_id && $po_id ){
		$posd_id = $db->insert('po_issuance_details',array('pos_id'=>$pos_id,'po_id'=>$po_id));
		if($posd_id){
			$q = $db->select('po p, po_item poi','*',array('p.po_id'=>$po_id),'AND p.po_id=poi.po_id');
			#echo $db->last_query;
			while($r = $db->fetch_array($q)):
				$issued = $db->getValue('po_issuance_item','sum(qty_issue)',array('po_item_id'=>$r['po_item_id']));
				$delivered = $r['qty_delivered'];
				$remaining = $delivered - $issued;
				if($remaining)
					$db->insert('po_issuance_item',array('posd_id'=>$posd_id,'pos_id'=>$pos_id,'po_id'=>$po_id,'po_item_id'=>$r['po_item_id'],'qty_issue'=>$remaining));
			endwhile;
			$_SESSION['notif_add']='Purchase Order Added!';
			functions::sendTo(functions::pageName().'?id='.functions::encode($pos_id).'&poid='.functions::encode($po_id).'&posd='.functions::encode($posd_id));
			die();
		}
	}
	else{
		functions::say('PO Already Exist!');
		functions::sendTo(functions::pageName().'?id='.functions::encode($pos_id));
		die();
	}
}
if( isset($_POST['btnSave']) ){
	$_SESSION['notif_add']='Item successfully updated!';
	functions::sendTo('po-issuance-manage-detail.php?id='.functions::encode($pos_id));
	die();
}
$disableColor='#CCC';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>P.O. Issuance Manage Details</title>
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
	<style>.tdSpace{padding: 0px 0px 0px 10px;}</style>
	<style>.amnt{padding-right: 12px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Issuance Details</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post">
					<table width="90%" border="0" style="font-size:12px;">
						<tr>
							<td>
								<div align="left">
									P.O.<input type="text" name="txpono" id="txpono" value="<?php echo $txpono; ?>">
									<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-info btn-small">
									<?php if($txpono && empty($posd_id)){ if($is_received){?><input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-success btn-small"><?php }else{echo '&nbsp;&nbsp;&nbsp;&nbsp;P.O. has not yet received!&nbsp;&nbsp;&nbsp;&nbsp;';} } ?>
									<a href="po-issuance-manage-detail.php?id=<?php echo functions::encode($pos_id) ?>" class="btn btn-danger btn-small" title="Cancel modification">Cancel</a>
								</div>
								<?php if($posd_id){?>

								<table width="100%" border="1">
									<thead>
										<tr style="background-color:#CCC">
											<th>DESCRIPTION</th>
											<th>UOM</th>
											<th width="15%">UNIT PRICE</th>
											<th height="30">QTY</th>
											<th width="10%">ISSUE</th>
											<th width="5%">&nbsp;</th>
										</tr>
									</thead>
									<tbody>
									<?php
									$total_cost=0;
									$po_id = $db->getValue('po_issuance_details','po_id',array('posd_id'=>$posd_id));
									$qp = $db->select('po_item','*',array('po_id'=>$po_id));
									while($rp = $db->fetch_array($qp)):
										$cost = $rp['cost'] * $rp['quantity'];
										$total_cost += $cost;
										$qty = $rp['qty_delivered'];
										$total_issuance = $db->getValue('po_issuance_item','sum(qty_issue)',array('po_item_id'=>$rp['po_item_id']));
										#echo $db->last_query;
										$qps = $db->select('po_issuance_item','*',array('posd_id'=>$posd_id,'po_item_id'=>$rp['po_item_id']));
										$rps = $db->fetch_array($qps);
										$posi_id = $rps['posi_id'];
										$qty_issue = $rps['qty_issue'];
										$qty = $rp['qty_delivered'] - ($total_issuance-$qty_issue);

										?>
										<tr id="tr<?php echo functions::encode($rp['po_item_id'])?>" style="<?php if (empty($posi_id))echo 'background-color:'.$disableColor; ?>">
											<td class="tdSpace"><div><?php echo $rp['item'] ?></div></td>
											<td><div align="center"><?php echo $rp['unit'] ?></div></td>
											<td class="amnt"><div align="right"><?php echo functions::formatMoney($rp['cost']);?></div></td>
											<td><div align="center"><?php echo number_format($qty);?></div></td>
											<td><div align="center" style="padding-top:6px;"><input type="number" name="selItm<?php echo functions::encode($rp['po_item_id']);?>" id="selItm<?php echo functions::encode($rp['po_item_id']);?>" onkeyup="sve('<?php echo functions::encode($rp['po_item_id'])?>',this.value,this.max)" value="<?php echo $qty_issue ?>" onChange="sve('<?php echo functions::encode($rp['po_item_id'])?>',this.value,this.max)" value="<?php echo $qty_issue ?>" min="0" max="<?php echo $qty ?>" step=".1" style="width:40px;font-size:12px;" <?php if (empty($posi_id))echo 'disabled' ?> required></div></td>
											<td><div align="center"><input type="checkbox" id="chk<?php echo functions::encode($rp['po_item_id'])?>" name="chk<?php echo functions::encode($rp['po_item_id'])?>" onClick="sveChk('<?php echo functions::encode($rp['po_item_id'])?>',document.getElementById('selItm<?php echo functions::encode($rp['po_item_id']);?>').value)" <?php if($posi_id){echo 'checked';} ?>></div></td>
										</tr>
									<?php endwhile;?>
										<tr>
											<td colspan="5">&nbsp;</td>
											<td><div align="center" style="padding:6px;"><input type="submit" class="btn btn-success btn-mini" name="btnSave" id="btnSave" value="Save"></div></td>
									</tbody>
								</table>

								<?php }else if($po_id){ ?>
								<table width="100%" border="1">
									<thead>
										<tr style="background-color:#CCC">
											<th height="30">QTY</th>
											<th>DESCRIPTION</th>
											<th>UOM</th>
											<th width="15%">UNIT PRICE</th>
											<th width="15%">TOTAL COST</th>
										</tr>
									</thead>
									<tbody>
									<?php
									$total_cost=0;
									$qp = $db->select('po_item','*',array('po_id'=>$po_id));
									while($rp = $db->fetch_array($qp)):
										$cost = $rp['cost'] * $rp['quantity'];
										$total_cost += $cost;
										$qty = $rp['qty_delivered'];
										?>
										<tr>
											<td><div align="center"><?php echo number_format($rp['qty_delivered']);?></div></td>
											<td class="tdSpace"><div><?php echo $rp['item'] ?></div></td>
											<td><div align="center"><?php echo $rp['unit'] ?></div></td>
											<td class="amnt"><div align="right"><?php echo functions::formatMoney($rp['cost']);?></div></td>
											<td class="amnt"><div align="right"><?php echo functions::formatMoney($cost) ?></div></td>
										</tr>
									<?php endwhile;?>
										<tr>
											<td colspan="5" style="padding:7px;"><div align="right"> Total <strong><?php echo functions::formatMoney($total_cost); ?></strong></div></td>
										</tr>
									</tbody>
								</table>
								<?php } ?>
							</td>
						</tr>
					</table>
					<div id="res"></div>
				</form>
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
<script src="../js/wxhBox.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script type="text/javascript">//$(document).ready(function(){$('#pushmenu').click(function(){$.post( "sidebarshowhide.php", { trig: "1"})});});</script>
<script type="text/javascript">
$(document).ready(function(){});
</script>
<script>
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
function sve(nme,vle,mx) {
	var mx = parseFloat(mx);
	var vle = parseFloat(vle);
	if(vle > mx){
		vle=mx;
		document.getElementById('selItm'+nme).value=vle;
	}
	var strURL="po-issuance-manage-detail-save.php?itmid="+nme+"&qty="+vle+"<?php echo '&posid='.functions::encode($pos_id).'&posd='.functions::encode($posd_id); ?>";
	var req = getXMLHTTP();

	if (req){
		req.onreadystatechange = function() {
			if (req.readyState == 4) {
				// only if "OK"
				if (req.status == 200) {
					//document.getElementById('res').innerHTML=req.responseText;            
				}else{
					alert("Problem while using XMLHTTP:\n" + req.statusText);
				}
			}
		}     
		req.open("GET", strURL, true);
		req.send(null);
	}
}
function sveChk(nme,vle) {
	var strURL="po-issuance-manage-detail-save.php?itmid="+nme+"&qty="+vle+"&chk=1<?php echo '&posid='.functions::encode($pos_id).'&posd='.functions::encode($posd_id); ?>";
	var req = getXMLHTTP();
	var trname = "tr"+nme;
	var chkbox = "chk"+nme;
	var inpt = 'selItm'+nme;
	if(document.getElementById(chkbox).checked == true){
		document.getElementById(trname).style.backgroundColor = "#FFF";
		document.getElementById(inpt).disabled = false;
	}
	else if(document.getElementById(chkbox).checked == false){
		document.getElementById(trname).style.backgroundColor = "#CCC";
		document.getElementById(inpt).disabled = true;
	}
	
	if (req){
		req.onreadystatechange = function() {
			if (req.readyState == 4) {
				// only if "OK"
				if (req.status == 200) {
					//document.getElementById('res').innerHTML=req.responseText;            
				}else{
					alert("Problem while using XMLHTTP:\n" + req.statusText);
				}
			}
		}     
		req.open("GET", strURL, true);
		req.send(null);
	}
}
</script>
<script>
function ask(){
    if(confirm('Do you want to remove this P.O.?'))
        return true;
    else
        return false; 
}
</script>
<?php if(isset($_SESSION['notif_delt'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_delt'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_delt']);} ?>
<?php if(isset($_SESSION['notif_add'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_add'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_add']);} ?>
<!-- end: JavaScript-->
</body>
</html>