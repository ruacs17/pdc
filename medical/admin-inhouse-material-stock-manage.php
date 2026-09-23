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
$quantity=''; $mon=date('m');$day=date('d');$year=date('Y');

if($quantity){
    $quantity = (functions::isfloat($quantity)) ? functions::formatMoney($quantity) : number_format($quantity);
}
$arrDate=array();
$arr_date = array();
$qAddDate = $db->select('inhouse_material_storage','DISTINCT ims_date',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'location'=>$location,'item_type'=>'medical'),'ORDER BY ims_date');
while($rAddDate = $db->fetch_array($qAddDate)):
    $arrDate[$rAddDate['ims_date']]='acquire';
endwhile;

$qDelDate = $db->select('medicine_distribution md,medicine_distribution_detail mdd','md_date,proj_id',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand']),'AND md.md_id=mdd.md_id GROUP BY md_date,proj_id ORDER BY md_date');
#echo $db->last_query;
while($rDelDate = $db->fetch_array($qDelDate)):
    $arrDate[$rDelDate['md_date']]='sold';
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
            <div align="left">
                <div>Item: <strong><?php echo $rM['item'] ?></strong></div>
                <div>Unit: <strong><?php echo ($rM['unit']) ? $rM['unit'] : '---'; ?></strong></div>
                <div>Brand: <strong><?php echo ($rM['brand']) ? $rM['brand'] : '---'; ?></strong></div>
                <div>Storage Location: <strong><?php echo ($location) ? $location : '---'; ?></strong></div>
            </div>
            <table width="99%" border="1" align="center" id="abcd" name="abcd" cellpadding="0" cellspacing="0" class="tablea table-hover" style="font-size:12px;" >
                <thead>
                    <tr>
                        <th width="10%" scope="col" height="40"><div align="left">Date</div></th>
                        <th width="10%" scope="col">Acquired</th>
                        <th width="40%" scope="col">Distributed</th>
                        <th width="10%" scope="col"><div align="center">On-hand</div></th>
                    </tr>
                </thead>   
                <tbody>
                <?php
                $count=0;$onhand=0;$onhand_disp=0;
                foreach($arrDate as $date => $event):
                    $sold=0;$acquire=0;$quantityPerDate=0;$qty=0;$count++;$ims_id = $count++;
                ?>
                    <tr>
                        <td height="30"><?php echo functions::datearr($date);?></td>
                        <?php
                        $ims_id = $db->getValue('inhouse_material_storage','ims_id',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'ims_date'=>$date,'location'=>$location,'item_type'=>'medical'));
                        $bgColor='';
                        $acquire_disp='';$sold_disp='';$onhand_disp='';
                        $acquire = $db->getValue('inhouse_material_storage','sum(quantity)',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'ims_date'=>$date,'location'=>$location,'item_type'=>'medical'));

                        $onhand += $acquire;
                        $sold = $db->getValue('medicine_distribution md,medicine_distribution_detail mdd','sum(qty)',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'md_date'=>$date,'location'=>$location),'AND md.md_id=mdd.md_id');
                        $onhand -= is_numeric($sold) ? $sold : 0;
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
                        <td>
                            <div align="center">
                                <table width="99%">
                                <?php
                                if($sold){
                                    echo '
                                    <tr>
                                        <td height="30" width="79%">Project</td>
                                        <td><div align="center">Quantity</div></td>
                                    </tr>
                                    ';
                                    $qDist = $db->select('project p, medicine_distribution md,medicine_distribution_detail mdd','proj_name,sum(qty) as qy',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'md_date'=>$date),'AND p.proj_id=md.proj_id AND md.md_id=mdd.md_id GROUP BY proj_name');
                                    #echo $db->last_query;
                                    while($rDist = $db->fetch_array($qDist)):
                                        echo '<tr>
                                                <td height="30">'.$rDist['proj_name'].'</td>
                                                <td><div align="center">'.$rDist['qy'].'</div></td>
                                            </tr>';
                                    endwhile;
                                }
                                #echo $sold_disp;
                                ?>
                                </table>
                            </div>
                        </td>
                        <td><div align="center"><?php echo $onhand?></div></td>
                    </tr>
                <?php endforeach;?>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td><div align="right"><strong>Total Onhand Quantity</strong></div></td>
                        <td><div align="center"><strong><?php echo $onhand?></strong></div></td>
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