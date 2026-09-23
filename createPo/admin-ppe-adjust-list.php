<?php require_once('authorize.php');
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
$ppe_id = (isset($_REQUEST['ppe_id']) && !empty($_REQUEST['ppe_id']) ) ? functions::decode($_REQUEST['ppe_id']) : 0;
$address='';$emp_name='';$position='';$marital_status='';$date_hired='';$work_status='';
$emp_id = $db->getValue('ppe','emp_id',array('ppe_id'=>$ppe_id));
$empInfo = $db->select('employee','*',array('emp_id'=>$emp_id));
while($r = $db->fetch_array($empInfo)):
    $curStreet = $r['curr_street'];
    $curProvince = $db->getValue('refprovince','provDesc',array('provCode'=>$r['curr_province']));
    $curCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$r['curr_cityMun']));
    $curBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$r['curr_add']));
    $address = $curStreet.' '.strtoupper($curBrngy).', '.$curCity.', '.$curProvince;
    $emp_name = ucwords(strtolower($r['fname'].' '.$r['lname']));
    $work_status = $r['work_status'];
    $marital_status = $r['civil_status'];
    $dp_id = $db->getValue('emp_position','dp_id',array('emp_id'=>$emp_id));
    $position = $db->getValue('dep_position','pos_name',array('dp_id'=>$dp_id));
    $date_hired = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id),'ORDER BY ews_date ASC');
endwhile;

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
<table class="table table-bordered table-hover">
    <?php
    $qList = $db->query('SELECT * FROM ppe ORDER BY ppe_id');
    while($rList = $db->fetch_array($qList)):
    ?>
        <tr>
            <td colspan="3">
                <?php echo functions::datearr($rList['ppe_date']);?>&nbsp;&nbsp;<?php echo $rList['ppe_no'];?><br>
                 <?php echo $db->getValue('employee','concat(emp_no," - ",lname,", ",fname)',array('emp_id'=>$rList['emp_id']));?><br>
                <?php echo $db->getValue('project','proj_name',array('proj_id'=>$rList['proj_id']));?><br>
                <table width="100%" border="1" style="font-size: 11px;">
                    <tr>
                        <th width="10%" scope="col"><div align="center">PPE Type</div></th>
                        <th width="10%" scope="col"><div align="center">Brand</div></th>
                        <th width="10%" scope="col"><div align="center">Quantity</div></th>
                        <th width="10%" scope="col"><div align="center">Size</div></th>
                        <th width="10%" scope="col"><div align="center">No. Of Times Issued</div></th>
                        <th width="10%" scope="col"><div align="center">Date Issued</div></th>
                        <th width="10%" scope="col"><div align="center">Replacement Date</div></th>
                        <th width="10%" scope="col"><div align="center">Mode of Issuance</div></th>
                        <th width="10%" scope="col"><div align="center">Remarks</div></th>
                    </tr>
                    <?php
                    $qItem = $db->select('ppe_item','*',array('ppe_id'=>$rList['ppe_id']),'ORDER BY ppei_type');
                    while($rItem = $db->fetch_array($qItem)):
                        $rID = $rItem['ppei_id'];
                        $bgColor = ($db->getValue('ppe_items','count(*)',array('ppei_id'=>$rItem['ppei_id'])) ) ? 'bgcolor="#f5ae00"' : '';
                    ?>
                    <tr <?php echo $bgColor;?>>
                        <td height="30">
                            <div align="center">
                                <a id="detail<?php echo $rItem['ppei_id']?>" class="thickbox" style="cursor:pointer;" title="Manage IPPE item" data-rel="tooltip" onclick="showThis(this.id,'admin-ppe-adjust-detail.php?ppe_id=<?php echo functions::encode($rItem['ppe_id']);?>&ppei_id=<?php echo functions::encode($rID)?>','Manage Item')"><?php echo $rItem['ppei_type'];?></a>
                            </div>
                        </td>
                        <td><div align="center"><?php echo $rItem['ppei_brand'];?></div></td>
                        <td><div align="center"><?php echo $rItem['ppei_qty'];?></div></td>
                        <td><div align="center"><?php echo $rItem['ppei_size'];?></div></td>
                        <td><div align="center"><?php echo $rItem['issued_count'];?></div></td>
                        <td><div align="center"><?php echo functions::datearr($rItem['issued_date']);?></div></td>
                        <td><div align="center"><?php echo functions::datearr($rItem['replacement_date']);?></div></td>
                        <td><div align="center"><?php echo $rItem['issuance_mode'];?></div></td>
                        <td><?php echo $rItem['remark'];?></td>
                    </tr>
                    <?php endwhile;?>
                </table>
                <br><br><br>
            </td>
        </tr>
    <?php endwhile;?>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<!-- end: JavaScript-->
</body>
</html>