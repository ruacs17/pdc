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
$editTrue=0;
$poidEdt='';
$item='';$itemDesc='';$unit='';$brand='';$class='';$type='';$min='';$cat='';$size='';
$msg='';$code='';
if( isset($_POST['btnAdd']) ){

	$selClass = ( isset($_POST['selClass']) && !empty($_POST['selClass']) ) ? functions::decode($_POST['selClass']) : '';
	$selType = ( isset($_POST['selType']) && !empty($_POST['selType']) ) ? functions::decode($_POST['selType']) : '';
	$txCode = ( isset($_POST['txCode']) && !empty($_POST['txCode']) ) ? trim($_POST['txCode']) : '';
	$txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : '';
	$txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? functions::decode($_POST['txUnit']) : '';
	$txBrand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? functions::decode($_POST['txBrand']) : '';
	$selCat = ( isset($_POST['selCat']) && !empty($_POST['selCat']) ) ? functions::decode($_POST['selCat']) : '';
	$txItemDesc = ( isset($_POST['txItemDesc']) && !empty($_POST['txItemDesc']) ) ? trim($_POST['txItemDesc']) : '';
	$txSize = ( isset($_POST['txSize']) && !empty($_POST['txSize']) ) ? trim($_POST['txSize']) : '';
	$txLeastReq = ( isset($_POST['txLeastReq']) && !empty($_POST['txLeastReq']) ) ? functions::moneyToDouble($_POST['txLeastReq']) : 0;

	if( $txItem && $selClass && $selType){
		if( $txCode && $db->getValue('material_reference','count(*)',array('m_code'=>$txCode)) ){
			$item=$txItem; $unit=$txUnit; $brand=$txBrand;$code=$txCode;$class=$selClass;$type=$selType;$min=$txLeastReq;$cat=$selCat;$itemDesc=$txItemDesc;$size=$txSize;
			$msg='<div align="center" style="background-color:#993300; color:#FFFFFF; font-size:15px;">Code Already Exist!</div>';
		}
		elseif( $db->getValue('material_reference','count(*)',array('lower(item)'=>strtolower($txItem),'lower(unit)'=>strtolower($txUnit),'lower(brand)'=>strtolower($txBrand),'lower(category)'=>strtolower($selCat)))==0 ){
			$ins = $db->insert('material_reference',array('m_code'=>$txCode,'item'=>$txItem,'unit'=>$txUnit,'brand'=>$txBrand,'classification'=>$selClass,'category'=>$selCat,'mdescription'=>$txItemDesc,'msize'=>$txSize,'mtype'=>$selType,'least_required'=>$txLeastReq));
			$_SESSION['notif_id_list']=$ins;
			$_SESSION['notif_success']='New Material Reference Saved!';
			functions::sendTo(functions::pageName());
			die();
		}
		else{
			$item=$txItem; $unit=$txUnit; $brand=$txBrand;$code=$txCode;$cat=$selCat;
			$msg='<div align="center" style="background-color:#993300; color:#FFFFFF; font-size:15px;">Information Already Exist!</div>';
		}
	}
	else
	functions::say('Please fill up the form properly!');
}
if( isset($_POST['btnSave']) ){

	$selClass = ( isset($_POST['selClass']) && !empty($_POST['selClass']) ) ? functions::decode($_POST['selClass']) : '';
	$selType = ( isset($_POST['selType']) && !empty($_POST['selType']) ) ? functions::decode($_POST['selType']) : '';
	$txCode = ( isset($_POST['txCode']) && !empty($_POST['txCode']) ) ? trim($_POST['txCode']) : '';
	$txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : '';
	$txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? functions::decode($_POST['txUnit']) : '';
	$txBrand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? functions::decode($_POST['txBrand']) : '';
	$selCat = ( isset($_POST['selCat']) && !empty($_POST['selCat']) ) ? functions::decode($_POST['selCat']) : '';
	$txItemDesc = ( isset($_POST['txItemDesc']) && !empty($_POST['txItemDesc']) ) ? trim($_POST['txItemDesc']) : '';
	$txSize = ( isset($_POST['txSize']) && !empty($_POST['txSize']) ) ? trim($_POST['txSize']) : '';
	$txLeastReq = ( isset($_POST['txLeastReq']) && !empty($_POST['txLeastReq']) ) ? functions::moneyToDouble($_POST['txLeastReq']) : 0;

	$txOldCode = ( isset($_POST['txOldCode']) && !empty($_POST['txOldCode']) ) ? functions::decode($_POST['txOldCode']) : '';
	$txOldItem = ( isset($_POST['txOldItem']) && !empty($_POST['txOldItem']) ) ? functions::decode($_POST['txOldItem']) : '';
	$txOldUnit = ( isset($_POST['txOldUnit']) && !empty($_POST['txOldUnit']) ) ? functions::decode($_POST['txOldUnit']) : '';
	$txOldBrand = ( isset($_POST['txOldBrand']) && !empty($_POST['txOldBrand']) ) ? functions::decode($_POST['txOldBrand']) : '';
	$txOldClass = ( isset($_POST['txOldClass']) && !empty($_POST['txOldClass']) ) ? functions::decode($_POST['txOldClass']) : '';
	$txOldType = ( isset($_POST['txOldType']) && !empty($_POST['txOldType']) ) ? functions::decode($_POST['txOldType']) : '';
	$txOldCat = ( isset($_POST['txOldCat']) && !empty($_POST['txOldCat']) ) ? functions::decode($_POST['txOldCat']) : '';
	$allowed=1;
	if( $txItem && $txUnit && $mf_id){
		if($txCode != $txOldCode){
			if( $db->getValue('material_reference','count(*)',array('m_code'=>$txCode)) ){
				$allowed=0;
				$item=$txItem; $unit=$txUnit; $brand=$txBrand;$code=$txCode;$cat=$selCat;$itemDesc=$txItemDesc;$size=$txSize;
				$msg='<div align="center" style="background-color:#993300; color:#FFFFFF; font-size:15px;">Code '.$txCode.' Already Exist!</div>';
			}
		}
		if($allowed){
			$db->update('material_reference',array('m_code'=>$txCode,'item'=>$txItem,'unit'=>$txUnit,'brand'=>$txBrand,'classification'=>$selClass,'category'=>$selCat,'mdescription'=>$txItemDesc,'msize'=>$txSize,'mtype'=>$selType,'least_required'=>$txLeastReq),array('mf_id'=>$mf_id));
			if( ($txOldItem != $txItem) || ($txOldUnit != $txUnit) || ($txOldBrand != $txBrand) ){
				$db->update('po_item',array('item'=>$txItem,'unit'=>$txUnit,'brand'=>$txBrand),array('item'=>$txOldItem,'unit'=>$txOldUnit,'brand'=>$txOldBrand));
				$db->update('inhouse_material_item',array('item'=>$txItem,'unit'=>$txUnit,'brand'=>$txBrand),array('item'=>$txOldItem,'unit'=>$txOldUnit,'brand'=>$txOldBrand));

				$db->update('ppe_material_storage',array('item'=>$txItem,'unit'=>$txUnit,'brand'=>$txBrand),array('item'=>$txOldItem,'unit'=>$txOldUnit,'brand'=>$txOldBrand));
				$db->update('ppe_items',array('item'=>$txItem,'unit'=>$txUnit,'brand'=>$txBrand),array('item'=>$txOldItem,'unit'=>$txOldUnit,'brand'=>$txOldBrand));
				$db->update('medicine_request_detail',array('item'=>$txItem,'unit'=>$txUnit,'brand'=>$txBrand),array('item'=>$txOldItem,'unit'=>$txOldUnit,'brand'=>$txOldBrand));
				$db->update('medicine_distribution_detail',array('item'=>$txItem,'unit'=>$txUnit,'brand'=>$txBrand),array('item'=>$txOldItem,'unit'=>$txOldUnit,'brand'=>$txOldBrand));
			}
			$db->update('inhouse_material_storage',array('item'=>$txItem,'unit'=>$txUnit,'brand'=>$txBrand),array('item'=>$txOldItem,'unit'=>$txOldUnit,'brand'=>$txOldBrand));
			$_SESSION['notif_success']='Material Reference Saved!';
			functions::sendTo(functions::pageName().'?mf_id='.functions::encode($mf_id));
			die();
		}
	}
}

