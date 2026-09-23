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
  
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Project Cost View</title>
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
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Particular Details</h2>
                    </div>
                    <div class="box-content">
                    <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered">
                      <tr>
                        <th width="14%" scope="col"><div align="center">Date</div></th>
                        <th width="14%" scope="col"><div align="center">Voucher ID</div></th>
                        <th width="20%" scope="col"><div align="center">Category</div></th>
                        <th width="19%" scope="col"><div align="center">Item Detail</div></th>
                        <th width="15%" scope="col"><div align="center">Amount</div></th>
                      </tr>
                      <tr>
                        <td colspan="5" height="25"></td>
                      </tr>
                          <?php 
              					  	$vdate='';
              						  $total_amount=0;
              					  	$qvDetails = $db->select('voucher_detail','*',array('proj_id'=>$p_id),'ORDER BY vd_date');
              					  	while($rvDetails = $db->fetch_array($qvDetails)):
              						  $total_amount += $rvDetails['amount'];
              					  ?>
                      <tr>
                        <td>
                          <?php 
                                if($vdate != $rvDetails['vd_date']){
                                  $vdate = $rvDetails['vd_date'];
                                  echo functions::datearr($rvDetails['vd_date']);
                                }
                          ?>
                        </td>
                        <td>
            							<?php 
            								$vid = $db->query('SELECT voucher_id FROM voucher_detail vd, voucher_particular vp WHERE vd.vp_id=vp.vp_id AND vd.vd_id="'.$db->clean($rvDetails['vd_id']).'"');
            								echo $db->result();
            							?>
                        </td>
                        <td><?php echo $rvDetails['category'];?></td>
                        <td><?php echo $rvDetails['item']?></td>
                        <td><?php echo functions::formatMoney($rvDetails['amount']);?></td>
                      </tr>
                          <?php endwhile;?>



                      <?php
                        $qPODetails = $db->query('SELECT * FROM po, po_item WHERE po.po_id=po_item.po_id AND po.proj_id="'.$db->clean($p_id).'" ORDER BY po.po_date');
                        while($rPODetails = $db->fetch_array($qPODetails)):
                        $amount=0;
                        $amount = $rPODetails['quantity'] * $rPODetails['cost'];
                        $total_amount += $amount;
                      ?>
                      <tr>
                        <td>
                          <?php 
                                if($vdate != $rPODetails['po_date']){
                                  $vdate = $rPODetails['po_date'];
                                  echo functions::datearr($rPODetails['po_date']);
                                }
                          ?>
                        </td>
                        <td>
                        <?php
                          echo $db->getValue('voucher_particular','voucher_id',array('vp_id'=>$rPODetails['vp_id']));
                        ?>
                        </td>
                        <td>PURCHASE ORDER PAYMENT</td>
                        <td><?php echo $rPODetails['item'].' ( '.$rPODetails['quantity'].' '.$rPODetails['unit'].' x '.functions::formatMoney($rPODetails['cost']).' )'?></td>
                        <td><?php echo functions::formatMoney($amount);?></td>
                      </tr>
                      <?php endwhile;?>
                      <tr>
                      	<td></td>
                        <td></td>
                        <td>&nbsp;</td>
                        <td><div align="right"><strong>Total Amount</strong></div></td>
                        <td><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
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
<script>
function delt(){
	if(confirm('Do you want to remove this?'))
		return true;
	else
		return false;	
}
</script>
<!-- end: JavaScript-->

</body>
</html>

