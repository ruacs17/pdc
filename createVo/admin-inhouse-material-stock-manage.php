<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$count=0;$editTrue=0;$total_quantity = 0;$imsl_id=0;$quantity=0;
$mf_id = (isset($_REQUEST['mf_id']) && !empty($_REQUEST['mf_id']) ) ? $db->clean(functions::decode($_REQUEST['mf_id'])) : 0;
$location = (isset($_REQUEST['loc']) && !empty($_REQUEST['loc']) ) ? $db->clean(functions::decode($_REQUEST['loc'])) : '';
$itemEdit = (isset($_REQUEST['itemEdt']) && !empty($_REQUEST['itemEdt']) ) ? $db->clean(functions::decode($_REQUEST['itemEdt'])) : 0;
$itemDel=(isset($_REQUEST['itemDlt']) && !empty($_REQUEST['itemDlt']) ) ? $db->clean(functions::decode($_REQUEST['itemDlt'])) : 0;
$qM = $db->select('material_reference','*',array('mf_id'=>$mf_id));
$rM = $db->fetch_array($qM);

if($itemDel){
    $db->delete('inhouse_material_storage',array('ims_id'=>$itemDel));
    functions::sendTo($_SERVER['PHP_SELF'].'?mf_id='.functions::encode($mf_id).'&loc='.functions::encode($location));
}

