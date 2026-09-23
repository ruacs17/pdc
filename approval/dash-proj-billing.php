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
$project_id = ( isset($_REQUEST['prjID']) && !empty($_REQUEST['prjID']) ) ? functions::decode($_REQUEST['prjID']) : '';

function monthDisp($date=0){
	$exp = explode('-',$date);
	$monthName='';
	if(count($exp)==2){
		$mn = $exp[1];
		$yr = $exp[0];
		$mkDate = mktime(0,0,0,$mn,1,$yr);
		$monthName = date("F'y",$mkDate);
	}
	return $monthName;
}
$arrDirectCost=array();
$arrOverheadCost=array();
$arrNetBilled=array();

$totalNetBilled=0; $allNetBilled=0; $totalCollected=0; $totalCollectible=0;
$year = date('Y');
$month_start = date('m');
$year_start = date('Y');
$month_end = date('m');
$year_end = date('Y');
$year = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? functions::decode($_REQUEST['yr']) : $year;
$arrMonth=array();

$month_start = '01';
$year_start = $year;

$month_end = '11';
$year_end=$year;
$start_date=$year.'-01-01';
$end_date=($year).'-12-31';
#end getting end month

$month_diff = functions::month_diff($start_date,$end_date);
for($i=0; $i<=12; $i++):
	$mkDate = mktime(0,0,0,$month_start + $i,1,$year_start);
	$arrMonth[] = date('Y-m',$mkDate);
	$arrDirectCost[date('Y-m',$mkDate)]=0;
	$arrNetBilled[date('Y-m',$mkDate)]=0;
