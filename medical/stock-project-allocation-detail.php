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
$count=0;$editTrue=0;$total_quantity = 0;$imsl_id=0;$quantity=0;
$mf_id = (isset($_REQUEST['mf_id']) && !empty($_REQUEST['mf_id']) ) ? $db->clean(functions::decode($_REQUEST['mf_id'])) : 0;
$proj_id = (isset($_REQUEST['proj_id']) && !empty($_REQUEST['proj_id']) ) ? $db->clean(functions::decode($_REQUEST['proj_id'])) : '';
$qM = $db->select('material_reference','*',array('mf_id'=>$mf_id));
$rM = $db->fetch_array($qM);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Inventory Item</title>
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
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Distribution History</h2>
        </div>
        <div class="box-content">
            <div align="left">
                <div>Item: <strong><?php echo $rM['item'] ?></strong></div>
                <div>Unit: <strong><?php echo ($rM['unit']) ? $rM['unit'] : '---'; ?></strong></div>
                <div>Brand: <strong><?php echo ($rM['brand']) ? $rM['brand'] : '---'; ?></strong></div>
                <div>Project: <strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$proj_id)); ?></strong></div>
            </div>
            <div align="center"><br>
            <div style="width:500px">
            <table width="50%" border="0" align="center" id="abcd" name="abcd" cellpadding="0" cellspacing="0" class="table table-hover table-bordered" style="font-size:12px;" >
                <thead>
                    <tr>
                        <th width="50%" scope="col" height="40"><div align="left">Date</div></th>
                        <th width="20%" scope="col"><div align="center">Distributed</div></th>
                        <th width="20%" scope="col"><div align="center">Unit</div></th>
                    </tr>
                </thead>   
                <tbody>
                <?php
                $count=0;$onhand=0;$totalAllocation=0;

                $qMD = $db->select('medicine_distribution md,medicine_distribution_detail mdd','md_date,item,unit,brand,sum(qty) as qy',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'proj_id'=>$proj_id),'AND md.md_id=mdd.md_id GROUP BY md_date,item,unit,brand ORDER BY md_date');
                #echo $db->last_query;
                while($rMD = $db->fetch_array($qMD)):
                    $date = $rMD['md_date'];
                    $allocation = is_numeric($rMD['qy']) ? $rMD['qy'] : 0;
                    $totalAllocation += $allocation;
                ?>
                    <tr>
                        <td height="20"><?php echo functions::datearr($date);?></td>
                        <td><div align="center"><?php echo $allocation;?></div></td>
                        <td><div align="center"><?php echo $rM['unit'];?></div></td>
                    </tr>
                <?php endwhile;?>
                <tbody>
                    <tr>
                        <td><div align="right"><strong>Total Allocation Quantity</strong></div></td>
                        <td><div align="center"><strong><?php echo $totalAllocation?></strong></div></td>
                        <td>&nbsp;</td>
                    </tr>
            </table>
            </div>
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
<script>
function delt(){
    if(confirm('Do you want to remove this Inventory?'))
        return true;
    else
        return false; 
}
$(document).ready(function(){
    var res = false;
    $('#btnAdd,#btnSave').click(function(){
        $('#msgtxQty').html("");
        $('#msgBdate').html("");
        $('#msgLoc').html("");

        if( $('#txQty').val()=="" ){
            $('#msgtxQty').html("Required!");
            $('#txQty').focus();
            res=false;
        }
        else if( $('#bdMon').val()=="" || $('#bdDay').val()=="" || $('#bdYear').val()==""){
            $('#msgBdate').html("Date Required!");
            res=false;
        }
        else if( $('#selLoc').val()=="" ){
            $('#msgLoc').html("Required!");
            $('#selLoc').focus();
            res=false;
        }
        else
            res=true;

        return res;
    });
});
</script>
<!-- end: JavaScript-->
</body>
</html>