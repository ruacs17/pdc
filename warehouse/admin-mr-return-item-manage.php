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
  require_once('../class/MoneytoWords.php');
  $db = new Database();
  $name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
  #$logs = new Logs();
  #$logs->save('visit');

$mr_id = (isset($_REQUEST['mr_id']) && !empty($_REQUEST['mr_id']) ) ? functions::decode($_REQUEST['mr_id']) : 0;
$mri_id_Edt=(isset($_REQUEST['txItmEdt']) && !empty($_REQUEST['txItmEdt']) ) ? functions::decode($_REQUEST['txItmEdt']) : 0;
$mon='00';$day='00';$year='0000';
if( isset($_POST['btnAdd']) ){

  $selEquip = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? functions::decode($_POST['selEquip']) : 0;
  $qty = ( isset($_POST['txQuantity']) && !empty($_POST['txQuantity']) ) ? trim($_POST['txQuantity']) : 0;
  $unit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : "";
  $remark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : '';

  $txMon = ( isset($_POST['txMon']) && !empty($_POST['txMon']) ) ? $_POST['txMon'] : '00';
  $txDay = ( isset($_POST['txDay']) && !empty($_POST['txDay']) ) ? $_POST['txDay'] : '00';
  $txYear = ( isset($_POST['txYear']) && !empty($_POST['txYear']) ) ? $_POST['txYear'] : '0000';
  $txReturnDate = $txYear.'-'.$txMon.'-'.$txDay;

  if( !empty($selEquip) && !empty($qty) && !empty($unit) ){
        $db->insert('mr_item',array('mr_id'=>$mr_id,'equip_id'=>$selEquip,'qty'=>$qty,'unit'=>$unit,'remark'=>$remark,'return_date'=>$txReturnDate));
  }
  functions::sendTo($_SERVER['PHP_SELF'].'?mr_id='.functions::encode($mr_id));
}

if( isset($_POST['btnSave']) ){

  $mri_id_Edt = ( isset($_POST['txItmEdt']) && !empty($_POST['txItmEdt']) ) ? functions::decode($_POST['txItmEdt']) : 0;
  $remark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : '';
  $selReturned = ( isset($_POST['selReturned']) && !empty($_POST['selReturned']) ) ? trim($_POST['selReturned']) : 1;

  $txMon = ( isset($_POST['txMon']) && !empty($_POST['txMon']) ) ? $_POST['txMon'] : '00';
  $txDay = ( isset($_POST['txDay']) && !empty($_POST['txDay']) ) ? $_POST['txDay'] : '00';
  $txYear = ( isset($_POST['txYear']) && !empty($_POST['txYear']) ) ? $_POST['txYear'] : '0000';
  $txReturnDate = $txYear.'-'.$txMon.'-'.$txDay;
  
  if( !empty($mri_id_Edt) ){
    $db->update('mr_item',array('remark'=>$remark,'returned'=>$selReturned,'return_date'=>$txReturnDate),array('mri_id'=>$mri_id_Edt));
  }
  functions::sendTo($_SERVER['PHP_SELF'].'?mr_id='.functions::encode($mr_id));
}

$editTrue=0;
$txItmEdt='';
$equip_id=''; $qty='';$remark='';$unit=''; $returned='';
if( isset($_REQUEST['txItmEdt']) && !empty($_REQUEST['txItmEdt']) ){
  $txItmEdt = functions::decode($_REQUEST['txItmEdt']);
  $editTrue = $db->getValue('mr_item','count(*)',array('mri_id'=>$txItmEdt));
  $qvedt = $db->select('mr_item','*',array('mri_id'=>$txItmEdt));
  $rvedt = $db->fetch_array($qvedt);
  $qty = $rvedt['qty'];
  $remark = $rvedt['remark'];
  $unit = $rvedt['unit'];
  $equip_id = $rvedt['equip_id'];
  $returned = $rvedt['returned'];

 $dateRet = explode('-',$rvedt['return_date']);
  if(count($dateRet)==3){
    $mon=$dateRet[1];
    $day=$dateRet[2];
    $year=$dateRet[0];  
  }
}
$qUnit = $db->query('SELECT DISTINCT unit FROM mr_item');
$namesUnit='';
  while($rUnit=$db->fetch_array($qUnit)):
      $string = preg_replace("/'/",'"',$rUnit['unit']);
      $namesUnit .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
  endwhile;
