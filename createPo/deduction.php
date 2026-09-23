<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arrVal=array();
if($p_id){
    $arrVal = array('proj_id'=>$p_id);
}

$idDel = (isset($_REQUEST['idDel']) && !empty($_REQUEST['idDel']) ) ? functions::decode($_REQUEST['idDel']) : 0;
if($idDel){
    if($db->getValue('voucher_detail','count(category_id)',array('category_id'=>$idDel))==0 && $db->getValue('po','count(category_id)',array('category_id'=>$idDel))==0 ){
        $db->delete('item_deduction',array('item_id'=>$idDel));
        functions::sendTo('deduction.php');       
    }
}
$arr_current_assets = array();$arr_noncurrent_assets = array();
$arr_current_liabilities=array();$arr_noncurrent_liabilities=array();
$arr_equity=array(); $arr_income=array();
$arr_exp_labor=array();$arr_exp_material=array();$arr_exp_subcon=array();$arr_exp_inco=array();$arr_exp_gase=array();
$arr_others=array();
$countCharge=0;
$qC = $db->select('item_deduction','*',array(),'ORDER BY account_type,account_type_group,numbering IS NULL,name');
while($rC = $db->fetch_array($qC)):
    $arr = array('numbering'=>$rC['numbering'],'item_id'=>$rC['item_id'],'name'=>$rC['name'],'description'=>$rC['description'],'cost_type'=>$rC['cost_type'],'expense_type'=>$rC['expense_type'],'account_type'=>$rC['account_type'],'account_type_group'=>$rC['account_type_group']);
    if($rC['account_type']=='assets'){
        if($rC['account_type_group']=='current asset')
            $arr_current_assets[]=$arr;
        else if($rC['account_type_group']=='non-current asset')
            $arr_noncurrent_assets[]=$arr;
    }
    elseif($rC['account_type']=='liabilities'){
        if($rC['account_type_group']=='current liabilities')
            $arr_current_liabilities[]=$arr;
        else if($rC['account_type_group']=='non-current liabilities')
            $arr_noncurrent_liabilities[]=$arr;
    }
    elseif($rC['account_type']=='equity'){
        if($rC['account_type_group']=='equity')
            $arr_equity[]=$arr;
    }
    elseif($rC['account_type']=='income'){
        if($rC['account_type_group']=='income')
            $arr_income[]=$arr;
    }
    elseif($rC['account_type']=='expense'){
        if($rC['account_type_group']=='direct labor')
            $arr_exp_labor[]=$arr;
        else if($rC['account_type_group']=='direct material')
            $arr_exp_material[]=$arr;
        else if($rC['account_type_group']=='sub contractor')
            $arr_exp_subcon[]=$arr;
        else if($rC['account_type_group']=='indirect cost')
            $arr_exp_inco[]=$arr;
        else if($rC['account_type_group']=='general, administrative and selling expense')
            $arr_exp_gase[]=$arr;
    }
    else{
        $arr_others[]=$arr;
    }
$countCharge++;
endwhile;
$arrCharge=array();
$arrCharge = array_merge($arr_current_assets,$arr_noncurrent_assets,$arr_current_liabilities,$arr_noncurrent_liabilities,$arr_equity,$arr_income,$arr_exp_labor,$arr_exp_material,$arr_exp_subcon,$arr_exp_inco,$arr_exp_gase,$arr_others);
?>
<!-- body content: start here-->
<div align="right"><a id="adc" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'deduction_add.php?','Charge Detail')">Add Charge Category</a></div><br>
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Charge Category List</h2>
        </div>
        <div class="box-content">
            <table class="table table-striped table-bordered" style="font-size:12px">
                <thead>
                    <tr>
                        <th width="20%">Name</th>
                        <th width="15%">Description</th>
                        <th width="10%">Project Expense Type</th>
                        <th width="10%">Project Cost Type</th>
                        <th width="20%">Account Type</th>
                        <th width="8%"><div align="center">Options</div></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $accountType='';
                foreach($arrCharge as $rDisp):
                    $accnt=($rDisp['account_type']) ? $rDisp['account_type'] : 'unknown account type';
                    if($accountType!=$accnt){
                        $accountType=$accnt;
                        echo '<tr><td colspan="6" height="50px"><div align="center" style="padding-top:20px;font-size:16px;"><strong>'.strtoupper($accountType).'</strong></div></td></tr>';
                    }
                ?>
                    <tr>
                        <td><?php echo $rDisp['name']; echo ($rDisp['numbering']) ? ' <i>('.$rDisp['numbering'].')</i>' : '';?></td>
                        <td><?php echo $rDisp['description'];?></td>
                        <td><?php echo $rDisp['expense_type'];?></td>
                        <td><?php echo $rDisp['cost_type'];?></td>
                        <td><?php echo strtoupper($rDisp['account_type']); echo ($rDisp['account_type_group']) ? ' - <i>('.$rDisp['account_type_group'].')</i>' : '';?></td>
                        <td style="padding-left:15px;">
                            <div align="left">
                                <a id="edit<?php echo $rDisp['item_id']?>" class="btn btn-mini btn-info thickbox" title="Modify this Project" data-rel="tooltip" onclick="showThis(this.id,'deduction_edit.php?did=<?php echo functions::encode($rDisp['item_id']);?>','Deduction Detail')"><i class="halflings-icon white pencil"></i></a>
                                <?php if($db->getValue('voucher_detail','count(category_id)',array('category_id'=>$rDisp['item_id']))==0 && $db->getValue('po','count(category_id)',array('category_id'=>$rDisp['item_id']))==0 ){?>
                                        <a id="del<?php echo $rDisp['item_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Item" data-rel="tooltip" href="deduction.php?idDel=<?php echo functions::encode($rDisp['item_id']);?>"><i class="halflings-icon white trash"></i></a>
                                <?php }?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function delt(){
    if(confirm('Do you want to remove this Item?'))
        return true;
    else
        return false; 
}
</script>
<?php require_once('templ_down.php');?>