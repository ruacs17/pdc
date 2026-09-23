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
$im_id = (isset($_REQUEST['im_id']) && !empty($_REQUEST['im_id']) ) ? functions::decode($_REQUEST['im_id']) : 0;

$project_id = $db->getValue('inhouse_material','proj_id',array('im_id'=>$im_id));

if( isset($_POST['btnAdd']) ){

    $txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : '';
    $txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : '';
    $txBrand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? trim($_POST['txBrand']) : '';

    if( $txItem && $txUnit){
        if( $db->getValue('material_reference','count(*)',array('lower(item)'=>strtolower($txItem)))==0 ){
            $db->insert('material_reference',array('item'=>$txItem,'unit'=>$txUnit,'brand'=>$txBrand));
            functions::say('New Material Reference Added!');
        }
    }
    functions::sendTo($_SERVER['PHP_SELF']);
}

$editTrue=0;
$poidEdt='';
$item='';$quantity='';$cost='';$unit='';$discount='';$brand='';
if( isset($_REQUEST['poidEdt']) && !empty($_REQUEST['poidEdt']) ){
    $poidEdt = functions::decode($_REQUEST['poidEdt']);
    $editTrue = $db->getValue('inhouse_material_item','count(*)',array('imi_id'=>$poidEdt));
    $qvedt = $db->select('inhouse_material_item','*',array('imi_id'=>$poidEdt));
    $rvedt = $db->fetch_array($qvedt);
    $item = $rvedt['item'];
    $quantity = $rvedt['quantity'];
    $unit = $rvedt['unit'];
    $brand = $rvedt['brand'];
    $cost = $rvedt['cost'];
    $discount = $rvedt['discount'];
}
$qItem = $db->query('SELECT DISTINCT item FROM inhouse_material_item UNION SELECT DISTINCT item FROM po_item ORDER BY item');
$namesItem='';
while($rItem=$db->fetch_array($qItem)):
    $string = preg_replace("/'/",'"',$rItem['item']);
    $namesItem .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesItem .= '"--"';

$qUnit = $db->query('SELECT DISTINCT unit FROM inhouse_material_item UNION SELECT DISTINCT unit FROM po_item ORDER BY unit');
$namesUnit='';
while($rUnit=$db->fetch_array($qUnit)):
    $string = preg_replace("/'/",'"',$rUnit['unit']);
    $namesUnit .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesUnit .= '"--"';

$qBrand = $db->query('SELECT DISTINCT brand FROM inhouse_material_item UNION SELECT DISTINCT brand FROM po_item ORDER BY brand');
$namesBrand='';
while($rBrand=$db->fetch_array($qBrand)):
    $string = preg_replace("/'/",'"',$rBrand['brand']);
    $namesBrand .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesBrand .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Material Reference</title>
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
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Material Reference</h2>
        </div>
        <div class="box-content">
            <form method="post">
                <table width="100%" border="0" align="center" class="table table-bordered">
                    <tr bgcolor="#f8f8f8">
                        <th width="30%" scope="col"><div align="right">Item</div></th>
                        <td>
                          <div align="left">
                            <input type="text" style="width:500px;" class="span6 typeahead" name="txItem" id="txItem" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesItem;?>]' value="<?php echo $item;?>">
                            <span class="help-inline warning" id="msgtxItem" style="font-weight:bold;" name="msgtxItem"></span>
                          </div>
                        </td>
                    </tr>
                    <tr bgcolor="#f8f8f8">
                        <th width="10%" scope="col"><div align="right">Unit</div></th>
                        <td><div align="left"><input type="text" style="width:100px;" class="span6 typeahead" name="txUnit" id="txUnit" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesUnit;?>]' value="<?php echo $unit?>"></div></td>
                    </tr>
                    <tr bgcolor="#f8f8f8">
                        <th width="10%" scope="col"><div align="right">Brand</div></th>
                        <td><div align="left"><input type="text" style="width:500px;" name="txBrand" id="txBrand" value="<?php echo $brand;?>" class="span6 typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesBrand;?>]'></div></td>
                    </tr>
                    <tr bgcolor="#f7ebeb">
                        <th width="10%" scope="col">&nbsp;</th>
                        <td>
                            <div align="left">
                            <?php 
                            if($editTrue)
                                 echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary">';
                            else
                                 echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary">';
                            ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
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
    if(confirm('Do you want to remove this?'))
        return true;
    else
        return false; 
}

$(document).ready(function(){
    var res = false;
    $('#btnAdd,#btnSave').click(function(){
        $('#msgtxItem').html("");
        $('#msgtxQty').html("");
        $('#msgtxQtyDel').html("");

        if( $('#txItem').val()=="" ){
            $('#msgtxItem').html("Specify Item!");
            $('#txItem').focus();
            res=false;
        }
        else if( $('#txQty').val()=="" ){
            $('#msgtxQty').html("Required!");
            $('#txQty').focus();
            res=false;
        }
        else if( $('#txCost').val()=="" ){
            $('#msgtxCost').html("Required!");
            $('#txCost').focus();
            res=false;
        }
        else
            res=true;
        return res;
    });
});
function projSel(PiEwgD){
    if(PiEwgD)
        window.location="<?php echo $_SERVER['PHP_SELF']?>?proj_id="+PiEwgD
    else
        window.location="<?php echo $_SERVER['PHP_SELF']?>"
}
</script>
<!-- end: JavaScript-->
</body>
</html>