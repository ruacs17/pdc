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

function getAmount($category_id,$yearMonth=''){
    global $db;
    $accntAmount=0;
    #$accntAmount = $db->getValue('po p, view_po_payment vpp','sum(paid)',array('p.category_id'=>$category_id,'left(p.po_date,7)'=>$yearMonth),'AND p.po_id=vpp.po_id AND paid>0 ORDER BY p.po_date');
    #$qPO = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND p.proj_id="'.$db->clean($project_id).'" AND left(p.po_date,7)="'.$month.'" AND itd.expense_type="materials" AND vpp.paid > 0');
    #$costMaterial += $db->result($qPO,0);
    $accntAmount += $db->getValue('voucher_detail vd, voucher_particular vp, voucher v','sum(amount)',array('vd.category_id'=>$category_id,'left(v.vdate,7)'=>$yearMonth),'AND vd.vp_id=vp.vp_id AND vp.voucher_id=v.voucher_id AND claimed=1');
    return $accntAmount;
}
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



$arr = array();
$year = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? functions::decode($_REQUEST['yr']) : date('Y');
$yrSearch = " AND left(vd_date,4)='".$db->clean($year)."'";  
$yrSearchPO = " AND left(po_date,4)='".$db->clean($year)."'";
$yrPO = " AND left(p.po_date,4)='".$year."'";
$yrVoucher = " AND left(v.vdate,4)='".$year."'";

if(isset($_POST['btnSearch'])){
    $yr = (isset($_POST['yr']) && !empty($_POST['yr']) ) ? functions::decode($_POST['yr']) : 0;
    if($yr){
        functions::sendTo($_SERVER['PHP_SELF'].'?yr='.functions::encode($yr));
    }  
}

$arrMonth=array();
$month_start = '01';
$year_start = $year;

$month_end = '11';
$year_end=$year;
$start_date=$year.'-01-01';
$end_date=$year.'-12-31';
$arr_asset_total_monthly=array(); $arr_asset_current_total_monthly=array(); $asset_current_total_amount=0; $asset_total_amount=0;
$arr_asset_noncurrent_total_monthly=array(); $asset_noncurrent_total_amount=0;


$arr_liabilities_total_monthly=array();
$arr_liabilities_current_total_monthly=array();$liabilities_current_total_amount=0;

$arr_liabilities_noncurrent_total_monthly=array(); $liabilities_noncurrent_total_amount=0;
$liabilities_total_amount=0;


$arr_equity_total_monthly=array();$equity_current_total_amount=0;
$arr_equity_equity_total_monthly=array();
$equity_total_amount=0;
for($i=0; $i<12; $i++):
    $mkDate = mktime(0,0,0,$month_start + $i,1,$year_start);
    $arrMonth[] = date('Y-m',$mkDate);
    $arr_month_type_group[date('Y-m',$mkDate)] = 0;
    $arr_asset_current_total_monthly[date('Y-m',$mkDate)] = 0;
    $arr_asset_noncurrent_total_monthly[date('Y-m',$mkDate)] = 0;
    $arr_asset_total_monthly[date('Y-m',$mkDate)] = 0;

    $arr_liabilities_current_total_monthly[date('Y-m',$mkDate)] = 0;
    $arr_liabilities_noncurrent_total_monthly[date('Y-m',$mkDate)] = 0;
    $arr_liabilities_total_monthly[date('Y-m',$mkDate)] = 0;

    $arr_equity_total_monthly[date('Y-m',$mkDate)] = 0;
    $arr_equity_equity_total_monthly[date('Y-m',$mkDate)] = 0;
endfor;


