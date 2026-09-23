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
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$searchedItem = (isset($_REQUEST['srcItm']) && !empty($_REQUEST['srcItm']) ) ? functions::decode($_REQUEST['srcItm']) : 0;
$poidEdt=0;
if( isset($_POST['btnAdd']) ){
    $txItem = ( isset($_POST['txItemID']) && !empty($_POST['txItemID']) ) ? functions::decode($_POST['txItemID']) : 0;
    $txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? functions::moneyToDouble($_POST['txQty']) : 0;

    $txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
    $txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
    $txBdate = ( $txbYear && $txbMon && $txbDay) ? $txbYear.'-'.$txbMon.'-'.$txbDay : '';
    $locID = ( isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? functions::decode($_POST['selLoc']) : '';

  if( $txItem && $txQty && $txBdate && $locID){

      if( $db->getValue('material_reference','count(*)',array('mf_id'=>$txItem)) ){
            $itemName = $db->getValue('material_reference','item',array('mf_id'=>$txItem));
            $unit = $db->getValue('material_reference','unit',array('mf_id'=>$txItem));
            $brand = $db->getValue('material_reference','brand',array('mf_id'=>$txItem));
            $location = $db->getValue('inhouse_material_storage_location','location',array('imsl_id'=>$locID));
            $db->insert('inhouse_material_storage',array('item'=>$itemName,'unit'=>$unit,'brand'=>$brand,'ims_date'=>$txBdate,'quantity'=>$txQty,'location'=>$location));
            functions::say('Added to Inventory!');
      }
      functions::sendTo($_SERVER['PHP_SELF']);
  }
}

$item='';$quantity='';$brand='';$cost='';$unit='';$qty_delivered='';$discount='';
if( $searchedItem ){
    $itemID = $db->getValue('material_reference','mf_id',array('mf_id'=>$searchedItem)); 
    $item = $db->getValue('material_reference','item',array('mf_id'=>$searchedItem));
    $brand = $db->getValue('material_reference','brand',array('mf_id'=>$searchedItem));
    $unit = $db->getValue('material_reference','unit',array('mf_id'=>$searchedItem));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Material Stock Add</title>
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
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Material Stock Add</h2>
        </div>
        <div class="box-content">
            <form method="post">
                Search Item:
                <textarea style="width:280px;" class="span6 typeahead" name="txSrchItem" id="txSrchItem" autocomplete='off' onkeyup="searchMaterial(this.value)" rows="1"></textarea>
                <div id="materialView"></div>
                <table width="100%" border="0" align="center" class="table table-striped">
                    <tr>
                        <th width="20%" scope="col"><div align="center">Item</div></th>
                        <th width="7%" scope="col"><div align="center">Unit</div></th>
                        <th width="10%" scope="col"><div align="center">Brand</div></th>
                        <th width="8%" scope="col"><div align="center">Quantity</div></th>
                        <th width="18%" scope="col"><div align="center">Date</div></th>
                        <th width="15%" scope="col"><div align="center">Storage Location</div></th>
                        <th width="8%" scope="col">&nbsp;</th>
                    </tr>
                    <tr bgcolor="#faf7f7">
                        <td>
                            <div align="center">
                                <input type="hidden" name="txItemID" id="txItemID" value="<?php echo ($searchedItem) ? functions::encode($searchedItem) : ''?>">
                                <textarea style="width:260px;" name="txItemNme" id="txItemNme" readonly><?php echo $item?></textarea>
                            </div>
                            <span class="help-inline warning" id="msgtxItem" style="font-weight:bold;" name="msgtxItem"></span>
                        </td>
                        <td><div align="center"><input type="text" style="width:70px;" class="span6 typeahead" name="txUnit" id="txUnit" value="<?php echo $unit?>" readonly></div></td>
                        <td><div align="center"><input type="text" style="width:150px;" name="txBrand" id="txBrand" value="<?php echo $brand;?>" readonly></div></td>
                        <td>
                            <div align="center"><input type="text" style="width:80px;" name="txQty" id="txQty" value="<?php echo $quantity?>" onkeypress="return checkinput(this, event);" onkeyup="FormatCurrency(this);"></div>
                            <span class="help-inline warning" id="msgtxQty" style="font-weight:bold;" name="msgtxQty"></span>
                        </td>
                        <td>
                            <div align="center">
                                <select name="bdMon" id="bdMon" style="width:75px;">
                                    <option value="">Month</option>
                                    <option value="01">Jan</option>
                                    <option value="02">Feb</option>
                                    <option value="03">Mar</option>
                                    <option value="04">Apr</option>
                                    <option value="05">May</option>
                                    <option value="06">Jun</option>
                                    <option value="07">Jul</option>
                                    <option value="08">Aug</option>
                                    <option value="09">Sep</option>
                                    <option value="10">Oct</option>
                                    <option value="11">Nov</option>
                                    <option value="12">Dec</option>
                                </select>
                                <select name="bdDay" id="bdDay" style="width:60px;">
                                    <option value="">Day</option>
                                    <?php for($i=1;$i<=31;$i++):?>
                                    <option value="<?php echo ($i<10) ? '0'.$i : $i;?>"><?php echo $i;?></option>
                                    <?php endfor;?>
                                </select>
                                <select name="bdYear" id="bdYear" style="width:70px;">
                                    <option value="">Year</option>
                                    <?php for($y=(date('Y') + 2);$y>=2012;$y--):?>
                                    <option value="<?php echo $y;?>"><?php echo $y;?></option>
                                    <?php endfor;?>
                                </select>
                                <span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
                            </div>
                        </td>
                        <td>
                            <div align="center">
                                <select name="selLoc" id="selLoc" style="width:200px;">
                                    <option value="">--select--</option>
                                    <?php 
                                    $q=$db->select('inhouse_material_storage_location','',array(),'ORDER BY location');
                                    while($r = $db->fetch_array($q)):
                                    ?>
                                    <option value="<?php echo functions::encode($r['imsl_id'])?>"><?php echo $r['location']?></option>
                                    <?php endwhile;?>
                                </select>
                            </div>
                            <span class="help-inline warning" id="msgLoc" style="font-weight:bold;" name="msgLoc"></span>
                        </td>
                        <td><div align="center"><input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-small btn-primary"></div>
                        </td>
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
$(document).ready(function(){
    var res = false;
    $('#btnAdd,#btnSave').click(function(){
        $('#msgtxItem').html("");
        $('#msgtxQty').html("");
        $('#msgBdate').html("");
        $('#msgLoc').html("");


        if( $('#txItemNme').val()=="" ){
            $('#msgtxItem').html("Item Required!");
            res=false;
        }
        else if( $('#txQty').val()=="" ){
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
<script>
function getXMLHTTP() { //fuction to return the xml http object
    var xmlhttp=false;  
    try{
        xmlhttp=new XMLHttpRequest();
    }
    catch(e)  {   
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
  
function searchMaterial(srch) {   
    var strURL="material_reference_list.php?srch="+srch;
    var req = getXMLHTTP();

    if (req) {
        req.onreadystatechange = function() {
            if (req.readyState == 4) {
                // only if "OK"
                if (req.status == 200) {            
                    document.getElementById('materialView').innerHTML=req.responseText;            
                }else{
                    alert("Problem while using XMLHTTP:\n" + req.statusText);
                }
            }       
        }     
        req.open("GET", strURL, true);
        req.send(null);
    }
}
</script>
</body>
</html>