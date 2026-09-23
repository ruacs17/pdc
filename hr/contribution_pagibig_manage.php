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
$filename='';
$hmdf = (isset($_REQUEST['hmdf']) && !empty($_REQUEST['hmdf']) ) ? functions::decode($_REQUEST['hmdf']) : 0;
$q = $db->select('table_hdmf','*',array('thdmf_id'=>$hmdf));
$r = $db->fetch_array($q);
$sal_from = ($r['sal_from']) ? functions::formatMoney($r['sal_from']) : '';
$sal_to = ($r['sal_to']) ? functions::formatMoney($r['sal_to']) : '';
$hdmf_er_share = ($r['hdmf_er_share']) ? functions::formatMoney($r['hdmf_er_share']) : '';
$hdmf_ee_share = ($r['hdmf_ee_share']) ? functions::formatMoney($r['hdmf_ee_share']) : '';
$hdmf_total = ($r['hdmf_total']) ? functions::formatMoney($r['hdmf_total']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>HDMF CONTRIBUTION SCHEDULE</title>
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
<?php
if( isset($_POST['btnSave']) ){
	$sal_from = ( isset($_POST['txSalFrom']) && !empty($_POST['txSalFrom']) ) ? functions::moneyToDouble($_POST['txSalFrom']) : 0;
	$sal_to = ( isset($_POST['txSalTo']) && !empty($_POST['txSalTo']) ) ? functions::moneyToDouble($_POST['txSalTo']) : 0;
	$hdmf_er_share = ( isset($_POST['txER']) && !empty($_POST['txER']) ) ? functions::moneyToDouble($_POST['txER']) : 0;
	$hdmf_ee_share = ( isset($_POST['txEE']) && !empty($_POST['txEE']) ) ? functions::moneyToDouble($_POST['txEE']) : 0;
	$hdmf_total = ( isset($_POST['txTotal']) && !empty($_POST['txTotal']) ) ? functions::moneyToDouble($_POST['txTotal']) : 0;
	$hdmf_type = ( isset($_POST['selType']) && !empty($_POST['selType']) ) ? $_POST['selType'] : '';
	
	$field = array('sal_from'=>$sal_from,'sal_to'=>$sal_to,'hdmf_er_share'=>$hdmf_er_share,'hdmf_ee_share'=>$hdmf_ee_share,'hdmf_total'=>$hdmf_total,'hdmf_type'=>$hdmf_type);
	if($sal_from && $sal_to){
		if($hmdf){
			$db->update('table_hdmf',$field,array('thdmf_id'=>$hmdf));
			$_SESSION['notif_success']='Changes saved!';
			functions::sendTo(functions::pageName().'?hmdf='.functions::encode($hmdf));
			die();
		}
		else{
			if( $db->getValue('table_hdmf','count(*)',array('sal_from'=>$sal_from))==0 ){
				$db->insert('table_hdmf',$field);
				if( $db->getValue('table_hdmf','count(*)',array('sal_from'=>$sal_from)) ){
					$_SESSION['notif_success']='Reference Successfully Added.';
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>HDMF Contribution Schedule</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="center">
					<table width="70%" border="0" class="tablea" cellspacing="5" cellpadding="5">
						<tr>
							<th width="40%" scope="row" height="50px"><div align="right" style="padding-top:17px;"><strong>Salary Bracket:&nbsp;&nbsp;&nbsp;</strong></div></th>
							<td>
								<div align="left">
									<table border="0">
										<tr>
											<td align="center">From</td>
											<td align="center" style="padding-left:10px;">To</td>
										</tr>
										<tr>
											<td><input type="text" name="txSalFrom" id="txSalFrom" class="span6" value="<?php echo $sal_from?>" style="width:150px;" onkeyup="FormatCurrency(this);"></td>
											<td style="padding-left:10px;"><input type="text" name="txSalTo" id="txSalTo" class="span6" value="<?php echo $sal_to?>" style="width:150px;" onkeyup="FormatCurrency(this);"></td>
										</tr>
									</table>
								</div>
							</td>
						</tr>
						<tr>
							<th width="20%" scope="row" height="70px"><div align="right"><strong>Employee Share:&nbsp;&nbsp;&nbsp;</strong></div></th>
							<td>
								<div align="left">
									<input type="text" name="txEE" id="txEE" class="span6" value="<?php echo $hdmf_ee_share?>" style="width:150px;" onkeyup="FormatCurrency(this);">
								</div>
							</td>
						</tr>
						<tr>
							<th width="20%" scope="row" height="50px"><div align="right"><strong>Employer Share:&nbsp;&nbsp;&nbsp;</strong></div></th>
							<td>
								<div align="left">
									<input type="text" name="txER" id="txER" class="span6" value="<?php echo $hdmf_er_share?>" style="width:150px;" onkeyup="FormatCurrency(this);">
								</div>
							</td>
						</tr>
						<tr>
							<th width="20%" scope="row" height="50px"><div align="right"><strong>Total Monthly Premium:&nbsp;&nbsp;&nbsp;</strong></div></th>
							<td>
								<div align="left">
									<input type="text" name="txTotal" id="txTotal" class="span6" value="<?php echo $hdmf_total?>" style="width:150px;" onkeyup="FormatCurrency(this);" readonly>
								</div>
							</td>
						</tr>
						<tr>
							<th width="20%" scope="row" height="50px"><div align="right"><strong>Type:&nbsp;&nbsp;&nbsp;</strong></div></th>
							<td>
								<div align="left">
									<select name="selType" id="selType">
										<option value="percent">Percentage</option>
										<option value="actual">Actual Number</option>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<th width="20%" scope="row" height="50px"><div align="center"></div></th>
							<td>
								<div align="left">
									<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small">
								</div>
							</td>
						</tr>
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
<!-- end: JavaScript-->
<script>
$(document).ready(function(){
	$('#txEE').keyup(function(){
		monthlyPremium();
	});
	$('#txER').keyup(function(){
		monthlyPremium();
	});

	function monthlyPremium(){
		var ee_share_temp = $('#txEE').val();
		var	ee_share_number = ee_share_temp.replace(",", "");
		var	ee_share = Number( ee_share_number.replace(/[^0-9\.]+/g,""));

		var er_share_temp = $('#txER').val();
		var	er_share_number = er_share_temp.replace(",", "");
		var	er_share = Number( er_share_number.replace(/[^0-9\.]+/g,""));

		var monthlyPremium = ee_share + er_share;
		$('#txTotal').val(monthlyPremium);
		FormatCurrency(document.getElementById('txTotal'));
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
</body>
</html>