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

$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$vp_id=(isset($_REQUEST['vpid']) && !empty($_REQUEST['vpid']) ) ? functions::decode($_REQUEST['vpid']) : 0;
$particular_name=$db->getValue('voucher_particular','vp_title',array('voucher_id'=>$vid,'vp_id'=>$vp_id));
$category="";	$item="";	$proj_id="";	$amount="";	$txDate="";
if( isset($_POST['btnSelectPart']) ){
	
	$particular_name = (isset($_POST['txPartName']) && !empty($_POST['txPartName'])) ? $_POST['txPartName'] : 0;
	if( $vp_id = $db->getValue('voucher_particular','vp_id',array('voucher_id'=>$vid,'vp_title'=>$particular_name)) ){
		;	
	}
	elseif($particular_name){
		$part_ins = $db->insertPrint('voucher_particular',array('vp_title'=>$particular_name,'voucher_id'=>$vid));
		$db->query($part_ins);
		$vp_id = $db->insert_id();	
	}
	functions::sendTo('voucher_part_add.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
}

if( isset($_POST['btnAdd']) ){
	$particular_name=$db->getValue('voucher_particular','vp_title',array('voucher_id'=>$vid,'vp_id'=>$vp_id));
	$category = ( isset($_POST['txItemCat']) && !empty($_POST['txItemCat']) ) ? $_POST['txItemCat'] : '0';
	$item = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : '0';
	$proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '0';
	$amount = ( isset($_POST['txAmount']) && !empty($_POST['txAmount']) ) ? functions::moneyToDouble($_POST['txAmount']) : '0';
	
	$txDate = ( isset($_POST['txDate']) && !empty($_POST['txDate']) ) ? $_POST['txDate'] : '0';
	if( $category && $proj_id && $amount && $vid && $vp_id && $txDate ){
		$db->insert('voucher_detail',array('vd_date'=>$txDate,'category'=>$category,'item'=>$item,'proj_id'=>$proj_id,'amount'=>$amount,'vp_id'=>$vp_id));
	}
	functions::sendTo('voucher_part_add.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
}

if( isset($_POST['btnSave']) ){
	
	$txvd_id = ( isset($_POST['txvd_id']) && !empty($_POST['txvd_id']) ) ? functions::decode($_POST['txvd_id']) : '0';
	$particular_name=$db->getValue('voucher_particular','vp_title',array('voucher_id'=>$vid,'vp_id'=>$vp_id));
	$category = ( isset($_POST['txItemCat']) && !empty($_POST['txItemCat']) ) ? $_POST['txItemCat'] : '0';
	$item = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : '0';
	$proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '0';
	$amount = ( isset($_POST['txAmount']) && !empty($_POST['txAmount']) ) ? functions::moneyToDouble($_POST['txAmount']) : '0';
	
	$txDate = ( isset($_POST['txDate']) && !empty($_POST['txDate']) ) ? $_POST['txDate'] : '0';
	if( $category && $proj_id && $amount && $txvd_id && $txDate){
		$db->update('voucher_detail',array('vd_date'=>$txDate,'category'=>$category,'item'=>$item,'proj_id'=>$proj_id,'amount'=>$amount),array('vd_id'=>$txvd_id));
	}
	functions::sendTo('voucher_part_add.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
}

if( isset($_REQUEST['vdidDel']) && !empty($_REQUEST['vdidDel']) ){
	$vdidDel = functions::decode($_REQUEST['vdidDel']);
	$db->delete('voucher_detail',array('vd_id'=>$vdidDel));
	functions::sendTo('voucher_part_add.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
}

$editTrue=0;
$vdidEdt=0;
$txDate = date('Y-m-d');
if( isset($_REQUEST['vdidEdt']) && !empty($_REQUEST['vdidEdt']) ){
	$vdidEdt = functions::decode($_REQUEST['vdidEdt']);
	$editTrue = $db->getValue('voucher_detail','count(*)',array('vd_id'=>$vdidEdt));
	#functions::sendTo('voucher_part_add.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
	$qvedt = $db->select('voucher_detail','*',array('vd_id'=>$vdidEdt));
	$rvedt = $db->fetch_array($qvedt);
	$category = $rvedt['category'];
	$item = $rvedt['item'];
	$proj_id = $rvedt['proj_id'];
	$amount = $rvedt['amount'];
	$txDate = $rvedt['vd_date'];
}
    $arrPayee = array();
    $qTitle = $db->select('voucher_particular','DISTINCT vp_title',array());
    $namesTitle='';
    while($rTitle=$db->fetch_array($qTitle)):
        $namesTitle .='"'.$rTitle['vp_title'].'",';
    endwhile;
    $namesTitle .= '"--"';
	
    $qvdCat = $db->select('voucher_detail','DISTINCT category',array());
    $namesvdCat='';
    while($rvdCat=$db->fetch_array($qvdCat)):
        $namesvdCat .='"'.$rvdCat['category'].'",';
    endwhile;
    $namesvdCat .= '"--"';
#echo $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Voucher Particular</title>
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
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Particular Details</h2>
                        <div class="box-icon">
                            <a href="#" class="btn-minimize"><i class="halflings-icon white chevron-up"></i></a>
                        </div>
                    </div>
                    <div class="box-content">
                  <form class="form-horizontal" method="post">           
                    <table width="60%" align="center" border="0">
                      <tr>
                        <td>
                          <div class="control-group">
                            <label class="control-label" for="inputSuccess">Particular Name</label>
                            <div class="controls">
                              <input type="text" class="span6 typeahead" name="txPartName" id="txPartName" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesTitle;?>]' value="<?php echo $particular_name?>">
                            <input type="submit" name="btnSelectPart" id="btnSelectPart" value="Select" class="btn btn-primary">
                            </div>
                          </div>
                        </td>
                      </tr>            
                    </table>
                    <?php if($vp_id){?>
                    <input type="hidden" name="txvd_id" id="txvd_id" value="<?php echo functions::encode($vdidEdt);?>">
                    <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered">
                      <tr>
                        <th width="14%" scope="col"><div align="center">Date</div></th>
                        <th width="20%" scope="col"><div align="center">Category</div></th>
                        <th width="19%" scope="col"><div align="center">Item Detail</div></th>
                        <th width="20%" scope="col"><div align="center">Project</div></th>
                        <th width="16%" scope="col"><div align="center">Amount</div></th>
                        <th width="8%" scope="col">&nbsp;</th>
                      </tr>
                      <tr bgcolor="#E4E1E1">
                        <td>
                              <input name="txDate" type="text" class="span6 mytextbox" id="txDate" style='width:100px;' value="<?php echo $txDate;?>" readonly>
                                <a href="javascript:NewCssCal('txDate')">
                                  <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                                </a>
                        </td>
                        <td>
								  <select name="txItemCat" id="selectError" data-rel="chosen">
									<option>--select--</option>
                                    <?php 
										$qCat = $db->select('item_deduction','name',array(),'ORDER BY name');
										while($rCat = $db->fetch_array($qCat)):
									?>
									<option value="<?php echo $rCat['name']?>" <?php if($category==$rCat['name'])echo 'selected="selected"';?>><?php echo $rCat['name']?></option>
                                    <?php endwhile;?>
								  </select>
                        </td>
                        <td>
                          <textarea name="txItem" id="txItem"><?php echo $item;?></textarea>
                        </td>
                        <td>
                        <select name="selProj" id="selProj" data-rel="chosen">
                        	<option value="">--select--</option>
                            <?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
									while($rProj = $db->fetch_array($qProj)):
							?>
                            <option value="<?php echo $rProj['proj_id']?>" <?php if($proj_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?></option>
                            <?php endwhile;?>
                        </select>
                        </td>
                        <td><input name="txAmount" type="text" id="txAmount" value="<?php echo ($amount) ? functions::formatMoney($amount) : '';?>" size="30" onkeyup="FormatCurrency(this);" /></td>
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
                      <tr><td colspan="6" height="25"></td></tr>
                      <?php 
					  	$vdate='';
						$total_amount=0;
					  	$qvDetails = $db->select('voucher_detail','*',array('vp_id'=>$vp_id),'ORDER BY vd_date');
					  	while($rvDetails = $db->fetch_array($qvDetails)):
						$total_amount += $rvDetails['amount'];
					  ?>
                      <tr>
                        <td>
                          <?php #echo functions::datearr($rvDetails['vd_date']);
                                if($vdate != $rvDetails['vd_date']){
                                  $vdate = $rvDetails['vd_date'];
                                  echo functions::datearr($rvDetails['vd_date']);
                                }
                          ?>
                        </td>
                        <td><?php echo $rvDetails['category'];?></td>
                        <td><?php echo $rvDetails['item']?></td>
                        <td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rvDetails['proj_id']));?></td>
                        <td><?php echo functions::formatMoney($rvDetails['amount']);?></td>
                        <td>
                        	<div align="center">
                               <a id="del<?php echo $rvDetails['vd_id']?>" class="btn btn-mini btn-warning" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="voucher_part_add.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>&vdidDel=<?php echo functions::encode($rvDetails['vd_id']);?>">
                                <i class="halflings-icon white trash"></i> 
                               </a>
                               <a id="edit<?php echo $rvDetails['vd_id']?>" class="btn btn-mini btn-info" title="Update this item" data-rel="tooltip" href="voucher_part_add.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>&vdidEdt=<?php echo functions::encode($rvDetails['vd_id']);?>">
                                <i class="halflings-icon white pencil"></i> 
                               </a>
                           </div>
                        </td>
                      </tr>
                      <?php endwhile;?>
                      <tr>
                      	<td></td>
                        <td></td>
                        <td></td>
                        <td><div align="right"><strong>Total Amount</strong></div></td>
                        <td><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
                        <td></td>
                       </tr>
                      
                    </table>
                    <?php }#if $vp_id?>
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
<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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

