<?php require_once('templ_up.php');?>
<?php
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$po_idDel = (isset($_REQUEST['po_idDel']) && !empty($_REQUEST['po_idDel']) ) ? functions::decode($_REQUEST['po_idDel']) : 0;
$arr = array();

$mon='';$txProj = '';$txPayee = '';$txbMon = '';$txbYear = ''; $txSearch='';

if( isset($_POST['btnSearch']) ){
    $arr = array();
    $_SESSION['poProj'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
    $_SESSION['poPayee'] = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : '';
    $_SESSION['poMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $_SESSION['poYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
    functions::sendTo($_SERVER['PHP_SELF']);
}
if($po_idDel){
    $amount = $db->getValue('po_item','sum( (qty_delivered * cost) )',array('po_id'=>$po_idDel));
    if($amount == 0){
        $db->delete('po',array('po_id'=>$po_idDel));
        functions::sendTo('po_list.php');
    }
}
if($po_id)
    $arr = array('po_id'=>$po_id);   
else{
    $txProj = ( isset($_SESSION['poProj']) && !empty($_SESSION['poProj']) ) ? $_SESSION['poProj'] : '';
    $txPayee = ( isset($_SESSION['poPayee']) && !empty($_SESSION['poPayee']) ) ? $_SESSION['poPayee'] : '';
    $txbMon = ( isset($_SESSION['poMon']) ) ? $_SESSION['poMon'] : date('m');
    $txbYear = ( isset($_SESSION['poYear']) ) ? $_SESSION['poYear'] : date('Y');
    if($txProj)
        $arr = array_merge($arr,array('proj_id'=>$txProj));
    if($txPayee)
        $arr = array_merge($arr,array('supplierID'=>$txPayee));

    if($txbMon && $txbYear)
        $arr = array_merge($arr,array('LEFT(po_date,7)'=>$txbYear.'-'.$txbMon));
    else if($txbMon)
        $arr = array_merge($arr,array('SUBSTRING(po_date,6,2)'=>$txbMon));
    elseif($txbYear)
        $arr = array_merge($arr,array('LEFT(po_date,4)'=>$txbYear));  
}

if(count($arr)){
    $rowdisplay=1000;
    $startrow=0;
}

$qPO = $db->select('po','*',$arr,'ORDER BY po_date DESC LIMIT '.$startrow.', '.$rowdisplay);
$num_record = $db->getValue('po','count(*)',$arr);

if( isset($_POST['btnSearch2']) ){
    $txSearch = ( isset($_POST['txSearch']) && !empty($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
    $qPO = $db->query("SELECT * FROM po WHERE po_no LIKE '%".$db->clean($txSearch)."%' OR invoice LIKE '%".$db->clean($txSearch)."%' LIMIT 0, 100");
    $num_record = $db->num_rows($qPO);
}
$bgc_unserved='#e6eb9c';  $bgc_not_received='#f5ae00'; $bgc_del_inc='#ff889e';
?>
<!-- body content: start here-->
<table width="470" cellspacing="0" cellpadding="0" border='0' align="right">
    <tr>
        <td width="10" height='30'><div style="background-color:<?php echo $bgc_unserved?>; width:20px;">&nbsp;</div></td>
        <td width="90"> <a id="poUnserve" href="#" class="thickbox" onclick="showThis(this.id,'po_monitor_unserve.php?','Unserve P.O.','1')">Unserved</a></td>
        <td width="10" height='30'><div style="background-color:<?php echo $bgc_not_received?>; width:20px;">&nbsp;</div></td>
        <td width="90"> Not Received</td>
        <td width="20"><div style="background-color:<?php echo $bgc_del_inc?>; width:20px;">&nbsp;</div></td>
        <td width="90"> Delivery Incomplete</td>
    </tr>
</table>
<br><br>
<div class="row-fluid">
        <div class="box span12">
                <div class="box-header" data-original-title>
                    <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Purchase Order List</h2>
                </div>
                <div class="box-content">
                    <form method="post">
                        <table border="0" align="right">
                            <tr>
                                <td><a id="poUnserved" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'po_monitor_unserve.php?','Unserve P.O.','1')">Unserved P.O.</a>&nbsp;&nbsp;&nbsp;<a id="poMonitoring" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'po_monitoring.php?','P.O. Monitoring')">Show Monitoring</a></td>
                            </tr>
                        </table>                        
                        <table border="0">
                            <tr>
                                <td colspan="4">P.O. Number or Invoice Number: <input type="text" name="txSearch" value="<?php echo $txSearch;?>">&nbsp;<input type="submit" name="btnSearch2" id="btnSearch2" value="Search" class="btn btn-primary">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="4"><hr width="100%"></td>
                            </tr>
                            <tr>
                                <td width="22%">
                                    <div align="left">
                                        <select name="bdYear" id="bdYear" style="width:90px;">
                                            <option value="">All Year</option>
                                            <?php
                                                $qYr = $db->select('po','DISTINCT LEFT(po_date,4) as yr',array(),'ORDER BY po_date DESC');
                                                while($rYr = $db->fetch_array($qYr)):
                                            ?>
                                            <option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
                                            <?php endwhile;?>
                                        </select>
                                        <select name="bdMon" id="bdMon" style="width:95px;">
                                            <option value="">All Month</option>
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
                                    </div>
                                </td>
                                <td width="15%">
                                    <div align="left">
                                        <select name="txPayee" id="txPayee" data-rel="chosen" style="width:350px;">
                                            <option value="">All Payee</option>
                                            <?php $qSup = $db->select('supplier','*',array(),'ORDER BY name');
                                                while($rSup = $db->fetch_array($qSup)):
                                            ?>
                                            <option value="<?php echo $rSup['supplierID']?>" <?php if($txPayee==$rSup['supplierID'])echo 'selected="selected"';?>><?php echo ($rSup['name']);?></option>
                                            <?php endwhile;?>
                                        </select>
                                    </div>
                                </td>
                                <td width="20%">
                                    <div align="left">
                                        <select name="selProj" id="selProj" data-rel="chosen" style="width:290px;">
                                            <option value="">All Project</option>
                                            <?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
                                                while($rProj = $db->fetch_array($qProj)):
                                            ?>
                                            <option value="<?php echo $rProj['proj_id']?>" <?php if($txProj==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?></option>
                                            <?php endwhile;?>
                                        </select>
                                    </div>
                                </td>
                                <td width="8%">
                                    <div align="center">
                                        <input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary">
                                    </div>
                                </td>
                            </tr>
                            <tr><td colspan="4"><hr width="100%"></td></tr>
                        </table>     
                    </form>
                    <table class="table table-bordered table-hover" style="font-size:12px">
                        <thead>
                            <tr>
                                <th width="6%"><div>P.O. #</div></th>
                                <th width="4%"><div align="center">Voucher</div></th>
                                <th width="6%"><div>Invoice</div></th>
                                <th width="9%">P.O. Date</th>
                                <th width="15%">Payee</th>
                                <th width="21%">Project</th>
                                <th width="10%">Amount</th>
                                <th width="11%"><div align="center">Options</div></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            while($rPO = $db->fetch_array($qPO)):
                                $qVid = $db->query('SELECT vp.voucher_id FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vpp.po_id="'.$db->clean($rPO['po_id']).'"');
                                $voucher_attached = $db->num_rows();
                                $amount = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id']));
								$vid = $db->getValue('voucher_particular','voucher_id',array('vp_id'=>$rPO['vp_id']));
                                $qty = $db->getValue('po_item','sum(quantity)',array('po_id'=>$rPO['po_id']));
                                $qty_delivered = $db->getValue('po_item','sum(qty_delivered)',array('po_id'=>$rPO['po_id']));
                                $approved=$db->getValue('voucher','count(approved)',array('voucher_id'=>$vid));
                                $received = $rPO['received'];
                                $bgColor='';

                                if( $voucher_attached==0 && $rPO['invoice']=="")
                                    $bgColor = 'bgcolor="'.$bgc_unserved.'"';
                                else if($received==0)
                                    $bgColor = 'bgcolor="'.$bgc_not_received.'"';
                                else if($qty != $qty_delivered)
                                    $bgColor = 'bgcolor="'.$bgc_del_inc.'"';

                                $viewOnlyPage = 'po_view_only.php';
                                $viewPage = 'po_view.php';
                                $editPage='po_edit.php';
                                $printPage='po_print.php';
                                $printPage1='po_print_1.php';
                                if( $db->getValue('po_fuel_equipment','count(po_id)',array('po_id'=>$rPO['po_id'])) ){

                                    if( $db->getValue('po','count(ref_id)',array('ref_id'=>$rPO['ref_id'])) >= 2 ){
                                        $editPage='po_fuel_edit_multiple.php';
                                    }
                                    else{
                                        $editPage='po_fuel_edit.php';
                                    }
                                    $viewOnlyPage = 'po_fuel_view_only.php';
                                    $printPage='po_fuel_print.php';
                                    $printPage1='po_fuel_print_1.php';
                                    $viewPage = 'po_fuel_view.php';
                                }
                                if($rPO['po_type']=='service'){
                                    $viewOnlyPage = 'po_service_view_only.php';
                                    $viewPage = 'po_service_view.php';
                                    $editPage='po_service_edit.php';
                                    $printPage='po_service_print.php';
                                    $printPage1='po_service_print.php';
                                }
                        ?>
                            <tr <?php echo $bgColor;?>>
                                <td><div><?php echo $rPO['po_no'];?></div></td>
                                <td>
                                    <?php 
                                        
                                        while($rVid = $db->fetch_array($qVid)):
                                    ?>
                                        <div align="center"><a href="voucher_view.php?vid=<?php echo functions::encode($rVid['voucher_id'])?>"><?php echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$rVid['voucher_id']));?></a></div>
                                    <?php endwhile;?>
                                </td>
                                <td><div><?php echo $rPO['invoice'];?></div></td>
                                <td><?php echo functions::datearr($rPO['po_date']);?></td>
                                <td><?php echo $db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID']));?></td>
                                <td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id']));?></td>
                                <td>
                                    <a id="costdetail<?php echo $rPO['po_id']?>" class="label label-info thickbox" title="View P.O. Details" data-rel="tooltip" onclick="showThis(this.id,'<?php echo $viewOnlyPage?>?po_id=<?php echo functions::encode($rPO['po_id']);?>','P.O. Details','1')">											
									<?php echo functions::formatMoney($amount);?>
                                    </a>
                                </td>
                                <td>
                                    <div align="center">
                                    <?php if($approved==0){?>
                                            <a id="detail<?php echo $rPO['po_id']?>" class="btn btn-mini btn-info thickbox" title="Manage P.O. item" data-rel="tooltip" onclick="showThis(this.id,'<?php echo $viewPage?>?po_id=<?php echo functions::encode($rPO['po_id']);?>','P.O. Details')"><i class="halflings-icon white plus-sign"></i></a>
                                            <a id="edit<?php echo $rPO['po_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify this P.O." data-rel="tooltip" onclick="showThis(this.id,'<?php echo $editPage?>?po_id=<?php echo functions::encode($rPO['po_id']);?>&refID=<?php echo functions::encode($rPO['ref_id'])?>','P.O. Details')"><i class="halflings-icon white pencil"></i></a>
                                    <?php }?>
                                            <a id="print<?php echo $rPO['po_id']?>" class="btn btn-mini btn-success thickbox" title="Print this P.O." data-rel="tooltip" onclick="showThis(this.id,'<?php echo $printPage?>?po_id=<?php echo functions::encode($rPO['po_id']);?>','P.O. Details','1')"><i class="halflings-icon white print"></i></a>
                                    <?php if( $db->getValue('po','count(*)',array('ref_id'=>$rPO['ref_id'])) > 1 ){?>
                                            <a id="print1<?php echo $rPO['po_id']?>" class="btn btn-mini btn-success thickbox" title="Print this P.O. as Group" data-rel="tooltip" onclick="showThis(this.id,'<?php echo $printPage1?>?ref_id=<?php echo functions::encode($rPO['ref_id']);?>','P.O. Details','1')"><i class="halflings-icon white list-alt"></i></a>
                                    <?php }?>
                                    <?php if($approved==0 && $amount==0){?>
                                            <a id="del<?php echo $rPO['po_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this P.O." data-rel="tooltip" href="po_list.php?po_idDel=<?php echo functions::encode($rPO['po_id']);?>"><i class="halflings-icon white trash"></i></a>
                                    <?php }?>
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
function delt(){
    if(confirm('Do you want to remove this P.O.?'))
        return true;
    else
        return false; 
}
</script>
<?php require_once('templ_down.php');?>