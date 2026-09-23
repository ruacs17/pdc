<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arrVal=array();
$searchVal='';
$selAccredited='';$selEvaluated='';
$orderBy = (isset($_REQUEST['orderBy']) && !empty($_REQUEST['orderBy']) ) ? functions::decode($_REQUEST['orderBy']) : 'name';

$ascDes = (isset($_REQUEST['ascDes']) && !empty($_REQUEST['ascDes']) ) ? $_REQUEST['ascDes'] : 'DESC';
$sort_AscDesc="ASC";
if($ascDes==="DESC"){
	$ascDes="ASC";
	$sort_AscDesc="ASC";
}
else if($ascDes==="ASC"){
	$ascDes="DESC";
	$sort_AscDesc="DESC";
}

if( isset($_REQUEST['srch']) ){
	$searchVal = ( isset($_REQUEST['srch']) ) ? $db->clean(trim($_REQUEST['srch'])) : '';
	$selAccredited = ( isset($_REQUEST['selAccredited']) ) ? $_REQUEST['selAccredited'] : '';
	$selEvaluated = ( isset($_REQUEST['selEvaluated']) ) ? $_REQUEST['selEvaluated'] : '';
	$qSearchAccredited = ($selAccredited) ? 'accredited="'.$db->clean($selAccredited).'" AND ' : '';
	$qSearchEvaluated = ($selEvaluated) ? 'evaluated="'.$db->clean($selEvaluated).'" AND ' : '';
	$qDisp = $db->query('SELECT * FROM supplier WHERE '.$qSearchEvaluated.$qSearchAccredited.' (name LIKE "%'.$db->clean($searchVal).'%" OR business_type LIKE "%'.$db->clean($searchVal).'%") ORDER BY name');
}
else if( isset($_POST['btnSearch']) ){
	$searchVal = ( isset($_POST['txSupName']) ) ? $_POST['txSupName'] : '';
	$selAccredited = ( isset($_POST['selAccredited']) ) ? $_POST['selAccredited'] : '';
	$selEvaluated = ( isset($_POST['selEvaluated']) ) ? $_POST['selEvaluated'] : '';
	$qSearchAccredited = ($selAccredited) ? 'accredited="'.$db->clean($selAccredited).'" AND ' : '';
	$qSearchEvaluated = ($selEvaluated) ? 'evaluated="'.$db->clean($selEvaluated).'" AND ' : '';
	$qDisp = $db->query('SELECT * FROM supplier WHERE '.$qSearchEvaluated.$qSearchAccredited.' (name LIKE "%'.$db->clean($searchVal).'%" OR business_type LIKE "%'.$db->clean($searchVal).'%") ORDER BY name');
}
else
	$qDisp = $db->select('supplier','*',$arrVal,'ORDER BY name');
$arrDisp=array();
while($rDisp = $db->fetch_array($qDisp)):
	$contact = ($rDisp['contactPhoneNo']) ? $rDisp['contactPhoneNo'].' <br>' : '';
	$contact .= ($rDisp['contactPhoneNo2']) ? $rDisp['contactPhoneNo2'].' <br>' : '';
	$contact .= ($rDisp['contactCellNo']) ? $rDisp['contactCellNo'].' <br>' : '';
	$contact .= ($rDisp['contactCellNo2']) ? $rDisp['contactCellNo2'].' <br>' : '';
	$arrDisp[] = array('supplierID'=>$rDisp['supplierID'],'name'=>$rDisp['name'],'address'=>$rDisp['address'],'contactPhoneNo'=>$contact,'business_type'=>$rDisp['business_type'],'accredited'=>$rDisp['accredited'],'evaluated'=>$rDisp['evaluated'],'vat'=>$rDisp['vat']);
endwhile;
if(count($arrDisp))
	functions::sortMultiArray($arrDisp,$orderBy,$sort_AscDesc);
