<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="9" ){
  header("Location: ../");
  die();
}
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

$mon='';$day='';$year='';$proj_name='';$proj_desc='';$incharge=0;$cost='';$remarks='';$status='';
$monCompletion='';$dayCompletion='';$yearCompletion='';
$monReviseCompletion='';$dayReviseCompletion='';$yearReviseCompletion='';
$monCompleted='';$dayCompleted='';$yearCompleted='';
$monContract='';$dayContract='';$yearContract='';
$monNoa='';$dayNoa='';$yearNoa='';
$monNtp='';$dayNtp='';$yearNtp='';
$daysRemaining=0;
$daysElapsed=0;
$commitment=0; $consultancy=0; $finders=0; $technical=0;
$qProj = $db->select('project','*',array('proj_id'=>$pid));
if($db->num_rows($qProj)){
 	$projInfo = $db->fetch_array($qProj);
 
	$proj_name = $projInfo['proj_name'];
	$proj_desc = $projInfo['proj_desc'];
	$incharge=$projInfo['incharge'];
	$cost = $projInfo['proj_cost'];
	$remarks=$projInfo['remarks'];
	$status=$projInfo['status'];
	$commitment = $projInfo['commitment'];
	$consultancy = $projInfo['consultancy'];
	$finders = $projInfo['finders'];
	$technical = $projInfo['technical'];	 
	$dateStart = $projInfo['date_start'];
	$dateCompletion = $projInfo['date_completion'];	
	$dateReviseCompletion = $projInfo['date_revise_completion'];
	$dateCompleted = $projInfo['date_completed'];
	$dateContract = $projInfo['date_contract'];
	$dateNoa = $projInfo['date_noa'];
	$dateNtp = $projInfo['date_ntp'];
	$daysExtension = functions::date_diff($dateCompletion,$dateReviseCompletion);
	$daysDuration = functions::date_diff($dateStart,$dateCompletion);

  $daysElapsed=0;
  $daysRemaining=0;
  
  if($dateStart <= date('Y-m-d')){

    if( isset($projInfo['date_completed']) ){
      $daysElapsed = functions::date_diff($dateStart,$projInfo['date_completed']);

      if($projInfo['date_revise_completion'])
        $daysRemaining = functions::date_diff($projInfo['date_completed'],$dateReviseCompletion);
      else if($projInfo['date_completion'])
        $daysRemaining = functions::date_diff($projInfo['date_completed'],$projInfo['date_completion']);
    }
    else if( !isset($projInfo['date_completed']) ){
      $daysElapsed = functions::date_diff($dateStart,date('Y-m-d'));
      if($projInfo['date_revise_completion'])
        $daysRemaining = functions::date_diff(date('Y-m-d'),$dateReviseCompletion);
      else if($projInfo['date_completion'])
        $daysRemaining = functions::date_diff(date('Y-m-d'),$projInfo['date_completion']);
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Project Detail</title>
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
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Detail</h2>
                    </div>
                    <div class="box-content" align="center">        
                    <table width="80%" align="center" border="0" class="table table-bordered table-hover">
                      <tr>
                      	<td width="17%" height="30">Project Name</td>
                        <td width="43%"><strong><?php echo $proj_name;?></strong></td>
                        <td>NOA</td>
                        <td><strong><?php echo functions::datearr($dateNoa);?></strong></td>
                      </tr>
                      <tr>
                      	<td height="30">Project Description</td>
                        <td><strong><?php echo $proj_desc?></strong></td>
                        <td>NTP</td>
                        <td><strong><?php echo functions::datearr($dateNtp);?></strong></td>
                      </tr>
                      <tr>
                      	<td height="30">In Charge</td>
                        <td><strong><?php echo $db->getValue('users','concat(lname,", ",fname)',array('user_id'=>$incharge))?></strong></td>
                        <td>Days Elapsed</td>
                        <td><strong><?php echo $daysElapsed;?></strong></td>
                      </tr>
                      <tr>
                      	<td height="30">Cost</td>
                        <td><strong><?php echo functions::formatMoney($cost);?></strong></td>
                        <td>Days Remaining</td>
                        <td><strong><?php echo $daysRemaining;?></strong></td>
                      </tr>
                      <tr>
                      	<td height="30">Date Started</td>
                        <td><strong><?php echo functions::datearr($dateStart);?></strong></td>
                        <td>Commitment</td>
                        <td><strong><?php echo $commitment?>%</strong></td>
                      </tr>
                      <tr>
                      	<td height="30">Target Date of Completion</td>
                        <td><strong><?php echo functions::datearr($dateCompletion);?></strong></td>
                        <td>Consultancy</td>
                        <td><strong><?php echo $consultancy;?>%</strong></td>
                      </tr>
                      <tr>
                        <td height="30">Contract Duration <br>(Calendar Days)</td>
                        <td><strong><?php echo $daysDuration;?></strong></td>
                        <td>Finders</td>
                        <td><strong><?php echo $finders?>%</strong></td>
                      </tr>
                      <tr>
                        <td width="17%">Revised Target Date of Completion</td>
                        <td width="43%"><strong><?php echo functions::datearr($dateReviseCompletion);?></strong></td>
                        <td>Technical</td>
                        <td><strong><?php echo $technical?>%</strong></td>
                      </tr>
                      <tr>
                        <td>Approved Time Extension <br>(Calendar Days)</td>
                        <td><strong><?php echo $daysExtension;?></strong></td>
                        <td>Status</td>
                        <td><strong><?php echo $status;?></strong></td>
                      </tr>
                      <tr>
                        <td width="18%">Date Completed</td>
                        <td width="22%"><strong><?php echo functions::datearr($dateCompleted);?></strong></td>
                        <td>Remark</td>
                        <td><strong><?php echo $remarks;?></strong></td>
                      </tr>
                      <tr>
                        <td>Contract</td>
                        <td><strong><?php echo functions::datearr($dateContract);?></strong></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>            
                    </table>
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

