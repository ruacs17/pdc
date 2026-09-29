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
$jv_id = ( isset($_REQUEST['jv']) && !empty($_REQUEST['jv']) ) ? functions::decode($_REQUEST['jv']) : '';
$jvd_id = ( isset($_REQUEST['jvd']) && !empty($_REQUEST['jvd']) ) ? functions::decode($_REQUEST['jvd']) : '';
$jvd_id_delt = ( isset($_REQUEST['jvdrmv']) && !empty($_REQUEST['jvdrmv']) ) ? functions::decode($_REQUEST['jvdrmv']) : '';
$jv_id = ( isset($_REQUEST['jv']) && !empty($_REQUEST['jv']) ) ? functions::decode($_REQUEST['jv']) : '';
$selItem='';$fieldTrue='';$txAmount="";$asType="";

$qjv = $db->select('journal_voucher','*',array('jv_id'=>$jv_id));
$rjv = $db->fetch_array($qjv);


$jvdEdt = $db->select('journal_voucher_detail','*',array('jvd_id'=>$jvd_id));
$rjvdEdt = $db->fetch_array($jvdEdt);
$selItem = $rjvdEdt['item_id'] ?? '';
$asType = $rjvdEdt['charge_type'] ?? '';
$txAmount = isset($rjvdEdt['amount']) ? functions::formatMoney($rjvdEdt['amount']) : '';
if($jvd_id_delt && $jv_id){
    $db->delete('journal_voucher_detail',array('jvd_id'=>$jvd_id_delt));
    functions::sendTo($_SERVER['PHP_SELF'].'?jv='.functions::encode($jv_id));
}
if( isset($_REQUEST['ap']) ){
    $fieldTrue=1;
}
function position($emp_id){
    global $db;
    $countPos=0;$position='';
    $qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
    while($rPos = $db->fetch_array($qPos)):
        if($countPos)
            $position .= ' /<br>';
        $position .= $rPos['pos_name'];
        $countPos++;
    endwhile;
    return $position;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Journal Particulars</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/style.css" rel="stylesheet">
    <link id="base-style" href="../css/loader.css" rel="stylesheet">
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
<?php
if( isset($_POST['btnSave']) ){
    $selItem = isset($_POST['selItem']) ? $_POST['selItem'] : "";
    $asType = isset($_POST['asType']) ? $_POST['asType'] : "";
    $txAmount = isset($_POST['txAmount']) ? $_POST['txAmount'] : "";
    $amount  = ($txAmount) ? functions::moneyToDouble($txAmount) : 0;
    if($selItem && $asType){
        //For Update
        if($jvd_id){
            $db->update('journal_voucher_detail',array('jv_id'=>$jv_id,'item_id'=>$selItem,'charge_type'=>$asType,'amount'=>$amount),array('jvd_id'=>$jvd_id));
            functions::say('Changes Saved!');
            functions::sendTo($_SERVER['PHP_SELF'].'?jv='.functions::encode($jv_id));
        }
        else{
            if( $jvd_id = $db->getValue('journal_voucher_detail','jvd_id',array('jv_id'=>$jv_id,'item_id'=>$selItem,'charge_type'=>$asType)) )//Check if exist
                $db->update('journal_voucher_detail',array('amount'=>$amount),array('jvd_id'=>$jvd_id));
            else
                $db->insert('journal_voucher_detail',array('jv_id'=>$jv_id,'item_id'=>$selItem,'charge_type'=>$asType,'amount'=>$amount));
        }
        functions::sendTo($_SERVER['PHP_SELF'].'?jv='.functions::encode($jv_id).'&ap=ap');
    }
    else{
        functions::say('Please fill up the form properly!');
    }
}
?>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid sortable">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Journal Voucher Particular</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <div align="right" style="padding-bottom:15px;"><a href="journal_voucher_manage.php?jv=<?php echo functions::encode($jv_id)?>&ap=ap" class="btn btn-small btn-primary">Edit Journal</a>&nbsp;&nbsp;&nbsp;<a  href="?jv=<?php echo functions::encode($jv_id)?>&ap=ap" class="btn btn-small <?php if($fieldTrue==0){echo 'btn-primary';} ?>">Add Particular</a>&nbsp;&nbsp;&nbsp;<a id="mrPrint" href="journal_voucher_print.php?jv=<?php echo functions::encode($jv_id)?>" class="btn btn-info btn-small"><i class="halflings-icon white print"></i></a></div>
                <div align="left" style="padding-bottom:10px;">Project / Department: <strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rjv['proj_id'])); ?></strong></div>
                <table width="100%" align="center" border="0">
                    <tr>
                        <td><div align="right">JV#:</div> </td>
                        <td width="12%"><div> <?php echo $rjv['jv_no'] ?></div></td>
                    </tr>
                    <tr>
                        <td><div align="right">Date:</div></td>
                        <td><div> <?php echo functions::datearr($rjv['jv_date']); ?></td>
                    </tr>
                </table>
                <?php if($fieldTrue){ ?>
                <br>
                <table width="" align="center" border="0">
                    <tr>
                        <tr style="background-color:#E4E1E1">
                            <th style="padding: 10px;" width="50%"><div align="center">Particular</div> </th>
                            <th style="padding: 10px;"><div align="center">Charge Type</div></th>
                            <th style="padding: 10px;"><div align="center">Amount</div></th>
                            <th style="padding: 10px;"></th>
                        </tr>
                        <tr>
                            <td style="padding: 10px;">
                                <div align="left">
                                    <select name="selItem" id="selItem" data-rel="chosen" style="width:450px;" required>
                                        <option value="">--select--</option>
                                        <?php 
                                        $qCat = $db->select('item_deduction','*',array(),'ORDER BY name');
                                        while($rCat = $db->fetch_array($qCat)):
                                        ?>
                                            <option value="<?php echo $rCat['item_id']?>" <?php if($selItem==$rCat['item_id'])echo 'selected="selected"';?>><?php echo $rCat['name']?></option>
                                        <?php endwhile;?>
                                    </select>
                                    <span class="help-inline warning" id="msgItem" style="font-weight:bold;" name="msgItem"></span>
                                </div>
                            </td>
                            <td style="padding: 10px;">
                                <div align="center">
                                    <select name="asType" id="asType" required>
                                        <option value="">--select--</option>
                                        <option value="debit" <?php if($asType=='debit')echo 'selected="selected"';?>>DEBIT</option>
                                        <option value="credit" <?php if($asType=='credit')echo 'selected="selected"';?>>CREDIT</option>
                                    </select>
                                </div>
                            </td>
                            <td style="padding: 10px;"><div align="center"><input type="text" name="txAmount" id="txAmount" class="span6" value="<?php echo $txAmount;?>" onkeyup="FormatCurrency(this);" style="width:150px" /></div></td>
                            <td style="padding: 10px;">
                                <div align="center">
                                    <input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-mini btn-primary">
                                    <a href="?jv=<?php echo functions::encode($jv_id)?>" class="btn btn-mini">Cancel</a>
                                </div>
                            </td>
                        </tr>
                    </tr>
                </table> <br><br>
                <?php } ?>
                <table width="80%" align="center" border="0" class="table table-bordered">
                    <tr style="background-color:#E4E1E1">
                        <th><div align="center"><strong>Particulars</strong></div></th>
                        <th width="15%"><div align="left"><strong>Debit</strong></div></th>
                        <th width="15%"><div align="left"><strong>Credit</strong></div></th>
                        <th width="10%"><div align="left"></div></th>
                    </tr>
                    <?php
                    $totalDebit=0; $totalCredit=0;
                    $qjvd = $db->select('journal_voucher_detail jvd, item_deduction itd ','*',array('jv_id'=>$jv_id),'AND itd.item_id=jvd.item_id ORDER BY charge_type DESC,name');
                    while($rjvd=$db->fetch_array($qjvd)):
                        $credit = ($rjvd['charge_type']=='credit') ? $rjvd['amount'] : 0;
                        $debit = ($rjvd['charge_type']=='debit') ? $rjvd['amount'] : 0;
                        $totalDebit += $debit;
                        $totalCredit += $credit;
                    ?>
                    <tr>
                        <td><div align="left" <?php if($rjvd['charge_type']=='credit'){echo 'style="padding-left: 50px;"';} ?>><?php echo $rjvd['name'] ?></div></td>
                        <td><div align="left"><?php echo ($debit) ? functions::formatMoney($debit) : ''; ?></div></td>
                        <td><div align="left"><?php echo ($credit) ? functions::formatMoney($credit) : ''; ?></div></td>
                        <td>
                            <div align="left" style="padding-left: 20px;">
                                <a id="edit<?php echo $rjvd['jvd_id']?>" class="btn btn-mini btn-warning" title="Modify this particular" data-rel="tooltip" href="?jv=<?php echo functions::encode($jv_id)?>&jvd=<?php echo functions::encode($rjvd['jvd_id'])?>&ap=ap"><i class="halflings-icon white pencil"></i></a>
                                <a id="del<?php echo $rjvd['jvd_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Particular" data-rel="tooltip" href="?jv=<?php echo functions::encode($jv_id)?>&jvdrmv=<?php echo functions::encode($rjvd['jvd_id'])?>&ap=ap"><i class="halflings-icon white trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <tr>
                        <td></td>
                        <td><strong><?php echo functions::formatMoney($totalDebit) ?></strong></td>
                        <td><strong><?php echo functions::formatMoney($totalCredit) ?></strong></td>
                        <td></td>
                    </tr>
                </table>
                <div align="left"><strong>Explanation:</strong></div>
                <div style="padding: 20px"><?php echo $rjv['remarks']; ?></div>
                <table width="100%" border="0" style="font-size: 12px;">
                    <tr>
                        <td align="center" width="50%">Prepared by:</td>
                        <td align="center" width="50%">Approved by:</td>
                    </tr>
                    <tr>
                        <td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$rjv['prepared_by']));?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
                        <td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$rjv['approved_by']));?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
                    </tr>
                    <tr>
                        <td align="center"><strong><?php echo position($rjv['prepared_by']);?></strong></td>
                        <td align="center"><strong><?php echo position($rjv['approved_by']);?></strong></td>
                    </tr>
                    <tr>
                        <td align="center" valign="bottom">Date:</td>
                        <td align="center" valign="bottom">Date:</td>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
$(document).ready(function(){
    var res = false;
    $('#btnSave').click(function(){
        $('#msgItem').html("");
        if( $('#selItem').val()=="" ){
            $('#msgItem').html("Particular Required!");
            res=false;
        }
        else
            res=true;
        return res;
    });
});
function delt(){
    if(confirm('Do you want to remove this Item?'))
        return true;
    else
        return false; 
}
</script>
</body>
</html>