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

$yr = 2017;#(isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? $db->clean(functions::decode($_REQUEST['yr'])) : 0;
$mn = 0;#(isset($_REQUEST['mn']) && !empty($_REQUEST['mn']) ) ? $db->clean(functions::decode($_REQUEST['mn'])) : 0;
$supplierID = (isset($_REQUEST['sup']) && !empty($_REQUEST['sup']) ) ? functions::decode($_REQUEST['sup']) : 0;
$proj_id = 4;#(isset($_REQUEST['proj']) && !empty($_REQUEST['proj']) ) ? functions::decode($_REQUEST['proj']) : 0;
  
$qYrMn='';
if($yr && $mn)
    $qYrMn = ' AND LEFT(p.po_date,7)="'.$yr.'-'.$mn.'"';
elseif($yr)
    $qYrMn = ' AND LEFT(p.po_date,4)="'.$yr.'"';

$q = $db->query('SELECT * FROM `po_item` poi, po p where p.po_id=poi.po_id AND p.proj_id="'.$db->clean($proj_id).'" AND p.vp_id IS NULL'.$qYrMn.' ORDER BY po_date');
#echo $db->last_query;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Non Voucher</title>
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
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Purchase Order Item Details</h2>
        </div>
        <div class="box-content">
            <div>Supplier: <strong><?php echo $db->getValue('supplier','name',array('supplierID'=>$supplierID));?></strong></div>
            <form class="form-horizontal" method="post">
                <table width="100%" border="0" align="center" class="table table-bordered">
                    <tr>
                        <th width="23%" scope="col"><div align="left">Item</div></th>
                        <th width="7%" scope="col"><div align="left">Qty Request</div></th>
                        <th width="7%" scope="col"><div align="left">Qty Delivered</div></th>
                        <th width="5%" scope="col"><div align="left">Unit</div></th>
                        <th width="10%" scope="col"><div align="left">Brand</div></th>
                        <th width="10%" scope="col"><div align="left">Price</div></th>
                        <th width="5%" scope="col"><div align="left">Discount</div></th>
                        <th width="10%" scope="col"><div align="left">Amount</div></th>
                    </tr>
                    <tr>
                        <td height="25"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td height="25"></td>
                        <td></td>
                        <td height="25"></td>
                        <td></td>
                    </tr>
                      <?php
                      $total_amount=0;
                      $amount=0;
                      while($rPOI = $db->fetch_array($q)):
                          $amount = $rPOI['cost'] * $rPOI['qty_delivered'];
                          $disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
                          $amount = $amount - $disc_amount;
                          $total_amount += $amount;
                          $bgColor='';
                          if($rPOI['quantity'] != $rPOI['qty_delivered'])
                              $bgColor = 'bgcolor="#f5ae00"';
                      ?>
                    <tr <?php echo $bgColor;?>>
                        <td><?php echo $rPOI['item'];?></td>
                        <td><?php echo $rPOI['quantity'];?></td>
                        <td><?php echo $rPOI['qty_delivered'];?></td>
                        <td><?php echo $rPOI['unit'];?></td>
                        <td><?php echo functions::datearr($rPOI['po_date']);?></td>
                        <td><?php echo functions::formatMoney($rPOI['cost']);?></td>
                        <td><?php echo $rPOI['discount'];?>%</td>
                        <td><?php echo functions::formatMoney($amount);?></td>
                    </tr>
                    <?php endwhile;?>
                    <tr>
                        <td></td>
                        <td>&nbsp;</td>
                        <td></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td colspan="2"><div align="right"><strong>Total Amount</strong></div></td>
                        <td><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
                    </tr>
                </table><p>&nbsp;</p>
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
<!-- end: JavaScript-->
</body>
</html>