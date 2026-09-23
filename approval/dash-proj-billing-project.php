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
$project_id = ( isset($_REQUEST['prjID']) && !empty($_REQUEST['prjID']) ) ? functions::decode($_REQUEST['prjID']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Project Billing Report</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
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
	<style>.padleft{padding-right: 5px; padding-left: 5px;}</style>
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header">
			<h2><i class="halflings-icon white th"></i><span class="break"></span>Project Billing Report</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="dash-proj-billing-income.php">Billing Income</a></li>
				<li><a href="dash-proj-billing-yearly.php">Yearly Report</a></li>
				<li><a href="dash-proj-billing.php" style="opacity:.9">All Projects</a></li>
				<li class="active"><a href="dash-proj-billing-project.php">Selected Project</a></li>
			</ul>
		</div>
		<table>
			<tr>
				<td>Select Project: </td>
				<td>
					<select name="txTran_proj" id="txTran_proj" data-rel="chosen" style="width:850px;font-size:12px;height:50px;" onChange="projSel(this.value)">
						<option value="">-- Select Project --</option>
						<?php $qProj = $db->select('project','*',array('project'=>'1'),'ORDER BY proj_name');
						while($rProj = $db->fetch_array($qProj)):
						?>
						<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($project_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
						<?php endwhile;?>
					</select>
				</td>
			</tr>
		</table><br><br><br><br>
		<table class="table-hover" width="100%" border="1" style="font-size:12px;">
			<thead>
				<tr style="background-color:#CCC;">
					<td class="padleft" width="20%"><strong>Project</strong></td>
					<td class="padleft" width="7%"><strong>Cost</strong></td>
					<td class="padleft" width="7%"><div align="left"><strong>Proposed Bill</strong></div></td>
					<td class="padleft" width="7%"><div align="right"><strong>% Billed</strong></div></td>
					<td class="padleft" width="7%"><div align="right"><strong>VAT</strong></div></td>
					<td class="padleft" width="7%"><div align="right"><strong>EWT</strong></div></td>
					<td class="padleft" width="7%"><div align="right"><strong>Retention</strong></div></td>
					<td class="padleft" width="7%"><div align="right"><strong>Contractor's Tax</strong></div></td>
					<td class="padleft" width="7%"><div align="right"><strong>Recoupment</strong></div></td>
					<td class="padleft" width="7%"><div align="right"><strong>Net Bill</strong></div></td>
					<td class="padleft" width="7%"><div align="center"><strong>Submitted</strong></div></td>
					<td class="padleft" width="7%"><div align="center"><strong>Collected</strong></div></td>
					<td class="padleft" width="7%"><div align="right"><strong>Days<br>Elapsed</strong></div></td>
				</tr>
			</thead>
			<tbody>
				<?php
				$qProj = $db->select('project','*',array('proj_id'=>$project_id));
				while( $rProj = $db->fetch_array($qProj)):
					$proj_cost = $rProj['proj_cost'];
				?>
				<tr>
					<td class="padleft">
						<strong><?php echo $rProj['proj_name']?></strong>
						<a id="AddBill<?php echo $rProj['proj_id']?>'" class="thickbox" style="cursor:pointer" title="Manage Billing" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-billing-add.php?pID=<?php echo functions::encode($rProj['proj_id'])?>','Add Billing')"><i class="halflings-icon plus-sign"></i></a>
					</td>
					<td class="padleft"><a id="edit<?php echo $rProj['proj_id']?>" style="cursor:pointer" class="thickbox" title="Modify this Project" data-rel="tooltip" onclick="showThis(this.id,'project_edit.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Detail')"><strong><?php echo functions::formatMoney($proj_cost)?></strong></a></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<?php
					$qPI = $db->select('project_income','*',array('proj_id'=>$rProj['proj_id']),' AND lower(name) != "advance payment" AND  lower(name) != "retention payment" ORDER BY pi_date ASC');
					$countBilledPercent=0;
					$totalAmountBilled=0;
					$totalNetBilled=0;
					$projRecoupment=0;$projVAT=0;$projEWT=0;$projRetention=0;$projConTax=0;
					while($rPI = $db->fetch_array($qPI)):
						$openPar='(<i>';$closePar='</i>)';
						$daysElapsed = 0;
						$amountBilled = $rPI['amount'];
						$netBilled = ($amountBilled - $rPI['vat'] - $rPI['ewt'] - $rPI['retention'] - $rPI['contractor'] - $rPI['recoupment']);
						$billedPercent = ($proj_cost && $amountBilled) ? functions::formatMoney(($amountBilled / $proj_cost) * 100) : 0;

						if($rPI['pi_date']){
							$openPar='';$closePar='';
							$totalAmountBilled += $amountBilled;
							$projVAT += $rPI['vat'];
							$projEWT += $rPI['ewt'];
							$projRetention += $rPI['retention'];
							$projConTax += $rPI['contractor'];
							$totalNetBilled += $netBilled;
							$countBilledPercent += $billedPercent;
							$projRecoupment += $rPI['recoupment'];
						}

						if($rPI['submit_date']){
							$daysElapsed = ($rPI['pi_date']) ? functions::date_diff($rPI['submit_date'],$rPI['pi_date']) : functions::date_diff($rPI['submit_date'],date('Y-m-d'));
						}
						else
							$daysElapsed = '-';
				?>
				<tr>
					<td class="padleft"><a id="manageBill<?php echo $rPI['pi_id']?>'" class="thickbox" style="cursor:pointer" title="Manage Billing" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-billing-add.php?pID=<?php echo functions::encode($rProj['proj_id'])?>&pEdt=<?php echo functions::encode($rPI['pi_id'])?>','Manage Billing')"><?php echo $rPI['name'];?></a></td>
					<td>&nbsp;</td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($amountBilled).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.$billedPercent.'%'.$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['vat']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['ewt']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['retention']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['contractor']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['recoupment']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($netBilled).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::datearr($rPI['submit_date']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::datearr($rPI['pi_date']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $daysElapsed;?></div></td>
				</tr>
				<?php endwhile;?>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalAmountBilled);?></strong></div></td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($countBilledPercent).'%'?></strong></div></td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($projVAT);?></strong></div></td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($projEWT);?></strong></div></td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($projRetention);?></strong></div></td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($projConTax);?></strong></div></td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($projRecoupment);?></strong></div></td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalNetBilled);?></strong></div></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
					<?php #advance payment and retention
					$advance_payment = 0;
					$qPI = $db->select('project_income','*',array('proj_id'=>$rProj['proj_id']),' AND (lower(name) = "advance payment" OR lower(name) = "retention payment") ORDER BY pi_date ASC');
					while($rPI = $db->fetch_array($qPI)):
						$advance_payment += ($rPI['name']=="Advance Payment") ? $rPI['amount'] : 0;
						$amountBilled = $rPI['amount'];
						$netBilled = ($amountBilled - $rPI['vat'] - $rPI['ewt'] - $rPI['retention'] - $rPI['contractor'] - $rPI['recoupment']);
					?>
				<tr>
					<td class="padleft"><?php echo $rPI['name'];?></td>
					<td>&nbsp;</td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($amountBilled);?></div></td>
					<td>&nbsp;</td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($rPI['vat']);?></div></td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($rPI['ewt']);?></div></td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($rPI['retention']);?></div></td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($rPI['contractor']);?></div></td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($rPI['recoupment']);?></div></td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($netBilled);?></div></td>
					<td class="padleft"><div align="right"><?php echo functions::datearr($rPI['submit_date']);?></div></td>
					<td class="padleft"><div align="right"><?php echo functions::datearr($rPI['pi_date']);?></div></td>
					<td>&nbsp;</td>
				</tr>
			<?php endwhile;?>
			<?php if($advance_payment && $projRecoupment){?>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td class="padleft"></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td class="padleft" colspan="2"><div align="right">Recoupment Balance</div></td>
					<td class="padleft"><strong><?php echo functions::formatMoney($advance_payment-$projRecoupment);?></strong></td>
					<td></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<?php }//if($advance_payment){?>
				<tr>
					<td colspan="13">&nbsp;</td>
				</tr>
			<?php endwhile;?>
			</tbody>
		</table>
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
<script>function selDt(PiEwgD){window.location="<?php echo functions::pageName()?>?yr="+PiEwgD}</script>
<script>
function projSel(v){
	window.location="<?php echo functions::pageName()?>?prjID=" + v;
}
</script>
<!-- end: JavaScript-->
</body>
</html>