<?php require_once('templ_up.php');?>
<?php
if( isset($_POST['btnCreate']) ){
    $insertID=0;
    $arrInsert = array();
    $inchargeID=0;
    $txProj_name = ( isset($_POST['txProj_name']) && !empty($_POST['txProj_name']) ) ? $_POST['txProj_name'] : '';
    $txProj_desc = ( isset($_POST['txProj_desc']) && !empty($_POST['txProj_desc']) ) ? $_POST['txProj_desc'] : '';
	  $txProj_remark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? $_POST['txRemark'] : '';
	  $txProj_status = ( isset($_POST['projStatus']) && !empty($_POST['projStatus']) ) ? $_POST['projStatus'] : '';
	  $txProj_cost = ( isset($_POST['txProjCost']) && !empty($_POST['txProjCost']) ) ? functions::moneyToDouble($_POST['txProjCost']) : '';
    $targetDayCompletion = ( isset($_POST['targetDayCompletion']) && !empty($_POST['targetDayCompletion']) ) ? $_POST['targetDayCompletion'] : 0; 
    $reviseTargetDayCompletion = ( isset($_POST['reviseTargetDayCompletion']) && !empty($_POST['reviseTargetDayCompletion']) ) ? $_POST['reviseTargetDayCompletion'] : 0;
    		
    $arrInsert = array('proj_name'=>$txProj_name,'status'=>$txProj_status);

    if($txProj_desc)
      $arrInsert = array_merge($arrInsert,array('proj_desc'=>($txProj_desc)));
      
    if($txProj_remark)
      $arrInsert = array_merge($arrInsert,array('remarks'=>($txProj_remark)));
        
    if($txProj_cost)
      $arrInsert = array_merge($arrInsert,array('proj_cost'=>($txProj_cost * 1)));
      
    if( isset($_POST['txIncharge']) ){
        $inchargeID = (isset($_POST['txIncharge'])) ? $_POST['txIncharge'] : array();
      if( $db->getValue('users','count(user_id)',array('user_id'=>$inchargeID)) )
          $arrInsert = array_merge($arrInsert,array('incharge'=>$inchargeID));
    }


	
    $txDateStart = ( isset($_POST['txDateStart']) && !empty($_POST['txDateStart']) ) ? $_POST['txDateStart'] : '';
    if($txDateStart){
      $arrInsert = array_merge($arrInsert,array('date_start'=>$txDateStart));

      $txDateCompletion = functions::AddDay($txDateStart,$targetDayCompletion);
      $arrInsert = array_merge($arrInsert,array('date_completion'=>$txDateCompletion));
      $txDateReviseCompletion = functions::AddDay($txDateCompletion,$reviseTargetDayCompletion);
      $arrInsert = array_merge($arrInsert,array('date_revise_completion'=>$txDateReviseCompletion));
    }

  
    $txDateCompleted = ( isset($_POST['txDateCompleted']) && !empty($_POST['txDateCompleted']) ) ? $_POST['txDateCompleted'] : '';
    if($txDateCompleted)
      $arrInsert = array_merge($arrInsert,array('date_completed'=>$txDateCompleted));
  
    $txDateContract = ( isset($_POST['txDateContract']) && !empty($_POST['txDateContract']) ) ? $_POST['txDateContract'] : '';
    if($txDateContract)
      $arrInsert = array_merge($arrInsert,array('date_contract'=>$txDateContract));
      
    $txDateNoa = ( isset($_POST['txDateNoa']) && !empty($_POST['txDateNoa']) ) ? $_POST['txDateNoa'] : '';
    if($txDateNoa)
      $arrInsert = array_merge($arrInsert,array('date_noa'=>$txDateNoa));
      
    $txDateNtp = ( isset($_POST['txDateNtp']) && !empty($_POST['txDateNtp']) ) ? $_POST['txDateNtp'] : '';
    if($txDateNtp)
      $arrInsert = array_merge($arrInsert,array('date_ntp'=>$txDateNtp));
	
    if( $txProj_name && $txDateStart ){

  		if( $db->getValue('project','count(proj_id)',array('proj_name'=>$txProj_name))==0 ){
            $ins =  $db->insertPrint('project',$arrInsert);
            $db->query($ins);
            $insertID = $db->insert_id();
            if($insertID){
                functions::sendTo('project_list.php?pid='.functions::encode($insertID));
            }
  		}
    }
}
?>
<script src="../js/formatCurrency.js"></script>
<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
            <!-- body content: start here-->
            <div class="row-fluid sortable">
                <div class="box span12">
                    <div class="box-header" data-original-title>
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Create Form</h2>
                        <div class="box-icon">
                            <a href="#" class="btn-minimize"><i class="halflings-icon white chevron-up"></i></a>
                        </div>
                    </div>
                    <div class="box-content">
                  <form class="form-horizontal" method="post">           
                    <table width="80%" align="center" border="0" style="background-color:#E4E1E1">
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td height="47" align="right">Project Name</td>
                        <td>&nbsp;</td>
                        <td><input type="text" class="span6" name="txProj_name" id="txProj_name" value=""></td>
                      </tr>
                      <tr>
                        <td height="47" align="right">Project Description</td>
                        <td>&nbsp;</td>
                        <td><input type="text" class="span6" name="txProj_desc" id="txProj_desc" value="" /></td>
                      </tr>
                      <tr>
                        <td height="47" align="right">In Charge</td>
                        <td>&nbsp;</td>
                        <td>
                              <select name="txIncharge" id="selectError" data-rel="chosen" style="width:300px;">
                              <option>--select--</option>
                                <?php
                                $qInCharge = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND ra.role_id="9" ORDER BY u.lname');
                                while($rInCharge = $db->fetch_array($qInCharge)):
                                ?>
                              <option value="<?php echo $rInCharge['user_id']?>" ><?php echo $rInCharge['lname'].', '.$rInCharge['fname']?></option>
                                <?php endwhile;?>
                              </select>
                        </td>
                      </tr>
                      <tr>
                        <td height="47" align="right">Cost</td>
                        <td>&nbsp;</td>
                        <td><input type="text" class="span6"  name="txProjCost" id="txProjCost" autocomplete='off' value="" onkeyup="FormatCurrency(this);"></td>
                      </tr>

                      <tr>
                        <td height="47" align="right">Date Start</td>
                        <td>&nbsp;</td>
                        <td>
                           <input name="txDateStart" type="text" class="span6 mytextbox" id="txDateStart" value="" readonly>
                           <a href="javascript:NewCssCal('txDateStart')">
                           <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                           </a>
                        </td>
                      </tr>
                      <tr>
                      	<td height="47" align="right">Target Completion (No of Days)</td>
                      	<td>&nbsp;</td>
                        <td><input type="input" name="targetDayCompletion" id="targetDayCompletion" value="" style="width:15%;" /></td>
                      </tr>
                      <tr>
                      	<td height="47" align="right">Revise Completion (No of Days)</td>
                      	<td>&nbsp;</td>
                        <td><input type="input" name="reviseTargetDayCompletion" id="reviseTargetDayCompletion" value="" style="width:15%;" /></td>
                      </tr>
                      <tr>
                      	<td height="47" align="right">Date Completed</td>
                      	<td>&nbsp;</td>
                        <td>
                        	<input name="txDateCompleted" type="text" class="span6 mytextbox" id="txDateCompleted" value="" readonly>
                            <a href="javascript:NewCssCal('txDateCompleted')">
                            <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                            </a>
                        </td>
                      </tr>
                      <tr>
                      	<td height="47" align="right">Date Contract</td>
                      	<td>&nbsp;</td>
                        <td>
                            <input name="txDateContract" type="text" class="span6 mytextbox" id="txDateContract" value="" readonly>
                            <a href="javascript:NewCssCal('txDateContract')">
                            <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                            </a>
                        </td>
                      </tr>
                      <tr>
                      	<td height="47" align="right">Date NOA</td>
                      	<td>&nbsp;</td>
                        <td>
                            <input name="txDateNoa" type="text" class="span6 mytextbox" id="txDateNoa" value="" readonly>
                            <a href="javascript:NewCssCal('txDateNoa')">
                            <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                            </a>
                        </td>
                      </tr>
                      <tr>
                      	<td height="47" align="right">Date NTP</td>
                      	<td>&nbsp;</td>
                        <td>
                            <input name="txDateNtp" type="text" class="span6 mytextbox" id="txDateNtp" value="" readonly>
                            <a href="javascript:NewCssCal('txDateNtp')">
                            <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                            </a>
                        </td>
                      </tr>
                      <tr>
                        <td height="68" align="right">Remark</td>
                        <td>&nbsp;</td>
                        <td><textarea name="txRemark" id="txRemark"></textarea></td>
                      </tr>
                      <tr>
                      	<td height="47" align="right">Status</td>
                      	<td>&nbsp;</td>
                        <td>
                              <select class="span6" name="projStatus" id="projStatus">
                              	<option value="ongoing">On Going</option>
                                <option value="done">Done</option>
                              </select>
                        </td>
                      </tr>
                      <tr>
                        <td height="47" colspan="3" align="center">
                            <input type="submit" name="btnCreate" id="btnCreate" value="Create" class="btn btn-primary">
                            <button class="btn">Cancel</button>
                        </td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>          
                    </table>
                 </form>
                    
                    </div>
                </div><!--/span-->
            
            </div><!--/row-->
            <!-- body content: end here-->
<?php require_once('templ_down.php');?>