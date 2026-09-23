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

$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=1000;
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$po_idDel = (isset($_REQUEST['po_idDel']) && !empty($_REQUEST['po_idDel']) ) ? functions::decode($_REQUEST['po_idDel']) : 0;
$arr = array('received'=>0);

$mon='';$txProj = '';$txPayee = '';$txbMon = '';$txbYear = ''; $txSearch='';

if( isset($_POST['btnSearch']) ){
	$_SESSION['poURProj'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
	$_SESSION['poURPayee'] = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : '';
	$_SESSION['poURMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['poURYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	functions::sendTo(functions::pageName());
	die();
}
if($po_id)
	array_merge($arr,array('po_id'=>$po_id));
else{
	$txProj = ( isset($_SESSION['poURProj']) && !empty($_SESSION['poURProj']) ) ? $_SESSION['poURProj'] : '';
	$txPayee = ( isset($_SESSION['poURPayee']) && !empty($_SESSION['poURPayee']) ) ? $_SESSION['poURPayee'] : '';
	$txbMon = ( isset($_SESSION['poURMon']) ) ? $_SESSION['poURMon'] : date('m');
	$txbYear = ( isset($_SESSION['poURYear']) ) ? $_SESSION['poURYear'] : date('Y');
	if($txProj)
		$arr = array_merge($arr,array('proj_id'=>$txProj));
	if($txPayee)
	$arr = array_merge($arr,array('supplierID'=>$txPayee));

	if($txbMon && $txbYear)
		$arr = array_merge($arr,array('LEFT(po_date,7)'=>$txbYear.'-'.$txbMon));
	else if($txbMon)
		$arr = array_merge($arr,array('SUBSTRING(po_date,6,2)'=>$txbMon));
	elseif($txbYear)
		$arr = array_merge($arr,array('LEFT(po_date,4)'=>$txbYear));  
}
$arrPO = array();
$num_record = $db->getValue('po','count(*)',$arr);
$qPO = $db->select('po','*',$arr,'ORDER BY po_date DESC LIMIT '.$startrow.', '.$rowdisplay);
while($rPO = $db->fetch_array($qPO)):

	$vid='';
	$qVid = $db->query('SELECT vp.voucher_id FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vpp.po_id="'.$db->clean($rPO['po_id']).'"');
	$voucher_attached = $db->num_rows();
	while($rVid = $db->fetch_array($qVid)):
		$vid .= $db->getValue('voucher','voucher_no',array('voucher_id'=>$rVid['voucher_id'])).'<br>';
	endwhile;


	if( $voucher_attached==0 && $rPO['invoice']=="" && $rPO['received']==0 ){//Unserved
		$supName = $db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID']));
		$pjct = $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id']));

		$amount = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id']));
		if( $db->getValue('po_fuel_equipment','count(po_id)',array('po_id'=>$rPO['po_id'])) ){
			$viewOnlyPage = 'po_fuel_view_only.php';
		}
		else if($rPO['po_type']=='service'){
			$viewOnlyPage = 'po_service_view_only.php';
		}
		else
		$viewOnlyPage = 'po_view_only.php';

		$arrPO[] = array('po_id'=>$rPO['po_id'],'po_no'=>$rPO['po_no'],'voucher_no'=>$vid,'invoice'=>$rPO['invoice'],'po_date'=>$rPO['po_date'],'supplier'=>$supName,'project'=>$pjct,'amount'=>$amount,'viewpage'=>$viewOnlyPage);
	}
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Unreceive P.O. Monitoring</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Unserved P.O.</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<table border="0">
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
						<td width="15%">
							<div align="left">
								<select name="txPayee" id="txPayee" data-rel="chosen" style="width:390px;">
									<option value="">All Payee</option>
									<?php $qSup = $db->select('supplier','*',array(),'ORDER BY name');
									while($rSup = $db->fetch_array($qSup)):
									?>
									<option value="<?php echo $rSup['supplierID']?>" <?php if($txPayee==$rSup['supplierID'])echo 'selected="selected"';?>><?php echo ($rSup['name']);?></option>
									<?php endwhile;?>
								</select>
							</div>
						</td>
						<td width="20%">
							<div align="left">
								<select name="selProj" id="selProj" data-rel="chosen" style="width:390px;">
									<option value="">All Project</option>
									<?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
									while($rProj = $db->fetch_array($qProj)):
									?>
									<option value="<?php echo $rProj['proj_id']?>" <?php if($txProj==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?></option>
									<?php endwhile;?>
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
			<table class="table table-bordered table-hover table-striped" style="font-size:12px">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="10%"><div>P.O. #</div></th>
						<th width="8%">P.O. Date</th>
						<th width="25%">Payee</th>
						<th width="45%">Project</th>
						<th width="8%"><div align="right" style="padding-right:40px;">Amount</div></th>
					</tr>
				</thead>
				<tbody>
				<?php $totalAmount=0; foreach($arrPO as $rrPO): $totalAmount+=$rrPO['amount'];?>
					<tr>
						<td><div><?php echo $rrPO['po_no'];?></div></td>
						<td><?php echo functions::datearr($rrPO['po_date']);?></td>
						<td><?php echo $rrPO['supplier'];?></td>
						<td><?php echo $rrPO['project'];?></td>
						<td><div align="right" style="padding-right:20px;"><a id="costdetail<?php echo $rrPO['po_id']?>" class="thickbox" style="cursor:pointer;" title="View P.O. Details" data-rel="tooltip" onclick="showThis(this.id,'<?php echo $rrPO['viewpage']?>?po_id=<?php echo functions::encode($rrPO['po_id']);?>','P.O. Details','1')"><?php echo functions::formatMoney($rrPO['amount']);?></a></div></td>
					</tr>
				<?php endforeach;?>
				<?php if(count($arrPO)==0){?>
					<tr><td colspan="5"><div align="center">---Nothing to Report---</div></td></tr>
				<?php }?>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td><div align="right" style="padding-right:10px;"><strong>Total</strong></div></td>
						<td><div align="right" style="padding-right:20px;"><strong><?php echo functions::formatMoney($totalAmount);?></strong></div></td>
					</tr>
				</tbody>
			</table>
			<div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=functions::pageName().'?',$search="");?></div>
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
<!-- end: JavaScript-->
</body>
</html>