<?php session_start();
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$mrp_date=date('Y-m-d');$price='';$supplier='';
$mf_id=(isset($_REQUEST['mf_id']) && !empty($_REQUEST['mf_id']) ) ? functions::decode($_REQUEST['mf_id']) : '';
$mrp_date=(isset($_REQUEST['dte']) && !empty($_REQUEST['dte']) ) ? functions::decode($_REQUEST['dte']) : date('Y-m-d');
$supplier=(isset($_REQUEST['supid']) && !empty($_REQUEST['supid']) ) ? functions::decode($_REQUEST['supid']) : '';
$itemEdit = $db->getValue('material_reference_price','mrp_id',array('mrp_date'=>$mrp_date,'supplierID'=>$supplier));

$itemEdit=(isset($_REQUEST['edtID']) && !empty($_REQUEST['edtID']) ) ? functions::decode($_REQUEST['edtID']) : $itemEdit;

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
    <!-- end: Favicon -->
<?php
if( isset($_POST['btnSave']) ){
    $mrp_date = ( isset($_POST['txPriceDate']) && !empty($_POST['txPriceDate']) ) ? $_POST['txPriceDate'] : NULL;
    $price = ( isset($_POST['txPrice']) && !empty($_POST['txPrice']) ) ? functions::moneyToDouble($_POST['txPrice']) : NULL;
    $supplier = ( isset($_POST['selSupplier']) && !empty($_POST['selSupplier']) ) ? functions::decode($_POST['selSupplier']) : NULL;

    if($mf_id && $mrp_date && $price && $supplier){

        if($itemEdit){
            $db->update('material_reference_price',array('supplierID'=>$supplier,'mrp_date'=>$mrp_date,'price'=>$price),array('mrp_id'=>$itemEdit));
        }
        else if( $mrp_id = $db->getValue('material_reference_price','mrp_id',array('supplierID'=>$supplier,'item'=>$item,'unit'=>$unit,'brand'=>$brand,'mrp_date'=>$mrp_date)) ){
            $db->update('material_reference_price',array('price'=>$price),array('supplierID'=>$supplier,'item'=>$item,'unit'=>$unit,'brand'=>$brand,'mrp_date'=>$mrp_date));
        }
        else{
            $itemEdit = $db->insert('material_reference_price',array('supplierID'=>$supplier,'item'=>$item,'unit'=>$unit,'brand'=>$brand,'mrp_date'=>$mrp_date,'price'=>$price));
        }
        functions::say('Price Monitoring Saved!');
        functions::sendTo($_SERVER['PHP_SELF'].'?mf_id='.functions::encode($mf_id).'&edtID='.functions::encode($itemEdit));
    }
    else{
        functions::say('Please fill up the form properly1');
    }
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Price Manage</h2>
        </div>
        <div class="box-content">
            <form method="post">
            <div align="left">
                <table width="40%" border="0">
                    <tr height="30px">
                        <td align="right" width="20%">Item / Product</td>
                        <td width="2%">&nbsp;</td>
                        <td align="left"><strong><?php echo $item;?></strong></td>
                    </tr>
                    <tr height="30px">
                        <td align="right">Unit</td>
                        <td>&nbsp;</td>
                        <td align="left"><strong><?php echo $unit;?></strong></td>
                    </tr>
                    <tr height="30px">
                        <td align="right">Brand</td>
                        <td>&nbsp;</td>
                        <td align="left"><strong><?php echo $brand;?></strong></td>
                    </tr>
                </table>
            </div>
                
            <div align="center">

                <table width="70%" border="0">
                    <tr>
                        <th width="20%" height="40">Date</th>
                        <th width="45%">Supplier</th>
                        <th width="25%">Price</th>
                        <th width="5%"></th>
                    </tr>
                    <tr>
                        <td style="padding-top: 20px;">
                            <div align="center">
                                <a href="javascript:NewCssCal('txPriceDate')">
                                    <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                                </a>
                                <input name="txPriceDate" type="text" class="span6 mytextbox" id="txPriceDate" value="<?php echo $mrp_date?>" style="width: 90px;" readonly>
                            </div>
                        </td>
                        <td align="center">
                            <div align="left" style="padding-top: 12px;">
                                <select name="selSupplier" id="selSupplier" data-rel="chosen" style="width:430px;" required>
                                    <option value="">-- Select Supplier --</option>
                                    <?php
                                        $qItmD = $db->select('po p, supplier s','p.supplierID, name, count(*)',array(),'WHERE s.supplierID=p.supplierID GROUP BY p.supplierID ORDER BY s.name');
                                        while($rItmD = $db->fetch_array($qItmD)):
                                    ?>
                                    <option value="<?php echo functions::encode($rItmD['supplierID'])?>" <?php if($rItmD['supplierID']===$supplier)echo 'selected="selected"';?>><?php echo ucwords(strtolower($rItmD['name']));?></option>
                                    <?php endwhile;?>
                                </select>
                            </div>
                        </td>
                        <td style="padding-top: 20px;">
                            <div align="center">
                                <input type="text" name="txPrice" id="txPrice" class="span6" value="<?php echo $price;?>" onkeyup="FormatCurrency(this);" required/>
                            </div>
                        </td>
                        <td style="padding-top: 10px;"><div align="center"><input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary"></div></td>
                    </tr>
                </table>
            </div><br><br>
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