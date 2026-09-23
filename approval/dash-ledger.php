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
$yrVO = " AND left(vd.vd_date,4)='".$yr."'";
$yrPO = " AND left(p.po_date,4)='".$yr."'";
if(isset($_POST['btnSearch'])){
    $yr = (isset($_POST['yr']) && !empty($_POST['yr']) ) ? functions::decode($_POST['yr']) : 0;
    if($yr){
        $yrSearch = " AND left(vd_date,4)='".$db->clean($yr)."'";  
        $yrSearchPO = " AND left(po_date,4)='".$db->clean($yr)."'";
        $yrPO = " AND left(p.po_date,4)='".$yr."'";
    }  
}
$arr_current_assets = array();$arr_noncurrent_assets = array();
$arr_current_liabilities=array();$arr_noncurrent_liabilities=array();
$arr_equity=array(); $arr_income=array();
$arr_exp_labor=array();$arr_exp_material=array();$arr_exp_subcon=array();$arr_exp_inco=array();$arr_exp_gase=array();
$arr_others=array();
$countCharge=0;
$qC = $db->select('item_deduction','*',array(),'ORDER BY account_type,account_type_group,name');
while($rC = $db->fetch_array($qC)):
    $arr = array('item_id'=>$rC['item_id'],'name'=>$rC['name'],'description'=>$rC['description'],'cost_type'=>$rC['cost_type'],'expense_type'=>$rC['expense_type'],'account_type'=>$rC['account_type'],'account_type_group'=>$rC['account_type_group']);
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
    elseif($rC['account_type']=='income'){
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
    }
$countCharge++;
endwhile;
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
                        <td width="31%" align="right"><strong>Select Year:</strong>&nbsp;</td>
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
                        <td width="52%" valign="middle"><input type="submit" name="btnSearch" id="btnSearch" value="Select" class="btn btn-primary"></td>
                    </tr>
                </table><br><br><br>
            </form>
            <table class="table table-striped table-bordered" style="font-size:12px">
                <thead>
                    <tr>
                        
                        <th width="10%">Project Expense Type</th>
                        <th width="10%">Project Cost Type</th>
                        <th width="20%">Account Type</th>
                        <th width="20%">Name</th>
                        <th width="8%"><div align="center">Amount</div></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $accountType='';
                $accntAmount=0;$accntAmountTotal=0;$cntChrg=0;
                foreach($arrCharge as $rDisp):
                    
                    $accountAmount=0;
                    $accnt=($rDisp['account_type']) ? $rDisp['account_type'] : 'unknown account type';
                    $accntAmount = $db->getValue('po p, view_po_payment vpp','sum(paid)',array('p.category_id'=>$rDisp['item_id']),'AND p.po_id=vpp.po_id AND paid>0 '.$yrPO.' ORDER BY p.po_date');
                    $accntAmount += $db->getValue('voucher_detail vd, voucher_particular vp, voucher v','sum(amount_issue)',array('vd.category_id'=>$rDisp['item_id']),'AND vd.vp_id=vp.vp_id AND vp.voucher_id=v.voucher_id'.$yrVO);
                    $accntAmountTotal+=$accntAmount;
                    $change=0;
                    if($accountType!=$accnt){
                        $accountType=$accnt;
                        $change=1;
                        echo '<tr><td colspan="5" height="50px"><div align="center" style="padding-top:20px;font-size:16px;"><strong>'.strtoupper($accountType).'</strong></div></td></tr>';
                        
                    }
                ?>
                    <tr>
                        <td><?php echo $rDisp['expense_type'];?></td>
                        <td><?php echo $rDisp['cost_type'];?></td>
                        <td><?php echo strtoupper($rDisp['account_type']); echo ($rDisp['account_type_group']) ? ' - <i>('.$rDisp['account_type_group'].')</i>' : '';?></td>
                        <td><?php echo $rDisp['name'];?></td>
                        <td><a id="view<?php echo $cntChrg?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onclick="showThis(this.id,'dash-ledgers-detail.php?cat=<?php echo functions::encode($rDisp['item_id']);?>&yr=<?php echo functions::encode($yr);?>&nme=<?php echo functions::encode($rDisp['name']);?>','Charges Detail','1')"><?php echo functions::formatMoney($accntAmount);?></a></td>
                    </tr>
                <?php 
                if(isset($arrCharge[$cntChrg+1])){
                    if($arrCharge[$cntChrg+1]['account_type'] != $rDisp['account_type']){
                ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Total</td>
                        <td><strong><?php echo functions::formatMoney($accntAmountTotal);?></strong></td>
                    </tr>
                <?php 
                    $accntAmount=0;
                    $accntAmountTotal=0;
                    $change=0;
                }}?>
                <?php $cntChrg++;endforeach;?>
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
<script>function selDt(PiEwgD){window.location="dash-proj-profit.php?pdt="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>