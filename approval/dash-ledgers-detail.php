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

$acntName = (isset($_REQUEST['nme']) && !empty($_REQUEST['nme']) ) ? functions::decode($_REQUEST['nme']) : '';
$cat = (isset($_REQUEST['cat']) && !empty($_REQUEST['cat']) ) ? functions::decode($_REQUEST['cat']) : 0;
$yr = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? functions::decode($_REQUEST['yr']) : 0;
if($yr){
    $yrSearch = " AND left(vd_date,4)='".$db->clean($yr)."'";  
    $yrSearchPO = " AND left(po_date,4)='".$db->clean($yr)."'";
    $yrPO = " AND left(p.po_date,4)='".$yr."'";
}
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
            <table class="table table-striped table-bordered" style="font-size:12px">
                <thead>
                    <tr>
                        <th width="10%">Date</th>
                        <th width="10%">Reference</th>
                        <th width="10%">Account Title</th>
                        <th width="10%">Description</th>
                        <th width="10%">Debit</th>
                        <th width="10%">Credit</th>
                        <th width="10%">Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $totalAmount=0;
                $arrDetails=array();
                    $qPO = $db->select('po p, view_po_payment vpp','*',array('p.category_id'=>$cat),'AND p.po_id=vpp.po_id AND paid>0 '.$yrPO.' ORDER BY p.po_date');
                    while($rPO = $db->fetch_array($qPO)):
                        $arrDetails[]=array('date'=>$rPO['po_date'],'reference'=>$rPO['po_no'],'account_title'=>$acntName,'description'=>'','debit'=>$rPO['paid'],'source_type'=>'P.O.');
                    endwhile;
                    $qVO = $db->select('voucher_detail vd, voucher_particular vp, voucher v','*',array('vd.category_id'=>$cat),'AND vd.vp_id=vp.vp_id AND vp.voucher_id=v.voucher_id'.$yrVO);
                    while($rVO = $db->fetch_array($qVO)):
                        $arrDetails[]=array('date'=>$rVO['vd_date'],'reference'=>$rVO['voucher_no'],'account_title'=>$rVO['item'],'description'=>$rVO['vp_title'],'debit'=>$rVO['amount_issue'],'source_type'=>'Voucher');
                    endwhile;
                    if(count($arrDetails))
                        functions::sortMultiArray($arrDetails,$orderBy="date",$sort_AscDesc="ASC");
                    foreach($arrDetails as $details):
                        $totalAmount+=$details['debit'];
                ?>
                    <tr>
                        <td><?php echo functions::datearr($details['date']);?></td>
                        <td><?php echo $details['reference'];?></td>
                        <td><?php echo $details['account_title'];?></td>
                        <td><?php echo $details['description'];?></td>
                        <td><?php echo functions::formatMoney($details['debit']);?></td>
                        <td></td>
                        <td><?php echo functions::formatMoney($totalAmount);?></td>
                    </tr>
                <?php endforeach;?>
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