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
$count=0;

$itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;
if($itemID=='jY'){
    $itemID=0;
    unset($_SESSION['itemID']);
}
elseif($itemID)
    $_SESSION['itemID']=$itemID;

$itemID=(isset($_SESSION['itemID'])) ? $_SESSION['itemID'] : 0;

function itemSubTotal($itemParent,&$sum){
    global $db;
    $q = $db->select('dqc','*',array('itemParent'=>$itemParent));
    while($r = $db->fetch_array($q)):
        $sum += is_numeric($r['total']) ? $r['total'] : 0;
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
    $q = $db->select('dqc','*',array('proj_id'=>$proj_id,'itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned)');

    while($r = $db->fetch_array($q)):
        $quantity='';
        $itemUnit='';
        $itemNo = ($r['itemNo']) ? $r['itemNo'] : "";

        $itemDesc = ($r['itemDesc']) ? '<strong>'.$r['itemDesc'].'</strong>' : "";

        if($level<=2){
            $it = itemTotal($r['id']);
            if( $it )
                $it = ($it<0) ? $it * -1 : $it;
            else
                $it = '';

            $quantity = ($r['total']) ? '<strong>'.functions::formatMoney($r['total']).'</strong>' : '<strong>'.functions::formatMoney($it).'</strong>';
            $itemUnit = ($r['itemUnit']) ? '<strong>'.$r['itemUnit'].'</strong>' : '<strong>'.itemUnitName($r['id']).'</strong>';

            if($level==2){
                $quantity = ($r['total']) ? functions::formatMoney($r['total']) : functions::formatMoney($it);
                $itemUnit = ($r['itemUnit']) ? $r['itemUnit'] : itemUnitName($r['id']);
                $itemDesc = ($r['itemDesc']) ? $r['itemDesc'] : "";     
            }

            $s = '';
            for($i=1; $i<=$level; $i++):
                $s .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            endfor;

            $opacity = ( $db->getValue('cost_analysis','count(*)',array('sub_item'=>$r['id'])) ) ? 'opacity:1' : "";
            $s .= $itemNo.'&nbsp;&nbsp;&nbsp;&nbsp;'.$itemDesc;
            $deleteItem = "ca_list.php?pid=".functions::encode($proj_id)."&itemID=".functions::encode($r['id']);
            $btn = '&nbsp;&nbsp;&nbsp;
                  <a id="AddPow'.$r['id'].'" style="cursor:pointer;'.$opacity.'" title="Manage POW" data-rel="tooltip" href="'.$deleteItem.'">
                    <i class="halflings-icon plus-sign"></i>
                  </a>
            ';
            $string ='
              <tr>
                  <td></td>
                  <td>'.$s.$btn.'</td>
                  <td>'.$quantity.'</td>
                  <td>'.$itemUnit.'</td>
                  <td></td>
              </tr>
            ';
            echo $string;
            disp($proj_id,$r['id'],$level + 1);
        }
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
    functions::sendTo('dqc.php?pid='.functions::encode($p_id).'&itemID='.functions::encode($itemID));
}
#delete sub item without group elements
$db->query('delete from cost_analysis where ca_id not in (select ca_id from ca_group)');
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
        opacity: 0.2;
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
                <h2><i class="halflings-icon white edit"></i><span class="break"></span>DQC</h2>
            </div>
            <div class="box-content">
                <ul class="nav tab-menu nav-tabs">
                    <li><a href="ca_list.php?pid=<?php echo functions::encode($p_id);?>">Cost Analysis</a></li>
                    <li class="active"><a href="#" style="opacity:.8">DQC</a></li>
                </ul>
            </div>
            <div class="box-content">
                <div>Project: <strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$p_id));?></strong></div><br><br>
                <select name="selParentItem" id="selParentItem" style="width:350px;" onChange="itemSel(this.value)">
                    <option value="all">-- All Item --</option>
                    <?php
                    $orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$p_id,'itemParent'=>0)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo';
                    $q = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>0),$orderBy);
                    while($r = $db->fetch_array($q)):?>
                    <option value="<?php echo functions::encode($r['id'])?>" <?php if($itemID==$r['id'])echo 'selected="selected"';?>><?php echo $r['itemNo'].'. '.$r['itemDesc']?></option>
                    <?php
                    endwhile;?>
                </select>
                <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;">
                    <tr>
                        <th width="6%" scope="col"><div align="center">Item No.</div></th>
                        <th width="33%" scope="col"><div align="left">Description</div></th>
                        <th width="9%" scope="col"><div align="left">Quantity</div></th>
                        <th width="10%" scope="col"><div align="left">Unit</div></th>
                        <th width="9%">&nbsp;</th>
                    </tr>
                    <?php
                    $orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$p_id,'itemParent'=>0)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo'; 
                    if($itemID)
                        $qList = $db->select('dqc','*',array('id'=>$itemID,'proj_id'=>$p_id),$orderBy);
                    else 
                        $qList = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>0),$orderBy);
                    while($rList = $db->fetch_array($qList)):
                    ?>
                    <tr>
                        <td><div align="center"><?php echo $rList['itemNo']?></div></td>
                        <td width="25%" ><strong><?php echo $rList['itemDesc']?></strong></td>
                        <td><strong><?php echo ($rList['total']) ? $rList['total'] : '';?></strong></td>
                        <td><strong><?php echo ($rList['itemUnit']) ? $rList['itemUnit'] : ''?></strong></td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php 
                        disp($p_id,$rList['id'],1);
                    ?>
                    <?php
                    endwhile;?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                </table><p>&nbsp;</p>
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