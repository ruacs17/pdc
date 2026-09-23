<?php require_once('templ_up.php');?>
<?php
if( isset($_REQUEST['sup']) ){
    $_SESSION['ai_sup'] = ( !empty($_REQUEST['sup']) ) ? functions::decode($_REQUEST['sup']) : 0;
    functions::sendTo($_SERVER['PHP_SELF']);
}

if( isset($_REQUEST['yr']) ){
    $_SESSION['ae_yr'] = ( !empty($_REQUEST['yr']) ) ? functions::decode($_REQUEST['yr']) : 0;
    functions::sendTo($_SERVER['PHP_SELF']);
}
$count=0;
$year = ( isset($_SESSION['ae_yr']) ) ? $_SESSION['ae_yr'] : 0;

$arr = array();
$yr=date('Y');
$supplierID='';
$yrSearch = " AND left(po_date,4)='".$yr."'";

$supplierID = (isset($_SESSION['ai_sup']) ) ? $_SESSION['ai_sup'] : 0;

$supplierQ='';

$yr = ( isset($_SESSION['ae_yr']) ) ? $_SESSION['ae_yr'] : 0;
if($yr=='all')
    $yrSearch = "";
else if($yr)
    $yrSearch = " AND left(po_date,4)='".$db->clean($yr)."'";
if($supplierID)
    $supplierQ = "AND p.supplierID='".$db->clean($supplierID)."'";
else
    $supplierQ = "AND sup.owned='1'";
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
                            <td width="32%" align="left" style="padding: 12px 0px 4px 0px">
                                <select name="sel_supplier" id="sel_supplier" style="width:580px;" onChange="supSel(this.value,document.getElementById('yr').value)">
                                    <option value="">-- Select Supplier --</option>
                                    <?php
                                    $qSup = $db->query('SELECT * FROM supplier WHERE owned=1 ORDER BY name');
                                    while($rSup = $db->fetch_array($qSup)):
                                    ?>
                                    <option value="<?php echo functions::encode($rSup['supplierID'])?>" <?php if($supplierID==$rSup['supplierID'])echo 'selected="selected"';?>><?php echo $rSup['name']?></option>
                                    <?php endwhile;?>
                                </select>
                            </td>
                            <td width="22%" align="left" style="padding: 12px 0px 4px 0px">
                                <select name="yr" id="yr" style="width:150px;" onChange="supSel(document.getElementById('sel_supplier').value,this.value)">
                                    <option value="" <?php if($yr==0)echo 'selected="selected"';?>>-- All Year --</option>
                                    <?php for($y=(date('Y')+1);$y>=2015;$y--):?>
                                    <option value="<?php echo functions::encode($y);?>" <?php if($yr==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
                                    <?php endfor;?>
                                </select>
                            </td>
                        </tr>
                    </table><br>
                </div>
                <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;">
                    <tr>
                        <th width="8%" scope="col"><div align="left">Date</div></th>
                        <th width="8%" scope="col"><div align="left">Voucher No.</div></th>
                        <th width="15%" scope="col"><div align="left">Charges Category</div></th>
                        <th width="25%" scope="col"><div align="left">Item Detail</div></th>
                        <th width="10%" scope="col"><div align="left">Amount</div></th>
                    </tr>
                    <tr>
                        <td colspan="5" height="25"></td>
                    </tr>
                    <?php
                    $total_amount=0;$vdate='';
                    $qPODetails = $db->query('SELECT * FROM po, po_item WHERE po.po_id=po_item.po_id AND po.supplierID="'.$db->clean($supplierID).'" '.$yrSearch.' ORDER BY po.po_date');
                    while($rPODetails = $db->fetch_array($qPODetails)):
                        $count++;
                        $amount=0;
                        $amount = $rPODetails['cost'] * $rPODetails['qty_delivered'];
                        $disc_amount = ($rPODetails['discount']) ? $amount * ($rPODetails['discount'] / 100) : 0;
                        $amount = $amount - $disc_amount;
                        $total_amount += $amount;
                    ?>
                    <tr>
                        <td>
                            <?php 
                            if($vdate != $rPODetails['po_date']){
                                $vdate = $rPODetails['po_date'];
                                echo functions::datearr($rPODetails['po_date']);
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $v = $db->getValue('voucher_particular','voucher_id',array('vp_id'=>$rPODetails['vp_id']));
                            echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$v));
                            ?>
                        </td>
                        <td><?php echo $db->getValue('voucher_particular','vp_title',array('vp_id'=>$rPODetails['vp_id']));?></td>
                        <td>
                            <?php 
                            echo $rPODetails['item'].' ( '.$rPODetails['qty_delivered'].' '.$rPODetails['unit'].' x ';
                            if( $db->getValue('po_item','count(DISTINCT cost)',array('item'=>$rPODetails['item'])) > 1 ){
                                echo functions::formatMoney($rPODetails['cost']);
                            }
                            else{
                                echo functions::formatMoney($rPODetails['cost']);
                            }
                            ?>
                            )
                        </td>
                        <td><?php echo functions::formatMoney($amount);?></td>
                    </tr>
                    <?php endwhile;?>
                    <?php
                    $q_voucher = $db->query('SELECT * FROM voucher v, voucher_particular vp, voucher_detail vd WHERE v.voucher_id=vp.voucher_id AND vd.vp_id=vp.vp_id AND vd.category_id=62 AND v.supplierID="'.$db->clean($supplierID).'"');
                    while($r_voucher = $db->fetch_array($q_voucher)):
                        $total_amount += $r_voucher['amount'];
                    ?>
                    <tr>
                        <td><?php echo functions::datearr($r_voucher['vd_date']);?></td>
                        <td><?php echo $r_voucher['voucher_no']?></td>
                        <td><?php echo $r_voucher['vp_title']?></td>
                        <td><?php echo $r_voucher['item']?></td>
                        <td><?php echo functions::formatMoney($r_voucher['amount']);?></td>
                    </tr>
                    <?php endwhile;?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>&nbsp;</td>
                        <td><div align="right"><strong>Total Amount</strong></div></td>
                        <td><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
                    </tr>
                </table>
            </div>
        </div><!--/span-->
    </div><!--/row-->
</form>
<!-- body content: end here-->
<script>function supSel(PiEwgD,YsgER){window.location="<?php echo $_SERVER['PHP_SELF']?>?sup="+ PiEwgD +"&yr="+YsgER}</script>
<?php require_once('templ_down.php');?>