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
$equip_id = (isset($_REQUEST['vdidEdt']) && !empty($_REQUEST['vdidEdt']) ) ? functions::decode($_REQUEST['vdidEdt']) : 0;
if( isset($_POST['btnSave']) && $equip_id){
    $arrDetails=array();
    $txRemark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : '';
    $txStatus = ( isset($_POST['txStatus']) && !empty($_POST['txStatus']) ) ? trim($_POST['txStatus']) : 'active';
    if( $txStatus ){
        $db->update('equipment',array('remarks'=>$txRemark,'status'=>$txStatus),array('equip_id'=>$equip_id));
        functions::say("Changes saved!");
    }
    functions::sendTo('admin-equipment-view.php?vdidVw='.functions::encode($equip_id));
}

$selClass='';$selType='';$txDesc='';$txModel='';$txBrand='';$txSerialNo='';$txPlateNo='';$txEngineNo='';$txChassisNo='';$txAcquiredMon='';$txAcquiredDay='';$txAcquiredYear=''; $selBrand='';$txInventoryNo='';$txQuantity=0;$txStatus='';
$txMVNo='';$txPower='';$txLocation='';$txGrossWt='';$txNetWt='';$txShipWt='';$txPrice='';$txRate=0;$txRemark='';$txProposedDay='';$txProposedYear=''; $txProposedMon=''; $txRemark=''; $txProposedNoYr=0; $txUsageLimit=0;$txWarranty=0;$txUnit='';
$q = $db->select('equipment','*',array('equip_id'=>$equip_id));
while( $r = $db->fetch_array($q) ):
  $txInventoryNo = $r['inventory_id'];
  $selClass = $r['classification'];
  $selType = $r['type'];
  $txDesc = $r['equip_desc'];
  $txModel = $r['model'];
  $txBrand = $r['brand'];
  $txSerialNo = $r['serial_no'];
  $txPlateNo = $r['plate_no'];
  $txEngineNo = $r['engine_no'];
  $txChassisNo = $r['chassis_no'];
  $txDateAcquired = $r['date_acquired'];
  $txMVNo = $r['mvFileNo'];
  $txPower = $r['power'];
  $txLocation = $r['location'];
  $txGrossWt = $r['gross_wt'];
  $txNetWt = $r['net_wt'];
  $txShipWt = $r['shipping_wt'];
  $txPrice = ($r['price']) ? functions::formatMoney($r['price']) : 0;
  $txRemark = $r['remarks'];
  $txProposedNoYr = $db->getValue('equipment','if( IFNULL(left(proposed_life,4),0) > IFNULL(left(date_acquired,4),0), IFNULL(left(proposed_life,4),0)-IFNULL(left(date_acquired,4),0),0) as proposedLife',array('equip_id'=>$r['equip_id']));
  $txWarranty = $db->getValue('equipment','if( IFNULL(left(warranty,4),0) > IFNULL(left(date_acquired,4),0), IFNULL(left(warranty,4),0)-IFNULL(left(date_acquired,4),0),0) as yrWarranty',array('equip_id'=>$r['equip_id']));
  $txUsageLimit = ($r['limit_minutes'] > 0) ? $r['limit_minutes'] / 60 : 0;
  $txRate = $r['rate'];
  $txQuantity = $r['quantity'];
  $txUnit = $r['unit'];
  $txStatus = $r['status'];

  $xdate = explode('-',$txDateAcquired);
  if(count($xdate)==3){
  $txAcquiredMon = $xdate[1];
  $txAcquiredDay = $xdate[2];
  $txAcquiredYear = $xdate[0];
  }

  $propDate = explode('-',$r['proposed_life']);
  if(count($propDate)==3){
  $txProposedMon = $propDate[1];
  $txProposedDay = $propDate[2];
  $txProposedYear = $propDate[0];
  }
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Equipment Update</title>
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
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>EQUIPMENT EDIT</h2>
                    </div>
                    <div class="box-content">
                      <div align="center">
                      <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="oldEquipName" id="oldEquipName" value="<?php echo functions::encode($txDesc)?>">
                        <table width="70%" border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <th width="35%" align="right" scope="row">&nbsp;</th>
                            <td width="2%">&nbsp;</td>
                            <td width="50%">&nbsp;</td>
                            <td width="13%">&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Equipment Classification</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="selClass" id="selClass" style="width:250px;" disabled>
                                    <option value="">-- Select --</option>
                                    <?php
                                        $qClass = $db->query('SELECT * FROM equipment_type WHERE type_name="classification" ORDER BY type_desc ');
                                        while($rClass = $db->fetch_array($qClass)):
                                    ?>
                                     <option value="<?php echo $rClass['type_desc']?>" <?php if($selClass==$rClass['type_desc'])echo 'selected="selected"';?>><?php echo $rClass['type_desc']?></option>
                                     <?php endwhile;?>
                                </select>
                            </td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Equipment Type</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="selType" id="selType" style="width:250px;" disabled>
                                    <option value="">-- Select --</option>
                                    <?php
                                        $qType = $db->query('SELECT * FROM equipment_type WHERE type_name="type" ORDER BY type_desc ');
                                        while($rType = $db->fetch_array($qType)):
                                    ?>
                                     <option value="<?php echo $rType['type_desc']?>" <?php if($selType==$rType['type_desc'])echo 'selected="selected"';?>><?php echo $rType['type_desc']?></option>
                                     <?php endwhile;?>
                                </select>
                            </td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Brand/Made</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="selBrand" id="selBrand" style="width:250px;" disabled>
                                    <option value="">-- Select --</option>
                                    <?php
                                        $qBrand = $db->query('SELECT * FROM equipment_type WHERE type_name="brand" ORDER BY type_desc ');
                                        while($rBrand = $db->fetch_array($qBrand)):
                                    ?>
                                     <option value="<?php echo $rBrand['type_desc']?>" <?php if($txBrand==$rBrand['type_desc'])echo 'selected="selected"';?>><?php echo $rBrand['type_desc']?></option>
                                     <?php endwhile;?>
                                </select>
                            </td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Equipment Picture</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                          <?php
                              $fileName = $db->getValue('images','name',array('equip_id'=>$equip_id));
                              $file = ($fileName) ? '../img_equip/'.$fileName : '../img_equip/blank-pic.png';
                          ?>
                          <img height="200" width="200" src="<?php echo $file?>">
                            </td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Equipment Code</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txInventoryNo" id="txInventoryNo" value="<?php echo $txInventoryNo;?>" disabled/></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Description</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                              <textarea type="text" name="txDesc" id="txDesc" style="width:300px" disabled><?php echo $txDesc?></textarea>
                            </td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Model</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txModel" id="txModel" value="<?php echo $txModel?>" disabled /></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Serial Number</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txSerialNo" id="txSerialNo" value="<?php echo $txSerialNo?>" disabled /></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Plate Number</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txPlateNo" id="txPlateNo" value="<?php echo $txPlateNo?>" disabled /></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">MV File Number</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txMVNo" id="txMVNo" value="<?php echo $txMVNo?>" disabled /></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Engine Number</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txEngineNo" id="txEngineNo" value="<?php echo $txEngineNo?>" disabled /></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Chassis Number</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txChassisNo" id="txChassisNo" value="<?php echo $txChassisNo?>" disabled /></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Power / Capacity</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txPower" id="txPower" value="<?php echo $txPower?>" disabled /></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Gross Weight</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txGrossWt" id="txGrossWt" value="<?php echo $txGrossWt?>" disabled /></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Net Weight</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txNetWt" id="txNetWt" value="<?php echo $txNetWt?>" disabled /></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Shipping Weight</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txShipWt" id="txShipWt" value="<?php echo $txShipWt?>" disabled /></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Acquisition Price</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" class="span6"  name="txPrice" id="txPrice" autocomplete='off' value="<?php echo $txPrice?>" onkeyup="FormatCurrency(this);" disabled></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Location</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txLocation" id="txLocation" value="<?php echo $txLocation?>" disabled /></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Date Acquired</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                              <select name="txAcquiredMon" id="txAcquiredMon" style="width:80px;" disabled>
                                <option value="">Month</option>
                                <option value="01" <?php if($txAcquiredMon=='01')echo 'selected="selected"';?>>Jan</option>
                                <option value="02" <?php if($txAcquiredMon=='02')echo 'selected="selected"';?>>Feb</option>
                                <option value="03" <?php if($txAcquiredMon=='03')echo 'selected="selected"';?>>Mar</option>
                                <option value="04" <?php if($txAcquiredMon=='04')echo 'selected="selected"';?>>Apr</option>
                                <option value="05" <?php if($txAcquiredMon=='05')echo 'selected="selected"';?>>May</option>
                                <option value="06" <?php if($txAcquiredMon=='06')echo 'selected="selected"';?>>Jun</option>
                                <option value="07" <?php if($txAcquiredMon=='07')echo 'selected="selected"';?>>Jul</option>
                                <option value="08" <?php if($txAcquiredMon=='08')echo 'selected="selected"';?>>Aug</option>
                                <option value="09" <?php if($txAcquiredMon=='09')echo 'selected="selected"';?>>Sep</option>
                                <option value="10" <?php if($txAcquiredMon=='10')echo 'selected="selected"';?>>Oct</option>
                                <option value="11" <?php if($txAcquiredMon=='11')echo 'selected="selected"';?>>Nov</option>
                                <option value="12" <?php if($txAcquiredMon=='12')echo 'selected="selected"';?>>Dec</option>
                              </select>
                              <select name="txAcquiredDay" id="txAcquiredDay" style="width:60px;" disabled>
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo $i;?>" <?php if($txAcquiredDay==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
                                <?php endfor;?>
                              </select>
                              <select name="txAcquiredYear" id="txAcquiredYear" style="width:70px;" disabled>
                                <option value="">Year</option>
                                <?php for($y=(date('Y') + 2);$y>=2000;$y--):?>
                                <option value="<?php echo $y;?>" <?php if($txAcquiredYear==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
                                <?php endfor;?>
                              </select>
                            </td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Warranty (No. of Years)</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txWarranty" id="txWarranty" value="<?php echo $txWarranty?>" onkeypress="return checkinput(this, event);" disabled/></td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Proposed Life (No. of Years)</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txProposedNoYr" id="txProposedNoYr" value="<?php echo $txProposedNoYr?>" onkeypress="return checkinput(this, event);" disabled/></td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Rate Per Hour</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" class="span6"  name="txRate" id="txRate" autocomplete='off' value="<?php echo $txRate?>" onkeyup="FormatCurrency(this);" disabled></td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Maintenance Period (No. of Hours)</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txUsageLimit" id="txUsageLimit" value="<?php echo $txUsageLimit?>" onkeypress="return checkinput(this, event);" disabled/></td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Quantity</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="txQuantity" id="txQuantity" data-rel="chosen" style="width:80px;" disabled>
                                    <?php for($i=1;$i<=100;$i++):?>
                                    <option value="<?php echo $i;?>" <?php if($txQuantity==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
                                    <?php endfor;?>
                                </select>
                                <select name="txUnit" id="txUnit" style="width:80px;" disabled>
                                    <option value="unit" <?php if($txUnit=="unit")echo 'selected="selected"';?>>Unit</option>
                                    <option value="piece" <?php if($txUnit=="piece")echo 'selected="selected"';?>>Piece</option>
                                    <option value="set" <?php if($txUnit=="set")echo 'selected="selected"';?>>Set</option>
                                    <option value="pair" <?php if($txUnit=="pair")echo 'selected="selected"';?>>Pair</option>
                                </select>
                            </td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Status</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="txStatus" id="txStatus" style="width:250px;">
                                    <option value="">--Select--</option>
                                    <option value="active" <?php if($txStatus=="active")echo 'selected="selected"';?>>Active</option>
                                    <option value="inactive" <?php if($txStatus=="inactive")echo 'selected="selected"';?>>Inactive</option>
                                    <option value="Good Condition" <?php if($txStatus=="Good Condition")echo 'selected="selected"';?>>Good Condition</option>
                                    <option value="For Repair" <?php if($txStatus=="For Repair")echo 'selected="selected"';?>>For Repair</option>
                                    <option value="For Rehab" <?php if($txStatus=="For Rehab")echo 'selected="selected"';?>>For Rehab</option>
                                    <option value="Unserviceable" <?php if($txStatus=="Unserviceable")echo 'selected="selected"';?>>Unserviceable</option>
                                    <option value="Sold" <?php if($txStatus=="Sold")echo 'selected="selected"';?>>Sold</option>
                                </select>
                                <span class="help-inline warning" id="MsgStatus" style="font-weight:bold;" name="MsgStatus"></span>
                            </td>
                          </tr>
                          <tr>
                            <th align="right" scope="row">Remarks</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                              <textarea type="text" name="txRemark" id="txRemark" style="width:300px"><?php echo $txRemark;?></textarea>
                            </td>
                            <td>&nbsp;</td>
                          </tr>
                          <tr>
                            <td class="tdSpace" colspan="3" align="center"><input type="submit" name="btnSave" id="btnSave" value="Save Changes" class="btn btn-primary"></td>
                          </tr>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
$(document).ready(function(){
  var res = false;
  $('#btnSave').click(function(){
    $('#MsgSelClass').html("");
    $('#MsgSelType').html("");
    $('#MsgtxDesc').html("");

    if( $('#selClass').val()=="" ){
      $('#MsgSelClass').html("Classification Required!");
      res=false;
    }
    else if( $('#selType').val()=="" ){
      $('#MsgSelType').html("Type Required!");
      res=false;
    }
    else if( $('#txDesc').val()=="" ){
      $('#MsgtxDesc').html("Description Required!");
      res=false;
    }
    else{
        res=true;
    }
    return res;
  });
});
</script>
<!-- end: JavaScript-->
</body>
</html>