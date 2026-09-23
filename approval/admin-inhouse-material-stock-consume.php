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
$count=0;$editTrue=0;$total_quantity = 0;
$mf_id = (isset($_REQUEST['mf_id']) && !empty($_REQUEST['mf_id']) ) ? $db->clean(functions::decode($_REQUEST['mf_id'])) : 0;
$itemEdit = (isset($_REQUEST['itemEdt']) && !empty($_REQUEST['itemEdt']) ) ? $db->clean(functions::decode($_REQUEST['itemEdt'])) : 0;
$itemDel=(isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ) ? $db->clean(functions::decode($_REQUEST['itemDel'])) : 0;
$qM = $db->select('material_reference','*',array('mf_id'=>$mf_id));
$rM = $db->fetch_array($qM);
$item = ($rM['item']) ? $rM['item'] : "";
$unit = ($rM['unit']) ? $rM['unit'] : "";
$brand = ($rM['brand']) ? $rM['brand'] : "";

$arrLocation = array();
$qLoc = $db->select('inhouse_material_storage','DISTINCT location',array('item'=>$item,'unit'=>$unit,'brand'=>$brand));
while($rLoc = $db->fetch_array($qLoc)):
    $arrLocation[] = $rLoc['location'];
endwhile;
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
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Inventory Item</h2>
        </div>
        <div class="box-content"><br>
            <table align="center" class="table" border="0">
                <tr bgcolor="#ece1e1">
                    <td width="25%">Item</td>
                    <td width="15%"><div align="center">Brand</div></td>
                    <td width="10%"><div align="center">Unit</div></td>
                    <?php foreach($arrLocation as $loc):?>
                    <td width="10%"><div align="center"><?php echo $loc?></div></td>
                    <?php endforeach;?>
                </tr>
                <tr bgcolor="#f8f8f8">
                    <td><strong><?php echo $rM['item']?></strong></td>
                    <td><div align="center"><strong><?php echo $rM['brand']?></strong></div></td>
                    <td><div align="center"><strong><?php echo $rM['unit'];?></strong></div></td>
                    <?php
                    $available=0;
                    foreach($arrLocation as $loc):
                        $qty = $db->getValue('inhouse_material_storage','quantity',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'location'=>$loc));
                        $consumed = $db->getValue('inhouse_material_item','sum(quantity)',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'location'=>$loc));
                        $available = $qty - $consumed;
                    ?>
                    <td><div align="center"><?php echo $available?></div></td>
                    <?php endforeach;?>
                </tr>
            </table><br><br>
            <table width="95%" border="0" align="center" id="abcd" name="abcd" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;" >
                <thead>
                    <tr>
                        <th width="20%" scope="col"><div align="left">Date</div></th>
                        <th width="65%" scope="col"><div align="left">Project</div></th>
                        <th width="10%" scope="col"><div align="left">Order</div></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $qItem = $db->select('inhouse_material_item imi, inhouse_material im','*',array('item'=>$item,'unit'=>$unit,'brand'=>$brand),'AND im.im_id=imi.im_id');
                    while($r = $db->fetch_array($qItem)):
                    ?>
                    <tr>
                        <td><?php echo functions::datearr($r['im_date'])?></td>
                        <td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$r['proj_id']))?></td>
                        <td><?php echo $r['quantity']?></td>
                    </tr>
                    <?php
                    endwhile;
                    ?>
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
<!-- end: JavaScript-->
</body>
</html>