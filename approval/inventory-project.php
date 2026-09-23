<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$arrVal=array('project'=>'1');
if($p_id){
    $arrVal = array('proj_id'=>$p_id,'project'=>'1');
}

if( isset($_REQUEST['pidDel']) && !empty($_REQUEST['pidDel']) ){
    $projDelID = functions::decode($_REQUEST['pidDel']);
    $db->delete('po',array('proj_id'=>$projDelID));
    $db->delete('voucher_detail',array('proj_id'=>$projDelID));
    $db->delete('project',array('proj_id'=>$projDelID));
}
?>
<table width="390" cellspacing="4" cellpadding="6" border='0' align="right">
    <tr>
        <td width="25" height='30'><div style="background-color:#ff0000; width:20px;">&nbsp;</div></td>
        <td width="82">>= 41%</td>
        <td width="25"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
        <td width="69">>= 31%</td>
        <td width="25"><div style="background-color:#eaff00; width:20px;">&nbsp;</div></td>
        <td width="59">>= 20%</td>
        <td width="25"><div style="background-color:#86f59b; width:20px;">&nbsp;</div></td>
        <td width="59">>= DONE</td>
    </tr>
</table>
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Project List</h2>
        </div>
        <div align="left"><br>&nbsp;&nbsp;
            <select name="selProj" id="selProj" data-rel="chosen" style="width:650px;font-size:12px;" onChange="projSel(this.value)">
                <option value="">--All Projects--</option>
                <?php
                $qProj = $db->select('project','*',array('project'=>'1'),'ORDER BY proj_name');
                while($rProj = $db->fetch_array($qProj)):
                ?>
                <option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($p_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
                <?php endwhile;?>
            </select>
        </div>
        <div class="box-content">
            <table class="table table-bordered" style="font-size:13px; font-family:Tahoma;">
                <thead>
                    <tr>
                        <th width="28%">Project Name</th>
                        <th width="10%">Date Started</th>
                        <th width="5%">Contract Duration<br>(Days)</th>
                        <th width="4%">Days Remain</th>
                        <th width="9%">Contract Amount</th>
                        <th width="9%">Actual Cost</th>
                        <th width="9%"><div align="center">Materials</div></th>
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
                    $daysExtension = functions::date_diff($dateCompletion,$dateReviseCompletion);
                    $daysDuration = functions::date_diff($dateStart,$dateCompletion);

                    $daysElapsed=0;
                    $daysRemaining=0;
                    if($dateStart <= date('Y-m-d')){
                        if( isset($rProj['date_completed']) ){
                            $daysElapsed = functions::date_diff($dateStart,$rProj['date_completed']);
                            if($rProj['date_revise_completion'])
                                $daysRemaining = functions::date_diff($rProj['date_completed'],$dateReviseCompletion);
                            else if($rProj['date_completion'])
                                $daysRemaining = functions::date_diff($rProj['date_completed'],$rProj['date_completion']);
                        }
                        else if( !isset($rProj['date_completed']) ){
                            $daysElapsed = functions::date_diff($dateStart,date('Y-m-d'));
                            if($rProj['date_revise_completion'])
                                $daysRemaining = functions::date_diff(date('Y-m-d'),$dateReviseCompletion);
                            else if($rProj['date_completion'])
                                $daysRemaining = functions::date_diff(date('Y-m-d'),$rProj['date_completion']);
                        }
                    }
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
                    }
                ?>
                    <tr <?php echo $bgColor;?>>
                         <td><a id="detail<?php echo $rProj['proj_id']?>" class="thickbox" style="cursor:pointer; text-decoration: none" title="Project Detail" data-rel="tooltip" onclick="showThis(this.id,'project_view.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Detail')"><?php echo $rProj['proj_name'];?></a></td>
                        <td><?php echo functions::datearr($rProj['date_start']);?></i></td>
                        <td><?php echo $daysDuration;?></td>
                        <td><?php echo $daysRemaining;?></td>
                        <td><?php echo functions::formatMoney($rProj['proj_cost']);?></td>
                        <td>
                            <?php if($amount){?>
                                <a id="costdetail<?php echo $rProj['proj_id']?>" class="label label-info thickbox" title="Actual Cost Details" data-rel="tooltip" onclick="showThis(this.id,'project_cost_view.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Cost Details','1')"><?php echo functions::formatMoney($amount);?></a>
                            <?php }else{
                                echo functions::formatMoney($amount);
                            }?>
                        </td>
                        <td><div align="center"><?php if($amount){?><a id="inventory<?php echo $rProj['proj_id']?>" class="label label-info thickbox" title="Inventory Detail" data-rel="tooltip" onclick="showThis(this.id,'inventory_item.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Inventory Detail','1')">View</a><?php }?></div></td>
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
function delt(){
    if(confirm('Do you want to remove this Project?'))
        return true;
    else
        return false; 
}
</script>
<?php require_once('templ_down.php');?>