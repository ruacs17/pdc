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
$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;
$txItmEdt='';$di_id='';
if( isset($_POST['btnAdd']) && $equip_id){
	$er_date = ( isset($_POST['txDate']) ) ? $_POST['txDate'] : date('Y-m-d');
	$damage = ( isset($_POST['txDamage']) && !empty($_POST['txDamage']) ) ? trim($_POST['txDamage']) : "";
	$repair_type = ( isset($_POST['selType']) && !empty($_POST['selType']) ) ? trim($_POST['selType']) : "";
	$location = ( isset($_POST['txLocation']) && !empty($_POST['txLocation']) ) ? trim($_POST['txLocation']) : "";
	$remarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : "";
	$quotation_labor = ( isset($_POST['txQLabor']) && !empty($_POST['txQLabor']) ) ? functions::moneyToDouble($_POST['txQLabor']) : '0';
	$quotation_part = ( isset($_POST['txQParts']) && !empty($_POST['txQParts']) ) ? functions::moneyToDouble($_POST['txQParts']) : '0';
	$discount = ( isset($_POST['txDiscount']) && !empty($_POST['txDiscount']) ) ? trim($_POST['txDiscount']) : '0';
	$proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? trim($_POST['selProj']) : NULL;
	$incharge = ( isset($_POST['txIncharge']) && !empty($_POST['txIncharge']) ) ? trim($_POST['txIncharge']) : "";
	$invoice = ( isset($_POST['txInvoice']) && !empty($_POST['txInvoice']) ) ? trim($_POST['txInvoice']) : "";

	$arrDetails = array('proj_id'=>$proj_id,'er_date'=>$er_date,'damage'=>$damage,'invoice'=>$invoice,'repair_type'=>$repair_type,'location'=>$location,'remarks'=>$remarks,'equip_id'=>$equip_id,'incharge'=>$incharge);

	if( $damage && $repair_type && $equip_id ){
		$er_id = $db->insert('equip_repair',$arrDetails);
		$_SESSION['notif_id_list2']=$er_id;
		$db->update('equip_repair',array('ref_id'=>$er_id),array('er_id'=>$er_id));

		if( !empty($_FILES['image']) && $_FILES['image']['error'] == 0 && $er_id ) {

			$uploaddir = '../img_equip/';
			$max_size = 2000 * 1024; // 500 KB
			// Generates random filename and extension 
			function tempnam_sfx($path, $suffix){
				do{
					$file = $path."/".mt_rand().$suffix;
					$fp = @fopen($file, 'x');
				}
				while(!$fp);

				fclose($fp);
				return $file;
			}
			// Process image with GD library
			$verifyimg = getimagesize($_FILES['image']['tmp_name']);

			// Make sure the MIME type is an image
			$pattern = "#^(image/)[^\s\n<]+$#i";

			if( !preg_match($pattern, $verifyimg['mime']) ){
				functions::say("Only image files are allowed!");
			}
			else{
				// Rename both the image and the extension 
				$uploadfile = tempnam_sfx($uploaddir, ".jpg");
				// Upload the file to a secure directory with the new name and extension
				if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
					$qPicIns = $db->insertPrint('doc_img',array('doc_name'=>basename($uploadfile),'doc_org_name'=>basename($_FILES['image']['name']),'mime_type'=>$_FILES['image']['type']));
					$db->query($qPicIns);
					$di_id = $db->insert_id();
					$db->update('equip_repair',array('di_id'=>$di_id),array('er_id'=>$er_id));
				}
			}
		}
		$_SESSION['notif_success']='New Record Added!';
		functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id));
		die();
	}
}

