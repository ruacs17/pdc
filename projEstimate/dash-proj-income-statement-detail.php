<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="12" ){
  header("Location: ../");
  die();
}
  $username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
  $user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
  require_once('../class/database.php');
  #require_once('../class/logs.php');
  require_once('../class/functions.php');
  $db = new Database();
  $name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
  #$logs = new Logs();
  #$logs->save('visit');
  
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$category_id = (isset($_REQUEST['cid']) && !empty($_REQUEST['cid']) ) ? functions::decode($_REQUEST['cid']) : 0;
$expense_type = (isset($_REQUEST['et']) && !empty($_REQUEST['et']) ) ? functions::decode($_REQUEST['et']) : 0;
$cost_type = (isset($_REQUEST['ct']) && !empty($_REQUEST['ct']) ) ? functions::decode($_REQUEST['ct']) : 0;
$month = (isset($_REQUEST['mon']) && !empty($_REQUEST['mon']) ) ? functions::decode($_REQUEST['mon']) : '';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Voucher Particular</title>
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
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Particular Details</h2>
                    </div>
                    <div class="box-content">
                    <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-striped table-bordered bootstrap-datatable datatable" style="font-size:12px;">
                      <thead>
                      <tr>
                        <th width="8%" scope="col"><div align="left">Date</div></th>
                        <th width="8%" scope="col"><div align="left">Voucher No.</div></th>
                        <th width="15%" scope="col"><div align="left">Category</div></th>
                        <th width="30%" scope="col"><div align="left">Item Detail</div></th>
                        <th width="10%" scope="col"><div align="left">Amount</div></th>
                      </tr>
                      <tr>
                        <td colspan="5" height="25"></td>
                      </tr>
                    </thead>
                    <tbody>
                          <?php 
                            $vdate='';
                            $total_amount=0;
                            $q = 'SELECT * FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($p_id).'"';
                            $q .= ($category_id) ? ' AND vd.category_id="'.$db->clean($category_id).'"' : '';
                            $q .= ($month) ? ' AND left(vd_date,7)="'.$db->clean($month).'"' : "";
                            $q .= ($expense_type) ? ' AND itd.expense_type="'.$db->clean($expense_type).'"' : "";
                            $q .= ($cost_type) ? ' AND itd.cost_type="'.$db->clean($cost_type).'"' : "";
                            $q .= ' ORDER BY vd_date';
                            $qvDetails = $db->query($q);
                            while($rvDetails = $db->fetch_array($qvDetails)):
                            $total_amount += $rvDetails['amount'];
                          ?>
                      <tr>
                        <td><?php echo functions::datearr($rvDetails['vd_date']);?></td>
                        <td>
                          <?php 
                            $vid = $db->query('SELECT voucher_id FROM voucher_detail vd, voucher_particular vp WHERE vd.vp_id=vp.vp_id AND vd.vd_id="'.$db->clean($rvDetails['vd_id']).'"');
                            echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$db->result()));
                          ?>
                        </td>
                        <td><?php echo $db->getValue('item_deduction','name',array('item_id'=>$rvDetails['category_id']));?></td>
                        <td><?php echo $rvDetails['item']?></td>
                        <td><?php echo functions::formatMoney($rvDetails['amount']);?></td>
                      </tr>
                          <?php endwhile;?>



                      <?php
                        $qPO = 'SELECT * FROM po p, po_item poi, item_deduction itd WHERE p.po_id=poi.po_id AND p.category_id=itd.item_id AND p.po_id IN (SELECT distinct po_id FROM view_po_payment WHERE paid > 0)';
                        $qPO .= ($p_id) ? ' AND p.proj_id="'.$db->clean($p_id).'"' : '';
                        $qPO .= ($category_id) ? ' AND p.category_id="'.$db->clean($category_id).'"' : '';
                        $qPO .= ($expense_type) ? ' AND itd.expense_type="'.$db->clean($expense_type).'"' : '';
                        $qPO .= ($cost_type) ? ' AND itd.cost_type="'.$db->clean($cost_type).'"' : "";
                        $qPO .= ($month) ? ' AND left(p.po_date,7)="'.$db->clean($month).'"' : "";
                        $qPO .=' ORDER BY p.po_date';

                        $qPODetails = $db->query($qPO);
                        #echo $db->last_query;
                        while($rPODetails = $db->fetch_array($qPODetails)):
                        $amount=0;
                            $amount = $rPODetails['cost'] * $rPODetails['qty_delivered'];
                            $disc_amount = ($rPODetails['discount']) ? $amount * ($rPODetails['discount'] / 100) : 0;
                            $amount = $amount - $disc_amount;
                            $total_amount += $amount;
                      ?>
                      <tr>
                        <td>
                          <?php echo functions::datearr($rPODetails['po_date']);?>
                        </td>
                        <td>
                        <?php
                          $v = $db->getValue('voucher_particular','voucher_id',array('vp_id'=>$rPODetails['vp_id']));
                          echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$v));
                        ?>
                        </td>
                        <td><?php echo $db->getValue('voucher_particular','vp_title',array('vp_id'=>$rPODetails['vp_id']));?></td>
                        <td><?php echo $rPODetails['item'].' ( '.round($rPODetails['qty_delivered']).' '.$rPODetails['unit'].' x '.functions::formatMoney($rPODetails['cost'],2).' )'?></td>
                        <td><?php echo functions::formatMoney($amount);?></td>
                      </tr>
                      <?php endwhile;?>

                      <?php
                        if($expense_type != "materials"){
                            $qInhouseMaterials = 'SELECT * FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($p_id).'"';
                            $qInhouseMaterials .= ($category_id) ? ' AND im.category_id="'.$db->clean($category_id).'"' : '';
                            $qInhouseMaterials .= ($expense_type) ? ' AND itd.expense_type="'.$db->clean($expense_type).'"' : '';
                            $qInhouseMaterials .= ($cost_type) ? ' AND itd.cost_type="'.$db->clean($cost_type).'"' : "";
                            $qInhouseMaterials .= ($month) ? ' AND left(im_date,7)="'.$db->clean($month).'"' : "";
                            $qInhouseMaterials .= 'ORDER BY im_date';

                        
                        $qIM = $db->query($qInhouseMaterials);
                        while($rIM = $db->fetch_array($qIM)):
                        $amount=0;
                            $amount = $rIM['cost'] * $rIM['quantity'];
                            $disc_amount = ($rIM['discount']) ? $amount * ($rIM['discount'] / 100) : 0;
                            $amount = $amount - $disc_amount;
                            $total_amount += $amount;
                      ?>
                      <tr>
                        <td>
                          <?php echo functions::datearr($rIM['im_date']);?>
                        </td>
                        <td>&nbsp;</td>
                        <td>Inhouse Warehouse Stock</td>
                        <td><?php echo $rIM['item'].' ( '.round($rIM['quantity']).' '.$rIM['unit'].' x '.functions::formatMoney($rIM['cost'],2).' )'?></td>
                        <td><?php echo functions::formatMoney($amount);?></td>
                      </tr>
                      <?php endwhile;
                      }#if($expense_type != "materials"){
                      ?>
                    </tbody>
                    </table>
                    <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-striped table-bordered" style="font-size:12px;">
                      <tr>
                        <td width="80%" colspan="3"><div align="right"><strong>Total Amount</strong></div></td>
                        <td width="15%"><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
                       </tr>
                    </table>
                    <p>&nbsp;</p>
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
<!-- end: JavaScript-->

</body>
</html>

