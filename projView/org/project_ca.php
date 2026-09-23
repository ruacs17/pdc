<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$arrVal=array('project'=>'1','incharge'=>$user_id);
if($p_id){
    $arrVal = array_merge($arrVal,array('proj_id'=>$p_id));
}
?>
            <!-- body content: start here-->
                    <div class="row-fluid">
                        <div class="box span12">
                            <div class="box-header" data-original-title>
                                <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Project List</h2>
                            </div>
                            <br>
                            <div align="right">
                                <a id="elm" title="Equipment Labor Material Unit Cost" data-rel="tooltip" class="btn btn-mini thickbox" onclick="showThis(this.id,'elm.php?','Equipment Manpower Material Unit Cost','1')"><i class="halflings-icon white zoom-in"></i>ELM Unit Cost</a>
                                <a id="elc" title="Equipment Labor Material Capabilities" data-rel="tooltip" class="btn btn-mini thickbox" onclick="showThis(this.id,'elc.php?','Equipment Manpower Material Capabilities','1')"><i class="halflings-icon white zoom-in"></i>ELM Capabilites</a>
                            &nbsp;&nbsp;&nbsp;
                            </div>
                    <div align="left"><br>&nbsp;&nbsp;
                                <select name="selProj" id="selProj" data-rel="chosen" style="width:650px;font-size:12px;" onChange="projSel(this.value)">
                                    <option value="">--All Projects--</option>
                                    <?php $qProj = $db->select('project','*',array('incharge'=>$user_id),'ORDER BY proj_name');
                                          while($rProj = $db->fetch_array($qProj)):
                                    ?>
                                    <option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($p_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
                                    <?php endwhile;?>
                                </select>
                    </div>
            </form>
                            <div class="box-content">
                                <table class="table table-bordered table-hover" style="font-size:13px; font-family:Tahoma;">
                                    <thead>
                                        <tr>
                                            <th width="30%">Project Name</th>
                                            <th width="10%">Date Started</th>
                                            <th width="9%">Contract Amount</th>
                                            <th width="9%">Actual Cost</th>
                                            <th width="9%"><div align="center">Comparative Income Statement</div></th>
                                            <th width="7%"><div align="center">DQC</div></th>
                                            <th width="7%"><div align="center">Cost Analysis</div></th>
                                            <th width="7%"><div align="center">POW</div></th>
                                            <th width="7%"><div align="center">BOQ</div></th>
                                            <th width="7%"><div align="center">Full Details</div></th>
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
                                        $non_po=$db->getValue('voucher_detail','sum(amount)',array('proj_id'=>$rProj['proj_id']));
                                        
                                        
                                        $qPO_amount = $db->query('SELECT round( sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ),2) FROM po, po_item WHERE po.po_id=po_item.po_id AND po.proj_id="'.$db->clean($rProj['proj_id']).'"');
                                        $po_amount = $db->result();
                                        $amount = $non_po + $po_amount;

                                        $qInhouseMaterial = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi where im.im_id=imi.im_id AND im.proj_id="'.$db->clean($rProj['proj_id']).'"');
                                        $amount += $db->result($qInhouseMaterial,0);

                                        $qInhouseRental = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id AND proj_id="'.$db->clean($rProj['proj_id']).'"');
                                        $amount += $db->result($qInhouseRental,0);
                                        
                                        $bgColor='';
                                        $consumedPercent=0;
                                        if( isset($rProj['date_completed']) ){
                                            $bgColor = 'bgcolor="#86f59b"';
                                        }
                                        else if($amount && $rProj['proj_cost']){
                                            $consumedPercent = ($amount/$rProj['proj_cost']) * 100;
                                            if( $consumedPercent >= 41)
                                                $bgColor = 'bgcolor="#ff0000" style="color: #FFF;"';
                                            elseif( $consumedPercent <= 40 && $consumedPercent >= 31)
                                                $bgColor = 'bgcolor="#f5ae00"';
                                            elseif( $consumedPercent <= 30 && $consumedPercent >= 20)
                                                $bgColor = 'bgcolor="#eaff00"';
                                        }
                                    ?>
                                        <tr <?php #echo $bgColor;?>>
                                            <td><?php echo $rProj['proj_name'];?></td>
                                            <td><?php echo functions::datearr($rProj['date_start']);?> </i></td>
                                            <td><?php echo functions::formatMoney($rProj['proj_cost']);?></td>
                                            <td>
                                                <a id="costdetail<?php echo $rProj['proj_id']?>" class="label label-info thickbox" title="Actual Cost Details" data-rel="tooltip" onclick="showThis(this.id,'project_cost_view.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Cost Details','1')">
                                                <?php echo functions::formatMoney($amount);?>
                                                </a>
                                            </td>
                                            <td>
                                                <div align="center">
                                                <a id="incmstmnt<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Project Cost Details" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement.php?prjID=<?php echo functions::encode($rProj['proj_id']);?>','Project Income Statement','1')">
                                                <i class="halflings-icon white list-alt"></i>
                                                </a>
                                                </div>
                                            </td>
                                            <td>
                                                <div align="center">
                                                <a id="dqc<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Detailed Quantity Calculation" data-rel="tooltip" onclick="showThis(this.id,'dqc.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Detailed Quantity Calculation','1')">
                                                <i class="halflings-icon white list-alt"></i>
                                                </a>
                                                </div>
                                            </td>
                                            <td>
                                                <div align="center">
                                                <a id="ca<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Cost Analysis" data-rel="tooltip" onclick="showThis(this.id,'ca_dqc.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Cost Analysis','1')">
                                                <i class="halflings-icon white list-alt"></i>
                                                </a>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if( $db->getValue('cost_analysis','count(*)',array('proj_id'=>$rProj['proj_id'])) ){?>
                                                <div align="center">
                                                <a id="pow<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Program Of Works" data-rel="tooltip" onclick="showThis(this.id,'pow.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Program Of Works','1')">
                                                <i class="halflings-icon white list-alt"></i>
                                                </a>
                                                </div>
                                                <?php }?>
                                            </td>
                                            <td>
                                                <?php if( $db->getValue('cost_analysis','count(*)',array('proj_id'=>$rProj['proj_id'])) ){?>
                                                <div align="center">
                                                <a id="boq<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Bill Of Quantities" data-rel="tooltip" onclick="showThis(this.id,'boq.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Bill Of Quantities','1')">
                                                <i class="halflings-icon white list-alt"></i>
                                                </a>
                                                </div>
                                                <?php }?>
                                            </td>
                                            <td>
                                                <div align="center">
                                                <a id="detail<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Project Detail" data-rel="tooltip" onclick="showThis(this.id,'project_view.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Detail')"><i class="halflings-icon white zoom-in"></i></a>
                                                </div>
                                            </td>
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
function projSel(PiEwgD){
    if(PiEwgD)
        window.location="<?php echo $_SERVER['PHP_SELF']?>?pid="+PiEwgD
    else
        window.location="<?php echo $_SERVER['PHP_SELF']?>"
}
</script>
<?php require_once('templ_down.php');?>