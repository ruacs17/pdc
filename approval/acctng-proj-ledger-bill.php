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
function monthDays($month=0,$year=0){
	$list = array();
	$month = ($month) ? $month : date('m');
	$year = ($year) ? $year : date('Y');
	for($d=1; $d<=31; $d++):
		$time = mktime(12,0,0,$month,$d,$year);
		if( date('m',$time)==$month )
			$list[]=date('Y-m-d',$time);
	endfor;
	return $list;
}

$yr = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');
$mn = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : '';

if( isset($_POST['btnSearch']) ){
	$year = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? functions::decode($_POST['bdYear']) : date('Y');
	$selMonth = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['act_selMonth']=$selMonth;
	$_SESSION['act_selYr']=$year;
	functions::sendTo($_SERVER['PHP_SELF']);
	die();
}
$selMonth = ( isset($_SESSION['act_selMonth']) && !empty($_SESSION['act_selMonth']) ) ? $_SESSION['act_selMonth'] : $mn;
$year = ( isset($_SESSION['act_selYr']) && !empty($_SESSION['act_selYr']) ) ? $_SESSION['act_selYr'] : $yr;

if($selMonth && $year)
	$arrdte = array('LEFT(submit_date,7)'=>$year.'-'.$selMonth);
elseif($year)
	$arrdte = array('LEFT(submit_date,4)'=>$year);  

$arrPI = array();
$pid = functions::decode('MTE5');
$qPI = $db->select('project_income pi, project p','p.proj_name,pi.*',$arrdte,'AND p.proj_id=pi.proj_id ORDER BY submit_date');
while($rPI = $db->fetch_array($qPI)):
	$arrPI[$rPI['submit_date']][] = array('pi_id'=>$rPI['pi_id'],'bill_id'=>$rPI['bill_id'],'name'=>$rPI['name'],'proj_name'=>$rPI['proj_name'],'proj_id'=>$rPI['proj_id'],'pi_date'=>$rPI['pi_date'],'submit_date'=>$rPI['submit_date'],'amount'=>$rPI['amount'],'vat'=>$rPI['vat'],'ewt'=>$rPI['ewt'],'retention'=>$rPI['retention'],'contractor'=>$rPI['contractor'],'recoupment'=>$rPI['recoupment']);
