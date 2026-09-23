<?php require_once('templ_up.php');?>
<?php
if( isset($_REQUEST['prj']) && !empty($_REQUEST['prj']) ){
    $_SESSION['ae_prj'] = functions::decode($_REQUEST['prj']);
    functions::sendTo($_SERVER['PHP_SELF']);
}

if( isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ){
    $_SESSION['ae_yr'] = functions::decode($_REQUEST['yr']);
    functions::sendTo($_SERVER['PHP_SELF']);
}

$project_id = ( isset($_SESSION['ae_prj']) ) ? $_SESSION['ae_prj'] : '';

$arrVal=array();
if($project_id){
    $arrVal = array('proj_id'=>$project_id,'project'=>'1');
}

function monthDisp($date=0){
    $exp = explode('-',$date);
    $monthName='';
    if(count($exp)==2){
        $mn = $exp[1];
        $yr = $exp[0];
        $mkDate = mktime(0,0,0,$mn,1,$yr);
        $monthName = date("F'y",$mkDate);
    }
    return $monthName;
}
#$project_id=9;
$arrDirectCost=array();
$arrOverheadCost=array();
$arrNetBilled=array();
$totalLabor=0;  $totalMaterial=0; $totalEquipment=0;  $totalCommitment=0; $totalFinder=0; $totalTechnical=0; $totalConsultancy=0; $totalDirectCost=0; $totalOverheadCost=0; $totalOperatingExpenses=0; $totalIncome=0; $totalNetBilled=0;
$countID=0;$totalVAT=0;$totalEWT=0;$totalRetention=0;$totalConTax=0;$totalRecoupment=0; $totalDeductions=0;

$year = date('Y');
$month_start = date('m');
$year_start = date('Y');
$month_end = date('m');
$year_end = date('Y');
$year = ( isset($_SESSION['ae_yr']) ) ? $_SESSION['ae_yr'] : $year;
$arrMonth=array();

$month_start = '01';
$year_start = $year;

$month_end = '11';
$year_end=$year;
$start_date=$year.'-01-01';
$end_date=$year.'-12-31';
#end getting end month

$month_diff = functions::month_diff($start_date,$year_end.'-'.$month_end.'-01');
for($i=0; $i<=$month_diff; $i++):
    $mkDate = mktime(0,0,0,$month_start + $i,1,$year_start);
    $arrMonth[] = date('Y-m',$mkDate);
    $arrDirectCost[date('Y-m',$mkDate)]=0;
    $arrNetBilled[date('Y-m',$mkDate)]=0;
