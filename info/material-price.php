<?php require_once('templ_up.php');?>
<?php
$arr = array();
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $db->clean($_REQUEST['startrow']) : 0;
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
        if(file_exists('../img_material/'.$itemDel.'.jpg'))
            unlink('../img_material/'.$itemDel.'.jpg');
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
$arrBgColor=array();
$qCol = $db->query('SELECT type_desc,remarks FROM material_type WHERE type_name="category" AND remarks <> "" GROUP BY type_desc,remarks ORDER BY type_desc');
while($rCol = $db->fetch_array($qCol)):
    $arrBgColor[$rCol['type_desc']]=$rCol['remarks'];
endwhile;

$selSearchBy = isset($_REQUEST['selSearchBy']) ? trim($_REQUEST['selSearchBy']) : "";
$selCategory = isset($_REQUEST['selCategory']) ? trim($_REQUEST['selCategory']) : "";
$selClassification = isset($_REQUEST['selClassification']) ? trim($_REQUEST['selClassification']) : "";
$selType = isset($_REQUEST['selType']) ? trim($_REQUEST['selType']) : "";
#$txItem = isset($_POST['txItem']) ? trim($_POST['txItem']) : "";
$txItemSearch = (isset($_REQUEST['txItem']) && !empty($_REQUEST['txItem']) ) ? trim($_REQUEST['txItem']) : "";

