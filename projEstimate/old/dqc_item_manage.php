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

function disp($proj_id,$parentItem,$level,$currentItemID){
    global $db;
    $string='';

    $orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$proj_id,'dqc_type'=>'1','itemParent'=>$parentItem)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo';
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
        $db->update('dqc',array('itemNo'=>$newItemNo),array('proj_id'=>$p_id,'dqc_type'=>'1','id'=>$r['id']));
        updateSubItem($p_id,$r['id']);
    endwhile;
}

$count=0;
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;

$readOnly =  ( $db->getValue('dqc','count(*)',array('itemParent'=>$itemID,'dqc_type'=>'1')) ) ? "readonly" : "";

$txItemDesc = $db->getValue('dqc','itemDesc',array('proj_id'=>$p_id,'dqc_type'=>'1','id'=>$itemID));
$qItemDetail = $db->select('dqc','*',array('proj_id'=>$p_id,'dqc_type'=>'1','id'=>$itemID));
$r = $db->fetch_array($qItemDetail);
$itemBase=($r['itemBase']) ? $r['itemBase'] : '';
$txItemDesc=($r['itemDesc']) ? $r['itemDesc'] : '';
$txSets = ($r['noOfSets']) ? $r['noOfSets'] : '';
$txUnits = ($r['noOfUnit']) ? $r['noOfUnit'] : '';
$txLength = ($r['length']) ? $r['length'] : '';
$txWidth = ($r['width']) ? $r['width'] : '';
$txDepth = ($r['depth']) ? $r['depth'] : '';
$txQuantity = ($r['total']) ? $r['total'] : '';
$txUnit = ($r['itemUnit']) ? $r['itemUnit'] : '';
$itemParentTx = ($r['itemParent']) ? $r['itemParent'] : '';

$itemUnt='';
if($readOnly==0){
    $itemBase=$db->getValue('dqc','itemBase',array('id'=>$itemID,'proj_id'=>$p_id));
    $itemUnt = (empty($txUnit)) ? $db->getValue('dqc','DISTINCT itemUnit',array('itemBase'=>$itemBase),'ORDER BY itemUnit DESC') : $txUnit;
}
$qUnit = $db->select('dqc','DISTINCT itemUnit',array());
$namesUnit='';
while($rUnit=$db->fetch_array($qUnit)):
    $string = preg_replace("/'/",'"',$rUnit['itemUnit']);
    $namesUnit .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesUnit .= '"--"';

