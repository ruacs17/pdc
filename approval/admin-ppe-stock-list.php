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
$arr = array();
$item='';
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $db->clean($_REQUEST['startrow']) : 0;
$rowdisplay=40;
$itemDel = (isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ) ? functions::decode($_REQUEST['itemDel']) : 0;
$mf_id='';
$mon='';$txProj = '';$txPayee = '';$txbMon = '';$txbYear = '';$num_record=0;
$loc_id='';
if( isset($_POST['btnViewAll']) ){
	unset($_SESSION['stock_item'],$_SESSION['stock_location']);
	functions::sendTo(functions::pageName());
}
$loc_id = (isset($_REQUEST['selLoc']) && !empty($_REQUEST['selLoc']) ) ? trim($_REQUEST['selLoc']) : "";
$item = (isset($_REQUEST['txItem']) && !empty($_REQUEST['txItem']) ) ? trim($_REQUEST['txItem']) : "";
if( $item || $loc_id ){
	$_SESSION['stock_item']=$item;
	$_SESSION['stock_location']=$loc_id;
	$q = 'SELECT item,unit,brand FROM ppe_material_storage WHERE item LIKE "%'.$db->clean($item).'%" GROUP BY item,unit,brand LIMIT '.$startrow.', '.$rowdisplay;
	$qCount = $db->query('SELECT item,unit,brand FROM ppe_material_storage WHERE item LIKE "%'.$db->clean($item).'%" GROUP BY item,unit,brand');
	$num_record = $db->num_rows();
}
else{
	$q = 'SELECT item,unit,brand FROM ppe_material_storage GROUP BY item,unit,brand LIMIT '.$startrow.', '.$rowdisplay;
	$qCount = $db->query('SELECT item,unit,brand FROM ppe_material_storage GROUP BY item,unit,brand');
	$num_record = $db->num_rows();
}

$arrLocation = array();
if($loc_id)
	$qLoc = $db->select('inhouse_material_storage_location','location',array('imsl_id'=>$loc_id));
else
	$qLoc = $db->select('inhouse_material_storage_location','location',array());
while($rLoc = $db->fetch_array($qLoc)):
$arrLocation[] = $rLoc['location'];
endwhile;