?>
<!-- body content: start here-->
<div align="right"><a id="adc" href="#" class="btn btn-info btn-small btn-setting thickbox" onclick="showThis(this.id,'supplier_add.php?','Supplier Detail')">Add New Supplier / Payee</a></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Supplier / Payee List</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="center">
					<table border="0">
						<tr>
							<td style="padding-top: 7px;"><input type="text" name="txSupName" id="txSupName" value="<?php echo $searchVal?>"></td>
							<td style="padding-top: 7px;">&nbsp;
								<select name="selAccredited" id="selAccredited" style="width: 150px;">
									<option value="">--Accreditation--</option>
									<option value="Accredited" <?php if($selAccredited=='Accredited')echo 'selected="selected"';?>>Accredited</option>
									<option value="Pre-Accredited" <?php if($selAccredited=="Pre-Accredited")echo 'selected="selected"';?>>Pre-Accredited</option>
									<option value="Not Accredited" <?php if($selAccredited=="Not Accredited")echo 'selected="selected"';?>>Not Accredited</option>
								</select>&nbsp;
							</td>
							<td style="padding-top: 7px;">&nbsp;
								<select name="selEvaluated" id="selEvaluated" style="width: 150px;">
									<option value="">--Evaluation--</option>
									<option value="Evaluated" <?php if($selEvaluated=='Evaluated')echo 'selected="selected"';?>>Evaluated</option>
									<option value="Passed" <?php if($selEvaluated=='Passed')echo 'selected="selected"';?>>Passed</option>
									<option value="Failed" <?php if($selEvaluated=='Failed')echo 'selected="selected"';?>>Failed</option>
								</select>&nbsp;
							</td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-info btn-small" name="btnSearch" id="btnSearch" value="Search"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-info btn-small" name="btnAll" id="btnAll" value="View All"></td>
						</tr>
					</table>
				</div>
			</form>
			<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="font-size: 12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="20%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('name').'&ascDes='.$ascDes?>">NAME</a></th>
						<th width="20%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('address').'&ascDes='.$ascDes?>">ADDRESS</a></th>
						<th width="15%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('contactPhoneNo').'&ascDes='.$ascDes?>">CONTACT #</a></th>
						<th width="11%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('business_type').'&ascDes='.$ascDes?>">BUSINESS TYPE</a></th>
						<th width="11%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('accredited').'&ascDes='.$ascDes?>">ACCREDITATION</a></th>
						<th width="11%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('evaluated').'&ascDes='.$ascDes?>">EVALUATION</a></th>
						<th width="9%" scope="col"><div align="center">OPTIONS</div></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($arrDisp as $itm):?>
					<tr id="rw<?php echo $itm['supplierID']?>">
						<td style="font-size: 14px;"><?php echo $itm['name']; echo ' <i>('.$itm['vat'].')</i>';?></td>
						<td><?php echo $itm['address']?></td>
						<td><?php echo $itm['contactPhoneNo'];?></td>
						<td><?php echo $itm['business_type']?></td>
						<td><?php echo ($itm['accredited']) ? $itm['accredited'] : '----';?></td>
						<td><?php echo ($itm['evaluated']) ? $itm['evaluated'] : '----';?></td>
						<td>
							<div align="center">
								<a id="vw<?php echo $itm['supplierID']?>" class="btn btn-mini btn-info thickbox" title="Supplier Detail" data-rel="tooltip" onclick="showThis(this.id,'supplier_view.php?sid=<?php echo functions::encode($itm['supplierID']);?>','Supplier Detail','1')"><i class="halflings-icon white zoom-in"></i></a>
								<a id="edit<?php echo $itm['supplierID']?>" class="btn btn-mini btn-warning thickbox" title="Modify this Supplier" data-rel="tooltip" onclick="showThis(this.id,'supplier_edit.php?sid=<?php echo functions::encode($itm['supplierID']);?>','Supplier Detail')"><i class="halflings-icon white pencil"></i></a>
							</div>
						</td>
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script>function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}</script>
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
