<?php require_once('templ_up.php');?>
<?php
$arr = array();
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;

$itemDel = (isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ) ? functions::decode($_REQUEST['itemDel']) : 0;
$mon='';
$txProj = '';
$txPayee = '';
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';

if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['el_proj'] = (isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
	$_SESSION['el_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	$_SESSION['el_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['el_day'] = (isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
	$_SESSION['el_mnTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $_POST['bdMonTo'] : '';
	$_SESSION['el_yrTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $_POST['bdYearTo'] : '';
	$_SESSION['el_dayTo'] = ( isset($_POST['bdDayTo']) && !empty($_POST['bdDayTo']) ) ? $_POST['bdDayTo'] : '';
	functions::sendTo(functions::pageName());
	die();	
}
if($itemDel){
	$amount = $db->getValue('inhouse_equip_leasing_item','count(*)',array('iel_id'=>$itemDel));
	if($amount == 0){
		$db->delete('inhouse_equip_leasing',array('iel_id'=>$itemDel));
		functions::sendTo(functions::pageName());
		die();
	}
}

$txProj = ( isset($_SESSION['el_proj']) ) ? $_SESSION['el_proj'] : '';
$txbYear = ( isset($_SESSION['el_yr']) ) ? $_SESSION['el_yr'] : date('Y');
$txbMon = ( isset($_SESSION['el_mn']) ) ? $_SESSION['el_mn'] : date('m');
$txbDay = ( isset($_SESSION['el_day']) ) ? $_SESSION['el_day'] : date('d');

$txbYearTo = ( isset($_SESSION['el_yrTo']) ) ? $_SESSION['el_yrTo'] : date('Y');
$txbMonTo = ( isset($_SESSION['el_mnTo']) ) ? $_SESSION['el_mnTo'] : date('m');
$txbDayTo = ( isset($_SESSION['el_dayTo']) ) ? $_SESSION['el_dayTo'] : date('d');

$where = 'WHERE 1'; 

if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
	$where .=' AND (iel_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
	$where .=' AND ( LEFT(iel_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(iel_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
	$where .=' AND (SUBSTRING(iel_date,6,2)>="'.$txbMon.'" AND SUBSTRING(iel_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
	$where .=' AND (LEFT(iel_date,4) >= "'.$txbYear.'" AND LEFT(iel_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
	$where .=' AND iel_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';
  
if($txProj){
	if( $db->getValue('inhouse_equip_leasing','count(*)',array('proj_id'=>$txProj)) )
		$where .=' AND proj_id="'.$db->clean($txProj).'"';#$arr = array_merge($arr,array('proj_id'=>$txProj));
	else
		$where .=' AND payee="'.$db->clean($txProj).'"';#$arr = array_merge($arr,array('payee'=>$txProj));
}

if(count($arr)){
	$rowdisplay=1000;
	$startrow=0;
}

#$qList = $db->select('inhouse_equip_leasing','*',$arr,'ORDER BY iel_date DESC LIMIT '.$startrow.', '.$rowdisplay);
#$qList = $db->select('inhouse_equip_leasing','*',$arr,'ORDER BY iel_date DESC');
$qList = $db->select('inhouse_equip_leasing','*',array(),$where.' ORDER BY iel_date DESC');
#echo $db->last_query;
$num_record = $db->getValue('inhouse_equip_leasing','count(*)',$arr);


$arrChargeList=array();
$qProj = $db->query('SELECT * FROM project WHERE proj_id IN (SELECT DISTINCT proj_id FROM inhouse_equip_leasing) ORDER BY proj_name');
while($rProj = $db->fetch_array($qProj)):
	if($rProj['proj_id'])
	$arrChargeList[$rProj['proj_name']]=$rProj['proj_id'];
endwhile;

$qPayee = $db->query('SELECT DISTINCT payee FROM inhouse_equip_leasing ORDER BY payee');
while($rPayee = $db->fetch_array($qPayee)):
	if($rPayee['payee'])
	$arrChargeList[strtoupper($rPayee['payee'])]=$rPayee['payee'];
endwhile;
ksort($arrChargeList);

$lists=array();
while($rList = $db->fetch_array($qList)):
	$amount = $db->getValue('inhouse_equip_leasing_item','sum( (duration * cost) - ( (duration * cost) * (discount/100) ) )',array('iel_id'=>$rList['iel_id']));
	$lists[]=array('iel_id'=>$rList['iel_id'],'proj_id'=>$rList['proj_id'],'iel_date'=>$rList['iel_date'],'is_paid'=>$rList['is_paid'],'amount'=>$amount);
endwhile;

$orderBy = (isset($_REQUEST['sort']) && !empty($_REQUEST['sort']) ) ? functions::decode($_REQUEST['sort']) : 'iel_date';
$ascDes = (isset($_REQUEST['ascDes']) && !empty($_REQUEST['ascDes']) ) ? $_REQUEST['ascDes'] : 'DESC';
if($ascDes==="DESC"){
	$ascDes="ASC";
}
else if($ascDes==="ASC"){
	$ascDes="DESC";
}
if(count($lists))
	functions::sortMultiArray($lists,$orderBy,$ascDes);
?>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>PROPERTY LEASING</h2>
		</div>
		<div class="box-content">
			<table width="200" cellspacing="0" cellpadding="0" border='0' align="right">
				<tr>
					<td width="10" height='30'><div style="background-color:#e6eb9c; width:20px;">&nbsp;</div></td>
					<td width="90"> Unpaid</td>
				</tr>
			</table><br><br>
			<div align="right"><a id="adc" href="#" class="btn btn-info btn-small btn-setting thickbox" onclick="showThis(this.id,'admin-equipment-leasing-add.php?','In-house leasing')">Add New Leasing</a> &nbsp;&nbsp;<a id="adcd" href="#" class="btn btn-info btn-small btn-setting thickbox" onclick="showThis(this.id,'admin-equipment-leasing-report.php?','Property Leasing Report','1')">Property Leasing Report</a></div><br>
			<form method="post">
				<table border="0">
					<tr>
						<td width="20%">
							<div align="center">
								<div align="center"><strong>FROM</strong></div>
								<select name="bdYear" id="bdYear" style="width:80px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('inhouse_equip_leasing','DISTINCT LEFT(iel_date,4) as yr',array(),'ORDER BY iel_date DESC');
									while($rYr = $db->fetch_array($qYr)):
									?>
									<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
									<?php endwhile;?>
								</select>
								<select name="bdMon" id="bdMon" style="width:85px;">
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
								<select name="bdDay" id="bdDay" style="width:65px;">
									<option value="">Day</option>
									<?php for($i=1;$i<=31;$i++):?>
									<option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDay)echo 'selected="selected"';?>><?php echo $i;?></option>
									<?php endfor;?>
								</select>
							</div>
							<div align="center">
								<div align="center"><strong>TO</strong></div>
								<select name="bdYearTo" id="bdYearTo" style="width:80px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('inhouse_equip_leasing','DISTINCT LEFT(iel_date,4) as yr',array(),'ORDER BY iel_date DESC');
									while($rYr = $db->fetch_array($qYr)):
									?>
									<option value="<?php echo $rYr['yr']?>" <?php if($txbYearTo==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
									<?php endwhile;?>
								</select>
								<select name="bdMonTo" id="bdMonTo" style="width:85px;">
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
								<select name="bdDayTo" id="bdDayTo" style="width:65px;">
									<option value="">Day</option>
									<?php for($i=1;$i<=31;$i++):?>
									<option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDayTo)echo 'selected="selected"';?>><?php echo $i;?></option>
									<?php endfor;?>
								</select>
							</div>
						</td>
						<td width="35%">
							<div align="left">
								<select name="selProj" id="selProj" data-rel="chosen" style="width:550px;">
									<option value="">All Project / Payee</option>
									<?php foreach($arrChargeList as $name => $id): ?>
									<option value="<?php echo $id?>" <?php if($txProj===$id)echo 'selected="selected"';?>><?php echo ucwords(strtolower($name));?></option>
									<?php endforeach;?>
								</select>
							</div>
						</td>
						<td width="8%"><div align="center"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small"></div></td>
					</tr>
					<tr><td colspan="4"><hr width="100%"></td></tr>
				</table>
			</form>
			<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="9%"><a href="?sort=<?php echo functions::encode('iel_date').'&ascDes='.$ascDes?>">Charging Date</a></th>
						<th width="40%"><a href="?sort=<?php echo functions::encode('proj_id').'&ascDes='.$ascDes?>">Project / Lessee</a></th>
						<th width="10%"><div align="right"><a href="?sort=<?php echo functions::encode('amount').'&ascDes='.$ascDes?>">Amount</a></div></th>
						<th width="11%"><div align="center">Options</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$totalAmount=0;
				foreach($lists as $rList):
					$amount = $rList['amount'];
					$totalAmount += $amount;
					$is_paid = $rList['is_paid'];
					$bgColor='';
					if($is_paid==1)
						$bgColor = 'bgcolor="#e6eb9c"';
				?>
					<tr id="rw<?php echo $rList['iel_id']?>" <?php echo $bgColor;?>>
						<td><?php echo functions::datearr($rList['iel_date']);?></td>
						<td><?php echo ($rList['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rList['proj_id'])) : $db->getValue('inhouse_equip_leasing','payee',array('iel_id'=>$rList['iel_id']));?></td>
						<td><div align="right"><a id="costdetail<?php echo $rList['iel_id']?>" class="label label-info thickbox" title="View Leasing Details" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-leasing-item-view.php?iel_id=<?php echo functions::encode($rList['iel_id']);?>','Equipment Item','1')"><?php echo functions::formatMoney($amount);?></a></div></td>
						<td>
							<div align="center">
								<a id="detail<?php echo $rList['iel_id']?>" class="btn btn-mini btn-info thickbox" title="Manage Leasing item" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-leasing-item-manage.php?iel_id=<?php echo functions::encode($rList['iel_id']);?>','Manage Item')"><i class="halflings-icon white plus-sign"></i></a>
								<a id="edit<?php echo $rList['iel_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify this Leasing" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-leasing-edit.php?iel_id=<?php echo functions::encode($rList['iel_id']);?>','Leasing Details')"><i class="halflings-icon white pencil"></i></a>
								<?php if( $db->getValue('inhouse_equip_leasing_item','count(*)',array('iel_id'=>$rList['iel_id']))==0 ){ ?>
								<a id="del<?php echo $rList['iel_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Leasing" data-rel="tooltip" href="<?php echo functions::pageName()?>?itemDel=<?php echo functions::encode($rList['iel_id']);?>"><i class="halflings-icon white trash"></i></a>
								<?php }?>
								<a id="print<?php echo $rList['iel_id']?>" class="btn btn-mini btn-success thickbox" title="Print this Leasing" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-leasing-print.php?iel_id=<?php echo functions::encode($rList['iel_id']);?>','Equipment Leasing Print','1')"><i class="halflings-icon white print"></i></a>
							</div>
						</td>
					</tr>
				<?php endforeach;?>
					<tr>
						<td>&nbsp;</td>
						<td><div align="right"><strong>Total Amount</strong></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($totalAmount);?></strong></div></td>
						<td>&nbsp;</td>
					</tr>
				</tbody>
			</table>
			<div align="center"><?php #functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=functions::pageName().'?',$search="");?></div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function delt(){
	if(confirm('Do you want to remove this Leasing?'))
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