<?php
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
#$logs = new Logs();
#$logs->save('visit');

$sid = (isset($_REQUEST['sid']) && !empty($_REQUEST['sid']) ) ? functions::decode($_REQUEST['sid']) : 0;

$qShow = $db->select('supplier','*',array('supplierID'=>$sid));
$rShow = $db->fetch_array($qShow);
$txtName = ( isset($rShow['name']) && !empty($rShow['name']) ) ? $rShow['name'] : '';
$txtAddress = ( isset($rShow['address']) && !empty($rShow['address']) ) ? $rShow['address'] : '';
$txtPhone1 = ( isset($rShow['contactPhoneNo']) && !empty($rShow['contactPhoneNo']) ) ? $rShow['contactPhoneNo'] : '';
$txtPhone2 = ( isset($rShow['contactPhoneNo2']) && !empty($rShow['contactPhoneNo2']) ) ? $rShow['contactPhoneNo2'] : '';
$txtCell1 = ( isset($rShow['contactCellNo']) && !empty($rShow['contactCellNo']) ) ? $rShow['contactCellNo'] : '';
$txtCell2 = ( isset($rShow['contactCellNo2']) && !empty($rShow['contactCellNo2']) ) ? $rShow['contactCellNo2'] : '';
$txtConPerson = ( isset($rShow['contactPerson']) && !empty($rShow['contactPerson']) ) ? $rShow['contactPerson'] : '';
$txTIN = ( isset($rShow['tin']) && !empty($rShow['tin']) ) ? $rShow['tin'] : '';
$txEmail = ( isset($rShow['email_add']) && !empty($rShow['email_add']) ) ? $rShow['email_add'] : '';
$txtConPersonDesig = ( isset($rShow['contact_designation']) && !empty($rShow['contact_designation']) ) ? $rShow['contact_designation'] : '';
$txBusType = ( isset($rShow['business_type']) && !empty($rShow['business_type']) ) ? $rShow['business_type'] : '';
$txYrEst = ( isset($rShow['year_established']) && !empty($rShow['year_established']) ) ? $rShow['year_established'] : '';
$txPayTerm = ( isset($rShow['payment_term']) && !empty($rShow['payment_term']) ) ? $rShow['payment_term'] : '';
$selAccredited = ( isset($rShow['accredited']) && !empty($rShow['accredited']) ) ? $rShow['accredited'] : '';
$selEvaluated = ( isset($rShow['evaluated']) && !empty($rShow['evaluated']) ) ? $rShow['evaluated'] : '';
$selVATStat = $rShow['vat'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Supplier Modify</title>
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
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Supplier / Payyee Details</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">        
                <table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
                    <tr>
                        <td width="30%" height="30"><div align="right">Supplier / Payee Name</div></td>
                        <td width="43%"><?php echo $txtName;?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Address</td>
                        <td><?php echo $txtAddress;?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Contact Phone Number 1</div></td>
                        <td><?php echo $txtPhone1;?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Contact Phone Number 2</div></td>
                        <td><?php echo $txtPhone2;?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Contact Cell Number 1</div></td>
                        <td><?php echo $txtCell1;?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Contact Cell Number 2</div></td>
                        <td><?php echo $txtCell2;?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Tax Identification Number (TIN)</div></td>
                        <td><?php echo $txTIN;?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">E-mail Address</div></td>
                        <td><?php echo $txEmail;?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Contact Person</div></td>
                        <td><?php echo $txtConPerson;?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Contact Person's Designation</div></td>
                        <td><?php echo $txtConPersonDesig;?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Type of Business</div></td>
                        <td><?php echo $txBusType;?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Year Established</div></td>
                        <td><?php echo $txYrEst;?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Payment Term</div></td>
                        <td><?php echo $txPayTerm;?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Accreditation Status</div></td>
                        <td><?php echo ($selAccredited) ? $selAccredited : '---' ?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Evaluation Status</div></td>
                        <td><?php echo ($selEvaluated) ? $selEvaluated : '---' ?></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">VAT Status</div></td>
                        <td><?php echo ($selVATStat) ? $selVATStat : '---' ?></td>
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
<!-- end: JavaScript-->
</body>
</html>