$arr_current_assets = array();$arr_noncurrent_assets = array();
$arr_current_liabilities=array();$arr_noncurrent_liabilities=array();
$arr_equity=array(); $arr_income=array();
$arr_exp_labor=array();$arr_exp_material=array();$arr_exp_subcon=array();$arr_exp_inco=array();$arr_exp_gase=array();
$arr_others=array();
$countCharge=0;
$qC = $db->select('item_deduction','*',array(),'ORDER BY account_type,account_type_group,numbering is null, numbering,name');
while($rC = $db->fetch_array($qC)):
    $arr = array('item_id'=>$rC['item_id'],'name'=>$rC['name'],'description'=>$rC['description'],'cost_type'=>$rC['cost_type'],'expense_type'=>$rC['expense_type'],'account_type'=>$rC['account_type'],'account_type_group'=>$rC['account_type_group'],'numbering'=>$rC['numbering']);
    if($rC['account_type']=='assets'){
        if($rC['account_type_group']=='current asset')
            $arr_current_assets[]=$arr;
        else if($rC['account_type_group']=='non-current asset')
            $arr_noncurrent_assets[]=$arr;
    }
    elseif($rC['account_type']=='liabilities'){
        if($rC['account_type_group']=='current liabilities')
            $arr_current_liabilities[]=$arr;
        else if($rC['account_type_group']=='non-current liabilities')
            $arr_noncurrent_liabilities[]=$arr;
    }
    elseif($rC['account_type']=='equity'){
        if($rC['account_type_group']=='equity')
            $arr_equity[]=$arr;
    }
    /*elseif($rC['account_type']=='income'){
        if($rC['account_type_group']=='income')
            $arr_income[]=$arr;
    }
    elseif($rC['account_type']=='expense'){
        if($rC['account_type_group']=='direct labor')
            $arr_exp_labor[]=$arr;
        else if($rC['account_type_group']=='direct material')
            $arr_exp_material[]=$arr;
        else if($rC['account_type_group']=='sub contractor')
            $arr_exp_subcon[]=$arr;
        else if($rC['account_type_group']=='indirect cost')
            $arr_exp_inco[]=$arr;
        else if($rC['account_type_group']=='general, administrative and selling expense')
            $arr_exp_gase[]=$arr;
    }
    else{
        $arr_others[]=$arr;
    }*/
