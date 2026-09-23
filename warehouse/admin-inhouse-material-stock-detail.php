<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="10" ){
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
  $count=0;
  $mf_id=(isset($_REQUEST['mf_id']) && !empty($_REQUEST['mf_id']) ) ? $db->clean(functions::decode($_REQUEST['mf_id'])) : 0;
  $itemDel=(isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ) ? $db->clean(functions::decode($_REQUEST['itemDel'])) : 0;

  if($itemDel){
    $db->delete('inhouse_material_storage',array('ims_id'=>$itemDel));
    functions::sendTo($_SERVER['PHP_SELF'].'?mf_id='.functions::encode($mf_id));
  }
  $total_quantity = 0;
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
                    <br>
                    <div align="right"><a id="adc" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'admin-inhouse-material-stock-add.php?addDefine=<?php echo functions::encode($mf_id)?>','In-house Warehouse Stock')">Add New Inventory</a>&nbsp;</div>
                    <div class="box-content">
                      <?php
                          $qM = $db->select('material_reference','*',array('mf_id'=>$mf_id));
                          $rM = $db->fetch_array($qM);
                          $itemDisplay = 'Material Name: <strong>'.$rM['item'].'</strong>';
                          $itemDisplay .= ($rM['unit']) ? '<br>Unit: <strong>'.$rM['unit'].'</strong>' : '';
                          $itemDisplay .= ($rM['brand']) ? '<br>Brand: <strong>'.$rM['brand'].'</strong>' : '';
                          echo $itemDisplay;
                      ?>
                    <br><br>
                    <table width="95%" border="0" align="center" id="abcd" name="abcd" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;" >
                      <thead>
                      <tr>
                        <th width="45%" scope="col"><div align="left">Date</div></th>
                        <th width="20%" scope="col"><div align="left">Quantity</div></th>
                        <th width="10%" scope="col">&nbsp;</th>
                      </tr>
                      <tr>
                        <td colspan="3" height="25"></td>
                      </tr>
                      </thead>   
                      <tbody>
                      <?php
                        $count=0;
                        $qItem = $db->select('inhouse_material_storage','*',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand']),'ORDER BY ims_date');
                        #echo $db->last_query;
                        while($rItem = $db->fetch_array($qItem)):
                            $count++;
                            $ims_id = $rItem['ims_id'];
                            $total_quantity += $rItem['quantity'];
                      ?>
                      <tr>
                        <td><?php echo functions::datearr($rItem['ims_date']);?></td>
                        <td><?php echo $rItem['quantity'];?></td>
                        <td>
                            <div align="center">
                                <a id="detail<?php echo $ims_id?>" class="btn btn-mini btn-info thickbox" title="Manage Purchase item" data-rel="tooltip" onclick="showThis(this.id,'admin-inhouse-material-stock-add.php?ims_id=<?php echo functions::encode($ims_id);?>','Details')">
                                    <i class="halflings-icon white pencil"></i>
                                </a>
                                <a id="del<?php echo $ims_id;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Purchase" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?mf_id=<?php echo functions::encode($mf_id);?>&itemDel=<?php echo functions::encode($ims_id);?>"><i class="halflings-icon white trash"></i></a>
                            </div>
                        </td>
                      </tr>
                      <?php endwhile;?>
                      <tbody>
                      <tr>
                        <td><div align="right"><strong>Total Quantity</strong></div></td>
                        <td><strong><?php echo $total_quantity;?></strong></td>
                        <td></td>
                      </tr>
                      </table>
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
</script>
<!-- end: JavaScript-->

</body>
</html>

