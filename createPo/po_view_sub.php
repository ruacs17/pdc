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
#echo functions::decode($_REQUEST['po_id']);
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
if( isset($_POST['btnAdd']) ){

	$txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? $_POST['txQty'] : '0';
	$txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? $_POST['txItem'] : '';
	$txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? $_POST['txUnit'] : '';
	$txBrand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? $_POST['txBrand'] : '';
	$txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? $_POST['txCost'] : '0';

	if( $txQty && $txItem && $txCost ){
		$db->insert('po_item',array('po_id'=>$po_id,'item'=>$txItem,'quantity'=>$txQty,'unit'=>$txUnit,'brand'=>$txBrand,'cost'=>$txCost));
	}
	functions::sendTo('po_view.php?po_id='.functions::encode($po_id));
}

if( isset($_POST['btnSave']) ){

	$txpoidEdt = ( isset($_POST['txpoidEdt']) && !empty($_POST['txpoidEdt']) ) ? functions::decode($_POST['txpoidEdt']) : '0';
	$txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? $_POST['txQty'] : '0';
	$txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? $_POST['txItem'] : '';
	$txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? $_POST['txUnit'] : '';
	$txBrand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? $_POST['txBrand'] : '';
	$txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? $_POST['txCost'] : '0';
	
	if( $txQty && $txItem && $txUnit && $txCost){
		$db->update('po_item',array('item'=>$txItem,'quantity'=>$txQty,'unit'=>$txUnit,'brand'=>$txBrand,'cost'=>$txCost),array('po_item_id'=>$txpoidEdt));
	}
	functions::sendTo('po_view.php?po_id='.functions::encode($po_id));
}

if( isset($_REQUEST['poidDel']) && !empty($_REQUEST['poidDel']) ){
	$poidDel = functions::decode($_REQUEST['poidDel']);
	$db->delete('po_item',array('po_item_id'=>$poidDel));
	functions::sendTo('po_view.php?po_id='.functions::encode($po_id));
}

$editTrue=0;
$poidEdt='';
$item='';$quantity='';$brand='';$cost='';$unit='';
if( isset($_REQUEST['poidEdt']) && !empty($_REQUEST['poidEdt']) ){
	$poidEdt = functions::decode($_REQUEST['poidEdt']);
	$editTrue = $db->getValue('po_item','count(*)',array('po_item_id'=>$poidEdt));
	$qvedt = $db->select('po_item','*',array('po_item_id'=>$poidEdt));
	$rvedt = $db->fetch_array($qvedt);
	$item = $rvedt['item'];
	$quantity = $rvedt['quantity'];
	$unit = $rvedt['unit'];
	$brand = $rvedt['brand'];
	$cost = $rvedt['cost'];
}
#echo $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Purchase Order Item Details</title>
            <!-- end: Meta -->
            <!-- start: Mobile Specific -->
            <meta name="viewport" content="width=device-width, initial-scale=1">
              <!-- end: Mobile Specific -->
              <!-- start: CSS -->
              <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
              <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
              <link id="base-style" href="../css/style.css" rel="stylesheet">
              <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
            <div class="row-fluid sortable">
                <div class="box span12">
                    <div class="box-header" data-original-title>
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Purchase Order Item Details</h2>
                    </div>
                    <div class="box-content">
                  <form class="form-horizontal" method="post">
                    <input type="hidden" name="txpoidEdt" id="txpoidEdt" value="<?php echo functions::encode($poidEdt);?>">
                    <table width="100%" border="0" align="center" class="table table-striped">
                      <tr>
                        <th width="12%" scope="col"><div align="center">Item</div></th>
                        <th width="12%" scope="col"><div align="center">Quantity</div></th>
                        <th width="12%" scope="col"><div align="center">Unit</div></th>
                        <th width="12%" scope="col"><div align="center">Brand</div></th>
                        <th width="12%" scope="col"><div align="center">Cost</div></th>
                        <th width="12%" scope="col"><div align="center">Amount</div></th>
                        <th width="10%" scope="col">&nbsp;</th>
                      </tr>
                      <tr bgcolor="#f7ebeb">
                        <td><textarea name="txItem" id="txItem"><?php echo $item;?></textarea></td>
                        <td><input name="txQty" type="text" id="txQty" size="3" value="<?php echo $quantity?>"></td>
                        <td><input name="txUnit" type="text" id="txUnit" size="3" value="<?php echo $unit?>"></td>
                        <td><input name="txBrand" type="text" id="txBrand" size="3" value="<?php echo $brand;?>"></td>
                        <td><input name="txCost" type="text" id="txCost" size="3" value="<?php echo $cost;?>"></td>
                        <td>&nbsp;</td>
                        <td><div align="center">
                        	<?php 
								if($editTrue)
									echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary">';
								else
									echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary">';
							?>
                            </div>
                        </td>
                      </tr>
                      <tr>
                        <td height="25"></td>
                        <td></td>
                        <td height="25"></td>
                        <td></td>
                        <td height="25"></td>
                        <td></td>
                        <td height="25"></td>
                      </tr>
                      <?php
						$total_amount=0;
						$amount=0;
					  	$qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
					  	while($rPOI = $db->fetch_array($qPOI)):
						$amount = $rPOI['cost'] * $rPOI['quantity'];
						$total_amount += $amount;
					  ?>
                      <tr>
                        <td><?php echo $rPOI['item'];?></td>
                        <td><?php echo round($rPOI['quantity'],2);?></td>
                        <td><?php echo $rPOI['unit'];?></td>
                        <td><?php echo $rPOI['brand'];?></td>
                        <td><?php echo $rPOI['cost'];?></td>
                        <td><?php echo functions::formatMoney($amount);?></td>
                        <td>
                        	<div align="center">
                               <a id="del<?php echo $rPOI['po_item_id'];?>" class="btn btn-mini btn-warning" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="po_view.php?po_id=<?php echo functions::encode($po_id);?>&poidDel=<?php echo functions::encode($rPOI['po_item_id']);?>">
                                <i class="halflings-icon white trash"></i> 
                               </a>
                               <a id="edit<?php echo $rPOI['po_item_id'];?>" class="btn btn-mini btn-info" title="Update this item" data-rel="tooltip" href="po_view.php?po_id=<?php echo functions::encode($po_id);?>&poidEdt=<?php echo functions::encode($rPOI['po_item_id']);?>">
                                <i class="halflings-icon white pencil"></i> 
                               </a>
                           </div>
                        </td>
                      </tr>
                      <?php endwhile;?>
                      <tr>
                      	<td></td>
                      	<td>&nbsp;</td>
                        <td></td>
                        <td>&nbsp;</td>
                        <td><div align="right"><strong>Total Amount</strong></div></td>
                        <td><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
                        <td></td>
                       </tr>
                      
                    </table>
                    <p>&nbsp;</p>
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
<script>
function delt(){
	if(confirm('Do you want to remove this?'))
		return true;
	else
		return false;	
}
</script>
<!-- end: JavaScript-->

</body>
</html>

