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

function monthDays($month=0,$year=0){
    $list = array();
    $month = ($month) ? $month : date('m');
    $year = ($year) ? $year : date('Y');
    for($d=1; $d<=31; $d++):
        $time = mktime(12,0,0,$month,$d,$year);
        if( date('m',$time)==$month )
            $list[]=date('Y-m-d',$time);
    endfor;
return $list;
}

$yr = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');
$mn = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : '';

if( isset($_POST['btnSearch']) ){
    $year = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? functions::decode($_POST['bdYear']) : date('Y');
    $selMonth = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $_SESSION['as_selMonth']=$selMonth;
    $_SESSION['as_selYr']=$year;
    functions::sendTo($_SERVER['PHP_SELF']);
    die();
}
$selMonth = ( isset($_SESSION['as_selMonth']) && !empty($_SESSION['as_selMonth']) ) ? $_SESSION['as_selMonth'] : $mn;
$year = ( isset($_SESSION['as_selYr']) && !empty($_SESSION['as_selYr']) ) ? $_SESSION['as_selYr'] : $yr;
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
if( isset($_REQUEST['biidel']) && !empty($_REQUEST['biidel']) ){
    $biidel = ( isset($_REQUEST['biidel']) && !empty($_REQUEST['biidel']) ) ? functions::decode($_REQUEST['biidel']) : 0;
    $db->delete('billing_income',array('bi_id'=>$biidel));
    functions::sendTo($_SERVER['PHP_SELF']);
    die();
}
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
    <style>
    .lnkOpt{
        opacity: 0.4;
        filter: alpha(opacity=20); 
        cursor:pointer
    }
    .lnkOpt:hover {
        opacity: 1.0;
        filter: alpha(opacity=100);
        cursor:pointer
    }
    /*a {
       opacity: 0.4;
        filter: alpha(opacity=20);
    }

    a:hover {
        opacity: 1.0;
        filter: alpha(opacity=100);
    }*/
    .scrollme {
        overflow-y: auto;
    }
    </style>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div> 
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header">
            <h2><i class="halflings-icon white th"></i><span class="break"></span>Income Billing Report</h2>
        </div>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li class="active"><a href="dash-proj-billing-income.php">Billing Income</a></li>
                <li><a href="dash-proj-billing-yearly.php">Yearly Report</a></li>
                <li><a href="dash-proj-billing.php" style="opacity:.9">All Projects</a></li>
                <li><a href="dash-proj-billing-project.php">Selected Project</a></li>
            </ul>
        </div>
        <table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
            <tr>
                <td width="50%"><div align="right"><a id="adc" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'dash-proj-billing-income-add.php?y=<?php echo functions::encode($year)?>&m=<?php echo functions::encode($selMonth)?>','Statement Add')">Add New Statement</a>&nbsp;&nbsp;&nbsp;</div></td>
            </tr>
        </table>
        <form method="post">
            <div align="center"><br>&nbsp;&nbsp;
                <select name="bdYear" id="bdYear">
                    <option value="">--Select Year--</option>
                    <?php for($y=(date('Y')+1);$y>=2012;$y--):?>
                    <option value="<?php echo functions::encode($y);?>" <?php if($year==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
                    <?php endfor;?>
                </select>
                <select name="bdMon" id="bdMon" style="width:95px;">
                    <option value="">All Month</option>
                    <option value="01" <?php if($selMonth=='01')echo 'selected="selected"';?>>Jan</option>
                    <option value="02" <?php if($selMonth=='02')echo 'selected="selected"';?>>Feb</option>
                    <option value="03" <?php if($selMonth=='03')echo 'selected="selected"';?>>Mar</option>
                    <option value="04" <?php if($selMonth=='04')echo 'selected="selected"';?>>Apr</option>
                    <option value="05" <?php if($selMonth=='05')echo 'selected="selected"';?>>May</option>
                    <option value="06" <?php if($selMonth=='06')echo 'selected="selected"';?>>Jun</option>
                    <option value="07" <?php if($selMonth=='07')echo 'selected="selected"';?>>Jul</option>
                    <option value="08" <?php if($selMonth=='08')echo 'selected="selected"';?>>Aug</option>
                    <option value="09" <?php if($selMonth=='09')echo 'selected="selected"';?>>Sep</option>
                    <option value="10" <?php if($selMonth=='10')echo 'selected="selected"';?>>Oct</option>
                    <option value="11" <?php if($selMonth=='11')echo 'selected="selected"';?>>Nov</option>
                    <option value="12" <?php if($selMonth=='12')echo 'selected="selected"';?>>Dec</option>
                </select>
                <input type="submit" name="btnSearch" id="btnSearch" value="View" class="btn btn-primary">
            </div>
            <div class="box-content">
                <table class="table" style="font-size:14px;">
                    <tbody>
                    <?php
                    $yearlyCollected=0;$yearlyBilled=0;$yearlySalesService=0;$yearlyVat=0;$yearlyEWT=0;
                    for($m = 1; $m <= 12; $m++):
                        $time = mktime(0,0,0,$m,1,$year);
                        $monthName = date('F',$time);
                        $days = monthDays($m,$year);
                        $mth = ($m<=9) ? '0'.$m : $m;
                        if($selMonth==$m || $selMonth == ''){
                            echo '
                            <tr>
                                <th width="10%">MONTH</th>
                                <th width="25%">PROJECT</th>
                                <th width="12%">AMOUNT BILLED</th>
                                <th width="12%">AMOUNT COLLECTED</th>
                                <th width="10%">SALES SERVICE</th>
                                <th width="10%">VAT</th>
                                <th width="10%">EWT</th>
                            </tr>
                            <tr>
                                <td colspan="7"><div align="left">&nbsp;</div></td>
                            </tr>
                            <tr>
                                <td colspan="7"><div align="left"><strong>Month Of '.$monthName.'</strong></div></td>
                            </tr>
                            ';
                        }
                        $monthlyCollected=0;$monthlyBilled=0;$monthlySalesService=0;$monthlyVat=0;$monthlyEWT=0;$vat=0;$ewt=0;$name='';
                        $qProj = $db->query('SELECT * FROM project_income WHERE left(pi_date,7)="'.$year.'-'.$mth.'" ORDER BY name');
                        while($rProj = $db->fetch_array($qProj)):
                            $vat=0; $ewt=0;
                            $amountBilled = $rProj['amount'];
                            $amountCollected = $amountBilled - ($rProj['vat']+$rProj['ewt']+$rProj['retention']+$rProj['contractor']+$rProj['recoupment']);
                                    
                            $salesService = $amountBilled / 1.12;
                            $vat = $rProj['vat'];
                            $ewt = $rProj['ewt'];
                            $monthlyCollected+=$amountCollected; $monthlyBilled+=$amountBilled; $monthlySalesService+=$salesService; $monthlyVat+=$vat; $monthlyEWT+=$ewt;
                            $yearlyCollected+=$amountCollected; $yearlyBilled+=$amountBilled; $yearlySalesService+=$salesService; $yearlyVat+=$vat; $yearlyEWT+=$ewt;
                            $tin = $db->getValue('project','tin',array('proj_id'=>$rProj['proj_id']));
                            if($selMonth==$m || $selMonth == ''){
                        ?>
                        <tr>
                            <td>&nbsp;</td>
                            <td><strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rProj['proj_id']));?></strong></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td><?php echo ($tin) ? 'TIN: <strong>'.$tin.'</strong>' : '';?></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;&nbsp;<?php echo $rProj['name'];?></td>
                            <td><?php echo functions::formatMoney($amountBilled);?></td>
                            <td><?php echo functions::formatMoney($amountCollected)?></td>
                            <td><?php echo functions::formatMoney($salesService);?></td>
                            <td><?php echo functions::formatMoney($vat);?></td>
                            <td><?php echo functions::formatMoney($ewt);?></td>
                        </tr>
                        <?php if($rProj['recoupment']){?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;&nbsp;<i>Recoupment</i></td>
                            <td><?php echo functions::formatMoney($rProj['recoupment']);?></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <?php }//end if($rProj['recoupment']){?>
                        <?php if($rProj['contractor']){?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;&nbsp;<i>Contractor's Tax</i></td>
                            <td><?php echo functions::formatMoney($rProj['contractor']);?></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <?php }//end if($rProj['contractor']){?>
                        <?php if($rProj['retention']){?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;&nbsp;<i>Retention</i></td>
                            <td><?php echo functions::formatMoney($rProj['retention']);?></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <?php }//end if($rProj['contractor']){?>
                        <tr>
                            <td colspan="7">&nbsp;</td>
                        </tr>
                        <?php }//end if($selMonth==$m || $selMonth == ''){
                        endwhile;
                        if($selMonth==$m || $selMonth == ''){
                            $qOtherBilling = $db->query('SELECT * FROM billing_income WHERE left(bi_date,7)="'.$year.'-'.$mth.'"');
                            while($rOB = $db->fetch_array($qOtherBilling)):
                                $amountBilled = $rOB['bi_amount'];
                                $vat_percent = ($rOB['vat_percent']) ? ($rOB['vat_percent'] / 100) : 0;
                                $ewt_percent = ($rOB['ewt_percent']) ? ($rOB['ewt_percent'] / 100) : 0;
                                $vat = $amountBilled * $vat_percent;
                                $ewt = $amountBilled * $ewt_percent;
                                $amountCollected = $amountBilled - $vat - $ewt;
                                $salesService = $amountBilled / 1.12;
                                $monthlyCollected+=$amountCollected; $monthlyBilled+=$amountBilled; $monthlySalesService+=$salesService; $monthlyVat+=$vat; $monthlyEWT+=$ewt;
                                $yearlyCollected+=$amountCollected; $yearlyBilled+=$amountBilled; $yearlySalesService+=$salesService; $yearlyVat+=$vat; $yearlyEWT+=$ewt;
                        ?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>
                                <strong><?php echo $rOB['bi_name'];?></strong>&nbsp;&nbsp;&nbsp;
                                <a id="ManageItem<?php echo $rOB['bi_id']?>" class="thickbox lnkOpt" title="Manage Item" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-billing-income-edit.php?biid=<?php echo functions::encode($rOB['bi_id'])?>','Adding DQC Item')"><i class="halflings-icon pencil"></i></a>
                                <a id="deleteItem<?php echo $rOB['bi_id']?>" class="lnkOpt" title="Delete Item" data-rel="tooltip" onClick="return delt()" href="?biidel=<?php echo functions::encode($rOB['bi_id'])?>"><i class="halflings-icon minus-sign"></i></a>
                            </td>
                            <td><?php echo functions::formatMoney($amountBilled);?></td>
                            <td><?php echo functions::formatMoney($amountCollected)?></td>
                            <td><?php echo functions::formatMoney($salesService);?></td>
                            <td><?php echo functions::formatMoney($vat);?></td>
                            <td><?php echo functions::formatMoney($ewt);?></td>
                        </tr>
                        <?php endwhile;?>
                        <tr>
                            <td colspan="7">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td><div align="right"><strong>MONTHLY TOTAL</strong></div></td>
                            <td><strong><?php echo functions::formatMoney($monthlyBilled);?></strong></td>
                            <td><strong><?php echo functions::formatMoney($monthlyCollected);?></strong></td>
                            <td><strong><?php echo functions::formatMoney($monthlySalesService);?></strong></td>
                            <td><strong><?php echo functions::formatMoney($monthlyVat);?></strong></td>
                            <td><strong><?php echo functions::formatMoney($monthlyEWT);?></strong></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td><div align="right"><strong>CUMMULATIVE TOTAL</strong></div></td>
                            <td><strong><?php echo functions::formatMoney($yearlyBilled);?></strong></td>
                            <td><strong><?php echo functions::formatMoney($yearlyCollected);?></strong></td>
                            <td><strong><?php echo functions::formatMoney($yearlySalesService);?></strong></td>
                            <td><strong><?php echo functions::formatMoney($yearlyVat);?></strong></td>
                            <td><strong><?php echo functions::formatMoney($yearlyEWT);?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="7" height="70">&nbsp;</td>
                        </tr>
                        <?php
                        }
                        endfor; #end for each month
                        ?>
                        <tr>
                            <td colspan="7"><hr width="100%" style="color:#FF0000"></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td><div align="right"><strong>End of Year <?php echo $year?></strong></div></td>
                            <td><strong><?php echo functions::formatMoney($yearlyBilled);?></strong></td>
                            <td><strong><?php echo functions::formatMoney($yearlyCollected);?></strong></td>
                            <td><strong><?php echo functions::formatMoney($yearlySalesService);?></strong></td>
                            <td><strong><?php echo functions::formatMoney($yearlyVat);?></strong></td>
                            <td><strong><?php echo functions::formatMoney($yearlyEWT);?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
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
<script>
function delt(){
    if(confirm('Do you want to remove this Billing?'))
        return true;
    else
        return false; 
}
</script>
<!-- end: JavaScript-->
</body>
</html>