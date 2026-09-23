<?php
  require_once('../class/database.php');
  #require_once('../class/logs.php');
  require_once('../class/functions.php');
  $db = new Database();
  #$logs = new Logs();
  #$logs->save('visit');
$item = ( isset($_REQUEST['item']) ) ? functions::decode($_REQUEST['item']) : '';
$unit = ( isset($_REQUEST['unit']) ) ? functions::decode($_REQUEST['unit']) : '';
$brand = ( isset($_REQUEST['brand']) ) ? functions::decode($_REQUEST['brand']) : '';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Project Report</title>
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
            <div class="row-fluid sortable">
              <div class="box span12">
                    <div class="box-header" data-original-title>
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Report</h2>
                    </div>
                <div class="box-content" align="center"> 
                    <div>
                  <form method="POST">
                    <input type="hidden" name="txOldItem" id="txOldItem" value="<?php echo functions::encode($item)?>">
                    <input type="hidden" name="txOldUnit" id="txOldUnit" value="<?php echo functions::encode($unit)?>">
                    <input type="hidden" name="txOldBrand" id="txOldBrand" value="<?php echo functions::encode($brand)?>">
                  <table width="80%" align="center" border="0" class="table table-bordered table-hover bootstrap-datatable datatable" style="font-size: 12px;">
                    <tr>
                        <th width="10%">Date</th>
                        <th width="40%">Item</th>
                        <th width="10%">Quantity</th>
                        <th width="20%">Unit</th>
                        <th width="20%">Brand</th>
                    </tr>
                <?php
                $qPO = $db->select('po_item poi, po p','*',array('item'=>$item,'unit'=>$unit,'brand'=>$brand),'AND poi.po_id=p.po_id ORDER BY p.proj_id');
                #echo $db->last_query;
                $proj_id='';
                while($rPO = $db->fetch_array($qPO)):
                  if($proj_id != $rPO['proj_id']){
                    $proj_id=$rPO['proj_id'];
                    ?>
                    <tr>
                        <td colspan="5">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="5"><strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id']));?></strong></td>
                    </tr>
                  <?php }?>
                    <tr>
                        <td><?php echo functions::datearr($rPO['po_date'])?></td>
                        <td><?php echo $rPO['item']?></td>
                        <td><?php echo $rPO['qty_delivered']?></td>
                        <td><?php echo $rPO['unit']?></td>
                        <td><?php echo $rPO['brand']?></td>
                    </tr>
                <?php 
                endwhile;

                ?>
                  </table>
                  <div align="center"><a href="<?php echo 'dash-material-edit.php?item='.functions::encode($item).'&unit='.functions::encode($unit).'&brand='.functions::encode($brand);?>">Edit</a></div>
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
<script>function selDt(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?pdt="+PiEwgD}</script>
<!-- end: JavaScript-->

</body>
</html>