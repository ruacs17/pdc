<?php require_once('templ_up.php');?>
<?php
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$arr = array();
$mon='';$txProj = '';$txPayee = '';$txbMon = '';$txbYear = ''; $txSearch='';
$num_record = 0;

if( isset($_POST['btnSearch2']) ){
    $txSearch = ( isset($_POST['txSearch']) && !empty($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
    $qPO = $db->query("SELECT ref_id,po_date,delivery_date,supplierID,invoice,received FROM po p WHERE (ref_id LIKE '%".$db->clean($txSearch)."%' OR invoice LIKE '%".$db->clean($txSearch)."%') GROUP BY p.ref_id LIMIT ".$startrow.", ".$rowdisplay);
    $qPOCount = $db->query("SELECT ref_id,po_date,delivery_date,supplierID,invoice,received FROM po p WHERE (ref_id LIKE '%".$db->clean($txSearch)."%' OR invoice LIKE '%".$db->clean($txSearch)."%') GROUP BY p.ref_id");
    $num_record = $db->num_rows($qPOCount);
}
$bgc_unserved='#e6eb9c';  $bgc_not_received='#f5ae00'; $bgc_del_inc='#ff889e';
?>
<!-- body content: start here-->
<table width="470" cellspacing="0" cellpadding="0" border='0' align="right">
    <tr>
        <td width="10" height='30' style="visibility:hidden;"><div style="background-color:#ff889e; width:20px;">&nbsp;</div></td>
        <td width="90" style="visibility:hidden;"> Unserved</td>
        <td width="20" style="visibility:hidden;"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
        <td width="90" style="visibility:hidden;"> Delivery Incomplete</td>
        <td width="10" height='30'><div style="background-color:<?php echo $bgc_not_received ?>; width:20px;">&nbsp;</div></td>
        <td width="90"> Not Received</td>
    </tr>
</table>
<br><br>
<div class="row-fluid">
        <div class="box span12">
                <div class="box-header" data-original-title>
                    <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Purchase Order Receiving</h2>
                </div>
                <div class="box-content">
                    <form method="post">                     
                        <table border="0">
                            <tr>
                                <td colspan="4">P.O. Number or Invoice Number: <input type="text" name="txSearch" value="<?php echo $txSearch;?>">&nbsp;<input type="submit" name="btnSearch2" id="btnSearch2" value="Search" class="btn btn-primary">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="4"><hr width="100%"></td>
                            </tr>
                            <tr><td colspan="4"><hr width="100%"></td></tr>
                        </table>     
                    </form>
                    <table class="table table-bordered table-hover" style="font-size:12px">
                        <thead>
                            <tr>
                                <th width="6%"><div>P.O. #</div></th>
                                <th width="9%">P.O. Date</th>
                                <th width="6%"><div>Invoice</div></th>
                                <th width="9%">Receive Date</th>
                                <th width="25%">Payee</th>
                                <th width="8%">Amount</th>
                                <th width="5%"><div align="center">View</div></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $countResult=0;
                        if($txSearch){
                            while($rPO = $db->fetch_array($qPO)):
                                $countResult++;
                                $total_amount=0;$amount=0;
                                $qPOI = $db->query('SELECT pi.item,sum(quantity) as qty,sum(qty_delivered) as qty_d,unit,brand,cost,cost as t_cost,discount FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$rPO['ref_id'].'" GROUP by pi.item');
                                while($rPOI = $db->fetch_array($qPOI)):
                                    $amount = $rPOI['t_cost'] * $rPOI['qty_d'];
                                    $disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
                                    $amount = $amount - $disc_amount;
                                    $total_amount += $amount;
                                endwhile;
                                $bgColor = ($rPO['received']==0) ? 'bgcolor="'.$bgc_not_received.'"' : '';
                        ?>
                            <tr <?php echo $bgColor; ?>>
                                <td><div><?php echo $rPO['ref_id'];?></div></td>
                                <td><?php echo functions::datearr($rPO['po_date']);?></td>
                                <td><div><?php echo $rPO['invoice'];?></div></td>
                                <td><?php echo functions::datearr($rPO['delivery_date']);?></td>
                                <td><?php echo $db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID']));?></td>
                                <td><?php echo functions::formatMoney($total_amount);?></td>
                                <td>
                                    <div align="center">
                                        <a id="costdetail<?php echo $rPO['ref_id']?>" class="btn btn-info btn-mini thickbox" title="View P.O. Details" data-rel="tooltip" onclick="showThis(this.id,'po_receive_view_only.php?ref_id=<?php echo functions::encode($rPO['ref_id']);?>','P.O. Details','1')"><i class="halflings-icon white search"></i></a>
                                    </div>
                                </td>
                            </tr>
                                <?php endwhile;
                            if($countResult==0)
                                echo '<tr><td colspan="7"><div align="center">--No Result Found--</div></td></tr>';
                        }?>
                        </tbody>
                    </table>
                    <div align="center"><?php if($countResult)functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=$_SERVER['PHP_SELF'].'?',$search="");?></div>
                </div>
        </div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function delt(){
    if(confirm('Do you want to remove this P.O.?'))
        return true;
    else
        return false; 
}
</script>
<?php require_once('templ_down.php');?>