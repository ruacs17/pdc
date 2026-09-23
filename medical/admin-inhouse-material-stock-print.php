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

$loc_id= isset($_SESSION['stock_location']) ? $_SESSION['stock_location'] : '';
$item= isset($_SESSION['stock_item']) ? $_SESSION['stock_item'] : '';
if($item){
    $q = 'SELECT item,unit,brand FROM inhouse_material_storage WHERE item LIKE "%'.$db->clean($item).'%" OR unit LIKE "%'.$db->clean($item).'%" OR brand LIKE "%'.$db->clean($item).'%" AND item_type="medical" GROUP BY item,unit,brand';
}
else{
    $q = 'SELECT item,unit,brand FROM inhouse_material_storage WHERE item_type="medical" GROUP BY item,unit,brand';
}
$q = isset($_SESSION['im_query']) ? $_SESSION['im_query'] : '';

$arrLocation = array();
if($loc_id)
    $qLoc = $db->select('inhouse_material_storage_location','location',array('imsl_id'=>$loc_id));
else
    $qLoc = $db->select('inhouse_material_storage_location','location',array());
while($rLoc = $db->fetch_array($qLoc)):
$arrLocation[] = $rLoc['location'];
endwhile;

$qList = $db->query($q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>Stock Print</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
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
    <style type="text/css">.padParLeft{padding-left:10px;}.padAmLeft{padding-left:60px;}</style>
    <script>window.print();</script>
</head>
<body bgcolor="#FFFFFF">
<div id="spinner"></div>
<!-- body content: start here-->
<table  width="700" border="0" align="center">
    <thead>
        <tr>
            <td>
                <?php
                require_once('../class/print_header.php');
                print_header('Item Stock Report as of '.date("F d, Y"));
                ?><br>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <table class="table table-bordered table-hover" style="font-size:12px">
                    <thead>
                        <tr>
                            <th width="35%">Item</th>
                            <th width="8%">Unit</th>
                            <th width="10%">Brand</th>
                            <?php foreach($arrLocation as $loc):?>
                            <th width="10%"><div align="center"><?php echo $loc?></div></th>
                            <?php endforeach;?>
                            <th width="10%"><div align="center">Total On-Stock</div></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $totalAmount=0;$count=0;
                    while($rList = $db->fetch_array($qList)):
                        $count++;
                        $mf_id = $db->getValue('material_reference','mf_id',array('item'=>$rList['item'],'unit'=>$rList['unit'],'brand'=>$rList['brand']));
                        $least_required = $db->getValue('material_reference','least_required',array('mf_id'=>$mf_id));
                    ?>
                        <tr>
                            <td><?php echo $rList['item']?></td>
                            <td><?php echo $rList['unit']?></td>
                            <td><?php echo $rList['brand']?></td>
                            <?php
                            $available=0; $totalAvailable=0;
                            foreach($arrLocation as $loc):
                                $consumed = $db->getValue('medicine_distribution_detail','sum(qty)',array('item'=>$rList['item'],'unit'=>$rList['unit'],'brand'=>$rList['brand'],'location'=>$loc));
                                $qty = $db->getValue('inhouse_material_storage','sum(quantity)',array('item'=>$rList['item'],'unit'=>$rList['unit'],'brand'=>$rList['brand'],'location'=>$loc));
                                $available = $qty - $consumed;
                                $totalAvailable += $available;
                                $available_disp=0; $totalAvailable_disp=0;
                                if($available)
                                    $available_disp = (functions::isfloat($available)) ? functions::formatMoney($available) : number_format($available);
                                if($totalAvailable)
                                    $totalAvailable_disp = (functions::isfloat($totalAvailable)) ? functions::formatMoney($totalAvailable) : number_format($totalAvailable);

                                $bgColor =  ($least_required > $available) ?  'bgcolor="#fcb77b"' : '';
                            ?>
                            <td><div align="center"><?php echo $available_disp?></div></td>
                            <?php endforeach;?>
                            <td><div align="center"><?php echo $totalAvailable_disp;?></div></td>
                        </tr>
                    <?php endwhile;?>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>