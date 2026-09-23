<?php require_once('templ_up.php');?>
<?php
$arr = array();
$item='';
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $db->clean($_REQUEST['startrow']) : 0;
$rowdisplay=40;
$mf_id='';
$mon='';$txProj = '';$txPayee = '';$txbMon = '';$txbYear = '';$num_record=0;
$loc_id='';
if( isset($_POST['btnViewAll']) ){
    unset($_SESSION['stock_item'],$_SESSION['stock_location']);
    functions::sendTo($_SERVER['PHP_SELF']);
    die();
}
if( isset($_POST['btnSearch']) ){
    $_SESSION['stock_location'] = (isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? trim($_POST['selLoc']) : "";
    $_SESSION['stock_item'] = (isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : "";
    functions::sendTo($_SERVER['PHP_SELF']);
    die();
}
$loc_id = (isset($_SESSION['stock_location']) ) ? trim($_SESSION['stock_location']) : "";
$item = (isset($_SESSION['stock_item']) ) ? trim($_SESSION['stock_item']) : "";
if( $item || $loc_id ){
    $_SESSION['stock_item']=$item;
    $_SESSION['stock_location']=$loc_id;
    $q = 'SELECT item,unit,brand FROM inhouse_material_storage WHERE item LIKE "%'.$db->clean($item).'%" AND item_type="medical" GROUP BY item,unit,brand LIMIT '.$startrow.', '.$rowdisplay;
    $_SESSION['im_query']=$q;
    $qCount = $db->query('SELECT item,unit,brand FROM inhouse_material_storage WHERE item LIKE "%'.$db->clean($item).'%" AND item_type="medical" GROUP BY item,unit,brand');
    $num_record = $db->num_rows();
}
else{
    $q = 'SELECT item,unit,brand FROM inhouse_material_storage WHERE item_type="medical" GROUP BY item,unit,brand LIMIT '.$startrow.', '.$rowdisplay;
    $_SESSION['im_query']=$q;
    $qCount = $db->query('SELECT item,unit,brand FROM inhouse_material_storage WHERE item_type="medical" GROUP BY item,unit,brand');
    $num_record = $db->num_rows();
}

$arrLocation = array();
if($loc_id)
    $qLoc = $db->select('inhouse_material_storage_location','location',array('imsl_id'=>$loc_id));
else
    $qLoc = $db->select('inhouse_material_storage_location','location',array());
while($rLoc = $db->fetch_array($qLoc)):
$arrLocation[] = $rLoc['location'];
endwhile;
$qList = $db->query($q);
?>
            <!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>MEDICAL STOCKS PER WAREHOUSE</h2>
        </div>
        <div class="box-content">
            <table width="200" cellspacing="0" cellpadding="0" border='0' align="right">
                <tr>
                    <td width="10" height='30'><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
                    <td width="90"> Low Stock Level</td>
                </tr>
            </table><br><br>
            <div align="right"><a id="itemPrint" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'admin-inhouse-material-stock-print.php?','Warehouse Stock Item Print','1')"><i class="halflings-icon white print"></i></a></div><br>
            <form method="post" action="?">
                <table border="0">
                    <tr>
                        <td style="padding: 10px 10px 5px 0px"><div align="left"><input type="text" style="width:450px; height:10px;" class="span6 typeahead" name="txItem" id="txItem" value="<?php echo $item;?>"></div></td>
                        <td style="padding: 10px 10px 5px 0px">
                            <div align="left">
                                <select name="selLoc" id="selLoc" style="width:200px;">
                                    <option value="">--All Location--</option>
                                    <?php
                                    $qLocation = $db->select('inhouse_material_storage_location','*',array());
                                    while($rLoc = $db->fetch_array($qLocation)):
                                    ?>
                                    <option value="<?php echo $rLoc['imsl_id']?>" <?php if($loc_id==$rLoc['imsl_id'])echo 'selected="selected"';?>><?php echo ucwords(strtolower($rLoc['location']));?></option>
                                    <?php endwhile;?>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div align="center">
                                <input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary">
                                <input type="submit" name="btnViewAll" id="btnViewAll" value="View All" class="btn btn-primary">
                            </div>
                        </td>
                    </tr>
                    <tr><td colspan="3"><hr width="100%"></td></tr>
                </table>
            </form>
            <table class="table table-bordered table-hover" style="font-size:14px;">
                <thead>
                    <tr>
                        <th width="35%">Item</th>
                        <th width="8%">Unit</th>
                        <th width="10%">Brand</th>
                        <?php foreach($arrLocation as $loc):?>
                        <th width="10%"><div align="center"><?php echo $loc?></div></th>
                        <?php endforeach;?>
                        <th width="10%"><div align="center">Total On-Stock</div></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $totalAmount=0;$count=0;
                while($rList = $db->fetch_array($qList)):
                    $count++;
                    $mf_id = $db->getValue('material_reference','mf_id',array('item'=>$rList['item'],'unit'=>$rList['unit'],'brand'=>$rList['brand']));
                    $least_required = $db->getValue('material_reference','least_required',array('mf_id'=>$mf_id));
                ?>
                    <tr>
                        <td><?php echo $rList['item']?></td>
                        <td><?php echo $rList['unit']?></td>
                        <td><?php echo $rList['brand']?></td>
                        <?php
                        $available=0; $totalAvailable=0;
                        foreach($arrLocation as $loc):
                            $consumed = $db->getValue('medicine_distribution_detail','sum(qty)',array('item'=>$rList['item'],'unit'=>$rList['unit'],'brand'=>$rList['brand'],'location'=>$loc));
                            $qty = $db->getValue('inhouse_material_storage','sum(quantity)',array('item'=>$rList['item'],'unit'=>$rList['unit'],'brand'=>$rList['brand'],'location'=>$loc));
                            #echo $db->last_query.'<br>';
                            $available = $qty - $consumed;
                            $totalAvailable += $available;
                            $available_disp=0; $totalAvailable_disp=0;
                            if($available)
                                $available_disp = (functions::isfloat($available)) ? functions::formatMoney($available) : number_format($available);
                            if($totalAvailable)
                                $totalAvailable_disp = (functions::isfloat($totalAvailable)) ? functions::formatMoney($totalAvailable) : number_format($totalAvailable);

                            $bgColor =  ($least_required > $available) ?  'bgcolor="#fcb77b"' : '';
                        ?>
                        <td <?php echo $bgColor?>><div align="center"><a id="s<?php echo $count++?>" class="thickbox" style="cursor:pointer;" title="Manage this Item" data-rel="tooltip" onclick="showThis(this.id,'admin-inhouse-material-stock-manage.php?mf_id=<?php echo functions::encode($mf_id);?>&loc=<?php echo functions::encode($loc)?>','Details')"><?php echo $available_disp?></a></div></td>
                        <?php endforeach;?>
                        <td><div align="center"><?php echo $totalAvailable_disp;?></div></td>
                    </tr>
                <?php endwhile;
                if($count==0){?>
                    <tr>
                        <td colspan="<?php echo count($arrLocation) + 5?>"><div align="center">--Nothing to Report--</div></td>
                    </tr>
                <?php }?>
                </tbody>
            </table>
            <div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=$_SERVER['PHP_SELF'].'?txItem='.$item.'&selLoc='.$loc_id,$search="txItem");?></div>
        </div>
    </div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>