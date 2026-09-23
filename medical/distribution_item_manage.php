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
$md_id = (isset($_REQUEST['mdid']) && !empty($_REQUEST['mdid']) ) ? functions::decode($_REQUEST['mdid']) : 0;
$searchedItem = (isset($_REQUEST['srcItm']) && !empty($_REQUEST['srcItm']) ) ? functions::decode($_REQUEST['srcItm']) : '';
$searchedBrnd = (isset($_REQUEST['srcBrnd']) && !empty($_REQUEST['srcBrnd']) ) ? functions::decode($_REQUEST['srcBrnd']) : '';
$searchedUnit = (isset($_REQUEST['srcUnit']) && !empty($_REQUEST['srcUnit']) ) ? functions::decode($_REQUEST['srcUnit']) : '';
$searchedLoc = (isset($_REQUEST['srcLoc']) && !empty($_REQUEST['srcLoc']) ) ? functions::decode($_REQUEST['srcLoc']) : '';
$available = (isset($_REQUEST['srcItmQtyAvlbl']) && !empty($_REQUEST['srcItmQtyAvlbl']) ) ? functions::decode($_REQUEST['srcItmQtyAvlbl']) : 0;
$project_id = $db->getValue('medicine_distribution','proj_id',array('md_id'=>$md_id));