endwhile;
/*echo '<pre>';
print_r($arrPI);
echo '</pre>';*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Project Billing Ledger</title>
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
	<style>
	/* Style the header */
	.header {
		background: #CCC;
	}
	/* The sticky class is added to the header with JS when it reaches its scroll position */
	.sticky {
		position: fixed;
		top: 0;
		width: 97%
	}
	.hideit{
		display:none;
	}
	.brdrNone{
		border:none;
	}
	</style>
    <!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Billing</h2>
		</div>
		<div class="box-content" align="center">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="acctng-proj-ledger-advance.php">Advances</a></li>
				<li><a href="acctng-proj-ledger-collect.php">Collected</a></li>
				<li class="active"><a href="acctng-proj-ledger-bill.php" style="opacity:.9">Billing</a></li>
			</ul>
			<form method="post">
				<div align="center"><br>&nbsp;&nbsp;
					<select name="bdYear" id="bdYear">
						<option value="">--Select Year--</option>
						<?php
						$qYr = $db->query('SELECT DISTINCT left(date_start,4) as dt FROM project WHERE date_start IS NOT NULL ORDER BY date_start DESC ');
						while($rYr = $db->fetch_array($qYr)):
						?>
						<option value="<?php echo functions::encode($rYr['dt'])?>" <?php if($year==$rYr['dt'])echo 'selected="selected"';?>><?php echo $rYr['dt']?></option>
						<?php endwhile;?>
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
					<input type="submit" name="btnSearch" id="btnSearch" value="View" class="btn btn-primary btn-small">
				</div>
				<div class="box-content">
					<table class="table table-bordered table-hover" style="font-size:14px;">
						<thead>
							<tr>
								<th width="8%">Date</th>
								<th width="6%">Billing ID</th>
								<th width="20%">Particular</th>
								<th width="8%"><div align="right">DEBIT</div></th>
								<th width="8%"><div align="right">CREDIT</div></th>
								<th width="3%"><div align="right">&nbsp;</div></th>
								<th width="4%"><div align="right">VAT</div></th>
								<th width="4%"><div align="right">WTAX</div></th>
								<th width="4%"><div align="right">Retention</div></th>
								<th width="4%"><div align="right">Recoupment</div></th>
							</tr>
							<tr><td colspan="10">&nbsp;</td></tr>
							<tr class="header" id="myHeader">
								<th style="border:none;" width="6%">Date</th>
								<th style="border:none;" width="4%">Billing ID</th>
								<th style="border:none;" width="12%">Particular</th>
								<th style="border:none;" width="6%"><div align="right">DEBIT</div></th>
								<th style="border:none;" width="6%"><div align="right">CREDIT</div></th>
								<th style="border:none;" width="3%"><div align="right">&nbsp;</div></th>
								<th style="border:none;" width="4%"><div align="right">VAT</div></th>
								<th style="border:none;" width="4%"><div align="right">WTAX</div></th>
								<th style="border:none;" width="4%"><div align="right">Retention</div></th>
								<th style="border:none;" width="4%"><div align="right">Recoupment</div></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$balance=0;$totalDebit=0;$totalCredit=0;$monthlyDebit=0;$monthlyCredit=0;
						$totalVAT=0;$monthlyVAT=0;$totalEWT=0;$monthlyEWT=0;$totalRET=0;$monthlyRET=0;$totalRECOUP=0;$monthlyRECOUP=0;
						for($m = 1; $m <= 12; $m++):
							$monthlyCredit=0;$monthlyDebit=0;$monthlyVAT=0;$monthlyEWT=0;$monthlyRET=0;$monthlyRECOUP=0;
							$time = mktime(0,0,0,$m,1,$year);
							$monthName = date('F',$time);
							$days = monthDays($m,$year);

							if($selMonth==$m || $selMonth == ''){
								echo '
							<tr>
								<td colspan="10"><div align="left"><strong>Month Of '.$monthName.'</strong></div></td>
							</tr>
								';
							}
							foreach($days as $day):
								if(isset($arrPI[$day])){
									foreach($arrPI[$day] as $rpi):
										$debt=0;$credt=0;$cons_rev=0;$vat_output=0;$billedAmount=0;$vat=0;$ewt=0;$retention=0;$contax=0;$recoupment=0;
										if($rpi['name']!="Advance Payment" && $rpi['name']!="Retention Payment"){
											$vat = $rpi['vat'];
											$ewt = $rpi['ewt'];
											$retention = $rpi['retention'];
											$contax = $rpi['contractor'];
											$recoupment = $rpi['recoupment'];

											$billedAmount=$rpi['amount'];
											$monthlyDebit+=$billedAmount;
											$totalDebit+=$billedAmount;
											$cons_rev = ($billedAmount) ? $billedAmount / 1.12 : 0;
											$vat_output = $cons_rev * .12;
											$monthlyCredit+=$cons_rev + $vat_output;
											$totalCredit+=$cons_rev + $vat_output;
											$debt = $billedAmount;
											$credt = $cons_rev + $vat_output;

											$totalVAT+=$vat;$monthlyVAT+=$vat;
											$totalEWT+=$ewt;$monthlyEWT+=$ewt;
											$totalRET+=$retention;$monthlyRET+=$retention;
											$totalRECOUP+=$recoupment;$monthlyRECOUP+=$recoupment;
										}
						?>
							<tr>
								<td colspan="10">
								Project: <strong><?php echo $rpi['proj_name']?></strong><br><br>
								Transaction: <strong><i><?php echo $rpi['name']?></i></strong>
								</td>
							</tr>
							<tr>
								<td><?php echo functions::datearr($day);?></td>
								<td><a id="view<?php echo $rpi['pi_id']?>" class="thickbox" title="Modify Billing ID" data-rel="tooltip" style="cursor:pointer;" onclick="showThis(this.id,'acctng-proj-billing-edit.php?piid=<?php echo functions::encode($rpi['pi_id'])?>','Statement Details')"><?php echo ($rpi['bill_id']) ? $rpi['bill_id'] : '<i class="halflings-icon pencil"></i>';?></a></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<?php if($rpi['name']=="Advance Payment" || $rpi['name']=="Retention Payment"){?>
							<tr>
								<td></td>
								<td></td>
								<td>Request For <?php echo $rpi['name']?></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<?php }else{?>
							<tr>
								<td></td>
								<td></td>
								<td>Accounts Receivable - Trade</td>
								<td><div align="right"><?php echo functions::formatMoney($billedAmount)?></div></td>
								<td></td>
								<td></td>
								<td><div align="right"><?php echo ($vat) ? functions::formatMoney($vat) : '';?></div></td>
								<td><div align="right"><?php echo ($ewt) ? functions::formatMoney($ewt) : '';?></div></td>
								<td><div align="right"><?php echo ($retention) ? functions::formatMoney($retention) : '';?></div></td>
								<td><div align="right"><?php echo ($recoupment) ? functions::formatMoney($recoupment) : '';?></div></td>
							</tr>
							<tr>
								<td></td>
								<td></td>
								<td><div style="padding-left:55px;">Construction Revenue</div></td>
								<td></td>
								<td><div align="right"><?php echo functions::formatMoney($cons_rev)?></div></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td></td>
								<td></td>
								<td><div style="padding-left:55px;">VAT Output</div></td>
								<td></td>
								<td><div align="right"><?php echo functions::formatMoney($vat_output)?></div></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td></td>
								<td></td>
								<td><div >&nbsp;</div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($debt)?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($credt)?></strong></div></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<?php }?>
							<tr><td colspan="10">&nbsp;</td></tr>
							<?php
								endforeach;
							}//if(isset($arrPI[$day])){

							endforeach; #end for each day
							if($selMonth==$m || $selMonth == ''){
						?>
							<tr>
								<td colspan="3"><div align="right"><strong> End of <?php echo $monthName.' '.$year;?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($monthlyDebit);?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($monthlyCredit);?></strong></div></td>
								<td></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($monthlyVAT);?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($monthlyEWT);?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($monthlyRET);?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($monthlyRECOUP);?></strong></div></td>
							</tr>
							<tr>
								<td colspan="3"><div align="right"><strong>Cumulative report until <?php echo $monthName.' '.$year;?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($totalDebit);?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($totalCredit);?></strong></div></td>
								<td></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($totalVAT);?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($totalEWT);?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($totalRET);?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($totalRECOUP);?></strong></div></td>
							</tr>
							<tr>
								<td colspan="10"><hr width="100%" style="color:#FF0000"></td>
							</tr>
						<?php
							}
						endfor; #end for each month
						?>
							<tr>
								<td colspan="10"><hr width="100%" style="color:#FF0000"></td>
							</tr>
							<tr>
								<td colspan="3"><div align="right"><strong>End of Year <?php echo $year?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($totalDebit);?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($totalCredit);?></strong></div></td>
								<td></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($totalVAT);?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($totalEWT);?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($totalRET);?></strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($totalRECOUP);?></strong></div></td>
							</tr>
						</tbody>
					</table>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
// When the user scrolls the page, execute myFunction
window.onscroll = function() {myFunction()};
window.onload = function(){header.classList.add("hideit");};

// Get the header
var header = document.getElementById("myHeader");

// Get the offset position of the navbar
var sticky = header.offsetTop;

// Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
function myFunction(){
	if (window.pageYOffset > sticky){
		header.classList.add("sticky");
		header.classList.remove("hideit");
	}else{
		header.classList.remove("sticky");
		header.classList.add("hideit");
	}
}
</script>
<!-- end: JavaScript-->
</body>
</html>