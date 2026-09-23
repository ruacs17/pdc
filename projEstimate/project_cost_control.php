<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$arrVal=array('incharge'=>$user_id);
if($p_id){
    $arrVal = array('proj_id'=>$p_id,'incharge'=>$user_id);
}

if( isset($_REQUEST['pidDel']) && !empty($_REQUEST['pidDel']) ){
    $projDelID = functions::decode($_REQUEST['pidDel']);
    $db->delete('project',array('proj_id'=>$projDelID));
    functions::sendTo('project_list.php');
}
?>
            <!-- body content: start here-->
<table width="315" cellspacing="4" cellpadding="6" border='0' align="right">
    <tr>
        <td width="25" height='30'><div style="background-color:#ff0000; width:20px;">&nbsp;</div></td>
        <td width="82">>= 41%</td>
        <td width="25"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
        <td width="69">>= 31%</td>
        <td width="25"><div style="background-color:#eaff00; width:20px;">&nbsp;</div></td>
        <td width="59">>= 20%</td>
    </tr>
</table>
                    <div class="row-fluid">
                        <div class="box span12">
                            <div class="box-header" data-original-title>
                                <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Project List</h2>
                            </div>
                            <div class="box-content">
                                <table class="table table-striped" border="0" style="font-size:13px; font-family:Tahoma;">
                                    <thead>
                                        <tr>
                                            <th width="5%">Project Cost</th>
                                            <th width="5%">VAT<br>(12%)</th>
                                            <th width="5%">EWT<br>(2%)</th>
                                            <th width="5%">MPFee<br>(0.80%)</th>
                                            <th width="5%">GenOver<br>(12%)</th>
                                            <th width="5%">Profit<br>(10%)</th>
                                            <th width="5%">Commit<br>(0%)</th>
                                            <th width="5%">Consultancy</th>
                                            <th width="5%">Finders<br>Fee</th>
                                            <th width="5%">Technical<br>Fee</th>
                                            <th width="5%">Total Budget<br>For Operation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        if(!is_numeric($startrow))
                                            $startrow=0;
                                        $qProj = $db->select('project','*',$arrVal,'ORDER BY date_start DESC LIMIT '.$startrow.', '.$rowdisplay);
                                        $num_record = $db->getValue('project','count(*)',$arrVal);
                                        while($rProj = $db->fetch_array($qProj)):
                                        $amount=0;
                                        $po_amount=0;
										$dateStart = $rProj['date_start'];
										$dateCompletion = $rProj['date_completion'];	
										$dateReviseCompletion = $rProj['date_revise_completion'];
										$dateCompleted = $rProj['date_completed'];
										$proj_cost = $rProj['proj_cost'];
										$vat12 = $proj_cost * .12;
										$ewt = $proj_cost * .2;
										$mpFee = $proj_cost * .08;
										$genover = $proj_cost * .12;
										$profit = $proj_cost * .10;
										$technical = $proj_cost * .04;
										$consultancy = $proj_cost * .06;
										$totalBudget = $proj_cost - ($vat12 + $ewt + $mpFee + $genover + $profit + $technical + $consultancy);
                                    ?>
                                    	<tr>
                                        	<td colspan="11" height="50" valign="center">&nbsp;</td>
                                        </tr>
                                    	<tr>
                                        	<td colspan="11"><strong><?php echo $rProj['proj_name'];?></strong></td>
                                        </tr>
                                        <tr>
                                            <td width="5%"><strong>Project Cost</strong></td>
                                            <td width="5%"><strong>VAT<br>(12%)</strong></td>
                                            <td width="5%"><strong>EWT<br>(2%)</strong></td>
                                            <td width="5%"><strong>MPFee<br>(0.80%)</strong></td>
                                            <td width="5%"><strong>GenOver<br>(12%)</strong></td>
                                            <td width="5%"><strong>Profit<br>(10%)</strong></td>
                                            <td width="5%"><strong>Commit<br>(0%)</strong></td>
                                            <td width="5%"><strong>Consultancy</strong></td>
                                            <td width="5%"><strong>Finders<br>Fee</strong></td>
                                            <td width="5%"><strong>Technical<br>Fee</strong></td>
                                            <td width="5%"><strong>Total Budget<br>For Operation</strong></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo functions::formatMoney($rProj['proj_cost']);?> </i></td>
                                            <td><?php echo functions::formatMoney($vat12);?></td>
                                            <td><?php echo functions::formatMoney($ewt);?></td>
                                            <td><?php echo functions::formatMoney($mpFee);?></td>
                                            <td><?php echo functions::formatMoney($genover);?></td>
                                            <td><?php echo functions::formatMoney($profit);?></td>
                                            <td></td>
                                            <td><?php echo functions::formatMoney($consultancy);?></td>
                                            <td></td>
                                            <td><?php echo functions::formatMoney($technical);?></td>
                                            <td><?php echo functions::formatMoney($totalBudget);?></td>
                                        </tr>
                                    <?php endwhile;?>
                                    </tbody>
                                </table>
                                <div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=$_SERVER['PHP_SELF'].'?',$search="");?></div>
                          </div>
                        </div><!--/span-->

                    </div><!--/row-->
            <!-- body content: end here-->
<script>
function projSel(PiEwgD){window.location="project_list.php?pid="+PiEwgD}
function delt(){
  if(confirm('Do you want to remove this Project?'))
    return true;
  else
    return false; 
}
</script>
<?php require_once('templ_down.php');?>
