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
  $count=0;

  $itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;

  function itemSubTotal($itemParent,&$sum){
    global $db;
    $q = $db->select('dqc','*',array('itemParent'=>$itemParent));
    while($r = $db->fetch_array($q)):
        $sum += $r['total'];
        itemSubTotal($r['id'],$sum);
    endwhile;
  }
  function itemTotal($itemParent){
    $sum=0;
    itemSubTotal($itemParent,$sum);
    return $sum;
  }
  function itemUnitName($itemParent){
    global $db;
    $unit='';
    $q = $db->select('dqc','*',array('itemParent'=>$itemParent));
    while($r = $db->fetch_array($q)):
        if( $r['itemUnit'] ){
            $unit = $r['itemUnit'];
            break;
        }
        else
            $unit = itemUnitName($r['id']);
    endwhile;
    return $unit;
  }

  function disp($proj_id,$parentItem,$level){
  global $db;
  $string='';
  #$orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$proj_id,'itemParent'=>$parentItem)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo';
  #$orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$proj_id,'itemParent'=>$parentItem)) > 9) ? 'ORDER BY cast(itemNo as unsigned)' : 'ORDER BY itemNo';
  $q = $db->select('dqc','*',array('proj_id'=>$proj_id,'itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned)');

  while($r = $db->fetch_array($q)):
    $itemNo = ($r['itemNo']) ? $r['itemNo'] : "";
    $noOfSets = ($r['noOfSets']) ? $r['noOfSets'] : "";
    $noOfUnit = ($r['noOfUnit']) ? $r['noOfUnit'] : "";
    $length = ($r['length']) ? $r['length'] : "";
    $size = ($r['size']) ? $r['size'] : "";
    $noUnit = ($r['noPerUnit']) ? $r['noPerUnit'] : "";
    $weight = ($r['weight']) ? $r['weight'] : "";
    $itemDesc = ($r['itemDesc']) ? $r['itemDesc'] : "";
    if( $db->getValue('dqc','count(*)',array('itemParent'=>$r['id'])) ){
      $itemDesc = ($r['itemDesc']) ? '<strong>'.$r['itemDesc'].'</strong>' : "";
    }

    /*if($level==1){
      $itemNo = ($r['itemNo']) ? '<strong>'.$r['itemNo'].'</strong>' : "";
      $itemDesc = ($r['itemDesc']) ? '<strong>'.$r['itemDesc'].'</strong>' : "";
    }*/
    if($level<=2){
      $it = itemTotal($r['id']);
      $it = ($it) ? functions::formatMoney($it) : '';
      $quantity = ($r['total']) ? functions::formatMoney($r['total']) : '<strong>'.$it.'</strong>';
      $itemUnit = ($r['itemUnit']) ? $r['itemUnit'] : '<strong>'.itemUnitName($r['id']).'</strong>';
    }
    else{
      $quantity = ($r['total']) ? functions::formatMoney($r['total']) : '';
      $itemUnit = ($r['itemUnit']) ? $r['itemUnit'] : "";
    }
    $s = '';
    for($i=1; $i<=$level; $i++):
      $s .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    endfor;
    $s .= $itemNo.'&nbsp;&nbsp;&nbsp;&nbsp;'.$itemDesc;
    $addSubItem = "showThis(this.id,'dqc2_item_add.php?pid=".functions::encode($proj_id)."&itemID=".functions::encode($r['id'])."','Adding DQC Item')";
    $manageItem = "showThis(this.id,'dqc2_item_manage.php?pid=".functions::encode($proj_id)."&itemID=".functions::encode($r['id'])."','Adding DQC Item')";
    $deleteItem = "dqc.php?pid=".functions::encode($proj_id)."&delItemID=".functions::encode($r['id'])."&itemID=".functions::encode($r['itemBase']);
    $btn = '&nbsp;&nbsp;&nbsp;
          <a id="AddSubItem'.$r['id'].'" class="thickbox" style="cursor:pointer" title="Add Sub Item" data-rel="tooltip" onclick="'.$addSubItem.'">
            <i class="halflings-icon plus-sign"></i>
          </a>
          <a id="ManageItem'.$r['id'].'" class="thickbox" style="cursor:pointer" title="Manage Item" data-rel="tooltip" onclick="'.$manageItem.'">
            <i class="halflings-icon pencil"></i>
          </a>
          <a id="DeleteItem'.$r['id'].'" style="cursor:pointer" title="Delete Item" data-rel="tooltip" onClick="return delt()" href="'.$deleteItem.'">
            <i class="halflings-icon minus-sign"></i>
          </a>
    ';
    $string ='
      <tr>
        <td></td>
        <td>'.$s.$btn.'</td>
        <td>'.$noOfSets.'</td>
        <td>'.$noOfUnit.'</td>
        <td>'.$length.'</td>
        <td>'.$size.'</td>
        <td>'.$noUnit.'</td>
        <td>'.$weight.'</td>
        <td>'.$quantity.'</td>
        <td>'.$itemUnit.'</td>
      </tr>
    ';
    echo $string;
    disp($proj_id,$r['id'],$level + 1);
  endwhile;
  }
  function deleteItemAndSub($p_id,$itemID){
    global $db;
    $q = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>$itemID));
    while( $r = $db->fetch_array($q) ):
        $item_id = $r['id'];
        $db->delete('dqc',array('proj_id'=>$p_id,'id'=>$item_id));
        deleteItemAndSub($p_id,$item_id);
    endwhile;
        $db->delete('dqc',array('proj_id'=>$p_id,'id'=>$itemID));
  }
  function updateItemNo($p_id,$itemParent){
    global $db;
    $itemParentItemNo = $db->getValue('dqc','itemNo',array('proj_id'=>$p_id,'id'=>$itemParent));
    $q = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>$itemParent),'ORDER BY id');
    $count=0;
    while($r = $db->fetch_array($q)):
      $count++;
      $newItemNo = ($itemParentItemNo) ? $itemParentItemNo. '.' . $count : $count;
      $db->update('dqc',array('itemNo'=>$newItemNo),array('proj_id'=>$p_id,'id'=>$r['id']));
      updateSubItem($p_id,$r['id']);
    endwhile;
  }

  function updateSubItem($p_id,$itemParent){
    global $db;
    $itemParentItemNo = $db->getValue('dqc','itemNo',array('proj_id'=>$p_id,'id'=>$itemParent));
    $q = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>$itemParent),'ORDER BY id');
    $count=0;
    while($r = $db->fetch_array($q)):
      $count++;
      $newItemNo = $itemParentItemNo. '.' . $count;
      $db->update('dqc',array('itemNo'=>$newItemNo),array('proj_id'=>$p_id,'id'=>$r['id']));
      updateSubItem($p_id,$r['id']);
    endwhile;
  }

  function deleteItem($p_id,$delItemID){
    global $db;
    $itemParent = $db->getValue('dqc','itemParent',array('proj_id'=>$p_id,'id'=>$delItemID));
    
    deleteItemAndSub($p_id,$delItemID);
    updateItemNo($p_id,$itemParent);

  }

  $p_id = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
  $delItemID = (isset($_REQUEST['delItemID']) && !empty($_REQUEST['delItemID']) ) ? functions::decode($_REQUEST['delItemID']) : 0;
  if( $delItemID && $p_id){
    deleteItem($p_id,$delItemID);
    functions::sendTo('dqc2.php?pid='.functions::encode($p_id).'&itemID='.functions::encode($itemID));
  }
  
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>DQC</title>
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
<style>
a {
    opacity: 0.4;
    filter: alpha(opacity=20); /* For IE8 and earlier */
}

