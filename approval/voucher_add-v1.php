<?php require_once('templ_up.php');?>
<?php
if( isset($_POST['btnCreate']) ){
    $insertID=0;
    $arrInsert = array();
    
    $txPayee = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : '';
    $txCheque = ( isset($_POST['txCheque']) && !empty($_POST['txCheque']) ) ? $_POST['txCheque'] : '';
    $txVoNo = ( isset($_POST['txVoNo']) && !empty($_POST['txVoNo']) ) ? $_POST['txVoNo'] : '';
    $txPrepare = ( isset($_POST['txPrepare']) && !empty($_POST['txPrepare']) ) ? $_POST['txPrepare'] : '';
    $txChecker = ( isset($_POST['txChecker']) && !empty($_POST['txChecker']) ) ? $_POST['txChecker'] : '';
    $txApprover = ( isset($_POST['txApprover']) && !empty($_POST['txApprover']) ) ? $_POST['txApprover'] : '';
	  $txVoType = ( isset($_POST['txVoType']) && !empty($_POST['txVoType']) ) ? $_POST['txVoType'] : '';
	  $txRemark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? $_POST['txRemark'] : '';

  	if($txVoNo){
  		$arrInsert = array('voucher_no'=>$txVoNo);
  	}
  	
  	if($txVoType){
  		$arrInsert = array_merge($arrInsert,array('vt_id'=>$txVoType));
  	}
  	
  	if($txRemark){
  		$arrInsert = array_merge($arrInsert,array('remarks'=>$txRemark));
  	}	
		
    if($txCheque){
		$arrInsert = array_merge($arrInsert,array('cheque_id'=>$txCheque));
    }

    if($txPrepare){
      if( $db->getValue('users','count(user_id)',array('user_id'=>$txPrepare)) ){
        $arrInsert = array_merge($arrInsert,array('preparedBy'=>$txPrepare));
      }
    }
    if($txChecker){
      if( $db->getValue('users','count(user_id)',array('user_id'=>$txChecker)) ){
        $arrInsert = array_merge($arrInsert,array('checkedBy'=>$txChecker));
      }
    }
    if($txApprover){
      if( $db->getValue('users','count(user_id)',array('user_id'=>$txApprover)) ){
        $arrInsert = array_merge($arrInsert,array('approvedBy'=>$txApprover));
      }
    }

    $txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
    $txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
    $txBdate = $txbYear.'-'.$txbMon.'-'.$txbDay;


    $txcMon = ( isset($_POST['bcMon']) && !empty($_POST['bcMon']) ) ? $_POST['bcMon'] : '';
    $txcDay = ( isset($_POST['bcDay']) && !empty($_POST['bcDay']) ) ? $_POST['bcDay'] : '';
    $txcYear = ( isset($_POST['bcYear']) && !empty($_POST['bcYear']) ) ? $_POST['bcYear'] : '';
    $txCdate = $txcYear.'-'.$txcMon.'-'.$txcDay;
    if($txcMon && $txcDay && $txcYear){
        $arrInsert = array_merge($arrInsert,array('cheque_date'=>$txCdate));
    }

    if( isset($_POST['txReceiver']) && !empty($_POST['txReceiver']) ){
        $receiver = (isset($_POST['txReceiver'])) ? $_POST['txReceiver'] : '';
        $arrInsert = array_merge($arrInsert,array('receivedBy'=>$receiver));
    }

    
    if($txPayee && $txbMon && $txbDay && $txbYear && $txPrepare){
      $allowed=1;
      if($txVoNo){
        if( $db->getValue('voucher','count(*)',array('voucher_no'=>$txVoNo)) )
            $allowed=0;
      }
        if($allowed){
          $ins = $db->insertPrint('voucher',array_merge(array('supplierID'=>$txPayee,'vdate'=>$txBdate),$arrInsert));
          $db->query($ins);
          $insertID = $db->insert_id();
          if($insertID)
              functions::sendTo('voucher_view.php?vid='.functions::encode($insertID));
        }
        else
          functions::say("Voucher Number already exist!");
    }
}
    $qList = $db->select('voucher','DISTINCT receivedBy',array());
    $namesReceivedBy='';
    while($rList=$db->fetch_array($qList)):
    $namesReceivedBy .= '"'.$rList['receivedBy'].'",';
    endwhile;
    $namesReceivedBy .= '"--"';
