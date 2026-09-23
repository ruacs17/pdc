<?php require_once('templ_up.php');?>
<?php
$arr = array();
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$itemDel = (isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ) ? functions::decode($_REQUEST['itemDel']) : 0;
$mon='';$txProj = '';$txPayee = '';$txbMon = '';$txbYear = ''; 
$txSearchMR = ( isset($_POST['btnSearchMR']) && isset($_POST['txSearchMR']) ) ? $_POST['txSearchMR'] : '';

if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['mr_eu'] = (isset($_POST['selUser']) && !empty($_POST['selUser']) ) ? $_POST['selUser'] : 0;
	$_SESSION['mr_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : 0;
	$_SESSION['mr_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : 0;
	functions::sendTo(functions::pageName());
	die();
}
if($itemDel){
	$amount = $db->getValue('mr_item','count(*)',array('mr_id'=>$itemDel));
	if($amount == 0){
		$db->delete('mr',array('mr_id'=>$itemDel));
		functions::sendTo(functions::pageName());
		die();
	}
}

$txUser = ( isset($_SESSION['mr_eu']) ) ? $_SESSION['mr_eu'] : '';
$txbYear = ( isset($_SESSION['mr_yr']) ) ? $_SESSION['mr_yr'] : date('Y');
$txbMon = ( isset($_SESSION['mr_mn']) ) ? $_SESSION['mr_mn'] : date('m');

if($txbMon && $txbYear)
	$arr = array('LEFT(mr_date,7)'=>$txbYear.'-'.$txbMon);
elseif($txbYear)
	$arr = array('LEFT(mr_date,4)'=>$txbYear);
elseif($txbMon)
	$arr = array('SUBSTRING(mr_date,6,2)'=>$txbMon);

if($txUser){
	if( $db->getValue('mr','count(*)',array('mr_emp'=>$txUser)) )
		$arr = array_merge($arr,array('mr_emp'=>$txUser));
}

if(count($arr)){
	$rowdisplay=1000;
	$startrow=0;
}
if($txSearchMR)
	$qList = $db->select('mr','*',array('mr_no'=>$txSearchMR));
else
	$qList = $db->select('mr','*',$arr,'ORDER BY mr_date DESC');

$num_record = $db->getValue('mr','count(*)',$arr);

$arrChargeList=array();
$qUser = $db->query('SELECT mr.mr_emp,fname,mname,lname FROM mr, employee eu WHERE mr.mr_emp=eu.emp_id GROUP BY mr_emp,fname,mname,lname ORDER BY eu.lname,eu.fname');
while($rUser = $db->fetch_array($qUser)):
	$nme = $rUser['lname'].', '.$rUser['fname'].' '.$rUser['mname'];
	$arrChargeList[$nme]=$rUser['mr_emp'];
endwhile;
?>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		 <div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Memorandum of Receipt</h2>
		</div>
		<div class="box-content">
			<div align="right">
				<a id="mrAdd" href="#" class="btn btn-info btn-small thickbox" onclick="showThis(this.id,'admin-mr-add.php?','MR Create')">Create New MR</a>
				<a id="mrClearance" href="#" class="btn btn-info btn-small thickbox" onclick="showThis(this.id,'admin-mr-clearance.php?','MR Clearance')">View MR Clearance</a>
			</div><br>
			<form method="post">
				<table class="table" border="0">
					<tr>
						<td colspan="3" height="70">
							<div class="controls">MR No.
								<div class="input-append">
									<input type="text" name="txSearchMR" id="txSearchMR" value="<?php echo $txSearchMR;?>"><input type="submit" name="btnSearchMR" id="btnSearchMR" value="Search" class="btn btn-primary btn-small">
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td width="20%">
							<div align="left">
								<select name="bdYear" id="bdYear" style="width:90px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('mr','DISTINCT LEFT(mr_date,4) as yr',array(),'ORDER BY mr_date DESC');
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
						<td width="35%" style="padding: 12px 0px 0px 0px">
							<div align="left">
								<select name="selUser" id="selUser" data-rel="chosen" style="width:650px;">
									<option value="">All User</option>
									<?php foreach($arrChargeList as $name => $id): ?>
									<option value="<?php echo $id?>" <?php if($txUser===$id)echo 'selected="selected"';?>><?php echo ucwords(strtolower($name));?></option>
									<?php endforeach;?>
								</select>
							</div>
						</td>
						<td width="8%">
							<div align="center">
								<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small">
							</div>
						</td>
					</tr>
					<tr><td colspan="3"><hr width="100%"></td></tr>
				</table> 
			</form>
			<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
					<th width="9%">MR Date</th>
					<th width="10%">MR No.</th>
					<th width="35%">Project</th>
					<th width="11%"><div align="center">Manage</div></th>
					</tr>
				</thead>
				<tbody>
				<?php while($rList = $db->fetch_array($qList)):?>
					<tr id="rw<?php echo $rList['mr_id']?>">
						<td><?php echo functions::datearr($rList['mr_date']);?></td>
						<td><?php echo $rList['mr_no'];?></td>
						<td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rList['proj_id']));?></td>
						<td>
							<div align="center">
								<a id="detail<?php echo $rList['mr_id']?>" class="btn btn-mini btn-info thickbox" title="Manage MR item" data-rel="tooltip" onclick="showThis(this.id,'admin-mr-item-manage.php?mr_id=<?php echo functions::encode($rList['mr_id']);?>','Manage Item')"><i class="halflings-icon white plus-sign"></i></a>
								<?php if( $db->getValue('mr_item','count(*)',array('mr_id'=>$rList['mr_id']))==0 ){ ?>
								<a id="del<?php echo $rList['mr_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this MR" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?itemDel=<?php echo functions::encode($rList['mr_id']);?>"><i class="halflings-icon white trash"></i></a>
								<?php }?>
							</div>
						</td>
					</tr>
				<?php endwhile;?>
				</tbody>
			</table>
			<div align="center"><?php #functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=$_SERVER['PHP_SELF'].'?',$search="");?></div>
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