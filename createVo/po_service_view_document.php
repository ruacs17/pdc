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

$vw = (isset($_REQUEST['vw']) && !empty($_REQUEST['vw']) ) ? $_REQUEST['vw'] : 0;
$v_page = ($vw==1) ? 'po_service_view_only.php' : 'po_service_view.php';
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
if( isset($_POST['btnSave']) && $po_id){
    if( !empty($_FILES['docpdf']) && $_FILES['docpdf']['error'] == 0 ) {
        #print_r($_FILES['docpdf']);
        $uploaddir = '../po_doc/';
        // Make sure the MIME type is an image
        #$pattern = "#^(/pdf)[^\s\n<]+$#i";
        $pattern = '/pdf/';
        if( !preg_match($pattern, $_FILES['docpdf']['type']) ){
            functions::say("Only PDF is allowed!");
        }
        else{
            // Upload the file to a secure directory with the new name and extension
            if( file_exists('../po_doc/'.$po_id.'.pdf') )
                unlink('../po_doc/'.$po_id.'.pdf');
            move_uploaded_file($_FILES['docpdf']['tmp_name'], '../po_doc/'.$po_id.'.pdf');
            functions::say('Document Successfully uploaded!');
            functions::sendTo($_SERVER['PHP_SELF'].'?po_id='.functions::encode($po_id));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Purchase Order Item</title>
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
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>P.O Service Document</h2>
        </div>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li class="active"><a href="po_service_view_document.php?po_id=<?php echo functions::encode($po_id)?>" style="opacity:.9">Document</a></li>
                <li><a href="<?php echo $v_page?>?po_id=<?php echo functions::encode($po_id)?>">Service Detail</a></li>
            </ul>
            <form method="post" enctype="multipart/form-data">
                <div align="center">
                    <?php
                    $pdf_name = '../po_doc/'.$po_id.'.pdf';
                    if(file_exists($pdf_name)){?>
                    <embed src="<?php echo $pdf_name?>" width="80%" height="1000px" /><br><br><br><br><br>
                    <?php }?>
                    <img height="200" width="200" src="../po_doc/blank-pdf.png"><br>
                    Select PDF to upload: <input type="file" name="docpdf"><br><br>
                    <input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary">
                </div><br>

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
    if(confirm('Do you want to remove this?'))
        return true;
    else
        return false; 
}

$(document).ready(function(){
    var res = false;
    $('#btnAdd,#btnSave').click(function(){
        $('#msgtxItem').html("");
        $('#msgtxQty').html("");
        $('#msgtxQtyDel').html("");
        
        if( $('#txItem').val()=="" ){
            $('#msgtxItem').html("Item Required!");
            res=false;
        }
        else if( $('#txCost').val()=="" ){
            $('#msgtxCost').html("Required!");
            $('#txCost').focus();
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