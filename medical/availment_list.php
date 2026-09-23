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
$mhd_id = (isset($_REQUEST['mhd']) && !empty($_REQUEST['mhd']) ) ? functions::decode($_REQUEST['mhd']) : 0;
$emp_id = (isset($_REQUEST['empid']) && !empty($_REQUEST['empid']) ) ? functions::decode($_REQUEST['empid']) : 0;
$deleteID = (isset($_REQUEST['mhaDel']) && !empty($_REQUEST['mhaDel']) ) ? functions::decode($_REQUEST['mhaDel']) : 0;
if($deleteID){
    $db->delete('medicine_healthcare_availment',array('mha_id'=>$deleteID));
    functions::sendTo($_SERVER['PHP_SELF'].'?empid='.functions::encode($emp_id).'&mhd='.functions::encode($mhd_id));
}
$healthcare_amount = $db->getValue('medicine_healthcare_duration','mhd_amount',array('mhd_id'=>$mhd_id));
$mhd_start = $db->getValue('medicine_healthcare_duration','mhd_start',array('mhd_id'=>$mhd_id));
$mhd_end = $db->getValue('medicine_healthcare_duration','mhd_end',array('mhd_id'=>$mhd_id));
$emp_name = $db->getValue('employee','concat(lname, ", ",fname)',array('emp_id'=>$emp_id));
$work_status = $db->getValue('employee','work_status',array('emp_id'=>$emp_id));
$qRec = $db->select('medicine_healthcare_availment','*',array('mhd_id'=>$mhd_id,'emp_id'=>$emp_id),'ORDER BY mha_date');

$cert_id = $db->getValue('employee','cert_id',array('emp_id'=>$emp_id));
$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
$file = (file_exists('../img_emp/'.$fileName) && $fileName) ? $fileName : 'blank-pic.png';
function position($emp_id){
    global $db;
    $countPos=0;$position='';
    $qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
    while($rPos = $db->fetch_array($qPos)):
        if($countPos)
            $position .= ' /<br>';
        $position .= $rPos['pos_name'];
        $countPos++;
    endwhile;
    return $position;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>HEALTHCARE AVAILMENT</title>
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid sortable">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Healthcare Availment Form</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <div align="right"><a id="poUnserved" href="#" class="btn btn-info btn-small btn-setting thickbox" onclick="showThis(this.id,'availment_manage.php?empid=<?php echo functions::encode($emp_id)?>','Availment Details')">Add Availment</a></div>
                <br>
                <table class="tablea" border="0" width="100%">
                    <tr>
                        <td width="20%" valign="top"><div align="left"><img width="200" height="200" src="../img_emp/<?php echo $file;?>"></div></td>
                        <td valign="top">
                            <table class="table">
                                <tr>
                                    <td width="10%"><div align="left">Name</div></td>
                                    <td><div align="left"><strong><?php echo $emp_name; ?></strong></div></td>
                                </tr>
                                <tr>    
                                    <td><div align="left">Position</div></td>
                                    <td><div align="left"><strong><?php echo position($emp_id); ?></strong></div></td>
                                </tr>
                                <tr> 
                                    <td><div align="left">Status</div></td>
                                    <td><div align="left"><strong><?php echo $work_status; ?></strong></div></td>
                                </tr>
                                <tr>
                                    <td><div align="left">Healthcare</div></td>
                                    <td><div align="left"><strong><?php echo functions::datearr($mhd_start).' - '.functions::datearr($mhd_end).' ('.functions::formatMoney($healthcare_amount).')'; ?></strong></div></td>
                                </tr>
                            </table>
                        </td>
                        
                    </tr>
                </table><br>
                <table class="table table-bordered table-hover" style="font-size:12px">
                    <thead>
                        <tr>
                            <th width="9%">Availment Date</th>
                            <th width="30%">Purpose</th>
                            <th width="10%">Amount</th>
                            <th width="10%">Balance</th>
                            <th width="8%"><div align="center">Options</div></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $total_consumed=0;
                        while($rRec = $db->fetch_array($qRec)):
                            $total_consumed += $rRec['mha_amount'];
                            $healthcare_amount -= $rRec['mha_amount'];
                    ?>
                        <tr>
                            <td><?php echo functions::datearr($rRec['mha_date']);?></td>
                            <td><?php echo $rRec['mha_purpose'];?></td>
                            <td><?php echo functions::formatMoney($rRec['mha_amount']);?></td>
                            <td><?php echo functions::formatMoney($healthcare_amount);?></td>
                            <td>
                                <div align="center">
                                    <a id="edit<?php echo $rRec['mha_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify this Record" data-rel="tooltip" onclick="showThis(this.id,'availment_manage.php?mha=<?php echo functions::encode($rRec['mha_id']);?>&empid=<?php echo functions::encode($emp_id)?>','Availment Details')"><i class="halflings-icon white pencil"></i></a>
                                    <a id="del<?php echo $rRec['mha_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Record" data-rel="tooltip" href="?mhaDel=<?php echo functions::encode($rRec['mha_id']);?>&empid=<?php echo functions::encode($rRec['emp_id']);?>&mhd=<?php echo functions::encode($mhd_id);?>"><i class="halflings-icon white trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile;?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td><strong><?php echo functions::formatMoney($total_consumed);?></strong></td>
                            <td><strong><?php echo functions::formatMoney($healthcare_amount);?></strong></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
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
function delt(){
    if(confirm('Do you want to remove this record?'))
        return true;
    else
        return false; 
}
</script>
<!-- end: JavaScript-->
</body>
</html>