if( isset($_POST['btnAdd']) ){ 
    $txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? functions::decode($_POST['txItem']) : '';
    $txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? $_POST['txQty'] : 0;
    $txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? $_POST['txUnit'] : '';
    $txBrand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? $_POST['txBrand'] : '';
    $txLoc = ( isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? functions::decode($_POST['selLoc']) : '';

    if( $txQty && $txItem && $txLoc){
        $db->insert('medicine_distribution_detail',array('md_id'=>$md_id,'item'=>$txItem,'qty'=>$txQty,'unit'=>$txUnit,'brand'=>$txBrand,'location'=>$txLoc));
    #echo $db->last_query;
    }
    functions::sendTo($_SERVER['PHP_SELF'].'?mdid='.functions::encode($md_id));
}

if( isset($_POST['btnSave']) ){
    $txpoidEdt = ( isset($_POST['txpoidEdt']) && !empty($_POST['txpoidEdt']) ) ? functions::decode($_POST['txpoidEdt']) : '0';
    $txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? functions::decode($_POST['txItem']) : '';
    $txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? $_POST['txQty'] : 0;
    $txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? $_POST['txUnit'] : '';
    $txBrand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? $_POST['txBrand'] : '';
    $txLoc = ( isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? functions::decode($_POST['selLoc']) : '';

    if( $txQty && $txItem && $txLoc){
        $db->update('medicine_distribution_detail',array('item'=>$txItem,'qty'=>$txQty,'unit'=>$txUnit,'brand'=>$txBrand,'location'=>$txLoc),array('mdd_id'=>$txpoidEdt));
    #echo $db->last_query;
    }
    functions::sendTo($_SERVER['PHP_SELF'].'?mdid='.functions::encode($md_id));
}

if( isset($_REQUEST['poidDel']) && !empty($_REQUEST['poidDel']) ){
    $poidDel = functions::decode($_REQUEST['poidDel']);
    $db->delete('medicine_distribution_detail',array('mdd_id'=>$poidDel));
    functions::sendTo($_SERVER['PHP_SELF'].'?mdid='.functions::encode($md_id));
}

$editTrue=0;
$poidEdt='';
$item='';$quantity='';$cost='';$unit='';$discount='';$brand=''; $location='';
if( isset($_REQUEST['poidEdt']) && !empty($_REQUEST['poidEdt']) ){
    $poidEdt = functions::decode($_REQUEST['poidEdt']);
    $editTrue = $db->getValue('medicine_distribution_detail','count(*)',array('mdd_id'=>$poidEdt));
    $qvedt = $db->select('medicine_distribution_detail','*',array('mdd_id'=>$poidEdt));
    $rvedt = $db->fetch_array($qvedt);
    $item = $rvedt['item'];
    $quantity = $rvedt['qty'];
    $unit = $rvedt['unit'];
    $brand = $rvedt['brand'];
    $location = $rvedt['location'];
    if($available==0){
        $consumed = $db->getValue('medicine_distribution_detail','sum(qty)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
        $stored = $db->getValue('inhouse_material_storage','sum(quantity)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
        $available = ($stored + $quantity) - $consumed;
    }
}

if( $searchedItem ){
    $item = $searchedItem;
    $brand = $searchedBrnd;
    $unit = $searchedUnit;
    $location = $searchedLoc;
    if($available==0){
        $consumed = $db->getValue('medicine_distribution_detail','sum(qty)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
        $stored = $db->getValue('inhouse_material_storage','sum(quantity)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
        $available = $stored - $consumed;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Medical Stock</title>
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
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Medical Stock</h2>
        </div>
        <div class="box-content">
            <form method="post">
                <div align="left"><br>
                    <div>Project / Department:
                        <strong>
                        <?php 
                            if($project_id){
                                $qProj = $db->select('project','*',array('proj_id'=>$project_id));
                                while($rProj = $db->fetch_array($qProj)):
                                    echo strtoupper($rProj['proj_name']);
                                    echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';
                                endwhile;
                            }
                        ?>
                        </strong>
                    </div>
                    <div>Date: <strong><?php echo functions::datearr($db->getValue('medicine_distribution','md_date',array('md_id'=>$md_id)));?></strong></div><br><br>
                </div>
                <input type="hidden" name="txpoidEdt" id="txpoidEdt" value="<?php echo functions::encode($poidEdt);?>">
                Search Item: 
                <textarea style="width:280px;" class="span6 typeahead" name="txSrchItem" id="txSrchItem" autocomplete='off' onkeyup="searchMaterial(this.value,document.getElementById('selLoc').value)" rows="1"><?php echo $item;?></textarea>
                <select name="selLoc" id="selLoc" onChange="searchMaterial(document.getElementById('txSrchItem').value,this.value)">
                    <option value="">--All Location--</option>
                    <?php 
                    $qLoc = $db->select('inhouse_material_storage_location','*',array());
                    while($rLoc = $db->fetch_array($qLoc)):
                    ?>
                    <option value="<?php echo functions::encode($rLoc['location'])?>" <?php if($location==$rLoc['location']){echo 'selected="selected"';}?>><?php echo $rLoc['location'];?></option>
                    <?php endwhile;?>
                </select>
                <div id="materialView"></div>
                <table width="100%" border="0" align="center" class="table table-striped">
                    <tr>
                        <th width="23%" scope="col"><div align="left">Item</div></th>
                        <th width="10%" scope="col"><div align="center">Unit</div></th>
                        <th width="12%" scope="col"><div align="center">Brand</div></th>
                        <th width="10%" scope="col"><div align="left">Quantity</div></th>
                        <th width="10%" scope="col">&nbsp;</th>
                    </tr>
                    <tr bgcolor="#faf7f7">
                        <td>
                            <div align="left">
                                <input type="hidden" name="txItem" id="txItem" value="<?php echo ($item) ? functions::encode($item) : ''?>">
                                <textarea readonly><?php echo $item?></textarea>
                            </div>
                          <span class="help-inline warning" id="msgtxItem" style="font-weight:bold;" name="msgtxItem"></span>
                        </td>
                        <td><div align="left"><input type="text" style="width:100px;" class="span6 typeahead" name="txUnit" id="txUnit" value="<?php echo $unit?>" readonly></div></td>
                        <td><div align="left"><input type="text" style="width:230px;" name="txBrand" id="txBrand" value="<?php echo $brand;?>" readonly></div></td>
                        <td>
                            <div>
                            <select name="txQty" id="txQty" data-rel="chosen" style="width:100px;">
                                <option value="">--select--</option>
                                <?php for($i=1; $i<=$available; $i++):?>
                                <option value="<?php echo $i?>" <?php if($quantity==$i){echo 'selected="selected"';}?>><?php echo $i?></option>
                                <?php endfor;?>
                            </select>
                            </div>
                            <span class="help-inline warning" id="msgtxQty" style="font-weight:bold;" name="msgtxQty"></span>
                        </td>
                        <td>
                            <div align="center">
                            <?php 
                            if($editTrue){
                                echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-mini btn-primary"> ';
                                echo '<a href="?mdid='.functions::encode($md_id).'" class="btn btn-mini">Cancel</a>';
                            }else
                                echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-small btn-primary">';
                            ?>
                            </div>
                        </td>
                    </tr>
                </table><br><br><br>
                <table width="100%" border="0" align="center" class="table table-striped">
                    <tr>
                        <th width="20%" scope="col"><div align="left">Item</div></th>
                        <th width="8%" scope="col"><div align="left">Unit</div></th>
                        <th width="13%" scope="col"><div align="left">Brand</div></th>
                        <th width="7%" scope="col"><div align="left">Quantity</div></th>
                        <th width="8%" scope="col">&nbsp;</th>
                    </tr>
                    <?php
                        $total_amount=0;
                        $disc_amount=0;
                        $amount=0;
                        $qPOI = $db->select('medicine_distribution md, medicine_distribution_detail mdd','*',array('md.md_id'=>$md_id),'AND md.md_id=mdd.md_id');
                        #echo $db->last_query;
                        while($rPOI = $db->fetch_array($qPOI)):
                    ?>
                    <tr>
                        <td><?php echo $rPOI['item'];?></td>
                        <td><?php echo $rPOI['unit'];?></td>
                        <td><?php echo $rPOI['brand'];?></td>
                        <td><?php echo $rPOI['qty'];?></td>
                        <td>
                            <div align="center">
                                <a id="edit<?php echo $rPOI['md_id'];?>" class="btn btn-mini btn-info" title="Update this item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?mdid=<?php echo functions::encode($md_id);?>&poidEdt=<?php echo functions::encode($rPOI['mdd_id']);?>"><i class="halflings-icon white pencil"></i></a>
                                <a id="del<?php echo $rPOI['md_id'];?>" class="btn btn-mini btn-warning" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?mdid=<?php echo functions::encode($md_id);?>&poidDel=<?php echo functions::encode($rPOI['mdd_id']);?>"><i class="halflings-icon white trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile;?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td></td>
                    </tr>
                </table><p>&nbsp;</p>
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
function getXMLHTTP() { //fuction to return the xml http object
    var xmlhttp=false;  
    try{
        xmlhttp=new XMLHttpRequest();
    }
    catch(e){   
        try{      
            xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch(e){
            try{
                xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch(e1){
                xmlhttp=false;
            }
        }
    }
    return xmlhttp;
}
function searchMaterial(srch,loc) {   
    var strURL="distribution_item_list.php?srch="+srch+"&loc="+loc+"&poidEdt=<?php echo functions::encode($poidEdt)?>&mdid=<?php echo functions::encode($md_id)?>";
    var req = getXMLHTTP();
    if(req){
        req.onreadystatechange = function() {
            if (req.readyState == 4) {
                // only if "OK"
                if (req.status == 200) {            
                    document.getElementById('materialView').innerHTML=req.responseText;            
                }
                else{
                    alert("Problem while using XMLHTTP:\n" + req.statusText);
                }
            }       
        }     
        req.open("GET", strURL, true);
        req.send(null);
    }
}
</script>
<!-- end: JavaScript-->
</body>
</html>