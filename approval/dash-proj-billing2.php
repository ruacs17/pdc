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
#$project_id=9;
$arrDirectCost=array();
$arrOverheadCost=array();
$arrNetBilled=array();
$totalLabor=0;  $totalMaterial=0; $totalEquipment=0;  $totalCommitment=0; $totalFinder=0; $totalTechnical=0; $totalSubcon=0; $totalConsultancy=0; $totalDirectCost=0; $totalOverheadCost=0; $totalOperatingExpenses=0; $totalIncome=0; $totalNetBilled=0;
$countID=0;$totalVAT=0;$totalEWT=0;$totalRetention=0;$totalConTax=0;$totalRecoupment=0; $totalDeductions=0;

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
$end_date=($year + 1).'-01-31';
#end getting end month


#$month_diff = functions::month_diff($start_date,$year_end.'-'.$month_end.'-01');
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
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header">
            <h2><i class="halflings-icon white th"></i><span class="break"></span>Project Billing Report</h2>
        </div>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li><a href="dash-proj-income-statement-yearly.php">Yearly Report</a></li>
                <li class="active"><a href="dash-proj-income-statement-all.php" style="opacity:.9">All Projects</a></li>
                <li><a href="dash-proj-income-statement.php">Selected Project</a></li>
            </ul>
        </div>
        <div align="center"><br>
            <table width="80%" border="0">
                <tr>
                    <td width="50%" align="right"><strong>Select Year:</strong>&nbsp;</td>
                    <td width="50%" align="left" valign="middle" style="padding: 12px 0px 4px 0px">
                        <select name="yr" id="yr" style="width:150px;" onchange='selDt(this.value)'>
                            <option value="">-- Select --</option>
                            <?php
                            $qYr = $db->query('SELECT DISTINCT left(vd_date,4) as dt FROM voucher_detail ORDER BY vd_date DESC ');
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
            <tr>
                <td>Project</td>
                <td>Cost</td>
                <?php foreach($arrMonth as $month):?>
                <td><div align="center"><?php echo monthDisp($month)?></div></td>
                <?php endforeach;?>
                <td class="padleft"><div align="right">VAT</div></td>
                <td class="padleft"><div align="right">EWT</div></td>
                <td class="padleft"><div align="right">Retention</div></td>
                <td class="padleft"><div align="right">Contractor's Tax</div></td>
                <td class="padleft"><div align="right">Recoupment</div></td>
                <td class="padleft"><div align="right">Net Bill</div></td>
                <td class="padleft"><div align="right">Submitted</div></td>
                <td class="padleft"><div align="right">Collected</div></td>
                <td class="padleft"><div align="right">Total</div></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td>&nbsp;</td>
                <?php endforeach;?>
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
            $qProj = $db->query('SELECT * FROM project WHERE project="1" AND proj_id IN (SELECT proj_id FROM project_income WHERE pi_date BETWEEN "'.$start_date.'" AND "'.$end_date.'")');
            while( $rProj = $db->fetch_array($qProj)):
            ?>
            <tr>
                <td><strong><?php echo $rProj['proj_name']?></strong></td>
                <td><strong><?php echo functions::formatMoney($rProj['proj_cost'])?></strong></td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td>&nbsp;</td>
                <?php endforeach;?>
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
                $qPI = $db->select('project_income','*',array('proj_id'=>$rProj['proj_id']),'ORDER BY pi_date ASC');
                while($rPI = $db->fetch_array($qPI)):
                ?>
            <tr>
                <td><?php echo $db->getValue('account_statement','transaction',array('project_income'=>$rPI['pi_id']));?></td>
                <td><?php echo functions::formatMoney($db->getValue('account_statement','as_amount',array('project_income'=>$rPI['pi_id'])));?></td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td>
                    <?php 
                    $as_amount = $db->getValue('account_statement','as_amount',array('project_income'=>$rPI['pi_id']),'AND left(as_date,7)="'.$monthIncome.'"');
                    echo ($as_amount) ? functions::formatMoney($as_amount) : '';
                    ?>
                </td>
                <?php endforeach;?>
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
                <?php endwhile;?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td>&nbsp;</td>
                <?php endforeach;?>
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
          <?php endwhile;?>
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
<script>function selDt(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?yr="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>