$namesUnit .= '"--"';


$eu_id = $db->getValue('mr','eu_id',array('mr_id'=>$mr_id));
$eu = ucwords(strtolower($db->getValue('equip_user','concat(fname," ",substring(mname,1,1),". ",lname)',array('eu_id'=>$eu_id))));
$marital_status = $db->getValue('equip_user','marital_status',array('eu_id'=>$eu_id));
$address = $db->getValue('equip_user','address',array('eu_id'=>$eu_id));
$position = $db->getValue('equip_user','position',array('eu_id'=>$eu_id));
$employer = $db->getValue('mr','employer',array('mr_id'=>$mr_id));
$employer_add = $db->getValue('mr','employer_add',array('mr_id'=>$mr_id));
$date_hired = $db->getValue('equip_user','date_hired',array('eu_id'=>$eu_id));
$assigned_location = $db->getValue('mr','assigned_location',array('mr_id'=>$mr_id));
$proj_id = $db->getValue('mr','proj_id',array('mr_id'=>$mr_id));
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$proj_id));

$released_date = $db->getValue('mr','released_date',array('mr_id'=>$mr_id));
$released_byID = $db->getValue('mr','released_by',array('mr_id'=>$mr_id));
$released_by = strtoupper($db->getValue('equip_user','concat(fname," ",substring(mname,1,1),". ",lname)',array('eu_id'=>$released_byID)));

$recorded_date = $db->getValue('mr','recorded_date',array('mr_id'=>$mr_id));
$recorded_byID = $db->getValue('mr','recorded_by',array('mr_id'=>$mr_id));
$recorded_by = strtoupper($db->getValue('equip_user','concat(fname," ",substring(mname,1,1),". ",lname)',array('eu_id'=>$recorded_byID)));

$received_date = $db->getValue('mr','received_date',array('mr_id'=>$mr_id));
$received_byID = $db->getValue('mr','received_by',array('mr_id'=>$mr_id));
$received_by = strtoupper($db->getValue('equip_user','concat(fname," ",substring(mname,1,1),". ",lname)',array('eu_id'=>$received_byID)));

$ret_received_byID = $db->getValue('mr','ret_received_by',array('mr_id'=>$mr_id));
$ret_received_by_title = $db->getValue('mr','ret_received_by_title',array('mr_id'=>$mr_id));
$ret_received_by = strtoupper($db->getValue('equip_user','concat(fname," ",substring(mname,1,1),". ",lname)',array('eu_id'=>$ret_received_byID)));
$ret_received_date = $db->getValue('mr','ret_received_date',array('mr_id'=>$mr_id));

$ret_checked_byID = $db->getValue('mr','ret_checked_by',array('mr_id'=>$mr_id));
$ret_checked_by_title = $db->getValue('mr','ret_checked_by_title',array('mr_id'=>$mr_id));
$ret_checked_by = strtoupper($db->getValue('equip_user','concat(fname," ",substring(mname,1,1),". ",lname)',array('eu_id'=>$ret_checked_byID)));
$ret_checked_date = $db->getValue('mr','ret_checked_date',array('mr_id'=>$mr_id));

$ret_noted_byID = $db->getValue('mr','ret_noted_by',array('mr_id'=>$mr_id));
$ret_noted_by_title = $db->getValue('mr','ret_noted_by_title',array('mr_id'=>$mr_id));
$ret_noted_by = strtoupper($db->getValue('equip_user','concat(fname," ",substring(mname,1,1),". ",lname)',array('eu_id'=>$ret_noted_byID)));
$ret_noted_date = $db->getValue('mr','ret_noted_date',array('mr_id'=>$mr_id));

$ret_approved_byID = $db->getValue('mr','ret_approved_by',array('mr_id'=>$mr_id));
$ret_approved_by_title = $db->getValue('mr','ret_approved_by_title',array('mr_id'=>$mr_id));
$ret_approved_by = strtoupper($db->getValue('equip_user','concat(fname," ",substring(mname,1,1),". ",lname)',array('eu_id'=>$ret_approved_byID)));
$ret_approved_date = $db->getValue('mr','ret_approved_date',array('mr_id'=>$mr_id));