if( isset($_POST['btnAdd']) ){

    $txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? functions::moneyToDouble($_POST['txQty']) : 0;
    $txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
    $txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
    $txBdate = $txbYear.'-'.$txbMon.'-'.$txbDay;
    $location = ( isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? functions::decode($_POST['selLoc']) : '';

    if( $txQty && $txBdate && $location ){

        if( $db->getValue('material_reference','count(*)',array('mf_id'=>$mf_id)) ){
            $itemName = $db->getValue('material_reference','item',array('mf_id'=>$mf_id));
            $unit = $db->getValue('material_reference','unit',array('mf_id'=>$mf_id));
            $brand = $db->getValue('material_reference','brand',array('mf_id'=>$mf_id));
            if( $db->getValue('inhouse_material_storage','count(*)',array('item'=>$itemName,'unit'=>$unit,'brand'=>$brand,'ims_date'=>$txBdate,'location'=>$location)) ){
                $stockQty = $db->getValue('inhouse_material_storage','quantity',array('item'=>$itemName,'unit'=>$unit,'brand'=>$brand,'ims_date'=>$txBdate,'location'=>$location));
                $db->update('inhouse_material_storage',array('quantity'=>($stockQty + $txQty)),array('item'=>$itemName,'unit'=>$unit,'brand'=>$brand,'ims_date'=>$txBdate,'location'=>$location));
            }
            else
                $db->insert('inhouse_material_storage',array('item'=>$itemName,'unit'=>$unit,'brand'=>$brand,'ims_date'=>$txBdate,'quantity'=>$txQty,'location'=>$location));
            functions::say('Added to Inventory!');
        }
        functions::sendTo($_SERVER['PHP_SELF'].'?mf_id='.functions::encode($mf_id).'&loc='.functions::encode($location));
    }
}

if( isset($_POST['btnSave']) ){

    $txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? functions::moneyToDouble($_POST['txQty']) : 0;
    $txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
    $txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
    $txBdate = $txbYear.'-'.$txbMon.'-'.$txbDay;
    $location = ( isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? functions::decode($_POST['selLoc']) : '';

    $stockDate = $db->getValue('inhouse_material_storage','ims_date',array('ims_id'=>$itemEdit));
    $existingQuantity = $db->getValue('inhouse_material_storage','quantity',array('ims_date'=>$txBdate));

    if( $txQty && $txBdate && $itemEdit && $location ){
        if($stockDate != $txBdate){
            $txQty += $existingQuantity;
            $db->delete('inhouse_material_storage',array('ims_date'=>$txBdate));
        }
        $db->update('inhouse_material_storage',array('ims_date'=>$txBdate,'quantity'=>$txQty,'location'=>$location),array('ims_id'=>$itemEdit));
        functions::say('Inventory Updated!');
        functions::sendTo($_SERVER['PHP_SELF'].'?mf_id='.functions::encode($mf_id).'&loc='.functions::encode($location));
    }
}

$quantity=''; $mon=date('m');$day=date('d');$year=date('Y');
if($itemEdit){
    $editTrue = $db->getValue('inhouse_material_storage','count(*)',array('ims_id'=>$itemEdit));
    $quantity = $db->getValue('inhouse_material_storage','quantity',array('ims_id'=>$itemEdit));
    $location = $db->getValue('inhouse_material_storage','location',array('ims_id'=>$itemEdit));
    $imsl_id = $db->getValue('inhouse_material_storage_location','imsl_id',array('location'=>$location));
    $vdate = $db->getValue('inhouse_material_storage','ims_date',array('ims_id'=>$itemEdit));
    $xdate = explode('-',$vdate);
    if(count($xdate)==3){
        $mon = $xdate[1];
        $day = $xdate[2];
        $year = $xdate[0];
    }
}
if($quantity){
    $quantity = (functions::isfloat($quantity)) ? functions::formatMoney($quantity) : number_format($quantity);
}
$arrDate=array();
$arr_date = array();
$qAddDate = $db->select('inhouse_material_storage','DISTINCT ims_date',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'location'=>$location),'ORDER BY ims_date');
while($rAddDate = $db->fetch_array($qAddDate)):
    $arrDate[$rAddDate['ims_date']]='acquire';
endwhile;

$qDelDate = $db->select('inhouse_material im,inhouse_material_item imi','DISTINCT im_date',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand']),'AND im.im_id=imi.im_id ORDER BY im_date');
while($rDelDate = $db->fetch_array($qDelDate)):
    $arrDate[$rDelDate['im_date']]='sold';
endwhile;
ksort($arrDate);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Inventory Item</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/style.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
    <script src="../js/formatCurrency.js"></script>
    <script src="../js/inputInt.js"></script>
    <!-- end: CSS -->
    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <link id="ie-style" href="../css/ie.css" rel="stylesheet">
    <![endif]-->
    <!--[if IE 9]>
    <link id="ie9style" href="../css/ie9.css" rel="stylesheet">
    <![endif]-->
    <!-- start: Favicon -->
    <link rel="shortcut icon" href="../img/favicon.png">
    <!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Inventory Item</h2>
        </div>
        <div class="box-content">
            <form method="post">
                <table align="center" class="table table-bordered" border="0">
                    <tr bgcolor="#ece1e1">
                        <td width="25%">Item</td>
                        <td width="15%"><div align="center">Brand</div></td>
                        <td width="5%"><div align="center">Unit</div></td>
                        <td width="8%"><div align="center">Quantity</div></td>
                        <td width="20%"><div align="center">Date</div></td>
                        <td width="15%" scope="col"><div align="center">Storage Location</div></td>
                        <td width="10%">&nbsp;</td>
                    </tr>
                    <tr bgcolor="#f8f8f8">
                        <td><strong><?php echo $rM['item']?></strong></td>
                        <td><div align="center"><strong><?php echo $rM['brand']?></strong></div></td>
                        <td><div align="center"><strong><?php echo $rM['unit'];?></strong></div></td>
                        <td>
                            <div align="center"><input type="text" style="width:80px;" name="txQty" id="txQty" value="<?php echo $quantity;?>" onkeypress="return checkinput(this, event);" onkeyup="FormatCurrency(this);"></div>
                            <span class="help-inline warning" id="msgtxQty" style="font-weight:bold;" name="msgtxQty"></span>
                        </td>
                        <td>
                            <div align="center">
                                <select name="bdMon" id="bdMon" style="width:80px;">
                                    <option value="">Month</option>
                                    <option value="01" <?php if($mon=='01')echo 'selected="selected"';?>>Jan</option>
                                    <option value="02" <?php if($mon=='02')echo 'selected="selected"';?>>Feb</option>
                                    <option value="03" <?php if($mon=='03')echo 'selected="selected"';?>>Mar</option>
                                    <option value="04" <?php if($mon=='04')echo 'selected="selected"';?>>Apr</option>
                                    <option value="05" <?php if($mon=='05')echo 'selected="selected"';?>>May</option>
                                    <option value="06" <?php if($mon=='06')echo 'selected="selected"';?>>Jun</option>
                                    <option value="07" <?php if($mon=='07')echo 'selected="selected"';?>>Jul</option>
                                    <option value="08" <?php if($mon=='08')echo 'selected="selected"';?>>Aug</option>
                                    <option value="09" <?php if($mon=='09')echo 'selected="selected"';?>>Sep</option>
                                    <option value="10" <?php if($mon=='10')echo 'selected="selected"';?>>Oct</option>
                                    <option value="11" <?php if($mon=='11')echo 'selected="selected"';?>>Nov</option>
                                    <option value="12" <?php if($mon=='12')echo 'selected="selected"';?>>Dec</option>
                                </select>
                                <select name="bdDay" id="bdDay" style="width:60px;">
                                    <option value="">Day</option>
                                    <?php for($i=1;$i<=31;$i++):?>
                                    <option value="<?php echo ($i < 10) ? '0'.$i : $i;?>" <?php if($i==$day)echo 'selected="selected"';?>><?php echo $i;?></option>
                                    <?php endfor;?>
                                </select>
                                <select name="bdYear" id="bdYear" style="width:70px;">
                                    <option value="">Year</option>
                                    <?php for($y=(date('Y') + 2);$y>=2012;$y--):?>
                                    <option value="<?php echo $y;?>" <?php if($y==$year)echo 'selected="selected"';?>><?php echo $y;?></option>
                                    <?php endfor;?>
                                </select>
                                <span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
                            </div>
                        </td>
                        <td>
                            <div align="center"><input type="hidden" name="selLoc" id="selLoc" value="<?php echo functions::encode($location)?>"><strong><?php echo $location?></strong></div>
                            <span class="help-inline warning" id="msgLoc" style="font-weight:bold;" name="msgLoc"></span>
                        </td>
                        <td>
                            <div align="center">
                                <?php 
                                if($editTrue){
                                    echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-mini btn-primary">&nbsp;';
                                    echo '<a href="?mf_id='.functions::encode($mf_id).'&loc='.functions::encode($location).'" class="btn btn-mini">Cancel</a>';
                                }
                                else
                                    echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary">';
                                ?>
                             </div>
                        </td>
                    </tr>
                </table>
            </form>
            <table width="95%" border="0" align="center" id="abcd" name="abcd" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;" >
                <thead>
                    <tr>
                        <th width="45%" scope="col"><div align="left">Date</div></th>
                        <th width="10%" scope="col">Acquired</th>
                        <th width="10%" scope="col">Sold</th>
                        <th width="10%" scope="col"><div align="center">On-hand</div></th>
                        <th width="10%" scope="col">&nbsp;</th>
                    </tr>
                </thead>   
                <tbody>
                <?php
                $count=0;$onhand=0;$onhand_disp=0;
                foreach($arrDate as $date => $event):
                    $sold=0;$acquire=0;$quantityPerDate=0;$qty=0;$count++;$ims_id = $count++;
                ?>
                    <tr>
                        <td><?php echo functions::datearr($date);?></td>
                        <?php
                        $ims_id = $db->getValue('inhouse_material_storage','ims_id',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'ims_date'=>$date,'location'=>$location));
                        $bgColor='';
                        $acquire_disp='';$sold_disp='';$onhand_disp='';
                        $acquire = $db->getValue('inhouse_material_storage','quantity',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'ims_date'=>$date,'location'=>$location));

                        $onhand += $acquire;
                        $sold = $db->getValue('inhouse_material im, inhouse_material_item imi','sum(quantity)',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'im_date'=>$date,'location'=>$location),'AND im.im_id=imi.im_id');
                        $onhand -= $sold;
                        if($acquire)
                            $acquire_disp = (functions::isfloat($acquire)) ? functions::formatMoney($acquire) : number_format($acquire);
                        if($sold)
                            $sold_disp = (functions::isfloat($sold)) ? functions::formatMoney($sold) : number_format($sold);
                        if($onhand)
                            $onhand_disp = (functions::isfloat($onhand)) ? functions::formatMoney($onhand) : number_format($onhand);

                        if($ims_id && ($itemEdit == $ims_id) )
                            $bgColor='bgcolor="#f5ae00"';
                        ?>
                        <td <?php echo $bgColor;?>><div align="center"><?php echo $acquire_disp;?></div></td>
                        <td><div align="center"><?php echo $sold_disp;?></div></td>
                        <td><div align="center"><?php echo $onhand_disp?></div></td>
                        <td>
                            <?php if($ims_id){?>
                            <div align="center">
                                <a id="update<?php echo $count?>" class="btn btn-mini btn-warning" title="Manage this Item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?mf_id=<?php echo functions::encode($mf_id);?>&itemEdt=<?php echo functions::encode($ims_id);?>"><i class="halflings-icon white pencil"></i></a>
                                <a id="dlt<?php echo $count?>" class="btn btn-mini btn-danger" title="Remove this inventory" data-rel="tooltip" onClick="return delt()" href="<?php echo $_SERVER['PHP_SELF']?>?mf_id=<?php echo functions::encode($mf_id);?>&itemDlt=<?php echo functions::encode($ims_id);?>&loc=<?php echo functions::encode($location);?>"><i class="halflings-icon white trash"></i></a>
                            </div>
                            <?php }?>
                        </td>
                    </tr>
                <?php endforeach;?>
                <tbody>
                    <tr>
                        <td><div align="right"><strong>Total Quantity</strong></div></td>
                        <td></td>
                        <td></td>
                        <td><div align="center"><strong><?php echo $onhand_disp?></strong></div></td>
                        <td></td>
                    </tr>
            </table>
        </div>
    </div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="../js/jquery.ui.touch-punch.js"></script>
