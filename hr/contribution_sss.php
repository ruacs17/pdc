<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/read_excel_xlsx.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
$delid = (isset($_REQUEST['delid']) && !empty($_REQUEST['delid']) ) ? functions::decode($_REQUEST['delid']) : '';
$highlight_steady=0;
if($delid){
	$db->delete('table_sss',array('tsss_id'=>$delid));
	$_SESSION['notif_warning']='Reference Removed!';
	functions::sendTo(functions::pageName());
	die();
}
$add = (isset($_REQUEST['add']) && !empty($_REQUEST['add']) ) ? 1: 0;
if($add){
	$_SESSION['notif_id_list']='add';
	$highlight_steady=1;
}
	
$sss_id = (isset($_REQUEST['sssid']) && !empty($_REQUEST['sssid']) ) ? functions::decode($_REQUEST['sssid']) : 0;
$q = $db->select('table_sss','*',array('tsss_id'=>$sss_id));
$r = $db->fetch_array($q);
if($r['tsss_id']){
	$highlight_steady=1;
	$_SESSION['notif_id_list']=$r['tsss_id'];
}
	
$sal_from = ($r['sal_from']) ? functions::formatMoney($r['sal_from']) : '';
$sal_to = ($r['sal_to']) ? functions::formatMoney($r['sal_to']) : '';
$msc = ($r['msc']) ? functions::formatMoney($r['msc']) : '';
$provident = ($r['provident']) ? functions::formatMoney($r['provident']) : '';
$msc_total = ($r['provident'] || $r['msc']) ? functions::formatMoney($r['provident'] + $r['msc']) : '';
$prov_er = ($r['prov_er']) ? functions::formatMoney($r['prov_er']) : '';
$prov_ee = ($r['prov_ee']) ? functions::formatMoney($r['prov_ee']) : '';
$prov_total = ($r['prov_total']) ? functions::formatMoney($r['prov_total']) : '';
$sss_er_share = ($r['sss_er_share']) ? functions::formatMoney($r['sss_er_share']) : '';
$sss_ee_share = ($r['sss_ee_share']) ? functions::formatMoney($r['sss_ee_share']) : '';
$sss_ec = ($r['sss_ec']) ? functions::formatMoney($r['sss_ec']) : '';
$sss_total = ($r['sss_total']) ? functions::formatMoney($r['sss_total']) : '';
$tc_er = ($r['tc_er']) ? functions::formatMoney($r['tc_er']) : '';
$tc_ee = ($r['tc_ee']) ? functions::formatMoney($r['tc_ee']) : '';
$tc_total = ($r['tc_total']) ? functions::formatMoney($r['tc_total']) : '';
#$logs = new Logs();
#$logs->save('visit');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>SSS Contribution</title>
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
	<script src="../js/inputInt.js"></script>
