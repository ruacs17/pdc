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
function empDetail($rEmp,$dep_id,&$count=0){
    global $db;
    $empCount=1;
        $emp_status = $db->getValue('emp_work_status','ews_stat',array('emp_id'=>$rEmp['emp_id']),'ORDER BY ews_date DESC LIMIT 1');
        $date_hired = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$rEmp['emp_id']),'ORDER BY ews_date LIMIT 1');
        $datetime1 = new DateTime($date_hired);
        $datetime2 = new DateTime(date('Y-m-d'));
        $interval = $datetime1->diff($datetime2);
        $intrval_year = $interval->format('%y');
        $intrval_month = $interval->format('%m');
        $intrval_day = $interval->format('%d');
        $display_yr=''; $display_mn=''; $display_day='';
        if($intrval_year){
            $display_yr = ($intrval_year > 1) ? $intrval_year.' Years, ' : $intrval_year.' Year, ';
        }
        if($intrval_month){
            $display_mn = ($intrval_month > 1) ? $intrval_month.' Months, ' : $intrval_month.' Month, ';
        }
        if($intrval_day){
            $display_day = ($intrval_day > 1) ? $intrval_day.' Days ' : $intrval_day.' Day ';
        }
        $intval = ($date_hired=='0000-00-00' || $date_hired=='') ? '----' : $display_yr.$display_mn.$display_day;
    ?>
    <tr>
        <td><div align="center"><?php echo $count++; ?></div></td>
        <td><div align="left" style="padding-left:1px"><?php echo $rEmp['emp_no'] ?></td>
        <td><div align="left" style="padding-left:1px"><?php echo $rEmp['lname'] ?></td>
        <td><div align="left" style="padding-left:1px"><?php echo $rEmp['fname'] ?></td>
        <td><div align="left" style="padding-left:1px"><?php echo $rEmp['mname'] ?></td>
        <td><div align="left" style="padding-left:1px"><?php echo $rEmp['extname'] ?></td>
        <td><div align="left" style="padding-left:1px"><?php echo $rEmp['gender'] ?></div></td>
        <td>
            <div align="left" style="padding-left:1px">
            <?php
                $q = $db->select('emp_position ep, dep_position dp','*',array('dep_id'=>$dep_id,'emp_id'=>$rEmp['emp_id']),'AND dp.dp_id=ep.dp_id');
                $cntPos = $db->num_rows($q);
                $countPos=1;
                $position='';
                while($r = $db->fetch_array($q)):
                    $position .= $r['pos_name'];
                    if($countPos < $cntPos)
                        $position.=' / ';
                    $countPos++;
                endwhile;
                echo $position;
            ?>
            </div>
        </td>
        <td><div align="left" style="padding-left:1px"><?php echo functions::datearr($date_hired) ?></div></td>
        <td><div align="left" style="padding-left:1px"><?php echo $intval; ?></div></td>
        <td><div align="left" style="padding-left:1px"><?php echo $emp_status; ?></div></td>
    </tr>
    <?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>Leave File Print</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
    <link href="../css/printerfoot.css" rel="stylesheet">
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
    <script>//window.print();</script>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table width="100%" border="0" align="center">
    <thead>
        <tr>
            <td align="center">
                <?php 
                require_once('../class/print_header.php');
                print_header('Employee List');
                ?><br>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td height="500" valign="top">
                <div align="center">
                    <table border="1" style="font-size: 10px;" width="100%">
                        <thead>
                            <tr bgcolor="#CCC">
                                <th width="2%" scope="col">No</th>
                                <th width="3%" scope="col">ID</th>
                                <th width="7%" scope="col">LAST NAME</th>
                                <th width="7%" scope="col">FIRST NAME</th>
                                <th width="4%" scope="col">MIDDLE NAME</th>
                                <th width="1%" scope="col">EXT.</th>
                                <th width="3%" scope="col">GENDER</th>
                                <th width="15%" scope="col">POSITION</th>
                                <th width="6%" scope="col">DATE HIRED</th>
                                <th width="11%" scope="col">LENGTH OF SERVICE</th>
                                <th width="4%" scope="col">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $countEmpEntireDep=0;
                            $empDepPosCount=0;
                            $empSecPosCount=0;
                            $qDep = $db->select('department','*',array(),'WHERE dep_head is NULL ORDER BY dep_name');
                            while($rDep = $db->fetch_array($qDep)):
                                echo '<tr><td colspan="11">&nbsp;</td></tr>';
                                echo '<tr><td colspan="11" bgcolor="#CCFF00"><strong>'.$rDep['dep_name'].'</strong></td></tr>';
                                $empDepPosCount++;

                                $qEmp = $db->select('employee emp, emp_position ep, dep_position dp','emp.*,dp.dep_id',array('dp.dep_id'=>$rDep['dep_id']),'AND emp.emp_id=ep.emp_id AND ep.dp_id=dp.dp_id GROUP BY emp.emp_id,dp.dep_id ORDER BY lname, fname');
                                while($rEmp = $db->fetch_array($qEmp)):
                                    empDetail($rEmp,$rDep['dep_id'],$empDepPosCount);
                                endwhile;
                                $countEmpEntireDep += $empDepPosCount-1;
                                
                                $empDepPosCount=0;

                                
                                $qSec = $db->select('department','*',array('dep_head'=>$rDep['dep_id']),'ORDER BY dep_name');
                                while($rSec = $db->fetch_array($qSec)):
                                    $empSecPosCount++;
                                    echo '<tr><td colspan="11">&nbsp;</td></tr>';
                                    echo '<tr><td colspan="11" style="padding-left:35px;" bgcolor="#009900"><strong><i>'.$rSec['dep_name'].'</i></strong></td></tr>';

                                    $qEmp = $db->select('employee emp, emp_position ep, dep_position dp','emp.*,dp.dep_id',array('dp.dep_id'=>$rSec['dep_id']),'AND emp.emp_id=ep.emp_id AND ep.dp_id=dp.dp_id GROUP BY emp.emp_id,dp.dep_id ORDER BY lname, fname');
                                    while($rEmp = $db->fetch_array($qEmp)):
                                        empDetail($rEmp,$rSec['dep_id'],$empSecPosCount);
                                    endwhile;
                                    $countEmpEntireDep += $empSecPosCount-1;
                                    $empSecPosCount=0;

                                endwhile;//while($rSec = $db->fetch_array($qSec)):
                                echo '<tr><td colspan="11" style="padding-left:10px;"><div align="center"><strong><i>'.$rDep['dep_name'].' : ('.$countEmpEntireDep.')'.'</i></strong></div></td></tr>';
                                $countEmpEntireDep=0;
                                #echo '<tr><td colspan="11">&nbsp;</td></tr>';

                            endwhile;//while($Dep = $db->fetch_array($qDep)):
                            ?>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<!-- end: JavaScript-->
</body>
</html>