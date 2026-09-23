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
$type_name=''; $type_editID='';
if( isset($_POST['btnAdd']) ){
  $txType = ( isset($_POST['txType']) && !empty($_POST['txType']) ) ? trim($_POST['txType']) : '';
  if( $txType ){
    if( $db->getValue('material_type','count(*)',array('type_name'=>'brand','type_desc'=>$txType))==0 ){
        $db->insert('material_type',array('type_name'=>'brand','type_desc'=>$txType));
    }
  }
  functions::sendTo($_SERVER['PHP_SELF']);
}
if( isset($_POST['btnSave']) ){
  $txType = ( isset($_POST['txType']) && !empty($_POST['txType']) ) ? trim($_POST['txType']) : '';
  $txID = ( isset($_POST['txID']) && !empty($_POST['txID']) ) ? functions::decode($_POST['txID']) : '';
  $oldUnit = $db->getValue('material_type','type_desc',array('mt_id'=>$txID));

  if( $txType && $txID){

    $db->update('material_type',array('type_desc'=>$txType),array('type_name'=>'brand','mt_id'=>$txID));

    if( functions::encode($oldUnit) != functions::encode($txType) ){
      $db->update('material_reference',array('brand'=>$txType),array('brand'=>$oldUnit));
      $db->update('po_item',array('brand'=>$txType),array('brand'=>$oldUnit));
      $db->update('inhouse_material_storage',array('brand'=>$txType),array('brand'=>$oldUnit));
      $db->update('inhouse_material_item',array('brand'=>$txType),array('brand'=>$oldUnit));
    }

    if( $db->getValue('material_type','count(*)',array('type_name'=>'brand','type_desc'=>$txType)) > 1 )
      $db->delete('material_type',array('mt_id'=>$txID));
  }
  functions::sendTo($_SERVER['PHP_SELF']);
}
if( isset($_REQUEST['vdidDel']) ){
    $db->delete('material_type',array('mt_id'=>functions::decode($_REQUEST['vdidDel'])));
    functions::sendTo($_SERVER['PHP_SELF']);
}
if( isset($_REQUEST['vdidEdt']) ){
    $type_editID = functions::decode($_REQUEST['vdidEdt']);
    $type_name = $db->getValue('material_type','type_desc',array('mt_id'=>$type_editID));
    #functions::sendTo($_SERVER['PHP_SELF']);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Material Brand</title>
            <!-- end: Meta -->
            <!-- start: Mobile Specific -->
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <!-- end: Mobile Specific -->
            <!-- start: CSS -->
            <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
            <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
            <link id="base-style" href="../css/style.css" rel="stylesheet">
            <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
            <script src="../js/formatCurrency.js"></script>
            <script src="../js/inputInt.js"></script>
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
            <style>.tdSpace{padding: 12px 0px 4px 0px;}</style>
  </head>
  <body>
  <!-- body content: start here-->
      <div class="row-fluid sortable">
          <div class="box span12">
              <div class="box-header" data-original-title>
                  <h2><i class="halflings-icon white edit"></i><span class="break"></span>MATERIAL BRAND</h2>
              </div>
              <div class="box-content">
                  <div align="center">
                    <form method="post">
                      <input type="hidden" name="txID" id="txID" value="<?php echo functions::encode($type_editID)?>">
                          <table width="45%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <th width="15%" align="right" scope="row">&nbsp;</th>
                              <td width="2%">&nbsp;</td>
                              <td width="10%">&nbsp;</td>
                              <td width="12%">&nbsp;</td>
                            </tr>
                            <tr>
                              <th align="right" scope="row">Brand Name</th>
                              <td>&nbsp;</td>
                              <td class="tdSpace"><input type="text" name="txType" id="txType" value="<?php echo $type_name?>" /></td>
                              <td>
                                <?php if($type_editID){?>
                                <input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary">
                                <?php }else{?>
                                <input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary">
                                <?php }?>
                              </td>
                            </tr>
                          </table>
                        <br><br><br>
                          <div style="width:500px">
                          <table width="25%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-bordered">
                            <tr>
                              <th width="25%" align="left" scope="row">Material Brand</th>
                              <td width="3%">&nbsp;</td>
                            </tr>
                            <?php
                                $q = $db->select('material_type','*',array('type_name'=>'brand'),'ORDER BY type_desc');
                                while($r=$db->fetch_array($q)):
                            ?>
                            <tr>
                              <td class="tdSpace"><?php echo $r['type_desc']?></td>
                              <td>
                                <div align="center">
                                    <a id="edt<?php echo $r['mt_id']?>" class="btn btn-mini btn-warning" title="Update this item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?vdidEdt=<?php echo functions::encode($r['mt_id']);?>">
                                      <i class="halflings-icon white pencil"></i> 
                                    </a>
                                    <?php if( $db->getValue('inhouse_material_item','count(*)',array('lower(brand)'=>strtolower($r['type_desc'])))==0 && $db->getValue('po_item','count(*)',array('lower(brand)'=>strtolower($r['type_desc'])))==0){ ?>
                                    <a id="del<?php echo $r['mt_id']?>" class="btn btn-mini btn-danger" onClick="return askDel()" title="Remove this item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?vdidDel=<?php echo functions::encode($r['mt_id']);?>">
                                      <i class="halflings-icon white trash"></i> 
                                    </a>
                                    <?php }?>
                                </div>
                              </td>
                            </tr>
                            <?php endwhile;?>
                          </table>
                        </div>
                    </form>
                  </div>
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
<script src="../js/wxhBox.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function askDel(){
  if(confirm('Do you want to delete this brand?'))
    return true;
  else
    return false; 
}
</script>
<!-- end: JavaScript-->
</body>
</html>