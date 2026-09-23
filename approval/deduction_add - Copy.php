<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="8" ){
  header("Location: ../");
  die();
}
  $username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
  $user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
  require_once('../class/database.php');
  #require_once('../class/logs.php');
  require_once('../class/functions.php');
  $db = new Database();
  $name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
  #$logs = new Logs();
  #$logs->save('visit');

$did = (isset($_REQUEST['did']) && !empty($_REQUEST['did']) ) ? functions::decode($_REQUEST['did']) : 0;

if( isset($_POST['btnSave']) ){
	$arrUpdate=array();
	$inchargeID="";
    $txDeduct_name = ( isset($_POST['txDeduct_name']) && !empty($_POST['txDeduct_name']) ) ? $_POST['txDeduct_name'] : '';
    $txDeduct_desc = ( isset($_POST['txDeduct_desc']) && !empty($_POST['txDeduct_desc']) ) ? $_POST['txDeduct_desc'] : '';

    if( $txDeduct_name && $txDeduct_desc){
					
  		if( $db->getValue('item_deduction','count(name)',array('name'=>$txDeduct_name))==0 ){ #if there's changes on project name, check if it's unique
  				$q_insert = $db->insertPrint('item_deduction',array('item_id'=>0,'name'=>$txDeduct_name,'description'=>$txDeduct_desc));
          $db->query($q_insert);
          $insertID = $db->insert_id();
          functions::sendTo('deduction_edit.php?did='.functions::encode($insertID));   
  		}
  		else{
  				functions::say('Category name already existed!');
  		}
    }
}

$deduct_name='';$deduct_desc='';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Charge Add</title>
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
            <div class="row-fluid sortable">
                <div class="box span12">
                    <div class="box-header" data-original-title>
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Charge Category Add Form</h2>
                    </div>
                    <div class="box-content">
                  <form class="form-horizontal" method="post">           
                    <table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
                      <tr>
                      	<td width="17%" height="30">Category Name</td>
                        <td width="43%"><input type="text" name="txDeduct_name" id="txDeduct_name" class="span6" value="<?php echo $deduct_name;?>"></td>
                      </tr>
                      <tr>
                      	<td height="30">Description</td>
                        <td><input type="text" name="txDeduct_desc" id="txDeduct_desc" class="span6" value="<?php echo $deduct_desc?>" /></td>
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