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

$mon='';$txProj = '';$txPayee = ''; 
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';
if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['in_proj'] = (isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
	$_SESSION['in_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	$_SESSION['in_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['in_day'] = (isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
	$_SESSION['in_mnTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $_POST['bdMonTo'] : '';
	$_SESSION['in_yrTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $_POST['bdYearTo'] : '';
	$_SESSION['in_dayTo'] = ( isset($_POST['bdDayTo']) && !empty($_POST['bdDayTo']) ) ? $_POST['bdDayTo'] : '';
	functions::sendTo(functions::pageName());
	die();
}

$txProj = ( isset($_SESSION['in_proj']) ) ? $_SESSION['in_proj'] : '';
$txbYear = ( isset($_SESSION['in_yr']) ) ? $_SESSION['in_yr'] : date('Y');
$txbMon = ( isset($_SESSION['in_mn']) ) ? $_SESSION['in_mn'] : date('m');
$txbDay = ( isset($_SESSION['in_day']) ) ? $_SESSION['in_day'] : date('d');
$txbYearTo = ( isset($_SESSION['in_yrTo']) ) ? $_SESSION['in_yrTo'] : date('Y');
$txbMonTo = ( isset($_SESSION['in_mnTo']) ) ? $_SESSION['in_mnTo'] : date('m');
$txbDayTo = ( isset($_SESSION['in_dayTo']) ) ? $_SESSION['in_dayTo'] : date('d');

$where = 'WHERE 1'; 

if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
	$where .=' AND (im_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
	$where .=' AND ( LEFT(im_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(im_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
	$where .=' AND (SUBSTRING(im_date,6,2)>="'.$txbMon.'" AND SUBSTRING(im_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
	$where .=' AND (LEFT(im_date,4) >= "'.$txbYear.'" AND LEFT(im_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
	$where .=' AND im_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj){
	if( $db->getValue('inhouse_material','count(*)',array('proj_id'=>$txProj)) )
		$where .=' AND proj_id="'.$db->clean($txProj).'"';
	else
		$where .=' AND payee="'.$db->clean($txProj).'"';
}


if($txProj)
	$qList = $db->select('inhouse_material','*',array(),$where.' ORDER BY im_date DESC');
else
	$qList = $db->select('inhouse_material','*',array('im_id'=>0),' ORDER BY im_date DESC');
#echo $db->last_query;

$arrChargeList=array();
$qProj = $db->query('SELECT * FROM project WHERE proj_id IN (SELECT DISTINCT proj_id FROM inhouse_material) ORDER BY proj_name');
while($rProj = $db->fetch_array($qProj)):
	if($rProj['proj_id'])
		$arrChargeList[$rProj['proj_name']]=$rProj['proj_id'];
endwhile;

$qPayee = $db->query('SELECT DISTINCT payee FROM inhouse_material ORDER BY payee');
while($rPayee = $db->fetch_array($qPayee)):
	if($rPayee['payee'])
		$arrChargeList[strtoupper($rPayee['payee'])]=$rPayee['payee'];
endwhile;
ksort($arrChargeList);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Warehouse Report</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Warehouse Stock Report</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
					<tr>
						<td width="50%"><div align="right"><a id="whprint" class="btn btn-small btn-info" href="admin-inhouse-material-payee-print.php"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div><br><br></td>
					</tr>
				</table>
				<table border="0">
					<tr>
						<td width="20%">
							<div align="center">
								<div align="center"><strong>FROM</strong></div>
								<select name="bdYear" id="bdYear" style="width:80px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('inhouse_material','DISTINCT LEFT(im_date,4) as yr',array(),'ORDER BY im_date DESC');
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
								<select name="bdDay" id="bdDay" style="width:60px;">
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
									$qYr = $db->select('inhouse_material','DISTINCT LEFT(im_date,4) as yr',array(),'ORDER BY im_date DESC');
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
								<select name="bdDayTo" id="bdDayTo" style="width:60px;">
									<option value="">Day</option>
									<?php for($i=1;$i<=31;$i++):?>
									<option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDayTo)echo 'selected="selected"';?>><?php echo $i;?></option>
									<?php endfor;?>
								</select>
							</div>
						</td>
						<td width="20%">
							<div align="left">
								<select name="selProj" id="selProj" data-rel="chosen" style="width:550px;">
									<option value="">Select Project / Payee</option>
									<?php foreach($arrChargeList as $name => $id): ?>
									<option value="<?php echo $id?>" <?php if($txProj===$id)echo 'selected="selected"';?>><?php echo ucwords(strtolower($name));?></option>
									<?php endforeach;?>
								</select>
							</div>
						</td>
						<td width="8%">
							<div align="center"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-small btn-primary"></div>
						</td>
					</tr>
					<tr><td colspan="4"><hr width="100%"></td></tr>
				</table>
				<?php
				$totalAmount=0;
				while($rList = $db->fetch_array($qList)):
					$amount = $db->getValue('inhouse_material_item','sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) )',array('im_id'=>$rList['im_id']));
					$totalAmount += $amount;

					$im_id = $rList['im_id'];
					$project_id = $db->getValue('inhouse_material','proj_id',array('im_id'=>$im_id));
				?>
					<div align="left"><br>
						<div style="display: none;">Project:
							<strong>
							<?php
							if($project_id){
								$qProj = $db->select('project','*',array('proj_id'=>$project_id));
								while($rProj = $db->fetch_array($qProj)):
									echo strtoupper($rProj['proj_name']);
									echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';
								endwhile;
							}
							else
								echo $db->getValue('inhouse_material','payee',array('im_id'=>$im_id));
							?>
							</strong>
						</div>
						<div>Date: <strong><?php echo functions::datearr($db->getValue('inhouse_material','im_date',array('im_id'=>$im_id)));?></strong></div>
						<div>Order No: 
							<strong>
							<?php
							$exp = explode('-',$db->getValue('inhouse_material','im_date',array('im_id'=>$im_id)));
							$m=0;$y=0;
							if(count($exp)==3){
								$m = $exp['1'];
								$y = $exp['0'];
							}
							echo $y.$m.$im_id;
							?>
							</strong>
						</div><br><br>
					</div>
					<table width="100%" border="0" align="center" class="table table-striped" style="font-size: 12px;">
						<thead>
							<tr style="background-color:#CCC;">
								<th width="20%" scope="col"><div align="left">Item</div></th>
								<th width="7%" scope="col"><div align="left">Quantity</div></th>
								<th width="8%" scope="col"><div align="left">Unit</div></th>
								<th width="15%" scope="col"><div align="left">Brand</div></th>
								<th width="8%" scope="col"><div align="right">Price</div></th>
								<th width="9%" scope="col"><div align="center">Discount</div></th>
								<th width="8%" scope="col"><div align="right">Amount</div></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$total_amount=0;$disc_amount=0;$amount=0;
						$qPOI = $db->select('inhouse_material_item','*',array('im_id'=>$im_id));
						while($rPOI = $db->fetch_array($qPOI)):
							$amount = $rPOI['cost'] * $rPOI['quantity'];
							$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
							$amount = $amount - $disc_amount;
							$total_amount += $amount;
						?>
						<tr>
							<td><?php echo $rPOI['item'];?></td>
							<td><?php echo round($rPOI['quantity'],2);?></td>
							<td><?php echo $rPOI['unit'];?></td>
							<td><?php echo $rPOI['brand'];?></td>
							<td><div align="right"><?php echo functions::formatMoney($rPOI['cost']);?></div></td>
							<td><div align="center"><?php echo $rPOI['discount'];?>%</div></td>
							<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><div align="right">Total Amount</div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
						</tr>
					</tbody>
				</table><p>&nbsp;</p>
				<?php endwhile;?>
				<table width="100%" border="0" align="center" class="table" style="font-size: 14px;">
					<tr>
						<td width="20%">&nbsp;</td>
						<td width="7%">&nbsp;</td>
						<td width="8%">&nbsp;</td>
						<td width="15%">&nbsp;</td>
						<td width="8%">&nbsp;</td>
						<td width="9%"><div align="right">Overall Amount</div></td>
						<td width="8%"><strong><?php echo functions::formatMoney($totalAmount)?></strong></td>
					</tr>
				</table>
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
<!-- end: JavaScript-->
</body>
</html>