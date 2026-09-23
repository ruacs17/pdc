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
    global $db;
    $q = $db->select('dqc','*',array('id'=>$itemParent));
    while($r = $db->fetch_array($q)):
        $sum += is_numeric($r['total']) ? $r['total'] : 0;
    endwhile;
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
    if( empty($unit) ){
        $unit = $db->getValue('dqc','itemUnit',array('id'=>$itemParent));
    }
    return $unit;
}

$count=0;
$subItemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;
$p_id = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0; 
$delGroup = (isset($_REQUEST['delcag_id']) && !empty($_REQUEST['delcag_id']) ) ? functions::decode($_REQUEST['delcag_id']) : 0; 
if($delGroup){
    $db->delete('ca_group',array('cag_id'=>$delGroup));
}
if( $subItemID ){
    if($p_id && $subItemID){
        if( $db->getValue('cost_analysis','count(*)',array('proj_id'=>$p_id,'sub_item'=>$subItemID))==0 ){
            $db->insert('cost_analysis',array('proj_id'=>$p_id,'sub_item'=>$subItemID));
        }
    }
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
    a{
        opacity: 0.4;
        filter: alpha(opacity=20); /* For IE8 and earlier */
    }
    a:hover{
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
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>POW</h2>
        </div>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li class="active"><a href="#" style="opacity:.8">Cost Analysis</a></li>
                <li><a href="ca_dqc.php?pid=<?php echo functions::encode($p_id);?>">DQC</a></li>
            </ul>
        </div>
        <div class="box-content">
            <table width="99%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="12%">Project</td>
                    <td width="2%">&nbsp;</td>
                    <td width="86%"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$p_id));?></td>
                </tr>
                <tr>
                    <td>Location</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>Owner</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <?php
                if($subItemID)
                    $qca = $db->select('cost_analysis','*',array('sub_item'=>$subItemID));
                else
                    $qca = $db->select('cost_analysis','*',array('proj_id'=>$p_id),'ORDER BY sub_item'); 
                while($rca = $db->fetch_array($qca)):
                    $subItemID=$rca['sub_item'];
                    $itemQ = $db->select('dqc','*',array('id'=>$subItemID));
                    $itemDetail = $db->fetch_array($itemQ);
                    $itemBaseID = (isset($itemDetail['itemBase'])) ? $itemDetail['itemBase'] : '';
                    $itemBase = $db->getValue('dqc','itemNo',array('id'=>$itemBaseID));
                    $itemBaseDesc = $db->getValue('dqc','itemDesc',array('id'=>$itemBaseID));
                    $itemNo = (isset($itemDetail['itemBase'])) ? $itemDetail['itemNo'] : '';
                    $itemNoDesc = (isset($itemDetail['itemBase'])) ? $itemDetail['itemDesc'] : '';
                    $estimatedQuantity = itemTotal($subItemID);
                    $itemUnit = itemUnitName($subItemID);

                    $ca_id = $db->getValue('cost_analysis','ca_id',array('proj_id'=>$p_id,'sub_item'=>$subItemID));

                    #deleting no cag_detail
                    $qGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id));
                    while($rGroup = $db->fetch_array($qGroup)):
                        $countDetail=0;
                        $qDetail = $db->select('cag_detail','*',array('cag_id'=>$rGroup['cag_id']));
                        while($rDetail = $db->fetch_array($qDetail)):
                            $countDetail++;
                        endwhile;
                        if($countDetail==0)
                            $db->delete('ca_group',array('cag_id'=>$rGroup['cag_id'],'ca_id'=>$ca_id));
                    endwhile;
                ?>
                <tr>
                    <td height="50px" colspan="3">&nbsp;</td>
                </tr>
                <tr bgcolor="#FF0000">
                    <td height="2px" colspan="3"></td>
                </tr>
                <tr>
                    <td height="50px" colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td>Item No</td>
                    <td>&nbsp;</td>
                    <td><?php echo $itemBase;?></td>
                </tr>
                <tr>
                    <td>Item of Work</td>
                    <td>&nbsp;</td>
                    <td><?php echo $itemBaseDesc;?></td>
                </tr>
                <tr>
                    <td>Sub-Item No.</td>
                    <td>&nbsp;</td>
                    <td><?php echo $itemNo;?></td>
                </tr>
                <tr>
                    <td>Sub-Item of Work</td>
                    <td>&nbsp;</td>
                    <td><?php echo $itemNoDesc;?></td>
                </tr>
                <tr>
                    <td>Estimated Quantities</td>
                    <td>&nbsp;</td>
                    <td><?php echo $estimatedQuantity?></td>
                </tr>
                <tr>
                    <td>Unit</td>
                    <td>&nbsp;</td>
                    <td><?php echo $itemUnit?></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3" bgcolor="#F7F7F7">
                        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;">
                            <tr bgcolor="#CCCCCC">
                                <th width="33%" scope="col">Equipment</th>
                                <th width="9%" scope="col"><div align="left">Capabilites</div></th>
                                <th width="9%" scope="col"><div align="left">No. of Units</div></th>
                                <th width="9%" scope="col"><div align="left">No. of Days</div></th>
                                <th width="9%" scope="col"><div align="left">Daily Rate</div></th>
                                <th width="9%" scope="col"><div align="left">Cost</div></th>
                            </tr>
                            <?php
                            $qEquipmentGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id,'type'=>'equipment'));
                            while($rEquipmentGroup = $db->fetch_array($qEquipmentGroup)):
                                $countDetail=0;
                            ?>
                            <tr>
                                <td colspan="6">&nbsp;</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong><?php echo $rEquipmentGroup['title']?>&nbsp;&nbsp;</strong>
                                    <a id="ManageItem<?php echo $rEquipmentGroup['cag_id']?>" class="thickbox" style="cursor:pointer" title="Manage Item" data-rel="tooltip" onclick="showThis(this.id,'ca_equipment_detail.php?pid=<?php echo functions::encode($p_id);?>&itemID=<?php echo functions::encode($subItemID);?>&cag_id=<?php echo functions::encode($rEquipmentGroup['cag_id']);?>','Adding DQC Item')"><i class="halflings-icon pencil"></i></a>
                                    <a id="deleteItem<?php echo $rEquipmentGroup['cag_id']?>" style="cursor:pointer" title="Delete Item" data-rel="tooltip" onClick="return delt()" href="<?php echo $_SERVER['PHP_SELF']?>?pid=<?php echo functions::encode($p_id);?>&itemID=<?php echo functions::encode($subItemID);?>&delcag_id=<?php echo functions::encode($rEquipmentGroup['cag_id']);?>"><i class="halflings-icon minus-sign"></i></a>
                                </td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                                <?php
                                $qEquipDetail = $db->select('cag_detail','*',array('cag_id'=>$rEquipmentGroup['cag_id']));
                                while($rEquipDetail = $db->fetch_array($qEquipDetail)):
                                    $rate = $db->getValue('elm_rate','itemRate',array('id'=>$rEquipDetail['element_id']));
                                    $elementDesc = $db->getValue('elm_rate','itemDesc',array('id'=>$rEquipDetail['element_id']));
                                ?>
                            <tr>
                                <td>&nbsp;&nbsp;&nbsp;<?php echo $elementDesc;?></td>
                                <td><?php echo $db->getValue('el_capability','concat(slow_manhour," ",itemUnit)',array('itemDesc'=>$elementDesc))?></td>
                                <td><?php echo functions::formatMoney($rEquipDetail['unitMen'])?></td>
                                <td><?php echo functions::formatMoney($rEquipDetail['day'])?></td>
                                <td><?php echo functions::formatMoney($rate)?></td>
                                <td><?php echo functions::formatMoney($rate * $rEquipDetail['unitMen'] * $rEquipDetail['day'])?></td>
                            </tr>
                            <?php 
                                endwhile;
                            endwhile;?>
                            <tr>
                                <td colspan="6">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="4">&nbsp;</td>
                                <td>Total Cost</td>
                                <td><strong><?php echo functions::formatMoney($db->getValue('cost_analysis_report','sum(cost)',array('sub_item'=>$subItemID,'type'=>'equipment')))?></strong></td>
                            </tr>
                            <tr>
                                <td><a id="AddEquipment<?php echo $subItemID?>" title="Add Equipment" data-rel="tooltip" style="opacity:1" class="btn btn-mini thickbox" onclick="showThis(this.id,'ca_equipment_detail.php?ca_id=<?php echo functions::encode($ca_id)?>','Add Equipment Detail')"><i class="halflings-icon white plus"></i>Add Equipment Item</a></td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="6" height="40px">&nbsp;</td>
                            </tr>
                            <tr bgcolor="#CCCCCC">
                                <th width="33%" scope="col">Labor</th>
                                <th width="9%" scope="col"><div align="left">Capabilites</div></th>
                                <th width="9%" scope="col"><div align="left">No. of Units</div></th>
                                <th width="9%" scope="col"><div align="left">No. of Days</div></th>
                                <th width="9%" scope="col"><div align="left">Daily Rate</div></th>
                                <th width="9%" scope="col"><div align="left">Cost</div></th>
                            </tr>
                            <?php
                            $qLaborGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id,'type'=>'labor'));
                            while($rLaborGroup = $db->fetch_array($qLaborGroup)):
                                $countDetail=0;
                            ?>
                            <tr>
                                <td colspan="6">&nbsp;</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong><?php echo $rLaborGroup['title']?>&nbsp;&nbsp;</strong>
                                    <a id="ManageItem<?php echo $rLaborGroup['cag_id']?>" class="thickbox" style="cursor:pointer" title="Manage Item" data-rel="tooltip" onclick="showThis(this.id,'ca_labor_detail.php?pid=<?php echo functions::encode($p_id);?>&itemID=<?php echo functions::encode($subItemID);?>&cag_id=<?php echo functions::encode($rLaborGroup['cag_id']);?>','Adding Item')"><i class="halflings-icon pencil"></i></a>
                                    <a id="deleteItem<?php echo $rLaborGroup['cag_id']?>" style="cursor:pointer" title="Delete Item" data-rel="tooltip" onClick="return delt()" href="<?php echo $_SERVER['PHP_SELF']?>?pid=<?php echo functions::encode($p_id);?>&itemID=<?php echo functions::encode($subItemID);?>&delcag_id=<?php echo functions::encode($rLaborGroup['cag_id']);?>"><i class="halflings-icon minus-sign"></i></a>
                                </td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                                <?php
                                $qLaborDetail = $db->select('cag_detail','*',array('cag_id'=>$rLaborGroup['cag_id']));
                                while($rLaborDetail = $db->fetch_array($qLaborDetail)):
                                    $rate = $db->getValue('elm_rate','itemRate',array('id'=>$rLaborDetail['element_id']));
                                    $elementDesc = $db->getValue('elm_rate','itemDesc',array('id'=>$rLaborDetail['element_id']));
                                ?>
                            <tr>
                                <td>&nbsp;&nbsp;&nbsp;<?php echo $elementDesc;?></td>
                                <td><?php echo $db->getValue('el_capability','concat(slow_manhour," ",itemUnit)',array('itemDesc'=>$elementDesc))?></td>
                                <td><?php echo functions::formatMoney($rLaborDetail['unitMen'])?></td>
                                <td><?php echo functions::formatMoney($rLaborDetail['day'])?></td>
                                <td><?php echo functions::formatMoney($rate)?></td>
                                <td><?php echo functions::formatMoney($rate * $rLaborDetail['unitMen'] * $rLaborDetail['day'])?></td>
                            </tr>
                            <?php 
                                endwhile;
                            endwhile;?>
                            <tr>
                                <td colspan="6">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="4">&nbsp;</td>
                                <td>Total Cost</td>
                                <td><strong><?php echo functions::formatMoney($db->getValue('cost_analysis_report','sum(cost)',array('sub_item'=>$subItemID,'type'=>'labor')))?></strong></td>
                            </tr>
                            <tr>
                                <td><a id="AddLabor<?php echo $subItemID?>" title="Add Labor Item" data-rel="tooltip" style="opacity:1" class="btn btn-mini thickbox" onclick="showThis(this.id,'ca_labor_detail.php?ca_id=<?php echo functions::encode($ca_id)?>','Add Labor Detail')"><i class="halflings-icon white plus"></i>Add Labor Item</a></td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="6" height="40px">&nbsp;</td>
                            </tr>
                            <tr bgcolor="#CCCCCC">
                                <th width="33%" scope="col">Materials</th>
                                <th width="9%" scope="col"><div align="left">Capabilites</div></th>
                                <th width="9%" scope="col"><div align="left">Unit</div></th>
                                <th width="9%" scope="col"><div align="left">Quantity</div></th>
                                <th width="9%" scope="col"><div align="left">Unit Cost</div></th>
                                <th width="9%" scope="col"><div align="left">Cost</div></th>
                            </tr>
                            <?php
                            $qMaterialGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id,'type'=>'materials'));
                            while($rMaterialGroup = $db->fetch_array($qMaterialGroup)):
                                $countDetail=0;
                            ?>
                            <tr>
                                <td colspan="6">&nbsp;</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong><?php echo $rMaterialGroup['title']?>&nbsp;&nbsp;</strong>
                                    <a id="ManageItem<?php echo $rMaterialGroup['cag_id']?>" class="thickbox" style="cursor:pointer" title="Manage Item" data-rel="tooltip" onclick="showThis(this.id,'ca_material_detail.php?pid=<?php echo functions::encode($p_id);?>&itemID=<?php echo functions::encode($subItemID);?>&cag_id=<?php echo functions::encode($rMaterialGroup['cag_id']);?>','Adding Item')"><i class="halflings-icon pencil"></i></a>
                                    <a id="deleteItem<?php echo $rMaterialGroup['cag_id']?>" style="cursor:pointer" title="Delete Item" data-rel="tooltip" onClick="return delt()" href="<?php echo $_SERVER['PHP_SELF']?>?pid=<?php echo functions::encode($p_id);?>&itemID=<?php echo functions::encode($subItemID);?>&delcag_id=<?php echo functions::encode($rMaterialGroup['cag_id']);?>"><i class="halflings-icon minus-sign"></i></a>
                                </td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                                  <?php
                                  $qMaterialDetail = $db->select('cag_detail','*',array('cag_id'=>$rMaterialGroup['cag_id']));
                                  while($rMaterialDetail = $db->fetch_array($qMaterialDetail)):
                                      $rate = $db->getValue('elm_rate','itemRate',array('id'=>$rMaterialDetail['element_id']));
                                      $elementDesc = $db->getValue('elm_rate','itemDesc',array('id'=>$rMaterialDetail['element_id']));
                                  ?>
                            <tr>
                                <td>&nbsp;&nbsp;&nbsp;<?php echo $elementDesc;?></td>
                                <td><?php echo $db->getValue('el_capability','concat(slow_manhour," ",itemUnit)',array('itemDesc'=>$elementDesc))?></td>
                                <td><?php echo $rMaterialDetail['unit']?></td>
                                <td><?php echo functions::formatMoney($rMaterialDetail['quantity'])?></td>
                                <td><?php echo functions::formatMoney($rate)?></td>
                                <td><?php echo functions::formatMoney($rate * $rMaterialDetail['quantity'])?></td>
                            </tr>
                            <?php 
                                  endwhile;
                            endwhile;?>
                            <tr>
                                <td colspan="6">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="4">&nbsp;</td>
                                <td>Total Cost</td>
                                <td><strong><?php echo functions::formatMoney($db->getValue('cost_analysis_report','sum(cost)',array('sub_item'=>$subItemID,'type'=>'materials')))?></strong></td>
                            </tr>
                            <tr>
                                <td><a id="AddMaterial<?php echo $subItemID?>" title="Add Material Item" data-rel="tooltip" style="opacity:1" class="btn btn-mini thickbox" onclick="showThis(this.id,'ca_material_detail.php?ca_id=<?php echo functions::encode($ca_id)?>','Add Material Detail')"><i class="halflings-icon white plus"></i>Add Material Item</a></td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="6" height="40px">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="5"><div align="right">Total Estimated Direct Cost</div></td>
                                <td>
                                    <strong>
                                        <?php 
                                        $totalEDC = $db->getValue('cost_analysis_report','if(sum(cost),sum(cost),0)',array('sub_item'=>$subItemID));
                                        $overAllEDC = $db->getValue('cost_analysis_report','if(sum(cost),sum(cost),0)',array('proj_id'=>$p_id));
                                        $ocm = $db->getValue('indirect_cost','ocm',array(),' WHERE '.$db->clean($overAllEDC). ' BETWEEN edc_from AND edc_to');
                                        $prft = $db->getValue('indirect_cost','profit',array(),' WHERE '.$db->clean($overAllEDC). ' BETWEEN edc_from AND edc_to');
                                        $cost_ocm = ($ocm) ? ($ocm / 100) * $totalEDC : 0;
                                        $cost_prft = ($prft) ? ($prft / 100) * $totalEDC : 0;
                                        $totalIndirectCost = $cost_ocm + $cost_prft;
                                        $vat = $totalIndirectCost * .12;
                                        echo functions::formatMoney($totalEDC);
                                        ?>
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6" height="40px">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="6">INDIRECT COST</td>
                            </tr>
                            <tr>
                                <td colspan="5"><div align="right">Overhead, Contengencies & Miscellaneous (<?php echo $ocm?>% of Direct Cost)</div></td>
                                <td><?php echo functions::formatMoney($cost_ocm);?></td>
                            </tr>
                            <tr>
                                <td colspan="5"><div align="right">Contractor's Profit (<?php echo $prft?>% of Direct Cost)</div></td>
                                <td><?php echo functions::formatMoney($cost_prft);?></td>
                            </tr>
                            <tr>
                                <td colspan="5"><div align="right">Total Indirect Cost</div></td>
                                <td><strong><?php echo functions::formatMoney($totalIndirectCost);?></strong></td>
                            </tr>
                            <tr>
                                <td colspan="5"><div align="right">VAT (12.0% of Equipment+Labor+Indirect Cost)</div></td>
                                <td><strong><?php echo functions::formatMoney($vat);?></strong></td>
                            </tr>
                            <tr>
                                <td colspan="6">TOTAL CONSTRUCTION COST</td>
                            </tr>
                            <tr>
                                <td colspan="5"><div align="right"> Unit Direct Cost (UDC)</div></td>
                                <td><strong><?php echo functions::formatMoney($totalEDC + $totalIndirectCost + $vat);?></strong></td>
                            </tr>
                        </table>
                    </td>
                <?php endwhile;?>
                </tr>
            </table>
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
function itemSel(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?pid=<?php echo functions::encode($p_id)?>&itemID="+PiEwgD}
</script>
<!-- end: JavaScript-->
</body>
</html>