?>
            <!-- body content: start here-->
            <div class="row-fluid sortable">
                <div class="box span12">
                    <div class="box-header" data-original-title>
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Voucher Create Form</h2>
                    </div>
                    <div class="box-content">
                  <form class="form-horizontal" method="post">           
                    <table width="60%" align="center" border="0" style="background-color:#E4E1E1">
                      <tr><td>&nbsp;</td></tr>
                      <tr>
                        <td>
                          <div class="control-group">
                            <label class="control-label" for="inputSuccess">Voucher Type</label>
                            <div class="controls">
                              <select name="txVoType" id="txVoType" data-rel="chosen" style="width:300px;">
                              <option value="">--select--</option>
                              <?php 
                                $qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
                                while($rVtype = $db->fetch_array($qVtype)):
                              ?>
                              <option value="<?php echo $rVtype['vt_id']?>" ><?php echo $rVtype['vt_name']?></option>
                              <?php endwhile;?>
                              </select>
                              <span class="help-inline warning" id="msgVoType" style="font-weight:bold;" name="msgVoType"></span>
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <div class="control-group">
                            <label class="control-label" for="inputSuccess">Voucher No:</label>
                            <div class="controls">
                              <input type="text" name="txVoNo" id="txVoNo" style="width:300px;" value="" />
                              <span class="help-inline warning" id="msgVono" style="font-weight:bold;" name="msgVono"></span>
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <div class="control-group">
                            <label class="control-label" for="inputSuccess">Voucher Date</label>
                            <div class="controls">
                              <select name="bdMon" id="bdMon" style="width:80px;">
                                <option value="">Month</option>
                                <option value="01">Jan</option>
                                <option value="02">Feb</option>
                                <option value="03">Mar</option>
                                <option value="04">Apr</option>
                                <option value="05">May</option>
                                <option value="06">Jun</option>
                                <option value="07">Jul</option>
                                <option value="08">Aug</option>
                                <option value="09">Sep</option>
                                <option value="10">Oct</option>
                                <option value="11">Nov</option>
                                <option value="12">Dec</option>
                              </select>
                              <select name="bdDay" id="bdDay" style="width:60px;">
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo $i;?>"><?php echo $i;?></option>
                                <?php endfor;?>
                              </select>
                              <select name="bdYear" id="bdYear" style="width:70px;">
                                <option value="">Year</option>
                                <?php for($y=(date('Y')+1);$y>=2015;$y--):?>
                                <option value="<?php echo $y;?>"><?php echo $y;?></option>
                                <?php endfor;?>
                              </select>
                              <span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <div class="control-group">
                            <label class="control-label" for="inputSuccess">Payee</label>
                            <div class="controls">
                                <select name="txPayee" id="txPayee" data-rel="chosen" style="width:400px;">
                                    <option value="">--select--</option>
                                    <?php $qSup = $db->select('supplier','*',array(),'ORDER BY name');
                                          while($rSup = $db->fetch_array($qSup)):
                                    ?>
                                    <option value="<?php echo $rSup['supplierID']?>"><?php echo ($rSup['name']);?></option>
                                    <?php endwhile;?>
                                </select>
                                <span class="help-inline warning" id="msgPayee" style="font-weight:bold;" name="msgPayee"></span>
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <div class="control-group">
                            <label class="control-label" for="inputSuccess">Cheque #</label>
                            <div class="controls">
                              <input type="text" name="txCheque" id="txCheque" style="width:300px;" value="" />
                              <span class="help-inline warning" id="msgCheque" style="font-weight:bold;" name="msgCheque"></span>
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <div class="control-group">
                            <label class="control-label" for="inputSuccess">Cheque Date</label>
                            <div class="controls">
                              <select name="bcMon" id="bcMon" style="width:80px;">
                                <option value="">Month</option>
                                <option value="01">Jan</option>
                                <option value="02">Feb</option>
                                <option value="03">Mar</option>
                                <option value="04">Apr</option>
                                <option value="05">May</option>
                                <option value="06">Jun</option>
                                <option value="07">Jul</option>
                                <option value="08">Aug</option>
                                <option value="09">Sep</option>
                                <option value="10">Oct</option>
                                <option value="11">Nov</option>
                                <option value="12">Dec</option>
                              </select>
                              <select name="bcDay" id="bcDay" style="width:60px;">
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo $i;?>"><?php echo $i;?></option>
                                <?php endfor;?>
                              </select>
                              <select name="bcYear" id="bcYear" style="width:70px;">
                                <option value="">Year</option>
                                <?php for($y=(date('Y')+1);$y>=2015;$y--):?>
                                <option value="<?php echo $y;?>"><?php echo $y;?></option>
                                <?php endfor;?>
                              </select>
                              <span class="help-inline warning" id="msgChequeDate" style="font-weight:bold;" name="msgChequeDate"></span>
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <div class="control-group">
                            <label class="control-label" for="inputSuccess">Prepared By</label>
                            <div class="controls">
                              <select name="txPrepare" id="txPrepare" data-rel="chosen" style="width:300px;">
                              <option value="">--select--</option>
                                <?php
                                $qPrep = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND ra.role_id="7" AND u.status="active" ORDER BY u.lname');
                                while($rPrep = $db->fetch_array($qPrep)):
                                ?>
                              <option value="<?php echo $rPrep['user_id']?>" ><?php echo $rPrep['lname'].', '.$rPrep['fname']?></option>
                                <?php endwhile;?>
                              </select>
                              <span class="help-inline warning" id="msgPrepared" style="font-weight:bold;" name="msgPrepared"></span>
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <div class="control-group">
                            <label class="control-label" for="inputSuccess">Checker</label>
                            <div class="controls">
                              <select name="txChecker" id="txChecker" data-rel="chosen" style="width:300px;">
                              <option value="">--select--</option>
                                <?php
                                $qCheck = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND ra.role_id="8" AND u.status="active" ORDER BY u.lname');
                                while($rCheck = $db->fetch_array($qCheck)):
                                ?>
                              <option value="<?php echo $rCheck['user_id']?>" ><?php echo $rCheck['lname'].', '.$rCheck['fname']?></option>
                                <?php endwhile;?>
                              </select>
                              <span class="help-inline warning" id="msgChecker" style="font-weight:bold;" name="msgChecker"></span>
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <div class="control-group">
                            <label class="control-label" for="inputSuccess">Approval</label>
                            <div class="controls">
                              <select name="txApprover" id="txApprover" data-rel="chosen" style="width:300px;">
                              <option value="">--select--</option>
                                <?php
                                $qApprove = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND ra.role_id="8" AND u.status="active" ORDER BY u.lname');
                                while($rApprove = $db->fetch_array($qApprove)):
                                ?>
                              <option value="<?php echo $rApprove['user_id']?>" ><?php echo $rApprove['lname'].', '.$rApprove['fname']?></option>
                                <?php endwhile;?>
                              </select>
                              <span class="help-inline warning" id="msgApprove" style="font-weight:bold;" name="msgAprove"></span>
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <div class="control-group">
                            <label class="control-label" for="inputSuccess">Receiver</label>
                            <div class="controls">
                              <input type="text" class="span6 typeahead" name="txReceiver" id="txReceiver" style="width:300px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesReceivedBy;?>]' value="">
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <div class="control-group">
                            <label class="control-label" for="inputSuccess">Remarks</label>
                            <div class="controls">
                              <input type="text" class="span6" name="txRemark" id="txRemark" value="">
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <div class="controls">
                            <input type="submit" name="btnCreate" id="btnCreate" value="Create" class="btn btn-primary">
                            <button class="btn">Cancel</button>
                          </div>
                        </td>
                      </tr>
                      <tr><td>&nbsp;</td></tr>        
                    </table>
                  </form>
                    
                    </div>
                </div><!--/span-->
            
            </div><!--/row-->
            <!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script>