$mr_remarks = $db->getValue('mr','mr_remarks',array('mr_id'=>$mr_id));
#$returned = $db->getValue('mr_item','returned',array('mr_id'=>$mr_id));
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>MR Manage</title>
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
    <style type="text/css">
    .padParLeft{padding-left:10px;}
  .padAmLeft{padding-left:60px;}
    </style>
  </head>
  <body bgcolor="#FFFFFF">
  <!-- body content: start here-->
<form method="post">
  <div class="row-fluid">
    <div class="box span12">
      <div class="box-header" data-original-title>
          <h2><i class="halflings-icon white edit"></i><span class="break"></span>MR Item Manage</h2>
      </div>
      <div align="left">
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td width="50%">
              <div align="left">
                <table width="180" cellspacing="0" cellpadding="0" border="0" align="left">
                    <tr>
                        <td width="10" height='30'><div style="background-color:#fcb77b; width:20px;">&nbsp;</div></td>
                        <td width="90"> Unreturned</td>
                    </tr>
                </table>
              </div>
            </td>
            <td>&nbsp;
            </td>
          </tr>
      </table>
      </div><br><br>
          <div class="box-content">
<table width="100%" border="0" align="center" style="font-size: 12px;">
   <tr>
    <td>
      <table width="100%" border="0" style="font-size: 12px;">
          <tr>
            <td height="15" align="center">
              <div>
                <strong>RETURN SLIP <br> MR No. <?php echo $db->getValue('mr','mr_no',array('mr_id'=>$mr_id));?></strong>
              </div>
            </td>
          </tr>
          <tr>
            <td>
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I, <strong><u><?php echo $eu?></u></strong>, of legal age, <strong><u><?php echo $marital_status?></u></strong> and a resident of <strong><u><?php echo $address?></u></strong> has been employed as <strong><u><?php echo $position?></u></strong> at <strong><u><?php echo $employer?></u></strong> with an office address in <strong><u><?php echo $employer_add?></u></strong> from <strong><u><?php echo functions::datearr($date_hired)?></u></strong> to present. I am currently assigned at <strong><u><?php echo $assigned_location?></u></strong> for the <strong><u><?php echo $proj_name?></u></strong> project.
            </td>
          </tr>
          <tr height="5">
            <td></td>
          </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;That I have to return the following tools and equipments under my accountability:
      <?php if($mri_id_Edt){?>
                    <div align="center">
                      <br><br><br>
                    <input type="hidden" name="txItmEdt" id="txItmEdt" value="<?php echo functions::encode($txItmEdt);?>">
                    <table width="95%" border="0" align="center">
                      <tr>
                        <th width="35%" scope="col" height="30"><div align="center">Equipment</div></th>
                        <th width="25%" scope="col"><div align="center">Remark</div></th>
                        <th width="15%" scope="col"><div align="center">Status</div></th>
                        <th width="25%" scope="col"><div align="center">Return Date</div></th>
                        <th width="10%" scope="col">&nbsp;</th>
                      </tr>
                      <tr>
                        <td>
                          <div align="center">
                            <?php
                              $qEqp = $db->select('equipment','*',array('equip_id'=>$equip_id));
                              while($rEqp = $db->fetch_array($qEqp)):
                                $equipmentName = ucwords(strtolower($rEqp['equip_desc']." ".$rEqp['plate_no']));
                              endwhile;
                              echo $equipmentName;
                            ?>
                        </td>
                        <td><div align="center"><input type="text" style="width:300px;" name="txRemark" id="txRemark" value="<?php echo $remark?>"></div></td>
                        <td>
                          <div align="center">
                            <select name="selReturned" id="selReturned" style="width:120px;font-size:12px;">
                                <option value="">-- Select --</option>
                                <option value="1" <?php if($returned==1)echo 'selected="selected"';?>>Not Returned</option>
                                <option value="2" <?php if($returned==2)echo 'selected="selected"';?>>Returned</option>
                            </select>
                          </div>
                        </td>
                        <td>
                            <div align="center">
                          <select name="txMon" id="txMon" style="width:80px;">
                            <option value="00">Month</option>
                            <option value="01" <?php if($mon=='01')echo 'selected="selected"';?>>Jan</option>
                            <option value="02" <?php if($mon=='02')echo 'selected="selected"';?>>Feb</option>
                            <option value="03" <?php if($mon=='03')echo 'selected="selected"';?>>Mar</option>
                            <option value="04" <?php if($mon=='04')echo 'selected="selected"';?>>Apr</option>
                            <option value="05" <?php if($mon=='05')echo 'selected="selected"';?>>May</option>
                            <option value="06" <?php if($mon=='06')echo 'selected="selected"';?>>Jun</option>
                            <option value="07" <?php if($mon=='07')echo 'selected="selected"';?>>Jul</option>
                            <option value="08" <?php if($mon=='08')echo 'selected="selected"';?>>Aug</option>
                            <option value="09" <?php if($mon=='09')echo 'selected="selected"';?>>Sep</option>
                            <option value="10" <?php if($mon=='10')echo 'selected="selected"';?>>Oct</option>
                            <option value="11" <?php if($mon=='11')echo 'selected="selected"';?>>Nov</option>
                            <option value="12" <?php if($mon=='12')echo 'selected="selected"';?>>Dec</option>
                          </select>
                          <select name="txDay" id="txDay" style="width:60px;">
                            <option value="00">Day</option>
                            <?php for($i=1;$i<=31;$i++):?>
                            <option value="<?php echo $i;?>" <?php if($day==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
                            <?php endfor;?>
                          </select>
                          <select name="txYear" id="txYear" style="width:70px;">
                            <option value="0000">Year</option>
                            <?php for($y=(date('Y') + 4);$y>=2012;$y--):?>
                            <option value="<?php echo $y;?>" <?php if($year==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
                            <?php endfor;?>
                          </select>
                            </div>
                        </td>
                        <td><div align="center"><input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary"></div></td>
                      </tr>
                    </table>
                    <br><br><br>
                  </div>
      <?php }?> 
                    <table width="100%" border="1" style="font-size: 11px;">
                      <tr>
                        <th width="3%" scope="col"><div align="center">Qty</div></th>
                        <th width="4%" scope="col"><div align="center">Unit</div></th>
                        <th width="25%" scope="col"><div align="left">Equipment</div></th>
                        <th width="7%" scope="col"><div align="left">Serial No.</div></th>
                        <th width="7%" scope="col"><div align="left">Inventory No.</div></th>
                        <th width="8%" scope="col"><div align="center">Unit Cost</div></th>
                        <th width="6%" scope="col"><div align="center">Date Purchased</div></th>
                        <th width="6%" scope="col"><div align="center">Date Returned</div></th>
                        <th width="5%" scope="col"><div align="center">Estimated<br>Useful<br>Life</div></th>
                        <th width="11%" scope="col"><div align="center">Remark</div></th>
                        <th width="5%" scope="col">&nbsp;</th>
                      </tr>
                      <?php
                          $qItem = $db->select('mr_item','*',array('mr_id'=>$mr_id));
                          while($rItem = $db->fetch_array($qItem)):
                            $equipmentName = '';
                            $qEqp = $db->select('equipment','*',array('equip_id'=>$rItem['equip_id']));
                            while($rEqp = $db->fetch_array($qEqp)):
                              $equipmentName = ucwords(strtolower($rEqp['equip_desc']." ".$rEqp['plate_no']));
                            endwhile;
                            $bgColor='';
                              if($rItem['returned']=="1")
                                  $bgColor = 'bgcolor="#fcb77b"';
                      ?>
                      <tr <?php echo $bgColor;?>>
                        <td height="30"><div align="center"><?php echo $rItem['qty'];?></div></td>
                        <td><div align="center"><?php echo $rItem['unit'];?></div></td>
                        <td><?php echo $equipmentName;?></td>
                        <td><?php echo $db->getValue('equipment','serial_no',array('equip_id'=>$rItem['equip_id']));?></td>
                        <td><?php echo $db->getValue('equipment','inventory_id',array('equip_id'=>$rItem['equip_id']));?></td>
                        <td><div align="center"><?php echo functions::formatMoney($db->getValue('equipment','price',array('equip_id'=>$rItem['equip_id'])));?></div></td>
                        <td><div align="center"><?php echo functions::datearr($db->getValue('equipment','date_acquired',array('equip_id'=>$rItem['equip_id'])));?></div></td>
                        <td><div align="center"><?php echo ($rItem['returned']==2) ? functions::datearr($rItem['return_date']) : '-- -- ----';?></div></td>
                        <td><div align="center"><?php $pl = $db->getValue('equipment','round(datediff(proposed_life,date_acquired)/365,1)',array('equip_id'=>$rItem['equip_id'])); echo ($pl) ? $pl. ' Year/s' : '';?></div></td>
                        <td><?php echo $rItem['remark'];?></td>
                        <td>
                          <div align="center">
                               <a id="edit<?php echo $rItem['mri_id'];?>" class="btn btn-mini btn-info" title="Update this item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?mr_id=<?php echo functions::encode($mr_id);?>&txItmEdt=<?php echo functions::encode($rItem['mri_id']);?>">
                                <i class="halflings-icon white pencil"></i> 
                               </a>
                           </div>
                        </td>
                      </tr>
                      <?php endwhile;?>
                    </table>
    </td>
  </tr>
  <tr>
      <td height="20"><div align="center"><strong>---------- NOTHING FOLLOWS ----------</strong></div></td>
  </tr>
  <tr>
      <td height="20"><div align="center">&nbsp;</div></td>
  </tr>
  <tr>
    <td align="center">
      <table width="100%" border="0" style="font-size: 12px;">
        <tr>
            <td align="center" width="25%">Received by:</td>
            <td align="center" width="25%">Checked and Inspected by: </td>
            <td align="center" width="25%">Noted by:</td>
            <td align="center" width="25%">Approved by:</td>
        </tr>
        <tr>
            <td height="30" align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $ret_received_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
            <td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $ret_checked_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
            <td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $ret_noted_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
            <td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $ret_approved_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
        </tr>
        <tr>
            <td height="10" align="center"><div><strong><?php echo $ret_received_by_title;?></strong></div></td>
            <td align="center"><div><strong><?php echo $ret_checked_by_title;?></strong></div></td>
            <td align="center"><div><strong><?php echo $ret_noted_by_title;?></strong></div></td>
            <td align="center"><div><strong><?php echo $ret_approved_by_title;?></strong></div></td>
        </tr>
        <tr>
            <td height="20" align="center" valign="bottom">Date: <strong><?php echo functions::datearr($ret_received_date);?></strong></td>
            <td align="center" valign="bottom">Date: <strong><?php echo functions::datearr($ret_checked_date);?></strong></td>
            <td align="center" valign="bottom">Date: <strong><?php echo functions::datearr($ret_noted_date);?></strong></td>
            <td align="center" valign="bottom">Date: <strong><?php echo functions::datearr($ret_approved_date);?></strong></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
      <td height="20"><div align="center">&nbsp;</div></td>
  </tr>
  <tr>
      <td height="20"><div align="lef"><?php if($mr_remarks){?>Remarks: <strong><?php echo $mr_remarks;?></strong><?php }?></div></td>
  </tr>
  <tr>
      <td height="20"><div align="center"><strong>--------------------- CLEARED ---------------------</strong></div></td>
  </tr>
</table><br><br><br>
</div>
</div>
</div>
</form>
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
function delt(){
  if(confirm('Do you want to remove this?'))
    return true;
  else
    return false; 
}

$(document).ready(function(){
  var res = false;
  $('#btnAdd,#btnSave').click(function(){
    $('#msgtxEquip').html("");
    $('#msgtxQuantity').html("");
    $('#msgtxUnit').html("");

    if( $('#selEquip').val()=="" ){
      $('#msgtxEquip').html("Specify Equipment!");
      $('#selEquip').focus();
      res=false;
    }
    else if( $('#txQuantity').val()=="" ){
      $('#msgtxQuantity').html("Quantity Required!");
      $('#txQuantity').focus();
      res=false;
    }
    else if( $('#txUnit').val()=="" ){
      $('#msgtxUnit').html("Unit Required!");
      $('#txUnit').focus();
      res=false;
    }
    else
      res=true;

    return res;
  });
});
</script>
<!-- end: JavaScript-->
</body>
</html>