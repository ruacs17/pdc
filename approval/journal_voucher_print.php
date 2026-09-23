<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
  
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));

$jv_id = ( isset($_REQUEST['jv']) && !empty($_REQUEST['jv']) ) ? functions::decode($_REQUEST['jv']) : '';
$qjv = $db->select('journal_voucher','*',array('jv_id'=>$jv_id));
$rjv = $db->fetch_array($qjv);

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
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>Equipment List Print</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
    <!-- end: CSS -->
    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <link id="ie-style" href="../css/ie.css" rel="stylesheet">
    <![endif]-->
    <!--[if IE 9]>
    <link id="ie9style" href="../css/ie9.css" rel="stylesheet">
    <![endif]-->
    <style type="text/css">
    .padParLeft{padding-left:10px;}
    .padAmLeft{padding-left:60px;}
    </style>
    </head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table  width="700" border="0" align="center">
    <thead>
        <tr>
            <td>
                <?php 
                require_once('../class/print_header.php');
                print_header('JOURNAL VOUCHER REPORT');
                ?><br>
            </td>
        </tr>
    </thead>
    </tbody>
        <tr>
            <td>
                <div align="left" style="padding-bottom:10px;">Project / Department: <strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rjv['proj_id'])); ?></strong></div>
                <div align="left">
                    <table width="100%" align="center" border="0" style="font-size:12px;">
                        <tr>
                            <td><div align="right">JV#:</div> </td>
                            <td width="15%"><div><strong><?php echo $rjv['jv_no'] ?></strong></div></td>
                        </tr>
                        <tr>
                            <td><div align="right">Date:</div></td>
                            <td><div><strong><?php echo functions::datearr($rjv['jv_date']); ?></strong></td>
                        </tr>
                    </table>
                </div>
                <table width="80%" align="center" border="0" class="table table-bordered" style="font-size:12px;">
                    <tr style="background-color:#E4E1E1">
                        <th><div align="center"><strong>Particulars</strong></div></th>
                        <th width="15%"><div align="left"><strong>Debit</strong></div></th>
                        <th width="15%"><div align="left"><strong>Credit</strong></div></th>
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
                    </tr>
                    <?php endwhile; ?>
                    <tr>
                        <td></td>
                        <td><strong><?php echo functions::formatMoney($totalDebit) ?></strong></td>
                        <td><strong><?php echo functions::formatMoney($totalCredit) ?></strong></td>
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
            </td>
        </tr>
    </tbody>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
    $(document).ready(function(){
        window.print();
        setTimeout("closePrint()",200);
    });
    function closePrint(){
        window.location="journal_voucher_manage_item.php?jv=<?php echo functions::encode($jv_id) ?>";
    }
</script>
<!-- end: JavaScript-->
</body>
</html>