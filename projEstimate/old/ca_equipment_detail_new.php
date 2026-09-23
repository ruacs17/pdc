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

$ca_id=(isset($_REQUEST['ca_id']) && !empty($_REQUEST['ca_id']) ) ? functions::decode($_REQUEST['ca_id']) : 0;
$cag_id=(isset($_REQUEST['cag_id']) && !empty($_REQUEST['cag_id']) ) ? functions::decode($_REQUEST['cag_id']) : 0;
$delcad_id=(isset($_REQUEST['delcad_id']) && !empty($_REQUEST['delcad_id']) ) ? functions::decode($_REQUEST['delcad_id']) : 0;
if($delcad_id){
    $db->delete('cag_detail',array('cagd_id'=>$delcad_id));
}

if($ca_id && $cag_id==0){
    $q_ins = $db->insertPrint('ca_group',array('ca_id'=>$ca_id,'title'=>'Description Here','type'=>'equipment'));
    $db->query($q_ins);
    $insertID = $db->insert_id();
    functions::sendTo($_SERVER['PHP_SELF'].'?cag_id='.functions::encode($insertID));
}
if( isset($_POST['txTitle']) ){
    $title = trim($_POST['txTitle']);
    $db->update('ca_group',array('title'=>$title),array('cag_id'=>$cag_id));
    functions::sendTo($_SERVER['PHP_SELF'].'?cag_id='.functions::encode($cag_id));
}

