<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/read_excel_xlsx.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$filename='';$uploaded_file='';$filetype='';
if( isset($_POST['upload']) ){
    echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
    echo 'Please wait while attendance is still processing........<br>';
    echo '<div id="spinner"></div>';
    if( !empty($_FILES['file']['name']) ){
        $filetype = strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
        $filename = date('Ymd');
        $path = '../attendance/';
        
        if ($_FILES["file"]["error"] > 0){
            echo "Error: " . $_FILES["file"]["error"] . "<br />";
        }
        else{
            if( ( $filetype==='xlsx' || $filetype==='xls' ) && ($_FILES["file"]["size"] <= 20000000) ){
                $fileExt = $filetype;
                if (file_exists($path.$filename.'.'.$fileExt)){
                    $count = 1;
                    while(file_exists($path.$filename.'.'.$fileExt)){
                        $filename = date('Ymd').'('.$count.')';
                        $uploaded_file=$path.$filename.'.'.$fileExt;
                        $count++;
                    }
                    move_uploaded_file($_FILES["file"]["tmp_name"],$uploaded_file);
                }
                else{
                    $uploaded_file=$path.$filename.'.'.$fileExt;
                    move_uploaded_file($_FILES["file"]["tmp_name"],$uploaded_file);
                }
            }
            else{
                functions::say("Inappropriate File type");
                functions::sendTo($_SERVER['PHP_SELF']);
            }
        }
    }
    else{
        functions::say('Please upload an excel file.');
        functions::sendTo($_SERVER['PHP_SELF']);
    }

    if($uploaded_file){
        $arrDataParam = array('filename'=>$uploaded_file,'filetype'=>$filetype);
        $serData = serialize($arrDataParam);
        functions::sendTo('attendance_upload_save.php?fn='.functions::encode($serData));
    // End of saving the attendance recordwin7
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Employee Attendance Upload</title>
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
    <script type="text/javascript" src="../js/datetimepicker_css.js"></script>
    
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
<div class="row-fluid">

    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post" enctype="multipart/form-data">
                <div align="center" style="padding-bottom: 15px;"><h2>ATTENDANCE UPLOAD</h2></div>
                <div align="center">
                <table border="0">
                    <tr>
                        <td>
                            <div align="left" style="padding-bottom: 15px;">Select Excel file to upload: <input type="file" name="file"></div>
                            <div align="center" style="padding-top: 40px;">
                                <input type="submit" name="upload" id="upload" value=" UPLOAD " class="btn btn-primary">
                            </div>
                        </td>
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
<!-- end: JavaScript-->
</body>
</html>