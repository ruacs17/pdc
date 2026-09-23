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
$mf_id = (isset($_REQUEST['mf_id']) && !empty($_REQUEST['mf_id']) ) ? functions::decode($_REQUEST['mf_id']) : 0;
$editTrue=0;
$poidEdt='';
$item='';$itemDesc='';$unit='';$brand='';$class='';$type='';$min='';$cat='';$size='';
$msg='';$code='';
if( isset($_POST['btnUpload']) ){
    if($mf_id){
            if( !empty($_FILES['image']) && $_FILES['image']['error'] == 0 ) {
                $max_size = 2000 * 1024; // 500 KB
                // Process image with GD library
                $verifyimg = getimagesize($_FILES['image']['tmp_name']);
                #print_r($verifyimg);
                // Make sure the MIME type is an image
                $pattern = "#^(image/)[^\s\n<]+$#i";

                if( !preg_match($pattern, $verifyimg['mime']) ){
                    functions::say("Only image files are allowed!");
                }
                else if( $_FILES["image"]["size"] > $max_size ){
                    functions::say("Image reached the limit size!");
                }
                else{
                    // Rename both the image and the extension 
                    $uploadfile = '../img_material/'.$mf_id.'.jpg';
                    // Upload the file to a secure directory with the new name and extension
                    if(file_exists('../img_material/'.$uploadfile))
                        unlink('../img_material/'.$uploadfile);
                    if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
                        if( $db->getValue('images_material','mf_id',array('mf_id'=>$mf_id)) )
                            $db->update('images_material',array('original_name'=>basename($_FILES['image']['name'])),array('mf_id'=>$mf_id));
                        else
                            $db->insert('images_material',array('name'=>basename($uploadfile),'original_name'=>basename($_FILES['image']['name']),'mime_type'=>$_FILES['image']['type'],'mf_id'=>$mf_id));
                    }else{
                        functions::say("Image upload failed!");
                    }
                }
            }
    }
}
if( isset($_POST['btnSave']) ){

    $txCAD = ( isset($_POST['txCAD']) && !empty($_POST['txCAD']) ) ? trim($_POST['txCAD']) : NULL;
    $txEngrStand = ( isset($_POST['txEngrStand']) && !empty($_POST['txEngrStand']) ) ? trim($_POST['txEngrStand']) : NULL;
    $txDUPA = ( isset($_POST['txDUPA']) && !empty($_POST['txDUPA']) ) ? trim($_POST['txDUPA']) : NULL;
    $txSpec = ( isset($_POST['txSpec']) && !empty($_POST['txSpec']) ) ? trim($_POST['txSpec']) : NULL;

    if($mf_id){
        $db->update('material_reference',array('cad'=>$txCAD,'engr_standard'=>$txEngrStand,'dupa'=>$txDUPA,'specification'=>$txSpec),array('mf_id'=>$mf_id));
    }
    functions::sendTo($_SERVER['PHP_SELF'].'?mf_id='.functions::encode($mf_id));
}

