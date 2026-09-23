<?php
  require_once('../class/database.php');
  #require_once('../class/logs.php');
  require_once('../class/functions.php');
  $db = new Database();
$item = ( isset($_REQUEST['item']) ) ? functions::decode($_REQUEST['item']) : '';
$unit = ( isset($_REQUEST['unit']) ) ? functions::decode($_REQUEST['unit']) : '';
$brand = ( isset($_REQUEST['brand']) ) ? functions::decode($_REQUEST['brand']) : '';

if( isset($_POST['btnSubmit']) ){
  $txOldItem = (isset($_POST['txOldItem'])) ? functions::decode($_POST['txOldItem']) : '';
  $txOldUnit = (isset($_POST['txOldUnit'])) ? functions::decode($_POST['txOldUnit']) : '';
  $txOldBrand = (isset($_POST['txOldBrand'])) ? functions::decode($_POST['txOldBrand']) : '';

  $txItemName = (isset($_POST['txItemName'])) ? $_POST['txItemName'] : '';
  $txUnit = (isset($_POST['txUnit'])) ? $_POST['txUnit'] : '';
  $txBrand = (isset($_POST['txBrand'])) ? $_POST['txBrand'] : '';

  $db->update('po_item',array('item'=>$txItemName,'unit'=>$txUnit,'brand'=>$txBrand),array('item'=>$txOldItem,'unit'=>$txOldUnit,'brand'=>$txOldBrand));
  $db->update('inhouse_material_item',array('item'=>$txItemName,'unit'=>$txUnit,'brand'=>$txBrand),array('item'=>$txOldItem,'unit'=>$txOldUnit,'brand'=>$txOldBrand));
  functions::sendTo($_SERVER['PHP_SELF'].'?item='.functions::encode($txItemName).'&unit='.functions::encode($txUnit).'&brand='.functions::encode($txBrand));
}

$qItem = $db->query('SELECT DISTINCT item FROM inhouse_material_item UNION SELECT DISTINCT item FROM po_item ORDER BY item');
$namesItem='';
  while($rItem=$db->fetch_array($qItem)):
      $string = preg_replace("/'/",'"',$rItem['item']);
      $namesItem .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
  endwhile;
$namesItem .= '"--"';

$qUnit = $db->query('SELECT DISTINCT unit FROM inhouse_material_item UNION SELECT DISTINCT unit FROM po_item ORDER BY unit');
$namesUnit='';
  while($rUnit=$db->fetch_array($qUnit)):
      $string = preg_replace("/'/",'"',$rUnit['unit']);
      $namesUnit .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
  endwhile;
$namesUnit .= '"--"';

$qBrand = $db->query('SELECT DISTINCT brand FROM inhouse_material_item UNION SELECT DISTINCT brand FROM po_item ORDER BY brand');
$namesBrand='';
  while($rBrand=$db->fetch_array($qBrand)):
      $string = preg_replace("/'/",'"',$rBrand['brand']);
      $namesBrand .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
  endwhile;
$namesBrand .= '"--"';
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
                        <th width="50%">Item</th>
                        <th width="25%">Unit</th>
                        <th width="25%">Brand</th>
                    </tr>
                    <tr>
                        <td><textarea name="txItemName" id="txItemName" style="width:450px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesItem;?>]'><?php echo $item;?></textarea></td>
                        <td><input type="text" name="txUnit" id="txUnit" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesUnit;?>]' value="<?php echo $unit;?>"></td>
                        <td><input type="text" name="txBrand" id="txBrand" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesBrand;?>]' value="<?php echo $brand;?>"></td>
                    </tr>
                  </table>
                  <div align="center"><input type="submit" name="btnSubmit" id="btnSubmit" value=" Save "></div>
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