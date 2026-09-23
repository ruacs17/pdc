<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="8" ){
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
$recorded_by_title = $db->getValue('mr','recorded_by_title',array('mr_id'=>$mr_id));
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
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>MR</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
     <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
<table width="700" border="0" align="center" style="font-size: 14px;">
  <tr>
    <td>
      <?php 
      require_once('../class/print_header.php');
      print_header('RETURN SLIP');
      ?>
    </td>
  </tr>
</table><br>
<table width="85%" border="0" align="center">
   <tr>
    <td>
      <table width="100%" border="0" style="font-size: 14px;">
          <tr>
            <td height="15" align="center"><div><strong>MR No. <?php echo $db->getValue('mr','mr_no',array('mr_id'=>$mr_id));?></strong></div></td>
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
      <table width="100%" border="1" style="font-size: 11px;">
                      <tr>
                        <th width="3%" scope="col"><div align="center">Qty</div></th>
                        <th width="4%" scope="col"><div align="center">Unit</div></th>
                        <th width="25%" scope="col"><div align="left">Equipment</div></th>
                        <th width="7%" scope="col"><div align="left">Serial No.</div></th>
                        <th width="7%" scope="col"><div align="left">Inventory No.</div></th>
                        <th width="7%" scope="col"><div align="center">Unit Cost</div></th>
                        <th width="7%" scope="col"><div align="center">Date Purchased</div></th>
                        <th width="7%" scope="col"><div align="center">Date Returned</div></th>
                        <th width="7%" scope="col"><div align="center">Estimated<br>Useful<br>Life</div></th>
                        <th width="11%" scope="col"><div align="center">Remark</div></th>
                      </tr>
                      <?php
                          $qItem = $db->select('mr_item','*',array('mr_id'=>$mr_id));
                          while($rItem = $db->fetch_array($qItem)):
                            $equipmentName = '';
                            $qEqp = $db->select('equipment','*',array('equip_id'=>$rItem['equip_id']));
                            while($rEqp = $db->fetch_array($qEqp)):
                              $equipmentName = ucwords(strtolower($rEqp['equip_desc']." ".$rEqp['plate_no']));
                            endwhile;
                      ?>
                      <tr>
                        <td><div align="center"><?php echo $rItem['qty'];?></div></td>
                        <td><div align="center"><?php echo $rItem['unit'];?></div></td>
                        <td><?php echo $equipmentName;?></td>
                        <td><?php echo $db->getValue('equipment','serial_no',array('equip_id'=>$rItem['equip_id']));?></td>
                        <td><?php echo $db->getValue('equipment','inventory_id',array('equip_id'=>$rItem['equip_id']));?></td>
                        <td><div align="center"><?php echo functions::formatMoney($db->getValue('equipment','price',array('equip_id'=>$rItem['equip_id'])));?></div></td>
                        <td><div align="center"><?php echo functions::datearr($db->getValue('equipment','date_acquired',array('equip_id'=>$rItem['equip_id'])));?></div></td>
                        <td><div align="center"><?php echo ($rItem['returned']==2) ? functions::datearr($rItem['return_date']) : '-- -- ----';?></div></td>
                        <td><div align="center"><?php $pl = $db->getValue('equipment','round(datediff(proposed_life,date_acquired)/365,1)',array('equip_id'=>$rItem['equip_id'])); echo ($pl) ? $pl. ' Year/s' : '';?></div></td>
                        <td><?php echo $rItem['remark'];?></td>
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
            <!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
    window.print();
    setTimeout("closePrint()",200);

});
  function closePrint(){
    window.location="admin-mr-return-item-manage.php?mr_id=<?php echo functions::encode($mr_id)?>";
  }
</script>
<!-- end: JavaScript-->
</body>
</html>