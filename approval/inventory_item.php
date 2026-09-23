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
$count=0;
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? $db->clean(functions::decode($_REQUEST['pid'])) : 0;
$total_amount = 0;

$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=30;

if( isset($_POST['txSearch']) )
    $startrow=0;

$searchItem = ( isset($_REQUEST['txSearch']) && !empty($_REQUEST['txSearch']) ) ? trim($_REQUEST['txSearch']) : '';
$arrItem = array();

if($searchItem){
    $qItem = $db->query('SELECT t.item,sum(t.qty) as qnty,t.unit,t.brand FROM ((SELECT item,sum(quantity) as qty,unit,brand FROM po_item pi,po p WHERE p.po_id=pi.po_id AND proj_id="'.$db->clean($p_id).'" AND item LIKE "%'.$db->clean($searchItem).'%" GROUP BY item,unit,brand) UNION ALL (SELECT item,sum(quantity) as qty,unit,brand FROM inhouse_material im,inhouse_material_item imi WHERE im.im_id=imi.im_id AND proj_id="'.$db->clean($p_id).'" AND item LIKE "%'.$db->clean($searchItem).'%" GROUP BY item,unit,brand)) as t GROUP BY t.item,t.unit,t.brand ORDER BY t.item LIMIT '.$startrow.', '.$rowdisplay);
    $num_recordQ = $db->query('SELECT t.item,sum(t.qty) as qnty,t.unit,t.brand FROM ((SELECT item,sum(quantity) as qty,unit,brand FROM po_item pi,po p WHERE p.po_id=pi.po_id AND proj_id="'.$db->clean($p_id).'" AND item LIKE "%'.$db->clean($searchItem).'%" GROUP BY item,unit,brand) UNION ALL (SELECT item,sum(quantity) as qty,unit,brand FROM inhouse_material im,inhouse_material_item imi WHERE im.im_id=imi.im_id AND proj_id="'.$db->clean($p_id).'" AND item LIKE "%'.$db->clean($searchItem).'%" GROUP BY item,unit,brand)) as t GROUP BY t.item,t.unit,t.brand');
}
else{
    $qItem = $db->query('SELECT t.item,sum(t.qty) as qnty,t.unit,t.brand FROM ((SELECT item,sum(quantity) as qty,unit,brand FROM po_item pi,po p WHERE p.po_id=pi.po_id AND proj_id="'.$db->clean($p_id).'" GROUP BY item,unit,brand) UNION ALL (SELECT item,sum(quantity) as qty,unit,brand FROM inhouse_material im,inhouse_material_item imi WHERE im.im_id=imi.im_id AND proj_id="'.$db->clean($p_id).'" GROUP BY item,unit,brand)) as t GROUP BY t.item,t.unit,t.brand ORDER BY t.item LIMIT '.$startrow.', '.$rowdisplay);
    $num_recordQ = $db->query('SELECT t.item,sum(t.qty) as qnty,t.unit,t.brand FROM ((SELECT item,sum(quantity) as qty,unit,brand FROM po_item pi,po p WHERE p.po_id=pi.po_id AND proj_id="'.$db->clean($p_id).'" GROUP BY item,unit,brand) UNION ALL (SELECT item,sum(quantity) as qty,unit,brand FROM inhouse_material im,inhouse_material_item imi WHERE im.im_id=imi.im_id AND proj_id="'.$db->clean($p_id).'" GROUP BY item,unit,brand)) as t GROUP BY t.item,t.unit,t.brand ORDER BY t.item');
}
$num_record = $db->num_rows($num_recordQ);
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
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Inventory Item</h2>
        </div>
        <div class="box-content">
            <form method="post">
                <div>
                    <table class="table">
                        <tr>
                            <td><div align="right"><input type="text" name="txSearch" id="txSearch" value="<?php echo $searchItem?>"></div></td>
                            <td><input type="submit" class="btn btn-info btn-small" name="btnSearch" id="btnSearch" value="Search"></td>
                        </tr>
                    </table>
                </div>
            </form>
            <table width="95%" border="0" align="center" id="abcd" name="abcd" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;" >
                <thead>
                    <tr>
                        <th width="45%" scope="col"><div align="left">Item</div></th>
                        <th width="15%" scope="col"><div align="left">Brand</div></th>
                        <th width="10%" scope="col"><div align="left">Estimate</div></th>
                        <th width="10%" scope="col"><div align="left">Delivered</div></th>
                        <th width="10%" scope="col"><div align="left">Available</div></th>
                        <th width="10%" scope="col"><div align="left">Unit</div></th>
                    </tr>
                    <tr>
                        <td colspan="6" height="25"></td>
                    </tr>
                </thead>   
                <tbody>
                  <?php
                  $count=0;
                  while($rItem = $db->fetch_array($qItem)):
                      $count++;
                      $sharedCount=0;
                      $qtyDelivered = ( functions::isfloat($rItem['qnty']) ) ? number_format($rItem['qnty'],2) : $rItem['qnty'];
                      $qtyConsumed = $db->getValue('project_warehouse','sum(quantity)',array('proj_id'=>$p_id,'item'=>$rItem['item'],'unit'=>$rItem['unit'],'brand'=>$rItem['brand']));
                      $qtyConsumed = ( functions::isfloat($qtyConsumed) ) ? number_format($qtyConsumed,2) : $qtyConsumed;
                      $qtyAvailable = $qtyDelivered - $qtyConsumed;
                      $qtyAvailable = ( functions::isfloat($qtyAvailable) ) ? number_format($qtyAvailable,2) : $qtyAvailable;
                  ?>
                    <tr>
                        <td><?php echo $rItem['item'];?></td>
                        <td><?php echo $rItem['brand'];?></td>
                        <td>
                            <?php
                            $elm_id = $db->getValue('elm_rate','id',array('itemDesc'=>$rItem['item']));
                            $cag_id = $db->getValue('cag_detail','cag_id',array('element_id'=>$elm_id));
                            $ca_id = $db->getValue('ca_group','ca_id',array('cag_id'=>$cag_id));
                            $proj_id = $db->getValue('cost_analysis','proj_id',array('ca_id'=>$ca_id));

                            $qEstQty = $db->query('SELECT quantity FROM cost_analysis ca, ca_group cg, cag_detail cgd, elm_rate er WHERE ca.ca_id=cg.ca_id AND cgd.cag_id=cg.cag_id AND cgd.element_id=er.id AND ca.proj_id="'.$db->clean($p_id).'" AND er.itemDesc="'.$db->clean($rItem['item']).'"');
                            echo $estimatedQuantity = $db->result($qEstQty);
                            ?>
                        </td>
                        <td>
                            <a id="inventory<?php echo $count?>" class="thickbox" style="cursor: pointer;" title="Item Delivery Detail" data-rel="tooltip" onclick="showThis(this.id,'inventory_item_detail.php?pid=<?php echo functions::encode($p_id);?>&item=<?php echo functions::encode($rItem['item']);?>&unt=<?php echo functions::encode($rItem['unit']);?>&brnd=<?php echo functions::encode($rItem['brand']);?>','Inventory Detail','1')">
                            <?php
                            $qDate = $db->query('SELECT count(DISTINCT po_date) FROM po_item pi,po p WHERE p.po_id=pi.po_id AND p.proj_id="'.$db->clean($p_id).'" AND pi.item="'.$db->clean($rItem['item']).'" AND pi.brand="'.$db->clean($rItem['brand']).'" AND pi.unit="'.$db->clean($rItem['unit']).'"');
                            $dateCount = $db->result($qDate);
                            echo ($dateCount > 1) ? '<strong>'.$qtyDelivered.'</strong>' : $qtyDelivered; 
                            ?>
                            </a>
                        </td>
                        <td><a id="receive<?php echo $count?>" class="thickbox" style="cursor: pointer;" title="Item Available" data-rel="tooltip" onclick="showThis(this.id,'inventory_item_receive.php?pid=<?php echo functions::encode($p_id);?>&item=<?php echo functions::encode($rItem['item']);?>&unt=<?php echo functions::encode($rItem['unit']);?>&brnd=<?php echo functions::encode($rItem['brand']);?>','Inventory Detail','1')"><?php echo '<strong>'.$qtyAvailable.'</strong>';?></a></td>
                        <td><?php echo $rItem['unit'];?></td>
                    </tr>
                  <?php endwhile;
                  if($count==0)
                        echo '
                    <tr>
                        <td colspan="3"><div align="center">-- No Record found--- </div></td>
                    </tr>
                    ';
                  ?>
                <tbody>
            </table>
            <div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=$_SERVER['PHP_SELF'].'?pid='.functions::encode($p_id).'&txSearch='.$searchItem,$search="");?></div>
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
<!-- end: JavaScript-->
</body>
</html>