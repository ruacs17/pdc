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
        $monthName = date("M'y",$mkDate);
    }
    return $monthName;
}
#$project_id=9;
$arrProjYear=array();
$arrDirectCost=array();
$arrOverheadCost=array();
$arrNetBilled=array();
$totalLabor=0;  $totalMaterial=0; $totalEquipment=0;  $totalCommitment=0; $totalFinder=0; $totalTechnical=0; $totalSubcon=0; $totalConsultancy=0; $totalDirectCost=0; $totalOverheadCost=0; $totalOperatingExpenses=0; $totalIncome=0; $totalNetBilled=0;
$countID=0;$totalVAT=0;$totalEWT=0;$totalRetention=0;$totalConTax=0;$totalRecoupment=0; $totalDeductions=0;

$totalCollectible=0; $totalCollected=0; $count=0;

$year = date('Y');
$month_start = date('m');
$month_end = date('m');
$year_end = date('Y');
$year = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? functions::decode($_REQUEST['yr']) : $year;
$arrMonth=array();

$month_start = '01';
$year_start = $year;

$month_end = '11';
$year_end=$year;
$start_date=$year.'-01-01';
$end_date=($year + 1).'-01-31';
#end getting end month

$month_diff = functions::month_diff($start_date,$end_date);
$arrMonth=array();
#$arrMonth=array();=array('01','02','03','04','05','06','07','08','09','10','11','12');
$arrMonthCollected=array();
$arrMonthCollectible=array();