function itemSubTotal($itemParent,&$sum){
    global $db;
    $q = $db->select('elm','*',array('itemParent'=>$itemParent));
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
    $q = $db->select('elm_rate','*',array('itemParent'=>$itemParent));
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
function disp($parentItem,$level){
    global $db;
    global $cag_id;
    $string='';
    $q = $db->select('elm_rate','*',array('itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned)');

    while($r = $db->fetch_array($q)):
        $itemNo = ($r['itemNo']) ? $r['itemNo'] : "";
        $itemDesc = ($r['itemDesc']) ? $r['itemDesc'] : "";
        $itemRate = ($r['itemRate']) ? functions::formatMoney($r['itemRate']) : '';
        $itemUnit = ($r['itemUnit']) ? $r['itemUnit'] : "";
        if( $db->getValue('elm_rate','count(*)',array('itemParent'=>$r['id'])) ){
            $itemDesc = ($r['itemDesc']) ? '<strong>'.$r['itemDesc'].'</strong>' : "";
        }
        $s = '';
        for($i=1; $i<=$level; $i++):
            $s .= '&nbsp;&nbsp;&nbsp;&nbsp;';
        endfor;
        $s .= $itemNo.'&nbsp;&nbsp;&nbsp;'.$itemDesc;
        $btn='';
        if( $itemRate ){
            $addSubItem = "showThis(this.id,'ca_equipment_detail_add.php?itemID=".functions::encode($r['id'])."&cag_id=".functions::encode($cag_id)."','Adding DQC Item')";
            $btn = '<a id="AddSubItem'.$r['id'].'" class="thickbox" style="cursor:pointer" title="Add Sub Item" data-rel="tooltip" onclick="'.$addSubItem.'"><i class="halflings-icon plus-sign"></i></a>';
        }
        $string ='
        <tr>
            <td>'.$s.'</td>
            <td>'.$itemUnit.'</td>
            <td>'.$itemRate.'</td>
            <td>'.$btn.'</td>
        </tr>';
        echo $string;
        disp($r['id'],$level + 1);
    endwhile;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Cost Analysis</title>
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
                <h2><i class="halflings-icon white edit"></i><span class="break"></span>Cost Analysis Equipment</h2>
            </div>
            <div class="box-content"><br>
                <div>Description: <input type="text" name="txTitle" id="txTitle" value="<?php echo $db->getValue('ca_group','title',array('cag_id'=>$cag_id));?>">&nbsp;&nbsp;<input type="submit" class="btn btn-info" name="btnSaveDesc" id="btnSaveDesc" value="Save"></div>
                <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;">
                    <tr bgcolor="#CCCCCC">
                        <th width="33%" scope="col">Equipment</th>
                        <th width="9%" scope="col"><div align="left">Capabilites</div></th>
                        <th width="9%" scope="col"><div align="left">No. of Units</div></th>
                        <th width="9%" scope="col"><div align="left">No. of Days</div></th>
                        <th width="9%" scope="col"><div align="left">Daily Rate</div></th>
                        <th width="9%" scope="col"><div align="left">Cost</div></th>
                        <th width="7%" scope="col"><div align="center">Options</div></th>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php 
                    $q_detail = $db->select('cag_detail','*',array('cag_id'=>$cag_id));
                    while($r_detail = $db->fetch_array($q_detail)):
                        $rate = $db->getValue('elm_rate','itemRate',array('id'=>$r_detail['element_id']));
                        $elementDesc = $db->getValue('elm_rate','itemDesc',array('id'=>$r_detail['element_id']));
                    ?>
                    <tr>
                        <td><?php echo $elementDesc?></td>
                        <td><?php echo $db->getValue('el_capability','concat(slow_manhour," ",itemUnit)',array('itemDesc'=>$elementDesc))?></td>
                        <td><?php echo functions::formatMoney($r_detail['unitMen'])?></td>
                        <td><?php echo functions::formatMoney($r_detail['day'])?></td>
                        <td><?php echo functions::formatMoney($rate)?></td>
                        <td><?php echo functions::formatMoney($rate * $r_detail['unitMen'] * $r_detail['day'])?></td>
                        <td>
                            <div align="center">
                                <a id="ManageItem<?php echo $r_detail['cagd_id']?>" class="thickbox" style="cursor:pointer" title="Manage Item" data-rel="tooltip" onclick="showThis(this.id,'ca_equipment_detail_add.php?cagd_id=<?php echo functions::encode($r_detail['cagd_id']);?>&cag_id=<?php echo functions::encode($r_detail['cag_id']);?>','Adding Item')"><i class="halflings-icon pencil"></i></a>
                                <a id="deleteItem<?php echo $r_detail['cagd_id']?>" style="cursor:pointer" title="Delete Item" data-rel="tooltip" onClick="return delt()" href="<?php echo $_SERVER['PHP_SELF']?>?cag_id=<?php echo functions::encode($cag_id);?>&delcad_id=<?php echo functions::encode($r_detail['cagd_id']);?>"><i class="halflings-icon minus-sign"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile;?>
                    </table>
                    <br><br><br>
                    <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;">
                        <tr bgcolor="#CCCCCC">
                            <th width="65%" scope="col"><div align="center">Equipment</div></th>
                            <th width="7%" scope="col"><div align="left">Unit</div></th>
                            <th width="7%" scope="col"><div align="left">Rate</div></th>
                            <th width="5%" scope="col">&nbsp;</th>
                        </tr>
                        <?php
                        $qType = $db->select('equipment','DISTINCT type',array(),'ORDER BY type');
                        while($rType = $db->fetch_array($qType)):
                        ?>
                        <tr>
                            <td><strong><?php echo $rType['type']?></strong></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                            <?php
                            $qEquip = $db->select('equipment','*',array('type'=>$rType['type']));
                            while($rEquip = $db->fetch_array($qEquip)):
                            ?>
                        <tr>
                            <td style="padding-left: 50px;"><?php echo $rEquip['name']?></td>
                            <td><?php echo $rEquip['unit']?></td>
                            <td><?php echo functions::formatMoney($rEquip['rate'])?></td>
                            <td>
                                <?php $addSubItem = "showThis(this.id,'ca_equipment_detail_add.php?itemID=".functions::encode($rEquip['equip_id'])."&cag_id=".functions::encode($cag_id)."','Adding DQC Item')";?>
                                <a id="AddSubItem'<?php echo $rEquip['equip_id']?>" class="thickbox" style="cursor:pointer" title="Add Sub Item" data-rel="tooltip" onclick="<?php echo $addSubItem?>">
                                    <i class="halflings-icon plus-sign"></i>
                                </a>
                            </td>
                        </tr>
                      <?php
                            endwhile;
                      ?>
                      <?php endwhile;#while($rType = $db->fetch_array($qType)):?>
                      <tr>
                        <td colspan="4">&nbsp;</td>
                      </tr>
                    </table>






                    <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;">
                      <tr bgcolor="#CCCCCC">
                        <th width="65%" scope="col"><div align="center">Equipment</div></th>
                        <th width="7%" scope="col"><div align="left">Unit</div></th>
                        <th width="7%" scope="col"><div align="left">Rate</div></th>
                        <th width="5%" scope="col">&nbsp;</th>
                      </tr>
                      <?php
                            $qList = $db->select('elm_rate','*',array('itemParent'=>0,'itemDesc'=>'Equipment'),'ORDER BY cast(itemNo as unsigned)');
                          while($rList = $db->fetch_array($qList)):
                      ?>
                      <tr>
                        <td width="25%" ><strong><?php echo $rList['itemDesc']?></strong></td>
                        <td><?php echo ($rList['itemUnit']) ? $rList['itemUnit'] : ''?></td>
                        <td><?php echo ($rList['itemRate']) ? $rList['itemRate'] : ''?></td>
                        <td>&nbsp;</td>
                      </tr>
                    <?php 
                        disp($rList['id'],1);
                    ?>
                    <?php
                    endwhile;?>
                      <tr>
                        <td colspan="4">&nbsp;</td>
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
<!-- end: JavaScript-->

</body>
</html>

