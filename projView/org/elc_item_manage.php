<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="9" ){
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

  function disp($parentItem,$level,$currentItemID){
  global $db;
  $string='';

  $q = $db->select('el_capability','*',array('itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned)');
  while($r = $db->fetch_array($q)):
    $s = '';
    for($i=1; $i<=$level; $i++):
      $s .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    endfor;
    $s .= $r['itemNo'].'&nbsp;&nbsp;'.$r['itemDesc'];
    $selected = ($currentItemID==$r['id']) ? 'selected="selected"' : "";
    $string ='
    <option value="'.$r['id'].'" '.$selected.'>'.$s.'</option>
    ';
    echo $string;
    disp($r['id'],$level + 1,$currentItemID);
  endwhile;
  }

  function updateSubItem($itemID){
    global $db;
    $itemParentItemNo = $db->getValue('el_capability','itemNo',array('id'=>$itemID));
    $q = $db->select('el_capability','*',array('itemParent'=>$itemID),'ORDER BY id');
    $count=0;
    while($r = $db->fetch_array($q)):
      $count++;
      $newItemNo = $itemParentItemNo. '.' . $count;
      $db->update('el_capability',array('itemNo'=>$newItemNo),array('id'=>$r['id']));
      updateSubItem($r['id']);
    endwhile;
  }



  $count=0;
  $itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;

  $readOnly =  ( $db->getValue('el_capability','count(*)',array('itemParent'=>$itemID)) ) ? "readonly" : "";

  $txItemDesc = $db->getValue('el_capability','itemDesc',array('id'=>$itemID));
  $qItemDetail = $db->select('el_capability','*',array('id'=>$itemID));
  $r = $db->fetch_array($qItemDetail);
  $itemBase=($r['itemBase']) ? $r['itemBase'] : '';
  $txItemDesc=($r['itemDesc']) ? $r['itemDesc'] : '';

  $txSlowManHour = ($r['slow_manhour']) ? functions::formatMoney($r['slow_manhour']) : '';
  $txSlowEHour = ($r['slow_ehour']) ? functions::formatMoney($r['slow_ehour']) : '';
  $txMedManHour = ($r['med_manhour']) ? functions::formatMoney($r['med_manhour']) : '';
  $txMedEHour = ($r['med_ehour']) ? functions::formatMoney($r['med_ehour']) : '';
  $txHighManHour = ($r['high_manhour']) ? functions::formatMoney($r['high_manhour']) : '';
  $txHighEHour = ($r['high_ehour']) ? functions::formatMoney($r['high_ehour']) : '';

  $txUnit = ($r['itemUnit']) ? $r['itemUnit'] : '';
  $itemParentTx = ($r['itemParent']) ? $r['itemParent'] : '';


  $qUnit = $db->select('el_capability','DISTINCT itemUnit',array());
  $namesUnit='';
  while($rUnit=$db->fetch_array($qUnit)):
      $string = preg_replace("/'/",'"',$rUnit['itemUnit']);
      $namesUnit .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
  endwhile;
  $namesUnit .= '"--"';


  if( isset($_POST['btnSave']) ){
    $selParentItem = (isset($_POST['selParentItem']) && !empty($_POST['selParentItem']) ) ? $_POST['selParentItem'] : '';
    if($selParentItem!=$itemParentTx){
        $itemIDParent = $db->getValue('el_capability','itemParent',array('id'=>$itemID));
        $rootItemNo = $db->getValue('el_capability','itemNo',array('id'=>$selParentItem));
        $subItemNo = $db->getValue('el_capability','count(*)',array('itemParent'=>$selParentItem)) + 1;
        $newItemNo = $rootItemNo.'.'.$subItemNo;
        $db->update('el_capability',array('itemNo'=>$newItemNo,'itemParent'=>$selParentItem),array('id'=>$itemID));
        updateSubItem($itemID); #updating Item's new subItem
        updateSubItem($itemIDParent); #update Item's recent subItem
    }

    $itemDesc = (isset($_POST['txItemDesc']) && !empty($_POST['txItemDesc']) ) ? trim($_POST['txItemDesc']) : '';
    $slow_manhour = (isset($_POST['txSlowManHour']) && !empty($_POST['txSlowManHour']) ) ? functions::moneyToDouble(trim($_POST['txSlowManHour'])) : 0;
    $slow_ehour = (isset($_POST['txSlowEHour']) && !empty($_POST['txSlowEHour']) ) ? functions::moneyToDouble(trim($_POST['txSlowEHour'])) : 0;
    $med_manhour = (isset($_POST['txMedManHour']) && !empty($_POST['txMedManHour']) ) ? functions::moneyToDouble(trim($_POST['txMedManHour'])) : 0;
    $med_ehour = (isset($_POST['txMedEHour']) && !empty($_POST['txMedEHour']) ) ? functions::moneyToDouble(trim($_POST['txMedEHour'])) : 0;
    $high_manhour = (isset($_POST['txHighManHour']) && !empty($_POST['txHighManHour']) ) ? functions::moneyToDouble(trim($_POST['txHighManHour'])) : 0;
    $high_ehour = (isset($_POST['txHighEHour']) && !empty($_POST['txHighEHour']) ) ? functions::moneyToDouble(trim($_POST['txHighEHour'])) : 0;
    $itemUnit = (isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : '';


    $db->update('el_capability',array('itemDesc'=>$itemDesc,'slow_manhour'=>$slow_manhour,'slow_ehour'=>$slow_ehour,'med_manhour'=>$med_manhour,'med_ehour'=>$med_ehour,'high_manhour'=>$high_manhour,'high_ehour'=>$high_ehour,'itemUnit'=>$itemUnit),array('id'=>$itemID));
    functions::sendTo($_SERVER['PHP_SELF'].'?itemID='.functions::encode($itemID));
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Item Add</title>
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
              <script src="../js/inputInt.js"></script>
              <script src="../js/formatCurrency.js"></script>
              <!-- end: Favicon -->

  </head>
  <body>
            <!-- body content: start here-->
            <div class="row-fluid sortable">
                <div class="box span12">
                    <div class="box-header" data-original-title>
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Item Manage</h2>
                    </div>
                    <div class="box-content">
                    <form method="post">
                        <div><strong>Parent Item:</strong>
                          <select name="selParentItem" id="selParentItem" style="width:350px;">
                            <option value="">None</option>
                            <?php 
                            $q = $db->select('el_capability','*',array('id'=>$itemBase),'ORDER BY cast(itemNo as unsigned)');
                            while($r = $db->fetch_array($q)):
                            ?>
                              <option value="<?php echo $r['id']?>" <?php if($itemParentTx==$r['id'])echo 'selected="selected"';?>><?php echo $r['itemDesc']?></option>
                            <?php
                              disp($r['id'],1,$itemParentTx);
                            endwhile;
                            ?>
                            </select>
                        </div><br/><br/><br/>
                    <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;">
                      <tr>
                        <th width="33%" scope="col"><div align="center">Description</div></th>
                        <th width="7%" scope="col"><div align="center">Manhours</div></th>
                        <th width="7%" scope="col"><div align="center">Ehours</div></th>
                        <th width="7%" scope="col"><div align="center">Manhours</div></th>
                        <th width="7%" scope="col"><div align="center">Ehours</div></th>
                        <th width="7%" scope="col"><div align="center">Manhours</div></th>
                        <th width="7%" scope="col"><div align="center">Ehours</div></th>
                        <th width="10%" scope="col"><div align="center">Unit</div></th>
                        <th width="8%" scope="col"><div align="center">&nbsp;</div></th>
                      </tr>
                      <tr>
                        <td><div align="center"><input type="text" name="txItemDesc" id="txItemDesc" value="<?php echo htmlentities($txItemDesc);?>" style="width: 430px;" ></div></td>
                        <td><div align="center"><input type="text" style="width: 60px;" onkeyup="FormatCurrency(this);" onkeypress="return checkinput(this, event);" <?php echo $readOnly?> name="txSlowManHour" id="txSlowManHour" value="<?php echo $txSlowManHour;?>"></div></td>
                        <td><div align="center"><input type="text" style="width: 60px;" onkeyup="FormatCurrency(this);" onkeypress="return checkinput(this, event);" <?php echo $readOnly?> name="txSlowEHour" id="txSlowEHour" value="<?php echo $txSlowEHour;?>"></div></td>
                        <td><div align="center"><input type="text" style="width: 60px;" onkeyup="FormatCurrency(this);" onkeypress="return checkinput(this, event);" <?php echo $readOnly?> name="txMedManHour" id="txMedManHour" value="<?php echo $txMedManHour;?>"></div></td>
                        <td><div align="center"><input type="text" style="width: 60px;" onkeyup="FormatCurrency(this);" onkeypress="return checkinput(this, event);" <?php echo $readOnly?> name="txMedEHour" id="txMedEHour" value="<?php echo $txMedEHour;?>"></div></td>
                        <td><div align="center"><input type="text" style="width: 60px;" onkeyup="FormatCurrency(this);" onkeypress="return checkinput(this, event);" <?php echo $readOnly?> name="txHighManHour" id="txHighManHour" value="<?php echo $txHighManHour;?>"></div></td>
                        <td><div align="center"><input type="text" style="width: 60px;" onkeyup="FormatCurrency(this);" onkeypress="return checkinput(this, event);" <?php echo $readOnly?> name="txHighEHour" id="txHighEHour" value="<?php echo $txHighEHour;?>"></div></td>
                        <td><div align="center"><input type="text" name="txUnit" id="txUnit" value="<?php echo $txUnit;?>" style="width: 120px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesUnit;?>]' <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="submit" name="btnSave" class="btn btn-medium btn-info" id="btnSave" value=" Save "></div></td>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<!-- end: JavaScript-->

</body>
</html>

