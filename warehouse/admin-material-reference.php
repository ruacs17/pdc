<?php require_once('templ_up.php');?>
<?php
$arr = array();
/*if( isset($_REQUEST['startrow']) ){
    if($_REQUEST['startrow'] == 0){
        functions::sendTo($_SERVER['PHP_SELF']);
        die();
    }
}*/
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$itemDel = (isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ) ? functions::decode($_REQUEST['itemDel']) : 0;
$arr = array();
$mon='';
$txProj = '';
$txPayee = '';
$txbMon = '';
$txbYear = ''; 
$item='';
$txItemSearch='';
if($itemDel){
    $txItemSearch = (isset($_REQUEST['txItemSearch']) && !empty($_REQUEST['txItemSearch']) ) ? functions::decode($_REQUEST['txItemSearch']) : 0;
    $itemName = $db->getValue('material_reference','lower(item)',array('mf_id'=>$itemDel));
    $itemUnit = $db->getValue('material_reference','lower(unit)',array('mf_id'=>$itemDel));
    $itemBrand = $db->getValue('material_reference','lower(brand)',array('mf_id'=>$itemDel));
    if( $db->getValue('inhouse_material_item','count(*)',array('lower(item)'=>$itemName,'lower(unit)'=>$itemUnit,'lower(brand)'=>$itemBrand))==0 && $db->getValue('po_item','count(*)',array('lower(item)'=>$itemName,'lower(unit)'=>$itemUnit,'lower(brand)'=>$itemBrand))==0){
        $db->delete('material_reference',array('mf_id'=>$itemDel));
    }
    if($txItemSearch && $startrow)
        functions::sendTo($_SERVER['PHP_SELF'].'?txItemSearch='.functions::encode($txItemSearch).'&startrow='.$startrow);
    elseif($txItemSearch)
        functions::sendTo($_SERVER['PHP_SELF'].'?txItemSearch='.functions::encode($txItemSearch));
    else
        functions::sendTo($_SERVER['PHP_SELF'].'?startrow='.$startrow);
}
if(count($arr)){
    $rowdisplay=1000;
    $startrow=0;
}
if( isset($_POST['btnViewAll']) ){
    functions::sendTo($_SERVER['PHP_SELF']);
}
else if( isset($_POST['btnSearch']) ){
    $txItemSearch = (isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : "";
    $q = 'SELECT * FROM material_reference WHERE item LIKE "%'.$db->clean($txItemSearch).'%" OR m_code LIKE "%'.$db->clean($txItemSearch).'%" OR classification LIKE "%'.$db->clean($txItemSearch).'%" OR mtype LIKE "%'.$db->clean($txItemSearch).'%" ORDER BY item LIMIT '.$startrow.', '.$rowdisplay;
    $num_recordQ = $db->query('SELECT * FROM material_reference WHERE item LIKE "%'.$db->clean($txItemSearch).'%" OR m_code LIKE "%'.$db->clean($txItemSearch).'%" OR classification LIKE "%'.$db->clean($txItemSearch).'%" OR mtype LIKE "%'.$db->clean($txItemSearch).'%"');
}
else if(isset($_REQUEST['txItemSearch']) && !empty($_REQUEST['txItemSearch']) ){
    $txItemSearch = (isset($_REQUEST['txItemSearch']) && !empty($_REQUEST['txItemSearch']) ) ? functions::decode(trim($_REQUEST['txItemSearch'])) : "";
    $q = 'SELECT * FROM material_reference WHERE item LIKE "%'.$db->clean($txItemSearch).'%" OR m_code LIKE "%'.$db->clean($txItemSearch).'%" OR classification LIKE "%'.$db->clean($txItemSearch).'%" OR mtype LIKE "%'.$db->clean($txItemSearch).'%" ORDER BY item LIMIT '.$startrow.', '.$rowdisplay;
    $num_recordQ = $db->query('SELECT * FROM material_reference WHERE item LIKE "%'.$db->clean($txItemSearch).'%" OR m_code LIKE "%'.$db->clean($txItemSearch).'%" OR classification LIKE "%'.$db->clean($txItemSearch).'%" OR mtype LIKE "%'.$db->clean($txItemSearch).'%"');
}
else{
    $q = $db->selectPrint('material_reference ','*',array(),'ORDER BY item LIMIT '.$startrow.', '.$rowdisplay);
    $num_recordQ = $db->query('SELECT * FROM material_reference');
}
    $qList = $db->query($q);
    $num_record = $db->num_rows($num_recordQ);
?>
<!-- body content: start here-->
<form method="post" action="?">
          <div class="row-fluid">
                <div class="box span12">
                      <div class="box-header" data-original-title>
                                <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>ITEM REFERENCE</h2>
                      </div>
                            <br>
                            <div align="right"><a id="adc" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'admin-material-reference-add.php?','Material Reference')">Add New Material Reference</a>&nbsp;</div>
                            <br><br>
                                <table class="table" border="0">
                                        <tr>
                                            <td width="20%">&nbsp;</td>
                                            <td width="35%">
                                                <div align="left">
                                                    <input type="text" style="width:500px; height:10px;" class="span6 typeahead" name="txItem" id="txItem" value="<?php echo $txItemSearch;?>">
                                                </div>
                                            </td>
                                            <td width="30%">
                                                <div align="left">
                                                    <input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary">
                                                    <input type="submit" name="btnViewAll" id="btnViewAll" value="View All" class="btn btn-primary">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr><td colspan="4"><hr width="100%"></td></tr>
                                </table>     
                                </form>
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th width="10%">Classification</th>
                                            <th width="10%">Type</th>
                                            <th width="12%">Code</th>
                                            <th width="27%">Material</th>
                                            <th width="15%">Unit</th>
                                            <th width="15%">Brand</th>
                                            <th width="11%"><div align="center">Option</div></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $totalAmount=0;
                                        while($rList = $db->fetch_array($qList)):
                                            $itemID = $rList['mf_id'];
                                    ?>
                                        <tr>
                                            <td><?php echo $rList['classification']?></td>
                                            <td><?php echo $rList['mtype']?></td>
                                            <td><?php echo $rList['m_code']?></td>
                                            <td><?php echo $rList['item']?></td>
                                            <td><?php echo $rList['unit']?></td>
                                            <td><?php echo $rList['brand']?></td>
                                            <td>
                                                <div align="center">
                                                <a id="edit<?php echo $itemID?>" class="btn btn-mini btn-warning thickbox" title="Modify this Material" data-rel="tooltip" onclick="showThis(this.id,'admin-material-reference-add.php?mf_id=<?php echo functions::encode($itemID);?>','Details')">
                                                    <i class="halflings-icon white pencil"></i>
                                                </a>
                                                <?php if( $db->getValue('inhouse_material_item','count(*)',array('lower(item)'=>strtolower($rList['item']),'lower(unit)'=>strtolower($rList['unit']),'lower(brand)'=>strtolower($rList['brand'])))==0 && $db->getValue('po_item','count(*)',array('lower(item)'=>strtolower($rList['item']),'lower(unit)'=>strtolower($rList['unit']),'lower(brand)'=>strtolower($rList['brand'])))==0){ ?>
                                                <a id="del<?php echo $itemID;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Material" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?itemDel=<?php echo functions::encode($itemID);?>&txItemSearch=<?php echo functions::encode($txItemSearch)?>&startrow=<?php echo $startrow?>"><i class="halflings-icon white trash"></i></a>
                                                <?php }?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile;?>
                                    </tbody>
                                </table>
                                <div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=$_SERVER['PHP_SELF'].'?txItemSearch='.functions::encode($txItemSearch),$search="");?></div>
                      </div>
                </div><!--/span-->
          </div><!--/row-->
</form>
<!-- body content: end here-->
<script>
    function delt(){
      if(confirm('Do you want to remove this Material Reference?'))
        return true;
      else
        return false; 
    }
</script>
<?php require_once('templ_down.php');?>