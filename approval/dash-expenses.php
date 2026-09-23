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
$arr = array();
$yr=date('Y');
$yrSearch = " AND left(vd_date,4)='".$yr."'";
$yrSearchPO = " AND left(po_date,4)='".$yr."'";
if(isset($_POST['btnSearch'])){
$yr = (isset($_POST['yr']) && !empty($_POST['yr']) ) ? functions::decode($_POST['yr']) : 0;
	if($yr){
		$yrSearch = " AND left(vd_date,4)='".$db->clean($yr)."'";  
		$yrSearchPO = " AND left(po_date,4)='".$db->clean($yr)."'";
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Charges Report</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Charges Report</h2>
		</div>
		<div class="box-content" align="center">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="charge_category_report_voucher_po.php">PO / Voucher</a></li>
				<li><a href="charge_category_report_voucher.php">Voucher</a></li>
				<li><a href="charge_category_report.php">PO</a></li>
				<li class="active"><a href="dash-expenses.php" style="opacity:.9">Charges Category Report</a></li>
			</ul>
			<form method="post">
				<table width="80%" border="0">
					<tr>
						<td width="40%" align="right"><strong>Select Year:</strong>&nbsp;</td>
						<td width="17%" align="left" valign="middle" style="padding: 12px 0px 4px 0px">
							<select name="yr" id="yr" style="width:150px;">
								<option value="">-- All Year --</option>
								<?php
								$qYr = $db->query('SELECT DISTINCT left(vd_date,4) as dt FROM voucher_detail ORDER BY vd_date DESC ');
								while($rYr = $db->fetch_array($qYr)):
								?>
								<option value="<?php echo functions::encode($rYr['dt'])?>" <?php if($yr==$rYr['dt'])echo 'selected="selected"';?>><?php echo $rYr['dt']?></option>
								<?php endwhile;?>
							</select>
						</td>
						<td width="52%" valign="middle"><input type="submit" name="btnSearch" id="btnSearch" value="Select" class="btn btn-small btn-primary"></td>
					</tr>
				</table><br><br><br>
			</form>
			<div style="width:50%">
			<table width="50%" align="center" border="1" class="table table-bordered table-hover table-striped">
				<thead>
					<tr style="background-color:#CCC;">
						<td width="50%" height="30"><strong>Charges Category</strong></td>
						<td width="10%"><div align="center"><strong>Spent</strong></div></td>
					</tr>
				</thead>
				<tbody>
				<?php
				$totalCost=0;
				$amount=0;
				$qSup = $db->query('SELECT category_id,name,id.expense_type, sum(amount) as amnt,item_id FROM `voucher_detail` vd, item_deduction id WHERE vd.category_id=id.item_id '.$yrSearch.' group by category_id ORDER BY id.expense_type,name ');
				while($rSup = $db->fetch_array($qSup)):
					$amount=0;
					$amount += $rSup['amnt'];

					$qPO = $db->select('po','*',array('received'=>1,'category_id'=>$rSup['category_id']),$yrSearchPO.'ORDER BY po_date DESC');
					while($rPO = $db->fetch_array($qPO)):
						$amount += $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id']));
					endwhile;
					$totalCost += $amount;
				?>
					<tr>
						<td height="25"><?php echo $rSup['name'];?> (<i><?php echo $rSup['expense_type']?></i>)</td>
						<td><div align="right"><a id="view<?php echo $rSup['item_id']?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onclick="showThis(this.id,'dash-expenses-detail.php?cid=<?php echo functions::encode($rSup['item_id']);?>&yr=<?php echo functions::encode($yr);?>','Charges Detail','1')"><?php echo functions::formatMoney($amount);?></a></div></td>
					</tr>
				<?php endwhile;?>
					<tr>
						<td height="25"><div align="right"><strong>Total Charges</strong></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($totalCost);?></strong></div></td>
					</tr>
				</tbody>
			</table>
			</div>
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
<script>function selDt(PiEwgD){window.location="dash-proj-profit.php?pdt="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>