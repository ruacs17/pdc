<?php require_once('templ_up.php');?>
<?php
$arr = array();
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;

$itemDel = (isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ) ? functions::decode($_REQUEST['itemDel']) : 0;

$txProj = '';
$txbMon = '';
$txbYear = '';
$txSearchNo = '';
if(isset($_POST['btnSearchIssue']))
	$txSearchNo = ( isset($_POST['txSearchNo']) && isset($_POST['txSearchNo']) ) ? $_POST['txSearchNo'] : '';

if( isset($_POST['btnSearch']) ){
	$_SESSION['pos_proj'] = (isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : 0;
	$_SESSION['pos_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : 0;
	$_SESSION['pos_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : 0;
}
if($itemDel){
	$db->delete('po_issuance',array('pos_id'=>$itemDel));
	$_SESSION['notif_delt']='Purchase Order Issuance Removed!';
	functions::sendTo(functions::pageName());
	die();
}

$txProj = ( isset($_SESSION['pos_proj']) ) ? $_SESSION['pos_proj'] : '';
$txbYear = ( isset($_SESSION['pos_yr']) ) ? $_SESSION['pos_yr'] : date('Y');
$txbMon = ( isset($_SESSION['pos_mn']) ) ? $_SESSION['pos_mn'] : date('m');

if($txbMon && $txbYear)
	$arr = array('LEFT(issue_date,7)'=>$txbYear.'-'.$txbMon);
elseif($txbYear)
	$arr = array('LEFT(issue_date,4)'=>$txbYear);
elseif($txbMon)
	$arr = array('SUBSTRING(issue_date,6,2)'=>$txbMon);


if(count($arr)){
	$rowdisplay=1000;
	$startrow=0;
}


if(isset($_POST['btnSearch'])){
	if($txProj){
		$qList = $db->select('po_issuance pos, po_issuance_details posd, po p, project proj','*',array('proj.proj_id'=>$txProj),'AND pos.pos_id=posd.pos_id AND p.po_id=posd.po_id AND proj.proj_id=p.proj_id');
	}
	else{
		$qList = $db->select('po_issuance','*',$arr,'ORDER BY issue_date DESC');
		functions::sendTo(functions::pageName());
		die();
	}
		
}
else if($txSearchNo)
	$qList = $db->select('po_issuance','*',array('series_no'=>$txSearchNo));
else
	$qList = $db->select('po_issuance','*',$arr,'ORDER BY issue_date DESC');


$num_record = $db->query($db->last_query);

$arrProjList=array();
$qProjList = $db->query('SELECT proj.proj_id,proj.proj_name FROM po_issuance_details posd, po p, project proj WHERE p.po_id=posd.po_id AND proj.proj_id=p.proj_id GROUP BY proj.proj_id ORDER BY proj.proj_name');
while($rProjList = $db->fetch_array($qProjList)):
	$arrProjList[$rProjList['proj_id']] = $rProjList['proj_name'];
endwhile;
?>
<!-- body content: start here-->
<div class="row-fluid" style="font-size:12px;">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>ISSUANCE OF PURCHASE ORDER</h2>
		</div>
		<div class="box-content">
			<div align="right">
				<a id="ppeAdd" href="#" class="btn btn-info btn-small thickbox" onclick="showThis(this.id,'po-issuance-manage.php?','Manage Issuance')">Create New ISSUANCE</a>
			</div><br>
			<form method="post">
				<table class="table" border="0">
					<tr>
						<td colspan="3" height="70">
							<div class="control-group">
								<label class="control-label" for="txSearchNo">Series No:</label>
								<div class="controls">
									<div class="input-append">
										<input type="text" name="txSearchNo" id="txSearchNo" value="<?php echo $txSearchNo;?>"><input type="submit" name="btnSearchIssue" id="btnSearchIssue" value="Search" class="btn btn-primary btn-small"><input type="submit" name="btnClear" id="btnClear" value="Clear" class="btn btn-small">
									</div>
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
									$qYr = $db->select('po_issuance','DISTINCT LEFT(issue_date,4) as yr',array(),'ORDER BY issue_date DESC');
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
								<select name="selProj" id="selProj" data-rel="chosen" style="width:650px;">
								<option value="">All Project</option>
								<?php foreach($arrProjList as $id => $name): ?>
								<option value="<?php echo $id?>" <?php if($txProj==$id)echo 'selected="selected"';?>><?php echo ucwords(strtolower($name));?></option>
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
			<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="9%">Issued Date</th>
						<th width="10%">Series No.</th>
						<th width="50%">Project</th>
						<th width="10%"><div>P.O.</div></th>
						<th width="8%"><div align="center">Manage</div></th>
					</tr>
				</thead>
				<tbody>
					<?php while($rList = $db->fetch_array($qList)):?>
					<tr id="rw<?php echo $rList['pos_id']?>">
						<td><?php echo functions::datearr($rList['issue_date']);?></td>
						<td><?php echo $rList['series_no'];?></td>
						<td>
							<?php
							$arrPrj = ($txProj) ? array('pos_id'=>$rList['pos_id'],'proj.proj_id'=>$txProj) : array('pos_id'=>$rList['pos_id']);
							$qProj = $db->select('po_issuance_details posd, po p, project proj','*',$arrPrj,'AND p.po_id=posd.po_id AND proj.proj_id=p.proj_id ORDER BY proj_name');
							while($rProj = $db->fetch_array($qProj)):
								echo '<div>'.$rProj['proj_name'].'</div>';
							endwhile;
							?>
						</td>
						<td>
							<?php
							$qpo = $db->select('po_issuance_details posd, po p','*',array('pos_id'=>$rList['pos_id']),'AND p.po_id=posd.po_id');
							while($rpo = $db->fetch_array($qpo)):
								echo '<div>'.$rpo['po_no'].'</div>';
							endwhile;
							?>
						</td>
						<td>
							<div align="center">
								<a id="detail<?php echo $rList['pos_id']?>" class="btn btn-mini btn-info thickbox" title="Manage Issuance item" data-rel="tooltip" onclick="showThis(this.id,'po-issuance-manage-detail.php?id=<?php echo functions::encode($rList['pos_id']);?>','Manage Issuance')"><i class="halflings-icon white plus-sign"></i></a>
								<a id="del<?php echo $rList['pos_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Issuance" data-rel="tooltip" href="<?php echo functions::pageName()?>?itemDel=<?php echo functions::encode($rList['pos_id']);?>"><i class="halflings-icon white trash"></i></a>
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
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>
<?php if(isset($_SESSION['notif_delt'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_delt'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_delt']);} ?>