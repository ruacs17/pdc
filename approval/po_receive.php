<?php require_once('templ_up.php');?>
<?php
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$arr = array();

$mon='';$txProj = '';$txPayee = '';$txbMon = '';$txbYear = ''; $txSearch='';$txPoType='';

if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['poProj'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
	$_SESSION['poPayee'] = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : '';
	$_SESSION['poMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['poYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	$_SESSION['poptype'] = ( isset($_POST['po_type']) && !empty($_POST['po_type']) ) ? $_POST['po_type'] : '';
	functions::sendTo(functions::pageName());
	die();
}

if($po_id)
	$arr = array('po_id'=>$po_id);   
else{
	$txPoType = ( isset($_SESSION['poptype']) && !empty($_SESSION['poptype']) ) ? $_SESSION['poptype'] : 'material';
	$txProj = ( isset($_SESSION['poProj']) && !empty($_SESSION['poProj']) ) ? $_SESSION['poProj'] : '';
	$txPayee = ( isset($_SESSION['poPayee']) && !empty($_SESSION['poPayee']) ) ? $_SESSION['poPayee'] : '';
	$txbMon = ( isset($_SESSION['poMon']) ) ? $_SESSION['poMon'] : date('m');
	$txbYear = ( isset($_SESSION['poYear']) ) ? $_SESSION['poYear'] : date('Y');
	if($txProj)
		$arr = array_merge($arr,array('proj_id'=>$txProj));
	if($txPayee)
		$arr = array_merge($arr,array('supplierID'=>$txPayee));
	if($txPoType)
		$arr = array_merge($arr,array('po_type'=>$txPoType));    

	if($txbMon && $txbYear)
		$arr = array_merge($arr,array('LEFT(po_date,7)'=>$txbYear.'-'.$txbMon));
	else if($txbMon)
		$arr = array_merge($arr,array('SUBSTRING(po_date,6,2)'=>$txbMon));
	elseif($txbYear)
		$arr = array_merge($arr,array('LEFT(po_date,4)'=>$txbYear));  
}
if(count($arr)){
    $rowdisplay=40;
}
$qPO = $db->select('po','ref_id,receive_no,po_date,delivery_date,supplierID,invoice',$arr,'GROUP BY ref_id ORDER BY locate("-",receive_no) desc, cast(SUBSTRING_INDEX(receive_no,"-",-1) as unsigned) desc LIMIT '.$startrow.', '.$rowdisplay);
#echo $db->last_query;
$qCount = $db->select('po','ref_id,receive_no,po_date,delivery_date,supplierID,invoice',$arr,'GROUP BY ref_id');
$num_record = $db->num_rows($qCount);
if( isset($_POST['btnSearch2']) ){
	$startrow=0;
	$txSearch = ( isset($_POST['txSearch']) && !empty($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
	$qPO = $db->query("SELECT ref_id,receive_no,po_date,delivery_date,supplierID,invoice FROM po p WHERE (ref_id LIKE '%".$db->clean($txSearch)."%' OR invoice LIKE '%".$db->clean($txSearch)."%') AND (po_type='fuel' OR po_type='material' OR po_type='accessory' OR po_type='parts') GROUP BY p.ref_id LIMIT ".$startrow.", ".$rowdisplay);
	#echo $db->last_query;
	$qCount = $db->query("SELECT ref_id,receive_no,po_date,delivery_date,supplierID,invoice FROM po p WHERE (ref_id LIKE '%".$db->clean($txSearch)."%' OR invoice LIKE '%".$db->clean($txSearch)."%') AND (po_type='fuel' OR po_type='material' OR po_type='accessory' OR po_type='parts') GROUP BY p.ref_id");
	$num_record = $db->num_rows($qCount);
}
?>
<!-- body content: start here-->
<table width="470" cellspacing="0" cellpadding="0" border='0' align="right">
	<tr>
		<td width="10" height='30'><div style="background-color:#ff889e; width:20px;">&nbsp;</div></td>
		<td width="90"> Unserved</td>
		<td width="10" height='30'><div style="background-color:#e6eb9c; width:20px;">&nbsp;</div></td>
		<td width="90"> Not Received</td>
		<td width="20"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
		<td width="90"> Delivery Incomplete</td>
	</tr>
</table>
<br><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Purchase Order Receiving</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<table border="0">
					<tr>
						<td colspan="4">P.O. Number or Invoice Number: <input type="text" name="txSearch" value="<?php echo $txSearch;?>">&nbsp;<input type="submit" name="btnSearch2" id="btnSearch2" value="Search" class="btn btn-primary btn-small">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="4"><hr width="100%"></td>
					</tr>
					<tr>
						<td width="15%">
							<div align="left">
								<select name="bdYear" id="bdYear" style="width:90px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('po','DISTINCT LEFT(po_date,4) as yr',array(),'ORDER BY po_date DESC');
									while($rYr = $db->fetch_array($qYr)):
									?>
									<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
									<?php endwhile;?>
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
							</div>
						</td>
						<td width="35%">
							<div align="left">
								<select name="txPayee" id="txPayee" data-rel="chosen" style="width:550px;">
									<option value="">All Payee</option>
									<?php $qSup = $db->select('supplier','*',array(),'ORDER BY name');
									while($rSup = $db->fetch_array($qSup)):
									?>
									<option value="<?php echo $rSup['supplierID']?>" <?php if($txPayee==$rSup['supplierID'])echo 'selected="selected"';?>><?php echo ($rSup['name']);?></option>
									<?php endwhile;?>
								</select>&nbsp;&nbsp;
								<select name="po_type" style="width:100px;">
									<option value='material' <?php if($txPoType=='material'){echo 'selected="selected"';} ?>>Material</option>
									<option value='fuel' <?php if($txPoType=='fuel'){echo 'selected="selected"';} ?>>Fuel</option>
									<option value='parts' <?php if($txPoType=='parts'){echo 'selected="selected"';} ?>>Parts</option>
									<option value='accessory' <?php if($txPoType=='accessory'){echo 'selected="selected"';} ?>>Accessory</option>
								</select>
							</div>
						</td>
						<td width="8%">
							<div align="center">
								<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small">
							</div>
						</td>
					</tr>
					<tr><td colspan="4"><hr width="100%"></td></tr>
				</table>
			</form>
			<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="7%"><div>P.O. #</div></th>
						<th width="8%">P.O. Date</th>
						<th width="7%"><div>Invoice</div></th>
						<th width="7%"><div>RR No</div></th>
						<th width="7%">Receive Date</th>
						<th width="35%">Payee</th>
						<th width="7%"><div align="right">Amount</div></th>
						<th width="5%"><div align="center">View</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$countPO=0;
				while($rPO = $db->fetch_array($qPO)):
					$total_amount=0;$amount=0;$countPO++;
					$qPOI = $db->query('SELECT pi.item,sum(quantity) as qty,sum(qty_delivered) as qty_d,unit,brand,cost,cost as t_cost,discount FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$rPO['ref_id'].'" GROUP by pi.item,unit,brand,cost');
					while($rPOI = $db->fetch_array($qPOI)):
						$amount = $rPOI['t_cost'] * $rPOI['qty_d'];
						$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
						$amount = $amount - $disc_amount;
						$total_amount += $amount;
					endwhile;
				?>
					<tr id="rw<?php echo $rPO['ref_id']?>">
						<td><div><?php echo $rPO['ref_id'];?></div></td>
						<td><?php echo functions::datearr($rPO['po_date']);?></td>
						<td><div><?php echo $rPO['invoice'];?></div></td>
						<td><div><?php echo $rPO['receive_no'];?></div></td>
						<td><?php echo functions::datearr($rPO['delivery_date']);?></td>
						<td><?php echo $db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID']));?></td>
						<td><div align="right"><?php echo functions::formatMoney($total_amount);?></div></td>
						<td>
							<div align="center">
								<a id="costdetail<?php echo $countPO?>" class="btn btn-info btn-mini thickbox" title="View P.O. Details" data-rel="tooltip" onclick="showThis(this.id,'po_receive_view_only.php?ref_id=<?php echo functions::encode($rPO['ref_id']);?>','P.O. Details')"><i class="halflings-icon white plus-sign"></i></a>
							</div>
						</td>
					</tr>
				<?php endwhile;?>
				</tbody>
			</table>
			<div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=functions::pageName().'?',$search="");?></div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function delt(){
	if(confirm('Do you want to remove this P.O.?'))
		return true;
	else
		return false; 
}
</script>
<?php require_once('templ_down.php');?>
<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({
		borderColor:"#87EAC1"
	}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({
		borderColor:""
	}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>
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