if( isset($_POST['btnSave']) ){
    $selParentItem = (isset($_POST['selParentItem']) && !empty($_POST['selParentItem']) ) ? $_POST['selParentItem'] : '';
    if($selParentItem!=$itemParentTx){
        $itemIDParent = $db->getValue('dqc','itemParent',array('id'=>$itemID,'proj_id'=>$p_id));
        $rootItemNo = $db->getValue('dqc','itemNo',array('id'=>$selParentItem));
        $subItemNo = $db->getValue('dqc','count(*)',array('itemParent'=>$selParentItem)) + 1;
        $newItemNo = $rootItemNo.'.'.$subItemNo;
        $db->update('dqc',array('itemNo'=>$newItemNo,'itemParent'=>$selParentItem),array('id'=>$itemID,'proj_id'=>$p_id));
        updateSubItem($p_id,$itemID); #updating Item's new subItem
        updateSubItem($p_id,$itemIDParent); #update Item's recent subItem
    }

    $itemDesc = (isset($_POST['txItemDesc']) && !empty($_POST['txItemDesc']) ) ? trim($_POST['txItemDesc']) : '';
    $noOfSets = (isset($_POST['txSets']) && !empty($_POST['txSets']) ) ? trim($_POST['txSets']) : 0;
    $noOfUnit = (isset($_POST['txUnits']) && !empty($_POST['txUnits']) ) ? trim($_POST['txUnits']) : 0; 
    $length = (isset($_POST['txLength']) && !empty($_POST['txLength']) ) ? trim($_POST['txLength']) : 0;
    $width = (isset($_POST['txWidth']) && !empty($_POST['txWidth']) ) ? trim($_POST['txWidth']) : 0;
    $depth = (isset($_POST['txDepth']) && !empty($_POST['txDepth']) ) ? trim($_POST['txDepth']) : 0;
    $itemUnit = (isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : '';
    $quantity = (isset($_POST['txQuantity']) && !empty($_POST['txQuantity']) ) ? trim($_POST['txQuantity']) : 0;
    #$quantity = round(($noOfSets * $noOfUnit * $length * $width * $depth),2);
    $db->update('dqc',array('itemDesc'=>$itemDesc,'noOfSets'=>$noOfSets,'noOfUnit'=>$noOfUnit,'length'=>$length,'width'=>$width,'depth'=>$depth,'total'=>$quantity,'itemUnit'=>$itemUnit),array('proj_id'=>$p_id,'dqc_type'=>'1','id'=>$itemID));
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
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Item Manage</h2>
        </div>
        <div class="box-content">
            <form method="post">
                <div>
                    <strong>Parent Item:</strong>
                    <select name="selParentItem" id="selParentItem" style="width:350px;">
                        <option value="">None</option>
                        <?php 
                        $orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$p_id,'id'=>$itemBase,'dqc_type'=>'1','itemParent'=>0)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo';
                        $q = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>0,'id'=>$itemBase,'dqc_type'=>'1'),'ORDER BY cast(itemNo as unsigned)');
                        while($r = $db->fetch_array($q)):
                        ?>
                        <option value="<?php echo $r['id']?>" <?php if($itemParentTx==$r['id'])echo 'selected="selected"';?>><?php echo $r['itemNo'].' '.$r['itemDesc']?></option>
                        <?php
                        disp($p_id,$r['id'],1,$itemParentTx);
                        endwhile;?>
                    </select>
                    &nbsp;&nbsp;&nbsp;<input type="checkbox" name="chkLess" id="chkLess" onClick="qtyCompute()" <?php if($txQuantity<0)echo 'checked="checked"';?>> Less?
                </div><br/><br/><br/>
                <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;">
                    <tr>
                        <th width="12%" scope="col"><div align="center">Item</div></th>
                        <th width="9%" scope="col"><div align="center">No. Of Sets</div></th>
                        <th width="9%" scope="col"><div align="center">No. of Units</div></th>
                        <th width="9%" scope="col"><div align="center">Length</div></th>
                        <th width="9%" scope="col"><div align="center">Width</div></th>
                        <th width="9%" scope="col"><div align="center">Depth</div></th>
                        <th width="9%" scope="col"><div align="center">Quantity</div></th>
                        <th width="10%" scope="col"><div align="center">Unit</div></th>
                        <th width="8%" scope="col"><div align="center">&nbsp;</div></th>
                    </tr>
                    <tr>
                        <td><div align="center"><input type="text" name="txItemDesc" id="txItemDesc" value="<?php echo htmlentities($txItemDesc);?>" style="width: 300px;" ></div></td>
                        <td><div align="center"><input type="text" name="txSets" id="txSets" value="<?php echo $txSets;?>" style="width: 50px;" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="text" name="txUnits" id="txUnits" value="<?php echo $txUnits;?>" style="width: 50px;" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="text" name="txLength" id="txLength" value="<?php echo $txLength;?>" style="width: 50px;" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="text" name="txWidth" id="txWidth" value="<?php echo $txWidth;?>" style="width: 50px;" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="text" name="txDepth" id="txDepth" value="<?php echo $txDepth;?>" style="width: 50px;" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="text" name="txQuantity" id="txQuantity" value="<?php echo $txQuantity;?>" style="width: 50px;" <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="text" name="txUnit" id="txUnit" value="<?php echo $txUnit;?>" style="width: 60px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesUnit;?>]' <?php echo $readOnly?>></div></td>
                        <td><div align="center"><input type="submit" name="btnSave" class="btn btn-medium btn-info" id="btnSave" value=" Save "></div></td>
                    </tr>
                </table><p>&nbsp;</p>
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
    var s = document.getElementById('txSets').value;
    var u = document.getElementById('txUnits').value;
    var l = document.getElementById('txLength').value;
    var w = document.getElementById('txWidth').value;
    var d = document.getElementById('txDepth').value;

    if( (s>0) && (u>0) && (l>0) && (w>0) && (d>0) ){
        if(document.getElementById('chkLess').checked){
            document.getElementById('txQuantity').value = parseFloat(s*u*l*w*d).toFixed(2) * -1;
            //document.getElementById('txQuantity').value = s*u*l*w*d * -1;
            document.getElementById('txUnit').value = "<?php echo $itemUnt?>";
        }
        else{
            document.getElementById('txQuantity').value = parseFloat(s*u*l*w*d).toFixed(2);
            //document.getElementById('txQuantity').value = s*u*l*w*d;
            document.getElementById('txUnit').value = "<?php echo $itemUnt?>";
        }
    }
    else{
        document.getElementById('txQuantity').value = 0;
        document.getElementById('txUnit').value = "";
    }
}
</script>
<!-- end: JavaScript-->
</body>
</html>