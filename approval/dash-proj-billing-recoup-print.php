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
$project_id = ( isset($_REQUEST['prjID']) && !empty($_REQUEST['prjID']) ) ? functions::decode($_REQUEST['prjID']) : '';

function monthDisp($date=0){
    $exp = explode('-',$date);
    $monthName='';
    if(count($exp)==2){
        $mn = $exp[1];
        $yr = $exp[0];
        $mkDate = mktime(0,0,0,$mn,1,$yr);
        $monthName = date("F'y",$mkDate);
    }
    return $monthName;
}
$arrDirectCost=array();
$arrOverheadCost=array();
$arrNetBilled=array();

$totalNetBilled=0; $allNetBilled=0; $totalCollected=0; $totalCollectible=0;
$year = date('Y');
$month_start = date('m');
$year_start = date('Y');
$month_end = date('m');
$year_end = date('Y');
$year = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? functions::decode($_REQUEST['yr']) : $year;
$arrMonth=array();

$month_start = '01';
$year_start = $year;

$month_end = '11';
$year_end=$year;
$start_date=$year.'-01-01';
$end_date=($year).'-12-31';
#end getting end month

$month_diff = functions::month_diff($start_date,$end_date);
for($i=0; $i<=12; $i++):
    $mkDate = mktime(0,0,0,$month_start + $i,1,$year_start);
    $arrMonth[] = date('Y-m',$mkDate);
    $arrDirectCost[date('Y-m',$mkDate)]=0;
    $arrNetBilled[date('Y-m',$mkDate)]=0;
endfor;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Project Billing Report - Recoupment Printing</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
    <link rel="shortcut icon" href="../img/favicon.png">
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
    <style>.padleft{padding-right: 5px; padding-left: 5px;}</style>
    <!-- end: Favicon -->
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table  width="700" border="0" align="center">
    <thead>
        <tr>
            <td>
                <?php 
                require_once('../class/print_header.php');
                print_header('Recoupment Summary of the Year '.$year);
                ?><br>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <table class="table table-bordered" width="100%" border="0" style="font-size:12px;">
                <?php
                $allAmountBilled=0;$allRecoupment=0;$totalAdvancePayment=0;
                $qProj = $db->query('SELECT * FROM project WHERE project="1" AND date_start BETWEEN "'.$start_date.'" AND "'.$end_date.'" ORDER BY proj_name');
                while( $rProj = $db->fetch_array($qProj)):
                    $proj_cost = $rProj['proj_cost'];
                ?>
                    <tr>
                        <td class="padleft" width="20%"><strong>&nbsp;</strong></td>
                        <td class="padleft" width="7%"><div align="left"><strong>&nbsp;</strong></div></td>
                        <td class="padleft" width="7%"><div align="left"><strong>Recoupment</strong></div></td>
                    </tr>
                    <tr>
                        <td class="padleft"><strong><?php echo $rProj['proj_name']?></strong></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                <?php
                $qPI = $db->select('project_income','*',array('proj_id'=>$rProj['proj_id']),' AND lower(name) != "advance payment" AND  lower(name) != "retention payment" ORDER BY pi_date ASC');
                $countBilledPercent=0;$totalAmountBilled=0;$totalNetBilled=0;$daysElapsed = 0;
                  
                $projRecoupment=0;
                while($rPI = $db->fetch_array($qPI)):
                    $daysElapsed = 0;
                    $amountBilled = $rPI['amount'];
                    $totalAmountBilled += $amountBilled;
                    $allAmountBilled +=$amountBilled;
                    $projRecoupment += $rPI['recoupment'];
                      
                    $netBilled = ($amountBilled - $rPI['vat'] - $rPI['ewt'] - $rPI['retention'] - $rPI['contractor'] - $rPI['recoupment']);
                    $totalNetBilled += $netBilled;
                    $allNetBilled += $netBilled;
                ?>
                    <tr>
                        <td class="padleft"><?php echo $rPI['name'];?></td>
                        <td class="padleft"></td>
                        <td class="padleft"><?php echo functions::formatMoney($rPI['recoupment']);?></td>
                    </tr>
                <?php endwhile;?>

                <?php #advance payment
                $advance_payment = 0;
                $qPI = $db->select('project_income','*',array('proj_id'=>$rProj['proj_id']),' AND (lower(name) = "advance payment") ORDER BY pi_date ASC');
                while($rPI = $db->fetch_array($qPI)):
                    $advance_payment += ($rPI['name']=="Advance Payment") ? $rPI['amount'] : 0;
                    $amountBilled = $rPI['amount'];
                    $netBilled = ($amountBilled - $rPI['vat'] - $rPI['ewt'] - $rPI['retention'] - $rPI['contractor'] - $rPI['recoupment']);

                    $totalAmountBilled += $amountBilled;
                    $allAmountBilled +=$amountBilled;

                    $netBilled = ($amountBilled - $rPI['vat'] - $rPI['ewt'] - $rPI['retention'] - $rPI['contractor'] - $rPI['recoupment']);
                    $totalNetBilled += $netBilled;
                    $allNetBilled += $netBilled;
                    $totalAdvancePayment += $amountBilled;
                ?>
                    <tr>
                        <td class="padleft"><?php echo $rPI['name'];?></td>
                        <td class="padleft"><?php echo functions::formatMoney($amountBilled);?></td>
                        <td class="padleft"></td>
                    </tr>
                <?php endwhile;//while($rPI = $db->fetch_array($qPI)):?>
                <?php if($advance_payment){$allRecoupment += $advance_payment-$projRecoupment;?>
                    <tr>
                        <td class="padleft"></td>
                        <td class="padleft"><div align="right">Balance</div></td>
                        <td class="padleft"><strong><?php echo functions::formatMoney($advance_payment-$projRecoupment);?></strong></td>
                    </tr>
                <?php }//if($advance_payment){?>
                    <tr>
                        <td colspan="3" height="20">&nbsp;</td>
                    </tr>
                <?php endwhile;?>
                    <tr>
                        <td class="padleft" colspan="2"><div align="right">Total Recoupment Balance</div></td>
                        <td class="padleft"><strong><?php echo functions::formatMoney($allRecoupment);?></strong></td>
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
    window.location="dash-proj-billing.php?yr=<?php echo functions::encode($year)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>