endfor;
?>
<!-- body content: start here-->
<form method="post">
    <div class="row-fluid">
        <div class="box span12">
            <div class="box-header" data-original-title>
                <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Expenses</h2>
            </div>
            <div class="box-content">
                <div align="center"><br>
                    <table width="80%" border="0">
                        <tr>
                            <td width="50%" align="right" style="padding: 12px 4px 4px 0px">
                                <select name="selProj" id="selProj" style="font-size:12px;height:50px;" onChange="projSel(this.value,document.getElementById('yr').value)">
                                    <option value="">-- Select Admin --</option>
                                    <?php $qProj = $db->select('project','*',array('project'=>'0'),'ORDER BY proj_name');
                                    while($rProj = $db->fetch_array($qProj)):
                                    ?>
                                    <option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($project_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
                                    <?php endwhile;?>
                                </select>
                            </td>
                            <td width="50%" align="left" valign="middle" style="padding: 12px 0px 4px 0px">
                                <select name="yr" id="yr" style="width:150px;font-size:12px;height:50px;" onChange="projSel(document.getElementById('selProj').value,this.value)">
                                    <option value="">-- Select Year --</option>
                                    <?php
                                    $qYr = $db->query('SELECT DISTINCT left(vd_date,4) as dt FROM voucher_detail ORDER BY vd_date DESC ');
                                    while($rYr = $db->fetch_array($qYr)):
                                    ?>
                                    <option value="<?php echo functions::encode($rYr['dt'])?>" <?php if($year==$rYr['dt'])echo 'selected="selected"';?>><?php echo $rYr['dt']?></option>
                                    <?php endwhile;?>
                                </select>
                            </td>
                        </tr>
                    </table><br>
                </div>
                <table class="table-hover" width="100%" border="1" style="font-size:12px;">
                    <tr>
                        <td>&nbsp;</td>
                        <?php foreach($arrMonth as $month):?>
                        <td><div align="center"><?php echo monthDisp($month)?></div></td>
                        <?php endforeach;?>
                        <td class="padleft"><div align="right">Total</div></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <?php foreach($arrMonth as $monthIncome):?>
                        <td>&nbsp;</td>
                        <?php endforeach;?>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <?php foreach($arrMonth as $monthIncome):?>
                        <td>&nbsp;</td>
                        <?php endforeach;?>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="<?php echo count($arrMonth) + 2?>">Direct Cost</td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;Labor</td>
                        <?php foreach($arrMonth as $month):?>
                        <td class="padleft">
                            <div align="right">
                                <?php
                                $costLabor=0;
                                $qVoucherLabor = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,7)="'.$month.'" AND id.expense_type="labor" AND p.owned_by="'.$db->clean($project_id).'" AND p.date_start IS NULL');
                                $totalLabor += $costLabor = $db->result($qVoucherLabor,0);
                                $arrDirectCost[$month] += $costLabor;
                                ?>
                                <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("labor");?>&mon=<?php echo functions::encode($month);?>','Expense Cost Details','1')"><?php echo ($costLabor) ? functions::formatMoney($costLabor) : '';?></a>
                            </div>
                        </td>
                        <?php endforeach;?>
                        <td class="padleft">
                            <div align="right">
                                <a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("labor");?>&yr=<?php echo functions::encode($year);?>','Expense Cost Details','1')"><?php echo functions::formatMoney($totalLabor);?></a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;Materials</td>
                        <?php foreach($arrMonth as $month):?>
                        <td class="padleft">
                            <div align="right">
                            <?php
                            $costMaterial=0;
                            $qVoucherMaterial = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,7)="'.$month.'" AND id.expense_type="materials" AND p.owned_by="'.$db->clean($project_id).'" AND p.date_start IS NULL');
                            $costMaterial += $db->result($qVoucherMaterial,0);

                            $qPO = $db->query('SELECT round( sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ),2) as res FROM po_item poi, po p, project pj WHERE p.proj_id=pj.proj_id AND p.po_id=poi.po_id AND left(po_date,7)="'.$month.'" AND vp_id IS NOT NULL AND pj.owned_by="'.$db->clean($project_id).'" AND pj.date_start IS NULL');
                            $costMaterial += $db->result($qPO,0);

                            $qInhouseMaterial = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi,project p where im.im_id=imi.im_id AND im.proj_id=p.proj_id AND p.owned_by="'.$db->clean($project_id).'" AND p.date_start IS NULL AND left(im_date,7)="'.$month.'"');
                            $costMaterial += $db->result($qInhouseMaterial,0);
                            $totalMaterial += $costMaterial;
                            $arrDirectCost[$month] += $costMaterial;
                            ?>
                            <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("materials");?>&mon=<?php echo functions::encode($month);?>','Expense Cost Details','1')"><?php echo ($costMaterial) ? functions::formatMoney($costMaterial) : '';?></a>
                            </div>
                        </td>
                        <?php endforeach;?>
                        <td class="padleft">
                            <div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("materials");?>&yr=<?php echo functions::encode($year);?>','Expense Cost Details','1')"><?php echo functions::formatMoney($totalMaterial);?></a></div>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;Equipment/Rental</td>
                        <?php foreach($arrMonth as $month):?>
                        <td class="padleft">
                            <div align="right">
                            <?php
                            $costEquipment=0;
                            $qVoucherEquipment = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,7)="'.$month.'" AND id.expense_type="equipment" AND p.owned_by="'.$db->clean($project_id).'" AND p.date_start IS NULL');
                            $costEquipment += $db->result($qVoucherEquipment,0);

                            $qInhouseRental = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli,project p WHERE iel.iel_id=ieli.iel_id AND iel.proj_id=p.proj_id AND p.owned_by="'.$db->clean($project_id).'" AND p.date_start IS NULL AND left(iel_date,7)="'.$month.'"');
                            $costEquipment += $db->result($qInhouseRental,0);

                            $totalEquipment += $costEquipment;
                            $arrDirectCost[$month] += $costEquipment;
                            ?>
                            <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("equipment");?>&mon=<?php echo functions::encode($month);?>','Expense Cost Details','1')"><?php echo ($costEquipment) ? functions::formatMoney($costEquipment) : '';?></a></div>
                        </td>
                        <?php endforeach;?>
                        <td class="padleft">
                            <div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("equipment");?>&yr=<?php echo functions::encode($year);?>','Expense Cost Details','1')"><?php echo functions::formatMoney($totalEquipment);?></a></div></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;Commitment</td>
                        <?php foreach($arrMonth as $month):?>
                        <td class="padleft">
                            <div align="right">
                                <?php
                                $costCommitment=0;
                                $qCommitment = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND left(vd_date,7)="'.$month.'" AND itd.cost_type="commitment" AND p.owned_by="'.$db->clean($project_id).'" AND p.date_start IS NULL');
                                $totalCommitment += $costCommitment = $db->result($qCommitment,0);
                                $arrDirectCost[$month] += $costCommitment;
                                ?>
                                <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("commitment");?>&mon=<?php echo functions::encode($month);?>','Expense Cost Details','1')"><?php echo ($costCommitment) ? functions::formatMoney($costCommitment) : '';?></a>
                            </div>
                        </td>
                        <?php endforeach;?>
                        <td class="padleft"><div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("commitment");?>&yr=<?php echo functions::encode($year);?>','Expense Cost Details','1')"><?php echo functions::formatMoney($totalCommitment);?></a></div></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;Finder's Fee</td>
                        <?php foreach($arrMonth as $month):?>
                        <td class="padleft">
                            <div align="right">
                                <?php
                                $costFinder=0;
                                $qFinder = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND left(vd_date,7)="'.$month.'" AND itd.cost_type="finders fee" AND p.owned_by="'.$db->clean($project_id).'" AND p.date_start IS NULL');
                                $totalFinder += $costFinder = $db->result($qFinder,0);
                                $arrDirectCost[$month] += $costFinder;
                                ?>
                                <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("finders fee");?>&mon=<?php echo functions::encode($month);?>','Expense Cost Details','1')"><?php echo ($costFinder) ? functions::formatMoney($costFinder) : '';?></a>
                            </div>
                        </td>
                        <?php endforeach;?>
                        <td class="padleft"><div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("finders fee");?>&yr=<?php echo functions::encode($year);?>','Expense Cost Details','1')"><?php echo functions::formatMoney($totalFinder);?></a></div></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;Consultancy</td>
                        <?php foreach($arrMonth as $month):?>
                        <td class="padleft">
                            <div align="right">
                                <?php
                                $costConsultancy=0;
                                $qConsultancy = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND left(vd_date,7)="'.$month.'" AND itd.cost_type="consultancy" AND p.owned_by="'.$db->clean($project_id).'" AND p.date_start IS NULL');
                                $totalConsultancy += $costConsultancy = $db->result($qConsultancy,0);
                                $arrDirectCost[$month] += $costConsultancy;
                                ?>
                                <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("consultancy");?>&mon=<?php echo functions::encode($month);?>','Expense Cost Details','1')"><?php echo ($costConsultancy) ? functions::formatMoney($costConsultancy) : '';?></a>
                            </div>
                        </td>
                        <?php endforeach;?>
                        <td class="padleft"><div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("consultancy");?>&yr=<?php echo functions::encode($year);?>','Expense Cost Details','1')"><?php echo functions::formatMoney($totalConsultancy);?></a></div></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;Technical Fee</td>
                        <?php foreach($arrMonth as $month):?>
                        <td class="padleft">
                            <div align="right">
                                <?php
                                $costTechnical=0;
                                $qTechnical = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND left(vd_date,7)="'.$month.'" AND itd.cost_type="technical fee" AND p.owned_by="'.$db->clean($project_id).'" AND p.date_start IS NULL');
                                $totalTechnical += $costTechnical = $db->result($qTechnical,0);
                                $arrDirectCost[$month] += $costTechnical;
                                ?>
                                <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("technical fee");?>&mon=<?php echo functions::encode($month);?>','Expense Cost Details','1')">
                                    <?php echo ($costTechnical) ? functions::formatMoney($costTechnical) : '';?>
                                </a>
                            </div>
                        </td>
                        <?php endforeach;?>
                        <td class="padleft"><div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("technical fee");?>&yr=<?php echo functions::encode($year);?>','Expense Cost Details','1')"><?php echo functions::formatMoney($totalTechnical);?></a></div></td>
                    </tr>
                    <tr>
                        <td><strong>Total Direct Cost</strong></td>
                        <?php foreach($arrMonth as $month):?>
                        <td class="padleft">
                            <div align="right">
                                <strong>
                                <?php
                                $totalDirectCost += $arrDirectCost[$month];
                                echo functions::formatMoney($arrDirectCost[$month]);
                                ?>
                                </strong>
                            </div>
                        </td>
                        <?php endforeach;?>
                        <td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalDirectCost);?></strong></div></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <?php foreach($arrMonth as $monthIncome):?>
                        <td>&nbsp;</td>
                        <?php endforeach;?>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="<?php echo count($arrMonth) + 2?>">Operating Expenses</td>
                    </tr>
                    <?php
                    $prevTotalOperatingExpenses = 0;
                    $qOverhead = $db->query('SELECT DISTINCT category_id FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND itd.expense_type="overhead" AND cost_type="operating expenses" AND left(vd.vd_date,4)="'.$year.'" AND p.owned_by="'.$db->clean($project_id).'" AND p.date_start IS NULL ORDER BY itd.name');
                    while($rOverhead = $db->fetch_array($qOverhead)):
                        $totalCategoryCost=0;
                    ?>
                    <tr>
                        <td style="padding: 2px 2px 2px 8px;"><?php echo $db->getValue('item_deduction','name',array('item_id'=>$rOverhead['category_id']));?></td>
                        <?php foreach($arrMonth as $month):
                        $countID++;
                        if( !isset($arrOverheadCost[$month]) )
                            $arrOverheadCost[$month]=0;
                        ?>
                        <td class="padleft">
                            <div align="right">
                                <?php
                                $categoryCost=0;
                                $q = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND vd.category_id="'.$rOverhead['category_id'].'" AND left(vd_date,7)="'.$month.'" AND p.owned_by="'.$db->clean($project_id).'" AND p.date_start IS NULL');
                                $categoryCost = $db->result($q,0);
                                $arrOverheadCost[$month] += $categoryCost;
                                $totalCategoryCost += $categoryCost;
                                $totalOverheadCost += $categoryCost;
                                $totalOperatingExpenses += $categoryCost;
                                ?>
                                <a id="costdetail<?php echo $rOverhead['category_id'].$countID;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&cid=<?php echo functions::encode($rOverhead['category_id']);?>&mon=<?php echo functions::encode($month);?>','Expense Cost Details','1')">
                                    <?php echo ($categoryCost) ? functions::formatMoney($categoryCost) : '';?>
                                </a>
                            </div>
                        </td>
                        <?php endforeach;?>
                        <td class="padleft"><div align="right"><a id="costTotaldetail<?php echo $rOverhead['category_id'].$countID;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'admin-expenses-detail.php?pid=<?php echo functions::encode($project_id);?>&cid=<?php echo functions::encode($rOverhead['category_id']);?>&yr=<?php echo functions::encode($year);?>','Expense Cost Details','1')"><?php echo functions::formatMoney($totalCategoryCost);?></a></div></td>
                    </tr>
                    <?php endwhile;?>
                    <tr>
                        <td><strong>Total Operating Expenses</strong></td>
                        <?php foreach($arrOverheadCost as $ohCost):?>
                        <td class="padleft"><div align="right"><strong><?php echo ($ohCost) ? functions::formatMoney($ohCost) : '';?></strong></div></td>
                        <?php endforeach;?>
                        <td class="padleft">
                            <div align="right">
                                <strong><?php echo functions::formatMoney($totalOverheadCost);?></strong>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="<?php echo count($arrMonth) + 1?>"><div align="right"><strong>Total Expenses&nbsp;&nbsp;</strong></div></td>
                        <td class="padleft">
                            <div align="right">
                                <strong>
                                <?php 
                                $totalExpenses = $totalDirectCost + $totalOperatingExpenses;
                                echo functions::formatMoney($totalExpenses);
                                ?>
                                </strong>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div><!--/span-->
    </div><!--/row-->
</form>
<!-- body content: end here-->
<script>function projSel(PiEwgD,YsgER){window.location="<?php echo $_SERVER['PHP_SELF']?>?prj="+ PiEwgD +"&yr="+YsgER}</script>
<?php require_once('templ_down.php');?>