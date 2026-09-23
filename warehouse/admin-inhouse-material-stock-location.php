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
$location=""; $address="";$idEdt='';
if( isset($_POST['btnCancel']) ){
  functions::sendTo($_SERVER['PHP_SELF']);
}
if( isset($_POST['btnSave']) ){
  $editID = ( isset($_POST['edtItm']) && !empty($_POST['edtItm']) ) ? functions::decode($_POST['edtItm']) : '';
  $oldLocation = $db->getValue('inhouse_material_storage_location','location',array('imsl_id'=>$editID));
  $location = ( isset($_POST['txLoc']) && !empty($_POST['txLoc']) ) ? trim($_POST['txLoc']) : '';
  $address = ( isset($_POST['txAdd']) && !empty($_POST['txAdd']) ) ? trim($_POST['txAdd']) : '';

  if($location && $address){
    $db->update('inhouse_material_storage_location',array('location'=>$location,'address'=>$address),array('imsl_id'=>$editID));
    $db->update('inhouse_material_storage',array('location'=>$location),array('location'=>$oldLocation));
    $db->update('inhouse_material_item',array('location'=>$location),array('location'=>$oldLocation));
    functions::say('Storage Location successfully updated!');
  }
  else
    functions::say('Fill up the form properly!');
  functions::sendTo($_SERVER['PHP_SELF']);
}

if( isset($_POST['btnAdd']) ){
  $location = ( isset($_POST['txLoc']) && !empty($_POST['txLoc']) ) ? trim($_POST['txLoc']) : '';
  $address = ( isset($_POST['txAdd']) && !empty($_POST['txAdd']) ) ? trim($_POST['txAdd']) : '';

  if($location && $address){
    if( $db->getValue('inhouse_material_storage_location','count(*)',array('location'=>$location,'address'=>$address))==0 ){
      $db->insert('inhouse_material_storage_location',array('location'=>$location,'address'=>$address));
      functions::say('New Storage Location Added!');
    }
    else
      functions::say('Storage Location Already exist!');
  }
  else
    functions::say('Fill up the form properly!');
  functions::sendTo($_SERVER['PHP_SELF']);
}
if( isset($_REQUEST['vdidEdt']) ){
    $idEdt = functions::decode($_REQUEST['vdidEdt']);
    $location = $db->getValue('inhouse_material_storage_location','location',array('imsl_id'=>$idEdt));
    $address = $db->getValue('inhouse_material_storage_location','address',array('imsl_id'=>$idEdt));
}

if( isset($_REQUEST['vdidDel']) ){
    $idDel = functions::decode($_REQUEST['vdidDel']);
    $loc = $db->getValue('inhouse_material_storage_location','location',array('imsl_id'=>$idDel));
    if( $db->getValue('inhouse_material_storage','*',array('location'=>$loc),'LIMIT 1')=="" )
      $db->delete('inhouse_material_storage_location',array('imsl_id'=>$idDel));
    else
      functions::say('Cannot Delete! Some Material are referencing it.');

    functions::sendTo($_SERVER['PHP_SELF']);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Equipment Add Classification</title>
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
                  <h2><i class="halflings-icon white edit"></i><span class="break"></span>STOCK LOCATION</h2>
              </div>
              <div class="box-content">
                  <div align="center">
                      <form method="post">
                        <input type="hidden" name="edtItm" id="edtItm" value="<?php echo functions::encode($idEdt)?>">
                        <table width="85%" border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td width="20%" scope="row"><div align="center">Location Name</div></td>
                            <td width="20%"><div align="center">Address</div></td>
                            <td width="12%">&nbsp;</td>
                          </tr>
                          <tr>
                            <td align="right"><div align="center"><input type="text" style="width:50%" name="txLoc" id="txLoc" value="<?php echo $location?>" /></div></td>
                            <td><div align="center"><input type="text" style="width:110%" name="txAdd" id="txAdd" value="<?php echo $address?>" /></div></td>
                            <td>
                              <div align="center">
                                <?php if($idEdt){?>
                                <input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary">
                                <input type="submit" name="btnCancel" id="btnCancel" value="Cancel" class="btn">
                                <?php }else{?>
                                <input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary">
                                <?php }?>
                              </div>
                              </td>
                          </tr>
                        </table><br><br><br>
                        <table width="25%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-bordered">
                          <tr>
                            <th width="40%">Location Name</th>
                            <th width="50%">Address</th>
                            <th width="10%">&nbsp;</th>
                          </tr>
                          <?php
                          $q = $db->select('inhouse_material_storage_location','*',array(),'ORDER BY location');
                          while($r = $db->fetch_array($q)):
                          ?>
                          <tr>
                            <td><?php echo $r['location']?></td>
                            <td><?php echo $r['address']?></td>
                            <td>
                              <div align="center">
                                  <a id="edit<?php echo $r['imsl_id']?>" class="btn btn-mini btn-info" title="Edit this location" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?vdidEdt=<?php echo functions::encode($r['imsl_id']);?>">
                                    <i class="halflings-icon white pencil"></i> 
                                  </a>
                                  <?php if( $db->getValue('inhouse_material_storage','*',array('location'=>$r['location']),'LIMIT 1')=="" && $db->getValue('inhouse_material_item','*',array('location'=>$r['location']),'LIMIT 1')==""){?>
                                    <a id="del<?php echo $r['imsl_id']?>" class="btn btn-mini btn-danger" onClick="return askDel()" title="Remove this Location" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?vdidDel=<?php echo functions::encode($r['imsl_id']);?>">
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
  if(confirm('Do you want to delete this location?'))
    return true;
  else
    return false; 
}
</script>
<!-- end: JavaScript-->
</body>
</html>