<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');

$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
#$fn = (isset($_REQUEST['fn']) && !empty($_REQUEST['fn']) ) ? functions::decode($_REQUEST['fn']).'.xlsx' : 0;
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
if($eatid){
    if( !isset($_SESSION['arr_attendance_emp']) ){
        $qEmpID = $db->select('emp_attendance_detail ead, employee e','DISTINCT emp_no',array('eat_id'=>$eatid),'AND ead.emp_id=e.emp_id');
        $selEmps = array();
        while($rEmpID = $db->fetch_array($qEmpID)):
            $selEmps[$rEmpID['emp_no']]=$rEmpID['emp_no'];
        endwhile;
        if( !isset($_SESSION['arr_attendance_emp']) )
            $_SESSION['arr_attendance_emp']=$selEmps;
    }
}
$confirmed = $db->getValue('emp_attendance','confirmed',array('eat_id'=>$eatid));
$fn = $db->getValue('emp_attendance','excelfile',array('eat_id'=>$eatid));
$ft = $db->getValue('emp_attendance','filetype',array('eat_id'=>$eatid));
$path = '../attendance/';
$filename=$path.$fn;
$totalRow = 0;
$content = array();
if($ft==='xlsx'){
    require_once('../class/read_excel_xlsx.php');
    if($fn){
        if ( $xlsx = read_excel_xlsx::parse($filename) ) {
            $content = $xlsx->rows() ;
        } else {
            echo read_excel_xlsx::parseError();
        }
        $totalRow = count($content);
    }
}
if($ft==='xls'){
    require_once('../class/read_excel_xls.php');
    if($fn){
        if ( $xls = read_excel_xls::parse($filename) ) {
            $content = $xls->rows() ;
        } else {
            echo read_excel_xls::parseError();
        }
        $totalRow = count($content);
    }
}



$arrEmployee=array();
$arrAttendanceRecord=array();
$arrAttendanceDate = isset($content[3]) ? $content[3] : array();
$dateRange = isset($content[2][2]) ? $content[2][2] : '';
$dateRangeFrom='';$dateRangeTo='';$yearFrom='';$dateFrom='';$monFrom='';
if($dateRange){
    $dateRangeExp = explode('~',$dateRange);
    $dateRangeFrom = isset($dateRangeExp[0]) ? trim($dateRangeExp[0]) : '';
    $dateRangeTo = isset($dateRangeExp[1]) ? trim($dateRangeExp[1]) : '';
    $yearFrom = substr($dateRangeFrom,0,4);
    $monFrom = substr($dateRangeFrom,5,2);
    $dateFrom = substr($dateRangeFrom,8,2);
}

$arrDate=array();
foreach($arrAttendanceDate as $aAindx => $aAVal):
    //if($aAVal)
        $arrDate[$aAindx]=$aAVal;
endforeach;

$emp_id=''; $emp_name='';
for($t=4;$t<$totalRow; $t++):
    #echo $t;
    #echo '<br>';
    if( ($t%2)==0 ){
        $emp_id=isset($content[$t][2]) ? $content[$t][2] : '';
        #$emp_name=isset($content[$t][10]) ? $content[$t][10] : '';
        $emp_name =  $db->getValue('employee','concat(lname,", ",fname," ",mname)',array('emp_no'=>$emp_id));
        $emp_name = ($emp_name) ? ucwords(strtolower($emp_name)) : 'zzzz';
        $arrEmployee[$emp_id]=$emp_name;
    }

endfor;

asort($arrEmployee);

if( isset($_POST['btnSubmit']) && $fn){
    $arrEmpID = isset($_POST['checkbox']) ? $_POST['checkbox'] : array();
    if( count($arrEmpID) ){
        $_SESSION['arr_attendance_emp']=$arrEmpID;
        #functions::sendTo('attendance_read.php?eatid='.functions::encode($eatid));
?>
        <link id="base-style" href="../css/loader.css" rel="stylesheet">
        Please wait while attendance is still processing........Don't Close this browser!<br>
        <div id="spinner"></div>
<?php
        functions::sendTo('attendance_save.php?eatid='.functions::encode($eatid));
        #print_r($_SESSION['arr_attendance_emp']);
    }
}
$arrSelEmp = (isset($_SESSION['arr_attendance_emp'])) ? $_SESSION['arr_attendance_emp'] : array();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Employee Attendance Select</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/style.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
    <script>
