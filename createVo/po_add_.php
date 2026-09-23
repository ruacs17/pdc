<?php require_once('templ_up.php');?>
<?php

$insertID=0;
$arrInsert = array();

$txTerms = ( isset($_POST['txTerms']) && !empty($_POST['txTerms']) ) ? $_POST['txTerms'] : '';
$txPayee = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : '';
$txInvoice = ( isset($_POST['txInvoice']) && !empty($_POST['txInvoice']) ) ? $_POST['txInvoice'] : '';
$txProj = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
$txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
$txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
$txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
$txBdate = $txbYear.'-'.$txbMon.'-'.$txbDay;
$txPreparedBy = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? $_POST['txPreparedBy'] : '';
$txApprove = ( isset($_POST['txApprove']) && !empty($_POST['txApprove']) ) ? $_POST['txApprove'] : '';
$txProjDetail = ( isset($_POST['txProjDetail']) && !empty($_POST['txProjDetail']) ) ? $_POST['txProjDetail'] : '';
$txItemCat = ( isset($_POST['txItemCat']) && !empty($_POST['txItemCat']) ) ? $_POST['txItemCat'] : 0;
$txReceive = ( isset($_POST['txReceive']) && !empty($_POST['txReceive']) ) ? $_POST['txReceive'] : 0;
$txbMonDelvry = ( isset($_POST['bdMonDelvry']) && !empty($_POST['bdMonDelvry']) ) ? $_POST['bdMonDelvry'] : '';
$txbDayDelvry = ( isset($_POST['bdDayDelvry']) && !empty($_POST['bdDayDelvry']) ) ? $_POST['bdDayDelvry'] : '';
$txbYearDelvry = ( isset($_POST['bdYearDelvry']) && !empty($_POST['bdYearDelvry']) ) ? $_POST['bdYearDelvry'] : '';
$delivery_date = ($txbYearDelvry && $txbMonDelvry && $txbDayDelvry) ? $txbYearDelvry.'-'.$txbMonDelvry.'-'.$txbDayDelvry : date('Y-m-d');
if($txReceive==0)
    $delivery_date=NULL;

$remarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : '';
$txPaymentTerm = ( isset($_POST['txPaymentTerm']) && !empty($_POST['txPaymentTerm']) ) ? trim($_POST['txPaymentTerm']) : '';


if( isset($_POST['btnCreate']) ){
    if( $txProj && $txPayee && $txbMon && $txbDay && $txbYear && $txPreparedBy && $txApprove && $txItemCat){

        if( $txInvoice && $db->getValue('po','count(*)',array('invoice'=>$txInvoice)) ){
            functions::say("Invoice already used in voucher!");
        }
        $insertID = $db->insert('po',array('proj_id'=>$txProj,'supplierID'=>$txPayee,'category_id'=>$txItemCat,'po_date'=>$txBdate,'purchaser'=>$txPreparedBy,'invoice'=>$txInvoice,'approved_by'=>$txApprove,'terms'=>$txTerms,'proj_detail'=>$txProjDetail,'po_type'=>'material','remarks'=>$remarks,'received'=>$txReceive,'delivery_date'=>$delivery_date,'payment_term'=>$txPaymentTerm));
        $ref_id = $db->getValue('po','concat(substring(po_date,1,4),substring(po_date,6,2),po_id) as dte',array('po_id'=>$insertID));
        $db->update('po',array('ref_id'=>$ref_id,'po_no'=>$ref_id),array('po_id'=>$insertID));

        if($insertID)
            functions::sendTo('po_list.php?po_id='.functions::encode($insertID));
        else
            functions::say('Failed to create po!');
    }
}
unset($_SESSION['po_arr_proj'],$_SESSION['RefID']);

$qItem = $db->select('po','DISTINCT payment_term',array());
$namesPaymentTerm='';
while($rItem=$db->fetch_array($qItem)):
       $string = preg_replace("/'/",'"',$rItem['payment_term']);
       $namesPaymentTerm .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesPaymentTerm .= '"--"';
