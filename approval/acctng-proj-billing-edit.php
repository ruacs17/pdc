<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$piid = (isset($_REQUEST['piid']) && !empty($_REQUEST['piid']) ) ? functions::decode($_REQUEST['piid']) : 0;
$as_id = $db->getValue('account_statement','as_id',array('project_income'=>$piid));

$q = $db->select('account_statement','*',array('as_id'=>$as_id));
$r = $db->fetch_array($q);
$pi_id = $r['project_income'];
$txTran_name = $r['transaction'];
$txBdate = $r['as_date'];
$txTran_AccntType = $r['account_type'];

$vdate = $r['as_date'];
$xdate = explode('-',$vdate);
if(count($xdate)==3){
    $mon = $xdate[1];
    $day = $xdate[2];
    $year = $xdate[0];  
}
$qPIs = $db->select('project_income','*',array('pi_id'=>$pi_id));
$rPIs = $db->fetch_array($qPIs);
$txTran_ID = $rPIs['bill_id'];
$proj_id = $rPIs['proj_id'];
$txTran_amount = $rPIs['amount'];
/*$txTran_vat = $rPI['vat'];
$txTran_ewt = $rPI['ewt'];
$txTran_retention = $rPI['retention'];
$txTran_contax = $rPI['contractor'];
$txTran_recoupment = $rPI['recoupment'];*/
$proj_cost = $db->getValue('project','proj_cost',array('proj_id'=>$proj_id));

if( isset($_POST['btnAdd']) ){

    $bill_id = ( isset($_POST['txTran_ID']) && !empty($_POST['txTran_ID']) ) ? trim($_POST['txTran_ID']) : '';

    if( $bill_id && $pi_id ){

        $db->update('project_income',array('bill_id'=>$bill_id),array('pi_id'=>$pi_id));
        functions::say("Billing ID Updated!");
        functions::sendTo($_SERVER['PHP_SELF'].'?piid='.functions::encode($pi_id));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Account Statement Billing Adding</title>
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
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Billing Form</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <table border="0">
                    <tr>
                        <td width="10%">Project: </td>
                        <td>
                            <?php
                            $qProj = $db->select('project','*',array('project'=>'1','proj_id'=>$proj_id),'ORDER BY proj_name');
                            while($rProj = $db->fetch_array($qProj)):
                                echo '<strong>'.strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')</strong>' : '';
                            endwhile;?>
                        </td>
                    </tr>
                    <tr><td colspan="2">&nbsp;</td></tr>
                    <tr>
                        <td>Project Cost: </td>
                        <td><div><strong><?php echo functions::formatMoney($proj_cost);?></strong></div></td>
                    </tr>
                </table>
                <div><br><br>
                    <?php if($pi_id){?>
                    <table width="100%" border="0" align="center" class="table table-hover">
                        <tr>
                            <th width="15%" scope="col"><div align="left">Transaction</div></th>
                            <th width="15%" scope="col"><div align="left">Billed</div></th>
                            <th width="10%" scope="col"><div align="left">VAT</div></th>
                            <th width="10%" scope="col"><div align="left">EWT</div></th>
                            <th width="10%" scope="col"><div align="left">Retention</div></th>
                            <th width="10%" scope="col"><div align="left">Contractor's Tax</div></th>
                            <th width="10%" scope="col"><div align="left">Recoupment</div></th>
                            <th width="10%" scope="col"><div align="left">Date Billed</div></th>
                            <th width="10%" scope="col"><div align="left">Date Paid</div></th>
                        </tr>
                        <?php
                        $totalBill=0;$totalBillPercent=0;$billPercent=0;$disc_amount=0;$amount=0;$selectedNetAmount=0;
                        $qPI = $db->select('project_income','*',array('proj_id'=>$proj_id,'pi_id'=>$pi_id),'ORDER BY submit_date,pi_date');
                        while($rPI = $db->fetch_array($qPI)):
                            $totalBill += $rPI['amount'];
                            $billPercent = ($proj_cost && $rPI['amount']) ? (($rPI['amount'] / $proj_cost) * 100) : 0;
                            $totalBillPercent += $billPercent;
                            $bgColor='';
                            $selectedNetAmount = $rPI['amount'] - ($rPI['vat'] + $rPI['ewt'] + $rPI['retention'] + $rPI['contractor'] + $rPI['recoupment']);
                        ?>
                        <tr <?php echo $bgColor;?>>
                            <td><?php echo $rPI['name'];?></td>
                            <td><?php echo functions::formatMoney($rPI['amount']);?><?php echo ($billPercent) ? '&nbsp;<i>('.functions::formatMoney($billPercent).'%)</i>' : '';?></td>
                            <td><?php echo functions::formatMoney($rPI['vat']);?></td>
                            <td><?php echo functions::formatMoney($rPI['ewt']);?></td>
                            <td><?php echo functions::formatMoney($rPI['retention']);?></td>
                            <td><?php echo functions::formatMoney($rPI['contractor']);?></td>
                            <td><?php echo functions::formatMoney($rPI['recoupment']);?></td>
                            <td><?php echo functions::datearr($rPI['submit_date']);?></td>
                            <td><?php echo functions::datearr($rPI['pi_date']);?></td>
                        </tr>
                        <?php endwhile;?>
                    </table>
                    <?php }#if($pID)?>

                    <?php if($as_id){?>
                    <table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
                        <tr>
                            <td width="17%" height="30">Account Type</td>
                            <td width="43%">
                                <select name="txVoType" id="txVoType" disabled>
                                    <option value="">--Account Type--</option>
                                    <?php 
                                    $qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
                                    while($rVtype = $db->fetch_array($qVtype)):
                                    ?>
                                    <option value="<?php echo $rVtype['vt_id']?>" <?php if($txTran_AccntType==$rVtype['vt_id'])echo 'selected="selected"';?>><?php echo $rVtype['vt_name']?></option>
                                    <?php endwhile;?>
                                </select>
                                <span class="help-inline warning" id="msgVoType" style="font-weight:bold;" name="msgVoType"></span>
                            </td>
                        </tr>
                        <tr>
                            <td height="30">Billing ID</td>
                            <td>
                                <input type="text" name="txTran_ID" id="txTran_ID" class="span6" value="<?php echo $txTran_ID;?>" style="width:218px"/>
                            </td>
                        </tr>
                        <tr>
                            <td height="30">Billing Amount</td>
                            <td>
                                <input type="text" name="txTran_amount" id="txTran_amount" class="span6" value="<?php echo functions::formatMoney($txTran_amount);?>" onkeyup="FormatCurrency(this);" style="width:218px" readonly />
                                <span class="help-inline warning" id="msgAmount" style="font-weight:bold;" name="msgAmount"></span>
                            </td>
                        </tr>
                        <tr>
                            <td height="30">Net Amount</td>
                            <td>
                                <input type="text" name="txTran_net_amount" id="txTran_net_amount" class="span6" value="<?php echo functions::formatMoney($selectedNetAmount);?>" onkeyup="FormatCurrency(this);" style="width:218px" readonly />
                                <span class="help-inline warning" id="msgAmount" style="font-weight:bold;" name="msgAmount"></span>
                            </td>
                        </tr>
                    </table>
                    <div align="center">
                        <input type="submit" name="btnAdd" id="btnAdd" value=" UPDATE " class="btn btn-primary">
                    </div>
                    <?php } #$pyID?>
                </div>
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
<!-- end: JavaScript-->
</body>
</html>