if( isset($_POST['btnSave']) && $equip_id){
	$er_id = ( isset($_POST['txItmEdt']) && !empty($_POST['txItmEdt']) ) ? functions::decode($_POST['txItmEdt']) : 0;
	$_SESSION['notif_id_list2']=$er_id;
	$er_date = ( isset($_POST['txDate']) ) ? $_POST['txDate'] : date('Y-m-d');
	$damage = ( isset($_POST['txDamage']) && !empty($_POST['txDamage']) ) ? trim($_POST['txDamage']) : "";
	$repair_type = ( isset($_POST['selType']) && !empty($_POST['selType']) ) ? trim($_POST['selType']) : "";
	$location = ( isset($_POST['txLocation']) && !empty($_POST['txLocation']) ) ? trim($_POST['txLocation']) : "";
	$remarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : "";
	$quotation_labor = ( isset($_POST['txQLabor']) && !empty($_POST['txQLabor']) ) ? functions::moneyToDouble($_POST['txQLabor']) : '0';
	$quotation_part = ( isset($_POST['txQParts']) && !empty($_POST['txQParts']) ) ? functions::moneyToDouble($_POST['txQParts']) : '0';
	$discount = ( isset($_POST['txDiscount']) && !empty($_POST['txDiscount']) ) ? trim($_POST['txDiscount']) : '0';
	$proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? trim($_POST['selProj']) : NULL;
	$incharge = ( isset($_POST['txIncharge']) && !empty($_POST['txIncharge']) ) ? trim($_POST['txIncharge']) : "";
	$invoice = ( isset($_POST['txInvoice']) && !empty($_POST['txInvoice']) ) ? trim($_POST['txInvoice']) : "";

    if( $damage && $repair_type && $equip_id ){
        $db->update('equip_repair',array('proj_id'=>$proj_id,'er_date'=>$er_date,'damage'=>$damage,'repair_type'=>$repair_type,'location'=>$location,'remarks'=>$remarks,'incharge'=>$incharge),array('er_id'=>$er_id));
 // echo $db->last_query;
 // die();
		if( !empty($_FILES['image']) && $_FILES['image']['error'] == 0 && $er_id ) {

			$uploaddir = '../img_equip/';
			$max_size = 2000 * 1024; // 500 KB
			// Generates random filename and extension 
			function tempnam_sfx($path, $suffix){
				do{
					$file = $path."/".mt_rand().$suffix;
					$fp = @fopen($file, 'x');
				}
				while(!$fp);

				fclose($fp);
				return $file;
			}
			// Process image with GD library
			$verifyimg = getimagesize($_FILES['image']['tmp_name']);

			// Make sure the MIME type is an image
			$pattern = "#^(image/)[^\s\n<]+$#i";

			if( !preg_match($pattern, $verifyimg['mime']) ){
				functions::say("Only image files are allowed!");
			}
			else{
				// Rename both the image and the extension 
				$uploadfile = tempnam_sfx($uploaddir, ".jpg");
				// Upload the file to a secure directory with the new name and extension
				if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
					$di_id = $db->getValue('equip_repair','di_id',array('er_id'=>$er_id));
					$oldFileName = $db->getValue('doc_img','doc_name',array('di_id'=>$di_id));
					if($oldFileName)
						unlink('../img_equip/'.$oldFileName);
					#$db->query('UPDATE equip_repair SET di_id=NULL WHERE er_id="'.$db->clean($er_id).'"');
					$db->update('equip_repair',array('di_id'=>NULL),array('er_id'=>$er_id));
					$db->delete('doc_img',array('di_id'=>$di_id));
                        
					$di_id = $db->insert('doc_img',array('doc_name'=>basename($uploadfile),'doc_org_name'=>basename($_FILES['image']['name']),'mime_type'=>$_FILES['image']['type']));
					$db->update('equip_repair',array('di_id'=>$di_id),array('er_id'=>$er_id));
				}
			}
		}
		$_SESSION['notif_success']='Record Successfully updated!';
		functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id).'&txItmEdt='.functions::encode($er_id));
		die();
	}
}

$damage='';$editTrue=0;$er_date=date('Y-m-d');$invoice='';$remarks='';$repair_type='';$location='';$description='';$qLabor=0;$qParts=0;$discount='';$incharge='';$proj_id='';$diID='';
if( isset($_REQUEST['txItmEdt']) && !empty($_REQUEST['txItmEdt']) ){
	$txItmEdt = functions::decode($_REQUEST['txItmEdt']);
	$_SESSION['notif_id_list2']=$txItmEdt;
	$editTrue = $db->getValue('equip_repair','count(*)',array('er_id'=>$txItmEdt));
	$qvedt = $db->select('equip_repair','*',array('er_id'=>$txItmEdt));
	$rvedt = $db->fetch_array($qvedt);
	$er_date = $rvedt['er_date'];
	$damage = $rvedt['damage'];
	$description = $rvedt['description'];
	$repair_type = $rvedt['repair_type'];
	$location = $rvedt['location'];
	$remarks = $rvedt['remarks'];
	$equip_id = $rvedt['equip_id'];
	$qLabor = $rvedt['quotation_labor'];
	$qParts = $rvedt['quotation_part'];
	$discount = $rvedt['discount'];
	$incharge = $rvedt['incharge'];
	$proj_id = $rvedt['proj_id'];
	$invoice = $rvedt['invoice'];
	$diID = $rvedt['di_id'];
}

$qLocation = $db->query('SELECT DISTINCT location FROM equip_repair ORDER BY location');
$namesLocation='';
while($rLocation=$db->fetch_array($qLocation)):
	$string = preg_replace("/'/",'"',$rLocation['location']);
	$namesLocation .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesLocation .= '"--"';

$qIncharge = $db->query('SELECT DISTINCT incharge FROM equip_repair ORDER BY location');
$namesIncharge='';
while($rIncharge=$db->fetch_array($qIncharge)):
	$string = preg_replace("/'/",'"',$rIncharge['incharge']);
	$namesIncharge .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesIncharge .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Property Detail</title>
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
	<style>
	.tdSpace{padding: 10px 0px 4px 10px;}
	</style>
