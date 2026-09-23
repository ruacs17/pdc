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
$count=0;
$txItemDesc='';
$txDepName='';
$txDepType="";
$itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;
$_SESSION['notif_id_list']=$itemID;
$dep_base = ($itemID) ? $db->getValue('department','dep_base',array('dep_id'=>$itemID)) : 0;
$txDepDesc="";
if( isset($_POST['btnAdd']) ){
	$dep_name = ( isset($_POST['txDepName']) && !empty($_POST['txDepName']) ) ? trim($_POST['txDepName']) : '';
	$dep_desc = ( isset($_POST['txDepDesc']) && !empty($_POST['txDepDesc']) ) ? trim($_POST['txDepDesc']) : '';
	$dep_head = ( isset($_POST['selDepParent']) && !empty($_POST['selDepParent']) ) ? $_POST['selDepParent'] : '0';
	$dep_type = ( isset($_POST['txDepType']) && !empty($_POST['txDepType']) ) ? trim($_POST['txDepType']) : '';
	if($dep_head){
		$dep_base = $db->getValue('department','dep_base',array('dep_id'=>$dep_head));
		$db->insert('department',array('dep_base'=>$dep_base,'dep_head'=>$dep_head,'dep_name'=>$dep_name,'dep_desc'=>$dep_desc,'dep_type'=>$dep_type));
		$_SESSION['notif_success']="New Department Added!";
	}
	functions::sendTo(functions::pageName().'?itemID='.functions::encode($dep_head));
	die();
}

function disp($dep_head,$level,$currentItemID){
	global $db;
	$string='';
	$q = $db->select('department','*',array('dep_head'=>$dep_head),'ORDER BY dep_name');
	while($r = $db->fetch_array($q)):
		$s = '';
		for($i=1; $i<=$level; $i++):
			$s .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		endfor;
		$dep_desc = ($r['dep_desc']) ? '&nbsp;&nbsp;('.$r['dep_desc'].')': '';
		$s .= $r['dep_name'].$dep_desc;
		$selected = ($currentItemID==$r['dep_id']) ? 'selected="selected"' : "";
		$string ='
		<option value="'.$r['dep_id'].'" '.$selected.'>'.$s.'</option>
		';
		echo $string;
		disp($r['dep_id'],$level + 1,$currentItemID);
	endwhile;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Department ADD</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
<body onLoad="document.getElementById('txDepDesc').focus()">
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span>DEPARTMENT ADD</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<table id="tblist" width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordereds" style="font-size:12px;background-color:#EEE;">
					<tr>
						<th width="15%" scope="col"><div align="left" style="padding:20px 0px 20px 0px">Department Head</div></th>
						<td>
							<div style="padding:20px 0px 20px 0px">
							<select name="selDepParent" id="selDepParent" style="width:100%;">
								<?php
								#if($itemID)
									$q = $db->select('department','*',array('dep_id'=>$itemID),'ORDER BY dep_name');
								// else if($dep_base)
								// $q = $db->select('department','*',array('dep_id'=>$dep_base),'ORDER BY dep_name');
								// else
								// $q = $db->select('department','*',array('dep_id'=>NULL),'ORDER BY dep_name');
								while($r = $db->fetch_array($q)):
									$dep_desc = ($r['dep_desc']) ? '&nbsp;&nbsp;('.$r['dep_desc'].')': '';
								?>
								<option value="<?php echo $r['dep_id']?>" <?php if($itemID==$r['dep_id'])echo 'selected="selected"';?>><?php echo $r['dep_name'].$dep_desc;?></option>
								<?php
								#disp($r['dep_id'],1,$itemID);
								endwhile;
								?>
							</select>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="col"><div align="left" style="padding:20px 0px 20px 0px">Short Name / Abbrevation</div></th>
						<td>
							<div style="padding:20px 0px 20px 0px">
								<input type="text" style="width:99%;" class="span6 typeahead" name="txDepName" id="txDepName" value="<?php echo $txDepName;?>" required>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="col"><div align="left" style="padding:20px 0px 20px 0px">Description / Complete name</div></th>
						<td>
							<div style="padding:20px 0px 20px 0px">
								<input type="text" style="width:99%;" class="span6 typeahead" name="txDepDesc" id="txDepDesc" value="<?php echo $txDepDesc;?>" required>
							</div>
						</td>
					</tr>
					<tr style="display:none;">
						<th scope="col"><div align="left" style="padding:20px 0px 20px 0px">Type</div></th>
						<td>
							<div style="padding:20px 0px 20px 0px">
								<select name="txDepType" id="txDepType">
									<option value="">--select--</option>
									<option value="DIVISION" <?php if($txDepType=='DIVISION'){echo 'selected="selected"';} ?>>DIVISION</option>
									<option value="SECTION" <?php if($txDepType=='SECTION'){echo 'selected="selected"';} ?>>SECTION</option>
								</select>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2"><div align="center"><input type="submit" name="btnAdd" class="btn btn-small btn-info" id="btnAdd" value="Add Department" onClick="return ask()"></div></td>
					</tr>
				</table><p>&nbsp;</p>
				<div style="padding-top:200px">&nbsp;</div>
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
<script src="../js/select2.js"></script>
<script type="text/javascript">$("#selDepParents").select2();</script>
<script type="text/javascript">
function ask(){
	if( confirm('Do you want to add this department?') )
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
<!-- end: JavaScript-->
</body>
</html>