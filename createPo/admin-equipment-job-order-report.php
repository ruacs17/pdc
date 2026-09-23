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
$itemDel = (isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ) ? functions::decode($_REQUEST['itemDel']) : 0;
$jid = (isset($_REQUEST['jid']) && !empty($_REQUEST['jid']) ) ? functions::decode($_REQUEST['jid']) : 0;
$mon='';$txProj = '';$txPayee = '';$txbMon = '';$txbYear = ''; $jo_status='';
$arr = array();
if( isset($_POST['btnSearchJo']) && !empty($_POST['btnSearchJo']) ){
	$_SESSION['jo_search'] = ( isset($_POST['txSearchJO']) && isset($_POST['txSearchJO']) ) ? $_POST['txSearchJO'] : '';
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnSearch']) ){
	$_SESSION['jo_eqp'] = (isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? $_POST['selEquip'] : 0;
	$_SESSION['jo_yr'] = ( isset($_POST['bdYear']) ) ? $_POST['bdYear'] : 0;
	$_SESSION['jo_mn'] = ( isset($_POST['bdMon']) ) ? $_POST['bdMon'] : 0;
	$_SESSION['jo_status'] = ( isset($_POST['txStatus']) && !empty($_POST['txStatus']) ) ? trim($_POST['txStatus']) : NULL;
	unset($_SESSION['jo_search']);
	functions::sendTo(functions::pageName());
	die();
}
if($itemDel){
	$db->delete('equip_job_order_mechanic',array('jo_id'=>$itemDel));
	$db->delete('equip_job_order',array('jo_id'=>$itemDel));
	$_SESSION['notif_warning']='Job Order Removed!';
	functions::sendTo(functions::pageName());
	die();
}

$equip_id = ( isset($_SESSION['jo_eqp']) ) ? $_SESSION['jo_eqp'] : '';
$txbYear = ( isset($_SESSION['jo_yr']) ) ? $_SESSION['jo_yr'] : date('Y');
$txbMon = ( isset($_SESSION['jo_mn']) ) ? $_SESSION['jo_mn'] : date('m');
$jo_status = ( isset($_SESSION['jo_status']) ) ? $_SESSION['jo_status'] : '';
$txSearchJO = ( isset($_SESSION['jo_search']) ) ? $_SESSION['jo_search'] : '';
if($jid){
	$arr=array('jo_id'=>$jid);
}
else{
	if($txbMon && $txbYear)
		$arr = array('LEFT(date_report,7)'=>$txbYear.'-'.$txbMon);
	elseif($txbYear)
		$arr = array('LEFT(date_report,4)'=>$txbYear);
	elseif($txbMon)
		$arr = array('SUBSTRING(date_report,6,2)'=>$txbMon);
	if($equip_id){
		$arr = array_merge($arr,array('jo.equip_id'=>$equip_id));
	}
	if($txSearchJO)
		$arr=array('jo_no'=>$txSearchJO);
	if($jo_status)
		$arr = array_merge($arr,array('jo.jo_status'=>$jo_status));	
}

if(count($arr)){
	$rowdisplay=1000;
	$startrow=0;
}
if(count($arr))
	$qList = $db->select('equip_job_order jo, equipment eqp','*,jo.remarks as rmks',$arr,'AND jo.equip_id=eqp.equip_id ORDER BY date_report DESC');
else
	$qList = $db->query('SELECT *,jo.remarks as rmks FROM equip_job_order jo, equipment eqp WHERE jo.equip_id=eqp.equip_id ORDER BY date_report DESC');
#echo $db->last_query;
$num_record = $db->getValue('equip_job_order','count(*)',$arr);
$arrStat=array();
$arrMnth= empty($txbYear) ? array() : array($txbYear.'-01'=>$txbYear.'-01',$txbYear.'-02'=>$txbYear.'-02',$txbYear.'-03'=>$txbYear.'-03',$txbYear.'-04'=>$txbYear.'-04',$txbYear.'-05'=>$txbYear.'-05',$txbYear.'-06'=>$txbYear.'-06',$txbYear.'-07'=>$txbYear.'-07',$txbYear.'-08'=>$txbYear.'-08',$txbYear.'-09'=>$txbYear.'-09',$txbYear.'-10'=>$txbYear.'-10',$txbYear.'-11'=>$txbYear.'-11',$txbYear.'-12'=>$txbYear.'-12');
$arrJOStat=array();
/*$qJO = $db->query('SELECT DISTINCT jo_status FROM equip_job_order ORDER BY jo_status');
while($rJO = $db->fetch_array($qJO)):
	if( !empty($rJO['jo_status']) )
		$arrJOStat[$rJO['jo_status']]=$rJO['jo_status'];
endwhile;
*/
$arrJOStat=array('Accomplished'=>'Accomplished','On-going Repair'=>'On-going Repair','Waiting Parts'=>'Waiting Parts','Pending'=>'Pending','for Schedule'=>'for Schedule','for QC'=>'for QC','Done Post'=>'Done Post','Release'=>'Release');
$q = $db->query('SELECT left(date_report,7) as mnth, jo_status, count(*) as cnt FROM equip_job_order GROUP BY mnth, jo_status');
while($r = $db->fetch_array($q)):
	$mnth = $r['mnth'];
	$stat = $r['jo_status'];
	$cnt = $r['cnt'];
	if($mnth && $stat)
		$arrJO[$mnth][$stat]=$cnt;
endwhile;
// echo '<pre>';
// print_r($arrJO);
// print_r($arrJOStat);
// print_r($arrMnth);
// echo '</pre>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Equipment Job Order</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
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
	<style>
	.tdSpace{padding: 12px 0px 4px 0px;}
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white th"></i><span class="break"></span>Equipment Job Order List</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li class="active"><a href="admin-equipment-job-order-report.php?" style="opacity:.9">Report</a></li>
				<li><a href="admin-equipment-job-order.php?">Monitoring</a></li>
				<li><a href="admin-equipment-job-order-manage.php?">Create Job Order</a></li>
			</ul>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="right" style="display:none;"><a id="print" class="btn btn-small btn-info thickbox" style="text-decoration:none;cursor:pointer;" title="Print Job Order List" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-job-order-print.php?jid=<?php #echo ($jid) ? functions::encode($jid) : '';?>','Job Order List Print','1')"><i class="halflings-icon white print"></i></a></div>
				<div align="center">
					<div class="control-group">
						<div class="controls">
							<div class="input-append">
								<select name="bdYear" id="bdYear" style="width:150px;">
									<option value="">--Select Year--</option>
									<?php for($i=(date('Y')+1); $i >= 2015;$i--): ?>
									<option value="<?php echo $i; ?>" <?php if($txbYear==$i){echo 'selected="selected"';} ?>><?php echo $i; ?></option>
									<?php endfor; ?>
								</select>
								<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small">
							</div>
						</div>
					</div>
				</div>
				<div style="padding-top:10">&nbsp;</div>
				<div class="table-wrapper">
					<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size:11px;">
						<thead>
							<tr style="background-color:#CCC;">
								<th width="10%"><div align="center">Month</div></th>
								<?php foreach($arrJOStat as $joStat): ?>
								<th width="7%"><div align="center"><?php echo $joStat; ?></div></th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
						<?php
						$countRes=0;
						$totalPerStat=array();
						foreach($arrMnth as $mnt):
						?>
							<tr>
								<td><div align="center"><?php echo date('F',strtotime($mnt));?></div></td>
								<?php
								foreach($arrJOStat as $joStat):
									$countRes++;
									$trn=isset($arrJO[$mnt][$joStat]) ? $arrJO[$mnt][$joStat] : 0;
									if(isset($totalPerStat[$joStat]))
										$totalPerStat[$joStat]+=$trn;
									else
										$totalPerStat[$joStat]=$trn;
								?>
								<td>
									<div align="center">
										<?php
										if($trn){?>
										<a id="detail<?php echo $countRes?>" class="thickbox" title="View Job Order" style="text-decoration:none;cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-job-order-detail.php?mon=<?php echo functions::encode($mnt)?>&stat=<?php echo functions::encode($joStat)?>','Job Order List','1')"><?php echo $trn; ?></a>
										<?php }else{ /*echo $trn;*/ } ?>
									</div>
								</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach;?>
							<tr>
								<td><div align="center"><strong>Total&nbsp;&nbsp;&nbsp;<i>(<?php echo array_sum($totalPerStat) ?>)</i></strong></div></td>
								<?php foreach($arrJOStat as $joStat):?>
								<td><div align="center"><strong><?php echo isset($totalPerStat[$joStat]) ? $totalPerStat[$joStat] : 0;?></strong></div></td>
								<?php endforeach; ?>
							</tr>
						<?php if($countRes==0){?>
							<tr>
								<td colspan="<?php echo count($arrJOStat)+1 ?>"><div align="center">--No Record found!--</div></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
			</form>
		</div>
	</div><!--/span-->
</div>
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
function delt(){
	if(confirm('Do you want to remove this Job Order?'))
		return true;
	else
		return false; 
}
</script>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<?php if(isset($_SESSION['notif_id'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id']);} ?>
<!-- end: JavaScript-->
</body>
</html>