endfor;
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
				<li class="active"><a href="dash-proj-billing.php" style="opacity:.9">All Projects</a></li>
				<li><a href="dash-proj-billing-project.php">Selected Project</a></li>
			</ul>
		</div>
		<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
			<tr>
				<td width="50%"><div align="right"><a id="whprint" href="dash-proj-billing-recoup-print.php?yr=<?php echo functions::encode($year)?>" class="btn btn-info"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div></td>
			</tr>
		</table>
		<div align="center"><br>
			<table width="80%" border="0">
				<tr>
					<td width="50%" align="right"><strong>Project Year Started:</strong>&nbsp;</td>
					<td width="50%" align="left" valign="middle" style="padding: 12px 0px 4px 0px">
						<select name="yr" id="yr" style="width:150px;" onchange='selDt(this.value)'>
							<option value="">-- Select --</option>
							<?php
							$qYr = $db->query('SELECT DISTINCT left(date_start,4) as dt FROM project WHERE date_start IS NOT NULL ORDER BY date_start DESC ');
							while($rYr = $db->fetch_array($qYr)):
							?>
							<option value="<?php echo functions::encode($rYr['dt'])?>" <?php if($year==$rYr['dt'])echo 'selected="selected"';?>><?php echo $rYr['dt']?></option>
							<?php endwhile;?>
						</select>
					</td>
				</tr>
			</table><br>
		</div>
		<table class="table-hover" width="100%" border="1" style="font-size:12px;">
			<?php
			$allAmountBilled=0;
			$allRecoupment=0;
			$qProj = $db->query('SELECT * FROM project WHERE project="1" AND date_start BETWEEN "'.$start_date.'" AND "'.$end_date.'" ORDER BY proj_name');
			while( $rProj = $db->fetch_array($qProj)):
				$proj_cost = $rProj['proj_cost'];
			?>
			<thead>
				<tr style="background-color:#CCC;">
					<td class="padleft" width="20%"><strong>Project</strong></td>
					<td class="padleft" width="7%"><strong>Cost</strong></td>
					<td class="padleft" width="7%"><div align="left"><strong>Proposed Bill</strong></div></td>
					<td class="padleft" width="7%"><div align="left"><strong>% Billed</strong></div></td>
					<td class="padleft" width="7%"><div align="left"><strong>VAT</strong></div></td>
					<td class="padleft" width="7%"><div align="left"><strong>EWT</strong></div></td>
					<td class="padleft" width="7%"><div align="left"><strong>Retention</strong></div></td>
					<td class="padleft" width="7%"><div align="left"><strong>Contractor's Tax</strong></div></td>
					<td class="padleft" width="7%"><div align="left"><strong>Recoupment</strong></div></td>
					<td class="padleft" width="7%"><div align="left"><strong>Net Bill</strong></div></td>
					<td class="padleft" width="7%"><div align="left"><strong>Submitted</strong></div></td>
					<td class="padleft" width="7%"><div align="left"><strong>Collected</strong></div></td>
					<td class="padleft" width="7%"><div align="left"><strong>Days<br>Elapsed</strong></div></td>
				</tr>
				<tr>
					<td class="padleft"><strong><?php echo $rProj['proj_name']?></strong><a id="AddBill<?php echo $rProj['proj_id']?>'" class="thickbox" style="cursor:pointer" title="Manage Billing" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-billing-add.php?pID=<?php echo functions::encode($rProj['proj_id'])?>','Add Billing')"><i class="halflings-icon plus-sign"></i></a><br></td>
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
				</tr>
				<tr>
					<td class="padleft" colspan="2"><div>TIN: <?php echo $rProj['tin'];?></div></td>
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
			</thead>
			<tbody>
				<?php
				$qPI = $db->select('project_income','*',array('proj_id'=>$rProj['proj_id']),' AND lower(name) != "advance payment" AND  lower(name) != "retention payment" ORDER BY pi_date ASC');
				$countBilledPercent=0;$totalAmountBilled=0; $totalNetBilled=0;
				$daysElapsed = 0;$projRecoupment=0;$projVAT=0;$projEWT=0;$projRetention=0;$projConTax=0;$openPar='';$closePar='';
				while($rPI = $db->fetch_array($qPI)):
					$openPar='(<i>';$closePar='</i>)';
					$daysElapsed = 0;
					$amountBilled = $rPI['amount'];
					$netBilled = ($amountBilled - $rPI['vat'] - $rPI['ewt'] - $rPI['retention'] - $rPI['contractor'] - $rPI['recoupment']);
					$billedPercent = ($proj_cost && $amountBilled) ? functions::formatMoney(($amountBilled / $proj_cost) * 100) : 0;
					if($rPI['pi_date']){
						$openPar='';$closePar='';
						$totalAmountBilled += $amountBilled;
						$allAmountBilled +=$amountBilled;
						$projRecoupment += $rPI['recoupment'];
						$projVAT += $rPI['vat'];
						$projEWT += $rPI['ewt'];
						$projRetention += $rPI['retention'];
						$projConTax += $rPI['contractor'];
						$totalNetBilled += $netBilled;
						$allNetBilled += $netBilled;
						$countBilledPercent += $billedPercent;
					}

					if($rPI['pi_date'])
						$totalCollected += $amountBilled;
					else
						$totalCollectible += $amountBilled;

					if($rPI['submit_date']){
						if($rPI['pi_date'])
							$daysElapsed = functions::date_diff($rPI['submit_date'],$rPI['pi_date']);
						else
							$daysElapsed = functions::date_diff($rPI['submit_date'],date('Y-m-d'));
					}
					else
						$daysElapsed = '-';
				?>
				<tr>
					<td class="padleft"><a id="manageBill<?php echo $rPI['pi_id']?>'" class="thickbox" style="cursor:pointer" title="Manage Billing" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-billing-add.php?pID=<?php echo functions::encode($rProj['proj_id'])?>&pEdt=<?php echo functions::encode($rPI['pi_id'])?>','Manage Billing')"><?php echo $rPI['name'];?></a></td>
					<td>&nbsp;</td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($amountBilled).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($billedPercent).'%'.$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['vat']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['ewt']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['retention']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['contractor']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['recoupment']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($netBilled).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::datearr($rPI['submit_date']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::datearr($rPI['pi_date']).$closePar;?></div></td>
					<td class="padleft"><div align="center"><?php echo $daysElapsed;?></div></td>
				</tr>
				<?php endwhile;?>
				<tr>
					<td>&nbsp;</td>
					<td class="padleft"><div align="right"><strong>Total</strong></div></td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalAmountBilled);?></div></strong></td>
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
				<?php #advance payment
				$advance_payment = 0; $recoup_bal=0;
				$qPI = $db->select('project_income','*',array('proj_id'=>$rProj['proj_id']),' AND (lower(name) = "advance payment" OR lower(name) = "retention payment") ORDER BY pi_date ASC');
				while($rPI = $db->fetch_array($qPI)):
					$openPar='(<i>';$closePar='</i>)';
					$advance_payment += ($rPI['name']=="Advance Payment") ? $rPI['amount'] : 0;
					$amountBilled = $rPI['amount'];
					$netBilled = ($amountBilled - $rPI['vat'] - $rPI['ewt'] - $rPI['retention'] - $rPI['contractor'] - $rPI['recoupment']);

					$netBilled = ($amountBilled - $rPI['vat'] - $rPI['ewt'] - $rPI['retention'] - $rPI['contractor'] - $rPI['recoupment']);
					$totalNetBilled += $netBilled;
					$billedPercent = ($proj_cost && $amountBilled) ? functions::formatMoney(($amountBilled / $proj_cost) * 100) : 0;
					if($rPI['submit_date']){
						if($rPI['pi_date'])
							$daysElapsed = functions::date_diff($rPI['submit_date'],$rPI['pi_date']);
						else
							$daysElapsed = functions::date_diff($rPI['submit_date'],date('Y-m-d'));
					}
					else
						$daysElapsed = '-';
					if($rPI['pi_date']){
						$openPar='';$closePar='';
						#$recoup_bal += $advance_payment-$projRecoupment;
					}
				?>
				<tr>
					<td class="padleft"><a id="manageBill<?php echo $rPI['pi_id']?>'" class="thickbox" style="cursor:pointer" title="Manage Billing" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-billing-add.php?pID=<?php echo functions::encode($rProj['proj_id'])?>&pEdt=<?php echo functions::encode($rPI['pi_id'])?>','Manage Billing')"><?php echo $rPI['name'];?></a></td>
					<td>&nbsp;</td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($amountBilled).$closePar;?></div></td>
					<td>&nbsp;</td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['vat']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['ewt']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['retention']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['contractor']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($rPI['recoupment']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::formatMoney($netBilled).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::datearr($rPI['submit_date']).$closePar;?></div></td>
					<td class="padleft"><div align="right"><?php echo $openPar.functions::datearr($rPI['pi_date']).$closePar;?></div></td>
					<td class="padleft"><div align="center"><?php echo $daysElapsed;?></div></td>
				</tr>
				<?php
				if($rPI['pi_date']){$allRecoupment += $advance_payment-$projRecoupment;}
				?>
				<?php endwhile;//while($rPI = $db->fetch_array($qPI)):?>
				<?php if($advance_payment && $projRecoupment){ ?>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td class="padleft"></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td class="padleft" colspan="2"><div align="right">Recoupment Balance</div></td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($advance_payment-$projRecoupment);#functions::formatMoney($advance_payment-$projRecoupment);?></strong></div></td>
					<td></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
					<?php }//if($advance_payment){?>
				<tr>
					<td colspan="13" height="50">&nbsp;</td>
				</tr>
				<?php endwhile;?>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($allAmountBilled);?></strong></div></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td class="padleft" colspan="2"><div align="right">Total Recoupment Balance</div></td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($allRecoupment);?></strong></div></td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($allNetBilled);?></strong></div></td>
					<td class="padleft"><strong><?php #echo functions::formatMoney($totalCollectible);?></strong></td>
					<td class="padleft"><strong><?php #echo functions::formatMoney($totalCollected);?></strong></td>
					<td>&nbsp;</td>
				</tr>
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
<!-- end: JavaScript-->
</body>
</html>