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

  function disp($proj_id,$parentItem,$level,$currentItemID){
  global $db;
  $string='';

  $orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$proj_id,'itemParent'=>$parentItem)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo';
  $q = $db->select('dqc','*',array('proj_id'=>$proj_id,'itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned)');
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
    disp($proj_id,$r['id'],$level + 1,$currentItemID);
  endwhile;
  }

  function updateSubItem($p_id,$itemID){
    global $db;
    $itemParentItemNo = $db->getValue('dqc','itemNo',array('proj_id'=>$p_id,'id'=>$itemID));
    $q = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>$itemID),'ORDER BY id');
    $count=0;
    while($r = $db->fetch_array($q)):
      $count++;
      $newItemNo = $itemParentItemNo. '.' . $count;
      $db->update('dqc',array('itemNo'=>$newItemNo),array('proj_id'=>$p_id,'id'=>$r['id']));
      updateSubItem($p_id,$r['id']);
    endwhile;
  }



  $count=0;
  $p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
  $itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;

  $readOnly =  ( $db->getValue('dqc','count(*)',array('itemParent'=>$itemID)) ) ? "readonly" : "";


  $txItemDesc = $db->getValue('dqc','itemDesc',array('proj_id'=>$p_id,'id'=>$itemID));
  $qItemDetail = $db->select('dqc','*',array('proj_id'=>$p_id,'id'=>$itemID));
  $r = $db->fetch_array($qItemDetail);
  $itemBase=($r['itemBase']) ? $r['itemBase'] : '';
  $txItemDesc=($r['itemDesc']) ? $r['itemDesc'] : '';
  $txSets = ($r['noOfSets']) ? $r['noOfSets'] : '';
  $txUnits = ($r['noOfUnit']) ? $r['noOfUnit'] : '';
  $txSize = ($r['size']) ? $r['size'] : "";
  $txWeight = ($r['weight']) ? $r['weight'] : "";
  $txLength = ($r['length']) ? $r['length'] : '';
  $txRequired = ($r['required']) ? $r['required'] : "";
  $txTotalRequired = ($r['totalRequired']) ? $r['totalRequired'] : "";
  $txNoOfCutLength = ($r['noOfCutLength']) ? $r['noOfCutLength'] : "";
  $txQtyFrom = ($r['lengthFrom']) ? $r['lengthFrom'] : "";
  $txQuantity = ($r['lengthQuantity']) ? $r['lengthQuantity'] : '';
  $txTotalWt = ($r['total']) ? $r['total'] : ""; 
  
  $itemParentTx = ($r['itemParent']) ? $r['itemParent'] : '';

  $itemUnt='';
  if($readOnly==0){
    $itemBase=$db->getValue('dqc','itemBase',array('id'=>$itemID,'proj_id'=>$p_id));
    $itemUnt = (empty($txUnit)) ? $db->getValue('dqc','DISTINCT itemUnit',array('itemBase'=>$itemBase),'ORDER BY itemUnit DESC') : $txUnit;
  }

  if( isset($_POST['btnSave']) ){
    $selParentItem = (isset($_POST['selParentItem']) && !empty($_POST['selParentItem']) ) ? $_POST['selParentItem'] : '';
    if($selParentItem!=$itemParentTx){
        $itemIDParent = $db->getValue('dqc','itemParent',array('id'=>$itemID,'proj_id'=>$p_id));
        $rootItemNo = $db->getValue('dqc','itemNo',array('id'=>$selParentItem));
        $subItemNo = $db->getValue('dqc','count(*)',array('itemParent'=>$selParentItem)) + 1;
        $newItemNo = ($rootItemNo) ? $rootItemNo.'.'.$subItemNo : $db->getValue('dqc','count(*)',array('proj_id'=>$p_id,'itemParent'=>0));
        $db->update('dqc',array('itemNo'=>$newItemNo,'itemParent'=>$selParentItem),array('id'=>$itemID,'proj_id'=>$p_id));
        updateSubItem($p_id,$itemID); #updating Item's new subItem
        updateSubItem($p_id,$itemIDParent); #update Item's recent subItem
    }

    $itemDesc = (isset($_POST['txItemDesc']) && !empty($_POST['txItemDesc']) ) ? trim($_POST['txItemDesc']) : '';
    $noOfSets = (isset($_POST['txSets']) && !empty($_POST['txSets']) ) ? trim($_POST['txSets']) : 0;
    $noOfUnit = (isset($_POST['txUnits']) && !empty($_POST['txUnits']) ) ? trim($_POST['txUnits']) : 0; 
    $size = (isset($_POST['txSize']) && !empty($_POST['txSize']) ) ? trim($_POST['txSize']) : 0;
    $weight = (isset($_POST['txWeight']) && !empty($_POST['txWeight']) ) ? trim($_POST['txWeight']) : 0;
    $length = (isset($_POST['txLength']) && !empty($_POST['txLength']) ) ? trim($_POST['txLength']) : 0;
    $required = (isset($_POST['txRequired']) && !empty($_POST['txRequired']) ) ? trim($_POST['txRequired']) : 0;
    $totalRequired = (isset($_POST['txTotalRequired']) && !empty($_POST['txTotalRequired']) ) ? trim($_POST['txTotalRequired']) : 0;
    $noOfCutLength = (isset($_POST['txNoOfCutLength']) && !empty($_POST['txNoOfCutLength']) ) ? trim($_POST['txNoOfCutLength']) : 0;
    $qtyFrom = (isset($_POST['txQtyFrom']) && !empty($_POST['txQtyFrom']) ) ? trim($_POST['txQtyFrom']) : 0;
    $quantity = (isset($_POST['txQuantity']) && !empty($_POST['txQuantity']) ) ? trim($_POST['txQuantity']) : 0;
    $totalWt = (isset($_POST['txTotalWt']) && !empty($_POST['txTotalWt']) ) ? trim($_POST['txTotalWt']) : 0;
    $db->update('dqc',array('itemDesc'=>$itemDesc,'noOfSets'=>$noOfSets,'noOfUnit'=>$noOfUnit,'size'=>$size,'weight'=>$weight,'length'=>$length,'required'=>$required,'totalRequired'=>$totalRequired,'noOfCutLength'=>$noOfCutLength,'lengthFrom'=>$qtyFrom,'lengthQuantity'=>$quantity,'total'=>$totalWt),array('proj_id'=>$p_id,'dqc_type'=>'3','id'=>$itemID));
    functions::sendTo($_SERVER['PHP_SELF'].'?pid='.functions::encode($p_id).'&itemID='.functions::encode($itemID));
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
                            <option value="0">None</option>
                            <?php
                            $orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$p_id,'id'=>$itemBase,'dqc_type'=>'3','itemParent'=>0)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo';
                            $q = $db->select('dqc','*',array('proj_id'=>$p_id,'dqc_type'=>'3','id'=>$itemBase,'itemParent'=>0),'ORDER BY cast(itemNo as unsigned)');
                            while($r = $db->fetch_array($q)):
                            ?>
                              <option value="<?php echo $r['id']?>" <?php if($itemParentTx==$r['id'])echo 'selected="selected"';?>><?php echo $r['itemNo'].' '.$r['itemDesc']?></option>
                            <?php
                              disp($p_id,$r['id'],1,$itemParentTx);
                            endwhile;
                            ?>
                            </select>
                        </div><br/><br/><br/>
                    <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;">
                      <tr>
                        <th width="12%" scope="col"><div align="center">Item</div></th>
                        <th width="9%" scope="col"><div align="center">No. Of Sets</div></th>
                        <th width="9%" scope="col"><div align="center">No. of Units</div></th>
                        <th width="9%" scope="col"><div align="center">Size</div></th>
                        <th width="9%" scope="col"><div align="center">Unit Wt.</div></th>
                        <th width="9%" scope="col"><div align="center">Bar Length</div></th>
                        <th width="9%" scope="col"><div align="center">Required Number</div></th>
                        <th width="9%" scope="col"><div align="center">Total Reqd No.</div></th>
                        <th width="9%" scope="col"><div align="center">No. of Cut</div></th>
                        <th width="9%" scope="col"><div align="center">From</div></th>
                        <th width="9%" scope="col"><div align="center">Qty</div></th>
                        <th width="9%" scope="col"><div align="center">Total Wt. (kgs)</div></th>
                        <th width="8%" scope="col"><div align="center">&nbsp;</div></th>
                      </tr>
                      <tr>
                        <td><div align="center"><input type="text" name="txItemDesc" id="txItemDesc" value="<?php echo htmlentities($txItemDesc);?>" style="width: 280px;" ></div></td>
                        <td><div align="center"><input type="text" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" style="width: 40px;" name="txSets" id="txSets" value="<?php echo $txSets;?>" <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="text" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" style="width: 40px;" name="txUnits" id="txUnits" value="<?php echo $txUnits;?>" <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="text" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" style="width: 40px;" name="txSize" id="txSize" value="<?php echo $txSize;?>" <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="text" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" style="width: 40px;" name="txWeight" id="txWeight" value="<?php echo $txWeight;?>"<?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="text" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" style="width: 40px;" name="txLength" id="txLength" value="<?php echo $txLength;?>" <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="text" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" style="width: 40px;" name="txRequired" id="txRequired" value="<?php echo $txRequired;?>" <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="text" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" style="width: 60px;" name="txTotalRequired" id="txTotalRequired" value="<?php echo $txTotalRequired;?>" readonly></div></td>
                        <td><div align="center"><input type="text" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" style="width: 60px;" name="txNoOfCutLength" id="txNoOfCutLength" value="<?php echo $txNoOfCutLength;?>" readonly></div></td>
                        <td><div align="center"><input type="text" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" style="width: 40px;" name="txQtyFrom" id="txQtyFrom" value="<?php echo $txQtyFrom;?>" <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="text" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" style="width: 60px;" name="txQuantity" id="txQuantity" value="<?php echo $txQuantity;?>" readonly></div></td>
                        <td><div align="center"><input type="text" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" style="width: 60px;" name="txTotalWt" id="txTotalWt" value="<?php echo $txTotalWt;?>" <?php echo $readOnly?>></div></td>
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
<script>
  function qtyCompute(){
    /*var s = document.getElementById('txSets').value;
    var u = document.getElementById('txUnits').value;
    var sz = document.getElementById('txUnits').value;
    var wt = document.getElementById('txWeight').value;
    var l = document.getElementById('txLength').value;
    var r = document.getElementById('txRequired').value;
    var tr = document.getElementById('txTotalRequired').value;
    var c = document.getElementById('txNoOfCutLength').value;
    var q = document.getElementById('txQtyFrom').value;
    var tw = document.getElementById('totalWt').value;
    
    if( (s>0) && (u>0) && (l>0) && (n>0) && (w>0) )
      document.getElementById('txQuantity').value = parseFloat(s*u*l*n*w).toFixed(2);
    else
      document.getElementById('txQuantity').value = 0;
    */
    totalRequired();
    totalNoCut();
    totalQty();
    totalWt();

  }
  function totalRequired(){
    var s = document.getElementById('txSets').value;
    var u = document.getElementById('txUnits').value;
    var r = document.getElementById('txRequired').value;
    if( (s>0) && (u>0) && (r>0) )
      document.getElementById('txTotalRequired').value = parseFloat(s*u*r).toFixed(2);
    else
      document.getElementById('txTotalRequired').value = 0;
  }
  function totalNoCut(){
    var l = document.getElementById('txLength').value;
    var q = document.getElementById('txQtyFrom').value;
    if( (l>0) && (q>0) )
      document.getElementById('txNoOfCutLength').value = parseFloat(q/l).toFixed(2);
    else
      document.getElementById('txNoOfCutLength').value = 0;
  }
  function totalQty(){
    var tr = document.getElementById('txTotalRequired').value;
    var c = document.getElementById('txNoOfCutLength').value;
    if( (tr>0) && (c>0) )
      document.getElementById('txQuantity').value = parseFloat(tr/c).toFixed(2);
    else
      document.getElementById('txQuantity').value = 0;
  }
  function totalWt(){
    var wt = document.getElementById('txWeight').value;
    var qf = document.getElementById('txQtyFrom').value;
    var q = document.getElementById('txQuantity').value;
    if( (wt>0) && (qf>0) && (q>0) ){
      document.getElementById('txTotalWt').value = parseFloat(wt*qf*q).toFixed(2);
      document.getElementById('txUnit').value = "<?php echo $itemUnt?>";
    }else{
      document.getElementById('txTotalWt').value = 0;
      document.getElementById('txUnit').value = "";
    }
  }
</script>
<!-- end: JavaScript-->

</body>
</html>