$qvedt = $db->select('material_reference','*',array('mf_id'=>$mf_id));
$rvedt = $db->fetch_array($qvedt);
$item = $rvedt['item'];
$unit = $rvedt['unit'];
$brand = $rvedt['brand'];
$code = $rvedt['m_code'];
$class = $rvedt['classification'];
$type = $rvedt['mtype'];
$min = $rvedt['least_required'];
$size = $rvedt['msize'];
$itemDesc = $rvedt['mdescription'];
$cat = $rvedt['category'];
$cad = $rvedt['cad'];
$engr_standard = $rvedt['engr_standard'];
$dupa = $rvedt['dupa'];
$specification = $rvedt['specification'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Material Reference</title>
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
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Material Reference</h2>
        </div>
        <?php if($mf_id){?>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li class="active"><a href="admin-material-reference-detail.php?mf_id=<?php echo functions::encode($mf_id)?>">More Detail</a></li>
                <li ><a href="admin-material-reference-add.php?mf_id=<?php echo functions::encode($mf_id)?>">Material Reference</a></li>
            </ul>
        </div>
        <?php }?>
        <div class="box-content">
            <form method="post" enctype="multipart/form-data">
                <table width="100%">
                    <tr>
                        <td width="60%" bgcolor="#f8f8f8">
                            <table class="table table-bordered" border="0" >
                                <tr>
                                    <td>Code:</td>
                                    <td><?php echo $code?></td>
                                </tr>
                                <tr>
                                    <td>Classification:</td>
                                    <td><?php echo $class?></td>
                                </tr>
                                <tr>
                                    <td>Type:</td>
                                    <td><?php echo $type?></td>
                                </tr>
                                <tr>
                                    <td>Item:</td>
                                    <td><?php echo $item?></td>
                                </tr>
                                <tr>
                                    <td>Description:</td>
                                    <td><?php echo $itemDesc?></td>
                                </tr>
                                <tr>
                                    <td>Size:</td>
                                    <td><?php echo $size?></td>
                                </tr>
                                <tr>
                                    <td>Unit:</td>
                                    <td><?php echo $unit?></td>
                                </tr>
                                <tr>
                                    <td>Brand:</td>
                                    <td><?php echo $brand?></td>
                                </tr>
                                <tr>
                                    <td>CAD Detail:</td>
                                    <td><textarea style="width:450px;" rows="1" name="txCAD" id="txCAD"><?php echo $cad;?></textarea></td>
                                </tr>
                                <tr>
                                    <td>Engineering Standards:</td>
                                    <td><textarea style="width:450px;" rows="1" name="txEngrStand" id="txEngrStand"><?php echo $engr_standard;?></textarea></td>
                                </tr>
                                <tr>
                                    <td>Specifications:</td>
                                    <td><textarea style="width:450px;" rows="1" name="txSpec" id="txSpec"><?php echo $specification;?></textarea></td>
                                </tr>
                                <tr>
                                    <td>DUPA:</td>
                                    <td><textarea style="width:450px;" rows="1" name="txDUPA" id="txDUPA"><?php echo $dupa;?></textarea></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td><input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary"></td>
                                </tr>
                            </table>
                        </td>
                        <td>&nbsp;</td>
                        <td valign="top" align="center">
                            <table>
                                <tr>
                                    <td>
                                        <?php
                                        $fileName = $db->getValue('images_material','name',array('mf_id'=>$mf_id));
                                        $file = ($fileName) ? '../img_material/'.$fileName : '../img_material/blank-pic.jpg';
                                        ?>
                                        <a id="adc" href="#" class="thickbox" onclick="showThis(this.id,'admin-material-reference-image.php?mf_id=<?php echo functions::encode($mf_id)?>','Item Image Preview','1')">
                                        <img height="400" width="400" src="<?php echo $file?>">
                                        </a><br>
                                        Select image to upload <input type="file" name="image">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center"><input type="submit" name="btnUpload" id="btnUpload" value="Upload" class="btn btn-primary"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <?php echo $msg;?>
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
    $('#btnAdd,#btnSave').click(function(){
        $('#msgtxItem').html("");
        $('#msgtxQty').html("");
        $('#msgtxQtyDel').html("");
        $('#MsgSelClass').html("");
        $('#MsgSelType').html("");
        $('#MsgSelCat').html("");
          
        if( $('#selCat').val()=="" ){
            $('#MsgSelCat').html("Specify Category!");
            $('#selCat').focus();
            res=false;
        }
        else if( $('#selClass').val()=="" ){
            $('#MsgSelClass').html("Specify Classification!");
            $('#selClass').focus();
            res=false;
        }
        else if( $('#selType').val()=="" ){
            $('#MsgSelType').html("Specify Type!");
            $('#selType').focus();
            res=false;
        }
        else if( $('#txItem').val()=="" ){
            $('#msgtxItem').html("Specify Item!");
            $('#txItem').focus();
            res=false;
        }
        else if( $('#txQty').val()=="" ){
            $('#msgtxQty').html("Required!");
            $('#txQty').focus();
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