?>
<!-- body content: start here-->
<div align="right"><a id="sadc" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'po_service_add.php?','SERVICE P.O.')">Create SERVICE P.O.</a> &nbsp; <a id="adc" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'po_add_multiple.php?','Create Multiple P.O.')">Create P.O. With Multiple Projects</a> &nbsp; <a id="updateMultiplePO" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'po_edit_multiple.php?','Update Multiple P.O.')">Update P.O. With Multiple Projects</a></div><br>
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Purchase Order Form</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <table width="60%" align="center" border="0" style="background-color:#E4E1E1">
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">P.O Date</label>
                                <div class="controls">
                                    <select name="bdMon" id="bdMon" style="width:80px;" required>
                                        <option value="">Month</option>
                                        <option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
                                        <option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
                                        <option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
                                        <option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
                                        <option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
                                        <option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
                                        <option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
                                        <option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
                                        <option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
                                        <option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
                                        <option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
                                        <option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
                                    </select>
                                    <select name="bdDay" id="bdDay" style="width:60px;" required>
                                        <option value="">Day</option>
                                        <?php for($i=1;$i<=31;$i++):?>
                                        <option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDay)echo 'selected="selected"';?>><?php echo $i;?></option>
                                        <?php endfor;?>
                                    </select>
                                    <select name="bdYear" id="bdYear" style="width:70px;" required>
                                        <option value="">Year</option>
                                        <?php for($y=(date('Y') + 7);$y>=2012;$y--):?>
                                        <option value="<?php echo $y;?>" <?php if($y==$txbYear)echo 'selected="selected"';?>><?php echo $y;?></option>
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
                                <label class="control-label" for="inputSuccess">Payee / Supplier</label>
                                <div class="controls">
                                    <select name="txPayee" id="txPayee" data-rel="chosen" style="width:500px;">
                                        <option value="">--select--</option>
                                        <?php $qSup = $db->select('supplier','*',array(),'ORDER BY name');
                                              while($rSup = $db->fetch_array($qSup)):
                                        ?>
                                        <option value="<?php echo $rSup['supplierID']?>" <?php if($txPayee==$rSup['supplierID'])echo 'selected="selected"';?>><?php echo ucwords(strtolower($rSup['name']));?></option>
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
                                <label class="control-label" for="inputSuccess">Project</label>
                                <div class="controls">
                                    <select name="selProj" id="selProj" data-rel="chosen" style="width:700px;">
                                        <option value="">--select--</option>
                                        <?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
                                              while($rProj = $db->fetch_array($qProj)):
                                        ?>
                                        <option value="<?php echo $rProj['proj_id']?>" <?php if($txProj==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo ucwords(strtolower($rProj['proj_name']));?></option>
                                        <?php endwhile;?>
                                    </select>
                                    <span class="help-inline warning" id="msgProject" style="font-weight:bold;" name="msgProject"></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Category</label>
                                <div class="controls">
                                    <select name="txItemCat" id="txItemCat" data-rel="chosen" style="width:400px;">
                                      <option value="">--select--</option>
                                      <?php 
                                      $qCat = $db->select('item_deduction','*',array(),'ORDER BY name');
                                      while($rCat = $db->fetch_array($qCat)):
                                      ?>
                                    <option value="<?php echo $rCat['item_id']?>" <?php if('MATERIALS'==$rCat['name']){echo 'selected="selected"';}else if($txItemCat==$rCat['item_id'])echo 'selected="selected"';?>><?php echo $rCat['name']?></option>
                                    <?php endwhile;?>
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Additional description</label>
                                <div class="controls">
                                    <input type="text" name="txProjDetail" id="txProjDetail" style="width:300px;"  value="<?php echo $txProjDetail;?>" />
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Invoice #</label>
                                <div class="controls">
                                    <input type="text" name="txInvoice" id="txInvoice" style="width:300px;" value="<?php echo $txInvoice;?>" />
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Purchaser</label>
                                <div class="controls">
                                    <select name="txPreparedBy" id="txPreparedBy" data-rel="chosen" style="width:400px;">
                                        <option value="">--select--</option>
                                        <?php $qPrep = $db->select('users','*',array('status'=>'active'),'ORDER BY lname');
                                              while($rPrep = $db->fetch_array($qPrep)):
                                        ?>
                                        <option value="<?php echo $rPrep['user_id']?>" <?php if($txPreparedBy==$rPrep['user_id'])echo 'selected="selected"';?>><?php echo strtoupper($rPrep['lname'].', '.$rPrep['fname']);?></option>
                                        <?php endwhile;?>
                                    </select> 
                                    <span class="help-inline warning" id="msgPrepare" style="font-weight:bold;" name="msgPrepare"></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Approval</label>
                                <div class="controls">
                                    <select name="txApprove" id="txApprove" data-rel="chosen" style="width:400px;">
                                        <option value="">--select--</option>
                                        <?php $qApprove = $db->select('users','*',array('status'=>'active'),'ORDER BY lname');
                                              while($rApprove = $db->fetch_array($qApprove)):
                                        ?>
                                        <option value="<?php echo $rApprove['user_id']?>" <?php if($txApprove==$rApprove['user_id'])echo 'selected="selected"';?>><?php echo strtoupper($rApprove['lname'].', '.$rApprove['fname']);?></option>
                                        <?php endwhile;?>
                                    </select>
                                    <span class="help-inline warning" id="msgApprove" style="font-weight:bold;" name="msgApprove"></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Terms of Payment</label>
                                <div class="controls">
                                    <input type="text" name="txPaymentTerm" id="txPaymentTerm" style="width:300px;" value="<?php echo $txPaymentTerm?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPaymentTerm;?>]' />
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group hidden-phone">
                                <label class="control-label" for="textarea2">Terms and Conditions</label>
                                <div class="controls">
                                    <textarea class="cleditor" id="txTerms" name="txTerms" rows="2">
                                    <?php if($txTerms){
                                        echo $txTerms;
                                    }else{
                                    ?>
                                        <div><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;"><b>TERMS AND CONDITIONS:</b></span></font></div><div><ol><li><b style="font-size: 13.3333px; color: rgb(51, 51, 51); font-family: Tahoma;">Pick-up</b></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">All materials enumerated in this order be ready One (1) hour before it will be picked-up by our representative.</span></font></li></ol><div><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;"><br></span></font></div><div><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;"><b>TERMS AND CONDITIONS:</b></span></font></div></div><div><ol><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;"><b>The Above materials shall be shipped to Cebu City Port...URGENT...</b></span></font></li><li><font color="#333333" face="Tahoma"><span style="font-size: 13.3333px;">All materials enumerated in this order shall be delivered Seven (7) Calendar day(s) reckoned from the date of receipt and signing of this purchase order.</span></font></li></ol></div>
                                    <?php }?>
                                    </textarea>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr style="display:none;">
                        <td><br><br>
                            <div class="control-group hidden-phone">
                                <label class="control-label" for="textarea2">Receive Status</label>
                                <div class="controls">
                                    <select name="txReceive" id="txReceive" style="width:150px;">
                                        <option value="0" <?php if($txReceive==0)echo 'selected="selected"';?>>Not Received</option>
                                        <option value="1" <?php if($txReceive==1)echo 'selected="selected"';?>>Received</option>
                                    </select>
                                    <select name="bdMonDelvry" id="bdMonDelvry" style="width:80px;">
                                        <option value="">Month</option>
                                        <option value="01" <?php if($txbMonDelvry=='01')echo 'selected="selected"';?>>Jan</option>
                                        <option value="02" <?php if($txbMonDelvry=='02')echo 'selected="selected"';?>>Feb</option>
                                        <option value="03" <?php if($txbMonDelvry=='03')echo 'selected="selected"';?>>Mar</option>
                                        <option value="04" <?php if($txbMonDelvry=='04')echo 'selected="selected"';?>>Apr</option>
                                        <option value="05" <?php if($txbMonDelvry=='05')echo 'selected="selected"';?>>May</option>
                                        <option value="06" <?php if($txbMonDelvry=='06')echo 'selected="selected"';?>>Jun</option>
                                        <option value="07" <?php if($txbMonDelvry=='07')echo 'selected="selected"';?>>Jul</option>
                                        <option value="08" <?php if($txbMonDelvry=='08')echo 'selected="selected"';?>>Aug</option>
                                        <option value="09" <?php if($txbMonDelvry=='09')echo 'selected="selected"';?>>Sep</option>
                                        <option value="10" <?php if($txbMonDelvry=='10')echo 'selected="selected"';?>>Oct</option>
                                        <option value="11" <?php if($txbMonDelvry=='11')echo 'selected="selected"';?>>Nov</option>
                                        <option value="12" <?php if($txbMonDelvry=='12')echo 'selected="selected"';?>>Dec</option>
                                    </select>
                                    <select name="bdDayDelvry" id="bdDayDelvry" style="width:60px;">
                                        <option value="">Day</option>
                                        <?php for($i=1;$i<=31;$i++):?>
                                        <option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDayDelvry)echo 'selected="selected"';?>><?php echo $i;?></option>
                                        <?php endfor;?>
                                    </select>
                                    <select name="bdYearDelvry" id="bdYearDelvry" style="width:70px;">
                                        <option value="">Year</option>
                                        <?php for($y=(date('Y') + 7);$y>=2012;$y--):?>
                                        <option value="<?php echo $y;?>" <?php if($y==$txbYearDelvry)echo 'selected="selected"';?>><?php echo $y;?></option>
                                        <?php endfor;?>
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group hidden-phone">
                                <label class="control-label" for="textarea2">Remarks</label>
                                <div class="controls">
                                    <textarea id="txRemarks" name="txRemarks" rows="2"><?php echo $remarks?></textarea>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr><td>&nbsp;</td></tr> 
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

    <?php if($txReceive==1){?>
    $('#bdMonDelvry').show();
    $('#bdDayDelvry').show();
    $('#bdYearDelvry').show();
    <?php }else{?>
    $('#bdMonDelvry').hide();
    $('#bdDayDelvry').hide();
    $('#bdYearDelvry').hide();
    <?php }?>
    $('#txReceive').change(function(){
        if( $('#txReceive').val()=="1" ){
            $('#bdMonDelvry').show();
            $('#bdDayDelvry').show();
            $('#bdYearDelvry').show();
        }
        else{
            $('#bdMonDelvry').hide();
            $('#bdDayDelvry').hide();
            $('#bdYearDelvry').hide();
        }
    });

  	$('#btnCreate').click(function(){
    $('#msgBdate').html("");
    $('#msgPayee').html("");
    $('#msgProject').html("");
    $('#msgPrepare').html("");
    $('#msgApprove').html("");
  		if( $('#bdYear').val()=="" || $('#bdMon').val()=="" || $('#bdDay').val()=="" ){
    		$('#msgBdate').html("Date Required!");
    		res=false;
  		}
  		else if( $('#txPayee').val()=="" ){
            $('#txPayee').focus();
    		$('#msgPayee').html("Supplier Required!");
    		res=false;
  		}
        else if( $('#selProj').val()=="" ){
            $('#msgProject').html("Project Required!");
            $('#selProj').focus();
            res=false;
        }
        else if( $('#txPreparedBy').val()=="" ){
            $('#msgPrepare').html("Purchase Required!");
            $('#txPreparedBy').focus();
            res=false;
        }
        else if( $('#txApprove').val()=="" ){
            $('#msgApprove').html("Approval Required!");
            $('#txApprove').focus();
            res=false;
        }
          else
            res=true;
    return res;
  	});
});
</script>