<script src="../js/modernizr.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.cookie.js"></script>
<script src='../js/fullcalendar.min.js'></script>
<script src='../js/jquery.dataTables.min.js'></script>
<script src="../js/excanvas.js"></script>
<script src="../js/jquery.flot.js"></script>
<script src="../js/jquery.flot.pie.js"></script>
<script src="../js/jquery.flot.stack.js"></script>
<script src="../js/jquery.flot.resize.min.js"></script>
<script src="../js/jquery.chosen.min.js"></script>
<script src="../js/jquery.uniform.min.js"></script>
<script src="../js/jquery.cleditor.min.js"></script>
<script src="../js/jquery.noty.js"></script>
<script src="../js/jquery.elfinder.min.js"></script>
<script src="../js/jquery.raty.min.js"></script>
<script src="../js/jquery.iphone.toggle.js"></script>
<script src="../js/jquery.uploadify-3.1.min.js"></script>
<script src="../js/jquery.gritter.min.js"></script>
<script src="../js/jquery.imagesloaded.js"></script>
<script src="../js/jquery.masonry.min.js"></script>
<script src="../js/jquery.knob.modified.js"></script>
<script src="../js/jquery.sparkline.min.js"></script>
<script src="../js/counter.js"></script>
<script src="../js/retina.js"></script>
<script src="../js/custom.js"></script>
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function delt(){
    if(confirm('Do you want to remove this Inventory?'))
        return true;
    else
        return false; 
}
$(document).ready(function(){
    var res = false;
    $('#btnAdd,#btnSave').click(function(){
        $('#msgtxQty').html("");
        $('#msgBdate').html("");
        $('#msgLoc').html("");

        if( $('#txQty').val()=="" ){
            $('#msgtxQty').html("Required!");
            $('#txQty').focus();
            res=false;
        }
        else if( $('#bdMon').val()=="" || $('#bdDay').val()=="" || $('#bdYear').val()==""){
            $('#msgBdate').html("Date Required!");
            res=false;
        }
        else if( $('#selLoc').val()=="" ){
            $('#msgLoc').html("Required!");
            $('#selLoc').focus();
            res=false;
        }
        else
            res=true;

        return res;
    });
});
</script>
<!-- end: JavaScript-->
</body>
</html>