if( $mf_id ){
	$editTrue = $db->getValue('material_reference','count(*)',array('mf_id'=>$mf_id));
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
}

if($min){
	$min = (functions::isfloat($min)) ? functions::formatMoney($min) : number_format($min);
}
$qItem = $db->query('SELECT DISTINCT item FROM material_reference ORDER BY item');
$namesItem='';
while($rItem=$db->fetch_array($qItem)):
	$string = preg_replace("/'/",'"',$rItem['item']);
	$namesItem .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesItem .= '"--"';

$qSize = $db->query('SELECT DISTINCT msize FROM material_reference ORDER BY msize');
$namesSize='';
while($rSize=$db->fetch_array($qSize)):
	$string = preg_replace("/'/",'"',$rSize['msize']);
	$namesSize .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesSize .= '"--"';
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
		<?php if($mf_id){?>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="admin-material-reference-detail.php?mf_id=<?php echo functions::encode($mf_id)?>">More Detail</a></li>
				<li class="active"><a href="admin-material-reference-add.php?mf_id=<?php echo functions::encode($mf_id)?>">Material Reference</a></li>
			</ul>
		</div>
		<?php }?>
		<div class="box-content">
			<form method="post">
				<input type="hidden" name="txOldCode" id="txOldCode" value="<?php echo functions::encode($code)?>">
				<input type="hidden" name="txOldItem" id="txOldItem" value="<?php echo functions::encode($item)?>">
				<input type="hidden" name="txOldUnit" id="txOldUnit" value="<?php echo functions::encode($unit)?>">
				<input type="hidden" name="txOldBrand" id="txOldBrand" value="<?php echo functions::encode($brand)?>">
				<input type="hidden" name="txOldClass" id="txOldClass" value="<?php echo functions::encode($class)?>">
				<input type="hidden" name="txOldType" id="txOldType" value="<?php echo functions::encode($type)?>">
				<input type="hidden" name="txOldCat" id="txOldCat" value="<?php echo functions::encode($cat)?>">
				<table width="100%" border="0" align="center" class="table table-bordered">
					<tr bgcolor="#f8f8f8">
						<th scope="col"><div align="right">Category</div></th>
						<td>
							<div align="left">
								<select name="selCat" id="selCat" style="width:300px;" required>
									<option value="">-- Select --</option>
									<?php
									$qType = $db->query('SELECT * FROM material_type WHERE type_name="category" ORDER BY type_desc ');
									while($rType = $db->fetch_array($qType)):
									?>
									<option value="<?php echo functions::encode($rType['type_desc'])?>" <?php if($cat==$rType['type_desc']){echo 'selected="selected"';}?>><?php echo $rType['type_desc']?></option>
									<?php endwhile;?>
								</select>
								<a id="mngCat" class="thickbox" style="cursor:pointer" onclick="showThis(this.id,'admin-material-reference-add-cat.php?','Manage Material Classification','1')" title="Manage Material Classification" data-rel="tooltip"><i class="halflings-icon plus-sign"></i></a>
								<span class="help-inline warning" id="MsgSelCat" style="font-weight:bold;" name="MsgSelCat"></span>
							</div>
						</td>
					</tr>
					<tr bgcolor="#f8f8f8">
						<th scope="col"><div align="right">Material Classification</div></th>
						<td>
							<div align="left">
								<select name="selClass" id="selClass" style="width:300px;" required>
									<option value="">-- Select --</option>
									<?php
									$qType = $db->query('SELECT * FROM material_type WHERE type_name="classification" ORDER BY type_desc ');
									while($rType = $db->fetch_array($qType)):
									?>
									<option value="<?php echo functions::encode($rType['type_desc'])?>" <?php if($class==$rType['type_desc']){echo 'selected="selected"';}?>><?php echo $rType['type_desc']?></option>
									<?php endwhile;?>
								</select>
								<a id="mngClass" class="thickbox" style="cursor:pointer" onclick="showThis(this.id,'admin-material-reference-add-class.php?','Manage Material Classification','1')" title="Manage Classification" data-rel="tooltip"><i class="halflings-icon plus-sign"></i></a>
								<span class="help-inline warning" id="MsgSelClass" style="font-weight:bold;" name="MsgSelClass"></span>
							</div>
						</td>
					</tr>
					<tr bgcolor="#f8f8f8">
						<th scope="col"><div align="right">Material Type</div></th>
						<td>
							<div align="left">
								<select name="selType" id="selType" style="width:300px;" required>
									<option value="">-- Select --</option>
									<?php
									$qType = $db->query('SELECT * FROM material_type WHERE type_name="type" ORDER BY type_desc ');
									while($rType = $db->fetch_array($qType)):
									?>
									<option value="<?php echo functions::encode($rType['type_desc'])?>" <?php if($type==$rType['type_desc']){echo 'selected="selected"';}?>><?php echo $rType['type_desc']?></option>
									<?php endwhile;?>
								</select>
								<a id="mngType" class="thickbox" style="cursor:pointer" onclick="showThis(this.id,'admin-material-reference-add-type.php?','Manage Material Type','1')" title="Manage Material Type" data-rel="tooltip"><i class="halflings-icon plus-sign"></i></a>
								<span class="help-inline warning" id="MsgSelType" style="font-weight:bold;" name="MsgSelType"></span>
							</div>
						</td>
					</tr>
					<tr bgcolor="#f8f8f8">
						<th scope="col"><div align="right">Code</div></th>
						<td><div align="left"><input type="text" style="width:300px;" class="span6 typeahead" name="txCode" id="txCode" autocomplete='off' value="<?php echo $code?>"></div></td>
					</tr>
					<tr bgcolor="#f8f8f8">
						<th width="30%" scope="col"><div align="right">Item Name</div></th>
						<td>
							<div align="left">
								<input type="text" class="span6 typeahead" style="width:300px;" name="txItem" id="txItem" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesItem;?>]' value="<?php echo $item;?>">
								<span class="help-inline warning" id="msgtxItem" style="font-weight:bold;" name="msgtxItem"></span>
							</div>
						</td>
					</tr>
					<tr bgcolor="#f8f8f8">
						<th width="30%" scope="col"><div align="right">Description</div></th>
						<td>
							<div align="left">
								<textarea class="span6 typeahead" style="width:300px;" name="txItemDesc" id="txItemDesc" autocomplete='off'><?php echo $itemDesc;?></textarea>
							</div>
						</td>
					</tr>
					<tr bgcolor="#f8f8f8">
						<th width="30%" scope="col"><div align="right">Size / Mass</div></th>
						<td>
							<div align="left">
								<input type="text" class="span6 typeahead" style="width:300px;" co name="txSize" id="txSize" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesSize;?>]' value="<?php echo $size;?>">
							</div>
						</td>
					</tr>
					<tr bgcolor="#f8f8f8">
						<th width="10%" scope="col"><div align="right">Unit</div></th>
						<td>
							<div align="left">
								<select name="txUnit" id="txUnit" style="width:300px;" required>
									<option value="">-- Select --</option>
									<?php
									$qType = $db->query('SELECT * FROM material_type WHERE type_name="unit" AND type_desc <> "" ORDER BY type_desc ');
									while($rType = $db->fetch_array($qType)):
									?>
									<option value="<?php echo functions::encode($rType['type_desc'])?>" <?php if($unit==$rType['type_desc']){echo 'selected="selected"';}?>><?php echo $rType['type_desc']?></option>
									<?php endwhile;?>
								</select>
								<a id="mngUnit" class="thickbox" style="cursor:pointer" onclick="showThis(this.id,'admin-material-reference-add-unit.php?','Manage Material Unit','1')" title="Manage Material Unit" data-rel="tooltip"><i class="halflings-icon plus-sign"></i></a>
							</div>
						</td>
					</tr>
					<tr bgcolor="#f8f8f8">
						<th width="10%" scope="col"><div align="right">Manufacturer / Brand</div></th>
						<td>
							<div align="left">
								<select name="txBrand" id="txBrand" style="width:300px;">
									<option value="">-- Select --</option>
									<?php
									$qType = $db->query('SELECT * FROM material_type WHERE type_name="brand" AND type_desc <> "" ORDER BY type_desc ');
									while($rType = $db->fetch_array($qType)):
									?>
									<option value="<?php echo functions::encode($rType['type_desc'])?>" <?php if($brand==$rType['type_desc']){echo 'selected="selected"';}?>><?php echo $rType['type_desc']?></option>
									<?php endwhile;?>
								</select>
								<a id="mngBrand" class="thickbox" style="cursor:pointer" onclick="showThis(this.id,'admin-material-reference-add-brand.php?','Manage Material Brand','1')" title="Manage Material Brand" data-rel="tooltip"><i class="halflings-icon plus-sign"></i></a>
							</div>
						</td>
					</tr>
					<tr bgcolor="#f8f8f8">
						<th width="10%" scope="col"><div align="right">Minimum Quantity Required</div></th>
						<td><div align="left"><input type="text" style="width:80px;" name="txLeastReq" id="txLeastReq" value="<?php echo $min;?>" onkeypress="return checkinput(this, event);" onkeyup="FormatCurrency(this);"></div></td>
					</tr>
					<tr bgcolor="#f7ebeb">
						<th width="10%" scope="col">&nbsp;</th>
						<td>
							<div align="left">
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
				<?php echo $msg;?>
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
	var res = false;

	$('#selCat').focus(function(){
		var deflt = $(this).val();
		$.get("admin-material-reference-sel-cat.php?sel="+deflt+"&typ=cat", function( data ) {
			$('#selCat').empty().append(data).val(deflt);
		});
	});
	$('#selClass').focus(function(){
		var deflt = $(this).val();
		$.get("admin-material-reference-sel-cat.php?sel="+deflt+"&typ=class", function( data ) {
			$('#selClass').empty().append(data).val(deflt);
		});
	});
	$('#selType').focus(function(){
		var deflt = $(this).val();
		$.get("admin-material-reference-sel-cat.php?sel="+deflt+"&typ=type", function( data ) {
			$('#selType').empty().append(data).val(deflt);
		});
	});
	$('#txUnit').focus(function(){
		var deflt = $(this).val();
		$.get("admin-material-reference-sel-cat.php?sel="+deflt+"&typ=unit", function( data ) {
			$('#txUnit').empty().append(data).val(deflt);
		});
	});
	$('#txBrand').focus(function(){
		var deflt = $(this).val();
		$.get("admin-material-reference-sel-cat.php?sel="+deflt+"&typ=brand", function( data ) {
			$('#txBrand').empty().append(data).val(deflt);
		});
	});

	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxItem').html("");
		$('#msgtxQty').html("");
		$('#msgtxQtyDel').html("");
		$('#MsgSelClass').html("");
		$('#MsgSelType').html("");
		$('#MsgSelCat').html("");
		if( $('#selCat').val()=="" ){
			$('#MsgSelCat').html("Specify Category!");
			$('#selCat').focus();
			res=false;
		}
		else if( $('#selClass').val()=="" ){
			$('#MsgSelClass').html("Specify Classification!");
			$('#selClass').focus();
			res=false;
		}
		else if( $('#selType').val()=="" ){
			$('#MsgSelType').html("Specify Type!");
			$('#selType').focus();
			res=false;
		}
		else if( $('#txItem').val()=="" ){
			$('#msgtxItem').html("Specify Item!");
			$('#txItem').focus();
			res=false;
		}
		else if( $('#txQty').val()=="" ){
			$('#msgtxQty').html("Required!");
			$('#txQty').focus();
			res=false;
		}
		else if( $('#txCost').val()=="" ){
			$('#msgtxCost').html("Required!");
			$('#txCost').focus();
			res=false;
		}
		else{
			if(confirm('Do you want to add this Reference?'))
				res=true;
			else
				res=false;
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