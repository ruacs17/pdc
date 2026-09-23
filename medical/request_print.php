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

$mrq_id = (isset($_REQUEST['mrq_id']) && !empty($_REQUEST['mrq_id']) ) ? functions::decode($_REQUEST['mrq_id']) : 0;

$q = $db->select('medicine_request','*',array('mrq_id'=>$mrq_id));
$r = $db->fetch_array($q);

$xdate = explode('-',$r['mrq_date']);
$mon = isset($xdate[1]) ? $xdate[1] : "";
$day = isset($xdate[2]) ? $xdate[2] : "";
$year = isset($xdate[0]) ? $xdate[0] : "";
$mrq_no = $r['mrq_no'];
$requested_by = $r['emp_id'];
$released_by = $r['released_by'];
$claimed = $r['claimed'];
$proj_id = $r['proj_id'];
$txClaim='';

$qEmp = $db->select('employee','*',array('emp_id'=>$requested_by));
$rEmp = $db->fetch_array($qEmp);
$bdate = $rEmp['bdate'];
$arrPosition=array(); $arrDepartment=array();
$qSelPos = $db->select('emp_position ep, dep_position dp,department d','*',array('emp_id'=>$rEmp['emp_id']),'AND ep.dp_id=dp.dp_id AND dp.dep_id=d.dep_id');
while($rSelPos = $db->fetch_array($qSelPos)):
    $arrPosition[$rSelPos['pos_name']]=$rSelPos['pos_name'];
    $arrDepartment[$rSelPos['dep_name']]=$rSelPos['dep_name'];
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>PO Fuel Report Print</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/loader.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
    <!-- end: CSS -->
    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <link id="ie-style" href="../css/ie.css" rel="stylesheet">
    <![endif]-->
    <!--[if IE 9]>
    <link id="ie9style" href="../css/ie9.css" rel="stylesheet">
    <![endif]-->
    <style type="text/css">.padParLeft{padding-left:10px;}.padAmLeft{padding-left:60px;}</style>
</head>
<body bgcolor="#FFFFFF">
<div id="spinner"></div>
<!-- body content: start here-->
<table  width="700" border="0" align="center">
    <thead>
        <tr>
            <td>
                <?php
                require_once('../class/print_header.php');
                print_header('Medicine Requisition Slip');
                ?><br>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <table border="1" width="100%" style="font-size:12px">
                    <tr>
                        <td colspan="4">Name: <strong><?php echo $rEmp['lname'].', '.$rEmp['fname']; ?></div></td>
                    </tr>
                    <tr>
                        <td>Age: <strong><?php echo functions::year_diff($bdate,date('Y-m-d'));?></div></td>
                        <td>Sex: <strong><?php echo $rEmp['gender'];?></div></td>
                        <td colspan="2">Civil Status: <strong><?php echo $rEmp['civil_status'];?></div></td>
                    </tr>
                    <tr>
                        <td width="15%"><div align="left">Position: </div></td>
                        <td width="25%"><div align="left"><strong><?php foreach($arrPosition as $pos): echo '<div>'.$pos.'</div>'; endforeach;?></strong></td>
                        <td width="15%"><div align="left">Department: </div></td>
                        <td width="25%"><div align="left"><strong><?php foreach($arrDepartment as $dep): echo '<div>'.$dep.'</div>'; endforeach;?></strong></td>
                    </tr>
                </table><br>
                <table class="table table-bordered table-hover" style="font-size:12px">
                    <thead>
                        <tr>
                            <th width="25%"><div align="left">Name of Medication</div></th>
                            <th width="15%"><div align="center">QTY</div></th>
                            <th width="15%"><div align="center">UNIT</div></th>
                            <th width="25%"><div align="center">Indication</div></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $qD = $db->select('medicine_request_detail','*',array('mrq_id'=>$mrq_id));
                            while($rD = $db->fetch_array($qD)):
                        ?>
                        <tr>
                            <td><div align="left"><?php echo $rD['item']; ?></div></td>
                            <td><div align="center"><?php echo $rD['qty']; ?></div></td>
                            <td><div align="center"><?php echo $rD['unit']; ?></div></td>
                            <td><div align="center"><?php echo $rD['indication']; ?></div></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <table width="100%">
                        <tr>
                            <td width="50%"><div align="left">Release By</div></td>
                            <td><div align="left">Requested By</div></td>
                        </tr>
                        <tr>
                            <td><div align="center"><?php echo $db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$released_by)); ?></div></td>
                            <td><div align="center"><?php echo $db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$requested_by)); ?></div></td>
                        </tr>
                    </table>
                </table>
            </td>
        </tr>
    </tbody>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
$(document).ready(function(){
    window.print();
});
</script>
<!-- end: JavaScript-->
</body>
</html>