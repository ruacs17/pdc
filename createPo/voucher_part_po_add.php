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
$txInvoice='';
if( isset($_POST['btnSearch']) ){
    $_SESSION['txinv'] = ( isset($_POST['txInvoice']) ) ? $_POST['txInvoice'] : '';
    functions::sendTo($_SERVER['REQUEST_URI']);
    die();
}
if( isset($_POST['btnViewAll']) ){
    unset($_POST['txInvoice']);
    functions::sendTo($_SERVER['REQUEST_URI']);
    die();
}

$arrSearch = array();
$vp_id = 0;
$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$vp_id = (isset($_REQUEST['vpid']) && !empty($_REQUEST['vpid']) ) ? functions::decode($_REQUEST['vpid']) : 0;
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$payee = $db->getValue('voucher','supplierID',array('voucher_id'=>$vid));
if( isset($_REQUEST['idTrg']) ){
    $command=0; 
    if(isset($_REQUEST['trg']) && !empty($_REQUEST['trg']) ){
        $trg = functions::decode($_REQUEST['trg']);
        if( is_numeric($trg) )
            $command=1;
        else if($trg=='a')
            $command=2;
    }

    if( $command==1){#check
        $invoice = $db->getValue('po','invoice',array('po_id'=>$po_id));
        $invoice = ($invoice) ? $invoice : ' -- ';
        $vp_title = 'P.O. PAYMENT - Invoice: '.$invoice;
        if( $poNum = $db->getValue('po','po_no',array('po_id'=>$po_id,'po_type'=>'service')) ){
            $vp_title = 'P.O. PAYMENT - Service: '.$poNum;
        }

        $vp_id = $db->insert('voucher_particular',array('vp_title'=>$vp_title,'voucher_id'=>$vid,'vtype'=>'po'));
        if($vid && $vp_id && $po_id){
            $balanceAmount = $db->getValue('view_po_payment','balance',array('po_id'=>$po_id));
            $db->insert('voucher_po_payment',array('vp_id'=>$vp_id,'po_id'=>$po_id,'amount'=>$balanceAmount));
            $db->update('po',array('vp_id'=>$vp_id),array('po_id'=>$po_id));  
        }
        $_SESSION['notif_success']='Item Selected';
    }
    else if($command==2){#unchecked
        $invoice = $db->getValue('po','invoice',array('po_id'=>$po_id));
        $invoice = ($invoice) ? $invoice : ' -- ';
        $vp_title = 'P.O. PAYMENT - Invoice: '.$invoice;
        $vp_id = $db->getValue('voucher_po_payment','vp_id',array('po_id'=>$po_id));
        if($vp_id && $po_id){
            #if not null, make it null;
            $db->update('po',array('vp_id'=>NULL),array('po_id'=>$po_id));
            $db->delete('voucher_particular',array('vp_id'=>$vp_id));
            $db->delete('voucher_po_payment',array('vp_id'=>$vp_id,'po_id'=>$po_id));
        }
        $_SESSION['notif_warning']='Item Unchecked';
    }
    functions::sendTo(functions::pageName().'?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Voucher P.O. Particular</title>
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
    <link rel="shortcut icon" href="../img/favicon.ico">
    <!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Purchase Order List</h2>
        </div>
        <div class="box-content">
            <form method="post">
                <table align="left">
                    <tr>
                        <td valign="top" style="padding-top: 2px"><input type="text" name="txInvoice" id="txInvoice" value="<?php echo $txInvoice?>" placeholder="P.O. or Invoice Number"></td>
                        <td valign="top">&nbsp;<input type="submit" class="btn btn-info" name="btnSearch" id="btnSearch" value="Search">&nbsp;<input type="submit" class="btn btn-info" name="btnViewAll" id="btnViewAll" value="View All"></td>
                    </tr>
                </table>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="8%">P.O. #</th>
                            <th width="8%">Invoice</th>
                            <th width="8%">P.O. Date</th>
                            <th width="15%">Payee</th>
                            <th width="19%">Project</th>
                            <th width="8%">Amount</th>
                            <th width="4%"><div align="center">&nbsp;</div></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        if($txInvoice)
                            $qPO = $db->query('SELECT * FROM view_po_payment WHERE supplierID="'.$db->clean($payee).'" AND po_id IN (SELECT po_id FROM po WHERE invoice="'.$db->clean($txInvoice).'" OR po_no="'.$db->clean($txInvoice).'") HAVING balance > 0 OR po_id IN (SELECT DISTINCT vpp.po_id FROM voucher_po_payment vpp, voucher_particular vp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($vid).'") ORDER BY po_date DESC');
                        else
                            $qPO = $db->query('SELECT * FROM view_po_payment WHERE supplierID="'.$db->clean($payee).'" HAVING balance > 0 OR po_id IN (SELECT DISTINCT vpp.po_id FROM voucher_po_payment vpp, voucher_particular vp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($vid).'") ORDER BY po_date DESC');
                            #echo $db->last_query;
                        while($rPO = $db->fetch_array($qPO)):
                              $invoice = $db->getValue('po','invoice',array('po_id'=>$rPO['po_id']));
                              $po_no = $db->getValue('po','po_no',array('po_id'=>$rPO['po_id']));
                              $checked="";
                              $trg=rand(1,100);
                              $checkedQ = $db->query('SELECT round(amount,2) FROM voucher_po_payment vpp, voucher_particular vp WHERE vpp.vp_id=vp.vp_id AND voucher_id="'.$db->clean($vid).'" AND vpp.po_id="'.$db->clean($rPO['po_id']).'"');
                              $paid = $db->result($checkedQ);
                              if($paid){
                                  $trg='a';
                                  $checked = 'checked="checked"';
                              }
                              $origCost = $rPO['cost'];
                              $balance = ($paid) ? $paid : $rPO['balance'];
                    ?>
                        <tr>
                            <td><?php echo $po_no;?> </i></td>
                            <td><?php echo $invoice;?> </i></td>
                            <td><?php echo functions::datearr($rPO['po_date']);?> </i></td>
                            <td><?php echo $db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID']));?></td>
                            <td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id']));?></td>
                            <td><a id="costdetail<?php echo $rPO['po_id']?>" class="label label-info thickbox" title="View P.O. Details" data-rel="tooltip" onclick="showThis(this.id,'po_view_only.php?po_id=<?php echo functions::encode($rPO['po_id']);?>','P.O. Details','1')"><?php echo functions::formatMoney($balance);?></a></td>
                            <td>
                                <div align="center">
                                <?php #if($invoice){?>
                                      <input type="checkbox" name="selBx" id="selBx<?php echo $rPO['po_id']?>" value="<?php echo $rPO['po_id']?>" onClick="sel('<?php echo functions::encode($vid);?>','<?php echo functions::encode($vp_id);?>','<?php echo functions::encode($rPO['po_id']);?>','<?php echo functions::encode($trg);?>')" <?php echo $checked;?>>
                                      <?php 
                                      # }
                                      #else{
                                      if(empty($invoice)){
                                      ?>
                                          <br><i>(No Invoice)</i>
                                <?php }?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile;?>
                    </tbody>
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
function sel(vid,vpid,po_id,trg){
    window.location="<?php echo $_SERVER['PHP_SELF']?>?idTrg=<?php echo functions::encode(rand(1,100))?>&vid="+vid+"&vpid="+vpid+"&po_id="+po_id+"&trg="+trg+"&Isge=<?php echo functions::encode(rand(1,100))?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>