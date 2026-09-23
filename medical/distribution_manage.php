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
$md_id = (isset($_REQUEST['mdid']) && !empty($_REQUEST['mdid']) ) ? functions::decode($_REQUEST['mdid']) : 0;

$q = $db->select('medicine_distribution','*',array('md_id'=>$md_id));
$r = $db->fetch_array($q);
$proj_id= $r['proj_id'];
$md_date = $r['md_date'];



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Medical Distribution Create</title>
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
    <style>.tdSpace{padding: 12px 0px 4px 0px;}</style>
<?php 
if( isset($_POST['btnAdd']) ){

    $arr = array();
    $proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? trim($_POST['selProj']) : '';
    $md_date = ( isset($_POST['txDistDate']) && !empty($_POST['txDistDate']) ) ? trim($_POST['txDistDate']) : '';

    if( $proj_id && $md_date ){
        if($md_id){
            $md_id = $db->update('medicine_distribution',array('proj_id'=>$proj_id,'md_date'=>$md_date),array('md_id'=>$md_id));    
            functions::say('Changes Saved!');
            functions::sendTo('?mdid='.functions::encode($md_id));
        }
        else{
            $md_id = $db->insert('medicine_distribution',array('proj_id'=>$proj_id,'md_date'=>$md_date));
            $md_no = $db->getValue('medicine_distribution','concat(substring(md_date,1,4),substring(md_date,6,2),md_id) as dte',array('md_id'=>$md_id));
            $db->update('medicine_distribution',array('md_no'=>$md_no),array('md_id'=>$md_id));
                if($md_id)
                    functions::sendTo('distribution_item_manage.php?mdid='.functions::encode($md_id));   
        }
    }
    else{
        functions::say('Please fill up the form properly!');
    }
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Distribution Manage Form</h2>
        </div>
        <div class="box-content">
            <div align="center">
                <form method="post">
                    <table width="75%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <th width="25%" align="right" scope="row">&nbsp;</th>
                            <td width="3%">&nbsp;</td>
                            <td width="50%">&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Project</th>
                            <td>&nbsp;</td>
                            <td style="padding: 25px 0px 4px 0px;">
                                <select name="selProj" id="selProj" data-rel="chosen" style="width:600px;">
                                    <option value="">--select--</option>
                                    <?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
                                          while($rProj = $db->fetch_array($qProj)):
                                    ?>
                                    <option value="<?php echo $rProj['proj_id']?>" <?php if($proj_id==$rProj['proj_id']){echo 'selected="selected"';} ?>><?php echo ($rProj['proj_name']);?></option>
                                    <?php endwhile;?>
                                </select>
                                <span class="help-inline warning" id="msgProject" style="font-weight:bold;" name="msgProject"></span>
                            </td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Distribution Date</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <a href="javascript:NewCssCal('txDistDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
                                <input name="txDistDate" type="text" class="span6 mytextbox" id="txDistDate" value="<?php echo ($md_date) ? $md_date : date('Y-m-d')?>" style="width: 90px;" readonly>
                                <span class="help-inline warning" style="font-weight:bold;" id="msgTxdate" name="msgTxdate"></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="tdSpace" colspan="3"><div align="center"><input type="submit" name="btnAdd" id="btnAdd" value="Save Distribution Report" class="btn btn-primary"></div></td>
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
<script src="../js/wxhBox.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function ask(){
    if(confirm('Do you want to save this Distribution Report?'))
        return true;
    else
        return false; 
}
$(document).ready(function(){
    var res = false;
    $('#btnAdd').click(function(){
        $('#msgProject').html("");

        if( $('#selProj').val()=="" ){
            $('#msgProject').html("Project Required!");
            res=false;
        }
        else{
            if(ask())
                res=true;
        }
        return res;
    });
});
</script>
<!-- end: JavaScript-->
</body>
</html>