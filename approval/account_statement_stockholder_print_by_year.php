<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');

$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));

$yr = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');
$selYear = ( isset($_SESSION['shy_selYr']) ) ? $_SESSION['shy_selYr'] : $yr;
$arr = array('transaction_type'=>'stock share');
if($selYear)
	$arr = array_merge(array('LEFT(as_date,4)'=>$selYear));

$stocktype = ( isset($_SESSION['shy_stype']) && !empty($_SESSION['shy_stype']) ) ? $_SESSION['shy_stype'] : '';
if($stocktype)
	$arr = array_merge($arr,array('stocktype'=>$stocktype));

$qShow = $db->query("SELECT emp.emp_id,emp.lname,emp.fname,
(select sum(ast1.as_amount) from account_statement ast1, stockshare sh1, employee emp1 where sh1.stocktype=sh.stocktype and ast1.transaction_type=ast.transaction_type AND ast1.as_id=sh1.as_id AND sh1.emp_id=emp1.emp_id AND emp1.emp_id=emp.emp_id AND LEFT(ast1.as_date,4)='".$db->clean($selYear)."') as selyear,
(select sum(ast2.as_amount) from account_statement ast2, stockshare sh2, employee emp2 where sh2.stocktype=sh.stocktype and ast2.transaction_type=ast.transaction_type AND ast2.as_id=sh2.as_id AND sh2.emp_id=emp2.emp_id AND emp2.emp_id=emp.emp_id AND LEFT(ast2.as_date,4)<='".$db->clean($selYear)."') as commulative,
(select sum(ast3.as_amount) from account_statement ast3, stockshare sh3, employee emp3 where sh3.stocktype=sh.stocktype and ast3.transaction_type=ast.transaction_type AND ast3.as_id=sh3.as_id AND sh3.emp_id=emp3.emp_id AND emp3.emp_id=emp.emp_id) as currentbalance
FROM account_statement ast, stockshare sh, employee emp WHERE stocktype='".$db->clean($stocktype)."' and transaction_type='stock share' AND ast.as_id=sh.as_id AND sh.emp_id=emp.emp_id GROUP BY emp.emp_id,emp.lname,emp.fname ORDER BY emp.lname,emp.fname ASC");
#echo $db->last_query;
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
			<table border="1" width="100%" cellpadding="3" style="font-size:10px;">
				<thead>
					<tr style="background-color:#E4E1E1">
						<th width="20%"><div align="center">Name</div></th>
						<th width="15%"><div align="center"><?php if($selYear){echo ($selYear==date('Y')) ? 'Jan 01, '.$selYear.' to<br> '.date('M d, Y') : 'Jan 01, '.$selYear.' - Dec 31, '.$selYear;}else{echo '----';}  ?></div></th>
						<th width="15%"><div align="center">Commulative up to <br><?php if($selYear){echo ($selYear==date('Y')) ? date('M d, Y') : 'Dec 31, '.$selYear;}else{echo '----';}  ?></div></th>
						<th width="15%"><div align="center">Current Balance as of <br><?php echo date('M d, Y'); ?></div></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$thisYear=0;$commulative=0;$currentbal=0;
					while($r = $db->fetch_array($qShow)):
						$thisYear+=$r['selyear']; $commulative+=$r['commulative'];$currentbal+=$r['currentbalance'];
					?>
					<tr>
						<td><div align="left"><?php echo $r['lname'].', '.$r['fname']; ?></div></td>
						<td><div align="center"><?php echo functions::formatMoney($r['selyear']); ?></div></td>
						<td><div align="center"><?php echo functions::formatMoney($r['commulative']); ?></div></td>
						<td><div align="center"><?php echo functions::formatMoney($r['currentbalance']); ?></div></td>
					</tr>
					<?php endwhile; ?>
					<tr>
						<td><div align="center"></div></td>
						<td><div align="center"><strong><?php echo functions::formatMoney($thisYear); ?></strong></div></td>
						<td><div align="center"><strong><?php echo functions::formatMoney($commulative); ?></strong></div></td>
						<td><div align="center"><strong><?php echo functions::formatMoney($currentbal); ?></strong></div></td>
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
	window.location="account_statement_stockholder_list_by_year.php";
}
</script>
<!-- end: JavaScript-->
</body>
</html>