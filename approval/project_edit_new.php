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
	$txProj_remark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? $_POST['txRemark'] : '';
	$txProj_status = ( isset($_POST['projStatus']) && !empty($_POST['projStatus']) ) ? $_POST['projStatus'] : '';
	$txProj_cost = ( isset($_POST['txProjCost']) && !empty($_POST['txProjCost']) ) ? functions::moneyToDouble($_POST['txProjCost']) : "";
  $targetDayCompletion = ( isset($_POST['targetDayCompletion']) && !empty($_POST['targetDayCompletion']) ) ? $_POST['targetDayCompletion'] : 0; 
  $reviseTargetDayCompletion = ( isset($_POST['reviseTargetDayCompletion']) && !empty($_POST['reviseTargetDayCompletion']) ) ? $_POST['reviseTargetDayCompletion'] : 0;

	
	$arrUpdate = array('proj_name'=>$txProj_name,'status'=>$txProj_status);

	if($txProj_desc)
		$arrUpdate = array_merge($arrUpdate,array('proj_desc'=>($txProj_desc)));
		
	if($txProj_remark)
		$arrUpdate = array_merge($arrUpdate,array('remarks'=>($txProj_remark)));
			
	if($txProj_cost)
		$arrUpdate = array_merge($arrUpdate,array('proj_cost'=>($txProj_cost * 1)));

    if( isset($_POST['txIncharge']) ){
        $incharge = (isset($_POST['txIncharge'])) ? explode('-',$_POST['txIncharge']) : array();
        $inchargeID = $incharge[0];
      if( $db->getValue('users','count(user_id)',array('user_id'=>$inchargeID)) )
        $arrUpdate = array_merge($arrUpdate,array('incharge'=>$inchargeID));
    }
	

    $txDateStart = ( isset($_POST['txDateStart']) && !empty($_POST['txDateStart']) ) ? $_POST['txDateStart'] : '';
    if($txDateStart){
		  $arrUpdate = array_merge($arrUpdate,array('date_start'=>$txDateStart));

      $txDateCompletion = functions::AddDay($txDateStart,$targetDayCompletion);
      $arrUpdate = array_merge($arrUpdate,array('date_completion'=>$txDateCompletion));
      $txDateReviseCompletion = functions::AddDay($txDateCompletion,$reviseTargetDayCompletion);
      $arrUpdate = array_merge($arrUpdate,array('date_revise_completion'=>$txDateReviseCompletion));        
    }
	
    $txDateCompleted = ( isset($_POST['txDateCompleted']) && !empty($_POST['txDateCompleted']) ) ? $_POST['txDateCompleted'] : '';
    if($txDateCompleted)
		  $arrUpdate = array_merge($arrUpdate,array('date_completed'=>$txDateCompleted));
	
    $txDateContract = ( isset($_POST['txDateContract']) && !empty($_POST['txDateContract']) ) ? $_POST['txDateContract'] : '';
    if($txDateContract)
		  $arrUpdate = array_merge($arrUpdate,array('date_contract'=>$txDateContract));
			
    $txDateNoa = ( isset($_POST['txDateNoa']) && !empty($_POST['txDateNoa']) ) ? $_POST['txDateNoa'] : '';
    if($txDateNoa)
		  $arrUpdate = array_merge($arrUpdate,array('date_noa'=>$txDateNoa));
			
    $txDateNtp = ( isset($_POST['txDateNtp']) && !empty($_POST['txDateNtp']) ) ? $_POST['txDateNtp'] : '';
    if($txDateNtp)
		  $arrUpdate = array_merge($arrUpdate,array('date_ntp'=>$txDateNtp));
    
    if( $txProj_name && $txDateStart ){
					
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
    functions::sendTo('project_edit.php?pid='.functions::encode($pid));
}
$dateStart='';
$dateCompletion='';
$dateContract='';
$dateNoa='';
$dateNtp='';
$dateReviseCompletion='';
$dateCompleted='';
$targetDayCompletion=0;
$reviseTargetDayCompletion=0;
$qProj = $db->select('project','*',array('proj_id'=>$pid));
if($db->num_rows($qProj)){
 $projInfo = $db->fetch_array($qProj);

 $proj_name = $projInfo['proj_name'];
 $proj_desc = $projInfo['proj_desc'];
 $incharge=$projInfo['incharge'];
 $cost = ($projInfo['proj_cost']) ? functions::formatMoney($projInfo['proj_cost']) : 0;
 $remarks=$projInfo['remarks'];
 $status=$projInfo['status'];
 
 $dateContract = ($projInfo['date_contract']) ? $projInfo['date_contract'] : '';
 $dateNoa = ($projInfo['date_noa']) ? $projInfo['date_noa'] : '';
 $dateNtp = ($projInfo['date_ntp']) ? $projInfo['date_ntp'] : '';
 $dateCompleted = ($projInfo['date_completed']) ? $projInfo['date_completed'] : '';


 $datestart = $projInfo['date_start'];
 $dateReviseCompletion=$projInfo['date_revise_completion'];
 $targetDayCompletion = ($projInfo['date_completion']) ? functions::date_diff($datestart,$projInfo['date_completion']) : 0;
 $reviseTargetDayCompletion = ($projInfo['date_revise_completion'] && $projInfo['date_completion'] ) ? functions::date_diff($projInfo['date_completion'],$projInfo['date_revise_completion']) : 0;

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
              <script src="../js/datetimepicker_css.js"></script>
  </head>
  <body>
            <!-- body content: start here-->
            <div class="row-fluid sortable">
                <div class="box span12">
                    <div class="box-header" data-original-title>
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Update Form</h2>
                        <div class="box-icon">
                            <a href="#" class="btn-minimize"><i class="halflings-icon white chevron-up"></i></a>
                        </div>
                    </div>
                    <div class="box-content">
                  <form class="form-horizontal" method="post">           
                    <table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
                      <tr>
                      	<td width="17%" height="30">Project Name</td>
                        <td width="36%"><input type="text" name="txProj_name" id="txProj_name" class="span6" value="<?php echo $proj_name;?>"></td>
                        <td width="13%">Date Completed</td>
                        <td width="34%">
                              <input name="txDateCompleted" type="text" class="span6 mytextbox" id="txDateCompleted" value="<?php echo $dateCompleted;?>" readonly>
                              <a href="javascript:NewCssCal('txDateCompleted')">
                                <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                              </a>
                        </td>
                      </tr>
                      <tr>
                      	<td height="30">Project Description</td>
                        <td><input type="text" name="txProj_desc" id="txProj_desc" class="span6" value="<?php echo $proj_desc?>" /></td>
                        <td>Contract</td>
                        <td>
                              <input name="txDateContract" type="text" class="span6 mytextbox" id="txDateContract" value="<?php echo $dateContract;?>" readonly>
                              <a href="javascript:NewCssCal('txDateContract')">
                                <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                              </a>
                        </td>
                      </tr>
                      <tr>
                      	<td height="30">In Charge</td>
                        <td>
                            <select name="txIncharge" id="selectError" data-rel="chosen" style="width:300px;">
                              <option>--select--</option>
                                <?php
                                $qInCharge = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND ra.role_id="9" ORDER BY u.lname');
                                while($rInCharge = $db->fetch_array($qInCharge)):
                                ?>
                              <option value="<?php echo $rInCharge['user_id']?>" <?php if($incharge==$rInCharge['user_id'])echo 'selected="selected"';?> ><?php echo $rInCharge['lname'].', '.$rInCharge['fname']?></option>
                                <?php endwhile;?>
                            </select>
                        </td>
                        <td>NOA</td>
                        <td>
                              <input name="txDateNoa" type="text" class="span6 mytextbox" id="txDateNoa" value="<?php echo $dateNoa;?>" readonly>
                              <a href="javascript:NewCssCal('txDateNoa')">
                                <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                              </a>
                        </td>
                      </tr>
                      <tr>
                      	<td height="30">Cost</td>
                        <td><input type="text" class="span6" name="txProjCost" id="txProjCost" autocomplete='off' value="<?php echo $cost;?>" onkeyup="FormatCurrency(this);"></td>
                        <td>NTP</td>
                        <td>
                                <input name="txDateNtp" type="text" class="span6 mytextbox" id="txDateNtp" value="<?php echo $dateNtp;?>" readonly>
                                <a href="javascript:NewCssCal('txDateNtp')">
                                  <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                                </a>
                        </td>
                      </tr>

                      <tr>
                      	<td height="30">Date Started</td>
                        <td>
                              <input name="txDateStart" type="text" class="span6 mytextbox" id="txDateStart" value="<?php echo $datestart;?>" readonly>
                                <a href="javascript:NewCssCal('txDateStart')">
                                  <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                                </a>
                        </td>
                        <td>Status</td>
                        <td>
                          <select name="projStatus" id="projStatus" class="span6">
                            <option value="">--select--</option>
                            <option value="done" <?php if($status=='done')echo 'selected="selected"';?>>Done</option>
                            <option value="ongoing" <?php if($status=='ongoing')echo 'selected="selected"';?>>On Going</option>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td height="30">Target Completion(No of Days)</td>
                        <td><input type="input" name="targetDayCompletion" id="targetDayCompletion" value="<?php echo $targetDayCompletion;?>" style="width:15%;" />
                        </td>
                        <td>Remark</td>
                        <td><textarea name="txRemark" id="txRemark" cols="7"><?php echo $remarks;?></textarea></td>
                      </tr>
                      <tr>
                        <td>Revise Completion (No of Days)</td>
                        <td><input type="input" name="reviseTargetDayCompletion" id="reviseTargetDayCompletion" value="<?php echo $reviseTargetDayCompletion;?>" style="width:15%;" /></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
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
<script src="../js/formatCurrency.js"></script>

<script>
function delt(){
  if(confirm('Do you want to remove this?'))
    return true;
  else
    return false; 
}
</script>
<!-- end: JavaScript-->

</body>
</html>

