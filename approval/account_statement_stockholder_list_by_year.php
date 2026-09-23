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
	$_SESSION['shy_selYr'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? functions::decode($_POST['bdYear']) : '';
	$_SESSION['shy_stype']=( isset($_POST['selSType']) && !empty($_POST['selSType']) ) ? $_POST['selSType'] : '';
	functions::sendTo(functions::pageName());
	die();
}
$txStockholder='';
$yr = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');

$selYear = ( isset($_SESSION['shy_selYr']) ) ? $_SESSION['shy_selYr'] : $yr;

$arr = array('transaction_type'=>'stock share');
if($selYear)
	$arr = array_merge(array('LEFT(as_date,4)'=>$selYear));


$stocktype = ( isset($_SESSION['shy_stype']) && !empty($_SESSION['shy_stype']) ) ? $_SESSION['shy_stype'] : '';
if($stocktype)
	$arr = array_merge($arr,array('stocktype'=>$stocktype));

$qYr = $db->query('SELECT max(LEFT(as_date,4)) as yrTo,min(LEFT(as_date,4)) as yrFrm FROM `account_statement` ast, stockshare sh WHERE ast.as_id=sh.as_id');
$rYr = $db->fetch_array($qYr);
$yrFrm = $rYr['yrFrm'];
$yrTo = $rYr['yrTo'];

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
				<li class="active"><a href="account_statement_stockholder_list_by_year.php" style="opacity:.9">Yearly Report</a></li>
				<li><a href="account_statement_stockholder_list.php">List View</a></li>
			</ul>
			<div align="right"><a id="mrPrint" href="account_statement_stockholder_print_by_year.php?" class="btn btn-info btn-small"><i class="halflings-icon white print"></i></a></div>
			
			<form class="form-horizontal" method="post">
				<div align="center"><br>&nbsp;&nbsp;
					<select name="bdYear" id="bdYear" style="width:120px;">
						<option value="">--Select Year--</option>
						<?php for($y=$yrTo;$y>=$yrFrm;$y--):?>
						<option value="<?php echo functions::encode($y);?>" <?php if($selYear==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
						<?php endfor;?>
					</select>
					<select name="selSType" id="selSType">
						<option value="">--Select Type--</option>
						<option value="common" <?php if($stocktype=='common')echo 'selected="selected"';?>>COMMON</option>
						<option value="preferred" <?php if($stocktype=='preferred')echo 'selected="selected"';?>>PREFERRED</option>
					</select>
					<input type="submit" name="btnSearch" id="btnSearch" value="View" class="btn btn-primary btn-small">
				</div>
				<br>
				<div align="center">
					<div style="width:75%">
						<table align="center" border="0" class="table table-bordered table-hover table-striped" style="font-size:12px;" >
							<thead>
								<tr style="background-color:#CCC">
									<th width="20%"><div align="left">Name</div></th>
									<th width="15%"><div align="center"><?php if($selYear){echo ($selYear==date('Y')) ? 'Jan 01, '.$selYear.' to '.date('M d, Y') : 'Jan 01, '.$selYear.' - Dec 31, '.$selYear;}else{echo '----';}  ?></div></th>
									<th width="15%"><div align="center">Commulative up to <?php if($selYear){echo ($selYear==date('Y')) ? date('M d, Y') : 'Dec 31, '.$selYear;}else{echo '----';}  ?></div></th>
									<th width="15%"><div align="center">Current Balance as of <?php echo date('M d, Y'); ?></div></th>
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
									<td><div align="right"><?php echo functions::formatMoney($r['selyear']); ?></div></td>
									<td><div align="right"><?php echo functions::formatMoney($r['commulative']); ?></div></td>
									<td><div align="right"><?php echo functions::formatMoney($r['currentbalance']); ?></div></td>
								</tr>
								<?php endwhile; ?>
								<tr>
									<td><div align="center"></div></td>
									<td><div align="right"><strong><?php echo functions::formatMoney($thisYear); ?></strong></div></td>
									<td><div align="right"><strong><?php echo functions::formatMoney($commulative); ?></strong></div></td>
									<td><div align="right"><strong><?php echo functions::formatMoney($currentbal); ?></strong></div></td>
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