$countCharge++;
endwhile;
$cntChrg=0;
$arrCharge=array();
$arrCharge = array_merge($arr_current_assets,$arr_noncurrent_assets,$arr_current_liabilities,$arr_noncurrent_liabilities,$arr_equity,$arr_income,$arr_exp_labor,$arr_exp_material,$arr_exp_subcon,$arr_exp_inco,$arr_exp_gase,$arr_others);
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
    <link id="base-style" href="../css/loader.css" rel="stylesheet">
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
<div id="spinner"></div>
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Charges Report</h2>
        </div>
        <div class="box-content" align="center">
            <ul class="nav tab-menu nav-tabs" style="display:none;">
                <li><a href="charge_category_report_voucher_po.php">PO / Voucher</a></li>
                <li><a href="charge_category_report_voucher.php">Voucher</a></li>
                <li><a href="charge_category_report.php">PO</a></li>
                <li class="active"><a href="dash-expenses.php" style="opacity:.9">Charges Category Report</a></li>
            </ul>
            <form method="post">
                <table width="80%" border="0">
                    <tr>
                        <td width="31%" align="right"><strong>Select Year:</strong>&nbsp;</td>
                        <td width="17%" align="left" valign="middle" style="padding: 12px 0px 4px 0px">
                            <select name="yr" id="yr" style="width:150px;">
                                <?php for($y=(date('Y')+1);$y>=2010;$y--):?>
                                <option value="<?php echo functions::encode($y);?>" <?php if($year==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
                                <?php endfor;?>
                             </select>
                        </td>
                        <td width="52%" valign="middle"><input type="submit" name="btnSearch" id="btnSearch" value="Select" class="btn btn-primary"></td>
                    </tr>
                </table><br><br><br>
            </form>
            <table border="1" cellspacing="2" width="98%" class="tablea table-striped table-bordered" style="font-size:12px">
                <thead>
                    <tr>
                        <th width="20%">&nbsp;</th>
                        <?php foreach($arrMonth as $monthBalance):?>
                        <td><div align="center"><?php echo monthDisp($monthBalance)?></div></td>
                        <?php endforeach;?>
                        <th width="8%"><div align="center">Amount</div></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding-left: 30px;">Advances to Suppliers</td>
                        <?php $totalAmountPerAccount=0;$accntAmount=0;
                        foreach($arrMonth as $monthBalance):?>
                        <td>
                            <div align="right">
                            <?php
                            $amnt=0;
                            $start_month = $year.'-01';
                            $amnt = $db->getValue('po p, voucher_po_payment vpp, voucher_particular vp, voucher v','sum(amount)',array(),'WHERE received=0 AND p.po_id=vpp.po_id AND vpp.vp_id=vp.vp_id AND vp.voucher_id=v.voucher_id AND left(v.vdate,7) BETWEEN "'.$start_month.'" AND "'.$monthBalance.'" AND v.approved is not null');
                            #echo $db->last_query;echo '<br>';
                            $accntAmount = $amnt;
                            #echo functions::formatMoney($accntAmount);
                            $arr_asset_current_total_monthly[$monthBalance]+=$accntAmount;
                            $arr_asset_total_monthly[$monthBalance]+=$accntAmount;
                            $totalAmountPerAccount=$accntAmount;
                            $asset_current_total_amount=$accntAmount;
                            $asset_total_amount+=$accntAmount;
                            $cntChrg++;
                            ?>
                            <div align="right"><a id="view<?php echo $cntChrg?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onclick="showThis(this.id,'dash-balance-sheet-details.php?strt=<?php echo functions::encode($start_month);?>&end=<?php echo functions::encode($monthBalance);?>&type=<?php echo functions::encode('advancestosupplier')?>','Charges Detail','1')"><?php echo functions::formatMoney($amnt);?></a></div>
                            </div>
                        </td>
                        <?php endforeach;?>
                        <td><div align="right"><?php echo functions::formatMoney($totalAmountPerAccount);?></div></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 30px;">Accounts Payable -Trade</td>
                        <?php $totalAmountPerAccount=0;$accntAmount=0;
                        foreach($arrMonth as $monthBalance):?>
                        <td>
                            <div align="right">
                            <?php
                            $amnt=0;
                            $start_month = $year.'-01';
                            #$amnt = $db->getValue('po p, view_po_payment vpp','sum(paid)',array(),'WHERE received=0 AND left(p.po_date,7) BETWEEN "'.$start_month.'" AND "'.$monthBalance.'"');
                            $amnt = $db->getValue('po p, view_po_payment vpp','sum(balance)',array(),'WHERE received=1 AND p.po_id=vpp.po_id AND left(p.po_date,7) BETWEEN "'.$start_month.'" AND "'.$monthBalance.'" AND balance > 0');
                            #echo $db->last_query;echo '<br>';
                            if($amnt <= 0)
                                $amnt=0;
                            $accntAmount = $amnt;
                            #echo functions::formatMoney($accntAmount);
                            $arr_asset_current_total_monthly[$monthBalance]+=$accntAmount;
                            $arr_asset_total_monthly[$monthBalance]+=$accntAmount;
                            $totalAmountPerAccount=$accntAmount;
                            $asset_current_total_amount=$accntAmount;
                            $asset_total_amount+=$accntAmount;
                            $cntChrg++;
                            ?>
                            <a id="aptd<?php echo $cntChrg?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onclick="showThis(this.id,'dash-balance-sheet-details-accountpayabletrade.php?strt=<?php echo functions::encode($start_month);?>&end=<?php echo functions::encode($monthBalance);?>&type=<?php echo functions::encode('accountpayabletrade')?>','Charges Detail','1')"><?php echo functions::formatMoney($accntAmount);?></a>
                            </div>
                        </td>
                        <?php endforeach;?>
                        <td><div align="right"><a id="apt<?php echo $cntChrg?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onclick="showThis(this.id,'dash-balance-sheet-details-accountpayabletrade.php?strt=<?php echo functions::encode($start_month);?>&end=<?php echo functions::encode($monthBalance);?>&type=<?php echo functions::encode('accountpayabletrade')?>','Charges Detail','1')"><?php echo functions::formatMoney($totalAmountPerAccount);?></a></div></td>
                    </tr>
                </tbody>
            </table>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>