$month_diff = functions::month_diff($start_date,$year_end.'-'.$month_end.'-01');
for($i=0; $i<=$month_diff; $i++):
    $mkDate = mktime(0,0,0,$month_start + $i,1,$year_start);
    $arrMonth[] = date('Y-m',$mkDate);
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
    <style>.padleft{padding-right: 5px;}</style>
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
                <li class="active"><a href="dash-proj-billing-yearly.php">Yearly Report</a></li>
                <li><a href="dash-proj-billing.php" style="opacity:.9">All Projects</a></li>
                <li><a href="dash-proj-billing-project.php">Selected Project</a></li>
            </ul>
        </div>
        <div align="center"><br>
            <table width="80%" border="0">
                <tr>
                    <td width="50%" align="right"><strong>Billing Year:</strong>&nbsp;</td>
                    <td width="50%" align="left" valign="middle" style="padding: 12px 0px 4px 0px">
                        <select name="yr" id="yr" style="width:150px;" onchange='selDt(this.value)'>
                            <option value="">-- Select --</option>
                            <?php
                                $qYr = $db->query('SELECT DISTINCT left(date_start,4) as dt FROM project WHERE date_start IS NOT NULL ORDER BY date_start DESC');
                                while($rYr = $db->fetch_array($qYr)): $arrProjYear[]=$rYr['dt'];
                            ?>
                            <option value="<?php echo functions::encode($rYr['dt'])?>" <?php if($year==$rYr['dt'])echo 'selected="selected"';?>><?php echo $rYr['dt']?></option>
                            <?php endwhile;?>
                        </select>
                    </td>
                </tr>
            </table><br>
        </div>
        <table class="table-hover" width="100%" border="1" style="font-size:12px;">
            <tr>
                <td class="padleft"><div align="center"><strong>&nbsp;Year&nbsp;</strong></div></td>
                <td class="padleft"><div align="center"><strong>Status</strong></div></td>
                <?php foreach($arrMonth as $month):?>
                <td><div align="center"><strong><?php echo monthDisp($month)?></strong></div></td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><strong>Total</strong></div></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td>&nbsp;</td>
                <?php endforeach;?>
                <td>&nbsp;</td>
            </tr>
            <?php 
            $qYr = $db->query('SELECT DISTINCT left(date_start,4) as dt FROM project WHERE date_start IS NOT NULL AND left(date_start,4)<="'.$year.'" ORDER BY date_start DESC');
            while($rYr = $db->fetch_array($qYr)):
                $yearlyCollected=0;$yearlyCollectible=0;
                $proj_start_yr=$rYr['dt'];
            ?>
            <tr>
                <td rowspan="2" align="center"><?php echo $rYr['dt']?></td>
                <td>Collectible</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        if( !isset($arrMonthCollectible[$monthIncome]) )
                            $arrMonthCollectible[$monthIncome]=0;

                        $monthCollectible='';$monthCollectible=$monthIncome;$monthCollectibleAmount=0;
                        $qCollectible = $db->query('SELECT sum(amount) as net FROM project_income WHERE left(submit_date,7)="'.$monthCollectible.'" AND pi_date IS NULL AND proj_id IN (SELECT proj_id FROM project WHERE left(date_start,4)="'.$proj_start_yr.'")');
                        #echo $db->last_query.'<br>';
                        $monthCollectibleAmount = $db->result($qCollectible);
                        $yearlyCollectible += $monthCollectibleAmount;
                        $totalCollectible += $monthCollectibleAmount;
                        $arrMonthCollectible[$monthIncome] += $monthCollectibleAmount;
                        ?>
                        <a id="costdetail<?php echo $count++?>" class="thickbox" title="Amount Details" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-billing-yearly-detail.php?dte=<?php echo functions::encode($monthCollectible)?>&dstart=<?php echo functions::encode($proj_start_yr)?>&colstat=2','Collectible Details','1')"><?php echo ($monthCollectibleAmount) ? functions::formatMoney($monthCollectibleAmount) : '';?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($yearlyCollectible)?></div></td>
            </tr>
            <tr>
                <td>Collected</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        if( !isset($arrMonthCollected[$monthIncome]) )
                            $arrMonthCollected[$monthIncome]=0;

                        $monthCollected='';$monthCollected=$monthIncome;$monthCollectedAmount=0;
                        $qCollected = $db->query('SELECT sum(amount) as net FROM project_income WHERE left(pi_date,7)="'.$monthCollected.'" AND proj_id IN (SELECT proj_id FROM project WHERE left(date_start,4)="'.$proj_start_yr.'")');
                        #echo $db->last_query;
                        $monthCollectedAmount = $db->result($qCollected);
                        $yearlyCollected += $monthCollectedAmount;
                        $totalCollected += $monthCollectedAmount;
                        $arrMonthCollected[$monthIncome] += $monthCollectedAmount;
                        ?>
                        <a id="costdetail<?php echo $count++?>" class="thickbox" title="Amount Details" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-billing-yearly-detail.php?dte=<?php echo functions::encode($monthCollected)?>&dstart=<?php echo functions::encode($proj_start_yr)?>&colstat=1','Collected Details','1')"><?php echo ($monthCollectedAmount) ? functions::formatMoney($monthCollectedAmount) : '';?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($yearlyCollected)?></div></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td>&nbsp;</td>
                <?php endforeach;?>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td>&nbsp;</td>
                <?php endforeach;?>
                <td>&nbsp;</td>
            </tr>
            <?php endwhile;?>
            <tr>
                <td>&nbsp;</td>
                <td>Total Collectible</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($arrMonthCollectible[$monthIncome]);?></div></td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalCollectible)?></div></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Cummulative</td>
                <?php $cummulativeCollectible=0; foreach($arrMonth as $monthIncome): $cummulativeCollectible += $arrMonthCollectible[$monthIncome]?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($cummulativeCollectible);?></div></td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalCollectible)?></div></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Total Cummulative</td>
                <?php
                $pastCollectibleQ = $db->query('SELECT sum(amount) as net FROM project_income WHERE left(submit_date,4)<"'.$year.'" AND pi_date IS NULL AND proj_id IN (SELECT proj_id FROM project WHERE left(date_start,4)<"'.$year.'")');
                $pastCollectible = $db->result($pastCollectibleQ);
                ?>
                <?php foreach($arrMonth as $monthIncome): $pastCollectible += $arrMonthCollectible[$monthIncome]?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($pastCollectible);?></div></td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($pastCollectible)?></div></td>
            </tr>

            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td>&nbsp;</td>
                <?php endforeach;?>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Total Collected</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($arrMonthCollected[$monthIncome]);?></div></td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalCollected)?></div></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Cummulative</td>
                <?php $cummulativeCollected=0; foreach($arrMonth as $monthIncome): $cummulativeCollected += $arrMonthCollected[$monthIncome]?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($cummulativeCollected);?></div></td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalCollected)?></div></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Total Cummulative</td>
                <?php
                $totalPastCollection=0;
                $pastCollectionQ = $db->query('SELECT sum(amount) as net FROM project_income WHERE left(pi_date,4)<"'.$year.'" AND proj_id IN (SELECT proj_id FROM project WHERE left(date_start,4)<"'.$year.'")');
                $pastCollection = $db->result($pastCollectionQ);
                ?>
                <?php foreach($arrMonth as $monthIncome): $pastCollection += $arrMonthCollected[$monthIncome]?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($pastCollection);?></div></td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($pastCollection)?></div></td>
            </tr>
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
<script>function selDt(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?yr="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>