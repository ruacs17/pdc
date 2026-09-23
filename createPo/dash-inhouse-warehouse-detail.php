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
$payee = (isset($_REQUEST['prjID']) && !empty($_REQUEST['prjID']) ) ? functions::decode($_REQUEST['prjID']) : 0;
$is_paid = (isset($_REQUEST['paidStat']) && !empty($_REQUEST['paidStat']) ) ? trim($_REQUEST['paidStat']) : 0;
$qYear='';

$selMonFrom = ( isset($_SESSION['ih_mon_from']) ) ? $_SESSION['ih_mon_from']: date('m');
$selDayFrom = ( isset($_SESSION['ih_day_from']) ) ? $_SESSION['ih_day_from']: date('d');
$selYrFrom = ( isset($_SESSION['ih_yr_from']) ) ? $_SESSION['ih_yr_from']: date('Y');

$selMonTo = ( isset($_SESSION['ih_mon_to']) ) ? $_SESSION['ih_mon_to']: date('m');
$selDayTo = ( isset($_SESSION['ih_day_to']) ) ? $_SESSION['ih_day_to']: date('d');
$selYrTo = ( isset($_SESSION['ih_yr_to']) ) ? $_SESSION['ih_yr_to']: date('Y');

$qYear='';
if( $selMonFrom && $selDayFrom && $selYrFrom && $selMonTo && $selDayTo && $selYrTo)
    $qYear =' AND (im_date BETWEEN "'.$selYrFrom.'-'.$selMonFrom.'-'.$selDayFrom.'" AND "'.$selYrTo.'-'.$selMonTo.'-'.$selDayTo.'")';
else if($selMonFrom && $selYrFrom && $selMonTo && $selYrTo)
    $qYear =' AND ( LEFT(im_date,7) >= "'.$selYrFrom.'-'.$selMonFrom.'" AND LEFT(im_date,7) <= "'.$selYrTo.'-'.$selMonTo.'")';
else if($selMonFrom && $selMonTo)
    $qYear =' AND (SUBSTRING(im_date,6,2)>="'.$selMonFrom.'" AND SUBSTRING(im_date,6,2) <= "'.$selMonTo.'")';
else if($selYrFrom && $selYrTo)
    $qYear =' AND (LEFT(im_date,4) >= "'.$selYrFrom.'" AND LEFT(im_date,4) <= "'.$selYrTo.'")';
else if($selMonFrom && $selYrFrom && $selDayFrom)
    $qYear =' AND im_date="'.$selYrFrom.'-'.$selMonFrom.'-'.$selDayFrom.'"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>In-House Warehouse Report Detail</title>
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
    <div class="span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>In-House Warehouse Stock</h2>
        </div>
        <div class="box-content">
            <div align="left"><br>
                <div>Project:
                    <strong>
                    <?php
                    $is_project=0;
                    $qProj = $db->select('project','*',array('proj_id'=>$payee));
                    if( $db->num_rows($qProj) ){
                        $is_project=1;
                        while($rProj = $db->fetch_array($qProj)):
                            echo strtoupper($rProj['proj_name']);
                            echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';
                        endwhile;
                    }
                    else
                        echo $db->getValue('inhouse_material','payee',array('payee'=>$payee));
                    ?>
                    </strong>
                </div>
                <div><br>
                <?php 
                    if($is_paid==1)
                        echo 'Payment Status: <strong>PAID</strong>';
                    elseif($is_paid==2)
                        echo 'Payment Status: <strong>UNPAID</strong>';
                ?>
                </div><br><br>
            </div>
            <table width="100%" border="0" align="center" class="table table-striped" style="font-size: 12px;">
                <tr>
                    <th width="20%" scope="col"><div align="left">Item</div></th>
                    <th width="7%" scope="col"><div align="left">Quantity</div></th>
                    <th width="8%" scope="col"><div align="left">Unit</div></th>
                    <th width="15%" scope="col"><div align="left">Brand</div></th>
                    <th width="8%" scope="col"><div align="left">Price</div></th>
                    <th width="9%" scope="col"><div align="left">Discount</div></th>
                    <th width="8%" scope="col"><div align="left">Amount</div></th>
                </tr>
                <?php
                $total_amount=0;$disc_amount=0;$amount=0;

                if($is_paid)
                    $qInhouse = ($is_project) ? $db->select('inhouse_material','im_id,im_date',array('proj_id'=>$payee,'is_paid'=>$is_paid),$qYear.'GROUP BY im_id ORDER BY im_date') : $db->select('inhouse_material','im_id,im_date',array('payee'=>$payee,'is_paid'=>$is_paid),$qYear.'GROUP BY im_id ORDER BY im_date');
                else
                    $qInhouse = ($is_project) ? $db->select('inhouse_material','im_id,im_date',array('proj_id'=>$payee),$qYear.'GROUP BY im_id ORDER BY im_date') : $db->select('inhouse_material','im_id,im_date',array('payee'=>$payee),$qYear.'GROUP BY im_id ORDER BY im_date');
                
                while($r_inhouse = $db->fetch_array($qInhouse)):
                    $im_id = $r_inhouse['im_id'];
                ?>
                <tr>
                    <td colspan="7"><strong><?php echo functions::datearr($r_inhouse['im_date'])?></strong></td>
                </tr>
                <?php
                $qPOI = $db->select('inhouse_material_item','*',array('im_id'=>$im_id));
                while($rPOI = $db->fetch_array($qPOI)):
                    $amount = $rPOI['cost'] * $rPOI['quantity'];
                    $disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
                    $amount = $amount - $disc_amount;
                    $total_amount += $amount;
                ?>
                <tr>
                    <td><?php echo $rPOI['item'];?></td>
                    <td><?php echo round($rPOI['quantity'],2);?></td>
                    <td><?php echo $rPOI['unit'];?></td>
                    <td><?php echo $rPOI['brand'];?></td>
                    <td><?php echo functions::formatMoney($rPOI['cost']);?></td>
                    <td><?php echo $rPOI['discount'];?>%</td>
                    <td><?php echo functions::formatMoney($amount);?></td>
                </tr>
                <?php endwhile;?>
                <tr>
                    <td colspan="7">&nbsp;</td>
                </tr>
                <?php
                 endwhile; # while($r_inhouse = $db->fetch_array($qInhouse)):
                ?>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td><div align="right"><strong>Total Amount</strong></div></td>
                    <td><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
                </tr>
            </table><p>&nbsp;</p>
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
<!-- end: JavaScript-->
</body>
</html>