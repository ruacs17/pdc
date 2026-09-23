<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/read_excel_xlsx.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');

$hol_type='';$hol_name='';$wage_percent='';$hol_fix='';
$hol_month='';$hol_day='';$hol_year='';
$hol_id = (isset($_REQUEST['holid']) && !empty($_REQUEST['holid']) ) ? functions::decode($_REQUEST['holid']) : 0;
if($hol_id){
	$q = $db->select('holiday','*',array('hol_id'=>$hol_id));
	$r = $db->fetch_array($q);
	$hol_name = $r['hol_name'];
	$hol_type = $r['hol_type'];
	$wage_percent = $r['wage_percent'];
	$hol_month = $r['hol_month'];
	$hol_day = $r['hol_day'];
	$hol_year = $r['hol_year'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Holiday Manage</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/inputInt.js"></script>
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
	$hol_type = ( isset($_POST['selType']) && !empty($_POST['selType']) ) ? $_POST['selType'] : 0;
	$hol_name = ( isset($_POST['txHolName']) && !empty($_POST['txHolName']) ) ? strtoupper(trim($_POST['txHolName'])) : 0;
	$wage_percent = ( isset($_POST['txWagePercent']) && !empty($_POST['txWagePercent']) ) ? $_POST['txWagePercent'] : 0;

	$hol_month = ( isset($_POST['selMon']) ) ? trim($_POST['selMon']) : '';
	$hol_day = ( isset($_POST['selDay']) ) ? trim($_POST['selDay']) : '';
	$hol_year = ( isset($_POST['selYr']) ) ? trim($_POST['selYr']) : '';

	if($hol_name && $hol_type && $hol_month && $hol_day && $hol_year){
		if($hol_id){
			$db->update('holiday',array('hol_name'=>$hol_name,'hol_month'=>$hol_month,'hol_day'=>$hol_day,'hol_year'=>$hol_year,'hol_type'=>$hol_type,'wage_percent'=>$wage_percent),array('hol_id'=>$hol_id));
			$_SESSION['notif_success']='Changes Saved!';
			functions::sendTo(functions::pageName().'?holid='.functions::encode($hol_id));
			die();
		}
		else{
			if( $db->getValue('holiday','count(*)',array('hol_name'=>$hol_name,'hol_month'=>$hol_month,'hol_day'=>$hol_day,'hol_year'=>$hol_year,'hol_type'=>$hol_type,'wage_percent'=>$wage_percent)) ){
				functions::say('Holiday Already Exist!');
			}
			else{
				$db->insert('holiday',array('hol_name'=>$hol_name,'hol_month'=>$hol_month,'hol_day'=>$hol_day,'hol_year'=>$hol_year,'hol_type'=>$hol_type,'wage_percent'=>$wage_percent));
				$_SESSION['notif_success']='New Holiday Added!';
				functions::sendTo(functions::pageName());
				die();
			}
		}
	}
	else{
		functions::say('Please fill up the form properly!');
	}
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post" onSubmit="return ask()">
				<div align="center" style="padding-bottom: 10px;"><h2>HOLIDAY DETAILS</h2></div>
				<div align="center">
					<table border="0" width="50%">
						<tr>
							<td height="50px" width="20%">Holiday Type:</td>
							<td>
								<div align="left">
									<select name="selType" id="selType" required>
										<option value="">--select--</option>
										<option value="Regular Holiday" <?php if($hol_type=='Regular Holiday')echo 'selected="selected"';?>>Regular Holiday</option>
										<option value="Special Non-Working Holiday" <?php if($hol_type=='Special Non-Working Holiday')echo 'selected="selected"';?>>Special Non-Working Holiday</option>
										<option value="Company Holiday" <?php if($hol_type=='Company Holiday')echo 'selected="selected"';?>>Company Holiday</option>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<td height="50px">Holiday Name:</td>
							<td><div align="left"><input type="text" name="txHolName" id="txHolName" value="<?php echo $hol_name?>" style="width:380px;" required></div></td>
						</tr>
						<tr>
							<td height="50px">Holiday Date:</td>
							<td>
								<div align="left">
									<select name="selMon" id="selMon" data-rel="chosen" style="width:80px;" required>
										<option value="">Month</option>
										<option value="01" <?php if($hol_month=='01')echo 'selected="selected"';?>>Jan</option>
										<option value="02" <?php if($hol_month=='02')echo 'selected="selected"';?>>Feb</option>
										<option value="03" <?php if($hol_month=='03')echo 'selected="selected"';?>>Mar</option>
										<option value="04" <?php if($hol_month=='04')echo 'selected="selected"';?>>Apr</option>
										<option value="05" <?php if($hol_month=='05')echo 'selected="selected"';?>>May</option>
										<option value="06" <?php if($hol_month=='06')echo 'selected="selected"';?>>Jun</option>
										<option value="07" <?php if($hol_month=='07')echo 'selected="selected"';?>>Jul</option>
										<option value="08" <?php if($hol_month=='08')echo 'selected="selected"';?>>Aug</option>
										<option value="09" <?php if($hol_month=='09')echo 'selected="selected"';?>>Sep</option>
										<option value="10" <?php if($hol_month=='10')echo 'selected="selected"';?>>Oct</option>
										<option value="11" <?php if($hol_month=='11')echo 'selected="selected"';?>>Nov</option>
										<option value="12" <?php if($hol_month=='12')echo 'selected="selected"';?>>Dec</option>
									</select>
									<select name="selDay" id="selDay" data-rel="chosen" style="width:70px;" required>
										<option value="">Day</option>
										<?php for($i=1;$i<=31;$i++):?>
										<option value="<?php echo ($i<10) ? '0'.$i : $i;?>" <?php if($hol_day==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
										<?php endfor;?>
									</select>
									<select name="selYr" id="selYr" data-rel="chosen" style="width:95px;">
										<option value="all" <?php if($hol_year=="all" || empty($hol_year))echo 'selected="selected"';?>>Yearly</option>
										<?php for($y=(date('Y')+1);$y>=(date('Y')-2);$y--):?>
										<option value="<?php echo $y;?>" <?php if($hol_year==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
										<?php endfor;?>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<td height="50px">Additional Pay:</td>
							<td><div align="left"><input type="text" name="txWagePercent" id="txWagePercent" value="<?php echo $wage_percent?>" onkeypress="return checkinput(this, event);" style="width:45px;"> %</div></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								<div align="left" style="padding-top: 40px;">
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
<script type="text/javascript">
function ask(){
	if(confirm('Do you want to save this information?'))
		return true;
	else
		return false;
}
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
<!-- end: JavaScript-->
</body>
</html>