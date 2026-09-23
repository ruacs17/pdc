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
$mrp_date=date('Y-m-d');$price='';$supplier='';
$mf_id=(isset($_REQUEST['mf_id']) && !empty($_REQUEST['mf_id']) ) ? functions::decode($_REQUEST['mf_id']) : '';
$itemEdit=(isset($_REQUEST['edtID']) && !empty($_REQUEST['edtID']) ) ? functions::decode($_REQUEST['edtID']) : '';

$txbMon = '';$txbYear = ''; 
$txbMonTo = '';$txbYearTo = '';

$q = $db->select('material_reference','*',array('mf_id'=>$mf_id));
$r = $db->fetch_array($q);
$item = $r['item'];
$unit = $r['unit'];
$brand = $r['brand'];

if($itemEdit){
    $qItm = $db->select('material_reference_price','*',array('mrp_id'=>$itemEdit));
    $rItm = $db->fetch_array($qItm);
    $mrp_date = $rItm['mrp_date'];
    $supplier = $rItm['supplierID'];
    $price = $rItm['price'];
}
if( isset($_POST['btnSearch']) ){
    $_SESSION['pmSup'] = ( isset($_POST['selSupplier']) && !empty($_POST['selSupplier']) ) ? $db->clean(functions::decode($_POST['selSupplier'])) : '';
    $_SESSION['pmMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $db->clean($_POST['bdMon']) : '';
    $_SESSION['pmYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $db->clean($_POST['bdYear']) : '';
    $_SESSION['pmMonTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $db->clean($_POST['bdMonTo']) : '';
    $_SESSION['pmYearTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $db->clean($_POST['bdYearTo']) : '';
    functions::sendTo($_SERVER['PHP_SELF'].'?mf_id='.functions::encode($mf_id));
}

$supplier = ( isset($_SESSION['pmSup']) && !empty($_SESSION['pmSup']) ) ? $_SESSION['pmSup'] : '';
$txbMon = ( isset($_SESSION['pmMon']) ) ? $_SESSION['pmMon'] : date('m');
$txbYear = ( isset($_SESSION['pmYear']) ) ? $_SESSION['pmYear'] : date('Y');
$txbMonTo = ( isset($_SESSION['pmMonTo']) ) ? $_SESSION['pmMonTo'] : date('m');
$txbYearTo = ( isset($_SESSION['pmYearTo']) ) ? $_SESSION['pmYearTo'] : date('Y');
$where='';
if($supplier){
    $where .= ' AND supplierID="'.$supplier.'"';
}
if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
    $where .=' AND ( LEFT(mrp_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(mrp_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'") ';
else if($txbYear && $txbYearTo)
    $where .=' AND (LEFT(mrp_date,4) >= "'.$txbYear.'" AND LEFT(mrp_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear)
    $where .=' AND ( LEFT(mrp_date,7) >= "'.$txbYear.'-'.$txbMon.'") ';
else if($txbMonTo && $txbYearTo)
    $where .=' AND ( LEFT(mrp_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'") ';
$qItmLst = $db->select('material_reference_price','*',array('item'=>$item,'brand'=>$brand,'unit'=>$unit),$where.'ORDER BY mrp_date DESC');

#echo $db->last_query;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Price Monitor</title>
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
    <script src="../js/inputInt.js"></script>
    <script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
    <style>body{font-size: 14px;}</style>
    <!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Price History</h2>
        </div>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li><a href="dash-price-monitor-manage.php?mf_id=<?php echo functions::encode($mf_id);?>">Manage</a></li>
                <li class="active"><a href="dash-price-monitor-history.php?mf_id=<?php echo functions::encode($mf_id);?>" style="opacity:.9">History</a></li>
            </ul>
            <form method="post">
            <div align="left">
                <table width="70%" border="0">
                    <tr height="30px">
                        <td align="left" width="40%">Item / Product</td>
                        <td align="left" width="20%" align="left">Unit</td>
                        <td align="left" width="30%" align="left">Brand</td>
                    </tr>
                    <tr height="30px">
                        <td align="left"><strong><?php echo $item;?></strong></td>
                        <td align="left"><strong><?php echo $unit;?></strong></td>
                        <td align="left"><strong><?php echo $brand;?></strong></td>
                    </tr>
                </table>
            </div>
                
            <div align="center">

                <table width="65%" border="0">
                    <tr>
                        <th width="25%" height="40">&nbsp;</th>
                        <th width="40%">&nbsp;</th>
                        <th width="5%"></th>
                    </tr>
                    <tr>
                        <td>
                            <div align="center"> 
                                <div align="center"><strong>FROM</strong></div>
                                <select name="bdYear" id="bdYear" style="width:90px;">
                                    <option value="">All Year</option>
                                    <?php
                                        $qYr = $db->select('material_reference_price','DISTINCT LEFT(mrp_date,4) as yr',array(),'ORDER BY mrp_date DESC');
                                        while($rYr = $db->fetch_array($qYr)):
                                    ?>
                                    <option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
                                    <?php endwhile;?>
                                </select>
                                <select name="bdMon" id="bdMon" style="width:95px;">
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
                            </div><br>
                            <div align="center">
                                <div align="center"><strong>TO</strong></div>
                                <select name="bdYearTo" id="bdYearTo" style="width:90px;">
                                    <option value="">All Year</option>
                                    <?php
                                        $qYr = $db->select('material_reference_price','DISTINCT LEFT(mrp_date,4) as yr',array(),'ORDER BY mrp_date DESC');
                                        while($rYr = $db->fetch_array($qYr)):
                                    ?>
                                    <option value="<?php echo $rYr['yr']?>" <?php if($txbYearTo==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
                                    <?php endwhile;?>
                                </select>
                                <select name="bdMonTo" id="bdMonTo" style="width:95px;">
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
                            </div>
                        </td>
                        <td align="center">
                            <div align="left" style="padding-top: 12px;">
                                <select name="selSupplier" id="selSupplier" data-rel="chosen" style="width:430px;">
                                    <option value="">-- All Supplier --</option>
                                    <?php
                                        $qItmD = $db->select('material_reference_price mrp, supplier s','s.supplierID,s.name',array('item'=>$item,'brand'=>$brand,'unit'=>$unit),'AND mrp.supplierID=s.supplierID GROUP BY s.supplierID,s.name');
                                        #$qItmD = $db->select('po p, supplier s','p.supplierID, name, count(*)',array(),'WHERE s.supplierID=p.supplierID GROUP BY p.supplierID ORDER BY s.name');
                                        while($rItmD = $db->fetch_array($qItmD)):
                                    ?>
                                    <option value="<?php echo functions::encode($rItmD['supplierID'])?>" <?php if($rItmD['supplierID']===$supplier)echo 'selected="selected"';?>><?php echo ucwords(strtolower($rItmD['name']));?></option>
                                    <?php endwhile;?>
                                </select>
                            </div>
                        </td>
                        <td style="padding-top: 10px;"><div align="center"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary"></div></td>
                    </tr>
                </table>
            </div><br><br>
            <div align="center">
            <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-striped table-hover" style="font-size:12px;">
                <tr>
                    <th width="20%"><div align="center">Date</div></th>
                    <th width="50%">Supplier</th>
                    <th width="20%"><div align="center">Price</div></th>
                    <th width="5%">&nbsp;</th>
                </tr>
                <?php
                $countRow=0;
                
                while($rIL = $db->fetch_array($qItmLst)):
                    $countRow++;
                    $itemID = $rIL['mrp_id'];
                ?>
                <tr>
                    <td height="25px"><div align="center"><?php echo functions::datearr($rIL['mrp_date'])?></div></td>
                    <td><div align="left"><?php echo $db->getValue('supplier','name',array('supplierID'=>$rIL['supplierID']));?></div></td>
                    <td><div align="center"><?php echo functions::formatMoney($rIL['price'])?></div></td>
                    <td style="padding-top: 10px;"><div align="center"><a href="dash-price-monitor-manage.php?mf_id=<?php echo functions::encode($mf_id)?>&edtID=<?php echo functions::encode($itemID)?>" class="btn btn-mini btn-warning"><i class="halflings-icon white pencil"></i></a></div></td>
                </tr>
                <?php endwhile;?>
                <?php if($countRow==0){?>
                <tr height="40px"><td colspan="4"><div align="center">----- Nothing to Report ---</div></td></tr>
                <?php }?>
            </table>
            </div>
            <div style="font-size:14px;">
            <?php
            $high=0;$low=0;
            echo ( $high = $db->getValue('material_reference_price','price',array('item'=>$item,'brand'=>$brand,'unit'=>$unit),'ORDER BY mrp_date DESC') ) ? '<br><br><strong>Highest Price: '. functions::formatMoney($high) .'</strong>': '';
            echo ( $low = $db->getValue('material_reference_price','price',array('item'=>$item,'brand'=>$brand,'unit'=>$unit),'ORDER BY mrp_date ASC') ) ? '<br><br><strong>Lowest Price: '. functions::formatMoney($low) .'</strong>': '';
            ?>
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
<script>function selDt(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?itm="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>