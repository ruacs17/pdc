<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');

if( isset($_POST['btnSearch']) ){
	$_SESSION['sh_emp'] = ( isset($_POST['txStockholder']) && !empty($_POST['txStockholder']) ) ? $_POST['txStockholder'] : '';
	$year = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? functions::decode($_POST['bdYear']) : '';
	$selMonth = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$account_type = ( isset($_POST['txVoType']) && !empty($_POST['txVoType']) ) ? $_POST['txVoType'] : '';
	$_SESSION['sh_acType']=$account_type;
	$_SESSION['sh_selMonth']=$selMonth;
	$_SESSION['sh_selYr']=$year;
	$_SESSION['sh_stype']=( isset($_POST['selSType']) && !empty($_POST['selSType']) ) ? $_POST['selSType'] : '';
	functions::sendTo(functions::pageName());
	die();
}
$txStockholder='';
$yr = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');
$mn = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : '';
$ac_type = ( isset($_REQUEST['t']) && !empty($_REQUEST['t']) ) ? functions::decode($_REQUEST['t']) : '';

$stockholder = ( isset($_SESSION['sh_emp']) && !empty($_SESSION['sh_emp']) ) ? $_SESSION['sh_emp'] : '';
$account_type = ( isset($_SESSION['sh_acType']) && !empty($_SESSION['sh_acType']) ) ? $_SESSION['sh_acType'] : $ac_type;
$selMonth = ( isset($_SESSION['sh_selMonth']) && !empty($_SESSION['sh_selMonth']) ) ? $_SESSION['sh_selMonth'] : $mn;
$selYear = ( isset($_SESSION['sh_selYr']) ) ? $_SESSION['sh_selYr'] : $yr;

$arr = array('transaction_type'=>'stock share');
if($selMonth && $selYear)
	$arr = array_merge(array('LEFT(as_date,7)'=>$selYear.'-'.$selMonth));
else if($selMonth)
	$arr = array_merge(array('SUBSTRING(as_date,6,2)'=>$selMonth));
elseif($selYear)
	$arr = array_merge(array('LEFT(as_date,4)'=>$selYear));

if($stockholder)
	$arr = array_merge($arr,array('emp.emp_id'=>$stockholder));

if($account_type)
	$arr = array_merge($arr,array('account_type'=>$account_type));

$stocktype = ( isset($_SESSION['sh_stype']) && !empty($_SESSION['sh_stype']) ) ? $_SESSION['sh_stype'] : '';
if($stocktype)
	$arr = array_merge($arr,array('stocktype'=>$stocktype));

$qYr = $db->query('SELECT max(LEFT(as_date,4)) as yrTo,min(LEFT(as_date,4)) as yrFrm FROM `account_statement` ast, stockshare sh WHERE ast.as_id=sh.as_id');
$rYr = $db->fetch_array($qYr);
$yrFrm = $rYr['yrFrm'];
$yrTo = $rYr['yrTo'];

