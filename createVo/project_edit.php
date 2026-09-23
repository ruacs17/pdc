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

$pid = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
if( isset($_POST['btnCancel']) ){
    functions::sendTo('project_view.php?pid='.functions::encode($pid));
}
if( isset($_POST['btnSave']) ){
    $arrUpdate=array();
    $inchargeID="";
    $txProj_name = ( isset($_POST['txProj_name']) && !empty($_POST['txProj_name']) ) ? $_POST['txProj_name'] : '';
    $txProj_desc = ( isset($_POST['txProj_desc']) && !empty($_POST['txProj_desc']) ) ? $_POST['txProj_desc'] : '';
    $txUnder = ( isset($_POST['txUnder']) && !empty($_POST['txUnder']) ) ? $_POST['txUnder'] : '';

    $arrUpdate = array('proj_name'=>$txProj_name);

    if($txProj_desc)
    	$arrUpdate = array_merge($arrUpdate,array('proj_desc'=>($txProj_desc)));

    if($txUnder)
        $arrUpdate = array_merge($arrUpdate,array('owned_by'=>$txUnder));
    else
        functions::say('Project Under required!');

    if( $txProj_name && $txUnder ){
      	if( $db->getValue('project','proj_name',array('proj_id'=>$pid))==$txProj_name ){ #no changes on project name
      		$db->update('project',$arrUpdate,array('proj_id'=>$pid));
            functions::sendTo('project_view.php?pid='.functions::encode($pid));		
      	}
      	else if( $db->getValue('project','count(proj_id)',array('proj_name'=>$txProj_name))==0 ){ #if there's changes on project name, check if it's unique
      		$db->update('project',$arrUpdate,array('proj_id'=>$pid));
            functions::sendTo('project_view.php?pid='.functions::encode($pid));
      	}
      	else{
      		functions::say('Project name already existed!');
      	}
    }
    functions::sendTo($_SERVER['PHP_SELF'].'?pid='.functions::encode($pid));
}

$proj_name='';$proj_desc='';$txUnder="";
$qProj = $db->select('project','*',array('proj_id'=>$pid));
if($db->num_rows($qProj)){
    $projInfo = $db->fetch_array($qProj);
    $proj_name = $projInfo['proj_name'];
    $proj_desc = $projInfo['proj_desc'];
    $txUnder = $projInfo['owned_by'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Project Modify</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/style.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Update Form</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <table width="80%" align="center" border="0" class="table table-bordered table-hover">
                    <tr>
                      	<td width="17%" height="30">Project Name</td>
                        <td width="43%"><input type="text" name="txProj_name" id="txProj_name" class="span6" value="<?php echo $proj_name;?>"></td>
                    </tr>
                    <tr>
                      	<td height="30">Project Description</td>
                        <td><input type="text" name="txProj_desc" id="txProj_desc" class="span6" value="<?php echo $proj_desc?>" /></td>
                    </tr>
                    <tr>
                        <td height="30">Under</td>
                        <td>
                            <select name="txUnder" id="txUnder" style="width:300px;">
                                <option value="">--select--</option>
                                <?php
                                $qUnder = $db->query('SELECT * FROM project WHERE project="0"');
                                while($rUnder = $db->fetch_array($qUnder)):
                                ?>
                                <option value="<?php echo $rUnder['proj_id']?>" <?php if($txUnder==$rUnder['proj_id'])echo 'selected="selected"';?>><?php echo $rUnder['proj_name']?></option>
                                <?php endwhile;?>
                            </select>
                        </td>
                    </tr>
                </table>
                <div align="center">
                    <input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary">
                    <input type="submit" name="btnCancel" id="btnCancel" value="Cancel" class="btn">
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
<!-- end: JavaScript-->
</body>
</html>