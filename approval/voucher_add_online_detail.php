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
$category=""; $item=""; $proj_id="";  $amount=0; $txDate="";$amountIssue=0;
$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$vp_id = (isset($_REQUEST['vpid']) && !empty($_REQUEST['vpid']) ) ? functions::decode($_REQUEST['vpid']) : 0;
$project = (isset($_REQUEST['prj']) && !empty($_REQUEST['prj']) ) ? functions::decode($_REQUEST['prj']) : 0;
$category = (isset($_REQUEST['itmCat']) && !empty($_REQUEST['itmCat']) ) ? functions::decode($_REQUEST['itmCat']) : 0;
$particular_name=$db->getValue('voucher_particular','vp_title',array('voucher_id'=>$vid,'vp_id'=>$vp_id));

if( isset($_POST['btnAdd']) ){
	$arr = array();
	$category = ( isset($_POST['txcat_id']) && !empty($_POST['txcat_id']) ) ? functions::decode($_POST['txcat_id']) : '';
	$item = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : '';
	$proj_id = ( isset($_POST['txprj_id']) && !empty($_POST['txprj_id']) ) ? functions::decode($_POST['txprj_id']) : '';

	$amount = ( isset($_POST['txAmount']) && !empty($_POST['txAmount']) ) ? functions::moneyToDouble($_POST['txAmount']) : '0';
	$date = $db->getValue('voucher','vdate',array('voucher_id'=>$vid));

	$arr = ($proj_id) ? array('proj_id'=>$proj_id) : array();

	if( $category  && $amount && $vid && $vp_id && $item ){
		$db->insert('voucher_detail',array_merge($arr,array('vd_date'=>$date,'category_id'=>$category,'item'=>$item,'amount'=>$amount,'amount_issue'=>$amount,'vp_id'=>$vp_id)));
		$_SESSION['notif_success']='Item Added!';
	}
	functions::sendTo(functions::pageName().'?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id).'&prj='.functions::encode($proj_id).'&itmCat='.functions::encode($category));
	die();
}

if( isset($_POST['btnSave']) ){
	$txvd_id = ( isset($_POST['txvd_id']) && !empty($_POST['txvd_id']) ) ? functions::decode($_POST['txvd_id']) : '0';
	$category = ( isset($_POST['txcat_id']) && !empty($_POST['txcat_id']) ) ? functions::decode($_POST['txcat_id']) : '';
	$item = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : '';
	$proj_id = ( isset($_POST['txprj_id']) && !empty($_POST['txprj_id']) ) ? functions::decode($_POST['txprj_id']) : '';
	$arr = array('proj_id'=>$proj_id);
	$amount = ( isset($_POST['txAmount']) && !empty($_POST['txAmount']) ) ? functions::moneyToDouble($_POST['txAmount']) : '0';

	if( $category && $amount && $txvd_id && $item){
		$db->update('voucher_detail',array('item'=>$item,'amount'=>$amount,'amount_issue'=>$amount),array('vd_id'=>$txvd_id));
		$_SESSION['notif_success']='Changes Saved!';
	}
	functions::sendTo(functions::pageName().'?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id).'&prj='.functions::encode($proj_id).'&itmCat='.functions::encode($category));
	die();
}

if( isset($_REQUEST['vdidDel']) && !empty($_REQUEST['vdidDel']) ){
	$vdidDel = functions::decode($_REQUEST['vdidDel']);
	$db->delete('voucher_detail',array('vd_id'=>$vdidDel));
	$_SESSION['notif_warning']='Item Removed!';
	functions::sendTo(functions::pageName().'?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id).'&prj='.functions::encode($project).'&itmCat='.functions::encode($category));
	die();
}

$editTrue=0;
$vdidEdt=0;
$td = $db->getValue('voucher','vdate',array('voucher_id'=>$vid));
$txDateEdt = explode("-",$td);
$txDate = (count($txDateEdt)==3) ? $txDateEdt[1].'/'.$txDateEdt[2].'/'.$txDateEdt[0] : date('m/d/Y');
if( isset($_REQUEST['vdidEdt']) && !empty($_REQUEST['vdidEdt']) ){
	$vdidEdt = functions::decode($_REQUEST['vdidEdt']);
	$editTrue = $db->getValue('voucher_detail','count(*)',array('vd_id'=>$vdidEdt));
	$qvedt = $db->select('voucher_detail','*',array('vd_id'=>$vdidEdt));
	$rvedt = $db->fetch_array($qvedt);
	$item = $rvedt['item'];
	$amount = $rvedt['amount'];
} 
$qItem = $db->select('voucher_detail','DISTINCT item',array());
$namesItem='';
while($rItem=$db->fetch_array($qItem)):
	$string = preg_replace("/'/",'"',$rItem['item']);
	$namesItem .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesItem .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Voucher Online Payment</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Online Payment Details</h2>
		</div>
		<div class="box-content" style="visibility:hidden;">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="<?php echo 'voucher_part_add_admin.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id)?>">Admin Charges</a></li>
				<li class="active"><a href="<?php echo 'voucher_part_add.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id)?>" style="opacity:.9">Project Charges</a></li>
			</ul>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<input type="hidden" name="txvd_id" id="txvd_id" value="<?php echo functions::encode($vdidEdt);?>">
				<input type="hidden" name="txprj_id" id="txprj_id" value="<?php echo functions::encode($project);?>">
				<input type="hidden" name="txcat_id" id="txcat_id" value="<?php echo functions::encode($category);?>">
				<table width="65%" align="center">
					<tr>
						<td>
							<div>Voucher No: <strong><?php echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$vid));?></strong></div>
							<div>Transaction Date: <strong><?php echo functions::datearr($db->getValue('voucher','vdate',array('voucher_id'=>$vid)));?></strong></div>
						</td>
					</tr>
					<tr>
						<td>
							<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered">
								<thead>
									<tr style="background-color:#CCC;">
										<th width="19%" scope="col"><div align="center">Item Detail</div></th>
										<th width="5%" scope="col"><div align="center">Amount Spent</div></th>
										<th width="8%" scope="col">&nbsp;</th>
									</tr>
								</thead>
								<tbody>
									<tr bgcolor="#f7ebeb">
										<td>
											<div align="center">
												<textarea name="txItem" id="txItem" autocomplete='off' style="width: 350px;" data-provide="typeahead" data-items="10" data-source='[<?php echo $namesItem;?>]'><?php echo $particular_name;?></textarea>
												<span class="help-inline warning" id="msgtxItem" style="font-weight:bold;" name="msgtxItem"></span>
											</div>
										</td>
										<td>
											<div align="center">
												<input name="txAmount" type="text" id="txAmount" style="width: 90px;text-align:center;" value="<?php echo ($amount) ? functions::formatMoney($amount) : '';?>" size="30" onkeyup="FormatCurrency(this);" />
												<span class="help-inline warning" id="msgtxAmount" style="font-weight:bold;" name="msgtxAmount"></span>
											</div>
										</td>
										<td>
											<div align="center">
											<?php
											if($editTrue)
											echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-mini btn-primary">';
											else
											echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-mini btn-primary">';
											?>
											</div>
										</td>
									</tr>
									<tr><td colspan="2" height="25"></td></tr>
									<?php
									$vdate='';
									$total_amount=0;
									$qvDetails = $db->select('voucher_detail','*',array('vp_id'=>$vp_id),'ORDER BY vd_date');
									while($rvDetails = $db->fetch_array($qvDetails)):
										$total_amount += $rvDetails['amount'];
									?>
									<tr>
										<td><div align="center"><?php echo $rvDetails['item']?></div></td>
										<td><div align="right"><?php echo functions::formatMoney($rvDetails['amount']);?></div></td>
										<td>
											<div align="center">
												<a id="edit<?php echo $rvDetails['vd_id']?>" class="btn btn-mini btn-warning" title="Update this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>&prj=<?php echo functions::encode($project)?>&itmCat=<?php echo functions::encode($category)?>&vdidEdt=<?php echo functions::encode($rvDetails['vd_id']);?>"><i class="halflings-icon white pencil"></i></a>
												<a id="del<?php echo $rvDetails['vd_id']?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>&prj=<?php echo functions::encode($project)?>&itmCat=<?php echo functions::encode($category)?>&vdidDel=<?php echo functions::encode($rvDetails['vd_id']);?>"><i class="halflings-icon white trash"></i></a>
											</div>
										</td>
									</tr>
									<?php endwhile;?>
									<tr>
										<td><div align="right"><strong>Total Amount</strong></div></td>
										<td><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
										<td></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</table><p>&nbsp;</p>
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
<script>
function delt(){
	if(confirm('Do you want to remove this item?'))
		return true;
	else
		return false; 
}
$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxItem').html("");
		$('#msgtxAmount').html("");

		if( $('#txItem').val()=="" ){
			$('#msgtxItem').html("Item Required!");
			$('#txItem').focus();
			res=false;
		}
		else if( $('#txAmount').val()==0 ){
			$('#msgtxAmount').html("Amount Required!");
			$('#msgtxAmount').focus();
			res=false;
		}
		else
			res=true;

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