a:hover {
    opacity: 1.0;
    filter: alpha(opacity=100); /* For IE8 and earlier */
}
</style>
<script>
    function delt(){
      if(confirm('Do you want to remove this Item?'))
        return true;
      else
        return false; 
    }
</script>
  </head>
  <body>

            <!-- body content: start here-->
            <div class="row-fluid">
              <form method="post">
                <div class="box span12">
                    <div class="box-header" data-original-title>
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>DQC of <?php echo $db->getValue('project','proj_name',array('proj_id'=>$p_id));?></h2>
                    </div>
                    <div class="box-content">
                      <ul class="nav tab-menu nav-tabs">
                        <li><a href="dqc3.php?pid=<?php echo functions::encode($p_id);?>">DQC 3</a></li>
                        <li class="active"><a href="#" style="opacity:.8">DQC 2</a></li>
                        <li><a href="dqc.php?pid=<?php echo functions::encode($p_id);?>">DQC 1</a></li>
                      </ul>
                    </div>
                    <div class="box-content">
                          <select name="selParentItem" id="selParentItem" style="width:350px;" onChange="itemSel(this.value)">
                            <option value="">-- All Item --</option>
                            <?php
                            $orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$p_id,'itemParent'=>0,'dqc_type'=>'2')) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo';
                              $q = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>0,'dqc_type'=>'2'),$orderBy);
                            while($r = $db->fetch_array($q)):
                            ?>
                              <option value="<?php echo functions::encode($r['id'])?>" <?php if($itemID==$r['id'])echo 'selected="selected"';?>><?php echo $r['itemNo'].'. '.$r['itemDesc']?></option>
                            <?php
                            endwhile;
                            ?>
                            </select>
                    <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;">
                      <tr>
                        <th width="6%" scope="col"><div align="center">Item No.</div></th>
                        <th width="33%" scope="col"><div align="left">Description</div></th>
                        <th width="7%" scope="col"><div align="left">No. Of Sets</div></th>
                        <th width="7%" scope="col"><div align="left">No. of Units</div></th>
                        <th width="7%" scope="col"><div align="left">Length</div></th>
                        <th width="14%" scope="col"><div align="left">Size(mm)</div></th>
                        <th width="7%" scope="col"><div align="left">No./Unit</div></th>
                        <th width="7%" scope="col"><div align="left">Unit Wt</div></th>
                        <th width="9%" scope="col"><div align="left">Quantity</div></th>
                        <th width="7%" scope="col"><div align="left">Unit</div></th>
                      </tr>
                      <?php
                          $orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$p_id,'dqc_type'=>'2','itemParent'=>0)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo'; 
                          if($itemID)
                            $qList = $db->select('dqc','*',array('id'=>$itemID,'proj_id'=>$p_id,'itemParent'=>0,'dqc_type'=>'2'),$orderBy);
                          else 
                            $qList = $db->select('dqc','*',array('proj_id'=>$p_id,'dqc_type'=>'2','itemParent'=>0),$orderBy);
                          while($rList = $db->fetch_array($qList)):
                      ?>
                      <tr>
                        <td><div align="center"><?php echo $rList['itemNo']?></div></td>
                        <td width="25%" >
                          <strong><?php echo $rList['itemDesc']?></strong>&nbsp;&nbsp;&nbsp;
                          <a id="AddSubItem<?php echo $rList['id']?>" class="thickbox" style="cursor:pointer" title="Add Sub Item" data-rel="tooltip" onclick="showThis(this.id,'dqc2_item_add.php?pid=<?php echo functions::encode($p_id);?>&itemID=<?php echo functions::encode($rList['id']);?>','Adding DQC Item')">
                            <i class="halflings-icon plus-sign"></i>
                          </a>
                          <a id="ManageItem<?php echo $rList['id']?>" class="thickbox" style="cursor:pointer" title="Manage Item" data-rel="tooltip" onclick="showThis(this.id,'dqc2_item_manage.php?pid=<?php echo functions::encode($p_id);?>&itemID=<?php echo functions::encode($rList['id']);?>','Adding DQC Item')">
                            <i class="halflings-icon pencil"></i>
                          </a>
                          <a id="deleteItem<?php echo $rList['id']?>" style="cursor:pointer" title="Delete Item" data-rel="tooltip" onClick="return delt()" href="dqc2.php?pid=<?php echo functions::encode($p_id);?>&delItemID=<?php echo functions::encode($rList['id']);?>">
                            <i class="halflings-icon minus-sign"></i>
                          </a>
                        </td>
                        <td><?php echo ($rList['noOfSets']) ? $rList['noOfSets'] : '';?></td>
                        <td><?php echo ($rList['noOfUnit']) ? $rList['noOfUnit'] : ''?></td>
                        <td><?php echo ($rList['length']) ? $rList['length'] : ''?></td>
                        <td><?php echo ($rList['size']) ? $rList['size'] : ''?></td>
                        <td><?php echo ($rList['noPerUnit']) ? $rList['noPerUnit'] : ''?></td>
                        <td><?php echo ($rList['weight']) ? $rList['weight'] : ''?></td>
                        <td><strong><?php echo ($rList['total']) ? $rList['total'] : '';?></strong></td>
                        <td><strong><?php echo ($rList['itemUnit']) ? $rList['itemUnit'] : '';?></strong></td>
                      </tr>
                    <?php 
                        disp($p_id,$rList['id'],1);
                    ?>
                    <?php
                    endwhile;?>
                      <tr>
                        <td colspan="10">&nbsp;</td>
                      </tr>
                      <tr>
                        <td><a id="addItem" title="Add Item" data-rel="tooltip" style="opacity:1.0" class="btn btn-mini thickbox" onclick="showThis(this.id,'dqc2_item_add.php?pid=<?php echo functions::encode($p_id);?>','Adding DQC Item')"><i class="halflings-icon white plus"></i>Add Item</a></td>
                        <td colspan="9">&nbsp;</td>
                      </tr>
                    </table>
                    <p>&nbsp;</p>
                  
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
<script>
function itemSel(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?pid=<?php echo functions::encode($p_id)?>&itemID="+PiEwgD}
</script>
<!-- end: JavaScript-->

</body>
</html>

