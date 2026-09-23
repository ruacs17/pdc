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

$startrow=0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;

$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$po_idDel = (isset($_REQUEST['po_idDel']) && !empty($_REQUEST['po_idDel']) ) ? functions::decode($_REQUEST['po_idDel']) : 0;
$arr = array();
$day='';
$mon='';
$txProj = '';
$txPayee = '';
$selCat = '';
$category='';
$supplierID='';
$txbMon = '';$txbYear = ''; 
$txbMonTo = '';$txbYearTo = '';
$equip_id='';
$txSearchPO='';
$totalAmount=0;
$unpaid=0;
unset($_SESSION['po_arr_proj'],$_SESSION['RefID']);

if( isset($_POST['btnViewAll']) ){
    unset($_SESSION['pfCharge'],$_SESSION['pfCat'],$_SESSION['pfMon'],$_SESSION['pfYear'],$_SESSION['pfDay']);
}
if( isset($_POST['btnSearch']) ){
    $_SESSION['pfCharge'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $db->clean($_POST['selProj']) : '';
    $_SESSION['pfCat'] = ( isset($_POST['selCat']) && !empty($_POST['selCat']) ) ? $db->clean(functions::decode($_POST['selCat'])) : '';
    $_SESSION['pfSup'] = ( isset($_POST['selSupplier']) && !empty($_POST['selSupplier']) ) ? $db->clean(functions::decode($_POST['selSupplier'])) : '';
    $_SESSION['pfMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $db->clean($_POST['bdMon']) : '';
    $_SESSION['pfYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $db->clean($_POST['bdYear']) : '';
    $_SESSION['pfDay'] = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $db->clean($_POST['bdDay']) : '';
    $_SESSION['pfMonTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $db->clean($_POST['bdMonTo']) : '';
    $_SESSION['pfYearTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $db->clean($_POST['bdYearTo']) : '';
    $_SESSION['pfDayTo'] = ( isset($_POST['bdDayTo']) && !empty($_POST['bdDayTo']) ) ? $db->clean($_POST['bdDayTo']) : '';
    functions::sendTo($_SERVER['PHP_SELF']);
}

$arrChargeList=array();
$qProj = $db->query('SELECT * FROM project WHERE proj_id IN (SELECT DISTINCT proj_id FROM voucher_detail) ORDER BY proj_name');
while($rProj = $db->fetch_array($qProj)):
    if($rProj['proj_id'])
        $arrChargeList[$rProj['proj_name']]=$rProj['proj_id'];
endwhile;
/*
$qPayee = $db->query('SELECT DISTINCT payee FROM po_fuel ORDER BY payee');
while($rPayee = $db->fetch_array($qPayee)):
    if($rPayee['payee'])
    $arrChargeList[strtoupper($rPayee['payee'])]=$rPayee['payee'];
endwhile;*/
ksort($arrChargeList);

$where = ''; 
        
    $supplier = ( isset($_SESSION['pfSup']) && !empty($_SESSION['pfSup']) ) ? $_SESSION['pfSup'] : '';
    $category = ( isset($_SESSION['pfCat']) && !empty($_SESSION['pfCat']) ) ? $_SESSION['pfCat'] : '';
    $charge = ( isset($_SESSION['pfCharge']) && !empty($_SESSION['pfCharge']) ) ? $_SESSION['pfCharge'] : '';
    $txbMon = ( isset($_SESSION['pfMon']) ) ? $_SESSION['pfMon'] : date('m');
    $txbYear = ( isset($_SESSION['pfYear']) ) ? $_SESSION['pfYear'] : date('Y');
    $txbDay = ( isset($_SESSION['pfDay']) ) ? $_SESSION['pfDay'] : date('d');
    $txbMonTo = ( isset($_SESSION['pfMonTo']) ) ? $_SESSION['pfMonTo'] : date('m');
    $txbYearTo = ( isset($_SESSION['pfYearTo']) ) ? $_SESSION['pfYearTo'] : date('Y');
    $txbDayTo = ( isset($_SESSION['pfDayTo']) ) ? $_SESSION['pfDayTo'] : date('d');

    $dateStart = $txbYear.'-'.$txbMon.'-'.$txbDay;
    $dateEnd = $txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo;

    if($charge){
        if( $db->getValue('project','count(proj_id)',array('proj_id'=>$charge)) )
            $where .= ' AND vd.proj_id="'.$charge.'"';
    }
    if($category){
        $where .= ' AND vd.category_id="'.$category.'"';
    }
    if($supplier){
        $where .= ' AND v.supplierID="'.$supplier.'"';
    }
    if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
        $where .=' AND (vd_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
    else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
        $where .=' AND ( LEFT(vd_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(vd_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
    else if($txbMon && $txbMonTo)
        $where .=' AND (SUBSTRING(vd_date,6,2)>="'.$txbMon.'" AND SUBSTRING(vd_date,6,2) <= "'.$txbMonTo.'")';
    else if($txbYear && $txbYearTo)
        $where .=' AND (LEFT(vd_date,4) >= "'.$txbYear.'" AND LEFT(vd_date,4) <= "'.$txbYear.'")';
    else if($txbMon && $txbYear && $txbDay)
        $where .=' AND vd_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';
    
    $qVO = $db->query('SELECT v.voucher_id,vdate as "dte", category_id, supplierID, proj_id, sum(amount_issue) as "amnt" FROM voucher v, voucher_particular vp, voucher_detail vd WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id '.$where.' GROUP BY v.voucher_id, proj_id, supplierID,category_id ORDER BY vdate');
    #echo $db->last_query;
    #die();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Charge Category Report</title>
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
    <!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Category Report </h2>
        </div>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li><a href="charge_category_report_voucher_po.php">PO / Voucher Fuel</a></li>
                <li class="active"><a href="#" style="opacity:.9">Voucher</a></li>
                <li><a href="charge_category_report.php">PO</a></li>
                <li><a href="dash-expenses.php">Charges Category Report</a></li>
            </ul>
            <form class="form-horizontal" method="post">
                <table width="100%" cellspacing="4" cellpadding="6" border='0' align="left" style="display:none;">
                    <tr>
                        <td width="50%"><div align="right"><a id="whprint" class="btn btn-info" href="po_fuel_report_print.php"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div><br><br></td>
                    </tr>
                </table>
                <div align="center"><h3>-- Voucher --</h3></div>
                <table border="0">
                    <tr>
                        <td width="20%">
                            <div align="center"> 
                                <div align="center"><strong>FROM</strong></div>
                                <select name="bdYear" id="bdYear" style="width:80px;">
                                    <option value="">All Year</option>
                                    <?php
                                        $qYr = $db->select('voucher_detail','DISTINCT LEFT(vd_date,4) as yr',array(),'ORDER BY vd_date DESC');
                                        while($rYr = $db->fetch_array($qYr)):
                                    ?>
                                    <option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
                                    <?php endwhile;?>
                                </select>
                                <select name="bdMon" id="bdMon" style="width:85px;">
                                    <option value="">All Month</option>
                                    <option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
                                    <option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
                                    <option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
                                    <option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
                                    <option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
                                    <option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
                                    <option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
                                    <option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
                                    <option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
                                    <option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
                                    <option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
                                    <option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
                                </select>
                                <select name="bdDay" id="bdDay" style="width:60px;">
                                    <option value="">Day</option>
                                    <?php for($i=1;$i<=31;$i++):?>
                                    <option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDay)echo 'selected="selected"';?>><?php echo $i;?></option>
                                    <?php endfor;?>
                                </select>
                            </div><br>
                            <div align="center">
                                <div align="center"><strong>TO</strong></div>
                                <select name="bdYearTo" id="bdYearTo" style="width:80px;">
                                    <option value="">All Year</option>
                                    <?php
                                        $qYr = $db->select('voucher_detail','DISTINCT LEFT(vd_date,4) as yr',array(),'ORDER BY vd_date DESC');
                                        while($rYr = $db->fetch_array($qYr)):
                                    ?>
                                    <option value="<?php echo $rYr['yr']?>" <?php if($txbYearTo==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
                                    <?php endwhile;?>
                                </select>
                                <select name="bdMonTo" id="bdMonTo" style="width:85px;">
                                    <option value="">All Month</option>
                                    <option value="01" <?php if($txbMonTo=='01')echo 'selected="selected"';?>>Jan</option>
                                    <option value="02" <?php if($txbMonTo=='02')echo 'selected="selected"';?>>Feb</option>
                                    <option value="03" <?php if($txbMonTo=='03')echo 'selected="selected"';?>>Mar</option>
                                    <option value="04" <?php if($txbMonTo=='04')echo 'selected="selected"';?>>Apr</option>
                                    <option value="05" <?php if($txbMonTo=='05')echo 'selected="selected"';?>>May</option>
                                    <option value="06" <?php if($txbMonTo=='06')echo 'selected="selected"';?>>Jun</option>
                                    <option value="07" <?php if($txbMonTo=='07')echo 'selected="selected"';?>>Jul</option>
                                    <option value="08" <?php if($txbMonTo=='08')echo 'selected="selected"';?>>Aug</option>
                                    <option value="09" <?php if($txbMonTo=='09')echo 'selected="selected"';?>>Sep</option>
                                    <option value="10" <?php if($txbMonTo=='10')echo 'selected="selected"';?>>Oct</option>
                                    <option value="11" <?php if($txbMonTo=='11')echo 'selected="selected"';?>>Nov</option>
                                    <option value="12" <?php if($txbMonTo=='12')echo 'selected="selected"';?>>Dec</option>
                                </select>
                                <select name="bdDayTo" id="bdDayTo" style="width:60px;">
                                    <option value="">Day</option>
                                    <?php for($i=1;$i<=31;$i++):?>
                                    <option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDayTo)echo 'selected="selected"';?>><?php echo $i;?></option>
                                    <?php endfor;?>
                                </select>
                            </div>
                        </td>
                        <td width="15%">
                            <div align="left">
                                <select name="selCat" id="selCat" data-rel="chosen" style="width:320px;">
                                    <option value="">-- All Category --</option>
                                    <?php
                                        $qItmD = $db->select('item_deduction','*',array(),'ORDER BY name');
                                        while($rItmD = $db->fetch_array($qItmD)):
                                    ?>
                                    <option value="<?php echo functions::encode($rItmD['item_id'])?>" <?php if($rItmD['item_id']===$category)echo 'selected="selected"';?>><?php echo ucwords(strtolower($rItmD['name']));?></option>
                                    <?php endwhile;?>
                                </select>
                            </div>
                        </td>
                        <td width="15%">
                            <div align="left">
                                <select name="selSupplier" id="selSupplier" data-rel="chosen" style="width:290px;">
                                    <option value="">-- All Supplier --</option>
                                    <?php
                                        $qItmD = $db->select('po p, supplier s','p.supplierID, name, count(*)',array(),'WHERE s.supplierID=p.supplierID GROUP BY p.supplierID ORDER BY s.name');
                                        while($rItmD = $db->fetch_array($qItmD)):
                                    ?>
                                    <option value="<?php echo functions::encode($rItmD['supplierID'])?>" <?php if($rItmD['supplierID']===$supplier)echo 'selected="selected"';?>><?php echo ucwords(strtolower($rItmD['name']));?></option>
                                    <?php endwhile;?>
                                </select>
                            </div>
                        </td>
                        <td width="20%">
                            <div align="left">
                                <select name="selProj" id="selProj" data-rel="chosen" style="width:330px;">
                                    <option value="">--All Charge To--</option>
                                    <?php foreach($arrChargeList as $name => $projid): ?>
                                    <option value="<?php echo $projid?>" <?php if($charge===$projid)echo 'selected="selected"';?>><?php echo ucwords(strtolower($name));?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                        </td>
                        <td width="5%">
                            <div align="center">
                                <input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary">
                            </div>
                        </td>
                    </tr>
                    <tr><td colspan="5"><hr width="100%"></td></tr>
                </table>
                    <table class="table table-bordered table-hover" style="font-size:12px">
                        <thead>
                            <tr>
                                <th width="9%">Date</th>
                                <th width="15%">Supplier</th>
                                <th width="21%">Project</th>
                                <th width="10%">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $totalAmount=0;$rID=0;
                        while($r = $db->fetch_array($qVO)):
                            $rID = $r['voucher_id'];
                            $viewOnlyPage = 'charge_category_detail_voucher.php';
                            $amount = $r['amnt'];
                            $totalAmount += $amount;
                            $bgColor='';
                        ?>
                            <tr <?php echo $bgColor;?>>
                                <td><?php echo functions::datearr($r['dte']);?></td>
                                <td><?php echo $db->getValue('supplier','name',array('supplierID'=>$r['supplierID']));?></td>
                                <td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$r['proj_id']));?></td>
                                <td>
                                    <a id="costdetail<?php echo $rID?>" class="label label-info thickbox" title="View Voucher Details" data-rel="tooltip" onclick="showThis(this.id,'<?php echo $viewOnlyPage?>?vid=<?php echo functions::encode($rID);?>&prj=<?php echo functions::encode($r['proj_id']);?>&cat=<?php echo functions::encode($r['category_id']);?>&dtStrt=<?php echo functions::encode($dateStart);?>&dtEnd=<?php echo functions::encode($dateEnd);?>','Voucher Details','1')">                                           
                                    <?php echo functions::formatMoney($amount);?>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile;?>
                            <tr>
                                <td colspan="3"><div align="right"><strong>Total Amount</strong></div></td>
                                <td><strong><?php echo functions::formatMoney($totalAmount);?></strong></td>
                            </tr>
                        </tbody>
                    </table>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>

<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>