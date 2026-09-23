<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');

$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));

$txStockholder='';
$yr = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');
$mn = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : '';
$ac_type = ( isset($_REQUEST['t']) && !empty($_REQUEST['t']) ) ? functions::decode($_REQUEST['t']) : '';

$stockholder = ( isset($_SESSION['sh_emp']) && !empty($_SESSION['sh_emp']) ) ? $_SESSION['sh_emp'] : '';
$account_type = ( isset($_SESSION['sh_acType']) && !empty($_SESSION['sh_acType']) ) ? $_SESSION['sh_acType'] : $ac_type;
$selMonth = ( isset($_SESSION['sh_selMonth']) && !empty($_SESSION['sh_selMonth']) ) ? $_SESSION['sh_selMonth'] : $mn;
$selYear = ( isset($_SESSION['sh_selYr']) ) ? $_SESSION['sh_selYr'] : $yr;
$stocktype = ( isset($_SESSION['sh_stype']) && !empty($_SESSION['sh_stype']) ) ? $_SESSION['sh_stype'] : '';
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
	$arrAS[$countRow]['stocktype']=$rShow['stocktype'];
	$countRow++;
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Account Statement Stockholder Print</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/printerfoot.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">
	.padParLeft{padding-left:50px;}
	.padAmLeft{padding-left:60px;}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table width="90%" border="0" align="center">
	<tr>
		<td>
			<?php 
			require_once('../class/print_header.php');
			print_header('ACCOUNT STATEMENT STOCKHOLDER');
			?>
		</td>
	</tr>
	<tr>
		<td valign='bottom'>
		</td>
	</tr>
	<tr>
		<td height="500" valign="top" align="center">
			<table border="1" width="100%" cellpadding="3" style="font-size:9px;">
				<thead>
					<tr style="background-color:#E4E1E1">
						<th width="8%"><div align="center">Date</div></th>
						<th width="35%"><div align="center">Transaction</div></th>
						<th width="8%"><div align="center">Stock Type</div></th>
						<th width="15%"><div align="center">Account</div></th>
						<th width="10%"><div align="right">Amount</div></th>
						<th width="10%"><div align="right">Balance</div></th>
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
						<td><div align="center" style="font-size:8px;"><?php echo $db->getValue('voucher_type','vt_name',array('vt_id'=>$rShow['account_type']))?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rShow['as_amount'])?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($runningBalance)?></div></td>
					</tr>
					<?php
					$nxDate = isset($arrAS[($countRow+1)]['as_date']) ? substr($arrAS[($countRow+1)]['as_date'], 0,7) : '';
					if( substr($rShow['as_date'], 0,7) != $nxDate ){?>
					<tr>
						<td colspan="4"><div align="right"><strong>For the month of <?php echo date('F Y',strtotime($rShow['as_date'])); ?></strong></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($monthlyTotal)?></strong></div></td>
						<td>&nbsp;</td>
					</tr>
					<?php $monthlyTotal=0;} ?>
					<?php $countRow++;$changed=0; endforeach;?>
					<tr>
						<td colspan="6">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="5"><div align="right"><strong>Ending Balance</strong></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($totalDeposit)?></strong></div></td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="account_statement_stockholder_list.php";
}
</script>
<!-- end: JavaScript-->
</body>
</html>