$(document).ready(function(){
  var res = false;
  $('#btnCreate').click(function(){
	  
	$('#msgVoType').html("");
    $('#msgBdate').html("");
    $('#msgPayee').html("");
    $('#msgProject').html("");
    $('#msgPrepared').html("");
    $('#msgChecker').html("");
    $('#msgApprove').html("");
    $('#msgVono').html("");


    if( $('#txVoType').val()=="" ){
      $('#txVoType').focus();
      $('#msgVoType').html("Type Required!");
      res=false;
    }
    else if( $('#txVoNo').val()=="" ){
      $('#txVoNo').focus();
      $('#msgVono').html("Voucher Number Required!");
      res=false;
    }
    else if( $('#bdYear').val()=="" || $('#bdMon').val()=="" || $('#bdDay').val()=="" ){
      $('#msgBdate').html("Date Required!");
      res=false;
    }
    else if( $('#txPayee').val()=="" ){
      $('#txPayee').focus();
      $('#msgPayee').html("Supplier Required!");
      res=false;
    }
    else if( $('#txPrepare').val()=="" ){
      $('#msgPrepared').html("Required!");
      $('#txPrepare').focus();
      res=false;
    }
    else if( $('#txChecker').val()=="" ){
      $('#msgChecker').html("Required!");
      $('#txChecker').focus();
      res=false;
    }
    else if( $('#txApprover').val()=="" ){
      $('#msgApprove').html("Approval Required!");
      $('#txApprover').focus();
      res=false;
    }
    else
      res=true;

    return res;
  });
});
</script>