var color = ""; 
var checked = false;
var id;
function checkedAll(formid){
    var arr = new Array();
    var n;
    var u=0;
    var w =0;
        if (checked == false){
            checked = true;
            color = "#E8D7D7";
        }
        else{
            checked = false;
            color = "";
        }
                
        for (var i = 0; i < document.getElementById(formid).elements.length; i++) {
            document.getElementById(formid).elements[i].checked = checked;
            id = document.getElementById(formid).elements[i].value; 
            if(id){
                arr[u] = id;
                u++;
            }
        }
        for(var w = 1; w < parseInt(u); w++){
            try{
                document.getElementById("tr["+arr[w]+"]").bgColor = color;  
            }
                catch(err){
            }    
        }
}
function checkit(obj_name,trID){
    color = "#E8D7D7";
    if(document.getElementById(obj_name).checked == false)
        document.getElementById(obj_name).checked = true;
    else
        document.getElementById(obj_name).checked = false;
    if(document.getElementById(obj_name).checked == true)
        document.getElementById(trID).bgColor = color;
    else
        document.getElementById(trID).bgColor = "";
}
function checkClr(chkID,trID){
    color = "#E8D7D7";
    if(document.getElementById(chkID).checked == true)
        document.getElementById(trID).bgColor = color;
    else
        document.getElementById(trID).bgColor = "";

}
    </script>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
        </div>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li><a href="attendance_payroll_view.php?eatid=<?php echo functions::encode($eatid)?>">Payroll</a></li>
                <li style="display:none;"><a href="attendance_read.php?eatid=<?php echo functions::encode($eatid)?>">Attendance</a></li>
                <li class="active"><a href="attendance_select.php?eatid=<?php echo functions::encode($featidn)?>" style="opacity:.9">Select Name</a></li>
                <li><a href="attendance_upload.php?eatid=<?php echo functions::encode($eatid)?>">Upload Att. Log</a></li>
            </ul>
        </div>
        <div class="box-content">
            <form class="form-horizontal" id="showform" name="showform" method="post">
                <div align="center" style="padding-bottom: 15px;"><h2>SELECT EMPLOYEE</h2></div>
                <div align="right"><input type="submit" class="btn btn-primary" name="btnSubmit" id="btnSubmit" value="Generate Payroll"  <?php if($confirmed)echo 'disabled="disabled"';?>></div><br>
                <table width="80%" align="center" class="table table-hover table-bordered" border="0">
                    <tr>
                        <th width="15%" height="30"><div align="left">ID Number</div></th>
                        <th><div align="left">Name</div></th>
                        <th width="8%"><div align="center">Select All <br><input type="checkbox" name="checkall" id="checkall" value="checkall" onclick="checkedAll('showform')"   <?php if($confirmed)echo 'disabled="disabled"';?>/></div></th>
                    </tr>
                        <tr>
                        <td height="30">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php
                    foreach($arrEmployee as $employee => $emp_name):
                        $emp_id = $employee;
                        $emp_name = ($emp_name=='zzzz') ? '--Not Registered--' : ucwords(strtolower($emp_name));
                    ?>
                    <tr id="<?php echo "tr[".$employee."]";?>" <?php if( isset($arrSelEmp[$emp_id]) ){echo "bgcolor='#E8D7D7'";}?> >
                        <td height="30" onclick="checkit('checkbox[<?php echo $employee?>]','<?php echo "tr[".$employee."]";?>')" <?php echo ($emp_name=='--Not Registered--') ? '' : 'style="cursor: pointer;"';?>><div align="left"><?php echo $employee;?></div></td>
                        <td onclick="checkit('checkbox[<?php echo $employee?>]','<?php echo "tr[".$employee."]";?>')" <?php echo ($emp_name=='--Not Registered--') ? '' : 'style="cursor: pointer;"';?>><div align="left"><?php echo $emp_name;?></div></td>
                        <td>
                            <div align="center">
                                <?php if($emp_name!="--Not Registered--"){?>
                                <input type="checkbox" name="checkbox[<?php echo $emp_id?>]" id="checkbox[<?php echo $emp_id?>]" value="<?php echo $emp_id?>" onClick="checkClr('checkbox[<?php echo $emp_id?>]','<?php echo "tr[".$employee."]";?>')" <?php if( isset($arrSelEmp[$emp_id]) ){echo 'checked="checked"';}?>  <?php if($confirmed)echo 'disabled="disabled"';?>/>
                                <?php }?>
                            </div>
                        </td>
                    </tr>
                <?php
                    endforeach;?>
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
<!-- end: JavaScript-->
</body>
</html>