<?php
if( isset($_POST['btnSave']) ){
	$sal_from = ( isset($_POST['txSalFrom']) && !empty($_POST['txSalFrom']) ) ? functions::moneyToDouble($_POST['txSalFrom']) : 0;
	$sal_to = ( isset($_POST['txSalTo']) && !empty($_POST['txSalTo']) ) ? functions::moneyToDouble($_POST['txSalTo']) : 0;
	$msc = ( isset($_POST['txMSC']) && !empty($_POST['txMSC']) ) ? functions::moneyToDouble($_POST['txMSC']) : 0;
	$sss_er_share = ( isset($_POST['txER']) && !empty($_POST['txER']) ) ? functions::moneyToDouble($_POST['txER']) : 0;
	$sss_ee_share = ( isset($_POST['txEE']) && !empty($_POST['txEE']) ) ? functions::moneyToDouble($_POST['txEE']) : 0;
	$sss_ec = ( isset($_POST['txEC']) && !empty($_POST['txEC']) ) ? functions::moneyToDouble($_POST['txEC']) : 0;
	$provident = ( isset($_POST['txProv']) && !empty($_POST['txProv']) ) ? functions::moneyToDouble($_POST['txProv']) : 0;
	$prov_er = ( isset($_POST['txProvER']) && !empty($_POST['txProvER']) ) ? functions::moneyToDouble($_POST['txProvER']) : 0;
	$prov_ee = ( isset($_POST['txProvEE']) && !empty($_POST['txProvEE']) ) ? functions::moneyToDouble($_POST['txProvEE']) : 0;
	$sss_total = $sss_er_share + $sss_ee_share;
	$prov_total = $prov_er + $prov_ee;
	$tc_er = $sss_er_share + $sss_ec + $prov_er;
	$tc_ee = $sss_ee_share + $prov_ee;
	$tc_total = $tc_er + $tc_ee;


	$field = array('sal_from'=>$sal_from,'sal_to'=>$sal_to,'msc'=>$msc,'sss_er_share'=>$sss_er_share,'sss_ee_share'=>$sss_ee_share,'sss_ec'=>$sss_ec,'sss_total'=>$sss_total,'provident'=>$provident,'prov_er'=>$prov_er,'prov_ee'=>$prov_ee,'prov_total'=>$prov_total,'tc_er'=>$tc_er,'tc_ee'=>$tc_ee,'tc_total'=>$tc_total);
	if($sal_from && $sal_to){
		if($sss_id){
			$db->update('table_sss',$field,array('tsss_id'=>$sss_id));
			$_SESSION['notif_id_list']=$sss_id;
			$_SESSION['notif_success']='Changes saved!';
			functions::sendTo(functions::pageName());
			#functions::sendTo(functions::pageName().'?sssid='.functions::encode($sss_id));
			die();
		}
		else{
			if( $db->getValue('table_sss','count(*)',array('sal_from'=>$sal_from))==0 ){
				$ins = $db->insert('table_sss',$field);
				if( $db->getValue('table_sss','count(*)',array('sal_from'=>$sal_from)) ){
					$_SESSION['notif_id_list']=$ins;
					$_SESSION['notif_success']='Reference Successfully Added!';
					functions::sendTo(functions::pageName());
					die();
				}
				else
					functions::say('Fail to insert.');
			}
			else
				functions::say('Reference already Exist!');
		}		
	}
	else
		functions::say('Please fill up the form properly!');
}
?>
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
	<style type="text/css">
	.padright{padding-right:10px;}
	.txbox{width:90px;text-align:center;font-size:12px;}
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	.table-wrapper thead tr:nth-child(2) th { background: #DDD;position: sticky; top: 65px;}
	</style>

</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="contribution_ph.php">Philhealth</a></li>
				<li><a href="contribution_pagibig.php">HDMF</a></li>
				<li class="active"><a href="contribution_sss.php" style="opacity:.9">SSS</a></li>
				<li><a href="contribution_personnel_list.php">PERSONNEL</a></li>
			</ul>
		</div>
		<div class="box-content">
			<div align="right"><a id="adc" href="?add=add" class="btn btn-info btn-small">Add Contribution Schedule</a></div>
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>SSS Contribution</h2></div>
				<div align="center">
					<div>
						<form method="post" onSubmit="return ask()">
							<div class="table-wrapper">
								<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover table-striped" border="0" style="font-size: 12px;">
									<thead>
										<tr style="background-color:#CCC;">
											<th rowspan="2" width="20%"><div align="center">RANGE OF COMPENSATION </div></th>
											<th colspan="3" width="10%"><div align="center">MONTHLY SALARY CREDIT</div></th>
											<th colspan="3"><div align="center">REGULAR SS</div></th>
											<th width="5%"><div align="center">EMPLOYEE'S<p style="font-size:11px">COMPENSATION</p></div></th>
											<th colspan="3"><div align="center">MANDATORY PROVIDENT FUND</div></th>
											<th colspan="3"><div align="center">TOTAL CONTRIBUTION</div></th>
											<th rowspan="2" scope="col" width="8%">&nbsp;</th>
										</tr>
										<tr style="background-color:#CCC;">
											<th scope="col" width="5%"><div align="center">Employees' Compensation</div></th>
											<th scope="col" width="5%"><div align="center">Mandatory Provident Fund</div></th>
											<th scope="col" width="5%"><div align="center">Total</div></th>
											<th scope="col" width="5%"><div align="center">ER</div></th>
											<th scope="col" width="5%"><div align="center">EE</div></th>
											<th scope="col" width="5%"><div align="center">TOTAL</div></th>
											<th scope="col" width="5%"><div align="center">ER</div></th>
											<th scope="col" width="5%"><div align="center">ER</div></th>
											<th scope="col" width="5%"><div align="center">EE</div></th>
											<th scope="col" width="5%"><div align="center">TOTAL</div></th>
											<th scope="col" width="5%"><div align="center">ER</div></th>
											<th scope="col" width="5%"><div align="center">EE</div></th>
											<th scope="col" width="5%"><div align="center">TOTAL</div></th>
										</tr>
									</thead>
									<tbody>
									<?php if($add){ ?>
									<tr id="rwadd">
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txSalFrom" id="txSalFrom" value="<?php echo $sal_from ?>" placeholder="from" onkeyup="FormatCurrency(this);" required> - <input type="text" name="txSalTo" id="txSalTo" value="<?php echo $sal_to ?>" style="width:70px;text-align:center;font-size:12px;" placeholder="to" onkeyup="FormatCurrency(this);" required></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txMSC" id="txMSC" class="span6" value="<?php echo $msc?>" onkeyup="FormatCurrency(this);" required></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txProv" id="txProv" class="span6" value="<?php echo $provident?>" onkeyup="FormatCurrency(this);"></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txMSCTotal" id="txMSCTotal" class="span6" value="<?php echo $msc_total?>" readonly></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txER" id="txER" class="span6" value="<?php echo $sss_er_share?>" onkeyup="FormatCurrency(this);" required></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txEE" id="txEE" class="span6" value="<?php echo $sss_ee_share?>" onkeyup="FormatCurrency(this);" required></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txRegTotal" id="txRegTotal" class="span6" value="<?php echo $sss_total?>" readonly></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txEC" id="txEC" class="span6" value="<?php echo $sss_ec?>" onkeyup="FormatCurrency(this);" required></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txProvER" id="txProvER" class="span6" value="<?php echo $prov_er?>" onkeyup="FormatCurrency(this);"></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txProvEE" id="txProvEE" class="span6" value="<?php echo $prov_ee?>" onkeyup="FormatCurrency(this);"></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txProvTotal" id="txProvTotal" class="span6" value="<?php echo $prov_total?>" readonly></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txTotalER" id="txTotalER" class="span6" value="<?php echo $tc_er?>" readonly></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txTotalEE" id="txTotalEE" class="span6" value="<?php echo $tc_ee?>" readonly></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txTotalAll" id="txTotalAll" class="span6" value="<?php echo $tc_total?>" readonly></div></td>
										<td>
											<div align="center">
												<button type="submit" class="btn btn-primary btn-small" name="btnSave" id="btnSave" value="Save" data-rel="tooltip" title="Save"><i class="icon-save"></i></button>
												<a class="btn btn-small" title="Cancel Edit" data-rel="tooltip" href="?"><i class="icon-ban-circle"></i></a>
											</div>
										</td>
									</tr>
									<?php } ?>
									<?php
									$qDisp = $db->select('table_sss','*',array(),'ORDER BY sal_from');
									while($rDisp = $db->fetch_array($qDisp)):
									$id = $rDisp['tsss_id'];
										if($sss_id==$id){
									?>
									<tr id="rw<?php echo $id?>">
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txSalFrom" id="txSalFrom" value="<?php echo $sal_from ?>" placeholder="from" onkeyup="FormatCurrency(this);" required> - <input type="text" name="txSalTo" id="txSalTo" value="<?php echo $sal_to ?>" style="width:70px;text-align:center;font-size:12px;" placeholder="to" onkeyup="FormatCurrency(this);" required></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txMSC" id="txMSC" class="span6" value="<?php echo $msc?>" onkeyup="FormatCurrency(this);" required></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txProv" id="txProv" class="span6" value="<?php echo $provident?>" onkeyup="FormatCurrency(this);"></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txMSCTotal" id="txMSCTotal" class="span6" value="<?php echo $msc_total?>" readonly></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txER" id="txER" class="span6" value="<?php echo $sss_er_share?>" onkeyup="FormatCurrency(this);" required></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txEE" id="txEE" class="span6" value="<?php echo $sss_ee_share?>" onkeyup="FormatCurrency(this);" required></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txRegTotal" id="txRegTotal" class="span6" value="<?php echo $sss_total?>" readonly></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txEC" id="txEC" class="span6" value="<?php echo $sss_ec?>" onkeyup="FormatCurrency(this);" required></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txProvER" id="txProvER" class="span6" value="<?php echo $prov_er?>" onkeyup="FormatCurrency(this);"></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txProvEE" id="txProvEE" class="span6" value="<?php echo $prov_ee?>" onkeyup="FormatCurrency(this);"></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txProvTotal" id="txProvTotal" class="span6" value="<?php echo $prov_total?>" readonly></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txTotalER" id="txTotalER" class="span6" value="<?php echo $tc_er?>" readonly></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txTotalEE" id="txTotalEE" class="span6" value="<?php echo $tc_ee?>" readonly></div></td>
										<td><div align="center"><input type="text" style="width:70px;text-align:center;font-size:12px;" name="txTotalAll" id="txTotalAll" class="span6" value="<?php echo $tc_total?>" readonly></div></td>
										<td>
											<div align="center">
												<button type="submit" class="btn btn-primary btn-small" onClick="return ask()" name="btnSave" id="btnSave" value="Save" data-rel="tooltip" title="Save"><i class="icon-save"></i></button>
												<a class="btn btn-small" title="Cancel Edit" data-rel="tooltip" href="?"><i class="icon-ban-circle"></i></a>
											</div>
										</td>
									</tr>
									<?php }else{ ?>
									<tr id="rw<?php echo $id?>">
										<td><div align="center"><?php echo functions::formatMoney($rDisp['sal_from']).' - '.functions::formatMoney($rDisp['sal_to'])?></div></td>
										<td><div align="right" class="padright"><?php echo functions::formatMoney($rDisp['msc'])?></div></td>
										<td><div align="right" class="padright"><?php echo functions::formatMoney($rDisp['provident'])?></div></td>
										<td><div align="right" class="padright"><?php echo functions::formatMoney($rDisp['msc']+$rDisp['provident'])?></div></td>
										<td><div align="right" class="padright"><?php echo functions::formatMoney($rDisp['sss_er_share'])?></div></td>
										<td><div align="right" class="padright"><?php echo functions::formatMoney($rDisp['sss_ee_share'])?></div></td>
										<td><div align="right" class="padright"><?php echo functions::formatMoney($rDisp['sss_total'])?></div></td>
										<td><div align="center"><?php echo functions::formatMoney($rDisp['sss_ec'])?></div></td>
										<td><div align="right" class="padright"><?php echo functions::formatMoney($rDisp['prov_er'])?></div></td>
										<td><div align="right" class="padright"><?php echo functions::formatMoney($rDisp['prov_ee'])?></div></td>
										<td><div align="right" class="padright"><?php echo functions::formatMoney($rDisp['prov_total'])?></div></td>
										<td><div align="right" class="padright"><?php echo functions::formatMoney($rDisp['tc_er'])?></div></td>
										<td><div align="right" class="padright"><?php echo functions::formatMoney($rDisp['tc_ee'])?></div></td>
										<td><div align="right" class="padright"><?php echo functions::formatMoney($rDisp['tc_total'])?></div></td>
										<td>
											<div align="center">
												<a id="adc<?php echo $id?>" class="btn btn-warning btn-mini" title="Manage Contribution Reference" href="?sssid=<?php echo functions::encode($id)?>"><i class="halflings-icon white pencil"></i></a>
												<a id="del<?php echo $id?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Contribution Reference" data-rel="tooltip" href="?delid=<?php echo functions::encode($id)?>"><i class="halflings-icon white trash"></i></a>
											</div>
										</td>
									</tr>
									<?php } ?>
									<?php endwhile;?>
									</tbody>
								</table>
							</div>
						</form>
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
<script>
// When the user scrolls the page, execute myFunction
window.onscroll = function() {myFunction()};
window.onload = function(){header.classList.add("hideit");};

// Get the header
var header = document.getElementById("myHeader");

// Get the offset position of the navbar
var sticky = header.offsetTop;
sticky = 280
// Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
function myFunction() {
	if (window.pageYOffset > sticky) {
		header.classList.add("sticky");
		header.classList.remove("hideit");
	}else{
		header.classList.remove("sticky");
		header.classList.add("hideit");
	}
}
function delt(){
	if(confirm('Do you want to remove this Reference?'))
		return true;
	else
		return false; 
}
function ask(){
	if(confirm('Do you want to save this information?'))
		return true;
	else
		return false;
}

$(document).ready(function(){
	$('#txSalFrom').keyup(function(){
		var salFrom_temp = $('#txSalFrom').val();
		var	salFrom_number = salFrom_temp.replace(",", "");
		var	salFrom = Number( salFrom_number.replace(/[^0-9\.]+/g,""));
		if(salFrom>=1){
			var salTo = salFrom + 499.99;
			$('#txSalTo').val(salTo);
			FormatCurrency(document.getElementById('txSalTo'));			
		}
	});
	$('#txMSC, #txProv').keyup(function(){
		calTotal('txMSC','txProv','txMSCTotal');
	});
	$('#txEE, #txER').keyup(function(){
		calTotal('txEE','txER','txRegTotal');
	});
	$('#txProvEE, #txProvER').keyup(function(){
		calTotal('txProvEE','txProvER','txProvTotal');
	});

	function calTotal(ee,er,txid){
		var	ee_share = getNumber(ee);
		var	er_share = getNumber(er);
		var monthlyPremium = ee_share + er_share;
		$('#'+txid).val(monthlyPremium);
		FormatCurrency(document.getElementById(txid));
	}
	$('#txProvEE, #txProvER, #txEE, #txER, #txEC').keyup(function(){
		var provEE = getNumber('txProvEE');
		var provER = getNumber('txProvER');

		var regEE = getNumber('txEE');
		var regER = getNumber('txER');

		var regEC = getNumber('txEC');

		var total_er = regER + provER + regEC;
		var total_ee = regEE + provEE;
		var total_all = total_er + total_ee;

		$('#txTotalER').val(total_er);
		FormatCurrency(document.getElementById('txTotalER'));
		$('#txTotalEE').val(total_ee);
		FormatCurrency(document.getElementById('txTotalEE'));
		$('#txTotalAll').val(total_all);
		FormatCurrency(document.getElementById('txTotalAll'));
	});

	function getNumber(id){
		var ee_share_temp = $('#'+id).val();
		var	ee_share_number = ee_share_temp.replace(",", "");
		return	ee_share = Number( ee_share_number.replace(/[^0-9\.]+/g,""));
	}
});


</script>
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','3px solid green');
	<?php if( $highlight_steady==0 ){ ?>
		$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
		$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
		window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
	<?php } ?>
});
</script>
<?php unset($_SESSION['notif_id_list'],$_SESSION['notif_steady']);} ?>
<!-- end: JavaScript-->
</body>
</html>