$qShow = $db->select('account_statement ast, stockshare sh, employee emp','*',$arr,'AND ast.as_id=sh.as_id AND sh.emp_id=emp.emp_id ORDER BY as_date ASC');
$arrAS = array();$countRow=0;
while($rShow = $db->fetch_array($qShow)):
	$arrAS[$countRow]['as_amount']=$rShow['as_amount'];
	$arrAS[$countRow]['as_date']=$rShow['as_date'];
	$arrAS[$countRow]['transaction']=$rShow['transaction'];
	$arrAS[$countRow]['fname']=$rShow['fname'];
	$arrAS[$countRow]['lname']=$rShow['lname'];
	$arrAS[$countRow]['account_type']=$rShow['account_type'];
	$arrAS[$countRow]['as_id']=$rShow['as_id'];
	$arrAS[$countRow]['stocktype']=$rShow['stocktype'];
	$countRow++;
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>STOCKHOLDER LIST</title>
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
	<link href="../css/select2.min.css" rel="stylesheet">
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>STOCKHOLDER TRANSACTION DETAIL</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="account_statement_stockholder_list_by_year.php">Yearly Report</a></li>
				<li class="active"><a href="account_statement_stockholder_list.php" style="opacity:.9">List View</a></li>
			</ul>
			<div align="right"><a id="mrPrint" href="account_statement_stockholder_print.php?" class="btn btn-info btn-small"><i class="halflings-icon white print"></i></a></div>
			<form class="form-horizontal" method="post">
				<div align="center">
					<select name="txStockholder" id="txStockholder" style="width:300px;text-align:left;">
						<option value="">-- All Stockholders --</option>
						<?php $qEU = $db->query('SELECT * FROM employee WHERE emp_id IN (SELECT DISTINCT emp_id FROM stockshare) ORDER BY lname');
						while($rEU = $db->fetch_array($qEU)):
						?>
						<option value="<?php echo $rEU['emp_id']?>" <?php if($stockholder==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
						<?php endwhile;?>
					</select>
					<select name="bdYear" id="bdYear" style="width:120px;">
						<option value="">--All Year--</option>
						<?php for($y=$yrTo;$y>=$yrFrm;$y--):?>
						<option value="<?php echo functions::encode($y);?>" <?php if($selYear==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
						<?php endfor;?>
					</select>
					<select name="bdMon" id="bdMon" style="width:95px;">
						<option value="">All Month</option>
						<option value="01" <?php if($selMonth=='01')echo 'selected="selected"';?>>Jan</option>
						<option value="02" <?php if($selMonth=='02')echo 'selected="selected"';?>>Feb</option>
						<option value="03" <?php if($selMonth=='03')echo 'selected="selected"';?>>Mar</option>
						<option value="04" <?php if($selMonth=='04')echo 'selected="selected"';?>>Apr</option>
						<option value="05" <?php if($selMonth=='05')echo 'selected="selected"';?>>May</option>
						<option value="06" <?php if($selMonth=='06')echo 'selected="selected"';?>>Jun</option>
						<option value="07" <?php if($selMonth=='07')echo 'selected="selected"';?>>Jul</option>
						<option value="08" <?php if($selMonth=='08')echo 'selected="selected"';?>>Aug</option>
						<option value="09" <?php if($selMonth=='09')echo 'selected="selected"';?>>Sep</option>
						<option value="10" <?php if($selMonth=='10')echo 'selected="selected"';?>>Oct</option>
						<option value="11" <?php if($selMonth=='11')echo 'selected="selected"';?>>Nov</option>
						<option value="12" <?php if($selMonth=='12')echo 'selected="selected"';?>>Dec</option>
					</select>
					<select name="selSType" id="selSType">
						<option value="">All Type</option>
						<option value="common" <?php if($stocktype=='common')echo 'selected="selected"';?>>COMMON</option>
						<option value="preferred" <?php if($stocktype=='preferred')echo 'selected="selected"';?>>PREFERRED</option>
					</select>
					<select name="txVoType" id="txVoType">
						<option value="">All Account</option>
						<?php 
						$qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
						while($rVtype = $db->fetch_array($qVtype)):
						?>
						<option value="<?php echo $rVtype['vt_id']?>" <?php if($account_type==$rVtype['vt_id'])echo 'selected="selected"';?> ><?php echo $rVtype['vt_name']?></option>
						<?php endwhile;?>
					</select>
					<input type="submit" name="btnSearch" id="btnSearch" value="View" class="btn btn-primary btn-small">
				</div>
				<br>
				<div align="center">
				<div style="width:95%">
				<table align="center" border="0" class="table table-bordered table-striped table-hover" style="font-size:12px;" >
					<thead>
					<tr style="background-color:#CCC;">
						<th width="12%"><div align="center">Date</div></th>
						<th width="40%"><div align="center">Transaction</div></th>
						<th width="12%"><div align="center">Stock Type</div></th>
						<th width="15%"><div align="center">Account</div></th>
						<th width="12%"><div align="right">Amount</div></th>
						<th width="15%"><div align="right">Balance</div></th>
					</tr>
					</thead>
					<tbody>
					<?php
					$totalDeposit=0;$runningBalance=0;$countRow=0;
					$curMonth='';$changed=0;$displayMonth='';$monthlyTotal=0;
					foreach($arrAS as $rShow):
						$totalDeposit+=$rShow['as_amount'];
						$runningBalance += $rShow['as_amount'];
						$curMonth =  substr($rShow['as_date'], 0,7);
						$monthlyTotal += $rShow['as_amount'];
						if($curMonth != $displayMonth){
							$displayMonth =  $curMonth;
							$changed=1;
						}
					?>
					<?php if($changed){?>
					<tr>
						<td colspan="6"><div align="left"><strong><?php echo date('F Y',strtotime($rShow['as_date'])); ?></strong></div></td>
					</tr>
					<?php }?>
					<tr>
						<td><div align="center"><?php echo functions::datearr($rShow['as_date']);?></div></td>
						<td>
							<div align="left">
							<?php
							echo $rShow['transaction'];
							echo ($rShow['lname']) ? '&nbsp;&nbsp;&nbsp;<i>('.$rShow['lname'].', '.$rShow['fname'].')</i>' : '';
							?>
							</div>
						</td>
						<td><div align="center"><?php echo $rShow['stocktype']?></div></td>
						<td><div align="center"><?php echo $db->getValue('voucher_type','vt_name',array('vt_id'=>$rShow['account_type']))?></div></td>
						<td><div align="right"><a id="prnt<?php echo $rShow['as_id'] ?>" style="cursor:pointer;" class="thickbox" onclick="showThis(this.id,'account_statement_edit_stockholder.php?eid=<?php echo functions::encode($rShow['as_id']) ?>','Statement Detail')"><?php echo functions::formatMoney($rShow['as_amount'])?></a></div></td>
						<td><div align="right"><?php echo functions::formatMoney($runningBalance)?></div></td>
					</tr>
					<?php
					$nxDate = isset($arrAS[($countRow+1)]['as_date']) ? substr($arrAS[($countRow+1)]['as_date'], 0,7) : '';
					if( substr($rShow['as_date'], 0,7) != $nxDate ){?>
					<tr>
						<td style="background-color:#E4E1E1" colspan="4"><div align="right"><strong>For the month of <?php echo date('F Y',strtotime($rShow['as_date'])); ?></strong></div></td>
						<td style="background-color:#E4E1E1"><div align="right"><strong><?php echo functions::formatMoney($monthlyTotal)?></strong></div></td>
						<td style="background-color:#E4E1E1">&nbsp;</td>
					</tr>
					<?php $monthlyTotal=0;} ?>
					<?php $countRow++;$changed=0; endforeach;?>
					<tr>
						<td colspan="6">&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><div align="right"><strong><?php echo functions::formatMoney($totalDeposit)?></strong></div></td>
					</tr>
					</tbody>
				</table>
				</div>
				</div>
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
<script src="../js/select2.js"></script>
<script type="text/javascript">$("#txStockholder").select2(); </script>
<!-- end: JavaScript-->
</body>
</html>