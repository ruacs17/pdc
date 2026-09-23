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
$start_month = (isset($_REQUEST['strt']) && !empty($_REQUEST['strt']) ) ? functions::decode($_REQUEST['strt']) : 0;
$monthBalance = (isset($_REQUEST['end']) && !empty($_REQUEST['end']) ) ? functions::decode($_REQUEST['end']) : 0;
$qPO = $db->select('po p, voucher_po_payment vpp, voucher_particular vp, voucher v','*',array(),'WHERE received=0 AND p.po_id=vpp.po_id AND vpp.vp_id=vp.vp_id AND vp.voucher_id=v.voucher_id AND (po_type="material" or po_type="fuel") AND left(v.vdate,7) BETWEEN "'.$start_month.'" AND "'.$monthBalance.'" AND v.approved is not null');
#echo $db->last_query;
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
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Advances To Supplier</h2>
        </div>
        <div class="box-content" align="center">

            <table class="table table-striped table-bordered" style="font-size:12px">
                <thead>
                    <tr>
                        <th width="10%">Date</th>
                        <th width="10%">Reference</th>
                        <th width="40%">Particular</th>
                        <th width="10%">Amout</th>
                        <th width="10%">Advance</th>
                        <th width="10%">Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $totalAmount=0;$totalPO=0;$totalPaid=0;$advances=0;
                $arrDetails=array();
                #echo $db->last_query;
                    #$qPO = $db->getValue('po p, voucher_po_payment vpp, voucher_particular vp, voucher v','sum(balance)',array(),'WHERE received=0 AND p.po_id=vpp.po_id AND left(p.po_date,7) BETWEEN "'.$start_month.'" AND "'.$monthBalance.'"');
                    #$qPO = $db->select('po p, voucher_po_payment vpp, voucher_particular vp, voucher v','*',array(),'WHERE received='.$received.' AND p.po_id=vpp.po_id AND vpp.vp_id=vp.vp_id AND vp.voucher_id=v.voucher_id AND left(v.vdate,7) BETWEEN "'.$start_month.'" AND "'.$monthBalance.'" AND v.approved is not null');
                    #$qPO = $db->select('po p, view_po_payment vpp','*',$arrSearchPO,'AND p.po_id=vpp.po_id AND paid>0 ORDER BY p.po_date');
                    while($rPO = $db->fetch_array($qPO)):

                        $qPODetails = $db->select('po_item','*',array('po_id'=>$rPO['po_id']));
                        $poItems='';$po_amount=0;
                        while($rPODetails = $db->fetch_array($qPODetails)):
                            $disc = ($rPODetails['discount']) ? ' -- discount '.$rPODetails['discount'].'%' : '';
                            $poItems .= $rPODetails['item'].' ( '.round($rPODetails['qty_delivered'],2).' '.$rPODetails['unit'].' x '.functions::formatMoney($rPODetails['cost']).' )'.$disc.'<br>';
                            $po_item_amount = $rPODetails['qty_delivered'] * $rPODetails['cost'];
                            #$disc_amount = ($rPODetails['discount']) ? $po_item_amount * ($rPODetails['discount'] / 100) : 0;
                            $disc_amount = 0;
                            $po_item_amount -= $disc_amount;
                            $po_amount += $po_item_amount;
                        endwhile;
                        $arrDetails[]=array('date'=>$rPO['vdate'],'reference'=>$rPO['po_no'],'particular'=>$poItems,'po_amount'=>$po_amount,'advance_amount'=>$rPO['amount'],'source_type'=>'P.O.');
                    endwhile;
                    // $qVO = $db->select('voucher_detail vd, voucher_particular vp, voucher v','*',$arrSearchVoucher,'AND vd.vp_id=vp.vp_id AND vp.voucher_id=v.voucher_id AND claimed=1');
                    // while($rVO = $db->fetch_array($qVO)):
                    //     $arrDetails[]=array('date'=>$rVO['vdate'],'reference'=>$rVO['voucher_no'],'account_title'=>$rVO['item'],'description'=>$rVO['vp_title'],'debit'=>$rVO['amount_issue'],'source_type'=>'Voucher');
                    // endwhile;


                    if(count($arrDetails))
                        functions::sortMultiArray($arrDetails,$orderBy="date",$sort_AscDesc="ASC");
                    foreach($arrDetails as $details):
                        $totalAmount+=$details['advance_amount'];
                        $totalPO+=$details['po_amount'];
                        $totalPaid+=$details['advance_amount'];
                ?>
                    <tr>
                        <td><?php echo functions::datearr($details['date']);?></td>
                        <td><?php echo $details['reference']; #echo '&nbsp;&nbsp;<i>('.$details['source_type'].')</i>'?></td>
                        <td><?php echo $details['particular'];?></td>
                        <td><?php echo functions::formatMoney($details['po_amount']);?></td>
                        <td><?php echo functions::formatMoney($details['advance_amount']);?></td>
                        <td><?php echo functions::formatMoney($totalPaid);?></td>
                    </tr>
                <?php endforeach;?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo functions::formatMoney($totalPO);?></td>
                        <td><?php echo functions::formatMoney($totalPaid);?></td>
                        <td></td>
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
<script>function selDt(PiEwgD){window.location="dash-proj-profit.php?pdt="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>