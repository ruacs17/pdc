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
$fromED = (isset($_REQUEST['fromED']) && !empty($_REQUEST['fromED']) ) ? $_REQUEST['fromED'] : '';
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$childDelete = (isset($_REQUEST['cidDel']) && !empty($_REQUEST['cidDel']) ) ? functions::decode($_REQUEST['cidDel']) : 0;

$txtspfName='';$txtspmName='';$txtsplName='';$txtspExtName='';$txtExtName='';$txtspOccupation='';$txspbusname='';
$txtspbusadd='';$txspTelNo='';$txtfrfName='';$txtfrmName='';$txtfrlName='';$txtfrExtName='';$txtmrfName='';$txtmrmName='';$txtmrlName='';
if($eid){
	$q = $db->select('employee','*',array('emp_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$txtspfName = $r['sp_fname'];
		$txtspmName = $r['sp_mname'];
		$txtsplName = $r['sp_lname'];
		$txtspExtName = $r['sp_extname'];
		$txtExtName = $r['extname'];
		$txtspOccupation = $r['sp_occupation'];
		$txspbusname = $r['sp_bus_name'];
		$txtspbusadd = $r['sp_bus_address'];
		$txspTelNo = $r['sp_tel_no'];
		$txtfrfName = $r['fr_fname'];
		$txtfrmName = $r['fr_mname'];
		$txtfrlName = $r['fr_lname'];
		$txtfrExtName = $r['fr_extname'];
		$txtmrfName = $r['mr_fname'];
		$txtmrmName = $r['mr_mname'];
		$txtmrlName = $r['mr_lname'];
	endwhile;
}
if($childDelete){
	$db->delete('emp_child',array('ec_id'=>$childDelete,'emp_id'=>$eid));
	$_SESSION['notif_warning']='Child Information Removed!';
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
if( isset($_POST['btnSave']) ){
	$sp_fname = ( isset($_POST['txtspfName']) ) ? strtoupper(trim($_POST['txtspfName'])) : '';
	$sp_mname = ( isset($_POST['txtspmName']) ) ? strtoupper(trim($_POST['txtspmName'])) : '';
	$sp_lname = ( isset($_POST['txtsplName']) ) ? strtoupper(trim($_POST['txtsplName'])) : '';
	$sp_extname = ( isset($_POST['txtspExtName']) ) ? strtoupper(trim($_POST['txtspExtName'])) : '';
	$sp_occupation = ( isset($_POST['txtspOccupation']) ) ? strtoupper(trim($_POST['txtspOccupation'])) : '';
	$sp_bus_name = ( isset($_POST['txspbusname']) ) ? strtoupper(trim($_POST['txspbusname'])) : '';
	$sp_bus_address = ( isset($_POST['txtspbusadd']) ) ? strtoupper(trim($_POST['txtspbusadd'])) : '';
	$sp_tel_no = ( isset($_POST['txspTelNo']) ) ? strtoupper(trim($_POST['txspTelNo'])) : '';

	$fr_fname = ( isset($_POST['txtfrfName']) ) ? strtoupper(trim($_POST['txtfrfName'])) : '';
	$fr_mname = ( isset($_POST['txtfrmName']) ) ? strtoupper(trim($_POST['txtfrmName'])) : '';
	$fr_lname = ( isset($_POST['txtfrlName']) ) ? strtoupper(trim($_POST['txtfrlName'])) : '';
	$fr_extname = ( isset($_POST['txtfrExtName']) ) ? strtoupper(trim($_POST['txtfrExtName'])) : '';

	$mr_fname = ( isset($_POST['txtmrfName']) ) ? strtoupper(trim($_POST['txtmrfName'])) : '';
	$mr_mname = ( isset($_POST['txtmrmName']) ) ? strtoupper(trim($_POST['txtmrmName'])) : '';
	$mr_lname = ( isset($_POST['txtmrlName']) ) ? strtoupper(trim($_POST['txtmrlName'])) : '';

	$arrUpdate = array('sp_fname'=>$sp_fname,'sp_mname'=>$sp_mname,'sp_lname'=>$sp_lname,'sp_extname'=>$sp_extname,
					'sp_occupation'=>$sp_occupation,'sp_bus_name'=>$sp_bus_name,'sp_bus_name'=>$sp_bus_name,'sp_bus_address'=>$sp_bus_address,'sp_tel_no'=>$sp_tel_no,
					'fr_fname'=>$fr_fname,'fr_mname'=>$fr_mname,'fr_lname'=>$fr_lname,'fr_extname'=>$fr_extname,
					'mr_fname'=>$mr_fname,'mr_mname'=>$mr_mname,'mr_lname'=>$mr_lname
					);
	$db->update('employee',$arrUpdate,array('emp_id'=>$eid));
	$_SESSION['notif_success']='Changes Saved!';
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Add</title>
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
	<style type="text/css">body{font-size: 12px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<?php if(empty($fromED)){ ?>
		<div class="box-content">
			<div align="right" class="nav tab-menu nav-tabs" style="padding-top: 3px;"><?php require_once('employee_options.php');?></div>
		</div>
		<?php } ?>
		<div class="box-content">
			<form class="form-horizontal" method="post" onSubmit="return ask()">
				<div align="center" style="padding-bottom: 15px;"><h2>FAMILY BACKGROUND</h2></div>
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="17%" height="30"></td>
						<td width="43%"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Spouse</div></td>
						<td>
							<table width="70%" border="0">
								<tr>
									<td>First Name</td>
									<td><input type="text" name="txtspfName" id="sp_fname" class="span6" value="<?php echo $txtspfName?>" style="width:314px;" onblur="getEval(this.id,this.value);" /></td>
								</tr>
								<tr>
									<td>Middle Name</td>
									<td><input type="text" name="txtspmName" id="sp_mname" class="span6" value="<?php echo $txtspmName?>" style="width:314px;" onblur="getEval(this.id,this.value);" /></td>
								</tr>
								<tr>
									<td width="20%">Last Name</td>
									<td><input type="text" name="txtsplName" id="sp_lname" class="span6" value="<?php echo $txtsplName?>" style="width:314px;" onblur="getEval(this.id,this.value);" /></td>
								</tr>
								<tr>
									<td>Suffix (JR, SR, III)</td>
									<td><input type="text" name="txtspExtName" id="sp_extname" class="span6" value="<?php echo $txtspExtName?>" style="width:314px;" onblur="getEval(this.id,this.value);" /></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Occupation</div></td>
						<td><input type="text" name="txtspOccupation" id="sp_occupation" class="span6" value="<?php echo $txtspOccupation?>" onblur="getEval(this.id,this.value);" /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Employer/Bus. Name</div></td>
						<td><input type="text" name="txspbusname" id="sp_bus_name" class="span6" value="<?php echo $txspbusname?>" onblur="getEval(this.id,this.value);" /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Business Address</div></td>
						<td><input type="text" name="txtspbusadd" id="sp_bus_address" class="span6" value="<?php echo $txtspbusadd?>" onblur="getEval(this.id,this.value);" /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Telephone No.</div></td>
						<td><input type="text" name="txspTelNo" id="sp_tel_no" class="span6" value="<?php echo $txspTelNo?>" onblur="getEval(this.id,this.value);" /></td>
					</tr>
					<tr>
						<td height="30" colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Father</div></td>
						<td>
							<table width="70%" border="0">
								<tr>
									<td>First Name</td>
									<td><input type="text" name="txtfrfName" id="fr_fname" class="span6" value="<?php echo $txtfrfName?>" style="width:314px;" onblur="getEval(this.id,this.value);" /></td>
								</tr>
								<tr>
									<td>Middle Name</td>
									<td><input type="text" name="txtfrmName" id="fr_mname" class="span6" value="<?php echo $txtfrmName?>" style="width:314px;" onblur="getEval(this.id,this.value);" /></td>
								</tr>
								<tr>
									<td width="20%">Last Name</td>
									<td><input type="text" name="txtfrlName" id="fr_lname" class="span6" value="<?php echo $txtfrlName?>" style="width:314px;" onblur="getEval(this.id,this.value);" /></td>
								</tr>
								<tr>
									<td>Suffix (JR, SR, III)</td>
									<td><input type="text" name="txtfrExtName" id="fr_extname" class="span6" value="<?php echo $txtfrExtName?>" style="width:314px;" onblur="getEval(this.id,this.value);" /></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td height="30" colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Mother's Maiden Name</div></td>
						<td>
							<table width="70%" border="0">
								<tr>
									<td>First Name</td>
									<td><input type="text" name="txtmrfName" id="mr_fname" class="span6" value="<?php echo $txtmrfName?>" style="width:314px;" onblur="getEval(this.id,this.value);" /></td>
								</tr>
								<tr>
									<td>Middle Name</td>
									<td><input type="text" name="txtmrmName" id="mr_mname" class="span6" value="<?php echo $txtmrmName?>" style="width:314px;" onblur="getEval(this.id,this.value);" /></td>
								</tr>
								<tr>
									<td width="20%">Last Name</td>
									<td><input type="text" name="txtmrlName" id="mr_lname" class="span6" value="<?php echo $txtmrlName?>" style="width:314px;" onblur="getEval(this.id,this.value);" /></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td height="30" colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Children</div></td>
						<td>
							<table width="85%" border="0">
								<tr>
									<td colspan="3"><div align="right"><a id="adc" href="#" class="btn btn-info btn-small thickbox" onclick="showThis(this.id,'employee_add_family_child.php?eid=<?php echo functions::encode($eid)?>','Child Detail')">Add Child</a></div></td>
								</tr>
								<tr>
									<td width="60%">Name</td>
									<td width="20%">Birth Date</td>
									<td width="10">&nbsp;</td>
								</tr>
								<?php
								$countChild=0;
								$qChild = $db->select('emp_child','*',array('emp_id'=>$eid));
								while($rChild = $db->fetch_array($qChild)):
									$countChild++;
								?>
								<tr>
									<td><?php echo $countChild.'. '.$rChild['ec_lname'].', '.$rChild['ec_fname'].' '.$rChild['ec_mname']?></td>
									<td><?php echo functions::datearr($rChild['ec_bdate']);?></td>
									<td>
										<div align="center">
											<a id="adc<?php echo $rChild['ec_id']?>" href="#" class="thickbox btn btn-warning btn-mini" onclick="showThis(this.id,'employee_add_family_child.php?eid=<?php echo functions::encode($eid)?>&cidEdt=<?php echo functions::encode($rChild['ec_id'])?>','Update Child Information')" title="Modify"><i class="halflings-icon white pencil"></i></a>
											<a href="?eid=<?php echo functions::encode($eid)?>&cidDel=<?php echo functions::encode($rChild['ec_id'])?>&fromED=<?php echo $fromED;?>" onClick="if(confirm('Do you want to remove this Child information?')){return true;}else{return false}" title="Remove" class="btn btn-danger btn-mini"><i class="halflings-icon white trash"></i></a>
										</div>
									</td>
								</tr>
								<?php endwhile;?>
							</table>
						</td>
					</tr>
				</table>
				<div align="center">
					<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small">
				</div>
			</form>
		</div>
	</div><!--/span-->
</div><!--/row-->
<div id="show"></div>
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
<script type="text/javascript">
var xmlhttp
var msg
function getEval(fld,val){
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null){
		alert ("Your browser does not support AJAX!");
		return;
	}

	var url="emp_fam.php";

	url=url+"?f="+fld+"&v="+val+'&di='+<?php echo $eid?>;
	xmlhttp.onreadystatechange=stateChanged;
	xmlhttp.open("GET",url,true);
	xmlhttp.send(null);
}

function stateChanged(){
	if (xmlhttp.readyState==4){
		document.getElementById("show").innerHTML=xmlhttp.responseText;
	}
}

function GetXmlHttpObject(){
	if (window.XMLHttpRequest){
		// code for IE7+, Firefox, Chrome, Opera, Safari
		return new XMLHttpRequest();
	}
	if (window.ActiveXObject){
		// code for IE6, IE5
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
return null;
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
<script type="text/javascript">
function ask(){
	if(confirm('Do you want to save this information?'))
		return true;
	else
		return false;
}
</script>
<!-- end: JavaScript-->
</body>
</html>