<?php
  require_once('../class/database.php');
  #require_once('../class/logs.php');
  require_once('../class/functions.php');
  $db = new Database();
  #$logs = new Logs();
  #$logs->save('visit');
  $item=(isset($_POST['sel_item']) && !empty($_POST['sel_item']) ) ? functions::decode($_POST['sel_item']) : '';
  $itemDate=(isset($_REQUEST['dte']) && !empty($_REQUEST['dte']) ) ? functions::decode($_REQUEST['dte']) : '';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Price Monitor</title>
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
            <form method="post">
                <div class="box span12">
                    <div class="box-header" data-original-title>
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Price History</h2>
                    </div>
                    <div class="box-content">
                      <ul class="nav tab-menu nav-tabs">
                          <li><a href="change-item-name.php">Item List</a></li>
                          <li class="active"><a href="dash-price-monitor.php" style="opacity:.9">Price Monitor</a></li>
                      </ul>
                    </div>
                    <div class="box-content">

                    <table width="80%" border="0">
                      <tr>
                        <td width="13%" align="right">Item / Product: &nbsp;</td>
                        <td width="32%" align="left">
                            <select name="sel_item" id="sel_item" data-rel="chosen" style="width:600px;">
                            <option value="">-- Search Item --</option>
                            <?php
                                $qItem = $db->query('SELECT DISTINCT item FROM po_item ORDER BY item');
                                while($rItem = $db->fetch_array($qItem)):
                            ?>
                             <option value="<?php echo functions::encode($rItem['item'])?>" <?php if($item==$rItem['item'])echo 'selected="selected"';?>><?php echo $rItem['item']?></option>
                             <?php endwhile;?>
                             </select>
                        </td>
                        <td width="10%" valign="middle"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary"></td>
                      </tr>
                    </table><br><br><br>
                    <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;">
                      <tr>
                        <th width="8%" scope="col"><div align="left">Date</div></th>
                        <th width="8%" scope="col"><div align="left">Unit</div></th>
                        <th width="8%" scope="col"><div align="left">Price</div></th>
                      </tr>
                      <?php
                      $qPrice = $db->query('SELECT po_date,unit,cost FROM `po_item` pi, po p WHERE p.po_id=pi.po_id and `item` = "'.$db->clean($item).'" group by po_date,cost ORDER BY `p`.`po_date` DESC');
                      while($rPrice = $db->fetch_array($qPrice)):
                          $bgColor='';
                        if($itemDate==$rPrice['po_date']){
                           $bgColor = 'bgcolor="#f5ae00"';
                        }
                      ?>
                      <tr <?php echo $bgColor;?>>
                        <td height="25"><?php echo functions::datearr($rPrice['po_date'])?></td>
                        <td><?php echo $rPrice['unit'];?></td>
                        <td><?php echo functions::formatMoney($rPrice['cost'])?></td>
                      </tr>
                    <?php endwhile;?>
                    </table>
                    <div style="font-size:12px;">
                    <?php
                      $high=0;
                      $low=0;
                      echo ( $high = $db->getValue('po_item','cost',array('item'=>$item),'ORDER BY cost DESC') ) ? '<br><br><strong>Highest Price: '. functions::formatMoney($high) .'</strong>': '';
                      echo ( $low = $db->getValue('po_item','cost',array('item'=>$item),'ORDER BY cost ASC') ) ? '<br><br><strong>Lowest Price: '. functions::formatMoney($low) .'</strong>': '';
                    ?>
                  </div>
                    </div>
                </div><!--/span-->
            </form>
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
<script>function selDt(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?itm="+PiEwgD}</script>
<!-- end: JavaScript-->

</body>
</html>