</head>
<body>
<!-- body content: start here-->
<form method="post" enctype="multipart/form-data">
	<div class="row-fluid">
		<div class="box span12">
			<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white edit"></i><span class="break"></span>PROPERTY REPAIR/MAINTENANCE HISTORY</h2>
			</div>
			<div class="box-content">
				<div align="center">
					<input type="hidden" name="txItmEdt" id="txItmEdt" value="<?php echo functions::encode($txItmEdt);?>">
					<table width="70%" border="0" align="center" style="background-color:#f9f6f6">
						<tr>
							<th bgcolor="#f7ebeb" scope="col" height="60px;"><div align="center">Property</div></th>
							<td class="tdSpace"><div align="left"><strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong></div></td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb"><div align="center">Repair Type</div></th>
							<td width="70%" valign="middle" class="tdSpace">
								<div align="left">
									<select name="selType" id="selType" style="width:300px;">
										<option value="">--select--</option>
										<option value="repair" <?php if($repair_type==="repair")echo 'selected="selected"';?>>REPAIR</option>
										<option value="maintenance" <?php if($repair_type==="maintenance")echo 'selected="selected"';?>>MAINTENANCE</option>
										<option value="repair & maintenance" <?php if($repair_type==="repair & maintenance")echo 'selected="selected"';?>>REPAIR & MAINTENANCE</option>
									</select>
									<span class="help-inline warning" id="msgselType" style="font-weight:bold;" name="msgselType"></span>
								</div>
							</td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Date</div></th>
							<td class="tdSpace">
								<div align="left">
									<a href="javascript:NewCssCal('txDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
									<input name="txDate" type="text" class="span6 mytextbox" style="width:280px;" id="txDate" value="<?php echo $er_date?>" style="width: 90px;" onclick="javascript:NewCssCal('txDate')">
								</div>
							</td>
						</tr>
						<tr>
						<th bgcolor="#f7ebeb" scope="col"><div align="center">Damage / Repair Description</div></th>
							<td class="tdSpace">
								<div align="left">
									<textarea style="width:80%;" rows="5" class="span6 typeahead" name="txDamage" id="txDamage" autocomplete='off'><?php echo $damage?></textarea>
									<span class="help-inline warning" id="msgtxDesc" style="font-weight:bold;" name="msgtxDesc"></span>
								</div>
							</td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Image</div></th>
							<td class="tdSpace">
								<div align="left">
									<table border="0" width="98%" align="center">
										<tr>
											<td align="center">
												<div align="center">
													<?php
													$fileName = $db->getValue('doc_img','doc_name',array('di_id'=>$diID));
													#echo $db->last_query;
													if($fileName){
														$file = '../img_equip/'.$fileName;
														echo '<img height="200" width="200" src="'.$file.'">';
													}
													else
														echo '<img height="200" width="200" src="../img_emp/blank-pic.png">';
													?>
													Select image to upload: <input type="file" name="image">
												</div><br>
											</td>
										</tr>
									</table>
								</div>
							</td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Shop Location</div></th>
							<td class="tdSpace"><div align="left"><textarea style="width:550px;" class="span6 typeahead" name="txLocation" id="txLocation" autocomplete='off' autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesLocation;?>]'><?php echo $location?></textarea></div></td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Performed By</div></th>
							<td class="tdSpace"><div align="left"><input type="text" style="width:550px;" class="span6 typeahead" name="txIncharge" id="txIncharge" autocomplete='off' autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesIncharge;?>]' value="<?php echo $incharge?>"></div></td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Remarks</div></th>
							<td class="tdSpace">
								<div align="left">
									<textarea style="width:350px;" class="span6 typeahead" name="txRemarks" id="txRemarks" autocomplete='off'><?php echo $remarks?></textarea>
									<span class="help-inline warning" id="msgtxRemarks" style="font-weight:bold;" name="msgtxRemarks"></span>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div align="center">
								<?php 
								if($editTrue)
								echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary btn-small">';
								else
								echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary btn-small">';
								?>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div><!--/span-->
	</div><!--/row-->
</form>
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
function delt(){
	if(confirm('Do you want to remove this?'))
		return true;
	else
		return false; 
}

$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxDesc').html("");
		$('#msgselType').html("");

		if( $('#selType').val()=="" ){
			$('#msgselType').html("Repair Type Required!");
			$('#selType').focus();
			res=false;
		}
		else if( $('#txDesc').val()=="" ){
			$('#msgtxDesc').html("Specify Description!");
			$('#txDesc').focus();
			res=false;
		}
		else{
			if(confirm('Do you want to submit this information?'))
				res=true;
		}
		return res;
	});
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
<!-- end: JavaScript-->
</body>
</html>