$qList = $db->select('material_reference','*',array(),'ORDER BY item IS NULL,cast(item as unsigned), item LIMIT '.$startrow.', '.$rowdisplay);
$num_record = $db->getValue('material_reference','count(*)',array());
if( isset($_POST['btnViewAll']) ){
    functions::sendTo($_SERVER['PHP_SELF']);
}
#else if( isset($_POST['btnSearch']) ){
if( $selSearchBy || $selCategory || $selClassification || $selType || $txItemSearch ){

    $num_record = $db->getValue('material_reference','count(*)',array(),'WHERE (item LIKE "%'.$db->clean($txItemSearch).'%" OR m_code LIKE "%'.$db->clean($txItemSearch).'%" OR classification LIKE "%'.$db->clean($txItemSearch).'%" OR mtype LIKE "%'.$db->clean($txItemSearch).'%")');
    if( $selSearchBy=='category' ){
        if($selCategory){
            $qList = $db->select('material_reference','*',array('category'=>$selCategory),'AND (item LIKE "%'.$db->clean($txItemSearch).'%") ORDER BY category IS NULL, category,item LIMIT '.$startrow.', '.$rowdisplay);
            $num_record = $db->getValue('material_reference','count(*)',array('category'=>$selCategory),'AND (item LIKE "%'.$db->clean($txItemSearch).'%")');
        }
        else{
            $qList = $db->select('material_reference','*',array(),'WHERE (item LIKE "%'.$db->clean($txItemSearch).'%") ORDER BY category IS NULL, category,item LIMIT '.$startrow.', '.$rowdisplay);
        }
    }
    else if( $selSearchBy=='classification' ){

        if( $selClassification ){
            $qList = $db->select('material_reference','*',array('classification'=>$selClassification),'AND (item LIKE "%'.$db->clean($txItemSearch).'%") ORDER BY classification IS NULL, classification LIMIT '.$startrow.', '.$rowdisplay);
            $num_record = $db->getValue('material_reference','count(*)',array('classification'=>$selClassification),'AND (item LIKE "%'.$db->clean($txItemSearch).'%") ');
        }
        else{
            $qList = $db->select('material_reference','*',array(),'WHERE (item LIKE "%'.$db->clean($txItemSearch).'%") ORDER BY classification IS NULL, classification,item LIMIT '.$startrow.', '.$rowdisplay);
        }
    }
    else if( $selSearchBy=='type' ){
        if( $selType ){
            $qList = $db->select('material_reference','*',array('mtype'=>$selType),'AND (item LIKE "%'.$db->clean($txItemSearch).'%") ORDER BY mtype IS NULL, mtype,item LIMIT '.$startrow.', '.$rowdisplay);
            
            $num_record = $db->getValue('material_reference','count(*)',array('mtype'=>$selType),'WHERE (item LIKE "%'.$db->clean($txItemSearch).'%") ');
        }
        else{
            $qList = $db->select('material_reference','*',array(),'WHERE (item LIKE "%'.$db->clean($txItemSearch).'%") ORDER BY mtype IS NULL, mtype,item LIMIT '.$startrow.', '.$rowdisplay);
        }
    }
    else{
        $qList = $db->query('SELECT * FROM material_reference WHERE item LIKE "%'.$db->clean($txItemSearch).'%" OR m_code LIKE "%'.$db->clean($txItemSearch).'%" OR classification LIKE "%'.$db->clean($txItemSearch).'%" OR mtype LIKE "%'.$db->clean($txItemSearch).'%" OR category LIKE "%'.$db->clean($txItemSearch).'%" ORDER BY item IS NULL,cast(item as unsigned), item LIMIT '.$startrow.', '.$rowdisplay);
    }
}
?>
<!-- body content: start here-->
<div class="row-fluid">
    <form method="post">
        <div class="box span12">
            <div class="box-header" data-original-title>
                <h2><i class="halflings-icon white edit"></i><span class="break"></span>Price History</h2>
            </div>
            <div class="box-content">
                <form method="post" action="?">
                    <table class="table" border="0">
                        <tr>
                            <td width="2%">&nbsp;</td>
                            <td width="45%">
                                <div align="left">
                                    <select name="selSearchBy" id="selSearchBy" style="width:150px;">
                                        <option value="" <?php if($selSearchBy=='')echo 'selected="selected"';?>>--Any--</option>
                                        <option value="category" <?php if($selSearchBy=='category')echo 'selected="selected"';?>>Category</option>
                                        <option value="classification" <?php if($selSearchBy=='classification')echo 'selected="selected"';?>>Classification</option>
                                        <option value="type" <?php if($selSearchBy=='type')echo 'selected="selected"';?>>Type</option>
                                        <option value="item" <?php if($selSearchBy=='item')echo 'selected="selected"';?>>Item</option>
                                    </select>
                                    <select name="selCategory" id="selCategory" style="width:250px;">
                                        <option value="" <?php if($selCategory=='')echo 'selected="selected"';?>>--All Category--</option>
                                        <?php
                                        $qCat = $db->query('SELECT DISTINCT type_desc as itm FROM material_type WHERE type_name="category" ORDER BY type_desc');
                                        while($rCat = $db->fetch_array($qCat)):
                                        ?>
                                        <option value="<?php echo $rCat['itm']?>" <?php if($selCategory==$rCat['itm'])echo 'selected="selected"';?>><?php echo $rCat['itm']?></option>
                                        <?php endwhile;?>
                                    </select>
                                    <select name="selClassification" id="selClassification" style="width:200px;">
                                        <option value="">--All Classification--</option>
                                        <?php
                                        $qCls = $db->query('SELECT DISTINCT type_desc as itm FROM material_type WHERE type_name="classification" ORDER BY type_desc');
                                        while($rCls = $db->fetch_array($qCls)):
                                        ?>
                                        <option value="<?php echo $rCls['itm']?>" <?php if($selClassification==$rCls['itm'])echo 'selected="selected"';?>><?php echo $rCls['itm']?></option>
                                        <?php endwhile;?>
                                    </select>
                                    <select name="selType" id="selType" style="width:200px;">
                                        <option value="" <?php if($selType=='')echo 'selected="selected"';?>>--All Type--</option>
                                        <?php
                                        $qType = $db->query('SELECT DISTINCT type_desc as itm FROM material_type WHERE type_name="type" ORDER BY type_desc');
                                        while($rType = $db->fetch_array($qType)):
                                        ?>
                                        <option value="<?php echo $rType['itm']?>" <?php if($selType==$rType['itm'])echo 'selected="selected"';?>><?php echo $rType['itm']?></option>
                                        <?php endwhile;?>
                                    </select>
                                    <input type="text" style="width:300px; height:10px;" class="span6 typeahead" name="txItem" id="txItem" value="<?php echo $txItemSearch;?>">
                                </div>
                            </td>
                            <td width="10%">
                                <div align="left">
                                    <input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-small btn-primary">
                                    <input type="submit" name="btnViewAll" id="btnViewAll" value="View All" class="btn btn-small btn-primary">
                                </div>
                            </td>
                        </tr>
                        <tr><td colspan="4"><hr width="100%"></td></tr>
                    </table>
                </form>
                <table class="table table-bordered table-hover" style="font-size:12px;">
                    <thead>
                        <tr style="background-color:#CCC;">
                            <th width="9%">Code</th>
                            <th width="10%">Category</th>
                            <th width="10%">Classification</th>
                            <th width="10%">Type</th>
                            <th width="20%">Item</th>
                            <th width="10%">Size</th>
                            <th width="5%">Unit</th>
                            <th width="10%">Brand</th>
                            <th width="8%"><div align="center">Option</div></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $totalAmount=0;$countList=0;
                    while($rList = $db->fetch_array($qList)):
                        $itemID = $rList['mf_id']; $countList++;
                    ?>
                        <tr <?php if( isset($arrBgColor[$rList['category']]) )echo 'bgcolor="'.$arrBgColor[$rList['category']].'"';?>>
                            <td><?php echo $rList['m_code']?></td>
                            <td><?php echo $rList['category']?></td>
                            <td><?php echo $rList['classification']?></td>
                            <td><?php echo $rList['mtype']?></td>
                            <td><?php echo $rList['item']?></td>
                            <td><?php echo $rList['msize']?></td>
                            <td><?php echo $rList['unit']?></td>
                            <td><?php echo $rList['brand']?></td>
                            <td style="padding-left:15px;">
                                <div align="left">
                                    <a id="edit<?php echo $itemID?>" class="btn btn-mini btn-info thickbox" title="View this Material" data-rel="tooltip" onclick="showThis(this.id,'admin-material-reference-detail.php?mf_id=<?php echo functions::encode($itemID);?>','Details')"><i class="halflings-icon white search"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile;
                    if($countList==0){?>
                        <tr>
                            <td colspan="9"><div align="center">--Nothing to Report--</div></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=$_SERVER['PHP_SELF'].'?txItem='.$txItemSearch.'&selSearchBy='.$selSearchBy.'&selCategory='.$selCategory.'&selClassification='.$selClassification.'&selType='.$selType,$search="");?></div>

            </div>
        </div><!--/span-->
    </form>
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script>
$(document).ready(function(){
    var searchBy='';
    $('#selCategory').hide();
    $('#selClassification').hide();
    $('#selType').hide();
    searchBy = $('#selSearchBy').val();

    if( searchBy =='category' )
        $('#selCategory').show();
    else if( searchBy =='classification' )
        $('#selClassification').show();
    else if( searchBy =='type' )
        $('#selType').show();

    $('#selSearchBy').change(function(){
        searchBy = $('#selSearchBy').val();
        $('#selCategory').hide();
        $('#selClassification').hide();
        $('#selType').hide();

        if( searchBy =='category' )
            $('#selCategory').show();
        else if( searchBy =='classification' )
            $('#selClassification').show();
        else if( searchBy =='type' )
            $('#selType').show();
    });

});
</script>