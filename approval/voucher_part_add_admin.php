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
#$vp_id=(isset($_REQUEST['vpid']) && !empty($_REQUEST['vpid']) ) ? functions::decode($_REQUEST['vpid']) : 0;
#$particular_name=$db->getValue('voucher_particular','vp_title',array('voucher_id'=>$vid,'vp_id'=>$vp_id));
$category=""; $item=""; $proj_id="";  $amount=0; $txDate="";$amountIssue=0;
#$particular_name = "Equipment Rental";

#$vp_id=$db->getValue('voucher_particular','vp_id',array('voucher_id'=>$vid,'vp_title'=>$particular_name,'vtype'=>'cash'));

$vp_id=$db->getValue('voucher_particular','vp_id',array('voucher_id'=>$vid,'vtype'=>'cash'));
$particular_name=$db->getValue('voucher_particular','vp_title',array('voucher_id'=>$vid,'vp_id'=>$vp_id,'vtype'=>'cash'));
if($particular_name != 'Equipment Rental'){
    $db->update('voucher_particular',array('vp_title'=>'Equipment Rental'),array('voucher_id'=>$vid,'vp_id'=>$vp_id,'vtype'=>'cash'));
}

if( $db->getValue('voucher_particular','vp_id',array('voucher_id'=>$vid,'vtype'=>'cash'))==0 ){
    $particular_name = "Equipment Rental";
    $part_ins = $db->insertPrint('voucher_particular',array('vp_title'=>$particular_name,'voucher_id'=>$vid,'vtype'=>'cash'));
    $db->query($part_ins);
    $vp_id = $db->insert_id();
    functions::sendTo($_SERVER['PHP_SELF'].'?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
}


if( isset($_POST['btnAdd']) ){
    $arr = array();
    $particular_name=$db->getValue('voucher_particular','vp_title',array('voucher_id'=>$vid,'vp_id'=>$vp_id,'vtype'=>'cash'));
    $category = 62;
    $item = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : '';
    $proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
    if($proj_id)
        $arr = array('proj_id'=>$proj_id);

    $amount = ( isset($_POST['txAmount']) && !empty($_POST['txAmount']) ) ? functions::moneyToDouble($_POST['txAmount']) : '0';
    $amountIssue = $amount;

    $txDate = ( isset($_POST['txDate']) && !empty($_POST['txDate']) ) ? $_POST['txDate'] : '';
    $arrDate = explode("/",$txDate);
    $date = $arrDate[2].'-'.$arrDate[0].'-'.$arrDate[1];
  
    if( $category  && $amountIssue && $vid && $vp_id && $item){
        $arr = array_merge($arr,array('vd_date'=>$date,'category_id'=>$category,'item'=>$item,'amount'=>$amount,'amount_issue'=>$amountIssue,'vp_id'=>$vp_id));
        $ins = $db->insertPrint('voucher_detail',$arr);
        $db->query($ins);
        $vd_id = $db->insert_id();
        $account_type = $db->getValue('voucher','vt_id',array('voucher_id'=>$vid));
        $v_no = $db->getValue('voucher','voucher_no',array('voucher_id'=>$vid));
        $chequeDate = $db->getValue('voucher','cheque_date',array('voucher_id'=>$vid));
        $as_date = ($chequeDate) ? $chequeDate : $date;
        $return = $amountIssue - $amount;
        if($return !=0 )
            $db->insert('account_statement',array('as_type'=>'credit','transaction'=>'cash return: '.$item,'description'=>'voucher: '.$v_no,'as_date'=>$as_date,'as_amount'=>round($return,2),'account_type'=>$account_type,'tag_id'=>$vd_id,'transaction_type'=>'cash return'));
    }
    functions::sendTo($_SERVER['PHP_SELF'].'?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
}

if( isset($_POST['btnSave']) ){
    $arr = array();
    $txvd_id = ( isset($_POST['txvd_id']) && !empty($_POST['txvd_id']) ) ? functions::decode($_POST['txvd_id']) : '0';
    $particular_name=$db->getValue('voucher_particular','vp_title',array('voucher_id'=>$vid,'vp_id'=>$vp_id,'vtype'=>'cash'));
    $category = 62;
    $item = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : '';
    $proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
    if($proj_id)
        $arr = array('proj_id'=>$proj_id);
    else
        $db->query("UPDATE voucher_detail SET proj_id=NULL WHERE vd_id='".$db->clean($txvd_id)."'");
    $amount = ( isset($_POST['txAmount']) && !empty($_POST['txAmount']) ) ? functions::moneyToDouble($_POST['txAmount']) : '0';
    $amountIssue = $amount;
    $txDate = ( isset($_POST['txDate']) && !empty($_POST['txDate']) ) ? $_POST['txDate'] : '';
    $arrDate = explode("/",$txDate);
    $date = $arrDate[2].'-'.$arrDate[0].'-'.$arrDate[1];

    if( $category && $amountIssue && $txvd_id && $item){
        $arr = array_merge($arr,array('vd_date'=>$date,'category_id'=>$category,'item'=>$item,'amount'=>$amount,'amount_issue'=>$amountIssue,'vp_id'=>$vp_id));
        $db->update('voucher_detail',$arr,array('vd_id'=>$txvd_id));

        $account_type = $db->getValue('voucher','vt_id',array('voucher_id'=>$vid));
        $chequeDate = $db->getValue('voucher','cheque_date',array('voucher_id'=>$vid));
        $v_no = $db->getValue('voucher','voucher_no',array('voucher_id'=>$vid));
        $as_date = ($chequeDate) ? $chequeDate : $date;
        $return = $amountIssue - $amount;
        if($return==0 )
            $db->delete('account_statement',array('tag_id'=>$txvd_id));
        else{
            if( $db->getValue('account_statement','count(*)',array('tag_id'=>$txvd_id)) )
                $db->update('account_statement',array('as_amount'=>round($return,2),'transaction'=>'cash return: '.$item,'as_date'=>$as_date,'confirmned'=>1),array('tag_id'=>$txvd_id));
            else
                $db->insert('account_statement',array('as_type'=>'credit','transaction'=>'cash return: '.$item,'description'=>'voucher: '.$v_no,'as_date'=>$as_date,'as_amount'=>round($return,2),'account_type'=>$account_type,'tag_id'=>$txvd_id,'transaction_type'=>'cash return'));
        }
    }
    functions::sendTo($_SERVER['PHP_SELF'].'?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
}

if( isset($_REQUEST['vdidDel']) && !empty($_REQUEST['vdidDel']) ){
    $vdidDel = functions::decode($_REQUEST['vdidDel']);
    $db->delete('voucher_detail',array('vd_id'=>$vdidDel));
    $db->delete('account_statement',array('tag_id'=>$vdidDel));
    functions::sendTo($_SERVER['PHP_SELF'].'?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
}

$editTrue=0;
$vdidEdt=0;
$td = $db->getValue('voucher','vdate',array('voucher_id'=>$vid));
$txDateEdt = explode("-",$td);
$txDate = $txDateEdt[1].'/'.$txDateEdt[2].'/'.$txDateEdt[0];

if( isset($_REQUEST['vdidEdt']) && !empty($_REQUEST['vdidEdt']) ){
    $vdidEdt = functions::decode($_REQUEST['vdidEdt']);
    $editTrue = $db->getValue('voucher_detail','count(*)',array('vd_id'=>$vdidEdt));
    $qvedt = $db->select('voucher_detail','*',array('vd_id'=>$vdidEdt));
    $rvedt = $db->fetch_array($qvedt);
    $category = $rvedt['category_id'];
    $item = $rvedt['item'];
    $proj_id = $rvedt['proj_id'];
    $amount = $rvedt['amount'];
    $amountIssue = $amount;
    $txDateEdt = explode("-",$rvedt['vd_date']);
    $txDate = $txDateEdt[1].'/'.$txDateEdt[2].'/'.$txDateEdt[0];
}
$arrPayee = array();
$qTitle = $db->select('voucher_particular','DISTINCT vp_title',array('vtype'=>'cash'));
$namesTitle='';
while($rTitle=$db->fetch_array($qTitle)):
    $string = preg_replace("/'/",'"',$rTitle['vp_title']);
    $namesTitle .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesTitle .= '"--"';
  
$qItem = $db->select('voucher_detail','DISTINCT item',array());
$namesItem='';
while($rItem=$db->fetch_array($qItem)):
    $string = preg_replace("/'/",'"',$rItem['item']);
    $namesItem .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesItem .= '"--"';
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
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Particular Details</h2>
        </div>
        <div class="box-content" style="visibility:hidden;">
            <ul class="nav tab-menu nav-tabs">
                <li class="active"><a href="<?php echo 'voucher_part_add_admin.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id)?>" style="opacity:.9">Admin Charges</a></li>
                <li><a href="<?php echo 'voucher_part_add.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id)?>">Project Charges</a></li>
            </ul>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <?php if($vp_id){?>
                <input type="hidden" name="txvd_id" id="txvd_id" value="<?php echo functions::encode($vdidEdt);?>">
                <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered">
                    <tr>
                        <th width="11%" scope="col"><div align="center">Date</div></th>
                        <th width="19%" scope="col"><div align="center">Item Detail</div></th>
                        <th width="20%" scope="col"><div align="center">Charge To</div></th>
                        <th width="10%" scope="col"><div align="center">Amount</div></th>
                        <th width="8%" scope="col">&nbsp;</th>
                    </tr>
                    <tr bgcolor="#f7ebeb">
                        <td><input type="text" class="input-medium datepicker" style="width: 80px;" name="txDate" id="txDate" value="<?php echo $txDate;?>"></td>
                        <td>
                            <textarea name="txItem" id="txItem" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesItem;?>]'><?php echo $item;?></textarea>
                            <span class="help-inline warning" id="msgtxItem" style="font-weight:bold;" name="msgtxItem"></span>
                        </td>
                        <td>
                            <select name="selProj" id="selProj" data-rel="chosen" style="width: 480px;">
                                <option value="">--Select--</option>
                                <?php 
                                $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
                                while($rProj = $db->fetch_array($qProj)):
                                ?>
                                <option value="<?php echo $rProj['proj_id']?>" <?php if($proj_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo ucwords(strtolower($rProj['proj_name']));?></option>
                                <?php endwhile;?>
                            </select>
                            <span class="help-inline warning" id="msgtxProj" style="font-weight:bold;" name="msgtxProj"></span>
                        </td>
                        <td>
                            <div align="center">
                                <input name="txAmount" type="text" id="txAmount" style="width: 90px;" value="<?php echo ($amount) ? functions::formatMoney($amount) : 0;?>" size="30" onkeyup="FormatCurrency(this);" />
                                <span class="help-inline warning" name="msgtxAmount" id="msgtxAmount" style="font-weight:bold;"></span>
                            </div>
                        </td>
                        <td>
                            <div align="center">
                            <?php 
                            if($editTrue)
                                echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary">';
                            else
                              echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary">';
                            ?>
                            </div>
                        </td>
                    </tr>
                    <tr><td colspan="5" height="25"></td></tr>
                      <?php
                      $vdate='';$total_amount=0;$total_amount_issue=0;
                      $qvDetails = $db->select('voucher_detail','*',array('vp_id'=>$vp_id),'ORDER BY vd_date');
                      while($rvDetails = $db->fetch_array($qvDetails)):
                          $total_amount_issue += $rvDetails['amount_issue'];
                          $total_amount += $rvDetails['amount'];
                      ?>
                    <tr>
                        <td>
                            <?php
                            if($vdate != $rvDetails['vd_date']){
                                $vdate = $rvDetails['vd_date'];
                                echo functions::datearr($rvDetails['vd_date']);
                            }?>
                        </td>
                        <td><?php echo $rvDetails['item']?></td>
                        <td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rvDetails['proj_id']));?></td>
                        <td><?php echo functions::formatMoney($rvDetails['amount']);?></td>
                        <td>
                            <div align="center">
                                <a id="del<?php echo $rvDetails['vd_id']?>" class="btn btn-mini btn-warning" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>&vdidDel=<?php echo functions::encode($rvDetails['vd_id']);?>"><i class="halflings-icon white trash"></i></a>
                                <a id="edit<?php echo $rvDetails['vd_id']?>" class="btn btn-mini btn-info" title="Update this item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>&vdidEdt=<?php echo functions::encode($rvDetails['vd_id']);?>"><i class="halflings-icon white pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile;?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td><div align="right"><strong>Total Amount</strong></div></td>
                        <td><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
                        <td></td>
                    </tr>
                </table>
                <?php }#if $vp_id?><p>&nbsp;</p>
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
$(document).ready(function(){
    var res = false;
    $('#btnAdd,#btnSave').click(function(){
        $('#msgtxCat').html("");
        $('#msgtxItem').html("");
        $('#msgtxProj').html("");
        $('#msgtxAmount').html("");
        $('#msgtxAmountIssue').html("");

        if( $('#txItem').val()=="" ){
            $('#msgtxItem').html("Item Required!");
            $('#txItem').focus();
            res=false;
        }
        else if( $('#txAmount').val()==0 ){
            $('#msgtxAmount').html("Amount Required!");
            $('#txAmount').focus();
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