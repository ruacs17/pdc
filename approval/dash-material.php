<?php
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
#$logs = new Logs();
#$logs->save('visit');

$itemArr = array();
$itemList=array();

$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;

$qPO = $db->query('SELECT item,unit,brand,count(*) as cnt FROM `po_item` GROUP by item,unit,brand');
while($rPO = $db->fetch_array($qPO)):
    $itemArr[] = array('item'=>$rPO['item'],'unit'=>$rPO['unit'],'brand'=>$rPO['brand'],'source'=>'po','count'=>$rPO['cnt']);
endwhile;
$qIM = $db->query('SELECT item,unit,brand,count(*)as cnt FROM `inhouse_material_item` GROUP by item,unit,brand');
while($rIM = $db->fetch_array($qIM)):
    $found=0;
    foreach($itemArr as $itemSingle):
        if( strtolower($itemSingle['item'])==strtolower($rIM['item']) && strtolower($itemSingle['unit'])==strtolower($rIM['unit']) && strtolower($itemSingle['brand'])==strtolower($rIM['brand']) )
            $found=1;

        if($found==1)
            break;
    endforeach;
    if($found==0){
        $itemArr[] = array('item'=>$rIM['item'],'unit'=>$rIM['unit'],'brand'=>$rIM['brand'],'source'=>'ims','count'=>$rIM['cnt']);
    }
endwhile;

if( count($itemArr) )
    functions::sortMultiArray($itemArr,'item',$ascDes='ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Item List</title>
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
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Report</h2>
        </div>
        <div class="box-content" align="center">
            <table width="80%" align="center" border="0" class="table table-bordered table-hover" style="font-size: 12px;">
                <tr>
                    <th width="3%">&nbsp;</th>
                    <th width="50%">Item</th>
                    <th width="20%">Unit</th>
                    <th width="20%">Brand</th>
                    <th width="10%">&nbsp;</th>
                </tr>
                <?php
                $count=0;
                foreach($itemArr as $item):
                    $count++;
                ?>
                <tr>
                    <td><?php echo number_format($count);?></td>
                    <td><a id="view<?php echo $count?>" class="thickbox" style="cursor: pointer;" title="Modify this Item" data-rel="tooltip" onclick="showThis(this.id,'dash-material-detail.php?item=<?php echo functions::encode($item['item']);?>&unit=<?php echo functions::encode($item['unit']);?>&brand=<?php echo functions::encode($item['brand']);?>','Material Detail','1')"><?php echo $item['item'];?></a></td>
                    <td><?php echo $item['unit'];?><?php echo ($item['count'] > 1) ? ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$item['count'] : '';?></td>
                    <td><?php echo $item['brand'];?></td>
                    <td><a id="edit<?php echo $count?>" class="btn btn-mini btn-warning thickbox" title="Modify this Item" data-rel="tooltip" onclick="showThis(this.id,'dash-material-edit.php?item=<?php echo functions::encode($item['item']);?>&unit=<?php echo functions::encode($item['unit']);?>&brand=<?php echo functions::encode($item['brand']);?>','Material Detail')"><i class="halflings-icon white pencil"></i></a></td>
                </tr>
                <?php endforeach;?>
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
<!-- end: JavaScript-->
</body>
</html>