if(count($arr)){
	$rowdisplay=1000;
	$startrow=0;
}
$qList = $db->query($q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>PPE Stock List</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>PPE Stock Add</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="admin-ppe-stock-add.php">Add Stock</a></li>
				<li class="active"><a  href="admin-ppe-stock-list.php" style="opacity:.9">List Stock</a></li>
			</ul>
		</div>
		<div class="box-content">
			<form method="post" action="?">
				<table border="0">
					<tr>
						<td style="padding: 10px 10px 5px 0px"><div align="left"><input type="text" style="width:450px; height:10px;" class="span6 typeahead" name="txItem" id="txItem" value="<?php echo $item;?>"></div></td>
						<td style="padding: 10px 10px 5px 0px">
							<div align="left">
								<select name="selLoc" id="selLoc" style="width:200px;">
									<option value="">--All Location--</option>
									<?php
									$qLocation = $db->select('inhouse_material_storage_location','*',array());
									while($rLoc = $db->fetch_array($qLocation)):
									?>
									<option value="<?php echo $rLoc['imsl_id']?>" <?php if($loc_id==$rLoc['imsl_id'])echo 'selected="selected"';?>><?php echo ucwords(strtolower($rLoc['location']));?></option>
									<?php endwhile;?>
								</select>
							</div>
						</td>
						<td>
							<div align="center">
								<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small">
								<input type="submit" name="btnViewAll" id="btnViewAll" value="View All" class="btn btn-primary btn-small">
							</div>
						</td>
					</tr>
					<tr><td colspan="3"><hr width="100%"></td></tr>
				</table>
				<table id="tblist" class="table <?php if(!isset($_SESSION['notif_stck_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th width="35%">Item</th>
							<th width="8%">Unit</th>
							<th width="10%">Brand</th>
							<?php foreach($arrLocation as $loc):?>
							<th width="10%"><div align="center"><?php echo $loc?></div></th>
							<?php endforeach;?>
							<th width="10%"><div align="center">Total On-Stock</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$totalAmount=0;$count=0;
					while($rList = $db->fetch_array($qList)):
						$count++;
						$mf_id = $db->getValue('material_reference','mf_id',array('item'=>$rList['item'],'unit'=>$rList['unit'],'brand'=>$rList['brand']));
						$least_required = $db->getValue('material_reference','least_required',array('mf_id'=>$mf_id));
					?>
						<tr id="rw<?php echo $mf_id?>">
							<td><?php echo $rList['item']?></td>
							<td><?php echo $rList['unit']?></td>
							<td><?php echo $rList['brand']?></td>
							<?php
							$available=0; $totalAvailable=0;
							foreach($arrLocation as $loc):
								$qty = $db->getValue('ppe_material_storage','sum(quantity)',array('item'=>$rList['item'],'unit'=>$rList['unit'],'brand'=>$rList['brand'],'location'=>$loc));
								$consumed = $db->getValue('ppe_items','sum(quantity-return_qty)',array('item'=>$rList['item'],'unit'=>$rList['unit'],'brand'=>$rList['brand'],'location'=>$loc));
								$available = $qty - $consumed;
								$totalAvailable += $available;
								$available_disp=0; $totalAvailable_disp=0;
								if($available)
									$available_disp = (functions::isfloat($available)) ? functions::formatMoney($available) : number_format($available);
								if($totalAvailable)
									$totalAvailable_disp = (functions::isfloat($totalAvailable)) ? functions::formatMoney($totalAvailable) : number_format($totalAvailable);

								$bgColor =  ($least_required > $available) ?  'bgcolor="#fcb77b"' : '';
							?>
							<td <?php echo $bgColor?>>
								<div align="center"><?php #echo $consumed;?>
									<a id="s<?php echo $count++?>" class="thickbox" style="cursor:pointer;" title="Manage this Item" data-rel="tooltip" onclick="showThis(this.id,'admin-ppe-material-stock-manage.php?mf_id=<?php echo functions::encode($mf_id);?>&loc=<?php echo functions::encode($loc)?>','Details')"><?php echo $available_disp?></a>
								</div>
							</td>
							<?php endforeach;?>
							<td><div align="center"><?php echo $totalAvailable_disp;?></div></td>
						</tr>
					<?php endwhile;
					if($count==0){?>
						<tr>
							<td colspan="<?php echo count($arrLocation) + 5?>"><div align="center">--Nothing to Report--</div></td>
						</tr>
					<?php }?>
					</tbody>
				</table>
				<div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=functions::pageName().'?txItem='.$item.'&selLoc='.$loc_id,$search="txItem");?></div>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxItem').html("");
		$('#msgtxQty').html("");
		$('#msgBdate').html("");
		$('#msgLoc').html("");


		if( $('#txItemNme').val()=="" ){
			$('#msgtxItem').html("Item Required!");
			res=false;
		}
		else if( $('#txQty').val()=="" ){
			$('#msgtxQty').html("Required!");
			$('#txQty').focus();
			res=false;
		}
		else if( $('#bdMon').val()=="" || $('#bdDay').val()=="" || $('#bdYear').val()==""){
			$('#msgBdate').html("Date Required!");
			res=false;
		}
		else if( $('#selLoc').val()=="" ){
			$('#msgLoc').html("Required!");
			$('#selLoc').focus();
			res=false;
		}
		else
			res=true;

		return res;
	});
});
</script>
<!-- end: JavaScript-->
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
  
function searchMaterial(srch) {   
	var strURL="material_reference_list.php?srch="+srch;
	var req = getXMLHTTP();

	if (req) {
		req.onreadystatechange = function() {
			if (req.readyState == 4) {
				// only if "OK"
				if (req.status == 200) {
					document.getElementById('materialView').innerHTML=req.responseText;
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
<?php if(isset($_SESSION['notif_stck_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_stck_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_stck_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_stck_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_stck_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_stck_list']);} ?>
</body>
</html>